<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Roles Management
     */
    public function rolesIndex(Request $request)
    {
        if ($request->ajax()) {
            $roles = Role::query();

            return DataTables::of($roles)
                ->addIndexColumn()
                ->addColumn('users_count', function($row) {
                    return '<span class="badge bg-info">' . $row->users()->count() . ' utilisateurs</span>';
                })
                ->addColumn('permissions_count', function($row) {
                    return '<span class="badge bg-success">' . $row->permissions()->count() . ' permissions</span>';
                })
                ->addColumn('guard_name', function($row) {
                    return '<span class="badge bg-secondary">' . $row->guard_name . '</span>';
                })
                ->addColumn('created_at', function($row) {
                    return $row->created_at->format('d/m/Y H:i');
                })
                ->addColumn('action', function($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="'.route('admin.roles.show', $row->id).'" class="btn btn-sm btn-info" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>';
                    $btn .= '<a href="'.route('admin.roles.edit', $row->id).'" class="btn btn-sm btn-warning" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>';
                    $btn .= '<a href="'.route('admin.roles.permissions', $row->id).'" class="btn btn-sm btn-primary" title="Gérer permissions">
                                <i class="fas fa-key"></i>
                            </a>';
                    if (!in_array($row->name, ['admin', 'manager', 'user'])) {
                        $btn .= '<button class="btn btn-sm btn-danger delete-role" data-id="'.$row->id.'" data-name="'.$row->name.'" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['users_count', 'permissions_count', 'guard_name', 'action'])
                ->make(true);
        }

        return view('pages.admin.roles.index');
    }

    public function rolesCreate()
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        return view('pages.admin.roles.create', compact('permissions'));
    }

    public function rolesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rôle créé avec succès!',
                'role_id' => $role->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rolesShow($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $users = $role->users()->with('employee')->paginate(10);
        $permissionsGrouped = $role->permissions->groupBy('module');

        return view('pages.admin.roles.show', compact('role', 'users', 'permissionsGrouped'));
    }

    public function rolesEdit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('pages.admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function rolesUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        try {
            DB::beginTransaction();

            $role = Role::findOrFail($id);
            $role->update(['name' => $request->name]);

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rôle mis à jour avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rolesDestroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            if (in_array($role->name, ['admin', 'Super Admin', 'Manager', 'Production', 'Decoupage', 'Vente et Caisse'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un rôle système!'
                ], 400);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rôle supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rolesPermissions($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('pages.admin.roles.permissions', compact('role', 'permissions', 'rolePermissions'));
    }

    public function updateRolePermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        try {
            DB::beginTransaction();

            $role = Role::findOrFail($id);

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permissions mises à jour avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permissions Management
     */
    public function permissionsIndex(Request $request)
    {
        if ($request->ajax()) {
            $permissions = Permission::query();

            return DataTables::of($permissions)
                ->addIndexColumn()
                ->addColumn('module', function($row) {
                    return $row->module ? '<span class="badge bg-primary">' . ucfirst($row->module) . '</span>' : '<span class="badge bg-secondary">Général</span>';
                })
                ->addColumn('roles_count', function($row) {
                    $count = $row->roles()->count();
                    return '<span class="badge bg-info">' . $count . ' rôles</span>';
                })
                ->addColumn('action', function($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<button class="btn btn-sm btn-warning edit-permission" data-id="'.$row->id.'" data-name="'.$row->name.'" data-module="'.$row->module.'" data-description="'.$row->description.'">
                                <i class="fas fa-edit"></i>
                            </button>';
                    $btn .= '<button class="btn btn-sm btn-danger delete-permission" data-id="'.$row->id.'" data-name="'.$row->name.'">
                                <i class="fas fa-trash"></i>
                            </button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['module', 'roles_count', 'action'])
                ->make(true);
        }

        $modules = Permission::select('module')->distinct()->whereNotNull('module')->pluck('module');
        return view('pages.admin.permissions.index', compact('modules'));
    }

    public function permissionsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'module' => 'nullable|string|max:100',
            'description' => 'nullable|string'
        ]);

        try {
            $permission = Permission::create([
                'name' => $request->name,
                'guard_name' => 'web',
                'module' => $request->module,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permission créée avec succès!',
                'permission' => $permission
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function permissionsUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'module' => 'nullable|string|max:100',
            'description' => 'nullable|string'
        ]);

        try {
            $permission = Permission::findOrFail($id);
            $permission->update([
                'name' => $request->name,
                'module' => $request->module,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permission mise à jour avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function permissionsDestroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $permission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permission supprimée avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * User Role Assignment
     */
    public function userRoles(Request $request)
    {
        if ($request->ajax()) {
            $users = User::with('roles', 'employee');

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('name', function($row) {
                    if ($row->employee) {
                        return '<strong>' . $row->employee->full_name . '</strong><br><small class="text-muted">' . $row->username . '</small>';
                    }
                    return '<strong>' . $row->username . '</strong>';
                })
                ->addColumn('email', function($row) {
                    return $row->email;
                })
                ->addColumn('roles', function($row) {
                    $roles = $row->roles;
                    if ($roles->isEmpty()) {
                        return '<span class="badge bg-secondary">Aucun rôle</span>';
                    }
                    $html = '';
                    foreach ($roles as $role) {
                        $html .= '<span class="badge bg-primary me-1">' . $role->name . '</span>';
                    }
                    return $html;
                })
                ->addColumn('action', function($row) {
                    return '<button class="btn btn-sm btn-primary assign-roles" data-id="'.$row->id.'" data-name="'.($row->employee?->full_name ?? $row->username).'">
                                <i class="fas fa-tags me-1"></i> Assigner rôles
                            </button>';
                })
                ->rawColumns(['name', 'roles', 'action'])
                ->make(true);
        }

        $roles = Role::all();
        return view('pages.admin.users.roles', compact('roles'));
    }

    public function assignUserRoles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        try {
            $user = User::findOrFail($id);
            $roles = Role::whereIn('id', $request->roles)->get();
            $user->syncRoles($roles);

            return response()->json([
                'success' => true,
                'message' => 'Rôles assignés avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUserRoles($id)
    {
        $user = User::with('roles')->findOrFail($id);
        return response()->json([
            'success' => true,
            'roles' => $user->roles->pluck('id')
        ]);
    }

    /**
     * Seed roles and permissions – delegates to RolesAndPermissionsSeeder.
     * Call via: php artisan db:seed --class=RolesAndPermissionsSeeder
     */
    public static function seedPermissions()
    {
        (new \Database\Seeders\RolesAndPermissionsSeeder())->run();
    }

}
