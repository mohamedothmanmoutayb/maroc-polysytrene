@extends('layouts.app')

@section('title', 'Attribution des Rôles aux Utilisateurs')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">
                            <i class="fas fa-user-tag me-2"></i>Attribution des Rôles
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
                                        Attribution des Rôles
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
                                <i class="fas fa-users fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="totalUsers">0</h2>
                                <span class="text-muted">Total Utilisateurs</span>
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
                                <i class="fas fa-user-check fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="usersWithRoles">0</h2>
                                <span class="text-muted">Avec rôles</span>
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
                                <i class="fas fa-user-times fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="usersWithoutRoles">0</h2>
                                <span class="text-muted">Sans rôle</span>
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
                                <i class="fas fa-tags fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="totalRoles">{{ $roles->count() }}</h2>
                                <span class="text-muted">Rôles disponibles</span>
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
                    <i class="fas fa-users me-2"></i>Liste des Utilisateurs
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="users-table" class="table table-hover w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>Utilisateur</th>
                                <th>Email</th>
                                <th>Rôles actuels</th>
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

    <!-- Assign Roles Modal -->
    <div class="modal fade" id="assignRolesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-tags me-2"></i>Assigner des rôles
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="assignRolesForm">
                    @csrf
                    <input type="hidden" id="user_id" name="user_id">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Assignation de rôles pour: <strong id="user_name"></strong>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Sélectionner les rôles</label>
                            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                @foreach ($roles as $role)
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input role-checkbox" name="roles[]"
                                            value="{{ $role->id }}" id="role_{{ $role->id }}">
                                        <label class="form-check-label" for="role_{{ $role->id }}">
                                            <span class="badge bg-primary me-2">{{ ucfirst($role->name) }}</span>
                                            <small class="text-muted">
                                                ({{ $role->permissions->count() }} permissions)
                                            </small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
@endsection

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
            var table = $('#users-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.users.roles') }}",
                    type: 'GET',
                    error: function(xhr) {
                        console.error('DataTable error:', xhr);
                        showToast('error', 'Erreur lors du chargement des données');
                    }
                },
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
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'roles',
                        name: 'roles',
                        orderable: false,
                        searchable: false
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
                    searchPlaceholder: "Nom, email..."
                },
                order: [
                    [1, 'asc']
                ],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "Tous"]
                ],
                drawCallback: function() {
                    // Update statistics after each draw
                    updateStatistics();
                }
            });

            function updateStatistics() {
                var totalUsers = table.rows().count();
                var usersWithRoles = 0;
                var usersWithoutRoles = 0;

                table.rows().every(function() {
                    var rowData = this.data();
                    if (rowData.roles && rowData.roles !==
                        '<span class="badge bg-secondary">Aucun rôle</span>') {
                        usersWithRoles++;
                    } else {
                        usersWithoutRoles++;
                    }
                });

                $('#totalUsers').text(totalUsers);
                $('#usersWithRoles').text(usersWithRoles);
                $('#usersWithoutRoles').text(usersWithoutRoles);
            }

            // Assign roles button click
            $(document).on('click', '.assign-roles', function() {
                var userId = $(this).data('id');
                var userName = $(this).data('name');

                $('#user_id').val(userId);
                $('#user_name').text(userName);

                // Load current user roles
                $.ajax({
                    url: "{{ url('admin/users') }}/" + userId + "/roles",
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            // Reset all checkboxes
                            $('.role-checkbox').prop('checked', false);
                            // Check the user's current roles
                            response.roles.forEach(function(roleId) {
                                $('#role_' + roleId).prop('checked', true);
                            });
                        }
                    },
                    error: function(xhr) {
                        showToast('error', 'Erreur lors du chargement des rôles');
                    }
                });

                $('#assignRolesModal').modal('show');
            });

            // Assign roles form submit
            $('#assignRolesForm').submit(function(e) {
                e.preventDefault();
                var userId = $('#user_id').val();
                var submitBtn = $(this).find('button[type="submit"]');

                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Enregistrement...');

                $.ajax({
                    url: "{{ url('admin/users') }}/" + userId + "/assign-roles",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            $('#assignRolesModal').modal('hide');
                            table.ajax.reload();
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON?.message ||
                            'Une erreur est survenue';
                        showToast('error', errorMessage);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i> Enregistrer');
                    }
                });
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

            // Auto-refresh every 60 seconds
            setInterval(function() {
                table.ajax.reload(null, false);
            }, 60000);
        });
    </script>
@endpush
