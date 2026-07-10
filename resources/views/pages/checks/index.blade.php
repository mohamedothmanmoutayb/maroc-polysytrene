@extends('layouts.app')

@section('title', 'Gestion des Chèques')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Chèques</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Chèques
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
                                <h4 class="fw-semibold mb-2">{{ $totalChecks }}</h4>
                                <p class="fs-3 mb-0">Total Chèques</p>
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
                <div class="card bg-info bg-opacity-10 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-semibold mb-2">{{ number_format($availableAmount, 2, ',', '.') }} DH</h4>
                                <p class="fs-3 mb-0">Montant Disponible</p>
                            </div>
                            <div class="text-info">
                                <iconify-icon icon="solar:wallet-money-outline" class="fs-1"></iconify-icon>
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
                                <h4 class="fw-semibold mb-2">{{ $depositedChecks }}</h4>
                                <p class="fs-3 mb-0">En Dépôt</p>
                            </div>
                            <div class="text-warning">
                                <iconify-icon icon="solar:clock-circle-outline" class="fs-1"></iconify-icon>
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
                            <i class="fas fa-money-check me-2"></i>Liste des Chèques
                        </h5>
                        @can('create_checks')
                        <a href="{{ route('checks.create') }}" class="btn btn-light">
                            <i class="fas fa-plus me-1"></i> Nouveau Chèque
                        </a>
                        @endcan
                    </div>
                    <div class="card-body">
                        <!-- Tabs navigation -->
                        <ul class="nav nav-tabs mb-4" id="checkTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all"
                                    type="button" role="tab" aria-controls="all" aria-selected="true">
                                    <i class="fas fa-list me-2"></i>Tous ({{ $totalChecks }})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="entreprise-tab" data-bs-toggle="tab"
                                    data-bs-target="#entreprise" type="button" role="tab" aria-controls="entreprise"
                                    aria-selected="false">
                                    <i class="fas fa-building me-2 text-primary"></i>Chèques Entreprise
                                    ({{ $enterpriseChecks }})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="client-tab" data-bs-toggle="tab" data-bs-target="#client"
                                    type="button" role="tab" aria-controls="client" aria-selected="false">
                                    <i class="fas fa-user me-2 text-success"></i>Chèques Client ({{ $clientChecks }})
                                </button>
                            </li>
                        </ul>

                        <!-- Tab content -->
                        <div class="tab-content" id="checkTabsContent">
                            <div class="tab-pane fade show active" id="all" role="tabpanel"
                                aria-labelledby="all-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="checks-table-all">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Numéro</th>
                                                <th>Tireur</th>
                                                <th>Banque</th>
                                                <th>Montant</th>
                                                <th>Disponible</th>
                                                <th>Dates</th>
                                                <th>Encaissement</th>
                                                <th>Type</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="entreprise" role="tabpanel" aria-labelledby="entreprise-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="checks-table-entreprise" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Numéro</th>
                                                <th>Tireur</th>
                                                <th>Banque</th>
                                                <th>Montant</th>
                                                <th>Disponible</th>
                                                <th>Dates</th>
                                                <th>Encaissement</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="client" role="tabpanel" aria-labelledby="client-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="checks-table-client" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Numéro</th>
                                                <th>Tireur</th>
                                                <th>Banque</th>
                                                <th>Montant</th>
                                                <th>Disponible</th>
                                                <th>Dates</th>
                                                <th>Encaissement</th>
                                                <th>Statut</th>
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
            color: #1268c5;
            font-weight: 600;
            color: #f3f3f3
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
                ]
            };

            // All Checks Table
            var tableAll = $('#checks-table-all').DataTable({ paging: false, lengthChange: false,
                ...commonConfig,
                // "Tous" has 11 columns (0-10): includes check_type_badge at index 8
                columnDefs: [{
                        orderable: false,
                        targets: [6, 7, 8, 9, 10]
                    } // Disable ordering for specific columns
                ],
                ajax: {
                    url: "{{ route('checks.index') }}",
                    data: function(d) {
                        d.type = 'all';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'check_number_formatted',
                        name: 'check_number'
                    },
                    {
                        data: 'account_holder',
                        name: 'account_holder'
                    },
                    {
                        data: 'bank_name',
                        name: 'bank_name'
                    },
                    {
                        data: 'amount_formatted',
                        name: 'amount'
                    },
                    {
                        data: 'available_amount',
                        name: 'available_amount'
                    },
                    {
                        data: 'dates',
                        name: 'issue_date'
                    },
                    {
                        data: 'clearing_info',
                        name: 'clearing_info'
                    },
                    {
                        data: 'check_type_badge',
                        name: 'check_type'
                    },
                    {
                        data: 'status_badge',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ]
            });

            // Enterprise Checks Table
            var tableEntreprise = $('#checks-table-entreprise').DataTable({ paging: false, lengthChange: false,
                ...commonConfig,
                // "Entreprise" has 10 columns (0-9): no check_type_badge column
                columnDefs: [{
                        orderable: false,
                        targets: [6, 7, 8, 9]
                    } // Disable ordering for specific columns
                ],
                ajax: {
                    url: "{{ route('checks.index') }}",
                    data: function(d) {
                        d.type = 'entreprise';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'check_number_formatted',
                        name: 'check_number'
                    },
                    {
                        data: 'account_holder',
                        name: 'account_holder'
                    },
                    {
                        data: 'bank_name',
                        name: 'bank_name'
                    },
                    {
                        data: 'amount_formatted',
                        name: 'amount'
                    },
                    {
                        data: 'available_amount',
                        name: 'available_amount'
                    },
                    {
                        data: 'dates',
                        name: 'issue_date'
                    },
                    {
                        data: 'clearing_info',
                        name: 'clearing_info'
                    },

                    {
                        data: 'status_badge',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ]
            });

            // Client Checks Table
            var tableClient = $('#checks-table-client').DataTable({ paging: false, lengthChange: false,
                ...commonConfig,
                // "Client" has 10 columns (0-9): no check_type_badge column
                columnDefs: [{
                        orderable: false,
                        targets: [6, 7, 8, 9]
                    } // Disable ordering for specific columns
                ],
                ajax: {
                    url: "{{ route('checks.index') }}",
                    data: function(d) {
                        d.type = 'client';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'check_number_formatted',
                        name: 'check_number'
                    },
                    {
                        data: 'account_holder',
                        name: 'account_holder'
                    },
                    {
                        data: 'bank_name',
                        name: 'bank_name'
                    },
                    {
                        data: 'amount_formatted',
                        name: 'amount'
                    },
                    {
                        data: 'available_amount',
                        name: 'available_amount'
                    },
                    {
                        data: 'dates',
                        name: 'issue_date'
                    },
                    {
                        data: 'clearing_info',
                        name: 'clearing_info'
                    },

                    {
                        data: 'status_badge',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action'
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
                    text: "Vous allez supprimer le chèque " + number,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, supprimer!',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('checks') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    showToast('success', response.message);
                                    tableAll.ajax.reload();
                                    tableEntreprise.ajax.reload();
                                    tableClient.ajax.reload();
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

            // Handle mark as paid (clear check)
            $(document).on('click', '.clear-check, .clear-check-btn', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var number = $(this).data('number');

                Swal.fire({
                    title: 'Confirmer le paiement',
                    text: "Voulez-vous marquer le chèque " + number + " comme payé?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, marquer payé!',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('checks') }}/" + id + "/clear",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    showToast('success', response.message);
                                    tableAll.ajax.reload();
                                    tableEntreprise.ajax.reload();
                                    tableClient.ajax.reload();
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

            // Handle mark as deposited
            $(document).on('click', '.mark-deposited', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var number = $(this).data('number');

                Swal.fire({
                    title: 'Confirmer le dépôt',
                    text: "Voulez-vous marquer le chèque " + number + " comme déposé?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#17a2b8',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, marquer déposé!',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('checks') }}/" + id + "/mark-deposited",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    showToast('success', response.message);
                                    tableAll.ajax.reload();
                                    tableEntreprise.ajax.reload();
                                    tableClient.ajax.reload();
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
                    text: "Voulez-vous marquer le chèque " + number + " comme rebondi?",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, marquer rebondi!',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('checks') }}/" + id + "/mark-bounced",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    showToast('success', response.message);
                                    tableAll.ajax.reload();
                                    tableEntreprise.ajax.reload();
                                    tableClient.ajax.reload();
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
