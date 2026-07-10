@extends('layouts.app')

@section('title', 'Modifier le rôle - ' . $role->name)

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">
                            <i class="fas fa-edit me-2"></i>Modifier le rôle : {{ $role->name }}
                        </h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.roles.index') }}">Rôles</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-warning text-dark">
                                        Modifier
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header card-header-custom">
                <h5 class="card-title mb-0" style="color:white">
                    <i class="fas fa-edit me-2"></i>Modifier le rôle : {{ $role->name }}
                </h5>
            </div>
            <div class="card-body">
                <form id="editRoleForm">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Nom du rôle *</label>
                        <input type="text" class="form-control" name="name" value="{{ $role->name }}" required>
                        <small class="text-muted">Utilisez des lettres minuscules et des underscores</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Permissions</label>
                        <div class="border rounded p-3" style="max-height: 500px; overflow-y: auto;">
                            @foreach ($permissions as $module => $modulePermissions)
                                <div class="mb-3">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input select-all-module"
                                            data-module="{{ $module }}"
                                            {{ count($modulePermissions) == count(array_intersect($modulePermissions->pluck('id')->toArray(), $rolePermissions)) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold">
                                            <i class="fas fa-folder-open me-1"></i> {{ $module ?: 'Général' }}
                                        </label>
                                    </div>
                                    <div class="ms-4">
                                        @foreach ($modulePermissions as $permission)
                                            <div class="form-check form-check-inline mb-2">
                                                <input type="checkbox" class="form-check-input permission-checkbox"
                                                    name="permissions[]" value="{{ $permission->id }}"
                                                    data-module="{{ $module }}"
                                                    {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                <label class="form-check-label small">
                                                    <code>{{ $permission->name }}</code>
                                                    @if ($permission->description)
                                                        <i class="fas fa-info-circle text-muted"
                                                            title="{{ $permission->description }}"></i>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Mettre à jour
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('.select-all-module').change(function() {
                    var module = $(this).data('module');
                    var isChecked = $(this).is(':checked');
                    $('.permission-checkbox[data-module="' + module + '"]').prop('checked', isChecked);
                });

                $('.permission-checkbox').change(function() {
                    var module = $(this).data('module');
                    var allChecked = $('.permission-checkbox[data-module="' + module + '"]:checked').length ===
                        $('.permission-checkbox[data-module="' + module + '"]').length;
                    $('.select-all-module[data-module="' + module + '"]').prop('checked', allChecked);
                });

                $('#editRoleForm').submit(function(e) {
                    e.preventDefault();
                    var submitBtn = $(this).find('button[type="submit"]');
                    submitBtn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span> Mise à jour...');

                    $.ajax({
                        url: "{{ route('admin.roles.update', $role->id) }}",
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                setTimeout(function() {
                                    window.location.href =
                                        "{{ route('admin.roles.index') }}";
                                }, 1500);
                            } else {
                                showToast('error', response.message);
                            }
                        },
                        error: function(xhr) {
                            showToast('error', xhr.responseJSON?.message ||
                                'Une erreur est survenue');
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false).html(
                                '<i class="fas fa-save me-1"></i> Mettre à jour');
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
            });
        </script>
    @endpush
@endsection
