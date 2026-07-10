@extends('layouts.app')

@section('title', 'Gestion des Traites')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Traites</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Traites
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
            <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                <div class="card bg-primary bg-opacity-10 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-semibold mb-2">{{ $totalTraites }}</h4>
                                <p class="fs-3 mb-0">Total Traites</p>
                            </div>
                            <div class="text-primary">
                                <iconify-icon icon="solar:bill-list-outline" class="fs-1"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                <div class="card bg-success bg-opacity-10 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-semibold mb-2">{{ number_format($totalAmount, 2, ',', '.') }} DH</h4>
                                <p class="fs-3 mb-0">Montant Total</p>
                            </div>
                            <div class="text-success">
                                <iconify-icon icon="solar:dollar-outline" class="fs-1"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                <div class="card bg-warning bg-opacity-10 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-semibold mb-2">{{ number_format($pendingAmount, 2, ',', '.') }} DH</h4>
                                <p class="fs-3 mb-0">En Attente</p>
                            </div>
                            <div class="text-warning">
                                <iconify-icon icon="solar:clock-circle-outline" class="fs-1"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                <div class="card bg-danger bg-opacity-10 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-semibold mb-2">{{ $overdueCount }}</h4>
                                <p class="fs-3 mb-0">En Retard</p>
                            </div>
                            <div class="text-danger">
                                <iconify-icon icon="solar:alarm-outline" class="fs-1"></iconify-icon>
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
                            <i class="fas fa-file-invoice me-2"></i>Liste des Traites
                        </h5>
                        @can('create_traites')
                        <a href="{{ route('traites.create') }}" class="btn btn-light">
                            <i class="fas fa-plus me-1"></i> Nouvelle Traite
                        </a>
                        @endcan
                    </div>
                    <div class="card-body">
                        <!-- Tabs navigation -->
                        <ul class="nav nav-tabs mb-4" id="traiteTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all"
                                    type="button" role="tab" aria-controls="all" aria-selected="true">
                                    <i class="fas fa-list me-2"></i>Tous ({{ $totalTraites }})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                                    type="button" role="tab" aria-controls="pending" aria-selected="false">
                                    <i class="fas fa-clock me-2 text-warning"></i>En Attente
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="paid-tab" data-bs-toggle="tab" data-bs-target="#paid"
                                    type="button" role="tab" aria-controls="paid" aria-selected="false">
                                    <i class="fas fa-check-circle me-2 text-success"></i>Payées
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue"
                                    type="button" role="tab" aria-controls="overdue" aria-selected="false">
                                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>En Retard
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="bounced-tab" data-bs-toggle="tab" data-bs-target="#bounced"
                                    type="button" role="tab" aria-controls="bounced" aria-selected="false">
                                    <i class="fas fa-times-circle me-2 text-danger"></i>Rejetées
                                </button>
                            </li>
                        </ul>

                        <!-- Tab content -->
                        <div class="tab-content" id="traiteTabsContent">
                            <div class="tab-pane fade show active" id="all" role="tabpanel"
                                aria-labelledby="all-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="traites-table-all" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>N° Traite</th>
                                                <th>Client</th>
                                                <th>Vente</th>
                                                <th>Montant</th>
                                                <th>Banque</th>
                                                <th>Dates</th>
                                                <th>Statut</th>
                                                <th>Document</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="traites-table-pending" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>N° Traite</th>
                                                <th>Client</th>
                                                <th>Vente</th>
                                                <th>Montant</th>
                                                <th>Banque</th>
                                                <th>Dates</th>
                                                <th>Statut</th>
                                                <th>Document</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="paid" role="tabpanel" aria-labelledby="paid-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="traites-table-paid" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>N° Traite</th>
                                                <th>Client</th>
                                                <th>Vente</th>
                                                <th>Montant</th>
                                                <th>Banque</th>
                                                <th>Dates</th>
                                                <th>Statut</th>
                                                <th>Document</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="overdue" role="tabpanel" aria-labelledby="overdue-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="traites-table-overdue" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>N° Traite</th>
                                                <th>Client</th>
                                                <th>Vente</th>
                                                <th>Montant</th>
                                                <th>Banque</th>
                                                <th>Dates</th>
                                                <th>Statut</th>
                                                <th>Document</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="bounced" role="tabpanel" aria-labelledby="bounced-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="traites-table-bounced" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>N° Traite</th>
                                                <th>Client</th>
                                                <th>Vente</th>
                                                <th>Montant</th>
                                                <th>Banque</th>
                                                <th>Dates</th>
                                                <th>Statut</th>
                                                <th>Document</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <style>
        .nav-tabs .nav-link {
            font-weight: 500;
            color: #495057;
        }

        .nav-tabs .nav-link.active {
            background-color: #1268c5;
            color: #fff;
            border-color: #1268c5;
        }

        .nav-tabs .nav-link i {
            font-size: 1.1rem;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Common DataTable configuration
            var commonConfig = {
                processing: true,
                serverSide: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
                },
                order: [
                    [1, 'desc']
                ],
                pageLength: 25,
                responsive: true
            };

            // All Traites Table
            var tableAll = $('#traites-table-all').DataTable({ paging: false, lengthChange: false, 
                ...commonConfig,
                ajax: {
                    url: "{{ route('traites.index') }}",
                    data: function(d) {
                        d.status = 'all';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'traite_number_formatted',
                        name: 'traite_number'
                    },
                    {
                        data: 'client_info',
                        name: 'client_id'
                    },
                    {
                        data: 'order_info',
                        name: 'order_id'
                    },
                    {
                        data: 'amount_formatted',
                        name: 'amount'
                    },
                    {
                        data: 'bank_info',
                        name: 'bank_name'
                    },
                    {
                        data: 'dates',
                        name: 'due_date'
                    },
                    {
                        data: 'status_info',
                        name: 'status'
                    },
                    {
                        data: 'document',
                        name: 'document_path'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Pending Traites Table
            var tablePending = $('#traites-table-pending').DataTable({ paging: false, lengthChange: false, 
                ...commonConfig,
                ajax: {
                    url: "{{ route('traites.index') }}",
                    data: function(d) {
                        d.status = 'pending';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'traite_number_formatted',
                        name: 'traite_number'
                    },
                    {
                        data: 'client_info',
                        name: 'client_id'
                    },
                    {
                        data: 'order_info',
                        name: 'order_id'
                    },
                    {
                        data: 'amount_formatted',
                        name: 'amount'
                    },
                    {
                        data: 'bank_info',
                        name: 'bank_name'
                    },
                    {
                        data: 'dates',
                        name: 'due_date'
                    },
                    {
                        data: 'status_info',
                        name: 'status'
                    },
                    {
                        data: 'document',
                        name: 'document_path'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Paid Traites Table
            var tablePaid = $('#traites-table-paid').DataTable({ paging: false, lengthChange: false, 
                ...commonConfig,
                ajax: {
                    url: "{{ route('traites.index') }}",
                    data: function(d) {
                        d.status = 'paid';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'traite_number_formatted',
                        name: 'traite_number'
                    },
                    {
                        data: 'client_info',
                        name: 'client_id'
                    },
                    {
                        data: 'order_info',
                        name: 'order_id'
                    },
                    {
                        data: 'amount_formatted',
                        name: 'amount'
                    },
                    {
                        data: 'bank_info',
                        name: 'bank_name'
                    },
                    {
                        data: 'dates',
                        name: 'due_date'
                    },
                    {
                        data: 'status_info',
                        name: 'status'
                    },
                    {
                        data: 'document',
                        name: 'document_path'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Overdue Traites Table
            var tableOverdue = $('#traites-table-overdue').DataTable({ paging: false, lengthChange: false, 
                ...commonConfig,
                ajax: {
                    url: "{{ route('traites.index') }}",
                    data: function(d) {
                        d.status = 'overdue';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'traite_number_formatted',
                        name: 'traite_number'
                    },
                    {
                        data: 'client_info',
                        name: 'client_id'
                    },
                    {
                        data: 'order_info',
                        name: 'order_id'
                    },
                    {
                        data: 'amount_formatted',
                        name: 'amount'
                    },
                    {
                        data: 'bank_info',
                        name: 'bank_name'
                    },
                    {
                        data: 'dates',
                        name: 'due_date'
                    },
                    {
                        data: 'status_info',
                        name: 'status'
                    },
                    {
                        data: 'document',
                        name: 'document_path'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Bounced Traites Table
            var tableBounced = $('#traites-table-bounced').DataTable({ paging: false, lengthChange: false, 
                ...commonConfig,
                ajax: {
                    url: "{{ route('traites.index') }}",
                    data: function(d) {
                        d.status = 'bounced';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'traite_number_formatted',
                        name: 'traite_number'
                    },
                    {
                        data: 'client_info',
                        name: 'client_id'
                    },
                    {
                        data: 'order_info',
                        name: 'order_id'
                    },
                    {
                        data: 'amount_formatted',
                        name: 'amount'
                    },
                    {
                        data: 'bank_info',
                        name: 'bank_name'
                    },
                    {
                        data: 'dates',
                        name: 'due_date'
                    },
                    {
                        data: 'status_info',
                        name: 'status'
                    },
                    {
                        data: 'document',
                        name: 'document_path'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Handle delete
            $(document).on('click', '.delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var number = $(this).data('number');

                Swal.fire({
                    title: 'Êtes-vous sûr?',
                    text: "Vous allez supprimer la traite " + number,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, supprimer!',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('traites') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    showToast('success', response.message);
                                    tableAll.ajax.reload();
                                    tablePending.ajax.reload();
                                    tablePaid.ajax.reload();
                                    tableOverdue.ajax.reload();
                                    tableBounced.ajax.reload();
                                } else {
                                    showToast('error', response.message);
                                }
                            },
                            error: function(xhr) {
                                showToast('error', xhr.responseJSON?.message ||
                                    'Une erreur est survenue');
                            }
                        });
                    }
                });
            });

            // Handle mark as paid
            $(document).on('click', '.mark-paid', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var number = $(this).data('number');

                Swal.fire({
                    title: 'Confirmer le paiement',
                    text: "Voulez-vous marquer la traite " + number + " comme payée?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, marquer payée!',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('traites') }}/" + id + "/mark-paid",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    showToast('success', response.message);
                                    tableAll.ajax.reload();
                                    tablePending.ajax.reload();
                                    tablePaid.ajax.reload();
                                    tableOverdue.ajax.reload();
                                    tableBounced.ajax.reload();
                                } else {
                                    showToast('error', response.message);
                                }
                            },
                            error: function(xhr) {
                                showToast('error', xhr.responseJSON?.message ||
                                    'Une erreur est survenue');
                            }
                        });
                    }
                });
            });

            // Handle mark as overdue
            $(document).on('click', '.mark-overdue', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var number = $(this).data('number');

                Swal.fire({
                    title: 'Confirmer le retard',
                    text: "Voulez-vous marquer la traite " + number + " comme en retard?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, marquer en retard!',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('traites') }}/" + id + "/mark-overdue",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    showToast('success', response.message);
                                    tableAll.ajax.reload();
                                    tablePending.ajax.reload();
                                    tablePaid.ajax.reload();
                                    tableOverdue.ajax.reload();
                                    tableBounced.ajax.reload();
                                } else {
                                    showToast('error', response.message);
                                }
                            },
                            error: function(xhr) {
                                showToast('error', xhr.responseJSON?.message ||
                                    'Une erreur est survenue');
                            }
                        });
                    }
                });
            });

            // Handle mark as bounced
            $(document).on('click', '.mark-bounced', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var number = $(this).data('number');

                Swal.fire({
                    title: 'Confirmer le rejet',
                    text: "Voulez-vous marquer la traite " + number + " comme rejetée?",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, marquer rejetée!',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('traites') }}/" + id + "/mark-bounced",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    showToast('success', response.message);
                                    tableAll.ajax.reload();
                                    tablePending.ajax.reload();
                                    tablePaid.ajax.reload();
                                    tableOverdue.ajax.reload();
                                    tableBounced.ajax.reload();
                                } else {
                                    showToast('error', response.message);
                                }
                            },
                            error: function(xhr) {
                                showToast('error', xhr.responseJSON?.message ||
                                    'Une erreur est survenue');
                            }
                        });
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
                }, 5000);
            }
        });
    </script>
@endpush
