<?php

namespace App\Http\Controllers;

use App\Models\BillOfMaterial;
use App\Models\Famille;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\ProductFamilleStock;
use App\Models\ProductStockMovement;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Exports\ProductsFamilyStockExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;


class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_products')->only(['index', 'show', 'getStatistics', 'getStockInfo', 'checkLowStock', 'getFamilleStockDetails', 'getFamillesForProduct', 'getProductionTime']);
        $this->middleware('can:create_products')->only(['create', 'store']);
        $this->middleware('can:edit_products')->only(['edit', 'update', 'toggleFamilles', 'updateFamilyPrices']);
        $this->middleware('can:delete_products')->only(['destroy']);
        $this->middleware('can:manage_product_stock')->only(['addStock']);
        $this->middleware('can:export_products')->only(['exportExcel', 'exportPdf']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $products = Product::with(['stock', 'familles', 'familleStocks'])
                ->select('products.*');

            // Apply filters
            if ($request->filled('product_type')) {
                $products->where('product_type', $request->product_type);
            }

            if ($request->filled('is_active')) {
                $products->where('is_active', $request->is_active == '1');
            }

            if ($request->filled('search') && !empty($request->search['value'])) {
                $searchTerm = $request->search['value'];
                $products->where(function($query) use ($searchTerm) {
                    $query->where('product_code', 'like', '%' . $searchTerm . '%')
                        ->orWhere('product_name', 'like', '%' . $searchTerm . '%');
                });
            }

            return DataTables::of($products)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $user = auth()->user();
                    $dropdown = '<div class="dropdown dropstart">
                        <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

                    if ($user->can('manage_product_stock')) {
                        $dropdown .= '<li>
                                <button class="dropdown-item d-flex align-items-center gap-3 btn-add-stock"
                                        data-product-id="'.$row->product_id.'"
                                        data-product-name="'.$row->product_name.'"
                                        data-product-code="'.$row->product_code.'"
                                        data-has-familles="'.($row->familles->count() > 0 ? 1 : 0).'"
                                        data-current-stock="'.$row->total_stock.'"
                                        data-unit="'.$row->unit_of_measure.'">
                                    <i class="fs-4 ti ti-plus text-success"></i><span class="text-success">Ajouter Stock</span>
                                </button>
                            </li>';
                    }

                    $dropdown .= '<li>
                                <button class="dropdown-item d-flex align-items-center gap-3 btn-view-prices"
                                        data-product-id="'.$row->product_id.'"
                                        data-product-name="'.$row->product_name.'"
                                        data-product-code="'.$row->product_code.'">
                                    <i class="fs-4 ti ti-currency-euro text-info"></i><span class="text-info">Voir Prix par Famille</span>
                                </button>
                            </li>';

                    if ($user->can('edit_products')) {
                        $dropdown .= '<li>
                                <button class="dropdown-item d-flex align-items-center gap-3 btn-edit-prices"
                                        data-product-id="'.$row->product_id.'"
                                        data-product-name="'.$row->product_name.'"
                                        data-product-code="'.$row->product_code.'">
                                    <i class="fs-4 ti ti-edit text-warning"></i><span class="text-warning">Modifier Prix par Famille</span>
                                </button>
                            </li>';
                    }

                    $dropdown .= '<li>
                                <a class="dropdown-item d-flex align-items-center gap-3" href="'.route('products.show', $row->product_id).'">
                                    <i class="fs-4 ti ti-eye"></i>Voir Détails
                                </a>
                            </li>';

                    if ($user->can('edit_products')) {
                        $dropdown .= '<li>
                                <a class="dropdown-item d-flex align-items-center gap-3" href="'.route('products.edit', $row->product_id).'">
                                    <i class="fs-4 ti ti-edit"></i>Modifier
                                </a>
                            </li>';
                    }

                    if ($user->can('create_production_orders')) {
                        $dropdown .= '<li>
                                <a class="dropdown-item d-flex align-items-center gap-3" href="'.route('production-orders.create').'?product_id='.$row->product_id.'">
                                    <i class="fs-4 ti ti-plus"></i>Produire
                                </a>
                            </li>';
                    }

                    if ($user->can('delete_products')) {
                        $dropdown .= '<li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 delete" href="javascript:void(0)"
                                data-id="'.$row->product_id.'"
                                data-name="'.$row->product_name.'">
                                    <i class="fs-4 ti ti-trash text-danger"></i><span class="text-danger">Supprimer</span>
                                </a>
                            </li>';
                    }

                    $dropdown .= '</ul></div>';
                    return $dropdown;
                })
                ->addColumn('product_type_badge', function($row){
                    switch($row->product_type) {
                        case 'production':
                            return '<span class="badge bg-primary">Production</span>';
                        case 'decoupage':
                            return '<span class="badge bg-warning">Découpage</span>';
                        case 'finale':
                            return '<span class="badge bg-success">Finale</span>';
                        default:
                            return '<span class="badge bg-secondary">Inconnu</span>';
                    }
                })
                ->addColumn('famille_info', function($row){
                    $familleCount = $row->familles->count();
                    if ($familleCount > 0) {
                        return '<span class="badge bg-info">
                            <i class="ti ti-layers me-1"></i>' . $familleCount . ' familles
                        </span>';
                    }
                    return '<span class="badge bg-secondary">Sans familles</span>';
                })
                ->addColumn('current_stock', function($row){
                    $totalStock = $row->total_stock;

                    $unitDisplay = '';
                    switch($row->product_type) {
                        case 'production':
                            $unitDisplay = 'bloc';
                            break;
                        case 'decoupage':
                            $unitDisplay = 'sous bloc';
                            break;
                        case 'finale':
                            $unitDisplay = 'pièce';
                            break;
                        default:
                            $unitDisplay = $row->unit_of_measure;
                    }

                    $html = '<div class="d-flex align-items-center gap-2">';

                    if ($row->familles->count() > 0) {
                        $html .= '<button type="button"
                                    class="btn btn-sm btn-outline-info btn-view-famille-stock"
                                    data-product-id="' . $row->product_id . '"
                                    data-product-name="' . $row->product_name . '"
                                    data-product-code="' . $row->product_code . '"
                                    data-unit="' . $unitDisplay . '">
                                <i class="ti ti-eye me-1"></i>' . number_format($totalStock, 2, ',', '.') . '
                            </button>';
                    } else {
                        $html .= '<span>' . number_format($totalStock, 2, ',', '.') . '</span>';
                    }

                    $html .= '<span class="text-muted">' . $unitDisplay . '</span>';
                    $html .= '</div>';

                    return $html;
                })
                ->addColumn('available_stock', function($row){
                    $availableStock = $row->total_available_stock;

                    $unitDisplay = '';
                    switch($row->product_type) {
                        case 'production':
                            $unitDisplay = 'bloc';
                            break;
                        case 'decoupage':
                            $unitDisplay = 'sous bloc';
                            break;
                        case 'finale':
                            $unitDisplay = 'pièce';
                            break;
                        default:
                            $unitDisplay = $row->unit_of_measure;
                    }

                    return number_format($availableStock, 2, ',', '.') . ' ' . $unitDisplay;
                })
                ->addColumn('stock_status', function($row){
                    $availableStock = $row->total_available_stock;
                    $minStock = $row->min_stock_level ?: 0;
                    $maxStock = $row->max_stock_level ?: 0;

                    if ($availableStock <= 0) {
                        return '<span class="badge bg-danger">Rupture</span>';
                    } elseif ($availableStock <= $minStock) {
                        return '<span class="badge bg-warning">Stock Bas</span>';
                    } elseif ($maxStock > 0 && $availableStock >= $maxStock) {
                        return '<span class="badge bg-info">Stock Élevé</span>';
                    } else {
                        return '<span class="badge bg-success">Normal</span>';
                    }
                })
                ->addColumn('status_badge', function($row){
                    return $row->is_active
                        ? '<span class="badge bg-success">Actif</span>'
                        : '<span class="badge bg-danger">Inactif</span>';
                })
                ->addColumn('famille_prices', function($row){
                    $html = '<div class="small">';
                    if ($row->familles->count() > 0) {
                        $html .= '<button type="button"
                                    class="btn btn-sm btn-outline-success btn-view-prices-quick"
                                    data-product-id="' . $row->product_id . '"
                                    data-product-name="' . $row->product_name . '"
                                    data-product-code="' . $row->product_code . '"
                                    title="Voir tous les prix">
                                    <i class="ti ti-eye me-1"></i>' . $row->familles->count() . ' famille(s)
                                </button>';
                    } else {
                        $html .= '<span class="text-muted">Aucune famille</span>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('dimensions', function($row){
                    return $row->height_m && $row->width_m && $row->depth_m
                        ? $row->height_m . '×' . $row->width_m . '×' . $row->depth_m . ' m'
                        : 'N/A';
                })
                ->addColumn('volume', function($row){
                    return $row->volume_m3 ? number_format($row->volume_m3, 3) . ' m³' : 'N/A';
                })
                ->addColumn('unit_of_measure', function($row){
                    switch($row->product_type) {
                        case 'production':
                            return '<span class="badge bg-primary">Bloc</span>';
                        case 'decoupage':
                            return '<span class="badge bg-warning">Sous Bloc</span>';
                        case 'finale':
                            return '<span class="badge bg-success">Pièce</span>';
                        default:
                            return '<span class="badge bg-secondary">' . $row->unit_of_measure . '</span>';
                    }
                })
                ->editColumn('cost_price', function($row){
                    return $row->cost_price ? number_format($row->cost_price, 2, ',', '.') . ' DH' : 'N/A';
                })
                ->editColumn('weight_kg', function($row){
                    return $row->weight_kg ? number_format($row->weight_kg, 2, ',', '.') . ' kg' : 'N/A';
                })
                ->editColumn('created_at', function($row){
                    return $row->created_at ? $row->created_at->format('d/m/Y') : 'N/A';
                })
                ->rawColumns([
                    'action',
                    'stock_status',
                    'status_badge',
                    'product_type_badge',
                    'famille_info',
                    'current_stock',
                    'famille_prices',
                    'unit_of_measure'
                ])
                ->make(true);
        }

        return view('pages.products.index');
    }

   public function create()
    {
        $rawMaterials = RawMaterial::where('is_active', true)->get(['material_id', 'material_code', 'material_name', 'unit_of_measure']);
        $familles = Famille::active()->get(['famille_id', 'famille_code', 'famille_name', 'prix_client', 'prix_grossiste', 'prix_commercial', 'prix_special']);

        return view('pages.products.create', compact('rawMaterials', 'familles'));
    }

    public function store(Request $request)
    {
        $productType = $request->input('product_type');

        $famillesData = null;
        if ($request->has('familles') && is_string($request->familles)) {
            $famillesData = json_decode($request->familles, true);
            $request->merge(['familles' => $famillesData]);
        }

        $validationRules = [
            'product_code' => 'required|unique:products|max:50',
            'product_name' => 'required|max:255',
            'product_type' => 'required|in:production,decoupage,finale',
            'unit_of_measure' => 'nullable|string|max:50',

            // Dimensions
            'height_m' => 'nullable|numeric|min:0',
            'width_m' => 'nullable|numeric|min:0',
            'depth_m' => 'nullable|numeric|min:0',
            'weight_kg' => 'nullable|numeric|min:0',

            // Stock levels
            'min_stock_level' => 'nullable|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0|gte:min_stock_level',

            'description' => 'nullable|string',

            // Status
            'is_active' => 'boolean',

            // Familles with prices
            'familles' => 'nullable|array',
            'familles.*.famille_id' => 'required_with:familles|exists:familles,famille_id',
            'familles.*.prix_client' => 'required|numeric|min:0',
            'familles.*.prix_grossiste' => 'required|numeric|min:0',
            'familles.*.prix_commercial' => 'required|numeric|min:0',
            'familles.*.prix_special' => 'required|numeric|min:0',
        ];

        if ($productType === 'production') {
            $validationRules['bill_of_materials'] = 'nullable|json';
        }

        $request->validate($validationRules);

        DB::beginTransaction();
        try {
            $productData = $request->except(['associated_familles', 'famille_quantities', 'bill_of_materials']);

            if (!$request->filled('unit_of_measure')) {
                switch ($productType) {
                    case 'production':
                        $productData['unit_of_measure'] = 'bloc';
                        break;
                    case 'decoupage':
                        $productData['unit_of_measure'] = 'sous bloc';
                        break;
                    case 'finale':
                        $productData['unit_of_measure'] = 'pièce';
                        break;
                    default:
                        $productData['unit_of_measure'] = 'unité';
                }
            }

            if ($request->height_m && $request->width_m && $request->depth_m) {
                $productData['volume_m3'] = $request->height_m * $request->width_m * $request->depth_m;
            }

            $productData['is_active'] = $request->has('is_active');

            unset($productData['production_time_days']);
            unset($productData['material_type']);
            unset($productData['color']);

            $product = Product::create($productData);

            ProductStock::create([
                'product_id' => $product->product_id,
                'current_quantity' => 0,
                'reserved_quantity' => 0,
                'location' => 'Entrepôt Principal',
                'last_updated' => now(),
            ]);

            if ($request->has('familles')) {
                $familleData = [];
                $volume = $productData['volume_m3'] ?? 1;

                foreach ($request->familles as $famille) {
                    $totalPrixClient = $famille['prix_client'] * $volume;
                    $totalPrixGrossiste = $famille['prix_grossiste'] * $volume;
                    $totalPrixCommercial = $famille['prix_commercial'] * $volume;
                    $totalPrixSpecial = $famille['prix_special'] * $volume;

                    $familleData[$famille['famille_id']] = [
                        'prix_client' => $totalPrixClient,
                        'prix_grossiste' => $totalPrixGrossiste,
                        'prix_commercial' => $totalPrixCommercial,
                        'prix_special' => $totalPrixSpecial,
                        'prix_client_m3' => $famille['prix_client'],
                        'prix_grossiste_m3' => $famille['prix_grossiste'],
                        'prix_commercial_m3' => $famille['prix_commercial'],
                        'prix_special_m3' => $famille['prix_special'],
                        'volume_applied' => $volume,
                        'sort_order' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                $product->familles()->sync($familleData);
            }

            // Create Bill of Materials for production products
            if ($productType === 'production') {
                $bomData = json_decode($request->input('bill_of_materials'), true) ?? [];

                if (!empty($bomData)) {
                    foreach ($bomData as $bomItem) {
                        BillOfMaterial::create([
                            'product_id' => $product->product_id,
                            'material_id' => $bomItem['material_id'],
                            'quantity_required' => $bomItem['quantity_required'],
                            'unit_of_measure' => $bomItem['unit_of_measure'] ?? null,
                            'scrap_factor' => $bomItem['scrap_factor'] ?? 0,
                            'notes' => $bomItem['notes'] ?? null,
                            'is_active' => true,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produit créé avec succès!',
                'redirect' => route('products.show', $product->product_id)
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
        $product = Product::with([
            'stock',
            'billOfMaterials.rawMaterial',
            'familles',
            'familleStocks.famille',
            'productionOrders' => function($query) {
                $query->orderBy('created_at', 'desc')->take(5);
            },
            'salesOrderItems' => function($query) {
                $query->orderBy('created_at', 'desc')->take(5);
            },
            'stockMovements' => function($query) {
                $query->with('performer')->orderBy('movement_date', 'desc')->take(50);
            },
        ])->findOrFail($id);

        return view('pages.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::with(['billOfMaterials.rawMaterial', 'familles'])->findOrFail($id);
        $categories = ProductCategory::all();
        $rawMaterials = RawMaterial::where('is_active', true)->get(['material_id', 'material_code', 'material_name', 'unit_of_measure']);
        $familles = Famille::active()->get(['famille_id', 'famille_code', 'famille_name', 'prix_client', 'prix_grossiste', 'prix_commercial', 'prix_special']);

        return view('pages.products.edit', compact('product', 'categories', 'rawMaterials', 'familles'));
    }

    public function getFamilyPrices($familleId)
    {
        try {
            $famille = Famille::findOrFail($familleId);

            return response()->json([
                'success' => true,
                'data' => [
                    'prix_client' => $famille->prix_client,
                    'prix_grossiste' => $famille->prix_grossiste,
                    'prix_commercial' => $famille->prix_commercial,
                    'prix_special' => $famille->prix_special,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $productType = $request->input('product_type');

        $validationRules = [
            'product_code' => 'required|unique:products,product_code,'.$id.',product_id|max:50',
            'product_name' => 'required|max:255',
            'product_type' => 'required|in:production,decoupage,finale',
            'unit_of_measure' => 'nullable|string|max:50',

            // Dimensions
            'height_m' => 'nullable|numeric|min:0',
            'width_m' => 'nullable|numeric|min:0',
            'depth_m' => 'nullable|numeric|min:0',
            'weight_kg' => 'nullable|numeric|min:0',

            // Stock levels
            'min_stock_level' => 'nullable|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0|gte:min_stock_level',

            'description' => 'nullable|string',

            // Status
            'is_active' => 'boolean',

            // Familles with prices
            'familles' => 'nullable|array',
            'familles.*.famille_id' => 'required_with:familles|exists:familles,famille_id',
            'familles.*.prix_client' => 'required|numeric|min:0',
            'familles.*.prix_grossiste' => 'required|numeric|min:0',
            'familles.*.prix_commercial' => 'required|numeric|min:0',
            'familles.*.prix_special' => 'required|numeric|min:0',
        ];

        if ($productType === 'production') {
            $validationRules['bill_of_materials'] = 'required|json';
        }

        $request->validate($validationRules);

        DB::beginTransaction();
        try {
            $productData = $request->except(['familles', 'bill_of_materials']);

            if (!$request->filled('unit_of_measure')) {
                switch ($productType) {
                    case 'production':
                        $productData['unit_of_measure'] = 'bloc';
                        break;
                    case 'decoupage':
                        $productData['unit_of_measure'] = 'sous bloc';
                        break;
                    case 'finale':
                        $productData['unit_of_measure'] = 'pièce';
                        break;
                    default:
                        $productData['unit_of_measure'] = 'unité';
                }
            }

            // Calculate volume if dimensions are provided
            if ($request->filled('height_m') && $request->filled('width_m') && $request->filled('depth_m')) {
                $productData['volume_m3'] = $request->height_m * $request->width_m * $request->depth_m;
            } else {
                $productData['volume_m3'] = null;
            }

            // Handle boolean fields
            $productData['is_active'] = $request->boolean('is_active');

            // Remove any fields that shouldn't be in the products table
            unset($productData['production_time_days']);
            unset($productData['material_type']);
            unset($productData['color']);

            // Update product basic information
            $product->update($productData);

            // Sync familles with their specific prices
            if ($request->has('familles')) {
                $familleData = [];

                $volume = $product->volume_m3 ?? 1;

                foreach ($request->familles as $index => $famille) {
                    $totalPrixClient = $famille['prix_client'] * $volume;
                    $totalPrixGrossiste = $famille['prix_grossiste'] * $volume;
                    $totalPrixCommercial = $famille['prix_commercial'] * $volume;
                    $totalPrixSpecial = $famille['prix_special'] * $volume;

                    $familleData[$famille['famille_id']] = [
                        'prix_client' => $totalPrixClient,
                        'prix_grossiste' => $totalPrixGrossiste,
                        'prix_commercial' => $totalPrixCommercial,
                        'prix_special' => $totalPrixSpecial,
                        'prix_client_m3' => $famille['prix_client'],
                        'prix_grossiste_m3' => $famille['prix_grossiste'],
                        'prix_commercial_m3' => $famille['prix_commercial'],
                        'prix_special_m3' => $famille['prix_special'],
                        'volume_applied' => $volume,
                        'sort_order' => $index,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                $product->familles()->sync($familleData);
            } else {
                $product->familles()->detach();
            }

            // Handle Bill of Materials for production products
            if ($productType === 'production') {
                // Delete existing BOM records
                $product->billOfMaterials()->delete();

                // Create new BOM records
                $bomData = json_decode($request->input('bill_of_materials'), true) ?? [];

                if (!empty($bomData)) {
                    foreach ($bomData as $bomItem) {
                        BillOfMaterial::create([
                            'product_id' => $product->product_id,
                            'material_id' => $bomItem['material_id'],
                            'quantity_required' => $bomItem['quantity_required'],
                            'unit_of_measure' => $bomItem['unit_of_measure'] ?? null,
                            'scrap_factor' => $bomItem['scrap_factor'] ?? 0,
                            'notes' => $bomItem['notes'] ?? null,
                            'is_active' => true,
                        ]);
                    }
                }
            } else {
                // If product type is not production, delete any existing BOM records
                $product->billOfMaterials()->delete();
            }

            // Update or create stock record if it doesn't exist
            if (!$product->stock) {
                $product->stock()->create([
                    'current_quantity' => 0,
                    'reserved_quantity' => 0,
                    'location' => 'Entrepôt Principal',
                    'last_updated' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produit mis à jour avec succès!',
                'redirect' => route('products.show', $product->product_id)
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
            $product = Product::findOrFail($id);

            // Check if product has associated records
            $hasProductionOrders = $product->productionOrders()->exists();
            $hasSales = $product->salesOrderItems()->exists();
            $hasProductionOutputs = $product->productionOutputs()->exists();

            if ($hasProductionOrders || $hasSales || $hasProductionOutputs) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce produit ne peut pas être supprimé car il est utilisé dans le système.'
                ], 400);
            }

            // Check if product has stock
            if ($product->total_stock > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un produit avec du stock. Veuillez d\'abord écouler le stock.'
                ], 400);
            }

            // Detach familles
            $product->familles()->detach();

            // Delete famille stocks
            $product->familleStocks()->delete();

            // Delete stock record
            $product->stock()->delete();

            // Delete BOM records
            $product->billOfMaterials()->delete();

            // Delete stock movements
            ProductStockMovement::where('product_id', $id)->delete();

            // Delete product
            $product->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produit supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    // Manage familles for a product
    public function manageFamilles($id)
    {
        $product = Product::with(['familles'])->findOrFail($id);
        $familles = Famille::active()->get(['famille_id', 'famille_code', 'famille_name']);

        return view('pages.products.manage-familles', compact('product', 'familles'));
    }

    public function updateFamilles(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:attach,detach',
            'famille_id' => 'required|exists:familles,famille_id',
            'quantity_per_unit' => 'nullable|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);

            if ($request->action === 'attach') {
                // Check if already attached
                if ($product->familles()->where('famille_id', $request->famille_id)->exists()) {
                    throw new \Exception('Cette famille est déjà associée à ce produit');
                }

                $product->familles()->attach($request->famille_id, [
                    'quantity_per_unit' => $request->quantity_per_unit ?? 1,
                ]);

                $message = 'Famille associée avec succès!';
            } else {
                // Check if famille has stock
                $familleStock = ProductFamilleStock::where('product_id', $id)
                    ->where('famille_id', $request->famille_id)
                    ->first();

                if ($familleStock && $familleStock->current_quantity > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Impossible de détacher une famille avec du stock.'
                    ], 400);
                }

                // Detach famille
                $product->familles()->detach($request->famille_id);

                // Delete famille stock if exists
                if ($familleStock) {
                    $familleStock->delete();
                }

                $message = 'Famille détachée avec succès!';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
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
     * Update family prices for a specific product
     */
    public function updateFamilyPrices(Request $request, $id)
    {
        $request->validate([
            'prices' => 'required|array',
            'prices.*.prix_client' => 'required|numeric|min:0',
            'prices.*.prix_grossiste' => 'required|numeric|min:0',
            'prices.*.prix_commercial' => 'required|numeric|min:0',
            'prices.*.prix_special' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);

            foreach ($request->prices as $familleId => $prices) {
                $exists = DB::table('product_famille')
                    ->where('product_id', $product->product_id)
                    ->where('famille_id', $familleId)
                    ->exists();

                if ($exists) {
                        $product->familles()->updateExistingPivot($familleId, [
                        'prix_client' => $prices['prix_client'],
                        'prix_grossiste' => $prices['prix_grossiste'],
                        'prix_commercial' => $prices['prix_commercial'],
                        'prix_special' => $prices['prix_special'],
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Les prix ont été mis à jour avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getFamilleStockDetails($id)
    {
        try {
            $product = Product::with(['familleStocks.famille'])->findOrFail($id);

            $familleStocks = [];
            $totalStock = 0;
            $totalAvailable = 0;

            foreach ($product->familleStocks as $familleStock) {
                $familleStocks[] = [
                    'famille_id' => $familleStock->famille_id,
                    'famille_name' => $familleStock->famille_name,
                    'famille_code' => $familleStock->famille->famille_code,
                    'current_quantity' => $familleStock->current_quantity,
                    'available_quantity' => $familleStock->available_quantity,
                    'reserved_quantity' => $familleStock->reserved_quantity,
                    'location' => $familleStock->location,
                    'last_restocked' => $familleStock->last_restocked ? $familleStock->last_restocked->format('d/m/Y') : null,
                ];

                $totalStock += $familleStock->current_quantity;
                $totalAvailable += $familleStock->available_quantity;
            }

            return response()->json([
                'success' => true,
                'product' => [
                    'product_id' => $product->product_id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->product_name,
                    'unit_of_measure' => $product->unit_of_measure,
                    'category_name' => $product->category ? $product->category->category_name : 'N/A',
                    'total_stock' => $totalStock,
                    'total_available' => $totalAvailable,
                ],
                'famille_stocks' => $familleStocks,
                'message' => 'Stock détaillé par famille'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addStock(Request $request, $id)
    {
        $request->validate([
            'stock_type' => 'nullable|in:famille,direct',
            'quantity' => 'required|numeric|min:0.001',
            'movement_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'unit_cost' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'famille_id' => 'required_if:stock_type,famille|exists:familles,famille_id'
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);
            $quantity = $request->quantity;

            $stockType = $request->stock_type ?? ($product->has_familles ? 'famille' : 'direct');
            $movementDate = $request->movement_date ?? now()->format('Y-m-d');
            $location = $request->location ?? ($stockType === 'famille' ? 'Entrepôt Principal' : 'Entrepôt Principal');

            if ($product->has_familles && $stockType === 'direct') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce produit utilise le système de familles. Veuillez sélectionner une famille ou utiliser le type "famille".'
                ], 400);
            }

            if ($stockType === 'famille') {
                if (!$request->has('famille_id')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Veuillez sélectionner une famille pour ce produit.'
                    ], 400);
                }

                $famille = Famille::findOrFail($request->famille_id);

                // Verify famille is associated with product - FIXED: qualify the column names
                $exists = DB::table('product_famille')
                    ->where('product_id', $product->product_id)
                    ->where('famille_id', $famille->famille_id)
                    ->exists();

                if (!$exists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cette famille n\'est pas associée à ce produit'
                    ], 400);
                }

                // Get or create famille stock record
                $familleStock = ProductFamilleStock::firstOrCreate(
                    [
                        'product_id' => $product->product_id,
                        'famille_id' => $famille->famille_id
                    ],
                    [
                        'famille_name' => $famille->famille_name,
                        'current_quantity' => 0,
                        'reserved_quantity' => 0,
                        'available_quantity' => 0,
                        'location' => $location,
                        'last_updated' => now(),
                    ]
                );

                // Update stock
                $previousStock = $familleStock->current_quantity;
                $familleStock->current_quantity += $quantity;
                $familleStock->available_quantity = $familleStock->current_quantity - $familleStock->reserved_quantity;
                $familleStock->last_updated = now();
                if ($quantity > 0) {
                    $familleStock->last_restocked = now();
                }
                $familleStock->save();

                // Record stock movement
                ProductStockMovement::create([
                    'product_id' => $product->product_id,
                    'famille_id' => $famille->famille_id,
                    'famille_name' => $famille->famille_name,
                    'movement_type' => 'manual_addition',
                    'quantity' => $quantity,
                    'previous_stock' => $previousStock,
                    'new_stock' => $familleStock->current_quantity,
                    'reference_type' => 'manual_adjustment',
                    'reference_number' => 'MAN-' . now()->format('YmdHis'),
                    'movement_date' => $movementDate,
                    'performed_by' => auth()->id(),
                    'notes' => $request->notes, // Optional, can be null
                ]);

                $message = "{$quantity} {$product->unit_of_measure} ajouté(s) à la famille {$famille->famille_name}";

            } else {
                // Add stock directly to product (non-famille system)
                if ($product->familles()->count() > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce produit utilise le système de familles. Veuillez sélectionner une famille.'
                    ], 400);
                }

                // Get or create product stock record
                $stock = $product->stock;
                if (!$stock) {
                    $stock = ProductStock::create([
                        'product_id' => $product->product_id,
                        'current_quantity' => 0,
                        'reserved_quantity' => 0,
                        'available_quantity' => 0,
                        'location' => $location,
                        'last_updated' => now(),
                    ]);
                }

                // Update main product stock
                $previousStock = $stock->current_quantity;
                $stock->current_quantity += $quantity;
                $stock->available_quantity = $stock->current_quantity - $stock->reserved_quantity;
                $stock->location = $location;
                $stock->last_updated = now();
                $stock->save();

                // Record stock movement
                ProductStockMovement::create([
                    'product_id' => $product->product_id,
                    'movement_type' => 'manual_addition',
                    'quantity' => $quantity,
                    'previous_stock' => $previousStock,
                    'new_stock' => $stock->current_quantity,
                    'reference_type' => 'manual_adjustment',
                    'reference_number' => 'DIR-' . now()->format('YmdHis'),
                    'famille_name' => 'Direct',
                    'movement_date' => $movementDate,
                    'performed_by' => auth()->id(),
                    'notes' => $request->notes, // Optional, can be null
                ]);

                $message = "{$quantity} {$product->unit_of_measure} ajouté(s) au stock principal";
            }

            // If unit cost is provided, update product cost (weighted average)
            if ($request->filled('unit_cost') && $quantity > 0) {
                $currentTotalValue = ($product->cost_price ?? 0) * $product->total_stock;
                $newTotalValue = $currentTotalValue + ($request->unit_cost * $quantity);
                $newTotalStock = $product->total_stock + $quantity;

                if ($newTotalStock > 0) {
                    $newCost = $newTotalValue / $newTotalStock;
                    $product->update(['cost_price' => $newCost]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'new_total_stock' => $product->refresh()->total_stock
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkLowStock()
    {
        $lowStockProducts = Product::with(['stock', 'category'])
            ->where(function($query) {
                $query->whereHas('stock', function($q) {
                    $q->whereRaw('available_quantity <= min_stock_level');
                })
                ->orWhereDoesntHave('stock');
            })
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lowStockProducts,
            'count' => $lowStockProducts->count()
        ]);
    }

    public function getStatistics()
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        $productionProducts = Product::production()->count();
        $decoupageProducts = Product::decoupage()->count();
        $finaleProducts = Product::finale()->count();

        $lowStockCount = Product::where(function($query) {
                $query->whereHas('stock', function($q) {
                    $q->whereRaw('available_quantity <= min_stock_level');
                })
                ->orWhereDoesntHave('stock');
            })
            ->where('is_active', true)
            ->count();

        $outOfStockCount = Product::where(function($query) {
                $query->whereHas('stock', function($q) {
                    $q->where('available_quantity', '<=', 0);
                })
                ->orWhereDoesntHave('stock');
            })
            ->where('is_active', true)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalProducts,
                'active' => $activeProducts,
                'production' => $productionProducts,
                'decoupage' => $decoupageProducts,
                'finale' => $finaleProducts,
                'low_stock' => $lowStockCount,
                'out_of_stock' => $outOfStockCount
            ]
        ]);
    }

    public function getProductionTime($id)
    {
        $product = Product::findOrFail($id);

        return response()->json([
            'success' => true,
            'production_time_days' => $product->production_time_days
        ]);
    }

    public function getProductsForConversion()
    {
        $productionProducts = Product::where('is_active', true)
            ->where('product_type', 'production')
            ->get(['product_id', 'product_code', 'product_name', 'unit_of_measure']);

        $decoupageProducts = Product::where('is_active', true)
            ->where('product_type', 'decoupage')
            ->get(['product_id', 'product_code', 'product_name', 'unit_of_measure']);

        $finaleProducts = Product::where('is_active', true)
            ->where('product_type', 'finale')
            ->get(['product_id', 'product_code', 'product_name', 'unit_of_measure']);

        return response()->json([
            'success' => true,
            'production_products' => $productionProducts,
            'decoupage_products' => $decoupageProducts,
            'finale_products' => $finaleProducts
        ]);
    }

    public function getFamillesForProduct($id)
    {
        try {
            $product = Product::with(['familles'])->findOrFail($id);

            $familles = [];
            foreach ($product->familles as $famille) {
                $familles[] = [
                    'famille_id' => $famille->famille_id,
                    'famille_code' => $famille->famille_code,
                    'famille_name' => $famille->famille_name,
                    'prix_client' => $famille->pivot->prix_client,
                    'prix_grossiste' => $famille->pivot->prix_grossiste,
                    'prix_commercial' => $famille->pivot->prix_commercial,
                    'prix_special' => $famille->pivot->prix_special,
                    'prix_client_m3' => $famille->pivot->prix_client_m3 ?? 0,
                    'prix_grossiste_m3' => $famille->pivot->prix_grossiste_m3 ?? 0,
                    'prix_commercial_m3' => $famille->pivot->prix_commercial_m3 ?? 0,
                    'prix_special_m3' => $famille->pivot->prix_special_m3 ?? 0,
                    'volume_applied' => $famille->pivot->volume_applied ?? 1,
                ];
            }

            return response()->json([
                'success' => true,
                'familles' => $familles,
                'message' => count($familles) . ' famille(s) associée(s)'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        try {
            $product = Product::findOrFail($id);

            // Check if product has stock when deactivating
            if (!$request->is_active && $product->total_stock > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de désactiver un produit avec du stock.'
                ], 400);
            }

            $product->update(['is_active' => $request->is_active]);

            return response()->json([
                'success' => true,
                'message' => $request->is_active
                    ? 'Produit activé avec succès!'
                    : 'Produit désactivé avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStockInfo(Request $request, $id)
    {
        try {
            $product = Product::with(['familleStocks', 'stock'])->findOrFail($id);

            $availableStock = 0;
            $unit = $product->unit_of_measure;

            if ($request->has('family_id')) {
                $familyId = $request->family_id;
                $familleStock = $product->familleStocks()
                    ->where('famille_id', $familyId)
                    ->first();

                if ($familleStock) {
                    $availableStock = $familleStock->available_quantity;
                    $unit = $product->unit_of_measure;
                }
            } else {
                if ($product->familles->count() > 0) {
                    $availableStock = $product->familleStocks->sum('available_quantity');
                } else if ($product->stock) {
                    $availableStock = $product->stock->available_quantity;
                }
            }

            return response()->json([
                'success' => true,
                'available_stock' => $availableStock,
                'unit' => $unit
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $productType = $request->get('product_type');
            $status = $request->get('status');

            return Excel::download(
                new ProductsFamilyStockExport($productType, $status),
                'produits-stock-famille-' . now()->format('Y-m-d-His') . '.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export Excel: ' . $e->getMessage()
            ], 500);
        }
    }

   public function exportPdf(Request $request)
    {
        try {
            ini_set('memory_limit', '512M');
            set_time_limit(600);

            $productType = $request->get('product_type');
            $status = $request->get('status');

            $query = Product::query();
            if ($productType) $query->where('product_type', $productType);
            if ($status !== null) $query->where('is_active', $status);
            $totalProducts = $query->count();

            if ($totalProducts > 500) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trop de produits pour l\'export PDF. Veuillez utiliser l\'export Excel ou réduire les filtres.'
                ], 400);
            }

            $products = $query->with(['familles', 'familleStocks.famille'])->get();

            foreach ($products as $product) {
                $product->product_type_label = $this->getProductTypeLabel($product->product_type);
                $product->unit_label = $this->getUnitLabel($product->product_type);
            }

            $pdf = Pdf::loadView('exports.products-family-stock-pdf', compact('products'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download('produits-stock-famille-' . now()->format('Y-m-d-His') . '.pdf');

        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    // Helper methods
    private function getProductTypeLabel($type)
    {
        switch ($type) {
            case 'production':
                return 'Production';
            case 'decoupage':
                return 'Découpage';
            case 'finale':
                return 'Vente';
            default:
                return ucfirst($type);
        }
    }

    private function getUnitLabel($type)
    {
        switch ($type) {
            case 'production':
                return 'Bloc';
            case 'decoupage':
                return 'Sous Bloc';
            case 'finale':
                return 'Piece';
            default:
                return 'Unité';
        }
    }

    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        $query = Product::where('is_active', true)
            ->select('product_id', 'product_name', 'product_code', 'volume_m3');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('product_name', 'like', '%' . $q . '%')
                    ->orWhere('product_code', 'like', '%' . $q . '%');
            });
        }

        $products = $query->orderBy('product_name')->limit(50)->get();

        return response()->json([
            'results' => $products->map(fn($p) => [
                'id'        => $p->product_id,
                'text'      => $p->product_name . ' (' . $p->product_code . ')' . ($p->volume_m3 ? ' - ' . number_format($p->volume_m3, 4) . ' m³' : ''),
                'volume_m3' => $p->volume_m3 ?? 0,
            ]),
        ]);
    }
}
