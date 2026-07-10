@extends('layouts.app')

@section('title', 'Sorties de Production - Par Commande')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Sorties de Production par Commande</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Sorties de Production
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Numéro d'Ordre</label>
                            <input type="text" class="form-control" id="filterOrderNumber" placeholder="PO-202401-0001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Produit</label>
                            <select class="form-control select2" id="filterProductId">
                                <option value="">Tous les produits</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->product_id }}">
                                        {{ $product->product_code }} - {{ $product->product_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Famille</label>
                            <select class="form-control select2" id="filterFamilleId">
                                <option value="">Toutes les familles</option>
                                @foreach ($familles as $famille)
                                    <option value="{{ $famille->famille_id }}">
                                        {{ $famille->famille_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Période</label>
                            <input type="text" class="form-control date-range-picker" id="filterDateRange"
                                placeholder="Sélectionner une période">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type Production</label>
                            <select class="form-control" id="filterOutputType">
                                <option value="">Tous</option>
                                <option value="type1">Production</option>
                                <option value="type2">Découpage</option>
                                <option value="type3">Conversion</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-secondary" id="resetFilters">
                                <i class="fas fa-redo me-1"></i> Réinitialiser
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Filtrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-boxes me-2"></i>Sorties de Production par Commande
                        </h5>
                        <div class="btn-group">
                            @can('create_production_output')
                                <a href="{{ route('production-output.create') }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-plus me-1"></i> Nouvelle Sortie
                                </a>
                            @endcan
                            <a href="{{ route('production-orders.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-tasks me-1"></i> Voir Ventes
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="production-output-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Commande</th>
                                        <th>Produit</th>
                                        <th>Production</th>
                                        <th>Volume</th>
                                        <th>Déchets</th>
                                        <th>Dates Sortie</th>
                                        <th>Créé le</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Outputs Modal -->
    <div class="modal fade" id="outputsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="outputsModalTitle">Détails des Sorties</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="outputsModalContent">
                    <!-- Content loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Wastes Modal -->
    <div class="modal fade" id="wastesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="wastesModalTitle">Détails des Déchets</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="wastesModalContent">
                    <!-- Content loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Volume Modal -->
    <div class="modal fade" id="volumeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="volumeModalTitle">Détails des Volumes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="volumeModalContent">
                    <!-- Content loaded via AJAX -->
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
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention!</strong> Cette action est irréversible.
                    </div>
                    <p>Êtes-vous sûr de vouloir supprimer cette sortie de production ?</p>
                    <ul class="text-danger">
                        <li>Le stock du produit sera ajusté</li>
                        <li>L'état de l'ordre de production sera recalculé</li>
                        <li>Cette action sera enregistrée dans l'historique</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Supprimer
                        </button>
                    </form>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.075);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .badge-status-completed {
            background-color: #28a745;
        }

        .badge-status-in_progress {
            background-color: #ffc107;
            color: #000;
        }

        .badge-status-pending {
            background-color: #6c757d;
        }

        .badge-status-cancelled {
            background-color: #dc3545;
        }

        .progress-sm {
            height: 20px;
        }

        .clickable-cell {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .clickable-cell:hover {
            background-color: #f8f9fa;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                language: "fr",
                placeholder: "Sélectionner...",
                allowClear: true
            });

            // Initialize Date Range Picker
            $('.date-range-picker').daterangepicker({
                locale: {
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    applyLabel: 'Appliquer',
                    cancelLabel: 'Annuler',
                    daysOfWeek: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
                    monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
                    ],
                    firstDay: 1
                },
                autoApply: true,
                startDate: moment().subtract(30, 'days'),
                endDate: moment()
            });

            // Initialize DataTable
            var table = $('#production-output-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('production-output.index') }}",
                    data: function(d) {
                        d.order_number = $('#filterOrderNumber').val();
                        d.product_id = $('#filterProductId').val();
                        d.famille_id = $('#filterFamilleId').val();
                        d.date_range = $('#filterDateRange').val();
                        d.output_type = $('#filterOutputType').val();
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
                        data: 'order_info',
                        name: 'order_number',
                        orderable: true
                    },
                    {
                        data: 'product_info',
                        name: 'product.product_name',
                        orderable: true
                    },
                    {
                        data: 'production_summary',
                        name: 'production_summary',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'volume_info',
                        name: 'volume_info',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'waste_info',
                        name: 'waste_info',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'output_dates',
                        name: 'output_dates',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'created_info',
                        name: 'created_at',
                        orderable: true,
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
                    [7, 'desc']
                ], // Default order by created_at
                responsive: true,
                dom: '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
                buttons: [{
                        extend: 'excel',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-print me-1"></i> Imprimer',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    }
                ],
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "Tous"]
                ],
                createdRow: function(row, data, dataIndex) {
                    // Add highlighting based on order status
                    if (data.status === 'completed') {
                        $(row).addClass('table-success');
                    } else if (data.status === 'cancelled') {
                        $(row).addClass('table-danger');
                    } else if (data.status === 'in_progress') {
                        $(row).addClass('table-warning');
                    }
                }
            });

            // Apply filters
            $('#filterForm').submit(function(e) {
                e.preventDefault();
                table.draw();
            });

            // Reset filters
            $('#resetFilters').click(function() {
                $('#filterOrderNumber').val('');
                $('#filterProductId').val('').trigger('change');
                $('#filterFamilleId').val('').trigger('change');
                $('#filterDateRange').val('');
                $('#filterOutputType').val('');
                table.draw();
            });

            // View outputs button click
            $(document).on('click', '.view-outputs-btn', function() {
                var orderId = $(this).data('order-id');
                loadOrderOutputs(orderId);
            });

            // View wastes button click
            $(document).on('click', '.view-wastes-btn', function() {
                var orderId = $(this).data('order-id');
                loadOrderWastes(orderId);
            });

            // View volume button click
            $(document).on('click', '.view-volume-btn', function() {
                var orderId = $(this).data('order-id');
                loadOrderVolume(orderId);
            });

            // Function to load order outputs
            function loadOrderOutputs(orderId) {
                $.ajax({
                    url: "{{ url('production-output/order-outputs') }}/" + orderId,
                    type: "GET",
                    beforeSend: function() {
                        $('#outputsModalContent').html(
                            '<div class="text-center p-5"><div class="spinner-border text-primary"></div><p class="mt-2">Chargement...</p></div>'
                        );
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#outputsModalTitle').text('Sorties - ' + response.order_number);

                            var html = '<div class="table-responsive">';
                            html += '<table class="table table-hover">';
                            html += '<thead><tr>';
                            html += '<th>Date</th>';
                            html += '<th>Quantité</th>';
                            html += '<th>Bonnes</th>';
                            html += '<th>Défauts</th>';
                            html += '<th>Volume</th>';
                            html += '<th>Type</th>';
                            html += '</tr></thead>';
                            html += '<tbody>';

                            if (response.outputs.length > 0) {
                                response.outputs.forEach(function(output) {
                                    html += '<tr>';
                                    html += '<td>' + output.date + '</td>';
                                    html += '<td>' + output.quantity + ' unités</td>';
                                    html += '<td>' + output.good + ' unités</td>';
                                    html += '<td>' + output.defective + ' unités</td>';
                                    html += '<td>' + output.volume + ' m³</td>';
                                    html += '<td>' + getTypeBadge(output.type) + '</td>';
                                    html += '</tr>';
                                });

                                // Add totals
                                var totalQuantity = response.outputs.reduce((sum, o) => sum + parseFloat(o
                                    .quantity), 0);
                                var totalGood = response.outputs.reduce((sum, o) => sum + parseFloat(o
                                    .good), 0);
                                var totalDefective = response.outputs.reduce((sum, o) => sum + parseFloat(
                                    o.defective), 0);

                                html += '<tr class="table-primary">';
                                html += '<td><strong>TOTAL</strong></td>';
                                html += '<td><strong>' + totalQuantity + ' unités</strong></td>';
                                html += '<td><strong>' + totalGood + ' unités</strong></td>';
                                html += '<td><strong>' + totalDefective + ' unités</strong></td>';
                                html += '<td colspan="2"></td>';
                                html += '</tr>';
                            } else {
                                html +=
                                    '<tr><td colspan="6" class="text-center text-muted">Aucune sortie trouvée</td></tr>';
                            }

                            html += '</tbody></table>';
                            html += '</div>';

                            $('#outputsModalContent').html(html);
                            $('#outputsModal').modal('show');
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function() {
                        $('#outputsModalContent').html(
                            '<div class="alert alert-danger">Erreur lors du chargement des sorties</div>'
                        );
                        showToast('error', 'Erreur lors du chargement des sorties');
                    }
                });
            }

            // Function to load order wastes
            function loadOrderWastes(orderId) {
                $.ajax({
                    url: "{{ url('production-output/order-wastes') }}/" + orderId,
                    type: "GET",
                    beforeSend: function() {
                        $('#wastesModalContent').html(
                            '<div class="text-center p-5"><div class="spinner-border text-warning"></div><p class="mt-2">Chargement...</p></div>'
                        );
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#wastesModalTitle').text('Déchets - ' + response.order_number);

                            var html = '<div class="row mb-3">';
                            html += '<div class="col-md-6">';
                            html += '<div class="card bg-success text-white">';
                            html += '<div class="card-body text-center">';
                            html += '<h6>♻️ Recyclable</h6>';
                            html += '<h4>' + response.recyclable_volume + ' m³</h4>';
                            html += '</div></div></div>';

                            html += '<div class="col-md-6">';
                            html += '<div class="card bg-danger text-white">';
                            html += '<div class="card-body text-center">';
                            html += '<h6>🗑️ Déchet</h6>';
                            html += '<h4>' + response.waste_volume + ' m³</h4>';
                            html += '</div></div></div></div>';

                            html += '<div class="alert alert-info text-center">';
                            html += '<strong>Total déchets:</strong> ' + response.total_volume + ' m³';
                            html += '</div>';

                            if (response.wastes.length > 0) {
                                html += '<div class="table-responsive">';
                                html += '<table class="table table-hover">';
                                html += '<thead><tr>';
                                html += '<th>Type</th>';
                                html += '<th>Source</th>';
                                html += '<th>Volume</th>';
                                html += '<th>Dimensions</th>';
                                html += '<th>Notes</th>';
                                html += '<th>Créé le</th>';
                                html += '</tr></thead>';
                                html += '<tbody>';

                                response.wastes.forEach(function(waste) {
                                    html += '<tr>';
                                    html += '<td>' + getWasteTypeBadge(waste.type) + '</td>';
                                    html += '<td>' + waste.source + '</td>';
                                    html += '<td>' + waste.volume + ' m³</td>';
                                    html += '<td>' + waste.dimensions + '</td>';
                                    html += '<td>' + (waste.notes || '') + '</td>';
                                    html += '<td>' + waste.created + '</td>';
                                    html += '</tr>';
                                });

                                html += '</tbody></table></div>';
                            } else {
                                html +=
                                    '<div class="alert alert-warning text-center">Aucun déchet enregistré</div>';
                            }

                            $('#wastesModalContent').html(html);
                            $('#wastesModal').modal('show');
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function() {
                        $('#wastesModalContent').html(
                            '<div class="alert alert-danger">Erreur lors du chargement des déchets</div>'
                        );
                        showToast('error', 'Erreur lors du chargement des déchets');
                    }
                });
            }

            // Function to load order volume
            function loadOrderVolume(orderId) {
                $.ajax({
                    url: "{{ url('production-output/order-volume') }}/" + orderId,
                    type: "GET",
                    beforeSend: function() {
                        $('#volumeModalContent').html(
                            '<div class="text-center p-5"><div class="spinner-border text-info"></div><p class="mt-2">Chargement...</p></div>'
                        );
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#volumeModalTitle').text('Volumes - ' + response.order_number);

                            var html = '<div class="row mb-3">';
                            html += '<div class="col-md-4">';
                            html += '<div class="card">';
                            html += '<div class="card-body text-center">';
                            html += '<h6>Volume Total</h6>';
                            html += '<h4>' + response.total_volume + ' m³</h4>';
                            html += '</div></div></div>';

                            html += '<div class="col-md-4">';
                            html += '<div class="card bg-success text-white">';
                            html += '<div class="card-body text-center">';
                            html += '<h6>Volume Utile</h6>';
                            html += '<h4>' + response.total_good_volume + ' m³</h4>';
                            html += '</div></div></div>';

                            html += '<div class="col-md-4">';
                            html += '<div class="card">';
                            html += '<div class="card-body text-center">';
                            html += '<h6>Volume Déchet</h6>';
                            html += '<h4>' + response.total_waste_volume + ' m³</h4>';
                            html += '</div></div></div></div>';

                            if (response.outputs.length > 0) {
                                html += '<div class="table-responsive">';
                                html += '<table class="table table-hover">';
                                html += '<thead><tr>';
                                html += '<th>Date</th>';
                                html += '<th>Quantité</th>';
                                html += '<th>Volume Total</th>';
                                html += '<th>Volume Utile</th>';
                                html += '<th>Volume Déchet</th>';
                                html += '</tr></thead>';
                                html += '<tbody>';

                                response.outputs.forEach(function(output) {
                                    html += '<tr>';
                                    html += '<td>' + output.date + '</td>';
                                    html += '<td>' + output.quantity + ' unités</td>';
                                    html += '<td>' + output.volume + ' m³</td>';
                                    html += '<td>' + output.good_volume + ' m³</td>';
                                    html += '<td>' + output.waste_volume + ' m³</td>';
                                    html += '</tr>';
                                });

                                html += '</tbody></table></div>';
                            } else {
                                html +=
                                    '<div class="alert alert-info text-center">Aucune donnée de volume disponible</div>';
                            }

                            $('#volumeModalContent').html(html);
                            $('#volumeModal').modal('show');
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function() {
                        $('#volumeModalContent').html(
                            '<div class="alert alert-danger">Erreur lors du chargement des volumes</div>'
                        );
                        showToast('error', 'Erreur lors du chargement des volumes');
                    }
                });
            }

            // Helper function to get type badge
            function getTypeBadge(type) {
                switch (type) {
                    case 'type1':
                        return '<span class="badge bg-primary">Production</span>';
                    case 'type2':
                        return '<span class="badge bg-info">Découpage</span>';
                    case 'type3':
                        return '<span class="badge bg-success">Conversion</span>';
                    case 'mixed_family':
                        return '<span class="badge bg-warning">Mixte</span>';
                    default:
                        return '<span class="badge bg-secondary">' + type + '</span>';
                }
            }

            // Helper function to get waste type badge
            function getWasteTypeBadge(type) {
                switch (type) {
                    case 'recyclable':
                        return '<span class="badge bg-success">♻️ Recyclable</span>';
                    case 'auto_defective':
                        return '<span class="badge bg-info">Auto-défaut</span>';
                    case 'waste':
                        return '<span class="badge bg-danger">🗑️ Déchet</span>';
                    default:
                        return '<span class="badge bg-secondary">' + type + '</span>';
                }
            }

            // Handle delete button click
            $(document).on('click', '.delete-output-btn', function(e) {
                e.preventDefault();
                var outputId = $(this).data('id');
                var deleteUrl = "{{ url('production-output') }}/" + outputId;
                $('#deleteForm').attr('action', deleteUrl);
                $('#deleteModal').modal('show');
            });

            // Delete Form Submit
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

            // Toast notification function
            function showToast(type, message) {
                var toast = $('<div class="toast align-items-center text-white bg-' +
                    (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger')) +
                    ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                    '<div class="d-flex">' +
                    '<div class="toast-body">' + message + '</div>' +
                    '<button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>' +
                    '</div>' +
                    '</div>');

                $('#toast-container').append(toast);
                var bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();

                setTimeout(function() {
                    toast.remove();
                }, 5000);
            }
        });
    </script>
@endpush
