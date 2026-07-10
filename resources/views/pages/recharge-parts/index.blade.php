@extends('layouts.app')

@section('title', 'Gestion des Pièces de Rechange')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Page Header -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Pièces de Rechange</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Pièces de Rechange
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
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Total Pièces</span>
                                <h3 class="mb-0" id="totalParts">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-boxes fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Stock Total</span>
                                <h3 class="mb-0" id="totalStock">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-cubes fs-1 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Stock Bas</span>
                                <h3 class="mb-0 text-warning" id="lowStockCount">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exclamation-triangle fs-1 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Rupture Stock</span>
                                <h3 class="mb-0 text-danger" id="outOfStock">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-times-circle fs-1 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-microchip me-2"></i>Liste des Pièces de Rechange
                        </h5>
                        <div>
                            @can('create_recharge_parts')
                            <button type="button" class="btn btn-light btn-sm" onclick="addPart()">
                                <i class="fas fa-plus me-1"></i> Nouvelle Pièce
                            </button>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="parts-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Nom</th>
                                        <th>Stock Actuel</th>
                                        <th>Stock Min</th>
                                        <th>Stock Max</th>
                                        <th>Statut</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded by DataTables -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="partModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="partModalLabel">Ajouter une pièce</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="partForm">
                    @csrf
                    <input type="hidden" id="part_id" name="part_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom de la pièce *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock Actuel *</label>
                            <input type="number" class="form-control" id="current_stock" name="current_stock"
                                min="0" value="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock Minimum *</label>
                            <input type="number" class="form-control" id="min_stock" name="min_stock" min="0"
                                value="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock Maximum</label>
                            <input type="number" class="form-control" id="max_stock" name="max_stock" min="0"
                                placeholder="Optionnel">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Adjust Stock Modal -->
    <div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajuster le stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="adjustStockForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="adjust_part_id" name="part_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pièce</label>
                            <input type="text" class="form-control" id="adjust_part_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ajustement *</label>
                            <input type="number" class="form-control" id="adjustment" name="adjustment" required>
                            <small class="text-muted">Valeur positive pour ajouter, négative pour retirer</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Raison</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Optionnel"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">Appliquer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer la pièce : <strong id="deletePartName"></strong> ?</p>
                    <p class="text-danger">Cette action est irréversible !</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>

    <script>
        let table;
        let deleteId = null;

        $(document).ready(function() {
            loadStatistics();

            table = $('#parts-table').DataTable({ paging: false, lengthChange: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('recharge-parts.index') }}",
                    type: 'GET',
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
                        data: 'current_stock',
                        name: 'current_stock',
                        className: 'text-center'
                    },
                    {
                        data: 'min_stock',
                        name: 'min_stock',
                        className: 'text-center'
                    },
                    {
                        data: 'max_stock',
                        name: 'max_stock',
                        className: 'text-center'
                    },
                    {
                        data: 'stock_status',
                        name: 'stock_status',
                        orderable: false,
                        searchable: false,
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
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json",
                    search: "Rechercher:",
                    searchPlaceholder: "Nom de la pièce..."
                },
                order: [
                    [1, 'asc']
                ],
                responsive: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Tous"]
                ]
            });

            // Add/Edit Form Submit
            $('#partForm').submit(function(e) {
                e.preventDefault();

                let id = $('#part_id').val();
                let url = id ? "{{ route('recharge-parts.update', '') }}/" + id :
                    "{{ route('recharge-parts.store') }}";
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $(this).serialize() + '&_method=' + method,
                    success: function(response) {
                        if (response.success) {
                            $('#partModal').modal('hide');
                            table.ajax.reload();
                            loadStatistics();
                            showToast('success', response.message);
                            resetForm();
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        if (errors) {
                            let errorMsg = Object.values(errors).flat().join('\n');
                            showToast('error', errorMsg);
                        } else {
                            showToast('error', 'Une erreur est survenue');
                        }
                    }
                });
            });

            // Adjust Stock Form Submit
            $('#adjustStockForm').submit(function(e) {
                e.preventDefault();

                let id = $('#adjust_part_id').val();
                let adjustment = $('#adjustment').val();
                let reason = $('#reason').val();

                $.ajax({
                    url: "recharge-parts/" + id + "/adjust-stock",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        adjustment: adjustment,
                        reason: reason
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#adjustStockModal').modal('hide');
                            table.ajax.reload();
                            loadStatistics();
                            showToast('success', response.message);
                            $('#adjustStockForm')[0].reset();
                        }
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON;
                        showToast('error', response?.message || 'Une erreur est survenue');
                    }
                });
            });

            // Confirm Delete
            $('#confirmDelete').click(function() {
                if (deleteId) {
                    $.ajax({
                        url: "{{ route('recharge-parts.destroy', '') }}/" + deleteId,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#deleteModal').modal('hide');
                                table.ajax.reload();
                                loadStatistics();
                                showToast('success', response.message);
                                deleteId = null;
                            }
                        },
                        error: function(xhr) {
                            let response = xhr.responseJSON;
                            showToast('error', response?.message || 'Une erreur est survenue');
                        }
                    });
                }
            });
        });

        function loadStatistics() {
            $.ajax({
                url: "{{ route('recharge-parts.statistics') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#totalParts').text(response.data.total_parts);
                        $('#totalStock').text(response.data.total_stock);
                        $('#lowStockCount').text(response.data.low_stock_count);
                        $('#outOfStock').text(response.data.out_of_stock);
                    }
                }
            });
        }

        function addPart() {
            resetForm();
            $('#partModalLabel').text('Ajouter une pièce');
            $('#partModal').modal('show');
        }

        function editPart(id) {
            $.ajax({
                url: "{{ route('recharge-parts.show', '') }}/" + id,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        let part = response.data;
                        $('#part_id').val(part.id);
                        $('#name').val(part.name);
                        $('#current_stock').val(part.current_stock);
                        $('#min_stock').val(part.min_stock);
                        $('#max_stock').val(part.max_stock);
                        $('#partModalLabel').text('Modifier la pièce');
                        $('#partModal').modal('show');
                    }
                }
            });
        }

        function adjustStock(id, name) {
            $('#adjust_part_id').val(id);
            $('#adjust_part_name').val(name);
            $('#adjustment').val('');
            $('#reason').val('');
            $('#adjustStockModal').modal('show');
        }

        function deletePart(id, name) {
            deleteId = id;
            $('#deletePartName').text(name);
            $('#deleteModal').modal('show');
        }

        function resetForm() {
            $('#part_id').val('');
            $('#partForm')[0].reset();
            $('#current_stock').val(0);
            $('#min_stock').val(0);
            $('#max_stock').val('');
        }

        function showToast(type, message) {
            var toast = $('<div class="toast align-items-center text-white bg-' +
                (type === 'success' ? 'success' : 'danger') +
                ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                '<div class="d-flex">' +
                '<div class="toast-body">' + message + '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
                '</div>' +
                '</div>');

            $('#toast-container').append(toast);
            var bsToast = new bootstrap.Toast(toast[0]);
            bsToast.show();

            setTimeout(function() {
                toast.remove();
            }, 5000);
        }
    </script>
@endpush
