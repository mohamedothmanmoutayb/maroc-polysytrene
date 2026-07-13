<?php

namespace App\Http\Controllers;

use App\Helpers\ProductionOrderNotificationHelper;
use App\Models\ProductionOrder;
use App\Models\Product;
use App\Models\BillOfMaterial;
use App\Models\Employee;
use App\Models\Famille;
use App\Models\ProductFamilleStock;
use App\Models\ProductionConsumption;
use App\Models\ProductionOutput;
use App\Models\ProductionWaste;
use App\Models\ProductStock;
use App\Models\ProductStockMovement;
use App\Models\RawMaterial;
use App\Models\RawMaterialCategory;
use App\Models\RawMaterialStockMovement;
use App\Models\StockMovementDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductionOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_production_orders')->only(['index', 'show', 'printOrder', 'apiShow', 'getStatistics', 'dashboardStatistics', 'getBom', 'getConversions', 'getSourceProducts', 'getFinalProducts', 'getOrderBom', 'getWastes', 'getOutputSummary', 'getBomForMaterial', 'getConsumedBlocks', 'getProductFamilles', 'getFamilles', 'getSourceProductFamilles']);
        $this->middleware('can:create_production_orders')->only(['create', 'store']);
        $this->middleware('can:edit_production_orders')->only(['edit', 'update', 'editOrder', 'cancelProduction']);
        $this->middleware('can:delete_production_orders')->only(['destroy']);
        $this->middleware('can:approve_production_orders')->only(['approve']);
        $this->middleware('can:start_production_orders')->only(['start']);
        $this->middleware('can:complete_production_orders')->only(['complete', 'completeWithConsumption']);
        $this->middleware('can:declare_production_waste')->only(['handleWasteDeclaration', 'getOrdersNeedingWasteDeclaration']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $orders = ProductionOrder::with(['product', 'creator', 'outputs', 'famille', 'wastes', 'sourceProduct'])
                ->select('production_orders.*')
                ->when($request->filled('status'), function ($query) use ($request) {
                    return $query->where('status', $request->status);
                })
                ->when($request->filled('production_type'), function ($query) use ($request) {
                    return $query->where('production_type', $request->production_type);
                })
                ->when($request->filled('priority'), function ($query) use ($request) {
                    return $query->where('priority', $request->priority);
                })
                ->when($request->filled('order_number'), function ($query) use ($request) {
                    return $query->where('order_number', 'like', '%' . $request->order_number . '%');
                })
                ->when($request->filled('product_id'), function ($query) use ($request) {
                    return $query->where('product_id', $request->product_id);
                })
                ->when($request->filled('responsible_employee_id'), function ($query) use ($request) {
                    return $query->where('responsible_employee_id', $request->responsible_employee_id);
                })
                ->when($request->filled('has_waste'), function ($query) use ($request) {
                    if ($request->has_waste === 'has_waste') {
                        return $query->has('wastes');
                    } elseif ($request->has_waste === 'needs_waste') {
                        return $query->where('status', 'in_progress')
                            ->whereDoesntHave('wastes')
                            ->whereHas('outputs', function($q) {
                                $q->havingRaw('SUM(quantity_produced) >= quantity_to_produce');
                            });
                    } elseif ($request->has_waste === 'no_waste') {
                        return $query->doesntHave('wastes');
                    }
                    return $query;
                })
                ->when($request->filled('date_range'), function ($query) use ($request) {
                    $dates = array_map('trim', explode(' - ', $request->date_range));

                    if (count($dates) == 2) {
                        $start = Carbon::createFromFormat('d/m/Y', $dates[0])->startOfDay();
                        $end = Carbon::createFromFormat('d/m/Y', $dates[1])->endOfDay();

                        return $query->whereBetween('start_date', [$start, $end]);
                    }

                    return $query->whereDate('start_date', Carbon::createFromFormat('d/m/Y', $dates[0]));
                });

            return DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    return view('pages.production-orders.components.actions', ['order' => $row])->render();
                })
                ->addColumn('product_name', function($row){
                    if (!$row->product) return 'N/A';

                    if ($row->production_type === 'type2') {
                        $subProducts = $row->getType2Products();
                        if ($subProducts->count() > 0) {
                            $first2 = $subProducts->take(2)->pluck('product_name')->toArray();
                            $rest = max(0, $subProducts->count() - 2);
                            $names = implode(', ', $first2);
                            $extra = $rest > 0 ? ' <span class="badge bg-secondary">+' . $rest . '</span>' : '';
                            $sourceName = $row->sourceProduct ? e($row->sourceProduct->product_name) : '';
                            return '<div class="d-flex flex-column">'
                                . ($sourceName ? '<div class="text-muted small">' . $sourceName . '</div>' : '')
                                . '<div>' . e($names) . $extra . '</div>'
                                . '</div>';
                        }
                    }

                    if ($row->production_type === 'type3') {
                        $subProducts = $row->getType3Products();
                        if ($subProducts->count() > 0) {
                            $first2 = $subProducts->take(2)->pluck('product_name')->toArray();
                            $rest = max(0, $subProducts->count() - 2);
                            $names = implode(', ', $first2);
                            $extra = $rest > 0 ? ' <span class="badge bg-secondary">+' . $rest . '</span>' : '';
                            $sourceName = $row->sourceProduct ? e($row->sourceProduct->product_name) : '';
                            return '<div class="d-flex flex-column">'
                                . ($sourceName ? '<div class="text-muted small">' . $sourceName . '</div>' : '')
                                . '<div>' . e($names) . $extra . '</div>'
                                . '</div>';
                        }
                    }

                    if ($row->production_type === 'type5') {
                        $subProducts = $row->getType5Products();
                        if ($subProducts->count() > 0) {
                            $first2 = $subProducts->take(2)->pluck('product_name')->toArray();
                            $rest = max(0, $subProducts->count() - 2);
                            $names = implode(', ', $first2);
                            $extra = $rest > 0 ? ' <span class="badge bg-secondary">+' . $rest . '</span>' : '';
                            return '<div class="d-flex flex-column">'
                                . '<div class="text-muted small">' . number_format($row->chutes_volume ?? 0, 4) . ' m³ chutes</div>'
                                . '<div>' . e($names) . $extra . '</div>'
                                . '</div>';
                        }
                    }

                    return '<div class="d-flex flex-column">
                        <div>' . e($row->product->product_name) . '</div>
                    </div>';
                })
                ->addColumn('progress', function($row){
                    if ($row->status === 'completed') {
                        return '100';
                    }

                    if ($row->production_type === 'decoupage') {
                        $progress = $this->calculateDecoupageProgress($row);
                    } else {
                        $totalProduced = $row->outputs->sum('quantity_produced');
                        $progress = $row->quantity_to_produce > 0 ? ($totalProduced / $row->quantity_to_produce) * 100 : 0;
                    }

                    return number_format(min($progress, 100), 1);
                })
                ->addColumn('remaining_quantity', function($row){
                    $remaining = $this->calculateRemainingQuantity($row);

                    if ($row->production_type == 'type2') {
                        return $remaining . ' sous-blocs';
                    } elseif ($row->production_type == 'type3') {
                        return $remaining . ' unités';
                    } elseif ($row->production_type == 'type4') {
                        return $remaining . ' unités';
                    } elseif ($row->production_type == 'type5') {
                        return $remaining . ' unités';
                    } else {
                        return $remaining . ' blocs';
                    }
                })
                ->addColumn('waste_info', function($row){
                    $wastes = $row->wastes ?? collect();

                    if ($wastes->isEmpty()) {
                        return '<span class="text-muted">Aucune chute</span>';
                    }

                    $totalVolume = $wastes->sum('volume_m3');
                    $recyclableVolume = $wastes->where('waste_type', 'recyclable')->sum('volume_m3');
                    $wasteVolume = $wastes->where('waste_type', 'waste')->sum('volume_m3');

                    $html = '<div class="small waste-info-cell">';
                    $html .= '<div><strong>Total:</strong> ' . number_format($totalVolume, 4) . ' m³</div>';

                    if ($recyclableVolume > 0) {
                        $html .= '<div class="text-success"><i class="fas fa-recycle me-1"></i>' . number_format($recyclableVolume, 4) . ' m³</div>';
                    }

                    if ($wasteVolume > 0) {
                        $html .= '<div class="text-danger"><i class="fas fa-trash me-1"></i>' . number_format($wasteVolume, 4) . ' m³</div>';
                    }

                    $html .= '<div class="text-muted mt-1">' . $wastes->count() . ' chute(s)</div>';
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('status_badge', function($row){
                    $badges = [
                        'pending' => '<span class="badge bg-warning">En attente</span>',
                        'approved' => '<span class="badge bg-info">Approuvé</span>',
                        'in_progress' => '<span class="badge bg-primary">En cours</span>',
                        'completed' => '<span class="badge bg-success">Terminé</span>',
                        'cancelled' => '<span class="badge bg-danger">Annulé</span>',
                    ];

                    $badge = $badges[$row->status] ?? '<span class="badge bg-secondary">Inconnu</span>';

                    if ($row->status === 'cancelled') {
                        if ($row->cancelled_at) {
                            $badge .= '<div class="small text-muted mt-1">' .
                                '<i class="fas fa-calendar-times me-1"></i>' . $row->cancelled_at->format('d/m/Y H:i') .
                                '</div>';
                        }
                        if ($row->cancellation_reason) {
                            $shortReason = strlen($row->cancellation_reason) > 40
                                ? substr($row->cancellation_reason, 0, 40) . '…'
                                : $row->cancellation_reason;
                            $badge .= '<div class="small text-danger" title="' . e($row->cancellation_reason) . '">' .
                                '<i class="fas fa-comment-dots me-1"></i>' . e($shortReason) .
                                '</div>';
                        }
                    }

                    return $badge;
                })
                ->addColumn('priority_badge', function($row){
                    $badges = [
                        'low' => '<span class="badge bg-secondary">Basse</span>',
                        'medium' => '<span class="badge bg-info">Moyenne</span>',
                        'high' => '<span class="badge bg-warning">Haute</span>',
                        'urgent' => '<span class="badge bg-danger">Urgente</span>',
                    ];

                    return $badges[$row->priority] ?? '<span class="badge bg-secondary">Inconnu</span>';
                })
                ->addColumn('has_waste_declaration', function($row) {
                    return $row->wastes->count() > 0 ? true : false;
                })
                ->editColumn('start_date', function($row){
                    if (!$row->start_date) return 'N/A';

                    return '<div>' . $row->start_date->format('d/m/Y') . '</div>';
                })
                ->orderColumn('status', function($query, $order) {
                    $query->orderByRaw("FIELD(status, 'pending', 'approved', 'in_progress', 'completed', 'cancelled') $order");
                })
                ->orderColumn('priority', function($query, $order) {
                    $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low') $order");
                })
                ->rawColumns(['action', 'status_badge', 'priority_badge', 'production_type_badge',
                            'progress', 'waste_info', 'remaining_quantity', 'product_name',
                            'start_date', 'expected_completion_date'])
                ->make(true);
        }

        $products = Product::where('is_active', true)->get();
        $employees = Employee::whereNull('resignation_date')->orderBy('full_name')->get();

        return view('pages.production-orders.index', compact('products', 'employees'));
    }

    private function calculateDecoupageProgress(ProductionOrder $order)
    {
        $progress = 0;

        // Decoupage phase (0-50%)
        if ($order->is_decoupage_completed) {
            $progress = 50;
        } else {
            $decoupageOutput = $order->decoupageOutputs->sum('quantity_produced');
            if ($order->required_quantity > 0) {
                $progress = min(($decoupageOutput / $order->required_quantity) * 50, 50);
            }
        }

        // Conversion phase (50-100%)
        if ($order->is_decoupage_completed) {
            $conversionOutput = $order->conversionOutputs->sum('quantity_produced');
            if ($order->quantity_to_produce > 0) {
                $conversionProgress = min(($conversionOutput / $order->quantity_to_produce) * 50, 50);
                $progress += $conversionProgress;
            }
        }

        return number_format(min($progress, 100), 1);
    }

    public function create(Request $request)
    {
        // For type1, get production products as final
        $productionProducts = Product::where('is_active', true)
            ->whereIn('product_type', ['production', 'both'])
            ->orderBy('product_name')
            ->get();

        // For type2, get decoupage products as final
        $decoupageProducts = Product::where('is_active', true)
            ->where('product_type', 'decoupage')
            ->orderBy('product_name')
            ->get();

        // For type3, get sales products as final
        $salesProducts = Product::where('is_active', true)
            ->whereIn('product_type', ['finale', 'both'])
            ->orderBy('product_name')
            ->get();

        $familles = Famille::where('is_active', true)->orderBy('famille_name')->get();

        $employees = Employee::whereNull('resignation_date')->orderBy('full_name')->get();

        $product_id = $request->get('product_id');

        if ($product_id) {
            $product = Product::findOrFail($product_id);
            $bom = BillOfMaterial::where('product_id', $product_id)
                ->with('rawMaterial')
                ->get();

            return view('pages.production-orders.create', compact(
                'productionProducts',
                'decoupageProducts',
                'salesProducts',
                'product',
                'familles',
                'bom',
                'employees'
            ));
        }

        return view('pages.production-orders.create', compact(
            'productionProducts',
            'decoupageProducts',
                'familles',
            'salesProducts',
            'employees'
        ));
    }

    /**
     * Store created production order in storage.
     */
    public function store(Request $request)
    {
        if ($request->production_type === 'type1') {
            $request->validate([
                'product_id' => 'required|exists:products,product_id',
                'famille_id' => 'nullable|exists:familles,famille_id',
                'quantity_to_produce' => 'required|numeric|min:0.01',
                'priority' => 'required|in:low,medium,high,urgent',
                'start_date' => 'required|date',
                'expected_completion_date' => 'required',
                'production_type' => 'required|in:type1,type2,type3,type4',
                'material_source' => 'nullable|in:bom_only,chutes_only,both',
                'chutes_volume' => 'nullable|numeric|min:0',
                'total_cost' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:500',
                'responsible_employee_id' => 'nullable|exists:employees,employee_id',
                'bom_consumptions' => 'nullable|array',
                'bom_consumptions.*.material_id' => 'required_with:bom_consumptions.*.planned_quantity|exists:raw_materials,material_id',
                'bom_consumptions.*.planned_quantity' => 'required_with:bom_consumptions.*.material_id|numeric|min:0',
                'bom_consumptions.*.quantity_required' => 'nullable|numeric|min:0',
                'bom_consumptions.*.save_to_product' => 'nullable|boolean',
                'bom_consumptions.*.remove_from_product' => 'nullable|boolean',
            ]);
        } elseif ($request->production_type === 'type2') {
            $request->validate([
                'source_product_id' => 'required|exists:products,product_id',
                'type2_total_blocks' => 'required|numeric|min:0.01',
                'type2_products' => 'required|array|min:1',
                'type2_products.*.product_id' => 'required|exists:products,product_id',
                'type2_products.*.quantity_to_produce' => 'required|numeric|min:0.01',
                'source_famille_id' => 'nullable|exists:familles,famille_id',
                'priority' => 'required|in:low,medium,high,urgent',
                'start_date' => 'required|date',
                'expected_completion_date' => 'required|date',
                'production_type' => 'required|in:type1,type2,type3,type4',
                'notes' => 'nullable|string|max:500',
                'responsible_employee_id' => 'nullable|exists:employees,employee_id',
            ]);
        } elseif ($request->production_type === 'type3') {
            $request->validate([
                'type3_source_products' => 'required|array|min:1',
                'type3_source_products.*.product_id' => 'required|exists:products,product_id',
                'type3_source_products.*.quantity' => 'required|numeric|min:0.01',
                'type3_products' => 'required|array|min:1',
                'type3_products.*.product_id' => 'required|exists:products,product_id',
                'type3_products.*.quantity_to_produce' => 'required|numeric|min:0.01',
                'source_famille_id' => 'nullable|exists:familles,famille_id',
                'famille_id' => 'nullable|exists:familles,famille_id',
                'priority' => 'required|in:low,medium,high,urgent',
                'start_date' => 'required|date',
                'expected_completion_date' => 'required|date',
                'production_type' => 'required|in:type1,type2,type3,type4',
                'notes' => 'nullable|string|max:500',
                'responsible_employee_id' => 'nullable|exists:employees,employee_id',
            ]);
       } elseif ($request->production_type === 'type4') {
            $request->validate([
                'source_product_id' => 'required|exists:products,product_id',
                'type4_total_units' => 'required|numeric|min:0.01',
                'type4_products' => 'required|array|min:1',
                'type4_products.*.product_id' => 'required|exists:products,product_id',
                'type4_products.*.quantity_to_produce' => 'required|numeric|min:0.01',
                'famille_id' => 'nullable|exists:familles,famille_id',
                'priority' => 'required|in:low,medium,high,urgent',
                'start_date' => 'required|date',
                'expected_completion_date' => 'required|date',
                'production_type' => 'required|in:type1,type2,type3,type4,type5',
                'notes' => 'nullable|string|max:500',
                'responsible_employee_id' => 'nullable|exists:employees,employee_id',
            ]);
        } elseif ($request->production_type === 'type5') {
            $request->validate([
                'chutes_volume' => 'required|numeric|min:0.01',
                'type5_products' => 'required|array|min:1',
                'type5_products.*.product_id' => 'required|exists:products,product_id',
                'type5_products.*.quantity_to_produce' => 'required|numeric|min:0.01',
                'famille_id' => 'required|exists:familles,famille_id',
                'priority' => 'required|in:low,medium,high,urgent',
                'start_date' => 'required|date',
                'expected_completion_date' => 'required|date',
                'production_type' => 'required|in:type1,type2,type3,type4,type5',
                'notes' => 'nullable|string|max:500',
                'responsible_employee_id' => 'nullable|exists:employees,employee_id',
                'force_chutes' => 'nullable|boolean',
            ]);
        } else {
            throw new \Exception("Type de production invalide.");
        }

        if ($request->production_type === 'type2') {
            $sourceProduct = Product::find($request->source_product_id);
            $sourceVolume = $this->calculateProductVolume($sourceProduct);
            $totalSourceVolume = $request->type2_total_blocks * $sourceVolume;

            $totalProducedVolume = 0;
            foreach ($request->type2_products as $productData) {
                $product = Product::find($productData['product_id']);
                $productVolume = $this->calculateProductVolume($product);
                $totalProducedVolume += $productData['quantity_to_produce'] * $productVolume;
            }

            if ($totalProducedVolume > ($totalSourceVolume + 0.0001)) {
                throw new \Exception(
                    "Le volume total des produits ({$totalProducedVolume} m³) " .
                    "dépasse le volume source disponible ({$totalSourceVolume} m³). " .
                    "Veuillez ajuster les quantités."
                );
            }
        }

        if ($request->production_type === 'type3') {
            if (!$request->has('type3_source_products') || count($request->type3_source_products) < 1) {
                throw new \Exception("Au moins un sous-bloc source est requis.");
            }
        }

        DB::beginTransaction();
        try {
            $year = date('Y');
            $month = date('m');
            $lastOrder = ProductionOrder::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->orderBy('order_id', 'desc')
                ->first();

            $orderNumber = 'PO-' . $year . $month . '-' . str_pad(
                $lastOrder ? (int)substr($lastOrder->order_number, -4) + 1 : 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            $finalFamille = null;
            $sourceFamille = null;
            $finalProduct = null;
            $sourceProduct = null;
            $requiredQuantity = 0;
            $wastePercentage = $request->waste_percentage ?? 0;
            $totalCost = $request->total_cost ?? 0;
            $materialSource = $request->material_source ?? 'bom_only';
            $chutesVolume = $request->chutes_volume ?? 0;

            $sourceVolume = 0;
            $finalVolume = 0;
            $totalVolumeProduced = 0;
            $wasteVolume = 0;

            $productsData = [];
            $resolvedSourceProducts = null;

            $bomItemsToSave = [];
            $bomItemsToRemove = [];
            $bomItemsToUpdate = [];

            switch ($request->production_type) {
                case 'type1':
                    $finalProduct = Product::findOrFail($request->product_id);

                    if (!$finalProduct->isProductionProduct() && $finalProduct->product_type !== 'both') {
                        throw new \Exception("Le produit doit être de type production pour la production directe.");
                    }

                    if ($finalProduct->has_familles && !$request->famille_id) {
                        throw new \Exception("Ce produit a des familles. Veuillez sélectionner une famille de destination.");
                    }

                    $requiredQuantity = (float) $request->quantity_to_produce;

                    // Process BOM items...
                    $currentBomMaterialIds = BillOfMaterial::where('product_id', $finalProduct->product_id)
                        ->pluck('material_id')
                        ->toArray();

                    $requestMaterialIds = [];

                    if ($request->has('bom_consumptions')) {
                        foreach ($request->bom_consumptions as $materialId => $consumption) {
                            if (isset($consumption['planned_quantity']) && $consumption['planned_quantity'] > 0) {
                                $requestMaterialIds[] = $materialId;
                                $shouldSaveToBom = false;

                                if (isset($consumption['save_to_product']) && $consumption['save_to_product'] == 1) {
                                    $shouldSaveToBom = true;
                                } else {
                                    $existingBom = BillOfMaterial::where('product_id', $finalProduct->product_id)
                                        ->where('material_id', $materialId)
                                        ->exists();
                                    if (!$existingBom) {
                                        $shouldSaveToBom = true;
                                    }
                                }

                                if ($shouldSaveToBom) {
                                    $bomItemsToSave[] = [
                                        'material_id' => $materialId,
                                        'quantity_required' => $consumption['quantity_required'] ?? 1,
                                    ];
                                }

                                if (isset($consumption['update_quantity']) && $consumption['update_quantity'] == 1) {
                                    $bomItemsToUpdate[] = [
                                        'material_id' => $materialId,
                                        'quantity_required' => $consumption['quantity_required'] ?? 1,
                                    ];
                                }
                            }
                        }
                    }

                    foreach ($currentBomMaterialIds as $currentMaterialId) {
                        if (!in_array($currentMaterialId, $requestMaterialIds)) {
                            $bomItemsToRemove[] = $currentMaterialId;
                        }
                    }

                    if (!empty($bomItemsToRemove)) {
                        foreach ($bomItemsToRemove as $materialIdToRemove) {
                            BillOfMaterial::where('product_id', $finalProduct->product_id)
                                ->where('material_id', $materialIdToRemove)
                                ->delete();
                        }
                    }

                    if (!empty($bomItemsToUpdate)) {
                        foreach ($bomItemsToUpdate as $updateItem) {
                            BillOfMaterial::where('product_id', $finalProduct->product_id)
                                ->where('material_id', $updateItem['material_id'])
                                ->update([
                                    'quantity_required' => $updateItem['quantity_required'],
                                    'updated_at' => now(),
                                ]);
                        }
                    }
                    break;

                case 'type2':
                    if (!$request->source_product_id) {
                        throw new \Exception("Le produit source est requis pour le découpage.");
                    }

                    $sourceProduct = Product::findOrFail($request->source_product_id);

                    if (!$sourceProduct->isProductionProduct() && $sourceProduct->product_type !== 'both') {
                        throw new \Exception("Le produit source doit être de type production pour le découpage.");
                    }

                    if ($sourceProduct->has_familles) {
                        if ($request->source_famille_id) {
                            $sourceFamille = Famille::find($request->source_famille_id);
                        } else {
                            $sourceFamille = $this->getOrCreateFamille(
                                $sourceProduct,
                                $sourceProduct->product_name . ' - Default',
                                'DFT_' . $sourceProduct->product_code
                            );
                        }
                    }

                    $totalSourceRequired = (float) $request->type2_total_blocks;
                    $totalQuantityToProduce = 0;
                    $totalVolume = 0;

                    foreach ($request->type2_products as $index => $productData) {
                        $decoupageProduct = Product::findOrFail($productData['product_id']);

                        if (!$decoupageProduct->isDecoupageProduct() && $decoupageProduct->product_type !== 'decoupage') {
                            throw new \Exception("Le produit #" . ($index + 1) . " doit être de type découpage.");
                        }

                        $quantityToProduce = $productData['quantity_to_produce'];
                        $productVolume = $this->calculateProductVolume($decoupageProduct);
                        $productTotalVolume = $quantityToProduce * $productVolume;

                        $totalQuantityToProduce += $quantityToProduce;
                        $totalVolume += $productTotalVolume;

                        $productsData[] = [
                            'product_id' => $decoupageProduct->product_id,
                            'product' => $decoupageProduct,
                            'quantity_to_produce' => $quantityToProduce,
                            'volume_per_unit' => $productVolume,
                            'total_volume' => $productTotalVolume,
                        ];
                    }

                    $firstDecoupageProduct = Product::find($productsData[0]['product_id']);
                    if ($request->source_famille_id) {
                        $finalFamille = Famille::find($request->source_famille_id);
                    } else {
                        throw new \Exception("Veuillez sélectionner une famille de destination pour les produits de découpage.");
                    }

                    $sourceVolume = $this->calculateProductVolume($sourceProduct);
                    $totalSourceVolume = $totalSourceRequired * $sourceVolume;
                    $wasteVolume = max(0, $totalSourceVolume - $totalVolume);

                    $requiredQuantity = $totalSourceRequired;
                    $finalProduct = $firstDecoupageProduct;
                    break;

                case 'type3':
                    // Multiple sous-blocs sources → multiple produits finaux
                    $totalSourceVolume = 0;
                    $totalSousBlocsRequired = 0;
                    $firstSourceProduct = null;
                    $resolvedSourceProducts = [];

                    foreach ($request->type3_source_products as $sbIndex => $sbData) {
                        $sbProduct = Product::findOrFail($sbData['product_id']);

                        if (!$sbProduct->isDecoupageProduct() && $sbProduct->product_type !== 'decoupage') {
                            throw new \Exception("Le sous-bloc #" . ($sbIndex + 1) . " doit être de type découpage.");
                        }

                        $sbQty = (float) $sbData['quantity'];
                        $sbVolume = $this->calculateProductVolume($sbProduct) * $sbQty;
                        $totalSourceVolume += $sbVolume;
                        $totalSousBlocsRequired += $sbQty;

                        $resolvedSourceProducts[] = [
                            'product_id' => $sbProduct->product_id,
                            'product_name' => $sbProduct->product_name,
                            'quantity' => $sbQty,
                        ];

                        if ($sbIndex === 0) {
                            $firstSourceProduct = $sbProduct;
                        }
                    }

                    $sourceProduct = $firstSourceProduct;

                    $totalQuantityToProduce = 0;
                    $totalVolume = 0;
                    $productsData = [];

                    foreach ($request->type3_products as $index => $productData) {
                        $finalProductItem = Product::findOrFail($productData['product_id']);

                        if (!$finalProductItem->isFinaleProduct() && $finalProductItem->product_type !== 'both') {
                            throw new \Exception("Le produit final #" . ($index + 1) . " doit être de type vente (finale).");
                        }

                        $quantityToProduce = $productData['quantity_to_produce'];
                        $productVolume = $this->calculateProductVolume($finalProductItem);
                        $productTotalVolume = $quantityToProduce * $productVolume;

                        $totalQuantityToProduce += $quantityToProduce;
                        $totalVolume += $productTotalVolume;

                        $productsData[] = [
                            'product_id' => $finalProductItem->product_id,
                            'product' => $finalProductItem,
                            'quantity_to_produce' => $quantityToProduce,
                            'volume_per_unit' => $productVolume,
                            'total_volume' => $productTotalVolume,
                        ];
                    }

                    if ($totalVolume > ($totalSourceVolume + 0.0001)) {
                        throw new \Exception(
                            "Le volume total des produits ({$totalVolume} m³) " .
                            "dépasse le volume source disponible ({$totalSourceVolume} m³). " .
                            "Veuillez ajuster les quantités."
                        );
                    }

                    $wasteVolume = max(0, $totalSourceVolume - $totalVolume);
                    $wastePercentage = $totalSourceVolume > 0 ? ($wasteVolume / $totalSourceVolume * 100) : 0;

                    if ($request->source_famille_id) {
                        $finalFamille = Famille::find($request->source_famille_id);
                        $sourceFamille = $finalFamille;
                    } else {
                        $finalFamille = null;
                        $sourceFamille = null;
                    }

                    // Stock check for each sous-bloc
                    foreach ($request->type3_source_products as $sbData) {
                        $sbProduct = Product::findOrFail($sbData['product_id']);
                        $this->checkSourceProductStock($sbProduct, (float) $sbData['quantity'],
                            $sourceFamille ? $sourceFamille->famille_id : null);
                    }

                    $requiredQuantity = $totalSousBlocsRequired;

                    $request->merge(['quantity_to_produce' => $totalQuantityToProduce]);
                    $request->merge(['required_quantity' => $totalSousBlocsRequired]);
                    // $resolvedSourceProducts is passed directly to prepareAdditionalData

                    $sourceVolume = $totalSourceVolume > 0 && $totalSousBlocsRequired > 0
                        ? $totalSourceVolume / $totalSousBlocsRequired
                        : 0;
                    $finalVolume = 0;
                    break;

                case 'type4':
                    if (!$request->source_product_id) {
                        throw new \Exception("Le produit source est requis pour la transformation.");
                    }

                    $sourceProduct = Product::findOrFail($request->source_product_id);

                    if (!$sourceProduct->isFinaleProduct() && $sourceProduct->product_type !== 'both') {
                        throw new \Exception("Le produit source doit être de type vente (finale) pour la transformation.");
                    }

                    $sourceVolumePerUnit = $this->calculateProductVolume($sourceProduct);
                    $totalUnitsRequired = (float) $request->type4_total_units;
                    $totalSourceVolume = $totalUnitsRequired * $sourceVolumePerUnit;

                    $totalQuantityToProduce = 0;
                    $totalVolume = 0;
                    $productsData = [];

                    foreach ($request->type4_products as $index => $productData) {
                        $finalProductItem = Product::findOrFail($productData['product_id']);

                        if (!$finalProductItem->isFinaleProduct() && $finalProductItem->product_type !== 'both') {
                            throw new \Exception("Le produit final #" . ($index + 1) . " doit être de type vente (finale).");
                        }

                        $quantityToProduce = $productData['quantity_to_produce'];
                        $productVolume = $this->calculateProductVolume($finalProductItem);
                        $productTotalVolume = $quantityToProduce * $productVolume;

                        $totalQuantityToProduce += $quantityToProduce;
                        $totalVolume += $productTotalVolume;

                        if ($totalVolume > ($totalSourceVolume + 0.0001)) {
                            throw new \Exception(
                                "Le volume total des produits ({$totalVolume} m³) " .
                                "dépasse le volume source disponible ({$totalSourceVolume} m³). " .
                                "Veuillez ajuster les quantités."
                            );
                        }

                        $productsData[] = [
                            'product_id' => $finalProductItem->product_id,
                            'product' => $finalProductItem,
                            'quantity_to_produce' => $quantityToProduce,
                            'volume_per_unit' => $productVolume,
                            'total_volume' => $productTotalVolume,
                        ];
                    }

                    if ($totalVolume > ($totalSourceVolume + 0.0001)) {
                        throw new \Exception(
                            "Le volume total des produits ({$totalVolume} m³) " .
                            "dépasse le volume source disponible ({$totalSourceVolume} m³). " .
                            "Veuillez ajuster les quantités."
                        );
                    }

                    $wasteVolume = max(0, $totalSourceVolume - $totalVolume);
                    $wastePercentage = $totalSourceVolume > 0 ? ($wasteVolume / $totalSourceVolume * 100) : 0;

                    $finalFamille = null;
                    if ($sourceProduct->has_familles) {
                        if ($request->famille_id) {
                            $finalFamille = Famille::find($request->famille_id);
                        } else {
                            throw new \Exception("Veuillez sélectionner une famille pour ce produit.");
                        }
                    }

                    $requiredQuantity = $totalUnitsRequired;

                    $this->checkSourceProductStock($sourceProduct, $requiredQuantity,
                        $finalFamille ? $finalFamille->famille_id : null);

                    $request->merge(['quantity_to_produce' => $totalQuantityToProduce]);
                    $request->merge(['required_quantity' => $totalUnitsRequired]);

                    $sourceVolume = $sourceVolumePerUnit;
                    $finalVolume = 0;

                    $request->merge(['source_famille_id' => $finalFamille ? $finalFamille->famille_id : null]);

                    break;

                case 'type5':
                    // Chutes de production → Produits finaux (multiple)
                    $chutesVolume = (float) ($request->chutes_volume ?? 0);

                    if ($chutesVolume <= 0) {
                        throw new \Exception("Veuillez saisir un volume de chutes valide.");
                    }

                    $forceChutesOverride = $request->boolean('force_chutes');

                    if (!$forceChutesOverride) {
                        $this->checkChutesAvailability($chutesVolume);
                    }

                    $totalQuantityToProduce = 0;
                    $totalVolume = 0;
                    $productsData = [];

                    foreach ($request->type5_products as $index => $productData) {
                        $finalProductItem = Product::findOrFail($productData['product_id']);

                        if (!$finalProductItem->isFinaleProduct() && $finalProductItem->product_type !== 'both') {
                            throw new \Exception("Le produit final #" . ($index + 1) . " doit être de type vente (finale).");
                        }

                        $quantityToProduce = $productData['quantity_to_produce'];
                        $productVolume = $this->calculateProductVolume($finalProductItem);
                        $productTotalVolume = $quantityToProduce * $productVolume;

                        $totalQuantityToProduce += $quantityToProduce;
                        $totalVolume += $productTotalVolume;

                        $productsData[] = [
                            'product_id' => $finalProductItem->product_id,
                            'product' => $finalProductItem,
                            'quantity_to_produce' => $quantityToProduce,
                            'volume_per_unit' => $productVolume,
                            'total_volume' => $productTotalVolume,
                        ];
                    }

                    if ($totalVolume > ($chutesVolume + 0.0001)) {
                        throw new \Exception(
                            "Le volume total des produits ({$totalVolume} m³) " .
                            "dépasse le volume de chutes alloué ({$chutesVolume} m³). " .
                            "Veuillez ajuster (augmenter) le volume de chutes ou réduire les quantités."
                        );
                    }

                    $wasteVolume = max(0, $chutesVolume - $totalVolume);
                    $wastePercentage = $chutesVolume > 0 ? ($wasteVolume / $chutesVolume * 100) : 0;

                    if ($request->famille_id) {
                        $finalFamille = Famille::find($request->famille_id);
                    } else {
                        throw new \Exception("Veuillez sélectionner une famille de destination.");
                    }

                    $requiredQuantity = $chutesVolume;
                    $materialSource = 'chutes_only';

                    $request->merge(['quantity_to_produce' => $totalQuantityToProduce]);
                    $request->merge(['required_quantity' => $chutesVolume]);

                    $sourceVolume = $chutesVolume;
                    $finalVolume = 0;
                    break;

                default:
                    throw new \Exception("Type de production invalide.");
            }

            $productionOrderData = [
                'order_number' => $orderNumber,
                'product_id' => in_array($request->production_type, ['type3', 'type4', 'type5'])
                    ? ($productsData[0]['product_id'] ?? null)
                    : ($finalProduct ? $finalProduct->product_id : null),
                'famille_id' => $request->production_type === 'type4'
                    ? ($request->famille_id ?? null)
                    : ($finalFamille ? $finalFamille->famille_id : ($request->famille_id ?? null)),
                'source_product_id' => $request->production_type === 'type4'
                    ? $request->source_product_id
                    : ($sourceProduct ? $sourceProduct->product_id : null),
                'source_famille_id' => $request->production_type === 'type4'
                    ? ($request->famille_id ?? null)
                    : ($sourceFamille ? $sourceFamille->famille_id : ($request->source_famille_id ?? null)),
                'quantity_to_produce' => $request->production_type === 'type1'
                    ? $request->quantity_to_produce
                    : $totalQuantityToProduce,
                'required_quantity' => $requiredQuantity,
                'priority' => $request->priority,
                'start_date' => $request->start_date,
                'expected_completion_date' => $request->expected_completion_date,
                'production_type' => $request->production_type,
                'waste_percentage' => $wastePercentage,
                'material_source' => $materialSource,
                'chutes_volume' => $chutesVolume,
                'total_cost' => $totalCost,
                'source_volume' => $sourceVolume,
                'final_volume' => $finalVolume,
                'total_volume_produced' => $totalVolumeProduced,
                'waste_volume' => $wasteVolume,
                'notes' => $request->notes,
                'status' => 'pending',
                'created_by' => auth()->id(),
                'responsible_employee_id' => $request->responsible_employee_id,
                'additional_data' => $this->prepareAdditionalData($request, $productsData, $resolvedSourceProducts),
            ];

            $productionOrder = ProductionOrder::create($productionOrderData);

            if ($request->production_type === 'type3') {
                Log::info('Type3 order created', [
                    'order_id' => $productionOrder->order_id,
                    'order_number' => $productionOrder->order_number,
                    'source_product_id' => $productionOrder->source_product_id,
                    'resolved_source_products' => $resolvedSourceProducts,
                    'products_data_count' => count($productsData),
                    'additional_data' => $productionOrder->additional_data,
                ]);
            }

            ProductionOrderNotificationHelper::notifyOrderCreated($productionOrder);

            if ($request->production_type === 'type1') {
                ProductionConsumption::where('production_order_id', $productionOrder->order_id)->delete();

                $this->createConsumptionRecords(
                    $productionOrder,
                    $request->quantity_to_produce,
                    $materialSource,
                    $chutesVolume,
                    $request->bom_consumptions ?? []
                );

                $this->updateProductBOM($finalProduct, $request->bom_consumptions ?? []);
            }

            if (!empty($bomItemsToSave)) {
                foreach ($bomItemsToSave as $bomItem) {
                    $existingBom = BillOfMaterial::where('product_id', $finalProduct->product_id)
                        ->where('material_id', $bomItem['material_id'])
                        ->first();

                    if (!$existingBom) {
                        $material = RawMaterial::find($bomItem['material_id']);
                        BillOfMaterial::create([
                            'product_id' => $finalProduct->product_id,
                            'material_id' => $bomItem['material_id'],
                            'quantity_required' => $bomItem['quantity_required'],
                            'unit_of_measure' => $material ? $material->unit_of_measure : 'unité',
                            'scrap_factor' => 0,
                            'is_active' => true,
                        ]);
                    }
                }
            }

            if (in_array($request->production_type, ['type2', 'type3', 'type4', 'type5']) && !empty($productsData)) {
                foreach ($productsData as $productData) {
                    $insertData = [
                        'production_order_id' => $productionOrder->order_id,
                        'product_id' => $productData['product_id'],
                        'quantity_to_produce' => $productData['quantity_to_produce'],
                        'source_required' => $requiredQuantity ?? 0,
                        'volume_per_unit' => $productData['volume_per_unit'],
                        'total_volume' => $productData['total_volume'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if ($request->production_type === 'type3' && isset($productData['conversion_rate'])) {
                        $insertData['conversion_rate'] = $productData['conversion_rate'];
                    }
                    if ($request->production_type === 'type2' && isset($productData['decoupage_ratio'])) {
                        $insertData['decoupage_ratio'] = $productData['decoupage_ratio'];
                    }

                    DB::table('production_order_products')->insert($insertData);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ordre de production créé avec succès!',
                'order_id' => $productionOrder->order_id,
                'order_number' => $productionOrder->order_number,
                'production_type' => $productionOrder->production_type,
                'bom_items_added' => count($bomItemsToSave),
                'bom_items_removed' => count($bomItemsToRemove),
                'bom_items_updated' => count($bomItemsToUpdate),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating production order: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product BOM based on user selections
     */
    private function updateProductBOM(Product $product, array $bomConsumptions)
    {
        foreach ($bomConsumptions as $materialId => $consumption) {
            if (isset($consumption['save_to_product']) && $consumption['save_to_product'] == 1) {
                BillOfMaterial::updateOrCreate(
                    [
                        'product_id' => $product->product_id,
                        'material_id' => $materialId
                    ],
                    [
                        'quantity_required' => $consumption['quantity_required'] ?? 1,
                        'unit_of_measure' => $this->getMaterialUnitOfMeasure($materialId),
                        'scrap_factor' => 0,
                        'is_active' => true,
                        'updated_at' => now(),
                    ]
                );
            }

            // Check if material should be removed from product's BOM
            if (isset($consumption['remove_from_product']) && $consumption['remove_from_product'] == 1) {
                BillOfMaterial::where('product_id', $product->product_id)
                    ->where('material_id', $materialId)
                    ->delete();
            }
        }
    }

    /**
     * Helper method to get material unit of measure
     */
    private function getMaterialUnitOfMeasure($materialId)
    {
        $material = RawMaterial::find($materialId);
        return $material ? $material->unit_of_measure : 'unité';
    }

    /**
     * Get quality details for a production order
     */
    public function getQualityDetails($orderId)
    {
        try {
            $order = ProductionOrder::with(['qualityCheckedBy', 'qualityOverrideBy', 'outputs.product'])
                ->findOrFail($orderId);

            $qualitySummary = [
                'quality_status' => $order->quality_status,
                'quality_score' => $order->quality_score,
                'raw_material_weight_kg' => $order->raw_material_weight_kg,
                'product_weight_kg' => $order->product_weight_kg,
                'weight_difference_percent' => $order->weight_difference_percent,
                'quality_notes' => $order->quality_notes,
                'quality_checked_at' => $order->quality_checked_at,
                'quality_checked_by' => $order->qualityCheckedBy?->name,
                'quality_override' => $order->quality_override,
                'quality_override_reason' => $order->quality_override_reason,
                'quality_override_at' => $order->quality_override_at,
                'quality_override_by' => $order->qualityOverrideBy?->name,
                'defect_rate_percent' => $order->defect_rate_percent,
                'total_good_quantity' => $order->total_good_quantity,
                'total_defective_quantity' => $order->total_defective_quantity,
                'efficiency_percent' => $order->efficiency_percent
            ];

            // Get quality breakdown by output
            $outputsQuality = [];
            foreach ($order->outputs as $output) {
                $goodQuantity = $output->quantity_produced - $output->quantity_defective;
                $defectRate = $output->quantity_produced > 0
                    ? ($output->quantity_defective / $output->quantity_produced) * 100
                    : 0;

                $outputsQuality[] = [
                    'date' => $output->production_date?->format('d/m/Y'),
                    'famille' => $output->famille?->famille_name,
                    'quantity_produced' => $output->quantity_produced,
                    'quantity_defective' => $output->quantity_defective,
                    'good_quantity' => $goodQuantity,
                    'defect_rate' => round($defectRate, 2),
                    'total_volume' => $output->total_volume_m3
                ];
            }

            return response()->json([
                'success' => true,
                'quality_summary' => $qualitySummary,
                'outputs_quality' => $outputsQuality,
                'can_override' => in_array($order->quality_status, ['warning', 'critical']) && !$order->quality_override,
                'is_completed' => $order->status === 'completed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create consumption records for Type 1 production
     */
    private function createConsumptionRecords(ProductionOrder $order, $quantity,
        $materialSource, $chutesVolume = 0, $bomConsumptions = [])
    {
        if ($materialSource === 'bom_only' || $materialSource === 'both') {
            if (!empty($bomConsumptions)) {
                foreach ($bomConsumptions as $materialId => $consumption) {
                    if (isset($consumption['planned_quantity']) && $consumption['planned_quantity'] > 0) {
                        $material = RawMaterial::find($materialId);

                        if ($material) {
                            $quantityRequired = $consumption['quantity_required'] ??
                                ($consumption['planned_quantity'] / $quantity);

                            $unitCost = $this->calculateFIFOUnitCost($materialId, $consumption['planned_quantity']);
                            if ($unitCost <= 0) {
                                $unitCost = $material->average_unit_cost ?? 0;
                            }

                            ProductionConsumption::create([
                                'production_order_id' => $order->order_id,
                                'material_id' => $materialId,
                                'planned_quantity' => $consumption['planned_quantity'],
                                'actual_quantity_used' => 0,
                                'waste_quantity' => 0,
                                'unit_cost' => $unitCost,
                                'total_cost' => $consumption['planned_quantity'] * $unitCost,
                                'notes' => $materialSource === 'both' ?
                                    'Quantité spécifiée manuellement (mode mixte)' :
                                    'Consommation planifiée (BOM standard)',
                                'is_waste' => false,
                            ]);
                        }
                    }
                }
            } else {
                $bom = BillOfMaterial::where('product_id', $order->product_id)
                    ->with('rawMaterial')
                    ->get();

                foreach ($bom as $item) {
                    $plannedQuantity = $item->quantity_required * $quantity;

                    if ($plannedQuantity > 0) {
                        $unitCost = $this->calculateFIFOUnitCost($item->material_id, $plannedQuantity);
                        if ($unitCost <= 0) {
                            $unitCost = $item->rawMaterial->average_unit_cost ?? 0;
                        }

                        ProductionConsumption::create([
                            'production_order_id' => $order->order_id,
                            'material_id' => $item->material_id,
                            'planned_quantity' => $plannedQuantity,
                            'actual_quantity_used' => 0,
                            'waste_quantity' => 0,
                            'unit_cost' => $unitCost,
                            'total_cost' => $plannedQuantity * $unitCost,
                            'notes' => 'Consommation planifiée (BOM standard)',
                            'is_waste' => false,
                        ]);
                    }
                }
            }
        }

        if ($materialSource === 'chutes_only' || $materialSource === 'both') {
            $chutesMaterial = RawMaterial::firstOrCreate(
                ['material_code' => 'CHUTE-PRODUCTION'],
                [
                    'material_name' => 'Chutes de Production',
                    'unit_of_measure' => 'm³',
                    'min_stock_level' => 0,
                    'max_stock_level' => 10000,
                    'is_active' => true,
                    'notes' => 'Chutes de production recyclées',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $chutesRequired = $chutesVolume;

            if ($chutesRequired > 0) {
                ProductionConsumption::create([
                    'production_order_id' => $order->order_id,
                    'material_id' => $chutesMaterial->material_id,
                    'planned_quantity' => $chutesRequired,
                    'actual_quantity_used' => 0,
                    'waste_quantity' => 0,
                    'unit_cost' => 0,
                    'total_cost' => 0,
                    'notes' => $materialSource === 'both' ?
                        'Chutes spécifiées manuellement (mode mixte)' :
                        'Chutes de production recyclées',
                    'is_waste' => true,
                ]);
            }
        }
    }

    /**
     * Helper method to get or create a famille
     */
    private function getOrCreateFamille(Product $product, $familleName, $familleCode)
    {
        $famille = Famille::firstOrCreate(
            [
                'famille_name' => $familleName,
                'famille_code' => $familleCode
            ],
            [
                'description' => 'Famille par défaut pour ' . $product->product_name,
                'is_active' => true,
                'sort_order' => 0
            ]
        );

        $famille->associateToProductIfNotExists($product->product_id);
        return $famille;
    }

    /**
     * Helper method to prepare additional data JSON
     */
    private function prepareAdditionalData($request, $productsData, $resolvedSourceProducts = null)
    {
        if ($request->production_type === 'type2' && !empty($productsData)) {
            return json_encode([
                'multiple_products' => true,
                'products_count' => count($productsData),
                'total_blocks_required' => $request->type2_total_blocks ?? 0,
                'products_summary' => array_map(function($product) {
                    return [
                        'product_id' => $product['product_id'],
                        'product_name' => $product['product']->product_name,
                        'quantity_to_produce' => $product['quantity_to_produce'],
                    ];
                }, $productsData)
            ]);
        }

        if ($request->production_type === 'type3' && !empty($productsData)) {
            return json_encode([
                'multiple_products' => true,
                'products_count' => count($productsData),
                'source_products' => $resolvedSourceProducts ?? $request->type3_source_products ?? [],
                'total_sous_blocs_required' => $request->required_quantity ?? 0,
                'products_summary' => array_map(function($product) {
                    return [
                        'product_id' => $product['product_id'],
                        'product_name' => $product['product']->product_name,
                        'quantity_to_produce' => $product['quantity_to_produce'],
                    ];
                }, $productsData)
            ]);
        }

        if ($request->production_type === 'type5' && !empty($productsData)) {
            return json_encode([
                'multiple_products' => true,
                'products_count' => count($productsData),
                'chutes_volume' => $request->chutes_volume ?? 0,
                'force_chutes_override' => $request->boolean('force_chutes'),
                'products_summary' => array_map(function($product) {
                    return [
                        'product_id' => $product['product_id'],
                        'product_name' => $product['product']->product_name,
                        'quantity_to_produce' => $product['quantity_to_produce'],
                    ];
                }, $productsData)
            ]);
        }

        return null;
    }

    /**
     * Check material availability for Type 1 production
     */
    private function checkMaterialAvailability(Product $product, $quantity)
    {
        $bom = BillOfMaterial::where('product_id', $product->product_id)
            ->with('rawMaterial')
            ->get();

        $insufficientMaterials = [];

        foreach ($bom as $item) {
            $requiredQuantity = $item->quantity_required * $quantity;
            $availableStock = $item->rawMaterial->current_stock ?? 0;

            if ($availableStock < $requiredQuantity) {
                $insufficientMaterials[] = [
                    'material' => $item->rawMaterial->material_name,
                    'required' => number_format($requiredQuantity, 2, ',', '.'),
                    'available' => number_format($availableStock, 2, ',', '.'),
                    'unit' => $item->rawMaterial->unit_of_measure
                ];
            }
        }

        if (!empty($insufficientMaterials)) {
            $message = "Stock insuffisant pour les matériaux suivants:\n";
            foreach ($insufficientMaterials as $material) {
                $message .= "- {$material['material']}: Requis {$material['required']}, Disponible {$material['available']} {$material['unit']}\n";
            }
            throw new \Exception($message);
        }
    }

    /**
     * Check chutes availability
     */
    private function checkChutesAvailability($chutesVolume)
    {
        $chutesMaterial = RawMaterial::where('material_code', 'CHUTE-PRODUCTION')->first();

        if (!$chutesMaterial) {
            throw new \Exception("Les chutes de production ne sont pas configurées dans le système.");
        }

        $availableStock = $chutesMaterial->current_stock ?? 0;

        if ($availableStock < $chutesVolume) {
            throw new \Exception(
                "Stock insuffisant de chutes. " .
                "Requis: " . number_format($chutesVolume, 4) . " m³, " .
                "Disponible: " . number_format($availableStock, 4) . " m³"
            );
        }
    }

    /**
     * Check source product stock availability
     */
    private function checkSourceProductStock(Product $sourceProduct, $requiredQuantity, $sourceFamilleId = null)
    {
        if ($sourceProduct->has_familles) {
            if (!$sourceFamilleId) {
                throw new \Exception("Le produit source a des familles. Veuillez sélectionner une famille source.");
            }

            $familleStock = ProductFamilleStock::where('product_id', $sourceProduct->product_id)
                ->where('famille_id', $sourceFamilleId)
                ->first();

            if (!$familleStock) {
                throw new \Exception("Stock de famille source non trouvé.");
            }

            $availableQuantity = $familleStock->current_quantity - $familleStock->reserved_quantity;

            // if ($availableQuantity < $requiredQuantity) {
            //     throw new \Exception(
            //         "Stock insuffisant dans la famille source '" . $familleStock->famille_name . "'. " .
            //         "Requis: " . number_format($requiredQuantity, 2, ',', '.') . " " . $sourceProduct->unit_of_measure . ", " .
            //         "Disponible: " . number_format($availableQuantity, 2, ',', '.') . " " . $sourceProduct->unit_of_measure
            //     );
            // }
        } else {
            if (!$sourceProduct->stock) {
                throw new \Exception("Le produit source n'a pas de stock disponible.");
            }

            $availableQuantity = $sourceProduct->stock->current_quantity - $sourceProduct->stock->reserved_quantity;

            // if ($availableQuantity < $requiredQuantity) {
            //     throw new \Exception(
            //         "Stock insuffisant pour le produit source. " .
            //         "Requis: " . number_format($requiredQuantity, 2, ',', '.') . " " . $sourceProduct->unit_of_measure . ", " .
            //         "Disponible: " . number_format($availableQuantity, 2, ',', '.') . " " . $sourceProduct->unit_of_measure
            //     );
            // }
        }
    }

    /**
     * Calculate product volume
     */
    private function calculateProductVolume(Product $product)
    {
        if ($product->volume_m3 && $product->volume_m3 > 0) {
            return $product->volume_m3;
        }

        if ($product->height_m && $product->width_m && $product->depth_m) {
            return $product->height_m * $product->width_m * $product->depth_m;
        }

        return 0;
    }

    private function checkMixedMaterialsAvailability(Product $product, $mixedMaterials, $chutesVolume)
    {
        $bom = BillOfMaterial::where('product_id', $product->product_id)
            ->with('rawMaterial')
            ->get();

        $insufficientMaterials = [];

        // Check each BOM material with user-specified quantity
        foreach ($bom as $item) {
            $materialId = $item->material_id;
            $userQuantity = $mixedMaterials[$materialId]['quantity'] ?? 0;

            if ($userQuantity > 0) {
                $availableStock = $item->rawMaterial->current_stock ?? 0;

                if ($availableStock < $userQuantity) {
                    $insufficientMaterials[] = [
                        'material' => $item->rawMaterial->material_name,
                        'required' => number_format($userQuantity, 4),
                        'available' => number_format($availableStock, 4),
                        'unit' => $item->rawMaterial->unit_of_measure
                    ];
                }
            }
        }

        // Check chutes availability
        $chutesMaterial = RawMaterial::where('material_code', 'CHUTE-PRODUCTION')->first();
        if ($chutesMaterial && $chutesVolume > 0) {
            $availableChutes = $chutesMaterial->current_stock ?? 0;

            if ($availableChutes < $chutesVolume) {
                $insufficientMaterials[] = [
                    'material' => $chutesMaterial->material_name,
                    'required' => number_format($chutesVolume, 4),
                    'available' => number_format($availableChutes, 4),
                    'unit' => 'm³'
                ];
            }
        }

        if (!empty($insufficientMaterials)) {
            $message = "Stock insuffisant pour les matériaux suivants:\n";
            foreach ($insufficientMaterials as $material) {
                $message .= "- {$material['material']}: Requis {$material['required']}, Disponible {$material['available']} {$material['unit']}\n";
            }
            throw new \Exception($message);
        }
    }

// /**
//  * Calculate FIFO unit cost
//  */
// private function calculateFIFOUnitCost($materialId, $quantityNeeded)
// {
//     // Get stock details in FIFO order (oldest first)
//     $stockDetails = StockMovementDetail::where('material_id', $materialId)
//         ->where('remaining_quantity', '>', 0)
//         ->orderBy('stock_movement_id', 'asc')
//         ->get();

//     $remainingToConsume = $quantityNeeded;
//     $totalCost = 0;

//     foreach ($stockDetails as $detail) {
//         if ($remainingToConsume <= 0) break;

//         $availableQuantity = $detail->remaining_quantity;
//         $quantityToTake = min($availableQuantity, $remainingToConsume);

//         $totalCost += $quantityToTake * $detail->unit_price;
//         $remainingToConsume -= $quantityToTake;
//     }

//     if ($remainingToConsume > 0) {
//         // If we still need more but no stock, use average cost
//         $averageCost = StockMovementDetail::where('material_id', $materialId)
//             ->where('remaining_quantity', '>', 0)
//             ->avg('unit_price');

//         $totalCost += $remainingToConsume * ($averageCost ?? 0);
//     }

//     return $quantityNeeded > 0 ? ($totalCost / $quantityNeeded) : 0;
// }

    /**
     * Handle waste declaration for a production order
     */
    public function handleWasteDeclaration(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'wastes' => 'required|array|min:1',
                'wastes.*.waste_type' => 'required|in:recyclable,waste',
                'wastes.*.waste_source' => 'required|string|max:100',
                'wastes.*.height' => 'required|numeric|min:0.000',
                'wastes.*.width' => 'required|numeric|min:0.000',
                'wastes.*.depth' => 'required|numeric|min:0.000',
                'wastes.*.waste_category' => 'nullable|string|max:100',
                'wastes.*.notes' => 'nullable|string|max:1000',
            ]);

            $productionOrder = ProductionOrder::with(['outputs', 'wastes'])->findOrFail($id);

            // if ($productionOrder->status === 'completed') {
            //     throw new \Exception('Cet ordre de production est déjà terminé.');
            // }

            $remaining = $this->calculateRemainingQuantity($productionOrder);
            if ($remaining > 0) {
                throw new \Exception('La production n\'est pas encore terminée. Restant: ' . $remaining . ' unités');
            }

            $wasteResults = [
                'recyclable_volume' => 0,
                'pure_waste_volume' => 0,
                'total_volume' => 0,
                'recyclable_count' => 0,
                'waste_count' => 0
            ];

            $chuteMaterial = $this->getOrCreateChuteMaterial();

            // Process each waste item
            foreach ($request->wastes as $wasteData) {
                $wasteType = $wasteData['waste_type'];
                $height = $wasteData['height'];
                $width = $wasteData['width'];
                $depth = $wasteData['depth'];
                $volume = $height * $width * $depth; // Calculate volume automatically
                $wasteSource = $wasteData['waste_source'];
                $wasteCategory = $wasteData['waste_category'] ?? null;
                $notes = $wasteData['notes'] ?? null;
                $wasteId = $wasteData['id'] ?? null;

                if ($wasteType === 'recyclable') {
                    $wasteResults['recyclable_volume'] += $volume;
                    $wasteResults['recyclable_count']++;

                    $wasteData = [
                        'production_order_id' => $productionOrder->order_id,
                        'waste_type' => 'recyclable',
                        'waste_source' => $wasteSource,
                        'waste_category' => null,
                        'height' => $height,
                        'width' => $width,
                        'depth' => $depth,
                        'quantity' => 1, // Always 1
                        'volume_m3' => $volume,
                        'notes' => $notes,
                        'is_recovered' => true,
                        'created_by' => auth()->id(),
                        'material_id' => $chuteMaterial->material_id,
                    ];

                    if ($wasteId) {
                        // Update existing waste
                        ProductionWaste::where('waste_id', $wasteId)->update($wasteData);
                    } else {
                        // Create new waste
                        ProductionWaste::create($wasteData);
                    }
                } else {
                    $wasteResults['pure_waste_volume'] += $volume;
                    $wasteResults['waste_count']++;

                    $finalCategory = $wasteCategory ?: 'Non spécifié';

                    $wasteData = [
                        'production_order_id' => $productionOrder->order_id,
                        'waste_type' => 'waste',
                        'waste_source' => $wasteSource,
                        'waste_category' => $finalCategory,
                        'height' => $height,
                        'width' => $width,
                        'depth' => $depth,
                        'quantity' => 1, // Always 1
                        'volume_m3' => $volume,
                        'notes' => $notes,
                        'is_recovered' => false,
                        'created_by' => auth()->id(),
                        'material_id' => null,
                    ];

                    if ($wasteId) {
                        // Update existing waste
                        ProductionWaste::where('waste_id', $wasteId)->update($wasteData);
                    } else {
                        // Create new waste
                        ProductionWaste::create($wasteData);
                    }
                }
            }

            $wasteResults['total_volume'] = $wasteResults['recyclable_volume'] + $wasteResults['pure_waste_volume'];

            // Update recyclable material stock
            if ($wasteResults['recyclable_volume'] > 0) {
                $this->updateRecyclableMaterialStock(
                    $chuteMaterial->material_id,
                    $wasteResults['recyclable_volume'],
                    $productionOrder
                );
            }

            // Check if order should be marked as completed
            $totalProduced = $productionOrder->outputs->sum('quantity_produced');
            $isOrderCompleted = $totalProduced >= $productionOrder->quantity_to_produce;

            $updateData = [
                'waste_volume' => $wasteResults['total_volume'],
            ];

            if ($isOrderCompleted) {
                $updateData['status'] = 'completed';
                $updateData['actual_completion_date'] = now();
                $updateData['completed_by'] = auth()->id();
            }

            $productionOrder->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Déclaration de chutes enregistrée avec succès!',
                'order_completed' => $isOrderCompleted,
                'waste_volume' => $wasteResults['total_volume'],
                'recyclable_volume' => $wasteResults['recyclable_volume'],
                'pure_waste_volume' => $wasteResults['pure_waste_volume'],
                'recyclable_count' => $wasteResults['recyclable_count'],
                'waste_count' => $wasteResults['waste_count'],
                'order_id' => $productionOrder->order_id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in handleWasteDeclaration: ' . $e->getMessage(), [
                'order_id' => $id,
                'request_data' => $request->all(),
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update recyclable material stock
     */
    private function updateRecyclableMaterialStock($materialId, $volume, ProductionOrder $order)
    {
        $material = RawMaterial::find($materialId);
        if (!$material) {
            Log::error('Material not found for waste', ['material_id' => $materialId]);
            return null;
        }

        $previousStock = $material->current_stock ?? 0;
        $newStock = $previousStock + $volume;

        // Check if stock movement already exists for this order
        $existingMovement = RawMaterialStockMovement::where('material_id', $materialId)
            ->where('reference_type', 'production_order')
            ->where('reference_id', $order->order_id)
            ->where('movement_type', 'waste_recovery')
            ->first();

        if ($existingMovement) {
            // Update existing movement
            $existingMovement->update([
                'quantity' => $volume,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'notes' => "Chutes de production: {$volume} m³ (Commande: {$order->order_number}) - Mise à jour",
                'updated_at' => now(),
            ]);
        } else {
            // Create new stock movement
            $movementId = DB::table('raw_material_stock_movements')->insertGetId([
                'material_id' => $materialId,
                'movement_type' => 'waste_recovery',
                'quantity' => $volume,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reference_type' => 'production_order',
                'reference_id' => $order->order_id,
                'reference_number' => $order->order_number,
                'movement_date' => now(),
                'performed_by' => auth()->id(),
                'notes' => "Chutes de production: {$volume} m³ (Commande: {$order->order_number})",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($movementId) {
                // Create stock movement detail
                $unitPrice = $this->calculateWasteUnitPrice($material, $volume);

                DB::table('stock_movement_details')->insert([
                    'stock_movement_id' => $movementId,
                    'material_id' => $materialId,
                    'quantity' => $volume,
                    'unit_price' => $unitPrice,
                    'remaining_quantity' => $volume,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Update material stock
        $material->current_stock = $newStock;
        $material->save();

        return true;
    }


    /**
     * Get production orders that need waste declaration
     */
    public function getOrdersNeedingWasteDeclaration()
    {
        $orders = ProductionOrder::with(['product', 'famille', 'outputs'])
            ->where('status', '!=', 'completed')
            ->get()
            ->filter(function($order) {
                return $this->calculateRemainingQuantity($order) <= 0;
            });

        return response()->json([
            'success' => true,
            'data' => $orders->values()
        ]);
    }

    /**
     * Calculate remaining quantity for an order
     */
    private function calculateRemainingQuantity(ProductionOrder $order)
    {
            if ($order->status === 'completed') {
                return 0;
            }

            if($order->production_type === 'decoupage') {
                $totalTargetProduced = $order->outputs
                ->sum('quantity_produced');
                return max(0, $order->quantity_to_produce - $totalTargetProduced);
            } else {
                $totalTargetProduced = $order->outputs
                    ->sum('quantity_produced');
                return max(0, $order->quantity_to_produce - $totalTargetProduced);
            }

    }

    private function getOrCreateChuteMaterial()
    {
        $chuteMaterial = RawMaterial::where('material_code', 'CHUTE-PRODUCTION')
            ->orWhere('material_name', 'Chutes de Production')
            ->first();

        if ($chuteMaterial) {
            return $chuteMaterial;
        }

        $wasteCategory = RawMaterialCategory::firstOrCreate(
            ['category_name' => 'Chutes et Déchets'],
            [
                'category_code' => 'WASTE',
                'description' => 'Chutes, déchets et rebuts de production',
                'is_active' => true,
                'created_at' => now(),
            ]
        );

        $chuteMaterial = RawMaterial::create([
            'material_code' => 'CHUTE-PRODUCTION',
            'material_name' => 'Chutes de Production',
            'category_id' => $wasteCategory->category_id,
            'unit_of_measure' => 'm³',
            'min_stock_level' => 0,
            'max_stock_level' => 10000,
            'supplier_id' => null,
            'magazine_id' => null,
            'notes' => 'Matière première pour toutes les chutes et déchets de production',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $chuteMaterial;
    }

    /**
     * Calculate unit price for waste material
     */
    private function calculateWasteUnitPrice($material, $volume)
    {
        $defaultValuePerM3 = 50.00;

        $isSubMaterial = $material->category->parent_category_id !== null;

        if ($isSubMaterial && $material->category->parent_category_id) {
            $parentCategory = RawMaterialCategory::find($material->category->parent_category_id);
            if ($parentCategory && $parentCategory->category_code === 'WASTE') {
                // This is a waste material
                return $defaultValuePerM3;
            }
        }

        // Check material name for clues about value
        $materialName = strtolower($material->material_name);

        if (str_contains($materialName, 'recyclable') || str_contains($materialName, 'reutilisable')) {
            return $defaultValuePerM3; // Higher value for recyclable
        } elseif (str_contains($materialName, 'non recyclable') || str_contains($materialName, 'déchet')) {
            return $defaultValuePerM3 * 0.3; // Lower value for non-recyclable
        } elseif (str_contains($materialName, 'auto') || str_contains($materialName, 'défaut')) {
            return $defaultValuePerM3 * 0.5; // Medium value for auto defects
        }

        return $defaultValuePerM3;
    }

    public function show($id)
    {
        return view('pages.production-orders.show', $this->prepareOrderShowData($id));
    }

    /**
     * Stream a printable A4 PDF of the production order (same data as show()).
     */
    public function printOrder($id)
    {
        $data = $this->prepareOrderShowData($id);

        $data['date'] = now()->format('d/m/Y');
        $data['time'] = now()->format('H:i');
        $data['username'] = auth()->user()->name ?? auth()->user()->username;

        $pdf = Pdf::loadView('pdf.production-order', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('ordre-production-' . $data['order']->order_number . '.pdf');
    }

    private function prepareOrderShowData($id)
    {
        $order = ProductionOrder::with([
            'product',
            'creator',
            'consumptions.rawMaterial',
            'outputs',
            'sourceProduct',
            'decoupageOutputs',
            'conversionOutputs',
            'famille',
            'qualityCheckedBy',
            'qualityOverrideBy',
            'responsibleEmployee'
        ])->findOrFail($id);

        $totalProduced = $order->outputs->sum('quantity_produced');
        $totalDefective = $order->outputs->sum('quantity_defective');

        if ($order->production_type === 'decoupage') {
            $progress = $this->calculateDecoupageProgress($order);
        } else {
            $progress = $order->quantity_to_produce > 0 ? ($totalProduced / $order->quantity_to_produce) * 100 : 0;
        }

        // Calculate quality metrics for Type 1 orders
        $qualityMetrics = null;
        if ($order->production_type === 'type1') {
            $qualityMetrics = $this->calculateQualityMetrics($order);
        }

        // Build source products and sub-products lists for type2/type3
        $sourceProducts = [];
        $subProducts = collect();
        if (in_array($order->production_type, ['type2', 'type3', 'type5'])) {
            $additionalData = is_array($order->additional_data)
                ? $order->additional_data
                : json_decode($order->additional_data ?? '{}', true);

            if (!empty($additionalData['source_products'])) {
                foreach ($additionalData['source_products'] as $sp) {
                    $name = $sp['product_name'] ?? null;
                    if (!$name) {
                        $product = \App\Models\Product::find($sp['product_id'] ?? null);
                        $name = $product ? $product->product_name : 'Inconnu';
                    }
                    $code = $sp['product_code'] ?? null;
                    if (!$code) {
                        $product = $product ?? \App\Models\Product::find($sp['product_id'] ?? null);
                        $code = $product ? $product->product_code : '';
                    }
                    $sourceProducts[] = [
                        'product_id'   => $sp['product_id'] ?? null,
                        'product_name' => $name,
                        'product_code' => $code,
                        'quantity'     => $sp['quantity'] ?? 0,
                    ];
                }
            }

            // Fallback for older orders that have no additional_data
            if (empty($sourceProducts) && $order->sourceProduct) {
                $sourceProducts[] = [
                    'product_id'   => $order->source_product_id,
                    'product_name' => $order->sourceProduct->product_name,
                    'product_code' => $order->sourceProduct->product_code ?? '',
                    'quantity'     => $order->required_quantity ?? $order->quantity_to_produce,
                ];
            }

            if ($order->production_type === 'type2') {
                $subProducts = collect($order->getType2ProductionSummary());
            } elseif ($order->production_type === 'type3') {
                $subProducts = collect($order->getType3ProductionSummary());
            } else {
                $subProducts = collect($order->getType5ProductionSummary());
            }
        }

        return compact('order', 'totalProduced', 'totalDefective', 'progress', 'qualityMetrics', 'sourceProducts', 'subProducts');
    }

    /**
     * Calculate quality metrics for Type 1 production orders
     */
    private function calculateQualityMetrics(ProductionOrder $order)
    {
        $totalGoodQuantity = $order->outputs->sum('quantity_produced') - $order->outputs->sum('quantity_defective');
        $totalDefectiveQuantity = $order->outputs->sum('quantity_defective');
        $defectRate = $order->quantity_to_produce > 0
            ? ($totalDefectiveQuantity / $order->quantity_to_produce) * 100
            : 0;

        // Get weight-based quality data from the order
        $qualityData = [
            'quality_status' => $order->quality_status ?? 'pending',
            'quality_score' => $order->quality_score,
            'raw_material_weight_kg' => $order->raw_material_weight_kg,
            'product_weight_kg' => $order->product_weight_kg,
            'weight_difference_percent' => $order->weight_difference_percent,
            'quality_notes' => $order->quality_notes,
            'quality_checked_at' => $order->quality_checked_at,
            'quality_checked_by' => $order->qualityCheckedBy?->name,
            'quality_override' => $order->quality_override,
            'quality_override_reason' => $order->quality_override_reason,
            'quality_override_at' => $order->quality_override_at,
            'quality_override_by' => $order->qualityOverrideBy?->name,
            'defect_rate_percent' => $defectRate,
            'total_good_quantity' => $totalGoodQuantity,
            'total_defective_quantity' => $totalDefectiveQuantity,
            'efficiency_percent' => $order->efficiency_percent
        ];

        // Get consumptions breakdown
        $consumptions = [];
        $totalWeightByMaterial = [];

        foreach ($order->consumptions as $consumption) {
            $material = $consumption->rawMaterial;
            if ($material) {
                $materialWeight = $this->calculateMaterialWeightInKg($material, $consumption->actual_quantity_used);

                $consumptions[] = [
                    'material_name' => $material->material_name,
                    'material_code' => $material->material_code,
                    'unit_of_measure' => $material->unit_of_measure,
                    'planned_quantity' => $consumption->planned_quantity,
                    'actual_quantity_used' => $consumption->actual_quantity_used,
                    'waste_quantity' => $consumption->waste_quantity,
                    'weight_kg' => $materialWeight,
                    'unit_cost' => $consumption->unit_cost,
                    'total_cost' => $consumption->total_cost,
                    'difference_percent' => $consumption->planned_quantity > 0
                        ? (abs($consumption->actual_quantity_used - $consumption->planned_quantity) / $consumption->planned_quantity) * 100
                        : 0
                ];

                $totalWeightByMaterial[] = [
                    'material_name' => $material->material_name,
                    'weight_kg' => $materialWeight
                ];
            }
        }

        // Get outputs breakdown
        $outputs = [];
        foreach ($order->outputs as $output) {
            $goodQuantity = $output->quantity_produced - $output->quantity_defective;
            $defectRateOutput = $output->quantity_produced > 0
                ? ($output->quantity_defective / $output->quantity_produced) * 100
                : 0;

            $outputs[] = [
                'date' => $output->production_date?->format('d/m/Y'),
                'famille' => $output->famille?->famille_name,
                'quantity_produced' => $output->quantity_produced,
                'quantity_defective' => $output->quantity_defective,
                'good_quantity' => $goodQuantity,
                'defect_rate' => round($defectRateOutput, 2),
                'total_volume' => $output->total_volume_m3,
                'is_final' => $output->famille_id == $order->famille_id
            ];
        }

        return [
            'quality' => $qualityData,
            'consumptions' => $consumptions,
            'outputs' => $outputs,
            'total_weight_by_material' => $totalWeightByMaterial
        ];
    }

    /**
     * Calculate material weight in kg
     */
    private function calculateMaterialWeightInKg($material, $quantity)
    {
        if ($material->unit_of_measure === 'kg' || $material->unit_of_measure === 'Kg') {
            return (float) $quantity;
        }

        if ($material->unit_of_measure === 'm³' || $material->unit_of_measure === 'm3') {
            $density = $material->density_kg_per_m3 ?? 650;
            return (float) $quantity * $density;
        }

        if ($material->weight_per_unit && $material->weight_per_unit > 0) {
            return (float) $quantity * $material->weight_per_unit;
        }

        return 0;
    }

    public function getWastes($id)
    {
        try {
            $order = ProductionOrder::with(['wastes' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])->findOrFail($id);

            $wastes = $order->wastes->map(function($waste) {
                return [
                    'id' => $waste->waste_id,
                    'waste_type' => $waste->waste_type,
                    'waste_source' => $waste->waste_source,
                    'height' => $waste->height,
                    'width' => $waste->width,
                    'depth' => $waste->depth,
                    'volume_m3' => $waste->volume_m3,
                    'waste_category' => $waste->waste_category,
                    'notes' => $waste->notes,
                    'created_at' => $waste->created_at->format('d/m/Y H:i'),
                    'created_by' => $waste->creator->name ?? 'N/A',
                ];
            });

            return response()->json([
                'success' => true,
                'wastes' => $wastes,
                'order_id' => $order->order_id,
                'order_number' => $order->order_number,
                'total_wastes' => $wastes->count(),
                'total_volume' => $wastes->sum('volume_m3'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting production order wastes: ' . $e->getMessage(), [
                'order_id' => $id,
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des chutes: ' . $e->getMessage()
            ], 500);
        }
    }


    public function apiShow($id)
    {
        try {
            $order = ProductionOrder::with([
                'product',
                'creator',
                'sourceProduct',
                'product.billOfMaterials.rawMaterial',
                'consumptions.rawMaterial',
                'outputs.famille',
                'famille',
                'sourceProduct.familles'
            ])->findOrFail($id);

            // Map old production_type to new ones if needed
            $productionType = $order->production_type;
            if ($productionType === 'direct') {
                $productionType = 'type1';
            } elseif ($productionType === 'decoupage') {
                // Determine if it's type2 or type3 based on product types
                if ($order->product && $order->product->product_type === 'decoupage') {
                    $productionType = 'type2';
                } elseif ($order->product && ($order->product->product_type === 'sales' || $order->product->product_type === 'finale')) {
                    $productionType = 'type3';
                }
            }

            $totalProduced = $order->outputs->sum('quantity_produced');
            $totalDefective = $order->outputs->sum('quantity_defective');

            // Calculate total volume from outputs
            $totalVolume = $order->outputs->sum('total_volume_m3');
            $wasteVolume = $order->outputs->sum('waste_volume_m3');
            $goodVolume = $totalVolume - $wasteVolume;

            // Calculate progress based on production type
            $progress = 0;
            $targetProduced = 0;
            $remaining = 0;
            $targetVolume = 0;

            if ($productionType === 'type1') {
                // For type1: calculate target famille production
                $targetProduced = $order->outputs
                    ->where('famille_id', $order->famille_id)
                    ->sum('quantity_produced');
                $targetVolume = $order->outputs
                    ->where('famille_id', $order->famille_id)
                    ->sum('total_volume_m3');
                $remaining = $order->quantity_to_produce - $targetProduced;
                $progress = $order->quantity_to_produce > 0 ? ($targetProduced / $order->quantity_to_produce) * 100 : 0;
            } elseif ($productionType === 'type2') {
                // For type2: calculate source material consumption
                $totalConsumed = $order->outputs
                    ->where('output_type', 'type2')
                    ->sum('quantity_consumed');

                // Calculate sous-blocs produced (this is the actual target for découpage)
                $sousBlocsProduced = $order->outputs
                    ->where('output_type', 'type2')
                    ->sum('quantity_produced');

                // Progress based on source material consumption
                $remaining = $order->required_quantity - $totalConsumed;
                $progress = $order->required_quantity > 0 ?
                    ($totalConsumed / $order->required_quantity) * 100 : 0;

                // Target produced should be the sous-blocs produced
                $targetProduced = $sousBlocsProduced;
            } elseif ($productionType === 'type3') {
                // For type3: calculate sous-blocs consumption
                $totalConsumed = $order->outputs
                    ->where('output_type', 'type3')
                    ->sum('quantity_consumed');
                $remaining = $order->required_quantity - $totalConsumed;
                $progress = $order->required_quantity > 0 ? ($totalConsumed / $order->required_quantity) * 100 : 0;
                $targetProduced = $order->outputs
                    ->where('famille_id', $order->famille_id)
                    ->sum('quantity_produced');
                $targetVolume = $order->outputs
                    ->where('famille_id', $order->famille_id)
                    ->sum('total_volume_m3');
            }

            // Get BOM from product (only for type1 production)
            $bom = $order->product->billOfMaterials ?? collect();

            // Calculate planned quantities for regular BOM items
            $bomWithPlannedQuantities = $bom->map(function($item) use ($order) {
                $plannedQuantity = $item->quantity_required * $order->quantity_to_produce;

                return [
                    'material_id' => $item->material_id,
                    'quantity_required' => $item->quantity_required,
                    'planned_quantity' => $plannedQuantity,
                    'raw_material' => $item->rawMaterial ? [
                        'material_id' => $item->rawMaterial->material_id,
                        'material_name' => $item->rawMaterial->material_name,
                        'material_code' => $item->rawMaterial->material_code,
                        'unit_of_measure' => $item->rawMaterial->unit_of_measure,
                        'current_stock' => $item->rawMaterial->current_stock,
                        'unit_cost' => $item->rawMaterial->unit_cost,
                    ] : null,
                    'is_chute' => false,
                ];
            });

            // Get chute material information for reference (but not as separate BOM item)
            $chuteMaterial = RawMaterial::where('material_code', 'CHUTE-PRODUCTION')
                ->orWhere('material_name', 'Chutes de Production')
                ->first();

            if (!$chuteMaterial) {
                // Create chute material if it doesn't exist
                $wasteCategory = RawMaterialCategory::firstOrCreate(
                    ['category_name' => 'Chutes et Déchets'],
                    [
                        'category_code' => 'WASTE',
                        'description' => 'Chutes, déchets et rebuts de production',
                        'is_active' => true,
                        'created_at' => now(),
                    ]
                );

                $chuteMaterial = RawMaterial::create([
                    'material_code' => 'CHUTE-PRODUCTION',
                    'material_name' => 'Chutes de Production',
                    'category_id' => $wasteCategory->category_id,
                    'unit_of_measure' => 'm³',
                    'min_stock_level' => 0,
                    'max_stock_level' => 10000,
                    'supplier_id' => null,
                    'magazine_id' => null,
                    'notes' => 'Matière première pour toutes les chutes et déchets de production',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Calculate chute consumption from order consumptions
            $chuteConsumption = null;
            $totalChuteConsumed = 0;

            if ($order->consumptions) {
                foreach ($order->consumptions as $consumption) {
                    if ($consumption->rawMaterial &&
                        ($consumption->rawMaterial->material_code === 'CHUTE-PRODUCTION' ||
                        $consumption->rawMaterial->material_name === 'Chutes de Production')) {
                        $chuteConsumption = $consumption;
                        $totalChuteConsumed = $consumption->actual_quantity_used ?? 0;
                        break;
                    }
                }
            }

            // Prepare details for type2/type3
            $conversionDetails = null;
            if ($productionType === 'type2' || $productionType === 'type3') {
                $sourceFamilleName = null;
                if ($order->source_famille_id) {
                    $sourceFamille = Famille::find($order->source_famille_id);
                    $sourceFamilleName = $sourceFamille ? $sourceFamille->famille_name : null;
                }

                $conversionDetails = [
                    'source_product' => $order->sourceProduct ? [
                        'product_id' => $order->sourceProduct->product_id,
                        'product_name' => $order->sourceProduct->product_name,
                        'product_code' => $order->sourceProduct->product_code,
                        'has_familles' => $order->sourceProduct->has_familles,
                        'total_volume' => $order->sourceProduct->total_volume,
                        'volume_m3' => $order->sourceProduct->volume_m3,
                        'height_m' => $order->sourceProduct->height_m,
                        'width_m' => $order->sourceProduct->width_m,
                        'depth_m' => $order->sourceProduct->depth_m,
                    ] : null,
                    'source_product_id' => $order->source_product_id,
                    'source_product_name' => $order->sourceProduct ? $order->sourceProduct->product_name : null,
                    'source_famille_id' => $order->source_famille_id,
                    'source_famille_name' => $sourceFamilleName,
                    'required_quantity' => $order->required_quantity,
                    'decoupage_ratio' => $order->decoupage_ratio,
                    'conversion_rate' => $order->conversion_rate,
                    'waste_percentage' => $order->waste_percentage,
                ];
            }

            // Calculate production by famille
            $productionByFamille = [];
            $familleGroups = $order->outputs->groupBy('famille_id');

            foreach ($familleGroups as $familleId => $outputs) {
                $famille = $outputs->first()->famille;
                $totalProduced = $outputs->sum('quantity_produced');
                $totalDefective = $outputs->sum('quantity_defective');
                $totalVolume = $outputs->sum('total_volume_m3');
                $wasteVolume = $outputs->sum('waste_volume_m3');

                $productionByFamille[] = [
                    'famille_id' => $familleId,
                    'famille_name' => $famille ? $famille->famille_name : 'Inconnu',
                    'total_produced' => $totalProduced,
                    'total_defective' => $totalDefective,
                    'good_quantity' => $totalProduced - $totalDefective,
                    'total_volume' => $totalVolume,
                    'waste_volume' => $wasteVolume,
                    'is_target' => ($familleId == $order->famille_id),
                ];
            }

            // Get product volume information
            $productVolume = 0;
            if ($order->product) {
                $product = $order->product;
                $productVolume = $product->total_volume;

                // If no total_volume but dimensions exist, calculate
                if (!$productVolume && $product->height_m && $product->width_m && $product->depth_m) {
                    $productVolume = ($product->height_m / 1000) * ($product->width_m / 1000) * ($product->depth_m / 1000);
                }
            }

            return response()->json([
                'success' => true,
                'order' => [
                    'order_id' => $order->order_id,
                    'order_number' => $order->order_number,
                    'product_id' => $order->product_id,
                    'material_source' => $order->material_source,
                    'bom_percentage' => $order->bom_percentage,
                    'chutes_volume' => $order->chutes_volume,
                    'product' => [
                        'product_id' => $order->product->product_id,
                        'product_name' => $order->product->product_name,
                        'product_code' => $order->product->product_code,
                        'product_type' => $order->product->product_type,
                        'has_familles' => $order->product->has_familles,
                        'total_volume' => $productVolume,
                        'volume_m3' => $order->product->volume_m3,
                        'height_m' => $order->product->height_m,
                        'width_m' => $order->product->width_m,
                        'depth_m' => $order->product->depth_m,
                    ],
                    'famille_id' => $order->famille_id,
                    'famille' => $order->famille ? [
                        'famille_id' => $order->famille->famille_id,
                        'famille_name' => $order->famille->famille_name,
                        'famille_code' => $order->famille->famille_code,
                    ] : null,
                    'famille_name' => $order->famille ? $order->famille->famille_name : null,
                    'quantity_to_produce' => $order->quantity_to_produce,
                    'status' => $order->status,
                    'priority' => $order->priority,
                    'production_type' => $productionType,
                    'source_product_id' => $order->source_product_id,
                    'source_famille_id' => $order->source_famille_id,
                    'source_famille_name' => $conversionDetails ? $conversionDetails['source_famille_name'] : null,
                    'required_quantity' => $order->required_quantity,
                    'decoupage_ratio' => $order->decoupage_ratio,
                    'conversion_rate' => $order->conversion_rate,
                    'waste_percentage' => $order->waste_percentage,
                    'conversion_details' => $conversionDetails,
                    'start_date' => $order->start_date?->format('Y-m-d'),
                    'expected_completion_date' => $order->expected_completion_date?->format('Y-m-d'),
                    'actual_completion_date' => $order->actual_completion_date?->format('Y-m-d'),
                    'notes' => $order->notes,
                    'created_by' => $order->created_by,
                    'consumptions' => $order->consumptions,
                    'creator' => $order->creator ? [
                        'name' => $order->creator->name,
                        'email' => $order->creator->email,
                    ] : null,
                    'bom' => $bomWithPlannedQuantities,
                    'chutes_material' => [
                        'material_id' => $chuteMaterial->material_id,
                        'material_name' => $chuteMaterial->material_name,
                        'material_code' => $chuteMaterial->material_code,
                        'unit_of_measure' => $chuteMaterial->unit_of_measure,
                        'current_stock' => $chuteMaterial->current_stock,
                        'unit_cost' => $chuteMaterial->unit_cost,
                    ],
                    'chute_consumption' => $chuteConsumption ? [
                        'actual_quantity_used' => $chuteConsumption->actual_quantity_used,
                        'waste_quantity' => $chuteConsumption->waste_quantity,
                    ] : null,
                    'outputs' => $order->outputs->map(function($output) {
                        return [
                            'output_id' => $output->output_id,
                            'output_type' => $output->output_type,
                            'famille_id' => $output->famille_id,
                            'famille_name' => $output->famille ? $output->famille->famille_name : ($output->famille_name ?? 'Inconnu'),
                            'quantity_produced' => $output->quantity_produced,
                            'quantity_consumed' => $output->quantity_consumed,
                            'quantity_defective' => $output->quantity_defective,
                            'total_volume_m3' => $output->total_volume_m3,
                            'waste_volume_m3' => $output->waste_volume_m3,
                            'unit_volume_m3' => $output->unit_volume_m3,
                            'quality_grade' => $output->quality_grade,
                            'production_date' => $output->production_date?->format('Y-m-d'),
                            'notes' => $output->notes,
                            'created_at' => $output->created_at,
                        ];
                    }),
                    'production_by_famille' => $productionByFamille,
                    'target_produced' => $targetProduced,
                    'total_produced' => $totalProduced,
                    'target_volume' => $targetVolume,
                    'total_volume' => $totalVolume,
                    'waste_volume' => $wasteVolume,
                    'good_volume' => $goodVolume,
                    'remaining' => $remaining,
                    'progress' => $progress,
                ],
                'statistics' => [
                    'total_produced' => $totalProduced,
                    'total_defective' => $totalDefective,
                    'total_volume' => $totalVolume,
                    'waste_volume' => $wasteVolume,
                    'good_volume' => $goodVolume,
                    'progress_percentage' => $progress,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or error occurred',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function editOrder($id)
    {
        $order = ProductionOrder::with([
            'product',
            'sourceProduct',
            'famille',
            'sourceFamille'
        ])->findOrFail($id);

        if (in_array($order->status, ['completed', 'cancelled'])) {
            return redirect()->route('production-orders.show', $id)
                ->with('error', 'Impossible de modifier un ordre ' . $order->status);
        }

        if ($order->outputs()->count() > 0) {
            return redirect()->route('production-orders.show', $id)
                ->with('error', 'Impossible de modifier un ordre avec des sorties de production.');
        }

        $productionProducts = Product::where('is_active', true)
            ->whereIn('product_type', ['production', 'both'])
            ->orderBy('product_name')
            ->get();

        $decoupageProducts = Product::where('is_active', true)
            ->where('product_type', 'decoupage')
            ->orderBy('product_name')
            ->get();

        $salesProducts = Product::where('is_active', true)
            ->whereIn('product_type', ['finale', 'both'])
            ->orderBy('product_name')
            ->get();

        $type3Products = [];
        if ($order->production_type === 'type3') {
            $type3Products = DB::table('production_order_products')
                ->where('production_order_id', $order->order_id)
                ->join('products', 'production_order_products.product_id', '=', 'products.product_id')
                ->select(
                    'production_order_products.*',
                    'products.product_name',
                    'products.product_code'
                )
                ->get();
        }

        $bom = null;
        if ($order->production_type === 'type1' && $order->product) {
            $bom = BillOfMaterial::where('product_id', $order->product_id)
                ->with('rawMaterial')
                ->get();
        }

        return view('pages.production-orders.edit', compact(
            'order',
            'productionProducts',
            'decoupageProducts',
            'salesProducts',
            'type3Products',
            'bom'
        ));
    }

    public function cancelProduction($id)
    {
        DB::beginTransaction();
        try {
            $order = ProductionOrder::findOrFail($id);

            if (!in_array($order->status, ['pending', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible d\'annuler un ordre ' . $order->status
                ], 400);
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
                'cancellation_reason' => request('reason', 'Annulé par l\'utilisateur'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Production annulée avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function completeDecoupage(Request $request, $id)
    {
        $request->validate([
            'quantity_produced' => 'required|numeric|min:0.01',
            'quantity_defective' => 'required|numeric|min:0',
            'production_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $order = ProductionOrder::with(['sourceProduct'])->findOrFail($id);

            if ($order->production_type !== 'decoupage') {
                throw new \Exception("Cette méthode est uniquement pour les ordres de découpage.");
            }

            if ($order->is_decoupage_completed) {
                throw new \Exception("La phase de découpage est déjà complétée.");
            }

            if ($request->quantity_produced > $order->required_quantity) {
                throw new \Exception("Quantité produite supérieure à la quantité requise. Maximum: " . $order->required_quantity);
            }

            $sousBlocsProduced = $request->quantity_produced * $order->decoupage_ratio;

            $sourceProduct = $order->sourceProduct;
            $quantityConsumed = $request->quantity_produced;
            $sourceVolume = $order->source_volume;
            $finalVolume = $order->final_volume;

            $totalSourceVolume = $request->quantity_produced * $sourceVolume;
            $totalFinalVolume = $sousBlocsProduced * $finalVolume;

            $wasteVolume = $request->waste_volume_m3 ?? max(0, $totalSourceVolume - $totalFinalVolume);

            if ($sourceProduct->has_familles && $order->source_famille_id) {

                $familleStock = ProductFamilleStock::where('product_id', $sourceProduct->product_id)
                    ->where('famille_id', $order->source_famille_id)
                    ->firstOrFail();

                // if ($familleStock->current_quantity < $quantityConsumed) {
                //     throw new \Exception("Stock insuffisant dans la famille source.");
                // }

                $previousStock = $familleStock->current_quantity;
                $familleStock->current_quantity -= $quantityConsumed;
                $familleStock->available_quantity = $familleStock->current_quantity - $familleStock->reserved_quantity;
                $familleStock->save();

                ProductStockMovement::create([
                    'product_id' => $sourceProduct->product_id,
                    'famille_id' => $order->source_famille_id,
                    'movement_type' => 'production_out',
                    'quantity' => -$quantityConsumed,
                    'previous_stock' => $previousStock,
                    'new_stock' => $familleStock->current_quantity,
                    'reference_type' => 'production_order',
                    'reference_id' => $order->order_id,
                    'reference_number' => $order->order_number,
                    'movement_date' => now(),
                    'performed_by' => auth()->id(),
                    'notes' => 'Découpage pour ordre ' . $order->order_number,
                ]);
            } else {

                $productStock = ProductStock::firstOrCreate(
                    ['product_id' => $sourceProduct->product_id],
                    ['current_quantity' => 0, 'reserved_quantity' => 0, 'available_quantity' => 0]
                );

                if ($productStock->current_quantity < $quantityConsumed) {
                    throw new \Exception("Stock insuffisant pour le produit source.");
                }

                $previousStock = $productStock->current_quantity;
                $productStock->current_quantity -= $quantityConsumed;
                $productStock->available_quantity = $productStock->current_quantity - $productStock->reserved_quantity;
                $productStock->save();

                ProductStockMovement::create([
                    'product_id' => $sourceProduct->product_id,
                    'movement_type' => 'production_out',
                    'quantity' => -$quantityConsumed,
                    'previous_stock' => $previousStock,
                    'new_stock' => $productStock->current_quantity,
                    'reference_type' => 'production_order',
                    'reference_id' => $order->order_id,
                    'reference_number' => $order->order_number,
                    'movement_date' => now(),
                    'performed_by' => auth()->id(),
                    'notes' => 'Découpage pour ordre ' . $order->order_number,
                ]);
            }

            $output = ProductionOutput::create([
                'production_order_id' => $order->order_id,
                'product_id' => $sourceProduct->product_id,
                'famille_id' => $order->source_famille_id,
                'output_type' => 'decoupage',
                'quantity_produced' => $sousBlocsProduced,
                'quantity_consumed' => $quantityConsumed,
                'quantity_defective' => $request->quantity_defective,
                'production_date' => $request->production_date,
                'unit_volume_m3' => $finalVolume,
                'total_volume_m3' => $totalFinalVolume,
                'waste_volume_m3' => $wasteVolume,
                'notes' => $request->notes . " | " .
                       $quantityConsumed . " blocs → " . $sousBlocsProduced . " sous-blocs | " .
                       "Volume source: " . number_format($totalSourceVolume, 4) . " m³ | " .
                       "Volume produit: " . number_format($totalFinalVolume, 4) . " m³ | " .
                       "Chute: " . number_format($wasteVolume, 4) . " m³",
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'is_final_output' => false,
            ]);

            $order->update([
                'is_decoupage_completed' => true,
                'sous_bloc_count' => $sousBlocsProduced,
                'status' => 'in_progress',
                'waste_volume' => $wasteVolume,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Découpage complété avec succès! ' . $sousBlocsProduced . ' sous-blocs produits.',
                'sous_blocs_produced' => $sousBlocsProduced,
                'waste_volume' => $wasteVolume,
                'output_id' => $output->output_id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function completeConversion(Request $request, $id)
    {
        $request->validate([
            'quantity_to_convert' => 'required|numeric|min:0.01',
            'production_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $order = ProductionOrder::with(['product', 'decoupageOutputs'])->findOrFail($id);

            if ($order->production_type !== 'decoupage') {
                throw new \Exception("Cette méthode est uniquement pour les ordres de découpage.");
            }

            if (!$order->is_decoupage_completed) {
                throw new \Exception("La phase de découpage doit être complétée d'abord.");
            }

            if ($order->is_conversion_completed) {
                throw new \Exception("La phase de conversion est déjà complétée.");
            }

            // Calculate available sous-blocks
            $totalSousBlocs = $order->sous_bloc_count;
            $sousBlocsUsed = $order->conversionOutputs->sum('quantity_consumed');
            $availableSousBlocs = $totalSousBlocs - $sousBlocsUsed;

            if ($request->quantity_to_convert > $availableSousBlocs) {
                throw new \Exception("Sous-blocs insuffisants. Disponible: " . $availableSousBlocs . ", Requis: " . $request->quantity_to_convert);
            }

            // Calculate final products produced
            $finalProductsProduced = $request->quantity_to_convert * $order->conversion_rate;

            // Add final products to stock
            $finalProduct = $order->product;

            if ($finalProduct->has_familles && $order->famille_id) {
                // Add to famille stock
                $familleStock = ProductFamilleStock::firstOrCreate(
                    [
                        'product_id' => $finalProduct->product_id,
                        'famille_id' => $order->famille_id
                    ],
                    [
                        'famille_name' => $order->famille->famille_name ?? 'Inconnu',
                        'current_quantity' => 0,
                        'reserved_quantity' => 0,
                        'available_quantity' => 0,
                        'location' => 'Entrepôt Principal',
                        'last_restocked' => now(),
                    ]
                );

                $previousStock = $familleStock->current_quantity;
                $familleStock->current_quantity += $finalProductsProduced;
                $familleStock->available_quantity = $familleStock->current_quantity - $familleStock->reserved_quantity;
                $familleStock->last_restocked = now();
                $familleStock->save();

                $stockRecord = $familleStock;
            } else {
                // Add to regular stock
                $productStock = ProductStock::firstOrCreate(
                    ['product_id' => $finalProduct->product_id],
                    [
                        'current_quantity' => 0,
                        'reserved_quantity' => 0,
                        'available_quantity' => 0,
                        'location' => 'Entrepôt Principal',
                        'last_restocked' => now(),
                    ]
                );

                $previousStock = $productStock->current_quantity;
                $productStock->current_quantity += $finalProductsProduced;
                $productStock->available_quantity = $productStock->current_quantity - $productStock->reserved_quantity;
                $productStock->last_restocked = now();
                $productStock->save();

                $stockRecord = $productStock;
            }

            // Record stock movement
            ProductStockMovement::create([
                'product_id' => $finalProduct->product_id,
                'famille_id' => $order->famille_id,
                'movement_type' => 'production_in',
                'quantity' => $finalProductsProduced,
                'previous_stock' => $previousStock,
                'new_stock' => $stockRecord->current_quantity,
                'reference_type' => 'production_order',
                'reference_id' => $order->order_id,
                'reference_number' => $order->order_number,
                'movement_date' => now(),
                'performed_by' => auth()->id(),
                'notes' => 'Conversion depuis découpage - Ordre ' . $order->order_number,
            ]);

            // Create conversion output record
            $output = ProductionOutput::create([
                'production_order_id' => $order->order_id,
                'product_id' => $order->product_id,
                'famille_id' => $order->famille_id,
                'output_type' => 'conversion',
                'quantity_produced' => $finalProductsProduced,
                'quantity_consumed' => $request->quantity_to_convert,
                'quantity_defective' => 0,
                'production_date' => $request->production_date,
                'notes' => $request->notes . " | " . $request->quantity_to_convert . " sous-blocs → " . $finalProductsProduced . " produits finis",
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'is_final_output' => true,
            ]);

            // Check if order is fully completed
            $totalFinalProducts = $order->conversionOutputs->sum('quantity_produced') + $finalProductsProduced;
            $isConversionCompleted = $totalFinalProducts >= $order->quantity_to_produce;

            // Update order status
            $updateData = [
                'is_conversion_completed' => $isConversionCompleted,
            ];

            if ($isConversionCompleted) {
                $updateData['status'] = 'completed';
                $updateData['actual_completion_date'] = now();
            }

            $order->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conversion complétée avec succès! ' . $finalProductsProduced . ' produits finis produits.',
                'final_products_produced' => $finalProductsProduced,
                'total_final_products' => $totalFinalProducts,
                'order_completed' => $isConversionCompleted,
                'output_id' => $output->output_id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    // DIRECT PRODUCTION COMPLETION (with BOM consumption)
    public function completeWithConsumption(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $order = ProductionOrder::with(['product.billOfMaterials.rawMaterial', 'outputs'])->findOrFail($id);

            if ($order->status !== 'in_progress') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les ordres en cours peuvent être complétés'
                ], 400);
            }

            if ($order->production_type !== 'direct') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette méthode est uniquement pour la production directe'
                ], 400);
            }

            $request->validate([
                'consumptions' => 'required|array',
                'consumptions.*.material_id' => 'required|exists:raw_materials,material_id',
                'consumptions.*.actual_quantity_used' => 'required|numeric|min:0',
                'consumptions.*.waste_quantity' => 'required|numeric|min:0',
                'quantity_produced' => 'required|numeric|min:0.01',
                'quantity_defective' => 'required|numeric|min:0',
                'production_date' => 'required|date|before_or_equal:today',
                'quality_grade' => 'required|in:excellent,good,average,poor',
                'notes' => 'nullable|string',
            ]);

            // Validate quantities
            $totalProduced = $order->outputs->sum('quantity_produced');
            $remaining = $order->quantity_to_produce - $totalProduced;

            if ($request->quantity_produced > $remaining) {
                throw new \Exception("Quantité excessive. Maximum {$remaining} unités autorisées.");
            }

            // Update or create consumption records and reduce stock using FIFO
            foreach ($request->consumptions as $consumptionData) {
                $material = RawMaterial::find($consumptionData['material_id']);

                // Calculate total quantity to consume
                $totalToConsume = $consumptionData['actual_quantity_used'] + $consumptionData['waste_quantity'];

                // Check if enough stock is available
                if ($material->current_stock < $totalToConsume) {
                    throw new \Exception("Stock insuffisant pour {$material->material_name}. Disponible: {$material->current_stock}, Requis: {$totalToConsume}");
                }

                // Get consumption record or create new one
                $consumption = ProductionConsumption::where('production_order_id', $order->order_id)
                    ->where('material_id', $material->material_id)
                    ->first();

                if ($consumption) {
                    // Update existing consumption
                    $consumption->update([
                        'actual_quantity_used' => $consumptionData['actual_quantity_used'],
                        'waste_quantity' => $consumptionData['waste_quantity'],
                        'total_cost' => $consumptionData['actual_quantity_used'] * ($material->average_unit_cost ?? 0),
                    ]);
                } else {
                    // Get planned quantity from BOM
                    $bomItem = BillOfMaterial::where('product_id', $order->product_id)
                        ->where('material_id', $material->material_id)
                        ->first();

                    $plannedQuantity = $bomItem ?
                        $bomItem->quantity_required * $order->quantity_to_produce : 0;

                    // Create new consumption record
                    ProductionConsumption::create([
                        'production_order_id' => $order->order_id,
                        'material_id' => $material->material_id,
                        'planned_quantity' => $plannedQuantity,
                        'actual_quantity_used' => $consumptionData['actual_quantity_used'],
                        'waste_quantity' => $consumptionData['waste_quantity'],
                        'unit_cost' => $material->average_unit_cost ?? 0,
                        'total_cost' => $consumptionData['actual_quantity_used'] * ($material->average_unit_cost ?? 0),
                        'notes' => 'Consommation enregistrée',
                    ]);
                }

                // Consume stock using FIFO (First In First Out)
                $this->consumeStockFIFO($material->material_id, $totalToConsume, $order, [
                    'production_output_id' => null, // Will be updated after output creation
                    'order_number' => $order->order_number,
                    'notes' => 'Consommation pour ordre de production'
                ]);
            }

            // Create production output
            $output = ProductionOutput::create([
                'production_order_id' => $order->order_id,
                'product_id' => $order->product_id,
                'famille_id' => $order->famille_id,
                'output_type' => 'direct',
                'quantity_produced' => $request->quantity_produced,
                'quantity_consumed' => 0,
                'quantity_defective' => $request->quantity_defective,
                'quality_grade' => $request->quality_grade,
                'production_date' => $request->production_date,
                'notes' => $request->notes,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'is_final_output' => true,
            ]);

            // Update stock movement references with output ID
            RawMaterialStockMovement::where('reference_type', 'production_order')
                ->where('reference_id', $order->order_id)
                ->whereNull('reference_number')
                ->update(['reference_id' => $output->output_id, 'reference_type' => 'production_output']);

            // Add good products to stock
            $goodQuantity = $request->quantity_produced - $request->quantity_defective;
            if ($goodQuantity > 0) {
                $this->addProductToStock($order->product_id, $order->famille_id, $goodQuantity, $order, $output);
            }

            // Check if order is completed
            $newTotalProduced = $totalProduced + $request->quantity_produced;
            if ($newTotalProduced >= $order->quantity_to_produce) {
                $order->update([
                    'status' => 'completed',
                    'actual_completion_date' => now(),
                    'completed_by' => auth()->id(),
                ]);
            } else {
                // Order still has remaining quantity
                $order->touch();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Production complétée avec succès!',
                'order_id' => $order->order_id,
                'output_id' => $output->output_id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    private function addProductToStock($productId, $familleId, $quantity, $order, $output)
    {
        $product = Product::find($productId);

        if ($product->has_familles && $familleId) {
            // Add to famille stock
            $familleStock = ProductFamilleStock::firstOrCreate(
                [
                    'product_id' => $productId,
                    'famille_id' => $familleId
                ],
                [
                    'famille_name' => $order->famille->famille_name ?? 'Inconnu',
                    'current_quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                    'location' => 'Entrepôt Principal',
                    'last_restocked' => now(),
                ]
            );

            $previousStock = $familleStock->current_quantity;
            $familleStock->current_quantity += $quantity;
            $familleStock->available_quantity = $familleStock->current_quantity - $familleStock->reserved_quantity;
            $familleStock->last_restocked = now();
            $familleStock->save();

            $stockRecord = $familleStock;
        } else {
            // Add to regular stock
            $productStock = ProductStock::firstOrCreate(
                ['product_id' => $productId],
                [
                    'current_quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                    'location' => 'Entrepôt Principal',
                    'last_restocked' => now(),
                ]
            );

            $previousStock = $productStock->current_quantity;
            $productStock->current_quantity += $quantity;
            $productStock->available_quantity = $productStock->current_quantity - $productStock->reserved_quantity;
            $productStock->last_restocked = now();
            $productStock->save();

            $stockRecord = $productStock;
        }

        // Record stock movement
        ProductStockMovement::create([
            'product_id' => $productId,
            'famille_id' => $familleId,
            'movement_type' => 'production_in',
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $stockRecord->current_quantity,
            'reference_type' => 'production_output',
            'reference_id' => $output->output_id,
            'reference_number' => $order->order_number,
            'movement_date' => now(),
            'performed_by' => auth()->id(),
            'notes' => 'Sortie de production ' . $order->order_number,
        ]);
    }

    private function consumeStockFIFO($materialId, $quantityNeeded, $productionOrder, $details = [], $allowNegative = false)
    {
        // Get stock details in FIFO order (oldest first)
        $stockDetails = StockMovementDetail::where('material_id', $materialId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('stock_movement_id', 'asc') // FIFO: oldest first
            ->get();

        $remainingToConsume = $quantityNeeded;
        $totalCost = 0;
        $consumedDetails = [];

        foreach ($stockDetails as $detail) {
            if ($remainingToConsume <= 0) break;

            $availableQuantity = $detail->remaining_quantity;
            $quantityToTake = min($availableQuantity, $remainingToConsume);

            // Update the stock detail
            $detail->remaining_quantity -= $quantityToTake;
            $detail->save();

            $remainingToConsume -= $quantityToTake;
            $totalCost += $quantityToTake * $detail->unit_price;

            $consumedDetails[] = [
                'stock_detail_id' => $detail->stock_detail_id,
                'quantity_consumed' => $quantityToTake,
                'unit_price' => $detail->unit_price,
                'total_cost' => $quantityToTake * $detail->unit_price
            ];
        }

        if ($remainingToConsume > 0 && !$allowNegative) {
            throw new \Exception("Stock insuffisant pour la matière première ID: {$materialId}");
        }

        $deficit = max(0, $remainingToConsume);

        // Record the consumption in stock movements
        $material = RawMaterial::find($materialId);
        $previousStock = $material->current_stock;
        $newStock = $previousStock - $quantityNeeded;

        $stockMovement = RawMaterialStockMovement::create([
            'material_id' => $materialId,
            'movement_type' => 'production_consumption',
            'quantity' => -$quantityNeeded,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'reference_type' => $details['reference_type'] ?? 'production_order',
            'reference_id' => $details['reference_id'] ?? $productionOrder->order_id,
            'reference_number' => $details['order_number'] ?? $productionOrder->order_number,
            'movement_date' => now(),
            'performed_by' => auth()->id(),
            'notes' => $details['notes'] ?? 'Consommation pour production',
            'created_at' => now(),
        ]);

        // Update the raw material's current stock
        $material->current_stock = $newStock;
        $material->save();

        // Record consumption details
        foreach ($consumedDetails as $consumedDetail) {
            DB::table('stock_consumption_details')->insert([
                'stock_movement_id' => $stockMovement->movement_id,
                'stock_detail_id' => $consumedDetail['stock_detail_id'],
                'quantity_consumed' => $consumedDetail['quantity_consumed'],
                'unit_price' => $consumedDetail['unit_price'],
                'total_cost' => $consumedDetail['total_cost'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Deficit consumed beyond available lots: record as a negative lot so that
        // current_stock (SUM of stock_movement_details.remaining_quantity) reflects
        // the negative balance, since availability was explicitly overridden.
        if ($deficit > 0) {
            StockMovementDetail::create([
                'stock_movement_id' => $stockMovement->movement_id,
                'material_id' => $materialId,
                'quantity' => -$deficit,
                'unit_price' => 0,
                'total_price' => 0,
                'remaining_quantity' => -$deficit,
            ]);

            Log::warning("Chute stock forced negative for material ID {$materialId}", [
                'deficit' => $deficit,
                'reference_id' => $details['reference_id'] ?? $productionOrder->order_id,
            ]);
        }
    }

    // ORDER MANAGEMENT METHODS

    public function edit($id)
    {
        $order = ProductionOrder::with(['product', 'consumptions.rawMaterial'])->findOrFail($id);

        if (in_array($order->status, ['completed', 'cancelled', 'in_progress'])) {
            return redirect()->route('production-orders.show', $id)
                ->with('error', 'Impossible de modifier un ordre ' . $order->status);
        }

        $products = Product::where('is_active', true)->get();

        $productionProducts = Product::where('is_active', true)
            ->whereIn('product_type', ['production', 'both'])
            ->get();

        $decoupageProducts = Product::where('is_active', true)
            ->where('product_type', 'decoupage')
            ->orWhere(function($query) {
                $query->where('is_active', true)
                    ->where('product_type', 'both');
            })
            ->get();

        $salesProducts = Product::where('is_active', true)
            ->whereIn('product_type', ['finale', 'both', 'sales'])
            ->get();

        $type3Products = [];
        if ($order->production_type === 'type3') {
            $type3Products = $order->getType3Products();
        }

        $employees = Employee::whereNull('resignation_date')->orderBy('full_name')->get();

        return view('pages.production-orders.edit', compact(
            'order',
            'products',
            'productionProducts',
            'decoupageProducts',
            'salesProducts',
            'type3Products',
            'employees'
        ));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $order = ProductionOrder::with(['product', 'sourceProduct', 'outputs'])->findOrFail($id);

            // Check if order can be edited
            if (in_array($order->status, ['completed', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de modifier un ordre ' . $order->status
                ], 400);
            }

            // Check if order has outputs
            if ($order->outputs()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de modifier un ordre avec des sorties de production.'
                ], 400);
            }

            $request->validate([
                'production_type' => 'required|in:type1,type2,type3',
                'product_id' => 'required|exists:products,product_id',
                'famille_id' => 'nullable|exists:familles,famille_id',
                'source_famille_id' => 'nullable|exists:familles,famille_id',
                'quantity_to_produce' => 'required|numeric|min:0.01',
                'priority' => 'required|in:low,medium,high,urgent',
                'start_date' => 'required|date',
                'expected_completion_date' => 'required|date',
                'notes' => 'nullable|string|max:500',
                'responsible_employee_id' => 'nullable|exists:employees,employee_id',
                'waste_percentage' => 'nullable|numeric|min:0|max:100',

                // Type specific validations
                'source_product_id' => 'nullable|exists:products,product_id',
                'decoupage_ratio' => 'nullable|numeric|min:1',
                'conversion_rate' => 'nullable|numeric|min:0.01',

                // Material source for Type 1
                'material_source' => 'nullable|in:bom_only,chutes_only,both',
                'bom_percentage' => 'nullable|numeric|min:0|max:100',
                'chutes_volume' => 'nullable|numeric|min:0',
            ]);

            // Type 3 additional validation
            if ($order->production_type === 'type3') {
                $request->validate([
                    'type3_products' => 'required|array|min:1',
                    'type3_products.*.product_id' => 'required|exists:products,product_id',
                    'type3_products.*.conversion_rate' => 'required|numeric|min:0.01',
                    'type3_products.*.quantity_to_produce' => 'required|numeric|min:0.01',
                ]);
            }

            $finalProduct = Product::findOrFail($request->product_id);
            $finalFamille = null;
            $sourceFamille = null;

            // Initialize variables
            $sourceProduct = null;
            $requiredQuantity = 0;
            $decoupageRatio = 0;
            $conversionRate = 0;
            $wastePercentage = $request->waste_percentage ?? 0;

            // Material source variables (for Type 1 only)
            $materialSource = $request->material_source ?? 'bom_only';
            $bomPercentage = $request->bom_percentage ?? 100;
            $chutesVolume = $request->chutes_volume ?? 0;

            // VOLUME CALCULATION VARIABLES
            $sourceVolume = 0;
            $finalVolume = 0;
            $totalVolumeProduced = 0;
            $wasteVolume = 0;

            // Validate based on production type
            switch ($order->production_type) {
                case 'type1':
                    // Type 1: Direct production (BOM -> Production Product)
                    // Validate product type
                    if (!$finalProduct->isProductionProduct() && $finalProduct->product_type !== 'both') {
                        throw new \Exception("Le produit doit être de type production pour la production directe.");
                    }

                    // Check if product has familles and famille is selected
                    if ($finalProduct->has_familles && !$request->famille_id) {
                        throw new \Exception("Ce produit a des familles. Veuillez sélectionner une famille de destination.");
                    }

                    $requiredQuantity = (float) $request->quantity_to_produce;

                    // Check BOM material availability with material source
                    $this->checkMaterialAvailability($finalProduct, $request->quantity_to_produce,
                        $materialSource, $chutesVolume, $bomPercentage);

                    break;

case 'type2':
    // Type 2: Production -> Découpage (Multiple Products)
    if (!$request->source_product_id) {
        throw new \Exception("Le produit source est requis pour le découpage.");
    }

    $sourceProduct = Product::findOrFail($request->source_product_id);

    // Validate source product type
    if (!$sourceProduct->isProductionProduct() && $sourceProduct->product_type !== 'both') {
        throw new \Exception("Le produit source doit être de type production pour le découpage.");
    }

    // Validate that we have products to produce
    if (!$request->has('type2_products') || empty($request->type2_products)) {
        throw new \Exception("Veuillez ajouter au moins un produit à découper.");
    }

    // Get or create famille for source product
    $sourceFamille = null;
    if ($sourceProduct->has_familles) {
        if ($request->source_famille_id) {
            $sourceFamille = Famille::find($request->source_famille_id);
        } else {
            // Create or get default famille for this product
            $sourceFamille = Famille::firstOrCreate(
                [
                    'famille_name' => $sourceProduct->product_name . ' - Default',
                    'famille_code' => 'DFT_' . $sourceProduct->product_code
                ],
                [
                    'description' => 'Famille par défaut pour ' . $sourceProduct->product_name,
                    'is_active' => true,
                    'sort_order' => 0
                ]
            );

            $sourceFamille->associateToProductIfNotExists($sourceProduct->product_id);
        }
    }

    // Calculate totals from all products
    $totalQuantityToProduce = 0;
    $totalSourceRequired = 0;
    $totalVolume = 0;
    $productsData = [];
    $totalWasteVolume = 0;

    foreach ($request->type2_products as $index => $productData) {
        $finalProduct = Product::findOrFail($productData['product_id']);

        // Validate final product type (should be decoupage type)
        if (!$finalProduct->isDecoupageProduct() && $finalProduct->product_type !== 'decoupage') {
            throw new \Exception("Le produit #" . ($index + 1) . " doit être de type découpage.");
        }

        $decoupageRatio = $productData['decoupage_ratio'];
        $quantityToProduce = $productData['quantity_to_produce'];

        // Calculate source required based on decoupage ratio
        $sourceRequired = ceil($quantityToProduce / $decoupageRatio);

        // Calculate volume
        $productVolume = $this->calculateProductVolume($finalProduct);
        $productTotalVolume = $quantityToProduce * $productVolume;

        $totalQuantityToProduce += $quantityToProduce;
        $totalSourceRequired += $sourceRequired;
        $totalVolume += $productTotalVolume;

        $productsData[] = [
            'product' => $finalProduct,
            'decoupage_ratio' => $decoupageRatio,
            'quantity_to_produce' => $quantityToProduce,
            'source_required' => $sourceRequired,
            'volume_per_unit' => $productVolume,
            'total_volume' => $productTotalVolume,
        ];
    }

    // Calculate waste volume
    $sourceVolume = $this->calculateProductVolume($sourceProduct);
    $totalSourceVolume = $totalSourceRequired * $sourceVolume;
    $wasteVolume = max(0, $totalSourceVolume - $totalVolume);

    // Check source product stock availability
    $this->checkSourceProductStock($sourceProduct, $totalSourceRequired, $sourceFamille ? $sourceFamille->famille_id : null);

    // Set the required quantity to the total source required
    $requiredQuantity = $totalSourceRequired;

    break;

                case 'type3':
                    // Type 3: Découpage -> Vente (Multiple Products)
                    if (!$request->source_product_id) {
                        throw new \Exception("Le produit source est requis pour la conversion.");
                    }

                    $sourceProduct = Product::findOrFail($request->source_product_id);

                    // Validate source product type is DECOUPAGE
                    if (!$sourceProduct->isDecoupageProduct() && $sourceProduct->product_type !== 'decoupage') {
                        throw new \Exception("Le produit source doit être de type découpage pour la conversion.");
                    }

                    // Get or create famille for source product
                    if ($sourceProduct->has_familles) {
                        if ($request->source_famille_id) {
                            $sourceFamille = Famille::find($request->source_famille_id);
                        } else {
                            // Create or get default famille for this decoupage product
                            $sourceFamille = Famille::firstOrCreate(
                                [
                                    'famille_name' => $sourceProduct->product_name . ' - Sous-blocs',
                                    'famille_code' => 'SUB_' . $sourceProduct->product_code
                                ],
                                [
                                    'description' => 'Famille de sous-blocs pour ' . $sourceProduct->product_name,
                                    'is_active' => true,
                                    'sort_order' => 0
                                ]
                            );

                            $sourceFamille->associateToProductIfNotExists($sourceProduct->product_id);
                        }
                    }

                    // Calculate totals from all products
                    $totalQuantityToProduce = 0;
                    $totalSourceRequired = 0;
                    $totalVolume = 0;
                    $productsData = [];

                    foreach ($request->type3_products as $index => $productData) {
                        $finalProductItem = Product::findOrFail($productData['product_id']);

                        // Validate final product type
                        if (!$finalProductItem->isFinaleProduct() && $finalProductItem->product_type !== 'both') {
                            throw new \Exception("Le produit final #" . ($index + 1) . " doit être de type vente (finale).");
                        }

                        $conversionRateItem = $productData['conversion_rate'];
                        $quantityToProduce = $productData['quantity_to_produce'];

                        // Calculate source required based on conversion rate
                        $sourceRequired = ceil($quantityToProduce / $conversionRateItem);

                        // Calculate volume
                        $productVolume = $this->calculateProductVolume($finalProductItem);
                        $productTotalVolume = $quantityToProduce * $productVolume;

                        $totalQuantityToProduce += $quantityToProduce;
                        $totalSourceRequired += $sourceRequired;
                        $totalVolume += $productTotalVolume;

                        $productsData[] = [
                            'product_id' => $finalProductItem->product_id,
                            'conversion_rate' => $conversionRateItem,
                            'quantity_to_produce' => $quantityToProduce,
                            'source_required' => $sourceRequired,
                            'volume_per_unit' => $productVolume,
                            'total_volume' => $productTotalVolume,
                        ];
                    }

                    // Get or create familles for final products
                    $finalFamille = null;
                    if ($productsData[0]['product_id'] && ($product = Product::find($productsData[0]['product_id'])) && $product->has_familles) {
                        if ($request->famille_id) {
                            $finalFamille = Famille::find($request->famille_id);
                        } else {
                            $finalFamille = Famille::firstOrCreate(
                                [
                                    'famille_name' => 'Type 3 - Multiple Produits',
                                    'famille_code' => 'T3_' . $sourceProduct->product_code
                                ],
                                [
                                    'description' => 'Famille pour conversion Type 3 avec multiple produits',
                                    'is_active' => true,
                                    'sort_order' => 0
                                ]
                            );
                        }
                    }

                    // Use the calculated source required
                    $requiredQuantity = $totalSourceRequired;

                    // Calculate volumes for Type 3
                    $sourceVolume = $this->calculateProductVolume($sourceProduct);
                    $totalSourceVolume = $requiredQuantity * $sourceVolume;

                    // Calculate waste volume (for informational purposes)
                    $wasteVolume = max(0, $totalSourceVolume - $totalVolume);

                    // Check source product stock availability
                    $this->checkSourceProductStock($sourceProduct, $requiredQuantity,
                        $sourceFamille ? $sourceFamille->famille_id : null);

                    // Update quantity to produce (sum of all products)
                    $request->merge(['quantity_to_produce' => $totalQuantityToProduce]);
                    $request->merge(['required_quantity' => $totalSourceRequired]);

                    break;

                default:
                    throw new \Exception("Type de production invalide.");
            }

            // Update the production order
            $updateData = [
                'product_id' => $request->production_type === 'type3' ?
                    ($productsData[0]['product_id'] ?? $order->product_id) :
                    $finalProduct->product_id,
                'famille_id' => $finalFamille ? $finalFamille->famille_id : ($request->famille_id ?? $order->famille_id),
                'source_product_id' => $sourceProduct ? $sourceProduct->product_id : ($request->source_product_id ?? $order->source_product_id),
                'source_famille_id' => $sourceFamille ? $sourceFamille->famille_id : ($request->source_famille_id ?? $order->source_famille_id),
                'quantity_to_produce' => $request->production_type === 'type3' ?
                    $totalQuantityToProduce :
                    $request->quantity_to_produce,
                'required_quantity' => $requiredQuantity,
                'priority' => $request->priority,
                'start_date' => $request->start_date,
                'expected_completion_date' => $request->expected_completion_date,
                'notes' => $request->notes,
                'responsible_employee_id' => $request->responsible_employee_id,
                'waste_percentage' => $wastePercentage,
                'decoupage_ratio' => $decoupageRatio,
                'conversion_rate' => $conversionRate,
                'material_source' => $materialSource,
                'bom_percentage' => $bomPercentage,
                'chutes_volume' => $chutesVolume,
                'source_volume' => $sourceVolume,
                'final_volume' => $finalVolume,
                'total_volume_produced' => $totalVolumeProduced ?? $totalVolume ?? 0,
                'waste_volume' => $wasteVolume,
                'additional_data' => $order->production_type === 'type3' ? json_encode([
                    'multiple_products' => true,
                    'products_count' => count($productsData),
                    'products_summary' => array_map(function($product) {
                        $prod = Product::find($product['product_id']);
                        return [
                            'product_id' => $product['product_id'],
                            'product_name' => $prod ? $prod->product_name : 'N/A',
                            'quantity_to_produce' => $product['quantity_to_produce'],
                            'conversion_rate' => $product['conversion_rate'],
                        ];
                    }, $productsData)
                ]) : $order->additional_data,
            ];

            $order->update($updateData);

            if ($order->production_type === 'type1') {
                $this->updateConsumptionRecords($order, $request->quantity_to_produce,
                    $materialSource, $chutesVolume, $bomPercentage);
            }

            // Update Type 3 products
            if ($order->production_type === 'type3') {
                // Delete existing Type 3 products
                DB::table('production_order_products')
                    ->where('production_order_id', $order->order_id)
                    ->delete();

                // Insert updated Type 3 products
                foreach ($productsData as $productData) {
                    DB::table('production_order_products')->insert([
                        'production_order_id' => $order->order_id,
                        'product_id' => $productData['product_id'],
                        'conversion_rate' => $productData['conversion_rate'],
                        'quantity_to_produce' => $productData['quantity_to_produce'],
                        'source_required' => $productData['source_required'],
                        'volume_per_unit' => $productData['volume_per_unit'],
                        'total_volume' => $productData['total_volume'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ordre mis à jour avec succès!',
                'order_id' => $order->order_id,
                'order_number' => $order->order_number,
                'production_type' => $order->production_type,
                'multiple_products' => $order->production_type === 'type3' ? count($productsData) : 0,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }


    private function updateConsumptionRecords(ProductionOrder $order, $newQuantity,
        $materialSource = 'bom_only', $chutesVolume = 0, $bomPercentage = 100)
    {
        // First, get all existing consumption records for this order
        $existingConsumptions = ProductionConsumption::where('production_order_id', $order->order_id)->get();

        // Get BOM for the product
        $bom = BillOfMaterial::where('product_id', $order->product_id)
            ->with('rawMaterial')
            ->get();

        $percentageFactor = $materialSource === 'both' ? ($bomPercentage / 100) :
                        ($materialSource === 'bom_only' ? 1 : 0);

        // Update or create BOM material consumption records
        foreach ($bom as $item) {
            $plannedQuantity = $item->quantity_required * $newQuantity * $percentageFactor;

            // Find existing record for this material
            $existingRecord = $existingConsumptions->firstWhere('material_id', $item->material_id);

            if ($existingRecord) {
                // Update existing record
                $existingRecord->update([
                    'planned_quantity' => $plannedQuantity,
                    'total_cost' => $plannedQuantity * $item->rawMaterial->unit_cost,
                    'updated_at' => now(),
                ]);
            } elseif ($plannedQuantity > 0) {
                // Create new record if it doesn't exist and we need it
                ProductionConsumption::create([
                    'production_order_id' => $order->order_id,
                    'material_id' => $item->material_id,
                    'planned_quantity' => $plannedQuantity,
                    'actual_quantity_used' => 0,
                    'waste_quantity' => 0,
                    'unit_cost' => $item->rawMaterial->unit_cost,
                    'total_cost' => $plannedQuantity * $item->rawMaterial->unit_cost,
                    'notes' => 'Consommation planifiée',
                    'is_waste' => false,
                ]);
            }
        }

        // Handle chutes material
        if ($materialSource === 'chutes_only' || $materialSource === 'both') {
            $chutesMaterial = RawMaterial::where('material_code', 'CHUTE-PRODUCTION')->first();

            if ($chutesMaterial) {
                $chutesRequired = $materialSource === 'chutes_only' ?
                    $chutesVolume :
                    ($chutesVolume * ((100 - $bomPercentage) / 100));

                // Find existing chutes record
                $existingChutesRecord = $existingConsumptions->firstWhere('material_id', $chutesMaterial->material_id);

                if ($existingChutesRecord) {
                    // Update existing chutes record
                    $existingChutesRecord->update([
                        'planned_quantity' => $chutesRequired,
                        'total_cost' => 0,
                        'updated_at' => now(),
                    ]);
                } elseif ($chutesRequired > 0) {
                    // Create new chutes record
                    ProductionConsumption::create([
                        'production_order_id' => $order->order_id,
                        'material_id' => $chutesMaterial->material_id,
                        'planned_quantity' => $chutesRequired,
                        'actual_quantity_used' => 0,
                        'waste_quantity' => 0,
                        'unit_cost' => 0,
                        'total_cost' => 0,
                        'notes' => 'Chutes de production recyclées',
                        'is_waste' => true,
                    ]);
                }
            }
        }

        $neededMaterialIds = [];

        // Add BOM material IDs if needed
        if ($percentageFactor > 0) {
            foreach ($bom as $item) {
                $neededMaterialIds[] = $item->material_id;
            }
        }

        // Add chutes material ID if needed
        if (($materialSource === 'chutes_only' || $materialSource === 'both') &&
            $chutesMaterial &&
            (($materialSource === 'chutes_only' && $chutesVolume > 0) ||
            ($materialSource === 'both' && ($chutesVolume * ((100 - $bomPercentage) / 100)) > 0))) {
            $neededMaterialIds[] = $chutesMaterial->material_id;
        }

        if (!empty($neededMaterialIds)) {
            ProductionConsumption::where('production_order_id', $order->order_id)
                ->whereNotIn('material_id', $neededMaterialIds)
                ->delete();
        } else {
            ProductionConsumption::where('production_order_id', $order->order_id)->delete();
        }
    }

    public function getConsumedBlocks($id)
    {
        try {
            $order = ProductionOrder::findOrFail($id);

            $totalConsumed = $order->outputs
                ->where('output_type', 'type2')
                ->sum('quantity_consumed');

            $totalProduced = $order->outputs
                ->where('output_type', 'type2')
                ->sum('quantity_produced');

            return response()->json([
                'success' => true,
                'total_consumed' => $totalConsumed,
                'total_produced' => $totalProduced,
                'required_quantity' => $order->required_quantity,
                'quantity_to_produce' => $order->quantity_to_produce
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getProductFamilles($productId)
    {
        try {
            $product = Product::with(['familles' => function($query) {
                $query->where('is_active', true)->orderBy('famille_name');
            }])->findOrFail($productId);

            $familles = $product->familles->map(function($famille) {
                return [
                    'famille_id' => $famille->famille_id,
                    'famille_name' => $famille->famille_name,
                    'famille_code' => $famille->famille_code,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $familles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $order = ProductionOrder::with(['product', 'consumptions.rawMaterial', 'sourceProduct'])->findOrFail($id);

            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les ordres en attente peuvent être approuvés'
                ], 400);
            }

            // Check material availability for direct production
            if ($order->production_type === 'direct') {
                $this->checkMaterialAvailability($order->product, $order->quantity_to_produce);
            }

            // Check source product availability for decoupage
            if ($order->production_type === 'decoupage' && $order->source_product_id) {
                $sourceProduct = $order->sourceProduct;
                $this->checkSourceProductStock($sourceProduct, $order->required_quantity, $order->source_famille_id);
            }

            $order->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            ProductionOrderNotificationHelper::notifyOrderApproved($order->fresh(['product']));

            return response()->json([
                'success' => true,
                'message' => 'Ordre approuvé avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function start($id)
    {
        DB::beginTransaction();
        try {
            $order = ProductionOrder::with(['consumptions.rawMaterial', 'sourceProduct', 'sourceFamille'])->findOrFail($id);

            if ($order->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les ordres approuvés peuvent démarrer'
                ], 400);
            }

            if ($order->production_type === 'type2' || $order->production_type === 'type3' || $order->production_type === 'type4') {
                $sourceFamilleId = $order->source_famille_id;

                if (!$sourceFamilleId) {
                    throw new \Exception("La famille source n'est pas définie pour cet ordre");
                }

                $additionalData = is_array($order->additional_data)
                    ? $order->additional_data
                    : json_decode($order->additional_data ?? '{}', true);
                $sourcesFromData = $additionalData['source_products'] ?? [];

                // Build list of [{product, qty}] to consume
                if (count($sourcesFromData) > 1) {
                    $toConsume = [];
                    foreach ($sourcesFromData as $sp) {
                        $spProduct = \App\Models\Product::find($sp['product_id'] ?? null);
                        if (!$spProduct) continue;
                        $spQty = (float) ($sp['quantity'] ?? 0);
                        if ($spQty <= 0) continue;
                        $toConsume[] = ['product' => $spProduct, 'qty' => $spQty];
                    }
                    // Fallback to primary if additional_data gave nothing usable
                    if (empty($toConsume)) {
                        $sp = $order->sourceProduct;
                        if (!$sp) throw new \Exception("Produit source non trouvé pour cet ordre");
                        $toConsume[] = ['product' => $sp, 'qty' => (float) $order->required_quantity];
                    }
                } else {
                    $sourceProduct = $order->sourceProduct;
                    if (!$sourceProduct) {
                        throw new \Exception("Produit source non trouvé pour cet ordre");
                    }
                    $toConsume = [['product' => $sourceProduct, 'qty' => (float) $order->required_quantity]];
                }

                $famille = Famille::find($sourceFamilleId);

                foreach ($toConsume as $item) {
                    $spProduct = $item['product'];
                    $spQty     = $item['qty'];

                    $familleStock = ProductFamilleStock::where('product_id', $spProduct->product_id)
                        ->where('famille_id', $sourceFamilleId)
                        ->first();

                    if (!$familleStock) {
                        $familleStock = ProductFamilleStock::create([
                            'product_id'       => $spProduct->product_id,
                            'famille_id'       => $sourceFamilleId,
                            'famille_name'     => $famille ? $famille->famille_name : 'Famille',
                            'current_quantity'  => 0,
                            'reserved_quantity' => 0,
                            'available_quantity'=> 0,
                            'location'         => 'Entrepôt Principal',
                            'last_restocked'   => now(),
                            'created_at'       => now(),
                        ]);
                    }

                    $previousStock = $familleStock->current_quantity;
                    $familleStock->current_quantity  -= $spQty;
                    $familleStock->available_quantity = $familleStock->current_quantity - $familleStock->reserved_quantity;
                    $familleStock->save();

                    ProductStockMovement::create([
                        'product_id'       => $spProduct->product_id,
                        'famille_id'       => $sourceFamilleId,
                        'movement_type'    => 'production_start',
                        'quantity'         => -$spQty,
                        'previous_stock'   => $previousStock,
                        'new_stock'        => $familleStock->current_quantity,
                        'reference_type'   => 'production_order',
                        'reference_id'     => $order->order_id,
                        'reference_number' => $order->order_number,
                        'movement_date'    => now(),
                        'performed_by'     => auth()->id(),
                        'notes'            => "Consommation de {$spQty} {$spProduct->unit_of_measure} au démarrage de l'ordre",
                        'created_at'       => now(),
                    ]);

                    Log::info("Stock consumed at start for order {$order->order_number}", [
                        'production_type'    => $order->production_type,
                        'product_id'         => $spProduct->product_id,
                        'famille_id'         => $sourceFamilleId,
                        'quantity'           => $spQty,
                        'remaining_stock'    => $familleStock->current_quantity,
                        'remaining_available'=> $familleStock->available_quantity,
                    ]);
                }
            } elseif ($order->production_type === 'type5') {
                $chutesVolume = (float) $order->chutes_volume;

                if ($chutesVolume <= 0) {
                    throw new \Exception("Le volume de chutes n'est pas défini pour cet ordre");
                }

                $chuteMaterial = $this->getOrCreateChuteMaterial();

                $additionalData = is_array($order->additional_data)
                    ? $order->additional_data
                    : json_decode($order->additional_data ?? '{}', true);
                $forceChutesOverride = (bool) ($additionalData['force_chutes_override'] ?? false);

                if (!$forceChutesOverride && $chuteMaterial->current_stock < $chutesVolume) {
                    throw new \Exception(
                        "Stock de chutes insuffisant pour démarrer. Requis: " .
                        number_format($chutesVolume, 4) . " m³, Disponible: " .
                        number_format($chuteMaterial->current_stock, 4) . " m³"
                    );
                }

                $this->consumeStockFIFO($chuteMaterial->material_id, $chutesVolume, $order, [
                    'notes' => "Consommation de {$chutesVolume} m³ de chutes au démarrage de l'ordre",
                ], $forceChutesOverride);

                $chuteMaterial->refresh();

                Log::info("Chutes consumed at start for order {$order->order_number}", [
                    'production_type' => $order->production_type,
                    'chutes_volume'   => $chutesVolume,
                    'remaining_stock' => $chuteMaterial->current_stock,
                ]);
            }

            $order->update([
                'status' => 'in_progress',
                'started_at' => now(),
                'started_by' => auth()->id(),
            ]);

            DB::commit();

            ProductionOrderNotificationHelper::notifyOrderStarted($order->fresh(['product']));

            return response()->json([
                'success' => true,
                'message' => 'Production démarrée avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting production order', [
                'order_id' => $id,
                'production_type' => $order->production_type ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function complete($id)
    {
        DB::beginTransaction();
        try {
            $order = ProductionOrder::with(['consumptions'])->findOrFail($id);

            if ($order->status !== 'in_progress') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les ordres en cours peuvent être complétés'
                ], 400);
            }

            if ($order->production_type === 'direct') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pour la production directe, utilisez la méthode avec consommation'
                ], 400);
            }

            if ($order->production_type === 'decoupage' && !$order->is_conversion_completed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pour le découpage, complétez d\'abord les deux phases'
                ], 400);
            }

            // Consume raw material stock (FIFO from StockMovementDetail) for each planned consumption
            foreach ($order->consumptions as $consumption) {
                $material = RawMaterial::find($consumption->material_id);
                if (!$material) continue;

                $qty = $consumption->planned_quantity ?? 0;
                if ($qty <= 0) continue;

                // Skip if stock movement already recorded for this consumption
                $alreadyConsumed = RawMaterialStockMovement::where('material_id', $consumption->material_id)
                    ->where('reference_type', 'production_order')
                    ->where('reference_id', $order->order_id)
                    ->where('movement_type', 'production_consumption')
                    ->exists();
                if ($alreadyConsumed) continue;

                $this->consumeStockFIFO($consumption->material_id, $qty, $order, [
                    'reference_type'  => 'production_order',
                    'reference_id'    => $order->order_id,
                    'order_number'    => $order->order_number,
                    'notes'           => 'Consommation à la clôture de l\'ordre ' . $order->order_number,
                ]);
            }

            $order->update([
                'status' => 'completed',
                'actual_completion_date' => now(),
                'completed_by' => auth()->id(),
            ]);

            DB::commit();

            ProductionOrderNotificationHelper::notifyOrderCompleted($order->fresh(['product']));

            return response()->json([
                'success' => true,
                'message' => 'Ordre marqué comme terminé!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return stock items (RM + source products) that will be restored when the order is cancelled.
     */
    public function getCancellationPreview($id)
    {
        try {
            $order = ProductionOrder::findOrFail($id);
            $items = [];

            // ── 1. Raw material consumed (type 1) — will be RESTORED (+) ────
            $rmMovements = RawMaterialStockMovement::where('movement_type', 'production_consumption')
                ->where('reference_number', $order->order_number)
                ->with('rawMaterial')
                ->get();

            $rmSummary = [];
            foreach ($rmMovements as $mv) {
                $mid = $mv->material_id;
                $rmSummary[$mid] = $rmSummary[$mid] ?? [
                    'key'       => 'rm_' . $mid,
                    'direction' => 'restore',
                    'type'      => 'raw_material',
                    'label'     => 'Matière Première',
                    'code'      => $mv->rawMaterial->material_code ?? '-',
                    'name'      => $mv->rawMaterial->material_name ?? 'Inconnu',
                    'unit'      => $mv->rawMaterial->unit_of_measure ?? '',
                    'qty'       => 0,
                ];
                $rmSummary[$mid]['qty'] += abs($mv->quantity);
            }

            // For each MP, prefer the réellement utilisée quantity recorded on
            // the consumption record (excludes waste) over the raw stock-movement
            // total (which includes waste). The movement total is kept as 'max'
            // so the user can still restore the full deducted amount if needed.
            $consumedByMaterial = ProductionConsumption::where('production_order_id', $order->order_id)
                ->whereIn('material_id', array_keys($rmSummary))
                ->pluck('actual_quantity_used', 'material_id');

            foreach ($rmSummary as $mid => &$summary) {
                $summary['max'] = $summary['qty'];
                $realQty = (float) ($consumedByMaterial[$mid] ?? 0);
                if ($realQty > 0) {
                    $summary['qty'] = min($realQty, $summary['max']);
                }
            }
            unset($summary);

            $items = array_merge($items, array_values($rmSummary));

            // ── 2. Source products consumed (type 2/3/4) — will be RESTORED (+)
            $sourceMovements = ProductStockMovement::whereIn('movement_type', [
                    'type2_consumption', 'type3_consumption', 'type4_consumption','production_start'
                ])
                ->where('reference_number', $order->order_number)
                ->with(['product', 'famille'])
                ->get();

            $sourceSummary = [];
            foreach ($sourceMovements as $mv) {
                $k = $mv->product_id . '_' . ($mv->famille_id ?? 0);
                $label = match ($mv->movement_type) {
                    'type2_consumption' => 'Source Découpage',
                    'type3_consumption' => 'Source Conversion',
                    default             => 'Source Finale',
                };
                $sourceSummary[$k] = $sourceSummary[$k] ?? [
                    'key'       => 'src_' . $k,
                    'direction' => 'restore',
                    'type'      => 'source_product',
                    'label'     => $label,
                    'code'      => $mv->product->product_code ?? '-',
                    'name'      => ($mv->product->product_name ?? 'Produit') .
                                   ' / ' . ($mv->famille->famille_name ?? $mv->famille_name ?? ''),
                    'unit'      => 'unités',
                    'qty'       => 0,
                ];
                $sourceSummary[$k]['qty'] += abs($mv->quantity);
            }

            // Supplement: source products from additional_data that have no recorded movement
            $additionalData = is_array($order->additional_data)
                ? $order->additional_data
                : json_decode($order->additional_data ?? '{}', true);
            $plannedSources = $additionalData['source_products'] ?? [];
            $sourceFamilleId = $order->source_famille_id;

            // Build a map of planned quantities by product_id so we can cap movement totals
            $plannedQtyByPid = [];
            foreach ($plannedSources as $sp) {
                if (!empty($sp['product_id'])) {
                    $plannedQtyByPid[$sp['product_id']] = (float) ($sp['quantity'] ?? 0);
                }
            }

            // Cap each source product's qty at its planned quantity (prevents double-deduction
            // in legacy orders where production_start + type3_consumption both fired)
            if (!empty($plannedQtyByPid)) {
                foreach ($sourceSummary as $k => &$entry) {
                    $pid = (int) explode('_', $k, 2)[0];
                    if (isset($plannedQtyByPid[$pid]) && $entry['qty'] > $plannedQtyByPid[$pid]) {
                        $entry['qty'] = $plannedQtyByPid[$pid];
                    }
                }
                unset($entry);
            }

            foreach ($plannedSources as $sp) {
                $pid = $sp['product_id'] ?? null;
                if (!$pid) continue;

                // Check if any movement already covers this product_id
                $covered = false;
                foreach ($sourceSummary as $k => $_) {
                    if (str_starts_with($k, $pid . '_')) { $covered = true; break; }
                }
                if ($covered) continue;

                $planKey = 'plan_' . $pid . '_' . ($sourceFamilleId ?? 0);
                $product = \App\Models\Product::find($pid);
                $famille = $sourceFamilleId ? \App\Models\Famille::find($sourceFamilleId) : null;

                $sourceSummary[$planKey] = [
                    'key'       => $planKey,
                    'direction' => 'restore',
                    'type'      => 'source_product',
                    'label'     => 'Source Planifiée',
                    'code'      => $product->product_code ?? '-',
                    'name'      => ($product->product_name ?? 'Produit') .
                                   ($famille ? ' / ' . $famille->famille_name : ''),
                    'unit'      => 'unités',
                    'qty'       => (float) ($sp['quantity'] ?? 0),
                ];
            }

            $items = array_merge($items, array_values($sourceSummary));

            // ── 3. Produced goods added to stock — will be REMOVED (-) ───────
            $outputMovements = ProductStockMovement::where('movement_type', 'production_output')
                ->where('reference_number', $order->order_number)
                ->where('quantity', '>', 0)
                ->with(['product', 'famille'])
                ->get();

            $outputSummary = [];
            foreach ($outputMovements as $mv) {
                $k = $mv->product_id . '_' . ($mv->famille_id ?? 0);
                $outputSummary[$k] = $outputSummary[$k] ?? [
                    'key'       => 'out_' . $k,
                    'direction' => 'remove',
                    'type'      => 'produced_product',
                    'label'     => 'Produit Fabriqué',
                    'code'      => $mv->product->product_code ?? '-',
                    'name'      => ($mv->product->product_name ?? 'Produit') .
                                   ' / ' . ($mv->famille->famille_name ?? $mv->famille_name ?? ''),
                    'unit'      => 'unités',
                    'qty'       => 0,
                ];
                $outputSummary[$k]['qty'] += abs($mv->quantity);
            }
            $items = array_merge($items, array_values($outputSummary));

            return response()->json([
                'success' => true,
                'items'   => $items,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'items'   => [],
            ], 500);
        }
    }

    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $order = ProductionOrder::findOrFail($id);

            if ($order->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet ordre est déjà annulé.'
                ], 400);
            }

            // Parse the user-selected stock items: [{key, qty}, ...]
            $stockItemsRaw = json_decode(request('stock_items', '[]'), true) ?: [];
            $stockItemsMap = []; // key => user-requested qty
            foreach ($stockItemsRaw as $item) {
                if (!empty($item['key'])) {
                    $stockItemsMap[$item['key']] = max(0, (float) ($item['qty'] ?? 0));
                }
            }

            $restoredPerMaterial = [];

            // ── 1. Revert RM stock ───────────────────────────────────────────
            $movements = RawMaterialStockMovement::where('movement_type', 'production_consumption')
                ->where('reference_number', $order->order_number)
                ->with('rawMaterial')
                ->get();

            // Group by material so we can do proportional FIFO restore
            $rmByMaterial = [];
            foreach ($movements as $mv) {
                $mid = $mv->material_id;
                if (!isset($rmByMaterial[$mid])) {
                    $rmByMaterial[$mid] = ['movements' => [], 'total' => 0, 'material' => $mv->rawMaterial];
                }
                $rmByMaterial[$mid]['movements'][] = $mv;
                $rmByMaterial[$mid]['total'] += abs($mv->quantity);
            }

            foreach ($rmByMaterial as $mid => $data) {
                $itemKey = 'rm_' . $mid;
                if (!array_key_exists($itemKey, $stockItemsMap)) {
                    continue; // user did not select this item
                }

                $totalConsumed = $data['total'];
                $userQty = min($stockItemsMap[$itemKey], $totalConsumed);
                if ($userQty <= 0) continue;

                // Restore FIFO details up to userQty (first consumed, first restored)
                $remaining = $userQty;
                foreach ($data['movements'] as $movement) {
                    if ($remaining <= 0) break;

                    $consumedDetails = DB::table('stock_consumption_details')
                        ->where('stock_movement_id', $movement->movement_id)
                        ->get();

                    foreach ($consumedDetails as $cd) {
                        if ($remaining <= 0) break;
                        $restoreAmount = min((float) $cd->quantity_consumed, $remaining);
                        StockMovementDetail::where('stock_detail_id', $cd->stock_detail_id)
                            ->increment('remaining_quantity', $restoreAmount);
                        $remaining -= $restoreAmount;
                    }

                    StockMovementDetail::where('stock_movement_id', $movement->movement_id)
                        ->where('remaining_quantity', '<', 0)
                        ->update(['remaining_quantity' => 0]);
                }

                $material = $data['material'];
                if ($material) {
                    $prevStock = (float) $material->current_stock;
                    $newStock  = $prevStock + $userQty;
                    $material->current_stock = $newStock;
                    $material->save();

                    RawMaterialStockMovement::create([
                        'material_id'      => $mid,
                        'movement_type'    => 'cancellation',
                        'quantity'         => $userQty,
                        'previous_stock'   => $prevStock,
                        'new_stock'        => $newStock,
                        'reference_type'   => 'production_order',
                        'reference_number' => $order->order_number,
                        'movement_date'    => now(),
                        'performed_by'     => auth()->id(),
                        'notes'            => 'Restauration stock - Annulation commande #' . $order->order_number,
                    ]);

                    $restoredPerMaterial[$itemKey] = [
                        'name'      => $material->material_name,
                        'qty'       => $userQty,
                        'unit'      => $material->unit_of_measure,
                        'direction' => 'restore',
                    ];
                }
            }

            // Reset production_consumption consumed flags
            ProductionConsumption::where('production_order_id', $order->order_id)
                ->update(['is_stock_consumed' => false, 'stock_consumed_quantity' => 0]);

            // ── 2. Revert source product stock (type 2/3/4 + production_start) → ADD BACK ──
            $sourceMovements = ProductStockMovement::whereIn('movement_type', [
                    'type2_consumption', 'type3_consumption', 'type4_consumption', 'production_start',
                ])
                ->where('reference_number', $order->order_number)
                ->with(['product', 'famille'])
                ->get();

            $srcByKey = [];
            foreach ($sourceMovements as $mv) {
                $k = $mv->product_id . '_' . ($mv->famille_id ?? 0);
                if (!isset($srcByKey[$k])) {
                    $srcByKey[$k] = ['total' => 0, 'mv' => $mv];
                }
                $srcByKey[$k]['total'] += abs($mv->quantity);
            }

            foreach ($srcByKey as $k => $data) {
                $itemKey = 'src_' . $k;
                if (!array_key_exists($itemKey, $stockItemsMap)) {
                    continue;
                }

                $mv      = $data['mv'];
                $userQty = min($stockItemsMap[$itemKey], $data['total']);
                if ($userQty <= 0) continue;

                $familleStock = ProductFamilleStock::where('product_id', $mv->product_id)
                    ->where('famille_id', $mv->famille_id)
                    ->first();

                if ($familleStock) {
                    $prev   = (float) $familleStock->current_quantity;
                    $newQty = $prev + $userQty;
                    $familleStock->current_quantity   = $newQty;
                    $familleStock->available_quantity = $newQty - $familleStock->reserved_quantity;
                    $familleStock->save();

                    ProductStockMovement::create([
                        'product_id'       => $mv->product_id,
                        'famille_id'       => $mv->famille_id,
                        'famille_name'     => $mv->famille_name,
                        'movement_type'    => 'cancellation_reversal',
                        'quantity'         => $userQty,
                        'previous_stock'   => $prev,
                        'new_stock'        => $newQty,
                        'reference_type'   => 'production_order',
                        'reference_number' => $order->order_number,
                        'movement_date'    => now(),
                        'performed_by'     => auth()->id(),
                        'notes'            => 'Restauration source - Annulation #' . $order->order_number,
                    ]);

                    $restoredPerMaterial[$itemKey] = [
                        'name'      => ($mv->product->product_name ?? 'Produit') . ' / ' .
                                       ($mv->famille->famille_name ?? $mv->famille_name ?? ''),
                        'qty'       => $userQty,
                        'unit'      => 'unités',
                        'direction' => 'restore',
                    ];
                }
            }

            // ── 2b. Restore planned source products that have no recorded movement ──
            foreach ($stockItemsMap as $itemKey => $userQty) {
                if (!str_starts_with($itemKey, 'plan_')) continue;
                if ($userQty <= 0) continue;

                // key format: plan_{product_id}_{famille_id}
                $parts     = explode('_', $itemKey, 3);
                $productId = $parts[1] ?? null;
                $familleId = $parts[2] ?? null;
                if (!$productId || $familleId === null) continue;

                $familleStock = ProductFamilleStock::where('product_id', $productId)
                    ->where('famille_id', $familleId)
                    ->first();

                $product = \App\Models\Product::find($productId);
                $famille = \App\Models\Famille::find($familleId);

                if ($familleStock) {
                    $prev   = (float) $familleStock->current_quantity;
                    $newQty = $prev + $userQty;
                    $familleStock->current_quantity   = $newQty;
                    $familleStock->available_quantity = $newQty - $familleStock->reserved_quantity;
                    $familleStock->save();
                } else {
                    $prev   = 0;
                    $newQty = $userQty;
                    ProductFamilleStock::create([
                        'product_id'        => $productId,
                        'famille_id'        => $familleId,
                        'famille_name'      => $famille?->famille_name ?? '',
                        'current_quantity'  => $newQty,
                        'reserved_quantity' => 0,
                        'available_quantity'=> $newQty,
                        'location'          => 'Entrepôt Principal',
                        'last_restocked'    => now(),
                        'created_at'        => now(),
                    ]);
                }

                ProductStockMovement::create([
                    'product_id'       => $productId,
                    'famille_id'       => $familleId,
                    'famille_name'     => $famille?->famille_name ?? '',
                    'movement_type'    => 'cancellation_reversal',
                    'quantity'         => $userQty,
                    'previous_stock'   => $prev,
                    'new_stock'        => $newQty,
                    'reference_type'   => 'production_order',
                    'reference_number' => $order->order_number,
                    'movement_date'    => now(),
                    'performed_by'     => auth()->id(),
                    'notes'            => 'Restauration source planifiée - Annulation #' . $order->order_number,
                ]);

                $restoredPerMaterial[$itemKey] = [
                    'name'      => ($product?->product_name ?? 'Produit') .
                                   ($famille ? ' / ' . $famille->famille_name : ''),
                    'qty'       => $userQty,
                    'unit'      => 'unités',
                    'direction' => 'restore',
                ];
            }

            // ── 3. Remove produced goods from stock → SUBTRACT ───────────────
            $outputMovements = ProductStockMovement::where('movement_type', 'production_output')
                ->where('reference_number', $order->order_number)
                ->where('quantity', '>', 0)
                ->with(['product', 'famille'])
                ->get();

            $outByKey = [];
            foreach ($outputMovements as $mv) {
                $k = $mv->product_id . '_' . ($mv->famille_id ?? 0);
                if (!isset($outByKey[$k])) {
                    $outByKey[$k] = ['total' => 0, 'mv' => $mv];
                }
                $outByKey[$k]['total'] += abs($mv->quantity);
            }

            foreach ($outByKey as $k => $data) {
                $itemKey = 'out_' . $k;
                if (!array_key_exists($itemKey, $stockItemsMap)) {
                    continue;
                }

                $mv      = $data['mv'];
                $userQty = min($stockItemsMap[$itemKey], $data['total']);
                if ($userQty <= 0) continue;

                $familleStock = ProductFamilleStock::where('product_id', $mv->product_id)
                    ->where('famille_id', $mv->famille_id)
                    ->first();

                if ($familleStock) {
                    $prev   = (float) $familleStock->current_quantity;
                    $newQty = $prev - $userQty;
                    $familleStock->current_quantity   = $newQty;
                    $familleStock->available_quantity = $newQty - $familleStock->reserved_quantity;
                    $familleStock->save();

                    ProductStockMovement::create([
                        'product_id'       => $mv->product_id,
                        'famille_id'       => $mv->famille_id,
                        'famille_name'     => $mv->famille_name,
                        'movement_type'    => 'cancellation_output_reversal',
                        'quantity'         => -$userQty,
                        'previous_stock'   => $prev,
                        'new_stock'        => $newQty,
                        'reference_type'   => 'production_order',
                        'reference_number' => $order->order_number,
                        'movement_date'    => now(),
                        'performed_by'     => auth()->id(),
                        'notes'            => 'Retrait produit fabriqué - Annulation #' . $order->order_number,
                    ]);

                    $restoredPerMaterial[$itemKey] = [
                        'name'      => ($mv->product->product_name ?? 'Produit') . ' / ' .
                                       ($mv->famille->famille_name ?? $mv->famille_name ?? ''),
                        'qty'       => $userQty,
                        'unit'      => 'unités',
                        'direction' => 'remove',
                    ];
                }
            }

            // ── Update order status ───────────────────────────────────────────
            $reasonLabels = [
                'stock_insufficient' => 'Stock insuffisant',
                'customer_cancelled' => 'Commande client annulée',
                'technical_issue'    => 'Problème technique',
                'schedule_conflict'  => 'Conflit d\'horaire',
                'quality_concerns'   => 'Problèmes de qualité',
                'other'              => 'Autre',
            ];
            $reasonCode = request('reason', 'other');
            $reasonLabel = $reasonLabels[$reasonCode] ?? $reasonCode;
            $additionalNotes = request('additional_notes');
            $cancellationReason = $reasonLabel . ($additionalNotes ? ' — ' . $additionalNotes : '');

            $order->update([
                'status'              => 'cancelled',
                'cancelled_by'        => auth()->id(),
                'cancelled_at'        => now(),
                'cancellation_reason' => $cancellationReason,
            ]);

            DB::commit();

            $adjustedCount = count($restoredPerMaterial);
            $message = 'Ordre annulé avec succès!';
            if ($adjustedCount > 0) {
                $message .= ' Stock ajusté pour ' . $adjustedCount . ' article(s).';
            }

            return response()->json([
                'success'  => true,
                'message'  => $message,
                'restored' => array_values($restoredPerMaterial),
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
            $order = ProductionOrder::findOrFail($id);

            if (!in_array($order->status, ['pending', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un ordre ' . $order->status
                ], 400);
            }

            // Delete related records
            ProductionConsumption::where('production_order_id', $id)->delete();
            ProductionOutput::where('production_order_id', $id)->delete();

            $order->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ordre supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }


private function calculateFIFOUnitCost($materialId, $quantityNeeded)
    {
        // Get stock details in FIFO order (oldest first)
        $stockDetails = StockMovementDetail::where('material_id', $materialId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('stock_movement_id', 'asc')
            ->get();

        $remainingToConsume = $quantityNeeded;
        $totalCost = 0;

        foreach ($stockDetails as $detail) {
            if ($remainingToConsume <= 0) break;

            $availableQuantity = $detail->remaining_quantity;
            $quantityToTake = min($availableQuantity, $remainingToConsume);

            $totalCost += $quantityToTake * $detail->unit_price;
            $remainingToConsume -= $quantityToTake;
        }

        if ($remainingToConsume > 0) {
            // If we still need more but no stock, use average cost
            $averageCost = StockMovementDetail::where('material_id', $materialId)
                ->where('remaining_quantity', '>', 0)
                ->avg('unit_price');

            $totalCost += $remainingToConsume * ($averageCost ?? 0);
        }

        return $quantityNeeded > 0 ? ($totalCost / $quantityNeeded) : 0;
    }

    public function getBom(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'quantity' => 'required|numeric|min:0.01',
            'material_source' => 'required|in:bom_only,chutes_only,both',
            'chutes_volume' => 'nullable|numeric|min:0',
            'bom_percentage' => 'nullable|integer|min:0|max:100'
        ]);

        try {
            $product = Product::findOrFail($request->product_id);

            if (!$product->isProductionProduct()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce produit n\'est pas configuré pour la production'
                ], 400);
            }

            $bom = BillOfMaterial::where('product_id', $request->product_id)
                ->with('rawMaterial')
                ->get();

            $bomItems = [];
            $totalCost = 0;

            $materialSource = $request->material_source;
            $chutesVolume = $request->chutes_volume ? (float)$request->chutes_volume : 0;

            if ($materialSource === 'bom_only' || $materialSource === 'both') {
                foreach ($bom as $item) {
                    $requiredQuantity = $item->quantity_required * $request->quantity;
                    $availableStock = $item->rawMaterial->current_stock ?? 0;
                    $unitCost = $this->calculateFIFOUnitCost($item->material_id, $requiredQuantity);
                    if ($unitCost <= 0) {
                        $unitCost = $item->rawMaterial->average_unit_cost ?? 0;
                    }
                    $itemCost = $requiredQuantity * $unitCost;
                    $totalCost += $itemCost;

                    $bomItems[] = [
                        'material_id' => $item->material_id,
                        'quantity_required' => $item->quantity_required,
                        'raw_material' => [
                            'material_id' => $item->rawMaterial->material_id,
                            'material_name' => $item->rawMaterial->material_name,
                            'material_code' => $item->rawMaterial->material_code,
                            'unit_of_measure' => $item->rawMaterial->unit_of_measure,
                            'current_stock' => $availableStock,
                            'unit_cost' => $unitCost,
                        ]
                    ];
                }
            }

            $chutesMaterial = null;
            if ($materialSource === 'chutes_only' || $materialSource === 'both') {
                $chutesMaterial = RawMaterial::where('material_code', 'CHUTE-PRODUCTION')->first();

                if (!$chutesMaterial) {
                    $chutesMaterial = RawMaterial::create([
                        'material_code' => 'CHUTE-PRODUCTION',
                        'material_name' => 'Chutes de Production',
                        'unit_of_measure' => 'm³',
                        'min_stock_level' => 0,
                        'max_stock_level' => 10000,
                        'is_active' => true,
                        'notes' => 'Chutes de production recyclées',
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'bom_items' => $bomItems,
                'chutes_material' => $chutesMaterial ? [
                    'material_id' => $chutesMaterial->material_id,
                    'material_name' => $chutesMaterial->material_name,
                    'material_code' => $chutesMaterial->material_code,
                    'unit_of_measure' => 'm³',
                    'current_stock' => $chutesMaterial->current_stock ?? 0,
                    'chutes_volume' => $chutesVolume,
                ] : null,
                'total_cost' => number_format($totalCost, 2, ',', '.'),
                'raw_total_cost' => $totalCost,
                'quantity' => $request->quantity,
                'material_source' => $materialSource
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getFamilles(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,product_id'
        ]);

        try {
            $product = Product::findOrFail($request->product_id);

            if (!$product->has_familles) {
                return response()->json([
                    'success' => true,
                    'has_familles' => false,
                    'html' => '<div class="alert alert-info">Ce produit n\'a pas de familles.</div>'
                ]);
            }

            $familles = Famille::whereHas('products', function($query) use ($product) {
                    $query->where('products.product_id', $product->product_id);
                })
                ->where('is_active', true)
                ->orderBy('famille_name')
                ->get();

            if ($familles->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'has_familles' => true,
                    'html' => '<div class="alert alert-warning">Aucune famille active trouvée pour ce produit.</div>'
                ]);
            }

            $html = '<div class="form-group">';

            // Determine label based on request
            $familleType = $request->get('famille_type', 'final');
            $label = $familleType === 'source' ?
                'Sélectionner la famille source *' :
                'Sélectionner la famille de destination *';

            $selectName = $familleType === 'source' ?
                'source_famille_id' :
                'famille_id';

            $selectId = $familleType === 'source' ?
                'source_famille_id' :
                'famille_id';

            $html .= '<label for="' . $selectId . '" class="form-label">' . $label . '</label>';
            $html .= '<select class="form-control select2" id="' . $selectId . '" name="' . $selectName . '" required>';
            $html .= '<option value="">Sélectionner une famille</option>';

            foreach ($familles as $famille) {
                // Get stock for this product in this famille
                $stock = ProductFamilleStock::where('product_id', $product->product_id)
                    ->where('famille_id', $famille->famille_id)
                    ->first();

                $availableStock = $stock ?
                    ($stock->current_quantity - $stock->reserved_quantity) : 0;

                $stockInfo = ' (Stock: ' . $availableStock . ' ' . $product->unit_of_measure . ')';

                $html .= '<option value="' . $famille->famille_id . '" data-available="' . $availableStock . '">';
                $html .= $famille->famille_name . ' (' . $famille->famille_code . ')' . $stockInfo;
                $html .= '</option>';
            }

            $html .= '</select>';
            $html .= '<small class="form-text text-muted">Sélectionnez la famille</small>';
            $html .= '</div>';

            return response()->json([
                'success' => true,
                'has_familles' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrderBom($id)
    {
        try {
            $order = ProductionOrder::with(['product.billOfMaterials.rawMaterial'])->findOrFail($id);

            if (!$order->product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit non trouvé'
                ], 404);
            }

            $bom = $order->product->billOfMaterials->map(function($item) use ($order) {
                $plannedQuantity = $item->quantity_required * $order->quantity_to_produce;

                return [
                    'material_id' => $item->material_id,
                    'quantity_required' => $item->quantity_required,
                    'planned_quantity' => $plannedQuantity,
                    'raw_material' => $item->rawMaterial ? [
                        'material_id' => $item->rawMaterial->material_id,
                        'material_name' => $item->rawMaterial->material_name,
                        'material_code' => $item->rawMaterial->material_code,
                        'unit_of_measure' => $item->rawMaterial->unit_of_measure,
                        'current_stock' => $item->rawMaterial->current_stock,
                        'unit_cost' => $item->rawMaterial->unit_cost,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'order_id' => $order->order_id,
                'order_number' => $order->order_number,
                'product_id' => $order->product_id,
                'product_name' => $order->product->product_name,
                'quantity_to_produce' => $order->quantity_to_produce,
                'bom' => $bom,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    // DASHBOARD AND EXPORT METHODS

    public function dashboardStatistics()
    {
        $today = now()->format('Y-m-d');
        $monthStart = now()->startOfMonth()->format('Y-m-d');

        // Calculate orders needing waste declaration
        $needsWasteDeclaration = ProductionOrder::where('status', 'in_progress')
            ->with(['outputs'])
            ->get()
            ->filter(function($order) {
                $remaining = $this->calculateRemainingQuantity($order);
                return $remaining <= 0 && $order->wastes->count() == 0;
            })
            ->count();

        $statistics = [
            'today' => [
                'count' => ProductionOrder::whereDate('created_at', $today)->count(),
                'completed' => ProductionOrder::whereDate('actual_completion_date', $today)
                    ->where('status', 'completed')->count(),
            ],
            'this_month' => [
                'count' => ProductionOrder::whereBetween('created_at', [$monthStart, $today])->count(),
                'completed' => ProductionOrder::whereBetween('actual_completion_date', [$monthStart, $today])
                    ->where('status', 'completed')->count(),
            ],
            'by_status' => ProductionOrder::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray(),
            'by_priority' => ProductionOrder::select('priority', DB::raw('COUNT(*) as count'))
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->groupBy('priority')
                ->get()
                ->pluck('count', 'priority')
                ->toArray(),
            'needs_waste_declaration' => $needsWasteDeclaration,
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    public function export(Request $request)
    {
        $orders = ProductionOrder::with(['product', 'creator'])
            ->when($request->filled('status'), function($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('production_type'), function($q) use ($request) {
                $q->where('production_type', $request->production_type);
            })
            ->when($request->filled('date_from'), function($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->get();

        // Return data for export (you can implement CSV/Excel export here)
        return response()->json([
            'success' => true,
            'data' => $orders,
            'total' => $orders->count()
        ]);
    }
}
