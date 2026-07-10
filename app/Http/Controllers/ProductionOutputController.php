<?php

namespace App\Http\Controllers;

use App\Models\BillOfMaterial;
use App\Models\Famille;
use App\Models\ProductionOrder;
use App\Models\ProductionOutput;
use App\Models\ProductionWaste;
use App\Models\Product;
use App\Models\ProductConversion;
use App\Models\ProductFamilleStock;
use App\Models\ProductionConsumption;
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

class ProductionOutputController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_production_output')->only(['index', 'show', 'getOrderOutputs', 'getOrderWastes', 'getOrderVolume', 'getStatistics']);
        $this->middleware('can:create_production_output')->only(['create', 'createType2', 'createType3', 'createType4', 'createType5', 'store', 'storeType2', 'storeType3', 'storeType4', 'storeType5']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Start with production orders that have outputs
            $query = ProductionOrder::with([
                'outputs' => function($q) {
                    $q->orderBy('production_date', 'desc');
                },
                'outputs.product',
                'outputs.famille',
                'outputs.approver',
                'product',
                'famille',
                'wastes.rawMaterial'
            ])
            ->whereHas('outputs') // Only show orders that have outputs
            ->select('production_orders.*');

            // Apply filters
            if ($request->filled('order_number')) {
                $query->where('order_number', 'like', '%' . $request->order_number . '%');
            }

            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->filled('famille_id')) {
                $query->where('famille_id', $request->famille_id);
            }

            if ($request->filled('date_range')) {
                $dateRange = str_replace('/', '-', $request->date_range);
                $dates = explode(' - ', $dateRange);

                if (count($dates) == 2) {
                    $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                    $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
                    $query->whereHas('outputs', function($q) use ($startDate, $endDate) {
                        $q->whereBetween('production_date', [$startDate, $endDate]);
                    });
                } elseif (count($dates) == 1) {
                    $date = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                    $query->whereHas('outputs', function($q) use ($date) {
                        $q->whereDate('production_date', $date);
                    });
                }
            }

            if ($request->filled('output_type')) {
                $query->where('production_type', $request->output_type);
            }

            // Handle ordering
            if ($request->has('order') && count($request->order) > 0) {
                $orderColumnIndex = $request->order[0]['column'];
                $orderDirection = $request->order[0]['dir'];
                $orderColumnName = $request->columns[$orderColumnIndex]['data'];

                $columnMappings = [
                    'order_number' => 'order_number',
                    'product.product_name' => 'products.product_name',
                    'production_date' => 'production_date',
                    'quantity_produced' => 'quantity_produced',
                    'created_at' => 'production_orders.created_at',
                ];

                if (isset($columnMappings[$orderColumnName])) {
                    if ($orderColumnName === 'product.product_name') {
                        $query->leftJoin('products', 'production_orders.product_id', '=', 'products.product_id')
                            ->orderBy($columnMappings[$orderColumnName], $orderDirection);
                    } else {
                        $query->orderBy($columnMappings[$orderColumnName], $orderDirection);
                    }
                } else {
                    $query->orderBy('production_orders.created_at', 'desc');
                }
            } else {
                $query->orderBy('production_orders.created_at', 'desc');
            }

            $totalRecords = $query->count();

            if ($request->has('length') && $request->length != -1) {
                $query->skip($request->start)->take($request->length);
            }

            $orders = $query->get();

            return DataTables::of($orders)
                ->setTotalRecords($totalRecords)
                ->setFilteredRecords($totalRecords)
                ->addIndexColumn()
                ->addColumn('action', function($order){
                    $output = $order->outputs->first();
                    return view('pages.production-output.components.actions', ['output' => $output, 'order' => $order])->render();
                })
                ->addColumn('order_info', function($order){
                    $statusBadge = match($order->status) {
                        'completed' => '<span class="badge bg-success">Terminé</span>',
                        'in_progress' => '<span class="badge bg-warning">En cours</span>',
                        'pending' => '<span class="badge bg-secondary">En attente</span>',
                        'cancelled' => '<span class="badge bg-danger">Annulé</span>',
                        default => '<span class="badge bg-secondary">' . $order->status . '</span>'
                    };

                    $typeBadge = match($order->production_type) {
                        'type1' => '<span class="badge bg-primary">Production</span>',
                        'type2' => '<span class="badge bg-info">Découpage</span>',
                        'type3' => '<span class="badge bg-success">Conversion</span>',
                        default => '<span class="badge bg-secondary">' . $order->production_type . '</span>'
                    };

                    return '<div>
                        <div class="fw-medium">
                            <a href="' . route('production-orders.show', $order->order_id) . '" class="text-primary">
                                ' . $order->order_number . '
                            </a>
                        </div>
                        <div class="small text-muted">
                            ' . $statusBadge . ' ' . $typeBadge . '
                        </div>
                    </div>';
                })
                ->addColumn('product_info', function($order){
                    if ($order->product) {
                        return '<div>
                            <div class="fw-medium">' . $order->product->product_name . '</div>
                            <div class="small text-muted">' . $order->product->product_code . '</div>
                        </div>';
                    }
                    return 'N/A';
                })
                ->addColumn('production_summary', function($order){
                    $outputs = $order->outputs ?? collect();

                    if ($outputs->isEmpty()) {
                        return '<span class="text-muted">Aucune sortie</span>';
                    }

                    $totalProduced = $outputs->sum('quantity_produced');
                    $totalDefective = $outputs->sum('quantity_defective');
                    $totalGood = $totalProduced - $totalDefective;
                    $targetQuantity = $order->quantity_to_produce ?? 0;
                    $remaining = max(0, $targetQuantity - $totalGood);

                    $outputDates = $outputs->map(function($output) {
                        return $output->production_date ? $output->production_date->format('d/m/Y') : '';
                    })->filter()->unique()->implode(', ');

                    return '<div>
                        <div class="d-flex justify-content-between mb-1">
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary view-outputs-btn"
                                        data-order-id="' . $order->order_id . '"
                                        title="Voir les sorties">
                                    <i class="fas fa-boxes"></i>
                                </button>
                            </div>
                            <div class="text-end">
                                <strong>' . $totalProduced . '</strong> unités
                            </div>
                        </div>
                        <div class="small text-muted">
                            <div>Bonnes: ' . $totalGood . ' | Défauts: ' . $totalDefective . '</div>
                            <div>Reste: ' . $remaining . ' | Dates: ' . ($outputDates ?: 'N/A') . '</div>
                        </div>
                    </div>';
                })
                ->addColumn('volume_info', function($order){
                    $outputs = $order->outputs ?? collect();

                    if ($outputs->isEmpty()) {
                        return '<span class="text-muted">N/A</span>';
                    }

                        $totalVolume = $outputs->sum('total_volume_m3');

                    return '<div class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-info view-volume-btn"
                                data-order-id="' . $order->order_id . '"
                                title="Voir le volume">
                            ' . number_format($totalVolume, 4) . ' m³
                        </button>
                    </div>';
                })
                ->addColumn('waste_info', function($order){
                    if (!$order->wastes || $order->wastes->isEmpty()) {
                        return '<span class="text-muted">Aucun déchet</span>';
                    }

                    $recyclableCount = $order->wastes->whereIn('waste_type', ['recyclable', 'waste'])->count();
                    $wasteCount = $order->wastes->where('waste_type', 'waste')->count();

                    $recyclableVolume = $order->wastes->whereIn('waste_type', ['recyclable', 'waste'])->sum('volume_m3');
                    $wasteVolume = $order->wastes->where('waste_type', 'waste')->sum('volume_m3');

                    return '<div class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-warning view-wastes-btn"
                                data-order-id="' . $order->order_id . '"
                                title="Voir les déchets">
                            <i class="fas fa-trash"></i> ' . $recyclableCount . ' ♻️ | ' . $wasteCount . ' 🗑️
                        </button>
                        <div class="small text-muted">
                            ' . number_format($recyclableVolume + $wasteVolume, 4) . ' m³
                        </div>
                    </div>';
                })
                ->addColumn('created_info', function($order){
                    if ($order->created_at) {
                        return '<div>
                            <div>' . $order->created_at->format('d/m/Y') . '</div>
                            <div class="small text-muted">' . $order->created_at->format('H:i') . '</div>
                        </div>';
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('output_dates', function($order){
                    $outputs = $order->outputs ?? collect();

                    if ($outputs->isEmpty()) {
                        return '<span class="text-muted">N/A</span>';
                    }

                    $dates = $outputs->map(function($output) {
                        return $output->production_date ? $output->production_date->format('d/m/Y') : '';
                    })->filter()->unique();

                    return '<div class="small text-center">' . $dates->implode('<br>') . '</div>';
                })
                ->rawColumns([
                    'action',
                    'order_info',
                    'product_info',
                    'production_summary',
                    'volume_info',
                    'waste_info',
                    'created_info',
                    'output_dates'
                ])
                ->with(['draw' => $request->draw])
                ->make(true);
        }

        $products = Product::where('is_active', true)->get();
        $familles = Famille::where('is_active', true)->get();

        return view('pages.production-output.index', compact('products', 'familles'));
    }

    public function getOrderOutputs($orderId)
    {
        try {
            $order = ProductionOrder::with(['outputs', 'outputs.product', 'outputs.famille'])->findOrFail($orderId);

            $outputs = $order->outputs->map(function($output) {
                return [
                    'date' => $output->production_date ? $output->production_date->format('d/m/Y') : 'N/A',
                    'quantity' => $output->quantity_produced,
                    'defective' => $output->quantity_defective,
                    'good' => $output->quantity_produced - $output->quantity_defective,
                    'volume' => number_format($output->total_volume_m3 ?? 0, 4),
                    'type' => $output->output_type,
                    'product_name' => $output->product->product_name ?? 'N/A',
                    'famille_name' => $output->famille->famille_name ?? 'N/A'
                ];
            });

            return response()->json([
                'success' => true,
                'order_number' => $order->order_number,
                'outputs' => $outputs,
                'total_outputs' => $outputs->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrderWastes($orderId)
    {
        try {
            $order = ProductionOrder::with(['wastes.rawMaterial'])->findOrFail($orderId);

            $wastes = $order->wastes->map(function($waste) {
                return [
                    'type' => $waste->waste_type,
                    'type_label' => $waste->waste_type === 'recyclable' ? 'Recyclable' :
                                ($waste->waste_type === 'waste' ? 'Waste' : 'Déchet'),
                    'source' => $waste->waste_source,
                    'category' => $waste->waste_category,
                    'volume' => number_format($waste->volume_m3 ?? 0, 4),
                    'dimensions' => $waste->height && $waste->width && $waste->depth ?
                                $waste->height . 'm × ' . $waste->width . 'm × ' . $waste->depth . 'm' : 'N/A',
                    'notes' => $waste->notes,
                    'material' => $waste->rawMaterial ? $waste->rawMaterial->material_name : null,
                    'created' => $waste->created_at ? $waste->created_at->format('d/m/Y H:i') : 'N/A'
                ];
            });

            $recyclableVolume = $order->wastes->whereIn('waste_type', ['recyclable', 'waste'])->sum('volume_m3');
            $wasteVolume = $order->wastes->where('waste_type', 'waste')->sum('volume_m3');

            return response()->json([
                'success' => true,
                'order_number' => $order->order_number,
                'wastes' => $wastes,
                'recyclable_volume' => number_format($recyclableVolume, 4),
                'waste_volume' => number_format($wasteVolume, 4),
                'total_volume' => number_format($recyclableVolume + $wasteVolume, 4)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrderVolume($orderId)
    {
        try {
            $order = ProductionOrder::with(['outputs'])->findOrFail($orderId);

            $outputs = $order->outputs->map(function($output) {
                return [
                    'date' => $output->production_date ? $output->production_date->format('d/m/Y') : 'N/A',
                    'quantity' => $output->quantity_produced,
                    'volume' => number_format($output->total_volume_m3 ?? 0, 4),
                    'waste_volume' => number_format($output->waste_volume_m3 ?? 0, 4),
                    'good_volume' => number_format(($output->total_volume_m3 ?? 0) - ($output->waste_volume_m3 ?? 0), 4)
                ];
            });

            $totalVolume = $order->outputs->sum('total_volume_m3');
            $totalWasteVolume = $order->outputs->sum('waste_volume_m3');
            $totalGoodVolume = $totalVolume - $totalWasteVolume;

            return response()->json([
                'success' => true,
                'order_number' => $order->order_number,
                'outputs' => $outputs,
                'total_volume' => number_format($totalVolume, 4),
                'total_waste_volume' => number_format($totalWasteVolume, 4),
                'total_good_volume' => number_format($totalGoodVolume, 4)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatistics(Request $request)
    {
        try {
            // Get statistics based on orders with outputs
            $todayOutputOrders = ProductionOrder::whereHas('outputs', function($q) {
                    $q->whereDate('production_date', Carbon::today());
                })
                ->count();

            $todayQuantity = ProductionOutput::whereDate('production_date', Carbon::today())
                ->sum('quantity_produced');

            $todayDefective = ProductionOutput::whereDate('production_date', Carbon::today())
                ->sum('quantity_defective');

            $todayDefectRate = $todayQuantity > 0 ? ($todayDefective / $todayQuantity) * 100 : 0;

            // This week statistics
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();

            $weekOutputOrders = ProductionOrder::whereHas('outputs', function($q) use ($startOfWeek, $endOfWeek) {
                    $q->whereBetween('production_date', [$startOfWeek, $endOfWeek]);
                })
                ->count();

            $weekQuantity = ProductionOutput::whereBetween('production_date', [$startOfWeek, $endOfWeek])
                ->sum('quantity_produced');

            // Total statistics
            $totalOrdersWithOutputs = ProductionOrder::whereHas('outputs')->count();
            $totalQuantity = ProductionOutput::sum('quantity_produced');
            $totalDefective = ProductionOutput::sum('quantity_defective');
            $totalDefectRate = $totalQuantity > 0 ? ($totalDefective / $totalQuantity) * 100 : 0;

            // Volume statistics
            $totalVolume = ProductionOutput::sum('total_volume_m3');
            $totalWasteVolume = ProductionOutput::sum('waste_volume_m3');
            $goodVolume = max(0, $totalVolume - $totalWasteVolume);

            // Waste statistics
            $totalRecyclableVolume = ProductionWaste::whereIn('waste_type', ['recyclable', 'waste'])
                ->sum('volume_m3');
            $totalPureWasteVolume = ProductionWaste::where('waste_type', 'waste')
                ->sum('volume_m3');

            return response()->json([
                'success' => true,
                'data' => [
                    'today' => [
                        'orders' => $todayOutputOrders,
                        'quantity' => $todayQuantity,
                        'defect_rate' => $todayDefectRate
                    ],
                    'week' => [
                        'orders' => $weekOutputOrders,
                        'quantity' => $weekQuantity
                    ],
                    'total' => [
                        'orders' => $totalOrdersWithOutputs,
                        'quantity' => $totalQuantity,
                        'defect_rate' => $totalDefectRate,
                        'volume' => $totalVolume,
                        'good_volume' => $goodVolume,
                        'waste_volume' => $totalWasteVolume,
                        'recyclable_volume' => $totalRecyclableVolume,
                        'pure_waste_volume' => $totalPureWasteVolume
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        $order_id = $request->get('order_id');
        $productionOrder = null;
        $product = null;
        $availableFamilles = collect();
        $productionOrders = collect();
        $existingWastes = collect();
        $productionOrders = ProductionOrder::where('status', 'in_progress')
            ->with(['product', 'outputs', 'famille', 'sourceProduct'])
            ->orderBy('created_at', 'desc')
            ->get();


        $wasteMaterials = RawMaterial::where('is_active', true)
            ->orderBy('material_name')
            ->get();

        if ($order_id) {
            $productionOrder = ProductionOrder::with([
                'product',
                'outputs',
                'famille',
                'sourceProduct',
                'wastes.rawMaterial'
            ])
                ->where('status', 'in_progress')
                ->find($order_id);

            if ($productionOrder) {
                if ($productionOrder->production_type === 'type2') {
                    return redirect()->route('production-output.create-type2', $order_id);
                }

                if ($productionOrder->production_type === 'type3') {
                    return redirect()->route('production-output.create-type3', $order_id);
                }

                if ($productionOrder->production_type === 'type4') {
                    return redirect()->route('production-output.create-type4', $order_id);
                }

                if ($productionOrder->production_type === 'type5') {
                    return redirect()->route('production-output.create-type5', $order_id);
                }

                $product = $productionOrder->product;

                if ($product->has_familles) {
                    $availableFamilles = Famille::whereHas('products', function($query) use ($product) {
                            $query->where('products.product_id', $product->product_id);
                        })
                        ->where('is_active', true)
                        ->orderBy('famille_name')
                        ->get();
                }

                $remaining = 0;

                if ($productionOrder->production_type === 'type1') {
                    $targetFamilleId = $productionOrder->famille_id;
                    $totalTargetProduced = $productionOrder->outputs
                        ->where('famille_id', $targetFamilleId)
                        ->sum('quantity_produced');
                    $remaining = $productionOrder->quantity_to_produce - $totalTargetProduced;
                } else if ($productionOrder->production_type === 'type2') {
                    $totalConsumed = $productionOrder->outputs
                        ->where('output_type', 'type2')
                        ->sum('quantity_consumed');
                    $remaining = $productionOrder->required_quantity - $totalConsumed;
                } else if ($productionOrder->production_type === 'type3') {
                    $totalConsumed = $productionOrder->outputs
                        ->where('output_type', 'type3')
                        ->sum('quantity_consumed');
                    $remaining = $productionOrder->required_quantity - $totalConsumed;

                } else if ($productionOrder->production_type === 'type4') {
                    $totalConsumed = $productionOrder->outputs
                        ->where('output_type', 'type4')
                        ->sum('quantity_consumed');
                    $remaining = $productionOrder->required_quantity - $totalConsumed;
                }

                $existingWastes = $productionOrder->wastes->map(function($waste) {
                    return [
                        'waste_id' => $waste->waste_id,
                        'waste_type' => $waste->waste_type,
                        'waste_source' => $waste->waste_source,
                        'waste_category' => $waste->waste_category,
                        'height' => $waste->height,
                        'width' => $waste->width,
                        'depth' => $waste->depth,
                        'volume_m3' => $waste->volume_m3,
                        'notes' => $waste->notes,
                        'material_name' => $waste->rawMaterial ? $waste->rawMaterial->material_name : null,
                        'created_at' => $waste->created_at,
                    ];
                });
            }
        }

        return view('pages.production-output.create', compact(
            'productionOrders',
            'productionOrder',
            'product',
            'availableFamilles',
            'wasteMaterials',
            'existingWastes'
        ));
    }

    public function createType2($order_id)
    {
        $order = ProductionOrder::with(['sourceProduct', 'sourceFamille', 'famille'])
            ->where('status', 'in_progress')
            ->where('production_type', 'type2')
            ->findOrFail($order_id);

        $products = $order->getType2ProductionSummary();

        $allCompleted = true;
        foreach ($products as $product) {
            if ($product['remaining_quantity'] > 0) {
                $allCompleted = false;
                break;
            }
        }

        if ($allCompleted) {
            return redirect()->route('production-orders.show', $order_id)
                ->with('success', 'Tous les produits de découpage de cet ordre sont déjà terminés.');
        }

        // dd($order);

        return view('pages.production-output.type2-create', compact('order', 'products'));
    }

    public function createType3($order_id)
    {
        try {

            $order = ProductionOrder::with(['sourceProduct', 'sourceFamille', 'famille'])
                ->where('status', 'in_progress')
                ->where('production_type', 'type3')
                ->find($order_id);

            if (!$order) {
                return redirect()->route('production-output.index')
                    ->with('error', 'Ordre non trouvé ou non en cours.');
            }

            $products = $order->getType3ProductionSummary();

            $allCompleted = true;
            foreach ($products as $product) {
                if (($product['remaining_quantity'] ?? 0) > 0) {
                    $allCompleted = false;
                    break;
                }
            }

            if ($allCompleted) {
                Log::info('All products completed for order', ['order_id' => $order_id]);
                return redirect()->route('production-orders.show', $order_id)
                    ->with('success', 'Tous les produits de cet ordre Type 3 sont déjà terminés.');
            }

            // Resolve source product names from additional_data
            $sourceProducts = [];
            $additionalData = json_decode($order->additional_data, true);
            if (!empty($additionalData['source_products'])) {
                foreach ($additionalData['source_products'] as $sp) {
                    // New orders have product_name stored directly; old orders need DB lookup
                    $name = $sp['product_name'] ?? null;
                    if (!$name) {
                        $product = Product::find($sp['product_id'] ?? null);
                        $name = $product ? $product->product_name : 'Inconnu';
                    }
                    $sourceProducts[] = [
                        'product_name' => $name,
                        'quantity' => $sp['quantity'] ?? 0,
                    ];
                }
            }
            // Fallback to single source product if no multiple sources in additional_data
            if (empty($sourceProducts) && $order->sourceProduct) {
                $sourceProducts[] = [
                    'product_name' => $order->sourceProduct->product_name,
                    'quantity' => $order->required_quantity,
                ];
            }

            Log::info('Rendering type3 create view', [
                'order_id' => $order_id,
                'products_count' => count($products),
                'source_products_count' => count($sourceProducts),
                'additional_data_source_products' => $additionalData['source_products'] ?? 'missing',
                'source_products' => $sourceProducts,
            ]);

            return view('pages.production-output.type3-create', compact('order', 'products', 'sourceProducts'));

        } catch (\Exception $e) {
            Log::error('Error in createType3', [
                'order_id' => $order_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('production-output.index')
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    /**
     * Create view for Type 5 production output (Chutes -> Produits Finis)
     */
    public function createType5($order_id)
    {
        try {
            $order = ProductionOrder::with(['famille'])
                ->where('status', 'in_progress')
                ->where('production_type', 'type5')
                ->find($order_id);

            if (!$order) {
                return redirect()->route('production-output.index')
                    ->with('error', 'Ordre non trouvé ou non en cours.');
            }

            $products = $order->getType5ProductionSummary();

            $allCompleted = true;
            foreach ($products as $product) {
                if (($product['remaining_quantity'] ?? 0) > 0) {
                    $allCompleted = false;
                    break;
                }
            }

            if ($allCompleted) {
                return redirect()->route('production-orders.show', $order_id)
                    ->with('success', 'Tous les produits de cet ordre Chutes → Produits Finis sont déjà terminés.');
            }

            return view('pages.production-output.type5-create', compact('order', 'products'));

        } catch (\Exception $e) {
            Log::error('Error in createType5', [
                'order_id' => $order_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('production-output.index')
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    /**
     * Create view for Type 4 production output
     */
    public function createType4($order_id)
    {
        try {
            $order = ProductionOrder::with(['sourceProduct', 'sourceFamille', 'famille'])
                ->where('status', 'in_progress')
                ->where('production_type', 'type4')
                ->find($order_id);

            if (!$order) {
                return redirect()->route('production-output.index')
                    ->with('error', 'Ordre non trouvé ou non en cours.');
            }

            $products = $order->getType4ProductionSummary();

            $allCompleted = true;
            foreach ($products as $product) {
                if (($product['remaining_quantity'] ?? 0) > 0) {
                    $allCompleted = false;
                    break;
                }
            }

            if ($allCompleted) {
                Log::info('All products completed for order', ['order_id' => $order_id]);
                return redirect()->route('production-orders.show', $order_id)
                    ->with('success', 'Tous les produits de cet ordre Type 4 sont déjà terminés.');
            }

            Log::info('Rendering type4 create view', [
                'order_id' => $order_id,
                'products_count' => count($products)
            ]);

            return view('pages.production-output.type4-create', compact('order', 'products'));

        } catch (\Exception $e) {
            Log::error('Error in createType4', [
                'order_id' => $order_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('production-output.index')
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function getOrderProduct($orderId, $productId)
    {
        try {
            $order = ProductionOrder::where('production_type', 'type3')->findOrFail($orderId);

            $productSummary = $order->getType3ProductSummary($productId);

            if (!$productSummary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit non trouvé dans cet ordre'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'product' => $productSummary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrderConsumed($orderId)
    {
        try {
            $order = ProductionOrder::findOrFail($orderId);

            $totalConsumed = ProductionOutput::where('production_order_id', $orderId)
                ->sum('quantity_consumed');

            return response()->json([
                'success' => true,
                'total_consumed' => $totalConsumed,
                'required_quantity' => $order->required_quantity,
                'remaining' => max(0, $order->required_quantity - $totalConsumed)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product details for Type 2 order
     */
    public function getOrderProductType2($orderId, $productId)
    {
        try {
            $order = ProductionOrder::where('production_type', 'type2')->findOrFail($orderId);

            $productSummary = $order->getType2ProductSummary($productId);

            if (!$productSummary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit non trouvé dans cet ordre'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'product' => $productSummary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get total sous-blocs consumed for Type 3 order
     */
    public function getOrderConsumedType3($order_id)
    {
        try {

            $order = ProductionOrder::where('status', 'in_progress')
                ->where('production_type', 'type3')
                ->find($order_id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ordre non trouvé ou pas en cours'
                ], 404);
            }

            $totalConsumed = ProductionOutput::where('production_order_id', $order_id)
                ->where('output_type', 'type3')
                ->sum('quantity_consumed');

            return response()->json([
                'success' => true,
                'total_consumed' => $totalConsumed,
                'required_quantity' => $order->required_quantity,
                'remaining' => max(0, $order->required_quantity - $totalConsumed)
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getOrderConsumedType3', [
                'order_id' => $order_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get total consumed for Type 2 order
     */
    public function getOrderConsumedType2($orderId)
    {
        try {
            $order = ProductionOrder::where('production_type', 'type2')->findOrFail($orderId);

            $totalConsumed = ProductionOutput::where('production_order_id', $orderId)
                ->where('output_type', 'type2')
                ->sum('quantity_consumed');

            return response()->json([
                'success' => true,
                'total_consumed' => $totalConsumed,
                'required_quantity' => $order->required_quantity,
                'remaining' => max(0, $order->required_quantity - $totalConsumed)
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
        $productionOrder = ProductionOrder::find($request->production_order_id);

        $rules = [
            'production_order_id' => 'required|exists:production_orders,order_id',
            'product_id' => 'required|exists:products,product_id',
            'famille_id' => 'required|exists:familles,famille_id',
            'quantity_produced' => 'required|numeric|min:0.01',
            'quantity_defective' => 'required|numeric|min:0',
            'production_date' => 'required|date|before_or_equal:today',
            'total_volume_m3' => 'nullable|numeric|min:0',
            'waste_volume_m3' => 'nullable|numeric|min:0',
            'unit_volume_m3' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'wastes' => 'nullable|array',
            'wastes.*.waste_type' => 'required|in:recyclable,waste',
            'wastes.*.waste_source' => 'required|string|max:100',
            'wastes.*.height' => 'nullable|numeric|min:0',
            'wastes.*.width' => 'nullable|numeric|min:0',
            'wastes.*.depth' => 'nullable|numeric|min:0',
            'wastes.*.volume_m3' => 'nullable|numeric|min:0',
            'wastes.*.notes' => 'nullable|string|max:1000',
            'skip_waste_declaration' => 'nullable|boolean',
            'force_completion' => 'nullable|boolean',
        ];

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $productionOrder = ProductionOrder::with([
                'product',
                'outputs',
                'famille'
            ])->findOrFail($request->production_order_id);

            $product = Product::findOrFail($request->product_id);
            $famille = Famille::findOrFail($request->famille_id);

            // Check if order is already completed
            if ($productionOrder->status === 'completed') {
                throw new \Exception('Cet ordre de production est déjà terminé.');
            }

            // Verify product matches order
            if ($productionOrder->product_id != $product->product_id) {
                throw new \Exception('Le produit ne correspond pas à l\'ordre de production');
            }

            // Validate defective quantity
            if ($request->quantity_defective > $request->quantity_produced) {
                throw new \Exception('La quantité défectueuse ne peut pas dépasser la quantité produite');
            }

            $goodQuantity = $request->quantity_produced - $request->quantity_defective;
            $unitVolume = $request->unit_volume_m3 ?? $product->getVolumePerUnitInM3() ?? 0;
            $totalVolume = $request->total_volume_m3 ?? ($unitVolume * $request->quantity_produced);
            $autoWasteVolume = $unitVolume * $request->quantity_defective;

            // Calculate target progression
            $currentTargetProduced = $productionOrder->outputs
                ->where('famille_id', $productionOrder->famille_id)
                ->sum('quantity_produced');

            $newTargetTotal = $currentTargetProduced + $request->quantity_produced;
            $targetReached = $newTargetTotal >= $productionOrder->quantity_to_produce;
            $isTargetFamille = ($productionOrder->famille_id == $request->famille_id);

            // For Type 1 - Validate consumptions if this is the final output
            if ($isTargetFamille && $targetReached) {
                // Check if consumptions exist
                $consumptions = ProductionConsumption::where('production_order_id', $productionOrder->order_id)->get();

                if ($consumptions->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'type' => 'consumption_required',
                        'message' => 'Veuillez d\'abord enregistrer les consommations des matières premières avant de finaliser la production.'
                    ], 422);
                }

                $validation = $this->validateConsumptionAccuracy($productionOrder, $request->quantity_produced, true);

                if (!$validation['valid']) {
                    $errorMessages = [];
                    foreach ($validation['errors'] as $error) {
                        if (isset($error['type']) && $error['type'] === 'weight_quality') {
                            $errorMessages[] = $error['message'] . ': ' . ($error['message'] ?? 'Écart de poids excessif');
                        } else {
                            $errorMessages[] = "Matériau: {$error['material_name']} - "
                                . "Planifié: {$error['planned']}, "
                                . "Réel: {$error['actual']}, "
                                . "Écart: {$error['percentage']}%";
                        }
                    }

                    $responseMessage = "La consommation des matières premières n'est pas conforme.\n";
                    $responseMessage .= implode("\n", $errorMessages);

                    if ($validation['weight_validation'] && !$validation['weight_validation']['valid']) {
                        $responseMessage .= "\n\n" . $validation['weight_validation']['warning'];
                    }

                    return response()->json([
                        'success' => false,
                        'type' => 'consumption_error',
                        'errors' => $validation['errors'],
                        'weight_validation' => $validation['weight_validation'],
                        'message' => $responseMessage
                    ], 422);
                }
            }

            // Sync product with famille if needed
            if ($product->has_familles) {
                $productHasFamille = $product->familles()
                    ->where('familles.famille_id', $famille->famille_id)
                    ->exists();

                if (!$productHasFamille) {
                    $this->syncProductFamille($product, $famille);
                }
            }

            // Determine output type
            $outputType = $isTargetFamille ? 'type1' : 'mixed_family';

            // Handle manual waste declaration
            $manualWasteVolume = 0;
            $hasWasteDeclaration = false;

            if ($request->has('wastes') && is_array($request->wastes)) {
                foreach ($request->wastes as $wasteData) {
                    $manualWasteVolume += floatval($wasteData['volume_m3'] ?? 0);
                    $hasWasteDeclaration = true;
                }
            }

            $totalWasteVolume = $autoWasteVolume + $manualWasteVolume;
            $forceCompletion = $request->boolean('force_completion', false);
            $skipWasteDeclaration = $request->boolean('skip_waste_declaration', false);
            $requiresWasteDeclaration = $productionOrder->quantity_to_produce > 0;

            // Create production output
            $output = ProductionOutput::create([
                'production_order_id' => $productionOrder->order_id,
                'product_id' => $product->product_id,
                'famille_id' => $famille->famille_id,
                'famille_name' => $famille->famille_name,
                'source_famille_id' => null,
                'output_type' => $outputType,
                'quantity_produced' => $request->quantity_produced,
                'quantity_consumed' => $goodQuantity,
                'quantity_defective' => $request->quantity_defective,
                'total_volume_m3' => $totalVolume,
                'waste_volume_m3' => $totalWasteVolume,
                'unit_volume_m3' => $unitVolume,
                'recyclable_waste_volume' => 0,
                'pure_waste_volume' => 0,
                'has_waste_declaration' => $hasWasteDeclaration,
                'requires_waste_declaration' => $requiresWasteDeclaration,
                'production_date' => $request->production_date,
                'notes' => $request->notes,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'is_final_output' => $isTargetFamille && $targetReached,
                'skip_waste_declaration' => $skipWasteDeclaration,
                'force_completion' => $forceCompletion,
            ]);

            // Handle wastes
            $wasteResults = [
                'recyclable_volume' => 0,
                'pure_waste_volume' => 0,
                'total_volume' => 0,
                'recyclable_count' => 0,
                'waste_count' => 0,
                'auto_defective_volume' => $autoWasteVolume,
                'has_waste_declaration' => $hasWasteDeclaration,
            ];

            if ($request->has('wastes') || $autoWasteVolume > 0) {
                $wasteResults = $this->handleProductionWastes(
                    $request->wastes ?? [],
                    $output,
                    $productionOrder,
                    $autoWasteVolume
                );
            }

            // Update output with waste data
            $output->update([
                'recyclable_waste_volume' => $wasteResults['recyclable_volume'],
                'pure_waste_volume' => $wasteResults['pure_waste_volume'],
                'has_waste_declaration' => $wasteResults['has_waste_declaration'],
            ]);

            // Add good products to famille stock
            if ($goodQuantity > 0) {
                $this->addToFamilleStock($product, $famille, $goodQuantity, $output, $totalVolume);
            }

            // Update production order totals
            $productionOrder->refresh();
            $productionOrder->load('outputs');

            $productionOrder->update([
                'waste_volume' => ($productionOrder->waste_volume ?? 0) + $wasteResults['total_volume'],
                'total_volume_produced' => ($productionOrder->total_volume_produced ?? 0) + $totalVolume,
                'requires_waste_declaration' => $requiresWasteDeclaration,
            ]);

            // Check if order should be completed
            $orderCompleted = false;
            $wasteDeclarationIncomplete = false;
            $completionBlocked = false;
            $wasteWarning = null;

            if ($isTargetFamille) {
                $updatedTargetProduced = $productionOrder->outputs
                    ->where('famille_id', $productionOrder->famille_id)
                    ->sum('quantity_produced');

                $quantityReached = $updatedTargetProduced >= $productionOrder->quantity_to_produce;

                if ($quantityReached) {
                    $hasAnyWasteInOrder = $productionOrder->outputs
                        ->where('famille_id', $productionOrder->famille_id)
                        ->contains('has_waste_declaration', true);

                    if ($requiresWasteDeclaration && !$hasAnyWasteInOrder && !$forceCompletion) {
                        $orderCompleted = false;
                        $wasteDeclarationIncomplete = true;
                        $completionBlocked = true;
                        $wasteWarning = "Quantité cible atteinte! Pour marquer l'ordre comme terminé, " .
                                    "ajoutez une chute (recyclable ou déchet, même avec volume 0) dans une sortie ultérieure.";

                        $productionOrder->update([
                            'status' => 'in_progress',
                            'waste_declaration_completed' => false,
                            'last_waste_warning' => $wasteWarning,
                        ]);
                    } else {
                        $orderCompleted = true;

                        // Mark all target famille outputs as final
                        ProductionOutput::where('production_order_id', $productionOrder->order_id)
                            ->where('famille_id', $productionOrder->famille_id)
                            ->update(['is_final_output' => true]);

                        $productionOrder->update([
                            'status' => 'completed',
                            'actual_completion_date' => now(),
                            'completed_by' => auth()->id(),
                            'waste_declaration_completed' => $hasAnyWasteInOrder,
                        ]);
                    }
                }
            }

            // Ensure order status is in_progress if not completed
            if (!$orderCompleted && $productionOrder->status !== 'in_progress') {
                $productionOrder->update(['status' => 'in_progress']);
            }

            if (!$isTargetFamille && $productionOrder->isType1()) {
                $weightValidation = $this->validateWeightQuality($productionOrder, $request->quantity_produced, false);

                if ($weightValidation['needs_validation'] && !$weightValidation['valid']) {
                    $output->update([
                        'notes' => ($output->notes ? $output->notes . "\n" : '') .
                            "[ATTENTION QUALITÉ] " . $weightValidation['warning']
                    ]);

                    Log::warning('Weight quality warning for non-final output', [
                        'output_id' => $output->output_id,
                        'order_id' => $productionOrder->order_id,
                        'validation' => $weightValidation
                    ]);
                }
            }

            DB::commit();

            // Prepare response
            $responseData = [
                'success' => true,
                'message' => 'Sortie de production enregistrée avec succès!',
                'output_id' => $output->output_id,
                'order_id' => $productionOrder->order_id,
                'is_target_famille' => $isTargetFamille,
                'order_completed' => $orderCompleted,
                'quantity_reached' => $targetReached,
                'is_final_output' => $isTargetFamille && $targetReached,
                'production_type' => $productionOrder->production_type,
                'total_volume' => $totalVolume,
                'auto_waste_volume' => $autoWasteVolume,
                'manual_waste_volume' => $manualWasteVolume,
                'total_waste_volume' => $totalWasteVolume,
                'wastes_count' => $request->has('wastes') ? count($request->wastes) : 0,
                'requires_waste_declaration' => $requiresWasteDeclaration,
                'has_waste_declaration' => $hasWasteDeclaration,
                'waste_declaration_incomplete' => $wasteDeclarationIncomplete,
                'completion_blocked' => $completionBlocked,
                'waste_warning' => $wasteWarning,
                'recyclable_waste_volume' => $wasteResults['recyclable_volume'] ?? 0,
                'pure_waste_volume' => $wasteResults['pure_waste_volume'] ?? 0,
                'total_waste_volume_result' => $wasteResults['total_volume'] ?? 0,
                'recyclable_count' => $wasteResults['recyclable_count'] ?? 0,
                'waste_count' => $wasteResults['waste_count'] ?? 0,
                'order_status' => $productionOrder->status,
                'remaining_quantity' => $isTargetFamille ?
                    max(0, $productionOrder->quantity_to_produce - ($productionOrder->outputs->where('famille_id', $productionOrder->famille_id)->sum('quantity_produced'))) : 0,
                'current_target_produced' => $productionOrder->outputs->where('famille_id', $productionOrder->famille_id)->sum('quantity_produced'),
                'skip_waste_declaration' => $skipWasteDeclaration,
                'force_completion' => $forceCompletion,
            ];

            return response()->json($responseData);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Production output store error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    private function validateConsumptionAccuracy($productionOrder, $currentOutputQuantity = null, $isFinalOutput = true)
    {
        $errors = [];
        $tolerance = 0.01;
        $valid = true;

        $consumptions = ProductionConsumption::where('production_order_id', $productionOrder->order_id)->get();

        foreach ($consumptions as $consumption) {
            $plannedQuantity = floatval($consumption->planned_quantity);
            $actualQuantity = floatval($consumption->actual_quantity_used);

            if ($plannedQuantity > 0 && $actualQuantity > 0) {
                $difference = abs($actualQuantity - $plannedQuantity);
                $percentage = ($difference / $plannedQuantity) * 100;

                if ($percentage > ($tolerance * 100)) {
                    $valid = false;
                    $material = $consumption->rawMaterial;
                    $errors[] = [
                        'material_id' => $consumption->material_id,
                        'material_name' => $material ? $material->material_name : 'Inconnu',
                        'planned' => round($plannedQuantity, 2),
                        'actual' => round($actualQuantity, 2),
                        'difference' => round($difference, 2),
                        'percentage' => round($percentage, 2)
                    ];
                }
            }
        }

        $weightValidation = null;
        if ($productionOrder->isType1()) {
            $weightValidation = $this->validateWeightQuality($productionOrder, $currentOutputQuantity, $isFinalOutput);

            if ($weightValidation['needs_validation'] && !$weightValidation['valid']) {
                $valid = false;
                $errors[] = [
                    'type' => 'weight_quality',
                    'message' => $weightValidation['message'],
                    'total_raw_material_weight' => $weightValidation['total_raw_material_weight'],
                    'total_product_weight' => $weightValidation['total_product_weight'],
                    'weight_difference_percent' => $weightValidation['weight_difference_percent'],
                    'tolerance_percent' => $weightValidation['tolerance_percent']
                ];
            }
        }

        return [
            'valid' => $valid,
            'errors' => $errors,
            'weight_validation' => $weightValidation
        ];
    }

    /**
     * Validate weight/quantity quality based on raw material consumption
     * Returns validation result with details - DOES NOT BLOCK PRODUCTION
     */
    private function validateWeightQuality($productionOrder, $outputQuantity = null, $isFinalOutput = true)
    {
        $product = $productionOrder->product;
        $productWeightPerUnit = $product->getWeightPerUnitInKg();

        if ($productWeightPerUnit <= 0) {
            Log::info('Weight quality validation skipped - product has no weight defined', [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name
            ]);
            return [
                'valid' => true,
                'needs_validation' => false,
                'quality_status' => 'good',
                'message' => 'Poids non défini pour ce produit'
            ];
        }

        // Get all consumptions for this order
        $consumptions = ProductionConsumption::where('production_order_id', $productionOrder->order_id)
            ->with('rawMaterial')
            ->get();

        if ($consumptions->isEmpty()) {
            Log::info('Weight quality validation skipped - no consumptions found');
            return [
                'valid' => true,
                'needs_validation' => false,
                'quality_status' => 'good',
                'message' => 'Aucune consommation enregistrée'
            ];
        }

        // Calculate total weight of all raw materials consumed
        $totalRawMaterialWeight = 0;
        $weightBreakdown = [];

        foreach ($consumptions as $consumption) {
            $material = $consumption->rawMaterial;
            if (!$material) continue;

            $materialWeight = $this->calculateMaterialWeightInKg($material, $consumption->actual_quantity_used);
            $totalRawMaterialWeight += $materialWeight;

            $weightBreakdown[] = [
                'material_name' => $material->material_name,
                'quantity' => $consumption->actual_quantity_used,
                'unit' => $material->unit_of_measure,
                'weight_kg' => round($materialWeight, 2)
            ];
        }

        // Calculate total product weight produced
        $totalProductWeight = 0;
        $outputsToConsider = $isFinalOutput
            ? $productionOrder->outputs->where('famille_id', $productionOrder->famille_id)
            : $productionOrder->outputs;

        foreach ($outputsToConsider as $output) {
            $goodQuantity = $output->quantity_produced - $output->quantity_defective;
            $totalProductWeight += $goodQuantity * $productWeightPerUnit;
        }

        // Add current output if provided
        if ($outputQuantity !== null) {
            $totalProductWeight += $outputQuantity * $productWeightPerUnit;
        }

        if ($totalProductWeight <= 0) {
            return [
                'valid' => true,
                'needs_validation' => false,
                'quality_status' => 'good',
                'message' => 'Aucun poids produit valide'
            ];
        }

        // Calculate weight ratio and difference
        $rawMaterialMinusProduct = $totalRawMaterialWeight - $totalProductWeight;
        $weightDifferencePercent = $totalRawMaterialWeight > 0
            ? (abs($totalRawMaterialWeight - $totalProductWeight) / $totalRawMaterialWeight) * 100
            : 0;

        // Define quality levels
        $goodTolerancePercent = 1.0;  // 1% = Good quality
        $warningTolerancePercent = 5.0; // 5% = Warning quality

        $isExcellent = $weightDifferencePercent <= 0.5;
        $isGood = $weightDifferencePercent <= $goodTolerancePercent;
        $isWarning = $weightDifferencePercent <= $warningTolerancePercent;
        $isCritical = !$isWarning;

        $qualityStatus = 'pending';
        $qualityScore = max(0, 100 - ($weightDifferencePercent * 10)); // Score out of 100

        if ($isExcellent) {
            $qualityStatus = 'good';
            $qualityScore = min(100, $qualityScore);
        } elseif ($isGood) {
            $qualityStatus = 'good';
        } elseif ($isWarning) {
            $qualityStatus = 'warning';
        } else {
            $qualityStatus = 'critical';
        }

        // Update production order with quality metrics
        $productionOrder->update([
            'quality_status' => $qualityStatus,
            'quality_score' => round($qualityScore, 2),
            'raw_material_weight_kg' => round($totalRawMaterialWeight, 2),
            'product_weight_kg' => round($totalProductWeight, 2),
            'weight_difference_percent' => round($weightDifferencePercent, 2),
            'quality_checked_at' => now(),
            'quality_checked_by' => auth()->id(),
            'quality_notes' => $this->generateQualityNotes($productionOrder, $weightDifferencePercent, $qualityStatus, $weightBreakdown)
        ]);

        // Also update total good/defective quantities
        $productionOrder->updateQualityMetrics();

        Log::info('Weight quality validation completed', [
            'order_id' => $productionOrder->order_id,
            'order_number' => $productionOrder->order_number,
            'quality_status' => $qualityStatus,
            'quality_score' => round($qualityScore, 2),
            'weight_difference_percent' => round($weightDifferencePercent, 2),
            'total_raw_material_weight_kg' => round($totalRawMaterialWeight, 2),
            'total_product_weight_kg' => round($totalProductWeight, 2)
        ]);

        return [
            'valid' => true, // Always return true - we don't block production
            'needs_validation' => true,
            'quality_status' => $qualityStatus,
            'quality_score' => round($qualityScore, 2),
            'total_raw_material_weight' => round($totalRawMaterialWeight, 2),
            'total_product_weight' => round($totalProductWeight, 2),
            'weight_difference' => round($rawMaterialMinusProduct, 2),
            'weight_difference_percent' => round($weightDifferencePercent, 2),
            'tolerance_good' => $goodTolerancePercent,
            'tolerance_warning' => $warningTolerancePercent,
            'breakdown' => $weightBreakdown,
            'message' => $this->getQualityMessage($qualityStatus, $weightDifferencePercent),
            'warning' => $qualityStatus === 'warning'
                ? "⚠️ Alerte qualité: Écart poids de {$weightDifferencePercent}% (tolérance: {$goodTolerancePercent}%)"
                : ($qualityStatus === 'critical'
                    ? "🔴 Qualité critique: Écart poids de {$weightDifferencePercent}% > {$warningTolerancePercent}%"
                    : null)
        ];
    }

    /**
     * Calculate material weight in kg based on unit of measure
     */
    private function calculateMaterialWeightInKg($material, $quantity)
    {
        if ($material->unit_of_measure === 'kg' || $material->unit_of_measure === 'Kg' || $material->unit_of_measure === 'kilogramme') {
            return (float) $quantity;
        }

        if ($material->unit_of_measure === 'm³' || $material->unit_of_measure === 'm3') {
            $density = $material->density_kg_per_m3 ?? 650;
            return (float) $quantity * $density;
        }

        if ($material->unit_weight_kg && $material->unit_weight_kg > 0) {
            return (float) $quantity * $material->unit_weight_kg;
        }

        return (float) $quantity * ($material->weight_per_unit ?? 0);
    }

    /**
     * Generate quality notes
     */
    private function generateQualityNotes($productionOrder, $weightDifferencePercent, $qualityStatus, $weightBreakdown)
    {
        $topMaterials = array_slice($weightBreakdown, 0, 3);
        $materialSummary = [];
        foreach ($topMaterials as $material) {
            $materialSummary[] = "{$material['material_name']}: {$material['weight_kg']} kg";
        }

        $notes = "Contrôle qualité effectué le " . now()->format('d/m/Y H:i') . "\n";
        $notes .= "Statut: " . ($qualityStatus === 'good' ? '✅ Bon' : ($qualityStatus === 'warning' ? '⚠️ Attention' : '🔴 Critique')) . "\n";
        $notes .= "Écart poids: {$weightDifferencePercent}%\n";
        $notes .= "Principaux matériaux consommés:\n- " . implode("\n- ", $materialSummary) . "\n";

        if ($qualityStatus !== 'good') {
            $notes .= "\n🔍 Recommandation: Vérifier les quantités de matière première et les pertes de production.";
        }

        return $notes;
    }

    /**
     * Get quality message based on status
     */
    private function getQualityMessage($qualityStatus, $weightDifferencePercent)
    {
        switch ($qualityStatus) {
            case 'good':
                return "✅ Qualité conforme: Écart poids {$weightDifferencePercent}% (dans les tolérances)";
            case 'warning':
                return "⚠️ Qualité à surveiller: Écart poids {$weightDifferencePercent}% (dépasse la tolérance idéale)";
            case 'critical':
                return "🔴 Qualité critique: Écart poids {$weightDifferencePercent}% (nécessite une vérification)";
            default:
                return "Qualité en attente d'évaluation";
        }
    }

    /**
     * Override quality check for a production order
     */
    public function overrideQuality(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500',
            'force_completion' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $order = ProductionOrder::findOrFail($orderId);

            $order->update([
                'quality_override' => true,
                'quality_override_reason' => $request->reason,
                'quality_override_at' => now(),
                'quality_override_by' => auth()->id(),
                'quality_status' => 'reviewed'
            ]);

            // If force completion is requested
            if ($request->force_completion && $order->status !== 'completed') {
                $totalProduced = $order->outputs->sum('quantity_produced');
                $targetReached = $totalProduced >= $order->quantity_to_produce;

                if ($targetReached) {
                    $order->update([
                        'status' => 'completed',
                        'actual_completion_date' => now(),
                        'completed_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Override qualité enregistré. L\'ordre peut être complété.',
                'order_id' => $order->order_id,
                'quality_override' => true
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
     * Store multiple produits découpage
     */
    public function storeType2(Request $request)
    {
        $request->validate([
            'production_order_id' => 'required|exists:production_orders,order_id',
            'source_product_id' => 'required|exists:products,product_id',
            'source_famille_id' => 'required|exists:familles,famille_id',
            'production_date' => 'required|date|before_or_equal:today',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,product_id',
            'products.*.quantity_produced' => 'required|numeric|min:0.01',
            'products.*.quantity_defective' => 'required|numeric|min:0',
            'total_volume_m3' => 'nullable|numeric|min:0',
            'waste_volume_m3' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $productionOrder = ProductionOrder::with(['sourceProduct', 'sourceFamille', 'outputs', 'wastes'])->findOrFail($request->production_order_id);

            if ($productionOrder->status === 'completed') {
                throw new \Exception('Cet ordre de production est déjà terminé.');
            }

            $plannedProducts = DB::table('production_order_products')
                ->where('production_order_id', $productionOrder->order_id)
                ->select('product_id', DB::raw('SUM(quantity_to_produce) as quantity_to_produce'))
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            // Consume source product stock once per order on first output declaration.
            // Include production_start in the check — if start() already deducted stock,
            // we must not deduct again here (would double-consume).
            $sourceAlreadyConsumed = ProductStockMovement::where('reference_number', $productionOrder->order_number)
                ->whereIn('movement_type', ['type2_consumption', 'production_start'])
                ->where('product_id', $productionOrder->source_product_id)
                ->exists();

            if (!$sourceAlreadyConsumed
                && $productionOrder->source_product_id
                && $productionOrder->source_famille_id
                && $productionOrder->required_quantity > 0
            ) {
                $this->consumeSourceFamilleStock(
                    $productionOrder->source_product_id,
                    $productionOrder->source_famille_id,
                    $productionOrder->required_quantity,
                    $productionOrder,
                    'type2'
                );
            }

            $outputs = [];
            $totalGoodQuantity = 0;
            $totalVolume = 0;
            $totalWasteVolume = 0;
            $allProductsCompleted = true;

            foreach ($request->products as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $quantityProduced = $productData['quantity_produced'];
                $quantityDefective = $productData['quantity_defective'];
                $goodQuantity = $quantityProduced - $quantityDefective;

                if ($quantityDefective > $quantityProduced) {
                    throw new \Exception("La quantité défectueuse ne peut pas dépasser la quantité produite pour {$product->product_name}");
                }

                $plannedQuantity = $plannedProducts[$product->product_id]->quantity_to_produce ?? 0;
                $alreadyProduced = ProductionOutput::where('production_order_id', $productionOrder->order_id)
                    ->where('product_id', $product->product_id)
                    ->where('output_type', 'type2')
                    ->sum('quantity_produced');

                $totalProducedForProduct = $alreadyProduced + $quantityProduced;

                if ($totalProducedForProduct > $plannedQuantity) {
                    throw new \Exception("Quantité excessive pour {$product->product_name}. Maximum: {$plannedQuantity}, déjà produit: {$alreadyProduced}");
                }

                $totalGoodQuantity += $goodQuantity;

                $unitVolume = $product->getVolumePerUnitInM3() ?? 0;
                $totalVolume += $quantityProduced * $unitVolume;
                $totalWasteVolume += $quantityDefective * $unitVolume;

                $sourceFamille = Famille::findOrFail($request->source_famille_id);

                $sourceProduct = $productionOrder->sourceProduct;
                $sourceHasFamille = $sourceProduct->familles()->where('familles.famille_id', $sourceFamille->famille_id)->exists();

                if (!$sourceHasFamille) {
                    $this->associateProductWithFamille($sourceProduct, $sourceFamille);
                    Log::info("Famille {$sourceFamille->famille_name} associée au produit source {$sourceProduct->product_name}");
                }

                $targetFamilleId = $productionOrder->famille_id;
                $targetFamille = Famille::find($targetFamilleId);

                if ($targetFamille) {
                    $targetHasFamille = $product->familles()->where('familles.famille_id', $targetFamilleId)->exists();
                    if (!$targetHasFamille) {
                        $this->associateProductWithFamille($product, $targetFamille, $sourceProduct);
                        Log::info("Famille {$targetFamille->famille_name} associée au produit cible {$product->product_name}");
                    }
                }

                $output = ProductionOutput::create([
                    'production_order_id' => $productionOrder->order_id,
                    'product_id' => $product->product_id,
                    'famille_id' => $targetFamilleId,
                    'famille_name' => $targetFamille ? $targetFamille->famille_name : null,
                    'source_famille_id' => $sourceFamille->famille_id,
                    'output_type' => 'type2',
                    'quantity_produced' => $quantityProduced,
                    'quantity_consumed' => $quantityProduced,
                    'quantity_defective' => $quantityDefective,
                    'total_volume_m3' => $quantityProduced * $unitVolume,
                    'waste_volume_m3' => $quantityDefective * $unitVolume,
                    'unit_volume_m3' => $unitVolume,
                    'production_date' => $request->production_date,
                    'notes' => $request->notes,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'is_final_output' => false,
                ]);

                $outputs[] = $output;

                if ($goodQuantity > 0 && $targetFamille) {
                    $this->addToFamilleStock($product, $targetFamille, $goodQuantity, $output, $quantityProduced * $unitVolume);
                }

                $autoWasteVolume = $unitVolume * $quantityDefective;
                if ($autoWasteVolume > 0) {
                    $chuteMaterial = $this->getOrCreateChuteMaterial();
                    ProductionWaste::create([
                        'production_order_id' => $output->productionOrder->production_order_id,
                        'material_id' => $chuteMaterial->material_id,
                        'waste_type' => 'auto_defective',
                        'waste_source' => 'Découpage',
                        'waste_category' => null,
                        'height' => null,
                        'width' => null,
                        'depth' => null,
                        'volume_m3' => $autoWasteVolume,
                        'notes' => 'Chute automatique des produits défectueux lors du découpage',
                        'is_recovered' => true,
                        'created_by' => auth()->id(),
                    ]);
                }

                if ($totalProducedForProduct < $plannedQuantity) {
                    $allProductsCompleted = false;
                }
            }

            $productionOrder->refresh();
            $productionOrder->load('outputs', 'wastes');

            $allProductsProduced = true;
            foreach ($plannedProducts as $plannedProduct) {
                $totalProduced = ProductionOutput::where('production_order_id', $productionOrder->order_id)
                    ->where('product_id', $plannedProduct->product_id)
                    ->where('output_type', 'type2')
                    ->sum('quantity_produced');

                if ($totalProduced < $plannedProduct->quantity_to_produce) {
                    $allProductsProduced = false;
                    break;
                }
            }

            $hasWasteDeclaration = $productionOrder->wastes->count() > 0;

            $orderCompleted = $allProductsProduced && $hasWasteDeclaration;

            if ($orderCompleted) {
                $productionOrder->update([
                    'status' => 'completed',
                    'actual_completion_date' => now(),
                    'completed_by' => auth()->id(),
                ]);
            } elseif ($allProductsProduced && !$hasWasteDeclaration) {
                $productionOrder->update([
                    'status' => 'in_progress',
                    'notes' => ($productionOrder->notes ? $productionOrder->notes . "\n" : '') .
                        "[SYSTEM] Tous les produits ont été produits. Veuillez déclarer les chutes pour finaliser."
                ]);
            } else {
                $productionOrder->update([
                    'status' => 'in_progress',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($outputs) . ' sortie(s) de découpage enregistrée(s) avec succès!',
                'outputs_count' => count($outputs),
                'order_completed' => $orderCompleted,
                'all_products_produced' => $allProductsProduced,
                'has_waste_declaration' => $hasWasteDeclaration,
                'total_volume' => $totalVolume,
                'total_waste_volume' => $totalWasteVolume,
                'total_good_quantity' => $totalGoodQuantity,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Type2 store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store multiple produits finaux
     */
    public function storeType3(Request $request)
    {
        $request->validate([
            'production_order_id' => 'required|exists:production_orders,order_id',
            'source_product_id' => 'required|exists:products,product_id',
            'source_famille_id' => 'required|exists:familles,famille_id',
            'production_date' => 'required|date|before_or_equal:today',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,product_id',
            'products.*.quantity_produced' => 'required|numeric|min:0.01',
            'products.*.quantity_defective' => 'required|numeric|min:0',
            'total_volume_m3' => 'nullable|numeric|min:0',
            'waste_volume_m3' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $productionOrder = ProductionOrder::with(['sourceProduct', 'sourceFamille', 'outputs', 'wastes'])->findOrFail($request->production_order_id);

            if ($productionOrder->status === 'completed') {
                throw new \Exception('Cet ordre de production est déjà terminé.');
            }

            $plannedProducts = DB::table('production_order_products')
                ->where('production_order_id', $productionOrder->order_id)
                ->select('product_id', DB::raw('SUM(quantity_to_produce) as quantity_to_produce'))
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            // Consume source product stock once per order on first output declaration.
            // Include production_start in the check — if start() already deducted stock,
            // we must not deduct again here (would double-consume).
            $sourceAlreadyConsumed = ProductStockMovement::where('reference_number', $productionOrder->order_number)
                ->whereIn('movement_type', ['type3_consumption', 'production_start'])
                ->where('product_id', $productionOrder->source_product_id)
                ->exists();

            if (!$sourceAlreadyConsumed
                && $productionOrder->source_product_id
                && $productionOrder->source_famille_id
                && $productionOrder->required_quantity > 0
            ) {
                $this->consumeSourceFamilleStock(
                    $productionOrder->source_product_id,
                    $productionOrder->source_famille_id,
                    $productionOrder->required_quantity,
                    $productionOrder,
                    'type3'
                );
            }

            $outputs = [];
            $totalGoodQuantity = 0;
            $totalVolume = 0;
            $totalWasteVolume = 0;

            foreach ($request->products as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $quantityProduced = $productData['quantity_produced'];
                $quantityDefective = $productData['quantity_defective'];
                $goodQuantity = $quantityProduced - $quantityDefective;

                if ($quantityDefective > $quantityProduced) {
                    throw new \Exception("La quantité défectueuse ne peut pas dépasser la quantité produite pour {$product->product_name}");
                }

                $plannedQuantity = $plannedProducts[$product->product_id]->quantity_to_produce ?? 0;
                $alreadyProduced = ProductionOutput::where('production_order_id', $productionOrder->order_id)
                    ->where('product_id', $product->product_id)
                    ->where('output_type', 'type3')
                    ->sum('quantity_produced');

                $totalProducedForProduct = $alreadyProduced + $quantityProduced;

                if ($totalProducedForProduct > $plannedQuantity) {
                    throw new \Exception("Quantité excessive pour {$product->product_name}. Maximum: {$plannedQuantity}, déjà produit: {$alreadyProduced}");
                }

                $type3Product = DB::table('production_order_products')
                    ->where('production_order_id', $productionOrder->order_id)
                    ->where('product_id', $product->product_id)
                    ->first();

                $conversionRate = $type3Product ? $type3Product->conversion_rate : 1;
                $sousBlocsConsumed = $quantityProduced * $conversionRate;

                $totalGoodQuantity += $goodQuantity;

                $unitVolume = $product->getVolumePerUnitInM3() ?? 0;
                $totalVolume += $quantityProduced * $unitVolume;
                $totalWasteVolume += $quantityDefective * $unitVolume;

                $sourceFamille = Famille::findOrFail($request->source_famille_id);

                $sourceProduct = $productionOrder->sourceProduct;
                $sourceHasFamille = $sourceProduct->familles()->where('familles.famille_id', $sourceFamille->famille_id)->exists();

                if (!$sourceHasFamille) {
                    $this->associateProductWithFamille($sourceProduct, $sourceFamille);
                    Log::info("Famille {$sourceFamille->famille_name} associée au produit source {$sourceProduct->product_name}");
                }

                $targetFamilleId = $productionOrder->famille_id;
                $targetFamille = Famille::find($targetFamilleId);

                if ($targetFamille) {
                    $targetHasFamille = $product->familles()->where('familles.famille_id', $targetFamilleId)->exists();
                    if (!$targetHasFamille) {
                        $this->associateProductWithFamille($product, $targetFamille, $sourceProduct);
                        Log::info("Famille {$targetFamille->famille_name} associée au produit cible {$product->product_name}");
                    }
                }

                $output = ProductionOutput::create([
                    'production_order_id' => $productionOrder->order_id,
                    'product_id' => $product->product_id,
                    'famille_id' => $targetFamilleId,
                    'famille_name' => $targetFamille ? $targetFamille->famille_name : null,
                    'source_famille_id' => $sourceFamille->famille_id,
                    'output_type' => 'type3',
                    'quantity_produced' => $quantityProduced,
                    'quantity_consumed' => $sousBlocsConsumed,
                    'quantity_defective' => $quantityDefective,
                    'total_volume_m3' => $quantityProduced * $unitVolume,
                    'waste_volume_m3' => $quantityDefective * $unitVolume,
                    'unit_volume_m3' => $unitVolume,
                    'production_date' => $request->production_date,
                    'notes' => $request->notes,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'is_final_output' => false,
                ]);

                $outputs[] = $output;

                if ($goodQuantity > 0 && $targetFamille) {
                    $this->addToFamilleStock($product, $targetFamille, $goodQuantity, $output, $quantityProduced * $unitVolume);
                }

                $autoWasteVolume = $unitVolume * $quantityDefective;
                if ($autoWasteVolume > 0) {
                    $chuteMaterial = $this->getOrCreateChuteMaterial();
                    ProductionWaste::create([
                        'production_order_id' => $output->productionOrder->production_order_id,
                        'material_id' => $chuteMaterial->material_id,
                        'waste_type' => 'auto_defective',
                        'waste_source' => 'Conversion',
                        'waste_category' => null,
                        'height' => null,
                        'width' => null,
                        'depth' => null,
                        'volume_m3' => $autoWasteVolume,
                        'notes' => 'Chute automatique des produits défectueux lors de la conversion',
                        'is_recovered' => true,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            $productionOrder->refresh();
            $productionOrder->load('outputs', 'wastes');

            $allProductsProduced = true;
            foreach ($plannedProducts as $plannedProduct) {
                $totalProduced = ProductionOutput::where('production_order_id', $productionOrder->order_id)
                    ->where('product_id', $plannedProduct->product_id)
                    ->where('output_type', 'type3')
                    ->sum('quantity_produced');

                if ($totalProduced < $plannedProduct->quantity_to_produce) {
                    $allProductsProduced = false;
                    break;
                }
            }

            $hasWasteDeclaration = $productionOrder->wastes->count() > 0;

            $orderCompleted = $allProductsProduced && $hasWasteDeclaration;

            if ($orderCompleted) {
                $productionOrder->update([
                    'status' => 'completed',
                    'actual_completion_date' => now(),
                    'completed_by' => auth()->id(),
                ]);
            } elseif ($allProductsProduced && !$hasWasteDeclaration) {
                $productionOrder->update([
                    'status' => 'in_progress',
                    'notes' => ($productionOrder->notes ? $productionOrder->notes . "\n" : '') .
                        "[SYSTEM] Tous les produits ont été produits. Veuillez déclarer les chutes pour finaliser."
                ]);
            } else {
                $productionOrder->update([
                    'status' => 'in_progress',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($outputs) . ' sortie(s) de conversion enregistrée(s) avec succès!',
                'outputs_count' => count($outputs),
                'order_completed' => $orderCompleted,
                'all_products_produced' => $allProductsProduced,
                'has_waste_declaration' => $hasWasteDeclaration,
                'total_volume' => $totalVolume,
                'total_waste_volume' => $totalWasteVolume,
                'total_good_quantity' => $totalGoodQuantity,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Type3 store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store Type 5 production output (Chutes -> Produits Finis)
     */
    public function storeType5(Request $request)
    {
        $request->validate([
            'production_order_id' => 'required|exists:production_orders,order_id',
            'production_date' => 'required|date|before_or_equal:today',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,product_id',
            'products.*.quantity_produced' => 'required|numeric|min:0.01',
            'products.*.quantity_defective' => 'required|numeric|min:0',
            'total_volume_m3' => 'nullable|numeric|min:0',
            'waste_volume_m3' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $productionOrder = ProductionOrder::with(['outputs', 'wastes', 'famille'])->findOrFail($request->production_order_id);

            if ($productionOrder->status === 'completed') {
                throw new \Exception('Cet ordre de production est déjà terminé.');
            }

            $plannedProducts = DB::table('production_order_products')
                ->where('production_order_id', $productionOrder->order_id)
                ->select('product_id', DB::raw('SUM(quantity_to_produce) as quantity_to_produce'))
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            $outputs = [];
            $totalGoodQuantity = 0;
            $totalVolume = 0;
            $totalWasteVolume = 0;

            $targetFamilleId = $productionOrder->famille_id;
            $targetFamille = Famille::find($targetFamilleId);

            foreach ($request->products as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $quantityProduced = $productData['quantity_produced'];
                $quantityDefective = $productData['quantity_defective'];
                $goodQuantity = $quantityProduced - $quantityDefective;

                if ($quantityDefective > $quantityProduced) {
                    throw new \Exception("La quantité défectueuse ne peut pas dépasser la quantité produite pour {$product->product_name}");
                }

                $plannedQuantity = $plannedProducts[$product->product_id]->quantity_to_produce ?? 0;
                $alreadyProduced = ProductionOutput::where('production_order_id', $productionOrder->order_id)
                    ->where('product_id', $product->product_id)
                    ->where('output_type', 'type5')
                    ->sum('quantity_produced');

                $totalProducedForProduct = $alreadyProduced + $quantityProduced;

                if ($totalProducedForProduct > $plannedQuantity) {
                    throw new \Exception("Quantité excessive pour {$product->product_name}. Maximum: {$plannedQuantity}, déjà produit: {$alreadyProduced}");
                }

                $totalGoodQuantity += $goodQuantity;

                $unitVolume = $product->getVolumePerUnitInM3() ?? 0;
                $totalVolume += $quantityProduced * $unitVolume;
                $totalWasteVolume += $quantityDefective * $unitVolume;

                if ($targetFamille) {
                    $targetHasFamille = $product->familles()->where('familles.famille_id', $targetFamilleId)->exists();
                    if (!$targetHasFamille) {
                        $this->associateProductWithFamille($product, $targetFamille);
                        Log::info("Famille {$targetFamille->famille_name} associée au produit cible {$product->product_name}");
                    }
                }

                $output = ProductionOutput::create([
                    'production_order_id' => $productionOrder->order_id,
                    'product_id' => $product->product_id,
                    'famille_id' => $targetFamilleId,
                    'famille_name' => $targetFamille ? $targetFamille->famille_name : null,
                    'output_type' => 'type5',
                    'quantity_produced' => $quantityProduced,
                    'quantity_consumed' => 0,
                    'quantity_defective' => $quantityDefective,
                    'total_volume_m3' => $quantityProduced * $unitVolume,
                    'waste_volume_m3' => $quantityDefective * $unitVolume,
                    'unit_volume_m3' => $unitVolume,
                    'production_date' => $request->production_date,
                    'notes' => $request->notes,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'is_final_output' => false,
                ]);

                $outputs[] = $output;

                if ($goodQuantity > 0 && $targetFamille) {
                    $this->addToFamilleStock($product, $targetFamille, $goodQuantity, $output, $quantityProduced * $unitVolume);
                }

                $autoWasteVolume = $unitVolume * $quantityDefective;
                if ($autoWasteVolume > 0) {
                    $chuteMaterial = $this->getOrCreateChuteMaterial();
                    ProductionWaste::create([
                        'production_order_id' => $productionOrder->order_id,
                        'material_id' => $chuteMaterial->material_id,
                        'waste_type' => 'auto_defective',
                        'waste_source' => 'Chutes → Produits Finis',
                        'waste_category' => null,
                        'height' => null,
                        'width' => null,
                        'depth' => null,
                        'volume_m3' => $autoWasteVolume,
                        'notes' => 'Chute automatique des produits défectueux lors de la production depuis chutes',
                        'is_recovered' => true,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            $productionOrder->refresh();
            $productionOrder->load('outputs', 'wastes');

            $allProductsProduced = true;
            foreach ($plannedProducts as $plannedProduct) {
                $totalProduced = ProductionOutput::where('production_order_id', $productionOrder->order_id)
                    ->where('product_id', $plannedProduct->product_id)
                    ->where('output_type', 'type5')
                    ->sum('quantity_produced');

                if ($totalProduced < $plannedProduct->quantity_to_produce) {
                    $allProductsProduced = false;
                    break;
                }
            }

            if ($allProductsProduced) {
                $productionOrder->update([
                    'status' => 'completed',
                    'actual_completion_date' => now(),
                    'completed_by' => auth()->id(),
                ]);
            } else {
                $productionOrder->update([
                    'status' => 'in_progress',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($outputs) . ' sortie(s) enregistrée(s) avec succès!',
                'outputs_count' => count($outputs),
                'order_completed' => $allProductsProduced,
                'all_products_produced' => $allProductsProduced,
                'total_volume' => $totalVolume,
                'total_waste_volume' => $totalWasteVolume,
                'total_good_quantity' => $totalGoodQuantity,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Type5 store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store Type 4 production output
     */
    public function storeType4(Request $request)
    {
        $request->validate([
            'production_order_id' => 'required|exists:production_orders,order_id',
            'source_product_id' => 'required|exists:products,product_id',
            'source_famille_id' => 'required|exists:familles,famille_id',
            'production_date' => 'required|date|before_or_equal:today',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,product_id',
            'products.*.quantity_produced' => 'required|numeric|min:0.01',
            'products.*.quantity_defective' => 'required|numeric|min:0',
            'total_volume_m3' => 'nullable|numeric|min:0',
            'waste_volume_m3' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $productionOrder = ProductionOrder::with(['sourceProduct', 'sourceFamille', 'famille', 'outputs', 'wastes'])
                ->findOrFail($request->production_order_id);

            if ($productionOrder->status === 'completed') {
                throw new \Exception('Cet ordre de production est déjà terminé.');
            }

            $plannedProducts = DB::table('production_order_products')
                ->where('production_order_id', $productionOrder->order_id)
                ->select('product_id', DB::raw('SUM(quantity_to_produce) as quantity_to_produce'))
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            // Consume source product stock once per order on first output declaration.
            // Include production_start in the check — if start() already deducted stock,
            // we must not deduct again here (would double-consume).
            $sourceAlreadyConsumed = ProductStockMovement::where('reference_number', $productionOrder->order_number)
                ->whereIn('movement_type', ['type4_consumption', 'production_start'])
                ->where('product_id', $productionOrder->source_product_id)
                ->exists();

            if (!$sourceAlreadyConsumed
                && $productionOrder->source_product_id
                && $productionOrder->source_famille_id
                && $productionOrder->required_quantity > 0
            ) {
                $this->consumeSourceFamilleStock(
                    $productionOrder->source_product_id,
                    $productionOrder->source_famille_id,
                    $productionOrder->required_quantity,
                    $productionOrder,
                    'type4'
                );
            }

            $outputs = [];
            $totalGoodQuantity = 0;
            $totalVolume = 0;
            $totalWasteVolume = 0;
            $allProductsCompleted = true;

            foreach ($request->products as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $quantityProduced = $productData['quantity_produced'];
                $quantityDefective = $productData['quantity_defective'];
                $goodQuantity = $quantityProduced - $quantityDefective;

                if ($quantityDefective > $quantityProduced) {
                    throw new \Exception("La quantité défectueuse ne peut pas dépasser la quantité produite pour {$product->product_name}");
                }

                $plannedQuantity = $plannedProducts[$product->product_id]->quantity_to_produce ?? 0;
                $alreadyProduced = ProductionOutput::where('production_order_id', $productionOrder->order_id)
                    ->where('product_id', $product->product_id)
                    ->where('output_type', 'type4')
                    ->sum('quantity_produced');

                $totalProducedForProduct = $alreadyProduced + $quantityProduced;

                if ($totalProducedForProduct > $plannedQuantity) {
                    throw new \Exception("Quantité excessive pour {$product->product_name}. Maximum: {$plannedQuantity}, déjà produit: {$alreadyProduced}");
                }

                $totalGoodQuantity += $goodQuantity;

                $unitVolume = $product->getVolumePerUnitInM3() ?? 0;
                $totalVolume += $quantityProduced * $unitVolume;
                $totalWasteVolume += $quantityDefective * $unitVolume;

                $sourceFamille = Famille::findOrFail($request->source_famille_id);
                $targetFamille = $productionOrder->famille;

                $sourceProduct = $productionOrder->sourceProduct;
                $sourceHasFamille = $sourceProduct->familles()->where('familles.famille_id', $sourceFamille->famille_id)->exists();

                if (!$sourceHasFamille) {
                    $this->associateProductWithFamille($sourceProduct, $sourceFamille);
                    Log::info("Famille {$sourceFamille->famille_name} associée au produit source {$sourceProduct->product_name}");
                }

                if ($targetFamille) {
                    $targetHasFamille = $product->familles()->where('familles.famille_id', $targetFamille->famille_id)->exists();
                    if (!$targetHasFamille) {
                        $this->associateProductWithFamille($product, $targetFamille, $sourceProduct);
                        Log::info("Famille {$targetFamille->famille_name} associée au produit cible {$product->product_name}");
                    }
                }

                $output = ProductionOutput::create([
                    'production_order_id' => $productionOrder->order_id,
                    'product_id' => $product->product_id,
                    'famille_id' => $targetFamille ? $targetFamille->famille_id : null,
                    'famille_name' => $targetFamille ? $targetFamille->famille_name : null,
                    'source_famille_id' => $sourceFamille->famille_id,
                    'output_type' => 'type4',
                    'quantity_produced' => $quantityProduced,
                    'quantity_consumed' => $quantityProduced,
                    'quantity_defective' => $quantityDefective,
                    'total_volume_m3' => $quantityProduced * $unitVolume,
                    'waste_volume_m3' => $quantityDefective * $unitVolume,
                    'unit_volume_m3' => $unitVolume,
                    'production_date' => $request->production_date,
                    'notes' => $request->notes,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'is_final_output' => false,
                ]);

                $outputs[] = $output;

                if ($goodQuantity > 0 && $targetFamille) {
                    $this->addToFamilleStock($product, $targetFamille, $goodQuantity, $output, $quantityProduced * $unitVolume);
                }

                $autoWasteVolume = $unitVolume * $quantityDefective;
                if ($autoWasteVolume > 0) {
                    $chuteMaterial = $this->getOrCreateChuteMaterial();
                    ProductionWaste::create([
                        'production_output_id' => $output->output_id,
                        'material_id' => $chuteMaterial->material_id,
                        'waste_type' => 'waste',
                        'waste_source' => 'Transformation Vente → Vente',
                        'waste_category' => null,
                        'height' => null,
                        'width' => null,
                        'depth' => null,
                        'volume_m3' => $autoWasteVolume,
                        'notes' => 'Chute automatique des produits défectueux lors de la transformation',
                        'is_recovered' => true,
                        'created_by' => auth()->id(),
                    ]);
                }

                if ($totalProducedForProduct < $plannedQuantity) {
                    $allProductsCompleted = false;
                }
            }

            $productionOrder->refresh();
            $productionOrder->load('outputs', 'wastes');

            $allProductsProduced = true;
            foreach ($plannedProducts as $plannedProduct) {
                $totalProduced = ProductionOutput::where('production_order_id', $productionOrder->order_id)
                    ->where('product_id', $plannedProduct->product_id)
                    ->where('output_type', 'type4')
                    ->sum('quantity_produced');

                if ($totalProduced < $plannedProduct->quantity_to_produce) {
                    $allProductsProduced = false;
                    break;
                }
            }

            $totalSourceConsumed = ProductionOutput::where('production_order_id', $productionOrder->order_id)
                ->where('output_type', 'type4')
                ->sum('quantity_consumed');

            $sourceFullyConsumed = $totalSourceConsumed >= $productionOrder->required_quantity;
            $hasWasteDeclaration = $productionOrder->wastes->count() > 0;

            $orderCompleted = $allProductsProduced && $sourceFullyConsumed && $hasWasteDeclaration;

            if ($orderCompleted) {
                $productionOrder->update([
                    'status' => 'completed',
                    'actual_completion_date' => now(),
                    'completed_by' => auth()->id(),
                ]);
            } elseif ($allProductsProduced && $sourceFullyConsumed && !$hasWasteDeclaration) {
                $productionOrder->update([
                    'status' => 'in_progress',
                    'notes' => ($productionOrder->notes ? $productionOrder->notes . "\n" : '') .
                        "[SYSTEM] Tous les produits ont été produits. Veuillez déclarer les chutes pour finaliser."
                ]);
            } else {
                $productionOrder->update([
                    'status' => 'in_progress',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($outputs) . ' sortie(s) de transformation enregistrée(s) avec succès!',
                'outputs_count' => count($outputs),
                'order_completed' => $orderCompleted,
                'all_products_produced' => $allProductsProduced,
                'source_fully_consumed' => $sourceFullyConsumed,
                'has_waste_declaration' => $hasWasteDeclaration,
                'total_volume' => $totalVolume,
                'total_waste_volume' => $totalWasteVolume,
                'total_good_quantity' => $totalGoodQuantity,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Type4 store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Associate a product with a famille and copy price information from source product if available
     */
    private function associateProductWithFamille(Product $product, Famille $famille, Product $sourceProduct = null)
    {
        // Check if association already exists
        if ($product->familles()->where('familles.famille_id', $famille->famille_id)->exists()) {
            return;
        }

        // Get price information from source product's famille association if available
        $priceData = [];
        if ($sourceProduct) {
            $sourcePivot = $sourceProduct->familles()->where('familles.famille_id', $famille->famille_id)->first();
            if ($sourcePivot) {
                $priceData = [
                    'prix_client' => $sourcePivot->pivot->prix_client,
                    'prix_grossiste' => $sourcePivot->pivot->prix_grossiste,
                    'prix_commercial' => $sourcePivot->pivot->prix_commercial,
                    'prix_special' => $sourcePivot->pivot->prix_special,
                    'quantity_per_unit' => $sourcePivot->pivot->quantity_per_unit ?? 1,
                ];
            }
        }

        // Attach famille to product
        $product->familles()->attach($famille->famille_id, array_merge([
            'quantity_per_unit' => 1,
            'sort_order' => $product->familles()->count() + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], $priceData));

        // Create stock record for this product-famille combination
        ProductFamilleStock::firstOrCreate(
            [
                'product_id' => $product->product_id,
                'famille_id' => $famille->famille_id,
            ],
            [
                'famille_name' => $famille->famille_name,
                'current_quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'location' => 'Entrepôt Principal',
                'last_restocked' => null,
                'created_at' => now(),
            ]
        );

        Log::info("Product {$product->product_name} associated with famille {$famille->famille_name}");
    }

    /**
     * Handle BOM consumption for type1 production
     */
    private function handleBOMConsumption($consumptions, $productionOrder, $product)
    {
        if (empty($consumptions) || !is_array($consumptions)) {
            Log::info('No consumptions provided for BOM');
            return;
        }

        foreach ($consumptions as $consumptionData) {
            // Skip if material_id or actual_quantity_used is empty
            if (empty($consumptionData['material_id']) || empty($consumptionData['actual_quantity_used'])) {
                Log::warning('Skipping consumption entry: missing material_id or quantity', [
                    'consumption_data' => $consumptionData
                ]);
                continue;
            }

            $material = RawMaterial::find($consumptionData['material_id']);
            if (!$material) {
                Log::warning('Material not found for consumption', [
                    'material_id' => $consumptionData['material_id']
                ]);
                continue;
            }

            $actualQuantityUsed = floatval($consumptionData['actual_quantity_used']);
            $wasteQuantity = isset($consumptionData['waste_quantity']) ? floatval($consumptionData['waste_quantity']) : 0;
            $totalToConsume = $actualQuantityUsed + $wasteQuantity;

            if ($totalToConsume <= 0) {
                Log::info('Zero consumption for material', [
                    'material_id' => $material->material_id,
                    'material_name' => $material->material_name
                ]);
                continue;
            }

            // Find or create consumption record
            $consumption = ProductionConsumption::firstOrNew([
                'production_order_id' => $productionOrder->order_id,
                'material_id' => $material->material_id,
            ]);

            if (!$consumption->exists) {
                // Get planned quantity from BOM if available
                $bomItem = BillOfMaterial::where('product_id', $product->product_id)
                    ->where('material_id', $material->material_id)
                    ->first();

                $consumption->planned_quantity = $bomItem ?
                    $bomItem->quantity_required * $productionOrder->quantity_to_produce : 0;
                $consumption->unit_cost = $material->average_unit_cost ?? 0;
                $consumption->waste_quantity = 0;
                $consumption->actual_quantity_used = 0;
                $consumption->total_cost = 0;
                $consumption->notes = null;
                $consumption->is_waste = false;
            }

            // Update consumption amounts
            $consumption->actual_quantity_used += $actualQuantityUsed;
            $consumption->waste_quantity += $wasteQuantity;
            $consumption->total_cost += $actualQuantityUsed * ($material->average_unit_cost ?? 0);
            $consumption->save();

            Log::info('BOM consumption recorded', [
                'production_order_id' => $productionOrder->order_id,
                'material_id' => $material->material_id,
                'material_name' => $material->material_name,
                'actual_quantity_used' => $actualQuantityUsed,
                'waste_quantity' => $wasteQuantity,
                'total_consumed' => $totalToConsume,
                'new_total_actual' => $consumption->actual_quantity_used,
                'new_total_waste' => $consumption->waste_quantity
            ]);

            // Consume stock from inventory (allows negative stock)
            try {
                $this->consumeRawMaterialStockFIFO($material->material_id, $totalToConsume, $productionOrder, [
                    'notes' => 'Consommation pour sortie de production type1 - Commande #' . $productionOrder->order_number,
                    'consumption_id' => $consumption->consumption_id
                ]);
            } catch (\Exception $e) {
                // Log the error but continue processing - allow negative stock
                Log::warning('Stock consumption warning (negative stock allowed)', [
                    'material_id' => $material->material_id,
                    'material_name' => $material->material_name,
                    'quantity' => $totalToConsume,
                    'current_stock' => $material->current_stock,
                    'error' => $e->getMessage()
                ]);
                // Continue execution - don't throw the exception
            }
        }

        Log::info('BOM consumption completed', [
            'production_order_id' => $productionOrder->order_id,
            'consumptions_count' => count($consumptions)
        ]);
    }

    /**
     * Handle production wastes
     */
    private function handleProductionWastes($wastes, ProductionOutput $output, ProductionOrder $order, $autoWasteVolume = 0)
    {
        $recyclableVolume = 0;
        $wasteVolume = 0;
        $chuteMaterial = null;

        $recyclableCount = 0;
        $wasteCount = 0;

        // LOGGING FOR DEBUG
        Log::info('=== HANDLE PRODUCTION WASTES START ===', [
            'output_id' => $output->output_id,
            'order_id' => $order->order_id,
            'auto_waste_volume' => $autoWasteVolume,
            'wastes_count' => is_array($wastes) ? count($wastes) : 0
        ]);

        // AUTO-DEFECTIVE WASTE: This comes from defective products
        // This volume is calculated as: unit_volume × quantity_defective
        if ($autoWasteVolume > 0) {
            $chuteMaterial = $this->getOrCreateChuteMaterial();

            ProductionWaste::create([
                'production_output_id' => $output->output_id,
                'material_id' => $chuteMaterial->material_id,
                'waste_type' => 'waste',
                'waste_source' => 'Défauts de production',
                'waste_category' => null,
                'height' => null,
                'width' => null,
                'depth' => null,
                'volume_m3' => $autoWasteVolume,
                'notes' => 'Chute automatique des produits défectueux',
                'is_recovered' => true,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            $recyclableVolume += $autoWasteVolume;
            $recyclableCount++;

            Log::info('Auto-defective waste created', [
                'volume' => $autoWasteVolume,
                'recyclable_volume_after' => $recyclableVolume
            ]);
        }

        // MANUAL WASTES: These are additional chutes from cutting/processing
        if (is_array($wastes) && count($wastes) > 0) {
            foreach ($wastes as $index => $wasteData) {
                $wasteType = $wasteData['waste_type'] ?? 'recyclable';
                $volume = floatval($wasteData['volume_m3'] ?? 0);
                $height = $wasteData['height'] ?? null;
                $width = $wasteData['width'] ?? null;
                $depth = $wasteData['depth'] ?? null;
                $wasteSource = $wasteData['waste_source'] ?? 'Découpage';
                $wasteCategory = $wasteData['waste_category'] ?? null;
                $notes = $wasteData['notes'] ?? null;

                Log::info('Processing manual waste', [
                    'index' => $index,
                    'type' => $wasteType,
                    'volume' => $volume,
                    'source' => $wasteSource
                ]);

                if ($wasteType === 'recyclable') {
                    // Recyclable waste from cutting
                    if ($volume > 0) {
                        $recyclableVolume += $volume;
                        $recyclableCount++;

                        if (!$chuteMaterial) {
                            $chuteMaterial = $this->getOrCreateChuteMaterial();
                        }

                        ProductionWaste::create([
                            'production_output_id' => $output->output_id,
                            'material_id' => $chuteMaterial->material_id,
                            'waste_type' => 'recyclable',
                            'waste_source' => $wasteSource,
                            'waste_category' => null,
                            'height' => $height,
                            'width' => $width,
                            'depth' => $depth,
                            'quantity' => 1,
                            'volume_m3' => $volume,
                            'notes' => $notes . ($height && $width && $depth ?
                                ' | Dimensions: ' . $height . 'm × ' . $width . 'm × ' . $depth . 'm' : ''),
                            'is_recovered' => true,
                            'created_by' => auth()->id(),
                            'created_at' => now(),
                        ]);

                        Log::info('Recyclable waste created', [
                            'volume' => $volume,
                            'recyclable_volume_after' => $recyclableVolume
                        ]);
                    }
                } else {
                    // Non-recyclable waste
                    if ($volume > 0) {
                        $wasteVolume += $volume;
                        $wasteCount++;

                        $finalCategory = $wasteCategory ?: 'Non spécifié';

                        ProductionWaste::create([
                            'production_output_id' => $output->output_id,
                            'material_id' => null,
                            'waste_type' => 'waste',
                            'waste_source' => $wasteSource,
                            'waste_category' => $finalCategory,
                            'height' => $height,
                            'width' => $width,
                            'depth' => $depth,
                            'quantity' => 1,
                            'volume_m3' => $volume,
                            'notes' => ($finalCategory ? "Catégorie: {$finalCategory} | " : '') .
                                    ($notes ?: '') .
                                    ($height && $width && $depth ?
                                        ' | Dimensions: ' . $height . 'm × ' . $width . 'm × ' . $depth . 'm' : ''),
                            'is_recovered' => false,
                            'created_by' => auth()->id(),
                            'created_at' => now(),
                        ]);

                        Log::info('Non-recyclable waste created', [
                            'volume' => $volume,
                            'waste_volume_after' => $wasteVolume
                        ]);
                    }
                }
            }
        }

        $totalWasteVolume = $recyclableVolume + $wasteVolume;

        Log::info('Waste calculation summary', [
            'auto_defective_volume' => $autoWasteVolume,
            'manual_recyclable_volume' => $recyclableVolume - $autoWasteVolume,
            'total_recyclable_volume' => $recyclableVolume,
            'non_recyclable_volume' => $wasteVolume,
            'total_waste_volume' => $totalWasteVolume,
            'recyclable_count' => $recyclableCount,
            'waste_count' => $wasteCount
        ]);

        // Add recyclable waste to raw material stock (for reuse)
        if ($recyclableVolume > 0 && $chuteMaterial) {
            $this->addWasteToRawMaterialStock(
                $chuteMaterial->material_id,
                $recyclableVolume,
                $order,
                $output,
                'Chutes de Production Recyclables'
            );

            Log::info('Added recyclable waste to stock', [
                'material_id' => $chuteMaterial->material_id,
                'volume' => $recyclableVolume
            ]);
        }

        Log::info('=== HANDLE PRODUCTION WASTES END ===');

        return [
            'recyclable_volume' => $recyclableVolume,    // Auto-defective + manual recyclable
            'pure_waste_volume' => $wasteVolume,         // Non-recyclable waste
            'total_volume' => $totalWasteVolume,         // Total waste volume
            'recyclable_count' => $recyclableCount,
            'waste_count' => $wasteCount,
            'auto_defective_volume' => $autoWasteVolume, // Just the defective products volume
            'has_waste_declaration' => $recyclableCount > 0 || $wasteCount > 0,
        ];
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

    // Simplify the addWasteToRawMaterialStock method
    private function addWasteToRawMaterialStock($materialId, $volume, ProductionOrder $order, ProductionOutput $output, $materialName)
    {
        $material = RawMaterial::find($materialId);
        if (!$material) {
            Log::error('Material not found for waste', ['material_id' => $materialId]);
            return null;
        }

        $previousStock = $material->current_stock ?? 0;
        $newStock = $previousStock + $volume;

        // Create stock movement
        $movementId = DB::table('raw_material_stock_movements')->insertGetId([
            'material_id' => $materialId,
            'movement_type' => 'waste_recovery',
            'quantity' => $volume,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'reference_type' => 'production_output',
            'reference_id' => $output->output_id,
            'reference_number' => $order->order_number,
            'movement_date' => now(),
            'performed_by' => auth()->id(),
            'notes' => "Chutes de production: {$volume} m³ (Commande: {$order->order_number})",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!$movementId) {
            Log::error('Failed to create stock movement');
            return null;
        }

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

        // Update material stock
        $material->current_stock = $newStock;
        $material->save();

        return $movementId;
    }

    /**
     * Calculate unit price for waste material
     */
    private function calculateWasteUnitPrice($material, $volume)
    {
        // For waste materials, use a nominal value or calculate based on original material
        // You can adjust this logic based on your business rules

        // Default value per cubic meter
        $defaultValuePerM3 = 50.00; // Example: 50€ per m³ for recyclable waste

        // Check if this is a sub-material of something
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

    /**
     * Consume raw material stock using FIFO
     */
    private function consumeRawMaterialStockFIFO($materialId, $quantityNeeded, $productionOrder, $details = [])
    {
        $stockDetails = StockMovementDetail::where('material_id', $materialId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('stock_movement_id', 'asc')
            ->get();

        $remainingToConsume = $quantityNeeded;
        $totalCost = 0;
        $consumedDetails = [];

        foreach ($stockDetails as $detail) {
            if ($remainingToConsume <= 0) break;

            $availableQuantity = $detail->remaining_quantity;
            $quantityToTake = min($availableQuantity, $remainingToConsume);

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

        $material = RawMaterial::find($materialId);
        $previousStock = $material->current_stock;
        $newStock = $previousStock - $quantityNeeded;

        $averageUnitCost = 0;
        if (count($consumedDetails) > 0) {
            $totalConsumedCost = array_sum(array_column($consumedDetails, 'total_cost'));
            $totalConsumedQty = $quantityNeeded - $remainingToConsume;
            $averageUnitCost = $totalConsumedQty > 0 ? $totalConsumedCost / $totalConsumedQty : 0;
        } else {
            $averageUnitCost = $material->average_unit_cost ?? 0;
        }

        if ($remainingToConsume > 0) {
            Log::warning("Stock insuffisant pour la matière première {$material->material_name} (ID: {$materialId}). " .
                "Stock actuel: {$previousStock}, Requis: {$quantityNeeded}, Manque: {$remainingToConsume}");

            $totalCost += $remainingToConsume * $averageUnitCost;
        }

        $stockMovement = RawMaterialStockMovement::create([
            'material_id' => $materialId,
            'movement_type' => 'production_consumption',
            'quantity' => -$quantityNeeded,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'reference_type' => 'production_output',
            'reference_number' => $productionOrder->order_number,
            'movement_date' => now(),
            'performed_by' => auth()->id(),
            'notes' => $details['notes'] ?? 'Consommation pour production' . ($remainingToConsume > 0 ? ' (Stock négatif autorisé)' : ''),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

        if ($remainingToConsume > 0) {
            $negativeStockDetail = StockMovementDetail::create([
                'stock_movement_id' => $stockMovement->movement_id,
                'material_id' => $materialId,
                'quantity' => -$remainingToConsume,
                'unit_price' => $averageUnitCost,
                'total_price' => -($remainingToConsume * $averageUnitCost),
                'remaining_quantity' => -$remainingToConsume,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Created negative stock movement detail', [
                'material_id' => $materialId,
                'stock_movement_id' => $stockMovement->movement_id,
                'stock_detail_id' => $negativeStockDetail->stock_detail_id,
                'negative_quantity' => -$remainingToConsume
            ]);
        }

        $material->current_stock = $newStock;
        $material->save();

        Log::info('Stock consumption completed', [
            'material_id' => $materialId,
            'material_name' => $material->material_name,
            'quantity_consumed' => $quantityNeeded,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'was_negative' => $newStock < 0,
            'negative_quantity' => $newStock < 0 ? abs($newStock) : 0
        ]);

        return $stockMovement;
    }

    /**
     * Consume source famille stock for type2 and type3 production
     */
    private function consumeSourceFamilleStock($sourceProductId, $sourceFamilleId, $quantityToConsume, $productionOrder, $processType)
    {
        Log::info('Attempting to consume source famille stock', [
            'source_product_id' => $sourceProductId,
            'source_famille_id' => $sourceFamilleId,
            'quantity' => $quantityToConsume,
            'process_type' => $processType,
            'order_id' => $productionOrder->order_id
        ]);

        // First, ensure the product has this famille association
        $sourceProduct = Product::find($sourceProductId);
        if (!$sourceProduct) {
            throw new \Exception("Produit source ID {$sourceProductId} non trouvé");
        }

        // Check if the product has this famille, create if not
        $productHasFamille = $sourceProduct->familles()
            ->where('familles.famille_id', $sourceFamilleId)
            ->exists();

        if (!$productHasFamille) {
            Log::info("Creating famille association for source product", [
                'product_id' => $sourceProductId,
                'famille_id' => $sourceFamilleId
            ]);

            // Get the famille
            $famille = Famille::find($sourceFamilleId);
            if (!$famille) {
                throw new \Exception("Famille ID {$sourceFamilleId} non trouvée");
            }

            // Associate the product with the famille
            $sourceProduct->familles()->attach($sourceFamilleId, [
                'quantity_per_unit' => 1,
                'sort_order' => $sourceProduct->familles()->count() + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Now get or create the stock record
        $sourceFamilleStock = ProductFamilleStock::firstOrCreate(
            [
                'product_id' => $sourceProductId,
                'famille_id' => $sourceFamilleId,
            ],
            [
                'famille_name' => Famille::find($sourceFamilleId)->famille_name ?? 'Famille',
                'current_quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'location' => 'Entrepôt Principal',
                'last_restocked' => now(),
                'created_at' => now(),
            ]
        );

        Log::info('Source famille stock record', [
            'exists' => $sourceFamilleStock->exists,
            'famille_stock_id' => $sourceFamilleStock->famille_stock_id,
            'current_quantity' => $sourceFamilleStock->current_quantity,
            'reserved_quantity' => $sourceFamilleStock->reserved_quantity,
            'available_quantity' => $sourceFamilleStock->available_quantity
        ]);

        $previousStock = $sourceFamilleStock->current_quantity;
        $sourceFamilleStock->current_quantity -= $quantityToConsume;
        $sourceFamilleStock->available_quantity = $sourceFamilleStock->current_quantity - $sourceFamilleStock->reserved_quantity;
        $sourceFamilleStock->save();

        Log::info('Stock consumed successfully', [
            'previous_stock' => $previousStock,
            'new_stock' => $sourceFamilleStock->current_quantity,
            'new_available' => $sourceFamilleStock->available_quantity
        ]);

        ProductStockMovement::create([
            'product_id' => $sourceProductId,
            'famille_id' => $sourceFamilleId,
            'movement_type' => $processType . '_consumption',
            'quantity' => -$quantityToConsume,
            'previous_stock' => $previousStock,
            'new_stock' => $sourceFamilleStock->current_quantity,
            'reference_type' => 'production_output',
            'reference_number' => $productionOrder->order_number,
            'movement_date' => now(),
            'performed_by' => auth()->id(),
            'notes' => 'Consommation pour ' . $processType . ' - ' . $productionOrder->order_number,
            'created_at' => now(),
        ]);

        return true;
    }

    /**
     * Synchronize product with famille if not already associated
     */
    private function syncProductFamille(Product $product, Famille $famille)
    {
        if (!$product->familles()->where('familles.famille_id', $famille->famille_id)->exists()) {
            $product->familles()->attach($famille->famille_id, [
                'quantity_per_unit' => 1,
                'sort_order' => $product->familles()->count() + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        ProductFamilleStock::firstOrCreate(
            [
                'product_id' => $product->product_id,
                'famille_id' => $famille->famille_id,
            ],
            [
                'famille_name' => $famille->famille_name,
                'current_quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'location' => 'Entrepôt Principal',
                'last_restocked' => null,
                'created_at' => now(),
            ]
        );
    }

    /**
     * Add products to famille stock with volume
     */
    private function addToFamilleStock(Product $product, Famille $famille, $quantity, ProductionOutput $output, $totalVolume = null)
    {
        $this->syncProductFamille($product, $famille);

        $familleStock = ProductFamilleStock::where('product_id', $product->product_id)
            ->where('famille_id', $famille->famille_id)
            ->first();


        if (!$familleStock) {
            $familleStock = ProductFamilleStock::create([
                'product_id' => $product->product_id,
                'famille_id' => $famille->famille_id,
                'famille_name' => $famille->famille_name,
                'current_quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'location' => 'Entrepôt Principal',
                'last_restocked' => now(),
                'created_at' => now(),
            ]);
        }

        $previousStock = $familleStock->current_quantity;
        $familleStock->current_quantity += $quantity;
        $familleStock->available_quantity = $familleStock->current_quantity - $familleStock->reserved_quantity;
        $familleStock->last_restocked = now();
        $familleStock->save();

        ProductStockMovement::create([
            'product_id' => $product->product_id,
            'famille_id' => $famille->famille_id,
            'famille_name' => $famille->famille_name,
            'movement_type' => 'production_output',
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $familleStock->current_quantity,
            'reference_type' => 'production_output',
            'reference_id' => $output->output_id,
            'reference_number' => $output->productionOrder->order_number,
            'movement_date' => now(),
            'performed_by' => auth()->id(),
            'notes' => 'Sortie de production ' . $output->productionOrder->order_number .
                     ' - Famille: ' . $famille->famille_name .
                     ($totalVolume ? ' - Volume: ' . number_format($totalVolume, 4) . ' m³' : ''),
            'created_at' => now(),
        ]);
    }

    private function checkAndUpdateOrderCompletion(ProductionOrder $order)
    {
        $totalTargetProduced = $order->outputs
            ->where('famille_id', $order->famille_id)
            ->sum('quantity_produced');

        $totalAllProduced = $order->outputs->sum('quantity_produced');

        $targetReached = $totalTargetProduced >= $order->quantity_to_produce;

        if ($targetReached) {
            ProductionOutput::where('production_order_id', $order->order_id)
                ->where('famille_id', $order->famille_id)
                ->update(['is_final_output' => true]);
        }

        if ($targetReached) {
            $order->update([
                'status' => 'completed',
                'actual_completion_date' => now(),
                'completed_by' => auth()->id(),
            ]);
        } else {
            $order->update([
                'status' => 'in_progress',
            ]);
        }

        return [
            'target_reached' => $targetReached,
            'target_produced' => $totalTargetProduced,
            'total_produced' => $totalAllProduced,
            'remaining' => $order->quantity_to_produce - $totalTargetProduced,
        ];
    }

    public function show($id)
    {
        $output = ProductionOutput::with([
            'productionOrder.product',
            'productionOrder.wastes.rawMaterial',
            'productionOrder.consumptions.rawMaterial',
            'product',
            'famille',
            'approver',
        ])->findOrFail($id);

        $productionSummary = $this->getProductionSummaryData($output->production_order_id);

        $defectRate = $output->quantity_produced > 0 ?
            ($output->quantity_defective / $output->quantity_produced) * 100 : 0;

        $goodQuantity = $output->quantity_produced - $output->quantity_defective;

        $unitVolume = $output->unit_volume_m3 ?? ($output->product->total_volume ?? 0);
        $goodVolume = $unitVolume * $goodQuantity;
        $wasteVolume = $unitVolume * $output->quantity_defective;

        $wastes = $output->productionOrder ? $output->productionOrder->wastes : collect();

        return view('pages.production-output.show', compact(
            'output',
            'productionSummary',
            'defectRate',
            'goodQuantity',
            'unitVolume',
            'goodVolume',
            'wasteVolume',
            'wastes'
        ));
    }

    private function getProductionSummaryData($orderId)
    {
        $order = ProductionOrder::with(['outputs.famille', 'famille'])->findOrFail($orderId);

        $summary = [
            'order' => $order,
            'target_famille' => $order->famille,
            'target_quantity' => $order->quantity_to_produce,
            'by_famille' => [],
            'total_produced' => 0,
            'target_produced' => 0,
            'remaining' => 0,
            'total_volume' => 0,
            'target_volume' => 0,
        ];

        $familleGroups = $order->outputs->groupBy('famille_id');

        foreach ($familleGroups as $familleId => $outputs) {
            $famille = $outputs->first()->famille;
            $totalProduced = $outputs->sum('quantity_produced');
            $totalDefective = $outputs->sum('quantity_defective');
            $totalVolume = $outputs->sum('total_volume_m3');
            $wasteVolume = $outputs->sum('waste_volume_m3');
            $goodQuantity = $totalProduced - $totalDefective;
            $goodVolume = $totalVolume - $wasteVolume;

            $summary['by_famille'][] = [
                'famille' => $famille,
                'total_produced' => $totalProduced,
                'total_defective' => $totalDefective,
                'good_quantity' => $goodQuantity,
                'total_volume' => $totalVolume,
                'waste_volume' => $wasteVolume,
                'good_volume' => $goodVolume,
                'is_target' => ($familleId == $order->famille_id),
            ];

            $summary['total_produced'] += $totalProduced;
            $summary['total_volume'] += $totalVolume;

            if ($familleId == $order->famille_id) {
                $summary['target_produced'] = $totalProduced;
                $summary['target_volume'] = $totalVolume;
            }
        }

        $summary['remaining'] = $order->quantity_to_produce - $summary['target_produced'];

        return $summary;
    }

    public function edit($id)
    {
        $output = ProductionOutput::with([
            'productionOrder.product',
            'product',
            'famille',
            'productionOrder.outputs'
        ])->findOrFail($id);

        if ($output->productionOrder->status === 'completed') {
            return redirect()->route('production-output.show', $id)
                ->with('error', 'Impossible de modifier une sortie pour un ordre terminé');
        }

        $availableFamilles = collect();
        if ($output->product->has_familles) {
            $availableFamilles = Famille::whereHas('products', function($query) use ($output) {
                    $query->where('products.product_id', $output->product_id);
                })
                ->where('is_active', true)
                ->orderBy('famille_name')
                ->get();
        }

        return view('pages.production-output.edit', compact('output', 'availableFamilles'));
    }

    public function update(Request $request, $id)
    {
        $output = ProductionOutput::with(['productionOrder', 'product', 'famille'])->findOrFail($id);

        if ($output->productionOrder->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de modifier une sortie pour un ordre terminé'
            ], 400);
        }

        $request->validate([
            'famille_id' => 'required|exists:familles,famille_id',
            'quantity_produced' => 'required|numeric|min:0.01',
            'quantity_defective' => 'required|numeric|min:0',
            'production_date' => 'required|date|before_or_equal:today',
            'total_volume_m3' => 'nullable|numeric|min:0',
            'waste_volume_m3' => 'nullable|numeric|min:0',
            'unit_volume_m3' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $famille = Famille::findOrFail($request->famille_id);

            if ($output->product->has_familles) {
                $productHasFamille = $output->product->familles()->where('familles.famille_id', $famille->famille_id)->exists();
                if (!$productHasFamille) {
                    throw new \Exception('Cette famille n\'appartient pas au produit');
                }
            }

            if ($request->quantity_defective > $request->quantity_produced) {
                throw new \Exception('La quantité défectueuse ne peut pas dépasser la quantité produite');
            }

            // Calculate old values
            $oldGoodQuantity = $output->quantity_produced - $output->quantity_defective;
            $oldUnitVolume = $output->unit_volume_m3 ?? 0;
            $oldTotalVolume = $output->total_volume_m3 ?? 0;
            $oldAutoWasteVolume = $oldUnitVolume * $output->quantity_defective;

            // Calculate new values
            $newGoodQuantity = $request->quantity_produced - $request->quantity_defective;
            $unitVolume = $request->unit_volume_m3 ?? $output->product->getVolumePerUnitInM3() ?? $oldUnitVolume;
            $totalVolume = $request->total_volume_m3 ?? ($unitVolume * $request->quantity_produced);
            $newAutoWasteVolume = $unitVolume * $request->quantity_defective;

            // Update waste volumes - preserve manual waste, update auto waste
            $existingWastes = ProductionWaste::where('production_output_id', $output->output_id)->get();
            $manualRecyclableVolume = $existingWastes->where('waste_type', 'recyclable')->sum('volume_m3');
            $manualWasteVolume = $existingWastes->where('waste_type', 'waste')->sum('volume_m3');

            $newTotalWasteVolume = $newAutoWasteVolume + $manualRecyclableVolume + $manualWasteVolume;

            Log::info('Updating output waste calculation', [
                'old_auto' => $oldAutoWasteVolume,
                'new_auto' => $newAutoWasteVolume,
                'manual_recyclable' => $manualRecyclableVolume,
                'manual_waste' => $manualWasteVolume,
                'new_total' => $newTotalWasteVolume
            ]);

            // Handle famille stock adjustments
            if ($output->famille_id != $request->famille_id) {
                // Remove from old famille
                $this->removeFromFamilleStock($output->product, $output->famille_id, $oldGoodQuantity, $output);

                // Add to new famille
                $this->addToFamilleStock($output->product, $famille, $newGoodQuantity, $output, $totalVolume);
            } else {
                // Adjust quantity in same famille
                $quantityDifference = $newGoodQuantity - $oldGoodQuantity;
                if ($quantityDifference != 0) {
                    $this->adjustFamilleStock($output->product, $famille, $quantityDifference, $output);
                }
            }

            // Determine output type
            $outputType = $output->output_type;
            if ($output->productionOrder->famille_id == $request->famille_id) {
                $outputType = 'type1';
            } else {
                $outputType = 'mixed_family';
            }

            // Update the output
            $output->update([
                'famille_id' => $request->famille_id,
                'famille_name' => $famille->famille_name,
                'output_type' => $outputType,
                'quantity_produced' => $request->quantity_produced,
                'quantity_defective' => $request->quantity_defective,
                'total_volume_m3' => $totalVolume,
                'waste_volume_m3' => $newTotalWasteVolume,
                'unit_volume_m3' => $unitVolume,
                'production_date' => $request->production_date,
                'notes' => $request->notes,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            // Update auto-defective waste records
            $autoWaste = $existingWastes->where('waste_type', 'waste')->first();
            if ($autoWaste) {
                if ($newAutoWasteVolume > 0) {
                    $autoWaste->update([
                        'volume_m3' => $newAutoWasteVolume,
                        'notes' => 'Chute automatique des produits défectueux (mise à jour)'
                    ]);
                } else {
                    $autoWaste->delete();
                }
            } else if ($newAutoWasteVolume > 0) {
                // Create new auto-defective waste if it doesn't exist
                $chuteMaterial = $this->getOrCreateChuteMaterial();
                ProductionWaste::create([
                    'production_output_id' => $output->output_id,
                    'material_id' => $chuteMaterial->material_id,
                    'waste_type' => 'waste',
                    'waste_source' => 'Défauts de production',
                    'waste_category' => null,
                    'height' => null,
                    'width' => null,
                    'depth' => null,
                    'volume_m3' => $newAutoWasteVolume,
                    'notes' => 'Chute automatique des produits défectueux',
                    'is_recovered' => true,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            // Recalculate waste totals
            $updatedWastes = ProductionWaste::where('production_output_id', $output->output_id)->get();
            $recyclableVolume = $updatedWastes->whereIn('waste_type', ['waste', 'recyclable'])->sum('volume_m3');
            $pureWasteVolume = $updatedWastes->where('waste_type', 'waste')->sum('volume_m3');

            $output->update([
                'recyclable_waste_volume' => $recyclableVolume,
                'pure_waste_volume' => $pureWasteVolume,
            ]);

            // Update order completion status
            $this->checkAndUpdateOrderCompletion($output->productionOrder);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sortie de production mise à jour avec succès!',
                'output' => [
                    'id' => $output->output_id,
                    'total_volume' => $totalVolume,
                    'auto_waste_volume' => $newAutoWasteVolume,
                    'recyclable_volume' => $recyclableVolume,
                    'pure_waste_volume' => $pureWasteVolume,
                    'total_waste_volume' => $newTotalWasteVolume
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Production output update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    private function removeFromFamilleStock(Product $product, $familleId, $quantity, ProductionOutput $output)
    {
        $familleStock = ProductFamilleStock::where('product_id', $product->product_id)
            ->where('famille_id', $familleId)
            ->first();

        if ($familleStock) {
            if ($familleStock->current_quantity < $quantity) {
                throw new \Exception('Stock insuffisant pour retirer cette quantité');
            }

            $previousStock = $familleStock->current_quantity;
            $familleStock->current_quantity -= $quantity;
            $familleStock->available_quantity = $familleStock->current_quantity - $familleStock->reserved_quantity;
            $familleStock->save();

            ProductStockMovement::create([
                'product_id' => $product->product_id,
                'famille_id' => $familleId,
                'movement_type' => 'production_adjustment',
                'quantity' => -$quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $familleStock->current_quantity,
                'reference_type' => 'production_output',
                'reference_id' => $output->output_id,
                'reference_number' => $output->productionOrder->order_number,
                'movement_date' => now(),
                'performed_by' => auth()->id(),
                'notes' => 'Ajustement sortie de production - Retrait',
            ]);
        }
    }

    private function adjustFamilleStock(Product $product, Famille $famille, $quantityDifference, ProductionOutput $output)
    {
        $familleStock = ProductFamilleStock::firstOrCreate(
            [
                'product_id' => $product->product_id,
                'famille_id' => $famille->famille_id,
            ],
            [
                'famille_name' => $famille->famille_name,
                'current_quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'location' => 'Entrepôt Principal',
                'last_restocked' => now(),
            ]
        );

        if ($quantityDifference < 0 && abs($quantityDifference) > $familleStock->current_quantity) {
            throw new \Exception('Stock insuffisant pour cet ajustement');
        }

        $previousStock = $familleStock->current_quantity;
        $familleStock->current_quantity += $quantityDifference;
        $familleStock->available_quantity = $familleStock->current_quantity - $familleStock->reserved_quantity;
        $familleStock->save();

        ProductStockMovement::create([
            'product_id' => $product->product_id,
            'famille_id' => $famille->famille_id,
            'famille_name' => $famille->famille_name,
            'movement_type' => 'production_adjustment',
            'quantity' => $quantityDifference,
            'previous_stock' => $previousStock,
            'new_stock' => $familleStock->current_quantity,
            'reference_type' => 'production_output',
            'reference_id' => $output->output_id,
            'reference_number' => $output->productionOrder->order_number,
            'movement_date' => now(),
            'performed_by' => auth()->id(),
            'notes' => 'Ajustement quantité sortie de production',
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $output = ProductionOutput::with(['productionOrder', 'product', 'famille'])->findOrFail($id);

            // Check if output can be deleted
            if ($output->productionOrder->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer une sortie pour un ordre terminé'
                ], 400);
            }

            // Remove good products from stock
            $goodQuantity = $output->quantity_produced - $output->quantity_defective;
            if ($goodQuantity > 0 && $output->famille_id) {
                $this->removeFromFamilleStock($output->product, $output->famille_id, $goodQuantity, $output);
            }

            // Delete the output
            $output->delete();

            // Check and update order completion
            $this->checkAndUpdateOrderCompletion($output->productionOrder);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sortie de production supprimée avec succès!'
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
     * Get consumptions for a production order
     */
    public function getOrderConsumptions($orderId)
    {
        try {
            $consumptions = ProductionConsumption::with('rawMaterial')
                ->where('production_order_id', $orderId)
                ->get();

            $productionOrder = ProductionOrder::findOrFail($orderId);
            $bomItems = BillOfMaterial::where('product_id', $productionOrder->product_id)->get();

            if ($consumptions->isEmpty() && $bomItems->isNotEmpty()) {
                foreach ($bomItems as $bomItem) {
                    $plannedQuantity = $bomItem->quantity_required * $productionOrder->quantity_to_produce;

                    $consumption = ProductionConsumption::create([
                        'production_order_id' => $orderId,
                        'material_id' => $bomItem->material_id,
                        'planned_quantity' => $plannedQuantity,
                        'actual_quantity_used' => 0,
                        'waste_quantity' => 0,
                        'unit_cost' => $bomItem->rawMaterial->average_unit_cost ?? 0,
                        'total_cost' => 0,
                        'notes' => null,
                    ]);
                    $consumptions->push($consumption);
                }
                $consumptions = ProductionConsumption::with('rawMaterial')
                    ->where('production_order_id', $orderId)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'consumptions' => $consumptions,
                'product_id' => $productionOrder->product_id,
                'quantity_to_produce' => $productionOrder->quantity_to_produce
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save consumptions for a production order
     */
    public function saveConsumptions(Request $request, $orderId)
    {
        try {
            $request->validate([
                'consumptions' => 'required|array',
                'consumptions.*.material_id' => 'required|exists:raw_materials,material_id',
                'consumptions.*.actual_quantity_used' => 'required|numeric|min:0',
                'consumptions.*.notes' => 'nullable|string|max:500',
                'global_notes' => 'nullable|string|max:1000',
            ]);

            $productionOrder = ProductionOrder::findOrFail($orderId);
            $hasErrors = false;
            $errors = [];
            $consumptionsToDeduct = [];

            foreach ($request->consumptions as $consumptionData) {
                $consumption = ProductionConsumption::where('production_order_id', $orderId)
                    ->where('material_id', $consumptionData['material_id'])
                    ->first();

                if (!$consumption) {
                    continue;
                }

                $actualQuantity = floatval($consumptionData['actual_quantity_used']);
                $plannedQuantity = floatval($consumption->planned_quantity);

                if ($plannedQuantity > 0) {
                    $difference = abs($actualQuantity - $plannedQuantity);
                    $percentage = ($difference / $plannedQuantity) * 100;

                    if ($percentage > 1) {
                        $hasErrors = true;
                        $material = RawMaterial::find($consumptionData['material_id']);
                        $errors[] = [
                            'material_name' => $material->material_name,
                            'planned' => $plannedQuantity,
                            'actual' => $actualQuantity,
                            'percentage' => round($percentage, 2)
                        ];
                    }
                }

                // Calculate how much stock still needs to be consumed (delta from already-consumed)
                $alreadyConsumed = floatval($consumption->stock_consumed_quantity ?? 0);
                $delta = $actualQuantity - $alreadyConsumed;

                $consumption->update([
                    'actual_quantity_used' => $actualQuantity,
                    'total_cost' => $actualQuantity * $consumption->unit_cost,
                    'notes' => $consumptionData['notes'] ?? null,
                    'updated_at' => now(),
                ]);

                // Queue FIFO deduction for this material (only positive deltas)
                if ($delta > 0.0001) {
                    $consumptionsToDeduct[] = [
                        'consumption' => $consumption,
                        'material_id' => $consumptionData['material_id'],
                        'delta' => $delta,
                        'new_total' => $actualQuantity,
                    ];
                }
            }

            if ($hasErrors) {
                return response()->json([
                    'success' => false,
                    'type' => 'validation_error',
                    'errors' => $errors,
                    'message' => 'Certaines consommations dépassent la tolérance de 1%'
                ], 422);
            }

            // Apply FIFO stock deductions after validation passes
            foreach ($consumptionsToDeduct as $item) {
                try {
                    $this->consumeRawMaterialStockFIFO(
                        $item['material_id'],
                        $item['delta'],
                        $productionOrder,
                        ['notes' => 'Consommation déclarée - Commande #' . $productionOrder->order_number]
                    );

                    $item['consumption']->update([
                        'is_stock_consumed' => true,
                        'stock_consumed_quantity' => $item['new_total'],
                    ]);
                } catch (\Exception $e) {
                    Log::warning('FIFO stock deduction failed for consumption declaration', [
                        'order_id' => $orderId,
                        'material_id' => $item['material_id'],
                        'delta' => $item['delta'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($request->has('global_notes')) {
                $productionOrder->update([
                    'consumption_notes' => $request->global_notes
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Consommations enregistrées et stock mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrderDetails($orderId)
    {
        try {
            $order = ProductionOrder::with(['product', 'outputs.famille', 'famille'])
                ->where('status', 'in_progress')
                ->findOrFail($orderId);

            // Calculate production by famille
            $productionByFamille = [];
            $totalTargetProduced = 0;
            $totalAllProduced = 0;

            foreach ($order->outputs->groupBy('famille_id') as $familleId => $outputs) {
                $famille = $outputs->first()->famille;
                $totalProduced = $outputs->sum('quantity_produced');
                $isTarget = ($familleId == $order->famille_id);

                $productionByFamille[] = [
                    'famille_id' => $familleId,
                    'famille_name' => $famille->famille_name,
                    'total_produced' => $totalProduced,
                    'is_target' => $isTarget,
                ];

                $totalAllProduced += $totalProduced;
                if ($isTarget) {
                    $totalTargetProduced = $totalProduced;
                }
            }

            // Get available familles for this product
            $availableFamilles = [];
            if ($order->product->has_familles) {
                $availableFamilles = Famille::whereHas('products', function($query) use ($order) {
                        $query->where('products.product_id', $order->product_id);
                    })
                    ->where('is_active', true)
                    ->orderBy('famille_name')
                    ->get()
                    ->map(function($famille) {
                        return [
                            'id' => $famille->famille_id,
                            'name' => $famille->famille_name,
                            'code' => $famille->famille_code,
                        ];
                    });
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => [
                        'id' => $order->order_id,
                        'number' => $order->order_number,
                        'product_id' => $order->product_id,
                        'product_name' => $order->product->product_name,
                        'product_type' => $order->product->product_type,
                        'has_familles' => $order->product->has_familles,
                        'target_famille_id' => $order->famille_id,
                        'target_famille_name' => $order->famille ? $order->famille->famille_name : null,
                        'planned_quantity' => $order->quantity_to_produce,
                        'target_produced' => $totalTargetProduced,
                        'total_produced' => $totalAllProduced,
                        'remaining' => $order->quantity_to_produce - $totalTargetProduced,
                    ],
                    'production_by_famille' => $productionByFamille,
                    'available_familles' => $availableFamilles,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ordre non trouvé ou erreur de chargement'
            ], 404);
        }
    }

    public function getProductionSummary($orderId)
    {
        try {
            $summary = $this->getProductionSummaryData($orderId);

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du résumé'
            ], 500);
        }
    }

    /**
     * Get source product stock for a specific famille
     */
    public function getSourceStock($productId, $familleId)
    {
        try {
            $product = Product::findOrFail($productId);

            // Check if product has this famille, create if not
            $productHasFamille = $product->familles()
                ->where('familles.famille_id', $familleId)
                ->exists();

            if (!$productHasFamille) {
                $famille = Famille::find($familleId);
                if ($famille) {
                    $product->familles()->attach($familleId, [
                        'quantity_per_unit' => 1,
                        'sort_order' => $product->familles()->count() + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Get or create stock record
            $stock = ProductFamilleStock::firstOrCreate(
                [
                    'product_id' => $productId,
                    'famille_id' => $familleId,
                ],
                [
                    'famille_name' => Famille::find($familleId)->famille_name ?? 'Famille',
                    'current_quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                    'location' => 'Entrepôt Principal',
                    'last_restocked' => now(),
                    'created_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'available_quantity' => $stock->available_quantity,
                'current_quantity' => $stock->current_quantity,
                'reserved_quantity' => $stock->reserved_quantity,
                'famille_name' => $stock->famille_name,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting source stock', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'famille_id' => $familleId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $outputs = ProductionOutput::with(['productionOrder', 'product', 'famille', 'approver'])
            ->when($request->filled('date_from'), function($query) use ($request) {
                $query->whereDate('production_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function($query) use ($request) {
                $query->whereDate('production_date', '<=', $request->date_to);
            })
            ->when($request->filled('order_id'), function($query) use ($request) {
                $query->where('production_order_id', $request->order_id);
            })
            ->when($request->filled('famille_id'), function($query) use ($request) {
                $query->where('famille_id', $request->famille_id);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $outputs,
            'total' => $outputs->count()
        ]);
    }
}
