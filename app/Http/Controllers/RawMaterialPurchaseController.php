<?php

namespace App\Http\Controllers;

use App\Models\Check;
use App\Models\CheckAllocation;
use App\Models\Magazine;
use App\Models\PurchasePaymentDocument;
use App\Models\RawMaterialPurchase;
use App\Models\RawMaterialPurchaseItem;
use App\Models\RawMaterial;
use App\Models\RawMaterialStockMovement;
use App\Models\StockMovementDetail;
use App\Models\Supplier;
use App\Models\Traite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class RawMaterialPurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_raw_material_purchases')->only(['index', 'show', 'getStatistics', 'generatePdf', 'getPurchaseDetails', 'getAvailableChecks', 'getAvailableTraites']);
        $this->middleware('can:create_raw_material_purchases')->only(['create', 'store', 'showReceiptForm', 'processReceipt', 'addPayment', 'storeCheck', 'updatePaymentStatus']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('suppliers')
                ->join('raw_material_purchases', 'suppliers.supplier_id', '=', 'raw_material_purchases.supplier_id')
                ->select(
                    'suppliers.supplier_id',
                    'suppliers.balance',
                    DB::raw('COALESCE(suppliers.company_name, suppliers.full_name) as supplier_name'),
                    DB::raw('COUNT(raw_material_purchases.purchase_id) as purchases_count'),
                    DB::raw('SUM(raw_material_purchases.final_amount) as total_amount'),
                    DB::raw('SUM(raw_material_purchases.paid_amount) as total_paid'),
                    DB::raw('SUM(raw_material_purchases.final_amount - raw_material_purchases.paid_amount) as total_rest'),
                    DB::raw('SUM(CASE WHEN raw_material_purchases.payment_status = \'pending\' THEN 1 ELSE 0 END) as pending_count'),
                    DB::raw('SUM(CASE WHEN raw_material_purchases.payment_status = \'partial\' THEN 1 ELSE 0 END) as partial_count'),
                    DB::raw('SUM(CASE WHEN raw_material_purchases.payment_status = \'paid\' THEN 1 ELSE 0 END) as paid_count')
                )
                ->whereNull('suppliers.deleted_at')
                ->groupBy('suppliers.supplier_id', 'suppliers.balance', 'suppliers.company_name', 'suppliers.full_name');

            if ($request->filled('supplier_id')) {
                $query->where('suppliers.supplier_id', $request->supplier_id);
            }

            if ($request->filled('payment_status')) {
                $query->where('raw_material_purchases.payment_status', $request->payment_status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('raw_material_purchases.purchase_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('raw_material_purchases.purchase_date', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('total_amount_display', function ($row) {
                    return number_format($row->total_amount, 2, ',', '.') . ' DH';
                })
                ->addColumn('total_paid_display', function ($row) {
                    return '<span class="text-success fw-bold">' . number_format($row->total_paid, 2, ',', '.') . ' DH</span>';
                })
                ->addColumn('total_rest_display', function ($row) {
                    $class = $row->total_rest > 0 ? 'text-danger' : 'text-success';
                    return '<span class="' . $class . ' fw-bold">' . number_format($row->total_rest, 2, ',', '.') . ' DH</span>';
                })
                ->addColumn('balance_display', function ($row) {
                    $b = (float) $row->balance;
                    if ($b < -0.01) {
                        return '<span class="text-success fw-bold">' . number_format(abs($b), 2, ',', '.') . ' DH <small>(crédit)</small></span>';
                    } elseif ($b > 0.01) {
                        return '<span class="text-danger fw-bold">' . number_format($b, 2, ',', '.') . ' DH <small>(dû)</small></span>';
                    }
                    return '<span class="text-muted">0,00 DH</span>';
                })
                ->addColumn('status_summary', function ($row) {
                    $badges = '';
                    if ($row->paid_count > 0)    $badges .= '<span class="badge bg-success me-1">' . $row->paid_count . ' payé</span>';
                    if ($row->partial_count > 0) $badges .= '<span class="badge bg-info me-1">' . $row->partial_count . ' avance</span>';
                    if ($row->pending_count > 0) $badges .= '<span class="badge bg-warning me-1">' . $row->pending_count . ' impayé</span>';
                    return $badges ?: '<span class="badge bg-secondary">-</span>';
                })
                ->addColumn('action', function ($row) {
                    $hasUnpaid = ($row->total_rest > 0);
                    $btn = '<div class="d-flex gap-1 justify-content-center">';
                    $btn .= '<button type="button" class="btn btn-sm btn-primary view-supplier-btn"
                                data-id="' . $row->supplier_id . '"
                                data-name="' . htmlspecialchars($row->supplier_name, ENT_QUOTES) . '"
                                title="Voir les achats">
                                <i class="fas fa-eye"></i>
                            </button>';
                    if ($hasUnpaid) {
                        $btn .= '<button type="button" class="btn btn-sm btn-success pay-supplier-btn"
                                    data-id="' . $row->supplier_id . '"
                                    data-name="' . htmlspecialchars($row->supplier_name, ENT_QUOTES) . '"
                                    data-rest="' . $row->total_rest . '"
                                    title="Payer (distribution FIFO)">
                                    <i class="fas fa-money-bill-wave"></i>
                                </button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['total_paid_display', 'total_rest_display', 'balance_display', 'status_summary', 'action'])
                ->make(true);
        }

        $suppliers = Supplier::where('is_active', true)->get();
        $materials = RawMaterial::where('is_active', true)->get();
        $magazines = Magazine::where('is_active', true)->get();

        return view('pages.raw-material-purchases.index', compact('suppliers', 'materials', 'magazines'));
    }

    public function getSupplierPurchasesList(Request $request, $supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);

        $query = RawMaterialPurchase::where('supplier_id', $supplierId);

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        $purchases = $query->orderBy('purchase_date', 'asc')->orderBy('purchase_id', 'asc')->get();

        $data = $purchases->map(function ($purchase) {
            $rest = $purchase->final_amount - $purchase->total_paid;
            return [
                'purchase_id'          => $purchase->purchase_id,
                'purchase_number'      => $purchase->purchase_number,
                'purchase_date'        => $purchase->purchase_date->format('d/m/Y'),
                'final_amount'         => (float) $purchase->final_amount,
                'total_paid'           => (float) $purchase->total_paid,
                'rest_amount'          => (float) $rest,
                'final_amount_display' => number_format($purchase->final_amount, 2, ',', '.'),
                'total_paid_display'   => number_format($purchase->total_paid, 2, ',', '.'),
                'rest_amount_display'  => number_format($rest, 2, ',', '.'),
                'rest_class'           => $rest > 0 ? 'text-danger' : 'text-success',
                'payment_status'       => $purchase->payment_status,
                'payment_status_label' => $purchase->payment_status_label,
                'show_url'             => route('raw-material-purchases.show', $purchase->purchase_id),
                'edit_url'             => route('raw-material-purchases.edit', $purchase->purchase_id),
            ];
        });

        return response()->json([
            'success'  => true,
            'supplier' => $supplier->display_name,
            'data'     => $data,
        ]);
    }

    public function distributeSupplierPayment(Request $request, $supplierId)
    {
        $request->validate([
            'amount'          => 'required|numeric|min:0.01',
            'payment_method'  => 'required|in:cash,bank_transfer,check,credit_card,traite',
            'payment_date'    => 'required|date',
            'notes'           => 'nullable|string',
            'check_id'        => 'required_if:payment_method,check|nullable|exists:checks,check_id',
            'payment_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'traite_id'       => 'nullable|exists:traites,traite_id',
            'traite_number'   => 'nullable|string|max:50',
            'traite_due_date' => 'nullable|date',
            'traite_bank'     => 'nullable|string|max:100',
        ]);

        $supplier = Supplier::findOrFail($supplierId);

        $purchases = RawMaterialPurchase::where('supplier_id', $supplierId)
            ->where('payment_status', '!=', 'paid')
            ->orderBy('purchase_date', 'asc')
            ->orderBy('purchase_id', 'asc')
            ->get();

        $requestedAmount = (float) $request->amount;
        $remainingBudget = $requestedAmount;
        $allocations = [];

        foreach ($purchases as $purchase) {
            if ($remainingBudget <= 0) break;
            $purchaseRest = (float) $purchase->final_amount - (float) $purchase->total_paid;
            if ($purchaseRest <= 0) continue;
            $allocated = min($remainingBudget, $purchaseRest);
            $allocations[] = ['purchase' => $purchase, 'amount' => $allocated];
            $remainingBudget -= $allocated;
        }

        // Anything left over (no unpaid purchase to absorb it, or an overpayment) goes
        // straight to the supplier balance — e.g. old debit balances tracked before
        // purchases existed in the app.
        $balanceAmount   = $remainingBudget > 0.005 ? $remainingBudget : 0;
        $newSupplierBalance = null;

        DB::beginTransaction();
        try {
            // Upload file once (shared reference)
            $filePath = null;
            $originalFilename = null;
            if ($request->hasFile('payment_file')) {
                $file = $request->file('payment_file');
                $originalFilename = $file->getClientOriginalName();
                $filePath = $file->store('payment-documents/' . date('Y/m'), 'public');
            }

            // Resolve check upfront
            $check = null;
            if ($request->payment_method === 'check') {
                $check = Check::findOrFail($request->check_id);
            }

            // Handle traite upfront (existing traite selected → mark paid; new → create as paid)
            $traiteId = null;
            if ($request->payment_method === 'traite') {
                if ($request->traite_id) {
                    $traite = Traite::findOrFail($request->traite_id);
                    $traite->status       = 'paid';
                    $traite->payment_date = now();
                    $traite->save();
                    $traiteId = $traite->traite_id;
                } else {
                    $traiteNumber    = $request->traite_number ?: ('TR-FOUR-' . date('Ymd') . '-' . str_pad(Traite::count() + 1, 4, '0', STR_PAD_LEFT));
                    $purchaseNumbers = implode(', ', array_map(fn($a) => $a['purchase']->purchase_number, $allocations));
                    $traiteNotes     = 'Traite fournisseur – ' . $supplier->display_name . ' – '
                        . ($purchaseNumbers ? 'achats: ' . $purchaseNumbers : 'solde fournisseur');
                    $newTraite = Traite::create([
                        'traite_number' => $traiteNumber,
                        'order_id'      => null,
                        'client_id'     => null,
                        'amount'        => $requestedAmount,
                        'issue_date'    => $request->payment_date,
                        'due_date'      => $request->traite_due_date ?? $request->payment_date,
                        'bank_name'     => $request->traite_bank,
                        'notes'         => $traiteNotes,
                        'status'        => 'paid',
                        'payment_date'  => now(),
                        'created_by'    => auth()->id(),
                    ]);
                    $traiteId = $newTraite->traite_id;
                }
            }

            foreach ($allocations as $alloc) {
                $purchase = $alloc['purchase'];
                $amount   = $alloc['amount'];

                PurchasePaymentDocument::create([
                    'purchase_id'       => $purchase->purchase_id,
                    'document_number'   => PurchasePaymentDocument::generateDocumentNumber(),
                    'document_type'     => $request->payment_method,
                    'check_id'          => $check ? $check->check_id : null,
                    'traite_id'         => $traiteId,
                    'file_path'         => $filePath,
                    'original_filename' => $originalFilename,
                    'amount'            => $amount,
                    'payment_method'    => $request->payment_method,
                    'payment_date'      => $request->payment_date,
                    'notes'             => $request->notes ?? ('Paiement groupé – ' . $supplier->display_name),
                    'uploaded_by'       => auth()->id(),
                ]);

                if ($check) {
                    CheckAllocation::create([
                        'check_id'         => $check->check_id,
                        'purchase_id'      => $purchase->purchase_id,
                        'allocated_amount' => $amount,
                        'notes'            => 'Distribution paiement – ' . $purchase->purchase_number,
                    ]);
                }

                // total_paid accessor already includes the doc created above
                $newPaid   = (float) $purchase->total_paid;
                $newStatus = $newPaid >= (float) $purchase->final_amount - 0.01 ? 'paid' : ($newPaid > 0 ? 'partial' : 'pending');
                $purchase->update(['paid_amount' => $newPaid, 'payment_status' => $newStatus]);

                // Update supplier balance for tracked purchases
                $isPurchaseTracked = DB::table('supplier_balance_history')
                    ->where('supplier_id', $supplierId)
                    ->where('reference_id', $purchase->purchase_id)
                    ->whereIn('type', ['purchase_unpaid', 'purchase_created'])
                    ->exists();

                if ($isPurchaseTracked && $amount > 0.005) {
                    $previousBalance = (float) $supplier->balance;
                    $newBalance      = $previousBalance - $amount;
                    $supplier->update(['balance' => $newBalance]);
                    $supplier->balanceHistory()->create([
                        'previous_balance' => $previousBalance,
                        'new_balance'      => $newBalance,
                        'amount'           => -$amount,
                        'type'             => 'payment_added',
                        'reference_type'   => 'purchase',
                        'reference_id'     => $purchase->purchase_id,
                        'description'      => "Paiement groupé achat #{$purchase->purchase_number}: " . number_format($amount, 2) . " DH",
                        'created_by'       => auth()->id(),
                    ]);
                    $supplier->refresh();
                }
            }

            // Leftover with no unpaid purchase to absorb it (or an overpayment) is credited
            // directly to the supplier balance — covers old debit balances tracked before
            // any purchase existed in the app.
            if ($balanceAmount > 0.005) {
                $previousBalance = (float) $supplier->balance;
                $newBalance      = $previousBalance - $balanceAmount;
                $supplier->update(['balance' => $newBalance]);
                $supplier->balanceHistory()->create([
                    'previous_balance' => $previousBalance,
                    'new_balance'      => $newBalance,
                    'amount'           => -$balanceAmount,
                    'type'             => 'payment_added',
                    'reference_type'   => 'direct',
                    'reference_id'     => 0,
                    'description'      => $request->notes ?: ('Paiement fournisseur (solde): ' . number_format($balanceAmount, 2, ',', '.') . ' DH'),
                    'created_by'       => auth()->id(),
                ]);
                $supplier->refresh();
                $newSupplierBalance = $newBalance;
            }

            if ($check) {
                $check->remaining_amount = max(0, $check->remaining_amount - $requestedAmount);
                if ($check->remaining_amount <= 0) $check->status = 'allocated';
                $check->deposit_date  = $request->payment_date;
                $check->clearing_date = $request->payment_date;
                $check->save();
            }

            DB::commit();

            $totalDistributed = array_sum(array_column($allocations, 'amount'));
            $message = [];
            if (count($allocations) > 0) {
                $message[] = number_format($totalDistributed, 2, ',', '.') . ' DH distribués sur ' . count($allocations) . ' achat(s)';
            }
            if ($balanceAmount > 0.005) {
                $message[] = number_format($balanceAmount, 2, ',', '.')
                    . ' DH ajoutés au solde fournisseur (nouveau solde: '
                    . number_format(abs($newSupplierBalance), 2, ',', '.') . ' DH '
                    . ($newSupplierBalance > 0.01 ? 'dû' : ($newSupplierBalance < -0.01 ? 'crédit' : 'soldé')) . ')';
            }

            return response()->json([
                'success'     => true,
                'message'     => implode(' + ', $message),
                'allocations' => array_map(fn($a) => [
                    'purchase_number' => $a['purchase']->purchase_number,
                    'amount'          => number_format($a['amount'], 2, ',', '.'),
                ], $allocations),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $materials = RawMaterial::where('is_active', true)->get();
        $magazines = Magazine::where('is_active', true)->get();
        $purchaseNumber = 'ACH-' . date('Ymd') . '-' . str_pad(RawMaterialPurchase::count() + 1, 4, '0', STR_PAD_LEFT);

        return view('pages.raw-material-purchases.create', compact('suppliers', 'materials', 'magazines', 'purchaseNumber'));
    }

    /**
     * Validate raw item payloads coming from the create/edit forms and
     * normalize them to the exact set of columns stored on
     * raw_material_purchase_items, based on each row's item_type.
     */
    private function normalizeAndValidateItems(array $items): array
    {
        if (empty($items)) {
            throw new \Exception('Aucun article ajouté');
        }

        $normalized = [];
        foreach ($items as $index => $item) {
            $rowLabel = 'Article ' . ($index + 1);
            $itemType = $item['item_type'] ?? 'raw_material';

            if (!in_array($itemType, ['raw_material', 'charge_diverse'], true)) {
                throw new \Exception("$rowLabel: type d'article invalide");
            }

            if ($itemType === 'charge_diverse') {
                $description = trim((string) ($item['description'] ?? ''));
                if ($description === '') {
                    throw new \Exception("$rowLabel: veuillez saisir une description pour la charge diverse");
                }
                if (!isset($item['total_price']) || !is_numeric($item['total_price']) || (float) $item['total_price'] <= 0) {
                    throw new \Exception("$rowLabel: veuillez saisir un prix total valide pour la charge diverse");
                }

                $normalized[] = [
                    'item_type' => 'charge_diverse',
                    'material_id' => null,
                    'description' => $description,
                    'quantity' => null,
                    'unit_price' => null,
                    'total_price' => round((float) $item['total_price'], 2),
                ];
            } else {
                if (empty($item['material_id']) || !RawMaterial::where('material_id', $item['material_id'])->exists()) {
                    throw new \Exception("$rowLabel: veuillez sélectionner une matière première valide");
                }
                if (!isset($item['quantity']) || !is_numeric($item['quantity']) || (float) $item['quantity'] <= 0) {
                    throw new \Exception("$rowLabel: veuillez saisir une quantité valide");
                }
                if (!isset($item['unit_price']) || !is_numeric($item['unit_price']) || (float) $item['unit_price'] < 0) {
                    throw new \Exception("$rowLabel: veuillez saisir un prix unitaire valide");
                }

                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];

                $normalized[] = [
                    'item_type' => 'raw_material',
                    'material_id' => $item['material_id'],
                    'description' => null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => round($quantity * $unitPrice, 2),
                ];
            }
        }

        return $normalized;
    }

    public function getPurchaseDetails($id)
    {
        $purchase = RawMaterialPurchase::findOrFail($id);
        return response()->json([
            'success' => true,
            'purchase_number' => $purchase->purchase_number,
            'final_amount' => $purchase->final_amount,
            'total_paid' => $purchase->total_paid,
            'remaining' => $purchase->final_amount - $purchase->total_paid
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_number' => 'required|unique:raw_material_purchases,purchase_number',
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'magazine_id' => 'required|exists:magazines,magazine_id',
            'purchase_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'include_tva' => 'nullable|boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|json',
            'payment_documents' => 'nullable|json',
        ]);

        DB::beginTransaction();
        try {
            $items = json_decode($request->items, true);
            $items = $this->normalizeAndValidateItems($items ?? []);

            // Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['total_price'];
            }

            $discountPercentage = $request->discount_percentage ?? 0;
            $discountAmount = ($subtotal * $discountPercentage) / 100;
            $finalAmount = $subtotal - $discountAmount;
            $includeTva = $request->boolean('include_tva');

            // Create purchase
            $purchase = RawMaterialPurchase::create([
                'purchase_number' => $request->purchase_number,
                'supplier_id' => $request->supplier_id,
                'magazine_id' => $request->magazine_id,
                'purchase_date' => $request->purchase_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'total_amount' => $subtotal,
                'include_tva' => $includeTva,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'payment_status' => 'pending',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // Create items
            foreach ($items as $item) {
                RawMaterialPurchaseItem::create(array_merge($item, [
                    'purchase_id' => $purchase->purchase_id,
                ]));
            }

            // Process payments if any
            $paymentDocs = json_decode($request->payment_documents, true) ?? [];
            $totalPaid = 0;
            $fileIndex = 0;

            foreach ($paymentDocs as $doc) {
                $amount = $doc['amount'];
                $totalPaid += $amount;

                // Handle file upload
                $filePath = null;
                $originalFilename = null;
                if ($request->hasFile('payment_file_' . $fileIndex)) {
                    $file = $request->file('payment_file_' . $fileIndex);
                    $originalFilename = $file->getClientOriginalName();
                    $filePath = $file->store('payment-documents/' . date('Y/m'), 'public');
                    $fileIndex++;
                }

                $docCheckId  = null;
                $docTraiteId = null;

                // Handle check allocation
                if ($doc['payment_method'] === 'check' && isset($doc['check_id'])) {
                    $check = Check::find($doc['check_id']);
                    if ($check) {
                        $docCheckId = $check->check_id;
                        CheckAllocation::create([
                            'check_id'         => $check->check_id,
                            'purchase_id'      => $purchase->purchase_id,
                            'allocated_amount' => $amount,
                            'notes'            => 'Paiement pour achat ' . $purchase->purchase_number,
                        ]);

                        // Mark check as fully encaissé
                        $check->remaining_amount = 0;
                        $check->status           = 'allocated';
                        $check->deposit_date     = $doc['payment_date'];
                        $check->clearing_date    = $doc['payment_date'];
                        $check->save();
                    }
                }

                // Create payment document with check/traite reference
                PurchasePaymentDocument::create([
                    'purchase_id'      => $purchase->purchase_id,
                    'document_number'  => PurchasePaymentDocument::generateDocumentNumber(),
                    'document_type'    => $doc['payment_method'],
                    'check_id'         => $docCheckId,
                    'traite_id'        => $docTraiteId,
                    'file_path'        => $filePath,
                    'original_filename' => $originalFilename,
                    'amount'           => $amount,
                    'payment_method'   => $doc['payment_method'],
                    'payment_date'     => $doc['payment_date'],
                    'notes'            => $doc['notes'] ?? null,
                    'uploaded_by'      => auth()->id(),
                ]);
            }

            // Update payment status
            $paymentStatus = 'pending';
            if ($totalPaid >= $finalAmount) {
                $paymentStatus = 'paid';
            } elseif ($totalPaid > 0) {
                $paymentStatus = 'partial';
            }

            $purchase->update([
                'paid_amount'    => $totalPaid,
                'payment_status' => $paymentStatus,
            ]);

            // Update supplier balance with unpaid/excess amount from this purchase
            $unpaidAmount = $finalAmount - $totalPaid;
            if (abs($unpaidAmount) > 0.005) {
                $supplier        = Supplier::findOrFail($request->supplier_id);
                $previousBalance = (float) $supplier->balance;
                $newBalance      = $previousBalance + $unpaidAmount;
                $supplier->update(['balance' => $newBalance]);
                $supplier->balanceHistory()->create([
                    'previous_balance' => $previousBalance,
                    'new_balance'      => $newBalance,
                    'amount'           => $unpaidAmount,
                    'type'             => $unpaidAmount > 0 ? 'purchase_unpaid' : 'overpayment_credit',
                    'reference_type'   => 'purchase',
                    'reference_id'     => $purchase->purchase_id,
                    'description'      => $unpaidAmount > 0
                        ? "Achat #{$purchase->purchase_number}: " . number_format($unpaidAmount, 2) . " DH non payé"
                        : "Achat #{$purchase->purchase_number}: excédent " . number_format(abs($unpaidAmount), 2) . " DH crédité en solde",
                    'created_by'       => auth()->id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Commande d\'achat créée avec succès!',
                'purchase_id' => $purchase->purchase_id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $purchase = RawMaterialPurchase::with([
            'supplier',
            'creator',
            'items.rawMaterial',
            'magazine',
            'paymentDocuments.uploader',
            'checkAllocations.check'
        ])->findOrFail($id);

        return view('pages.raw-material-purchases.show', compact('purchase'));
    }

    public function edit($id)
    {
        $purchase = RawMaterialPurchase::with(['items.rawMaterial'])->findOrFail($id);

        $hasCheckAllocation = $purchase->checkAllocations()->exists();

        $suppliers = Supplier::where('is_active', true)->get();
        $materials = RawMaterial::where('is_active', true)->get();
        $magazines = Magazine::where('is_active', true)->get();

        return view('pages.raw-material-purchases.edit', compact('purchase', 'suppliers', 'materials', 'magazines', 'hasCheckAllocation'));
    }

    public function update(Request $request, $id)
    {
        $purchase = RawMaterialPurchase::findOrFail($id);

        if ($purchase->checkAllocations()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande a déjà des allocations de chèques. Vous ne pouvez plus la modifier.'
            ], 400);
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'magazine_id' => 'required|exists:magazines,magazine_id',
            'purchase_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'include_tva' => 'nullable|boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|json',
        ]);

        DB::beginTransaction();
        try {
            $items = json_decode($request->items, true);
            $items = $this->normalizeAndValidateItems($items ?? []);

            // Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['total_price'];
            }

            $discountPercentage = $request->discount_percentage ?? 0;
            $discountAmount = ($subtotal * $discountPercentage) / 100;
            $finalAmount = $subtotal - $discountAmount;
            $includeTva = $request->boolean('include_tva');

            $oldFinalAmount = (float) $purchase->final_amount;
            $oldSupplierId  = $purchase->supplier_id;
            $paidAmount     = (float) $purchase->paid_amount;

            // Update purchase
            $purchase->update([
                'supplier_id' => $request->supplier_id,
                'magazine_id' => $request->magazine_id,
                'purchase_date' => $request->purchase_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'total_amount' => $subtotal,
                'include_tva' => $includeTva,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'notes' => $request->notes,
            ]);

            // Delete existing items
            $purchase->items()->delete();

            // Create new items
            foreach ($items as $item) {
                RawMaterialPurchaseItem::create(array_merge($item, [
                    'purchase_id' => $purchase->purchase_id,
                ]));
            }

            // Sync supplier balance with the change in unpaid amount (final_amount - paid_amount)
            $oldRemaining = $oldFinalAmount - $paidAmount;
            $newRemaining = (float) $finalAmount - $paidAmount;

            if ($oldSupplierId == $request->supplier_id) {
                $delta = $newRemaining - $oldRemaining;
                if (abs($delta) > 0.005) {
                    $supplier        = $purchase->supplier;
                    $previousBalance = (float) $supplier->balance;
                    $newBalance      = $previousBalance + $delta;
                    $supplier->update(['balance' => $newBalance]);
                    $supplier->balanceHistory()->create([
                        'previous_balance' => $previousBalance,
                        'new_balance'      => $newBalance,
                        'amount'           => $delta,
                        'type'             => 'purchase_updated',
                        'reference_type'   => 'purchase',
                        'reference_id'     => $purchase->purchase_id,
                        'description'      => "Achat #{$purchase->purchase_number} modifié: " .
                            number_format($oldFinalAmount, 2, ',', '.') . ' → ' . number_format($finalAmount, 2, ',', '.') . ' DH',
                        'created_by'       => auth()->id(),
                    ]);
                }
            } else {
                // Supplier changed: move the unpaid debt from the old supplier to the new one
                if (abs($oldRemaining) > 0.005) {
                    $oldSupplier        = Supplier::find($oldSupplierId);
                    $oldPreviousBalance = (float) $oldSupplier->balance;
                    $oldNewBalance      = $oldPreviousBalance - $oldRemaining;
                    $oldSupplier->update(['balance' => $oldNewBalance]);
                    $oldSupplier->balanceHistory()->create([
                        'previous_balance' => $oldPreviousBalance,
                        'new_balance'      => $oldNewBalance,
                        'amount'           => -$oldRemaining,
                        'type'             => 'purchase_updated',
                        'reference_type'   => 'purchase',
                        'reference_id'     => $purchase->purchase_id,
                        'description'      => "Achat #{$purchase->purchase_number} transféré vers un autre fournisseur",
                        'created_by'       => auth()->id(),
                    ]);
                }
                if (abs($newRemaining) > 0.005) {
                    $newSupplier        = $purchase->supplier;
                    $newPreviousBalance = (float) $newSupplier->balance;
                    $newNewBalance      = $newPreviousBalance + $newRemaining;
                    $newSupplier->update(['balance' => $newNewBalance]);
                    $newSupplier->balanceHistory()->create([
                        'previous_balance' => $newPreviousBalance,
                        'new_balance'      => $newNewBalance,
                        'amount'           => $newRemaining,
                        'type'             => 'purchase_updated',
                        'reference_type'   => 'purchase',
                        'reference_id'     => $purchase->purchase_id,
                        'description'      => "Achat #{$purchase->purchase_number} repris depuis un autre fournisseur: " .
                            number_format($newRemaining, 2, ',', '.') . ' DH',
                        'created_by'       => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Commande d\'achat mise à jour avec succès!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $purchase = RawMaterialPurchase::findOrFail($id);

            if ($purchase->actual_delivery_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer une commande déjà livrée.'
                ], 400);
            }

            if ($purchase->total_paid > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer une commande avec des paiements effectués.'
                ], 400);
            }

            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Commande d\'achat supprimée avec succès!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addPayment(Request $request)
    {
        $request->validate([
            'purchase_id'     => 'required|exists:raw_material_purchases,purchase_id',
            'amount'          => 'required|numeric|min:0.01',
            'payment_method'  => 'required|in:cash,bank_transfer,check,credit_card,traite',
            'payment_date'    => 'required|date',
            'notes'           => 'nullable|string',
            'check_id'        => 'required_if:payment_method,check|nullable|exists:checks,check_id',
            'payment_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'traite_id'       => 'nullable|exists:traites,traite_id',
            'traite_number'   => 'nullable|string|max:50',
            'traite_due_date' => 'nullable|date',
            'traite_bank'     => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $purchase = RawMaterialPurchase::findOrFail($request->purchase_id);
            $remaining = (float) $purchase->final_amount - (float) $purchase->total_paid;

            if ($remaining <= 0.005) {
                return response()->json(['success' => false, 'message' => 'Cet achat est déjà entièrement soldé.'], 400);
            }

            $requestedAmount  = (float) $request->amount;
            $allowOverpayment = in_array($request->payment_method, ['cash', 'bank_transfer', 'credit_card']);

            if (!$allowOverpayment && $requestedAmount > $remaining + 0.005) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant dépasse le reste à payer (' . number_format($remaining, 2, ',', '.') . ' DH)'
                ], 400);
            }

            $paymentAmount = min($requestedAmount, $remaining);
            $excess        = max(0, $requestedAmount - $remaining);

            $filePath = null;
            $originalFilename = null;
            if ($request->hasFile('payment_file')) {
                $file = $request->file('payment_file');
                $originalFilename = $file->getClientOriginalName();
                $filePath = $file->store('payment-documents/' . date('Y/m'), 'public');
            }

            // Resolve check before creating the payment doc
            $check    = null;
            $checkId  = null;
            $traiteId = null;

            if ($request->payment_method === 'check') {
                $check = Check::find($request->check_id);
                if (!$check) {
                    throw new \Exception('Chèque non trouvé');
                }

                if (CheckAllocation::where('check_id', $check->check_id)->exists()) {
                    throw new \Exception('Ce chèque a déjà été utilisé');
                }

                $checkId = $check->check_id;
            }

            if ($request->payment_method === 'traite') {
                if ($request->traite_id) {
                    $traite = Traite::findOrFail($request->traite_id);
                    $traite->status       = 'paid';
                    $traite->payment_date = now();
                    $traite->save();
                    $traiteId = $traite->traite_id;
                } else {
                    $traiteNumber = $request->traite_number ?: ('TR-FOUR-' . date('Ymd') . '-' . str_pad(Traite::count() + 1, 4, '0', STR_PAD_LEFT));
                    $newTraite = Traite::create([
                        'traite_number' => $traiteNumber,
                        'order_id'      => null,
                        'client_id'     => null,
                        'amount'        => $request->amount,
                        'issue_date'    => $request->payment_date,
                        'due_date'      => $request->traite_due_date ?? $request->payment_date,
                        'bank_name'     => $request->traite_bank,
                        'notes'         => 'Traite fournisseur – achat #' . $purchase->purchase_number,
                        'status'        => 'paid',
                        'payment_date'  => now(),
                        'created_by'    => auth()->id(),
                    ]);
                    $traiteId = $newTraite->traite_id;
                }
            }

            PurchasePaymentDocument::create([
                'purchase_id'     => $purchase->purchase_id,
                'document_number' => PurchasePaymentDocument::generateDocumentNumber(),
                'document_type'   => $request->payment_method,
                'check_id'        => $checkId,
                'traite_id'       => $traiteId,
                'file_path'       => $filePath,
                'original_filename' => $originalFilename,
                'amount'          => $paymentAmount,
                'payment_method'  => $request->payment_method,
                'payment_date'    => $request->payment_date,
                'notes'           => $request->notes,
                'uploaded_by'     => auth()->id(),
            ]);

            if ($check) {
                CheckAllocation::create([
                    'check_id'         => $check->check_id,
                    'purchase_id'      => $purchase->purchase_id,
                    'allocated_amount' => $paymentAmount,
                    'notes'            => $request->notes,
                ]);

                $check->remaining_amount = max(0, $check->remaining_amount - $paymentAmount);
                if ($check->remaining_amount <= 0) $check->status = 'allocated';
                $check->deposit_date  = $request->payment_date;
                $check->clearing_date = $request->payment_date;
                $check->save();
            }

            // total_paid accessor queries payment documents (new doc already inserted above)
            $newTotalPaid = (float) $purchase->total_paid;
            $paymentStatus = 'pending';
            if ($newTotalPaid >= $purchase->final_amount) {
                $paymentStatus = 'paid';
            } elseif ($newTotalPaid > 0) {
                $paymentStatus = 'partial';
            }

            $purchase->update([
                'paid_amount'    => $newTotalPaid,
                'payment_status' => $paymentStatus,
            ]);

            // Update supplier balance
            $supplier = Supplier::findOrFail($purchase->supplier_id);

            // Decrement balance by payment amount (only for purchases tracked in balance history)
            $isPurchaseTracked = DB::table('supplier_balance_history')
                ->where('supplier_id', $purchase->supplier_id)
                ->where('reference_id', $purchase->purchase_id)
                ->whereIn('type', ['purchase_unpaid', 'purchase_created'])
                ->exists();

            if ($isPurchaseTracked && $paymentAmount > 0.005) {
                $previousBalance = (float) $supplier->balance;
                $newBalance      = $previousBalance - $paymentAmount;
                $supplier->update(['balance' => $newBalance]);
                $supplier->balanceHistory()->create([
                    'previous_balance' => $previousBalance,
                    'new_balance'      => $newBalance,
                    'amount'           => -$paymentAmount,
                    'type'             => 'payment_added',
                    'reference_type'   => 'purchase',
                    'reference_id'     => $purchase->purchase_id,
                    'description'      => "Paiement achat #{$purchase->purchase_number}: " . number_format($paymentAmount, 2) . " DH",
                    'created_by'       => auth()->id(),
                ]);
                $supplier->refresh();
            }

            // Credit overpayment excess to supplier balance
            if ($excess > 0.005) {
                $previousBalance = (float) $supplier->balance;
                $newBalance      = $previousBalance - $excess;
                $supplier->update(['balance' => $newBalance]);
                $supplier->balanceHistory()->create([
                    'previous_balance' => $previousBalance,
                    'new_balance'      => $newBalance,
                    'amount'           => -$excess,
                    'type'             => 'overpayment_credit',
                    'reference_type'   => 'purchase',
                    'reference_id'     => $purchase->purchase_id,
                    'description'      => "Excédent paiement achat #{$purchase->purchase_number}: " . number_format($excess, 2) . " DH crédité en solde",
                    'created_by'       => auth()->id(),
                ]);
            }

            DB::commit();

            $message = 'Paiement ajouté avec succès!';
            if ($excess > 0.005) {
                $message .= ' Excédent de ' . number_format($excess, 2, ',', '.') . ' DH crédité en solde fournisseur.';
            }

            return response()->json([
                'success'        => true,
                'message'        => $message,
                'new_total_paid' => number_format($newTotalPaid, 2, ',', '.'),
                'remaining'      => number_format($purchase->final_amount - $newTotalPaid, 2, ',', '.'),
                'payment_status' => $paymentStatus,
                'excess_credited' => $excess > 0.005 ? number_format($excess, 2, ',', '.') : null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAvailableChecks(Request $request)
    {
        $type = $request->get('type');

        // Collect all used check IDs from both allocations and payment documents
        $usedViaAllocations = CheckAllocation::pluck('check_id');
        $usedViaDocs        = PurchasePaymentDocument::whereNotNull('check_id')->pluck('check_id');
        $usedCheckIds       = $usedViaAllocations->merge($usedViaDocs)->unique();

        $query = Check::where('is_active', true)
            ->whereIn('status', ['pending', 'deposited', 'cleared'])
            ->where('remaining_amount', '>', 0)
            ->whereNotIn('check_id', $usedCheckIds);

        if ($type) {
            $query->where('check_type', $type);
        }

        $checks = $query->get()
            ->filter(function ($check) {
                return $check->available_amount > 0;
            })
            ->map(function ($check) {
                return [
                    'check_id'        => $check->check_id,
                    'check_number'    => $check->check_number,
                    'bank_name'       => $check->bank_name,
                    'amount'          => $check->amount,
                    'available_amount' => $check->available_amount,
                    'issue_date'      => $check->issue_date ? $check->issue_date->format('Y-m-d') : null,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $checks
        ]);
    }

    public function getAvailableTraites()
    {
        $traites = Traite::where('status', 'pending')
            ->whereNull('order_id')
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($traite) {
                return [
                    'traite_id'     => $traite->traite_id,
                    'traite_number' => $traite->traite_number,
                    'bank_name'     => $traite->bank_name,
                    'amount'        => (float) $traite->amount,
                    'issue_date'    => $traite->issue_date ? $traite->issue_date->format('Y-m-d') : null,
                    'due_date'      => $traite->due_date ? $traite->due_date->format('Y-m-d') : null,
                ];
            })
            ->values();

        return response()->json(['success' => true, 'data' => $traites]);
    }

    public function storeCheck(Request $request)
    {
        $request->validate([
            'check_type' => 'required|in:entreprise,client',
            'check_number' => 'required|string|unique:checks,check_number',
            'bank_name' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'payee' => 'nullable|string',
            'check_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $filePath = null;
            if ($request->hasFile('check_file')) {
                $file = $request->file('check_file');
                $filePath = $file->store('checks/' . date('Y/m'), 'public');
            }

            $check = Check::create([
                'check_number' => $request->check_number,
                'check_type' => $request->check_type,
                'bank_name' => $request->bank_name,
                'amount' => $request->amount,
                'remaining_amount' => $request->amount,
                'account_holder' => $request->payee ?? auth()->user()->name,
                'issue_date' => $request->issue_date,
                'deposit_date' => $request->issue_date,
                'clearing_date' => $request->due_date,
                'check_image' => $filePath,
                'status' => 'pending',
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Chèque ajouté avec succès',
                'check_id' => $check->check_id,
                'check_number' => $check->check_number,
                'amount' => $check->amount,
                'available_amount' => $check->remaining_amount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showReceiptForm($id)
    {
        $purchase = RawMaterialPurchase::with(['items.rawMaterial', 'supplier', 'magazine'])->findOrFail($id);
        return view('pages.raw-material-purchases.receipt', compact('purchase'));
    }

    public function processReceipt(Request $request, $id)
    {
        $request->validate([
            'actual_delivery_date' => 'required|date',
            'items' => 'required|array',
            'items.*.purchase_item_id' => 'required|exists:raw_material_purchase_items,purchase_item_id',
            'items.*.received_quantity' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $purchase = RawMaterialPurchase::findOrFail($id);

            if ($purchase->actual_delivery_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette commande a déjà été livrée.'
                ], 400);
            }

            foreach ($request->items as $itemData) {
                $item = RawMaterialPurchaseItem::find($itemData['purchase_item_id']);
                if ($item && $itemData['received_quantity'] > $item->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "La quantité reçue pour {$item->rawMaterial->material_name} ne peut pas dépasser la quantité commandée."
                    ], 400);
                }
            }

            $purchase->update(['actual_delivery_date' => $request->actual_delivery_date]);

            foreach ($request->items as $itemData) {
                $item = RawMaterialPurchaseItem::find($itemData['purchase_item_id']);
                if ($item && $itemData['received_quantity'] > 0) {
                    $item->received_quantity = $itemData['received_quantity'];
                    $item->save();

                    $material = RawMaterial::find($item->material_id);
                    if ($material) {
                        $oldStock = $material->current_stock;
                        $newStock = $oldStock + $itemData['received_quantity'];

                        $stockMovement = RawMaterialStockMovement::create([
                            'material_id' => $item->material_id,
                            'movement_type' => 'purchase',
                            'quantity' => $itemData['received_quantity'],
                            'previous_stock' => $oldStock,
                            'new_stock' => $newStock,
                            'reference_type' => 'purchase',
                            'reference_id' => $purchase->purchase_id,
                            'reference_number' => $purchase->purchase_number,
                            'movement_date' => now(),
                            'performed_by' => auth()->id(),
                            'notes' => 'Réception commande ' . $purchase->purchase_number,
                        ]);

                        StockMovementDetail::create([
                            'stock_movement_id' => $stockMovement->movement_id,
                            'material_id' => $item->material_id,
                            'quantity' => $itemData['received_quantity'],
                            'unit_price' => $item->unit_price,
                            'total_price' => $itemData['received_quantity'] * $item->unit_price,
                            'remaining_quantity' => $itemData['received_quantity'],
                        ]);

                        $material->current_stock = $newStock;
                        $material->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Réception enregistrée avec succès!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePaymentDocument(Request $request, $documentId)
    {
        $request->validate([
            'amount'          => 'required|numeric|min:0.01',
            'payment_date'    => 'required|date',
            'payment_method'  => 'required|in:cash,check,bank_transfer,traite,transfer,credit_card',
            'notes'           => 'nullable|string|max:1000',
            'document'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'check_id'        => 'nullable|exists:checks,check_id',
            'traite_id'       => 'nullable|exists:traites,traite_id',
            'traite_number'   => 'nullable|string|max:50',
            'traite_due_date' => 'nullable|date',
            'traite_bank'     => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $doc = PurchasePaymentDocument::findOrFail($documentId);
            $purchase = $doc->purchase;
            $oldAmount = $doc->amount;
            $oldMethod = $doc->payment_method;

            // Check payments may only be corrected to cash
            if ($oldMethod === 'check' && !in_array($request->payment_method, ['check', 'cash'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un paiement par chèque ne peut être modifié qu\'en espèces.',
                ], 400);
            }

            // Validate new amount doesn't exceed remaining + old amount
            $otherPaid = $purchase->paymentDocuments()
                ->where('document_id', '!=', $doc->document_id)
                ->sum('amount');
            $maxAllowed = $purchase->final_amount - $otherPaid;
            if ($request->amount > $maxAllowed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Montant trop élevé. Maximum: ' . number_format($maxAllowed, 2, ',', '.') . ' DH'
                ], 400);
            }

            // Handle file upload (replaces existing)
            $filePath = $doc->file_path;
            $originalFilename = $doc->original_filename;
            if ($request->hasFile('document')) {
                if ($filePath) Storage::disk('public')->delete($filePath);
                $file = $request->file('document');
                $originalFilename = $file->getClientOriginalName();
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('payment-documents/' . date('Y/m'), $filename, 'public');
            }

            // If changing method away from check, release check allocation
            if ($oldMethod === 'check' && $request->payment_method !== 'check') {
                $allocation = CheckAllocation::where('purchase_id', $purchase->purchase_id)
                    ->where('allocated_amount', $oldAmount)
                    ->first();
                if ($allocation) {
                    $check = $allocation->check;
                    $check->remaining_amount += $allocation->allocated_amount;
                    if ($check->status === 'allocated') $check->status = 'deposited';
                    $check->save();
                    $allocation->delete();
                }
            }

            // If switching TO check, create new allocation
            if ($request->payment_method === 'check' && $oldMethod !== 'check') {
                $check = Check::findOrFail($request->check_id);
                CheckAllocation::create([
                    'check_id'         => $check->check_id,
                    'purchase_id'      => $purchase->purchase_id,
                    'allocated_amount' => $request->amount,
                    'notes'            => $request->notes ?? 'Modification paiement',
                ]);
                $check->remaining_amount = $check->amount - $check->allocations()->sum('allocated_amount');
                if ($check->remaining_amount <= 0) $check->status = 'allocated';
                $check->clearing_date = now();
                $check->save();
            }

            // If switching TO traite, create/use traite record
            if ($request->payment_method === 'traite' && $oldMethod !== 'traite') {
                if ($request->traite_id) {
                    Traite::findOrFail($request->traite_id)->update(['status' => 'paid', 'payment_date' => now()]);
                } else {
                    $traiteNumber = $request->traite_number ?: ('TR-FOUR-' . date('Ymd') . '-' . str_pad(Traite::count() + 1, 4, '0', STR_PAD_LEFT));
                    Traite::create([
                        'traite_number' => $traiteNumber,
                        'order_id'      => null,
                        'client_id'     => null,
                        'amount'        => $request->amount,
                        'issue_date'    => $request->payment_date,
                        'due_date'      => $request->traite_due_date ?? $request->payment_date,
                        'bank_name'     => $request->traite_bank,
                        'notes'         => 'Traite fournisseur – achat #' . $purchase->purchase_number . ' (modification)',
                        'status'        => 'paid',
                        'payment_date'  => now(),
                        'created_by'    => auth()->id(),
                    ]);
                }
            }

            $doc->update([
                'payment_method' => $request->payment_method,
                'document_type' => $request->payment_method,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
                'file_path' => $filePath,
                'original_filename' => $originalFilename,
            ]);

            // Recalculate purchase paid_amount
            $totalPaid = $purchase->paymentDocuments()->sum('amount');
            $purchase->paid_amount = $totalPaid;
            if ($totalPaid <= 0) {
                $purchase->payment_status = 'pending';
            } elseif ($totalPaid >= $purchase->final_amount - 0.01) {
                $purchase->payment_status = 'paid';
            } else {
                $purchase->payment_status = 'partial';
            }
            $purchase->save();

            // Sync supplier balance with the change in amount (only for payments that were tracked when added)
            $amountDelta = (float) $request->amount - (float) $oldAmount;
            if (abs($amountDelta) > 0.005) {
                $isPaymentTracked = DB::table('supplier_balance_history')
                    ->where('reference_id', $purchase->purchase_id)
                    ->where('type', 'payment_added')
                    ->exists();

                if ($isPaymentTracked) {
                    $supplier        = $purchase->supplier;
                    $previousBalance = (float) $supplier->balance;
                    $newBalance      = $previousBalance - $amountDelta;
                    $supplier->update(['balance' => $newBalance]);
                    $supplier->balanceHistory()->create([
                        'previous_balance' => $previousBalance,
                        'new_balance'      => $newBalance,
                        'amount'           => -$amountDelta,
                        'type'             => 'payment_updated',
                        'reference_type'   => 'purchase',
                        'reference_id'     => $purchase->purchase_id,
                        'description'      => "Paiement modifié sur achat #{$purchase->purchase_number}: " .
                            number_format($oldAmount, 2, ',', '.') . ' → ' . number_format($request->amount, 2, ',', '.') . ' DH',
                        'created_by'       => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document de paiement mis à jour avec succès.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deletePaymentDocument($documentId)
    {
        DB::beginTransaction();
        try {
            $doc = PurchasePaymentDocument::findOrFail($documentId);
            $purchase = $doc->purchase;
            $deletedAmount = (float) $doc->amount;

            // Mark check/traite as bounced on deletion
            if ($doc->payment_method === 'check') {
                $allocation = CheckAllocation::where('purchase_id', $purchase->purchase_id)
                    ->where('allocated_amount', $doc->amount)
                    ->first();
                if ($allocation) {
                    $check = $allocation->check;
                    $check->status = 'bounced';
                    $check->save();
                    $allocation->delete();
                }
            }

            if ($doc->payment_method === 'traite' && $doc->traite_id) {
                $traite = Traite::find($doc->traite_id);
                if ($traite) {
                    $traite->status = 'bounced';
                    $traite->save();
                }
            }

            // Delete file
            if ($doc->file_path) {
                Storage::disk('public')->delete($doc->file_path);
            }

            $doc->delete();

            // Recalculate purchase paid_amount
            $totalPaid = $purchase->paymentDocuments()->sum('amount');
            $purchase->paid_amount = $totalPaid;
            if ($totalPaid <= 0) {
                $purchase->payment_status = 'pending';
            } elseif ($totalPaid >= $purchase->final_amount - 0.01) {
                $purchase->payment_status = 'paid';
            } else {
                $purchase->payment_status = 'partial';
            }
            $purchase->save();

            // Removing the payment means the supplier is owed that amount again (tracked payments only)
            if ($deletedAmount > 0.005) {
                $isPaymentTracked = DB::table('supplier_balance_history')
                    ->where('reference_id', $purchase->purchase_id)
                    ->where('type', 'payment_added')
                    ->exists();

                if ($isPaymentTracked) {
                    $supplier        = $purchase->supplier;
                    $previousBalance = (float) $supplier->balance;
                    $newBalance      = $previousBalance + $deletedAmount;
                    $supplier->update(['balance' => $newBalance]);
                    $supplier->balanceHistory()->create([
                        'previous_balance' => $previousBalance,
                        'new_balance'      => $newBalance,
                        'amount'           => $deletedAmount,
                        'type'             => 'payment_deleted',
                        'reference_type'   => 'purchase',
                        'reference_id'     => $purchase->purchase_id,
                        'description'      => "Paiement supprimé sur achat #{$purchase->purchase_number}: +" .
                            number_format($deletedAmount, 2, ',', '.') . ' DH',
                        'created_by'       => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document de paiement supprimé avec succès.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generatePdf($id)
    {
        $purchase = RawMaterialPurchase::with(['supplier', 'items.rawMaterial'])->findOrFail($id);
        // Implement PDF generation here
        return response()->json([
            'success' => true,
            'message' => 'PDF généré avec succès'
        ]);
    }
}
