@extends('layouts.app')

@section('title', 'Modifier Véhicule - ' . $vehicle->registration_number)

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Véhicule</h4>
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
                                        {{ $vehicle->registration_number }}
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
                    <i class="fas fa-edit me-2"></i>Modifier le Véhicule
                </h5>
            </div>
            <div class="card-body">
                <form id="vehicleForm">
                    @csrf
                    @method('PUT')

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Type *</label>
                                <select class="form-control" name="type" required>
                                    <option value="voiture" {{ $vehicle->type == 'voiture' ? 'selected' : '' }}>Voiture
                                    </option>
                                    <option value="camion" {{ $vehicle->type == 'camion' ? 'selected' : '' }}>Camion
                                    </option>
                                    <option value="machine" {{ $vehicle->type == 'machine' ? 'selected' : '' }}>Machine
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">N° Immatriculation *</label>
                                <input type="text" class="form-control" name="registration_number"
                                    value="{{ $vehicle->registration_number }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Date d'achat</label>
                                <input type="date" class="form-control" name="purchase_date"
                                    value="{{ $vehicle->purchase_date ? $vehicle->purchase_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Kilométrage actuel</label>
                                <input type="number" class="form-control" name="current_mileage"
                                    value="{{ $vehicle->current_mileage }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Statut *</label>
                                <select class="form-control" name="status" required>
                                    <option value="active" {{ $vehicle->status == 'active' ? 'selected' : '' }}>Actif
                                    </option>
                                    <option value="maintenance" {{ $vehicle->status == 'maintenance' ? 'selected' : '' }}>
                                        En maintenance</option>
                                    <option value="inactive" {{ $vehicle->status == 'inactive' ? 'selected' : '' }}>Inactif
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Documents (Mettez à jour avec les nouvelles informations)</h6>
                            <small class="text-muted">Les anciennes versions seront conservées dans l'historique</small>
                        </div>
                        <div class="card-body">
                            <div id="documentsContainer">
                                @foreach ($documentTypes as $type)
                                    @php
                                        $currentDoc = $vehicle->getCurrentDocument($type->document_type_id);
                                    @endphp
                                    <div class="document-row mb-4 p-3 border rounded">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">{{ $type->type_name }}</h6>
                                            @if ($currentDoc)
                                                <small class="text-muted">
                                                    Actuel:
                                                    {{ $currentDoc->end_date ? $currentDoc->end_date->format('d/m/Y') : 'N/A' }}
                                                    {!! $currentDoc->status_badge !!}
                                                </small>
                                                <button type="button" class="btn btn-sm btn-outline-info view-history"
                                                    data-document-type="{{ $type->document_type_id }}"
                                                    data-vehicle-id="{{ $vehicle->vehicle_id }}"
                                                    data-type-name="{{ $type->type_name }}">
                                                    <i class="fas fa-history me-1"></i>Historique
                                                </button>
                                            @endif
                                        </div>
                                        <input type="hidden"
                                            name="documents[{{ $type->document_type_id }}][document_type_id]"
                                            value="{{ $type->document_type_id }}">

                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small">N° Document</label>
                                                <input type="text" class="form-control"
                                                    name="documents[{{ $type->document_type_id }}][document_number]"
                                                    value="{{ $currentDoc->document_number ?? '' }}">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small">Date de début</label>
                                                <input type="date" class="form-control start-date"
                                                    name="documents[{{ $type->document_type_id }}][start_date]"
                                                    value="{{ $currentDoc->start_date ? $currentDoc->start_date->format('Y-m-d') : '' }}">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small">Date de fin *</label>
                                                <input type="date" class="form-control end-date"
                                                    name="documents[{{ $type->document_type_id }}][end_date]"
                                                    value="{{ $currentDoc->end_date ? $currentDoc->end_date->format('Y-m-d') : '' }}"
                                                    required>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Autorité émettrice</label>
                                                <input type="text" class="form-control"
                                                    name="documents[{{ $type->document_type_id }}][issuing_authority]"
                                                    value="{{ $currentDoc->issuing_authority ?? '' }}">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Notes</label>
                                                <input type="text" class="form-control"
                                                    name="documents[{{ $type->document_type_id }}][notes]"
                                                    value="{{ $currentDoc->notes ?? '' }}">
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
                            <textarea class="form-control" name="notes" rows="3">{{ $vehicle->notes }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Mettre à jour
                        </button>
                        <a href="{{ route('vehicles.show', $vehicle->vehicle_id) }}" class="btn btn-info">
                            <i class="fas fa-eye me-1"></i> Voir détails
                        </a>
                        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Document History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-history me-2"></i>Historique des documents
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 id="historyModalTitle" class="mb-3"></h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="historyTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>N° Document</th>
                                    <th>Autorité</th>
                                    <th>Statut</th>
                                    <th>Créé le</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">Chargement...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // View document history
            $(document).on('click', '.view-history', function() {
                var documentTypeId = $(this).data('document-type');
                var vehicleId = $(this).data('vehicle-id');
                var typeName = $(this).data('type-name');

                $('#historyModalTitle').text('Historique - ' + typeName);
                $('#historyTableBody').html(
                    '<tr><td colspan="6" class="text-center">Chargement...</td></tr>');

                $.ajax({
                    url: "{{ url('vehicles') }}/" + vehicleId + "/documents/" + documentTypeId +
                        "/history",
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            var html = '';
                            $.each(response.data, function(index, doc) {
                                var statusBadge = '';
                                if (doc.is_current) {
                                    statusBadge =
                                        '<span class="badge bg-success">Actuel</span>';
                                } else if (doc.end_date && new Date(doc.end_date) <
                                    new Date()) {
                                    statusBadge =
                                        '<span class="badge bg-danger">Expiré</span>';
                                } else {
                                    statusBadge =
                                        '<span class="badge bg-secondary">Ancien</span>';
                                }

                                html += '<tr>' +
                                    '<td>' + (doc.start_date || '-') + '</td>' +
                                    '<td>' + (doc.end_date || '-') + '</td>' +
                                    '<td>' + (doc.document_number || '-') + '</td>' +
                                    '<td>' + (doc.issuing_authority || '-') + '</td>' +
                                    '<td class="text-center">' + statusBadge + '</td>' +
                                    '<td>' + new Date(doc.created_at)
                                    .toLocaleDateString('fr-FR') + '</td>' +
                                    '</tr>';
                            });
                            $('#historyTableBody').html(html);
                        } else {
                            $('#historyTableBody').html(
                                '<tr><td colspan="6" class="text-center text-muted">Aucun historique trouvé</td></tr>'
                                );
                        }
                    },
                    error: function() {
                        $('#historyTableBody').html(
                            '<tr><td colspan="6" class="text-center text-danger">Erreur lors du chargement</td></tr>'
                            );
                    }
                });

                var modal = new bootstrap.Modal(document.getElementById('historyModal'));
                modal.show();
            });

            // Form submission
            $('#vehicleForm').submit(function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Mise à jour...');

                $.ajax({
                    url: "{{ route('vehicles.update', $vehicle->vehicle_id) }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('vehicles.show', $vehicle->vehicle_id) }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMessage = xhr.responseJSON?.message ||
                            'Erreur lors de la mise à jour';

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
