<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Check;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_purchases')->only(['index', 'show', 'getStatistics', 'getInvoiceInfo', 'getAvailableChecks', 'print']);
        $this->middleware('can:create_purchases')->only(['create', 'store']);
        $this->middleware('can:edit_purchases')->only(['edit', 'update']);
        $this->middleware('can:delete_purchases')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $payments = Payment::with(['invoice.order.client', 'client', 'recorder', 'check'])->select('payments.*');

            return DataTables::of($payments)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $dropdown = '<div class="dropdown dropstart">
                        <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3" href="'.route('sales.payments.show', $row->payment_id).'">
                                    <i class="fs-4 ti ti-eye"></i>Voir Détails
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3" href="'.route('sales.payments.edit', $row->payment_id).'">
                                    <i class="fs-4 ti ti-edit"></i>Modifier
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3" href="'.route('sales.payments.print', $row->payment_id).'" target="_blank">
                                    <i class="fs-4 ti ti-printer"></i>Reçu
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 delete" href="javascript:void(0)"
                                   data-id="'.$row->payment_id.'"
                                   data-number="'.$row->payment_number.'">
                                    <i class="fs-4 ti ti-trash text-danger"></i><span class="text-danger">Supprimer</span>
                                </a>
                            </li>
                        </ul>
                    </div>';
                    return $dropdown;
                })
                ->addColumn('client_name', function($row){
                    return $row->client->display_name;
                })
                ->addColumn('invoice_number', function($row){
                    return $row->invoice->invoice_number;
                })
                ->addColumn('payment_date_formatted', function($row){
                    return $row->payment_date->format('d/m/Y');
                })
                ->addColumn('check_info', function($row){
                    if ($row->payment_method == 'check' && $row->check) {
                        return '<span class="badge badge-info">Chèque n°: ' . $row->check->check_number . '</span>';
                    }
                    return '-';
                })
                ->addColumn('status_badge', function($row){
                    $badges = [
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger'
                    ];
                    $color = $badges[$row->status] ?? 'secondary';
                    $labels = [
                        'pending' => 'En attente',
                        'completed' => 'Complété',
                        'cancelled' => 'Annulé'
                    ];
                    $label = $labels[$row->status] ?? $row->status;
                    return '<span class="badge badge-'.$color.'">'.$label.'</span>';
                })
                ->addColumn('payment_method_badge', function($row){
                    $badges = [
                        'cash' => 'success',
                        'check' => 'primary',
                        'bank_transfer' => 'info',
                        'credit_card' => 'warning'
                    ];
                    $color = $badges[$row->payment_method] ?? 'secondary';
                    $labels = [
                        'cash' => 'Espèces',
                        'check' => 'Chèque',
                        'bank_transfer' => 'Virement',
                        'credit_card' => 'Carte'
                    ];
                    $label = $labels[$row->payment_method] ?? $row->payment_method;
                    return '<span class="badge badge-'.$color.'">'.$label.'</span>';
                })
                ->editColumn('amount', function($row){
                    return number_format($row->amount, 2, ',', '.') . ' DH';
                })
                ->rawColumns(['action', 'status_badge', 'payment_method_badge', 'check_info'])
                ->make(true);
        }

        return view('pages.sales.payments.index');
    }

    public function create($invoiceId = null)
    {
        $clients = Client::where('is_active', true)->get();
        $invoices = Invoice::whereColumn('amount_paid', '<', 'total_amount')
            ->with('order.client')
            ->get();

        $nextPaymentNumber = 'PAY-' . date('Ymd') . '-' . str_pad(Payment::count() + 1, 4, '0', STR_PAD_LEFT);

        $invoice = null;
        if ($invoiceId) {
            $invoice = Invoice::with('order.client')->find($invoiceId);
        }

        // Get available checks
        $availableChecks = Check::where('is_active', true)
            ->where('check_type', 'client')
            ->where('status', 'pending')
            ->get();

        return view('pages.sales.payments.create', compact('clients', 'invoices', 'nextPaymentNumber', 'invoice', 'availableChecks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_number' => 'required|unique:payments|max:50',
            'invoice_id' => 'required|exists:invoices,invoice_id',
            'client_id' => 'required|exists:clients,client_id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,check,bank_transfer,credit_card',
            'check_id' => 'nullable|exists:checks,check_id',
            'check_number' => 'nullable|required_if:payment_method,check|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'account_holder' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'check_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'transaction_reference' => 'nullable|string|max:100',
            'status' => 'required|in:pending,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::find($request->invoice_id);

            $remainingBalance = $invoice->total_amount - $invoice->amount_paid;
            if ($request->amount > $remainingBalance && $request->status == 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant du paiement ne peut pas dépasser le solde restant de ' . number_format($remainingBalance, 2, ',', '.') . ' DH'
                ], 400);
            }

            $checkImagePath = null;
            if ($request->hasFile('check_image') && $request->payment_method == 'check') {
                $checkImage = $request->file('check_image');
                $filename = time() . '_' . $request->check_number . '.' . $checkImage->getClientOriginalExtension();
                $path = $checkImage->storeAs('checks', $filename, 'public');
                $checkImagePath = $path;
            }

            // Handle check payment
            $checkId = null;
            if ($request->payment_method == 'check') {
                if ($request->check_id) {
                    // Use existing check
                    $checkId = $request->check_id;

                    // Update check status if payment is completed
                    if ($request->status == 'completed') {
                        $check = Check::find($checkId);
                        $check->update([
                            'amount' => $request->amount,
                            'bank_name' => $request->bank_name,
                            'account_holder' => $request->account_holder ?? $invoice->order->client->display_name,
                            'issue_date' => $request->issue_date ?? $request->payment_date,
                        ]);
                    }
                } else {
                    // Create new check record
                    $check = Check::create([
                        'check_number' => $request->check_number,
                        'check_type' => 'client',
                        'amount' => $request->amount,
                        'bank_name' => $request->bank_name,
                        'account_holder' => $request->account_holder ?? $invoice->order->client->display_name,
                        'issue_date' => $request->issue_date ?? $request->payment_date,
                        'status' => $request->status == 'completed' ? 'pending' : 'cancelled',
                        'notes' => 'Paiement pour facture: ' . $invoice->invoice_number,
                        'is_active' => true,
                        'created_by' => Auth::id(),
                    ]);

                    $checkId = $check->check_id;

                    // Store check image if exists
                    if ($checkImagePath) {
                        $check->update(['check_image' => $checkImagePath]);
                    }
                }
            }

            $paymentData = [
                'payment_number' => $request->payment_number,
                'invoice_id' => $request->invoice_id,
                'client_id' => $request->client_id,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'check_id' => $checkId,
                'check_number' => $request->check_number,
                'bank_name' => $request->bank_name,
                'transaction_reference' => $request->transaction_reference,
                'status' => $request->status,
                'notes' => $request->notes,
                'recorded_by' => Auth::id(),
            ];

            // Add check image path if exists
            if ($checkImagePath) {
                $paymentData['check_image'] = $checkImagePath;
            }

            $payment = Payment::create($paymentData);

            // Update invoice if payment is completed
            if ($request->status == 'completed') {
                $invoice->amount_paid += $request->amount;

                // Update order payment status
                if ($invoice->amount_paid >= $invoice->total_amount) {
                    if ($invoice->order) {
                        $invoice->order->update([
                            'payment_status' => 'paid'
                        ]);
                    }
                } else {
                    if ($invoice->order) {
                        $invoice->order->update([
                            'payment_status' => 'partial'
                        ]);
                    }
                }

                $invoice->save();

                // Update client balance: payment received increases balance (reduces debt or builds advance)
                $client = Client::find($request->client_id);
                if ($client) {
                    $previousBalance = $client->balance;
                    $newBalance = $previousBalance + $request->amount;
                    $client->balance = $newBalance;
                    $client->save();

                    $client->balanceHistory()->create([
                        'previous_balance' => $previousBalance,
                        'new_balance' => $newBalance,
                        'amount' => $request->amount,
                        'type' => 'payment_added',
                        'reference_type' => 'payment',
                        'reference_id' => $payment->payment_id,
                        'description' => 'Paiement de ' . number_format($request->amount, 2, ',', '.') . ' DH pour facture #' . $invoice->invoice_number,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement enregistré avec succès!',
                'payment_id' => $payment->payment_id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $payment = Payment::with(['invoice.order.client', 'client', 'recorder', 'check'])->findOrFail($id);
        return view('pages.sales.payments.show', compact('payment'));
    }

    public function edit($id)
    {
        $payment = Payment::with(['invoice.order.client', 'client', 'check'])->findOrFail($id);
        $clients = Client::where('is_active', true)->get();
        $invoices = Invoice::with('order.client')->get();

        // Get available checks
        $availableChecks = Check::where('is_active', true)
            ->where('check_type', 'client')
            ->whereIn('status', ['pending', 'cleared'])
            ->get();

        return view('pages.sales.payments.edit', compact('payment', 'clients', 'invoices', 'availableChecks'));
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $originalAmount = $payment->amount;
        $originalStatus = $payment->status;

        $request->validate([
            'payment_number' => 'required|unique:payments,payment_number,'.$id.',payment_id|max:50',
            'invoice_id' => 'required|exists:invoices,invoice_id',
            'client_id' => 'required|exists:clients,client_id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,check,bank_transfer,credit_card',
            'check_id' => 'nullable|exists:checks,check_id',
            'check_number' => 'nullable|required_if:payment_method,check|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'account_holder' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'check_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'transaction_reference' => 'nullable|string|max:100',
            'status' => 'required|in:pending,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::find($request->invoice_id);

            // If payment is completed, validate amount
            if ($request->status == 'completed') {
                // First, revert the original payment if it was completed
                if ($originalStatus == 'completed') {
                    $invoice->amount_paid -= $originalAmount;
                }

                // Then add new amount
                $newTotalPaid = $invoice->amount_paid + $request->amount;
                if ($newTotalPaid > $invoice->total_amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le montant total payé ne peut pas dépasser le montant total de la facture.'
                    ], 400);
                }

                $invoice->amount_paid = $newTotalPaid;
            } elseif ($originalStatus == 'completed' && $request->status != 'completed') {
                // Revert the payment if status changed from completed
                $invoice->amount_paid -= $originalAmount;
            }

            // Handle check payment
            $checkId = null;
            if ($request->payment_method == 'check') {
                if ($request->check_id) {
                    // Use existing check
                    $checkId = $request->check_id;

                    // Update check status if payment is completed
                    if ($request->status == 'completed') {
                        $check = Check::find($checkId);
                        $check->update([
                            'amount' => $request->amount,
                            'bank_name' => $request->bank_name,
                            'account_holder' => $request->account_holder ?? $invoice->order->client->display_name,
                            'issue_date' => $request->issue_date ?? $request->payment_date,
                        ]);
                    }
                } else {
                    // Create new check record
                    $check = Check::create([
                        'check_number' => $request->check_number,
                        'check_type' => 'client',
                        'amount' => $request->amount,
                        'bank_name' => $request->bank_name,
                        'account_holder' => $request->account_holder ?? $invoice->order->client->display_name,
                        'issue_date' => $request->issue_date ?? $request->payment_date,
                        'status' => $request->status == 'completed' ? 'pending' : 'cancelled',
                        'notes' => 'Paiement pour facture: ' . $invoice->invoice_number,
                        'is_active' => true,
                        'created_by' => Auth::id(),
                    ]);

                    $checkId = $check->check_id;
                }
            }

            // Handle check image
            $checkImagePath = $payment->check_image;
            if ($request->hasFile('check_image') && $request->payment_method == 'check') {
                $checkImage = $request->file('check_image');
                $filename = time() . '_' . $request->check_number . '.' . $checkImage->getClientOriginalExtension();
                $path = $checkImage->storeAs('checks', $filename, 'public');
                $checkImagePath = $path;

                // Update check record with image if exists
                if ($checkId) {
                    Check::find($checkId)->update(['check_image' => $checkImagePath]);
                }
            }

            // Update payment
            $payment->update([
                'payment_number' => $request->payment_number,
                'invoice_id' => $request->invoice_id,
                'client_id' => $request->client_id,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'check_id' => $checkId,
                'check_number' => $request->check_number,
                'bank_name' => $request->bank_name,
                'transaction_reference' => $request->transaction_reference,
                'check_image' => $checkImagePath,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            // Update order payment status
            if ($request->status == 'completed') {
                if ($invoice->amount_paid >= $invoice->total_amount) {
                    if ($invoice->order) {
                        $invoice->order->update(['payment_status' => 'paid']);
                    }
                } else {
                    if ($invoice->order) {
                        $invoice->order->update(['payment_status' => 'partial']);
                    }
                }
            } else {
                // Recalculate order payment status based on other payments
                $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
                if ($totalPaid >= $invoice->total_amount) {
                    if ($invoice->order) {
                        $invoice->order->update(['payment_status' => 'paid']);
                    }
                } elseif ($totalPaid > 0) {
                    if ($invoice->order) {
                        $invoice->order->update(['payment_status' => 'partial']);
                    }
                } else {
                    if ($invoice->order) {
                        $invoice->order->update(['payment_status' => 'pending']);
                    }
                }
            }

            $invoice->save();

            // Update client balance based on status/amount change
            $client = Client::find($request->client_id);
            if ($client) {
                $balanceAdjustment = 0;
                $description = '';

                if ($request->status == 'completed' && $originalStatus != 'completed') {
                    // Payment became completed: add full new amount
                    $balanceAdjustment = $request->amount;
                    $description = 'Paiement complété de ' . number_format($request->amount, 2, ',', '.') . ' DH pour facture #' . $invoice->invoice_number;
                } elseif ($request->status != 'completed' && $originalStatus == 'completed') {
                    // Payment was uncompleted: reverse original amount
                    $balanceAdjustment = -$originalAmount;
                    $description = 'Paiement annulé (' . number_format($originalAmount, 2, ',', '.') . ' DH) pour facture #' . $invoice->invoice_number;
                } elseif ($request->status == 'completed' && $originalStatus == 'completed' && $request->amount != $originalAmount) {
                    // Amount changed while staying completed: adjust the difference
                    $balanceAdjustment = $request->amount - $originalAmount;
                    $description = 'Paiement modifié pour facture #' . $invoice->invoice_number .
                        ': ' . number_format($originalAmount, 2, ',', '.') . ' → ' . number_format($request->amount, 2, ',', '.') . ' DH';
                }

                if ($balanceAdjustment != 0) {
                    $previousBalance = $client->balance;
                    $newBalance = $previousBalance + $balanceAdjustment;
                    $client->balance = $newBalance;
                    $client->save();

                    $client->balanceHistory()->create([
                        'previous_balance' => $previousBalance,
                        'new_balance' => $newBalance,
                        'amount' => $balanceAdjustment,
                        'type' => 'payment_updated',
                        'reference_type' => 'payment',
                        'reference_id' => $payment->payment_id,
                        'description' => $description,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement mis à jour avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::findOrFail($id);

            // Revert payment from invoice if it was completed
            if ($payment->status == 'completed') {
                $invoice = $payment->invoice;
                $invoice->amount_paid -= $payment->amount;

                // Recalculate order payment status
                $totalPaid = $invoice->payments()
                    ->where('status', 'completed')
                    ->where('payment_id', '!=', $id)
                    ->sum('amount');

                if ($totalPaid >= $invoice->total_amount) {
                    if ($invoice->order) {
                        $invoice->order->update(['payment_status' => 'paid']);
                    }
                } elseif ($totalPaid > 0) {
                    if ($invoice->order) {
                        $invoice->order->update(['payment_status' => 'partial']);
                    }
                } else {
                    if ($invoice->order) {
                        $invoice->order->update(['payment_status' => 'pending']);
                    }
                }

                $invoice->save();
            }

            // Reverse client balance if payment was completed
            if ($payment->status == 'completed') {
                $client = Client::find($payment->client_id);
                if ($client) {
                    $previousBalance = $client->balance;
                    $newBalance = $previousBalance - $payment->amount;
                    $client->balance = $newBalance;
                    $client->save();

                    $invoiceNumber = $payment->invoice ? $payment->invoice->invoice_number : 'N/A';
                    $client->balanceHistory()->create([
                        'previous_balance' => $previousBalance,
                        'new_balance' => $newBalance,
                        'amount' => -$payment->amount,
                        'type' => 'payment_deleted',
                        'reference_type' => 'payment',
                        'reference_id' => $payment->payment_id,
                        'description' => 'Suppression paiement de ' . number_format($payment->amount, 2, ',', '.') . ' DH pour facture #' . $invoiceNumber,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // Don't delete the check record, just remove the association
            $payment->update(['check_id' => null]);
            $payment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function print($id)
    {
        $payment = Payment::with(['invoice.order.client', 'client', 'recorder'])->findOrFail($id);
        return view('pages.sales.payments.print', compact('payment'));
    }

    public function getStatistics()
    {
        $totalPayments = Payment::count();
        $completedPayments = Payment::where('status', 'completed')->count();
        $todayPayments = Payment::whereDate('payment_date', today())->count();
        $totalAmount = Payment::sum('amount');
        $todayAmount = Payment::whereDate('payment_date', today())->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalPayments,
                'completed' => $completedPayments,
                'today' => $todayPayments,
                'total_amount' => $totalAmount,
                'today_amount' => $todayAmount
            ]
        ]);
    }

    public function getInvoiceInfo($id)
    {
        $invoice = Invoice::with(['order.client'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'client_id' => $invoice->order->client_id,
            'total_amount' => $invoice->total_amount,
            'amount_paid' => $invoice->amount_paid,
            'balance_due' => $invoice->balance_due,
            'client_name' => $invoice->order->client->display_name
        ]);
    }

    // New method to get available checks
    public function getAvailableChecks()
    {
        $checks = Check::where('is_active', true)
            ->where('check_type', 'client')
            ->where('status', 'pending')
            ->select('check_id', 'check_number', 'amount', 'bank_name', 'account_holder', 'issue_date')
            ->get();

        return response()->json([
            'success' => true,
            'checks' => $checks
        ]);
    }
}
