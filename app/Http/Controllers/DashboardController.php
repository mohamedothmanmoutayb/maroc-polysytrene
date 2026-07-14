<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\ProductionOutput;
use App\Models\ProductionConsumption;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\StockMovementDetail;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Check;
use App\Models\Traite;
use App\Models\Supplier;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Machine;
use App\Models\SalesOrderPayment;
use App\Models\PurchasePaymentDocument;
use App\Models\RawMaterialPurchase;
use App\Models\ProductStock;
use App\Models\ProductFamilleStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // ── Période sélectionnée (filtres de dates) ──────────────────────────
        [$periodStart, $periodEnd, $quickFilter, $periodLabel] = $this->resolvePeriod($request);

        // ── Client stats ──────────────────────────────────────────────────────
        $clientTypeStats = collect([
            ['type' => 'client',      'label' => 'Clients',      'color' => '#0d6efd'],
            ['type' => 'commerciale', 'label' => 'Commerciales', 'color' => '#198754'],
            ['type' => 'grossiste',   'label' => 'Grossistes',   'color' => '#ffc107'],
            ['type' => 'special',     'label' => 'Spéciaux',     'color' => '#fd7e14'],
        ])->map(function ($item) {
            $item['count'] = Client::where('client_type', $item['type'])->where('is_active', true)->count();
            return $item;
        });

        $totalClients = $clientTypeStats->sum('count');
        $clientTypeStats = $clientTypeStats->map(function ($item) use ($totalClients) {
            $item['percentage'] = $totalClients > 0 ? round(($item['count'] / $totalClients) * 100) : 0;
            return $item;
        });

        $clientMonthlyGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $clientMonthlyGrowth[] = Client::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // ── Monthly sales (12 months) ─────────────────────────────────────────
        $monthlySalesData = [];
        $monthsLabels = [];
        for ($i = 0; $i < 12; $i++) {
            $month = now()->subMonths(11 - $i);
            $monthsLabels[] = $month->format('M');
            $monthlySalesData[] = (float) SalesOrder::whereYear('order_date', $month->year)
                ->whereMonth('order_date', $month->month)
                ->sum('final_amount');
        }

        // ── Monthly expenses (12 months) ──────────────────────────────────────
        $monthlyExpensesData = [];
        for ($i = 0; $i < 12; $i++) {
            $month = now()->subMonths(11 - $i);
            $monthlyExpensesData[] = (float) Expense::whereYear('expense_date', $month->year)
                ->whereMonth('expense_date', $month->month)
                ->sum('amount');
        }

        // ── Payment stats ─────────────────────────────────────────────────────
        $paymentStatusStats = [
            'paid'    => ['count' => SalesOrder::where('payment_status', 'paid')->count(),    'amount' => SalesOrder::where('payment_status', 'paid')->sum('final_amount')],
            'pending' => ['count' => SalesOrder::where('payment_status', 'pending')->count(), 'amount' => SalesOrder::where('payment_status', 'pending')->sum('final_amount')],
            'partial' => ['count' => SalesOrder::where('payment_status', 'partial')->count(), 'amount' => SalesOrder::where('payment_status', 'partial')->sum('final_amount')],
            'overdue' => ['count' => SalesOrder::where('payment_status', 'overdue')->count(), 'amount' => SalesOrder::where('payment_status', 'overdue')->sum('final_amount')],
        ];

        $paymentMethods = [
            ['method' => 'cash',          'label' => 'Espèces',  'color' => '#198754'],
            ['method' => 'check',         'label' => 'Chèque',   'color' => '#0d6efd'],
            ['method' => 'bank_transfer', 'label' => 'Virement', 'color' => '#0dcaf0'],
            ['method' => 'credit_card',   'label' => 'Carte',    'color' => '#ffc107'],
        ];

        $paymentMethodStats = collect($paymentMethods)->map(function ($item) {
            $total = SalesOrderPayment::where('payment_method', $item['method'])->sum('amount');
            $item['total'] = (float) $total;
            return $item;
        });

        $totalPayments = $paymentMethodStats->sum('total');
        $paymentMethodStats = $paymentMethodStats->map(function ($item) use ($totalPayments) {
            $item['percentage'] = $totalPayments > 0 ? round(($item['total'] / $totalPayments) * 100) : 0;
            return $item;
        });

        // ── Top & low selling products ────────────────────────────────────────
        $topSellingProducts = $this->getTopSellingProducts();
        $lowSellingProducts = $this->getLowSellingProducts();

        $topClients = Client::select(
                'clients.*',
                DB::raw('SUM(sales_orders.final_amount) as total_purchases'),
                DB::raw('COUNT(sales_orders.order_id) as orders_count')
            )
            ->join('sales_orders', 'clients.client_id', '=', 'sales_orders.client_id')
            ->where('clients.is_active', true)
            ->groupBy('clients.client_id')
            ->orderBy('total_purchases', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($client) {
                $client->display_name       = $client->display_name;
                $client->client_type_label  = $client->client_type_label;
                $client->person_type_label  = $client->person_type_label;
                return $client;
            });

        // ── Stock ─────────────────────────────────────────────────────────────
        $lowStockMaterials = $this->getLowStockMaterials();
        $lowStockProducts  = $this->getLowStockProducts();

        $totalMaterialValue = StockMovementDetail::where('remaining_quantity', '>', 0)
            ->sum(DB::raw('remaining_quantity * unit_price'));

        // ── Production ────────────────────────────────────────────────────────
        $recentProductionOrders = ProductionOrder::with(['product', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                $order->status_badge = $this->getProductionStatusBadge($order->status);
                return $order;
            });

        // Production output sur la période sélectionnée
        $periodQtyProduced = ProductionOutput::whereBetween('production_date', [$periodStart, $periodEnd])->sum('quantity_produced');
        $periodVolumeM3    = ProductionOutput::whereBetween('production_date', [$periodStart, $periodEnd])->sum('total_volume_m3');
        $periodDefective   = ProductionOutput::whereBetween('production_date', [$periodStart, $periodEnd])->sum('quantity_defective');
        $productionYield   = $periodQtyProduced > 0
            ? round((($periodQtyProduced - $periodDefective) / $periodQtyProduced) * 100, 1)
            : 0;

        // Objective = total quantity to produce across in_progress orders
        $productionObjective = ProductionOrder::where('status', 'in_progress')->sum('quantity_to_produce');
        $productionProgress  = $productionObjective > 0
            ? min(100, round(($periodQtyProduced / $productionObjective) * 100))
            : 0;

        // Late production orders
        $lateProductionOrders = ProductionOrder::whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('expected_completion_date')
            ->whereDate('expected_completion_date', '<', today())
            ->count();

        // ── Sales orders ──────────────────────────────────────────────────────
        $recentSalesOrders = SalesOrder::with(['client', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                $order->status_badge = $this->getSalesStatusBadge($order->status);
                return $order;
            });

        $pendingSalesOrders = SalesOrder::where('payment_status', 'pending')->count();
        $overdueSalesOrders = $paymentStatusStats['overdue']['count'];

        // ── Finance ───────────────────────────────────────────────────────────
        // Valeurs "aujourd'hui" (référence fixe)
        $todaySales    = SalesOrder::whereDate('order_date', today())->sum('final_amount');
        $todayExpenses = Expense::whereDate('expense_date', today())->sum('amount');
        // Valeurs de la période sélectionnée
        $periodSales      = SalesOrder::whereBetween('order_date', [$periodStart, $periodEnd])->sum('final_amount');
        $periodSalesCount = SalesOrder::whereBetween('order_date', [$periodStart, $periodEnd])->count();
        $periodExpenses   = Expense::whereBetween('expense_date', [$periodStart, $periodEnd])->sum('amount');
        $periodProfit     = $periodSales - $periodExpenses;
        $periodMargin     = $periodSales > 0 ? round(($periodProfit / $periodSales) * 100, 1) : 0;

        // ── Machines ──────────────────────────────────────────────────────────
        $machinesBreakdown = Machine::where('status', '!=', 'active')->count();
        $machinesInMaint   = Machine::where('status', 'maintenance')->with('documents')->get();

        // ═══════════════════════════════════════════════════════════════════════
        // ÉTAT DE TRÉSORERIE (Cash Flow Statement)
        // Formule:
        // Résultat NET = (Crédit Client + La Caisse + Stock MP + Stock Produit)
        //               - (Crédit Fournisseur + Charges Fixes)
        //
        // Taux de couverture = (Résultat NET / (Crédit Client + La Caisse + Stock MP + Stock Produit)) × 100
        // ═══════════════════════════════════════════════════════════════════════

        // Get current month and year (références "mois courant")
        $currentMonth = now()->month;
        $currentYear = now()->year;
        // Période sélectionnée via les filtres de dates
        $dateFrom = $periodStart;
        $dateTo = $periodEnd;

        // 1. CRÉDIT FOURNISSEUR (Supplier Credit - Amount we owe to suppliers)
        // Get all raw material purchases that are not fully paid
        $creditFournisseur = RawMaterialPurchase::whereBetween('purchase_date', [$periodStart, $periodEnd])
            ->where(function($query) {
                $query->where('payment_status', 'pending')
                    ->orWhere('payment_status', 'partial');
            })
            ->sum(DB::raw('final_amount - paid_amount'));

        // 2. CHARGES FIXES (Fixed Expenses - All expenses + salaires employés)
        $depensesMois = Expense::whereBetween('expense_date', [$periodStart, $periodEnd])
            ->sum('amount');

        // Salaires payés de la période: heures pointées × taux horaire (même formule que la paie)
        $salairesEmployes = (float) Attendance::join('employees', 'employees.employee_id', '=', 'attendances.employee_id')
            ->whereBetween('attendances.date', [$periodStart, $periodEnd])
            ->sum(DB::raw('attendances.hours_worked * COALESCE(employees.hourly_salary, 0)'));

        $chargesFixes = $depensesMois + $salairesEmployes;

        // 3. CRÉDIT CLIENT (Client Credit - Unpaid sales/ventes impayé)
        $creditClient = SalesOrder::whereBetween('order_date', [$periodStart, $periodEnd])
            ->sum(DB::raw('final_amount - paid_amount'));

        // 4. LA CAISSE (Sales Payments received - encaissements)
        $laCaisse = SalesOrderPayment::whereBetween('payment_date', [$periodStart, $periodEnd])
            ->sum('amount');

        // 5. STOCK MP (Raw Material Stock Value with Weighted Average Cost)
        $stockMPData = $this->calculateRawMaterialStockValue();
        $stockMP = $stockMPData['total_value'];
        $stockMPDetails = $stockMPData['details'];

        // 6. STOCK PRODUIT (Finished Product Stock Value)
        $stockProduitData = $this->calculateProductStockValue();
        $stockProduit = $stockProduitData['total_value'];
        $stockProduitDetails = $stockProduitData['details'];

        // Calculate DÉNOMINATEUR for Taux de couverture
        $denominateur = $creditClient + $laCaisse + $stockMP + $stockProduit;

        // Calculate Résultat NET
        $totalPositif = $denominateur; // Crédit Client + La Caisse + Stock MP + Stock Produit
        $totalNegatif = $creditFournisseur + $chargesFixes;
        $resultatNet = $totalPositif - $totalNegatif;

        // Calculate Taux de couverture
        $tauxCouverture = $denominateur > 0 ? round(($resultatNet / $denominateur) * 100, 2) : 0;

        // Prepare cash flow data for view
        $cashFlowData = [
            // Negatives (Debts/Charges - what decreases the treasury)
            'credit_fournisseur' => $creditFournisseur,
            'charges_fixes' => $chargesFixes,
            'depenses_mois' => $depensesMois,
            'salaires_employes' => $salairesEmployes,
            'total_negatif' => $totalNegatif,

            // Positives (Assets/Sources - what increases the treasury)
            'credit_client' => $creditClient,
            'la_caisse' => $laCaisse,
            'stock_mp' => $stockMP,
            'stock_produit' => $stockProduit,
            'total_positif' => $totalPositif,
            'denominateur' => $denominateur,

            // Stock details for display
            'stock_mp_details' => $stockMPDetails,
            'stock_produit_details' => $stockProduitDetails,

            // Result
            'resultat_net' => $resultatNet,
            'taux_couverture' => $tauxCouverture,
            'taux_couverture_class' => $this->getCoverageRateClass($tauxCouverture),

            // Period info
            'month' => $periodLabel,
            'date_from' => $dateFrom->format('d/m/Y'),
            'date_to' => $dateTo->format('d/m/Y'),
        ];

        // ═══════════════════════════════════════════════════════════════════════
        // DONNÉES POUR LE TABLEAU DE BORD (checklist métier)
        // ═══════════════════════════════════════════════════════════════════════

        // 1. CHIFFRE D'AFFAIRES PAR JOUR (mois courant) — pour bascule Mois / Jour
        $daysInMonth   = now()->daysInMonth;
        $dailyLabels   = [];
        $dailySalesData    = [];
        $dailyExpensesData = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $day = now()->startOfMonth()->addDays($d - 1);
            $dailyLabels[]        = $day->format('d');
            $dailySalesData[]     = (float) SalesOrder::whereDate('order_date', $day)->sum('final_amount');
            $dailyExpensesData[]  = (float) Expense::whereDate('expense_date', $day)->sum('amount');
        }

        // 2. RÈGLEMENTS PAR JOUR (7 derniers jours, par mode de paiement)
        $dailyPayments = [];
        for ($i = 6; $i >= 0; $i--) {
            $day  = now()->subDays($i);
            $base = SalesOrderPayment::whereDate('payment_date', $day);
            $cash     = (float) (clone $base)->where('payment_method', 'cash')->sum('amount');
            $check    = (float) (clone $base)->where('payment_method', 'check')->sum('amount');
            $traite   = (float) (clone $base)->where('payment_method', 'traite')->sum('amount');
            $transfer = (float) (clone $base)->where('payment_method', 'transfer')->sum('amount');
            $dailyPayments[] = [
                'date'     => $day->format('d/m'),
                'is_today' => $day->isToday(),
                'cash'     => $cash,
                'check'    => $check,
                'traite'   => $traite,
                'transfer' => $transfer,
                'total'    => $cash + $check + $traite + $transfer,
            ];
        }

        // 3. COÛT DE PRODUCTION (période / mois) — valeur des matières consommées
        $prodCostPeriod = (float) ProductionConsumption::whereHas('productionOrder', function ($q) use ($periodStart, $periodEnd) {
            $q->whereBetween('start_date', [$periodStart, $periodEnd]);
        })->sum('total_cost');
        $prodCostMonth = (float) ProductionConsumption::whereHas('productionOrder', function ($q) use ($currentYear, $currentMonth) {
            $q->whereYear('start_date', $currentYear)->whereMonth('start_date', $currentMonth);
        })->sum('total_cost');

        // 3.a QUANTITÉ PRODUITE EN m³ PAR ARTICLE (période sélectionnée)
        $productionByProduct = ProductionOutput::join('products', 'production_output.product_id', '=', 'products.product_id')
            ->whereBetween('production_output.production_date', [$periodStart, $periodEnd])
            ->groupBy('products.product_id', 'products.product_name', 'products.product_code')
            ->selectRaw('products.product_name, products.product_code,
                SUM(production_output.quantity_produced) as qty_produced,
                SUM(production_output.total_volume_m3) as volume_m3')
            ->orderByDesc('volume_m3')
            ->limit(8)
            ->get();

        // 3.b QUANTITÉ MATIÈRE PREMIÈRE CONSOMMÉE (période sélectionnée) — EPS + gaz + ...
        $materialConsumption = ProductionConsumption::join('raw_materials', 'production_consumption.material_id', '=', 'raw_materials.material_id')
            ->whereHas('productionOrder', function ($q) use ($periodStart, $periodEnd) {
                $q->whereBetween('start_date', [$periodStart, $periodEnd]);
            })
            ->groupBy('raw_materials.material_id', 'raw_materials.material_name', 'raw_materials.material_code', 'raw_materials.unit_of_measure')
            ->selectRaw('raw_materials.material_name, raw_materials.material_code, raw_materials.unit_of_measure,
                SUM(production_consumption.actual_quantity_used) as qty_used,
                SUM(production_consumption.planned_quantity) as qty_planned,
                SUM(production_consumption.total_cost) as total_cost')
            ->orderByDesc('total_cost')
            ->limit(8)
            ->get();

        // 4. CAPACITÉ DE PRODUCTION PAR ÉQUIPE (production / découpage) + rendement (période sélectionnée)
        $typeLabels = [
            'type1' => 'Production',
            'type2' => 'Découpage',
            'type3' => 'Conversion',
            'type4' => 'Transformation',
            'type5' => 'Chutes → Finis',
        ];
        $capacityByType = ProductionOutput::join('production_orders', 'production_output.production_order_id', '=', 'production_orders.order_id')
            ->whereBetween('production_output.production_date', [$periodStart, $periodEnd])
            ->groupBy('production_orders.production_type')
            ->selectRaw('production_orders.production_type as production_type,
                SUM(production_output.quantity_produced) as qty_produced,
                SUM(production_output.quantity_defective) as qty_defective,
                SUM(production_output.total_volume_m3) as volume_m3')
            ->get()
            ->map(function ($row) use ($typeLabels) {
                $produced  = (float) $row->qty_produced;
                $defective = (float) $row->qty_defective;
                $row->label = $typeLabels[$row->production_type] ?? $row->production_type;
                $row->yield = $produced > 0 ? round((($produced - $defective) / $produced) * 100, 1) : 0;
                return $row;
            });

        // 5. ÉCHÉANCES : Chèques / Traites (fournisseur / client) avec dates
        $echeances = collect();

        Check::whereNotIn('status', ['cleared'])
            ->orderByRaw('COALESCE(clearing_date, deposit_date, issue_date) asc')
            ->limit(10)
            ->get()
            ->each(function ($check) use ($echeances) {
                $date = $check->clearing_date ?? $check->deposit_date ?? $check->issue_date;
                $echeances->push([
                    'instrument' => 'Chèque',
                    'sens'       => $check->check_type === 'client' ? 'Client' : 'Fournisseur',
                    'party'      => $check->account_holder ?? $check->bank_name ?? '—',
                    'reference'  => $check->check_number,
                    'amount'     => (float) ($check->remaining_amount ?? $check->amount),
                    'date'       => $date,
                    'status'     => $check->status,
                ]);
            });

        Traite::whereNotIn('status', ['paid', 'cancelled'])
            ->with('client')
            ->orderByRaw('COALESCE(due_date, issue_date) asc')
            ->limit(10)
            ->get()
            ->each(function ($traite) use ($echeances) {
                $echeances->push([
                    'instrument' => 'Traite',
                    'sens'       => 'Client',
                    'party'      => $traite->client->display_name ?? $traite->drawee ?? '—',
                    'reference'  => $traite->traite_number,
                    'amount'     => (float) $traite->amount,
                    'date'       => $traite->due_date ?? $traite->issue_date,
                    'status'     => $traite->status,
                ]);
            });

        $echeances = $echeances
            ->sortBy(fn ($e) => $e['date'] ? $e['date']->timestamp : PHP_INT_MAX)
            ->take(10)
            ->values();

        // ── Alerts ────────────────────────────────────────────────────────────
        $totalAlerts = $lowStockProducts->count()
            + $lowStockMaterials->count()
            + $lateProductionOrders
            + $overdueSalesOrders
            + $machinesBreakdown;

        // ── Stats compact ─────────────────────────────────────────────────────
        $stats = [
            'user'                       => $user,
            // Clients
            'total_clients'              => Client::count(),
            'new_clients_this_month'     => Client::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'active_clients'             => Client::where('is_active', true)->count(),
            // Production
            'total_production_orders'    => ProductionOrder::count(),
            'in_progress_orders'         => ProductionOrder::where('status', 'in_progress')->count(),
            'completed_production_orders'=> ProductionOrder::where('status', 'completed')->count(),
            'pending_production_orders'  => ProductionOrder::whereIn('status', ['pending', 'approved'])->count(),
            'late_production_orders'     => $lateProductionOrders,
            'period_qty_produced'        => $periodQtyProduced,
            'period_volume_m3'           => round($periodVolumeM3, 2),
            'production_objective'       => $productionObjective,
            'production_progress'        => $productionProgress,
            'production_yield'           => $productionYield,
            // Sales
            'total_sales_orders'         => SalesOrder::count(),
            'total_sales_amount'         => SalesOrder::sum('final_amount'),
            'pending_sales_orders'       => $pendingSalesOrders,
            'overdue_sales_orders'       => $overdueSalesOrders,
            // Finance
            'today_sales'                => (float) $todaySales,
            'today_expenses'             => (float) $todayExpenses,
            'period_sales'               => (float) $periodSales,
            'period_sales_count'         => $periodSalesCount,
            'period_expenses'            => (float) $periodExpenses,
            'period_profit'              => (float) $periodProfit,
            'period_margin_pct'          => $periodMargin,
            'total_expenses'             => Expense::sum('amount'),
            // Payments
            'completed_payments' => SalesOrderPayment::sum('amount'),
            // Machines
            'machines_breakdown'         => $machinesBreakdown,
            // Alerts
            'total_alerts'               => $totalAlerts,
            // Material stock value
            'total_material_value'       => (float) $totalMaterialValue,
        ];

        return view('pages.dashboard.index', compact(
            'stats',
            'periodStart',
            'periodEnd',
            'quickFilter',
            'periodLabel',
            'clientTypeStats',
            'clientMonthlyGrowth',
            'monthlySalesData',
            'monthlyExpensesData',
            'monthsLabels',
            'paymentStatusStats',
            'paymentMethodStats',
            'topSellingProducts',
            'lowSellingProducts',
            'topClients',
            'recentProductionOrders',
            'recentSalesOrders',
            'lowStockProducts',
            'lowStockMaterials',
            'machinesInMaint',
            'cashFlowData',
            // Checklist métier
            'dailyLabels',
            'dailySalesData',
            'dailyExpensesData',
            'dailyPayments',
            'prodCostPeriod',
            'prodCostMonth',
            'productionByProduct',
            'materialConsumption',
            'capacityByType',
            'echeances'
        ));
    }

    /**
     * Résout la période sélectionnée à partir de la requête.
     * Filtres rapides: today, this_week, last_week, this_month, last_month, all_time, custom.
     * Par défaut: aujourd'hui.
     *
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon, 2: string, 3: string}
     */
    private function resolvePeriod(Request $request)
    {
        $quickFilter = $request->get('quick_filter');
        $isQuick = in_array($quickFilter, ['today', 'this_week', 'last_week', 'this_month', 'last_month', 'all_time'], true);
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        // Plage personnalisée si des dates valides sont fournies (sauf si un filtre rapide est cliqué)
        if (!$isQuick && $dateFrom && $dateTo) {
            try {
                $start = Carbon::parse($dateFrom)->startOfDay();
                $end   = Carbon::parse($dateTo)->endOfDay();
                if ($start->gt($end)) {
                    [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                }
                $label = $start->isSameDay($end)
                    ? $start->translatedFormat('d M Y')
                    : $start->translatedFormat('d M Y') . ' — ' . $end->translatedFormat('d M Y');
                return [$start, $end, 'custom', $label];
            } catch (\Exception $e) {
                // Retombe sur le filtre rapide en cas de dates invalides
            }
        }

        switch ($quickFilter) {
            case 'this_week':
                return [now()->startOfWeek(), now()->endOfWeek(), 'this_week', 'Cette semaine'];
            case 'last_week':
                return [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek(), 'last_week', 'La semaine dernière'];
            case 'this_month':
                return [now()->startOfMonth(), now()->endOfMonth(), 'this_month', 'Ce mois-ci'];
            case 'last_month':
                return [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth(), 'last_month', 'Le mois dernier'];
            case 'all_time':
                return [Carbon::create(2000, 1, 1)->startOfDay(), now()->endOfDay(), 'all_time', 'Tout le temps'];
            case 'today':
            default:
                return [now()->startOfDay(), now()->endOfDay(), 'today', "Aujourd'hui"];
        }
    }

    /**
     * Calculate Raw Material Stock Value with Weighted Average Cost
     *
     * Weighted Average Cost = (Sum of (Quantity × Unit Price)) / Total Quantity
     * Then Stock Value = Total Quantity × Weighted Average Cost
     *
     * Example:
     * - Lot 1: 100 q × 10 DH = 1000 DH
     * - Lot 2: 20 q × 5 DH = 100 DH
     * - Total: 120 q, Total Value = 1100 DH
     * - Weighted Average = 1100 / 120 = 9.17 DH
     *
     * If Lot 2 is sold out, then remaining stock is 100 q × 10 DH = 1000 DH
     * Weighted Average = 1000 / 100 = 10 DH
     */
    private function calculateRawMaterialStockValue()
    {
        $stockDetails = StockMovementDetail::where('remaining_quantity', '>', 0)
            ->with('rawMaterial')
            ->get()
            ->groupBy('material_id');

        $totalValue = 0;
        $details = [];

        foreach ($stockDetails as $materialId => $detailsList) {
            $material = $detailsList->first()->rawMaterial;
            $materialName = $material ? $material->material_name : 'Unknown';
            $materialCode = $material ? $material->material_code : 'N/A';

            $totalQuantity = 0;
            $totalCost = 0;
            $lots = [];

            foreach ($detailsList as $detail) {
                $quantity = (float) $detail->remaining_quantity;
                $unitPrice = (float) $detail->unit_price;
                $totalCost += $quantity * $unitPrice;
                $totalQuantity += $quantity;

                $lots[] = [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $quantity * $unitPrice,
                ];
            }

            $weightedAverageCost = $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;
            $materialValue = $totalQuantity * $weightedAverageCost;
            $totalValue += $materialValue;

            $details[] = [
                'material_id' => $materialId,
                'material_name' => $materialName,
                'material_code' => $materialCode,
                'total_quantity' => $totalQuantity,
                'weighted_average_cost' => $weightedAverageCost,
                'total_value' => $materialValue,
                'lots' => $lots,
            ];
        }

        return [
            'total_value' => $totalValue,
            'details' => $details,
        ];
    }

    /**
     * Calculate Finished Product Stock Value
     * Uses the appropriate price from the pivot table for family products
     * For simple products (without families), uses product.price_client
     */
    private function calculateProductStockValue()
    {
        $totalValue = 0;
        $details = [];

        // Products with families (variant stock)
        // For these, we need to get the price from the pivot table
        $familyStocks = ProductFamilleStock::with(['product', 'famille'])
            ->where('current_quantity', '>', 0)
            ->get();

        foreach ($familyStocks as $familyStock) {
            if ($familyStock->product) {
                $quantity = (float) $familyStock->current_quantity;

                // Get the price from the pivot table for this specific famille
                $unitPrice = 0;
                $priceType = 'prix_client'; // Default to client price

                // Find the pivot relationship for this product and famille
                // Specify the table name to avoid ambiguity
                $pivot = $familyStock->product->familles()
                    ->where('product_famille.famille_id', $familyStock->famille_id)
                    ->first();

                if ($pivot) {
                    // Get price from pivot table
                    $unitPrice = (float) ($pivot->pivot->prix_client ?? 0);
                    $priceType = 'prix_client';
                } else {
                    // Fallback to product price if no pivot found
                    $unitPrice = (float) ($familyStock->product->price_client ?? 0);
                    $priceType = 'product_price_client';
                }

                $productValue = $quantity * $unitPrice;
                $totalValue += $productValue;

                $details[] = [
                    'product_id' => $familyStock->product->product_id,
                    'product_name' => $familyStock->product->product_name,
                    'product_code' => $familyStock->product->product_code,
                    'famille_id' => $familyStock->famille_id,
                    'famille_name' => $familyStock->famille_name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'price_type' => $priceType,
                    'total_value' => $productValue,
                    'type' => 'family_stock',
                ];
            }
        }

        // Products without families (simple stock)
        // Use product.price_client directly
        $productStocks = ProductStock::with('product')
            ->where('current_quantity', '>', 0)
            ->get();

        foreach ($productStocks as $productStock) {
            if ($productStock->product && !$productStock->product->familles()->exists()) {
                $quantity = (float) $productStock->current_quantity;
                $unitPrice = (float) ($productStock->product->price_client ?? 0);
                $productValue = $quantity * $unitPrice;
                $totalValue += $productValue;

                $details[] = [
                    'product_id' => $productStock->product->product_id,
                    'product_name' => $productStock->product->product_name,
                    'product_code' => $productStock->product->product_code,
                    'famille_name' => null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'price_type' => 'product_price_client',
                    'total_value' => $productValue,
                    'type' => 'simple_stock',
                ];
            }
        }

        return [
            'total_value' => $totalValue,
            'details' => $details,
        ];
    }

    /**
     * Get coverage rate CSS class
     */
    private function getCoverageRateClass($rate)
    {
        if ($rate >= 70) return 'success';
        if ($rate >= 50) return 'warning';
        if ($rate >= 30) return 'info';
        return 'danger';
    }

    private function getLowStockProducts()
    {
        $products = Product::with(['familleStocks', 'stock'])->active()->get();
        $lowStockProducts = collect();

        foreach ($products as $product) {
            if ($product->familleStocks()->exists()) {
                foreach ($product->familleStocks as $familleStock) {
                    if ($familleStock->available_quantity <= $product->min_stock_level) {
                        $copy = clone $product;
                        $copy->current_stock   = $familleStock->current_quantity;
                        $copy->available_stock = $familleStock->available_quantity;
                        $copy->famille_name    = $familleStock->famille_name;
                        $copy->famille_id      = $familleStock->famille_id;
                        $lowStockProducts->push($copy);
                    }
                }
            } else {
                if ($product->stock && $product->stock->available_quantity <= $product->min_stock_level) {
                    $product->current_stock   = $product->stock->current_quantity;
                    $product->available_stock = $product->stock->available_quantity;
                    $lowStockProducts->push($product);
                }
            }
        }

        return $lowStockProducts->sortBy('available_stock')->take(5);
    }

    private function getLowStockMaterials()
    {
        $materials = RawMaterial::where('is_active', true)->get();
        $lowStockMaterials = collect();

        foreach ($materials as $material) {
            $currentStock = StockMovementDetail::where('material_id', $material->material_id)
                ->sum('remaining_quantity');
            if ($currentStock <= $material->min_stock_level) {
                $material->current_stock = $currentStock;
                $lowStockMaterials->push($material);
            }
        }

        return $lowStockMaterials->sortBy('current_stock')->take(5);
    }


    private function getProductSalesStats()
    {
        return SalesOrderItem::whereIn('item_type', ['production', 'decoupage', 'finale'])
            ->select(
                'item_id',
                'item_name',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->groupBy('item_id', 'item_name')
            ->get();
    }

    private function getTopSellingProducts($limit = 5)
    {
        $stats = $this->getProductSalesStats();

        if ($stats->isEmpty()) {
            return collect();
        }

        $topProducts = $stats->sortByDesc('total_revenue')->take($limit);

        // Enrich with product codes
        $productIds = $stats->pluck('item_id')->unique()->toArray();
        $products = Product::whereIn('product_id', $productIds)->get()->keyBy('product_id');

        return $topProducts->map(function($item) use ($products) {
            $product = $products->get($item->item_id);
            $item->product_id = $item->item_id;
            $item->product_name = $item->item_name;
            $item->product_code = $product ? $product->product_code : $item->item_name;
            return $item;
        });
    }

    private function getLowSellingProducts($limit = 5)
    {
        $stats = $this->getProductSalesStats();

        if ($stats->isEmpty()) {
            return collect();
        }

        $lowProducts = $stats->sortBy('total_revenue')->take($limit);

        // Enrich with product codes
        $productIds = $stats->pluck('item_id')->unique()->toArray();
        $products = Product::whereIn('product_id', $productIds)->get()->keyBy('product_id');

        return $lowProducts->map(function($item) use ($products) {
            $product = $products->get($item->item_id);
            $item->product_id = $item->item_id;
            $item->product_name = $item->item_name;
            $item->product_code = $product ? $product->product_code : $item->item_name;
            return $item;
        });
    }

    private function getProductionStatusBadge($status)
    {
        $badges = ['draft' => 'secondary', 'pending' => 'warning', 'approved' => 'info', 'in_progress' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'];
        $labels = ['draft' => 'Brouillon', 'pending' => 'En attente', 'approved' => 'Approuvé', 'in_progress' => 'En cours', 'completed' => 'Terminé', 'cancelled' => 'Annulé'];
        return '<span class="badge bg-' . ($badges[$status] ?? 'secondary') . '">' . ($labels[$status] ?? $status) . '</span>';
    }

    private function getSalesStatusBadge($status)
    {
        $badges = ['draft' => 'secondary', 'pending' => 'warning', 'confirmed' => 'info', 'processing' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'];
        $labels = ['draft' => 'Brouillon', 'pending' => 'En attente', 'confirmed' => 'Confirmé', 'processing' => 'En traitement', 'completed' => 'Payé', 'cancelled' => 'Annulé'];
        return '<span class="badge bg-' . ($badges[$status] ?? 'secondary') . '">' . ($labels[$status] ?? $status) . '</span>';
    }
}
