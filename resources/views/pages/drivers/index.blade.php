@extends('layouts.app')

@section('title', 'Gestion des Chauffeurs')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Chauffeurs</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Chauffeurs
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
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Total Chauffeurs</span>
                                <h3 class="mb-0" id="totalDrivers">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Chauffeurs Actifs</span>
                                <h3 class="mb-0" id="activeDrivers">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fs-1 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Permis Expirant</span>
                                <h3 class="mb-0" id="expiringLicenses">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-id-card fs-1 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Visites Médicales</span>
                                <h3 class="mb-0" id="pendingMedicalVisits">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-stethoscope fs-1 text-info"></i>
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
                            <i class="fas fa-users me-2"></i>Liste des Chauffeurs
                        </h5>
                        <div>
                            <button class="btn btn-light btn-sm" id="filterBtn">
                                <i class="fas fa-filter me-1"></i> Filtres
                            </button>
                            @can('create_drivers')
                            <a href="{{ route('drivers.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Nouveau Chauffeur
                            </a>
                            @endcan
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
                                    <option value="inactive">Inactif</option>
                                    <option value="suspended">Suspendu</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterLicenseStatus" class="form-label">Statut Permis</label>
                                <select class="form-control" id="filterLicenseStatus">
                                    <option value="">Tous</option>
                                    <option value="valid">Valide</option>
                                    <option value="expiring_soon">Expire bientôt</option>
                                    <option value="expired">Expiré</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterMedicalStatus" class="form-label">Visite Médicale</label>
                                <select class="form-control" id="filterMedicalStatus">
                                    <option value="">Tous</option>
                                    <option value="up_to_date">À jour</option>
                                    <option value="due_soon">Prévue bientôt</option>
                                    <option value="overdue">En retard</option>
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

                        <!-- Drivers Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="drivers-table">
                                <thead class="thead-light">
                                    32
                                    <th width="5%">#</th>
                                    <th width="15%">Nom complet</th>
                                    <th width="10%">CIN</th>
                                    <th width="10%">N° Permis</th>
                                    <th width="12%">Catégorie</th>
                                    <th width="10%">Statut Permis</th>
                                    <th width="10%">Visite Médicale</th>
                                    <th width="10%">Téléphone</th>
                                    <th width="8%">Statut</th>
                                    <th width="10%">Actions</th>
                                    32
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
                    <p>Êtes-vous sûr de vouloir supprimer le chauffeur :</p>
                    <p class="fw-bold text-center" id="deleteDriverName"></p>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Cette action est irréversible !
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

        .stat-card {
            transition: transform 0.2s;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        .table td {
            vertical-align: middle;
        }

        .driver-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .dropdown-item i {
            width: 20px;
            text-align: center;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .toast {
            min-width: 300px;
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
            table = $('#drivers-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('drivers.index') }}",
                    data: function(d) {
                        d.status = $('#filterStatus').val();
                        d.license_status = $('#filterLicenseStatus').val();
                        d.medical_status = $('#filterMedicalStatus').val();
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
                        data: 'full_name',
                        name: 'full_name',
                        render: function(data, type, row) {
                            return '<div class="d-flex align-items-center">' +
                                '<div>' +
                                '<div class="fw-bold">' + data + '</div>' +
                                '</div>' +
                                '</div>';
                        }
                    },
                    {
                        data: 'cin',
                        name: 'cin',
                        className: 'text-center'
                    },
                    {
                        data: 'license_number',
                        name: 'license_number',
                        className: 'text-center fw-bold'
                    },
                    {
                        data: 'license_category',
                        name: 'license_category',
                        className: 'text-center',
                        render: function(data) {
                            if (!data) return '-';
                            const categories = {
                                'A': 'A - Moto',
                                'B': 'B - Voiture',
                                'C': 'C - Camion',
                                'D': 'D - Transport',
                                'E': 'E - Remorque'
                            };
                            return '<span class="badge bg-secondary">' + (categories[data] ||
                                data) + '</span>';
                        }
                    },
                    {
                        data: 'license_status',
                        name: 'license_status',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'medical_status',
                        name: 'medical_status',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'phone',
                        name: 'phone',
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
                $('#filterLicenseStatus').val('');
                $('#filterMedicalStatus').val('');
                table.ajax.reload();
                loadStatistics();
            });

            // Load statistics
            loadStatistics();

            function loadStatistics() {
                $.ajax({
                    url: "{{ route('drivers.statistics') }}",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#totalDrivers').text(response.statistics.total || 0);
                            $('#activeDrivers').text(response.statistics.active || 0);
                            $('#expiringLicenses').text(response.statistics.expiring_licenses || 0);
                            $('#pendingMedicalVisits').text(response.statistics
                                .pending_medical_visits || 0);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading statistics:', xhr);
                    }
                });
            }

            // Delete button click
            $(document).on('click', '.delete', function() {
                deleteId = $(this).data('id');
                let name = $(this).data('name');
                $('#deleteDriverName').text(name);
                $('#deleteModal').modal('show');
            });

            // Confirm delete
            $('#confirmDeleteBtn').click(function() {
                if (!deleteId) return;

                $.ajax({
                    url: "{{ url('drivers') }}/" + deleteId,
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
                    license_status: $('#filterLicenseStatus').val(),
                    medical_status: $('#filterMedicalStatus').val()
                };
                var queryString = $.param(filters);
                window.location.href = "{{ route('drivers.export.excel') }}?" + queryString;
            });

            $('#exportPdfBtn').click(function(e) {
                e.preventDefault();
                var filters = {
                    status: $('#filterStatus').val(),
                    license_status: $('#filterLicenseStatus').val(),
                    medical_status: $('#filterMedicalStatus').val()
                };
                var queryString = $.param(filters);
                window.location.href = "{{ route('drivers.export.pdf') }}?" + queryString;
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
