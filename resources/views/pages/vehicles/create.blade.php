@extends('layouts.app')

@section('title', 'Nouveau Véhicule')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Nouveau Véhicule</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('vehicles.index') }}">
                                        Véhicules
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

        <div class="card">
            <div class="card-header card-header-custom">
                <h5 class="card-title mb-0" style="color:white">
                    <i class="fas fa-plus-circle me-2"></i>Ajouter un Véhicule
                </h5>
            </div>
            <div class="card-body">
                <form id="vehicleForm">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Type *</label>
                                <select class="form-control" name="type" required>
                                    <option value="voiture">Voiture</option>
                                    <option value="camion">Camion</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">N° Immatriculation *</label>
                                <input type="text" class="form-control" name="registration_number" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Date d'achat</label>
                                <input type="date" class="form-control" name="purchase_date">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Kilométrage actuel</label>
                                <input type="number" class="form-control" name="current_mileage" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Statut *</label>
                                <select class="form-control" name="status" required>
                                    <option value="active">Actif</option>
                                    <option value="maintenance">En maintenance</option>
                                    <option value="inactive">Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Documents</h6>
                        </div>
                        <div class="card-body">
                            <div id="documentsContainer">
                                @foreach ($documentTypes as $type)
                                    <div class="document-row mb-4 p-3 border rounded">
                                        <h6 class="mb-3">{{ $type->type_name }}</h6>
                                        <input type="hidden"
                                            name="documents[{{ $type->document_type_id }}][document_type_id]"
                                            value="{{ $type->document_type_id }}">

                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small">N° Document</label>
                                                <input type="text" class="form-control"
                                                    name="documents[{{ $type->document_type_id }}][document_number]">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small">Date de début</label>
                                                <input type="date" class="form-control start-date"
                                                    name="documents[{{ $type->document_type_id }}][start_date]"
                                                    data-default-duration="{{ $type->default_duration_days }}"
                                                    data-reminder-days="{{ $type->reminder_days_before }}">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small">Date de fin</label>
                                                <input type="date" class="form-control end-date"
                                                    name="documents[{{ $type->document_type_id }}][end_date]">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Autorité émettrice</label>
                                                <input type="text" class="form-control"
                                                    name="documents[{{ $type->document_type_id }}][issuing_authority]">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Notes</label>
                                                <input type="text" class="form-control"
                                                    name="documents[{{ $type->document_type_id }}][notes]">
                                            </div>
                                            <div class="col-12">
                                                <small class="text-muted duration-note"></small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-group">
                            <label class="form-label">Notes générales</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Only calculate the end date once a start date is chosen for that
            // document row; otherwise just show the type's duration/reminder as a note.
            $('#documentsContainer').on('change', '.start-date', function() {
                const row = $(this).closest('.document-row');
                const defaultDuration = $(this).data('default-duration');
                const reminderDays = $(this).data('reminder-days');
                const startDateVal = $(this).val();
                let infoParts = [];

                if (defaultDuration && defaultDuration > 0 && startDateVal) {
                    const start = new Date(startDateVal);
                    const end = new Date(start);
                    end.setDate(end.getDate() + parseInt(defaultDuration));

                    const year = end.getFullYear();
                    const month = String(end.getMonth() + 1).padStart(2, '0');
                    const day = String(end.getDate()).padStart(2, '0');
                    row.find('.end-date').val(`${year}-${month}-${day}`);

                    infoParts.push(`Durée: ${defaultDuration} jours (fin estimée le ${day}/${month}/${year})`);
                } else if (defaultDuration && defaultDuration > 0) {
                    infoParts.push(`Durée par défaut: ${defaultDuration} jours — sélectionnez une date de début pour calculer la date de fin`);
                }

                if (reminderDays) {
                    infoParts.push(`Rappel programmé ${reminderDays} jours avant expiration`);
                }

                row.find('.duration-note').text(infoParts.join(' — '));
            });

            $('#vehicleForm').submit(function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                $.ajax({
                    url: "{{ route('vehicles.store') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href = "{{ route('vehicles.index') }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMessage = xhr.responseJSON?.message ||
                            'Erreur lors de l\'enregistrement';

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
        });

        function showToast(type, message) {
            var toast = $('<div class="toast align-items-center text-white bg-' +
                (type === 'success' ? 'success' : 'danger') +
                ' border-0" role="alert">' +
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
    </script>
@endpush
