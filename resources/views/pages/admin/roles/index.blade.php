@extends('layouts.app')

@section('title', 'Gestion des Rôles')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">
                            <i class="fas fa-users-cog me-2"></i>Gestion des Rôles
                        </h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Rôles
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary rounded-circle p-3 text-white">
                                <i class="fas fa-users-cog fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="totalRoles">0</h2>
                                <span class="text-muted">Total Rôles</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success rounded-circle p-3 text-white">
                                <i class="fas fa-users fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="totalUsers">0</h2>
                                <span class="text-muted">Utilisateurs</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-info rounded-circle p-3 text-white">
                                <i class="fas fa-key fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="totalPermissions">0</h2>
                                <span class="text-muted">Permissions</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning rounded-circle p-3 text-white">
                                <i class="fas fa-tags fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="avgPermissions">0</h2>
                                <span class="text-muted">Moy. par rôle</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0" style="color:white">
                    <i class="fas fa-users-cog me-2"></i>Liste des Rôles
                </h5>
                <div>
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                        <i class="fas fa-plus me-1"></i> Nouveau Rôle
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover w-100" id="roles-table">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>Nom du rôle</th>
                                <th>Guard</th>
                                <th>Utilisateurs</th>
                                <th>Permissions</th>
                                <th>Créé le</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Role Modal -->
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Créer un nouveau rôle
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="createRoleForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom du rôle *</label>
                            <input type="text" class="form-control" name="name" required
                                placeholder="Ex: commercial, chef_production, etc.">
                            <small class="text-muted">Utilisez des lettres minuscules et des underscores</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                @php
                                    $permissionsByModule = \Spatie\Permission\Models\Permission::orderBy('module')
                                        ->orderBy('name')
                                        ->get()
                                        ->groupBy('module');
                                @endphp
                                @foreach ($permissionsByModule as $module => $permissions)
                                    <div class="mb-3">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input select-all-module"
                                                data-module="{{ $module }}">
                                            <label class="form-check-label fw-bold">
                                                <i class="fas fa-folder-open me-1"></i> {{ $module ?: 'Général' }}
                                            </label>
                                        </div>
                                        <div class="ms-4">
                                            @foreach ($permissions as $permission)
                                                <div class="form-check form-check-inline mb-2">
                                                    <input type="checkbox" class="form-check-input permission-checkbox"
                                                        name="permissions[]" value="{{ $permission->id }}"
                                                        data-module="{{ $module }}">
                                                    <label class="form-check-label small">
                                                        <code>{{ $permission->name }}</code>
                                                        @if ($permission->description)
                                                            <i class="fas fa-info-circle text-muted"
                                                                title="{{ $permission->description }}"></i>
                                                        @endif
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer le rôle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    @push('stylesheets')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
        <style>
            .table td {
                vertical-align: middle;
            }

            .badge {
                font-size: 11px;
                padding: 4px 8px;
            }

            .toast-container {
                z-index: 9999;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
        <script>
            $(document).ready(function() {
                var table = $('#roles-table').DataTable({ paging: false, lengthChange: false, 
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: "{{ route('admin.roles.index') }}",
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'guard_name',
                            name: 'guard_name',
                            className: 'text-center'
                        },
                        {
                            data: 'users_count',
                            name: 'users_count',
                            orderable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'permissions_count',
                            name: 'permissions_count',
                            orderable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            className: 'text-center'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        }
                    ],
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json",
                        search: "Rechercher:",
                        searchPlaceholder: "Nom du rôle..."
                    },
                    order: [
                        [1, 'asc']
                    ],
                    pageLength: 10,
                    drawCallback: function() {
                        updateStatistics();
                    }
                });

                function updateStatistics() {
                    var totalRoles = table.rows().count();
                    $('#totalRoles').text(totalRoles);

                    var totalUsers = 0;
                    var totalPermissionsSum = 0;
                    table.rows().every(function() {
                        var rowData = this.data();
                        var usersMatch = rowData.users_count.match(/\d+/);
                        if (usersMatch) totalUsers += parseInt(usersMatch[0]);
                        var permsMatch = rowData.permissions_count.match(/\d+/);
                        if (permsMatch) totalPermissionsSum += parseInt(permsMatch[0]);
                    });

                    $('#totalUsers').text(totalUsers);
                    $('#totalPermissions').text(totalPermissionsSum);
                    $('#avgPermissions').text(totalRoles > 0 ? Math.round(totalPermissionsSum / totalRoles) : 0);
                }

                // Select all permissions in a module
                $('.select-all-module').change(function() {
                    var module = $(this).data('module');
                    var isChecked = $(this).is(':checked');
                    $('.permission-checkbox[data-module="' + module + '"]').prop('checked', isChecked);
                });

                // When any permission is unchecked, uncheck the "select all" for that module
                $('.permission-checkbox').change(function() {
                    var module = $(this).data('module');
                    var allChecked = $('.permission-checkbox[data-module="' + module + '"]:checked').length ===
                        $('.permission-checkbox[data-module="' + module + '"]').length;
                    $('.select-all-module[data-module="' + module + '"]').prop('checked', allChecked);
                });

                $('#createRoleForm').submit(function(e) {
                    e.preventDefault();
                    var submitBtn = $(this).find('button[type="submit"]');
                    submitBtn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span> Création...');

                    $.ajax({
                        url: "{{ route('admin.roles.store') }}",
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.success) {
                                $('#createRoleModal').modal('hide');
                                table.ajax.reload();
                                showToast('success', response.message);
                                $('#createRoleForm')[0].reset();
                                // Reset all checkboxes
                                $('.select-all-module').prop('checked', false);
                                $('.permission-checkbox').prop('checked', false);
                            } else {
                                showToast('error', response.message);
                            }
                        },
                        error: function(xhr) {
                            showToast('error', xhr.responseJSON?.message ||
                                'Une erreur est survenue');
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false).html('Créer le rôle');
                        }
                    });
                });

                $(document).on('click', '.delete-role', function() {
                    var id = $(this).data('id');
                    var name = $(this).data('name');

                    if (confirm('Êtes-vous sûr de vouloir supprimer le rôle "' + name + '" ?')) {
                        $.ajax({
                            url: "{{ url('admin/roles') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    table.ajax.reload();
                                    showToast('success', response.message);
                                } else {
                                    showToast('error', response.message);
                                }
                            },
                            error: function(xhr) {
                                showToast('error', xhr.responseJSON?.message || 'Erreur');
                            }
                        });
                    }
                });

                function showToast(type, message) {
                    var toastId = 'toast-' + Date.now();
                    var bgClass = type === 'success' ? 'bg-success' : 'bg-danger';

                    var toastHtml = `
                        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
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
