<?php

namespace App\Http\Controllers;

use App\Models\Magazine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MagazineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $magazines = Magazine::select('magazines.*');

            return DataTables::of($magazines)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $dropdown = '<div class="dropdown dropstart">
                        <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 view-magazine"
                                   href="javascript:void(0)"
                                   data-id="'.$row->magazine_id.'">
                                    <i class="fs-4 ti ti-eye"></i>Voir
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 edit-magazine"
                                   href="javascript:void(0)"
                                   data-id="'.$row->magazine_id.'">
                                    <i class="fs-4 ti ti-edit"></i>Modifier
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 delete-magazine"
                                   href="javascript:void(0)"
                                   data-id="'.$row->magazine_id.'"
                                   data-name="'.$row->magazine_name.'">
                                    <i class="fs-4 ti ti-trash text-danger"></i>
                                    <span class="text-danger">Supprimer</span>
                                </a>
                            </li>
                        </ul>
                    </div>';
                    return $dropdown;
                })
                ->addColumn('status_badge', function($row){
                    return $row->is_active
                        ? '<span class="badge badge-success">Actif</span>'
                        : '<span class="badge badge-danger">Inactif</span>';
                })
                // ->addColumn('materials_count', function($row){
                //     $count = DB::table('raw_materials')
                //         ->where('magazine_id', $row->magazine_id)
                //         ->count();
                //     return '<span class="badge bg-info">'.$count.' matières</span>';
                // })
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
        }

        return view('pages.magazines.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'magazine_code' => 'required|unique:magazines|max:20',
            'magazine_name' => 'required|max:100',
            'location' => 'nullable|max:100',
            'description' => 'nullable',
            'is_active' => 'boolean',
        ]);

        try {
            Magazine::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Magasin créé avec succès!'
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
        $magazine = Magazine::findOrFail($id);


        return response()->json([
            'success' => true,
            'data' => [
                'magazine' => $magazine,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $magazine = Magazine::findOrFail($id);

        $request->validate([
            'magazine_code' => 'required|unique:magazines,magazine_code,'.$id.',magazine_id|max:20',
            'magazine_name' => 'required|max:100',
            'location' => 'nullable|max:100',
            'description' => 'nullable',
            'is_active' => 'boolean',
        ]);

        try {
            $magazine->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Magasin mis à jour avec succès!'
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
        DB::beginTransaction();
        try {
            $magazine = Magazine::findOrFail($id);

            // Check if magazine has materials
            $hasMaterials = DB::table('raw_materials')->where('magazine_id', $id)->exists();
            if ($hasMaterials) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un magasin contenant des matières premières.'
                ], 400);
            }

            $magazine->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Magasin supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get magazines for select2 dropdown
     */
    public function getMagazinesForSelect(Request $request)
    {
        $query = $request->get('q');

        $magazines = Magazine::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('magazine_code', 'like', '%' . $query . '%')
                  ->orWhere('magazine_name', 'like', '%' . $query . '%');
            })
            ->select('magazine_id as id', 'magazine_name as text', 'magazine_code')
            ->orderBy('magazine_name')
            ->get();

        return response()->json($magazines);
    }
}
