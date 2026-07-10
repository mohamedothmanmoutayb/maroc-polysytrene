@extends('layouts.app')

@section('title', 'Nouveau Client')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Nouveau Client</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('clients.index') }}">
                                        Clients
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
                            <i class="fas fa-plus-circle me-2"></i>Créer un Nouveau Client
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="clientForm">
                            @csrf

                            <!-- Client Type Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="person_type" class="form-label">Type de Personne *</label>
                                        <select class="form-control" id="person_type" name="person_type" required>
                                            <option value="">Sélectionner un type</option>
                                            <option value="physique">Physique</option>
                                            <option value="morale">Morale</option>
                                            <option value="special">Spécial</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="client_type" class="form-label">Type de Client *</label>
                                        <select class="form-control" id="client_type" name="client_type" required>
                                            <option value="">Sélectionner</option>
                                            <option value="client">Client</option>
                                            <option value="commerciale">Commerciale</option>
                                            <option value="grossiste">Grossiste</option>
                                            <option value="special">Spécial</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Name Section (changes based on person_type) -->
                            <div class="row mb-4">
                                <div class="col-md-6" id="name-section">
                                    <div class="form-group">
                                        <label for="name" class="form-label">Nom Complet *</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            maxlength="100" placeholder="Ex: Ahmed Benali">
                                    </div>
                                </div>
                                <div class="col-md-6" id="entreprise-section" style="display: none;">
                                    <div class="form-group">
                                        <label for="entreprise_name" class="form-label">Nom de l'Entreprise *</label>
                                        <input type="text" class="form-control" id="entreprise_name"
                                            name="entreprise_name" maxlength="100" placeholder="Ex: SARL MATELAS MAROC">
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-label">Téléphone</label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            maxlength="20" placeholder="Ex: 0612345678">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            maxlength="100" placeholder="Ex: client@entreprise.com">
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="address" class="form-label">Adresse *</label>
                                        <textarea class="form-control" id="address" name="address" rows="2" placeholder="Adresse complète..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Identification Numbers (changes based on person_type) -->
                            <div class="row mb-4">
                                <div class="col-md-6" id="cin-section">
                                    <div class="form-group">
                                        <label for="cin" class="form-label">CIN *</label>
                                        <input type="text" class="form-control" id="cin" name="cin"
                                            maxlength="20" placeholder="Ex: AB123456">
                                    </div>
                                </div>
                                <div class="col-md-6" id="ice-section" style="display: none;">
                                    <div class="form-group">
                                        <label for="ice" class="form-label">ICE</label>
                                        <input type="text" class="form-control" id="ice" name="ice"
                                            maxlength="20" placeholder="Ex: 001234567890123">
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Fields for Morale -->
                            <div class="row mb-4" id="morale-fields" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="rc" class="form-label">Registre de Commerce (RC)</label>
                                        <input type="text" class="form-control" id="rc" name="rc"
                                            maxlength="20" placeholder="Ex: 12345">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="patente" class="form-label">Patente</label>
                                        <input type="text" class="form-control" id="patente" name="patente"
                                            maxlength="20" placeholder="Ex: 123456789">
                                    </div>
                                </div>
                            </div>

                            <!-- Credit Limit -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="credit_limit" class="form-label">Plafond de Crédit (DH) *</label>
                                        <input type="number" class="form-control" id="credit_limit" name="credit_limit"
                                            value="0" min="0" step="0.01" required>
                                        <small class="form-text text-muted">Montant maximum non dépassable</small>
                                    </div>
                                </div>
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

                            <!-- Notes -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                            placeholder="Notes ou remarques sur le client..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Upload Reminder -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Documentation requise:</strong> Après la création du client, vous pourrez uploader
                                les documents nécessaires (CIN pour les physiques, ICE/RC/Patente pour les morales)
                                depuis la page de détails du client.
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('clients.index') }}" class="btn btn-secondary">
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
            function togglePersonTypeFields(personType) {
                if (personType === 'morale') {
                    // Show morale fields
                    $('#entreprise-section').show();
                    $('#ice-section').show();
                    $('#morale-fields').show();

                    // Hide physique fields
                    $('#name-section').hide();
                    $('#cin-section').hide();

                    // Set required attributes
                    $('#entreprise_name').prop('required', true);
                    $('#ice').prop('required', false);
                    $('#name').prop('required', false);
                    $('#cin').prop('required', false);
                } else if (personType === 'physique') {
                    // Show physique fields
                    $('#name-section').show();
                    $('#cin-section').show();

                    // Hide morale fields
                    $('#entreprise-section').hide();
                    $('#ice-section').hide();
                    $('#morale-fields').hide();

                    // Set required attributes
                    $('#name').prop('required', true);
                    $('#cin').prop('required', false);
                    $('#entreprise_name').prop('required', false);
                    $('#ice').prop('required', false);
                } else {
                    // Hide all fields if no type selected
                    $('#name-section, #entreprise-section, #cin-section, #ice-section, #morale-fields').hide();
                }
            }
            // Initialize on page load
            var initialPersonType = $('#person_type').val();
            togglePersonTypeFields(initialPersonType);

            // Handle person type change
            $('#person_type').change(function() {
                var personType = $(this).val();
                togglePersonTypeFields(personType);
            });

            // Client Form Submit
            $('#clientForm').submit(function(e) {
                e.preventDefault();

                var personType = $('#person_type').val();
                var clientType = $('#client_type').val();

                // Validate required fields
                var errors = [];

                if (!personType) {
                    errors.push('Le type de personne est requis');
                }

                if (!clientType) {
                    errors.push('Le type de client est requis');
                }

                if (personType === 'physique') {
                    if (!$('#name').val()) errors.push(
                        'Le nom complet est requis pour les clients physiques');
                } else if (personType === 'morale') {
                    if (!$('#entreprise_name').val()) errors.push(
                        'Le nom de l\'entreprise est requis pour les clients moraux');
                }

                if (errors.length > 0) {
                    showToast('error', errors.join('<br>'));
                    return;
                }

                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('clients.store') }}",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href = "{{ route('clients.index') }}";
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
