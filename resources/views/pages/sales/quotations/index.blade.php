@extends('layouts.app')

@section('title', 'Gestion des Devis')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Devis</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <span class="text-muted">Ventes</span>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Devis
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4 vente">
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Total Devis</span>
                                <h3 class="mb-0" id="totalQuotes">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-file-invoice fs-1 text-primary"></i>
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
                                <span class="text-muted">En attente</span>
                                <h3 class="mb-0" id="pendingQuotes">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fs-1 text-warning"></i>
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
                                <span class="text-muted">Acceptés</span>
                                <h3 class="mb-0" id="acceptedQuotes">0</h3>
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
                                <span class="text-muted">Brouillons</span>
                                <h3 class="mb-0" id="draftQuotes">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-pen fs-1 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted">Montant Total des Devis Acceptés</span>
                                <h2 class="mb-0" id="totalAcceptedAmount">0 DH</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fs-1 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body py-2">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Date début</label>
                                <input type="date" class="form-control form-control-sm" id="dateFrom">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Date fin</label>
                                <input type="date" class="form-control form-control-sm" id="dateTo">
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-sm btn-primary me-1" id="applyFilter">
                                    <i class="fas fa-filter me-1"></i> Appliquer
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" id="resetFilter">
                                    <i class="fas fa-undo me-1"></i> Réinitialiser
                                </button>
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
                            <i class="fas fa-file-invoice me-2"></i>Liste des Devis
                        </h5>
                        <div>
                            @can('create_sales_quotations')
                            <a href="{{ route('sales.quotations.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Nouveau Devis
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="quotations-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>N° Devis</th>
                                        <th>Client</th>
                                        <th>Date</th>
                                        <th>Montant TTC</th>
                                        <th>Statut</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded by DataTables -->
                                </tbody>
                                <tfoot>
                                    <tr class="table-info fw-bold">
                                        <td colspan="4" class="text-end">Total Montant TTC :</td>
                                        <td id="table-total-amount" class="text-end">0,00 DH</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF Options Modal -->
    <div class="modal fade" id="pdfOptionsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-pdf me-2"></i>Options du PDF
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="pdfOptionsForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Afficher les prix</label>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="show_prices"
                                            id="showPricesYes" value="1" checked>
                                        <label class="form-check-label" for="showPricesYes">
                                            <i class="fas fa-eye text-success me-1"></i>Avec prix
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="show_prices"
                                            id="showPricesNo" value="0">
                                        <label class="form-check-label" for="showPricesNo">
                                            <i class="fas fa-eye-slash text-warning me-1"></i>Sans prix
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">En-tête</label>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="show_logo" id="showLogoYes"
                                            value="1" checked>
                                        <label class="form-check-label" for="showLogoYes">
                                            <i class="fas fa-image text-info me-1"></i>Avec entête (logo)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="show_logo" id="showLogoNo"
                                            value="0">
                                        <label class="form-check-label" for="showLogoNo">
                                            <i class="fas fa-ban text-secondary me-1"></i>Sans entête
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Type d'affichage</label>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="display_type"
                                            id="displayTypeUnite" value="unite" checked>
                                        <label class="form-check-label" for="displayTypeUnite">
                                            <i class="fas fa-weight-hanging text-primary me-1"></i>Avec unité (U)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="display_type"
                                            id="displayTypeVolume" value="volume">
                                        <label class="form-check-label" for="displayTypeVolume">
                                            <i class="fas fa-cube text-success me-1"></i>Avec volume
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                <span id="displayTypeHelp">Unité: Affiche l'unité de mesure standard</span>
                            </small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                    <button type="button" class="btn btn-primary" id="generatePdfBtn">
                        <i class="fas fa-print me-1"></i>Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="document">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer le devis : <strong id="deleteQuoteNumber"></strong> ?</p>
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

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <style>
        .vente .fas,
        .fa-chart-line {
            font-size: 38px !important;
        }

        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -6px;
            margin-left: 1px;
        }

        .dropdown-submenu:hover .dropdown-menu {
            display: block;
        }

        .dropdown-submenu .dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.255em;
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid transparent;
            border-right: 0;
            border-bottom: 0.3em solid transparent;
            border-left: 0.3em solid;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .dropdown-menu .dropdown-item {
            cursor: pointer;
        }

        .dropdown-menu .dropdown-item i {
            width: 20px;
            text-align: center;
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
    <script>
        function formatCurrency(amount) {
            return new Intl.NumberFormat('fr-MA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount) + ' DH';
        }

        $(document).ready(function() {
            let currentQuoteId = null;

            // Initialize DataTable
            var table = $('#quotations-table').DataTable({ paging: true, lengthChange: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'Tout']
                ],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('sales.quotations.index') }}",
                    type: 'GET',
                    data: function(d) {
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                    }
                },
                drawCallback: function() {
                    var json = this.api().ajax.json();
                    if (json && json.total_amount !== undefined) {
                        $('#table-total-amount').text(formatCurrency(json.total_amount));
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
                        data: 'quote_number',
                        name: 'quote_number'
                    },
                    {
                        data: 'client_name',
                        name: 'client.display_name'
                    },
                    {
                        data: 'quote_date',
                        name: 'quote_date',
                        className: 'text-center'
                    },
                    {
                        data: 'final_amount',
                        name: 'final_amount',
                        className: 'text-end'
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
                    [3, 'desc']
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
                ]
            });

            // Filter buttons
            $('#applyFilter').click(function() {
                table.ajax.reload();
            });

            $('#resetFilter').click(function() {
                $('#dateFrom').val('');
                $('#dateTo').val('');
                table.ajax.reload();
            });

            // Load statistics
            loadStatistics();

            function loadStatistics() {
                $.ajax({
                    url: "{{ route('sales.quotations.statistics') }}",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#totalQuotes').text(response.data.total);
                            $('#pendingQuotes').text(response.data.sent || 0);
                            $('#acceptedQuotes').text(response.data.accepted || 0);
                            $('#draftQuotes').text(response.data.draft || 0);
                            $('#totalAcceptedAmount').text(formatCurrency(response.data
                                .accepted_amount || 0));
                        }
                    },
                    error: function() {
                        $('#totalQuotes').text('0');
                        $('#pendingQuotes').text('0');
                        $('#acceptedQuotes').text('0');
                        $('#draftQuotes').text('0');
                        $('#totalAcceptedAmount').text('0 DH');
                    }
                });
            }

            // Refresh table
            $('#refreshTable').click(function() {
                table.ajax.reload();
                loadStatistics();
            });

            // Handle PDF generation
            window.openPdfOptions = function(quoteId) {
                currentQuoteId = quoteId;
                $('#pdfOptionsModal').modal('show');
            }

            $('#generatePdfBtn').click(function() {
                const showPrices = $('input[name="show_prices"]:checked').val();
                const showLogo = $('input[name="show_logo"]:checked').val();
                const displayType = $('input[name="display_type"]:checked').val();

                const url =
                    `/sales/quotations/${currentQuoteId}/pdf?show_prices=${showPrices}&show_logo=${showLogo}&display_type=${displayType}`;

                const printWindow = window.open(url, '_blank', 'width=800,height=600');

                if (printWindow) {
                    printWindow.focus();

                    printWindow.onload = function() {
                        setTimeout(function() {
                            printWindow.print();
                            printWindow.onafterprint = function() {
                                printWindow.close();
                            };
                        }, 1000);
                    };
                }

                $('#pdfOptionsModal').modal('hide');
            });

            // Update help text based on display type selection
            $('input[name="display_type"]').change(function() {
                if ($(this).val() === 'unite') {
                    $('#displayTypeHelp').text(
                        'Unité: Affiche l\'unité de mesure standard (PIECE, KG, etc.)');
                } else {
                    $('#displayTypeHelp').text(
                        'Volume: Affiche le volume total (quantité × volume unitaire)');
                }
            });

            // Handle delete button click
            $(document).on('click', '.dropdown-item.delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var number = $(this).data('number');

                currentQuoteId = id;
                $('#deleteQuoteNumber').text(number);
                $('#deleteForm').attr('action', "{{ url('sales/quotations') }}/" + id);
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
                            table.ajax.reload();
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

            // Toast function
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
        });

        function formatCurrency(amount) {
            return new Intl.NumberFormat('fr-MA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount) + ' DH';
        }
    </script>
@endpush
