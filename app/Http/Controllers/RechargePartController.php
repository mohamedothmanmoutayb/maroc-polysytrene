<?php

namespace App\Http\Controllers;

use App\Models\RechargePart;
use App\Models\RechargePartStockMovement;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RechargePartController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_recharge_parts')->only(['index', 'show', 'getStatistics', 'getLowStock', 'history']);
        $this->middleware('can:create_recharge_parts')->only(['create', 'store']);
        $this->middleware('can:edit_recharge_parts')->only(['edit', 'update']);
        $this->middleware('can:delete_recharge_parts')->only(['destroy']);
        $this->middleware('can:adjust_recharge_parts_stock')->only(['adjustStock']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $parts = RechargePart::query();

            return DataTables::of($parts)
                ->addIndexColumn()
                ->addColumn('stock_status', function($row) {
                    return $row->stock_status;
                })
                ->addColumn('action', function($row) {
                    $user = auth()->user();
                    $btn = '<div class="dropdown dropstart">';
                    $btn .= '<a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical fs-6"></i>
                            </a>';
                    $btn .= '<ul class="dropdown-menu dropdown-menu-end">';
                    if ($user->can('edit_recharge_parts')) {
                        $btn .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="editPart(' . $row->id . ')">
                                    <i class="fas fa-edit me-2"></i>Modifier
                                </a></li>';
                    }
                    if ($user->can('adjust_recharge_parts_stock')) {
                        $btn .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="adjustStock(' . $row->id . ', \'' . $row->name . '\', \'in\')">
                                    <i class="fas fa-plus-circle me-2 text-success"></i>Ajouter Stock
                                </a></li>';
                        $btn .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="adjustStock(' . $row->id . ', \'' . $row->name . '\', \'out\')">
                                    <i class="fas fa-minus-circle me-2 text-danger"></i>Retirer Stock
                                </a></li>';
                    }
                    if ($user->can('view_recharge_parts')) {
                        $btn .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="showHistory(' . $row->id . ', \'' . $row->name . '\')">
                                    <i class="fas fa-history me-2"></i>Historique
                                </a></li>';
                    }
                    if ($user->can('delete_recharge_parts')) {
                        $btn .= '<li><hr class="dropdown-divider"></li>';
                        $btn .= '<li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deletePart(' . $row->id . ', \'' . $row->name . '\')">
                                    <i class="fas fa-trash me-2"></i>Supprimer
                                </a></li>';
                    }
                    $btn .= '</ul></div>';
                    return $btn;
                })
                ->editColumn('current_stock', function($row) {
                    return number_format($row->current_stock) . ' unités';
                })
                ->editColumn('min_stock', function($row) {
                    return number_format($row->min_stock) . ' unités';
                })
                ->editColumn('max_stock', function($row) {
                    return $row->max_stock ? number_format($row->max_stock) . ' unités' : 'Illimité';
                })
                ->rawColumns(['action', 'stock_status'])
                ->make(true);
        }

        return view('pages.recharge-parts.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:recharge_parts_stock,name',
            'current_stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ]);

        try {
            $part = RechargePart::create([
                'name' => $request->name,
                'current_stock' => $request->current_stock,
                'min_stock' => $request->min_stock,
                'max_stock' => $request->max_stock,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pièce ajoutée avec succès!',
                'data' => $part
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $part = RechargePart::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $part
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pièce non trouvée'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:recharge_parts_stock,name,' . $id,
            'current_stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ]);

        try {
            $part = RechargePart::findOrFail($id);
            $part->update([
                'name' => $request->name,
                'current_stock' => $request->current_stock,
                'min_stock' => $request->min_stock,
                'max_stock' => $request->max_stock,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pièce modifiée avec succès!',
                'data' => $part
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Adjust stock quantity (add or remove)
     */
    public function adjustStock(Request $request, $id)
    {
        $request->validate([
            'movement_type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $part = RechargePart::findOrFail($id);
            $oldStock = $part->current_stock;
            $newStock = $request->movement_type === 'in'
                ? $oldStock + $request->quantity
                : $oldStock - $request->quantity;

            if ($newStock < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le stock ne peut pas être négatif!'
                ], 400);
            }

            $part->current_stock = $newStock;
            $part->save();

            RechargePartStockMovement::create([
                'part_id' => $part->id,
                'movement_type' => $request->movement_type,
                'quantity' => $request->quantity,
                'previous_stock' => $oldStock,
                'new_stock' => $newStock,
                'reason' => $request->reason,
                'performed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock ajusté avec succès!',
                'data' => [
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
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
     * Get stock movement history for a part
     */
    public function history($id)
    {
        try {
            $part = RechargePart::findOrFail($id);

            $movements = $part->movements()
                ->with('performer:id,username')
                ->get()
                ->map(function ($movement) {
                    return [
                        'date' => $movement->created_at->format('d/m/Y H:i'),
                        'movement_type' => $movement->movement_type,
                        'quantity' => $movement->quantity,
                        'previous_stock' => $movement->previous_stock,
                        'new_stock' => $movement->new_stock,
                        'reason' => $movement->reason,
                        'performed_by' => $movement->performer->username ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'part_name' => $part->name,
                'data' => $movements,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pièce non trouvée'
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $part = RechargePart::findOrFail($id);
            $part->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pièce supprimée avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock items
     */
    public function getLowStock()
    {
        $lowStockItems = RechargePart::whereRaw('current_stock <= min_stock')->get();

        return response()->json([
            'success' => true,
            'data' => $lowStockItems,
            'count' => $lowStockItems->count()
        ]);
    }

    /**
     * Get dashboard statistics
     */
    public function getStatistics()
    {
        $totalParts = RechargePart::count();
        $totalStock = RechargePart::sum('current_stock');
        $lowStockCount = RechargePart::whereRaw('current_stock <= min_stock')->count();
        $outOfStock = RechargePart::where('current_stock', 0)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_parts' => $totalParts,
                'total_stock' => $totalStock,
                'low_stock_count' => $lowStockCount,
                'out_of_stock' => $outOfStock
            ]
        ]);
    }
}
