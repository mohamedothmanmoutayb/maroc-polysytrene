<?php
namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Models\VehicleDocumentType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_vehicles')->only(['index', 'show', 'getStatistics', 'getDocumentHistory']);
        $this->middleware('can:create_vehicles')->only(['create', 'store']);
        $this->middleware('can:edit_vehicles')->only(['edit', 'update']);
        $this->middleware('can:delete_vehicles')->only(['destroy']);
        $this->middleware('can:manage_vehicle_documents')->only(['addDocument', 'getDocumentTypes', 'exportExcel', 'exportPdf']);
    }

    public function index(Request $request)
    {
        // Handle statistics request
        if ($request->has('statistics')) {
            return $this->getStatistics();
        }

        if ($request->ajax()) {
            $vehicles = Vehicle::select('vehicles.*')->with(['documents' => function($query) {
                $query->where('is_current', true)->with('documentType');
            }]);

            // Apply filters
            if ($request->filled('type')) {
                $vehicles->where('type', $request->type);
            }

            if ($request->filled('status')) {
                $vehicles->where('status', $request->status);
            }

            if ($request->filled('document_status')) {
                $vehicles->whereHas('documents', function($query) use ($request) {
                    $query->where('is_current', true);
                    if ($request->document_status === 'expired') {
                        $query->where('end_date', '<', Carbon::now());
                    } elseif ($request->document_status === 'expiring_soon') {
                        $query->where('end_date', '>=', Carbon::now())
                            ->where('end_date', '<=', Carbon::now()->addDays(30));
                    } elseif ($request->document_status === 'valid') {
                        $query->where('end_date', '>', Carbon::now());
                    }
                });
            }

            return DataTables::of($vehicles)
                ->addIndexColumn()
                ->addColumn('type_badge', function($row) {
                    $badges = [
                        'voiture' => 'primary',
                        'camion' => 'warning',
                        'machine' => 'info',
                    ];
                    $color = $badges[$row->type] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . $row->type_label . '</span>';
                })
                ->addColumn('documents_status', function($row) {
                    $html = '<div class="text-start">';
                    foreach ($row->documents as $doc) {
                        $statusClass = '';
                        $statusText = '';

                        if (!$doc->end_date) {
                            $statusClass = 'secondary';
                            $statusText = 'Non renseigné';
                        } elseif ($doc->end_date < Carbon::now()) {
                            $statusClass = 'danger';
                            $statusText = 'Expiré';
                        } elseif ($doc->end_date <= Carbon::now()->addDays(30)) {
                            $statusClass = 'warning';
                            $statusText = 'Expire bientôt';
                        } else {
                            $statusClass = 'success';
                            $statusText = 'Valide';
                        }

                        $daysLeft = $doc->end_date ? Carbon::now()->diffInDays($doc->end_date, false) : 0;
                        $daysText = $daysLeft > 0 ? " ({$daysLeft}j)" : '';

                        $html .= '<div class="document-item d-flex justify-content-between align-items-center">';
                        $html .= '<span class="small fw-bold">' . e($doc->documentType->type_name) . ':</span>';
                        $html .= '<span class="badge bg-' . $statusClass . '">' . $statusText . $daysText . '</span>';
                        $html .= '<button type="button" class="btn btn-sm btn-outline-info view-history" ';
                        $html .= 'data-document-type="' . $doc->document_type_id . '" ';
                        $html .= 'data-vehicle-id="' . $row->vehicle_id . '" ';
                        $html .= 'data-type-name="' . e($doc->documentType->type_name) . '">';
                        $html .= '<i class="fas fa-history"></i>';
                        $html .= '</button>';
                        $html .= '</div>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('status_badge', function($row) {
                    return $row->status_badge;
                })
                ->addColumn('action', function($row) {
                    $user = auth()->user();
                    $btn = '<div class="dropdown dropstart">';
                    $btn .= '<a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown">';
                    $btn .= '<i class="ti ti-dots-vertical fs-6"></i></a>';
                    $btn .= '<ul class="dropdown-menu dropdown-menu-end">';
                    $btn .= '<li><a class="dropdown-item" href="'.route('vehicles.show', $row->vehicle_id).'">
                                <i class="fas fa-eye me-2"></i>Voir</a></li>';
                    if ($user->can('edit_vehicles')) {
                        $btn .= '<li><a class="dropdown-item" href="'.route('vehicles.edit', $row->vehicle_id).'">
                                    <i class="fas fa-edit me-2"></i>Modifier</a></li>';
                    }
                    if ($user->can('delete_vehicles')) {
                        $btn .= '<li><hr class="dropdown-divider"></li>';
                        $btn .= '<li><a class="dropdown-item delete" href="#" data-id="'.$row->vehicle_id.'"
                                    data-number="'.$row->registration_number.'">
                                    <i class="fas fa-trash text-danger me-2"></i>Supprimer</a></li>';
                    }
                    $btn .= '</ul></div>';
                    return $btn;
                })
                ->addColumn('current_mileage', function($row) {
                    return $row->current_mileage ?? 0;
                })
                ->rawColumns(['type_badge', 'documents_status', 'status_badge', 'action'])
                ->make(true);
        }

        return view('pages.vehicles.index');
    }

    /**
     * Get statistics for the dashboard
     */
    public function getStatistics()
    {
        try {
            $total = Vehicle::count();
            $active = Vehicle::where('status', 'active')->count();
            $maintenance = Vehicle::where('status', 'maintenance')->count();

            // Count expired documents
            $expiredDocuments = VehicleDocument::where('is_current', true)
                ->where('end_date', '<', Carbon::now())
                ->count();

            return response()->json([
                'success' => true,
                'statistics' => [
                    'total' => $total,
                    'active' => $active,
                    'maintenance' => $maintenance,
                    'expired_documents' => $expiredDocuments,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        $documentTypes = VehicleDocumentType::active()->orderBy('sort_order')->get();
        return view('pages.vehicles.create', compact('documentTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:voiture,camion,machine',
            'registration_number' => 'required|unique:vehicles|max:50',
            'purchase_date' => 'nullable|date',
            'current_mileage' => 'nullable|integer|min:0',
            'status' => 'required|in:active,maintenance,inactive',
            'notes' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*.document_type_id' => 'required|exists:vehicle_document_types,document_type_id',
            'documents.*.document_number' => 'nullable|string|max:100',
            'documents.*.start_date' => 'nullable|date',
            'documents.*.end_date' => 'nullable|date',
            'documents.*.issuing_authority' => 'nullable|string|max:100',
            'documents.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $vehicle = Vehicle::create($request->only([
                'type', 'registration_number', 'purchase_date',
                'current_mileage', 'status', 'notes'
            ]));

            if ($request->has('documents')) {
                foreach ($request->documents as $docData) {
                    if (!empty($docData['end_date'])) {
                        VehicleDocument::create([
                            'vehicle_id' => $vehicle->vehicle_id,
                            'document_type_id' => $docData['document_type_id'],
                            'document_number' => $docData['document_number'] ?? null,
                            'start_date' => $docData['start_date'] ?? null,
                            'end_date' => $docData['end_date'],
                            'issuing_authority' => $docData['issuing_authority'] ?? null,
                            'notes' => $docData['notes'] ?? null,
                            'is_current' => true,
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Véhicule créé avec succès!'
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
        $vehicle = Vehicle::with(['documents' => function($query) {
            $query->with('documentType')->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        return view('pages.vehicles.show', compact('vehicle'));
    }

    public function edit($id)
    {
        $vehicle = Vehicle::with(['currentDocuments' => function($query) {
            $query->with('documentType');
        }])->findOrFail($id);

        $documentTypes = VehicleDocumentType::active()->orderBy('sort_order')->get();

        return view('pages.vehicles.edit', compact('vehicle', 'documentTypes'));
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $request->validate([
            'type' => 'required|in:voiture,camion,machine',
            'registration_number' => 'required|max:50|unique:vehicles,registration_number,'.$id.',vehicle_id',
            'purchase_date' => 'nullable|date',
            'current_mileage' => 'nullable|integer|min:0',
            'status' => 'required|in:active,maintenance,inactive',
            'notes' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*.document_type_id' => 'required|exists:vehicle_document_types,document_type_id',
            'documents.*.document_number' => 'nullable|string|max:100',
            'documents.*.start_date' => 'nullable|date',
            'documents.*.end_date' => 'nullable|date',
            'documents.*.issuing_authority' => 'nullable|string|max:100',
            'documents.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $vehicle->update($request->only([
                'type', 'registration_number', 'purchase_date',
                'current_mileage', 'status', 'notes'
            ]));

            if ($request->has('documents')) {
                foreach ($request->documents as $docData) {
                    if (!empty($docData['end_date'])) {
                        // Set old document as not current
                        VehicleDocument::where('vehicle_id', $vehicle->vehicle_id)
                            ->where('document_type_id', $docData['document_type_id'])
                            ->where('is_current', true)
                            ->update(['is_current' => false]);

                        // Create new document
                        VehicleDocument::create([
                            'vehicle_id' => $vehicle->vehicle_id,
                            'document_type_id' => $docData['document_type_id'],
                            'document_number' => $docData['document_number'] ?? null,
                            'start_date' => $docData['start_date'] ?? null,
                            'end_date' => $docData['end_date'],
                            'issuing_authority' => $docData['issuing_authority'] ?? null,
                            'notes' => $docData['notes'] ?? null,
                            'is_current' => true,
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Véhicule mis à jour avec succès!'
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
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->documents()->delete();
            $vehicle->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Véhicule supprimé avec succès!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDocumentHistory($vehicleId, $documentTypeId)
    {
        try {
            $documents = VehicleDocument::where('vehicle_id', $vehicleId)
                ->where('document_type_id', $documentTypeId)
                ->with('documentType')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $documents
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeDocument(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,vehicle_id',
            'document_type_id' => 'required|exists:vehicle_document_types,document_type_id',
            'document_number' => 'nullable|string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'required|date',
            'issuing_authority' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Set old document as not current
            VehicleDocument::where('vehicle_id', $request->vehicle_id)
                ->where('document_type_id', $request->document_type_id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            // Create new document
            VehicleDocument::create([
                'vehicle_id' => $request->vehicle_id,
                'document_type_id' => $request->document_type_id,
                'document_number' => $request->document_number,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'issuing_authority' => $request->issuing_authority,
                'notes' => $request->notes,
                'is_current' => true,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document ajouté avec succès!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $vehicles = Vehicle::with(['documents' => function($query) {
                $query->where('is_current', true)->with('documentType');
            }]);

            // Apply filters
            if ($request->filled('type')) {
                $vehicles->where('type', $request->type);
            }
            if ($request->filled('status')) {
                $vehicles->where('status', $request->status);
            }

            $vehicles = $vehicles->get();

            // Create Excel export logic here
            // You can use Maatwebsite\Excel or custom export

            return response()->json([
                'success' => true,
                'message' => 'Export Excel en cours de développement'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $vehicles = Vehicle::with(['documents' => function($query) {
                $query->where('is_current', true)->with('documentType');
            }]);

            // Apply filters
            if ($request->filled('type')) {
                $vehicles->where('type', $request->type);
            }
            if ($request->filled('status')) {
                $vehicles->where('status', $request->status);
            }

            $vehicles = $vehicles->get();

            // Create PDF export logic here
            // You can use Barryvdh\DomPDF

            return response()->json([
                'success' => true,
                'message' => 'Export PDF en cours de développement'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
