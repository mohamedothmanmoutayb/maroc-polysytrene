<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('view_employees');

        if ($request->ajax()) {
            $employees = Employee::with('user.roles')->select('employees.*');

            return DataTables::of($employees)
                ->addIndexColumn()
                ->addColumn('full_name', function($row) {
                    return '<strong>' . e($row->full_name) . '</strong>';
                })
                ->addColumn('photo', function($row) {
                    if ($row->photo && Storage::disk('public')->exists($row->photo)) {
                        return '<img src="'.asset('storage/'.$row->photo).'" class="rounded-circle" width="40" height="40" style="object-fit: cover;">';
                    }
                    return '<img src="'.asset('assets/images/profile/user-1.jpg').'" class="rounded-circle" width="40" height="40" style="object-fit: cover;">';
                })
                ->addColumn('cin', function($row) {
                    return $row->cin ?? '<span class="text-muted">-</span>';
                })
                ->addColumn('phone', function($row) {
                    return $row->phone ?? '<span class="text-muted">-</span>';
                })
                ->addColumn('department', function($row) {
                    return $row->department ?? '<span class="text-muted">-</span>';
                })
                ->addColumn('salary', function($row) {
                    if ($row->monthly_salary) {
                        return number_format($row->monthly_salary, 2, ',', '.') . ' DH';
                    }
                    if ($row->hourly_salary) {
                        return number_format($row->hourly_salary, 2, ',', '.') . ' DH/h';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('hire_date', function($row) {
                    return $row->hire_date ? $row->hire_date->format('d/m/Y') : '<span class="text-muted">-</span>';
                })
                ->addColumn('has_account', function($row) {
                    if ($row->user) {
                        $roles = $row->user->roles;

                        $rolesHtml = '<div class="mt-1">';
                        if ($roles->isNotEmpty()) {
                            foreach ($roles as $role) {
                                $badgeClass = match($role->name) {
                                    'admin' => 'danger',
                                    'manager' => 'warning',
                                    'sales' => 'info',
                                    'production' => 'primary',
                                    'accountant' => 'success',
                                    'user' => 'secondary',
                                    default => 'secondary'
                                };
                                $rolesHtml .= '<span class="badge bg-' . $badgeClass . ' me-1 mb-1" title="' . e($role->name) . '">' . ucfirst(e($role->name)) . '</span>';
                            }
                        } else {
                            $rolesHtml .= '<span class="badge bg-secondary me-1 mb-1">Aucun rôle</span>';
                        }
                        $rolesHtml .= '</div>';

                        return '<div class="text-center">
                                    <span class="badge bg-success mb-1">Oui</span>
                                </div>';
                    }
                    return '<div class="text-center">
                                <span class="badge bg-secondary">Non</span>
                            </div>';
                })
                ->addColumn('status', function($row) {
                    if ($row->resignation_date) {
                        return '<span class="badge bg-danger">Démissionné</span>';
                    }
                    return '<span class="badge bg-success">Actif</span>';
                })
                ->addColumn('action', function($row) {
                    $btn = '<div class="dropdown dropstart">';
                    $btn .= '<a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical fs-6"></i>
                            </a>';
                    $btn .= '<ul class="dropdown-menu dropdown-menu-end">';

                    if (auth()->user()->can('view_employees')) {
                        $btn .= '<li><a class="dropdown-item" href="'.route('employees.show', $row->employee_id).'">
                                    <i class="fas fa-eye me-2"></i>Voir</a></li>';
                    }

                    if (auth()->user()->can('edit_employees')) {
                        $btn .= '<li><a class="dropdown-item" href="'.route('employees.edit', $row->employee_id).'">
                                    <i class="fas fa-edit me-2"></i>Modifier</a></li>';
                    }

                    if (auth()->user()->can('manage_employee_documents')) {
                        $btn .= '<li><a class="dropdown-item" href="'.route('employees.documents.index', $row->employee_id).'">
                                    <i class="fas fa-folder-open me-2"></i>Documents</a></li>';
                    }

                    if (!$row->user && auth()->user()->can('manage_users')) {
                        $btn .= '<li><a class="dropdown-item create-account" href="#" data-id="'.$row->employee_id.'" data-name="'.e($row->full_name).'">
                                    <i class="fas fa-user-plus me-2"></i>Créer compte utilisateur</a></li>';
                    }

                    if (auth()->user()->can('delete_employees')) {
                        $btn .= '<li><hr class="dropdown-divider"></li>';
                        $btn .= '<li><a class="dropdown-item delete" href="#" data-id="'.$row->employee_id.'" data-name="'.e($row->full_name).'">
                                    <i class="fas fa-trash text-danger me-2"></i>Supprimer</a></li>';
                    }

                    $btn .= '</ul></div>';
                    return $btn;
                })
                ->rawColumns(['full_name', 'photo', 'cin', 'phone', 'salary', 'hire_date','department', 'has_account', 'status', 'action'])
                ->make(true);
        }

        // Statistics for dashboard
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::whereNull('resignation_date')->count();
        $departedEmployees = Employee::whereNotNull('resignation_date')->count();
        $totalMonthlySalary = Employee::whereNull('resignation_date')->sum('monthly_salary');
        $avgMonthlySalary = $activeEmployees > 0 ? $totalMonthlySalary / $activeEmployees : 0;

        $departments = Employee::whereNotNull('department')->distinct('department')->pluck('department');

        // Get employees with user accounts count
        $employeesWithAccounts = Employee::whereNotNull('user_id')->count();
        $employeesWithoutAccounts = $totalEmployees - $employeesWithAccounts;

        // Get role distribution
        $roleDistribution = [];
        $roles = Role::all();
        foreach ($roles as $role) {
            $roleDistribution[$role->name] = User::role($role->name)->count();
        }

        return view('pages.employees.index', compact(
            'totalEmployees',
            'activeEmployees',
            'departedEmployees',
            'totalMonthlySalary',
            'avgMonthlySalary',
            'departments',
            'employeesWithAccounts',
            'employeesWithoutAccounts',
            'roleDistribution'
        ));
    }

    public function create()
    {
        $this->authorize('create_employees');

        $roles = Role::all();
        return view('pages.employees.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create_employees');

        $request->validate([
            'full_name' => 'required|string|max:200',
            'cin' => 'nullable|string|max:50|unique:employees,cin',
            'cnss' => 'nullable|string|max:50|unique:employees,cnss',
            'zk_uid' => 'nullable|string|max:50|unique:employees,zk_uid',
            'email' => 'nullable|email|max:100|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'hire_date' => 'required|date',
            'resignation_date' => 'nullable|date',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hourly_salary' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'create_user_account' => 'nullable|boolean',
            'username' => 'required_if:create_user_account,1|nullable|string|max:255|unique:users,username',
            'user_email' => 'required_if:create_user_account,1|nullable|email|max:255|unique:users,email',
            'password' => 'required_if:create_user_account,1|nullable|string|min:8|confirmed',
            'role_id' => 'required_if:create_user_account,1|nullable|exists:roles,id',
        ]);

        DB::beginTransaction();
        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $filename = 'employee_' . time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('employees', $filename, 'public');
            }

            $employee = Employee::create([
                'full_name' => $request->full_name,
                'cin' => $request->cin,
                'cnss' => $request->cnss,
                'zk_uid' => $request->zk_uid,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'birth_date' => $request->birth_date,
                'hire_date' => $request->hire_date,
                'resignation_date' => $request->resignation_date,
                'department' => $request->department,
                'position' => $request->position,
                'hourly_salary' => $request->hourly_salary,
                'monthly_salary' => $request->monthly_salary,
                'emergency_contact' => $request->emergency_contact,
                'emergency_phone' => $request->emergency_phone,
                'photo' => $photoPath,
            ]);

            // Create user account if requested
            if ($request->create_user_account) {
                $user = User::create([
                    'username' => $request->username,
                    'email' => $request->user_email,
                    'password' => Hash::make($request->password),
                    'is_active' => true,
                ]);

                $role = Role::findById($request->role_id);
                $user->assignRole($role);

                $employee->update(['user_id' => $user->id]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Employé créé avec succès!',
                'employee_id' => $employee->employee_id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $this->authorize('view_employees');
        // Get role distribution
        $roleDistribution = [];
        $roles = Role::all();
        foreach ($roles as $role) {
            $roleDistribution[$role->name] = User::role($role->name)->count();
        }

        $employee = Employee::with('user.roles')->findOrFail($id);
        return view('pages.employees.show', compact('employee','roleDistribution'));
    }

    public function edit($id)
    {
        $this->authorize('edit_employees');

        $employee = Employee::findOrFail($id);
        $roles = Role::all();
        $userRoles = $employee->user ? $employee->user->roles->pluck('id')->toArray() : [];

        return view('pages.employees.edit', compact('employee', 'roles', 'userRoles'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit_employees');

        $employee = Employee::findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:200',
            'cin' => 'nullable|string|max:50|unique:employees,cin,'.$id.',employee_id',
            'cnss' => 'nullable|string|max:50|unique:employees,cnss,'.$id.',employee_id',
            'zk_uid' => 'nullable|string|max:50|unique:employees,zk_uid,'.$id.',employee_id',
            'email' => 'nullable|email|max:100|unique:employees,email,'.$id.',employee_id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'hire_date' => 'required|date',
            'resignation_date' => 'nullable|date',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hourly_salary' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'update_user_roles' => 'nullable|boolean',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        DB::beginTransaction();
        try {
            $photoPath = $employee->photo;

            if ($request->hasFile('photo')) {
                if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                    Storage::disk('public')->delete($employee->photo);
                }

                $photo = $request->file('photo');
                $filename = 'employee_' . time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('employees', $filename, 'public');
            }

            $employee->update([
                'full_name' => $request->full_name,
                'cin' => $request->cin,
                'cnss' => $request->cnss,
                'zk_uid' => $request->zk_uid,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'birth_date' => $request->birth_date,
                'hire_date' => $request->hire_date,
                'resignation_date' => $request->resignation_date,
                'department' => $request->department,
                'position' => $request->position,
                'hourly_salary' => $request->hourly_salary,
                'monthly_salary' => $request->monthly_salary,
                'emergency_contact' => $request->emergency_contact,
                'emergency_phone' => $request->emergency_phone,
                'photo' => $photoPath,
            ]);

            // Update user roles if employee has a user account
            if ($employee->user && $request->update_user_roles && $request->has('role_ids')) {
                $roles = Role::whereIn('id', $request->role_ids)->get();
                $employee->user->syncRoles($roles);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Employé mis à jour avec succès!'
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
        $this->authorize('delete_employees');

        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($id);

            if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                Storage::disk('public')->delete($employee->photo);
            }

            // Optionally delete the associated user account
            if ($employee->user && auth()->user()->can('manage_users')) {
                $employee->user->delete();
            }

            $employee->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Employé supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createUser(Request $request, $id)
    {
        $this->authorize('manage_users');

        $employee = Employee::findOrFail($id);

        if ($employee->user) {
            return response()->json([
                'success' => false,
                'message' => 'Cet employé a déjà un compte utilisateur.'
            ], 400);
        }

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::findById($request->role_id);

            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);

            $user->assignRole($role);
            $employee->update(['user_id' => $user->id]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compte utilisateur créé avec succès!',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $role->name
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du compte: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateUserRoles(Request $request, $id)
    {
        $this->authorize('manage_users');

        $employee = Employee::findOrFail($id);

        if (!$employee->user) {
            return response()->json([
                'success' => false,
                'message' => 'Cet employé n\'a pas de compte utilisateur.'
            ], 400);
        }

        $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        try {
            $roles = Role::whereIn('id', $request->role_ids)->get();
            $employee->user->syncRoles($roles);

            return response()->json([
                'success' => true,
                'message' => 'Rôles mis à jour avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAvailableRoles()
    {
        $this->authorize('manage_users');

        $roles = Role::all()->map(function($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions->count(),
                'users_count' => $role->users->count()
            ];
        });

        return response()->json([
            'success' => true,
            'roles' => $roles
        ]);
    }

    public function getStatistics()
    {
        $this->authorize('view_employees');

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::whereNull('resignation_date')->count();
        $departedEmployees = Employee::whereNotNull('resignation_date')->count();
        $totalMonthlySalary = Employee::whereNull('resignation_date')->sum('monthly_salary');
        $avgMonthlySalary = $activeEmployees > 0 ? $totalMonthlySalary / $activeEmployees : 0;

        $employeesWithAccounts = Employee::whereNotNull('user_id')->count();
        $employeesWithoutAccounts = $totalEmployees - $employeesWithAccounts;

        $roleDistribution = [];
        $roles = Role::all();
        foreach ($roles as $role) {
            $roleDistribution[$role->name] = User::role($role->name)->count();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalEmployees,
                'active' => $activeEmployees,
                'departed' => $departedEmployees,
                'total_monthly_salary' => $totalMonthlySalary,
                'avg_monthly_salary' => $avgMonthlySalary,
                'employees_with_accounts' => $employeesWithAccounts,
                'employees_without_accounts' => $employeesWithoutAccounts,
                'role_distribution' => $roleDistribution,
            ]
        ]);
    }
}
