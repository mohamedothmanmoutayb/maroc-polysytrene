<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductFamilleStock;
use App\Models\RawMaterial;
use App\Models\Famille;
use App\Models\StockAdjustment;
use App\Models\StockMovementDetail;
use App\Models\RawMaterialStockMovement;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_inventory')->only(['index', 'getInventoryData', 'getStatistics', 'getMovements']);
        $this->middleware('can:adjust_inventory')->only(['adjustStock', 'createAdjustment', 'storeAdjustment', 'bulkRequestAdjustments']);
        $this->middleware('can:approve_inventory_adjustments')->only(['approveAdjustment', 'rejectAdjustment', 'approveAllAdjustments']);
        $this->middleware('can:export_inventory')->only(['export', 'exportExcel', 'exportPdf']);
    }

    /**
     * Display inventory management page
     */
    public function index()
    {
        // Get statistics
        $totalProducts = Product::count();
        $totalRawMaterials = RawMaterial::count();

        // Product stock stats
        $totalProductStock = Product::sum(DB::raw('(SELECT COALESCE(SUM(current_quantity), 0) FROM product_famille_stock WHERE product_famille_stock.product_id = products.product_id)'));
        $totalRawMaterialStock = RawMaterial::sum('current_stock');

        // Pending adjustments
        $pendingAdjustments = StockAdjustment::where('status', 'pending')->count();

        return view('pages.inventory.index', compact(
            'totalProducts',
            'totalRawMaterials',
            'totalProductStock',
            'totalRawMaterialStock',
            'pendingAdjustments'
        ));
    }

    /**
     * Get products data for DataTable
     */
    public function getProducts(Request $request)
    {
        $products = Product::with(['familles', 'familleStocks.famille'])
            ->select('products.*');

        return DataTables::of($products)
            ->addIndexColumn()
            ->addColumn('product_name_with_code', function($product) {
                return '<strong>' . e($product->product_name) . '</strong><br><small class="text-muted">Code: ' . e($product->product_code) . '</small>';
            })
            ->addColumn('families_stock', function($product) {
                if ($product->familles->count() > 0) {
                    $parts = [];
                    foreach ($product->familles as $famille) {
                        $input = '<input type="number" class="form-control form-control-sm text-end inline-stock-input"'
                            . ' style="width:90px;display:inline-block;"'
                            . ' min="0" step="0.01"'
                            . ' data-product-id="' . $product->product_id . '"'
                            . ' data-famille-id="' . $famille->famille_id . '"'
                            . ' data-label="' . e($product->product_name) . ' — ' . e($famille->famille_name) . '">';
                        $parts[] = '<span class="fw-semibold small me-1">' . e($famille->famille_name) . ':</span>' . $input;
                    }
                    return '<div class="d-flex flex-wrap align-items-center gap-2">'
                        . implode('<span class="text-muted mx-1">|</span>', $parts)
                        . '</div>';
                }

                // Product without families — single empty input
                $familleId = $product->familleStocks->first()?->famille_id ?? 0;

                return '<input type="number" class="form-control form-control-sm text-end inline-stock-input"'
                    . ' style="max-width:130px"'
                    . ' min="0" step="0.01"'
                    . ' data-product-id="' . $product->product_id . '"'
                    . ' data-famille-id="' . $familleId . '"'
                    . ' data-label="' . e($product->product_name) . '">';
            })
            ->addColumn('total_stock', function($product) {
                return number_format($product->total_stock, 2, ',', '.');
            })
            ->addColumn('min_stock', function($product) {
                return $product->min_stock_level ? number_format($product->min_stock_level, 2, ',', '.') : '-';
            })
            ->addColumn('status_badge', function($product) {
                $totalStock = $product->total_stock;
                $minStock = $product->min_stock_level;

                if ($minStock && $totalStock <= $minStock) {
                    return '<span class="badge badge-danger">Stock Bas</span>';
                } elseif ($minStock && $totalStock <= $minStock * 2) {
                    return '<span class="badge badge-warning">Stock Moyen</span>';
                }
                return '<span class="badge badge-success">Stock Normal</span>';
            })
            ->editColumn('product_type', function($product) {
                return $product->product_type_label;
            })
            ->rawColumns(['product_name_with_code', 'families_stock', 'status_badge'])
            ->make(true);
    }

    /**
     * Get raw materials data for DataTable
     */
    public function getRawMaterials(Request $request)
    {
        $materials = RawMaterial::with('category')
            ->select('raw_materials.*');

        return DataTables::of($materials)
            ->addIndexColumn()
            ->addColumn('material_name_with_code', function($material) {
                return '<strong>' . e($material->material_name) . '</strong><br><small class="text-muted">Code: ' . e($material->material_code) . '</small>';
            })
            ->addColumn('current_stock_display', function($material) {
                $class = '';
                if ($material->min_stock_level && $material->current_stock <= $material->min_stock_level) {
                    $class = 'text-danger';
                } elseif ($material->min_stock_level && $material->current_stock <= $material->min_stock_level * 2) {
                    $class = 'text-warning';
                }

                return '<div class="d-flex justify-content-between align-items-center">
                            <span class="' . $class . ' fw-bold">' . number_format($material->current_stock, 2, ',', '.') . ' ' . e($material->unit_of_measure ?? 'U') . '</span>
                            <button type="button" class="btn btn-sm btn-primary adjust-raw-material"
                                    data-material-id="' . $material->material_id . '"
                                    data-material-name="' . e($material->material_name) . '"
                                    data-current-stock="' . $material->current_stock . '"
                                    data-unit="' . e($material->unit_of_measure ?? 'U') . '">
                                <i class="fas fa-edit"></i> Ajuster
                            </button>
                        </div>';
            })
            ->addColumn('current_stock_value', function($material) {
                return number_format($material->current_stock, 2, ',', '.');
            })
            ->addColumn('min_stock', function($material) {
                return $material->min_stock_level ? number_format($material->min_stock_level, 2, ',', '.') : '-';
            })
            ->addColumn('max_stock', function($material) {
                return $material->max_stock_level ? number_format($material->max_stock_level, 2, ',', '.') : '-';
            })
            ->addColumn('category_name', function($material) {
                return $material->category ? $material->category->name : '-';
            })
            ->addColumn('status_badge', function($material) {
                if ($material->min_stock_level && $material->current_stock <= $material->min_stock_level) {
                    return '<span class="badge badge-danger">Stock Critique</span>';
                } elseif ($material->min_stock_level && $material->current_stock <= $material->min_stock_level * 2) {
                    return '<span class="badge badge-warning">Stock Bas</span>';
                } elseif ($material->max_stock_level && $material->current_stock >= $material->max_stock_level) {
                    return '<span class="badge badge-info">Stock Max</span>';
                }
                return '<span class="badge badge-success">Normal</span>';
            })
            ->rawColumns(['material_name_with_code', 'current_stock_display', 'status_badge'])
            ->make(true);
    }

    /**
     * Get pending stock adjustments
     */
    public function getPendingAdjustments(Request $request)
    {
        $adjustments = StockAdjustment::with(['requester', 'approver', 'famille'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        return DataTables::of($adjustments)
            ->addIndexColumn()
            ->addColumn('reference_info', function($adjustment) {
                if ($adjustment->adjustment_type == 'product_famille') {
                    $product = Product::find($adjustment->reference_id);
                    return 'Produit: ' . ($product ? $product->product_name : 'N/A') . '<br>Famille: ' . ($adjustment->famille ? $adjustment->famille->famille_name : 'N/A');
                } elseif ($adjustment->adjustment_type == 'raw_material') {
                    $material = RawMaterial::find($adjustment->reference_id);
                    return 'Matière: ' . ($material ? $material->material_name : 'N/A');
                }
                return 'N/A';
            })
            ->addColumn('old_quantity_formatted', function($adjustment) {
                return number_format($adjustment->old_quantity, 2, ',', '.');
            })
            ->addColumn('new_quantity_formatted', function($adjustment) {
                return number_format($adjustment->new_quantity, 2, ',', '.');
            })
            ->addColumn('adjusted_quantity_formatted', function($adjustment) {
                $diff = $adjustment->adjusted_quantity;
                $class = $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : '');
                $sign = $diff > 0 ? '+' : '';
                return '<span class="' . $class . '">' . $sign . number_format($diff, 2, ',', '.') . '</span>';
            })
            ->addColumn('requested_by_name', function($adjustment) {
                return $adjustment->requester ? $adjustment->requester->name : 'N/A';
            })
            ->addColumn('actions', function($adjustment) {
                return '<div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-success approve-adjustment" data-id="' . $adjustment->adjustment_id . '">
                                <i class="fas fa-check"></i> Approuver
                            </button>
                            <button type="button" class="btn btn-danger reject-adjustment" data-id="' . $adjustment->adjustment_id . '">
                                <i class="fas fa-times"></i> Rejeter
                            </button>
                        </div>';
            })
            ->rawColumns(['reference_info', 'adjusted_quantity_formatted', 'actions'])
            ->make(true);
    }

    /**
     * Request stock adjustment
     */
    public function requestAdjustment(Request $request)
    {
        $request->validate([
            'adjustment_type' => 'required|in:product_famille,raw_material',
            'reference_id' => 'required',
            'new_quantity' => 'required|numeric|min:0',
            'reason' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldQuantity = 0;

            if ($request->adjustment_type == 'product_famille') {
                $request->validate([
                    'famille_id' => 'required|exists:familles,famille_id',
                ]);

                $familleStock = ProductFamilleStock::where('product_id', $request->reference_id)
                    ->where('famille_id', $request->famille_id)
                    ->first();

                $oldQuantity = $familleStock ? $familleStock->current_quantity : 0;

                StockAdjustment::create([
                    'adjustment_type' => 'product_famille',
                    'reference_id' => $request->reference_id,
                    'famille_id' => $request->famille_id,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $request->new_quantity,
                    'adjusted_quantity' => $request->new_quantity - $oldQuantity,
                    'reason' => $request->reason,
                    'status' => 'pending',
                    'requested_by' => Auth::id(),
                ]);
            }
            elseif ($request->adjustment_type == 'raw_material') {
                $material = RawMaterial::findOrFail($request->reference_id);
                $oldQuantity = $material->current_stock;

                StockAdjustment::create([
                    'adjustment_type' => 'raw_material',
                    'reference_id' => $request->reference_id,
                    'famille_id' => null,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $request->new_quantity,
                    'adjusted_quantity' => $request->new_quantity - $oldQuantity,
                    'reason' => $request->reason,
                    'status' => 'pending',
                    'requested_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Demande d\'ajustement créée avec succès. En attente d\'approbation.'
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
     * Approve stock adjustment
     */
    public function approveAdjustment($id)
    {
        DB::beginTransaction();
        try {
            $adjustment = StockAdjustment::findOrFail($id);

            if ($adjustment->status != 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet ajustement a déjà été traité.'
                ], 400);
            }

            if ($adjustment->adjustment_type == 'product_famille') {
                $familleStock = ProductFamilleStock::where('product_id', $adjustment->reference_id)
                    ->where('famille_id', $adjustment->famille_id)
                    ->first();

                if ($familleStock) {
                    $familleStock->current_quantity = $adjustment->new_quantity;
                    $familleStock->updateStock();
                } else {
                    // Create new stock record if it doesn't exist
                    $famille = Famille::find($adjustment->famille_id);
                    ProductFamilleStock::create([
                        'product_id' => $adjustment->reference_id,
                        'famille_id' => $adjustment->famille_id,
                        'famille_name' => $famille ? $famille->famille_name : null,
                        'current_quantity' => $adjustment->new_quantity,
                        'reserved_quantity' => 0,
                        'available_quantity' => $adjustment->new_quantity,
                        'last_updated' => now(),
                    ]);
                }
            }
            elseif ($adjustment->adjustment_type == 'raw_material') {
                $material      = RawMaterial::findOrFail($adjustment->reference_id);
                $previousStock = (float) $material->current_stock;

                $material->current_stock = (float) $adjustment->new_quantity;
                $material->save();

                $movement = RawMaterialStockMovement::create([
                    'material_id'      => $material->material_id,
                    'movement_type'    => 'adjustment',
                    'quantity'         => (float) $adjustment->adjusted_quantity,
                    'previous_stock'   => $previousStock,
                    'new_stock'        => (float) $adjustment->new_quantity,
                    'reference_number' => 'ADJ-' . date('YmdHis'),
                    'performed_by'     => Auth::id(),
                    'notes'            => $adjustment->reason,
                ]);

                $qty       = (float) $adjustment->adjusted_quantity;
                $unitPrice = (float) ($adjustment->unit_price ?? 0);

                StockMovementDetail::create([
                    'stock_movement_id'  => $movement->movement_id,
                    'material_id'        => $material->material_id,
                    'quantity'           => $qty,
                    'unit_price'         => $unitPrice,
                    'total_price'        => $qty * $unitPrice,
                    'remaining_quantity' => $qty,
                ]);
            }

            $adjustment->status = 'approved';
            $adjustment->approved_by = Auth::id();
            $adjustment->approved_at = now();
            $adjustment->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ajustement approuvé avec succès.'
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
     * Reject stock adjustment
     */
    public function rejectAdjustment(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|min:3'
        ]);

        DB::beginTransaction();
        try {
            $adjustment = StockAdjustment::findOrFail($id);

            if ($adjustment->status != 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet ajustement a déjà été traité.'
                ], 400);
            }

            $adjustment->status = 'rejected';
            $adjustment->approved_by = Auth::id();
            $adjustment->approved_at = now();
            $adjustment->admin_notes = $request->admin_notes;
            $adjustment->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ajustement rejeté.'
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
     * Get existing FIFO lots (StockMovementDetails) for a raw material
     */
    public function getMaterialDetails($materialId)
    {
        $material = RawMaterial::findOrFail($materialId);

        $details = StockMovementDetail::where('material_id', $materialId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($d) => [
                'stock_detail_id'    => $d->stock_detail_id,
                'unit_price'         => (float) $d->unit_price,
                'remaining_quantity' => (float) $d->remaining_quantity,
                'quantity'           => (float) $d->quantity,
                'date'               => $d->created_at->format('d/m/Y'),
            ]);

        return response()->json([
            'success'       => true,
            'current_stock' => (float) $material->current_stock,
            'unit'          => $material->unit_of_measure ?? 'U',
            'details'       => $details,
        ]);
    }

    /**
     * Submit a raw-material stock adjustment for approval (pending)
     */
    public function directAdjustRawMaterial(Request $request)
    {
        $request->validate([
            'material_id'    => 'required|exists:raw_materials,material_id',
            'quantity_to_add'=> 'required|numeric|min:0',
            'unit_price'     => 'required|numeric|min:0',
            'reason'         => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $material  = RawMaterial::findOrFail($request->material_id);
            $oldStock  = (float) $material->current_stock;
            $newStock  = (float) $request->quantity_to_add;
            $adjustedQty = $newStock - $oldStock;

            StockAdjustment::create([
                'adjustment_type'   => 'raw_material',
                'reference_id'      => $material->material_id,
                'famille_id'        => null,
                'old_quantity'      => $oldStock,
                'new_quantity'      => $newStock,
                'adjusted_quantity' => $adjustedQty,
                'unit_price'        => (float) $request->unit_price,
                'reason'            => $request->reason,
                'status'            => 'pending',
                'requested_by'      => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Demande d\'ajustement soumise. En attente d\'approbation.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit multiple stock adjustment requests (pending — requires approve to apply)
     */
    public function bulkRequestAdjustments(Request $request)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.famille_id' => 'required|integer',
            'items.*.new_quantity'=> 'required|numeric|min:0',
            'reason'             => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $count  = 0;
            $reason = $request->reason ?: 'Saisie groupée — en attente d\'approbation';

            foreach ($request->items as $item) {
                $productId   = (int) $item['product_id'];
                $familleId   = (int) $item['famille_id'];
                $newQuantity = (float) $item['new_quantity'];

                $familleStock = ProductFamilleStock::where('product_id', $productId)
                    ->where('famille_id', $familleId)
                    ->first();

                $oldQuantity = $familleStock ? (float) $familleStock->current_quantity : 0.0;

                StockAdjustment::create([
                    'adjustment_type'   => 'product_famille',
                    'reference_id'      => $productId,
                    'famille_id'        => $familleId,
                    'old_quantity'      => $oldQuantity,
                    'new_quantity'      => $newQuantity,
                    'adjusted_quantity' => $newQuantity - $oldQuantity,
                    'reason'            => $reason,
                    'status'            => 'pending',
                    'requested_by'      => Auth::id(),
                ]);

                $count++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $count . ' demande(s) soumise(s) avec succès. En attente d\'approbation.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve all pending adjustments at once
     */
    public function approveAllAdjustments()
    {
        DB::beginTransaction();
        try {
            $adjustments = StockAdjustment::where('status', 'pending')->get();
            $count = 0;

            foreach ($adjustments as $adjustment) {
                if ($adjustment->adjustment_type === 'product_famille') {
                    $familleStock = ProductFamilleStock::where('product_id', $adjustment->reference_id)
                        ->where('famille_id', $adjustment->famille_id)
                        ->first();

                    if ($familleStock) {
                        $familleStock->current_quantity = $adjustment->new_quantity;
                        $familleStock->updateStock();
                    } else {
                        $famille = Famille::find($adjustment->famille_id);
                        ProductFamilleStock::create([
                            'product_id'         => $adjustment->reference_id,
                            'famille_id'         => $adjustment->famille_id,
                            'famille_name'       => $famille ? $famille->famille_name : null,
                            'current_quantity'   => $adjustment->new_quantity,
                            'reserved_quantity'  => 0,
                            'available_quantity' => $adjustment->new_quantity,
                            'last_updated'       => now(),
                        ]);
                    }
                } elseif ($adjustment->adjustment_type === 'raw_material') {
                    $material = RawMaterial::find($adjustment->reference_id);
                    if (!$material) continue;

                    $previousStock = (float) $material->current_stock;
                    $material->current_stock = (float) $adjustment->new_quantity;
                    $material->save();

                    $movement = RawMaterialStockMovement::create([
                        'material_id'      => $material->material_id,
                        'movement_type'    => 'adjustment',
                        'quantity'         => (float) $adjustment->adjusted_quantity,
                        'previous_stock'   => $previousStock,
                        'new_stock'        => (float) $adjustment->new_quantity,
                        'reference_number' => 'ADJ-' . date('YmdHis') . '-' . $material->material_id,
                        'performed_by'     => Auth::id(),
                        'notes'            => $adjustment->reason,
                    ]);

                    $qty       = (float) $adjustment->adjusted_quantity;
                    $unitPrice = (float) ($adjustment->unit_price ?? 0);
                    StockMovementDetail::create([
                        'stock_movement_id'  => $movement->movement_id,
                        'material_id'        => $material->material_id,
                        'quantity'           => $qty,
                        'unit_price'         => $unitPrice,
                        'total_price'        => $qty * $unitPrice,
                        'remaining_quantity' => $qty,
                    ]);
                }

                $adjustment->status      = 'approved';
                $adjustment->approved_by = Auth::id();
                $adjustment->approved_at = now();
                $adjustment->save();
                $count++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $count . ' ajustement(s) approuvé(s) avec succès.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get product families for stock adjustment
     */
    public function getProductFamilies($productId)
    {
        $product = Product::with('familles')->findOrFail($productId);

        $families = $product->familles->map(function($famille) use ($product) {
            $familleStock = ProductFamilleStock::where('product_id', $product->product_id)
                ->where('famille_id', $famille->famille_id)
                ->first();

            return [
                'id' => $famille->famille_id,
                'name' => $famille->famille_name,
                'current_stock' => $familleStock ? $familleStock->current_quantity : 0
            ];
        });

        return response()->json([
            'success' => true,
            'families' => $families
        ]);
    }
}
