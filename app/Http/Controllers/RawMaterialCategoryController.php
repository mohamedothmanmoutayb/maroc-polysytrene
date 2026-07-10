<?php

namespace App\Http\Controllers;

use App\Models\RawMaterialCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RawMaterialCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories = RawMaterialCategory::with(['parent', 'children', 'rawMaterials'])
                ->select('raw_material_categories.*');

            return DataTables::of($categories)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $dropdown = '<div class="dropdown dropstart">
                        <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 view" href="javascript:void(0)" data-id="'.$row->category_id.'">
                                    <i class="fs-4 ti ti-eye"></i>Voir
                                </a>
                            </li>
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
                ->addColumn('materials_count', function($row){
                    return $row->rawMaterials->count();
                })
                ->addColumn('hierarchy', function($row){
                    return $row->parent_category_id ? 'Sous-catégorie' : 'Catégorie Principale';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $parentCategories = RawMaterialCategory::whereNull('parent_category_id')->get();

        return view('pages.raw-material-categories.index', compact('parentCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentCategories = RawMaterialCategory::whereNull('parent_category_id')->get();
        return view('pages.raw-material-categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:100|unique:raw_material_categories,category_name',
            'description' => 'nullable|string|max:255',
            'parent_category_id' => 'nullable|exists:raw_material_categories,category_id',
        ]);

        try {
            RawMaterialCategory::create($request->all());

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

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = RawMaterialCategory::with(['parent', 'children', 'rawMaterials'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $category = RawMaterialCategory::findOrFail($id);
        $parentCategories = RawMaterialCategory::whereNull('parent_category_id')
            ->where('category_id', '!=', $id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $category,
            'parentCategories' => $parentCategories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = RawMaterialCategory::findOrFail($id);

        $request->validate([
            'category_name' => 'required|string|max:100|unique:raw_material_categories,category_name,'.$id.',category_id',
            'description' => 'nullable|string|max:255',
            'parent_category_id' => 'nullable|exists:raw_material_categories,category_id',
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $category = RawMaterialCategory::findOrFail($id);

            // Check if category has subcategories
            if ($category->children()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cette catégorie car elle contient des sous-catégories.'
                ], 400);
            }

            // Check if category has materials
            if ($category->rawMaterials()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cette catégorie car elle est associée à des matières premières.'
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

    /**
     * Get categories for select2
     */
    public function getCategories(Request $request)
    {
        $query = $request->get('q');

        $categories = RawMaterialCategory::where('category_name', 'like', '%' . $query . '%')
            ->select('category_id', 'category_name')
            ->limit(10)
            ->get();

        return response()->json($categories);
    }
}
