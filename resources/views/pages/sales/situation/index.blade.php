@extends('layouts.app')

@section('title', 'Situation des Clients')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Ventes</h6>
                        <h3>{{ $summary['total_orders'] }}</h3>
                        <small>Montant: {{ number_format($summary['total_amount'], 2, ',', '.') }} DH</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Payé</h6>
                        <h3>{{ number_format($summary['total_paid'], 2, ',', '.') }} DH</h3>
                        <small>{{ $summary['paid_orders'] }} ventes payées</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Impayé</h6>
                        <h3>{{ number_format($summary['total_unpaid'], 2, ',', '.') }} DH</h3>
                        <small>{{ $summary['partial_orders'] }} avances, {{ $summary['pending_orders'] }} impayés</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Taux de Recouvrement</h6>
                        @php
                            $recoveryRate =
                                $summary['total_amount'] > 0
                                    ? ($summary['total_paid'] / $summary['total_amount']) * 100
                                    : 0;
                        @endphp
                        <h3>{{ number_format($recoveryRate, 1) }}%</h3>
                        <small>{{ number_format($summary['total_paid'], 2, ',', '.') }} /
                            {{ number_format($summary['total_amount'], 2, ',', '.') }} DH</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Filtres</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Client</label>
                        <select class="form-control select2" id="client_id" name="client_id">
                            <option value="">Tous les clients</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->client_id }}">
                                    {{ $client->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Statut Paiement</label>
                        <select class="form-control" id="payment_status" name="payment_status">
                            <option value="">Tous</option>
                            <option value="pending">Non Payé</option>
                            <option value="partial">Avance</option>
                            <option value="paid">Payé</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date début</label>
                        <input type="date" class="form-control" id="date_from" name="date_from">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date fin</label>
                        <input type="date" class="form-control" id="date_to" name="date_to">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary me-2" id="applyFilters">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>
                        <button type="button" class="btn btn-success" id="exportData">
                            <i class="fas fa-file-excel"></i> Exporter
                        </button>
                        <button type="button" class="btn btn-secondary ms-2" id="resetFilters">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Liste des Ventes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="situationTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>N° Commande</th>
                                <th>Client</th>
                                <th>Montant Total</th>
                                <th>Payé</th>
                                <th>Reste</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Balance History Modal -->
    <div class="modal fade" id="balanceHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Historique du Solde</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="balanceHistoryContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        #situationTable_filter {
            display: none
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                language: 'fr',
                placeholder: 'Sélectionner...',
                allowClear: true
            });

            // Initialize DataTable
            let table = $('#situationTable').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('sales.situation.index') }}",
                    data: function(d) {
                        d.client_id = $('#client_id').val();
                        d.payment_status = $('#payment_status').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'order_date_formatted',
                        name: 'order_date'
                    },
                    {
                        data: 'order_number',
                        name: 'order_number'
                    },
                    {
                        data: 'client_name',
                        name: 'client.display_name'
                    },
                    {
                        data: 'final_amount',
                        name: 'final_amount'
                    },
                    {
                        data: 'paid_amount',
                        name: 'paid_amount'
                    },
                    {
                        data: 'rest_amount',
                        name: 'rest_amount',
                        orderable: false
                    },
                    {
                        data: 'payment_status_badge',
                        name: 'payment_status'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false
                    }
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json"
                },
                order: [
                    [1, 'desc']
                ]
            });

            // Apply filters
            $('#applyFilters').click(function() {
                table.draw();
            });

            // Reset filters
            $('#resetFilters').click(function() {
                $('#client_id').val('').trigger('change');
                $('#payment_status').val('');
                $('#date_from').val('');
                $('#date_to').val('');
                table.draw();
            });

            // Export data
            $('#exportData').click(function() {
                let params = {
                    client_id: $('#client_id').val(),
                    payment_status: $('#payment_status').val(),
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val()
                };

                let queryString = $.param(params);
                window.location.href = "{{ route('sales.situation.export') }}?" + queryString;
            });
            // View client balance history
            $(document).on('click', '.view-balance-history', function() {
                let clientId = $(this).data('client-id');

                $.ajax({
                    url: "{{ route('sales.situation.client', '') }}/" + clientId,
                    type: 'GET',
                    success: function(response) {
                        $('#balanceHistoryContent').html(response);
                        $('#balanceHistoryModal').modal('show');
                    }
                });
            });
        });
    </script>
@endpush
