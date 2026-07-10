@extends('layouts.app')

@section('title', 'Rapport Mensuel des Présences')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Rapport Mensuel des Présences</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('attendance.index') }}">
                                        Présences
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Rapport
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Année</label>
                                <select id="report_year" class="form-control">
                                    @for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                                            {{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mois</label>
                                <select id="report_month" class="form-control">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary" id="generate-report">
                                    <i class="fas fa-chart-line me-1"></i> Générer
                                </button>
                            </div>
                                            <div class="col-md-2">
                                <button type="button" class="btn btn-success" id="export-excel">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger" id="export-pdf">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header card-header-custom">
                <h5 class="card-title mb-0" style="color:white">
                    <i class="fas fa-chart-bar me-2"></i>Rapport des Présences
                    <span id="report-period" class="ms-2"></span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="report-table">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Employé</th>
                                <th width="10%">Département</th>
                                <th width="8%">Présent</th>
                                <th width="8%">Absent</th>
                                <th width="8%">Retard</th>
                                <th width="8%">Demi-Journée</th>
                                <th width="8%">Congés</th>
                                <th width="8%">Total Absences</th>
                                <th width="10%">Total Heures</th>
                                <th width="12%">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="report-body">
                            <tr>
                                <td colspan="11" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
    <style>
        .stats-card {
            transition: transform 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .badge-absent {
            background-color: #dc3545;
        }

        .badge-present {
            background-color: #28a745;
        }

        .badge-late {
            background-color: #ffc107;
            color: #212529;
        }

        .progress-bar-custom {
            height: 8px;
            border-radius: 4px;
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
        let reportTable = null;

        $(document).ready(function() {
            initTable();
            loadReport();

            $('#generate-report').click(function() {
                loadReport();
            });

            $('#export-excel').click(function() {
                if (reportTable) reportTable.button('.buttons-excel').trigger();
            });

            $('#export-pdf').click(function() {
                if (reportTable) reportTable.button('.buttons-pdf').trigger();
            });
        });

        function initTable() {
            reportTable = $('#report-table').DataTable({ paging: false, lengthChange: false, 
                processing: false,
                serverSide: false,
                data: [],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'full_name', name: 'full_name' },
                    { data: 'department', name: 'department' },
                    { data: 'present', name: 'present', className: 'text-center' },
                    { data: 'absent', name: 'absent', className: 'text-center' },
                    { data: 'late', name: 'late', className: 'text-center' },
                    { data: 'half_day', name: 'half_day', className: 'text-center' },
                    { data: 'leaves', name: 'leaves', className: 'text-center' },
                    { data: 'total_absent', name: 'total_absent', className: 'text-center' },
                    { data: 'total_hours', name: 'total_hours', className: 'text-center' },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data) { return data || ''; }
                    }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm d-none buttons-excel',
                        title: 'Rapport_Presences'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm d-none buttons-pdf',
                        title: 'Rapport_Presences',
                        orientation: 'landscape',
                        pageSize: 'A3'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimer',
                        className: 'btn btn-secondary btn-sm d-none buttons-print'
                    }
                ],
                pageLength: 25,
                responsive: true,
                order: [[8, 'desc']]
            });
        }

        function loadReport() {
            let year  = $('#report_year').val();
            let month = $('#report_month').val();

            $('#generate-report').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Chargement...');

            $.ajax({
                url: '{{ route('attendance.report') }}',
                type: 'GET',
                data: { year: year, month: month },
                success: function(response) {
                    if (response.success) {
                        reportTable.clear().rows.add(response.data).draw();
                        $('#report-period').text('(' + getMonthName(month) + ' ' + year + ')');
                    } else {
                        showToast('error', 'Erreur lors du chargement du rapport');
                    }
                },
                error: function(xhr) {
                    console.error('Report error:', xhr.responseText);
                    showToast('error', 'Erreur lors du chargement du rapport');
                },
                complete: function() {
                    $('#generate-report').prop('disabled', false).html('<i class="fas fa-chart-line me-1"></i> Générer');
                }
            });
        }

        function getMonthName(month) {
            const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            return months[parseInt(month) - 1];
        }

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
            new bootstrap.Toast(toast[0]).show();
            setTimeout(function() { toast.remove(); }, 5000);
        }
    </script>
@endpush
