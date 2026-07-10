@extends('layouts.app')

@section('title', 'Gérer les permissions - ' . $role->name)

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">
                            <i class="fas fa-key me-2"></i>Gérer les permissions : {{ $role->name }}
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
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Permissions
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
            <div class="col-md-4">
                <div class="card bg-primary-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary rounded-circle p-3 text-white">
                                <i class="fas fa-key fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="totalPermissions">0</h2>
                                <span class="text-muted">Permissions totales</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success rounded-circle p-3 text-white">
                                <i class="fas fa-check-circle fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="assignedPermissions">0</h2>
                                <span class="text-muted">Permissions assignées</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info-subtle border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-info rounded-circle p-3 text-white">
                                <i class="fas fa-chart-line fs-6"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold" id="percentageProgress">0%</h2>
                                <span class="text-muted">Progression</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0" style="color:white">
                    <i class="fas fa-key me-2"></i>Permissions pour le rôle : {{ $role->name }}
                </h5>
                <div>
                    <button type="button" class="btn btn-light" id="selectAllPermissions">
                        <i class="fas fa-check-double me-1"></i> Tout sélectionner
                    </button>
                    <button type="button" class="btn btn-light" id="deselectAllPermissions">
                        <i class="fas fa-times-circle me-1"></i> Tout désélectionner
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="permissionsForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchPermission"
                            placeholder="Rechercher une permission...">
                    </div>

                    <div class="border rounded p-3" style="max-height: 500px; overflow-y: auto;">
                        @foreach ($permissions as $module => $modulePermissions)
                            <div class="mb-3 permission-module" data-module="{{ $module }}">
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input select-all-module"
                                        data-module="{{ $module }}">
                                    <label class="form-check-label fw-bold">
                                        <i class="fas fa-folder-open me-1"></i> {{ $module ?: 'Général' }}
                                        <span class="badge bg-secondary ms-2">{{ $modulePermissions->count() }}
                                            permissions</span>
                                    </label>
                                </div>
                                <div class="ms-4">
                                    @foreach ($modulePermissions as $permission)
                                        <div class="form-check form-check-inline mb-2 permission-item"
                                            data-permission-name="{{ strtolower($permission->name) }}">
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

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer les permissions
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Retour
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
                function updateStats() {
                    var totalPermissions = $('.permission-checkbox').length;
                    var assignedPermissions = $('.permission-checkbox:checked').length;
                    var percentage = totalPermissions > 0 ? Math.round((assignedPermissions / totalPermissions) * 100) :
                        0;

                    $('#totalPermissions').text(totalPermissions);
                    $('#assignedPermissions').text(assignedPermissions);
                    $('#percentageProgress').text(percentage + '%');

                    // Update progress bar if exists
                    $('#percentageProgress').html(percentage + '%');
                }

                // Initial stats
                updateStats();

                // Select all permissions in a module
                $('.select-all-module').change(function() {
                    var module = $(this).data('module');
                    var isChecked = $(this).is(':checked');
                    $('.permission-checkbox[data-module="' + module + '"]').prop('checked', isChecked);
                    updateStats();
                });

                // When any permission is unchecked, uncheck the "select all" for that module
                $('.permission-checkbox').change(function() {
                    var module = $(this).data('module');
                    var allChecked = $('.permission-checkbox[data-module="' + module + '"]:checked').length ===
                        $('.permission-checkbox[data-module="' + module + '"]').length;
                    $('.select-all-module[data-module="' + module + '"]').prop('checked', allChecked);
                    updateStats();
                });

                // Search functionality
                $('#searchPermission').on('keyup', function() {
                    var searchTerm = $(this).val().toLowerCase();

                    $('.permission-module').each(function() {
                        var module = $(this);
                        var hasVisiblePermissions = false;

                        module.find('.permission-item').each(function() {
                            var permissionName = $(this).data('permission-name');
                            if (permissionName.includes(searchTerm) || searchTerm === '') {
                                $(this).show();
                                hasVisiblePermissions = true;
                            } else {
                                $(this).hide();
                            }
                        });

                        if (hasVisiblePermissions || searchTerm === '') {
                            module.show();
                        } else {
                            module.hide();
                        }
                    });
                });

                // Select all permissions button
                $('#selectAllPermissions').click(function() {
                    $('.permission-checkbox').prop('checked', true);
                    $('.select-all-module').prop('checked', true);
                    updateStats();
                    showToast('info', 'Toutes les permissions ont été sélectionnées');
                });

                // Deselect all permissions button
                $('#deselectAllPermissions').click(function() {
                    $('.permission-checkbox').prop('checked', false);
                    $('.select-all-module').prop('checked', false);
                    updateStats();
                    showToast('info', 'Toutes les permissions ont été désélectionnées');
                });

                $('#permissionsForm').submit(function(e) {
                    e.preventDefault();
                    var submitBtn = $(this).find('button[type="submit"]');
                    submitBtn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span> Enregistrement...');

                    $.ajax({
                        url: "{{ route('admin.roles.update-permissions', $role->id) }}",
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                updateStats();
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
                                '<i class="fas fa-save me-1"></i> Enregistrer les permissions');
                        }
                    });
                });

                function showToast(type, message) {
                    var toastId = 'toast-' + Date.now();
                    var bgClass = type === 'success' ? 'bg-success' : (type === 'info' ? 'bg-info' : 'bg-danger');

                    var toastHtml = `
                        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <i class="fas ${type === 'success' ? 'fa-check-circle' : (type === 'info' ? 'fa-info-circle' : 'fa-exclamation-circle')} me-2"></i>
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
