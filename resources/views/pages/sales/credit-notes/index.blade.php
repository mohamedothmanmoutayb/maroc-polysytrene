{{-- resources/views/pages/sales/credit-notes/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestion des Avoirs')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Avoirs</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Avoirs
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
                                <span class="text-muted">Total Avoirs</span>
                                <h3 class="mb-0" id="totalCreditNotes">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-undo-alt fs-1 text-primary"></i>
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
                                <span class="text-muted">En Attente</span>
                                <h3 class="mb-0" id="pendingApproval">0</h3>
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
                                <span class="text-muted">Traitées</span>
                                <h3 class="mb-0" id="processedCreditNotes">0</h3>
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
                                <span class="text-muted">Aujourd'hui</span>
                                <h3 class="mb-0" id="todayCreditNotes">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-day fs-1 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount Cards -->
        <div class="row mb-4">
            <div class="col-xl-6 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted">Montant Total Traité</span>
                                <h2 class="mb-0" id="totalAmount">0 DH</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-money-bill-wave fs-1 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted">Montant En Attente</span>
                                <h2 class="mb-0" id="pendingAmount">0 DH</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-hourglass-half fs-1 text-warning"></i>
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
                            <i class="fas fa-undo-alt me-2"></i>Liste des Avoirs
                        </h5>
                        <div>
                            @can('create_credit_notes')
                            <a href="{{ route('credit-notes.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Nouvel Avoir
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Date Filter -->
                        <div class="row mb-3 g-2 align-items-end">
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
                        <div class="table-responsive">
                            <table id="credit-notes-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>N° Avoir</th>
                                        <th>Client</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded by DataTables -->
                                </tbody>
                                <tfoot>
                                    <tr class="table-info fw-bold">
                                        <td colspan="4" class="text-end">Total Montant :</td>
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

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer l'avoir : <strong id="deleteCreditNoteNumber"></strong> ?</p>
                    <p class="text-danger">Cette action est irréversible !</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Approuver l'avoir</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir approuver l'avoir : <strong id="approveCreditNoteNumber"></strong> ?</p>
                    <p class="text-info">L'avoir pourra ensuite être traité pour mise à jour du stock et du compte client.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-success" id="confirmApproveBtn">Approuver</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Rejeter l'avoir</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir rejeter l'avoir : <strong id="rejectCreditNoteNumber"></strong> ?</p>
                    <p class="text-warning">Cette action est définitive.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectBtn">Rejeter</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Modal -->
    <div class="modal fade" id="processModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Traiter l'avoir</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir traiter l'avoir : <strong id="processCreditNoteNumber"></strong> ?</p>
                    <p>Cette action va :</p>
                    <ul>
                        <li>Mettre à jour le stock (ajouter les quantités retournées)</li>
                        <li>Créditer le compte client du montant total</li>
                    </ul>
                    <p class="text-success">Cette action est définitive et ne peut pas être annulée.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="confirmProcessBtn">Traiter</button>
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
        .fa-money-bill-wave {
            font-size: 38px !important;
        }

        .badge-badge-secondary {
            background-color: #6c757d;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }

        .badge-badge-warning {
            background-color: #ffc107;
            color: #212529;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }

        .badge-badge-info {
            background-color: #17a2b8;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }

        .badge-badge-danger {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }

        .badge-badge-success {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }

        .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
        }

        .dropdown-menu {
            min-width: 200px;
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
            let currentActionId = null;
            let currentActionNumber = null;

            // Set default date filter to today
            const today = new Date().toISOString().split('T')[0];
            $('#dateFrom').val(today);
            $('#dateTo').val(today);

            // Initialize DataTable
            var table = $('#credit-notes-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('credit-notes.index') }}",
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
                        data: 'credit_note_number',
                        name: 'credit_note_number'
                    },
                    {
                        data: 'client_name',
                        name: 'client.display_name'
                    },
                    {
                        data: 'credit_note_date',
                        name: 'credit_note_date',
                        className: 'text-center'
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
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
                ]
            });

            // Filter buttons
            $('#applyFilter').click(function() {
                table.ajax.reload();
            });

            $('#resetFilter').click(function() {
                const today = new Date().toISOString().split('T')[0];
                $('#dateFrom').val(today);
                $('#dateTo').val(today);
                table.ajax.reload();
            });

            // Load statistics
            loadStatistics();

            function loadStatistics() {
                $.ajax({
                    url: "{{ route('credit-notes.statistics') }}",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#totalCreditNotes').text(response.data.total);
                            $('#pendingApproval').text(response.data.pending_approval);
                            $('#processedCreditNotes').text(response.data.total_amount > 0 ?
                                response.data.total_amount : 0);
                            $('#todayCreditNotes').text(response.data.today);
                            $('#totalAmount').text(formatCurrency(response.data.total_amount));
                            $('#pendingAmount').text(formatCurrency(response.data.pending_amount));
                        }
                    },
                    error: function() {
                        $('#totalCreditNotes').text('0');
                        $('#pendingApproval').text('0');
                        $('#processedCreditNotes').text('0');
                        $('#todayCreditNotes').text('0');
                        $('#totalAmount').text('0 DH');
                        $('#pendingAmount').text('0 DH');
                    }
                });
            }

            // Delete button click - using event delegation
            $(document).on('click', '.delete-credit-note', function(e) {
                e.preventDefault();
                currentActionId = $(this).data('id');
                currentActionNumber = $(this).data('number');
                $('#deleteCreditNoteNumber').text(currentActionNumber);
                $('#deleteModal').modal('show');
            });

            // Confirm delete
            $('#confirmDeleteBtn').click(function() {
                if (!currentActionId) return;

                $.ajax({
                    url: "{{ url('credit-notes') }}/" + currentActionId,
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
                        var response = xhr.responseJSON;
                        showToast('error', response?.message ||
                        'Erreur lors de la suppression');
                    }
                });
            });

            // Approve button click
            $(document).on('click', '.approve-credit-note', function(e) {
                e.preventDefault();
                currentActionId = $(this).data('id');
                currentActionNumber = $(this).data('number');
                $('#approveCreditNoteNumber').text(currentActionNumber);
                $('#approveModal').modal('show');
            });

            // Confirm approve
            $('#confirmApproveBtn').click(function() {
                if (!currentActionId) return;

                $.ajax({
                    url: "{{ url('credit-notes') }}/" + currentActionId + "/approve",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#approveModal').modal('hide');
                            table.ajax.reload();
                            loadStatistics();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors de l\'approbation');
                    }
                });
            });

            // Reject button click
            $(document).on('click', '.reject-credit-note', function(e) {
                e.preventDefault();
                currentActionId = $(this).data('id');
                currentActionNumber = $(this).data('number');
                $('#rejectCreditNoteNumber').text(currentActionNumber);
                $('#rejectModal').modal('show');
            });

            // Confirm reject
            $('#confirmRejectBtn').click(function() {
                if (!currentActionId) return;

                $.ajax({
                    url: "{{ url('credit-notes') }}/" + currentActionId + "/reject",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#rejectModal').modal('hide');
                            table.ajax.reload();
                            loadStatistics();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message || 'Erreur lors du rejet');
                    }
                });
            });

            // Process button click
            $(document).on('click', '.process-credit-note', function(e) {
                e.preventDefault();
                currentActionId = $(this).data('id');
                currentActionNumber = $(this).data('number');
                $('#processCreditNoteNumber').text(currentActionNumber);
                $('#processModal').modal('show');
            });

            // Confirm process
            $('#confirmProcessBtn').click(function() {
                if (!currentActionId) return;

                $.ajax({
                    url: "{{ url('credit-notes') }}/" + currentActionId + "/process",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#processModal').modal('hide');
                            table.ajax.reload();
                            loadStatistics();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors du traitement');
                    }
                });
            });

            // PDF download
            $(document).on('click', '.pdf-credit-note', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                let url = "{{ url('credit-notes') }}/" + id + "/pdf";
                window.open(url, '_blank');
            });

            // Auto-refresh statistics every 30 seconds
            setInterval(loadStatistics, 30000);
        });

        function formatCurrency(amount) {
            return new Intl.NumberFormat('fr-MA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount) + ' DH';
        }

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
    </script>
@endpush
