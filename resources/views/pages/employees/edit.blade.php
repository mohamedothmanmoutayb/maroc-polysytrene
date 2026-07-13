@extends('layouts.app')

@section('title', 'Modifier Employé')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Employé</h4>
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
                                        Modifier
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
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-edit me-2"></i>Modifier: {{ $employee->full_name }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="employeeForm" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Left Column - Photo -->
                                <div class="col-md-3 text-center mb-4">
                                    <div class="position-relative d-inline-block">
                                        <img src="{{ $employee->photo ? asset('storage/' . $employee->photo) : asset('assets/images/profile/user-1.jpg') }}"
                                            id="photoPreview" class="rounded-circle" width="180" height="180"
                                            style="object-fit: cover; border: 3px solid #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
                                        <label for="photo"
                                            class="position-absolute bottom-0 end-0 bg-primary rounded-circle p-3"
                                            style="cursor: pointer;">
                                            <i class="fas fa-camera text-white fs-5"></i>
                                        </label>
                                        <input type="file" id="photo" name="photo" class="d-none" accept="image/*">
                                    </div>
                                    <p class="text-muted mt-3">Cliquez sur l'icône pour changer la photo</p>
                                    @if ($employee->photo)
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> Laissez vide pour conserver la photo actuelle
                                        </small>
                                    @endif
                                </div>

                                <!-- Right Column - Form Fields -->
                                <div class="col-md-9">
                                    <div class="row">
                                        <h6 class="mb-3 fw-bold text-primary">
                                            <i class="fas fa-user me-2"></i>Informations Personnelles
                                        </h6>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nom Complet *</label>
                                            <input type="text" class="form-control" name="full_name"
                                                value="{{ $employee->full_name }}" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">CIN</label>
                                            <input type="text" class="form-control" name="cin"
                                                value="{{ $employee->cin }}" placeholder="Ex: BE123456">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">CNSS</label>
                                            <input type="text" class="form-control" name="cnss"
                                                value="{{ $employee->cnss }}" placeholder="Numéro CNSS">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">ID Pointeuse (ZKTeco)</label>
                                            <input type="text" class="form-control" name="zk_uid"
                                                value="{{ $employee->zk_uid }}" placeholder="ID utilisateur sur la pointeuse">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email"
                                                value="{{ $employee->email }}" placeholder="exemple@email.com">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Téléphone</label>
                                            <input type="text" class="form-control" name="phone"
                                                value="{{ $employee->phone }}" placeholder="06 XX XX XX XX">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Date de Naissance</label>
                                            <input type="date" class="form-control" name="birth_date"
                                                value="{{ $employee->birth_date ? $employee->birth_date->format('Y-m-d') : '' }}">
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Adresse</label>
                                            <textarea class="form-control" name="address" rows="2" placeholder="Adresse complète">{{ $employee->address }}</textarea>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <h6 class="mb-3 fw-bold text-success">
                                            <i class="fas fa-briefcase me-2"></i>Informations Professionnelles
                                        </h6>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Département</label>
                                            <input type="text" class="form-control" name="department"
                                                value="{{ $employee->department }}" placeholder="Ex: Production">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Poste</label>
                                            <input type="text" class="form-control" name="position"
                                                value="{{ $employee->position }}" placeholder="Ex: Opérateur">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Date d'Embauche *</label>
                                            <input type="date" class="form-control" name="hire_date"
                                                value="{{ $employee->hire_date->format('Y-m-d') }}" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Date de démission</label>
                                            <input type="date" class="form-control" name="resignation_date"
                                                value="{{ $employee->resignation_date ? $employee->resignation_date->format('Y-m-d') : '' }}">
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <h6 class="mb-3 fw-bold text-warning">
                                            <i class="fas fa-money-bill-wave me-2"></i>Informations Salariales
                                        </h6>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Salaire Mensuel (DH)</label>
                                            <input type="number" class="form-control" name="monthly_salary"
                                                step="0.01" min="0" value="{{ $employee->monthly_salary }}"
                                                placeholder="0.00">
                                            <small class="text-muted">Laissez vide si salaire horaire</small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Salaire Horaire (DH)</label>
                                            <input type="number" class="form-control" name="hourly_salary"
                                                step="0.01" min="0" value="{{ $employee->hourly_salary }}"
                                                placeholder="0.00">
                                            <small class="text-muted">Laissez vide si salaire mensuel</small>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <h6 class="mb-3 fw-bold text-info">
                                            <i class="fas fa-phone-alt me-2"></i>Contact d'Urgence
                                        </h6>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Personne à contacter</label>
                                            <input type="text" class="form-control" name="emergency_contact"
                                                value="{{ $employee->emergency_contact }}" placeholder="Nom complet">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Téléphone d'urgence</label>
                                            <input type="text" class="form-control" name="emergency_phone"
                                                value="{{ $employee->emergency_phone }}" placeholder="06 XX XX XX XX">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Mettre à jour
                                </button>
                                <a href="{{ route('employees.show', $employee->employee_id) }}" class="btn btn-info">
                                    <i class="fas fa-eye me-1"></i> Voir détails
                                </a>
                                <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Preview photo
            $('#photo').change(function(e) {
                const file = this.files[0];
                if (file) {
                    const fileSize = file.size / 1024 / 1024;
                    if (fileSize > 2) {
                        showToast('error', 'La taille de l\'image ne doit pas dépasser 2MB');
                        $(this).val('');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#photoPreview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Form submit
            $('#employeeForm').submit(function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Mise à jour...');

                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                $.ajax({
                    url: "{{ route('employees.update', $employee->employee_id) }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('employees.show', $employee->employee_id) }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(originalText);
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
                        submitBtn.prop('disabled', false).html(originalText);
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
