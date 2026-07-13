<?php

namespace App\Http\Controllers;

use App\Models\ProductionConsumption;
use App\Models\ProductionOrder;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ProductionConsumptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_production_consumption')->only(['index', 'show', 'getOrderConsumptions', 'getStatistics']);
        $this->middleware('can:create_production_output')->only(['create', 'store']);
        $this->middleware('can:edit_production_orders')->only(['edit', 'update']);
        $this->middleware('can:delete_production_orders')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $consumptions = ProductionConsumption::with(['productionOrder', 'rawMaterial'])
                ->select('production_consumption.*')
                ->when($request->filled('order_id'), function ($query) use ($request) {
                    return $query->where('production_order_id', $request->order_id);
                })
                ->when($request->filled('status'), function ($query) use ($request) {
                    return match ($request->status) {
                        'planned' => $query->where('actual_quantity_used', 0),
                        'conforme' => $query->where('actual_quantity_used', '!=', 0)
                            ->whereRaw('ABS(actual_quantity_used - planned_quantity) <= (planned_quantity * 0.05)'),
                        'under' => $query->where('actual_quantity_used', '!=', 0)
                            ->whereRaw('ABS(actual_quantity_used - planned_quantity) > (planned_quantity * 0.05)')
                            ->whereColumn('actual_quantity_used', '<', 'planned_quantity'),
                        'over' => $query->where('actual_quantity_used', '!=', 0)
                            ->whereRaw('ABS(actual_quantity_used - planned_quantity) > (planned_quantity * 0.05)')
                            ->whereColumn('actual_quantity_used', '>=', 'planned_quantity'),
                        default => $query,
                    };
                })
                ->when($request->filled('date_range'), function ($query) use ($request) {
                    // The filter sends ISO (Y-m-d) dates joined with ' to '
                    $dates = array_map('trim', explode(' to ', $request->date_range));

                    if (count($dates) == 2) {
                        $start = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
                        $end = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();

                        return $query->whereHas('productionOrder', function ($q) use ($start, $end) {
                            $q->whereBetween('start_date', [$start, $end]);
                        });
                    }

                    $date = Carbon::createFromFormat('Y-m-d', $dates[0]);
                    return $query->whereHas('productionOrder', function ($q) use ($date) {
                        $q->whereDate('start_date', $date);
                    });
                });

            return DataTables::of($consumptions)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $dropdown = '<div class="dropdown dropstart">
                        <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3" href="'.route('production-consumption.show', $row->consumption_id).'">
                                    <i class="fs-4 ti ti-eye"></i>Voir Détails
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3" href="'.route('production-consumption.edit', $row->consumption_id).'">
                                    <i class="fs-4 ti ti-edit"></i>Modifier
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 delete" href="javascript:void(0)"
                                   data-id="'.$row->consumption_id.'"
                                   data-reference="'.$row->production_order_id.'">
                                    <i class="fs-4 ti ti-trash text-danger"></i><span class="text-danger">Supprimer</span>
                                </a>
                            </li>
                        </ul>
                    </div>';
                    return $dropdown;
                })
                ->addColumn('consumption_date', function($row){
                    // production_consumption.created_at is stamped by MySQL's CURRENT_TIMESTAMP,
                    // which uses the DB server's system timezone (America/New_York), not UTC.
                    return $row->created_at
                        ? Carbon::parse($row->created_at, 'America/New_York')
                            ->setTimezone('Africa/Casablanca')
                            ->format('d/m/Y H:i')
                        : 'N/A';
                })
                ->addColumn('order_number', function($row){
                    return $row->productionOrder ? $row->productionOrder->order_number : 'N/A';
                })
                ->addColumn('material_name', function($row){
                    return $row->rawMaterial ? $row->rawMaterial->material_name : 'N/A';
                })
                ->addColumn('material_code', function($row){
                    return $row->rawMaterial ? $row->rawMaterial->material_code : 'N/A';
                })
                ->addColumn('consumption_status', function($row){
                    if ($row->actual_quantity_used == 0) {
                        return '<span class="badge badge-secondary">Planifié</span>';
                    } elseif (abs($row->actual_quantity_used - $row->planned_quantity) <= ($row->planned_quantity * 0.05)) {
                        return '<span class="badge badge-success">Conforme</span>';
                    } elseif ($row->actual_quantity_used < $row->planned_quantity) {
                        return '<span class="badge badge-info">Sous-consommation</span>';
                    } else {
                        return '<span class="badge badge-warning">Sur-consommation</span>';
                    }
                })
                ->addColumn('waste_percentage', function($row){
                    if ($row->actual_quantity_used > 0) {
                        $wastePercent = ($row->waste_quantity / $row->actual_quantity_used) * 100;
                        return number_format($wastePercent, 2, ',', '.') . '%';
                    }
                    return '0%';
                })
                ->editColumn('planned_quantity', function($row){
                    return number_format($row->planned_quantity, 2, ',', '.') . ' ' . $row->rawMaterial->unit_of_measure;
                })
                ->editColumn('actual_quantity_used', function($row){
                    return number_format($row->actual_quantity_used, 2, ',', '.') . ' ' . $row->rawMaterial->unit_of_measure;
                })
                ->editColumn('waste_quantity', function($row){
                    return number_format($row->waste_quantity, 2, ',', '.') . ' ' . $row->rawMaterial->unit_of_measure;
                })
                ->editColumn('unit_cost', function($row){
                    return number_format($row->unit_cost, 2, ',', '.') . ' DH';
                })
                ->editColumn('total_cost', function($row){
                    return number_format($row->total_cost, 2, ',', '.') . ' DH';
                })
                ->rawColumns(['action', 'consumption_status'])
                ->make(true);
        }

        $productionOrders = ProductionOrder::whereIn('status', ['in_progress', 'completed'])
            ->with('product')
            ->get();

        return view('pages.production-consumption.index', compact('productionOrders'));
    }

    public function create(Request $request)
    {
        $order_id = $request->get('order_id');
        $productionOrder = null;

        if ($order_id) {
            $productionOrder = ProductionOrder::with(['product', 'consumptions.rawMaterial'])->find($order_id);
        }

        // Get in-progress or completed production orders
        $productionOrders = ProductionOrder::whereIn('status', ['in_progress', 'completed'])
            ->with('product')
            ->get();

        // Get raw materials
        $rawMaterials = RawMaterial::where('is_active', true)->get();

        return view('pages.production-consumption.create', compact('productionOrders', 'productionOrder', 'rawMaterials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'production_order_id' => 'required|exists:production_orders,order_id',
            'material_id' => 'required|exists:raw_materials,material_id',
            'actual_quantity_used' => 'required|numeric|min:0',
            'waste_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $productionOrder = ProductionOrder::findOrFail($request->production_order_id);
            $rawMaterial = RawMaterial::findOrFail($request->material_id);

            // Check if consumption record already exists for this order and material
            $existingConsumption = ProductionConsumption::where('production_order_id', $request->production_order_id)
                ->where('material_id', $request->material_id)
                ->first();

            if ($existingConsumption) {
                // Update existing consumption
                $existingConsumption->update([
                    'actual_quantity_used' => $request->actual_quantity_used,
                    'waste_quantity' => $request->waste_quantity,
                    'total_cost' => $request->actual_quantity_used * $rawMaterial->unit_cost,
                    'notes' => $request->notes,
                ]);

                $consumption = $existingConsumption;
            } else {
                // Get planned quantity from BOM if available
                $bom = DB::table('bill_of_materials')
                    ->where('product_id', $productionOrder->product_id)
                    ->where('material_id', $request->material_id)
                    ->first();

                $plannedQuantity = $bom ? $bom->quantity_per_unit * $productionOrder->quantity_to_produce : 0;

                // Create new consumption record
                $consumption = ProductionConsumption::create([
                    'production_order_id' => $request->production_order_id,
                    'material_id' => $request->material_id,
                    'planned_quantity' => $plannedQuantity,
                    'actual_quantity_used' => $request->actual_quantity_used,
                    'waste_quantity' => $request->waste_quantity,
                    'unit_cost' => $rawMaterial->unit_cost,
                    'total_cost' => $request->actual_quantity_used * $rawMaterial->unit_cost,
                    'notes' => $request->notes,
                ]);
            }

            // Update raw material stock (consumption was already reserved when production started)
            $rawMaterial->current_stock -= $request->actual_quantity_used + $request->waste_quantity;
            $rawMaterial->save();

            // Record stock movement for actual consumption
            DB::table('raw_material_stock_movements')->insert([
                'material_id' => $rawMaterial->material_id,
                'movement_type' => 'production_consumption_actual',
                'quantity' => -($request->actual_quantity_used + $request->waste_quantity),
                'previous_stock' => $rawMaterial->current_stock + $request->actual_quantity_used + $request->waste_quantity,
                'new_stock' => $rawMaterial->current_stock,
                'reference_type' => 'production_order',
                'reference_id' => $productionOrder->order_id,
                'movement_date' => now(),
                'performed_by' => auth()->id(),
                'notes' => 'Consommation réelle pour ordre ' . $productionOrder->order_number
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Consommation enregistrée avec succès!',
                'consumption_id' => $consumption->consumption_id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $consumption = ProductionConsumption::with([
            'productionOrder.product',
            'rawMaterial'
        ])->findOrFail($id);

        return view('pages.production-consumption.show', compact('consumption'));
    }

    public function edit($id)
    {
        $consumption = ProductionConsumption::with([
            'productionOrder.product',
            'rawMaterial'
        ])->findOrFail($id);

        $productionOrders = ProductionOrder::whereIn('status', ['in_progress', 'completed'])
            ->with('product')
            ->get();

        $rawMaterials = RawMaterial::where('is_active', true)->get();

        return view('pages.production-consumption.edit', compact('consumption', 'productionOrders', 'rawMaterials'));
    }

    public function update(Request $request, $id)
    {
        $consumption = ProductionConsumption::with(['productionOrder', 'rawMaterial'])->findOrFail($id);

        $request->validate([
            'actual_quantity_used' => 'required|numeric|min:0',
            'waste_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $rawMaterial = $consumption->rawMaterial;

            // Calculate stock adjustment
            $oldTotalUsed = $consumption->actual_quantity_used + $consumption->waste_quantity;
            $newTotalUsed = $request->actual_quantity_used + $request->waste_quantity;
            $quantityDifference = $newTotalUsed - $oldTotalUsed;

            // Update raw material stock
            $newStock = $rawMaterial->current_stock - $quantityDifference;

            // Prevent negative stock
            if ($newStock < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le stock ne peut pas devenir négatif'
                ], 400);
            }

            $rawMaterial->current_stock = $newStock;
            $rawMaterial->save();

            // Record stock movement
            DB::table('raw_material_stock_movements')->insert([
                'material_id' => $rawMaterial->material_id,
                'movement_type' => 'production_consumption_adjustment',
                'quantity' => -$quantityDifference,
                'previous_stock' => $rawMaterial->current_stock + $quantityDifference,
                'new_stock' => $rawMaterial->current_stock,
                'reference_type' => 'production_consumption',
                'reference_id' => $consumption->consumption_id,
                'movement_date' => now(),
                'performed_by' => auth()->id(),
                'notes' => 'Ajustement consommation #' . $consumption->consumption_id
            ]);

            // Update consumption
            $consumption->update([
                'actual_quantity_used' => $request->actual_quantity_used,
                'waste_quantity' => $request->waste_quantity,
                'total_cost' => $request->actual_quantity_used * $rawMaterial->unit_cost,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Consommation mise à jour avec succès!'
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
            $consumption = ProductionConsumption::with(['productionOrder', 'rawMaterial'])->findOrFail($id);

            // Return consumed quantity to stock
            $totalUsed = $consumption->actual_quantity_used + $consumption->waste_quantity;
            $rawMaterial = $consumption->rawMaterial;

            $rawMaterial->current_stock += $totalUsed;
            $rawMaterial->save();

            // Record stock movement
            DB::table('raw_material_stock_movements')->insert([
                'material_id' => $rawMaterial->material_id,
                'movement_type' => 'production_consumption_reversal',
                'quantity' => $totalUsed,
                'previous_stock' => $rawMaterial->current_stock - $totalUsed,
                'new_stock' => $rawMaterial->current_stock,
                'reference_type' => 'production_consumption',
                'reference_id' => $consumption->consumption_id,
                'movement_date' => now(),
                'performed_by' => auth()->id(),
                'notes' => 'Annulation consommation #' . $consumption->consumption_id
            ]);

            // Delete consumption record
            $consumption->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Consommation supprimée avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrderConsumptions($order_id)
    {
        $consumptions = ProductionConsumption::with('rawMaterial')
            ->where('production_order_id', $order_id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $consumptions
        ]);
    }

    public function getStatistics()
    {
        $totalConsumptionCost = ProductionConsumption::sum('total_cost');
        $totalWasteCost = ProductionConsumption::sum(DB::raw('waste_quantity * unit_cost'));

        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $monthEnd = now()->endOfMonth()->format('Y-m-d');

        $monthlyConsumption = ProductionConsumption::whereHas('productionOrder', function($query) use ($monthStart, $monthEnd) {
            $query->whereBetween('created_at', [$monthStart, $monthEnd]);
        })->sum('total_cost');

        $topMaterials = ProductionConsumption::selectRaw('material_id, SUM(actual_quantity_used) as total_used, SUM(total_cost) as total_cost')
            ->with('rawMaterial')
            ->groupBy('material_id')
            ->orderBy('total_cost', 'DESC')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_consumption_cost' => $totalConsumptionCost,
                'total_waste_cost' => $totalWasteCost,
                'monthly_consumption' => $monthlyConsumption,
                'top_materials' => $topMaterials
            ]
        ]);
    }
}
