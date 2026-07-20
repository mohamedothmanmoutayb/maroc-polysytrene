@extends('layouts.app')

@section('title', 'Gestion des Machines')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Machines</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Machines
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="statistics row mb-4">
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Total Machines</span>
                                <h3 class="mb-0" id="totalMachines">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-cogs fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Machines Actives</span>
                                <h3 class="mb-0" id="activeMachines">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fs-1 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">En Maintenance</span>
                                <h3 class="mb-0" id="maintenanceMachines">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-tools fs-1 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Documents Expirés</span>
                                <h3 class="mb-0" id="expiredDocuments">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exclamation-triangle fs-1 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-cogs me-2"></i>Liste des Machines
                        </h5>
                        <div>
                            <button class="btn btn-light btn-sm" id="filterBtn">
                                <i class="fas fa-filter me-1"></i> Filtres
                            </button>
                            @can('create_machines')
                            <a href="{{ route('machines.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Nouvelle Machine
                            </a>
                            @endcan
                            <a href="{{ route('machine-maintenance.print-all') }}" target="_blank"
                                class="btn btn-light btn-sm">
                                <i class="fas fa-print me-1"></i> Imprimer Maintenance
                            </a>
                            <div class="btn-group ms-2">
                                <button type="button" class="btn btn-light btn-sm dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download me-1"></i> Exporter
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" id="exportExcelBtn">
                                            <i class="fas fa-file-excel text-success me-2"></i>Excel
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" id="exportPdfBtn">
                                            <i class="fas fa-file-pdf text-danger me-2"></i>PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters Section -->
                        <div class="row mb-3" id="filtersSection" style="display: none;">
                            <div class="col-md-3">
                                <label for="filterStatus" class="form-label">Statut</label>
                                <select class="form-control" id="filterStatus" name="status">
                                    <option value="">Tous</option>
                                    <option value="active">Actif</option>
                                    <option value="maintenance">En maintenance</option>
                                    <option value="inactive">Inactif</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterDocumentStatus" class="form-label">État des documents</label>
                                <select class="form-control" id="filterDocumentStatus">
                                    <option value="">Tous</option>
                                    <option value="expired">Expirés</option>
                                    <option value="expiring_soon">Expirant bientôt</option>
                                    <option value="valid">Valides</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-primary me-2" id="applyFilters">
                                    <i class="fas fa-check me-1"></i> Appliquer
                                </button>
                                <button class="btn btn-secondary" id="resetFilters">
                                    <i class="fas fa-undo me-1"></i> Réinitialiser
                                </button>
                            </div>
                        </div>

                        <!-- Machines Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="machines-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="15%">Nom</th>
                                        <th width="15%">N° Série</th>
                                        <th width="25%">Documents</th>
                                        <th width="10%">Statut</th>
                                        <th width="15%">Heures de fonctionnement</th>
                                        <th width="15%">Actions</th>
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

    <!-- Document History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-history me-2"></i>Historique des documents
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 id="historyModalTitle" class="mb-3"></h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>N° Document</th>
                                    <th>Autorité</th>
                                    <th>Statut</th>
                                    <th>Créé le</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">Chargement...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer la machine :</p>
                    <p class="fw-bold text-center" id="deleteMachineName"></p>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Cette action est irréversible ! Tous les documents associés seront également supprimés.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <style>
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 0;
        }

        .document-item {
            padding: 4px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .document-item:last-child {
            border-bottom: none;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        let table;
        let deleteId = null;

        $(document).ready(function() {
            // Initialize DataTable
            table = $('#machines-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('machines.index') }}",
                    data: function(d) {
                        d.status = $('#filterStatus').val();
                        d.document_status = $('#filterDocumentStatus').val();
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
                        name: 'name',
                        className: 'fw-bold'
                    },
                    {
                        data: 'serial_number',
                        name: 'serial_number',
                        className: ''
                    },
                    {
                        data: 'documents_status',
                        name: 'documents_status',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'operating_hours',
                        name: 'operating_hours',
                        className: 'text-end',
                        render: function(data) {
                            return data ? new Intl.NumberFormat('fr-FR').format(data) + ' h' :
                                '0 h';
                        }
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
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                },
                order: [
                    [1, 'asc']
                ],
                pageLength: 25,
                responsive: true
            });

            // Toggle filters
            $('#filterBtn').click(function() {
                $('#filtersSection').slideToggle();
            });

            // Apply filters
            $('#applyFilters').click(function() {
                table.ajax.reload();
                loadStatistics();
            });

            // Reset filters
            $('#resetFilters').click(function() {
                $('#filterStatus').val('');
                $('#filterDocumentStatus').val('');
                table.ajax.reload();
                loadStatistics();
            });

            // Load statistics
            loadStatistics();

            function loadStatistics() {
                $.ajax({
                    url: "{{ route('machines.index') }}",
                    type: "GET",
                    data: {
                        statistics: true
                    },
                    success: function(response) {
                        if (response.statistics) {
                            $('#totalMachines').text(response.statistics.total || 0);
                            $('#activeMachines').text(response.statistics.active || 0);
                            $('#maintenanceMachines').text(response.statistics.maintenance || 0);
                            $('#expiredDocuments').text(response.statistics.expired_documents || 0);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading statistics:', xhr);
                    }
                });
            }

            // View document history
            $(document).on('click', '.view-history', function() {
                var documentTypeId = $(this).data('document-type');
                var machineId = $(this).data('machine-id');
                var typeName = $(this).data('type-name');

                $('#historyModalTitle').text('Historique - ' + typeName);
                $('#historyTableBody').html(
                    '<tr><td colspan="6" class="text-center">Chargement...</td></tr>');

                $.ajax({
                    url: "{{ url('machines') }}/" + machineId + "/documents/" + documentTypeId +
                        "/history",
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            var html = '';
                            $.each(response.data, function(index, doc) {
                                var statusBadge = '';
                                if (doc.is_current) {
                                    statusBadge =
                                        '<span class="badge bg-success">Actuel</span>';
                                } else if (doc.end_date && new Date(doc.end_date) <
                                    new Date()) {
                                    statusBadge =
                                        '<span class="badge bg-danger">Expiré</span>';
                                } else {
                                    statusBadge =
                                        '<span class="badge bg-secondary">Ancien</span>';
                                }

                                html += '<tr>' +
                                    '<td>' + (doc.start_date || '-') + '</td>' +
                                    '<td>' + (doc.end_date || '-') + '</td>' +
                                    '<td>' + (doc.document_number || '-') + '</td>' +
                                    '<td>' + (doc.issuing_authority || '-') + '</td>' +
                                    '<td class="text-center">' + statusBadge + '</td>' +
                                    '<td>' + new Date(doc.created_at)
                                    .toLocaleDateString('fr-FR') + '</td>' +
                                    '</tr>';
                            });
                            $('#historyTableBody').html(html);
                        } else {
                            $('#historyTableBody').html(
                                '<tr><td colspan="6" class="text-center text-muted">Aucun historique trouvé</td></tr>'
                            );
                        }
                    },
                    error: function() {
                        $('#historyTableBody').html(
                            '<tr><td colspan="6" class="text-center text-danger">Erreur lors du chargement</td></tr>'
                        );
                    }
                });

                var modal = new bootstrap.Modal(document.getElementById('historyModal'));
                modal.show();
            });

            // Delete button click
            $(document).on('click', '.delete', function() {
                deleteId = $(this).data('id');
                let name = $(this).data('name');
                $('#deleteMachineName').text(name);
                $('#deleteModal').modal('show');
            });

            // Confirm delete
            $('#confirmDeleteBtn').click(function() {
                if (!deleteId) return;

                $.ajax({
                    url: "{{ url('machines') }}/" + deleteId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#deleteModal').modal('hide');
                            table.ajax.reload();
                            loadStatistics();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors de la suppression');
                    }
                });
            });

            // Export functions
            $('#exportExcelBtn').click(function(e) {
                e.preventDefault();
                var filters = {
                    status: $('#filterStatus').val(),
                    document_status: $('#filterDocumentStatus').val()
                };
                var queryString = $.param(filters);
                window.location.href = "{{ route('machines.export.excel') }}?" + queryString;
            });

            $('#exportPdfBtn').click(function(e) {
                e.preventDefault();
                var filters = {
                    status: $('#filterStatus').val(),
                    document_status: $('#filterDocumentStatus').val()
                };
                var queryString = $.param(filters);
                window.location.href = "{{ route('machines.export.pdf') }}?" + queryString;
            });

            // Auto-refresh statistics every 30 seconds
            setInterval(loadStatistics, 30000);
        });

        function showToast(type, message) {
            var toast = $('<div class="toast align-items-center text-white bg-' +
                (type === 'success' ? 'success' : 'danger') +
                ' border-0" role="alert">' +
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
