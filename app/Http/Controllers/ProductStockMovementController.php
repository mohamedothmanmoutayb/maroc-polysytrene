<?php

namespace App\Http\Controllers;

use App\Models\ProductStockMovement;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductStockMovementController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $movements = ProductStockMovement::with(['product', 'famille'])
                ->when($request->filled('famille_id'), function($query) use ($request) {
                    $query->where('famille_id', $request->famille_id);
                })
                ->when($request->filled('product_id'), function($query) use ($request) {
                    $query->where('product_id', $request->product_id);
                })
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc');

            return DataTables::of($movements)
                ->addIndexColumn()
                ->addColumn('product_name', function($row) {
                    return $row->product ? $row->product->product_name : 'N/A';
                })
                ->addColumn('famille_name', function($row) {
                    return $row->famille ? $row->famille->famille_name : 'N/A';
                })
                ->addColumn('movement_type_badge', function($row) {
                    $badges = [
                        'initial_stock' => 'secondary',
                        'manual_addition' => 'success',
                        'manual_removal' => 'danger',
                        'production_output' => 'info',
                        'sales' => 'warning',
                        'adjustment' => 'primary',
                    ];
                    $labels = [
                        'initial_stock' => 'Stock Initial',
                        'manual_addition' => 'Ajout Manuel',
                        'manual_removal' => 'Retrait Manuel',
                        'production_output' => 'Sortie Production',
                        'sales' => 'Vente',
                        'adjustment' => 'Ajustement',
                    ];
                    $color = $badges[$row->movement_type] ?? 'secondary';
                    $label = $labels[$row->movement_type] ?? $row->movement_type;
                    return '<span class="badge bg-' . $color . '">' . $label . '</span>';
                })
                ->editColumn('quantity', function($row) {
                    $class = $row->quantity >= 0 ? 'text-success' : 'text-danger';
                    $sign = $row->quantity >= 0 ? '+' : '';
                    return '<span class="' . $class . '">' . $sign . number_format($row->quantity, 2, ',', '.') . '</span>';
                })
                ->editColumn('movement_date', function($row) {
                    return $row->movement_date ? $row->movement_date->format('d/m/Y H:i') : 'N/A';
                })
                ->rawColumns(['movement_type_badge', 'quantity'])
                ->make(true);
        }

        return view('pages.stock-movements.index');
    }
}
