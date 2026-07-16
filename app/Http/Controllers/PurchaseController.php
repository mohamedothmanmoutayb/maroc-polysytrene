<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Client;
use App\Models\SalesOrderPayment;
use App\Models\Check;
use App\Models\Traite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_purchases')->only(['index', 'show', 'getStatistics', 'getClientOrders']);
        $this->middleware('can:create_purchases')->only(['create', 'store', 'addPaymentToClient']);
        $this->middleware('can:edit_purchases')->only(['edit', 'update']);
        $this->middleware('can:delete_purchases')->only(['destroy']);
    }

    /**
     * Display règlements grouped by client (one row per client).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dateFrom = $request->filled('date_from') ? $request->date_from : null;
            $dateTo   = $request->filled('date_to')   ? $request->date_to   : null;
            $clientId = $request->filled('client_id') ? (int) $request->client_id : null;
            $paymentMethod = $request->filled('payment_method') ? $request->payment_method : null;

            // Exclude advance/avoir — they are not "new money", they use existing client balance.
            // total_paid uses the full received amount (not just the portion applied to the
            // order), so it reflects what the client actually handed over.
            $query = DB::table('sales_order_payments as p')
                ->leftJoin('sales_orders as so', 'p.order_id', '=', 'so.order_id')
                ->whereNotIn('p.payment_method', ['advance', 'avoir'])
                ->select([
                    DB::raw('COALESCE(p.client_id, so.client_id) as client_id'),
                    DB::raw('SUM(COALESCE(p.received_amount, p.amount)) as total_paid'),
                    DB::raw('COUNT(*) as payment_count'),
                    DB::raw('COUNT(DISTINCT p.order_id) as order_count'),
                    DB::raw('MAX(p.payment_date) as last_payment_date'),
                ])
                ->where(function ($q) {
                    $q->whereNotNull('p.client_id')->orWhereNotNull('so.client_id');
                })
                ->groupBy(DB::raw('COALESCE(p.client_id, so.client_id)'));

            if ($dateFrom) $query->whereDate('p.payment_date', '>=', $dateFrom);
            if ($dateTo)   $query->whereDate('p.payment_date', '<=', $dateTo);
            if ($clientId) {
                $query->where(DB::raw('COALESCE(p.client_id, so.client_id)'), '=', $clientId);
            }
            if ($paymentMethod) {
                $query->where('p.payment_method', $paymentMethod);
            }

            $query->orderByRaw('MAX(p.payment_date) DESC');

            $grandTotal = (clone $query)->get()->sum('total_paid');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('client_name', function ($row) {
                    $client = Client::find($row->client_id);
                    return $client ? $client->display_name : '—';
                })
                ->addColumn('order_count_badge', function ($row) {
                    return '<span class="badge bg-primary">' . $row->payment_count . ' règlement(s)</span>';
                })
                ->addColumn('total_paid_fmt', function ($row) {
                    return '<span class="text-success fw-bold">' . number_format($row->total_paid, 2, ',', '.') . ' DH</span>';
                })
                ->addColumn('total_remaining_fmt', function ($row) {
                    $client  = Client::find($row->client_id);
                    $balance = $client ? (float) $client->balance : 0;
                    if ($balance < -0.01) {
                        return '<span class="text-danger fw-bold">' . number_format(abs($balance), 2, ',', '.') . ' DH <small>(dû)</small></span>';
                    } elseif ($balance > 0.01) {
                        return '<span class="text-success fw-bold">' . number_format($balance, 2, ',', '.') . ' DH <small>(avance)</small></span>';
                    }
                    return '<span class="text-muted fw-bold">0,00 DH</span>';
                })
                ->addColumn('action', function ($row) {
                    $client = Client::find($row->client_id);
                    $clientName = $client ? htmlspecialchars($client->display_name, ENT_QUOTES) : '—';
                    return '<button type="button" class="btn btn-sm btn-outline-primary btn-client-orders"
                                data-client-id="' . $row->client_id . '"
                                data-client-name="' . $clientName . '">
                                <i class="fas fa-list me-1"></i>Voir règlements
                            </button>';
                })
                ->with('grandTotal', $grandTotal)
                ->rawColumns(['action', 'order_count_badge', 'total_paid_fmt', 'total_remaining_fmt'])
                ->make(true);
        }

        // Summary stats — exclude advance/avoir (they are not "new money")
        $totalReglements  = SalesOrderPayment::whereNotIn('payment_method', ['advance', 'avoir'])->count();
        $totalAmount      = SalesOrderPayment::whereNotIn('payment_method', ['advance', 'avoir'])
            ->selectRaw('SUM(COALESCE(received_amount, amount)) as total')->value('total') ?? 0;
        $orderReglements  = SalesOrderPayment::whereNotNull('order_id')->whereNotIn('payment_method', ['advance', 'avoir'])->count();
        $directReglements = SalesOrderPayment::whereNull('order_id')->whereNull('credit_note_id')->whereNotIn('payment_method', ['advance', 'avoir'])->count();
        $clients = Client::orderBy('name')->get(['client_id', 'name', 'entreprise_name', 'person_type']);

        return view('pages.purchases.index', compact(
            'totalReglements', 'totalAmount', 'orderReglements', 'directReglements', 'clients'
        ));
    }

    /**
     * Update a payment (edit method, amount, document).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,transfer,traite,advance,avoir',
            'notes' => 'nullable|string|max:1000',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $payment = SalesOrderPayment::findOrFail($id);
            $oldAmount = $payment->amount;
            $oldMethod = $payment->payment_method;
            // 'amount' submitted here is the FULL amount the client handed over (same value
            // shown in the edit modal / règlements list). For order-linked payments it may
            // exceed what this order can absorb — the extra is credited to the client balance,
            // exactly like a new payment with an excess would be.
            $newTotal = (float) $request->amount;
            $oldReceived = (float) ($payment->received_amount ?? $oldAmount);
            $oldExcess = max(0, $oldReceived - (float) $oldAmount);

            $newApplied = $newTotal;
            $newExcess = 0.0;

            if ($payment->order_id) {
                $order = $payment->order;
                $otherPaid = $order->payments()->where('payment_id', '!=', $payment->payment_id)->sum('amount');
                $maxForThis = max(0, $order->final_amount - $otherPaid);
                $newApplied = min($newTotal, $maxForThis);
                $newExcess = round($newTotal - $newApplied, 2);
            }

            // Handle file upload
            $filePath = $payment->document_path;
            $originalFilename = $payment->original_filename;
            if ($request->hasFile('document')) {
                if ($filePath) Storage::disk('public')->delete($filePath);
                $file = $request->file('document');
                $originalFilename = $file->getClientOriginalName();
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('payment-documents/' . date('Y/m'), $filename, 'public');
            }

            $payment->update([
                'payment_method' => $request->payment_method,
                'amount' => $newApplied,
                'received_amount' => $newTotal,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
                'document_path' => $filePath,
                'original_filename' => $originalFilename,
            ]);

            // If linked to an order, recalculate order paid_amount
            if ($payment->order_id) {
                $order = $payment->order;
                $totalPaid = $order->payments()->sum('amount');
                $oldOrderPaid = $order->paid_amount;
                $order->paid_amount = $totalPaid;
                if ($totalPaid <= 0) {
                    $order->payment_status = 'pending';
                } elseif ($totalPaid >= $order->final_amount - 0.01) {
                    $order->payment_status = 'paid';
                } else {
                    $order->payment_status = 'partial';
                }
                $order->save();

                // Update client balance for the change in amount actually applied to the order
                $client = $order->client;
                $oldUnpaidAmount = $order->final_amount - $oldOrderPaid;
                $client->updateBalanceFromOrder($order, 'order_updated', $oldUnpaidAmount);
                $client->updateCreditUsage();

                // Update client balance for the change in excess credited (if any)
                $excessDelta = round($newExcess - $oldExcess, 2);
                if (abs($excessDelta) > 0.005) {
                    $client->refresh();
                    $previousBalance = (float) $client->balance;
                    $newBalance = $previousBalance + $excessDelta;
                    $client->balance = $newBalance;
                    $client->save();

                    $client->balanceHistory()->create([
                        'previous_balance' => $previousBalance,
                        'new_balance'      => $newBalance,
                        'amount'           => $excessDelta,
                        'type'             => 'payment_added',
                        'reference_type'   => 'sales_order',
                        'reference_id'     => $order->order_id,
                        'description'      => 'Correction règlement #' . $payment->payment_id . ' — excédent: ' . number_format($newExcess, 2, ',', '.') . ' DH',
                        'created_by'       => auth()->id(),
                    ]);
                }
            }

            // Handle associated check/traite
            if ($oldMethod === 'check' && $request->payment_method !== 'check') {
                Check::where('amount', $oldAmount)->where('notes', 'like', '%' . ($payment->order->order_number ?? '') . '%')->delete();
            }
            if ($oldMethod === 'traite' && $request->payment_method !== 'traite') {
                Traite::where('payment_id', $payment->payment_id)->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Règlement mis à jour avec succès!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update payment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a payment and reverse all effects (order paid_amount, client balance, credit).
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $payment = SalesOrderPayment::findOrFail($id);

            // Determine client
            $client = $payment->client;
            if (!$client && $payment->order) {
                $client = $payment->order->client;
            }

            // Reverse advance on balance
            if ($payment->payment_method === 'advance' && $client && $payment->order) {
                $client->reverseAdvance($payment->amount, $payment->order, 'Suppression règlement #' . $payment->payment_id);
            }

            // If linked to an order, recalculate order paid_amount
            if ($payment->order_id) {
                $order = $payment->order;
                $oldPaid = $order->paid_amount;

                // Mark associated check/traite as bounced
                if ($payment->payment_method === 'check') {
                    $check = Check::where('notes', 'like', '%' . $order->order_number . '%')
                        ->where('amount', $payment->amount)->first();
                    if ($check) {
                        $check->status = 'bounced';
                        $check->save();
                    }
                } elseif ($payment->payment_method === 'traite') {
                    $traite = Traite::where('payment_id', $payment->payment_id)->first();
                    if ($traite) {
                        $traite->status = 'bounced';
                        $traite->save();
                    }
                }

                // Delete document
                if ($payment->document_path) {
                    Storage::disk('public')->delete($payment->document_path);
                }

                // Capture the excess (portion that went to the client balance, not this
                // order) before the row is deleted, so it can be reversed too.
                $excess = max(0, (float) ($payment->received_amount ?? $payment->amount) - (float) $payment->amount);

                $payment->delete();

                $totalPaid = $order->payments()->sum('amount');
                $order->paid_amount = $totalPaid;
                if ($totalPaid <= 0) {
                    $order->payment_status = 'pending';
                } elseif ($totalPaid >= $order->final_amount - 0.01) {
                    $order->payment_status = 'paid';
                } else {
                    $order->payment_status = 'partial';
                }
                $order->save();

                // Update client balance
                if ($client) {
                    $client->updateBalanceFromOrder($order, 'payment_deleted', $oldPaid - $payment->amount > 0 ? $payment->amount : null);
                    $client->updateCreditUsage();

                    if ($excess > 0.005) {
                        $client->refresh();
                        $previousBalance = (float) $client->balance;
                        $newBalance = $previousBalance - $excess;
                        $client->balance = $newBalance;
                        $client->save();

                        $client->balanceHistory()->create([
                            'previous_balance' => $previousBalance,
                            'new_balance'      => $newBalance,
                            'amount'           => -$excess,
                            'type'             => 'payment_deleted',
                            'reference_type'   => 'sales_order',
                            'reference_id'     => $order->order_id,
                            'description'      => 'Suppression règlement #' . $payment->payment_id . ' — annulation excédent: ' . number_format($excess, 2, ',', '.') . ' DH',
                            'created_by'       => auth()->id(),
                        ]);
                    }
                }
            } else {
                // Direct payment (no order) — deduct from client balance
                if ($payment->document_path) {
                    Storage::disk('public')->delete($payment->document_path);
                }
                $payment->delete();

                if ($client && $payment->payment_method !== 'advance' && $payment->payment_method !== 'avoir') {
                    $previousBalance = $client->balance;
                    $client->balance = $previousBalance - $payment->amount;
                    $client->save();
                    $client->balanceHistory()->create([
                        'previous_balance' => $previousBalance,
                        'new_balance' => $client->balance,
                        'amount' => -$payment->amount,
                        'type' => 'payment_deleted',
                        'reference_type' => 'direct_payment',
                        'reference_id' => $payment->payment_id,
                        'description' => 'Suppression règlement direct #' . $payment->payment_id,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Règlement supprimé avec succès!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delete payment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a payment to client from the règlement details page
     * (same logic as ClientController@distributePayment).
     */
    public function addPaymentToClient(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,client_id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,transfer,traite',
            'target_order_id' => 'nullable|exists:sales_orders,order_id',
            'selected_orders' => 'nullable|array',
            'selected_orders.*.order_id' => 'required_with:selected_orders|exists:sales_orders,order_id',
            'selected_orders.*.amount' => 'required_with:selected_orders|numeric|min:0.01',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Cheque/traite details — same rules as ClientController::distributePayment
        if ($request->payment_method === 'check') {
            $request->validate([
                'check_number' => 'required|string|max:100',
                'check_amount' => 'required|numeric|min:0.01',
                'bank_name' => 'required|string|max:255',
                'account_holder' => 'required|string|max:255',
                'issue_date' => 'required|date',
            ]);

            if ($request->check_amount != $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant du chèque doit être égal au montant du paiement'
                ], 422);
            }
        }

        if ($request->payment_method === 'traite') {
            $request->validate([
                'traite_number' => 'required|string|max:100',
                'traite_amount' => 'required|numeric|min:0.01',
                'traite_bank_name' => 'required|string|max:255',
                'drawee' => 'required|string|max:255',
                'traite_issue_date' => 'required|date',
                'due_date' => 'required|date',
            ]);

            if ($request->traite_amount != $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant de la traite doit être égal au montant du paiement'
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $client = Client::findOrFail($request->client_id);
            $amount = (float) $request->amount;

            // File upload
            $filePath = null;
            $originalFilename = null;
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $originalFilename = $file->getClientOriginalName();
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('payment-documents/' . date('Y/m'), $filename, 'public');
            }

            // Register the cheque / traite — same as ClientController::distributePayment
            $traite = null;

            if ($request->payment_method === 'check') {
                $check = Check::create([
                    'check_number' => $request->check_number,
                    'check_type' => 'client',
                    'amount' => $request->check_amount,
                    'remaining_amount' => $request->check_amount,
                    'bank_name' => $request->bank_name,
                    'account_holder' => $request->account_holder,
                    'issue_date' => $request->issue_date,
                    'deposit_date' => $request->deposit_date,
                    'status' => 'pending',
                    'notes' => $request->notes,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);

                if ($request->hasFile('check_images')) {
                    foreach ($request->file('check_images') as $image) {
                        $image->store('checks/' . $check->check_id, 'public');
                    }
                }
            } elseif ($request->payment_method === 'traite') {
                $traite = Traite::create([
                    'traite_number' => $request->traite_number,
                    'amount' => $request->traite_amount,
                    'client_id' => $client->client_id,
                    'issue_date' => $request->traite_issue_date,
                    'due_date' => $request->due_date,
                    'bank_name' => $request->traite_bank_name,
                    'drawee' => $request->drawee,
                    'drawee_address' => $request->drawee_address,
                    'status' => 'paid',
                    'payment_date' => now(),
                    'notes' => $request->notes,
                    'created_by' => auth()->id(),
                ]);

                if ($request->hasFile('traite_document')) {
                    $traiteDocument = $request->file('traite_document');
                    $traite->update([
                        'document_path' => $traiteDocument->store('traites/' . $traite->traite_id, 'public'),
                        'original_filename' => $traiteDocument->getClientOriginalName(),
                    ]);
                }
            }

            // The first payment created below is linked to the traite so the
            // règlement page (purchases.show) can display it.
            $firstPayment = null;

            // If a specific order is targeted, pay that order
            if ($request->filled('target_order_id')) {
                $order = SalesOrder::where('client_id', $client->client_id)->findOrFail($request->target_order_id);
                $remaining = max(0, $order->final_amount - $order->paid_amount);
                $applyAmount = min($amount, $remaining);

                if ($applyAmount <= 0) {
                    return response()->json(['success' => false, 'message' => 'Cette commande est déjà entièrement payée.'], 400);
                }

                $payment = $order->payments()->create([
                    'client_id' => $client->client_id,
                    'payment_method' => $request->payment_method,
                    'amount' => $applyAmount,
                    'payment_date' => $request->payment_date,
                    'document_path' => $filePath,
                    'original_filename' => $originalFilename,
                    'notes' => $request->notes ?? null,
                ]);
                $firstPayment = $firstPayment ?? $payment;

                $oldPaid = $order->paid_amount;
                $order->paid_amount += $applyAmount;
                $order->payment_status = $order->paid_amount >= $order->final_amount - 0.01 ? 'paid' : 'partial';
                $order->save();

                $client->updateBalanceFromOrder($order, 'payment_added', $oldPaid);
                $unpaidBefore = $order->final_amount - $oldPaid;
                if ($unpaidBefore > 0 && $order->paid_amount >= $order->final_amount - 0.01) {
                    $client->releaseCredit($unpaidBefore, $order, 'Paiement sur vente #' . $order->order_number);
                }

                $excess = round($amount - $applyAmount, 2);
                if ($excess > 0.005) {
                    $excessPayment = SalesOrderPayment::create([
                        'client_id' => $client->client_id,
                        'payment_method' => $request->payment_method,
                        'amount' => $excess,
                        'payment_date' => $request->payment_date,
                        'notes' => 'Excédent de commande #' . $order->order_number,
                    ]);
                    $firstPayment = $firstPayment ?? $excessPayment;
                    $client->balance += $excess;
                    $client->save();
                    $client->balanceHistory()->create([
                        'previous_balance' => $client->balance - $excess,
                        'new_balance' => $client->balance,
                        'amount' => $excess,
                        'type' => 'payment_excess',
                        'reference_type' => 'sales_order',
                        'reference_id' => $order->order_id,
                        'description' => 'Excédent commande #' . $order->order_number,
                        'created_by' => auth()->id(),
                    ]);
                }

            // If selected orders are provided, pay only those with their specified amounts
            } elseif ($request->filled('selected_orders') && is_array($request->selected_orders)) {
                $selectedOrders = collect($request->selected_orders);
                $totalDistributed = 0;

                foreach ($selectedOrders as $so) {
                    $soAmount = round((float) ($so['amount'] ?? 0), 2);
                    if ($soAmount <= 0) continue;

                    $order = SalesOrder::where('client_id', $client->client_id)->find($so['order_id'] ?? 0);
                    if (!$order) continue;

                    $remaining = max(0, $order->final_amount - $order->paid_amount);
                    $applyAmount = min($soAmount, $remaining);
                    if ($applyAmount <= 0) continue;

                    $payment = $order->payments()->create([
                        'client_id' => $client->client_id,
                        'payment_method' => $request->payment_method,
                        'amount' => $applyAmount,
                        'payment_date' => $request->payment_date,
                        'document_path' => $filePath,
                        'original_filename' => $originalFilename,
                        'notes' => $request->notes ?? null,
                    ]);
                    $firstPayment = $firstPayment ?? $payment;

                    $oldPaid = $order->paid_amount;
                    $order->paid_amount += $applyAmount;
                    $order->payment_status = $order->paid_amount >= $order->final_amount - 0.01 ? 'paid' : 'partial';
                    $order->save();

                    $client->refresh();
                    $client->updateBalanceFromOrder($order, 'payment_added', $oldPaid);
                    $unpaidBefore = $order->final_amount - $oldPaid;
                    if ($unpaidBefore > 0 && $order->paid_amount >= $order->final_amount - 0.01) {
                        $client->releaseCredit($unpaidBefore, $order, 'Paiement sur vente #' . $order->order_number);
                    }

                    $totalDistributed += $applyAmount;
                }

                // Any excess goes to client balance
                $excess = round($amount - $totalDistributed, 2);
                if ($excess > 0.005) {
                    $excessPayment = SalesOrderPayment::create([
                        'client_id' => $client->client_id,
                        'payment_method' => $request->payment_method,
                        'amount' => $excess,
                        'payment_date' => $request->payment_date,
                        'notes' => 'Excédent après distribution sélectionnée',
                    ]);
                    $firstPayment = $firstPayment ?? $excessPayment;
                    $prevBalance = $client->balance;
                    $client->balance += $excess;
                    $client->save();
                    $client->balanceHistory()->create([
                        'previous_balance' => $prevBalance,
                        'new_balance' => $client->balance,
                        'amount' => $excess,
                        'type' => 'payment_excess',
                        'reference_type' => 'direct_payment',
                        'reference_id' => null,
                        'description' => 'Excédent après distribution sélectionnée',
                        'created_by' => auth()->id(),
                    ]);
                }

            // Auto-distribute across all unpaid orders (FIFO), like distributePayment
            // in ClientController, when no specific order/selection was provided
            } else {
                $unpaidOrders = SalesOrder::where('client_id', $client->client_id)
                    ->whereIn('payment_status', ['pending', 'partial'])
                    ->whereRaw('final_amount > paid_amount')
                    ->orderBy('order_date', 'asc')
                    ->get();

                $remainingAmount = $amount;

                if ($unpaidOrders->isEmpty()) {
                    // No unpaid orders — direct payment to client balance
                    $payment = SalesOrderPayment::create([
                        'client_id' => $client->client_id,
                        'payment_method' => $request->payment_method,
                        'amount' => $amount,
                        'payment_date' => $request->payment_date,
                        'document_path' => $filePath,
                        'original_filename' => $originalFilename,
                        'notes' => $request->notes ?? 'Règlement direct client',
                    ]);
                    $firstPayment = $firstPayment ?? $payment;
                    $prevBalance = $client->balance;
                    $client->balance += $amount;
                    $client->save();
                    $client->balanceHistory()->create([
                        'previous_balance' => $prevBalance,
                        'new_balance' => $client->balance,
                        'amount' => $amount,
                        'type' => 'payment_excess',
                        'reference_type' => 'direct_payment',
                        'reference_id' => null,
                        'description' => 'Règlement direct (aucune commande impayée)',
                        'created_by' => auth()->id(),
                    ]);
                } else {
                    foreach ($unpaidOrders as $order) {
                        if ($remainingAmount <= 0.005) break;
                        $orderRemaining = max(0, $order->final_amount - $order->paid_amount);
                        $apply = min($remainingAmount, $orderRemaining);
                        if ($apply <= 0) continue;

                        $payment = $order->payments()->create([
                            'client_id' => $client->client_id,
                            'payment_method' => $request->payment_method,
                            'amount' => $apply,
                            'payment_date' => $request->payment_date,
                            'document_path' => $filePath,
                            'original_filename' => $originalFilename,
                            'notes' => $request->notes ?? null,
                        ]);
                        $firstPayment = $firstPayment ?? $payment;

                        $oldPaid = $order->paid_amount;
                        $order->paid_amount += $apply;
                        $order->payment_status = $order->paid_amount >= $order->final_amount - 0.01 ? 'paid' : 'partial';
                        $order->save();

                        $client->refresh();
                        $client->updateBalanceFromOrder($order, 'payment_added', $oldPaid);
                        $unpaidBefore = $order->final_amount - $oldPaid;
                        if ($unpaidBefore > 0 && $order->paid_amount >= $order->final_amount - 0.01) {
                            $client->releaseCredit($unpaidBefore, $order, 'Paiement sur vente #' . $order->order_number);
                        }

                        $remainingAmount -= $apply;
                    }

                    // Any leftover goes to client balance
                    if ($remainingAmount > 0.005) {
                        $excessPayment = SalesOrderPayment::create([
                            'client_id' => $client->client_id,
                            'payment_method' => $request->payment_method,
                            'amount' => $remainingAmount,
                            'payment_date' => $request->payment_date,
                            'notes' => 'Excédent après distribution',
                        ]);
                        $firstPayment = $firstPayment ?? $excessPayment;
                        $prevBalance = $client->balance;
                        $client->balance += $remainingAmount;
                        $client->save();
                        $client->balanceHistory()->create([
                            'previous_balance' => $prevBalance,
                            'new_balance' => $client->balance,
                            'amount' => $remainingAmount,
                            'type' => 'payment_excess',
                            'reference_type' => 'direct_payment',
                            'reference_id' => null,
                            'description' => 'Excédent après distribution sur commandes',
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }

            // Link the traite to the payment so the règlement page can find it
            if ($traite && $firstPayment) {
                $traite->update([
                    'payment_id' => $firstPayment->payment_id,
                    'order_id' => $firstPayment->order_id,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement ajouté avec succès!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Add payment to client error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return individual payments for a given client — used by the detail modal.
     */
    public function getClientPayments(Request $request, $clientId)
    {
        try {
            $dateFrom = $request->filled('date_from') ? $request->date_from : null;
            $dateTo   = $request->filled('date_to')   ? $request->date_to   : null;
            $paymentMethod = $request->filled('payment_method') ? $request->payment_method : null;

            $query = SalesOrderPayment::with('order')
                ->whereNotIn('payment_method', ['advance', 'avoir'])
                ->where(function ($q) use ($clientId) {
                    $q->where('client_id', $clientId)
                      ->orWhereHas('order', fn($o) => $o->where('client_id', $clientId));
                });

            if ($dateFrom) $query->whereDate('payment_date', '>=', $dateFrom);
            if ($dateTo)   $query->whereDate('payment_date', '<=', $dateTo);
            if ($paymentMethod) $query->where('payment_method', $paymentMethod);

            $payments = $query->orderBy('payment_date', 'desc')->get()->map(function ($p) {
                return [
                    'payment_id' => $p->payment_id,
                    'order_id' => $p->order_id,
                    'order_number' => $p->order->order_number ?? null,
                    'payment_method' => $p->payment_method,
                    // 'amount' stays the portion applied to the order (used to prefill/cap the
                    // edit form); 'display_amount' is the full sum the client handed over.
                    'amount' => $p->amount,
                    'display_amount' => $p->display_amount,
                    'payment_date' => $p->payment_date ? $p->payment_date->format('d/m/Y') : null,
                    'payment_date_formatted' => $p->payment_date ? $p->payment_date->format('Y-m-d') : null,
                    'notes' => $p->notes,
                ];
            });

            return response()->json([
                'success' => true,
                'payments' => $payments,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return all orders (with payment detail) AND direct payments for a given client — used by the modal.
     */
    public function getClientOrders(Request $request, $clientId)
    {
        try {
            $dateFrom = $request->filled('date_from') ? $request->date_from : null;
            $dateTo   = $request->filled('date_to')   ? $request->date_to   : null;

            $ordersQuery = SalesOrder::with(['payments' => function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('payment_date', '>=', $dateFrom);
                    if ($dateTo)   $q->whereDate('payment_date', '<=', $dateTo);
                }])
                ->where('client_id', $clientId)
                ->whereHas('payments', function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('payment_date', '>=', $dateFrom);
                    if ($dateTo)   $q->whereDate('payment_date', '<=', $dateTo);
                });

            $orders = $ordersQuery->orderBy('order_date', 'desc')->get();

            $directPaymentsQuery = SalesOrderPayment::with('client')
                ->where('client_id', $clientId)
                ->whereNull('order_id')
                ->whereNull('credit_note_id');

            if ($dateFrom) $directPaymentsQuery->whereDate('payment_date', '>=', $dateFrom);
            if ($dateTo)   $directPaymentsQuery->whereDate('payment_date', '<=', $dateTo);

            $directPayments = $directPaymentsQuery->orderBy('payment_date', 'desc')->get();

            $methodLabels = [
                'cash' => 'Espèces', 'check' => 'Chèque', 'transfer' => 'Virement',
                'traite' => 'Traite', 'advance' => 'Avance', 'avoir' => 'Avoir',
            ];
            $methodColors = [
                'cash' => 'success', 'check' => 'info', 'transfer' => 'primary',
                'traite' => 'warning', 'advance' => 'secondary', 'avoir' => 'dark',
            ];

            $data = $orders->map(function ($order) use ($methodLabels, $methodColors) {
                $payments = $order->payments->map(function ($p) use ($methodLabels, $methodColors) {
                    $ref = '#REG-' . str_pad($p->payment_id, 6, '0', STR_PAD_LEFT);
                    $label = $methodLabels[$p->payment_method] ?? $p->payment_method;
                    $color = $methodColors[$p->payment_method] ?? 'secondary';
                    $notes = $p->notes ? ' — ' . $p->notes : '';
                    return [
                        'payment_id' => $p->payment_id, 'reference' => $ref,
                        'method' => $p->payment_method, 'method_label' => $label,
                        'method_color' => $color,
                        // 'amount'/'amount_raw' stay the portion applied to this order;
                        // 'display_amount'/'display_amount_raw' are the full sum received.
                        'amount' => number_format($p->amount, 2, ',', '.') . ' DH',
                        'amount_raw' => $p->amount,
                        'display_amount' => number_format($p->display_amount, 2, ',', '.') . ' DH',
                        'display_amount_raw' => $p->display_amount,
                        'date' => $p->payment_date ? $p->payment_date->format('d/m/Y') : '—',
                        'notes' => $notes,
                        'full_label' => $label . ($notes ? ' (' . trim($notes, ' —') . ')' : ''),
                    ];
                });

                return [
                    'order_id' => $order->order_id, 'order_number' => $order->order_number,
                    'order_date' => $order->order_date->format('d/m/Y'),
                    'total_amount' => number_format($order->final_amount, 2, ',', '.') . ' DH',
                    'paid_amount' => number_format($order->paid_amount, 2, ',', '.') . ' DH',
                    'remaining' => number_format(max(0, $order->final_amount - $order->paid_amount), 2, ',', '.') . ' DH',
                    'remaining_raw' => max(0, $order->final_amount - $order->paid_amount),
                    'payment_status' => $order->payment_status, 'payments' => $payments,
                    'type' => 'order',
                ];
            });

            if ($directPayments->isNotEmpty()) {
                $directPmts = $directPayments->map(function ($p) use ($methodLabels, $methodColors) {
                    $ref = '#REG-' . str_pad($p->payment_id, 6, '0', STR_PAD_LEFT);
                    $label = $methodLabels[$p->payment_method] ?? $p->payment_method;
                    $color = $methodColors[$p->payment_method] ?? 'secondary';
                    $notes = $p->notes ? ' — ' . $p->notes : '';
                    return [
                        'payment_id' => $p->payment_id, 'reference' => $ref,
                        'method' => $p->payment_method, 'method_label' => $label,
                        'method_color' => $color,
                        'amount' => number_format($p->amount, 2, ',', '.') . ' DH',
                        'amount_raw' => $p->amount,
                        'display_amount' => number_format($p->display_amount, 2, ',', '.') . ' DH',
                        'display_amount_raw' => $p->display_amount,
                        'date' => $p->payment_date ? $p->payment_date->format('d/m/Y') : '—',
                        'notes' => $notes,
                        'full_label' => $label . ($notes ? ' (' . trim($notes, ' —') . ')' : ''),
                    ];
                });
                $directTotal = $directPayments->sum('amount');
                $data->push([
                    'order_id' => null, 'order_number' => 'Paiements Directs',
                    'order_date' => '—',
                    'total_amount' => number_format($directTotal, 2, ',', '.') . ' DH',
                    'paid_amount' => number_format($directTotal, 2, ',', '.') . ' DH',
                    'remaining' => '0,00 DH', 'remaining_raw' => 0,
                    'payment_status' => 'paid', 'payments' => $directPmts,
                    'type' => 'direct',
                ]);
            }

            return response()->json(['success' => true, 'data' => $data]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified règlement.
     */
    public function show($id)
    {
        $reglement = SalesOrderPayment::with(['order', 'order.client', 'order.items', 'client', 'creditNote', 'creditNote.client'])
            ->findOrFail($id);

        $client = $reglement->client
            ?? ($reglement->order->client ?? null)
            ?? ($reglement->creditNote->client ?? null);

        $check  = null;
        $traite = null;

        if ($reglement->payment_method === 'check') {
            $check = Check::where('notes', 'like', '%' . ($reglement->order ? $reglement->order->order_number : '') . '%')
                ->where('amount', $reglement->amount)
                ->first();
        } elseif ($reglement->payment_method === 'traite') {
            $traite = Traite::where('payment_id', $reglement->payment_id)->first();
        }

        $clients = Client::orderBy('name')->get(['client_id', 'name', 'entreprise_name', 'person_type']);

        return view('pages.purchases.show', compact('reglement', 'client', 'check', 'traite', 'clients'));
    }

    /**
     * Download règlement document.
     */
    public function downloadDocument($id)
    {
        $reglement = SalesOrderPayment::findOrFail($id);

        if (!$reglement->document_path) {
            return redirect()->back()->with('error', 'Aucun document attaché à ce règlement.');
        }
        if (!Storage::disk('public')->exists($reglement->document_path)) {
            return redirect()->back()->with('error', 'Le fichier n\'existe plus sur le serveur.');
        }

        $filename = $reglement->original_filename ?? 'reglement_' . $reglement->payment_id . '.pdf';
        return Storage::disk('public')->download($reglement->document_path, $filename);
    }

    /**
     * Get règlement statistics.
     */
    public function getStatistics(Request $request)
    {
        try {
            $query = SalesOrderPayment::whereNotIn('payment_method', ['advance', 'avoir']);

            if ($request->filled('date_from')) {
                $query->whereDate('payment_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('payment_date', '<=', $request->date_to);
            }

            $totalAmount    = $query->sum('amount');
            $totalCount     = $query->count();
            $orderPayments  = (clone $query)->whereNotNull('order_id')->count();
            $directPayments = (clone $query)->whereNull('order_id')->whereNull('credit_note_id')->count();

            $byMethod = (clone $query)
                ->select('payment_method', DB::raw('SUM(amount) as total'))
                ->groupBy('payment_method')
                ->get()
                ->map(fn($item) => [
                    'method'       => $item->payment_method,
                    'method_label' => (new SalesOrderPayment)->getMethodLabelAttribute(),
                    'total'        => $item->total,
                ]);

            return response()->json([
                'success' => true,
                'data'    => [
                    'total_amount'    => $totalAmount,
                    'total_count'     => $totalCount,
                    'order_payments'  => $orderPayments,
                    'direct_payments' => $directPayments,
                    'by_method'       => $byMethod,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }
}
