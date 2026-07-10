<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\Client;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesOrderPayment;
use App\Models\ProductFamilleStock;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreditNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_credit_notes')->only(['index', 'show', 'getStatistics', 'getClientOrders', 'getOrderItems', 'getClientInfo', 'generatePdf']);
        $this->middleware('can:create_credit_notes')->only(['create', 'store']);
        $this->middleware('can:edit_credit_notes')->only(['edit', 'update', 'approve', 'reject', 'process']);
        $this->middleware('can:delete_credit_notes')->only(['destroy']);
    }

    /**
     * Display a listing of credit notes
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = CreditNote::with(['client', 'creator'])
                ->select('credit_notes.*');

            if ($request->filled('date_from')) {
                $query->whereDate('credit_note_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('credit_note_date', '<=', $request->date_to);
            }

            $totalAmount = (clone $query)->sum('total_amount');

            $creditNotes = $query;

            return DataTables::of($creditNotes)
                ->with(['total_amount' => $totalAmount])
                ->addIndexColumn()
                ->addColumn('action', function($creditNote) {
                    $btn = '<div class="dropdown dropstart">';
                    $btn .= '<a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical fs-6"></i>
                            </a>';
                    $btn .= '<ul class="dropdown-menu dropdown-menu-end">';
                    $btn .= '<li><a class="dropdown-item" href="'.route('credit-notes.show', $creditNote->credit_note_id).'">
                                <i class="fas fa-eye me-2"></i>Voir</a></li>';

                    if ($creditNote->status === 'draft') {
                        $btn .= '<li><a class="dropdown-item" href="'.route('credit-notes.edit', $creditNote->credit_note_id).'">
                                    <i class="fas fa-edit me-2"></i>Modifier</a></li>';
                    }

                    if (in_array($creditNote->status, ['draft', 'pending', 'rejected'])) {
                        $btn .= '<li><hr class="dropdown-divider"></li>';
                        $btn .= '<li><a class="dropdown-item delete-credit-note" href="javascript:void(0)" data-id="'.$creditNote->credit_note_id.'" data-number="'.$creditNote->credit_note_number.'">
                                    <i class="fas fa-trash text-danger me-2"></i>Supprimer</a></li>';
                    }

                    if ($creditNote->status === 'pending') {
                        $btn .= '<li><a class="dropdown-item approve-credit-note" href="javascript:void(0)" data-id="'.$creditNote->credit_note_id.'" data-number="'.$creditNote->credit_note_number.'">
                                    <i class="fas fa-check-circle text-success me-2"></i>Approuver</a></li>';
                        $btn .= '<li><a class="dropdown-item reject-credit-note" href="javascript:void(0)" data-id="'.$creditNote->credit_note_id.'" data-number="'.$creditNote->credit_note_number.'">
                                    <i class="fas fa-times-circle text-danger me-2"></i>Rejeter</a></li>';
                    }

                    if ($creditNote->status === 'approved') {
                        $btn .= '<li><a class="dropdown-item process-credit-note" href="javascript:void(0)" data-id="'.$creditNote->credit_note_id.'" data-number="'.$creditNote->credit_note_number.'">
                                    <i class="fas fa-check-double text-success me-2"></i>Traiter</a></li>';
                    }

                    if (in_array($creditNote->status, ['approved', 'processed'])) {
                        $btn .= '<li><hr class="dropdown-divider"></li>';
                        $btn .= '<li><a class="dropdown-item pdf-credit-note" href="javascript:void(0)" data-id="'.$creditNote->credit_note_id.'">
                                    <i class="fas fa-file-pdf text-danger me-2"></i>PDF</a></li>';
                    }

                    $btn .= '</ul></div>';
                    return $btn;
                })
                ->addColumn('client_name', function($row) {
                    return $row->client->display_name;
                })
                ->addColumn('status_badge', function($row) {
                    $badges = [
                        'draft' => 'secondary',
                        'pending' => 'warning',
                        'approved' => 'info',
                        'rejected' => 'danger',
                        'processed' => 'success',
                    ];
                    $labels = [
                        'draft' => 'Brouillon',
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                        'processed' => 'Traité',
                    ];
                    $color = $badges[$row->status] ?? 'secondary';
                    $label = $labels[$row->status] ?? $row->status;
                    return '<span class="badge badge-'.$color.'">'.$label.'</span>';
                })
                ->editColumn('total_amount', function($row) {
                    return number_format($row->total_amount, 2, ',', '.') . ' DH';
                })
                ->editColumn('credit_note_date', function($row) {
                    return $row->credit_note_date->format('d/m/Y');
                })
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
        }

        return view('pages.sales.credit-notes.index');
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics()
    {
        $totalCreditNotes = CreditNote::count();
        $todayCreditNotes = CreditNote::whereDate('credit_note_date', today())->count();
        $pendingApproval = CreditNote::where('status', 'pending')->count();
        $totalAmount = CreditNote::where('status', 'processed')->sum('total_amount');
        $pendingAmount = CreditNote::whereIn('status', ['pending', 'approved'])->sum('total_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalCreditNotes,
                'today' => $todayCreditNotes,
                'pending_approval' => $pendingApproval,
                'total_amount' => $totalAmount,
                'pending_amount' => $pendingAmount
            ]
        ]);
    }

    /**
     * Show form to create new credit note
     */
    public function create()
    {
        $clients = Client::where('is_active', true)->get();
        $nextNumber = CreditNote::generateNumber();

        return view('pages.sales.credit-notes.create', compact('clients', 'nextNumber'));
    }

    /**
     * Get client info with balance and credit details
     */
    public function getClientInfo($clientId)
    {
        try {
            $client = Client::findOrFail($clientId);

            // Get unpaid sales orders
            $unpaidOrders = SalesOrder::where('client_id', $clientId)
                ->where('payment_status', 'pending')
                ->orWhere('payment_status', 'partial')
                ->where('final_amount', '>', 'paid_amount')
                ->get()
                ->map(function($order) {
                    $remaining = $order->final_amount - $order->paid_amount;
                    return [
                        'order_id' => $order->order_id,
                        'order_number' => $order->order_number,
                        'order_date' => $order->order_date->format('d/m/Y'),
                        'total_amount' => $order->final_amount,
                        'paid_amount' => $order->paid_amount,
                        'remaining' => $remaining,
                        'remaining_formatted' => number_format($remaining, 2, ',', '.') . ' DH',
                    ];
                });

            $totalUnpaid = $unpaidOrders->sum('remaining');


            return response()->json([
                'success' => true,
                'data' => [
                    'client_name' => $client->display_name,
                    'balance' => $client->balance,
                    'balance_formatted' => $client->balance_formatted,
                    'balance_status' => $client->balance_status,
                    'available_advance' => $client->available_advance,
                    'advance_formatted' => $client->advance_formatted,
                    'has_advance' => $client->has_advance,
                    'total_debt' => $client->total_debt,
                    'debt_formatted' => $client->debt_formatted,
                    'has_debt' => $client->has_debt,
                    'credit_limit' => $client->credit_limit,
                    'credit_usage' => $client->credit_usage,
                    'credit_available' => $client->credit_available,
                    'has_credit' => $client->has_credit,
                    'unpaid_orders' => $unpaidOrders,
                    'total_unpaid' => $totalUnpaid,
                    'total_unpaid_formatted' => number_format($totalUnpaid, 2, ',', '.') . ' DH',
                ]
            ]);


        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all sales orders for a client
     */
    public function getClientOrders($clientId)
    {
        try {
            $orders = SalesOrder::where('client_id', $clientId)
                ->orderBy('order_date', 'desc')
                ->get()
                ->map(function($order) {
                    $remaining = $order->final_amount - $order->paid_amount;
                    return [
                        'order_id' => $order->order_id,
                        'order_number' => $order->order_number,
                        'order_date' => $order->order_date->format('d/m/Y'),
                        'total_amount' => $order->final_amount,
                        'total_formatted' => number_format($order->final_amount, 2, ',', '.') . ' DH',
                        'paid_amount' => $order->paid_amount,
                        'paid_formatted' => number_format($order->paid_amount, 2, ',', '.') . ' DH',
                        'remaining' => $remaining,
                        'remaining_formatted' => number_format($remaining, 2, ',', '.') . ' DH',
                        'payment_status' => $order->payment_status,
                        'payment_status_label' => $order->payment_status == 'paid' ? 'Payé' :
                                                ($order->payment_status == 'partial' ? 'Avance' : 'Non Payé'),
                        'items_count' => $order->items()->count(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get items from a specific order
     */
    public function getOrderItems($orderId)
    {
        try {
            $order = SalesOrder::with(['items'])->findOrFail($orderId);

            $items = $order->items->map(function($item) {
                return [
                    'order_item_id' => $item->order_item_id,
                    'item_type' => $item->item_type,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'quantity' => $item->quantity,
                    'quantity_formatted' => number_format($item->quantity, 2, ',', '.'),
                    'unit_price' => $item->unit_price,
                    'unit_price_formatted' => number_format($item->unit_price, 2, ',', '.') . ' DH',
                    'total_price' => $item->total_price,
                    'total_formatted' => number_format($item->total_price, 2, ',', '.') . ' DH',
                    'family_id' => $item->family_id,
                    'family_name' => $item->family_name,
                    'type_label' => $item->type_label,
                ];
            });

            return response()->json([
                'success' => true,
                'order' => [
                    'order_id' => $order->order_id,
                    'order_number' => $order->order_number,
                    'order_date' => $order->order_date->format('d/m/Y'),
                    'total_amount' => $order->final_amount,
                    'total_formatted' => number_format($order->final_amount, 2, ',', '.') . ' DH',
                    'paid_amount' => $order->paid_amount,
                    'paid_formatted' => number_format($order->paid_amount, 2, ',', '.') . ' DH',
                    'remaining' => $order->final_amount - $order->paid_amount,
                    'remaining_formatted' => number_format($order->final_amount - $order->paid_amount, 2, ',', '.') . ' DH',
                    'payment_status' => $order->payment_status,
                ],
                'items' => $items
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new credit note
     */
    public function store(Request $request)
    {
        $request->validate([
            'credit_note_number' => 'required|unique:credit_notes|max:50',
            'client_id' => 'required|exists:clients,client_id',
            'sales_order_id' => 'nullable|exists:sales_orders,order_id',
            'credit_note_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.order_id' => 'nullable',
            'items.*.order_item_id' => 'nullable',
            'items.*.item_type' => 'required',
            'items.*.item_id' => 'required',
            'items.*.item_name' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.family_id' => 'nullable',
            'items.*.family_name' => 'nullable',
            'items.*.reason' => 'nullable|string',
            'disposition' => 'required|in:refund,credit,balance',
            'apply_to_order_id' => 'nullable|exists:sales_orders,order_id',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $client = Client::findOrFail($request->client_id);

            // Calculate total amount
            $totalAmount = 0;
            $itemsData = [];
            $ordersAffected = [];

            foreach ($request->items as $itemData) {
                $quantity = (float) $itemData['quantity'];
                $unitPrice = (float) $itemData['unit_price'];
                $itemTotal = $quantity * $unitPrice;
                $totalAmount += $itemTotal;

                $itemsData[] = [
                    'order_id' => $itemData['order_id'] ?? null,
                    'order_item_id' => $itemData['order_item_id'] ?? null,
                    'item_type' => $itemData['item_type'],
                    'item_id' => $itemData['item_id'],
                    'item_name' => $itemData['item_name'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotal,
                    'family_id' => $itemData['family_id'] ?? null,
                    'family_name' => $itemData['family_name'] ?? null,
                    'reason' => $itemData['reason'] ?? null,
                ];

                if ($itemData['order_id']) {
                    $ordersAffected[$itemData['order_id']] = true;
                }
            }

            // Create credit note
            $creditNote = CreditNote::create([
                'credit_note_number' => $request->credit_note_number,
                'client_id' => $request->client_id,
                'sales_order_id' => $request->sales_order_id,
                'credit_note_date' => $request->credit_note_date,
                'total_amount' => $totalAmount,
                'disposition' => $request->disposition,
                'status' => 'pending',
                'reason' => $request->reason,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Create items
            foreach ($itemsData as $itemData) {
                $creditNote->items()->create($itemData);
            }

            // If disposition is to apply to another unpaid order
            if ($request->disposition === 'credit' && $request->filled('apply_to_order_id')) {
                $targetOrder = SalesOrder::find($request->apply_to_order_id);
                if ($targetOrder && $targetOrder->payment_status !== 'paid') {
                    $remainingOrderAmount = $targetOrder->final_amount - $targetOrder->paid_amount;
                    $amountToApply = min($totalAmount, $remainingOrderAmount);

                    if ($amountToApply > 0) {
                        $targetOrder->payments()->create([
                            'payment_method' => 'avoir',
                            'amount' => $amountToApply,
                            'payment_date' => $request->credit_note_date,
                            'notes' => "Avoir N°{$creditNote->credit_note_number} appliqué",
                            'credit_note_id' => $creditNote->credit_note_id,
                        ]);

                        $targetOrder->paid_amount += $amountToApply;
                        if ($targetOrder->paid_amount >= $targetOrder->final_amount - 0.01) {
                            $targetOrder->payment_status = 'paid';
                        } elseif ($targetOrder->paid_amount > 0) {
                            $targetOrder->payment_status = 'partial';
                        }
                        $targetOrder->save();

                        if ($targetOrder->payment_status === 'paid') {
                            $client->releaseCredit($amountToApply, $targetOrder, "Avoir N°{$creditNote->credit_note_number}");
                        }

                        DB::table('credit_note_order_applications')->insert([
                            'credit_note_id' => $creditNote->credit_note_id,
                            'order_id' => $targetOrder->order_id,
                            'amount' => $amountToApply,
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            $dispositionMessages = [
                'refund' => 'Avoir créé avec succès! Le client sera remboursé après traitement.',
                'credit' => 'Avoir créé avec succès! Il sera appliqué aux ventes impayées.',
                'balance' => 'Avoir créé avec succès! Il sera ajouté au solde du client après traitement.',
            ];

            return response()->json([
                'success' => true,
                'message' => $dispositionMessages[$request->disposition],
                'credit_note_id' => $creditNote->credit_note_id,
                'credit_note_number' => $creditNote->credit_note_number,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Credit note creation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'avoir: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show credit note details
     */
    public function show($id)
    {
        $creditNote = CreditNote::with(['client', 'salesOrder', 'items', 'creator', 'approver'])
            ->findOrFail($id);

        // Get applied payments if any
        $appliedPayments = DB::table('credit_note_order_applications')
            ->where('credit_note_id', $id)
            ->get();

        return view('pages.sales.credit-notes.show', compact('creditNote', 'appliedPayments'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $creditNote = CreditNote::with(['items'])->findOrFail($id);

        if ($creditNote->status !== 'draft') {
            return redirect()->route('credit-notes.index')
                ->with('error', 'Seuls les avoirs en brouillon peuvent être modifiés.');
        }

        $clients = Client::where('is_active', true)->get();

        return view('pages.sales.credit-notes.edit', compact('creditNote', 'clients'));
    }

    /**
     * Update credit note
     */
    public function update(Request $request, $id)
    {
        $creditNote = CreditNote::findOrFail($id);

        if ($creditNote->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les avoirs en brouillon peuvent être modifiés.'
            ], 400);
        }

        $request->validate([
            'credit_note_number' => 'required|unique:credit_notes,credit_note_number,'.$id.',credit_note_id|max:50',
            'client_id' => 'required|exists:clients,client_id',
            'credit_note_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.order_id' => 'nullable',
            'items.*.order_item_id' => 'nullable',
            'items.*.item_type' => 'required',
            'items.*.item_id' => 'required',
            'items.*.item_name' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.family_id' => 'nullable',
            'items.*.family_name' => 'nullable',
            'items.*.reason' => 'nullable|string',
            'disposition' => 'required|in:refund,credit,balance',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Calculate total amount
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $itemData) {
                $quantity = (float) $itemData['quantity'];
                $unitPrice = (float) $itemData['unit_price'];
                $itemTotal = $quantity * $unitPrice;
                $totalAmount += $itemTotal;

                $itemsData[] = [
                    'order_id' => $itemData['order_id'] ?? null,
                    'order_item_id' => $itemData['order_item_id'] ?? null,
                    'item_type' => $itemData['item_type'],
                    'item_id' => $itemData['item_id'],
                    'item_name' => $itemData['item_name'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotal,
                    'family_id' => $itemData['family_id'] ?? null,
                    'family_name' => $itemData['family_name'] ?? null,
                    'reason' => $itemData['reason'] ?? null,
                ];
            }

            // Update credit note
            $creditNote->update([
                'credit_note_number' => $request->credit_note_number,
                'client_id' => $request->client_id,
                'credit_note_date' => $request->credit_note_date,
                'total_amount' => $totalAmount,
                'disposition' => $request->disposition,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            // Delete old items and create new ones
            $creditNote->items()->delete();

            foreach ($itemsData as $itemData) {
                $creditNote->items()->create($itemData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Avoir mis à jour avec succès!',
                'credit_note_id' => $creditNote->credit_note_id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Credit note update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'avoir: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve credit note
     */
    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $creditNote = CreditNote::findOrFail($id);

            if ($creditNote->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet avoir ne peut pas être approuvé.'
                ], 400);
            }

            $creditNote->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Avoir approuvé avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject credit note
     */
    public function reject($id)
    {
        DB::beginTransaction();
        try {
            $creditNote = CreditNote::findOrFail($id);

            if ($creditNote->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet avoir ne peut pas être rejeté.'
                ], 400);
            }

            $creditNote->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Avoir rejeté!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process credit note (update stock and client balance)
     */
    public function process($id)
    {
        DB::beginTransaction();
        try {
            $creditNote = CreditNote::with(['items'])->findOrFail($id);

            if ($creditNote->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les avoirs approuvés peuvent être traités.'
                ], 400);
            }

            // Update stock for each item (add back to stock)
            foreach ($creditNote->items as $item) {
                if ($item->item_type !== 'raw_material' && !empty($item->family_id)) {
                    $this->updateProductStock($item, true);
                }
            }

            // Process credit note based on disposition
            if ($creditNote->disposition === 'refund') {
                // Update client balance (add credit to client for refund)
                $client = $creditNote->client;
                $previousBalance = $client->balance;
                $client->balance += $creditNote->total_amount;
                $client->save();

                // Record balance history
                $client->balanceHistory()->create([
                    'previous_balance' => $previousBalance,
                    'new_balance' => $client->balance,
                    'amount' => $creditNote->total_amount,
                    'type' => 'credit_note_refund',
                    'reference_type' => 'credit_note',
                    'reference_id' => $creditNote->credit_note_id,
                    'description' => "Remboursement Avoir N°{$creditNote->credit_note_number}",
                    'created_by' => Auth::id(),
                ]);
            } elseif ($creditNote->disposition === 'balance') {
                // Add credit to client's balance/solde only — no cash refund, not applied to a vente
                $client = $creditNote->client;
                $previousBalance = $client->balance;
                $client->balance += $creditNote->total_amount;
                $client->save();

                // Record balance history
                $client->balanceHistory()->create([
                    'previous_balance' => $previousBalance,
                    'new_balance' => $client->balance,
                    'amount' => $creditNote->total_amount,
                    'type' => 'credit_note_balance',
                    'reference_type' => 'credit_note',
                    'reference_id' => $creditNote->credit_note_id,
                    'description' => "Avoir N°{$creditNote->credit_note_number} ajouté au solde client",
                    'created_by' => Auth::id(),
                ]);
            }
            // For credit disposition, the amounts were already applied during creation

            $creditNote->update(['status' => 'processed']);

            DB::commit();

            $processMessages = [
                'refund' => 'Avoir traité avec succès! Le stock a été mis à jour et le client sera remboursé.',
                'credit' => 'Avoir traité avec succès! Le stock a été mis à jour et les ventes impayées ont été créditées.',
                'balance' => 'Avoir traité avec succès! Le stock a été mis à jour et le solde du client a été crédité.',
            ];

            return response()->json([
                'success' => true,
                'message' => $processMessages[$creditNote->disposition],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Credit note processing error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement de l\'avoir: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product stock (add or remove)
     */
    private function updateProductStock($item, $addToStock = true)
    {
        try {
            $familleStock = ProductFamilleStock::where('product_id', $item->item_id)
                ->where('famille_id', $item->family_id)
                ->first();

            if ($familleStock) {
                if ($addToStock) {
                    $familleStock->current_quantity += $item->quantity;
                } else {
                    $familleStock->current_quantity -= $item->quantity;
                }
                $familleStock->available_quantity = $familleStock->current_quantity - $familleStock->reserved_quantity;
                $familleStock->last_updated = now();
                $familleStock->save();
            } else {
                ProductFamilleStock::create([
                    'product_id' => $item->item_id,
                    'famille_id' => $item->family_id,
                    'famille_name' => $item->family_name,
                    'current_quantity' => $addToStock ? $item->quantity : -$item->quantity,
                    'reserved_quantity' => 0,
                    'available_quantity' => $addToStock ? $item->quantity : -$item->quantity,
                    'last_updated' => now(),
                ]);
            }

        } catch (\Exception $e) {
            \Log::warning('Stock update warning: ' . $e->getMessage());
        }
    }

    /**
     * Delete credit note
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $creditNote = CreditNote::findOrFail($id);

            if (!in_array($creditNote->status, ['draft', 'pending', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les avoirs en brouillon, en attente ou rejetés peuvent être supprimés.'
                ], 400);
            }

            // Disposition "credit" is applied to a sales order at creation time (before
            // approval), so deleting the credit note must undo that payment/credit usage.
            if ($creditNote->disposition === 'credit') {
                $applications = DB::table('credit_note_order_applications')
                    ->where('credit_note_id', $creditNote->credit_note_id)
                    ->get();

                foreach ($applications as $application) {
                    $targetOrder = SalesOrder::find($application->order_id);
                    if ($targetOrder) {
                        $wasFullyPaid = $targetOrder->payment_status === 'paid';

                        $targetOrder->payments()->where('credit_note_id', $creditNote->credit_note_id)->delete();
                        $targetOrder->paid_amount = max(0, $targetOrder->paid_amount - $application->amount);
                        if ($targetOrder->paid_amount <= 0) {
                            $targetOrder->payment_status = 'pending';
                        } elseif ($targetOrder->paid_amount < $targetOrder->final_amount - 0.01) {
                            $targetOrder->payment_status = 'partial';
                        } else {
                            $targetOrder->payment_status = 'paid';
                        }
                        $targetOrder->save();

                        if ($wasFullyPaid && $targetOrder->payment_status !== 'paid') {
                            $creditNote->client->useCredit($application->amount, $targetOrder, "Annulation Avoir N°{$creditNote->credit_note_number}");
                        }
                    }
                }

                DB::table('credit_note_order_applications')->where('credit_note_id', $creditNote->credit_note_id)->delete();
            }

            $creditNote->items()->delete();
            $creditNote->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Avoir supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF
     */
    public function generatePdf($id)
    {
        try {
            $creditNote = CreditNote::with(['client', 'items', 'creator'])
                ->findOrFail($id);

            // Get base64 logo if exists
            $enteteBase64 = '';
            if (file_exists(public_path('images/entete.jpg'))) {
                $entetePath = public_path('images/entete.jpg');
                $enteteBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($entetePath));
            }

            $data = [
                'creditNote' => $creditNote,
                'credit_note_number_formatted' => $creditNote->credit_note_number,
                'client' => $creditNote->client,
                'items' => $creditNote->items,
                'date' => now()->format('d/m/Y'),
                'time' => now()->format('H:i'),
                'username' => auth()->user()->name ?? auth()->user()->username,
                'enteteBase64' => $enteteBase64,
                'numberToFrench' => function($number) {
                    return $this->numberToFrench($number);
                }
            ];

            $pdf = Pdf::loadView('pdf.credit-note', $data);
            $pdf->setPaper('A4', 'portrait');

            return $pdf->download('avoir-' . $creditNote->credit_note_number . '.pdf');

        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert number to French words
     */
    private function numberToFrench($number)
    {
        $number = (float) $number;
        $integer = floor($number);
        $decimal = round(($number - $integer) * 100);

        $units = ['', 'UN', 'DEUX', 'TROIS', 'QUATRE', 'CINQ', 'SIX', 'SEPT', 'HUIT', 'NEUF', 'DIX', 'ONZE', 'DOUZE', 'TREIZE', 'QUATORZE', 'QUINZE', 'SEIZE', 'DIX-SEPT', 'DIX-HUIT', 'DIX-NEUF'];
        $tens = ['', '', 'VINGT', 'TRENTE', 'QUARANTE', 'CINQUANTE', 'SOIXANTE', 'SOIXANTE-DIX', 'QUATRE-VINGTS', 'QUATRE-VINGT-DIX'];

        $convert = function($num) use (&$convert, $units, $tens) {
            if ($num < 20) return $units[$num];

            if ($num < 100) {
                $ten = floor($num / 10);
                $unit = $num % 10;

                if ($ten == 7) {
                    return 'SOIXANTE' . ($unit == 0 ? '' : '-' . $units[10 + $unit]);
                }
                if ($ten == 9) {
                    return 'QUATRE-VINGT' . ($unit == 0 ? '' : '-' . $units[10 + $unit]);
                }

                if ($unit == 0) {
                    return $tens[$ten];
                } elseif ($unit == 1) {
                    return $tens[$ten] . ' ET UN';
                } else {
                    return $tens[$ten] . '-' . $units[$unit];
                }
            }

            if ($num < 1000) {
                $hundred = floor($num / 100);
                $remainder = $num % 100;

                $hundredText = ($hundred == 1 ? 'CENT' : $units[$hundred] . ' CENT');
                if ($hundred > 1 && $remainder == 0) $hundredText .= 'S';

                return $hundredText . ($remainder > 0 ? ' ' . $convert($remainder) : '');
            }

            $divisors = [
                1000000000 => 'MILLIARD',
                1000000 => 'MILLION',
                1000 => 'MILLE'
            ];

            foreach ($divisors as $divisor => $word) {
                if ($num >= $divisor) {
                    $quotient = floor($num / $divisor);
                    $remainder = $num % $divisor;

                    $quotientText = $quotient == 1 ? 'UN' : $convert($quotient);

                    if ($word == 'MILLE') {
                        $word = 'MILLE';
                    } else {
                        if ($quotient > 1) {
                            $word .= 'S';
                        }
                    }

                    $result = $quotientText . ' ' . $word;

                    if ($remainder > 0) {
                        $result .= ' ' . $convert($remainder);
                    }

                    return $result;
                }
            }

            return '';
        };

        $result = $convert($integer);

        if ($decimal > 0) {
            $result .= ' ET ' . $convert($decimal) . ' CENTIME' . ($decimal > 1 ? 'S' : '');
        }

        return trim($result);
    }
}
