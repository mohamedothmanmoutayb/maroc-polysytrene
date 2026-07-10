@extends('layouts.app')

@section('title', 'Gestion des Matières Premières')

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <style>
        .stock-low {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .stock-high {
            background-color: rgba(40, 167, 69, 0.1) !important;
        }

        .stock-critical {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Matières Premières</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Matières Premières
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-boxes me-2"></i>Liste des Matières Premières
                        </h5>
                        <div>
                            @can('create_raw_materials')
                            <a href="{{ route('raw-materials.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Nouvelle Matière
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="materials-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Code</th>
                                        <th>Nom</th>
                                        <th>Catégorie</th>
                                        <th>Unité</th>
                                        <th>Stock Actuel</th>
                                        <th>Coût Moyen</th>
                                        <th>Prix</th>
                                        <th>Statut Stock</th>
                                        <th>Statut</th>
                                        <th width="8%">Actions</th>
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

    <!-- Modal pour ajuster le stock (Updated for new system) -->
    <div class="modal fade" id="adjustStockModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gestion du Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Le stock est maintenant géré automatiquement via les achats.
                        Pour ajuster le stock, veuillez créer une commande d'achat.
                    </div>
                    <div class="text-center">
                        <a href="{{ route('raw-material-purchases.create') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-cart me-1"></i> Créer un achat
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour voir le détail du stock par prix -->
    <div class="modal fade" id="stockDetailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détail du Stock par Prix</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm" id="stockDetailTable">
                        <thead>
                            <tr>
                                <th>Prix d'achat (DH)</th>
                                <th>Quantité disponible</th>
                                <th>Valeur totale (DH)</th>
                                <th>Dernière réception</th>
                            </tr>
                        </thead>
                        <tbody id="stockDetailBody">
                            <!-- Stock details will be loaded here -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total:</th>
                                <th id="totalStock">0</th>
                                <th id="totalValue">0.00 DH</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer la matière première : <strong id="deleteMaterialName"></strong> ?
                    </p>
                    <p class="text-danger">Cette action est irréversible !</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialiser DataTable
            var table = $('#materials-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: "{{ route('raw-materials.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'material_code',
                        name: 'material_code'
                    },
                    {
                        data: 'material_name',
                        name: 'material_name'
                    },
                    {
                        data: 'category_name',
                        name: 'category.category_name'
                    },
                    {
                        data: 'unit_of_measure',
                        name: 'unit_of_measure',
                        className: 'text-center'
                    },
                    {
                        data: 'current_stock',
                        name: 'current_stock',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                return '<span class="badge bg-info cursor-pointer view-stock" data-id="' +
                                    row.material_id + '" title="Voir le détail par prix">' + data +
                                    '</span>';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'average_cost',
                        name: 'average_cost',
                        className: 'text-center',
                        render: function(data) {
                            return '<span class="badge bg-success">' + data + '</span>';
                        }
                    },
                    {
                        data: 'prices',
                        name: 'prices',
                        orderable: false,
                        searchable: false,
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
                        data: 'status_badge',
                        name: 'is_active',
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
                    },
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                },
                order: [
                    [1, 'asc']
                ],
                responsive: true,
                dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip',
                buttons: [{
                        extend: 'excel',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                        }
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                        }
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-print me-1"></i> Imprimer',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                        }
                    }
                ],
                createdRow: function(row, data, dataIndex) {
                    // Stock status row coloring
                    var currentStock = parseFloat(data.current_stock) || 0;
                    var minStock = parseFloat(data.min_stock_level) || 0;

                    if (currentStock <= minStock * 0.5 && minStock > 0) {
                        $(row).addClass('stock-critical');
                    } else if (currentStock <= minStock && minStock > 0) {
                        $(row).addClass('stock-low');
                    } else if (currentStock >= (parseFloat(data.max_stock_level) || Infinity)) {
                        $(row).addClass('stock-high');
                    }
                },
                drawCallback: function() {
                    $('[data-bs-toggle="popover"]').each(function() {
                        var existing = bootstrap.Popover.getInstance(this);
                        if (existing) existing.dispose();
                        new bootstrap.Popover(this);
                    });
                }
            });

            // Handle dropdown menu clicks
            $(document).on('click', '.dropdown-item.view', function() {
                var materialId = $(this).data('id');
                window.location.href = '/raw-materials/' + materialId;
            });

            $(document).on('click', '.dropdown-item.edit', function() {
                var materialId = $(this).data('id');
                window.location.href = '/raw-materials/' + materialId + '/edit';
            });

            $(document).on('click', '.dropdown-item.adjust-stock', function() {
                $('#adjustStockModal').modal('show');
            });

            // View stock details by price
            $(document).on('click', '.view-stock', function() {
                var materialId = $(this).data('id');
                loadStockDetails(materialId);
            });

            function loadStockDetails(materialId) {
                $.ajax({
                    url: '/raw-materials/' + materialId + '/stock-details',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#stockDetailBody').empty();
                            var totalStock = 0;
                            var totalValue = 0;

                            response.data.forEach(function(detail) {
                                var row = '<tr>' +
                                    '<td class="text-end">' + parseFloat(detail.unit_price)
                                    .toFixed(2) + ' DH</td>' +
                                    '<td class="text-center">' + parseFloat(detail
                                        .remaining_quantity).toFixed(2) + '</td>' +
                                    '<td class="text-end">' + (parseFloat(detail
                                        .remaining_quantity) * parseFloat(detail
                                        .unit_price)).toFixed(2) + ' DH</td>' +
                                    '<td>' + new Date(detail.movement_date).toLocaleDateString(
                                        'fr-FR') + '</td>' +
                                    '</tr>';
                                $('#stockDetailBody').append(row);

                                totalStock += parseFloat(detail.remaining_quantity);
                                totalValue += parseFloat(detail.remaining_quantity) *
                                    parseFloat(detail.unit_price);
                            });

                            $('#totalStock').text(totalStock.toFixed(2));
                            $('#totalValue').text(totalValue.toFixed(2) + ' DH');
                            $('#stockDetailModal').modal('show');
                        } else {
                            showToast('error', 'Impossible de charger les détails du stock');
                        }
                    },
                    error: function() {
                        showToast('error', 'Erreur lors du chargement des détails');
                    }
                });
            }

            $(document).on('click', '.dropdown-item.delete', function() {
                var materialId = $(this).data('id');
                var materialName = $(this).data('name');

                $('#deleteMaterialName').text(materialName);
                $('#deleteForm').attr('action', '/raw-materials/' + materialId);
                $('#deleteModal').modal('show');
            });

            $('#deleteForm').submit(function(e) {
                e.preventDefault();

                var form = $(this);
                var url = form.attr('action');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#deleteModal').modal('hide');
                            table.draw();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON;
                        showToast('error', response?.message ||
                            'Erreur lors de la suppression');
                    }
                });
            });

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
                }, 3000);
            }
        });
    </script>
@endpush
