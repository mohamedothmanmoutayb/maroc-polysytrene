<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_drivers')->only(['index', 'show', 'getStatistics', 'exportExcel', 'exportPdf']);
        $this->middleware('can:create_drivers')->only(['create', 'store']);
        $this->middleware('can:edit_drivers')->only(['edit', 'update']);
        $this->middleware('can:delete_drivers')->only(['destroy']);
    }

    public function index(Request $request)
    {
        // Handle statistics request
        if ($request->has('statistics')) {
            return $this->getStatistics();
        }

        if ($request->ajax()) {
            $drivers = Driver::with('employee')->select('drivers.*');

            // Apply status filter
            if ($request->filled('status')) {
                $drivers->where('status', $request->status);
            }

            // Apply license status filter
            if ($request->filled('license_status')) {
                $now = Carbon::now();
                switch ($request->license_status) {
                    case 'valid':
                        $drivers->where('license_expiry_date', '>', $now->addDays(30));
                        break;
                    case 'expiring_soon':
                        $drivers->where('license_expiry_date', '>=', $now)
                            ->where('license_expiry_date', '<=', $now->addDays(30));
                        break;
                    case 'expired':
                        $drivers->where('license_expiry_date', '<', $now);
                        break;
                }
            }

            // Apply medical status filter
            if ($request->filled('medical_status')) {
                $now = Carbon::now();
                switch ($request->medical_status) {
                    case 'up_to_date':
                        $drivers->where('next_medical_visit_date', '>', $now->addDays(30))
                            ->orWhereNull('next_medical_visit_date');
                        break;
                    case 'due_soon':
                        $drivers->where('next_medical_visit_date', '>=', $now)
                            ->where('next_medical_visit_date', '<=', $now->addDays(30));
                        break;
                    case 'overdue':
                        $drivers->where('next_medical_visit_date', '<', $now);
                        break;
                }
            }

            return DataTables::of($drivers)
                ->addIndexColumn()
                ->addColumn('full_name', function($row) {
                    return $row->employee ? $row->employee->full_name : 'N/A';
                })
                ->addColumn('cin', function($row) {
                    return $row->employee ? $row->employee->cin : 'N/A';
                })
                ->addColumn('phone', function($row) {
                    return $row->employee ? $row->employee->phone : 'N/A';
                })
                ->addColumn('license_category', function($row) {
                    return $row->license_category;
                })
                ->addColumn('license_status', function($row) {
                    if (!$row->license_expiry_date) return '<span class="badge bg-secondary">Non renseigné</span>';
                    $daysLeft = Carbon::now()->diffInDays($row->license_expiry_date, false);
                    if ($daysLeft < 0) {
                        return '<span class="badge bg-danger">Expiré</span>';
                    } elseif ($daysLeft <= 10) {
                        return '<span class="badge bg-warning text-dark">Expire dans ' . $daysLeft . 'j</span>';
                    } elseif ($daysLeft <= 30) {
                        return '<span class="badge bg-info">Expire dans ' . $daysLeft . 'j</span>';
                    }
                    return '<span class="badge bg-success">Valide</span>';
                })
                ->addColumn('medical_status', function($row) {
                    if (!$row->next_medical_visit_date) return '<span class="badge bg-secondary">Non planifiée</span>';
                    $daysLeft = Carbon::now()->diffInDays($row->next_medical_visit_date, false);
                    if ($daysLeft < 0) {
                        return '<span class="badge bg-danger">En retard</span>';
                    } elseif ($daysLeft <= 10) {
                        return '<span class="badge bg-warning text-dark">Dans ' . $daysLeft . 'j</span>';
                    } elseif ($daysLeft <= 30) {
                        return '<span class="badge bg-info">Dans ' . $daysLeft . 'j</span>';
                    }
                    return '<span class="badge bg-success">À jour</span>';
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
                    $btn .= '<li><a class="dropdown-item" href="'.route('drivers.show', $row->driver_id).'">
                                <i class="fas fa-eye me-2"></i>Voir</a></li>';
                    if ($user->can('edit_drivers')) {
                        $btn .= '<li><a class="dropdown-item" href="'.route('drivers.edit', $row->driver_id).'">
                                    <i class="fas fa-edit me-2"></i>Modifier</a></li>';
                    }
                    if ($user->can('delete_drivers')) {
                        $btn .= '<li><hr class="dropdown-divider"></li>';
                        $btn .= '<li><a class="dropdown-item delete" href="#" data-id="'.$row->driver_id.'"
                                    data-name="'.$row->full_name.'">
                                    <i class="fas fa-trash text-danger me-2"></i>Supprimer</a></li>';
                    }
                    $btn .= '</ul></div>';
                    return $btn;
                })
                ->rawColumns(['license_status', 'medical_status', 'status_badge', 'action'])
                ->make(true);
        }

        return view('pages.drivers.index');
    }

    public function create()
    {
        $employees = Employee::orderBy('full_name')->get();
        return view('pages.drivers.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'license_number' => 'required|string|max:50|unique:drivers',
            'license_expiry_date' => 'required|date',
            'license_category' => 'nullable|string|max:20',
            'medical_visit_date' => 'nullable|date',
            'next_medical_visit_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,suspended',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            Driver::create($request->all());
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Chauffeur créé avec succès!'
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
        $driver = Driver::with('employee')->findOrFail($id);
        return view('pages.drivers.show', compact('driver'));
    }

    public function edit($id)
    {
        $driver = Driver::with('employee')->findOrFail($id);
        $employees = Employee::orderBy('full_name')->get();
        return view('pages.drivers.edit', compact('driver', 'employees'));
    }

    public function getStatistics()
    {
        try {
            $total = Driver::count();
            $active = Driver::where('status', 'active')->count();

            // Count licenses expiring within 30 days
            $expiringLicenses = Driver::where('license_expiry_date', '>=', Carbon::now())
                ->where('license_expiry_date', '<=', Carbon::now()->addDays(30))
                ->count();

            // Count pending medical visits (next visit within 30 days or overdue)
            $pendingMedicalVisits = Driver::where(function($query) {
                $query->where('next_medical_visit_date', '<=', Carbon::now()->addDays(30))
                    ->orWhere('next_medical_visit_date', '<', Carbon::now());
            })->whereNotNull('next_medical_visit_date')->count();

            return response()->json([
                'success' => true,
                'statistics' => [
                    'total' => $total,
                    'active' => $active,
                    'expiring_licenses' => $expiringLicenses,
                    'pending_medical_visits' => $pendingMedicalVisits,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'license_number' => 'required|string|max:50|unique:drivers,license_number,'.$id.',driver_id',
            'license_expiry_date' => 'required|date',
            'license_category' => 'nullable|string|max:20',
            'medical_visit_date' => 'nullable|date',
            'next_medical_visit_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,suspended',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $driver->update($request->all());
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Chauffeur mis à jour avec succès!'
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
            $driver = Driver::findOrFail($id);
            $driver->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Chauffeur supprimé avec succès!'
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
