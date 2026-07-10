<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineDocument;
use App\Models\MachineDocumentType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MachineController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_machines')->only(['index', 'show', 'getStatistics', 'getDocumentHistory', 'exportExcel', 'exportPdf']);
        $this->middleware('can:create_machines')->only(['create', 'store']);
        $this->middleware('can:edit_machines')->only(['edit', 'update', 'addDocument']);
        $this->middleware('can:delete_machines')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->has('statistics')) {
            return $this->getStatistics();
        }

        if ($request->ajax()) {
            $machines = Machine::select('machines.*')->with(['documents' => function($query) {
                $query->where('is_current', true)->with('documentType');
            }]);

            // Apply filters
            if ($request->filled('status')) {
                $machines->where('status', $request->status);
            }

            if ($request->filled('document_status')) {
                $machines->whereHas('documents', function($query) use ($request) {
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

            return DataTables::of($machines)
                ->addIndexColumn()
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
                        $html .= 'data-machine-id="' . $row->machine_id . '" ';
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
                    $btn .= '<li><a class="dropdown-item" href="'.route('machines.show', $row->machine_id).'">
                                <i class="fas fa-eye me-2"></i>Voir</a></li>';
                    if ($user->can('edit_machines')) {
                        $btn .= '<li><a class="dropdown-item" href="'.route('machines.edit', $row->machine_id).'">
                                    <i class="fas fa-edit me-2"></i>Modifier</a></li>';
                    }
                    if ($user->can('delete_machines')) {
                        $btn .= '<li><hr class="dropdown-divider"></li>';
                        $btn .= '<li><a class="dropdown-item delete" href="#" data-id="'.$row->machine_id.'"
                                    data-name="'.$row->name.'">
                                    <i class="fas fa-trash text-danger me-2"></i>Supprimer</a></li>';
                    }
                    $btn .= '</ul></div>';
                    return $btn;
                })
                ->rawColumns(['documents_status', 'status_badge', 'action'])
                ->make(true);
        }

        return view('pages.machines.index');
    }

    public function getStatistics()
    {
        try {
            $total = Machine::count();
            $active = Machine::where('status', 'active')->count();
            $maintenance = Machine::where('status', 'maintenance')->count();

            $expiredDocuments = MachineDocument::where('is_current', true)
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
        $documentTypes = MachineDocumentType::active()->orderBy('sort_order')->get();
        return view('pages.machines.create', compact('documentTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'required|unique:machines|max:100',
            'model' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'operating_hours' => 'nullable|integer|min:0',
            'status' => 'required|in:active,maintenance,inactive',
            'notes' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*.document_type_id' => 'required|exists:machine_document_types,document_type_id',
            'documents.*.document_number' => 'nullable|string|max:100',
            'documents.*.start_date' => 'nullable|date',
            'documents.*.end_date' => 'nullable|date',
            'documents.*.issuing_authority' => 'nullable|string|max:100',
            'documents.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $machine = Machine::create($request->only([
                'name', 'serial_number', 'model', 'manufacturer',
                'purchase_date', 'operating_hours', 'status', 'notes'
            ]));

            if ($request->has('documents')) {
                foreach ($request->documents as $docData) {
                    if (!empty($docData['end_date'])) {
                        MachineDocument::create([
                            'machine_id' => $machine->machine_id,
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
                'message' => 'Machine créée avec succès!'
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
        $machine = Machine::with(['documents' => function($query) {
            $query->with('documentType')->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        return view('pages.machines.show', compact('machine'));
    }

    public function edit($id)
    {
        $machine = Machine::with(['currentDocuments' => function($query) {
            $query->with('documentType');
        }])->findOrFail($id);

        $documentTypes = MachineDocumentType::active()->orderBy('sort_order')->get();

        return view('pages.machines.edit', compact('machine', 'documentTypes'));
    }

    public function update(Request $request, $id)
    {
        $machine = Machine::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'required|max:100|unique:machines,serial_number,'.$id.',machine_id',
            'model' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'operating_hours' => 'nullable|integer|min:0',
            'status' => 'required|in:active,maintenance,inactive',
            'notes' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*.document_type_id' => 'required|exists:machine_document_types,document_type_id',
            'documents.*.document_number' => 'nullable|string|max:100',
            'documents.*.start_date' => 'nullable|date',
            'documents.*.end_date' => 'nullable|date',
            'documents.*.issuing_authority' => 'nullable|string|max:100',
            'documents.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $machine->update($request->only([
                'name', 'serial_number', 'model', 'manufacturer',
                'purchase_date', 'operating_hours', 'status', 'notes'
            ]));

            if ($request->has('documents')) {
                foreach ($request->documents as $docData) {
                    if (!empty($docData['end_date'])) {
                        MachineDocument::where('machine_id', $machine->machine_id)
                            ->where('document_type_id', $docData['document_type_id'])
                            ->where('is_current', true)
                            ->update(['is_current' => false]);

                        MachineDocument::create([
                            'machine_id' => $machine->machine_id,
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
                'message' => 'Machine mise à jour avec succès!'
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
            $machine = Machine::findOrFail($id);
            $machine->documents()->delete();
            $machine->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Machine supprimée avec succès!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDocumentHistory($machineId, $documentTypeId)
    {
        try {
            $documents = MachineDocument::where('machine_id', $machineId)
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
            'machine_id' => 'required|exists:machines,machine_id',
            'document_type_id' => 'required|exists:machine_document_types,document_type_id',
            'document_number' => 'nullable|string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'required|date',
            'issuing_authority' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            MachineDocument::where('machine_id', $request->machine_id)
                ->where('document_type_id', $request->document_type_id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            MachineDocument::create([
                'machine_id' => $request->machine_id,
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
}
