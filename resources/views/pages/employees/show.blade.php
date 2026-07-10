@extends('layouts.app')

@section('title', 'Détails Employé')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails Employé</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('employees.index') }}">
                                        Employés
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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-user me-2"></i>{{ $employee->full_name }}
                        </h5>
                        <div>
                            @if (!$employee->user)
                                <button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal"
                                    data-bs-target="#createUserModal">
                                    <i class="fas fa-user-plus me-1"></i> Créer compte
                                </button>
                            @endif
                            <a href="{{ route('employees.edit', $employee->employee_id) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column - Photo and Basic Info -->
                            <div class="col-md-4 text-center mb-4">
                                <div class="position-relative d-inline-block">
                                    <img src="{{ $employee->photo ? asset('storage/' . $employee->photo) : asset('assets/images/profile/user-1.jpg') }}"
                                        class="rounded-circle" width="200" height="200"
                                        style="object-fit: cover; border: 3px solid #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
                                </div>

                                <div class="mt-4">
                                    @if ($employee->user)
                                        <span class="badge bg-success px-3 py-2 mb-2">
                                            <i class="fas fa-check-circle me-1"></i> Compte utilisateur actif
                                        </span>
                                        <div class="mt-2">
                                            <small class="text-muted d-block">Username:
                                                {{ $employee->user->username }}</small>
                                            <small class="text-muted d-block">Email: {{ $employee->user->email }}</small>
                                            <small class="text-muted d-block">Rôle:
                                                {{ ucfirst($employee->user->role) }}</small>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary px-3 py-2">
                                            <i class="fas fa-times-circle me-1"></i> Aucun compte utilisateur
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-4">
                                    @if ($employee->resignation_date)
                                        <span class="badge bg-danger px-4 py-2">
                                            <i class="fas fa-user-minus me-1"></i> Démissionné le
                                            {{ $employee->resignation_date->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="badge bg-success px-4 py-2">
                                            <i class="fas fa-user-check me-1"></i> Employé actif
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Right Column - Detailed Information -->
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-12 mb-4">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3 fw-bold text-primary">
                                                    <i class="fas fa-info-circle me-2"></i>Informations Personnelles
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <strong>CIN:</strong>
                                                        <p class="mb-0">{{ $employee->cin ?? 'Non renseigné' }}</p>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <strong>CNSS:</strong>
                                                        <p class="mb-0">{{ $employee->cnss ?? 'Non renseigné' }}</p>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Email:</strong>
                                                        <p class="mb-0">{{ $employee->email ?? 'Non renseigné' }}</p>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Téléphone:</strong>
                                                        <p class="mb-0">{{ $employee->phone ?? 'Non renseigné' }}</p>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Date de Naissance:</strong>
                                                        <p class="mb-0">
                                                            {{ $employee->birth_date ? $employee->birth_date->format('d/m/Y') : 'Non renseignée' }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-12 mb-2">
                                                        <strong>Adresse:</strong>
                                                        <p class="mb-0">{{ $employee->address ?? 'Non renseignée' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <div class="card border-0 bg-light h-100">
                                            <div class="card-body">
                                                <h6 class="mb-3 fw-bold text-success">
                                                    <i class="fas fa-briefcase me-2"></i>Informations Professionnelles
                                                </h6>
                                                <table class="table table-sm">
                                                    <tr>
                                                        <th>Département:</th>
                                                        <td>{{ $employee->department ?? 'Non renseigné' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Poste:</th>
                                                        <td>{{ $employee->position ?? 'Non renseigné' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Date d'Embauche:</th>
                                                        <td>{{ $employee->hire_date->format('d/m/Y') }}</td>
                                                    </tr>
                                                    @if ($employee->resignation_date)
                                                        <tr>
                                                            <th>Date de Départ:</th>
                                                            <td>{{ $employee->resignation_date->format('d/m/Y') }}</td>
                                                        </tr>
                                                    @endif
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <div class="card border-0 bg-light h-100">
                                            <div class="card-body">
                                                <h6 class="mb-3 fw-bold text-warning">
                                                    <i class="fas fa-money-bill-wave me-2"></i>Informations Salariales
                                                </h6>
                                                <table class="table table-sm">
                                                    @if ($employee->monthly_salary)
                                                        <tr>
                                                            <th>Salaire Mensuel:</th>
                                                            <td class="fw-bold">
                                                                {{ number_format($employee->monthly_salary, 2, ',', '.') }} DH</td>
                                                        </tr>
                                                    @endif
                                                    @if ($employee->hourly_salary)
                                                        <tr>
                                                            <th>Salaire Horaire:</th>
                                                            <td class="fw-bold">
                                                                {{ number_format($employee->hourly_salary, 2, ',', '.') }} DH/h</td>
                                                        </tr>
                                                    @endif
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3 fw-bold text-info">
                                                    <i class="fas fa-phone-alt me-2"></i>Contact d'Urgence
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Personne à contacter:</strong>
                                                        <p class="mb-0">
                                                            {{ $employee->emergency_contact ?? 'Non renseigné' }}</p>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Téléphone d'urgence:</strong>
                                                        <p class="mb-0">
                                                            {{ $employee->emergency_phone ?? 'Non renseigné' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="border-top pt-3 mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i> Enregistré le
                                                {{ $employee->created_at->format('d/m/Y à H:i') }}
                                                @if ($employee->updated_at != $employee->created_at)
                                                    | Dernière modification le
                                                    {{ $employee->updated_at->format('d/m/Y à H:i') }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Add this section after the existing information in the employee show blade --}}

        <!-- Attendance Statistics -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar-check me-2"></i>Statistiques de Présence
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="stats_year" class="form-label">Année</label>
                                <select id="stats_year" class="form-control">
                                    @for ($y = date('Y') - 2; $y <= date('Y'); $y++)
                                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>
                                            {{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="stats_month" class="form-label">Mois</label>
                                <select id="stats_month" class="form-control">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $m == date('m') ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-primary d-block" id="load-stats">
                                    <i class="fas fa-chart-bar me-1"></i> Charger
                                </button>
                            </div>
                        </div>

                        <div id="stats-container">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance History Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-history me-2"></i>Historique des Présences
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="attendance-history-table">
                                <thead>
                                    32shall
                                    <th>Date</th>
                                    <th>Jour</th>
                                    <th>Arrivée</th>
                                    <th>Départ</th>
                                    <th>Heures</th>
                                    <th>Statut</th>
                                    <th>Motif</th>
                                    </tr>
                                </thead>
                                <tbody id="attendance-history-body">
                                    <tr>
                                        <td colspan="7" class="text-center">Chargement...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Créer un compte utilisateur
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createUserForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Création d'un compte pour: <strong>{{ $employee->full_name }}</strong>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom d'utilisateur *</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Mot de passe *</label>
                                <input type="password" class="form-control" name="password" required minlength="8">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmer mot de passe *</label>
                                <input type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rôle *</label>
                            <select class="form-control" name="role_id" required>
                                <option value="">Sélectionner un rôle...</option>
                                @foreach ($roleDistribution as $roleName => $count)
                                    @php
                                        $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
                                    @endphp
                                    @if ($role)
                                        <option value="{{ $role->id }}">
                                            {{ ucfirst($roleName) }}
                                            @if ($count > 0)
                                                ({{ $count }} utilisateurs)
                                            @endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Créer le compte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            loadAttendanceStats();
            loadAttendanceHistory();

            $('#load-stats').click(function() {
                loadAttendanceStats();
                loadAttendanceHistory();
            });

            function loadAttendanceStats() {
                let year = $('#stats_year').val();
                let month = $('#stats_month').val();
                let employeeId = {{ $employee->employee_id }};

                $.ajax({
                    url: `/attendance/employee/${employeeId}/stats`,
                    type: 'GET',
                    data: {
                        year: year,
                        month: month
                    },
                    success: function(response) {
                        if (response.success) {
                            displayStats(response.data);
                        }
                    },
                    error: function() {
                        $('#stats-container').html(
                            '<div class="alert alert-danger">Erreur de chargement des statistiques</div>'
                        );
                    }
                });
            }

            function displayStats(stats) {
                let html = `
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>${stats.present}</h3>
                            <small>Jours Présents</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h3>${stats.absent}</h3>
                            <small>Jours Absents</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3>${stats.late}</h3>
                            <small>Retards</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3>${stats.half_day}</h3>
                            <small>Demi-journées</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h3>${stats.paid_leave}</h3>
                            <small>Congés Payés</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h3>${stats.sick_leave}</h3>
                            <small>Arrêts Maladie</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body text-center">
                            <h3>${stats.total_hours} h</h3>
                            <small>Total Heures</small>
                        </div>
                    </div>
                </div>
            </div>
        `;
                $('#stats-container').html(html);
            }

            function loadAttendanceHistory() {
                let year = $('#stats_year').val();
                let month = $('#stats_month').val();
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
                        }
                    },
                    error: function() {
                        $('#attendance-history-body').html(
                            '<tr><td colspan="7" class="text-center text-danger">Erreur de chargement</td></tr>'
                        );
                    }
                });
            }

            function displayHistory(attendances) {
                if (attendances.length === 0) {
                    $('#attendance-history-body').html(
                        '<tr><td colspan="7" class="text-center">Aucune donnée pour cette période</td></tr>');
                    return;
                }

                let html = '';
                attendances.forEach(function(att) {
                    let statusClass = '';
                    let statusText = '';

                    switch (att.status) {
                        case 'present':
                            statusClass = 'success';
                            statusText = 'Présent';
                            break;
                        case 'absent':
                            statusClass = 'danger';
                            statusText = 'Absent';
                            break;
                        case 'late':
                            statusClass = 'warning';
                            statusText = 'Retard';
                            break;
                        case 'half_day':
                            statusClass = 'info';
                            statusText = 'Demi-journée';
                            break;
                        case 'holiday':
                            statusClass = 'primary';
                            statusText = 'Congé';
                            break;
                        case 'sick_leave':
                            statusClass = 'secondary';
                            statusText = 'Arrêt maladie';
                            break;
                        case 'paid_leave':
                            statusClass = 'info';
                            statusText = 'Congé payé';
                            break;
                        case 'unpaid_leave':
                            statusClass = 'dark';
                            statusText = 'Congé sans solde';
                            break;
                    }

                    html += `
                <tr>
                    <td>${att.date}</td>
                    <td>${att.day_of_week}</td>
                    <td>${att.check_in || '-'}</td>
                    <td>${att.check_out || '-'}</td>
                    <td>${att.hours_worked || '-'}</td>
                    <td><span class="badge bg-${statusClass}">${statusText}</span></td>
                    <td>${att.reason || '-'}</td>
                </tr>
            `;
                });

                $('#attendance-history-body').html(html);
            }

            // Create user form submit
            $('#createUserForm').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('employees.create-user', $employee->employee_id) }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON?.errors;
                        var errorMessage = xhr.responseJSON?.message ||
                            'Une erreur est survenue';

                        if (errors) {
                            errorMessage = '';
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        }

                        showToast('error', errorMessage);
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
