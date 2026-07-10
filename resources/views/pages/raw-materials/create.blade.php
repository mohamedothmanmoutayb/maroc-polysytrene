@extends('layouts.app')

@section('title', 'Nouvelle Matière Première')

@push('stylesheets')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
@endpush

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Nouvelle Matière Première</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Nouvelle Matière
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
                            <i class="fas fa-plus-circle me-2"></i>Créer une Nouvelle Matière Première
                        </h5>
                        <div>
                            <a href="{{ route('raw-materials.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="createMaterialForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="material_code" class="form-label">Code Matière *</label>
                                    <input type="text" class="form-control" id="material_code" name="material_code"
                                        required maxlength="20" placeholder="Ex: MP001">
                                    <small class="form-text text-muted">Code unique d'identification</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="material_name" class="form-label">Nom de la Matière *</label>
                                    <input type="text" class="form-control" id="material_name" name="material_name"
                                        required maxlength="100" placeholder="Ex: Bois de chêne">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="category_id" class="form-label">Catégorie *</label>
                                    <select class="form-control select2" id="category_id" name="category_id" required>
                                        <option value="">Sélectionner une catégorie</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->category_id }}">{{ $category->category_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="unit_of_measure" class="form-label">Unité de Mesure *</label>
                                    <select class="form-control" id="unit_of_measure" name="unit_of_measure" required>
                                        <option value="">Sélectionner une unité</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit }}">{{ $unit }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="min_stock_level" class="form-label">Stock Minimum</label>
                                    <input type="number" class="form-control" id="min_stock_level" name="min_stock_level"
                                        step="0.01" min="0" placeholder="Ex: 100">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="max_stock_level" class="form-label">Stock Maximum</label>
                                    <input type="number" class="form-control" id="max_stock_level" name="max_stock_level"
                                        step="0.01" min="0" placeholder="Ex: 1000">
                                    <small class="form-text text-muted">Doit être supérieur ou égal au stock minimum</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="prix_client" class="form-label">Prix Client (DH)</label>
                                    <input type="number" class="form-control" id="prix_client" name="prix_client"
                                        step="0.01" min="0" value="0">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="prix_grossiste" class="form-label">Prix Grossiste (DH)</label>
                                    <input type="number" class="form-control" id="prix_grossiste" name="prix_grossiste"
                                        step="0.01" min="0" value="0">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="prix_commercial" class="form-label">Prix Commercial (DH)</label>
                                    <input type="number" class="form-control" id="prix_commercial" name="prix_commercial"
                                        step="0.01" min="0" value="0">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="prix_special" class="form-label">Prix Spécial (DH)</label>
                                    <input type="number" class="form-control" id="prix_special" name="prix_special"
                                        step="0.01" min="0" value="0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="is_active" class="form-label">Statut</label>
                                    <select class="form-control" id="is_active" name="is_active">
                                        <option value="1" selected>Actif</option>
                                        <option value="0">Inactif</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"
                                    placeholder="Informations supplémentaires..."></textarea>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note importante :</strong> Le stock et le prix unitaire seront automatiquement gérés
                                à partir des achats. Aucun stock initial n'est nécessaire lors de la création.
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('raw-materials.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Container pour les toasts -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                language: "fr",
                placeholder: "Sélectionner...",
                allowClear: true
            });

            $('#createMaterialForm').submit(function(e) {
                e.preventDefault();

                var minStock = parseFloat($('#min_stock_level').val()) || 0;
                var maxStock = parseFloat($('#max_stock_level').val()) || 0;

                if (maxStock > 0 && minStock > maxStock) {
                    showToast('error', 'Le stock maximum doit être supérieur ou égal au stock minimum');
                    return;
                }

                $.ajax({
                    url: "{{ route('raw-materials.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('raw-materials.index') }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = '';

                        if (errors) {
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        } else {
                            errorMessage = 'Une erreur est survenue';
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
