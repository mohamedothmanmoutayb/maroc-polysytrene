@extends('layouts.app')

@section('title', 'Gestion des Règlements')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Règlements</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">Règlements</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="statistics row mb-4">
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Total des Règlements</span>
                                <h3 class="mb-0" id="totalReglements">{{ $totalReglements }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-money-bill-wave fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Montant Total</span>
                                <h3 class="mb-0" id="totalAmountCard">{{ number_format($totalAmount, 2, ',', '.') }} DH</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fs-1 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Règlements sur Ventes</span>
                                <h3 class="mb-0">{{ $orderReglements }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-shopping-cart fs-1 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Règlements Directs</span>
                                <h3 class="mb-0">{{ $directReglements }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user fs-1 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Filtre Rapide</label>
                                <select class="form-select form-select-sm" id="quickFilter">
                                    <option value="all" selected>Toutes les périodes</option>
                                    <option value="today">Aujourd'hui</option>
                                    <option value="yesterday">Hier</option>
                                    <option value="this_week">Cette semaine</option>
                                    <option value="last_week">Semaine dernière</option>
                                    <option value="this_month">Ce mois</option>
                                    <option value="last_month">Mois dernier</option>
                                    <option value="this_year">Cette année</option>
                                    <option value="last_year">Année dernière</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Date de début</label>
                                <input type="date" class="form-control form-control-sm" id="dateFrom">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Date de fin</label>
                                <input type="date" class="form-control form-control-sm" id="dateTo">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Rechercher un client</label>
                                <select class="form-select form-select-sm" id="clientFilter">
                                    <option value="">— Tous les clients —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->client_id }}">{{ $client->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Type de paiement</label>
                                <select class="form-select form-select-sm" id="paymentMethodFilter">
                                    <option value="">— Tous les types —</option>
                                    <option value="cash">Espèces</option>
                                    <option value="check">Chèque</option>
                                    <option value="transfer">Virement</option>
                                    <option value="traite">Traite</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-primary flex-fill" id="applyFilter">
                                    <i class="fas fa-filter me-1"></i> Filtrer
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary flex-fill" id="resetFilter">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clients / Règlements Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-users me-2"></i>Règlements par Client
                        </h5>
                        <button type="button" class="btn btn-light btn-sm" id="btnAddPaymentFromIndex">
                            <i class="fas fa-plus me-1"></i>Ajouter un paiement
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="reglements-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Client</th>
                                        <th class="text-center">Nb. Règlements</th>
                                        <th class="text-end">Encaissé</th>
                                        <th class="text-end">Solde Total</th>
                                        <th width="12%" class="text-center">Détail</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <td colspan="3" class="text-end fw-bold">
                                            Total (<span id="count-footer">0</span> client(s))
                                        </td>
                                        <td class="text-end fw-bold text-success" id="total-footer">0,00 DH</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Modal — Individual payments per client ──────────────────── --}}
    <div class="modal fade" id="clientPaymentsModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-list me-2"></i>Règlements de <strong id="modal-client-name">—</strong>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="modal-loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted">Chargement…</div>
                    </div>
                    <div id="modal-content" style="display:none;">
                        <table class="table table-bordered table-hover mb-0" id="modal-payments-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Référence</th>
                                    <th>Commande</th>
                                    <th class="text-center">Méthode</th>
                                    <th class="text-end">Montant</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="modal-payments-body"></tbody>
                        </table>
                    </div>
                    <div id="modal-empty" class="text-center py-5 text-muted" style="display:none;">
                        <i class="fas fa-inbox fs-1 mb-3 d-block"></i>
                        Aucun règlement trouvé pour ce client dans la période sélectionnée.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Payment Modal ─────────────────────────────────────────────────── --}}
    <div class="modal fade" id="editPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Modifier le Règlement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editPaymentForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_payment_id">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Montant (DH) *</label>
                                <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date *</label>
                                <input type="date" class="form-control" id="edit_date" name="payment_date" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Méthode *</label>
                                <select class="form-control" id="edit_method" name="payment_method" required>
                                    <option value="cash">Espèces</option>
                                    <option value="check">Chèque</option>
                                    <option value="transfer">Virement</option>
                                    <option value="traite">Traite</option>
                                    <option value="advance">Avance</option>
                                    <option value="avoir">Avoir</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Document</label>
                                <input type="file" class="form-control" id="edit_document" name="document" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">PDF, JPG, PNG (max 10MB)</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal ──────────────────────────────────────────── --}}
    <div class="modal fade" id="deletePaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer le règlement <strong id="delete_ref"></strong> ?</p>
                    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Cette action annule le paiement, recalcule le solde de la commande et le crédit client.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn"><i class="fas fa-trash me-1"></i>Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Payment Modal (like client distribute-payment) ────────────────── --}}
    <div class="modal fade" id="addPaymentIndexModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>Ajouter un paiement client</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addPaymentIndexForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Client *</label>
                                <select class="form-control" id="api_client_select" name="client_id" required style="width:100%">
                                    <option value="">Rechercher un client...</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->client_id }}">{{ $client->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4" id="api_balance_wrapper" style="display:none;">
                                <label class="form-label fw-bold">Solde client</label>
                                <div class="alert alert-success mb-0 py-2 text-center">
                                    <strong id="api_client_balance">0.00 DH</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Unpaid Orders -->
                        <div class="mb-4" id="api_orders_wrapper" style="display:none;">
                            <label class="form-label fw-bold">Ventes impayées — cochez celles à payer</label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="apiUnpaidOrdersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%"><input type="checkbox" id="apiSelectAll" class="form-check-input"></th>
                                            <th>#</th><th>Vente</th><th class="text-end">Montant</th><th class="text-end">Reste</th>
                                            <th class="text-end">Payer</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <small class="text-muted">Décochez les ventes que vous ne souhaitez pas payer. Le montant non distribué sera ajouté en solde.</small>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Montant (DH) *</label>
                                <input type="number" class="form-control" id="api_amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date *</label>
                                <input type="date" class="form-control" id="api_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Méthode *</label>
                                <select class="form-control" id="api_method" name="payment_method" required>
                                    <option value="">Sélectionner</option>
                                    <option value="cash">Espèces</option>
                                    <option value="check">Chèque</option>
                                    <option value="transfer">Virement</option>
                                    <option value="traite">Traite</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Document</label>
                                <input type="file" class="form-control" id="api_document" name="document" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        <!-- Payment Method Details Section (Cheque/Traite) — same as client page -->
                        <div id="apiPaymentDetailsSection" style="display: none;">
                            <!-- Cheque Details -->
                            <div id="apiChequeDetails" style="display: none;">
                                <div class="card mt-3 mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Détails du Chèque</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="api_check_number" class="form-label">Numéro de chèque *</label>
                                                <input type="text" class="form-control" id="api_check_number"
                                                    name="check_number">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="api_check_amount" class="form-label">Montant *</label>
                                                <input type="number" class="form-control" id="api_check_amount"
                                                    name="check_amount" step="0.01">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="api_bank_name" class="form-label">Banque *</label>
                                                <input type="text" class="form-control" id="api_bank_name"
                                                    name="bank_name">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="api_account_holder" class="form-label">Titulaire du compte
                                                    *</label>
                                                <input type="text" class="form-control" id="api_account_holder"
                                                    name="account_holder">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="api_issue_date" class="form-label">Date d'émission *</label>
                                                <input type="date" class="form-control" id="api_issue_date"
                                                    value="{{ date('Y-m-d') }}" name="issue_date">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="api_deposit_date" class="form-label">Date d'échéance *</label>
                                                <input type="date" class="form-control" id="api_deposit_date"
                                                    value="{{ date('Y-m-d') }}" name="deposit_date">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="api_check_images" class="form-label">Document (Recto/Verso)</label>
                                            <input type="file" class="form-control" id="api_check_images"
                                                name="check_images[]" accept="image/*,application/pdf" multiple>
                                            <small class="text-muted">Vous pouvez sélectionner plusieurs fichiers (recto,
                                                verso)</small>
                                        </div>
                                        <div id="apiCheckImagesPreview" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Traite Details -->
                            <div id="apiTraiteDetails" style="display: none;">
                                <div class="card mt-3 mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Détails de la Traite
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="api_traite_number" class="form-label">Numéro de traite *</label>
                                                <input type="text" class="form-control" id="api_traite_number"
                                                    name="traite_number">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="api_traite_amount" class="form-label">Montant *</label>
                                                <input type="number" class="form-control" id="api_traite_amount"
                                                    name="traite_amount" step="0.01">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="api_traite_bank_name" class="form-label">Banque *</label>
                                                <input type="text" class="form-control" id="api_traite_bank_name"
                                                    name="traite_bank_name">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="api_drawee" class="form-label">Tiré (Nom/Entreprise) *</label>
                                                <input type="text" class="form-control" id="api_drawee"
                                                    name="drawee">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="api_traite_issue_date" class="form-label">Date d'émission
                                                    *</label>
                                                <input type="date" class="form-control" id="api_traite_issue_date"
                                                    value="{{ date('Y-m-d') }}" name="traite_issue_date">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="api_due_date" class="form-label">Date d'échéance *</label>
                                                <input type="date" class="form-control" id="api_due_date"
                                                    value="{{ date('Y-m-d') }}" name="due_date">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="api_drawee_address" class="form-label">Adresse du tiré</label>
                                            <textarea class="form-control" id="api_drawee_address" name="drawee_address" rows="2"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="api_traite_document" class="form-label">Document (Traite
                                                scannée)</label>
                                            <input type="file" class="form-control" id="api_traite_document"
                                                name="traite_document" accept="image/*,application/pdf">
                                            <small class="text-muted">Uploader la traite scannée (PDF, JPEG, PNG)</small>
                                        </div>
                                        <div id="apiTraiteDocumentPreview" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="api_notes" name="notes" rows="2"></textarea>
                        </div>

                        <!-- Distribution Preview -->
                        <div class="alert alert-info mt-3" id="apiDistributionPreview" style="display:none;">
                            <i class="fas fa-chart-line me-2"></i>
                            <strong>Aperçu de la distribution:</strong>
                            <div id="apiDistributionDetails" class="mt-2 small"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="toast-container" style="position:fixed;top:20px;right:20px;z-index:9999;"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <style>
        .card-header-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        #modal-payments-table td { vertical-align: middle; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="{{ asset('/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script>
    function formatCurrency(amount) {
        if (amount === undefined || amount === null) return '0,00 DH';
        return new Intl.NumberFormat('fr-MA', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount) + ' DH';
    }

    function showToast(type, message) {
        var toast = $('<div class="toast align-items-center text-white bg-' +
            (type === 'success' ? 'success' : 'danger') +
            ' border-0" role="alert"><div class="d-flex"><div class="toast-body">' +
            message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>');
        $('#toast-container').append(toast);
        new bootstrap.Toast(toast[0]).show();
        setTimeout(function() { toast.remove(); }, 5000);
    }

    $(document).ready(function () {
        $('#clientFilter').select2({
            placeholder: '— Tous les clients —',
            allowClear: true, width: '100%',
            language: { noResults: function() { return 'Aucun client trouvé'; } }
        });

        function getDateRangeFromQuickFilter(filterType) {
            const today = new Date();
            const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            let dateFrom = null, dateTo = null;
            switch (filterType) {
                case 'today': dateFrom = today; dateTo = today; break;
                case 'yesterday': dateFrom = new Date(today); dateFrom.setDate(today.getDate() - 1); dateTo = new Date(dateFrom); break;
                case 'this_week': const day = today.getDay(); const diff = today.getDate() - day + (day === 0 ? -6 : 1); dateFrom = new Date(today); dateFrom.setDate(diff); dateTo = new Date(today); break;
                case 'last_week': const lw = new Date(today); lw.setDate(today.getDate() - 7); const lwd = lw.getDay(); const lwDiff = lw.getDate() - lwd + (lwd === 0 ? -6 : 1); dateFrom = new Date(lw); dateFrom.setDate(lwDiff); dateTo = new Date(today); dateTo.setDate(today.getDate() - 1); break;
                case 'this_month': dateFrom = startOfMonth; dateTo = new Date(today); break;
                case 'last_month': dateFrom = new Date(today.getFullYear(), today.getMonth() - 1, 1); dateTo = new Date(today.getFullYear(), today.getMonth(), 0); break;
                case 'this_year': dateFrom = new Date(today.getFullYear(), 0, 1); dateTo = new Date(today); break;
                case 'last_year': dateFrom = new Date(today.getFullYear() - 1, 0, 1); dateTo = new Date(today.getFullYear() - 1, 11, 31); break;
                case 'all': dateFrom = null; dateTo = null; break;
                default: dateFrom = null; dateTo = null;
            }
            return {
                date_from: dateFrom ? dateFrom.toISOString().split('T')[0] : '',
                date_to: dateTo ? dateTo.toISOString().split('T')[0] : ''
            };
        }

        $('#quickFilter').change(function () {
            var range = getDateRangeFromQuickFilter($(this).val());
            $('#dateFrom').val(range.date_from);
            $('#dateTo').val(range.date_to);
            table.ajax.reload();
        });

        $('#dateFrom, #dateTo').change(function () {
            if ($(this).val()) $('#quickFilter').val('');
        });

        // ── Main DataTable (grouped by client) ────────────────────────
        var table = $('#reglements-table').DataTable({ lengthChange: false, 
            processing: true, serverSide: true, searching: false,
            ajax: {
                url: "{{ route('purchases.index') }}",
                type: 'GET',
                data: function (d) {
                    d.date_from = $('#dateFrom').val();
                    d.date_to = $('#dateTo').val();
                    d.client_id = $('#clientFilter').val();
                    d.payment_method = $('#paymentMethodFilter').val();
                },
                error: function () { showToast('error', 'Erreur lors du chargement des données'); }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'client_name', name: 'client_name', orderable: false, searchable: false },
                { data: 'order_count_badge', name: 'order_count', orderable: false, searchable: false, className: 'text-center' },
                { data: 'total_paid_fmt', name: 'total_paid_fmt', orderable: false, searchable: false, className: 'text-end' },
                { data: 'total_remaining_fmt', name: 'total_remaining_fmt', orderable: false, searchable: false, className: 'text-end' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json" },
            order: [],
            responsive: true, paging: false,
            drawCallback: function (settings) {
                var json = this.api().ajax.json();
                if (json && json.grandTotal !== undefined) {
                    $('#total-footer').text(formatCurrency(json.grandTotal));
                    $('#count-footer').text(json.recordsFiltered);
                }
            }
        });

        $('#applyFilter').click(function () { table.ajax.reload(); });

        $('#resetFilter').click(function () {
            $('#dateFrom').val(''); $('#dateTo').val('');
            $('#clientFilter').val('').trigger('change');
            $('#paymentMethodFilter').val('');
            $('#quickFilter').val('all');
            table.ajax.reload();
        });

        $('#paymentMethodFilter').change(function () { table.ajax.reload(); });

        // ── Open client payments detail modal ─────────────────────────
        var currentClientId = null;
        var currentClientName = '';

        $(document).on('click', '.btn-client-orders', function () {
            currentClientId = $(this).data('client-id');
            currentClientName = $(this).data('client-name');

            $('#modal-client-name').text(currentClientName);
            $('#modal-loading').show();
            $('#modal-content').hide();
            $('#modal-empty').hide();
            $('#modal-payments-body').empty();

            $('#clientPaymentsModal').modal('show');
            loadClientPayments(currentClientId);
        });

        function loadClientPayments(clientId) {
            $.ajax({
                url: "{{ url('purchases') }}/client/" + clientId + "/payments",
                type: 'GET',
                data: {
                    date_from: $('#dateFrom').val(),
                    date_to: $('#dateTo').val(),
                    payment_method: $('#paymentMethodFilter').val(),
                },
                success: function (res) {
                    $('#modal-loading').hide();
                    if (!res.success) {
                        showToast('error', res.message || 'Erreur');
                        return;
                    }
                    if (!res.payments || res.payments.length === 0) {
                        $('#modal-empty').show();
                        return;
                    }

                    var methodLabels = { cash: 'Espèces', check: 'Chèque', transfer: 'Virement', traite: 'Traite', advance: 'Avance', avoir: 'Avoir' };
                    var methodColors = { cash: 'success', check: 'info', transfer: 'primary', traite: 'warning', advance: 'secondary', avoir: 'dark' };

                    res.payments.forEach(function (p, idx) {
                        var ref = '#REG-' + String(p.payment_id).padStart(6, '0');
                        var orderLink = p.order_number
                            ? '<a href="{{ url("sales/orders") }}/' + p.order_id + '" class="text-primary" target="_blank">' + escHtml(p.order_number) + '</a>'
                            : '<span class="badge bg-warning">Direct client</span>';
                        var methodLabel = methodLabels[p.payment_method] || p.payment_method;
                        var methodColor = methodColors[p.payment_method] || 'secondary';
                        // 'display_amount' is the full sum the client actually handed over —
                        // shown in the table AND prefilled in the edit form, since the update
                        // endpoint now re-splits it between the order and any excess itself.
                        var displayAmount = parseFloat(p.display_amount != null ? p.display_amount : p.amount);
                        var date = p.payment_date || '—';

                        var editBtn = '<button class="btn btn-sm btn-outline-warning btn-edit-pmt" title="Modifier"' +
                            ' data-id="' + p.payment_id + '"' +
                            ' data-amount="' + displayAmount + '"' +
                            ' data-method="' + p.payment_method + '"' +
                            ' data-date="' + (p.payment_date_formatted || '') + '"' +
                            ' data-notes="' + escHtml(p.notes || '') + '"' +
                            ' data-order-id="' + (p.order_id || '') + '">' +
                            '<i class="fas fa-edit"></i></button>';

                        var delBtn = '<button class="btn btn-sm btn-outline-danger btn-delete-pmt" title="Supprimer"' +
                            ' data-id="' + p.payment_id + '"' +
                            ' data-ref="' + ref + '">' +
                            '<i class="fas fa-trash"></i></button>';

                        var row = '<tr>' +
                            '<td class="text-center">' + (idx + 1) + '</td>' +
                            '<td><a href="{{ url("purchases") }}/' + p.payment_id + '">' + escHtml(ref) + '</a></td>' +
                            '<td>' + orderLink + '</td>' +
                            '<td class="text-center"><span class="badge bg-' + methodColor + '">' + methodLabel + '</span></td>' +
                            '<td class="text-end"><strong class="text-success">' + displayAmount.toFixed(2) + ' DH</strong></td>' +
                            '<td class="text-center">' + escHtml(date) + '</td>' +
                            '<td class="text-center">' + editBtn + ' ' + delBtn + '</td>' +
                            '</tr>';
                        $('#modal-payments-body').append(row);
                    });
                    $('#modal-content').show();
                },
                error: function () {
                    $('#modal-loading').hide();
                    showToast('error', 'Impossible de charger les règlements');
                }
            });
        }

        function escHtml(t) {
            if (!t) return '';
            return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        // ── Edit Payment ──────────────────────────────────────────────
        $(document).on('click', '.btn-edit-pmt', function () {
            var $btn = $(this);
            $('#edit_payment_id').val($btn.data('id'));
            $('#edit_amount').val($btn.data('amount'));
            $('#edit_date').val($btn.data('date'));
            $('#edit_method').val($btn.data('method'));
            $('#edit_notes').val($btn.data('notes'));
            $('#edit_document').val('');
            $('#editPaymentModal').modal('show');
        });

        $('#editPaymentForm').submit(function (e) {
            e.preventDefault();
            var id = $('#edit_payment_id').val();
            var formData = new FormData(this);
            formData.append('_method', 'PUT');

            $.ajax({
                url: "{{ url('purchases') }}/" + id,
                type: 'POST', data: formData, processData: false, contentType: false,
                success: function (res) {
                    if (res.success) {
                        $('#editPaymentModal').modal('hide');
                        showToast('success', res.message);
                        table.ajax.reload();
                        loadClientPayments(currentClientId);
                    } else { showToast('error', res.message); }
                },
                error: function (xhr) {
                    showToast('error', xhr.responseJSON?.message || 'Erreur');
                }
            });
        });

        // ── Delete Payment ────────────────────────────────────────────
        var deleteId = null;
        $(document).on('click', '.btn-delete-pmt', function () {
            deleteId = $(this).data('id');
            $('#delete_ref').text($(this).data('ref'));
            $('#deletePaymentModal').modal('show');
        });

        $('#confirmDeleteBtn').click(function () {
            if (!deleteId) return;
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Suppression...');

            $.ajax({
                url: "{{ url('purchases') }}/" + deleteId,
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) {
                        $('#deletePaymentModal').modal('hide');
                        showToast('success', res.message);
                        table.ajax.reload();
                        loadClientPayments(currentClientId);
                    } else { showToast('error', res.message); }
                    $btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i>Supprimer');
                },
                error: function (xhr) {
                    showToast('error', xhr.responseJSON?.message || 'Erreur');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i>Supprimer');
                }
            });
        });

        // ── Add Payment from Index ────────────────────────────────────
        $('#api_client_select').select2({
            placeholder: 'Rechercher un client...',
            allowClear: true, width: '100%',
            dropdownParent: $('#addPaymentIndexModal'),
            language: { noResults: function() { return 'Aucun client trouvé'; } }
        });

        // Reset form when modal opens
        $('#btnAddPaymentFromIndex').click(function () {
            $('#addPaymentIndexForm')[0].reset();
            $('#api_amount').val('');
            $('#api_notes').val('');
            $('#api_client_select').val('').trigger('change');
            $('#api_balance_wrapper').hide();
            $('#api_orders_wrapper').hide();
            $('#apiUnpaidOrdersTable tbody').empty();
            $('#apiDistributionPreview').hide();
            $('#api_date').val(new Date().toISOString().split('T')[0]);
            $('#apiPaymentDetailsSection').hide();
            $('#apiChequeDetails').hide();
            $('#apiTraiteDetails').hide();
            $('#apiCheckImagesPreview').empty();
            $('#apiTraiteDocumentPreview').empty();
            $('#api_check_number, #api_check_amount, #api_bank_name, #api_account_holder, #api_issue_date, #api_traite_number, #api_traite_amount, #api_traite_bank_name, #api_drawee, #api_traite_issue_date, #api_due_date')
                .prop('required', false);
            $('#addPaymentIndexModal').modal('show');
        });

        // Show cheque/traite details depending on the method — same as client page
        $('#api_method').on('change', function () {
            var method = $(this).val();
            $('#apiPaymentDetailsSection').show();

            if (method === 'check') {
                $('#apiChequeDetails').show();
                $('#apiTraiteDetails').hide();
                $('#api_check_number, #api_check_amount, #api_bank_name, #api_account_holder, #api_issue_date')
                    .prop('required', true);
                $('#api_traite_number, #api_traite_amount, #api_traite_bank_name, #api_drawee, #api_traite_issue_date, #api_due_date')
                    .prop('required', false);
            } else if (method === 'traite') {
                $('#apiChequeDetails').hide();
                $('#apiTraiteDetails').show();
                $('#api_traite_number, #api_traite_amount, #api_traite_bank_name, #api_drawee, #api_traite_issue_date, #api_due_date')
                    .prop('required', true);
                $('#api_check_number, #api_check_amount, #api_bank_name, #api_account_holder, #api_issue_date')
                    .prop('required', false);
            } else {
                $('#apiPaymentDetailsSection').hide();
                $('#apiChequeDetails').hide();
                $('#apiTraiteDetails').hide();
                $('#api_check_number, #api_check_amount, #api_bank_name, #api_account_holder, #api_issue_date, #api_traite_number, #api_traite_amount, #api_traite_bank_name, #api_drawee, #api_traite_issue_date, #api_due_date')
                    .prop('required', false);
            }
        });

        // File previews for cheque images and traite document
        $('#api_check_images').on('change', function () {
            var previewContainer = $('#apiCheckImagesPreview');
            previewContainer.empty();
            if (this.files) {
                Array.from(this.files).forEach(function (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        previewContainer.append($(
                            '<div class="position-relative d-inline-block me-2 mb-2" style="width:120px;">' +
                            '<div class="border rounded p-1 bg-light text-center">' +
                            (file.type.indexOf('image/') === 0
                                ? '<img src="' + e.target.result + '" class="img-fluid rounded" style="height:80px;object-fit:cover;">'
                                : '<i class="fas fa-file-pdf fa-3x text-danger"></i>') +
                            '<div class="small text-truncate mt-1">' + escHtml(file.name) + '</div>' +
                            '</div></div>'
                        ));
                    };
                    reader.readAsDataURL(file);
                });
            }
        });

        $('#api_traite_document').on('change', function () {
            var previewContainer = $('#apiTraiteDocumentPreview');
            previewContainer.empty();
            if (this.files && this.files[0]) {
                var file = this.files[0];
                var reader = new FileReader();
                reader.onload = function (e) {
                    previewContainer.append($(
                        '<div class="position-relative d-inline-block me-2 mb-2" style="width:150px;">' +
                        '<div class="border rounded p-2 bg-light text-center">' +
                        (file.type.indexOf('image/') === 0
                            ? '<img src="' + e.target.result + '" class="img-fluid rounded" style="height:100px;object-fit:cover;">'
                            : '<i class="fas fa-file-pdf fa-4x text-danger"></i>') +
                        '<div class="small text-truncate mt-1">' + escHtml(file.name) + '</div>' +
                        '</div></div>'
                    ));
                };
                reader.readAsDataURL(file);
            }
        });

        // Client selected → load balance & unpaid orders
        $('#api_client_select').on('change', function () {
            var clientId = $(this).val();
            if (!clientId) {
                $('#api_balance_wrapper').hide();
                $('#api_orders_wrapper').hide();
                $('#apiUnpaidOrdersTable tbody').empty();
                $('#apiDistributionPreview').hide();
                return;
            }
            loadClientBalance(clientId);
            loadUnpaidOrdersForPayment(clientId);
            updateDistributionPreview();
        });

        function loadClientBalance(clientId) {
            $.ajax({
                url: "{{ url('clients') }}/" + clientId + "/balance",
                type: 'GET',
                success: function (res) {
                    if (res && res.balance !== undefined) {
                        var bal = parseFloat(res.balance);
                        $('#api_client_balance').text(bal.toFixed(2) + ' DH');
                        $('#api_balance_wrapper').show();
                    }
                }
            });
        }

        function loadUnpaidOrdersForPayment(clientId) {
            $('#apiUnpaidOrdersTable tbody').html('<tr><td colspan="6" class="text-center text-muted">Chargement...</td></tr>');
            $('#api_orders_wrapper').show();

            $.ajax({
                url: "{{ url('sales-orders') }}/client/" + clientId + "/unpaid",
                type: 'GET',
                success: function (res) {
                    var tbody = $('#apiUnpaidOrdersTable tbody');
                    tbody.empty();
                    if (res && res.orders && res.orders.length > 0) {
                        res.orders.forEach(function (order, i) {
                            var unpaid = parseFloat(order.unpaid_amount) || 0;
                            tbody.append(
                                '<tr data-order-id="' + order.order_id + '" data-unpaid="' + unpaid + '">' +
                                '<td class="text-center"><input type="checkbox" class="form-check-input api-order-check" checked></td>' +
                                '<td>' + (i + 1) + '</td>' +
                                '<td><a href="{{ url("sales/orders") }}/' + order.order_id + '" target="_blank">' + escHtml(order.order_number) + '</a></td>' +
                                '<td class="text-end">' + order.total_amount + ' DH</td>' +
                                '<td class="text-end text-danger">' + unpaid.toFixed(2) + ' DH</td>' +
                                '<td class="text-end"><input type="number" class="form-control form-control-sm api-order-amount" style="width:100px;display:inline-block;" value="' + unpaid.toFixed(2) + '" step="0.01" min="0"></td>' +
                                '</tr>'
                            );
                        });
                        // Select All handler
                        $('#apiSelectAll').off('change').on('change', function () {
                            $('.api-order-check').prop('checked', $(this).is(':checked'));
                            updateDistributionPreview();
                        });
                        // Individual check/amount change
                        $('.api-order-check, .api-order-amount').off('input change').on('input change', function () {
                            updateDistributionPreview();
                        });
                    } else {
                        tbody.html('<tr><td colspan="6" class="text-center text-muted">Aucune vente impayée</td></tr>');
                    }
                },
                error: function () {
                    $('#apiUnpaidOrdersTable tbody').html('<tr><td colspan="6" class="text-center text-danger">Erreur</td></tr>');
                }
            });
        }

        function updateDistributionPreview() {
            var amount = parseFloat($('#api_amount').val()) || 0;
            var clientId = $('#api_client_select').val();
            if (!clientId || amount <= 0) {
                $('#apiDistributionPreview').hide();
                return;
            }

            var rows = $('#apiUnpaidOrdersTable tbody tr[data-order-id]');
            if (rows.length === 0 || rows.find('td').length <= 1) {
                $('#apiDistributionDetails').html('<em>Aucune vente impayée — le montant sera ajouté au solde client.</em>');
                $('#apiDistributionPreview').show();
                return;
            }

            var remaining = amount;
            var details = [];

            rows.each(function () {
                if (remaining <= 0.005) return;
                var $row = $(this);
                if (!$row.find('.api-order-check').is(':checked')) return;
                var orderAmount = parseFloat($row.find('.api-order-amount').val()) || 0;
                var unpaid = parseFloat($row.data('unpaid')) || 0;
                var apply = Math.min(remaining, orderAmount, unpaid);
                if (apply > 0) {
                    details.push('<div>→ ' + $row.find('td:eq(2)').text() + ' : <strong>' + apply.toFixed(2) + ' DH</strong> (Reste: ' + unpaid.toFixed(2) + ' DH)</div>');
                    remaining -= apply;
                }
            });

            if (details.length === 0) {
                $('#apiDistributionDetails').html('<em>Tout le montant sera ajouté au solde client.</em>');
            } else if (remaining > 0.005) {
                details.push('<div class="text-success mt-1">→ <strong>Solde client: +' + remaining.toFixed(2) + ' DH</strong></div>');
            }

            $('#apiDistributionDetails').html(details.join(''));
            $('#apiDistributionPreview').show();
        }

        $('#api_amount').on('input', function () { updateDistributionPreview(); });

        // Submit add payment form — send selected order amounts
        $('#addPaymentIndexForm').submit(function (e) {
            e.preventDefault();

            var amount = parseFloat($('#api_amount').val()) || 0;
            var paymentMethod = $('#api_method').val();

            // Cheque/traite validation — same rules as the client page
            if (paymentMethod === 'check') {
                if (!$('#api_check_number').val()) {
                    showToast('error', 'Veuillez saisir le numéro de chèque');
                    return;
                }
                if (!$('#api_check_amount').val()) {
                    showToast('error', 'Veuillez saisir le montant du chèque');
                    return;
                }
                if (parseFloat($('#api_check_amount').val()) !== amount) {
                    showToast('error', 'Le montant du chèque doit être égal au montant du paiement');
                    return;
                }
            } else if (paymentMethod === 'traite') {
                if (!$('#api_traite_number').val()) {
                    showToast('error', 'Veuillez saisir le numéro de traite');
                    return;
                }
                if (!$('#api_traite_amount').val()) {
                    showToast('error', 'Veuillez saisir le montant de la traite');
                    return;
                }
                if (parseFloat($('#api_traite_amount').val()) !== amount) {
                    showToast('error', 'Le montant de la traite doit être égal au montant du paiement');
                    return;
                }
            }

            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Enregistrement...');

            var formData = new FormData(this);

            // Collect selected order amounts
            var selectedOrders = [];
            $('#apiUnpaidOrdersTable tbody tr[data-order-id]').each(function () {
                var $row = $(this);
                if ($row.find('.api-order-check').is(':checked')) {
                    var orderId = $row.data('order-id');
                    var payAmount = parseFloat($row.find('.api-order-amount').val()) || 0;
                    if (payAmount > 0) {
                        selectedOrders.push({ order_id: orderId, amount: payAmount });
                    }
                }
            });

            if (selectedOrders.length > 0) {
                selectedOrders.forEach(function (o, i) {
                    formData.append('selected_orders[' + i + '][order_id]', o.order_id);
                    formData.append('selected_orders[' + i + '][amount]', o.amount);
                });
            }

            $.ajax({
                url: "{{ route('purchases.add-payment') }}",
                type: 'POST', data: formData, processData: false, contentType: false,
                success: function (res) {
                    if (res.success) {
                        $('#addPaymentIndexModal').modal('hide');
                        showToast('success', res.message);
                        table.ajax.reload();
                    } else { showToast('error', res.message); }
                    $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Enregistrer');
                },
                error: function (xhr) {
                    showToast('error', xhr.responseJSON?.message || 'Erreur lors de l\'ajout du paiement');
                    $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Enregistrer');
                }
            });
        });
    });
    </script>
@endpush
