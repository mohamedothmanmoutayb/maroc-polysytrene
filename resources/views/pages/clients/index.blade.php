@extends('layouts.app')

@section('title', 'Gestion des Clients')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Clients</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Clients
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
            <div class="col-xl-4 col-sm-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="text-muted d-block mb-1">Total Clients</span>
                                <h2 class="mb-0 fw-bold" id="totalClients">0</h2>
                                <small class="text-muted">Tous les clients</small>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-users fs-1 text-primary"></i>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between">
                            <span class="small text-muted">
                                <i class="fas fa-circle text-success me-1"></i> Actifs: <span id="activeClients">0</span>
                            </span>
                            <span class="small text-muted">
                                <i class="fas fa-building me-1"></i> Morale: <span id="moraleClients">0</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-sm-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="text-muted d-block mb-1">Ventes Totales</span>
                                <h2 class="mb-0 fw-bold" id="totalSales">0 DH</h2>
                                <small class="text-muted">Toutes ventes confondues</small>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-chart-line fs-1 text-success"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="small text-muted">
                                <i class="fas fa-check-circle text-success me-1"></i> Payé: <span id="totalPaid">0 DH</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-sm-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="text-muted d-block mb-1">Créances</span>
                                <h2 class="mb-0 fw-bold text-danger" id="totalReceivables">0 DH</h2>
                                <small class="text-muted">Montant total impayé</small>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-hand-holding-usd fs-1 text-warning"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="small text-muted">
                                <i class="fas fa-exclamation-triangle text-warning me-1"></i> À recouvrer
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clients List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-list me-2"></i>Liste des Clients
                        </h5>
                        <div>
                            <button type="button" class="btn btn-light btn-sm me-2" data-bs-toggle="collapse"
                                data-bs-target="#filterSection">
                                <i class="fas fa-filter me-1"></i> Filtres
                            </button>
                            @can('create_clients')
                                <a href="{{ route('clients.create') }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-plus me-1"></i> Nouveau Client
                                </a>
                            @endcan
                        </div>
                    </div>
                    <!-- Filter Section -->
                    <div class="collapse show" id="filterSection">
                        <div class="card-body border-bottom">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" class="form-control" id="searchByName"
                                            placeholder="Rechercher par nom, entreprise, téléphone, CIN, ICE...">
                                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select class="form-select form-select-sm" id="filterClientType">
                                        <option value="">Tous les types</option>
                                        <option value="client">Client</option>
                                        <option value="commerciale">Commerciale</option>
                                        <option value="grossiste">Grossiste</option>
                                        <option value="special">Special</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select class="form-select form-select-sm" id="filterPersonType">
                                        <option value="">Toutes les personnes</option>
                                        <option value="physique">Physique</option>
                                        <option value="morale">Morale</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select class="form-select form-select-sm" id="filterStatus">
                                        <option value="">Tous les statuts</option>
                                        <option value="1">Actif</option>
                                        <option value="0">Inactif</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select class="form-select form-select-sm" id="filterCredit">
                                        <option value="">Crédit</option>
                                        <option value="over">Dépassé</option>
                                        <option value="warning">>70%</option>
                                        <option value="good">
                                            <70%< /option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="clients-table" class="table table-hover w-100">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Nom / Entreprise</th>
                                        <th width="8%">Type</th>
                                        <th width="8%">Personne</th>
                                        <th width="10%">Téléphone</th>
                                        <th width="10%">Identification</th>
                                        <th width="8%">Adresse</th>
                                        <th width="15%">Solde</th>
                                        <th width="15%">Crédit</th>
                                        <th width="15%">Résumé Achats</th>
                                        <th width="8%">Statut</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Êtes-vous sûr de vouloir supprimer le client :</p>
                    <h6 class="fw-bold text-danger mb-3" id="deleteClientName"></h6>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-1"></i>
                        Cette action est irréversible et supprimera tous les documents associés.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Balance Modal -->
    <div class="modal fade" id="addBalanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-wallet me-2"></i>Ajouter du Solde
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addBalanceForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="balance_client_id" name="client_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Client</label>
                                <p class="form-control-plaintext" id="balance_client_name"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Solde Actuel</label>
                                <p class="form-control-plaintext" id="current_balance"></p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="balance_amount" class="form-label fw-bold">Montant à Ajouter (DH) *</label>
                            <input type="number" class="form-control" id="balance_amount" name="amount"
                                step="0.01" required>
                            <small class="text-muted">Ce montant sera ajouté au solde du client (trop-perçu)</small>
                        </div>
                        <div class="mb-3">
                            <label for="balance_reason" class="form-label fw-bold">Motif / Référence</label>
                            <input type="text" class="form-control" id="balance_reason" name="reason"
                                placeholder="Ex: Remboursement, Avoir, Correction de paiement...">
                        </div>
                        <div class="alert alert-warning mt-3" id="newBalancePreview">
                            <i class="fas fa-calculator me-2"></i>
                            Nouveau solde après ajout: <strong id="new_balance_display">0.00 DH</strong>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Annuler
                        </button>
                        <button type="submit" class="btn btn-success" id="submitBalanceBtn">
                            <i class="fas fa-plus-circle me-1"></i>Ajouter Solde
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Client Payment Modal -->
    <div class="modal fade" id="clientPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-money-bill-wave me-2"></i>Ajouter un paiement client
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="clientPaymentForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="payment_client_id" name="client_id">
                        <input type="hidden" id="totalUnpaidAmount" name="total_unpaid_amount">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <div class="alert alert-info">
                                    <strong><i class="fas fa-user me-2"></i>Client:</strong>
                                    <span id="payment_client_name"></span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="alert alert-success">
                                    <strong><i class="fas fa-wallet me-2"></i>Solde:</strong>
                                    <span id="payment_client_balance">0.00 DH</span>
                                </div>
                            </div>
                        </div>

                        <!-- Unpaid Orders List -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Ventes impayées (de la plus ancienne à la plus
                                récente)</label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="unpaidOrdersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th>Vente</th>
                                            <th width="20%" class="text-end">Montant Total</th>
                                            <th width="20%" class="text-end">Reste à payer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Chargement...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Payment Amount -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="payment_amount" class="form-label fw-bold">Montant à payer (DH) *</label>
                                <input type="number" class="form-control" id="payment_amount" name="amount"
                                    step="0.01" min="0.01" required>
                                <small class="text-muted" id="amountHelp"></small>
                                <!-- Warning message will appear here -->
                                <div id="amountWarning" class="mt-2" style="display: none;">
                                    <div class="alert alert-warning alert-dismissible fade show mb-0 py-2">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <span id="warningMessage"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="payment_date" class="form-label fw-bold">Date de paiement *</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label fw-bold">Méthode de paiement *</label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="">Sélectionner</option>
                                    <option value="cash">Espèces</option>
                                    <option value="check">Chèque</option>
                                    <option value="transfer">Virement</option>
                                    <option value="traite">Traite</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="payment_reference" class="form-label fw-bold">Référence (N° chèque, virement,
                                    etc.)</label>
                                <input type="text" class="form-control" id="payment_reference" name="reference">
                            </div>
                        </div>

                        <!-- Payment Method Details Section (Cheque/Traite) -->
                        <div id="paymentDetailsSection" style="display: none;">
                            <!-- Cheque Details -->
                            <div id="chequeDetails" style="display: none;">
                                <div class="card mt-3 mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Détails du Chèque</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="check_number" class="form-label">Numéro de chèque *</label>
                                                <input type="text" class="form-control" id="check_number"
                                                    name="check_number">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="check_amount" class="form-label">Montant *</label>
                                                <input type="number" class="form-control" id="check_amount"
                                                    name="check_amount" step="0.01">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="bank_name" class="form-label">Banque *</label>
                                                <input type="text" class="form-control" id="bank_name"
                                                    name="bank_name">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="account_holder" class="form-label">Titulaire du compte
                                                    *</label>
                                                <input type="text" class="form-control" id="account_holder"
                                                    name="account_holder">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="issue_date" class="form-label">Date d'émission *</label>
                                                <input type="date" class="form-control" id="issue_date"
                                                    value="{{ date('Y-m-d') }}" name="issue_date">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="deposit_date" class="form-label">Date d'échéance *</label>
                                                <input type="date" class="form-control" id="deposit_date"
                                                    value="{{ date('Y-m-d') }}" name="deposit_date">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="check_image" class="form-label">Document (Recto/Verso)</label>
                                            <input type="file" class="form-control" id="check_image"
                                                name="check_image" accept="image/*,application/pdf" multiple>
                                            <small class="text-muted">Vous pouvez sélectionner plusieurs fichiers (recto,
                                                verso)</small>
                                        </div>
                                        <div id="checkImagesPreview" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Traite Details -->
                            <div id="traiteDetails" style="display: none;">
                                <div class="card mt-3 mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Détails de la Traite
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="traite_number" class="form-label">Numéro de traite *</label>
                                                <input type="text" class="form-control" id="traite_number"
                                                    name="traite_number">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="traite_amount" class="form-label">Montant *</label>
                                                <input type="number" class="form-control" id="traite_amount"
                                                    name="traite_amount" step="0.01">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="traite_bank_name" class="form-label">Banque *</label>
                                                <input type="text" class="form-control" id="traite_bank_name"
                                                    name="traite_bank_name">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="drawee" class="form-label">Tiré (Nom/Entreprise) *</label>
                                                <input type="text" class="form-control" id="drawee"
                                                    name="drawee">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="traite_issue_date" class="form-label">Date d'émission
                                                    *</label>
                                                <input type="date" class="form-control" id="traite_issue_date"
                                                    value="{{ date('Y-m-d') }}" name="traite_issue_date">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="due_date" class="form-label">Date d'échéance *</label>
                                                <input type="date" class="form-control" id="due_date"
                                                    value="{{ date('Y-m-d') }}" name="due_date">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="drawee_address" class="form-label">Adresse du tiré</label>
                                            <textarea class="form-control" id="drawee_address" name="drawee_address" rows="2"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="traite_document" class="form-label">Document (Traite
                                                scannée)</label>
                                            <input type="file" class="form-control" id="traite_document"
                                                name="traite_document" accept="image/*,application/pdf">
                                            <small class="text-muted">Uploader la traite scannée (PDF, JPEG, PNG)</small>
                                        </div>
                                        <div id="traiteDocumentPreview" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Distribution Preview -->
                        <div class="alert alert-success mt-3" id="distributionPreview" style="display: none;">
                            <i class="fas fa-chart-line me-2"></i>
                            <strong>Aperçu de la distribution:</strong>
                            <div id="distributionDetails" class="mt-2 small"></div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="payment_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="payment_notes" name="notes" rows="2"
                                placeholder="Notes supplémentaires..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Annuler
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitPaymentBtn">
                            <i class="fas fa-save me-1"></i>Enregistrer le paiement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <style>
        .card-header-custom {
            background: linear-gradient(45deg, #3a3f51, #2c3e50);
        }

        .progress {
            border-radius: 10px;
        }

        .progress-bar {
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
        }

        .table> :not(caption)>*>* {
            padding: 0.8rem 0.5rem;
            vertical-align: middle;
        }

        .dropdown-menu {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
            padding: 0.5rem;
        }

        .dropdown-item {
            border-radius: 6px;
            padding: 0.6rem 1rem;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .card {
            border: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .bg-opacity-10 {
            opacity: 0.1;
        }

        .toast-container {
            z-index: 9999;
        }

        #clients-table_filter {
            display: none;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#clients-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('clients.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.client_type = $('#filterClientType').val();
                        d.person_type = $('#filterPersonType').val();
                        d.is_active = $('#filterStatus').val();
                        d.credit_status = $('#filterCredit').val();
                        d.search_name = $('#searchByName').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false
                    },
                    {
                        data: 'display_name',
                        name: 'display_name'
                    },
                    {
                        data: 'client_type_badge',
                        name: 'client_type',
                        searchable: false
                    },
                    {
                        data: 'person_type_badge',
                        name: 'person_type',
                        searchable: false
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'identification',
                        name: 'identification'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'balance',
                        name: 'balance',
                        searchable: false
                    },
                    {
                        data: 'credit_info',
                        name: 'credit_limit',
                        searchable: false
                    },
                    {
                        data: 'purchase_summary',
                        name: 'purchase_summary',
                        searchable: false
                    },
                    {
                        data: 'status_badge',
                        name: 'is_active',
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        searchable: false
                    }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                },
                pageLength: 25,
                order: [
                    [1, 'asc']
                ]
            });

            // Apply filters when changed
            $('#filterClientType, #filterPersonType, #filterStatus, #filterCredit').on('change', function() {
                table.draw();
            });

            // Search with debounce
            let searchTimeout;
            $('#searchByName').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    table.draw();
                }, 500);
            });

            // Clear search
            $('#clearSearch').on('click', function() {
                $('#searchByName').val('');
                table.draw();
            });

            // Load global statistics
            loadStatistics();

            function loadStatistics() {
                $.ajax({
                    url: "{{ route('clients.statistics') }}",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#totalClients').text(response.data.total);
                            $('#activeClients').text(response.data.active);
                            $('#physiqueClients').text(response.data.physique);
                            $('#moraleClients').text(response.data.morale);
                            $('#totalReceivables').text(response.data.total_receivables + ' DH');
                            $('#totalSales').text(response.data.total_sales + ' DH');
                            $('#totalPaid').text(response.data.total_paid + ' DH');

                            var totalSales = parseFloat(response.data.total_sales.replace(/\s/g, '')) ||
                                0;
                            var totalPaid = parseFloat(response.data.total_paid.replace(/\s/g, '')) ||
                                0;
                            var paymentRate = totalSales > 0 ? ((totalPaid / totalSales) * 100).toFixed(
                                1) : 0;

                            $('#paymentRate').text(paymentRate + '%');
                            $('#paymentProgress').css('width', paymentRate + '%');
                        }
                    }
                });
            }

            // Delete client
            $(document).on('click', '.delete-client', function() {
                var clientId = $(this).data('id');
                var clientName = $(this).data('name');
                $('#deleteClientName').text(clientName);
                $('#deleteForm').attr('action', "{{ url('clients') }}/" + clientId);
                $('#deleteModal').modal('show');
            });

            $('#deleteForm').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var submitBtn = form.find('button[type="submit"]');
                var originalText = submitBtn.html();

                submitBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Suppression...')
                    .prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        $('#deleteModal').modal('hide');
                        table.draw();
                        loadStatistics();
                        showToast('success', response.message);
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON;
                        showToast('error', response?.message ||
                            'Erreur lors de la suppression');
                    },
                    complete: function() {
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Open Add Balance Modal
            $(document).on('click', '.add-balance-btn', function() {
                var clientId = $(this).data('id');
                var clientName = $(this).data('name');

                $('#addBalanceForm')[0].reset();
                $('#balance_amount').val('');
                $('#balance_reason').val('');

                $('#balance_client_id').val(clientId);
                $('#balance_client_name').text(clientName);

                $.ajax({
                    url: "/clients/" + clientId + "/balance",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            var currentBalance = response.data.balance;
                            var balanceFormatted = response.data.balance_formatted;
                            $('#current_balance').html(balanceFormatted);
                            $('#current_balance').data('value', currentBalance);
                            updateBalancePreview();
                        } else {
                            $('#current_balance').text('0.00 DH');
                            $('#current_balance').data('value', 0);
                        }
                    },
                    error: function() {
                        $('#current_balance').text('0.00 DH');
                        $('#current_balance').data('value', 0);
                    }
                });

                $('#addBalanceModal').modal('show');
            });

            function updateBalancePreview() {
                var currentBalance = parseFloat($('#current_balance').data('value') || 0);
                var amount = parseFloat($('#balance_amount').val()) || 0;
                var newBalance = currentBalance + amount;
                var newBalanceClass = newBalance > 0 ? 'text-success' : (newBalance < 0 ? 'text-danger' :
                    'text-secondary');
                $('#new_balance_display').html('<span class="' + newBalanceClass + ' fw-bold">' + newBalance
                    .toFixed(2) + ' DH</span>');
            }

            $('#balance_amount').on('input', function() {
                updateBalancePreview();
            });

            $('#addBalanceForm').submit(function(e) {
                e.preventDefault();
                var clientId = $('#balance_client_id').val();
                var amount = $('#balance_amount').val();
                var reason = $('#balance_reason').val();
                var notes = $('#balance_notes').val();

                var submitBtn = $('#submitBalanceBtn');
                var originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Ajout...');

                $.ajax({
                    url: "/clients/" + clientId + "/add-balance",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        amount: amount,
                        reason: reason,
                        notes: notes
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            $('#addBalanceModal').modal('hide');
                            table.ajax.reload();
                            loadStatistics();
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON;
                        showToast('error', response?.message ||
                            'Erreur lors de l\'ajout du solde');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Handle payment method change to show cheque/traite details
            $('#payment_method').on('change', function() {
                var method = $(this).val();
                $('#paymentDetailsSection').show();

                if (method === 'check') {
                    $('#chequeDetails').show();
                    $('#traiteDetails').hide();
                    // Make cheque fields required
                    $('#check_number, #check_amount, #bank_name, #account_holder, #issue_date').prop(
                        'required', true);
                    $('#traite_number, #traite_amount, #traite_bank_name, #drawee, #traite_issue_date, #due_date')
                        .prop('required', false);
                } else if (method === 'traite') {
                    $('#chequeDetails').hide();
                    $('#traiteDetails').show();
                    // Make traite fields required
                    $('#traite_number, #traite_amount, #traite_bank_name, #drawee, #traite_issue_date, #due_date')
                        .prop('required', true);
                    $('#check_number, #check_amount, #bank_name, #account_holder, #issue_date').prop(
                        'required', false);
                } else {
                    $('#paymentDetailsSection').hide();
                    $('#chequeDetails').hide();
                    $('#traiteDetails').hide();
                    // Remove required from all
                    $('#check_number, #check_amount, #bank_name, #account_holder, #issue_date, #traite_number, #traite_amount, #traite_bank_name, #drawee, #traite_issue_date, #due_date')
                        .prop('required', false);
                }
            });

            // Handle cheque file upload preview
            $('#check_image').on('change', function() {
                var previewContainer = $('#checkImagesPreview');
                previewContainer.empty();

                if (this.files) {
                    Array.from(this.files).forEach(function(file, index) {
                        var reader = new FileReader();
                        var fileId = 'file_' + Date.now() + '_' + index;

                        reader.onload = function(e) {
                            var fileItem = $(`
                            <div id="${fileId}" class="position-relative d-inline-block me-2 mb-2" style="width: 120px;">
                                <div class="border rounded p-1 bg-light">
                                    <div class="text-center">
                                        ${file.type.startsWith('image/') ?
                                            `<img src="${e.target.result}" class="img-fluid rounded" style="height: 80px; object-fit: cover;">` :
                                            `<i class="fas fa-file-pdf fa-3x text-danger"></i>`
                                        }
                                        <div class="small text-truncate mt-1">${file.name}</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" style="border-radius: 50%; width: 22px; height: 22px; line-height: 1; padding: 0;" onclick="removeFileFromInput('check_image', '${fileId}')">
                                        <i class="fas fa-times fa-xs"></i>
                                    </button>
                                </div>
                            </div>
                        `);
                            previewContainer.append(fileItem);
                        };

                        reader.readAsDataURL(file);
                    });
                }
            });

            // Handle traite document upload preview
            $('#traite_document').on('change', function() {
                var previewContainer = $('#traiteDocumentPreview');
                previewContainer.empty();

                if (this.files && this.files[0]) {
                    var file = this.files[0];
                    var reader = new FileReader();
                    var fileId = 'traite_' + Date.now();

                    reader.onload = function(e) {
                        var fileItem = $(`
                        <div id="${fileId}" class="position-relative d-inline-block me-2 mb-2" style="width: 150px;">
                            <div class="border rounded p-2 bg-light">
                                <div class="text-center">
                                    ${file.type.startsWith('image/') ?
                                        `<img src="${e.target.result}" class="img-fluid rounded" style="height: 100px; object-fit: cover;">` :
                                        `<i class="fas fa-file-pdf fa-4x text-danger"></i>`
                                    }
                                    <div class="small text-truncate mt-1">${file.name}</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" style="border-radius: 50%; width: 22px; height: 22px; line-height: 1; padding: 0;" onclick="removeFileFromInput('traite_document', '${fileId}')">
                                    <i class="fas fa-times fa-xs"></i>
                                </button>
                            </div>
                        </div>
                    `);
                        previewContainer.append(fileItem);
                    };

                    reader.readAsDataURL(file);
                }
            });

            window.openPaymentModal = function(clientId, clientName) {
                $('#clientPaymentForm')[0].reset();
                $('#payment_amount').val('');
                $('#payment_reference').val('');
                $('#payment_notes').val('');
                $('#distributionPreview').hide();
                $('#amountWarning').hide();
                $('#paymentDetailsSection').hide();
                $('#chequeDetails').hide();
                $('#traiteDetails').hide();
                $('#checkImagesPreview').empty();
                $('#traiteDocumentPreview').empty();
                $('#payment_client_id').val(clientId);
                $('#payment_client_name').text(clientName);

                // Reset required fields
                $('#check_number, #check_amount, #bank_name, #account_holder, #issue_date, #traite_number, #traite_amount, #traite_bank_name, #drawee, #traite_issue_date, #due_date')
                    .prop('required', false);

                loadClientBalance(clientId);
                loadUnpaidOrders(clientId);
                $('#clientPaymentModal').modal('show');
            };

            $('#clientPaymentModal').on('hidden.bs.modal', function() {
                $('#amountWarning').hide();
                $('#distributionPreview').hide();
                $('#paymentDetailsSection').hide();
                $('#chequeDetails').hide();
                $('#traiteDetails').hide();
                $('#checkImagesPreview').empty();
                $('#traiteDocumentPreview').empty();
                $('#payment_amount').val('');
                $('#amountHelp').html('');
                $('#payment_method').val('');
            });

            function loadClientBalance(clientId) {
                $.ajax({
                    url: "/clients/" + clientId + "/balance",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#payment_client_balance').html(response.data.balance + ' DH');
                            $('#payment_client_balance').data('balance', response.data.balance || 0);
                        } else {
                            $('#payment_client_balance').html('0.00 DH');
                            $('#payment_client_balance').data('balance', 0);
                        }
                    },
                    error: function() {
                        $('#payment_client_balance').html('0.00 DH');
                        $('#payment_client_balance').data('balance', 0);
                    }
                });
            }

            function loadUnpaidOrders(clientId) {
                $('#unpaidOrdersTable tbody').html(
                    '<tr><td colspan="4" class="text-center"><div class="spinner-border spinner-border-sm"></div> Chargement...</td></tr>'
                );

                $.ajax({
                    url: "{{ url('sales-orders') }}/client/" + clientId + "/unpaid",
                    type: "GET",
                    success: function(response) {
                        if (response.success && response.orders.length > 0) {
                            let html = '';
                            let totalUnpaid = 0;

                            response.orders.forEach(function(order, index) {
                                totalUnpaid += order.unpaid_amount;
                                html += `
                                <tr data-order-id="${order.order_id}" data-unpaid="${order.unpaid_amount}">
                                    <td>${index + 1}</td>
                                    <td>
                                        <strong>${order.order_number}</strong><br>
                                        <small class="text-muted">${order.order_date}</small>
                                    </td>
                                    <td class="text-end">${parseFloat(order.total_amount).toFixed(2)} DH</td>
                                    <td class="text-end text-danger">${parseFloat(order.unpaid_amount).toFixed(2)} DH</td>
                                 </tr>
                            `;
                            });

                            html += `
                            <tr class="table-active">
                                <td colspan="3" class="text-end"><strong>Total impayé:</strong></td>
                                <td class="text-end"><strong class="text-danger">${totalUnpaid.toFixed(2)} DH</strong></td>
                            </tr>
                        `;

                            $('#unpaidOrdersTable tbody').html(html);
                            $('#totalUnpaidAmount').val(totalUnpaid);
                            $('#amountHelp').html('Maximum: ' + totalUnpaid.toFixed(2) + ' DH');
                        } else {
                            $('#unpaidOrdersTable tbody').html(
                                '<tr><td colspan="4" class="text-center text-muted">Aucune vente impayée</td></tr>'
                            );
                            $('#totalUnpaidAmount').val(0);
                            $('#amountHelp').html(
                                'Aucune vente impayée, le montant sera ajouté au solde');
                        }
                    },
                    error: function() {
                        $('#unpaidOrdersTable tbody').html(
                            '<tr><td colspan="4" class="text-center text-danger">Erreur de chargement</td></tr>'
                        );
                    }
                });
            }

            // Calculate distribution preview
            $('#payment_amount').on('input', function() {
                let amount = parseFloat($(this).val()) || 0;
                let totalUnpaid = parseFloat($('#totalUnpaidAmount').val()) || 0;

                if (amount <= 0) {
                    $('#distributionPreview').hide();
                    $('#amountWarning').hide();
                    return;
                }

                if (amount > totalUnpaid) {
                    let excess = amount - totalUnpaid;
                    let warningHtml = '⚠️ Montant supérieur au total impayé (' +
                        totalUnpaid.toFixed(2) + ' DH). ' +
                        'Excédent de ' + excess.toFixed(2) + ' DH sera ajouté au solde client.';
                    $('#warningMessage').html(warningHtml);
                    $('#amountWarning').show();
                    $('#amountHelp').html('');
                } else {
                    $('#amountWarning').hide();
                    $('#amountHelp').html('Maximum: ' + totalUnpaid.toFixed(2) + ' DH');
                }

                $(this).removeClass('is-invalid');

                let remaining = Math.min(amount, totalUnpaid);
                let distribution = [];
                let orderRows = $('#unpaidOrdersTable tbody tr[data-order-id]');

                orderRows.each(function() {
                    if (remaining <= 0) return;
                    let orderNumber = $(this).find('strong').text();
                    let unpaidAmount = parseFloat($(this).data('unpaid'));
                    let paidAmount = Math.min(remaining, unpaidAmount);
                    remaining -= paidAmount;

                    distribution.push({
                        order_number: orderNumber,
                        paid: paidAmount,
                        remaining_after: unpaidAmount - paidAmount
                    });
                });

                let excess = amount > totalUnpaid ? amount - totalUnpaid : 0;

                let previewHtml =
                    '<div class="table-responsive"><table class="table table-sm table-bordered">';
                previewHtml +=
                    '<thead><tr><th>Vente</th><th class="text-end">Montant payé</th><th class="text-end">Reste après</th></thead><tbody>';

                distribution.forEach(function(item) {
                    previewHtml += `
                    <tr>
                        <td>${item.order_number}</td>
                        <td class="text-end text-success">${item.paid.toFixed(2)} DH</td>
                        <td class="text-end ${item.remaining_after > 0 ? 'text-danger' : 'text-success'}">${item.remaining_after.toFixed(2)} DH</td>
                    </tr>
                `;
                });

                if (excess > 0) {
                    previewHtml += `
                    <tr class="table-info">
                        <td><strong>Excédent (ajouté au solde)</strong></td>
                        <td class="text-end text-primary"><strong>${excess.toFixed(2)} DH</strong></td>
                        <td class="text-end">-</td>
                    </tr>
                `;
                }

                previewHtml += '</tbody> </div>';
                $('#distributionDetails').html(previewHtml);
                $('#distributionPreview').show();
            });

            // Submit payment form with cheque/traite data
            $('#clientPaymentForm').submit(function(e) {
                e.preventDefault();

                let clientId = $('#payment_client_id').val();
                let amount = parseFloat($('#payment_amount').val()) || 0;
                let paymentDate = $('#payment_date').val();
                let paymentMethod = $('#payment_method').val();
                let reference = $('#payment_reference').val();
                let notes = $('#payment_notes').val();

                if (!clientId) {
                    showToast('error', 'Client non trouvé');
                    return;
                }

                if (amount <= 0) {
                    showToast('error', 'Veuillez saisir un montant valide');
                    return;
                }

                if (!paymentDate) {
                    showToast('error', 'Veuillez sélectionner une date de paiement');
                    return;
                }

                if (!paymentMethod) {
                    showToast('error', 'Veuillez sélectionner une méthode de paiement');
                    return;
                }

                // Prepare form data for file upload
                let formData = new FormData(this);
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                // Add cheque/traite specific data
                if (paymentMethod === 'check') {
                    if (!$('#check_number').val()) {
                        showToast('error', 'Veuillez saisir le numéro de chèque');
                        return;
                    }
                    if (!$('#check_amount').val()) {
                        showToast('error', 'Veuillez saisir le montant du chèque');
                        return;
                    }
                    if (parseFloat($('#check_amount').val()) !== amount) {
                        showToast('error', 'Le montant du chèque doit être égal au montant du paiement');
                        return;
                    }
                    formData.append('check_number', $('#check_number').val());
                    formData.append('check_amount', $('#check_amount').val());
                    formData.append('bank_name', $('#bank_name').val());
                    formData.append('account_holder', $('#account_holder').val());
                    formData.append('issue_date', $('#issue_date').val());
                    formData.append('deposit_date', $('#deposit_date').val());

                    // Append cheque images
                    let checkImages = $('#check_image')[0].files;
                    for (let i = 0; i < checkImages.length; i++) {
                        formData.append('check_images[]', checkImages[i]);
                    }
                } else if (paymentMethod === 'traite') {
                    if (!$('#traite_number').val()) {
                        showToast('error', 'Veuillez saisir le numéro de traite');
                        return;
                    }
                    if (!$('#traite_amount').val()) {
                        showToast('error', 'Veuillez saisir le montant de la traite');
                        return;
                    }
                    if (parseFloat($('#traite_amount').val()) !== amount) {
                        showToast('error', 'Le montant de la traite doit être égal au montant du paiement');
                        return;
                    }
                    formData.append('traite_number', $('#traite_number').val());
                    formData.append('traite_amount', $('#traite_amount').val());
                    formData.append('traite_bank_name', $('#traite_bank_name').val());
                    formData.append('drawee', $('#drawee').val());
                    formData.append('traite_issue_date', $('#traite_issue_date').val());
                    formData.append('due_date', $('#due_date').val());
                    formData.append('drawee_address', $('#drawee_address').val());

                    // Append traite document
                    let traiteDocument = $('#traite_document')[0].files[0];
                    if (traiteDocument) {
                        formData.append('traite_document', traiteDocument);
                    }
                }

                let submitBtn = $('#submitPaymentBtn');
                let originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Traitement...');

                $.ajax({
                    url: "{{ url('clients') }}/" + clientId + "/distribute-payment",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            $('#clientPaymentModal').modal('hide');
                            table.ajax.reload();
                            loadStatistics();
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON;
                        showToast('error', response?.message ||
                            'Erreur lors du traitement du paiement');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            $(document).on('click', '.add-payment-btn', function() {
                var clientId = $(this).data('id');
                var clientName = $(this).data('name');

                $('#clientPaymentForm')[0].reset();
                $('#payment_amount').val('');
                $('#payment_reference').val('');
                $('#payment_notes').val('');
                $('#distributionPreview').hide();
                $('#paymentDetailsSection').hide();
                $('#checkImagesPreview').empty();
                $('#traiteDocumentPreview').empty();
                $('#payment_client_id').val(clientId);
                $('#payment_client_name').text(clientName);

                loadClientBalance(clientId);
                loadUnpaidOrders(clientId);
                $('#clientPaymentModal').modal('show');
            });

            // Toast notification function
            function showToast(type, message) {
                var bgColor = type === 'success' ? 'bg-success' : (type === 'warning' ? 'bg-warning' : 'bg-danger');
                var icon = type === 'success' ? 'check-circle' : (type === 'warning' ? 'exclamation-triangle' :
                    'exclamation-circle');

                var toast = $(`
                <div class="toast align-items-center text-white ${bgColor} border-0 mb-2" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${icon} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `);

                $('#toast-container').append(toast);
                var bsToast = new bootstrap.Toast(toast[0], {
                    delay: 5000
                });
                bsToast.show();

                setTimeout(function() {
                    toast.remove();
                }, 6000);
            }
        });

        // Global function to remove files from input
        function removeFileFromInput(inputId, fileId) {
            // For multiple files input
            if (inputId === 'check_image') {
                var input = document.getElementById(inputId);
                var dt = new DataTransfer();
                var files = input.files;

                // Find which file to remove by matching with preview index
                var fileIndex = -1;
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    // Simple mechanism to track files (you might want a more robust solution)
                    if (fileIndex === -1 && i < $('#checkImagesPreview .position-relative').length) {
                        fileIndex = i;
                        break;
                    }
                }

                if (fileIndex !== -1) {
                    for (var i = 0; i < files.length; i++) {
                        if (i !== fileIndex) {
                            dt.items.add(files[i]);
                        }
                    }
                    input.files = dt.files;
                }
            } else {
                // For single file input
                $('#' + inputId).val('');
            }

            // Remove the preview element
            $('#' + fileId).remove();
        }
    </script>
@endpush
