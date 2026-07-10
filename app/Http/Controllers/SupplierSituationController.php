<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\RawMaterialPurchase;
use App\Models\PurchasePaymentDocument;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class SupplierSituationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_supplier_situation');
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
                    // Actual unpaid: sum only non-paid purchases, independent of date/status filters
                    DB::raw("(SELECT COALESCE(SUM(p2.final_amount - p2.paid_amount), 0) FROM raw_material_purchases p2 WHERE p2.supplier_id = suppliers.supplier_id AND p2.payment_status != 'paid') as actual_unpaid_rest")
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
                ->addColumn('balance_display', function($row) {
                    $b = (float) $row->balance;
                    if ($b < -0.01) {
                        return '<span class="text-success fw-bold">' . number_format(abs($b), 2, ',', '.') . ' DH <small>(crédit)</small></span>';
                    } elseif ($b > 0.01) {
                        return '<span class="text-danger fw-bold">' . number_format($b, 2, ',', '.') . ' DH <small>(dû)</small></span>';
                    }
                    return '<span class="text-muted">0,00 DH</span>';
                })
                ->addColumn('total_amount_display', function($row) {
                    return number_format($row->total_amount, 2, ',', '.') . ' DH';
                })
                ->addColumn('total_paid_display', function($row) {
                    return '<span class="text-success fw-bold">' . number_format($row->total_paid, 2, ',', '.') . ' DH</span>';
                })
                ->addColumn('total_rest_display', function($row) {
                    $actual = (float) ($row->actual_unpaid_rest ?? 0);
                    $class  = $actual > 0.01 ? 'text-danger' : 'text-success';
                    return '<span class="' . $class . ' fw-bold">' . number_format($actual, 2, ',', '.') . ' DH</span>';
                })
                ->addColumn('actions', function($row) {
                    $balance      = (float) ($row->balance ?? 0);
                    $actualUnpaid = (float) ($row->actual_unpaid_rest ?? 0);
                    $btn = '<div class="d-flex gap-1 flex-wrap justify-content-center">';
                    $btn .= '<button type="button" class="btn btn-sm btn-primary view-details-btn"
                                data-id="' . $row->supplier_id . '"
                                data-name="' . htmlspecialchars($row->supplier_name, ENT_QUOTES) . '"
                                data-balance="' . $balance . '"
                                title="Voir les achats">
                                <i class="fas fa-list me-1"></i>Détails
                            </button>';
                    if ($actualUnpaid > 0.01) {
                        $btn .= '<button type="button" class="btn btn-sm btn-success pay-supplier-all-btn"
                                    data-id="' . $row->supplier_id . '"
                                    data-name="' . htmlspecialchars($row->supplier_name, ENT_QUOTES) . '"
                                    data-rest="' . $actualUnpaid . '"
                                    data-balance="' . $balance . '"
                                    title="Paiement groupé FIFO">
                                    <i class="fas fa-money-bill-wave"></i>
                                </button>';
                    }
                    $btn .= '<button type="button" class="btn btn-sm btn-warning text-white add-balance-btn"
                                data-id="' . $row->supplier_id . '"
                                data-name="' . htmlspecialchars($row->supplier_name, ENT_QUOTES) . '"
                                data-balance="' . $balance . '"
                                title="Ajouter du solde">
                                <i class="fas fa-wallet"></i>
                            </button>';
                    $btn .= '<a href="' . route('suppliers.situation.supplier', $row->supplier_id) . '"
                                class="btn btn-sm btn-info" title="Situation complète">
                                <i class="fas fa-chart-line"></i>
                            </a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['balance_display', 'total_paid_display', 'total_rest_display', 'actions'])
                ->make(true);
        }

        $suppliers = Supplier::where('is_active', true)->orderBy('company_name')->get();

        // Get summary statistics
        $summary = [
            'total_purchases' => RawMaterialPurchase::count(),
            'total_amount' => RawMaterialPurchase::sum('final_amount'),
            'total_paid' => RawMaterialPurchase::sum('paid_amount'),
            'total_unpaid' => RawMaterialPurchase::sum(DB::raw('final_amount - paid_amount')),
            'pending_purchases' => RawMaterialPurchase::where('payment_status', 'pending')->count(),
            'partial_purchases' => RawMaterialPurchase::where('payment_status', 'partial')->count(),
            'paid_purchases' => RawMaterialPurchase::where('payment_status', 'paid')->count(),
        ];

        return view('pages.suppliers.situation.index', compact('suppliers', 'summary'));
    }

    public function supplierSituation($supplierId)
    {
        $supplier = Supplier::with([
            'purchases' => function($query) {
                $query->orderBy('purchase_date', 'desc');
            },
            'purchases.items',
            'purchases.paymentDocuments',
            'balanceHistory' => function($query) {
                $query->with('creator')->limit(50);
            }
        ])->findOrFail($supplierId);

        $purchases = $supplier->purchases()->orderBy('purchase_date', 'desc')->paginate(20);
        $paymentMethods = $supplier->payment_methods_summary;

        return view('pages.suppliers.situation.supplier', compact('supplier', 'purchases', 'paymentMethods'));
    }

    public function getSupplierPurchases(Request $request, $supplierId)
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

        $purchases = $query->with('paymentDocuments')->orderBy('purchase_date', 'desc')->get();

        $methodLabels = [
            'cash'          => 'Espèces',
            'bank_transfer' => 'Virement',
            'check'         => 'Chèque',
            'traite'        => 'Traite',
            'credit_card'   => 'Carte',
            'balance'       => 'Solde',
        ];

        $data = $purchases->map(function($purchase) use ($methodLabels) {
            $rest = $purchase->final_amount - $purchase->total_paid;

            $docs = $purchase->paymentDocuments->map(function($doc) use ($methodLabels) {
                return [
                    'document_id'      => $doc->document_id,
                    'document_number'  => $doc->document_number,
                    'amount'           => (float) $doc->amount,
                    'amount_display'   => number_format($doc->amount, 2, ',', '.'),
                    'payment_method'   => $doc->payment_method,
                    'method_label'     => $methodLabels[$doc->payment_method] ?? ucfirst($doc->payment_method),
                    'payment_date'     => $doc->payment_date ? $doc->payment_date->format('d/m/Y') : '—',
                    'payment_date_raw' => $doc->payment_date ? $doc->payment_date->format('Y-m-d') : '',
                    'notes'            => $doc->notes ?? '',
                ];
            })->values()->toArray();

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
                'payment_documents'    => $docs,
            ];
        });

        return response()->json([
            'success'  => true,
            'supplier' => $supplier->display_name,
            'data'     => $data,
        ]);
    }

    public function export(Request $request)
    {
        $query = RawMaterialPurchase::with(['supplier']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        $purchases = $query->orderBy('purchase_date', 'desc')->get();

        $filename = 'situation_fournisseur_' . date('Y-m-d_His') . '.csv';

        $headers = [
            "Content-type" => "text/csv; charset=utf-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Date', 'N° Achat', 'Fournisseur', 'Montant Total', 'Payé', 'Reste', 'Statut'];

        $callback = function() use ($purchases, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($purchases as $purchase) {
                $row = [
                    $purchase->purchase_date->format('d/m/Y'),
                    $purchase->purchase_number,
                    $purchase->supplier ? ($purchase->supplier->company_name ?? $purchase->supplier->full_name) : 'N/A',
                    number_format($purchase->final_amount, 2, ',', '.') . ' DH',
                    number_format($purchase->total_paid, 2, ',', '.') . ' DH',
                    number_format($purchase->final_amount - $purchase->total_paid, 2, ',', '.') . ' DH',
                    $purchase->payment_status == 'pending' ? 'Non Payé' :
                        ($purchase->payment_status == 'partial' ? 'Avance' : 'Payé'),
                ];
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getSupplierBalance($supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $availableCredit = max(0, -$supplier->balance);

        return response()->json([
            'success'          => true,
            'balance'          => (float) $supplier->balance,
            'available_credit' => $availableCredit,
            'balance_formatted' => number_format(abs($supplier->balance), 2, ',', '.') . ' DH',
            'balance_status'   => $supplier->balance_status,
        ]);
    }

    public function addSupplierBalance(Request $request, $supplierId)
    {
        $request->validate([
            'amount'       => ['required', 'numeric', function ($attr, $value, $fail) {
                if (abs((float) $value) < 0.01) $fail('Le montant doit être différent de zéro.');
            }],
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $supplier = Supplier::findOrFail($supplierId);
            $previousBalance = (float) $supplier->balance;
            $newBalance      = $previousBalance - (float) $request->amount;

            $supplier->update(['balance' => $newBalance]);

            $supplier->balanceHistory()->create([
                'previous_balance' => $previousBalance,
                'new_balance'      => $newBalance,
                'amount'           => -(float) $request->amount,
                'type'             => 'direct_payment',
                'reference_type'   => 'direct',
                'reference_id'     => 0,
                'description'      => $request->notes ?? 'Paiement direct fournisseur (solde)',
                'created_by'       => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Solde mis à jour avec succès! Nouveau solde: ' . number_format(abs($newBalance), 2, ',', '.') . ' DH',
                'new_balance' => $newBalance,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function payByBalance(Request $request, $supplierId)
    {
        $request->validate([
            'purchase_id'  => 'required|exists:raw_material_purchases,purchase_id',
            'amount'       => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $supplier = Supplier::findOrFail($supplierId);
            $purchase = RawMaterialPurchase::where('supplier_id', $supplierId)
                ->findOrFail($request->purchase_id);

            $availableCredit = max(0, -(float) $supplier->balance);
            if ($availableCredit <= 0) {
                return response()->json(['success' => false, 'message' => 'Aucun solde disponible pour ce fournisseur.'], 400);
            }

            $remaining   = (float) $purchase->final_amount - (float) $purchase->total_paid;
            $applyAmount = min((float) $request->amount, $remaining, $availableCredit);

            if ($applyAmount <= 0.005) {
                return response()->json(['success' => false, 'message' => 'Montant invalide ou achat déjà soldé.'], 400);
            }

            PurchasePaymentDocument::create([
                'purchase_id'       => $purchase->purchase_id,
                'document_number'   => PurchasePaymentDocument::generateDocumentNumber(),
                'document_type'     => 'balance',
                'file_path'         => null,
                'original_filename' => null,
                'amount'            => $applyAmount,
                'payment_method'    => 'balance',
                'payment_date'      => $request->payment_date,
                'notes'             => 'Paiement par solde fournisseur',
                'uploaded_by'       => auth()->id(),
            ]);

            $newPaid   = (float) $purchase->total_paid + $applyAmount;
            $newStatus = $newPaid >= (float) $purchase->final_amount - 0.01 ? 'paid' : ($newPaid > 0 ? 'partial' : 'pending');
            $purchase->update(['paid_amount' => $newPaid, 'payment_status' => $newStatus]);

            $previousBalance = (float) $supplier->balance;
            $newBalance      = $previousBalance + $applyAmount;
            $supplier->update(['balance' => $newBalance]);

            $supplier->balanceHistory()->create([
                'previous_balance' => $previousBalance,
                'new_balance'      => $newBalance,
                'amount'           => $applyAmount,
                'type'             => 'payment_from_balance',
                'reference_type'   => 'purchase',
                'reference_id'     => $purchase->purchase_id,
                'description'      => "Paiement achat #{$purchase->purchase_number} par solde",
                'created_by'       => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => number_format($applyAmount, 2, ',', '.') . ' DH payé par solde sur achat #' . $purchase->purchase_number,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function printSupplierSituation(Request $request, $supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        $purchasesQuery = RawMaterialPurchase::where('supplier_id', $supplierId)
            ->with(['paymentDocuments' => function ($q) {
                $q->orderBy('payment_date')->orderBy('document_id');
            }])
            ->orderBy('purchase_date')
            ->orderBy('purchase_id');

        if ($dateFrom) {
            $purchasesQuery->whereDate('purchase_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $purchasesQuery->whereDate('purchase_date', '<=', $dateTo);
        }

        $purchases = $purchasesQuery->get();

        $methodLabels = [
            'cash'          => 'ESPÈCES',
            'bank_transfer' => 'VIREMENT',
            'check'         => 'CHÈQUE',
            'traite'        => 'TRAITE',
            'credit_card'   => 'CARTE',
            'balance'       => 'SOLDE',
        ];

        $statusLabels = [
            'pending' => 'Non Payé',
            'partial' => 'Avance',
            'paid'    => 'Payé',
        ];

        $entries = [];

        foreach ($purchases as $purchase) {
            $entries[] = [
                'date'        => $purchase->purchase_date,
                'designation' => 'Achat : ' . $purchase->purchase_number,
                'debit'       => (float) $purchase->final_amount,
                'credit'      => 0.0,
                'etat'        => $statusLabels[$purchase->payment_status] ?? '',
                'mode'        => '',
            ];

            foreach ($purchase->paymentDocuments as $doc) {
                $entries[] = [
                    'date'        => $doc->payment_date,
                    'designation' => 'REG : ' . $doc->document_number,
                    'debit'       => 0.0,
                    'credit'      => (float) $doc->amount,
                    'etat'        => '',
                    'mode'        => $methodLabels[$doc->payment_method] ?? strtoupper($doc->payment_method),
                ];
            }
        }

        $runningBalance = 0.0;
        foreach ($entries as &$entry) {
            $runningBalance += $entry['debit'] - $entry['credit'];
            $entry['solde'] = $runningBalance;
        }
        unset($entry);

        $totalDebit  = array_sum(array_column($entries, 'debit'));
        $totalCredit = array_sum(array_column($entries, 'credit'));
        $finalSolde  = $totalDebit - $totalCredit;

        $printDate = now()->format('d/m/Y');
        $showLogo  = $request->query('show_logo', 1);

        $data = [
            'supplier'    => $supplier,
            'entries'     => $entries,
            'totalDebit'  => $totalDebit,
            'totalCredit' => $totalCredit,
            'finalSolde'  => $finalSolde,
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
            'printDate'   => $printDate,
            'showLogo'    => (bool) $showLogo,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.supplier-situation', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('situation-fournisseur-' . $supplier->supplier_id . '-' . date('Y-m-d') . '.pdf');
    }

    public function getStatistics()
    {
        $totalSuppliers = Supplier::count();
        $activeSuppliers = Supplier::where('is_active', true)->count();

        $totalPurchases = RawMaterialPurchase::sum('final_amount');
        $totalPaid = RawMaterialPurchase::sum('paid_amount');
        $totalUnpaid = $totalPurchases - $totalPaid;

        $suppliersWithBalance = Supplier::where('balance', '!=', 0)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_suppliers' => $totalSuppliers,
                'active_suppliers' => $activeSuppliers,
                'total_purchases' => number_format($totalPurchases, 2, ',', '.'),
                'total_paid' => number_format($totalPaid, 2, ',', '.'),
                'total_unpaid' => number_format($totalUnpaid, 2, ',', '.'),
                'suppliers_with_balance' => $suppliersWithBalance,
            ]
        ]);
    }
}
