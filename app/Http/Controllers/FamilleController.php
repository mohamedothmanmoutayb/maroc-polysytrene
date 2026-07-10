<?php

namespace App\Http\Controllers;

use App\Models\Famille;
use App\Models\Product;
use App\Models\ProductFamille;
use App\Models\ProductFamilleStock;
use App\Models\ProductStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FamilleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_families')->only(['index', 'show', 'getByProduct', 'getStockInfo']);
        $this->middleware('can:create_families')->only(['create', 'store']);
        $this->middleware('can:edit_families')->only(['edit', 'update', 'adjustStock', 'manageProducts']);
        $this->middleware('can:delete_families')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $familles = Famille::with(['products', 'stocks'])
                ->select('familles.*');

            // Apply product filter
            if ($request->filled('product_id')) {
                $familles->whereHas('products', function($query) use ($request) {
                    $query->where('products.product_id', $request->product_id);
                });
            }

            // Apply status filter
            if ($request->filled('is_active') && $request->is_active !== '') {
                $familles->where('is_active', $request->is_active);
            }

            // Apply search
            if ($request->filled('search') && !empty($request->search['value'])) {
                $searchTerm = $request->search['value'];
                $familles->where(function($query) use ($searchTerm) {
                    $query->where('famille_code', 'like', '%' . $searchTerm . '%')
                        ->orWhere('famille_name', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('products', function($q) use ($searchTerm) {
                            $q->where('product_name', 'like', '%' . $searchTerm . '%')
                                ->orWhere('product_code', 'like', '%' . $searchTerm . '%');
                        });
                });
            }

            // Apply sorting - handle column names properly
            if ($request->has('order') && count($request->order) > 0) {
                $orderColumnIndex = $request->order[0]['column'];
                $orderDirection = $request->order[0]['dir'];
                $orderColumnName = $request->columns[$orderColumnIndex]['data'];

                $columnMappings = [
                    'famille_code' => 'famille_code',
                    'famille_name' => 'famille_name',
                    'created_at' => 'created_at',
                ];

                if (isset($columnMappings[$orderColumnName])) {
                    $familles->orderBy($columnMappings[$orderColumnName], $orderDirection);
                } else {
                    $familles->orderBy('familles.created_at', 'desc');
                }
            } else {
                $familles->orderBy('familles.created_at', 'desc');
            }

            // Use DataTables native pagination - let the package handle it
            return DataTables::of($familles)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    return view('pages.familles.components.actions', ['famille' => $row])->render();
                })
                ->addColumn('product_info', function($row){
                    if ($row->products->count() > 0) {
                        $productNames = $row->products->take(3)->map(function($product) {
                            return $product->product_name . ' (' . $product->product_code . ')';
                        })->implode('<br>');

                        if ($row->products->count() > 3) {
                            $productNames .= '<br><small class="text-muted">+' . ($row->products->count() - 3) . ' plus</small>';
                        }

                        return $productNames;
                    }
                    return 'Aucun produit associé';
                })
                ->addColumn('stock_info', function($row){
                    $totalStock = $row->stocks->sum('current_quantity');
                    $availableStock = $row->stocks->sum('available_quantity');

                    return '<div>
                        <div>Stock total: <strong>' . number_format($totalStock, 2, ',', '.') . '</strong></div>
                        <div class="small text-muted">Disponible: ' . number_format($availableStock, 2, ',', '.') . '</div>
                    </div>';
                })
                ->addColumn('price_info', function($row){
                    return '<div class="price-info">
                        <div><small>Client:</small> <strong>' . number_format($row->prix_client, 2, ',', '.') . ' DH</strong></div>
                        <div><small>Grossiste:</small> <strong>' . number_format($row->prix_grossiste, 2, ',', '.') . ' DH</strong></div>
                        <div><small>Commercial:</small> <strong>' . number_format($row->prix_commercial, 2, ',', '.') . ' DH</strong></div>
                        <div><small>Spécial:</small> <strong>' . number_format($row->prix_special, 2, ',', '.') . ' DH</strong></div>
                        <div><small>Revient:</small> <strong>' . number_format($row->prix_revient, 2, ',', '.') . ' DH</strong></div>
                    </div>';
                })
                ->addColumn('status_badge', function($row){
                    return $row->is_active ?
                        '<span class="badge bg-success">Active</span>' :
                        '<span class="badge bg-danger">Inactive</span>';
                })
                ->editColumn('created_at', function($row){
                    return $row->created_at ? $row->created_at->format('d/m/Y H:i') : 'N/A';
                })
                ->rawColumns(['action', 'product_info', 'stock_info', 'price_info', 'status_badge', 'created_at'])
                ->make(true);
        }

        $products = Product::where('is_active', true)->get();
        return view('pages.familles.index', compact('products'));
    }

    public function create()
    {
        return view('pages.familles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'famille_code' => 'required|string|max:50|unique:familles,famille_code',
            'famille_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'prix_client' => 'nullable|numeric|min:0',
            'prix_grossiste' => 'nullable|numeric|min:0',
            'prix_commercial' => 'nullable|numeric|min:0',
            'prix_special' => 'nullable|numeric|min:0',
            'prix_revient' => 'nullable|numeric|min:0',
            'associated_products' => 'nullable|array',
            'associated_products.*' => 'nullable|exists:products,product_id',
            'quantity_per_unit' => 'nullable|array',
            'quantity_per_unit.*' => 'nullable|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $famille = Famille::create([
                'famille_code' => $request->famille_code,
                'famille_name' => $request->famille_name,
                'description' => $request->description,
                'is_active' => true,
                'sort_order' => 0,
                'prix_client' => $request->prix_client ?? 0,
                'prix_grossiste' => $request->prix_grossiste ?? 0,
                'prix_commercial' => $request->prix_commercial ?? 0,
                'prix_special' => $request->prix_special ?? 0,
                'prix_revient' => $request->prix_revient ?? 0,
            ]);

            // Build default pivot data for all active products
            $syncData = [];
            $allProducts = \App\Models\Product::where('is_active', true)
                ->orderBy('product_name')
                ->get(['product_id']);

            foreach ($allProducts as $index => $product) {
                $syncData[$product->product_id] = [
                    'quantity_per_unit' => 1,
                    'sort_order'        => $index,
                    'prix_client'       => $famille->prix_client,
                    'prix_grossiste'    => $famille->prix_grossiste,
                    'prix_commercial'   => $famille->prix_commercial,
                    'prix_special'      => $famille->prix_special,
                ];
            }

            // Allow manual overrides from the form if provided
            if ($request->filled('associated_products')) {
                foreach ($request->associated_products as $index => $productId) {
                    if (empty($productId)) {
                        continue;
                    }
                    if (isset($syncData[$productId])) {
                        $syncData[$productId]['quantity_per_unit'] = $request->quantity_per_unit[$index] ?? 1;
                    }
                }
            }

            if (!empty($syncData)) {
                $famille->products()->sync($syncData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Famille créée avec succès!',
                'famille_id' => $famille->famille_id,
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
        $famille = Famille::with(['stocks.product', 'outputs' => function($query) {
            $query->orderBy('production_date', 'desc')->take(10);
        }])->findOrFail($id);

        $stockMovements = $famille->stockMovements()
            ->with(['product', 'performer'])
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('pages.familles.show', compact('famille', 'stockMovements'));
    }

    public function getProductsData(Request $request, $id)
    {
        $famille = Famille::findOrFail($id);
        $editable = $request->boolean('editable');

        $query = DB::table('product_famille as pf')
            ->join('products as p', 'pf.product_id', '=', 'p.product_id')
            ->leftJoin('product_famille_stock as pfs', function ($j) use ($id) {
                $j->on('pfs.product_id', '=', 'p.product_id')
                  ->where('pfs.famille_id', '=', $id);
            })
            ->where('pf.famille_id', $id)
            ->select([
                'p.product_id',
                'p.product_name',
                'p.product_code',
                'p.volume_m3',
                'pf.prix_client',
                'pf.prix_grossiste',
                'pf.prix_commercial',
                'pf.prix_special',
                DB::raw('COALESCE(pfs.current_quantity, 0) as current_quantity'),
                DB::raw('COALESCE(pfs.available_quantity, 0) as available_quantity'),
            ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) use ($id, $editable) {
                if ($editable) {
                    return '<button class="btn btn-sm btn-danger btn-detach-product"
                        data-product-id="' . $row->product_id . '"
                        data-product-name="' . htmlspecialchars($row->product_name, ENT_QUOTES) . '"
                        title="Retirer"><i class="fas fa-times"></i></button>';
                }
                $btn = '<a href="' . route('products.show', $row->product_id) . '" class="btn btn-sm btn-info" title="Voir"><i class="fas fa-eye"></i></a>';
                if ($row->current_quantity > 0) {
                    $btn .= ' <button class="btn btn-sm btn-warning btn-adjust-stock"
                        data-famille-id="' . $id . '"
                        data-product-id="' . $row->product_id . '"
                        data-current-stock="' . $row->current_quantity . '"
                        title="Ajuster Stock"><i class="fas fa-adjust"></i></button>';
                }
                return $btn;
            })
            ->editColumn('prix_client',    fn($r) => number_format($r->prix_client, 2, ',', '.') . ' DH')
            ->editColumn('prix_grossiste', fn($r) => number_format($r->prix_grossiste, 2, ',', '.') . ' DH')
            ->editColumn('prix_commercial',fn($r) => number_format($r->prix_commercial, 2, ',', '.') . ' DH')
            ->editColumn('prix_special',   fn($r) => number_format($r->prix_special, 2, ',', '.') . ' DH')
            ->editColumn('current_quantity', fn($r) => number_format($r->current_quantity, 2, ',', '.')
                . '<small class="text-muted d-block">Dispo: ' . number_format($r->available_quantity, 2, ',', '.') . '</small>')
            ->rawColumns(['action', 'current_quantity'])
            ->make(true);
    }

    public function edit($id)
    {
        $famille = Famille::with('products')->findOrFail($id);

        // Pass only what the JS needs — no full collection passed to the view
        $existingProducts = $famille->products->map(fn($p) => [
            'id'               => $p->product_id,
            'text'             => $p->product_name . ' (' . $p->product_code . ')' . ($p->volume_m3 ? ' - ' . number_format($p->volume_m3, 4) . ' m³' : ''),
            'volume_m3'        => (float) ($p->volume_m3 ?? 0),
            'quantity_per_unit'=> (float) ($p->pivot->quantity_per_unit ?? 1),
            'prix_client'      => (float) ($p->pivot->prix_client ?? 0),
            'prix_grossiste'   => (float) ($p->pivot->prix_grossiste ?? 0),
            'prix_commercial'  => (float) ($p->pivot->prix_commercial ?? 0),
            'prix_special'     => (float) ($p->pivot->prix_special ?? 0),
        ])->values();

        return view('pages.familles.edit', compact('famille', 'existingProducts'));
    }

    public function update(Request $request, $id)
    {
        $famille = Famille::findOrFail($id);

        $request->validate([
            'famille_code' => 'required|string|max:50|unique:familles,famille_code,' . $id . ',famille_id',
            'famille_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'prix_client' => 'nullable|numeric|min:0',
            'prix_grossiste' => 'nullable|numeric|min:0',
            'prix_commercial' => 'nullable|numeric|min:0',
            'prix_special' => 'nullable|numeric|min:0',
            'prix_revient' => 'nullable|numeric|min:0',
            'associated_products' => 'nullable|array',
            'associated_products.*' => 'nullable|exists:products,product_id',
            'quantity_per_unit' => 'nullable|array',
            'quantity_per_unit.*' => 'nullable|numeric|min:0.01',
            'apply_prices_to_products' => 'nullable|boolean', // Add validation for the new field
        ]);

        DB::beginTransaction();
        try {
            $oldName = $famille->famille_name;

            $famille->update([
                'famille_code' => $request->famille_code,
                'famille_name' => $request->famille_name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
                'prix_client' => $request->prix_client ?? 0,
                'prix_grossiste' => $request->prix_grossiste ?? 0,
                'prix_commercial' => $request->prix_commercial ?? 0,
                'prix_special' => $request->prix_special ?? 0,
                'prix_revient' => $request->prix_revient ?? 0,
            ]);

            if ($oldName !== $request->famille_name) {
                $famille->stocks()->update(['famille_name' => $request->famille_name]);
            }

            $syncData = [];
            if ($request->filled('associated_products')) {
                foreach ($request->associated_products as $index => $productId) {
                    if (empty($productId)) {
                        continue;
                    }

                    $syncData[$productId] = [
                        'quantity_per_unit' => $request->quantity_per_unit[$index] ?? 1,
                        'sort_order' => $index,
                    ];
                }
            }

            $famille->products()->sync($syncData);

            // Always apply famille prices (× volume) to all associated products
            $this->applyPricesToExistingProducts($famille->fresh(['products']));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Famille mise à jour avec succès!' . ($request->boolean('apply_prices_to_products') ? ' Les prix ont été appliqués à tous les produits associés.' : '')
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
     * Apply new famille prices to all associated products
     */
    private function applyPricesToExistingProducts(Famille $famille)
    {
        $products = $famille->products;

        foreach ($products as $product) {
            $volume = $product->volume_m3 ?? 1;

            $newClientPrice = $famille->prix_client * $volume;
            $newGrossistePrice = $famille->prix_grossiste * $volume;
            $newCommercialPrice = $famille->prix_commercial * $volume;
            $newSpecialPrice = $famille->prix_special * $volume;

            DB::table('product_famille')
                ->where('product_id', $product->product_id)
                ->where('famille_id', $famille->famille_id)
                ->update([
                    'prix_client' => $newClientPrice,
                    'prix_grossiste' => $newGrossistePrice,
                    'prix_commercial' => $newCommercialPrice,
                    'prix_special' => $newSpecialPrice,
                    'prix_client_m3' => $famille->prix_client,
                    'prix_grossiste_m3' => $famille->prix_grossiste,
                    'prix_commercial_m3' => $famille->prix_commercial,
                    'prix_special_m3' => $famille->prix_special,
                    'volume_applied' => $volume,
                    'updated_at' => now(),
                ]);
        }
    }

    public function destroy($id)
    {
        $famille = Famille::with(['stocks', 'outputs'])->findOrFail($id);

        $totalStock = $famille->stocks->sum('current_quantity');
        if ($totalStock > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une famille avec du stock'
            ], 400);
        }

        if ($famille->outputs->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une famille utilisée dans des productions'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $famille->stocks()->delete();
            $famille->products()->detach();
            $famille->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Famille supprimée avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByProduct($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $familles = $product->familles()
                ->where('is_active', true)
                ->orderBy('famille_name')
                ->get()
                ->map(function($famille) use ($productId) {
                    $pivot = $famille->products()->where('product_id', $productId)->first()->pivot;
                    $stock = $famille->stocks()->where('product_id', $productId)->first();

                    return [
                        'id' => $famille->famille_id,
                        'code' => $famille->famille_code,
                        'name' => $famille->famille_name,
                        'display_name' => $famille->famille_name . ' (' . $famille->famille_code . ')',
                        'quantity_per_unit' => $pivot->quantity_per_unit ?? 1,
                        'stock' => $stock ? $stock->current_quantity : 0,
                        'available_stock' => $stock ? $stock->available_quantity : 0,
                        'prix_client' => $famille->prix_client,
                        'prix_grossiste' => $famille->prix_grossiste,
                        'prix_commercial' => $famille->prix_commercial,
                        'prix_special' => $famille->prix_special,
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

    public function adjustStock(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'adjustment_type' => 'required|in:add,remove,set',
            'quantity' => 'required|numeric|min:0',
            'notes' => 'required|string|max:500',
            'adjustment_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $famille = Famille::findOrFail($id);
            $product = Product::findOrFail($request->product_id);

            if (!$famille->products()->where('product_id', $request->product_id)->exists()) {
                throw new \Exception('Cette famille n\'est pas associée à ce produit');
            }

            $stock = ProductFamilleStock::where('product_id', $request->product_id)
                ->where('famille_id', $id)
                ->first();

            if (!$stock) {
                $stock = ProductFamilleStock::create([
                    'product_id' => $request->product_id,
                    'famille_id' => $famille->famille_id,
                    'famille_name' => $famille->famille_name,
                    'current_quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                    'location' => 'Entrepôt Principal',
                    'last_updated' => now(),
                ]);
            }

            $previousStock = $stock->current_quantity;
            $adjustment = $request->quantity;
            $movementType = 'stock_adjustment';

            switch ($request->adjustment_type) {
                case 'add':
                    $newStock = $previousStock + $adjustment;
                    $quantity = $adjustment;
                    $movementType = 'manual_addition';
                    $stock->increment('current_quantity', $adjustment);
                    $stock->increment('available_quantity', $adjustment);
                    break;

                case 'remove':
                    if ($previousStock < $adjustment) {
                        throw new \Exception('Stock insuffisant pour effectuer ce retrait');
                    }
                    $newStock = $previousStock - $adjustment;
                    $quantity = -$adjustment;
                    $movementType = 'manual_removal';
                    $stock->decrement('current_quantity', $adjustment);
                    $stock->decrement('available_quantity', $adjustment);
                    break;

                case 'set':
                    $quantity = $adjustment - $previousStock;
                    $newStock = $adjustment;
                    $stock->current_quantity = $adjustment;
                    $stock->available_quantity = $adjustment - $stock->reserved_quantity;
                    break;
            }

            $stock->last_updated = now();
            if ($request->adjustment_type === 'add') {
                $stock->last_restocked = now();
            }
            $stock->save();

            ProductStockMovement::create([
                'product_id' => $request->product_id,
                'famille_id' => $famille->famille_id,
                'famille_name' => $famille->famille_name,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reference_type' => 'famille',
                'reference_id' => $famille->famille_id,
                'reference_number' => $famille->famille_code,
                'movement_date' => $request->adjustment_date,
                'performed_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock ajusté avec succès!',
                'new_stock' => $newStock,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStockInfo($productId, $familleId)
    {
        try {
            $stock = ProductFamilleStock::where('product_id', $productId)
                ->where('famille_id', $familleId)
                ->first();

            if (!$stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'current_quantity' => $stock->current_quantity,
                    'available_quantity' => $stock->available_quantity,
                    'reserved_quantity' => $stock->reserved_quantity,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function manageProducts(Request $request, $id)
    {
        $famille = Famille::findOrFail($id);

        $request->validate([
            'action' => 'required|in:attach,detach',
            'product_id' => 'required|exists:products,product_id',
            'quantity_per_unit' => 'nullable|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            if ($request->action === 'attach') {
                if ($famille->products()->where('product_id', $request->product_id)->exists()) {
                    throw new \Exception('Ce produit est déjà associé à cette famille');
                }

                $famille->products()->attach($request->product_id, [
                    'quantity_per_unit' => $request->quantity_per_unit ?? 1,
                ]);

                $message = 'Produit associé avec succès!';
            } else {
                $stock = $famille->stocks()->where('product_id', $request->product_id)->first();
                if ($stock && $stock->current_quantity > 0) {
                    throw new \Exception('Impossible de détacher un produit avec du stock');
                }

                if ($stock) {
                    $stock->delete();
                }

                $famille->products()->detach($request->product_id);
                $message = 'Produit détaché avec succès!';
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
}
