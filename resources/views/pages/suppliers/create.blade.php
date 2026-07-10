@extends('layouts.app')

@section('title', 'Nouveau Fournisseur')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Nouveau Fournisseur</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('suppliers.index') }}">
                                        Fournisseurs
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Nouveau
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
                            <i class="fas fa-plus-circle me-2"></i>Créer un Nouveau Fournisseur
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="supplierForm">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="supplier_type" class="form-label">Type de Fournisseur *</label>
                                        <select class="form-control" id="supplier_type" name="supplier_type" required>
                                            <option value="">Sélectionner un type</option>
                                            <option value="physique">Physique (Individuel)</option>
                                            <option value="morale">Morale (Entreprise)</option>
                                        </select>
                                        <small class="form-text text-muted">
                                            <strong>Physique:</strong> Fournisseur individuel<br>
                                            <strong>Morale:</strong> Fournisseur entreprise
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="full_name" class="form-label">Nom Complet</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name"
                                            maxlength="100" placeholder="Ex: Ahmed Benali">
                                        <small class="form-text text-muted">Obligatoire pour les fournisseurs
                                            physiques</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group" id="company-name-section" style="display: none;">
                                        <label for="company_name" class="form-label">Nom de l'Entreprise *</label>
                                        <input type="text" class="form-control" id="company_name" name="company_name"
                                            maxlength="100" placeholder="Ex: SARL MATELAS MAROC">
                                        <small class="form-text text-muted">Obligatoire pour les fournisseurs moraux</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3" id="representative-section" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="representative_name" class="form-label">Nom du Représentant</label>
                                        <input type="text" class="form-control" id="representative_name"
                                            name="representative_name" maxlength="100" placeholder="Ex: Karim El Fassi">
                                        <small class="form-text text-muted">Optionnel</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3" id="company-info-section" style="display: none;">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ice" class="form-label">ICE</label>
                                        <input type="text" class="form-control" id="ice" name="ice"
                                            maxlength="30" placeholder="Ex: 001234567890123">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="rc" class="form-label">RC</label>
                                        <input type="text" class="form-control" id="rc" name="rc"
                                            maxlength="30" placeholder="Ex: 12345">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="patente" class="form-label">Patente</label>
                                        <input type="text" class="form-control" id="patente" name="patente"
                                            maxlength="30" placeholder="Ex: 47123456">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-label">Téléphone *</label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            required maxlength="20" placeholder="Ex: 0612345678">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            maxlength="100" placeholder="Ex: contact@fournisseur.com">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="address" class="form-label">Adresse</label>
                                        <textarea class="form-control" id="address" name="address" rows="2" placeholder="Adresse complète..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="is_active" class="form-label">Statut</label>
                                        <select class="form-control" id="is_active" name="is_active">
                                            <option value="1" selected>Actif</option>
                                            <option value="0">Inactif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
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
            // Show/hide fields based on supplier type
            $('#supplier_type').change(function() {
                var supplierType = $(this).val();

                $('#company-name-section').hide();
                $('#representative-section').hide();
                $('#company-info-section').hide();

                $('#company_name').prop('required', false);
                $('#full_name').prop('required', false);

                if (supplierType === 'morale') {
                    $('#company-name-section').show();
                    $('#representative-section').show();
                    $('#company-info-section').show();
                    $('#company_name').prop('required', true);
                } else if (supplierType === 'physique') {
                    $('#full_name').prop('required', true);
                }
            });

            // Initialize based on selected value
            $('#supplier_type').trigger('change');

            // Supplier Form Submit
            $('#supplierForm').submit(function(e) {
                e.preventDefault();

                var supplierType = $('#supplier_type').val();

                // Validate required fields based on supplier type
                if (supplierType === 'morale' && !$('#company_name').val()) {
                    showToast('error', 'Le nom de l\'entreprise est requis pour les fournisseurs moraux');
                    return;
                }

                if (supplierType === 'physique' && !$('#full_name').val()) {
                    showToast('error', 'Le nom complet est requis pour les fournisseurs physiques');
                    return;
                }

                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('suppliers.store') }}",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href = "{{ route('suppliers.index') }}";
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
                            errorMessage = xhr.responseJSON?.message ||
                                'Une erreur est survenue';
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
