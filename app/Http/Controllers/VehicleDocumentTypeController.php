<?php
namespace App\Http\Controllers;

use App\Models\VehicleDocumentType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class VehicleDocumentTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $types = VehicleDocumentType::select('vehicle_document_types.*');

            return DataTables::of($types)
                ->addIndexColumn()
                ->addColumn('status_badge', function($row) {
                    return $row->is_active
                        ? '<span class="badge bg-success">Actif</span>'
                        : '<span class="badge bg-danger">Inactif</span>';
                })
                ->addColumn('action', function($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button class="btn btn-sm btn-warning edit-type" data-id="'.$row->document_type_id.'">
                                <i class="fas fa-edit"></i>
                            </button>';
                    $btn .= '<button class="btn btn-sm btn-danger delete-type" data-id="'.$row->document_type_id.'"
                                data-name="'.$row->type_name.'">
                                <i class="fas fa-trash"></i>
                            </button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('pages.vehicle-document-types.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_code' => 'required|unique:vehicle_document_types|max:50',
            'type_name' => 'required|max:100',
            'description' => 'nullable|string',
            'default_duration_days' => 'nullable|integer|min:1',
            'reminder_days_before' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            VehicleDocumentType::create([
                'type_code' => $request->type_code,
                'type_name' => $request->type_name,
                'description' => $request->description,
                'default_duration_days' => $request->default_duration_days,
                'reminder_days_before' => $request->reminder_days_before,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => $request->has('is_active'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Type de document créé avec succès!'
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
        try {
            $type = VehicleDocumentType::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $type
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Type de document non trouvé'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $type = VehicleDocumentType::findOrFail($id);

        $request->validate([
            'type_code' => 'required|max:50|unique:vehicle_document_types,type_code,'.$id.',document_type_id',
            'type_name' => 'required|max:100',
            'description' => 'nullable|string',
            'default_duration_days' => 'nullable|integer|min:1',
            'reminder_days_before' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $type->update([
                'type_code' => $request->type_code,
                'type_name' => $request->type_name,
                'description' => $request->description,
                'default_duration_days' => $request->default_duration_days,
                'reminder_days_before' => $request->reminder_days_before,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => $request->has('is_active'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Type de document mis à jour avec succès!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $type = VehicleDocumentType::findOrFail($id);

            // Check if there are documents using this type
            if ($type->documents()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce type car il est utilisé par des documents.'
                ], 400);
            }

            $type->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Type de document supprimé avec succès!'
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
