<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Client;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\RawMaterialStockMovement;
use App\Models\StockMovementDetail;
use App\Models\Check;
use App\Models\Expense;
use App\Models\Famille;
use App\Models\ProductFamilleStock;
use App\Models\ProductStockMovement;
use App\Models\PurchasePaymentDocument;
use App\Models\SalesOrderItem;
use App\Models\SalesOrderPayment;
use App\Models\Traite;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SalesOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_sales_orders')->only(['index', 'show', 'getStatistics', 'getRevenueStatistics', 'getCashFlowStatistics', 'getTotalOrdersValue', 'getVolumeStatistics', 'getOrderItems', 'generateDeliveryNote']);
        $this->middleware('can:create_sales_orders')->only(['create', 'store', 'addPayment', 'deletePayment', 'createInvoice']);
        $this->middleware('can:edit_sales_orders')->only(['edit', 'update']);
        $this->middleware('can:delete_sales_orders')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $orders = SalesOrder::with(['client', 'items'])->select('sales_orders.*');

            if ($request->filled('date_from')) {
                $orders->whereDate('order_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $orders->whereDate('order_date', '<=', $request->date_to);
            }

            $orders->orderBy('order_date', 'desc')->orderBy('order_id', 'desc');

            return DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('client_name', function ($row) {
                    return $row->client->display_name;
                })
                ->addColumn('client_balance', function ($row) {
                    $b = (float) ($row->client->balance ?? 0);
                    if ($b > 0.01) {
                        return '<span class="text-success fw-bold">' . number_format($b, 2, ',', '.') . ' DH</span>';
                    } elseif ($b < -0.01) {
                        return '<span class="text-danger fw-bold">' . number_format(abs($b), 2, ',', '.') . ' DH <small>(dû)</small></span>';
                    }
                    return '<span class="text-muted">0,00 DH</span>';
                })
                ->filterColumn('client_name', function ($query, $keyword) {
                    $query->whereHas('client', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                            ->orWhere('entreprise_name', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('order_date_formatted', function ($row) {
                    return $row->order_date ? $row->order_date->format('d/m/Y') : '-';
                })
                ->addColumn('total_volume', function ($row) {
                    $totalVolume = 0;
                    $rawMaterialQtys = [];
                    foreach ($row->items as $item) {
                        if ($item->item_type == 'raw_material') {
                            $rawMaterial = \App\Models\RawMaterial::find($item->item_id);
                            $unit = $rawMaterial ? $rawMaterial->unit_of_measure : 'U';
                            if (!isset($rawMaterialQtys[$unit])) {
                                $rawMaterialQtys[$unit] = 0;
                            }
                            $rawMaterialQtys[$unit] += $item->quantity;
                        } else {
                            $product = \App\Models\Product::find($item->item_id);
                            if ($product) {
                                $volumePerUnit = $product->getVolumePerUnitInM3();
                                $totalVolume += $item->quantity * $volumePerUnit;
                            }
                        }
                    }

                    $parts = [];
                    if ($totalVolume > 0) {
                        $parts[] = '<span class="badge bg-info">' . number_format($totalVolume, 4) . ' m³</span>';
                    }
                    foreach ($rawMaterialQtys as $unit => $qty) {
                        $parts[] = '<span class="badge bg-warning text-dark">' . number_format($qty, 2) . ' ' . strtoupper($unit) . '</span>';
                    }
                    if (empty($parts)) {
                        return '<span class="badge bg-secondary">-</span>';
                    }
                    return implode(' ', $parts);
                })
                ->addColumn('action', function ($order) {
                    $user = auth()->user();
                    $btn = '<div class="dropdown dropstart">';
                    $btn .= ' <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>';
                    $btn .= '<ul class="dropdown-menu dropdown-menu-end">';
                    $btn .= '<li><a class="dropdown-item" href="' . route('sales.orders.show', $order->order_id) . '">
                                <i class="fas fa-eye me-2"></i>Voir</a></li>';
                    $btn .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="openDeliveryNoteModal(' . $order->order_id . ', \'' . $order->order_number . '\')">
                                <i class="fas fa-truck me-2"></i>Bon de livraison
                            </a></li>';
                    if ($user->can('edit_sales_orders')) {
                        $btn .= '<li><a class="dropdown-item" href="' . route('sales.orders.edit', $order->order_id) . '">
                                    <i class="fas fa-edit me-2"></i>Modifier</a></li>';
                    }
                    if ($user->can('delete_sales_orders')) {
                        $btn .= '<li><hr class="dropdown-divider"></li>';
                        $btn .= '<li><a class="dropdown-item delete" href="#" data-id="' . $order->order_id . '" data-number="' . $order->order_number . '">
                                    <i class="fas fa-trash text-danger me-2"></i>Supprimer</a></li>';
                    }
                    $btn .= '</ul></div>';
                    return $btn;
                })
                ->addColumn('payment_status_badge', function ($row) {
                    $badges = [
                        'pending' => 'danger',
                        'partial' => 'warning',
                        'paid' => 'success',
                    ];
                    $color = $badges[$row->payment_status] ?? 'secondary';
                    $labels = [
                        'pending' => 'Non Payé',
                        'partial' => 'Avance',
                        'paid' => 'Payé',
                    ];
                    $label = $labels[$row->payment_status] ?? $row->payment_status;
                    return '<span class="badge badge-' . $color . '">' . $label . '</span>';
                })
                ->editColumn('final_amount', function ($row) {
                    return number_format($row->final_amount, 2, ',', '.') . ' DH';
                })
                ->editColumn('paid_amount', function ($row) {
                    return number_format($row->paid_amount, 2, ',', '.') . ' DH';
                })
                ->rawColumns(['action', 'payment_status_badge', 'total_volume', 'client_balance'])
                ->make(true);
        }

        return view('pages.sales.orders.index');
    }

    /**
     * Calculate total volume of products sold in an order
     */
    private function calculateOrderVolume($orderId)
    {
        $order = SalesOrder::with('items')->find($orderId);
        $totalVolume = 0;

        foreach ($order->items as $item) {
            if ($item->item_type != 'raw_material') {
                $product = Product::find($item->item_id);
                if ($product) {
                    $volumePerUnit = $product->getVolumePerUnitInM3();
                    $totalVolume += $item->quantity * $volumePerUnit;
                }
            }
        }

        return $totalVolume;
    }

    /**
     * Get total volume statistics for sold products
     */
    public function getVolumeStatistics(Request $request)
    {
        try {
            $query = SalesOrder::with(['items']);

            if ($request->filled('date_from')) {
                $query->whereDate('order_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('order_date', '<=', $request->date_to);
            }

            $orders = $query->get();
            $totalVolume = 0;

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    if ($item->item_type != 'raw_material') {
                        $product = Product::find($item->item_id);
                        if ($product) {
                            $volumePerUnit = $product->getVolumePerUnitInM3();
                            $totalVolume += $item->quantity * $volumePerUnit;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_volume' => round($totalVolume, 4),
                    'total_volume_formatted' => number_format($totalVolume, 4) . ' m³'
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Volume statistics error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        $clients = Client::where('is_active', true)->get();
        $products = Product::where('is_active', true)->with(['familles'])->get();
        $rawMaterials = RawMaterial::where('is_active', true)->get();
        $familles = Famille::where('is_active', true)->orderBy('famille_name')->get();

        $nextOrderNumber = $this->generateNextOrderNumber();

        return view('pages.sales.orders.create', compact('clients', 'products', 'rawMaterials', 'familles', 'nextOrderNumber'));
    }

    private function generateNextOrderNumber()
    {
        $today = date('Ymd');
        $prefix = 'CMD-' . $today . '-';

        $lastOrder = SalesOrder::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastSequence = intval(substr($lastOrder->order_number, -4));
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get revenue statistics with date filters
     */
    public function getRevenueStatistics(Request $request)
    {
        try {
            $revenueQuery = SalesOrderPayment::leftJoin('sales_orders', 'sales_order_payments.order_id', '=', 'sales_orders.order_id');

            if ($request->filled('date_from')) {
                $revenueQuery->whereDate('sales_order_payments.payment_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $revenueQuery->whereDate('sales_order_payments.payment_date', '<=', $request->date_to);
            }

            $totalRevenue = (clone $revenueQuery)->where('sales_order_payments.payment_method', '!=', 'transfer')->sum('sales_order_payments.amount');
            $paymentsCount = $revenueQuery->count();

            $expenseQuery = Expense::query();
            if ($request->filled('date_from')) {
                $expenseQuery->whereDate('expense_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $expenseQuery->whereDate('expense_date', '<=', $request->date_to);
            }
            $totalExpenses = $expenseQuery->sum('amount');
            $expensesCount = $expenseQuery->count();

            $supplierPaymentQuery = PurchasePaymentDocument::query();
            if ($request->filled('date_from')) {
                $supplierPaymentQuery->whereDate('payment_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $supplierPaymentQuery->whereDate('payment_date', '<=', $request->date_to);
            }
            $totalSupplierPayments = $supplierPaymentQuery->sum('amount');
            $supplierPaymentsCount = $supplierPaymentQuery->count();

            $netRevenue = $totalRevenue - $totalExpenses - $totalSupplierPayments;

            return response()->json([
                'success' => true,
                'data' => [
                    'revenue' => $totalRevenue,
                    'expenses' => $totalExpenses,
                    'supplier_payments' => $totalSupplierPayments,
                    'net_revenue' => $netRevenue,
                    'payments_count' => $paymentsCount,
                    'expenses_count' => $expensesCount,
                    'supplier_payments_count' => $supplierPaymentsCount,
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get total orders value statistics (including unpaid)
     */
    public function getTotalOrdersValue(Request $request)
    {
        try {
            $dateFrom = $request->filled('date_from') ? $request->date_from : null;
            $dateTo   = $request->filled('date_to')   ? $request->date_to   : null;

            // Filter by order_date to match the DataTable view
            $orderQuery = SalesOrder::query();
            if ($dateFrom) $orderQuery->whereDate('order_date', '>=', $dateFrom);
            if ($dateTo)   $orderQuery->whereDate('order_date', '<=', $dateTo);

            $totalOrdersValue = $orderQuery->sum('final_amount');
            $paidValue        = $orderQuery->sum('paid_amount');
            $unpaidValue      = $orderQuery->sum(DB::raw('GREATEST(0, final_amount - paid_amount)'));

            return response()->json([
                'success' => true,
                'data' => [
                    'total_orders_value' => $totalOrdersValue,
                    'paid_value' => $paidValue,
                    'unpaid_value' => $unpaidValue,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed cash flow statistics
     */
    public function getCashFlowStatistics(Request $request)
    {
        try {
            $dateFrom = $request->filled('date_from') ? $request->date_from : null;
            $dateTo   = $request->filled('date_to')   ? $request->date_to   : null;

            // ── Sales income: amount + count per payment method ─────────
            // Include both order payments AND direct client payments
            // Exclude advance/avoir (they don't represent actual cash inflow)

            // 1. Order-linked payments (filtered by payment_date so virements added on a different day than the order are included)
            $orderIncomeQuery = SalesOrderPayment::join('sales_orders', 'sales_order_payments.order_id', '=', 'sales_orders.order_id')
                ->where('sales_order_payments.payment_method', '!=', 'advance')
                ->where('sales_order_payments.payment_method', '!=', 'avoir');
            if ($dateFrom) $orderIncomeQuery->whereDate('sales_order_payments.payment_date', '>=', $dateFrom);
            if ($dateTo)   $orderIncomeQuery->whereDate('sales_order_payments.payment_date', '<=', $dateTo);

            $orderIncomeRaw = $orderIncomeQuery
                ->select('sales_order_payments.payment_method', DB::raw('SUM(sales_order_payments.amount) as total'), DB::raw('COUNT(*) as cnt'))
                ->groupBy('sales_order_payments.payment_method')
                ->get();

            // 2. Direct client payments (no order, no credit note — filtered by payment_date)
            $directIncomeQuery = SalesOrderPayment::whereNull('order_id')
                ->whereNull('credit_note_id')
                ->whereNotNull('client_id')
                ->where('payment_method', '!=', 'advance')
                ->where('payment_method', '!=', 'avoir');
            if ($dateFrom) $directIncomeQuery->whereDate('payment_date', '>=', $dateFrom);
            if ($dateTo)   $directIncomeQuery->whereDate('payment_date', '<=', $dateTo);

            $directIncomeRaw = $directIncomeQuery
                ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as cnt'))
                ->groupBy('payment_method')
                ->get();

            // Merge results
            $salesIncome = [];
            foreach ($orderIncomeRaw as $row) {
                $salesIncome[$row->payment_method] = [
                    'total' => (float) ($salesIncome[$row->payment_method]['total'] ?? 0) + (float) $row->total,
                    'count' => (int)   ($salesIncome[$row->payment_method]['count'] ?? 0) + (int) $row->cnt,
                ];
            }
            foreach ($directIncomeRaw as $row) {
                $salesIncome[$row->payment_method] = [
                    'total' => (float) ($salesIncome[$row->payment_method]['total'] ?? 0) + (float) $row->total,
                    'count' => (int)   ($salesIncome[$row->payment_method]['count'] ?? 0) + (int) $row->cnt,
                ];
            }

            // ── Expenses: amount + count per payment method ─────────────
            $expensesQuery = Expense::query();
            if ($dateFrom) $expensesQuery->whereDate('expense_date', '>=', $dateFrom);
            if ($dateTo)   $expensesQuery->whereDate('expense_date', '<=', $dateTo);

            $expensesRaw = $expensesQuery
                ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as cnt'))
                ->groupBy('payment_method')
                ->get();

            $expenses = [];
            foreach ($expensesRaw as $row) {
                $expenses[$row->payment_method] = [
                    'total' => (float) $row->total,
                    'count' => (int)   $row->cnt,
                ];
            }

            // ── Supplier payments: amount + count per payment method ─────
            $supplierQuery = PurchasePaymentDocument::query();
            if ($dateFrom) $supplierQuery->whereDate('payment_date', '>=', $dateFrom);
            if ($dateTo)   $supplierQuery->whereDate('payment_date', '<=', $dateTo);

            $supplierRaw = $supplierQuery
                ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as cnt'))
                ->groupBy('payment_method')
                ->get();

            $supplierPayments = [];
            foreach ($supplierRaw as $row) {
                $supplierPayments[$row->payment_method] = [
                    'total' => (float) $row->total,
                    'count' => (int)   $row->cnt,
                ];
            }

            // ── Client cheques (check_type = 'client') ──────────────────
            $clientCheckQuery = Check::where('check_type', 'client');
            if ($dateFrom) $clientCheckQuery->whereDate('issue_date', '>=', $dateFrom);
            if ($dateTo)   $clientCheckQuery->whereDate('issue_date', '<=', $dateTo);

            $clientCheckRow = $clientCheckQuery
                ->select(DB::raw('COUNT(*) as cnt'), DB::raw('SUM(amount) as total'))
                ->first();

            $clientChecks = [
                'count' => (int)   ($clientCheckRow->cnt   ?? 0),
                'total' => (float) ($clientCheckRow->total ?? 0),
            ];

            // ── Entreprise cheque supplier payments (not part of caisse cheque balance) ──
            $supplierEntrCheckQuery = PurchasePaymentDocument::join('checks', 'purchase_payment_documents.check_id', '=', 'checks.check_id')
                ->where('checks.check_type', 'entreprise');
            if ($dateFrom) $supplierEntrCheckQuery->whereDate('purchase_payment_documents.payment_date', '>=', $dateFrom);
            if ($dateTo)   $supplierEntrCheckQuery->whereDate('purchase_payment_documents.payment_date', '<=', $dateTo);

            $supplierEntrCheckTotal = (float) $supplierEntrCheckQuery->sum('purchase_payment_documents.amount');

            // ── Totals ───────────────────────────────────────────────────
            $totalIn  = array_sum(array_column($salesIncome, 'total'));
            // Solde caisse excludes bank transfers (like getRevenueStatistics)
            $totalInCash = $totalIn - ($salesIncome['transfer']['total'] ?? 0);
            $totalExpenses       = array_sum(array_column($expenses,         'total'));
            $totalSupplierPayments = array_sum(array_column($supplierPayments, 'total'));
            $totalOut = $totalExpenses + $totalSupplierPayments;
            $netCashFlow = $totalInCash - $totalOut;

            return response()->json([
                'success' => true,
                'data' => [
                    'sales_income'             => $salesIncome,
                    'sales_income_total'       => $totalIn,
                    'expenses'                 => $expenses,
                    'expenses_total'           => $totalExpenses,
                    'supplier_payments'        => $supplierPayments,
                    'supplier_payments_total'  => $totalSupplierPayments,
                    'total_out'                => $totalOut,
                    'net_cash_flow'            => $netCashFlow,
                    'client_checks'                        => $clientChecks,
                    'supplier_entr_check_total'          => $supplierEntrCheckTotal,
                    'date_from'                           => $dateFrom,
                    'date_to'                             => $dateTo,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_number' => 'required|unique:sales_orders|max:50',
            'client_id' => 'required|exists:clients,client_id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:raw_material,production,decoupage,finale',
            'items.*.item_id' => 'required',
            'items.*.name' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.family_id' => 'nullable',
            'items.*.family_name' => 'nullable',
            'payments' => 'nullable|array',
            'payments.*.method' => 'required_with:payments|in:cash,check,transfer,traite,advance',
            'payments.*.amount' => 'required_with:payments|numeric|min:0.01',
            'payments.*.date' => 'required_with:payments|date',
            'payments.*.check_number' => 'required_if:payments.*.method,check|string',
            'payments.*.bank_name' => 'required_if:payments.*.method,check|string',
            'payments.*.account_holder' => 'required_if:payments.*.method,check|string',
            'payments.*.due_date' => 'required_if:payments.*.method,check|date',
            'payments.*.transfer_reference' => 'required_if:payments.*.method,transfer|string',
            'payments.*.account_number' => 'required_if:payments.*.method,transfer|string',
            'payments.*.traite_number' => 'required_if:payments.*.method,traite|string',
            'payments.*.drawee' => 'required_if:payments.*.method,traite|string',
            'payments.*.due_date' => 'required_if:payments.*.method,traite|date',
            'payments.*.advance_reference' => 'nullable|string',
            'bypass_credit' => 'nullable|in:1',
            'display_advance' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $client = Client::findOrFail($request->client_id);

            // Calculate order total
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $index => $itemData) {
                $quantity = (float) $itemData['quantity'];
                $unitPrice = (float) $itemData['unit_price'];
                $itemTotal = $quantity * $unitPrice;
                $totalAmount += $itemTotal;

                $itemsData[] = [
                    'item_type' => $itemData['type'],
                    'item_id' => $itemData['item_id'],
                    'item_code' => $itemData['code'] ?? null,
                    'item_name' => $itemData['name'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotal,
                    'family_id' => $itemData['family_id'] ?? null,
                    'family_name' => $itemData['family_name'] ?? null,
                ];
            }

            // Calculate total payments
            $totalPaid = 0;
            $advanceUsed = 0;

            if ($request->has('payments')) {
                foreach ($request->payments as $payment) {
                    $totalPaid += (float) $payment['amount'];
                    if ($payment['method'] === 'advance') {
                        $advanceUsed += (float) $payment['amount'];
                    }
                }
            }

            // Check if client has enough advance for advance payments
            if ($advanceUsed > 0 && $advanceUsed > $client->available_advance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solde insuffisant. Disponible: ' . $client->advance_formatted
                ], 400);
            }

            // Check credit for unpaid amount
            $unpaidAmount = $totalAmount - $totalPaid;
            // if (!$request->has('bypass_credit') && $unpaidAmount > 0) {
            //     $totalCreditNeeded = $client->credit_usage + $unpaidAmount;
            //     if ($totalCreditNeeded > $client->credit_limit) {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Limite de crédit dépassée. Utilisé: ' .
            //                         number_format($client->credit_usage, 2, ',', '.') . ' DH, ' .
            //                         'Limite: ' . number_format($client->credit_limit, 2, ',', '.') . ' DH'
            //         ], 400);
            //     }
            // }

            // Determine payment status
            if ($totalPaid >= $totalAmount - 0.01) {
                $paymentStatus = 'paid';
            } elseif ($totalPaid > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'pending';
            }

            $order = SalesOrder::create([
                'order_number' => $request->order_number,
                'client_id' => $request->client_id,
                'order_date' => $request->order_date,
                'total_amount' => $totalAmount,
                'final_amount' => $totalAmount,
                'paid_amount' => $totalPaid,
                'payment_status' => $paymentStatus,
                'notes' => $request->notes,
                'display_advance' => $request->display_advance,
                'created_by' => Auth::id(),
            ]);

            foreach ($itemsData as $itemData) {
                $order->items()->create($itemData);

                if ($itemData['item_type'] !== 'raw_material' && !empty($itemData['family_id'])) {
                    $this->updateProductStock($itemData, $order);
                }

                if ($itemData['item_type'] === 'raw_material' && !empty($itemData['item_id'])) {
                    $this->consumeRawMaterialStockFIFO(
                        $itemData['item_id'],
                        $itemData['quantity'],
                        $order
                    );
                }
            }

            if ($unpaidAmount > 0) {
                $client->useCredit($unpaidAmount, $order, 'Crédit utilisé pour vente');
            }

            if ($request->has('payments') && count($request->payments) > 0) {
                $paymentFiles = [];
                foreach ($request->allFiles() as $key => $file) {
                    if (preg_match('/payments\[(\d+)\]\[document\]/', $key, $matches)) {
                        $paymentIndex = $matches[1];
                        $paymentFiles[$paymentIndex] = $file;
                    }
                }

                foreach ($request->payments as $index => $paymentData) {
                    $filePath = null;
                    $originalFilename = null;

                    if (isset($paymentFiles[$index])) {
                        $file = $paymentFiles[$index];
                        $originalFilename = $file->getClientOriginalName();

                        $extension = $file->getClientOriginalExtension();
                        $filename = time() . '_' . uniqid() . '.' . $extension;

                        $filePath = $file->storeAs(
                            'payment-documents/' . date('Y/m'),
                            $filename,
                            'public'
                        );
                    }

                    switch ($paymentData['method']) {
                        case 'advance':
                            $this->processAdvancePayment($order, $client, $paymentData, $filePath, $originalFilename);
                            break;

                        case 'check':
                            $this->processCheckPayment($order, $paymentData, $filePath, $originalFilename);
                            break;

                        case 'transfer':
                            $this->processTransferPayment($order, $paymentData, $filePath, $originalFilename);
                            break;

                        case 'traite':
                            $this->processTraitePayment($order, $paymentData, $filePath, $originalFilename);
                            break;

                        case 'cash':
                            $this->processCashPayment($order, $paymentData, $filePath, $originalFilename);
                            break;
                    }
                }
            }

            // Update client balance based on order and payments
            $client->updateBalanceFromOrder($order, 'order_created');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vente créée avec succès!',
                'order_id' => $order->order_id,
                'order_number' => $order->order_number,
                'client_balance' => $client->balance,
                'client_balance_formatted' => $client->balance_formatted
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Order creation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la vente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process advance payment
     */
    private function processAdvancePayment($order, $client, $paymentData, $filePath, $originalFilename)
    {
        $advanceAmount = (float) $paymentData['amount'];

        if ($client->available_advance < $advanceAmount) {
            throw new \Exception('Solde insuffisant. Disponible: ' . $client->advance_formatted);
        }

        $payment = $order->payments()->create([
            'payment_method' => 'advance',
            'amount' => $advanceAmount,
            'payment_date' => $paymentData['date'],
            'document_path' => $filePath,
            'original_filename' => $originalFilename,
            'notes' => $paymentData['advance_reference'] ?? 'Utilisation solde client',
        ]);

        $client->useAdvance($advanceAmount, $order, $paymentData['advance_reference'] ?? null);
    }

    /**
     * Process check payment
     */
    private function processCheckPayment($order, $paymentData, $filePath, $originalFilename)
    {
        $payment = $order->payments()->create([
            'payment_method' => 'check',
            'amount' => $paymentData['amount'],
            'payment_date' => $paymentData['date'],
            'document_path' => $filePath,
            'original_filename' => $originalFilename,
            'notes' => $paymentData['notes'] ?? null,
        ]);

        $check = new Check();
        $check->check_number = $paymentData['check_number'];
        $check->check_type = 'client';
        $check->client_id = $order->client_id;
        $check->order_id = $order->order_id;
        $check->payment_id = $payment->payment_id;
        $check->amount = $paymentData['amount'];
        $check->remaining_amount = $paymentData['amount'];
        $check->bank_name = $paymentData['bank_name'];
        $check->account_holder = $paymentData['account_holder'];
        $check->issue_date = $paymentData['date'];
        $check->deposit_date = $paymentData['date'];
        $check->due_date = $paymentData['due_date'] ?? null;
        $check->check_image = $filePath;
        $check->status = 'deposited';
        $check->is_active = true;
        $check->created_by = Auth::id();
        $check->notes = 'Paiement pour vente ' . $order->order_number;
        $check->save();

        $payment->update([
            'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                "Chèque N°: {$paymentData['check_number']}, Banque: {$paymentData['bank_name']}"
        ]);
    }

    /**
     * Process transfer payment
     */
    private function processTransferPayment($order, $paymentData, $filePath, $originalFilename)
    {
        $payment = $order->payments()->create([
            'payment_method' => 'transfer',
            'amount' => $paymentData['amount'],
            'payment_date' => $paymentData['date'],
            'document_path' => $filePath,
            'original_filename' => $originalFilename,
            'notes' => $paymentData['notes'] ?? null,
        ]);

        $payment->update([
            'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                "Réf: {$paymentData['transfer_reference']}, " .
                "Compte: {$paymentData['account_number']}, " .
                "Banque: {$paymentData['bank_name']}"
        ]);
    }

    /**
     * Process traite payment
     */
    private function processTraitePayment($order, $paymentData, $filePath, $originalFilename)
    {
        $payment = $order->payments()->create([
            'payment_method' => 'traite',
            'amount' => $paymentData['amount'],
            'payment_date' => $paymentData['date'],
            'document_path' => $filePath,
            'original_filename' => $originalFilename,
            'notes' => $paymentData['notes'] ?? null,
        ]);

        $traiteData = [
            'traite_number' => $paymentData['traite_number'],
            'order_id' => $order->order_id,
            'payment_id' => $payment->payment_id,
            'client_id' => $order->client_id,
            'amount' => $paymentData['amount'],
            'issue_date' => $paymentData['date'],
            'due_date' => $paymentData['due_date'],
            'bank_name' => $paymentData['bank_name'] ?? null,
            'drawee' => $paymentData['drawee'],
            'drawee_address' => $paymentData['drawee_address'] ?? null,
            'notes' => $paymentData['notes'] ?? null,
            'status' => 'pending',
            'document_path' => $filePath,
            'original_filename' => $originalFilename,
            'created_by' => Auth::id(),
        ];

        Traite::create($traiteData);

        $payment->update([
            'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                "Traite N°: {$paymentData['traite_number']}, Échéance: " .
                \Carbon\Carbon::parse($paymentData['due_date'])->format('d/m/Y')
        ]);
    }

    /**
     * Process cash payment
     */
    private function processCashPayment($order, $paymentData, $filePath, $originalFilename)
    {
        $payment = $order->payments()->create([
            'payment_method' => 'cash',
            'amount' => $paymentData['amount'],
            'payment_date' => $paymentData['date'],
            'document_path' => $filePath,
            'original_filename' => $originalFilename,
            'notes' => $paymentData['notes'] ?? null,
        ]);

        if (!empty($paymentData['cash_reference'])) {
            $payment->update([
                'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                    "Réf: {$paymentData['cash_reference']}"
            ]);
        }
    }

    /**
     * Update product stock
     */
    private function updateProductStock($itemData, $salesOrder = null)
    {
        try {
            $familleStock = ProductFamilleStock::where('product_id', $itemData['item_id'])
                ->where('famille_id', $itemData['family_id'])
                ->first();

            $previousStock = $familleStock ? $familleStock->current_quantity : 0;

            if ($familleStock) {
                $familleStock->current_quantity -= $itemData['quantity'];
                $familleStock->available_quantity = $familleStock->current_quantity - $familleStock->reserved_quantity;
                $familleStock->last_updated = now();
                $familleStock->save();
            } else {
                ProductFamilleStock::create([
                    'product_id' => $itemData['item_id'],
                    'famille_id' => $itemData['family_id'],
                    'famille_name' => $itemData['family_name'],
                    'current_quantity' => -$itemData['quantity'],
                    'reserved_quantity' => 0,
                    'available_quantity' => -$itemData['quantity'],
                    'last_updated' => now(),
                ]);
            }

            $newStock = $familleStock ? $familleStock->fresh()->current_quantity : (-$itemData['quantity']);

            // Create product stock movement for the sale
            ProductStockMovement::create([
                'product_id' => $itemData['item_id'],
                'famille_id' => $itemData['family_id'] ?? null,
                'famille_name' => $itemData['family_name'] ?? null,
                'movement_type' => 'sales',
                'quantity' => -(float) $itemData['quantity'],
                'previous_stock' => (float) $previousStock,
                'new_stock' => (float) $newStock,
                'reference_type' => 'sales_order',
                'reference_id' => $salesOrder ? $salesOrder->order_id : null,
                'reference_number' => $salesOrder ? $salesOrder->order_number : null,
                'movement_date' => now(),
                'performed_by' => Auth::id(),
                'notes' => $salesOrder ? 'Vente - Commande #' . $salesOrder->order_number : 'Vente',
            ]);
        } catch (\Exception $e) {
            \Log::warning('Stock update warning: ' . $e->getMessage());
        }
    }

    private function consumeRawMaterialStockFIFO($materialId, $quantityNeeded, $salesOrder)
    {
        $stockDetails = StockMovementDetail::where('material_id', $materialId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('stock_movement_id', 'asc')
            ->get();

        $remainingToConsume = (float) $quantityNeeded;
        $totalCost = 0;
        $consumedDetails = [];

        foreach ($stockDetails as $detail) {
            if ($remainingToConsume <= 0) break;

            $quantityToTake = min((float) $detail->remaining_quantity, $remainingToConsume);
            $detail->remaining_quantity -= $quantityToTake;
            $detail->save();

            $remainingToConsume -= $quantityToTake;
            $totalCost += $quantityToTake * $detail->unit_price;
            $consumedDetails[] = [
                'stock_detail_id' => $detail->stock_detail_id,
                'quantity_consumed' => $quantityToTake,
                'unit_price' => $detail->unit_price,
                'total_cost' => $quantityToTake * $detail->unit_price,
            ];
        }

        $material = RawMaterial::find($materialId);
        $previousStock = (float) $material->current_stock;
        $newStock = $previousStock - (float) $quantityNeeded;

        $avgUnitCost = count($consumedDetails) > 0
            ? $totalCost / ((float) $quantityNeeded - $remainingToConsume)
            : ($material->average_unit_cost ?? 0);

        $stockMovement = RawMaterialStockMovement::create([
            'material_id'     => $materialId,
            'movement_type'   => 'sale',
            'quantity'        => -(float) $quantityNeeded,
            'previous_stock'  => $previousStock,
            'new_stock'       => $newStock,
            'reference_type'  => 'sales_order',
            'reference_number' => $salesOrder->order_number,
            'movement_date'   => now(),
            'performed_by'    => Auth::id(),
            'notes'           => 'Vente MP - Commande #' . $salesOrder->order_number,
        ]);

        foreach ($consumedDetails as $consumedDetail) {
            DB::table('stock_consumption_details')->insert([
                'stock_movement_id' => $stockMovement->movement_id,
                'stock_detail_id'   => $consumedDetail['stock_detail_id'],
                'quantity_consumed' => $consumedDetail['quantity_consumed'],
                'unit_price'        => $consumedDetail['unit_price'],
                'total_cost'        => $consumedDetail['total_cost'],
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        // If sold more than available, create a negative stock detail
        if ($remainingToConsume > 0) {
            $negativeDetail = StockMovementDetail::create([
                'stock_movement_id' => $stockMovement->movement_id,
                'material_id'       => $materialId,
                'quantity'          => -$remainingToConsume,
                'unit_price'        => $avgUnitCost,
                'total_price'       => - ($remainingToConsume * $avgUnitCost),
                'remaining_quantity' => -$remainingToConsume,
            ]);
        }

        $material->current_stock = $newStock;
        $material->save();

        Log::info('RM stock consumed for sales order', [
            'material_id' => $materialId,
            'order_number' => $salesOrder->order_number,
            'quantity_consumed' => $quantityNeeded,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
        ]);

        return $stockMovement;
    }

    public function show($id)
    {
        $order = SalesOrder::with(['client', 'creator', 'items', 'payments', 'creditNotes.items'])->findOrFail($id);
        return view('pages.sales.orders.show', compact('order'));
    }

    public function edit($id)
    {
        $order = SalesOrder::with(['client', 'items', 'payments'])->findOrFail($id);
        $clients = Client::where('is_active', true)->get();
        $products = Product::where('is_active', true)->with(['familles'])->get();
        $rawMaterials = RawMaterial::where('is_active', true)->get();

        return view('pages.sales.orders.edit', compact('order', 'clients', 'products', 'rawMaterials'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'order_number' => 'required|unique:sales_orders,order_number,' . $id . ',order_id|max:50',
            'client_id' => 'required|exists:clients,client_id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:raw_material,production,decoupage,finale',
            'items.*.item_id' => 'required',
            'items.*.name' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.family_id' => 'nullable',
            'items.*.family_name' => 'nullable',
            'payments' => 'nullable|array',
            'payments.*.method' => 'required_with:payments|in:cash,check,transfer,traite,advance',
            'payments.*.amount' => 'required_with:payments|numeric|min:0.01',
            'payments.*.date' => 'required_with:payments|date',
            'payments.*.check_number' => 'required_if:payments.*.method,check|string',
            'payments.*.bank_name' => 'required_if:payments.*.method,check|string',
            'payments.*.account_holder' => 'required_if:payments.*.method,check|string',
            'payments.*.due_date' => 'required_if:payments.*.method,check|date',
            'payments.*.transfer_reference' => 'required_if:payments.*.method,transfer|string',
            'payments.*.account_number' => 'required_if:payments.*.method,transfer|string',
            'payments.*.traite_number' => 'required_if:payments.*.method,traite|string',
            'payments.*.drawee' => 'required_if:payments.*.method,traite|string',
            'payments.*.due_date' => 'required_if:payments.*.method,traite|date',
            'payments.*.advance_reference' => 'nullable|string',
            'bypass_credit' => 'nullable|in:1',
            'display_advance' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $order = SalesOrder::findOrFail($id);
            $oldTotalAmount = $order->total_amount;
            $oldPaidAmount = $order->paid_amount;
            $oldClientId = $order->client_id;

            $client = Client::findOrFail($request->client_id);

            // Calculate new order total
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $index => $itemData) {
                $quantity = (float) $itemData['quantity'];
                $unitPrice = (float) $itemData['unit_price'];
                $itemTotal = $quantity * $unitPrice;
                $totalAmount += $itemTotal;

                $itemsData[] = [
                    'item_type' => $itemData['type'],
                    'item_id' => $itemData['item_id'],
                    'item_name' => $itemData['name'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotal,
                    'family_id' => $itemData['family_id'] ?? null,
                    'family_name' => $itemData['family_name'] ?? null,
                ];
            }

            // Calculate total payments (existing + new)
            $newPaymentsTotal = 0;
            $advanceUsed = 0;

            if ($request->has('payments')) {
                foreach ($request->payments as $payment) {
                    $newPaymentsTotal += (float) $payment['amount'];
                    if ($payment['method'] === 'advance') {
                        $advanceUsed += (float) $payment['amount'];
                    }
                }
            }

            $totalPaid = $oldPaidAmount + $newPaymentsTotal;

            // Check if client has enough advance for advance payments
            if ($advanceUsed > 0 && $advanceUsed > $client->available_advance) {
                throw new \Exception('Solde insuffisant. Disponible: ' . $client->advance_formatted);
            }

            // Calculate unpaid amount
            $unpaidAmount = $totalAmount - $totalPaid;

            // Get current credit usage from other orders (excluding this one)
            $otherOrdersUnpaid = $client->salesOrders()
                ->whereIn('payment_status', ['pending', 'partial'])
                ->where('order_id', '!=', $id)
                ->sum(DB::raw('final_amount - paid_amount'));

            $totalCreditNeeded = $otherOrdersUnpaid + $unpaidAmount;

            // Check credit limit - if exceeded, show warning but don't block
            $creditExceeded = $client->credit_limit > 0 && $totalCreditNeeded > $client->credit_limit;
            $excessAmount = $creditExceeded ? $totalCreditNeeded - $client->credit_limit : 0;

            // If bypass_credit is not set and credit is exceeded, ask for confirmation
            // if (!$request->has('bypass_credit') && $creditExceeded) {
            //     return response()->json([
            //         'success' => false,
            //         'credit_exceeded' => true,
            //         'message' => 'Limite de crédit dépassée de ' . number_format($excessAmount, 2, ',', '.') . ' DH',
            //         'details' => [
            //             'credit_limit' => $client->credit_limit,
            //             'credit_used' => $otherOrdersUnpaid,
            //             'new_credit_needed' => $unpaidAmount,
            //             'total_needed' => $totalCreditNeeded,
            //             'excess_amount' => $excessAmount,
            //             'client_balance' => $client->balance,
            //             'client_balance_formatted' => $client->balance_formatted
            //         ]
            //     ], 400);
            // }

            // Determine payment status
            if ($totalPaid >= $totalAmount - 0.01) {
                $paymentStatus = 'paid';
            } elseif ($totalPaid > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'pending';
            }

            // Store old values for balance calculation
            $oldUnpaidAmount = $oldTotalAmount - $oldPaidAmount;

            // Update order
            $order->update([
                'order_number' => $request->order_number,
                'client_id' => $request->client_id,
                'order_date' => $request->order_date,
                'total_amount' => $totalAmount,
                'final_amount' => $totalAmount,
                'paid_amount' => $totalPaid,
                'payment_status' => $paymentStatus,
                'notes' => $request->notes,
                'display_advance' => $request->display_advance,
                'updated_by' => Auth::id(),
            ]);

            // Restore stock from old items
            foreach ($order->items as $oldItem) {
                if ($oldItem->item_type !== 'raw_material' && !empty($oldItem->family_id)) {
                    $familleStock = ProductFamilleStock::where('product_id', $oldItem->item_id)
                        ->where('famille_id', $oldItem->family_id)
                        ->first();

                    if ($familleStock) {
                        $familleStock->current_quantity += $oldItem->quantity;
                        $familleStock->available_quantity = $familleStock->current_quantity - $familleStock->reserved_quantity;
                        $familleStock->last_updated = now();
                        $familleStock->save();
                    }
                }
            }

            // Delete old items and create new ones
            $order->items()->delete();

            foreach ($itemsData as $itemData) {
                $order->items()->create($itemData);

                if ($itemData['item_type'] !== 'raw_material' && !empty($itemData['family_id'])) {
                    try {
                        $familleStock = ProductFamilleStock::where('product_id', $itemData['item_id'])
                            ->where('famille_id', $itemData['family_id'])
                            ->first();

                        if ($familleStock) {
                            $familleStock->current_quantity -= $itemData['quantity'];
                            $familleStock->available_quantity = $familleStock->current_quantity - $familleStock->reserved_quantity;
                            $familleStock->last_updated = now();
                            $familleStock->save();
                        } else {
                            ProductFamilleStock::create([
                                'product_id' => $itemData['item_id'],
                                'famille_id' => $itemData['family_id'],
                                'famille_name' => $itemData['family_name'],
                                'current_quantity' => -$itemData['quantity'],
                                'reserved_quantity' => 0,
                                'available_quantity' => -$itemData['quantity'],
                                'last_updated' => now(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Stock update warning: ' . $e->getMessage());
                    }
                }
            }

            // Handle credit usage and balance
            $newUnpaidAmount = $totalAmount - $totalPaid;

            if ($oldClientId == $request->client_id) {
                // Same client - adjust credit usage and balance
                $unpaidDifference = $newUnpaidAmount - $oldUnpaidAmount;

                if ($unpaidDifference != 0) {
                    // Update credit usage (only up to credit limit, excess goes to balance)
                    $currentCreditUsage = $client->credit_usage;
                    $newCreditUsage = max(0, min($client->credit_limit, $otherOrdersUnpaid + $newUnpaidAmount));
                    $creditDifference = $newCreditUsage - $currentCreditUsage;

                    if ($creditDifference != 0) {
                        if ($creditDifference > 0) {
                            $client->useCredit($creditDifference, $order, 'Crédit supplémentaire pour modification vente');
                        } else {
                            $client->releaseCredit(abs($creditDifference), $order, 'Crédit libéré suite modification vente');
                        }
                    }

                    // Balance will be updated via updateBalanceFromOrder
                }
            } else {
                // Client changed - release credit/balance from old client, apply to new client
                if ($oldUnpaidAmount > 0) {
                    $oldClient = Client::find($oldClientId);
                    if ($oldClient) {
                        $oldClient->releaseCredit(min($oldClient->credit_usage, $oldUnpaidAmount), $order, 'Crédit libéré suite changement client');
                    }
                }

                if ($newUnpaidAmount > 0) {
                    $creditToUse = min($client->credit_limit - $otherOrdersUnpaid, $newUnpaidAmount);
                    if ($creditToUse > 0) {
                        $client->useCredit($creditToUse, $order, 'Crédit utilisé pour vente après changement client');
                    }
                }
            }

            // Process new payments
            if ($request->has('payments') && count($request->payments) > 0) {
                $paymentFiles = [];
                foreach ($request->allFiles() as $key => $file) {
                    if (preg_match('/payments\[(\d+)\]\[document\]/', $key, $matches)) {
                        $paymentIndex = $matches[1];
                        $paymentFiles[$paymentIndex] = $file;
                    }
                }

                foreach ($request->payments as $index => $paymentData) {
                    $filePath = null;
                    $originalFilename = null;

                    if (isset($paymentFiles[$index])) {
                        $file = $paymentFiles[$index];
                        $originalFilename = $file->getClientOriginalName();

                        $extension = $file->getClientOriginalExtension();
                        $filename = time() . '_' . uniqid() . '.' . $extension;

                        $filePath = $file->storeAs(
                            'payment-documents/' . date('Y/m'),
                            $filename,
                            'public'
                        );
                    }

                    switch ($paymentData['method']) {
                        case 'advance':
                            // Check if client has enough advance
                            if ($client->available_advance < (float) $paymentData['amount']) {
                                throw new \Exception('Avance insuffisante. Disponible: ' . $client->advance_formatted);
                            }

                            $payment = $order->payments()->create([
                                'payment_method' => 'advance',
                                'amount' => (float) $paymentData['amount'],
                                'payment_date' => $paymentData['date'],
                                'document_path' => $filePath,
                                'original_filename' => $originalFilename,
                                'notes' => $paymentData['advance_reference'] ?? 'Utilisation avance client',
                            ]);

                            $client->useAdvance((float) $paymentData['amount'], $order, $paymentData['advance_reference'] ?? null);
                            break;

                        case 'check':
                            $payment = $order->payments()->create([
                                'payment_method' => 'check',
                                'amount' => (float) $paymentData['amount'],
                                'payment_date' => $paymentData['date'],
                                'document_path' => $filePath,
                                'original_filename' => $originalFilename,
                                'notes' => $paymentData['notes'] ?? null,
                            ]);

                            $check = new Check();
                            $check->check_number = $paymentData['check_number'];
                            $check->check_type = 'client';
                            $check->amount = (float) $paymentData['amount'];
                            $check->remaining_amount = (float) $paymentData['amount'];
                            $check->bank_name = $paymentData['bank_name'];
                            $check->account_holder = $paymentData['account_holder'];
                            $check->issue_date = $paymentData['date'];
                            $check->deposit_date = $paymentData['date'];
                            $check->due_date = $paymentData['due_date'] ?? null;
                            $check->check_image = $filePath;
                            $check->status = 'deposited';
                            $check->is_active = true;
                            $check->created_by = Auth::id();
                            $check->notes = 'Paiement pour vente ' . $order->order_number;
                            $check->save();

                            $payment->update([
                                'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                                    "Chèque N°: {$paymentData['check_number']}, Banque: {$paymentData['bank_name']}"
                            ]);
                            break;

                        case 'transfer':
                            $payment = $order->payments()->create([
                                'payment_method' => 'transfer',
                                'amount' => (float) $paymentData['amount'],
                                'payment_date' => $paymentData['date'],
                                'document_path' => $filePath,
                                'original_filename' => $originalFilename,
                                'notes' => $paymentData['notes'] ?? null,
                            ]);

                            $payment->update([
                                'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                                    "Réf: {$paymentData['transfer_reference']}, " .
                                    "Compte: {$paymentData['account_number']}, " .
                                    "Banque: {$paymentData['bank_name']}"
                            ]);
                            break;

                        case 'traite':
                            $payment = $order->payments()->create([
                                'payment_method' => 'traite',
                                'amount' => (float) $paymentData['amount'],
                                'payment_date' => $paymentData['date'],
                                'document_path' => $filePath,
                                'original_filename' => $originalFilename,
                                'notes' => $paymentData['notes'] ?? null,
                            ]);

                            $traiteData = [
                                'traite_number' => $paymentData['traite_number'],
                                'order_id' => $order->order_id,
                                'payment_id' => $payment->payment_id,
                                'client_id' => $order->client_id,
                                'amount' => (float) $paymentData['amount'],
                                'issue_date' => $paymentData['date'],
                                'due_date' => $paymentData['due_date'],
                                'bank_name' => $paymentData['bank_name'] ?? null,
                                'drawee' => $paymentData['drawee'],
                                'drawee_address' => $paymentData['drawee_address'] ?? null,
                                'notes' => $paymentData['notes'] ?? null,
                                'status' => 'pending',
                                'document_path' => $filePath,
                                'original_filename' => $originalFilename,
                                'created_by' => Auth::id(),
                            ];

                            Traite::create($traiteData);

                            $payment->update([
                                'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                                    "Traite N°: {$paymentData['traite_number']}, Échéance: " .
                                    \Carbon\Carbon::parse($paymentData['due_date'])->format('d/m/Y')
                            ]);
                            break;

                        case 'cash':
                            $payment = $order->payments()->create([
                                'payment_method' => 'cash',
                                'amount' => (float) $paymentData['amount'],
                                'payment_date' => $paymentData['date'],
                                'document_path' => $filePath,
                                'original_filename' => $originalFilename,
                                'notes' => $paymentData['notes'] ?? null,
                            ]);

                            if (!empty($paymentData['cash_reference'])) {
                                $payment->update([
                                    'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                                        "Réf: {$paymentData['cash_reference']}"
                                ]);
                            }
                            break;
                    }
                }
            }

            // Update client balance based on order changes
            // Pass old unpaid amount (before update) so the delta is calculated correctly
            $oldUnpaidAmount = $oldTotalAmount - $oldPaidAmount;
            $client->updateBalanceFromOrder($order, 'order_updated', $oldUnpaidAmount);

            DB::commit();

            \Log::info('Order updated successfully', [
                'order_id' => $order->order_id,
                'order_number' => $order->order_number,
                'total_amount' => $totalAmount,
                'paid_amount' => $totalPaid,
                'payment_status' => $paymentStatus,
                'credit_usage' => $client->credit_usage,
                'client_balance' => $client->balance,
                'credit_exceeded' => $creditExceeded,
                'excess_amount' => $excessAmount
            ]);

            return response()->json([
                'success' => true,
                'message' => $creditExceeded ?
                    'vente mise à jour avec succès! (Crédit dépassé de ' . number_format($excessAmount, 2, ',', '.') . ' DH)' :
                    'vente mise à jour avec succès!',
                'order_id' => $order->order_id,
                'order_number' => $order->order_number,
                'client_balance' => $client->balance,
                'client_balance_formatted' => $client->balance_formatted,
                'credit_usage' => $client->credit_usage,
                'credit_limit' => $client->credit_limit,
                'credit_exceeded' => $creditExceeded,
                'excess_amount' => $excessAmount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Order update error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la vente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $order = SalesOrder::with(['client', 'items', 'payments'])->findOrFail($id);
            $client = $order->client;

            $unpaidAmount = $order->final_amount - $order->paid_amount;

            $overpaidAmount = max(0, $order->paid_amount - $order->final_amount);

            if ($unpaidAmount > 0) {
                $client->releaseCredit($unpaidAmount, $order, 'Commande supprimée - Libération du crédit');

                \Log::info('Credit released on order deletion', [
                    'order_id' => $order->order_id,
                    'unpaid_amount' => $unpaidAmount,
                    'new_credit_usage' => $client->credit_usage
                ]);
            }

            $balanceImpact = 0;

            if ($unpaidAmount > 0) {
                $balanceImpact += $unpaidAmount;
            }

            if ($overpaidAmount > 0) {
                $balanceImpact -= $overpaidAmount;
            }

            if ($balanceImpact != 0) {
                $previousBalance = $client->balance;
                $newBalance = $previousBalance + $balanceImpact;

                $client->balance = $newBalance;
                $client->save();

                $client->balanceHistory()->create([
                    'previous_balance' => $previousBalance,
                    'new_balance' => $newBalance,
                    'amount' => $balanceImpact,
                    'type' => 'order_deleted',
                    'reference_type' => 'sales_order',
                    'reference_id' => $order->order_id,
                    'description' => "Suppression commande #{$order->order_number}: " .
                        ($unpaidAmount > 0 ? "Libération de " . number_format($unpaidAmount, 2, ',', '.') . " DH d'impayé. " : "") .
                        ($overpaidAmount > 0 ? "Annulation du trop-perçu de " . number_format($overpaidAmount, 2, ',', '.') . " DH. " : ""),
                    'created_by' => auth()->id(),
                ]);

                \Log::info('Balance updated on order deletion', [
                    'order_id' => $order->order_id,
                    'previous_balance' => $previousBalance,
                    'new_balance' => $newBalance,
                    'balance_impact' => $balanceImpact
                ]);
            }

            foreach ($order->payments as $payment) {
                if ($payment->payment_method === 'advance') {
                    $client->reverseAdvance($payment->amount, $order, 'Annulation paiement avance suite suppression commande');
                }
            }

            foreach ($order->items as $item) {
                if ($item->item_type !== 'raw_material' && !empty($item->family_id)) {
                    $familleStock = ProductFamilleStock::where('product_id', $item->item_id)
                        ->where('famille_id', $item->family_id)
                        ->first();

                    if ($familleStock) {
                        $familleStock->current_quantity += $item->quantity;
                        $familleStock->available_quantity = $familleStock->current_quantity - $familleStock->reserved_quantity;
                        $familleStock->last_updated = now();
                        $familleStock->save();

                        \Log::info('Stock restored on order deletion', [
                            'product_id' => $item->item_id,
                            'quantity' => $item->quantity
                        ]);
                    }
                }
            }

            foreach ($order->payments as $payment) {
                if ($payment->payment_method === 'check') {
                    $check = Check::where('notes', 'like', '%' . $order->order_number . '%')
                        ->where('amount', $payment->amount)
                        ->first();
                    if ($check) {
                        if ($check->check_image) {
                            Storage::disk('public')->delete($check->check_image);
                        }
                        $check->delete();
                    }
                } elseif ($payment->payment_method === 'traite') {
                    $traite = Traite::where('order_id', $order->order_id)
                        ->where('payment_id', $payment->payment_id)
                        ->first();
                    if ($traite) {
                        if ($traite->document_path) {
                            Storage::disk('public')->delete($traite->document_path);
                        }
                        $traite->delete();
                    }
                }

                if ($payment->document_path) {
                    Storage::disk('public')->delete($payment->document_path);
                }

                $payment->delete();
            }

            $order->items()->delete();

            $order->delete();

            DB::commit();

            $message = 'Commande supprimée avec succès!';
            if ($unpaidAmount > 0) {
                $message .= ' Crédit libéré: ' . number_format($unpaidAmount, 2, ',', '.') . ' DH.';
            }
            if ($balanceImpact != 0) {
                $message .= ' Solde client mis à jour.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'client_credit_usage' => $client->credit_usage,
                    'client_balance' => $client->balance,
                    'client_balance_formatted' => $client->balance_formatted,
                    'credit_released' => $unpaidAmount,
                    'balance_impact' => $balanceImpact
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order deletion error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a payment from an order
     */
    public function deletePayment($orderId, $paymentId)
    {
        DB::beginTransaction();
        try {
            $order = SalesOrder::findOrFail($orderId);
            $payment = $order->payments()->findOrFail($paymentId);

            if ($payment->payment_method === 'advance') {
                $client = $order->client;
                $client->reverseAdvance($payment->amount, $order, 'Annulation paiement avance');
            }

            if ($payment->payment_method === 'check') {
                $check = Check::where('notes', 'like', '%' . $order->order_number . '%')
                    ->where('amount', $payment->amount)
                    ->first();
                if ($check) {
                    $check->status = 'bounced';
                    $check->save();
                }
            } elseif ($payment->payment_method === 'traite') {
                $traite = Traite::where('order_id', $orderId)
                    ->where('payment_id', $payment->payment_id)
                    ->first();
                if ($traite) {
                    $traite->status = 'bounced';
                    $traite->order_id = null;
                    $traite->payment_id = null;
                    $traite->save();
                }
            }

            if ($payment->document_path) {
                Storage::disk('public')->delete($payment->document_path);
            }

            // Capture the excess (portion that went to the client balance, not this order)
            // before the row is deleted, so it can be reversed too.
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

            $client = $order->client;
            $client->updateBalanceFromOrder($order, 'payment_deleted', $payment->amount);

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

            DB::commit();

            $order->load('payments');

            return response()->json([
                'success' => true,
                'message' => 'Paiement supprimé avec succès!',
                'order' => [
                    'paid_amount' => number_format($order->paid_amount, 2, ',', '.'),
                    'total_received' => number_format($order->payments->sum('display_amount'), 2, ',', '.'),
                    'payment_status' => $order->payment_status,
                    'remaining' => number_format(max(0, $order->final_amount - $order->paid_amount), 2, ',', '.')
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatistics(Request $request)
    {
        try {
            // Base query for orders (for counts)
            $orderQuery = SalesOrder::query();

            // Apply date filters to orders if needed (for order counts)
            if ($request->filled('date_from')) {
                $orderQuery->whereDate('order_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $orderQuery->whereDate('order_date', '<=', $request->date_to);
            }

            $totalOrders = $orderQuery->count();
            $todayOrders = SalesOrder::whereDate('order_date', today())->count();
            $pendingPayment = SalesOrder::where('payment_status', 'pending')->sum('final_amount');

            // Revenue based on payment dates (not order dates)
            $revenueQuery = SalesOrderPayment::where('payment_method', '!=', 'advance')
                ->where('payment_method', '!=', 'avoir');

            if ($request->filled('date_from')) {
                $revenueQuery->whereDate('payment_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $revenueQuery->whereDate('payment_date', '<=', $request->date_to);
            }

            $filteredRevenue = $revenueQuery->sum('amount');

            // Get completed orders count based on payment status
            $completedOrders = SalesOrder::where('payment_status', 'paid')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalOrders,
                    'today' => $todayOrders,
                    'pending' => $pendingPayment,
                    'completed' => $completedOrders,
                    'revenue' => $filteredRevenue
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProductDetails($id)
    {
        $product = Product::with(['familles'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'name' => $product->product_name,
            'code' => $product->product_code,
            'price' => $product->price_client,
            'type' => $product->product_type,
            'has_familles' => $product->familles->count() > 0,
            'familles' => $product->familles->map(function ($famille) {
                return [
                    'id' => $famille->famille_id,
                    'name' => $famille->famille_name,
                    'code' => $famille->famille_code,
                ];
            })
        ]);
    }

    public function getRawMaterialDetails($id)
    {
        $material = RawMaterial::findOrFail($id);

        return response()->json([
            'success' => true,
            'name' => $material->material_name,
            'code' => $material->material_code,
            'price' => 0,
            'unit' => $material->unit_of_measure,
        ]);
    }

    /**
     * Get products by type for AJAX requests
     */
    public function getProductsByType($type)
    {
        try {
            $products = Product::where('is_active', true)
                ->where('product_type', $type)
                ->with(['familles'])
                ->get()
                ->map(function ($product) {
                    $data = [
                        'id' => $product->product_id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'price' => $product->price_client,
                        'price_revendeur' => $product->price_revendeur,
                        'price_commercial' => $product->price_commercial,
                        'price_special' => $product->price_special,
                        'volume' => $product->volume_m3 ?? $product->getTotalVolumeAttribute(), // Add volume
                        'has_families' => $product->familles->count() > 0,
                    ];

                    if ($product->familles->count() > 0) {
                        $data['families'] = $product->familles->map(function ($famille) use ($product) {
                            return [
                                'id' => $famille->famille_id,
                                'name' => $famille->famille_name,
                                'code' => $famille->famille_code,
                                'prix_client' => $famille->pivot->prix_client ?? $product->price_client,
                                'prix_grossiste' => $famille->pivot->prix_grossiste ?? $product->price_revendeur ?? $product->price_client,
                                'prix_commercial' => $famille->pivot->prix_commercial ?? $product->price_commercial ?? $product->price_client,
                                'prix_special' => $famille->pivot->prix_special ?? $product->price_special ?? $product->price_client,
                                'quantity_per_unit' => $famille->pivot->quantity_per_unit ?? 1,
                            ];
                        });
                    }

                    return $data;
                });

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get raw materials list for AJAX requests
     */
    public function getRawMaterialsList()
    {
        try {
            $materials = RawMaterial::where('is_active', true)
                ->get()
                ->map(function ($material) {
                    return [
                        'id' => $material->material_id,
                        'name' => $material->material_name,
                        'code' => $material->material_code,
                        'price' => 0,
                        'unit' => $material->unit_of_measure,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $materials
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unpaid orders for a client (oldest first)
     */
    public function getClientUnpaidOrders($id)
    {
        try {
            $orders = SalesOrder::where('client_id', $id)
                ->whereIn('payment_status', ['pending', 'partial'])
                ->where('final_amount', '>', DB::raw('paid_amount'))
                ->orderBy('order_date', 'asc')
                ->orderBy('order_id', 'asc')
                ->get()
                ->map(function ($order) {
                    return [
                        'order_id' => $order->order_id,
                        'order_number' => $order->order_number,
                        'order_date' => $order->order_date->format('d/m/Y'),
                        'total_amount' => number_format($order->final_amount, 2, ',', '.'),
                        'unpaid_amount' => $order->final_amount - $order->paid_amount
                    ];
                });

            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a payment to an existing order
     */
    public function addPayment(Request $request, $id)
    {
        $request->validate([
            'method' => 'required|in:cash,check,transfer,traite,advance',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'check_number' => 'required_if:method,check|string',
            'bank_name' => 'required_if:method,check|string',
            'account_holder' => 'required_if:method,check|string',
            'due_date' => 'required_if:method,check|date',
            'transfer_reference' => 'required_if:method,transfer|string',
            'account_number' => 'required_if:method,transfer|string',
            'traite_number' => 'required_if:method,traite|string',
            'drawee' => 'required_if:method,traite|string',
            'due_date' => 'required_if:method,traite|date',
            'advance_reference' => 'nullable|string',
            'excess_action' => 'nullable|in:balance,orders',
            'excess_orders' => 'nullable|array',
            'excess_orders.*' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $order = SalesOrder::with('client')->findOrFail($id);
            $client = $order->client;

            $amount = (float) $request->amount;
            $remaining = max(0.0, $order->final_amount - $order->paid_amount);
            $applyToOrder = min($amount, $remaining);
            $excess = round($amount - $applyToOrder, 2);

            // Check if payment method is advance and client has enough advance
            if ($request->method === 'advance') {
                if ($client->available_advance < $applyToOrder) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Solde insuffisant. Disponible: ' . $client->advance_formatted
                    ], 400);
                }
            }

            // Handle file upload if present
            $filePath = null;
            $originalFilename = null;

            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $originalFilename = $file->getClientOriginalName();

                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '.' . $extension;

                $filePath = $file->storeAs(
                    'payment-documents/' . date('Y/m'),
                    $filename,
                    'public'
                );
            }

            // Create payment record for the portion applied to this order. `amount` stays
            // capped to what's actually applied (needed for paid_amount/credit accounting);
            // `received_amount` keeps the full sum the client handed over, for display.
            $payment = $order->payments()->create([
                'payment_method' => $request->method,
                'amount' => $applyToOrder,
                'received_amount' => $amount,
                'payment_date' => $request->date,
                'document_path' => $filePath,
                'original_filename' => $originalFilename,
                'notes' => $request->notes ?? null,
            ]);

            // Process based on payment method
            switch ($request->method) {
                case 'check':
                    $check = new Check();
                    $check->check_number = $request->check_number;
                    $check->check_type = 'client';
                    $check->client_id = $order->client_id;
                    $check->order_id = $order->order_id;
                    $check->payment_id = $payment->payment_id;
                    $check->amount = $amount;
                    $check->remaining_amount = $amount;
                    $check->bank_name = $request->bank_name;
                    $check->account_holder = $request->account_holder;
                    $check->issue_date = $request->date;
                    $check->deposit_date = $request->date;
                    $check->due_date = $request->due_date ?? null;
                    $check->check_image = $filePath;
                    $check->status = 'deposited';
                    $check->is_active = true;
                    $check->created_by = Auth::id();
                    $check->notes = 'Paiement pour vente ' . $order->order_number;
                    $check->save();

                    $payment->update([
                        'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                            "Chèque N°: {$request->check_number}, Banque: {$request->bank_name}"
                    ]);
                    break;

                case 'transfer':
                    $payment->update([
                        'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                            "Réf: {$request->transfer_reference}, " .
                            "Compte: {$request->account_number}, " .
                            "Banque: {$request->bank_name}"
                    ]);
                    break;

                case 'traite':
                    $traiteData = [
                        'traite_number' => $request->traite_number,
                        'order_id' => $order->order_id,
                        'payment_id' => $payment->payment_id,
                        'client_id' => $order->client_id,
                        'amount' => $amount,
                        'issue_date' => $request->date,
                        'due_date' => $request->due_date,
                        'bank_name' => $request->bank_name ?? null,
                        'drawee' => $request->drawee,
                        'drawee_address' => $request->drawee_address ?? null,
                        'notes' => $request->notes ?? null,
                        'status' => 'pending',
                        'document_path' => $filePath,
                        'original_filename' => $originalFilename,
                        'created_by' => Auth::id(),
                    ];

                    Traite::create($traiteData);

                    $payment->update([
                        'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                            "Traite N°: {$request->traite_number}, Échéance: " .
                            \Carbon\Carbon::parse($request->due_date)->format('d/m/Y')
                    ]);
                    break;

                case 'advance':
                    $client->useAdvance($applyToOrder > 0 ? $applyToOrder : $amount, $order, $request->advance_reference ?? null);

                    $payment->update([
                        'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                            ($request->advance_reference ? "Réf: {$request->advance_reference}" : 'Utilisation solde client')
                    ]);
                    break;

                case 'cash':
                    if (!empty($request->cash_reference)) {
                        $payment->update([
                            'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                                "Réf: {$request->cash_reference}"
                        ]);
                    }
                    break;
            }

            // Update order paid amount and status
            $oldPaidAmount = $order->paid_amount;
            $order->paid_amount += $applyToOrder;

            if ($order->paid_amount >= $order->final_amount - 0.01) {
                $order->payment_status = 'paid';
            } elseif ($order->paid_amount > 0) {
                $order->payment_status = 'partial';
            }

            $order->save();

            // Update client balance
            $client->updateBalanceFromOrder($order, 'payment_added', $oldPaidAmount);

            // Release credit if payment covers unpaid amount
            $unpaidBefore = $order->final_amount - $oldPaidAmount;
            $unpaidAfter = $order->final_amount - $order->paid_amount;

            if ($unpaidBefore > 0 && $unpaidAfter < $unpaidBefore) {
                $creditReleased = $unpaidBefore - $unpaidAfter;
                $client->releaseCredit($creditReleased, $order, 'Paiement reçu sur vente');
            }

            // Handle excess payment (only for non-advance methods)
            if ($excess > 0 && $request->method !== 'advance') {
                $excessAction = $request->excess_action ?? 'balance';
                $remainingExcess = $excess;

                if ($excessAction === 'orders') {
                    foreach (($request->excess_orders ?? []) as $eid => $eamt) {
                        $eamt = round((float) $eamt, 2);
                        if ($eamt <= 0) continue;

                        $eorder = SalesOrder::find((int) $eid);
                        if (!$eorder || $eorder->client_id !== $order->client_id) continue;

                        $eRemaining = max(0.0, $eorder->final_amount - $eorder->paid_amount);
                        $eApply = min($eamt, $eRemaining);
                        if ($eApply <= 0) continue;

                        $eorder->payments()->create([
                            'payment_method' => $request->method,
                            'amount' => $eApply,
                            'payment_date' => $request->date,
                            'notes' => 'Excédent de commande #' . $order->order_number,
                        ]);

                        $eOldPaid = $eorder->paid_amount;
                        $eorder->paid_amount += $eApply;
                        $eorder->payment_status = $eorder->paid_amount >= $eorder->final_amount - 0.01 ? 'paid' : 'partial';
                        $eorder->save();

                        $client->refresh();
                        $client->updateBalanceFromOrder($eorder, 'payment_added', $eOldPaid);

                        $eUnpaidBefore = $eorder->final_amount - $eOldPaid;
                        $eUnpaidAfter = $eorder->final_amount - $eorder->paid_amount;
                        if ($eUnpaidBefore > 0 && $eUnpaidAfter < $eUnpaidBefore) {
                            $client->releaseCredit($eUnpaidBefore - $eUnpaidAfter, $eorder, 'Excédent appliqué');
                        }

                        $remainingExcess -= $eApply;
                    }
                }

                // Any remaining excess goes to client balance
                if ($remainingExcess > 0.005) {
                    $client->refresh();
                    $previousBalance = $client->balance;
                    $client->balance = $previousBalance + $remainingExcess;
                    $client->save();

                    $client->balanceHistory()->create([
                        'previous_balance' => $previousBalance,
                        'new_balance'      => $client->balance,
                        'amount'           => $remainingExcess,
                        'type'             => 'payment_added',
                        'reference_type'   => 'sales_order',
                        'reference_id'     => $order->order_id,
                        'description'      => 'Excédent commande #' . $order->order_number . ': +' . number_format($remainingExcess, 2, ',', '.') . ' DH',
                        'created_by'       => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            // Get updated data for response
            $restAmount = max(0, $order->final_amount - $order->paid_amount);
            $order->load('payments');

            return response()->json([
                'success' => true,
                'message' => 'Paiement ajouté avec succès!',
                'order' => [
                    'paid_amount' => number_format($order->paid_amount, 2, ',', '.'),
                    'total_received' => number_format($order->payments->sum('display_amount'), 2, ',', '.'),
                    'payment_status' => $order->payment_status,
                    'payment_status_label' => $order->payment_status == 'paid' ? 'Payé' : ($order->payment_status == 'partial' ? 'Avance' : 'Non Payé'),
                    'rest_amount' => number_format($restAmount, 2, ',', '.'),
                    'rest_amount_class' => $restAmount > 0 ? 'text-danger' : 'text-success',
                    'credit_usage' => $client->credit_usage,
                    'client_balance' => $client->balance
                ],
                'payment' => [
                    'id' => $payment->payment_id,
                    'method' => $payment->payment_method,
                    'amount' => number_format($payment->display_amount, 2, ',', '.'),
                    'date' => $payment->payment_date->format('d/m/Y'),
                    'method_label' => $payment->method_label,
                    'notes' => $payment->notes
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Add payment error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateDeliveryNote($id, Request $request)
    {
        try {
            $order = SalesOrder::with(['client', 'items'])->findOrFail($id);
            $showPrices = $request->query('show_prices', 1);
            $showLogo = $request->query('show_logo', 1);
            $displayType = $request->query('display_type', 'unite');
            $priceType = $request->query('price_type', 'ttc');

            $totalQuantity = $order->items->sum('quantity');
            $tvaRate = 0.20;

            $totalVolume = 0;
            if ($displayType === 'volume') {
                foreach ($order->items as $item) {
                    if ($item->item_type != 'raw_material' && class_exists('App\Models\Product')) {
                        $product = \App\Models\Product::find($item->item_id);
                        if ($product && $product->volume) {
                            $totalVolume += $item->quantity * $product->volume;
                        }
                    }
                }
            }

            $itemsData = [];
            $roundedTotalAmountTTC = 0;
            $roundedTotalAmountHT = 0;

            foreach ($order->items as $item) {
                $productCode = '';
                $productUnit = '';
                $volumePerUnit = 0;
                $totalVolume = 0;
                $displayName = $item->item_name;

                if ($item->item_type != 'raw_material' && class_exists('App\Models\Product')) {
                    $product = \App\Models\Product::find($item->item_id);
                    if ($product) {
                        $productCode = $product->product_code;
                        $productUnit = $product->unit_of_measure;
                        $volumePerUnit = $product->volume_per_unit ?? ($product->total_volume ?? 0);
                        $totalVolume = $item->quantity * $volumePerUnit;
                        $displayName = $item->item_name;
                    }
                } elseif ($item->item_type == 'raw_material') {
                    $rawMaterial = \App\Models\RawMaterial::find($item->item_id);
                    if ($rawMaterial) {
                        $productUnit = $rawMaterial->unit_of_measure;
                    }
                }

                $unitPriceTTC = $item->unit_price;
                $unitPriceHT = $unitPriceTTC / (1 + $tvaRate);
                $totalPriceTTC = $unitPriceTTC * $item->quantity;
                $totalPriceHT = $unitPriceHT * $item->quantity;

                $roundedUnitPriceTTC = $unitPriceTTC;
                $roundedUnitPriceHT = $unitPriceHT;
                $roundedTotalPriceTTC = round($totalPriceTTC);
                $roundedTotalPriceHT = round($totalPriceHT);

                $roundedTotalAmountTTC += $roundedTotalPriceTTC;
                $roundedTotalAmountHT += $roundedTotalPriceHT;

                $itemsData[] = [
                    'item' => $item,
                    'productCode' => $productCode,
                    'productUnit' => $productUnit,
                    'displayName' => $displayName,
                    'familleName' => $item->family_name ?? '',
                    'totalVolume' => $totalVolume,
                    'unitPriceTTC' => $unitPriceTTC,
                    'unitPriceHT' => $unitPriceHT,
                    'totalPriceTTC' => $totalPriceTTC,
                    'totalPriceHT' => $totalPriceHT,
                    'roundedUnitPriceTTC' => $roundedUnitPriceTTC,
                    'roundedUnitPriceHT' => $roundedUnitPriceHT,
                    'roundedTotalPriceTTC' => $roundedTotalPriceTTC,
                    'roundedTotalPriceHT' => $roundedTotalPriceHT,
                ];
            }

            $clientBalance = $order->client->balance ?? 0;
            $balanceSign = $clientBalance > 0 ? '+' : ($clientBalance < 0 ? '-' : '');
            $balanceFormatted = $balanceSign . number_format(abs($clientBalance), 2, ',', '.') . ' DH';
            $balanceStatus = $clientBalance > 0 ? 'Créditeur (Avance)' : ($clientBalance < 0 ? 'Débiteur (Impayé)' : 'Soldé');
            $balanceClass = $clientBalance > 0 ? 'text-success' : ($clientBalance < 0 ? 'text-danger' : 'text-secondary');

            $data = [
                'order' => $order,
                'client' => $order->client,
                'itemsData' => $itemsData,
                'showPrices' => (bool) $showPrices,
                'showLogo' => (bool) $showLogo,
                'displayType' => $displayType,
                'priceType' => $priceType,
                'totalVolume' => $totalVolume,
                'date' => now()->format('d/m/Y'),
                'time' => now()->format('H:i'),
                'delivery_note_number' => 'BL-' . str_pad($order->order_id, 4, '0', STR_PAD_LEFT),
                'username' => auth()->user()->name ?? auth()->user()->username,
                'totalQuantity' => $totalQuantity,
                'client_balance' => $clientBalance,
                'balance_formatted' => $balanceFormatted,
                'balance_status' => $balanceStatus,
                'balance_class' => $balanceClass,
                'display_advance' => $order->display_advance,
                'totalAmountTTC' => $roundedTotalAmountTTC,
                'totalAmountHT' => $roundedTotalAmountHT,
                'tvaRate' => $tvaRate * 100, // 20%
                'numberToFrench' => function ($number) {
                    return $this->numberToFrench($number);
                }
            ];

            $pdf = Pdf::loadView('pdf.delivery-note', $data);
            $pdf->setPaper('A5', 'portrait');

            return $pdf->download('bon-livraison-' . $order->order_number . '.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate delivery note for inline viewing/printing
     */
    public function viewDeliveryNote($id, Request $request)
    {
        try {
            $order = SalesOrder::with(['client', 'items'])->findOrFail($id);
            $showPrices = $request->query('show_prices', 1);
            $showLogo = $request->query('show_logo', 1);
            $displayType = $request->query('display_type', 'unite');
            $priceType = $request->query('price_type', 'ttc');

            $totalQuantity = $order->items->sum('quantity');
            $tvaRate = 0.20;

            $totalVolume = 0;
            if ($displayType === 'volume') {
                foreach ($order->items as $item) {
                    if ($item->item_type != 'raw_material' && class_exists('App\Models\Product')) {
                        $product = \App\Models\Product::find($item->item_id);
                        if ($product && $product->volume) {
                            $totalVolume += $item->quantity * $product->volume;
                        }
                    }
                }
            }

            $itemsData = [];
            $roundedTotalAmountTTC = 0;
            $roundedTotalAmountHT = 0;

            foreach ($order->items as $item) {
                $productCode = '';
                $productUnit = '';
                $volumePerUnit = 0;
                $totalVolume = 0;
                $displayName = $item->item_name;

                if ($item->item_type != 'raw_material' && class_exists('App\Models\Product')) {
                    $product = \App\Models\Product::find($item->item_id);
                    if ($product) {
                        $productCode = $product->product_code;
                        $productUnit = $product->unit_of_measure;
                        $volumePerUnit = $product->volume_per_unit ?? ($product->total_volume ?? 0);
                        $totalVolume = $item->quantity * $volumePerUnit;
                        $displayName = $item->item_name;
                    }
                } elseif ($item->item_type == 'raw_material') {
                    $rawMaterial = \App\Models\RawMaterial::find($item->item_id);
                    if ($rawMaterial) {
                        $productUnit = $rawMaterial->unit_of_measure;
                    }
                }

                $unitPriceTTC = $item->unit_price;
                $unitPriceHT = $unitPriceTTC / (1 + $tvaRate);
                $totalPriceTTC = $unitPriceTTC * $item->quantity;
                $totalPriceHT = $unitPriceHT * $item->quantity;

                $roundedUnitPriceTTC = $unitPriceTTC;
                $roundedUnitPriceHT = $unitPriceHT;
                $roundedTotalPriceTTC = round($totalPriceTTC);
                $roundedTotalPriceHT = round($totalPriceHT);

                $roundedTotalAmountTTC += $roundedTotalPriceTTC;
                $roundedTotalAmountHT += $roundedTotalPriceHT;

                $itemsData[] = [
                    'item' => $item,
                    'productCode' => $productCode,
                    'productUnit' => $productUnit,
                    'displayName' => $displayName,
                    'familleName' => $item->family_name ?? '',
                    'totalVolume' => $totalVolume,
                    'unitPriceTTC' => $unitPriceTTC,
                    'unitPriceHT' => $unitPriceHT,
                    'totalPriceTTC' => $totalPriceTTC,
                    'totalPriceHT' => $totalPriceHT,
                    'roundedUnitPriceTTC' => $roundedUnitPriceTTC,
                    'roundedUnitPriceHT' => $roundedUnitPriceHT,
                    'roundedTotalPriceTTC' => $roundedTotalPriceTTC,
                    'roundedTotalPriceHT' => $roundedTotalPriceHT,
                ];
            }

            $clientBalance = $order->client->balance ?? 0;
            $balanceSign = $clientBalance > 0 ? '+' : ($clientBalance < 0 ? '-' : '');
            $balanceFormatted = $balanceSign . number_format(abs($clientBalance), 2, ',', '.') . ' DH';
            $balanceStatus = $clientBalance > 0 ? 'Créditeur (Avance)' : ($clientBalance < 0 ? 'Débiteur (Impayé)' : 'Soldé');
            $balanceClass = $clientBalance > 0 ? 'text-success' : ($clientBalance < 0 ? 'text-danger' : 'text-secondary');

            $data = [
                'order' => $order,
                'client' => $order->client,
                'itemsData' => $itemsData,
                'showPrices' => (bool) $showPrices,
                'showLogo' => (bool) $showLogo,
                'displayType' => $displayType,
                'priceType' => $priceType,
                'totalVolume' => $totalVolume,
                'date' => now()->format('d/m/Y'),
                'time' => now()->format('H:i'),
                'delivery_note_number' => 'BL-' . str_pad($order->order_id, 4, '0', STR_PAD_LEFT),
                'username' => auth()->user()->name ?? auth()->user()->username,
                'totalQuantity' => $totalQuantity,
                'client_balance' => $clientBalance,
                'balance_formatted' => $balanceFormatted,
                'balance_status' => $balanceStatus,
                'balance_class' => $balanceClass,
                'display_advance' => $order->display_advance,
                'totalAmountTTC' => $roundedTotalAmountTTC,
                'totalAmountHT' => $roundedTotalAmountHT,
                'tvaRate' => $tvaRate * 100,
                'numberToFrench' => function ($number) {
                    return $this->numberToFrench($number);
                }
            ];

            $pdf = Pdf::loadView('pdf.delivery-note', $data);
            $pdf->setPaper('A5', 'portrait');

            // Stream the PDF inline instead of downloading
            return $pdf->stream('bon-livraison-' . $order->order_number . '.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF View Error: ' . $e->getMessage());

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

        $negative = $number < 0;
        $number = abs($number);

        $integer = floor($number);
        $decimal = round(($number - $integer) * 100);

        $units = ['', 'UN', 'DEUX', 'TROIS', 'QUATRE', 'CINQ', 'SIX', 'SEPT', 'HUIT', 'NEUF', 'DIX', 'ONZE', 'DOUZE', 'TREIZE', 'QUATORZE', 'QUINZE', 'SEIZE', 'DIX-SEPT', 'DIX-HUIT', 'DIX-NEUF'];
        $tens = ['', '', 'VINGT', 'TRENTE', 'QUARANTE', 'CINQUANTE', 'SOIXANTE', 'SOIXANTE-DIX', 'QUATRE-VINGTS', 'QUATRE-VINGT-DIX'];

        $convert = function ($num) use (&$convert, $units, $tens) {
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

                    // "MILLE"/"MILLION", jamais "UN MILLE"/"UN MILLION"
                    $quotientText = $quotient == 1 ? '' : $convert($quotient);

                    if ($word == 'MILLE') {
                        $word = 'MILLE';
                    } else {
                        if ($quotient > 1) {
                            $word .= 'S';
                        }
                    }

                    $result = trim($quotientText . ' ' . $word);

                    if ($remainder > 0) {
                        $result .= ' ' . $convert($remainder);
                    }

                    return $result;
                }
            }

            return '';
        };

        $result = $convert($integer);

        if ($negative) {
            $result = 'MOINS ' . $result;
        }

        if ($decimal > 0) {
            $result .= ' ET ' . $convert($decimal) . ' CENTIME' . ($decimal > 1 ? 'S' : '');
        }

        return trim($result);
    }
}
