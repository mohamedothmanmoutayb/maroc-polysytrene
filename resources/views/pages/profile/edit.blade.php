@extends('layouts.app')

@section('title', 'Mon Profil')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Mon Profil</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Profil
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
                            <i class="fas fa-user-circle me-2"></i>Modifier mon profil
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="profileForm" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-4 text-center mb-4">
                                    <div class="position-relative d-inline-block">
                                        <img src="{{ Auth::user()->profile_photo ? asset('storage/' . Auth::user()->profile_photo) : asset('assets/images/profile/user-1.jpg') }}"
                                            id="profilePreview" class="rounded-circle" width="150" height="150"
                                            style="object-fit: cover; border: 3px solid #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
                                        <label for="profile_photo"
                                            class="position-absolute bottom-0 end-0 bg-primary rounded-circle p-2"
                                            style="cursor: pointer;">
                                            <i class="fas fa-camera text-white"></i>
                                        </label>
                                        <input type="file" id="profile_photo" name="profile_photo" class="d-none"
                                            accept="image/*">
                                    </div>
                                    <p class="text-muted mt-2">Cliquez sur l'icône pour changer la photo</p>
                                </div>

                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nom d'utilisateur *</label>
                                            <input type="text" class="form-control" name="username"
                                                value="{{ $user->username }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email *</label>
                                            <input type="email" class="form-control" name="email"
                                                value="{{ $user->email }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Téléphone</label>
                                            <input type="text" class="form-control" name="phone"
                                                value="{{ $user->phone }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Rôle</label>
                                            <input type="text" class="form-control" value="{{ ucfirst($user->role) }}"
                                                disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Statut</label>
                                            <input type="text" class="form-control"
                                                value="{{ $user->is_active ? 'Actif' : 'Inactif' }}" disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Membre depuis</label>
                                            <input type="text" class="form-control"
                                                value="{{ $user->created_at->format('d/m/Y') }}" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('profile.password') }}" class="btn btn-warning">
                                    <i class="fas fa-key me-1"></i> Changer mot de passe
                                </a>
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
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
            // Preview profile photo
            $('#profile_photo').change(function(e) {
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
                        $('#profilePreview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Form submit
            $('#profileForm').submit(function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                const formData = new FormData(this);

                $.ajax({
                    url: "{{ route('profile.update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html(originalText);
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
