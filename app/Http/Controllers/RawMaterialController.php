<?php

namespace App\Http\Controllers;

use App\Models\Magazine;
use App\Models\RawMaterial;
use App\Models\RawMaterialCategory;
use App\Models\StockMovementDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RawMaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_raw_materials')->only(['index', 'show', 'getByCode', 'getList', 'getListForSale', 'stockMovements', 'autocomplete', 'getStockDetails', 'getStockInfoPurchase', 'getDetails', 'getStockInfo']);
        $this->middleware('can:create_raw_materials')->only(['create', 'store']);
        $this->middleware('can:edit_raw_materials')->only(['edit', 'update']);
        $this->middleware('can:delete_raw_materials')->only(['destroy']);
        $this->middleware('can:manage_raw_material_stock')->only(['adjustStock']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $materials = RawMaterial::with(['category'])
                ->select('raw_materials.*');

            return DataTables::of($materials)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $user = auth()->user();
                    $dropdown = '<div class="dropdown dropstart">
                        <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 view" href="javascript:void(0)" data-id="'.$row->material_id.'">
                                    <i class="fs-4 ti ti-eye"></i>Voir
                                </a>
                            </li>';
                    if ($user->can('edit_raw_materials')) {
                        $dropdown .= '<li>
                                <a class="dropdown-item d-flex align-items-center gap-3 edit" href="javascript:void(0)" data-id="'.$row->material_id.'">
                                    <i class="fs-4 ti ti-edit"></i>Modifier
                                </a>
                            </li>';
                    }
                    if ($user->can('manage_raw_material_stock')) {
                        $dropdown .= '<li>
                                <a class="dropdown-item d-flex align-items-center gap-3 adjust-stock" href="javascript:void(0)"
                                data-id="'.$row->material_id.'"
                                data-stock="'.$row->current_stock.'"
                                data-unit="'.$row->unit_of_measure.'">
                                    <i class="fs-4 ti ti-adjustments"></i>Ajuster Stock
                                </a>
                            </li>';
                    }
                    if ($user->can('delete_raw_materials')) {
                        $dropdown .= '<li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 delete" href="javascript:void(0)"
                                data-id="'.$row->material_id.'"
                                data-name="'.$row->material_name.'">
                                    <i class="fs-4 ti ti-trash text-danger"></i><span class="text-danger">Supprimer</span>
                                </a>
                            </li>';
                    }
                    $dropdown .= '</ul></div>';
                    return $dropdown;
                })
                ->addColumn('category_name', function($row){
                    return $row->category ? $row->category->category_name : 'N/A';
                })
                ->addColumn('material_code', function($row){
                    return '<span class="badge badge-primary">' .$row->material_code. '</span>' ;
                })
                // ->addColumn('supplier_name', function($row){
                //     return $row->supplier ? $row->supplier->company_name ?? $row->supplier->full_name    : 'N/A';
                // })
                // ->addColumn('magazine_name', function($row){
                //     return $row->magazine ? $row->magazine->magazine_name : 'N/A';
                // })
                ->addColumn('stock_status', function($row){
                    $status = '';
                    if ($row->current_stock <= $row->min_stock_level) {
                        $status = '<span class="badge badge-danger">Stock Bas</span>';
                    } elseif ($row->current_stock >= $row->max_stock_level) {
                        $status = '<span class="badge badge-warning">Stock Élevé</span>';
                    } else {
                        $status = '<span class="badge badge-success">Normal</span>';
                    }
                    return $status;
                })
                ->addColumn('status_badge', function($row){
                    return $row->is_active
                        ? '<span class="badge badge-success">Actif</span>'
                        : '<span class="badge badge-danger">Inactif</span>';
                })
                ->addColumn('current_stock', function($row){
                    return number_format($row->current_stock, 2, ',', '.') . ' ' . $row->unit_of_measure;
                })
                ->addColumn('average_cost', function($row){
                    return number_format($row->average_unit_cost, 2, ',', '.') . ' DH';
                })
                ->addColumn('prices', function($row){
                    $content = 'Client: ' . number_format($row->prix_client, 2, ',', '.') . ' DH'
                        . '<br>Grossiste: ' . number_format($row->prix_grossiste, 2, ',', '.') . ' DH'
                        . '<br>Commercial: ' . number_format($row->prix_commercial, 2, ',', '.') . ' DH'
                        . '<br>Spécial: ' . number_format($row->prix_special, 2, ',', '.') . ' DH';

                    return '<span class="badge bg-primary prices-popover" tabindex="0" role="button"
                        data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-html="true"
                        data-bs-placement="left" data-bs-content="' . e($content) . '">
                        <i class="fas fa-tags me-1"></i>Voir prix
                    </span>';
                })
                ->rawColumns(['action', 'stock_status', 'status_badge', 'material_code', 'prices'])
                ->make(true);
        }

        $categories = RawMaterialCategory::where('is_active', true)->get();
        $magazines = Magazine::where('is_active', true)->get();

        return view('pages.raw-materials.index', compact('categories'));
    }

    public function create()
    {
        $categories = RawMaterialCategory::where('is_active', true)->get();
        $units = ['kg', 'meter', 'piece', 'liter', 'roll', 'sheet'];

        return view('pages.raw-materials.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'material_code' => 'required|unique:raw_materials|max:20',
            'material_name' => 'required|max:100',
            'category_id' => 'required|exists:raw_material_categories,category_id',
            'unit_of_measure' => 'required|in:kg,meter,piece,liter,roll,sheet',
            'min_stock_level' => 'nullable|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0|gte:min_stock_level',
            'prix_client' => 'nullable|numeric|min:0',
            'prix_grossiste' => 'nullable|numeric|min:0',
            'prix_commercial' => 'nullable|numeric|min:0',
            'prix_special' => 'nullable|numeric|min:0',
            'notes' => 'nullable',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $materialData = $request->except(['supplier_id', 'magazine_id']);
            $material = RawMaterial::create($materialData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Matière première créée avec succès!'
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
        $material = RawMaterial::with(['category', 'stockMovements.performer'])
            ->findOrFail($id);

        $stockDetails = DB::table('stock_movement_details as sd')
            ->join('raw_material_stock_movements as sm', 'sm.movement_id', '=', 'sd.stock_movement_id')
            ->leftJoin('users as u', 'u.id', '=', 'sm.performed_by')
            ->where('sd.material_id', $id)
            ->where('sd.remaining_quantity', '>', 0)
            ->select(
                'sd.stock_detail_id',
                'sd.unit_price',
                'sd.remaining_quantity',
                'sm.movement_type',
                'sm.movement_date',
                'sm.reference_type',
                'sm.reference_id',
                'u.username as performed_by'
            )
            ->orderBy('sd.unit_price', 'asc')
            ->get();

        $stockMovements = $material->stockMovements()
            ->with('performer')
            ->orderBy('movement_date', 'desc')
            ->limit(10)
            ->get();

        $totalStock = $stockDetails->sum('remaining_quantity');
        $totalValue = $stockDetails->sum(function($detail) {
            return $detail->remaining_quantity * $detail->unit_price;
        });
        $averageCost = $totalStock > 0 ? $totalValue / $totalStock : 0;

        return view('pages.raw-materials.show', compact(
            'material',
            'stockDetails',
            'totalStock',
            'totalValue',
            'averageCost',
            'stockMovements'
        ));
    }

    public function stockMovements($id)
    {
        $material = RawMaterial::with(['category'])->findOrFail($id);

        $stockMovements = $material->stockMovements()
            ->with('performer')
            ->orderBy('movement_date', 'desc')
            ->paginate(20);

        return view('pages.raw-materials.stock-movements', compact('material', 'stockMovements'));
    }

    public function edit($id)
    {
        $material = RawMaterial::findOrFail($id);
        $categories = RawMaterialCategory::where('is_active', true)->get();
        $units = ['kg', 'meter', 'piece', 'liter', 'roll', 'sheet'];

        return view('pages.raw-materials.edit', compact('material', 'categories', 'units'));
    }

    public function update(Request $request, $id)
    {
        $material = RawMaterial::findOrFail($id);

        $request->validate([
            'material_code' => 'required|unique:raw_materials,material_code,'.$id.',material_id|max:20',
            'material_name' => 'required|max:100',
            'category_id' => 'required|exists:raw_material_categories,category_id',
            'unit_of_measure' => 'required|in:kg,meter,piece,liter,roll,sheet',
            'min_stock_level' => 'nullable|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0|gte:min_stock_level',
            'prix_client' => 'nullable|numeric|min:0',
            'prix_grossiste' => 'nullable|numeric|min:0',
            'prix_commercial' => 'nullable|numeric|min:0',
            'prix_special' => 'nullable|numeric|min:0',
            'notes' => 'nullable',
            'is_active' => 'boolean',
        ]);

        try {
            $materialData = $request->except(['supplier_id', 'magazine_id']);
            $material->update($materialData);

            return response()->json([
                'success' => true,
                'message' => 'Matière première mise à jour avec succès!'
            ]);

        } catch (\Exception $e) {
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
            $material = RawMaterial::findOrFail($id);

            $hasBom = DB::table('bill_of_materials')->where('material_id', $id)->exists();
            $hasPurchases = DB::table('raw_material_purchase_items')->where('material_id', $id)->exists();
            $hasConsumption = DB::table('production_consumption')->where('material_id', $id)->exists();

            if ($hasBom || $hasPurchases || $hasConsumption) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette matière première ne peut pas être supprimée car elle est utilisée dans le système.'
                ], 400);
            }

            DB::table('raw_material_stock_movements')->where('material_id', $id)->delete();

            $material->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Matière première supprimée avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function adjustStock(Request $request, $id)
    {
        $request->validate([
            'adjustment_type' => 'required|in:add,remove,set',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $material = RawMaterial::findOrFail($id);
            $oldStock = $material->current_stock;

            switch ($request->adjustment_type) {
                case 'add':
                    $newStock = $oldStock + $request->quantity;
                    break;
                case 'remove':
                    if ($request->quantity > $oldStock) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Quantité à retirer supérieure au stock disponible'
                        ], 400);
                    }
                    $newStock = $oldStock - $request->quantity;
                    break;
                case 'set':
                    $newStock = $request->quantity;
                    break;
            }

            $material->current_stock = $newStock;
            $material->save();

            DB::table('raw_material_stock_movements')->insert([
                'material_id' => $id,
                'movement_type' => 'adjustment',
                'quantity' => $request->adjustment_type === 'set' ? $newStock : $request->quantity,
                'previous_stock' => $oldStock,
                'new_stock' => $newStock,
                'reference_type' => 'manual_adjustment',
                'movement_date' => now(),
                'performed_by' => auth()->id(),
                'notes' => $request->reason . ' (Ajustement manuel: ' . $request->adjustment_type . ')'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock ajusté avec succès!',
                'new_stock' => $newStock
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajustement: ' . $e->getMessage()
            ], 500);
        }
    }


    public function autocomplete(Request $request)
    {
        $query = $request->get('query');

        $materials = RawMaterial::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('material_code', 'like', '%' . $query . '%')
                  ->orWhere('material_name', 'like', '%' . $query . '%');
            })
            ->select('material_id', 'material_code', 'material_name', 'unit_of_measure', 'current_stock', 'unit_cost')
            ->limit(10)
            ->get();

        return response()->json($materials);
    }

    public function getStockDetails($id)
    {
        $stockDetails = DB::table('stock_movement_details as sd')
            ->join('raw_material_stock_movements as sm', 'sm.movement_id', '=', 'sd.stock_movement_id')
            ->where('sd.material_id', $id)
            ->where('sd.remaining_quantity', '>', 0)
            ->select(
                'sd.unit_price',
                'sd.remaining_quantity',
                'sm.movement_date'
            )
            ->orderBy('sd.unit_price', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stockDetails
        ]);
    }


    public function getByCode(Request $request)
    {
        $request->validate([
            'material_code' => 'required|string'
        ]);

        try {
            $material = RawMaterial::where('material_code', $request->material_code)
                ->first();

            if (!$material) {
                return response()->json([
                    'success' => true,
                    'material_exists' => false,
                    'message' => 'Matière première CHUTE-PRODUCTION non trouvée. Elle sera créée automatiquement lors de la création de l\'ordre.'
                ]);
            }

            $currentStock = 0;

            if (method_exists($material, 'getCurrentStockAttribute')) {
                $currentStock = $material->current_stock;
            }
            else {
                try {
                    $currentStock = StockMovementDetail::where('material_id', $material->material_id)
                        ->sum('remaining_quantity');
                } catch (\Exception $e) {
                    $currentStock = 0;
                }
            }

            return response()->json([
                'success' => true,
                'material_exists' => true,
                'material' => [
                    'material_id' => $material->material_id,
                    'material_code' => $material->material_code,
                    'material_name' => $material->material_name,
                    'unit_of_measure' => $material->unit_of_measure,
                    'current_stock' => $currentStock,
                    'is_active' => $material->is_active,
                    'notes' => $material->notes,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching material by code: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDetails($id)
    {
        $material = RawMaterial::findOrFail($id);

        return response()->json([
            'success' => true,
            'unit_cost' => $material->unit_cost,
            'unit_of_measure' => $material->unit_of_measure,
            'current_stock' => $material->current_stock
        ]);
    }

    public function getStockInfo($id)
    {
        $material = RawMaterial::findOrFail($id);

        return response()->json([
            'success' => true,
            'available_stock' => $material->current_stock
        ]);
    }

    public function getStockInfoPurchase($id)
    {
        try {
            $material = RawMaterial::findOrFail($id);

            $availableStock = $material->current_stock ?? 0;

            return response()->json([
                'success' => true,
                'available_stock' => $availableStock,
                'unit' => $material->unit_of_measure
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getList(Request $request)
    {
        try {
            $materials = RawMaterial::with('category')
                ->where('is_active', true)
                ->orderBy('material_name')
                ->get()
                ->map(function($material) {
                    // Get current stock from stock movement details
                    $currentStock = StockMovementDetail::where('material_id', $material->material_id)
                        ->sum('remaining_quantity');

                    // Get average unit cost
                    $totalValue = StockMovementDetail::where('material_id', $material->material_id)
                        ->where('remaining_quantity', '>', 0)
                        ->sum(DB::raw('remaining_quantity * unit_price'));

                    $averageUnitCost = $currentStock > 0 ? $totalValue / $currentStock : 0;

                    return [
                        'material_id' => $material->material_id,
                        'material_code' => $material->material_code,
                        'material_name' => $material->material_name,
                        'unit_of_measure' => $material->unit_of_measure,
                        'current_stock' => $currentStock,
                        'average_unit_cost' => $averageUnitCost,
                        'category_name' => $material->category ? $material->category->category_name : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $materials
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting raw materials list: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getListForSale(Request $request)
    {
        try {
            $materials = RawMaterial::with('category')
                ->where('is_active', true)
                ->orderBy('material_name')
                ->get()
                ->map(function($material) {
                    // Get current stock from stock movement details
                    $currentStock = StockMovementDetail::where('material_id', $material->material_id)
                        ->sum('remaining_quantity');

                    // Get average unit cost
                    $totalValue = StockMovementDetail::where('material_id', $material->material_id)
                        ->where('remaining_quantity', '>', 0)
                        ->sum(DB::raw('remaining_quantity * unit_price'));

                    $averageUnitCost = $currentStock > 0 ? $totalValue / $currentStock : 0;

                return [
                        'id' => $material->material_id,
                        'material_id' => $material->material_id,
                        'code' => $material->material_code,
                        'name' => $material->material_name,
                        'unit_of_measure' => $material->unit_of_measure,
                        'current_stock' => $currentStock,
                        'price' => $averageUnitCost,
                        'prix_client' => $material->prix_client,
                        'prix_grossiste' => $material->prix_grossiste,
                        'prix_commercial' => $material->prix_commercial,
                        'prix_special' => $material->prix_special,
                        'category_name' => $material->category ? $material->category->category_name : null,
                        'has_families' => false, // Add this flag
                        'type' => 'raw_material' // Add type indicator
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $materials
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting raw materials list: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);    
        }
    }
}
