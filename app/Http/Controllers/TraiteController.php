<?php

namespace App\Http\Controllers;

use App\Models\Traite;
use App\Models\Client;
use App\Models\ClientBalanceHistory;
use App\Models\SalesOrder;
use App\Models\SalesOrderPayment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TraiteController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_traites')->only(['index', 'show', 'getStatistics']);
        $this->middleware('can:create_traites')->only(['create', 'store']);
        $this->middleware('can:manage_traites')->only(['edit', 'update', 'destroy', 'honor', 'bounce', 'deposit']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $status = $request->get('status', 'all');

            $traites = Traite::with(['client', 'order', 'creator', 'payment'])->select('traites.*');

            if ($status !== 'all') {
                $traites->where('status', $status);
            }

            return DataTables::of($traites)
                ->addIndexColumn()
                ->addColumn('action', function ($traite) {
                    $btn = '<div class="dropdown dropstart">';
                    $btn .= '<a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>';
                    $btn .= '<ul class="dropdown-menu dropdown-menu-end">';
                    $btn .= '<li><a class="dropdown-item" href="' . route('traites.show', $traite->traite_id) . '">
                                <i class="fas fa-eye me-2"></i>Voir</a></li>';
                    $btn .= '<li><a class="dropdown-item" href="' . route('traites.edit', $traite->traite_id) . '">
                                <i class="fas fa-edit me-2"></i>Éditer</a></li>';

                    if ($traite->status === 'pending') {
                        $btn .= '<li><a class="dropdown-item mark-paid" href="#" data-id="' . $traite->traite_id . '" data-number="' . $traite->traite_number . '">
                                    <i class="fas fa-check-circle me-2 text-success"></i>Marquer comme Payé</a></li>';
                    }

                    if ($traite->status === 'pending' && $traite->is_overdue) {
                        $btn .= '<li><a class="dropdown-item mark-overdue" href="#" data-id="' . $traite->traite_id . '" data-number="' . $traite->traite_number . '">
                                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Marquer En Retard</a></li>';
                    }

                    if ($traite->status !== 'paid' && $traite->status !== 'bounced') {
                        $btn .= '<li><a class="dropdown-item mark-bounced" href="#" data-id="' . $traite->traite_id . '" data-number="' . $traite->traite_number . '">
                                    <i class="fas fa-times-circle me-2 text-danger"></i>Marquer Rebondi</a></li>';
                    }

                    $btn .= '<li><hr class="dropdown-divider"></li>';
                    $btn .= '<li><a class="dropdown-item delete" href="#" data-id="' . $traite->traite_id . '" data-number="' . $traite->traite_number . '">
                                <i class="fas fa-trash text-danger me-2"></i>Supprimer</a></li>';
                    $btn .= '</ul></div>';

                    return $btn;
                })
                ->addColumn('traite_number_formatted', function ($row) {
                    return '<strong>' . e($row->traite_number) . '</strong>';
                })
                ->addColumn('client_info', function ($row) {
                    if ($row->client) {
                        $balanceInfo = '';
                        if ($row->client->balance != 0) {
                            $balanceClass = $row->client->balance > 0 ? 'text-success' : 'text-danger';
                            $balanceIcon = $row->client->balance > 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                            $balanceText = $row->client->balance > 0 ? 'Crédit' : 'Dette';
                            $balanceInfo = '<small class="d-block ' . $balanceClass . '">
                                <i class="fas ' . $balanceIcon . ' me-1"></i>' . $balanceText . ': ' . number_format(abs($row->client->balance), 2, ',', '.') . ' DH
                            </small>';
                        }
                        return '<div><strong>' . e($row->client->display_name) . '</strong></div>
                                <small class="text-muted">' . e($row->client->code ?? '') . '</small>
                                ' . $balanceInfo;
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('order_info', function ($row) {
                    if ($row->order) {
                        $remaining = $row->order->remaining_amount;
                        $remainingHtml = '';
                        if ($remaining > 0) {
                            $remainingHtml = '<small class="d-block text-warning">Restant: ' . number_format($remaining, 2, ',', '.') . ' DH</small>';
                        } elseif ($remaining == 0) {
                            $remainingHtml = '<small class="d-block text-success">Soldé</small>';
                        }
                        return '<a href="' . route('sales.orders.show', $row->order_id) . '" class="text-decoration-none">
                                    <strong>' . e($row->order->order_number) . '</strong>
                                </a>' . $remainingHtml;
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('amount_formatted', function ($row) {
                    return '<strong>' . number_format($row->amount, 2, ',', '.') . ' DH</strong>';
                })
                ->addColumn('bank_info', function ($row) {
                    $html = '<div>' . e($row->bank_name ?? '-') . '</div>';
                    if ($row->drawee) {
                        $html .= '<small class="text-muted">Tiré: ' . e($row->drawee) . '</small>';
                    }
                    return $html;
                })
                ->addColumn('dates', function ($row) {
                    $html = '<div class="small">';
                    $html .= '<div><strong>Émission:</strong> ' . ($row->issue_date ? $row->issue_date->format('d/m/Y') : '-') . '</div>';
                    $html .= '<div><strong>Échéance:</strong> ' . ($row->due_date ? $row->due_date->format('d/m/Y') : '-') . '</div>';
                    if ($row->payment_date) {
                        $html .= '<div><strong>Payé le:</strong> ' . $row->payment_date->format('d/m/Y') . '</div>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('status_info', function ($row) {
                    if ($row->status === 'paid') {
                        return '<span class="badge bg-success">Payé</span>';
                    }

                    if ($row->status === 'overdue' || $row->is_overdue) {
                        return '<span class="badge bg-danger">En retard</span>';
                    }

                    if ($row->status === 'bounced') {
                        return '<span class="badge bg-danger">Rebondi</span>';
                    }

                    $daysLeft = $row->due_date ? now()->diffInDays($row->due_date, false) : null;

                    if ($daysLeft === null) {
                        return '<span class="badge bg-warning">En attente</span>';
                    } elseif ($daysLeft < 0) {
                        return '<span class="badge bg-danger">En retard (' . abs($daysLeft) . 'j)</span>';
                    } elseif ($daysLeft === 0) {
                        return '<span class="badge bg-warning">Échéance aujourd\'hui</span>';
                    } else {
                        return '<span class="badge bg-info">' . $daysLeft . ' jour(s) restant</span>';
                    }
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->status_badge;
                })
                ->addColumn('document', function ($row) {
                    if ($row->document_path) {
                        $extension = pathinfo($row->document_path, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                        if ($isImage) {
                            return '<img src="' . asset('storage/' . $row->document_path) . '"
                                      alt="Document" style="max-height: 40px; max-width: 60px; cursor: pointer;"
                                      class="img-thumbnail" onclick="window.open(\'' . asset('storage/' . $row->document_path) . '\', \'_blank\')">';
                        } else {
                            return '<a href="' . asset('storage/' . $row->document_path) . '" download="' . $row->original_filename . '" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-download"></i>
                                    </a>';
                        }
                    }
                    return '-';
                })
                ->rawColumns(['action', 'traite_number_formatted', 'client_info', 'order_info', 'amount_formatted', 'bank_info', 'dates', 'status_info', 'status_badge', 'document'])
                ->make(true);
        }

        $totalTraites = Traite::count();
        $totalAmount = Traite::sum('amount');
        $pendingAmount = Traite::where('status', 'pending')->sum('amount');
        $paidCount = Traite::where('status', 'paid')->count();
        $overdueCount = Traite::where('status', 'pending')->where('due_date', '<', now())->count();
        $paidAmount = Traite::where('status', 'paid')->sum('amount');
        $bouncedCount = Traite::where('status', 'bounced')->count();

        return view('pages.traites.index', compact(
            'totalTraites',
            'totalAmount',
            'pendingAmount',
            'paidCount',
            'overdueCount',
            'paidAmount',
            'bouncedCount'
        ));
    }

    public function create()
    {
        $nextTraiteNumber = 'TR-' . date('Ymd') . '-' . str_pad(Traite::count() + 1, 4, '0', STR_PAD_LEFT);
        $clients = Client::where('is_active', true)->get();
        $orders = SalesOrder::where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->where('payment_status', '!=', 'paid')
                    ->orWhereNull('payment_status');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.traites.create', compact('nextTraiteNumber', 'clients', 'orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'traite_number' => 'required|unique:traites|max:50',
            'order_id' => 'nullable|exists:sales_orders,order_id',
            'client_id' => 'nullable|exists:clients,client_id',
            'amount' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'bank_name' => 'nullable|string|max:100',
            'drawee' => 'nullable|string|max:200',
            'drawee_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,paid,overdue,bounced',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $documentPath = null;
            $originalFilename = null;

            if ($request->hasFile('document')) {
                $document = $request->file('document');
                $originalFilename = $document->getClientOriginalName();
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalFilename);
                $path = $document->storeAs('traites', $filename, 'public');
                $documentPath = $path;
            }

            // Get client from order if not provided
            $clientId = $request->client_id;
            if (!$clientId && $request->order_id) {
                $order = SalesOrder::find($request->order_id);
                if ($order) {
                    $clientId = $order->client_id;
                }
            }

            $traite = Traite::create([
                'traite_number' => $request->traite_number,
                'order_id' => $request->order_id,
                'client_id' => $clientId,
                'amount' => $request->amount,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'bank_name' => $request->bank_name,
                'drawee' => $request->drawee,
                'drawee_address' => $request->drawee_address,
                'notes' => $request->notes,
                'status' => $request->status,
                'document_path' => $documentPath,
                'original_filename' => $originalFilename,
                'created_by' => Auth::id(),
            ]);

            // If status is paid, create payment and update client balance
            if ($request->status === 'paid') {
                $this->processTraitePayment($traite);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Traite créé avec succès!',
                'traite_id' => $traite->traite_id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $traite = Traite::with(['client', 'order', 'payment', 'creator'])->findOrFail($id);
        return view('pages.traites.show', compact('traite'));
    }

    public function edit($id)
    {
        $traite = Traite::findOrFail($id);
        $clients = Client::where('is_active', true)->get();
        $orders = SalesOrder::where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->where('payment_status', '!=', 'paid')
                    ->orWhereNull('payment_status');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.traites.edit', compact('traite', 'clients', 'orders'));
    }

    public function update(Request $request, $id)
    {
        $traite = Traite::findOrFail($id);

        $request->validate([
            'traite_number' => 'required|unique:traites,traite_number,' . $id . ',traite_id|max:50',
            'order_id' => 'nullable|exists:sales_orders,order_id',
            'client_id' => 'nullable|exists:clients,client_id',
            'amount' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'bank_name' => 'nullable|string|max:100',
            'drawee' => 'nullable|string|max:200',
            'drawee_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,paid,overdue,bounced',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $traite->status;
            $oldAmount = $traite->amount;
            $oldOrderId = $traite->order_id;
            $oldClientId = $traite->client_id;

            $documentPath = $traite->document_path;
            $originalFilename = $traite->original_filename;

            if ($request->hasFile('document')) {
                if ($traite->document_path && Storage::disk('public')->exists($traite->document_path)) {
                    Storage::disk('public')->delete($traite->document_path);
                }

                $document = $request->file('document');
                $originalFilename = $document->getClientOriginalName();
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalFilename);
                $path = $document->storeAs('traites', $filename, 'public');
                $documentPath = $path;
            }

            // Get client from order if not provided
            $clientId = $request->client_id;
            if (!$clientId && $request->order_id) {
                $order = SalesOrder::find($request->order_id);
                if ($order) {
                    $clientId = $order->client_id;
                }
            }

            $traite->update([
                'traite_number' => $request->traite_number,
                'order_id' => $request->order_id,
                'client_id' => $clientId,
                'amount' => $request->amount,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'bank_name' => $request->bank_name,
                'drawee' => $request->drawee,
                'drawee_address' => $request->drawee_address,
                'notes' => $request->notes,
                'status' => $request->status,
                'document_path' => $documentPath,
                'original_filename' => $originalFilename,
            ]);

            // Handle payment processing based on status changes
            if ($request->status === 'paid' && $oldStatus !== 'paid' && !$traite->payment_id) {
                // Traite became paid and has no payment yet - create one
                $this->processTraitePayment($traite);
            } elseif ($oldStatus === 'paid' && $request->status !== 'paid') {
                // Traite was paid but now is not - reverse payment
                $this->reverseTraitePayment($traite);
            } elseif ($request->status === 'bounced' && $oldStatus !== 'bounced' && $traite->payment_id) {
                // Traite bounced but had already been counted as a payment - reverse the client's solde
                $this->reverseTraitePayment($traite);
            } elseif ($request->status === 'paid' && ($oldAmount != $request->amount || $oldOrderId != $request->order_id || $oldClientId != $clientId)) {
                // Traite is paid but details changed - update payment
                $this->updateTraitePayment($traite, $oldAmount, $oldOrderId, $oldClientId);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Traite mis à jour avec succès!'
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
            $traite = Traite::findOrFail($id);

            // If this traite already impacted the client's solde, reverse it first
            if ($traite->payment_id) {
                $this->reverseTraitePayment($traite);
            }

            if ($traite->document_path && Storage::disk('public')->exists($traite->document_path)) {
                Storage::disk('public')->delete($traite->document_path);
            }

            $traite->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Traite supprimé avec succès!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markAsPaid(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $traite = Traite::findOrFail($id);

            if ($traite->status === 'paid') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cette traite est déjà payée.'
                ], 400);
            }

            // If this traite already has a payment linked (e.g. recorded at order
            // creation), it already impacted the solde - avoid crediting it twice.
            $alreadyLinked = (bool) $traite->payment_id;

            // Update traite status to paid
            $traite->update([
                'status' => 'paid',
                'payment_date' => now()
            ]);

            if (!$alreadyLinked) {
                // Process payment creation and client balance update
                $this->processTraitePayment($traite);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $alreadyLinked
                    ? 'Traite marquée comme payée avec succès!'
                    : 'Traite marquée comme payée avec succès! Le paiement a été enregistré et le solde client mis à jour.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'opération: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markAsOverdue(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $traite = Traite::findOrFail($id);

            if ($traite->status === 'paid') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Une traite payée ne peut pas être marquée comme en retard.'
                ], 400);
            }

            $traite->update([
                'status' => 'overdue'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Traite marquée comme en retard!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'opération: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markAsBounced(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $traite = Traite::findOrFail($id);

            if ($traite->status === 'paid') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Une traite payée ne peut pas être marquée comme rejetée.'
                ], 400);
            }

            // If this traite had already been counted as a payment (e.g. recorded
            // at order creation), reverse it so the client's solde reflects the bounce.
            if ($traite->payment_id) {
                $this->reverseTraitePayment($traite);
            }

            $traite->update([
                'status' => 'bounced'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Traite marquée comme rejetée! Le solde client a été mis à jour.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'opération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment when traite is marked as paid
     */
    private function processTraitePayment($traite)
    {
        // Generate unique payment number
        $paymentNumber = 'PAY-TR-' . $traite->traite_id . '-' . date('YmdHis');

        $allocatedToOrder = 0;
        $amountToCredit = $traite->amount;

        // If traite is linked to an order
        if ($traite->order_id) {
            $order = SalesOrder::find($traite->order_id);
            if ($order) {
                $remainingOnOrder = $order->remaining_amount;

                // Payment amount cannot exceed order remaining amount
                if ($traite->amount > $remainingOnOrder) {
                    $allocatedToOrder = $remainingOnOrder;
                    $amountToCredit = $traite->amount - $remainingOnOrder;
                } else {
                    $allocatedToOrder = $traite->amount;
                    $amountToCredit = 0;
                }
            }
        }

        // Create payment record
        $payment = SalesOrderPayment::create([
            'payment_number' => $paymentNumber,
            'order_id' => $traite->order_id,
            'client_id' => $traite->client_id,
            'amount' => $traite->amount,
            'payment_method' => 'traite',
            'payment_date' => now(),
            'reference' => $traite->traite_number,
            'notes' => 'Paiement via traite: ' . $traite->traite_number . ' (Échéance: ' . ($traite->due_date ? $traite->due_date->format('d/m/Y') : 'N/A') . ')',
            'status' => 'completed',
            'created_by' => Auth::id(),
        ]);

        // Link payment to traite
        $traite->update([
            'payment_id' => $payment->payment_id
        ]);

        // Update order payment status if linked to order
        if ($traite->order_id) {
            $order = SalesOrder::find($traite->order_id);
            if ($order) {
                $order->updatePaidAmount();

                $client = Client::find($traite->client_id);
                if ($client) {
                    $client->updateBalanceFromOrder($order, 'payment_added', $order->paid_amount - $traite->amount);
                }
            }

            if ($amountToCredit > 0) {
                $this->updateClientBalance(
                    $traite->client_id,
                    $amountToCredit,
                    'credit',
                    $traite,
                    "Excédent de paiement sur traite #{$traite->traite_number} après paiement de la commande #{$order->order_number}"
                );
            }
        } else {
            $this->updateClientBalance(
                $traite->client_id,
                $traite->amount,
                'credit',
                $traite,
                "Crédit client via traite #{$traite->traite_number}"
            );
        }
    }

    /**
     * Update payment when traite details change while paid
     */
    private function updateTraitePayment($traite, $oldAmount, $oldOrderId, $oldClientId)
    {
        $payment = SalesOrderPayment::find($traite->payment_id);
        if (!$payment) {
            $this->processTraitePayment($traite);
            return;
        }

        $oldOrder = $oldOrderId ? SalesOrder::find($oldOrderId) : null;
        $newOrder = $traite->order_id ? SalesOrder::find($traite->order_id) : null;
        $client = Client::find($traite->client_id);

        if ($oldOrder) {
            $oldPaymentAmount = $payment->amount;
            $oldOrder->paid_amount -= $oldPaymentAmount;
            $oldOrder->save();
            $oldOrder->updatePaymentStatus();

            if ($client) {
                $client->updateBalanceFromOrder($oldOrder, 'payment_deleted', $oldPaymentAmount);
            }
        } elseif ($oldClientId) {
            $this->updateClientBalance(
                $oldClientId,
                $oldAmount,
                'debit',
                $traite,
                "Annulation du crédit suite à modification de la traite #{$traite->traite_number}"
            );
        }

        $payment->update([
            'amount' => $traite->amount,
            'order_id' => $traite->order_id,
            'client_id' => $traite->client_id,
            'notes' => 'Paiement via traite: ' . $traite->traite_number . ' (Modifié le ' . now()->format('d/m/Y H:i') . ')'
        ]);

        $allocatedToOrder = 0;
        $amountToCredit = $traite->amount;

        if ($traite->order_id && $newOrder) {
            $remainingOnOrder = $newOrder->remaining_amount;

            if ($traite->amount > $remainingOnOrder) {
                $allocatedToOrder = $remainingOnOrder;
                $amountToCredit = $traite->amount - $remainingOnOrder;
            } else {
                $allocatedToOrder = $traite->amount;
                $amountToCredit = 0;
            }

            $newOrder->paid_amount += $allocatedToOrder;
            $newOrder->save();
            $newOrder->updatePaymentStatus();

            if ($client) {
                $client->updateBalanceFromOrder($newOrder, 'payment_added', $newOrder->paid_amount - $allocatedToOrder);
            }
        }

        if ($amountToCredit > 0) {
            $this->updateClientBalance(
                $traite->client_id,
                $amountToCredit,
                'credit',
                $traite,
                "Excédent de paiement sur traite #{$traite->traite_number} après modification"
            );
        }
    }

    /**
     * Reverse payment when traite is unmarked as paid or deleted
     */
    private function reverseTraitePayment($traite)
    {
        if ($traite->payment_id) {
            $payment = SalesOrderPayment::find($traite->payment_id);
            if ($payment) {
                $order = $traite->order_id ? SalesOrder::find($traite->order_id) : null;

                if ($order) {
                    $order->paid_amount -= $payment->amount;
                    $order->save();
                    $order->updatePaymentStatus();

                    $client = Client::find($traite->client_id);
                    if ($client) {
                        // updateBalanceFromOrder expects the amount actually applied
                        // to THIS order, not the full traite amount — passing the
                        // full amount undercounts the reversal whenever part of the
                        // traite went to this order and the rest was credited as excess.
                        $client->updateBalanceFromOrder($order, 'payment_deleted', $payment->amount);

                        // Any excess beyond what was applied to this order was
                        // credited directly to the client's solde — reverse that too.
                        $excess = round((float) $traite->amount - (float) $payment->amount, 2);
                        if ($excess > 0.005) {
                            $client->refresh();
                            $previousBalance = (float) $client->balance;
                            $newBalance = $previousBalance - $excess;
                            $client->balance = $newBalance;
                            $client->save();

                            $client->balanceHistory()->create([
                                'previous_balance' => $previousBalance,
                                'new_balance' => $newBalance,
                                'amount' => -$excess,
                                'type' => 'payment_deleted',
                                'reference_type' => 'traite',
                                'reference_id' => $traite->traite_id,
                                'description' => "Annulation de l'excédent suite à suppression/annulation de la traite #{$traite->traite_number}: " .
                                    number_format($excess, 2, ',', '.') . ' DH',
                                'created_by' => Auth::id(),
                            ]);
                        }
                    }
                } else {
                    $this->updateClientBalance(
                        $traite->client_id,
                        $traite->amount,
                        'debit',
                        $traite,
                        "Annulation du crédit suite à suppression/annulation de la traite #{$traite->traite_number}"
                    );
                }

                $payment->delete();
            }
        }

        $traite->update([
            'payment_id' => null,
            'payment_date' => null
        ]);
    }

    /**
     * Update client balance using the existing Client model methods
     */
    private function updateClientBalance($clientId, $amount, $type, $traite, $description = null)
    {
        $client = Client::find($clientId);
        if (!$client) {
            return;
        }

        $previousBalance = $client->balance;

        if ($type === 'credit') {
            $newBalance = $previousBalance + $amount;
        } else {
            $newBalance = $previousBalance - $amount;
        }

        $client->balance = $newBalance;
        $client->save();

        ClientBalanceHistory::create([
            'client_id' => $clientId,
            'previous_balance' => $previousBalance,
            'new_balance' => $newBalance,
            'amount' => $type === 'credit' ? $amount : -$amount,
            'type' => $type === 'credit' ? 'traite_credit' : 'traite_debit',
            'reference_type' => 'traite',
            'reference_id' => $traite->traite_id,
            'description' => $description ?: ($type === 'credit'
                ? "Crédit via traite #{$traite->traite_number}"
                : "Débit via annulation traite #{$traite->traite_number}"),
            'created_by' => Auth::id(),
        ]);
    }

    public function getStatistics()
    {
        $totalTraites = Traite::count();
        $pendingCount = Traite::where('status', 'pending')->count();
        $paidCount = Traite::where('status', 'paid')->count();
        $overdueCount = Traite::where('status', 'pending')->where('due_date', '<', now())->count();
        $bouncedCount = Traite::where('status', 'bounced')->count();
        $totalAmount = Traite::sum('amount');
        $pendingAmount = Traite::where('status', 'pending')->sum('amount');
        $paidAmount = Traite::where('status', 'paid')->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalTraites,
                'pending' => $pendingCount,
                'paid' => $paidCount,
                'overdue' => $overdueCount,
                'bounced' => $bouncedCount,
                'total_amount' => $totalAmount,
                'pending_amount' => $pendingAmount,
                'paid_amount' => $paidAmount
            ]
        ]);
    }
}
