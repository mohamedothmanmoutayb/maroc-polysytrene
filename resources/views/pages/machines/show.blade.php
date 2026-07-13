@extends('layouts.app')

@section('title', 'Détails Machine - ' . $machine->name)

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails Machine</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('machines.index') }}">
                                        Machines
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        {{ $machine->name }}
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
                                <th width="40%">Nom:</th>
                                <td><strong>{{ $machine->name }}</strong></td>
                            </tr>
                            <tr>
                                <th>N° Série:</th>
                                <td><strong>{{ $machine->serial_number }}</strong></td>
                            </tr>
                            <tr>
                                <th>Modèle:</th>
                                <td>{{ $machine->model ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Fabricant:</th>
                                <td>{{ $machine->manufacturer ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Date d'achat:</th>
                                <td>{{ $machine->purchase_date ? $machine->purchase_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Statut:</th>
                                <td>{!! $machine->status_badge !!}</td>
                            </tr>
                            <tr>
                                <th>Heures de fonctionnement:</th>
                                <td>{{ number_format($machine->operating_hours, 0) }} heures</td>
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
                            $currentDocs = $machine->documents->where('is_current', true);
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
                                                        data-machine-id="{{ $machine->machine_id }}"
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
                                Aucun document enregistré pour cette machine.
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
                                    @foreach ($machine->documents->sortByDesc('created_at') as $doc)
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

        <!-- Maintenance Préventive -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-tools me-2"></i>Maintenance Préventive
                        </h6>
                        @can('create_machine_maintenance')
                            <button type="button" class="btn btn-sm btn-dark" onclick="openCreateMaintenance()">
                                <i class="fas fa-plus me-1"></i> Nouveau Programme
                            </button>
                        @endcan
                    </div>
                    <div class="card-body">
                        @if ($machine->maintenanceSchedules->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Programme</th>
                                            <th>Intervalle</th>
                                            <th>Dernière effectuée</th>
                                            <th>Prochaine échéance</th>
                                            <th>Statut</th>
                                            <th width="20%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($machine->maintenanceSchedules as $schedule)
                                            <tr>
                                                <td class="fw-bold">
                                                    {{ $schedule->label }}
                                                    @if (!$schedule->is_active)
                                                        <span class="badge bg-secondary ms-1">Inactif</span>
                                                    @endif
                                                </td>
                                                <td>Tous les {{ $schedule->interval_days }} jours</td>
                                                <td>{{ $schedule->last_completed_at ? $schedule->last_completed_at->format('d/m/Y') : '-' }}</td>
                                                <td>{{ $schedule->next_due_at->format('d/m/Y') }}</td>
                                                <td>{!! $schedule->status_badge !!}</td>
                                                <td>
                                                    <div class="btn-group">
                                                        @can('complete_machine_maintenance')
                                                            <button type="button" class="btn btn-sm btn-success"
                                                                onclick="completeMaintenance({{ $schedule->id }}, '{{ addslashes($schedule->label) }}')"
                                                                title="Confirmer effectuée">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        @endcan
                                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                            onclick="showMaintenanceHistory({{ $schedule->id }}, '{{ addslashes($schedule->label) }}')"
                                                            title="Historique">
                                                            <i class="fas fa-history"></i>
                                                        </button>
                                                        @can('edit_machine_maintenance')
                                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                                onclick="editMaintenance({{ $schedule->id }})" title="Modifier">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        @endcan
                                                        @can('delete_machine_maintenance')
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteMaintenance({{ $schedule->id }}, '{{ addslashes($schedule->label) }}')"
                                                                title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Aucun programme de maintenance préventive pour cette machine.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($machine->notes)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-sticky-note me-2"></i>Notes
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $machine->notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="d-flex gap-2 mt-4">
            @can('edit_machines')
            <a href="{{ route('machines.edit', $machine->machine_id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Modifier
            </a>
            @endcan
            <a href="{{ route('machines.index') }}" class="btn btn-secondary">
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
                    <input type="hidden" name="machine_id" value="{{ $machine->machine_id }}">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Type de document *</label>
                                <select class="form-control" name="document_type_id" id="documentTypeSelect" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach (\App\Models\MachineDocumentType::active()->orderBy('sort_order')->get() as $type)
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

    <!-- Modal: Nouveau/Modifier Programme de Maintenance -->
    <div class="modal fade" id="maintenanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="maintenanceModalLabel">
                        <i class="fas fa-tools me-2"></i>Nouveau Programme de Maintenance
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="maintenanceForm">
                    @csrf
                    <input type="hidden" name="machine_id" value="{{ $machine->machine_id }}">
                    <input type="hidden" id="maintenance_id" name="maintenance_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Libellé *</label>
                            <input type="text" class="form-control" name="label" id="maintenance_label"
                                placeholder="Ex: Vidange moteur, Graissage général" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="maintenance_description" rows="2"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Intervalle (jours) *</label>
                                <input type="number" class="form-control" name="interval_days"
                                    id="maintenance_interval" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rappel (jours avant) *</label>
                                <input type="number" class="form-control" name="reminder_days_before"
                                    id="maintenance_reminder" min="0" value="7">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prochaine échéance *</label>
                            <input type="date" class="form-control" name="next_due_at" id="maintenance_next_due"
                                required>
                        </div>
                        <div class="mb-3" id="maintenance_active_wrapper" style="display:none">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                    id="maintenance_is_active" value="1" checked>
                                <label class="form-check-label">Actif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="maintenanceSubmitBtn">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Confirmer Maintenance Effectuée -->
    <div class="modal fade" id="completeMaintenanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Confirmer la maintenance effectuée
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="completeMaintenanceForm">
                    @csrf
                    <div class="modal-body">
                        <p>Confirmez-vous que la maintenance <strong id="complete_maintenance_label"></strong> a bien
                            été effectuée aujourd'hui ?</p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            La prochaine échéance sera automatiquement recalculée à partir d'aujourd'hui.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="complete_notes" rows="2"
                                placeholder="Optionnel"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Confirmer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Historique de Maintenance -->
    <div class="modal fade" id="maintenanceHistoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-history me-2"></i>Historique — <span id="maintenance_history_label"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Effectuée le</th>
                                    <th>Échéance précédente</th>
                                    <th>Nouvelle échéance</th>
                                    <th>Notes</th>
                                    <th>Par</th>
                                </tr>
                            </thead>
                            <tbody id="maintenance_history_body"></tbody>
                        </table>
                    </div>
                    <p class="text-muted text-center mb-0" id="maintenance_history_empty" style="display:none">
                        Aucun historique pour ce programme.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Supprimer Programme de Maintenance -->
    <div class="modal fade" id="deleteMaintenanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Supprimer le programme de maintenance : <strong id="delete_maintenance_label"></strong> ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteMaintenanceBtn">Supprimer</button>
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
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        const maintenanceSchedules = @json($machine->maintenanceSchedules->keyBy('id'));

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

            // Calculate end date only once a start date is chosen; otherwise just
            // show the type's duration/reminder as a note (no premature "today" fallback)
            function updateDocumentTypeInfo() {
                const selectedOption = $('#documentTypeSelect').find('option:selected');
                const defaultDuration = selectedOption.data('default-duration');
                const reminderDays = selectedOption.data('reminder-days');
                const typeName = selectedOption.text().trim();
                const startDateVal = $('#startDate').val();

                if (!typeName) {
                    $('#documentTypeInfo').html(
                        '<i class="fas fa-info-circle me-2"></i>Sélectionnez un type de document pour voir les informations'
                    );
                    return;
                }

                let infoParts = [];

                if (defaultDuration && defaultDuration > 0 && startDateVal) {
                    const start = new Date(startDateVal);
                    const end = new Date(start);
                    end.setDate(end.getDate() + parseInt(defaultDuration));

                    const year = end.getFullYear();
                    const month = String(end.getMonth() + 1).padStart(2, '0');
                    const day = String(end.getDate()).padStart(2, '0');
                    $('#endDate').val(`${year}-${month}-${day}`);

                    infoParts.push(`Durée: ${defaultDuration} jours (fin estimée le ${day}/${month}/${year})`);
                } else if (defaultDuration && defaultDuration > 0) {
                    infoParts.push(`Durée par défaut: ${defaultDuration} jours — sélectionnez une date de début pour calculer la date de fin`);
                }

                if (reminderDays) {
                    infoParts.push(`Rappel programmé ${reminderDays} jours avant expiration`);
                }

                if (infoParts.length === 0) {
                    infoParts.push('Aucune durée ni rappel configurés pour ce type de document');
                }

                $('#documentTypeInfo').html(`
                    <i class="fas fa-bell me-2"></i>
                    <strong>${typeName}</strong><br>
                    <small class="text-muted">${infoParts.join(' — ')}</small>
                `);
            }

            $('#documentTypeSelect').change(updateDocumentTypeInfo);
            $('#startDate').change(updateDocumentTypeInfo);

            // Document form submission
            $('#documentForm').submit(function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                $.ajax({
                    url: "{{ route('machines.documents.store') }}",
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
                var machineId = $(this).data('machine-id');
                var typeName = $(this).data('type-name');

                $('#historyModalTitle').text('Historique - ' + typeName);
                $('#historyTableBody').html(
                    '<tr><td colspan="6" class="text-center text-muted">Chargement...</td></tr>');

                $.ajax({
                    url: "{{ url('machines') }}/" + machineId + "/document-history/" + documentTypeId,
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
                                } else if (doc.end_date && new Date(doc.end_date) <=
                                    new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)) {
                                    statusBadge =
                                        '<span class="badge bg-warning text-dark">Expire bientôt</span>';
                                } else {
                                    statusBadge =
                                        '<span class="badge bg-secondary">Ancien</span>';
                                }

                                html += '<tr>' +
                                    '<td>' + formatDate(doc.start_date) + '</td>' +
                                    '<td>' + formatDate(doc.end_date) + '</td>' +
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

            // Create/update maintenance schedule
            $('#maintenanceForm').submit(function(e) {
                e.preventDefault();

                const id = $('#maintenance_id').val();
                const url = id ? "{{ url('machine-maintenance') }}/" + id :
                    "{{ route('machine-maintenance.store') }}";

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $(this).serialize() + (id ? '&_method=PUT' : ''),
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMessage = xhr.responseJSON?.message || 'Erreur lors de l\'enregistrement';

                        if (errors) {
                            errorMessage = Object.values(errors).flat().join('\n');
                        }

                        showToast('error', errorMessage);
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Confirm maintenance completion (the "approve" step)
            $('#completeMaintenanceForm').submit(function(e) {
                e.preventDefault();

                const id = $(this).data('schedule-id');
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Confirmation...');

                $.ajax({
                    url: "{{ url('machine-maintenance') }}/" + id + "/complete",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#completeMaintenanceModal').modal('hide');
                            showToast('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message || 'Erreur lors de la confirmation');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Delete maintenance schedule
            let deleteMaintenanceId = null;
            $('#confirmDeleteMaintenanceBtn').click(function() {
                if (!deleteMaintenanceId) return;

                $.ajax({
                    url: "{{ url('machine-maintenance') }}/" + deleteMaintenanceId,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#deleteMaintenanceModal').modal('hide');
                            showToast('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message || 'Erreur lors de la suppression');
                    }
                });
            });

            window.deleteMaintenance = function(id, label) {
                deleteMaintenanceId = id;
                $('#delete_maintenance_label').text(label);
                $('#deleteMaintenanceModal').modal('show');
            };
        });

        function resetMaintenanceForm() {
            $('#maintenanceForm')[0].reset();
            $('#maintenance_id').val('');
            $('#maintenance_reminder').val(7);
        }

        function openCreateMaintenance() {
            resetMaintenanceForm();
            $('#maintenanceModalLabel').html('<i class="fas fa-tools me-2"></i>Nouveau Programme de Maintenance');
            $('#maintenance_active_wrapper').hide();
            $('#maintenanceSubmitBtn').html('<i class="fas fa-save me-1"></i> Enregistrer');
            new bootstrap.Modal(document.getElementById('maintenanceModal')).show();
        }

        function editMaintenance(id) {
            const schedule = maintenanceSchedules[id];
            if (!schedule) return;

            $('#maintenance_id').val(schedule.id);
            $('#maintenance_label').val(schedule.label);
            $('#maintenance_description').val(schedule.description);
            $('#maintenance_interval').val(schedule.interval_days);
            $('#maintenance_reminder').val(schedule.reminder_days_before);
            $('#maintenance_next_due').val(schedule.next_due_at ? schedule.next_due_at.split('T')[0] : '');
            $('#maintenance_is_active').prop('checked', !!schedule.is_active);
            $('#maintenance_active_wrapper').show();
            $('#maintenanceModalLabel').html('<i class="fas fa-tools me-2"></i>Modifier le Programme de Maintenance');
            $('#maintenanceSubmitBtn').html('<i class="fas fa-save me-1"></i> Mettre à jour');
            new bootstrap.Modal(document.getElementById('maintenanceModal')).show();
        }

        function completeMaintenance(id, label) {
            $('#completeMaintenanceForm').data('schedule-id', id);
            $('#complete_maintenance_label').text(label);
            $('#complete_notes').val('');
            new bootstrap.Modal(document.getElementById('completeMaintenanceModal')).show();
        }

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function formatDate(value) {
            return value ? new Date(value).toLocaleDateString('fr-FR') : '-';
        }

        function showMaintenanceHistory(id, label) {
            $('#maintenance_history_label').text(label);
            $('#maintenance_history_body').empty();
            $('#maintenance_history_empty').hide();

            $.ajax({
                url: "{{ url('machine-maintenance') }}/" + id + "/history",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        if (response.data.length === 0) {
                            $('#maintenance_history_empty').show();
                        } else {
                            response.data.forEach(function(entry) {
                                const row = '<tr>' +
                                    '<td>' + escapeHtml(entry.completed_at) + '</td>' +
                                    '<td>' + escapeHtml(entry.previous_due_at) + '</td>' +
                                    '<td>' + escapeHtml(entry.next_due_at) + '</td>' +
                                    '<td>' + escapeHtml(entry.notes || '-') + '</td>' +
                                    '<td>' + escapeHtml(entry.completed_by) + '</td>' +
                                    '</tr>';
                                $('#maintenance_history_body').append(row);
                            });
                        }
                        new bootstrap.Modal(document.getElementById('maintenanceHistoryModal')).show();
                    }
                },
                error: function(xhr) {
                    showToast('error', xhr.responseJSON?.message || 'Erreur lors du chargement de l\'historique');
                }
            });
        }

        function showToast(type, message) {
            var bgColor = type === 'success' ? 'bg-success' : (type === 'warning' ? 'bg-warning' : 'bg-danger');
            var toast = $('<div class="toast align-items-center text-white ' + bgColor +
                ' border-0" role="alert">' +
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
