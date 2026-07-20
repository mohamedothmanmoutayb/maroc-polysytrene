@extends('layouts.app')

@section('title', 'Modifier Chèque')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Chèque</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('checks.index') }}">
                                        Chèques
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
                            <i class="fas fa-edit me-2"></i>Modifier le Chèque: {{ $check->check_number }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="checkForm" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="check_number" class="form-label">Numéro Chèque *</label>
                                        <input type="text" class="form-control" id="check_number" name="check_number"
                                            value="{{ $check->check_number }}" required maxlength="50">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="check_type" class="form-label">Type de Chèque *</label>
                                        <select class="form-control" id="check_type" name="check_type" required>
                                            <option value="">Sélectionner...</option>
                                            <option value="entreprise" {{ $check->check_type == 'entreprise' ? 'selected' : '' }}>
                                                Entreprise (Chèque émis par l'entreprise)</option>
                                            <option value="client" {{ $check->check_type == 'client' ? 'selected' : '' }}>
                                                Client (Chèque reçu d'un client)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4" id="client_id_row" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="client_id" class="form-label">Client</label>
                                        <select class="form-control" id="client_id" name="client_id">
                                            <option value="">Sélectionner...</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->client_id }}" {{ $check->client_id == $client->client_id ? 'selected' : '' }}>
                                                    {{ $client->display_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Optionnel — permet de rattacher ce chèque à un client.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount" class="form-label">Montant * (DH)</label>
                                        <input type="number" class="form-control" id="amount" name="amount" required
                                            min="0.01" step="0.01"
                                            value="{{ number_format($check->amount, 2, '.', '') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bank_name" class="form-label">Banque *</label>
                                        <input type="text" class="form-control" id="bank_name" name="bank_name" required
                                            maxlength="100" value="{{ $check->bank_name }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="account_holder" class="form-label">Tireur du Chèque *</label>
                                        <input type="text" class="form-control" id="account_holder" name="account_holder"
                                            required maxlength="200" value="{{ $check->account_holder }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="issue_date" class="form-label">Date d'Émission *</label>
                                        <input type="date" class="form-control" id="issue_date" name="issue_date"
                                            value="{{ $check->issue_date->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="deposit_date" class="form-label">Date d'échéance</label>
                                        <input type="date" class="form-control" id="deposit_date" name="deposit_date"
                                            value="{{ $check->deposit_date ? $check->deposit_date->format('Y-m-d') : '' }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="clearing_date" class="form-label">Date d'Encaissement</label>
                                        <input type="date" class="form-control" id="clearing_date"
                                            name="clearing_date"
                                            value="{{ $check->clearing_date ? $check->clearing_date->format('Y-m-d') : '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status" class="form-label">Statut *</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="pending" {{ $check->status == 'pending' ? 'selected' : '' }}>En
                                                attente</option>
                                            <option value="deposited"
                                                {{ $check->status == 'deposited' ? 'selected' : '' }}>Déposé</option>
                                            <option value="cleared" {{ $check->status == 'cleared' ? 'selected' : '' }}>
                                                Encaissé</option>
                                            <option value="bounced" {{ $check->status == 'bounced' ? 'selected' : '' }}>
                                                Rebondi</option>
                                            <option value="cancelled"
                                                {{ $check->status == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="check_image" class="form-label">Image du Chèque</label>
                                        <input type="file" class="form-control" id="check_image" name="check_image"
                                            accept="image/*">
                                        <small class="form-text text-muted">Formats: jpeg, png, jpg, gif (max: 2MB)</small>

                                        @if ($check->check_image)
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $check->check_image) }}"
                                                    alt="Image du chèque" style="max-height: 80px; max-width: 120px;"
                                                    class="img-thumbnail">
                                                <small class="text-muted d-block">Image actuelle. Téléchargez une nouvelle
                                                    image pour la remplacer.</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Statut Actif</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_active"
                                                name="is_active" {{ $check->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                {{ $check->is_active ? 'Actif' : 'Inactif' }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ $check->notes }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('checks.show', $check->check_id) }}" class="btn btn-info">
                                    <i class="fas fa-eye me-1"></i> Voir Détails
                                </a>
                                <a href="{{ route('checks.index') }}" class="btn btn-secondary">
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
            // Show the client selector only for client-type checks
            function toggleClientField() {
                if ($('#check_type').val() === 'client') {
                    $('#client_id_row').show();
                } else {
                    $('#client_id_row').hide();
                    $('#client_id').val('');
                }
            }
            $('#check_type').on('change', toggleClientField);
            toggleClientField();

            // Validate check image size
            $('#check_image').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const fileSize = file.size / 1024 / 1024; // Convert to MB
                    if (fileSize > 2) {
                        showToast('error', 'La taille de l\'image ne doit pas dépasser 2MB');
                        $(this).val('');
                    }
                }
            });

            // Check Form Submit
            $('#checkForm').submit(function(e) {
                e.preventDefault();

                // Show loading
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                $.ajax({
                    url: "{{ route('checks.update', $check->check_id) }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('checks.show', $check->check_id) }}";
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
                    (type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger') +
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
