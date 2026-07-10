@extends('layouts.app')

@section('title', 'Gestion des Permissions')

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">
                            <i class="fas fa-key me-2"></i>Gestion des Permissions
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
                                        Permissions
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
            <div class="col-md-4">
                <div class="card bg-primary-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary rounded-circle p-3 text-white">
                                <i class="fas fa-key fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="totalPermissions">0</h2>
                                <span class="text-muted">Total Permissions</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success rounded-circle p-3 text-white">
                                <i class="fas fa-folder-open fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="totalModules">{{ $modules->count() }}</h2>
                                <span class="text-muted">Modules</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-info rounded-circle p-3 text-white">
                                <i class="fas fa-tags fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="avgPermissions">0</h2>
                                <span class="text-muted">Moy. par module</span>
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
                    <i class="fas fa-key me-2"></i>Liste des Permissions
                </h5>
                <div>
                    <button type="button" class="btn btn-light" data-bs-toggle="modal"
                        data-bs-target="#createPermissionModal">
                        <i class="fas fa-plus me-1"></i> Nouvelle Permission
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-primary filter-module" data-module="all">Tous</button>
                        @foreach ($modules as $module)
                            <button type="button" class="btn btn-outline-secondary filter-module"
                                data-module="{{ $module }}">{{ $module }}</button>
                        @endforeach
                    </div>
                </div>
                <input type="hidden" id="moduleFilter" value="">
                <div class="table-responsive">
                    <table class="table table-hover w-100" id="permissions-table">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>Nom</th>
                                <th>Module</th>
                                <th>Description</th>
                                <th>Rôles associés</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Permission Modal -->
    <div class="modal fade" id="createPermissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Créer une permission
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="createPermissionForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="name" required
                                placeholder="Ex: view_products, create_sales_orders">
                            <small class="text-muted">Format: action_module (ex: view_products)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Module</label>
                            <input type="text" class="form-control" name="module"
                                placeholder="Ex: Produits, Ventes, Production">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Description de la permission"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Permission Modal -->
    <div class="modal fade" id="editPermissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Modifier la permission
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editPermissionForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_permission_id" name="permission_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Module</label>
                            <input type="text" class="form-control" id="edit_module" name="module">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
        <script>
            $(document).ready(function() {
                var table = $('#permissions-table').DataTable({ paging: false, lengthChange: false, 
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('admin.permissions.index') }}",
                        data: function(d) {
                            d.module = $('#moduleFilter').val();
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
                            data: 'module',
                            name: 'module',
                            className: 'text-center'
                        },
                        {
                            data: 'description',
                            name: 'description',
                            defaultContent: '-'
                        },
                        {
                            data: 'roles_count',
                            name: 'roles_count',
                            orderable: false,
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
                        searchPlaceholder: "Nom, module..."
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
                    var totalPermissions = table.rows().count();
                    $('#totalPermissions').text(totalPermissions);

                    var modules = {};
                    table.rows().every(function() {
                        var rowData = this.data();
                        if (rowData.module && rowData.module !==
                            '<span class="badge bg-primary">Général</span>') {
                            var moduleName = rowData.module.replace('<span class="badge bg-primary">', '')
                                .replace('</span>', '');
                            modules[moduleName] = (modules[moduleName] || 0) + 1;
                        }
                    });

                    var avgPermissions = Object.keys(modules).length > 0 ?
                        Math.round(totalPermissions / Object.keys(modules).length) : 0;
                    $('#avgPermissions').text(avgPermissions);
                }

                $('.filter-module').click(function() {
                    var module = $(this).data('module');
                    $('#moduleFilter').val(module === 'all' ? '' : module);
                    table.ajax.reload();
                    $('.filter-module').removeClass('btn-primary').addClass('btn-outline-secondary');
                    $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
                });

                $('#createPermissionForm').submit(function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: "{{ route('admin.permissions.store') }}",
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.success) {
                                $('#createPermissionModal').modal('hide');
                                table.ajax.reload();
                                showToast('success', response.message);
                                $('#createPermissionForm')[0].reset();
                            }
                        },
                        error: function(xhr) {
                            showToast('error', xhr.responseJSON?.message || 'Erreur');
                        }
                    });
                });

                $(document).on('click', '.edit-permission', function() {
                    var id = $(this).data('id');
                    var name = $(this).data('name');
                    var module = $(this).data('module');
                    var description = $(this).data('description');

                    $('#edit_permission_id').val(id);
                    $('#edit_name').val(name);
                    $('#edit_module').val(module);
                    $('#edit_description').val(description);
                    $('#editPermissionModal').modal('show');
                });

                $('#editPermissionForm').submit(function(e) {
                    e.preventDefault();
                    var id = $('#edit_permission_id').val();
                    $.ajax({
                        url: "{{ url('admin/permissions') }}/" + id,
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.success) {
                                $('#editPermissionModal').modal('hide');
                                table.ajax.reload();
                                showToast('success', response.message);
                            }
                        },
                        error: function(xhr) {
                            showToast('error', xhr.responseJSON?.message || 'Erreur');
                        }
                    });
                });

                $(document).on('click', '.delete-permission', function() {
                    var id = $(this).data('id');
                    var name = $(this).data('name');

                    if (confirm('Êtes-vous sûr de vouloir supprimer la permission "' + name + '" ?')) {
                        $.ajax({
                            url: "{{ url('admin/permissions') }}/" + id,
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
                        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert">
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
                    var bsToast = new bootstrap.Toast(toastElement, {
                        delay: 5000
                    });
                    bsToast.show();

                    toastElement.addEventListener('hidden.bs.toast', function() {
                        $(this).remove();
                    });
                }
            });
        </script>
    @endpush
@endsection
