@extends('layouts.app')

@section('title', 'Gestion des Dépenses')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Dépenses</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Dépenses
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
                <div class="card bg-primary-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary rounded-circle p-3 text-white">
                                <i class="fas fa-coins fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold">{{ number_format($totalExpenses, 2, ',', '.') }} DH</h2>
                                <span class="text-muted">Total Dépenses</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning rounded-circle p-3 text-white">
                                <i class="fas fa-clock fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold">{{ $pendingExpenses }}</h2>
                                <span class="text-muted">En attente</span>
                                <small class="d-block">{{ number_format($pendingAmount, 2, ',', '.') }} DH</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success rounded-circle p-3 text-white">
                                <i class="fas fa-check-circle fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold">{{ $approvedExpenses }}</h2>
                                <span class="text-muted">Approuvées</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-info rounded-circle p-3 text-white">
                                <i class="fas fa-tags fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold">{{ $categories->count() }}</h2>
                                <span class="text-muted">Catégories</span>
                            </div>
                        </div>
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
                            <label class="form-label">Catégorie</label>
                            <select class="form-control select2" id="filterCategoryId">
                                <option value="">Toutes les catégories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->category_id }}">
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Paiement</label>
                            <select class="form-control" id="filterPaymentMethod">
                                <option value="">Tous</option>
                                <option value="cash">Espèces</option>
                                <option value="check">Chèque</option>
                                <option value="transfer">Virement</option>
                                <option value="credit_card">Carte</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Statut</label>
                            <select class="form-control" id="filterStatus">
                                <option value="">Tous</option>
                                <option value="pending">En attente</option>
                                <option value="approved">Approuvé</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Période</label>
                            <input type="text" class="form-control date-range-picker" id="filterDateRange"
                                placeholder="Sélectionner une période">
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

        <!-- Main Card -->
        <div class="card">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0" style="color:white">
                    <i class="fas fa-coins me-2"></i>Liste des Dépenses
                </h5>
                <div>
                    <a href="{{ route('expense-categories.index') }}" class="btn btn-info me-2">
                        <i class="fas fa-tags me-1"></i> Catégories
                    </a>
                    @can('create_expenses')
                    <a href="{{ route('expenses.create') }}" class="btn btn-light">
                        <i class="fas fa-plus-circle me-1"></i> Nouvelle Dépense
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="expenses-table" class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>N° Dépense</th>
                                <th>Date</th>
                                <th>Catégorie</th>
                                <th>Montant</th>
                                <th>Paiement</th>
                                <th>Bénéficiaire</th>
                                <th>Statut</th>
                                <th>Enregistré par</th>
                                <th>Approuvé par</th>
                                <th width="80px">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
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

            // Initialize Date Range Picker with quick presets
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
            });

            var table = $('#expenses-table').DataTable({ paging: false, lengthChange: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('expenses.index') }}",
                    data: function(d) {
                        d.category_id = $('#filterCategoryId').val();
                        d.payment_method = $('#filterPaymentMethod').val();
                        d.status = $('#filterStatus').val();
                        d.date_range = $('#filterDateRange').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'expense_number',
                        name: 'expense_number'
                    },
                    {
                        data: 'expense_date',
                        name: 'expense_date'
                    },
                    {
                        data: 'category',
                        name: 'category.category_name'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'payment_method',
                        name: 'payment_method'
                    },
                    {
                        data: 'paid_to',
                        name: 'paid_to'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'recorded_by',
                        name: 'recorder.username'
                    },
                    {
                        data: 'approved_by',
                        name: 'approver.username',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [2, 'desc']
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                }
            });

            $('#filterForm').submit(function(e) {
                e.preventDefault();
                table.draw();
            });

            // Reset filters
            $('#resetFilters').click(function() {
                $('#filterCategoryId').val('').trigger('change');
                $('#filterPaymentMethod').val('');
                $('#filterStatus').val('');
                $('#filterDateRange').val('');
                table.draw();
            });

            // Delete expense
            $(document).on('click', '.delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var number = $(this).data('number');

                if (confirm('Êtes-vous sûr de vouloir supprimer la dépense ' + number + ' ?')) {
                    $.ajax({
                        url: "{{ route('expenses.destroy', '') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                table.ajax.reload();
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

            // Approve expense
            $(document).on('click', '.approve', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var number = $(this).data('number');

                if (confirm('Approuver la dépense ' + number + ' ?')) {
                    $.ajax({
                        url: "{{ route('expenses.approve', '') }}/" + id,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                table.ajax.reload();
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
