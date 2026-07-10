@extends('layouts.app')

@section('title', 'Gestion des Employés')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Employés</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Employés
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
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-primary-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary rounded-circle p-3 text-white">
                                <i class="fas fa-users fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold">{{ $totalEmployees }}</h2>
                                <span class="text-muted">Total Employés</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-success-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success rounded-circle p-3 text-white">
                                <i class="fas fa-user-check fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold">{{ $activeEmployees }}</h2>
                                <span class="text-muted">Employés Actifs</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-warning-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning rounded-circle p-3 text-white">
                                <i class="fas fa-money-bill-wave fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold">{{ number_format($totalMonthlySalary, 2, ',', '.') }} DH</h2>
                                <span class="text-muted">Masse Salariale</span>
                                <small class="d-block text-muted">Moy: {{ number_format($avgMonthlySalary, 2, ',', '.') }} DH</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-info-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-info rounded-circle p-3 text-white">
                                <i class="fas fa-building fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold">{{ $departments->count() }}</h2>
                                <span class="text-muted">Départements</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Statistics Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-user-circle me-2"></i>Comptes Utilisateurs
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h3 class="text-success mb-0">{{ $employeesWithAccounts }}</h3>
                                    <small class="text-muted">Avec compte</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div>
                                    <h3 class="text-warning mb-0">{{ $employeesWithoutAccounts }}</h3>
                                    <small class="text-muted">Sans compte</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-tags me-2"></i>Distribution des Rôles
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            @foreach ($roleDistribution as $roleName => $count)
                                @if ($count > 0)
                                    @php
                                        $badgeClass = match ($roleName) {
                                            'admin' => 'danger',
                                            'manager' => 'warning',
                                            'sales' => 'info',
                                            'production' => 'primary',
                                            'accountant' => 'success',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <div class="text-center">
                                        <span class="badge bg-{{ $badgeClass }} p-2" style="font-size: 14px;">
                                            {{ ucfirst($roleName) }}: {{ $count }}
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0" style="color:white">
                    <i class="fas fa-users me-2"></i>Liste des Employés
                </h5>
                @can('create_employees')
                    <a href="{{ route('employees.create') }}" class="btn btn-light">
                        <i class="fas fa-plus-circle me-1"></i> Nouvel Employé
                    </a>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="employees-table" class="table table-hover w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="8%">Photo</th>
                                <th>Nom Complet</th>
                                <th>CIN</th>
                                <th>Téléphone</th>
                                <th>Département</th>
                                <th>Salaire</th>
                                <th>Date Embauche</th>
                                <th width="15%">Compte</th>
                                <th width="8%">Statut</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Créer un compte utilisateur
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="createUserForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="employee_id" name="employee_id">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Création d'un compte pour: <strong id="employee_name"></strong>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom d'utilisateur *</label>
                                <input type="text" class="form-control" name="username" required>
                                <small class="text-muted">Ex: john.doe</small>
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
                                <small class="text-muted">Minimum 8 caractères</small>
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
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Créer le compte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <style>
        .table td {
            vertical-align: middle;
        }

        .badge {
            font-size: 11px;
            padding: 4px 8px;
        }

        .toast-container {
            z-index: 9999;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#employees-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('employees.index') }}",
                    type: 'GET',
                    error: function(xhr) {
                        console.error('DataTable error:', xhr);
                        showToast('error', 'Erreur lors du chargement des données');
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
                        data: 'photo',
                        name: 'photo',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'full_name',
                        name: 'full_name'
                    },
                    {
                        data: 'cin',
                        name: 'cin'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'salary',
                        name: 'salary',
                        orderable: false,
                        className: 'text-end'
                    },
                    {
                        data: 'hire_date',
                        name: 'hire_date',
                        className: 'text-center'
                    },
                    {
                        data: 'has_account',
                        name: 'has_account',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'status',
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
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json",
                    search: "Rechercher:",
                    searchPlaceholder: "Nom, CIN, téléphone..."
                },
                order: [
                    [2, 'asc']
                ],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "Tous"]
                ],
                drawCallback: function() {
                    // Initialize any tooltips
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });

            // Delete employee
            $(document).on('click', '.delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');

                if (confirm('Êtes-vous sûr de vouloir supprimer l\'employé "' + name +
                        '" ?\nCette action est irréversible !')) {
                    $.ajax({
                        url: "{{ route('employees.destroy', '') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                table.ajax.reload();
                                // Reload statistics
                                location.reload();
                            } else {
                                showToast('error', response.message);
                            }
                        },
                        error: function(xhr) {
                            var message = xhr.responseJSON?.message ||
                                'Une erreur est survenue';
                            showToast('error', message);
                        }
                    });
                }
            });

            // Create account button click
            $(document).on('click', '.create-account', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');

                $('#employee_id').val(id);
                $('#employee_name').text(name);
                $('#createUserForm')[0].reset();
                $('#createUserModal').modal('show');
            });

            // Create user form submit
            $('#createUserForm').submit(function(e) {
                e.preventDefault();
                var id = $('#employee_id').val();
                var submitBtn = $(this).find('button[type="submit"]');

                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Création...');

                $.ajax({
                    url: '/employees/' + id + '/createUser',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            $('#createUserModal').modal('hide');
                            table.ajax.reload();
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
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i> Créer le compte');
                    }
                });
            });

            function showToast(type, message) {
                var toastId = 'toast-' + Date.now();
                var bgClass = type === 'success' ? 'bg-success' : 'bg-danger';

                var toastHtml = `
                    <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `;

                $('#toast-container').append(toastHtml);
                var toastElement = document.getElementById(toastId);
                var bsToast = new bootstrap.Toast(toastElement);
                bsToast.show();

                toastElement.addEventListener('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }

            // Auto-refresh every 60 seconds
            setInterval(function() {
                table.ajax.reload(null, false);
            }, 60000);
        });
    </script>
@endpush
