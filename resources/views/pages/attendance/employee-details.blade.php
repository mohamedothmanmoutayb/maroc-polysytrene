@extends('layouts.app')

@section('title', 'Détails Présences - ' . $employee->full_name)

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <div>
                            <h4 class="mb-4 mb-sm-0 card-title">
                                Détails des Présences - {{ $employee->full_name }}
                            </h4>
                            <small class="text-muted">{{ $employee->department ?? 'Aucun département' }} |
                                {{ $employee->position ?? 'Aucun poste' }}</small>
                        </div>
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
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('attendance.report') }}">
                                        Rapport
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Détails
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Info Card -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <img src="{{ $employee->photo ? asset('storage/' . $employee->photo) : asset('assets/images/profile/user-1.jpg') }}"
                                    class="rounded-circle" width="100" height="100"
                                    style="object-fit: cover; border: 3px solid white;">
                            </div>
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col">
                                        <small>Département</small>
                                        <h6>{{ $employee->department ?? '-' }}</h6>
                                    </div>
                                    <div class="col">
                                        <small>Poste</small>
                                        <h6>{{ $employee->position ?? '-' }}</h6>
                                    </div>
                                    <div class="col">
                                        <small>Date d'embauche</small>
                                        <h6>{{ $employee->hire_date->format('d/m/Y') }}</h6>
                                    </div>
                                    <div class="col">
                                        <small>Date de démission</small>
                                        <h6>
                                            @if ($employee->resignation_date)
                                                <span class="badge bg-danger">{{ $employee->resignation_date->format('d/m/Y') }}</span>
                                            @else
                                                <span class="badge bg-success">En activité</span>
                                            @endif
                                        </h6>
                                    </div>
                                    <div class="col">
                                        <small>Téléphone</small>
                                        <h6>{{ $employee->phone ?? '-' }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-check text-success fs-1 mb-2"></i>
                        <h3 class="mb-0">{{ $stats['present'] }}</h3>
                        <small class="text-muted">Jours Présents</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-times text-danger fs-1 mb-2"></i>
                        <h3 class="mb-0">{{ $stats['absent'] }}</h3>
                        <small class="text-muted">Jours Absents</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-clock text-warning fs-1 mb-2"></i>
                        <h3 class="mb-0">{{ $stats['late'] }}</h3>
                        <small class="text-muted">Retards</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-hourglass-half text-info fs-1 mb-2"></i>
                        <h3 class="mb-0">{{ number_format($stats['total_hours'], 2, ',', '.') }}</h3>
                        <small class="text-muted">Total Heures</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Détails des Absences</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Absences non justifiées</span>
                                <strong class="text-danger">{{ $stats['absent'] }}</strong>
                            </div>
                            <div class="progress mt-1" style="height: 5px;">
                                <div class="progress-bar bg-danger"
                                    style="width: {{ min(100, ($stats['absent'] / max(1, $stats['absent'] + $stats['present'])) * 100) }}%">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Retards</span>
                                <strong class="text-warning">{{ $stats['late'] }}</strong>
                            </div>
                            <div class="progress mt-1" style="height: 5px;">
                                <div class="progress-bar bg-warning"
                                    style="width: {{ min(100, ($stats['late'] / max(1, $stats['late'] + $stats['present'])) * 100) }}%">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Demi-journées</span>
                                <strong class="text-info">{{ $stats['half_day'] }}</strong>
                            </div>
                            <div class="progress mt-1" style="height: 5px;">
                                <div class="progress-bar bg-info"
                                    style="width: {{ min(100, ($stats['half_day'] / max(1, $stats['half_day'] + $stats['present'])) * 100) }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Congés et Arrêts</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Congés payés</span>
                                <strong>{{ $stats['paid_leave'] }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Arrêts maladie</span>
                                <strong>{{ $stats['sick_leave'] }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Congés sans solde</span>
                                <strong>{{ $stats['unpaid_leave'] }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Congés (total)</span>
                                <strong
                                    class="text-primary">{{ $stats['paid_leave'] + $stats['sick_leave'] + $stats['unpaid_leave'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Résumé</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Taux de présence</span>
                                <strong class="text-success">
                                    {{ $stats['present'] + $stats['present'] > 0 ? round(($stats['present'] / max(1, $stats['present'] + $stats['absent'] + $stats['late'] + $stats['half_day'])) * 100, 1) : 0 }}%
                                </strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Taux d'absentéisme</span>
                                <strong class="text-danger">
                                    {{ $stats['absent'] + $stats['late'] + $stats['half_day'] > 0 ? round((($stats['absent'] + $stats['late'] + $stats['half_day']) / max(1, $stats['present'] + $stats['absent'] + $stats['late'] + $stats['half_day'])) * 100, 1) : 0 }}%
                                </strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Jours ouvrés</span>
                                <strong>{{ $stats['present'] + $stats['absent'] + $stats['late'] + $stats['half_day'] + $stats['paid_leave'] + $stats['sick_leave'] + $stats['unpaid_leave'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Payment (Rémunération) -->
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Rémunération</h6>
                <div>
                    <select id="pay_year" class="form-control form-control-sm d-inline-block" style="width: auto;">
                        @for ($y = date('Y') - 2; $y <= date('Y'); $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endfor
                    </select>
                    <select id="pay_month" class="form-control form-control-sm d-inline-block" style="width: auto;">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
                            </option>
                        @endfor
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" id="refresh-payment">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <small class="text-muted">Prix/Heure</small>
                        <h5 id="pay-rate" class="mb-0">{{ number_format($payment['rate'], 2, ',', '.') }} DH</h5>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Total (Heures × Prix)</small>
                        <h5 id="pay-total" class="mb-0 text-primary">{{ number_format($payment['total_dh'], 2, ',', '.') }} DH</h5>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Avance</small>
                        <h5 id="pay-avance" class="mb-0 text-warning">{{ number_format($payment['avance'], 2, ',', '.') }} DH</h5>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Reste</small>
                        <h5 id="pay-reste" class="mb-0 text-success">{{ number_format($payment['reste'], 2, ',', '.') }} DH</h5>
                    </div>
                </div>
                <div class="text-muted small mt-3 text-center">
                    Total heures travaillées : <strong id="pay-hours">{{ number_format($payment['total_hours'], 2, ',', '.') }}</strong> h
                </div>
            </div>
        </div>

        <!-- Attendance History Table with Time Entries -->
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Historique des Présences</h6>
                <div>
                    <select id="history_year" class="form-control form-control-sm d-inline-block" style="width: auto;">
                        @for ($y = date('Y') - 2; $y <= date('Y'); $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endfor
                    </select>
                    <select id="history_month" class="form-control form-control-sm d-inline-block" style="width: auto;">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
                            </option>
                        @endfor
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" id="refresh-history">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="history-table">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Jour</th>
                                <th>Horaires travaillés</th>
                                <th>Pauses</th>
                                <th>Total Heures</th>
                                <th>Statut</th>
                                <th>Motif</th>
                            </tr>
                        </thead>
                        <tbody id="history-body">
                            <tr>
                                <td colspan="7" class="text-center">
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
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stats-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .progress {
            background-color: #e9ecef;
        }

        .time-badge {
            background-color: #0d6efd;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin: 2px;
            display: inline-block;
        }

        .break-badge {
            background-color: #ffc107;
            color: #212529;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin: 2px;
            display: inline-block;
        }

        .badge-present {
            background-color: #28a745;
        }

        .badge-absent {
            background-color: #dc3545;
        }

        .badge-late {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-half-day {
            background-color: #17a2b8;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        let historyTable;

        $(document).ready(function() {
            loadHistory();

            $('#refresh-history').click(function() {
                loadHistory();
            });

            $('#history_year, #history_month').change(function() {
                loadHistory();
            });

            $('#refresh-payment').click(function() {
                loadPayment();
            });

            $('#pay_year, #pay_month').change(function() {
                loadPayment();
            });
        });

        function fmtMoney(v) {
            return parseFloat(v || 0).toLocaleString('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function loadPayment() {
            let year = $('#pay_year').val();
            let month = $('#pay_month').val();
            let employeeId = {{ $employee->employee_id }};

            $.ajax({
                url: `/attendance/employee/${employeeId}/payment`,
                type: 'GET',
                data: {
                    year: year,
                    month: month
                },
                success: function(response) {
                    if (response.success) {
                        const d = response.data;
                        $('#pay-rate').text(fmtMoney(d.rate) + ' DH');
                        $('#pay-total').text(fmtMoney(d.total_dh) + ' DH');
                        $('#pay-avance').text(fmtMoney(d.avance) + ' DH');
                        $('#pay-reste').text(fmtMoney(d.reste) + ' DH');
                        $('#pay-hours').text(fmtMoney(d.total_hours));
                    } else {
                        showToast('error', 'Erreur lors du chargement');
                    }
                },
                error: function() {
                    showToast('error', 'Erreur lors du chargement de la rémunération');
                }
            });
        }

        function loadHistory() {
            let year = $('#history_year').val();
            let month = $('#history_month').val();
            let employeeId = {{ $employee->employee_id }};

            $.ajax({
                url: `/attendance/employee/${employeeId}/history`,
                type: 'GET',
                data: {
                    year: year,
                    month: month
                },
                success: function(response) {
                    if (response.success) {
                        displayHistory(response.data);
                    } else {
                        showToast('error', 'Erreur lors du chargement');
                    }
                },
                error: function() {
                    showToast('error', 'Erreur lors du chargement de l\'historique');
                }
            });
        }

        function displayHistory(attendances) {
            if (historyTable) {
                historyTable.destroy();
            }

            if (attendances.length === 0) {
                $('#history-body').html(
                    '<tr><td colspan="7" class="text-center">Aucune donnée pour cette période</td></tr>');
                return;
            }

            let html = '';
            attendances.forEach(function(att) {
                let statusClass = '';
                let statusText = '';

                switch (att.status) {
                    case 'present':
                        statusClass = 'bg-success';
                        statusText = 'Présent';
                        break;
                    case 'absent':
                        statusClass = 'bg-danger';
                        statusText = 'Absent';
                        break;
                    case 'late':
                        statusClass = 'bg-warning text-dark';
                        statusText = 'Retard';
                        break;
                    case 'half_day':
                        statusClass = 'bg-info';
                        statusText = 'Demi-journée';
                        break;
                    case 'holiday':
                        statusClass = 'bg-primary';
                        statusText = 'Congé';
                        break;
                    case 'sick_leave':
                        statusClass = 'bg-secondary';
                        statusText = 'Arrêt maladie';
                        break;
                    case 'paid_leave':
                        statusClass = 'bg-info';
                        statusText = 'Congé payé';
                        break;
                    case 'unpaid_leave':
                        statusClass = 'bg-dark';
                        statusText = 'Congé sans solde';
                        break;
                }

                html += `
                    <tr>
                        <td>${att.date}</td>
                        <td>${att.day_of_week}</td>
                        <td>${att.time_entries || '-'}</td>
                        <td>${att.break_entries || '-'}</td>
                        <td class="text-center"><strong>${att.hours_worked}</strong></td>
                        <td class="text-center"><span class="badge ${statusClass} px-3 py-2">${statusText}</span></td>
                        <td>${att.reason || '-'}</td>
                    </tr>
                `;
            });

            $('#history-body').html(html);

            // Initialize DataTable
            historyTable = $('#history-table').DataTable({ paging: false, lengthChange: false, 
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                },
                pageLength: 25,
                order: [
                    [0, 'desc']
                ],
                responsive: true
            });
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
            var bsToast = new bootstrap.Toast(toast[0]);
            bsToast.show();

            setTimeout(function() {
                toast.remove();
            }, 5000);
        }
    </script>
@endpush
