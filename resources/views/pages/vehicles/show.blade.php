@extends('layouts.app')

@section('title', 'Détails Véhicule - ' . $vehicle->registration_number)

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails Véhicule</h4>
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

        <div class="row">
            <!-- Informations Générales -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Informations Générales
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">Type:</th>
                                <td>{!! $vehicle->type_badge !!}</td>
                            </tr>
                            <tr>
                                <th>N° Immatriculation:</th>
                                <td><strong>{{ $vehicle->registration_number }}</strong></td>
                            </tr>
                            <tr>
                                <th>Date d'achat:</th>
                                <td>{{ $vehicle->purchase_date ? $vehicle->purchase_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Statut:</th>
                                <td>{!! $vehicle->status_badge !!}</td>
                            </tr>
                            <tr>
                                <th>Kilométrage:</th>
                                <td>{{ number_format($vehicle->current_mileage, 0) }} km</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Documents Actuels -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>Documents et Échéances
                        </h6>
                        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal"
                            data-bs-target="#documentModal">
                            <i class="fas fa-plus me-1"></i> Ajouter un Document
                        </button>
                    </div>
                    <div class="card-body">
                        @php
                            $currentDocs = $vehicle->documents->where('is_current', true);
                        @endphp

                        @if ($currentDocs->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Document</th>
                                            <th>N° Document</th>
                                            <th>Date début</th>
                                            <th>Date fin</th>
                                            <th>Jours restants</th>
                                            <th>Statut</th>
                                            <th width="10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($currentDocs as $doc)
                                            @php
                                                $daysLeft = $doc->end_date
                                                    ? \Carbon\Carbon::now()->diffInDays($doc->end_date, false)
                                                    : 0;
                                                $statusClass = '';
                                                $statusText = '';

                                                if (!$doc->end_date) {
                                                    $statusClass = 'secondary';
                                                    $statusText = 'Non renseigné';
                                                } elseif ($doc->end_date < \Carbon\Carbon::now()) {
                                                    $statusClass = 'danger';
                                                    $statusText = 'Expiré';
                                                } elseif ($doc->end_date <= \Carbon\Carbon::now()->addDays(30)) {
                                                    $statusClass = 'warning';
                                                    $statusText = 'Expire bientôt';
                                                } else {
                                                    $statusClass = 'success';
                                                    $statusText = 'Valide';
                                                }
                                            @endphp
                                            <tr>
                                                <td class="fw-bold">{{ $doc->documentType->type_name }}</td>
                                                <td>{{ $doc->document_number ?? '-' }}</td>
                                                <td>{{ $doc->start_date ? $doc->start_date->format('d/m/Y') : '-' }}</td>
                                                <td>{{ $doc->end_date ? $doc->end_date->format('d/m/Y') : '-' }}</td>
                                                <td class="text-center">
                                                    @if ($doc->end_date && $daysLeft > 0)
                                                        <span class="badge bg-{{ $statusClass }}">
                                                            {{ $daysLeft }} jours
                                                        </span>
                                                    @elseif($doc->end_date && $daysLeft <= 0)
                                                        <span class="badge bg-danger">Expiré</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-info view-history"
                                                        data-document-type="{{ $doc->document_type_id }}"
                                                        data-vehicle-id="{{ $vehicle->vehicle_id }}"
                                                        data-type-name="{{ $doc->documentType->type_name }}">
                                                        <i class="fas fa-history"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Aucun document enregistré pour ce véhicule.
                                <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal"
                                    data-bs-target="#documentModal">
                                    <i class="fas fa-plus me-1"></i> Ajouter un document
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des Documents -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-history me-2"></i>Historique Complet des Documents
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="history-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>N° Document</th>
                                        <th>Date début</th>
                                        <th>Date fin</th>
                                        <th>Autorité</th>
                                        <th>Statut</th>
                                        <th>Notes</th>
                                        <th>Créé le</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vehicle->documents->sortByDesc('created_at') as $doc)
                                        @php
                                            $statusClass = '';
                                            $statusText = '';

                                            if (!$doc->end_date) {
                                                $statusClass = 'secondary';
                                                $statusText = 'Non renseigné';
                                            } elseif ($doc->end_date < \Carbon\Carbon::now()) {
                                                $statusClass = 'danger';
                                                $statusText = 'Expiré';
                                            } elseif ($doc->end_date <= \Carbon\Carbon::now()->addDays(30)) {
                                                $statusClass = 'warning';
                                                $statusText = 'Expire bientôt';
                                            } else {
                                                $statusClass = 'success';
                                                $statusText = 'Valide';
                                            }
                                        @endphp
                                        <tr @if ($doc->is_current) class="table-success" @endif>
                                            <td class="fw-bold">
                                                {{ $doc->documentType->type_name }}
                                                @if ($doc->is_current)
                                                    <span class="badge bg-success ms-1">Actuel</span>
                                                @endif
                                            </td>
                                            <td>{{ $doc->document_number ?? '-' }}</td>
                                            <td>{{ $doc->start_date ? $doc->start_date->format('d/m/Y') : '-' }}</td>
                                            <td>{{ $doc->end_date ? $doc->end_date->format('d/m/Y') : '-' }}</td>
                                            <td>{{ $doc->issuing_authority ?? '-' }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                            </td>
                                            <td>{{ $doc->notes ?? '-' }}</td>
                                            <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($vehicle->notes)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-sticky-note me-2"></i>Notes
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $vehicle->notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="d-flex gap-2 mt-4">
            @can('edit_vehicles')
            <a href="{{ route('vehicles.edit', $vehicle->vehicle_id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Modifier
            </a>
            @endcan
            <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <!-- Modal: Ajouter un Document -->
    <div class="modal fade" id="documentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Ajouter un Document
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="documentForm">
                    @csrf
                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->vehicle_id }}">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Type de document *</label>
                                <select class="form-control" name="document_type_id" id="documentTypeSelect" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach (\App\Models\VehicleDocumentType::active()->orderBy('sort_order')->get() as $type)
                                        <option value="{{ $type->document_type_id }}"
                                            data-default-duration="{{ $type->default_duration_days }}"
                                            data-reminder-days="{{ $type->reminder_days_before }}">
                                            {{ $type->type_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">N° Document</label>
                                <input type="text" class="form-control" name="document_number">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Date de début</label>
                                <input type="date" class="form-control" name="start_date" id="startDate">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date de fin *</label>
                                <input type="date" class="form-control" name="end_date" id="endDate" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Autorité émettrice</label>
                                <input type="text" class="form-control" name="issuing_authority">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <input type="text" class="form-control" name="notes">
                            </div>
                        </div>
                        <div class="alert alert-info" id="documentTypeInfo">
                            <i class="fas fa-info-circle me-2"></i>
                            Sélectionnez un type de document pour voir les informations
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Historique par Type -->
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
                    <h6 id="historyModalTitle" class="mb-3 fw-bold"></h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
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
                                    <td colspan="6" class="text-center text-muted">Chargement...</td>
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

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <style>
        .table-success {
            background-color: #d4edda !important;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .card-header {
            border-bottom: none;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .document-badge {
            display: inline-block;
            padding: 4px 8px;
            margin: 2px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .modal-lg {
            max-width: 800px;
        }

        .toast {
            min-width: 300px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable for history
            $('#history-table').DataTable({ paging: false, lengthChange: false, 
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                },
                order: [
                    [7, 'desc']
                ],
                pageLength: 10,
                responsive: true
            });

            // Auto-calculate end date when document type is selected
            $('#documentTypeSelect').change(function() {
                const selectedOption = $(this).find('option:selected');
                const defaultDuration = selectedOption.data('default-duration');
                const reminderDays = selectedOption.data('reminder-days');
                const typeName = selectedOption.text();

                if (defaultDuration && defaultDuration > 0) {
                    const startDate = $('#startDate').val();
                    const today = new Date();
                    const start = startDate ? new Date(startDate) : today;

                    const endDate = new Date(start);
                    endDate.setDate(endDate.getDate() + parseInt(defaultDuration));

                    const year = endDate.getFullYear();
                    const month = String(endDate.getMonth() + 1).padStart(2, '0');
                    const day = String(endDate.getDate()).padStart(2, '0');
                    $('#endDate').val(`${year}-${month}-${day}`);
                }

                if (reminderDays) {
                    $('#documentTypeInfo').html(`
                        <i class="fas fa-bell me-2"></i>
                        <strong>${typeName}</strong><br>
                        <small class="text-muted">Rappel programmé ${reminderDays} jours avant expiration</small>
                    `);
                } else {
                    $('#documentTypeInfo').html(`
                        <i class="fas fa-file-alt me-2"></i>
                        <strong>${typeName}</strong><br>
                        <small class="text-muted">Aucun rappel configuré pour ce type de document</small>
                    `);
                }
            });

            // Update end date when start date changes
            $('#startDate').change(function() {
                const selectedOption = $('#documentTypeSelect').find('option:selected');
                const defaultDuration = selectedOption.data('default-duration');

                if (defaultDuration && defaultDuration > 0 && $(this).val()) {
                    const startDate = new Date($(this).val());
                    const endDate = new Date(startDate);
                    endDate.setDate(endDate.getDate() + parseInt(defaultDuration));

                    const year = endDate.getFullYear();
                    const month = String(endDate.getMonth() + 1).padStart(2, '0');
                    const day = String(endDate.getDate()).padStart(2, '0');
                    $('#endDate').val(`${year}-${month}-${day}`);
                }
            });

            // Document form submission
            $('#documentForm').submit(function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                $.ajax({
                    url: "{{ route('vehicles.documents.store') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#documentModal').modal('hide');
                            showToast('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        let errorMessage = xhr.responseJSON?.message ||
                            'Erreur lors de l\'enregistrement';

                        if (errors) {
                            errorMessage = '';
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        }

                        showToast('error', errorMessage);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // View document history
            $(document).on('click', '.view-history', function() {
                var documentTypeId = $(this).data('document-type');
                var vehicleId = $(this).data('vehicle-id');
                var typeName = $(this).data('type-name');

                $('#historyModalTitle').text('Historique - ' + typeName);
                $('#historyTableBody').html(
                    '<tr><td colspan="6" class="text-center text-muted">Chargement...</td></tr>');

                $.ajax({
                    url: "{{ url('vehicles') }}/" + vehicleId + "/documents/" + documentTypeId +
                        "/history",
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            var html = '';
                            $.each(response.data, function(index, doc) {
                                var statusBadge = '';
                                var statusClass = '';

                                if (doc.is_current) {
                                    statusClass = 'success';
                                    statusBadge =
                                        '<span class="badge bg-success">Actuel</span>';
                                } else if (doc.end_date && new Date(doc.end_date) <
                                    new Date()) {
                                    statusClass = 'danger';
                                    statusBadge =
                                        '<span class="badge bg-danger">Expiré</span>';
                                } else if (doc.end_date && new Date(doc.end_date) <=
                                    new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)) {
                                    statusClass = 'warning';
                                    statusBadge =
                                        '<span class="badge bg-warning text-dark">Expire bientôt</span>';
                                } else {
                                    statusClass = 'secondary';
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
                                    .toLocaleDateString('fr-FR') + ' ' + new Date(doc
                                        .created_at).toLocaleTimeString('fr-FR') +
                                    '</td>' +
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
                        showToast('error', 'Erreur lors du chargement de l\'historique');
                    }
                });

                var modal = new bootstrap.Modal(document.getElementById('historyModal'));
                modal.show();
            });
        });

        // Toast notification function
        function showToast(type, message) {
            var toastId = 'toast-' + Date.now();
            var bgColor = type === 'success' ? 'bg-success' : (type === 'warning' ? 'bg-warning' : 'bg-danger');

            var toast = $('<div id="' + toastId + '" class="toast align-items-center text-white ' + bgColor +
                ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                '<div class="d-flex">' +
                '<div class="toast-body">' + message + '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
                '</div>' +
                '</div>');

            $('#toast-container').append(toast);
            var bsToast = new bootstrap.Toast(toast[0], {
                autohide: true,
                delay: 5000
            });
            bsToast.show();

            toast.on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }
    </script>
@endpush
