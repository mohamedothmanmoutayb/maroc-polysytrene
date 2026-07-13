@extends('layouts.app')

@section('title', 'Consommation Matières Premières')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion de la Consommation Matières Premières</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Consommation
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
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Coût Total</span>
                                <h3 class="mb-0" id="totalConsumptionCost">0 DH</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-money-bill-wave fs-1 text-primary"></i>
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
                                <span class="text-muted">Coût Déchets</span>
                                <h3 class="mb-0" id="totalWasteCost">0 DH</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-trash-alt fs-1 text-danger"></i>
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
                                <span class="text-muted">Ce Mois</span>
                                <h3 class="mb-0" id="monthlyConsumption">0 DH</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-alt fs-1 text-success"></i>
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
                                <span class="text-muted">Top Matières</span>
                                <h3 class="mb-0" id="topMaterialsCount">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-star fs-1 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-filter me-2"></i>Filtres
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filter_order" class="form-label">Ordre de Production</label>
                            <select class="form-control select2" id="filter_order">
                                <option value="">Tous les ordres</option>
                                @foreach ($productionOrders as $order)
                                    <option value="{{ $order->order_id }}">
                                        {{ $order->order_number }} - {{ $order->product->product_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filter_status" class="form-label">Statut Consommation</label>
                            <select class="form-control" id="filter_status">
                                <option value="">Tous</option>
                                <option value="planned">Planifié</option>
                                <option value="under">Sous-consommation</option>
                                <option value="over">Sur-consommation</option>
                                <option value="conforme">Conforme</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterDateRange" class="form-label">Durée</label>
                            <input type="text" class="form-control date-range-picker" id="filterDateRange"
                                placeholder="Sélectionner une période">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <button class="btn btn-primary" id="applyFilters">
                            <i class="fas fa-filter me-1"></i> Appliquer
                        </button>
                        <button class="btn btn-secondary" id="resetFilters">
                            <i class="fas fa-redo me-1"></i> Réinitialiser
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-list-alt me-2"></i>Liste des Consommations Matières Premières
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="production-consumption-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Date Consommation</th>
                                        <th>Ordre Production</th>
                                        <th>Matière Première</th>
                                        <th>Planifié</th>
                                        <th>Réel</th>
                                        <th>Déchet</th>
                                        <th>Taux Déchet</th>
                                        <th>Coût Unitaire</th>
                                        <th>Coût Total</th>
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

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cette consommation ?</p>
                    <p class="text-danger">Cette action ajustera le stock de la matière première !</p>
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

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
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

            // Initialize Date Range Picker ("Durée")
            $('#filterDateRange').daterangepicker({
                locale: {
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    applyLabel: 'Appliquer',
                    cancelLabel: 'Annuler',
                    customRangeLabel: 'Personnalisé',
                    daysOfWeek: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
                    monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
                    ],
                    firstDay: 1
                },
                autoApply: false,
                autoUpdateInput: false,
                showDropdowns: true,
                opens: 'right',
                ranges: {
                    'Aujourd\'hui': [moment(), moment()],
                    'Hier': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Cette semaine': [moment().startOf('week'), moment().endOf('week')],
                    'La semaine dernière': [moment().subtract(1, 'week').startOf('week'), moment().subtract(
                        1, 'week').endOf('week')],
                    'Ce mois-ci': [moment().startOf('month'), moment().endOf('month')],
                    'Le mois dernier': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    '30 derniers jours': [moment().subtract(29, 'days'), moment()],
                    '90 derniers jours': [moment().subtract(89, 'days'), moment()],
                    'Cette année': [moment().startOf('year'), moment().endOf('year')],
                    'L\'année dernière': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1,
                        'year').endOf('year')]
                }
            }, function(start, end, label) {
                $('#filterDateRange').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
                $('#filterDateRange').data('iso', start.format('YYYY-MM-DD') + ' to ' + end.format(
                    'YYYY-MM-DD'));
            });

            // Initialize DataTable
            var table = $('#production-consumption-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('production-consumption.index') }}",
                    data: function(d) {
                        d.order_id = $('#filter_order').val();
                        d.status = $('#filter_status').val();
                        d.date_range = $('#filterDateRange').data('iso') || '';
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
                        data: 'consumption_date',
                        name: 'created_at',
                        className: 'text-center'
                    },
                    {
                        data: 'order_number',
                        name: 'productionOrder.order_number'
                    },
                    {
                        data: 'material_name',
                        name: 'rawMaterial.material_name'
                    },
                    {
                        data: 'planned_quantity',
                        name: 'planned_quantity',
                        className: 'text-center'
                    },
                    {
                        data: 'actual_quantity_used',
                        name: 'actual_quantity_used',
                        className: 'text-center'
                    },
                    {
                        data: 'waste_quantity',
                        name: 'waste_quantity',
                        className: 'text-center'
                    },
                    {
                        data: 'waste_percentage',
                        name: 'waste_quantity',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'unit_cost',
                        name: 'unit_cost',
                        className: 'text-right'
                    },
                    {
                        data: 'total_cost',
                        name: 'total_cost',
                        className: 'text-right'
                    },
                    {
                        data: 'consumption_status',
                        name: 'consumption_status',
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
                    [1, 'desc']
                ],
                responsive: true,
                dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip',
                buttons: [{
                        extend: 'excel',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-file-pdf me-1"></i> PDF'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-print me-1"></i> Imprimer'
                    }
                ],
                createdRow: function(row, data, dataIndex) {
                    // Add highlighting for over-consumption
                    if (data.actual_quantity_used > data.planned_quantity * 1.05) {
                        $(row).addClass('table-warning');
                    }
                    // Add highlighting for high waste
                    var wastePercent = (data.waste_quantity / data.actual_quantity_used) * 100;
                    if (wastePercent > 10) {
                        $(row).addClass('table-danger');
                    }
                }
            });

            // Load statistics
            loadStatistics();

            // Function to load statistics
            function loadStatistics() {
                $.ajax({
                    url: "{{ route('production-consumption.statistics') }}",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#totalConsumptionCost').text(response.data.total_consumption_cost
                                .toFixed(2) + ' DH');
                            $('#totalWasteCost').text(response.data.total_waste_cost.toFixed(2) +
                                ' DH');
                            $('#monthlyConsumption').text(response.data.monthly_consumption.toFixed(2) +
                                ' DH');
                            $('#topMaterialsCount').text(response.data.top_materials.length);
                        }
                    },
                    error: function() {
                        // Fallback if statistics endpoint fails
                        $('#totalConsumptionCost').text('0 DH');
                        $('#totalWasteCost').text('0 DH');
                        $('#monthlyConsumption').text('0 DH');
                        $('#topMaterialsCount').text('0');
                    }
                });
            }

            // Apply filters
            $('#applyFilters').click(function() {
                table.draw();
                loadStatistics();
            });

            // Reset filters
            $('#resetFilters').click(function() {
                $('#filter_order').val('').trigger('change');
                $('#filter_status').val('');
                $('#filterDateRange').val('').removeData('iso');
                table.draw();
                loadStatistics();
            });

            // Handle delete button click
            var deleteConsumptionId;
            $(document).on('click', '.dropdown-item.delete', function() {
                deleteConsumptionId = $(this).data('id');

                $('#deleteForm').attr('action', "{{ url('production-consumption') }}/" +
                    deleteConsumptionId);
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
                            loadStatistics();
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

            // Auto-refresh statistics every 30 seconds
            setInterval(loadStatistics, 30000);

            function showToast(type, message) {
                var toast = $('<div class="toast align-items-center text-white bg-' +
                    (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger')) +
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

            // Add CSS for highlighting
            $('head').append(
                '<style>.table-warning { background-color: rgba(255, 193, 7, 0.1) !important; } .table-danger { background-color: rgba(220, 53, 69, 0.1) !important; }</style>'
            );
        });
    </script>
@endpush
