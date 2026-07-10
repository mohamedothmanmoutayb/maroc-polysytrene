@extends('layouts.app')

@section('title', 'Détails du rôle - ' . $role->name)

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">
                            <i class="fas fa-users-cog me-2"></i>Détails du rôle : {{ $role->name }}
                        </h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.roles.index') }}">Rôles</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-info text-white">
                                        Détails
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Information Card -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Informations du rôle
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>ID:</strong></td>
                                <td>#{{ $role->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nom:</strong></td>
                                <td><span class="badge bg-primary">{{ $role->name }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Guard:</strong></td>
                                <td><span class="badge bg-secondary">{{ $role->guard_name }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Créé le:</strong></td>
                                <td>{{ $role->created_at->format('d/m/Y à H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Modifié le:</strong></td>
                                <td>{{ $role->updated_at->format('d/m/Y à H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Utilisateurs:</strong></td>
                                <td><span class="badge bg-info">{{ $users->total() }} utilisateur(s)</span></td>
                            </tr>
                            <tr>
                                <td><strong>Permissions:</strong></td>
                                <td><span class="badge bg-success">{{ $role->permissions->count() }} permission(s)</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            <a href="{{ route('admin.roles.permissions', $role->id) }}" class="btn btn-primary">
                                <i class="fas fa-key me-1"></i> Gérer permissions
                            </a>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions Card -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-key me-2"></i>Permissions associées
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($permissionsGrouped->count() > 0)
                            <div class="mb-3">
                                <input type="text" class="form-control" id="searchPermission"
                                    placeholder="Rechercher une permission...">
                            </div>
                            <div style="max-height: 500px; overflow-y: auto;">
                                @foreach ($permissionsGrouped as $module => $modulePermissions)
                                    <div class="mb-3 permission-module" data-module="{{ $module }}">
                                        <div class="border-bottom pb-2 mb-2">
                                            <h6 class="fw-bold">
                                                <i class="fas fa-folder-open me-2"></i>
                                                {{ $module ?: 'Général' }}
                                                <span class="badge bg-secondary ms-2">{{ $modulePermissions->count() }}
                                                    permissions</span>
                                            </h6>
                                        </div>
                                        <div class="ms-3">
                                            @foreach ($modulePermissions as $permission)
                                                <div class="permission-item mb-1"
                                                    data-permission-name="{{ strtolower($permission->name) }}">
                                                    <span class="badge bg-info me-2">
                                                        <i class="fas fa-check-circle"></i>
                                                    </span>
                                                    <code>{{ $permission->name }}</code>
                                                    @if ($permission->description)
                                                        <i class="fas fa-info-circle text-muted ms-1"
                                                            title="{{ $permission->description }}"></i>
                                                    @endif
                                                </div>
                                                <br>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Aucune permission n'est associée à ce rôle.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Users with this role -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>Utilisateurs avec ce rôle
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($users->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom d'utilisateur</th>
                                            <th>Email</th>
                                            <th>Employé</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            <tr>
                                                <td>#{{ $user->id }}</td>
                                                <td>
                                                    <strong>{{ $user->username }}</strong>
                                                    @if ($user->employee)
                                                        <br><small
                                                            class="text-muted">{{ $user->employee->full_name }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    @if ($user->employee)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-user-check me-1"></i> Associé
                                                        </span>
                                                        <br>
                                                        <small>{{ $user->employee->position ?? 'Employé' }}</small>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-user-slash me-1"></i> Non associé
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.users.roles') }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-tags me-1"></i> Gérer rôles
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $users->links() }}
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Aucun utilisateur n'a ce rôle pour le moment.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Search permission functionality
                $('#searchPermission').on('keyup', function() {
                    var searchTerm = $(this).val().toLowerCase();

                    $('.permission-module').each(function() {
                        var module = $(this);
                        var hasVisiblePermissions = false;

                        module.find('.permission-item').each(function() {
                            var permissionName = $(this).data('permission-name');
                            if (permissionName.includes(searchTerm) || searchTerm === '') {
                                $(this).show();
                                hasVisiblePermissions = true;
                            } else {
                                $(this).hide();
                            }
                        });

                        if (hasVisiblePermissions || searchTerm === '') {
                            module.show();
                        } else {
                            module.hide();
                        }
                    });
                });

                function showToast(type, message) {
                    var toastId = 'toast-' + Date.now();
                    var bgClass = type === 'success' ? 'bg-success' : (type === 'info' ? 'bg-info' : 'bg-danger');

                    var toastHtml = `
                        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <i class="fas ${type === 'success' ? 'fa-check-circle' : (type === 'info' ? 'fa-info-circle' : 'fa-exclamation-circle')} me-2"></i>
                                    ${message}
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                            </div>
                        </div>
                    `;

                    $('#toast-container').append(toastHtml);
                    var toastElement = document.getElementById(toastId);
                    var bsToast = new bootstrap.Toast(toastElement);
                    bsToast.show();

                    toastElement.addEventListener('hidden.bs.toast', function() {
                        $(this).remove();
                    });
                }
            });
        </script>
    @endpush
@endsection
