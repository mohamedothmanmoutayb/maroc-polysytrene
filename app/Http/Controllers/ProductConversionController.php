<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductConversion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductConversionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $conversions = ProductConversion::with(['parentProduct', 'childProduct'])
                ->select('product_conversions.*');

            return DataTables::of($conversions)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $dropdown = '<div class="dropdown dropstart">
                        <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 edit-conversion" href="javascript:void(0)"
                                   data-id="'.$row->conversion_id.'">
                                    <i class="fs-4 ti ti-edit"></i>Modifier
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 delete-conversion" href="javascript:void(0)"
                                   data-id="'.$row->conversion_id.'"
                                   data-parent="'.$row->parent_product_id.'"
                                   data-child="'.$row->child_product_id.'">
                                    <i class="fs-4 ti ti-trash text-danger"></i><span class="text-danger">Supprimer</span>
                                </a>
                            </li>
                        </ul>
                    </div>';
                    return $dropdown;
                })
                ->addColumn('parent_product_name', function($row){
                    return $row->parentProduct ? $row->parentProduct->product_name : 'N/A';
                })
                ->addColumn('child_product_name', function($row){
                    return $row->childProduct ? $row->childProduct->product_name : 'N/A';
                })
                ->addColumn('parent_product_unit', function($row){
                    return $row->parentProduct ? $row->parentProduct->unit_of_measure : 'N/A';
                })
                ->addColumn('child_product_unit', function($row){
                    return $row->childProduct ? $row->childProduct->unit_of_measure : 'N/A';
                })
                ->addColumn('conversion_formula', function($row){
                    return '1 ' . ($row->parentProduct ? $row->parentProduct->unit_of_measure : '') .
                           ' = ' . number_format($row->conversion_rate, 4) .
                           ' ' . ($row->childProduct ? $row->childProduct->unit_of_measure : '');
                })
                ->addColumn('status_badge', function($row){
                    return $row->is_active
                        ? '<span class="badge badge-success">Actif</span>'
                        : '<span class="badge badge-danger">Inactif</span>';
                })
                ->addColumn('waste_info', function($row){
                    if ($row->waste_percentage > 0) {
                        return '<span class="badge badge-warning">'.number_format($row->waste_percentage, 2, ',', '.').'% déchet</span>';
                    }
                    return '<span class="badge badge-success">Pas de déchet</span>';
                })
                ->addColumn('effective_rate', function($row){
                    $effectiveRate = $row->conversion_rate * (1 - ($row->waste_percentage / 100));
                    return number_format($effectiveRate, 4);
                })
                ->editColumn('conversion_rate', function($row){
                    return number_format($row->conversion_rate, 4);
                })
                ->editColumn('waste_percentage', function($row){
                    return number_format($row->waste_percentage, 2, ',', '.') . '%';
                })
                ->rawColumns(['action', 'status_badge', 'waste_info', 'conversion_formula'])
                ->make(true);
        }

        $productionProducts = Product::where('is_active', true)
            ->where(function($query) {
                $query->where('product_type', 'production')
                      ->orWhere('product_type', 'both');
            })
            ->orderBy('product_name')
            ->get();

        $salesProducts = Product::where('is_active', true)
            ->where(function($query) {
                $query->where('product_type', 'sales')
                      ->orWhere('product_type', 'both');
            })
            ->orderBy('product_name')
            ->get();

        return view('pages.product-conversions.index', compact('productionProducts', 'salesProducts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_product_id' => 'required|exists:products,product_id',
            'child_product_id' => 'required|exists:products,product_id|different:parent_product_id',
            'conversion_rate' => 'required|numeric|min:0.0001',
            'waste_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $existingConversion = ProductConversion::where('parent_product_id', $request->parent_product_id)
            ->where('child_product_id', $request->child_product_id)
            ->first();

        if ($existingConversion) {
            return response()->json([
                'success' => false,
                'message' => 'Cette conversion existe déjà!'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $conversion = ProductConversion::create([
                'parent_product_id' => $request->parent_product_id,
                'child_product_id' => $request->child_product_id,
                'conversion_rate' => $request->conversion_rate,
                'waste_percentage' => $request->waste_percentage ?? 0,
                'is_active' => true,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conversion ajoutée avec succès!',
                'conversion' => $conversion->load(['parentProduct', 'childProduct'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $conversion = ProductConversion::with(['parentProduct', 'childProduct'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'conversion' => $conversion
        ]);
    }

    public function update(Request $request, $id)
    {
        $conversion = ProductConversion::findOrFail($id);

        $request->validate([
            'conversion_rate' => 'required|numeric|min:0.0001',
            'waste_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $conversion->update([
                'conversion_rate' => $request->conversion_rate,
                'waste_percentage' => $request->waste_percentage ?? 0,
                'is_active' => $request->is_active ?? true,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Conversion mise à jour avec succès!'
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
            $conversion = ProductConversion::findOrFail($id);
            $conversion->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conversion supprimée avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getConversionInfo($parentProductId)
    {
        $conversions = ProductConversion::where('parent_product_id', $parentProductId)
            ->where('is_active', true)
            ->with('childProduct')
            ->get();

        return response()->json([
            'success' => true,
            'conversions' => $conversions
        ]);
    }

    public function getStatistics()
    {
        $totalConversions = ProductConversion::count();
        $activeConversions = ProductConversion::where('is_active', true)->count();
        $productionsWithConversion = ProductConversion::distinct('parent_product_id')->count('parent_product_id');
        $averageWaste = ProductConversion::avg('waste_percentage');

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalConversions,
                'active' => $activeConversions,
                'productions_with_conversion' => $productionsWithConversion,
                'average_waste' => number_format($averageWaste, 2, ',', '.')
            ]
        ]);
    }
}
