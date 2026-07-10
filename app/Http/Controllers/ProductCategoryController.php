<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories = ProductCategory::with(['parent', 'children', 'products'])
                ->select('product_categories.*');

            return DataTables::of($categories)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $dropdown = '<div class="dropdown dropstart">
                        <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 edit" href="javascript:void(0)" data-id="'.$row->category_id.'">
                                    <i class="fs-4 ti ti-edit"></i>Modifier
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 delete" href="javascript:void(0)"
                                   data-id="'.$row->category_id.'"
                                   data-name="'.$row->category_name.'">
                                    <i class="fs-4 ti ti-trash text-danger"></i><span class="text-danger">Supprimer</span>
                                </a>
                            </li>
                        </ul>
                    </div>';
                    return $dropdown;
                })
                ->addColumn('parent_category', function($row){
                    return $row->parent ? $row->parent->category_name : 'Catégorie Principale';
                })
                ->addColumn('subcategories_count', function($row){
                    return $row->children->count();
                })
                ->addColumn('products_count', function($row){
                    return $row->products->count();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $parentCategories = ProductCategory::whereNull('parent_category_id')->get();

        return view('pages.product-categories.index', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:100|unique:product_categories,category_name',
            'description' => 'nullable|string|max:255',
            'parent_category_id' => 'nullable|exists:product_categories,category_id',
        ]);

        try {
            ProductCategory::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Catégorie créée avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $category = ProductCategory::findOrFail($id);
        $parentCategories = ProductCategory::whereNull('parent_category_id')
            ->where('category_id', '!=', $id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $category,
            'parentCategories' => $parentCategories
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = ProductCategory::findOrFail($id);

        $request->validate([
            'category_name' => 'required|string|max:100|unique:product_categories,category_name,'.$id.',category_id',
            'description' => 'nullable|string|max:255',
            'parent_category_id' => 'nullable|exists:product_categories,category_id',
        ]);

        try {
            // Prevent circular reference
            if ($request->parent_category_id == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une catégorie ne peut pas être sa propre parent'
                ], 400);
            }

            $category->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Catégorie mise à jour avec succès!'
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
        try {
            $category = ProductCategory::findOrFail($id);

            // Check if category has subcategories
            if ($category->children()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cette catégorie car elle contient des sous-catégories.'
                ], 400);
            }

            // Check if category has products
            if ($category->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cette catégorie car elle est associée à des produits.'
                ], 400);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Catégorie supprimée avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }
}
