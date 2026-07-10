@extends('layouts.app')

@section('title', 'Nouvelle Famille')

@section('content')
    <div class="container-fluid">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Nouvelle Famille</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('familles.index') }}">
                                        Familles
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Nouvelle Famille
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
                            <i class="fas fa-plus-circle me-2"></i>Créer une Nouvelle Famille
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="familleForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-layer-group me-2"></i>Informations Famille
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group mb-3">
                                                <label for="famille_code" class="form-label">Code Famille *</label>
                                                <input type="text" class="form-control" id="famille_code"
                                                    name="famille_code" required>
                                                <small class="form-text text-muted">Code unique pour identifier la
                                                    famille</small>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="famille_name" class="form-label">Nom de la Famille *</label>
                                                <input type="text" class="form-control" id="famille_name"
                                                    name="famille_name" required>
                                                <small class="form-text text-muted">Nom d'affichage de la famille</small>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-tags me-2"></i>Tarification
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="prix_client" class="form-label">Prix Client (DH)</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="prix_client"
                                                            name="prix_client" min="0" step="0.01" value="0">
                                                        <span class="input-group-text">DH</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="prix_grossiste" class="form-label">Prix Grossiste
                                                        (DH)</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="prix_grossiste"
                                                            name="prix_grossiste" min="0" step="0.01"
                                                            value="0">
                                                        <span class="input-group-text">DH</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="prix_commercial" class="form-label">Prix Commercial
                                                        (DH)</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="prix_commercial"
                                                            name="prix_commercial" min="0" step="0.01"
                                                            value="0">
                                                        <span class="input-group-text">DH</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="prix_special" class="form-label">Prix Spécial (DH)</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="prix_special"
                                                            name="prix_special" min="0" step="0.01"
                                                            value="0">
                                                        <span class="input-group-text">DH</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="prix_revient" class="form-label">Prix Revient
                                                        (DH)</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="prix_revient"
                                                            name="prix_revient" min="0" step="0.01"
                                                            value="0">
                                                        <span class="input-group-text">DH</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="alert alert-info mt-2">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Ces prix seront appliqués à tous les produits de cette famille.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-cubes me-2"></i>Produits Associés
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info mb-3">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Vous pouvez associer cette famille à un ou plusieurs produits.
                                            </div>

                                            <div id="productsContainer">
                                                <!-- Product rows will be added here -->
                                            </div>

                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                id="addProductBtn">
                                                <i class="fas fa-plus me-1"></i> Ajouter un Produit
                                            </button>

                                            {{-- Rows built dynamically by JS with AJAX Select2 --}}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-save me-2"></i>Enregistrement
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <strong>Important:</strong> Vous pourrez ajouter d'autres produits et
                                                ajuster le stock après la création.
                                            </div>

                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                                    <i class="fas fa-save me-2"></i> Créer la Famille
                                                </button>
                                                <a href="{{ route('familles.index') }}" class="btn btn-secondary">
                                                    <i class="fas fa-times me-2"></i> Annuler
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
        }

        .product-row {
            transition: all 0.3s ease;
        }

        .product-row.removing {
            opacity: 0;
            transform: translateX(-100%);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 0;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
        const SEARCH_URL = "{{ route('products.search') }}";

        $(document).ready(function() {

            function makeSelect2Ajax() {
                return {
                    ajax: {
                        url: SEARCH_URL,
                        dataType: 'json',
                        delay: 250,
                        data: function (p) { return { q: p.term || '' }; },
                        processResults: function (data) { return { results: data.results }; },
                        cache: true
                    },
                    language: 'fr',
                    placeholder: 'Sélectionner un produit...',
                    allowClear: true,
                    minimumInputLength: 0,
                };
            }

            function addProductRow() {
                const row = $(`
                    <div class="product-row mb-3 border-bottom pb-3">
                        <div class="row">
                            <div class="col-md-7">
                                <label class="form-label">Produit</label>
                                <select class="form-control select2-product" name="associated_products[]"></select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quantité/Unité</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="quantity_per_unit[]" min="0.01" step="0.01" value="1">
                                    <span class="input-group-text">/unité</span>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-danger remove-product-btn">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `);
                row.find('.select2-product').select2(makeSelect2Ajax());
                $('#productsContainer').append(row);
            }

            addProductRow();

            $('#addProductBtn').click(function() { addProductRow(); });

            $(document).on('click', '.remove-product-btn', function() {
                const row = $(this).closest('.product-row');
                row.addClass('removing');
                setTimeout(() => row.remove(), 300);
            });

            // Form submission
            $('#familleForm').submit(function(e) {
                e.preventDefault();

                const submitBtn = $('#submitBtn');
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-2"></i> Création...');

                const formData = new FormData(this);

                const productSelects = $('.select2-product');
                productSelects.each(function(index) {
                    if (!$(this).val()) {
                        formData.delete(`associated_products[${index}]`);
                        formData.delete(`quantity_per_unit[${index}]`);
                    }
                });

                // Submit form
                $.ajax({
                    url: "{{ route('familles.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href = "{{ route('familles.index') }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(
                                '<i class="fas fa-save me-2"></i> Créer la Famille');
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        let errorMessage = '';

                        if (errors) {
                            Object.values(errors).forEach(function(errorArray) {
                                errorArray.forEach(function(error) {
                                    errorMessage += error + '<br>';
                                });
                            });
                        } else {
                            errorMessage = xhr.responseJSON?.message ||
                                'Une erreur est survenue';
                        }

                        showToast('error', errorMessage);
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save me-2"></i> Créer la Famille');
                    }
                });
            });

            // Toast notification
            function showToast(type, message) {
                const toast = $(`
                    <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0"
                         role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">${message}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `);

                $('#toast-container').append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();

                setTimeout(() => toast.remove(), 5000);
            }
        });
    </script>
@endpush
