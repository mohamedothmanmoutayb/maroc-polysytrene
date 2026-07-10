<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories = ExpenseCategory::withCount('expenses')->select('expense_categories.*');

            return DataTables::of($categories)
                ->addIndexColumn()
                ->addColumn('category_name', function($row) {
                    return '<strong>' . $row->category_name . '</strong>';
                })
                ->addColumn('expenses_count', function($row) {
                    return '<span class="badge bg-info">' . $row->expenses_count . '</span>';
                })
                ->addColumn('status', function($row) {
                    if ($row->is_active) {
                        return '<span class="badge bg-success">Actif</span>';
                    }
                    return '<span class="badge bg-secondary">Inactif</span>';
                })
                ->addColumn('action', function($row) {
                    $btn = '<div class="dropdown dropstart">';
                    $btn .= ' <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>';
                    $btn .= '<ul class="dropdown-menu dropdown-menu-end">';
                    $btn .= '<li><a class="dropdown-item" href="'.route('expense-categories.edit', $row->category_id).'">
                                <i class="fas fa-edit me-2"></i>Modifier</a></li>';

                    if ($row->expenses_count == 0) {
                        $btn .= '<li><hr class="dropdown-divider"></li>';
                        $btn .= '<li><a class="dropdown-item delete" href="#" data-id="'.$row->category_id.'" data-name="'.$row->category_name.'">
                                    <i class="fas fa-trash text-danger me-2"></i>Supprimer</a></li>';
                    }

                    $btn .= '</ul></div>';

                    return $btn;
                })
                ->rawColumns(['category_name', 'expenses_count', 'status', 'action'])
                ->make(true);
        }

        $totalCategories = ExpenseCategory::count();
        $activeCategories = ExpenseCategory::where('is_active', true)->count();
        $totalExpenses = \App\Models\Expense::count();

        return view('pages.expense-categories.index', compact('totalCategories', 'activeCategories', 'totalExpenses'));
    }

    public function create()
    {
        return view('pages.expense-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:100|unique:expense_categories',
            'description' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $isActive = $request->has('is_active') && $request->is_active === 'on' ? 1 : 0;

            ExpenseCategory::create([
                'category_name' => $request->category_name,
                'description' => $request->description,
                'is_active' => $isActive,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Catégorie créée avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $category = ExpenseCategory::findOrFail($id);
        return view('pages.expense-categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = ExpenseCategory::findOrFail($id);

        $request->validate([
            'category_name' => 'required|string|max:100|unique:expense_categories,category_name,'.$id.',category_id',
            'description' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $isActive = $request->has('is_active') && $request->is_active === 'on' ? 1 : 0;

            $category->update([
                'category_name' => $request->category_name,
                'description' => $request->description,
                'is_active' => $isActive,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Catégorie mise à jour avec succès!'
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
            $category = ExpenseCategory::findOrFail($id);

            // Check if category has expenses
            if ($category->expenses()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette catégorie contient des dépenses et ne peut pas être supprimée.'
                ], 400);
            }

            $category->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Catégorie supprimée avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCategories()
    {
        $categories = ExpenseCategory::where('is_active', true)
            ->select('category_id', 'category_name')
            ->orderBy('category_name')
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }
}
