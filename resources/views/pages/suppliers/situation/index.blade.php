@extends('layouts.app')

@section('title', 'Situation des Fournisseurs')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Achats</h6>
                        <h3>{{ $summary['total_purchases'] }}</h3>
                        <small>Montant: {{ number_format($summary['total_amount'], 2, ',', '.') }} DH</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Payé</h6>
                        <h3>{{ number_format($summary['total_paid'], 2, ',', '.') }} DH</h3>
                        <small>{{ $summary['paid_purchases'] }} achats payés</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Impayé</h6>
                        <h3>{{ number_format($summary['total_unpaid'], 2, ',', '.') }} DH</h3>
                        <small>{{ $summary['partial_purchases'] }} avances, {{ $summary['pending_purchases'] }}
                            impayés</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Taux de Paiement</h6>
                        @php
                            $paymentRate =
                                $summary['total_amount'] > 0
                                    ? ($summary['total_paid'] / $summary['total_amount']) * 100
                                    : 0;
                        @endphp
                        <h3>{{ number_format($paymentRate, 1) }}%</h3>
                        <small>{{ number_format($summary['total_paid'], 2, ',', '.') }} /
                            {{ number_format($summary['total_amount'], 2, ',', '.') }} DH</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Filtres</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Fournisseur</label>
                        <select class="form-control select2" id="supplier_id" name="supplier_id">
                            <option value="">Tous les fournisseurs</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->supplier_id }}">
                                    {{ $supplier->company_name ?? $supplier->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Statut Paiement</label>
                        <select class="form-control" id="payment_status" name="payment_status">
                            <option value="">Tous</option>
                            <option value="pending">Non Payé</option>
                            <option value="partial">Avance</option>
                            <option value="paid">Payé</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date début</label>
                        <input type="date" class="form-control" id="date_from" name="date_from">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date fin</label>
                        <input type="date" class="form-control" id="date_to" name="date_to">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-primary" id="applyFilters">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>
                        <button type="button" class="btn btn-success" id="exportData">
                            <i class="fas fa-file-excel"></i> Exporter
                        </button>
                        <button type="button" class="btn btn-secondary" id="resetFilters">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Grouped Supplier Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Situation par Fournisseur</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="situationTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fournisseur</th>
                                <th class="text-center">Nb. Achats</th>
                                <th class="text-end">Montant Total</th>
                                <th class="text-end">Total Payé</th>
                                <th class="text-end">Reste</th>
                                <th class="text-center">Solde</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════ SUPPLIER DETAILS MODAL ══════════════════ --}}
    <div class="modal fade" id="supplierDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white flex-wrap gap-2">
                    <h5 class="modal-title" id="supplierDetailsTitle">
                        <i class="fas fa-list me-2"></i>Achats du fournisseur
                    </h5>
                    <div class="d-flex align-items-center gap-2 ms-auto me-2 flex-wrap">
                        <label class="mb-0 small fw-semibold text-white">Du :</label>
                        <input type="date" id="modal_date_from"
                            style="padding:3px 8px; border:1px solid #ced4da; border-radius:4px; font-size:12px; background:#fff; color:#333;">
                        <label class="mb-0 small fw-semibold text-white">Au :</label>
                        <input type="date" id="modal_date_to"
                            style="padding:3px 8px; border:1px solid #ced4da; border-radius:4px; font-size:12px; background:#fff; color:#333;">
                        <button type="button" class="btn btn-sm btn-light" id="resetModalDateBtn" title="Réinitialiser">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Supplier balance row --}}
                    <div id="supplierBalanceRow" class="alert alert-info d-flex align-items-center gap-3 mb-3"
                        style="display:none!important">
                        <i class="fas fa-wallet fs-4"></i>
                        <div>
                            <strong>Solde disponible:</strong>
                            <span id="modalSupplierBalance" class="fs-5 ms-2 fw-bold">—</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-success ms-auto" id="btnPayByBalanceFromDetails">
                            <i class="fas fa-coins me-1"></i>Payer par Solde
                        </button>
                    </div>

                    <div id="supplierDetailsLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Chargement des achats...</p>
                    </div>
                    <div id="supplierDetailsContent" style="display:none;">
                        <div class="row mb-3" id="modalSummaryRow"></div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="supplierPurchasesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Date</th>
                                        <th>N° Achat</th>
                                        <th class="text-end">Montant</th>
                                        <th class="text-end">Payé</th>
                                        <th class="text-end">Reste</th>
                                        <th class="text-center">Statut</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="supplierPurchasesBody"></tbody>
                                <tfoot id="supplierPurchasesFoot"></tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="printSupplierSituationBtn">
                        <i class="fas fa-file-pdf me-1"></i> Télécharger Situation
                    </button>
                    <a href="#" id="supplierFullSituationLink" class="btn btn-info">
                        <i class="fas fa-chart-line me-1"></i> Situation Complète
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════ PAY BY BALANCE MODAL ══════════════════ --}}
    <div class="modal fade" id="payByBalanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-coins me-2"></i>Payer par Solde</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="payByBalanceForm">
                    <input type="hidden" id="pbb_purchase_id">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            Solde disponible: <strong id="pbb_available_credit">0,00 DH</strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Achat: <strong id="pbb_purchase_number">—</strong></label>
                            <div>Reste à payer: <strong id="pbb_remaining" class="text-danger">—</strong></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Montant à payer par solde *</label>
                            <input type="number" class="form-control" id="pbb_amount" step="0.01" min="0.01"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" class="form-control" id="pbb_date" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Payer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════ ADD BALANCE MODAL ══════════════════ --}}
    <div class="modal fade" id="addBalanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-wallet me-2"></i>Ajouter un Solde Fournisseur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addBalanceForm">
                    <div class="modal-body">
                        <p class="text-muted small">Enregistrez un paiement direct au fournisseur (non attribué à un achat
                            spécifique). Ce montant sera crédité en solde et peut être utilisé pour payer des achats futurs.
                            <br><strong>Montant négatif</strong> : pour récupérer du solde (retrait de crédit).
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Montant (DH) * <small class="text-muted">(négatif pour récupérer)</small></label>
                            <input type="text" class="form-control" id="bal_amount"
                                pattern="^-?[0-9]+([.,][0-9]+)?$" inputmode="decimal"
                                placeholder="ex: 950,40" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" class="form-control" id="bal_date" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="bal_notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-info"><i class="fas fa-save me-1"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════ DISTRIBUTE PAYMENT MODAL ══════════════════ --}}
    <div class="modal fade" id="distributePaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>Paiement Global Fournisseur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="distributePaymentForm" enctype="multipart/form-data">
                    <input type="hidden" id="dist_check_id" name="check_id">
                    <input type="hidden" id="dist_traite_id" name="traite_id">
                    <input type="hidden" id="dist_total_rest_raw" value="0">
                    <div class="modal-body">
                        <p class="text-muted small">Le montant sera distribué automatiquement sur les achats impayés (ordre
                            chronologique).</p>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-danger bg-opacity-10 border border-danger rounded">
                                    <span class="small text-danger fw-semibold">Total impayé</span>
                                    <strong class="text-danger" id="dist_total_rest_display">—</strong>
                                </div>
                            </div>
                            <div class="col-6" id="dist_balance_info_row" style="display:none;">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-success bg-opacity-10 border border-success rounded">
                                    <span class="small text-success fw-semibold">Solde crédit</span>
                                    <strong class="text-success" id="dist_balance_info_display">—</strong>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Montant (DH) *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="dist_amount" name="amount"
                                        step="0.01" min="0.01" required>
                                    <span id="dist_solde_badge" class="input-group-text text-success fw-bold"
                                        style="display:none;" title="Le surplus sera ajouté au solde fournisseur">
                                        +solde
                                    </span>
                                </div>
                                <small id="dist_excess_info" class="text-success mt-1" style="display:none;"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date *</label>
                                <input type="date" class="form-control" id="dist_date" name="payment_date"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Méthode *</label>
                                <select class="form-control" id="dist_method" name="payment_method" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="cash">Espèces</option>
                                    <option value="bank_transfer">Virement Bancaire</option>
                                    <option value="check">Chèque</option>
                                    <option value="traite">Traite</option>
                                    <option value="credit_card">Carte de crédit</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Document</label>
                                <input type="file" class="form-control" id="dist_file" name="payment_file"
                                    accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>

                        {{-- Check selection (shown when check selected) --}}
                        <div id="dist_check_section" class="border rounded p-3 bg-light mb-3" style="display:none;">
                            <h6 class="mb-2"><i class="fas fa-money-check me-1"></i>Sélection du Chèque</h6>
                            <div id="dist_check_selected_info" class="alert alert-success py-2 mb-2"
                                style="display:none;">
                                <i class="fas fa-check-circle me-1"></i>Chèque sélectionné: <strong
                                    id="dist_check_selected_label"></strong>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2"
                                    id="dist_check_clear_btn">Changer</button>
                            </div>
                            <div id="dist_check_picker">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" id="dist_checks_table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>N° Chèque</th>
                                                <th>Banque</th>
                                                <th>Montant</th>
                                                <th>Disponible</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="dist_checks_body">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Chargement...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Traite section (shown when traite selected) --}}
                        <div id="dist_traite_section" class="border rounded p-3 bg-light mb-3" style="display:none;">
                            <h6 class="mb-2"><i class="fas fa-file-invoice me-1"></i>Traite</h6>
                            <div id="dist_traite_selected_info" class="alert alert-success py-2 mb-2"
                                style="display:none;">
                                <i class="fas fa-check-circle me-1"></i>Traite sélectionnée: <strong
                                    id="dist_traite_selected_label"></strong>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2"
                                    id="dist_traite_clear_btn">Changer</button>
                            </div>
                            <div id="dist_traite_picker">
                                <ul class="nav nav-tabs mb-2" id="dist_traite_tabs">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="#dist_traite_existing_tab"
                                            data-bs-toggle="tab">Existante</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#dist_traite_new_tab" data-bs-toggle="tab">Nouvelle</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="dist_traite_existing_tab">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>N° Traite</th>
                                                        <th>Banque</th>
                                                        <th>Montant</th>
                                                        <th>Échéance</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dist_traites_body">
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">Chargement...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="dist_traite_new_tab">
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label form-label-sm">N° Traite</label>
                                                <input type="text" class="form-control form-control-sm"
                                                    id="dist_traite_number" name="traite_number"
                                                    placeholder="Auto si vide">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label form-label-sm">Date d'Échéance *</label>
                                                <input type="date" class="form-control form-control-sm"
                                                    id="dist_traite_due" name="traite_due_date">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label form-label-sm">Banque</label>
                                                <input type="text" class="form-control form-control-sm"
                                                    id="dist_traite_bank" name="traite_bank"
                                                    placeholder="Nom de la banque">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="dist_notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success"><i
                                class="fas fa-save me-1"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════ PAYMENT METHOD SELECTION MODAL ══════════════════ --}}
    <div class="modal fade" id="paymentMethodModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Choisir le moyen de paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <button type="button" class="list-group-item list-group-item-action payment-type-select"
                            data-type="cash">
                            <i class="fas fa-money-bill-wave me-2 text-success"></i> Espèces
                        </button>
                        <button type="button" class="list-group-item list-group-item-action payment-type-select"
                            data-type="bank_transfer">
                            <i class="fas fa-university me-2 text-primary"></i> Virement Bancaire
                        </button>
                        <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="collapse"
                            data-bs-target="#checkOptions">
                            <i class="fas fa-money-check me-2 text-info"></i> Chèque
                            <i class="fas fa-chevron-down float-end"></i>
                        </button>
                        <div class="collapse" id="checkOptions">
                            <div class="list-group">
                                <button type="button"
                                    class="list-group-item list-group-item-action payment-type-select ps-5"
                                    data-type="check" data-check-type="entreprise">
                                    <i class="fas fa-building me-2"></i> Chèque Entreprise
                                </button>
                                <button type="button"
                                    class="list-group-item list-group-item-action payment-type-select ps-5"
                                    data-type="check" data-check-type="client">
                                    <i class="fas fa-user me-2"></i> Chèque Client
                                </button>
                                <button type="button"
                                    class="list-group-item list-group-item-action payment-type-select ps-5"
                                    data-type="new_check_entreprise">
                                    <i class="fas fa-plus-circle me-2 text-success"></i> Nouveau Chèque Entreprise
                                </button>
                                <button type="button"
                                    class="list-group-item list-group-item-action payment-type-select ps-5"
                                    data-type="new_check_client">
                                    <i class="fas fa-plus-circle me-2 text-success"></i> Nouveau Chèque Client
                                </button>
                            </div>
                        </div>
                        <button type="button" class="list-group-item list-group-item-action payment-type-select"
                            data-type="traite">
                            <i class="fas fa-file-invoice me-2 text-warning"></i> Traite
                        </button>
                        <button type="button" class="list-group-item list-group-item-action payment-type-select"
                            data-type="credit_card">
                            <i class="fas fa-credit-card me-2 text-warning"></i> Carte de crédit
                        </button>
                        <button type="button" id="solde-payment-option"
                            class="list-group-item list-group-item-action payment-type-select" data-type="solde"
                            style="display:none;">
                            <i class="fas fa-coins me-2 text-success"></i> Solde fournisseur
                            <span id="solde-payment-credit" class="badge bg-success float-end"></span>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════ CASH MODAL ══════════════════ --}}
    <div class="modal fade" id="cashModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Paiement en Espèces</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="cashForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Montant *</label>
                            <input type="number" class="form-control" id="cash_amount" min="0.01" step="0.01"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" class="form-control" id="cash_date" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Document (Reçu)</label>
                            <input type="file" class="form-control" id="cash_file" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="cash_notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="showPaymentMethodModal()">Retour</button>
                        <button type="submit" class="btn btn-primary">Ajouter le paiement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════ BANK TRANSFER MODAL ══════════════════ --}}
    <div class="modal fade" id="bankTransferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Virement Bancaire</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bankTransferForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Montant *</label>
                            <input type="number" class="form-control" id="transfer_amount" min="0.01"
                                step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" class="form-control" id="transfer_date" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Numéro de Transaction</label>
                            <input type="text" class="form-control" id="transfer_reference">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Document</label>
                            <input type="file" class="form-control" id="transfer_file" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="transfer_notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="showPaymentMethodModal()">Retour</button>
                        <button type="submit" class="btn btn-primary">Ajouter le paiement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════ TRAITE MODAL ══════════════════ --}}
    <div class="modal fade" id="traiteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Paiement par Traite</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="traiteForm">
                    <input type="hidden" id="traite_id_hidden">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Montant *</label>
                            <input type="number" class="form-control" id="traite_amount" min="0.01" step="0.01"
                                required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Date d'Émission *</label>
                            <input type="date" class="form-control" id="traite_issue_date"
                                value="{{ date('Y-m-d') }}" required>
                        </div>

                        {{-- Selected traite info --}}
                        <div id="traite_selected_info" class="alert alert-success py-2 mb-2" style="display:none;">
                            <i class="fas fa-check-circle me-1"></i>Traite sélectionnée: <strong
                                id="traite_selected_label"></strong>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2"
                                id="traite_clear_btn">Changer</button>
                        </div>

                        <div id="traite_picker_section">
                            <ul class="nav nav-tabs mb-2" id="traite_tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#traite_existing_tab" data-bs-toggle="tab">Traite
                                        Existante</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#traite_new_tab" data-bs-toggle="tab">Nouvelle Traite</a>
                                </li>
                            </ul>
                            <div class="tab-content border border-top-0 rounded-bottom p-3">
                                <div class="tab-pane active" id="traite_existing_tab">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>N° Traite</th>
                                                    <th>Banque</th>
                                                    <th>Montant</th>
                                                    <th>Échéance</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="traite_existing_body">
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">Chargement...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane" id="traite_new_tab">
                                    <div class="alert alert-info small mb-3">
                                        <i class="fas fa-info-circle me-1"></i>
                                        La traite sera créée avec statut <strong>Encaissée</strong>.
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">N° Traite</label>
                                            <input type="text" class="form-control" id="traite_number"
                                                placeholder="Auto si vide">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Date d'Échéance *</label>
                                            <input type="date" class="form-control" id="traite_due_date">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Banque</label>
                                            <input type="text" class="form-control" id="traite_bank"
                                                placeholder="Nom de la banque">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Notes</label>
                                            <textarea class="form-control" id="traite_notes" rows="1"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="showPaymentMethodModal()">Retour</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-check me-1"></i>Encaisser et
                            Payer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════ CHECK SELECTION MODAL ══════════════════ --}}
    <div class="modal fade" id="checkSelectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkModalTitle">Sélectionner un Chèque</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="selected_check_type">
                    <input type="hidden" id="selected_check_type_value">

                    <div id="availableChecksList" style="display:none;">
                        <h6 id="availableChecksTitle">Chèques disponibles</h6>
                        <div class="table-responsive">
                            <table class="table table-sm" id="checksTable">
                                <thead>
                                    <tr>
                                        <th>N° Chèque</th>
                                        <th>Banque</th>
                                        <th>Montant Total</th>
                                        <th>Montant Disponible</th>
                                        <th>Date d'Émission</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="checksBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div id="newCheckForm" style="display:none;">
                        <h6 id="newCheckTitle">Ajouter un nouveau chèque</h6>
                        <form id="quickCheckForm">
                            <input type="hidden" id="new_check_type" value="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Numéro de Chèque *</label>
                                    <input type="text" class="form-control" id="new_check_number" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Banque *</label>
                                    <input type="text" class="form-control" id="new_check_bank" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Montant *</label>
                                    <input type="number" class="form-control" id="new_check_amount" min="0.01"
                                        step="0.01" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date d'Émission *</label>
                                    <input type="date" class="form-control" id="new_check_issue_date"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date d'Échéance *</label>
                                    <input type="date" class="form-control" id="new_check_due_date"
                                        value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Image du Chèque *</label>
                                    <input type="file" class="form-control" id="new_check_file"
                                        accept=".pdf,.jpg,.jpeg,.png" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Bénéficiaire</label>
                                    <input type="text" class="form-control" id="new_check_payee">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="showPaymentMethodModal()">Retour</button>
                    <button type="button" class="btn btn-success" id="saveNewCheckBtn"
                        style="display:none;">Enregistrer le chèque</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════ CREDIT CARD MODAL ══════════════════ --}}
    <div class="modal fade" id="creditCardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Paiement par Carte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="creditCardForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Montant *</label>
                            <input type="number" class="form-control" id="card_amount" min="0.01" step="0.01"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" class="form-control" id="card_date" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type de Carte</label>
                            <select class="form-control" id="card_type">
                                <option value="visa">Visa</option>
                                <option value="mastercard">Mastercard</option>
                                <option value="amex">American Express</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Document (Reçu)</label>
                            <input type="file" class="form-control" id="card_file" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="card_notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="showPaymentMethodModal()">Retour</button>
                        <button type="submit" class="btn btn-primary">Ajouter le paiement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════ EDIT PAYMENT MODAL ══════════════════ --}}
    <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Modifier le Paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editPaymentForm" enctype="multipart/form-data">
                    <input type="hidden" id="edit_doc_id">
                    <input type="hidden" id="edit_original_method">
                    <input type="hidden" id="edit_check_id" name="check_id">
                    <input type="hidden" id="edit_traite_id" name="traite_id">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Méthode de paiement *</label>
                                <select class="form-control" id="edit_method" name="payment_method" required></select>
                                <small id="edit_method_hint" class="text-warning" style="display:none;">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Un chèque ne peut être remplacé que par
                                    des espèces.
                                </small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Montant *</label>
                                <input type="number" class="form-control" id="edit_amount" name="amount"
                                    step="0.01" min="0.01" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date *</label>
                                <input type="date" class="form-control" id="edit_date" name="payment_date" required>
                            </div>
                        </div>

                        {{-- Check picker (shown when switching to check) --}}
                        <div id="edit_check_section" class="border rounded p-3 bg-light mb-3" style="display:none;">
                            <h6 class="mb-2"><i class="fas fa-money-check me-1"></i>Sélection du Chèque</h6>
                            <div id="edit_check_selected_info" class="alert alert-success py-2 mb-2"
                                style="display:none;">
                                <i class="fas fa-check-circle me-1"></i>Chèque: <strong
                                    id="edit_check_selected_label"></strong>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2"
                                    id="edit_check_clear_btn">Changer</button>
                            </div>
                            <div id="edit_check_picker">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>N° Chèque</th>
                                                <th>Banque</th>
                                                <th>Montant</th>
                                                <th>Disponible</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="edit_checks_body">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Chargement...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Traite picker (shown when switching to traite) --}}
                        <div id="edit_traite_section" class="border rounded p-3 bg-light mb-3" style="display:none;">
                            <h6 class="mb-2"><i class="fas fa-file-invoice me-1"></i>Traite</h6>
                            <div id="edit_traite_selected_info" class="alert alert-success py-2 mb-2"
                                style="display:none;">
                                <i class="fas fa-check-circle me-1"></i>Traite: <strong
                                    id="edit_traite_selected_label"></strong>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2"
                                    id="edit_traite_clear_btn">Changer</button>
                            </div>
                            <div id="edit_traite_picker">
                                <ul class="nav nav-tabs mb-2">
                                    <li class="nav-item"><a class="nav-link active" href="#edit_traite_existing"
                                            data-bs-toggle="tab">Existante</a></li>
                                    <li class="nav-item"><a class="nav-link" href="#edit_traite_new"
                                            data-bs-toggle="tab">Nouvelle</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="edit_traite_existing">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>N° Traite</th>
                                                        <th>Banque</th>
                                                        <th>Montant</th>
                                                        <th>Échéance</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="edit_traites_body">
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">Chargement...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="edit_traite_new">
                                        <div class="alert alert-info small mb-2">
                                            <i class="fas fa-info-circle me-1"></i>Créée avec statut
                                            <strong>Encaissée</strong>.
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label form-label-sm">N° Traite</label>
                                                <input type="text" class="form-control form-control-sm"
                                                    id="edit_traite_number" name="traite_number"
                                                    placeholder="Auto si vide">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label form-label-sm">Échéance *</label>
                                                <input type="date" class="form-control form-control-sm"
                                                    id="edit_traite_due" name="traite_due_date">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label form-label-sm">Banque</label>
                                                <input type="text" class="form-control form-control-sm"
                                                    id="edit_traite_bank" name="traite_bank">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Document (remplacer)</label>
                                <input type="file" class="form-control" id="edit_file" name="document"
                                    accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning"><i
                                class="fas fa-save me-1"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        .btn-xs {
            padding: 0.15rem 0.4rem;
            font-size: 0.72rem;
        }

        .entreprise-check {
            border-left: 4px solid #007bff;
        }

        .client-check {
            border-left: 4px solid #28a745;
        }

        #supplierPurchasesTable tbody tr {
            cursor: default;
        }

        #supplierPurchasesTable tbody tr:hover {
            background-color: rgba(0, 0, 0, .04);
        }

        .btn-group-sm>.btn {
            font-size: 0.75rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>

    <script>
        let currentPurchaseData = {};
        let currentSupplierId = null;
        let currentSupplierName = '';
        let currentAvailableCredit = 0;

        // ── Helpers ────────────────────────────────────────────────────────────
        function showPaymentMethodModal() {
            // Show/hide solde option based on available credit
            if (currentAvailableCredit > 0.005) {
                $('#solde-payment-credit').text(currentAvailableCredit.toFixed(2) + ' DH');
                $('#solde-payment-option').show();
            } else {
                $('#solde-payment-option').hide();
            }
            $('.modal.show').each(function() {
                $(this).modal('hide');
            });
            setTimeout(function() {
                $('#paymentMethodModal').modal('show');
            }, 300);
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            var d = new Date(dateString);
            if (isNaN(d.getTime())) return 'N/A';
            return String(d.getDate()).padStart(2, '0') + '/' +
                String(d.getMonth() + 1).padStart(2, '0') + '/' +
                d.getFullYear();
        }

        function showToast(type, message) {
            var toast = $('<div class="toast align-items-center text-white bg-' +
                (type === 'success' ? 'success' : 'danger') +
                ' border-0" role="alert">' +
                '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
                '</div></div>');
            $('#toast-container').append(toast);
            var bsToast = new bootstrap.Toast(toast[0]);
            bsToast.show();
            setTimeout(function() {
                toast.remove();
            }, 5000);
        }

        function loadSupplierPurchases() {
            $('#supplierDetailsLoading').show();
            $('#supplierDetailsContent').hide();
            $('#supplierPurchasesBody').empty();
            $('#supplierPurchasesFoot').empty();
            $('#modalSummaryRow').empty();

            $.ajax({
                url: "{{ url('suppliers/situation/supplier') }}/" + currentSupplierId + "/purchases",
                type: 'GET',
                data: {
                    payment_status: $('#payment_status').val(),
                    date_from: $('#modal_date_from').val(),
                    date_to: $('#modal_date_to').val()
                },
                success: function(response) {
                    if (response.success) {
                        renderSupplierPurchases(response.data);
                    } else {
                        showToast('error', 'Erreur lors du chargement');
                        $('#supplierDetailsLoading').hide();
                    }
                },
                error: function() {
                    showToast('error', 'Erreur lors du chargement des achats');
                    $('#supplierDetailsLoading').hide();
                }
            });
        }

        function refreshDetailsModal() {
            if (currentSupplierId) {
                setTimeout(function() { loadSupplierPurchases(); }, 600);
            }
        }

        function renderSupplierPurchases(purchases) {
            var tbody      = $('#supplierPurchasesBody');
            var tfoot      = $('#supplierPurchasesFoot');
            var summaryRow = $('#modalSummaryRow');
            tbody.empty(); tfoot.empty(); summaryRow.empty();

            if (purchases.length === 0) {
                tbody.append('<tr><td colspan="8" class="text-center text-muted py-3">Aucun achat trouvé</td></tr>');
            } else {
                var totAmount = 0, totPaid = 0, totRest = 0;

                purchases.forEach(function(p, i) {
                    totAmount += p.final_amount;
                    totPaid   += p.total_paid;
                    totRest   += p.rest_amount;

                    var payBtn = '';
                    if (p.payment_status !== 'paid') {
                        payBtn = `<button type="button" class="btn btn-sm btn-success add-payment-btn ms-1"
                                    data-id="${p.purchase_id}"
                                    data-number="${p.purchase_number}"
                                    data-total="${p.final_amount}"
                                    data-paid="${p.total_paid}"
                                    title="Ajouter un paiement">
                                    <i class="fas fa-money-bill"></i>
                                  </button>`;

                        if (currentAvailableCredit > 0.005) {
                            payBtn += `<button type="button" class="btn btn-sm btn-outline-success pay-by-balance-single ms-1"
                                        data-id="${p.purchase_id}"
                                        data-number="${p.purchase_number}"
                                        data-remaining="${p.rest_amount}"
                                        title="Payer par solde">
                                        <i class="fas fa-coins"></i>
                                       </button>`;
                        }
                    }

                    var docsCount  = (p.payment_documents || []).length;
                    var docsToggle = docsCount > 0
                        ? `<button type="button" class="btn btn-sm btn-outline-secondary toggle-docs-btn ms-1"
                               data-target="docs-${p.purchase_id}" title="Voir paiements (${docsCount})">
                               <i class="fas fa-receipt"></i> ${docsCount}
                           </button>`
                        : '';

                    tbody.append(`
                        <tr>
                            <td class="text-center">${i + 1}</td>
                            <td>${p.purchase_date}</td>
                            <td><strong>${p.purchase_number}</strong></td>
                            <td class="text-end">${p.final_amount_display} DH</td>
                            <td class="text-end text-success fw-bold">${p.total_paid_display} DH</td>
                            <td class="text-end ${p.rest_class} fw-bold">${p.rest_amount_display} DH</td>
                            <td class="text-center">${p.payment_status_label}</td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                    <a href="${p.show_url}" class="btn btn-sm btn-info" title="Voir les détails"><i class="fas fa-eye"></i></a>
                                    ${payBtn}
                                    ${docsToggle}
                                </div>
                            </td>
                        </tr>
                    `);

                    if (docsCount > 0) {
                        var docsHtml = '<table class="table table-sm mb-0 table-bordered">' +
                            '<thead class="table-secondary"><tr>' +
                            '<th>N° Doc</th><th>Méthode</th><th class="text-end">Montant</th><th>Date</th><th>Notes</th><th class="text-center">Actions</th>' +
                            '</tr></thead><tbody>';

                        p.payment_documents.forEach(function(doc) {
                            var methodIcon = {
                                cash: 'fas fa-money-bill-wave text-success',
                                bank_transfer: 'fas fa-university text-primary',
                                check: 'fas fa-money-check text-info',
                                traite: 'fas fa-file-invoice text-warning',
                                credit_card: 'fas fa-credit-card text-secondary',
                                balance: 'fas fa-coins text-success',
                            }[doc.payment_method] || 'fas fa-circle text-secondary';

                            docsHtml += `<tr>
                                <td><small>${doc.document_number}</small></td>
                                <td><i class="${methodIcon} me-1"></i>${doc.method_label}</td>
                                <td class="text-end fw-bold">${doc.amount_display} DH</td>
                                <td>${doc.payment_date}</td>
                                <td><small class="text-muted">${doc.notes}</small></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs btn-warning edit-doc-btn"
                                        data-doc-id="${doc.document_id}"
                                        data-method="${doc.payment_method}"
                                        data-amount="${doc.amount}"
                                        data-date="${doc.payment_date_raw}"
                                        data-notes="${doc.notes}"
                                        title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-danger delete-doc-btn ms-1"
                                        data-doc-id="${doc.document_id}"
                                        data-number="${doc.document_number}"
                                        title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>`;
                        });

                        docsHtml += '</tbody></table>';
                        tbody.append(`
                            <tr id="docs-${p.purchase_id}" style="display:none;">
                                <td colspan="8" class="p-0 bg-light">${docsHtml}</td>
                            </tr>
                        `);
                    }
                });

                tfoot.append(`
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Totaux</td>
                        <td class="text-end">${totAmount.toLocaleString('de-DE', {minimumFractionDigits:2})} DH</td>
                        <td class="text-end text-success">${totPaid.toLocaleString('de-DE', {minimumFractionDigits:2})} DH</td>
                        <td class="text-end ${totRest > 0 ? 'text-warning' : 'text-success'}">${totRest.toLocaleString('de-DE', {minimumFractionDigits:2})} DH</td>
                        <td colspan="2"></td>
                    </tr>
                `);

                summaryRow.html(`
                    <div class="col-md-4">
                        <div class="card border-primary mb-0">
                            <div class="card-body py-2 px-3">
                                <small class="text-muted">Total Achats (${purchases.length})</small>
                                <div class="fw-bold text-primary">${totAmount.toLocaleString('de-DE', {minimumFractionDigits:2})} DH</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success mb-0">
                            <div class="card-body py-2 px-3">
                                <small class="text-muted">Total Payé</small>
                                <div class="fw-bold text-success">${totPaid.toLocaleString('de-DE', {minimumFractionDigits:2})} DH</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-${totRest > 0 ? 'danger' : 'success'} mb-0">
                            <div class="card-body py-2 px-3">
                                <small class="text-muted">Reste à Payer</small>
                                <div class="fw-bold text-${totRest > 0 ? 'danger' : 'success'}">${totRest.toLocaleString('de-DE', {minimumFractionDigits:2})} DH</div>
                            </div>
                        </div>
                    </div>
                `);
            }

            $('#supplierDetailsLoading').hide();
            $('#supplierDetailsContent').show();
        }

        $(document).ready(function() {
            $('.select2').select2({
                language: 'fr',
                placeholder: 'Sélectionner...',
                allowClear: true
            });

            // ── Main DataTable ────────────────────────────────────────────────
            var table = $('#situationTable').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('suppliers.situation.index') }}",
                    data: function(d) {
                        d.supplier_id = $('#supplier_id').val();
                        d.payment_status = $('#payment_status').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'supplier_name'
                    },
                    {
                        data: 'purchases_count',
                        className: 'text-center'
                    },
                    {
                        data: 'total_amount_display',
                        className: 'text-end'
                    },
                    {
                        data: 'total_paid_display',
                        className: 'text-end'
                    },
                    {
                        data: 'total_rest_display',
                        orderable: false,
                        className: 'text-end'
                    },
                    {
                        data: 'balance_display',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        className: 'text-center'
                    }
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                },
                order: [
                    [1, 'asc']
                ],
                pageLength: 25
            });

            $('#applyFilters').click(function() {
                table.draw();
            });

            $('#resetFilters').click(function() {
                $('#supplier_id').val('').trigger('change');
                $('#payment_status').val('');
                $('#date_from').val('');
                $('#date_to').val('');
                table.draw();
            });

            $('#exportData').click(function() {
                window.location.href = "{{ route('suppliers.situation.export') }}?" + $.param({
                    supplier_id: $('#supplier_id').val(),
                    payment_status: $('#payment_status').val(),
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val()
                });
            });

            // ── Supplier Details Modal ────────────────────────────────────────
            $(document).on('click', '.view-details-btn', function() {
                currentSupplierId = $(this).data('id');
                currentSupplierName = $(this).data('name');

                $('#supplierDetailsTitle').html('<i class="fas fa-list me-2"></i>Achats : ' +
                    currentSupplierName);
                $('#supplierDetailsLoading').show();
                $('#supplierDetailsContent').hide();
                $('#supplierPurchasesBody').empty();
                $('#supplierPurchasesFoot').empty();
                $('#modalSummaryRow').empty();
                $('#supplierBalanceRow').hide();
                $('#supplierFullSituationLink').attr('href', "{{ url('suppliers/situation/supplier') }}/" +
                    currentSupplierId);
                $('#supplierDetailsModal').modal('show');

                // Load balance first
                $.ajax({
                    url: "{{ url('suppliers/situation/supplier') }}/" + currentSupplierId +
                        "/balance",
                    type: 'GET',
                    success: function(res) {
                        if (res.success) {
                            currentAvailableCredit = parseFloat(res.available_credit) || 0;
                            if (currentAvailableCredit > 0.005) {
                                $('#modalSupplierBalance').text(currentAvailableCredit.toFixed(
                                        2) + ' DH')
                                    .removeClass('text-danger text-muted').addClass(
                                        'text-success');
                                $('#supplierBalanceRow').show();
                            } else {
                                $('#supplierBalanceRow').hide();
                            }
                        }
                    }
                });

                // Reset modal date filters when opening
                $('#modal_date_from').val('');
                $('#modal_date_to').val('');

                loadSupplierPurchases();
            });

            // ── Modal date filter handlers ────────────────────────────────────
            $('#modal_date_from').on('change', function() {
                if (!$('#modal_date_to').val()) {
                    $('#modal_date_to').val(new Date().toISOString().slice(0, 10));
                }
                if (currentSupplierId) loadSupplierPurchases();
            });

            $('#modal_date_to').on('change', function() {
                if (currentSupplierId) loadSupplierPurchases();
            });

            $('#resetModalDateBtn').on('click', function() {
                $('#modal_date_from').val('');
                $('#modal_date_to').val('');
                if (currentSupplierId) loadSupplierPurchases();
            });

            // ── Print/download supplier situation from modal ───────────────────
            $('#printSupplierSituationBtn').on('click', function() {
                var params = new URLSearchParams({
                    show_logo: 1
                });
                var dateFrom = $('#modal_date_from').val();
                var dateTo = $('#modal_date_to').val();
                if (dateFrom) params.append('date_from', dateFrom);
                if (dateTo) params.append('date_to', dateTo);
                window.open("{{ url('suppliers/situation/supplier') }}/" + currentSupplierId + "/print?" +
                    params.toString(), '_blank');
            });

            // ── Pay by balance (from header button in details modal) ───────────
            $('#btnPayByBalanceFromDetails').click(function() {
                // Show a list to pick which purchase to pay
                var rows = $('#supplierPurchasesBody tr');
                var options = '';
                rows.each(function() {
                    var btn = $(this).find('.pay-by-balance-single');
                    if (btn.length) {
                        options += '<option value="' + btn.data('id') + '" data-remaining="' + btn
                            .data('remaining') + '">' +
                            btn.data('number') + ' (Reste: ' + parseFloat(btn.data('remaining'))
                            .toFixed(2) + ' DH)</option>';
                    }
                });
                if (!options) {
                    showToast('error', 'Aucun achat impayé à solder');
                    return;
                }

                // Pick first unpaid purchase by default
                var firstBtn = $('#supplierPurchasesBody .pay-by-balance-single').first();
                if (firstBtn.length) {
                    firstBtn.trigger('click');
                }
            });

            // ── Pay by balance (per purchase row) ─────────────────────────────
            $(document).on('click', '.pay-by-balance-single', function() {
                var purchaseId = $(this).data('id');
                var purchaseNumber = $(this).data('number');
                var remaining = parseFloat($(this).data('remaining'));

                $('#pbb_purchase_id').val(purchaseId);
                $('#pbb_purchase_number').text(purchaseNumber);
                $('#pbb_remaining').text(remaining.toFixed(2) + ' DH');
                $('#pbb_available_credit').text(currentAvailableCredit.toFixed(2) + ' DH');
                $('#pbb_amount').val(Math.min(remaining, currentAvailableCredit).toFixed(2));
                $('#pbb_date').val(new Date().toISOString().split('T')[0]);
                $('#supplierDetailsModal').modal('hide');
                setTimeout(function() {
                    $('#payByBalanceModal').modal('show');
                }, 300);
            });

            $('#payByBalanceForm').submit(function(e) {
                e.preventDefault();
                var $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>...');

                $.ajax({
                    url: "{{ url('suppliers/situation/supplier') }}/" + currentSupplierId +
                        "/pay-by-balance",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        purchase_id: $('#pbb_purchase_id').val(),
                        amount: $('#pbb_amount').val(),
                        payment_date: $('#pbb_date').val(),
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#payByBalanceModal').modal('hide');
                            showToast('success', res.message);
                            table.draw();
                            refreshDetailsModal();
                        } else {
                            showToast('error', res.message);
                        }
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-check me-1"></i>Payer');
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message || 'Erreur');
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-check me-1"></i>Payer');
                    }
                });
            });

            // ── Distribute Payment (global button) ───────────────────────────
            $(document).on('click', '.pay-supplier-all-btn', function() {
                currentSupplierId = $(this).data('id');
                currentSupplierName = $(this).data('name');
                var totalRest = parseFloat($(this).data('rest')) || 0;
                var balance   = parseFloat($(this).data('balance')) || 0;
                var credit    = balance < -0.01 ? Math.abs(balance) : 0;

                $('#distributePaymentForm')[0].reset();
                $('#dist_check_id').val('');
                $('#dist_traite_id').val('');
                $('#dist_date').val(new Date().toISOString().split('T')[0]);
                $('#dist_check_section').hide();
                $('#dist_traite_section').hide();
                $('#dist_check_selected_info').hide();
                $('#dist_check_picker').show();
                $('#dist_traite_selected_info').hide();
                $('#dist_traite_picker').show();

                // Populate info section
                $('#dist_total_rest_raw').val(totalRest);
                $('#dist_total_rest_display').text(
                    totalRest.toLocaleString('de-DE', {minimumFractionDigits: 2}) + ' DH');
                $('#dist_amount').val(totalRest.toFixed(2));

                if (credit > 0.005) {
                    $('#dist_balance_info_display').text(
                        credit.toLocaleString('de-DE', {minimumFractionDigits: 2}) + ' DH');
                    $('#dist_balance_info_row').show();
                } else {
                    $('#dist_balance_info_row').hide();
                }
                $('#dist_solde_badge').hide();
                $('#dist_excess_info').hide().text('');

                $('#distributePaymentModal').modal('show');
            });

            // ── Surplus → solde indicator ─────────────────────────────────────
            $('#dist_amount').on('input', function() {
                var amount = parseFloat($(this).val()) || 0;
                var rest   = parseFloat($('#dist_total_rest_raw').val()) || 0;
                var excess = amount - rest;
                if (excess > 0.005) {
                    $('#dist_solde_badge').show();
                    $('#dist_excess_info').text(
                        excess.toLocaleString('de-DE', {minimumFractionDigits: 2}) +
                        ' DH → solde fournisseur'
                    ).show();
                } else {
                    $('#dist_solde_badge').hide();
                    $('#dist_excess_info').hide();
                }
            });

            $('#dist_method').change(function() {
                var method = $(this).val();
                $('#dist_check_section').toggle(method === 'check');
                $('#dist_traite_section').toggle(method === 'traite');

                if (method === 'check') {
                    loadDistributeChecks();
                }
                if (method === 'traite') {
                    loadTraitesList('#dist_traites_body', '#dist_traite_existing_tab');
                }
            });

            function loadDistributeChecks() {
                $('#dist_checks_body').html('<tr><td colspan="5" class="text-center">Chargement...</td></tr>');
                $.ajax({
                    url: "{{ route('raw-material-purchases.available-checks') }}",
                    type: 'GET',
                    success: function(res) {
                        var tbody = $('#dist_checks_body');
                        tbody.empty();
                        if (!res.success || !res.data.length) {
                            tbody.html(
                                '<tr><td colspan="5" class="text-center text-muted">Aucun chèque disponible</td></tr>'
                                );
                            return;
                        }
                        res.data.forEach(function(c) {
                            tbody.append('<tr>' +
                                '<td>' + (c.check_number || 'N/A') + '</td>' +
                                '<td>' + (c.bank_name || 'N/A') + '</td>' +
                                '<td>' + parseFloat(c.amount).toFixed(2) + ' DH</td>' +
                                '<td>' + parseFloat(c.available_amount).toFixed(2) +
                                ' DH</td>' +
                                '<td><button type="button" class="btn btn-sm btn-primary dist-select-check" ' +
                                'data-id="' + c.check_id + '" data-label="' + c
                                .check_number + ' (' + parseFloat(c.available_amount)
                                .toFixed(2) + ' DH)">Utiliser</button></td>' +
                                '</tr>');
                        });
                    },
                    error: function() {
                        $('#dist_checks_body').html(
                            '<tr><td colspan="5" class="text-center text-danger">Erreur</td></tr>');
                    }
                });
            }

            $(document).on('click', '.dist-select-check', function() {
                $('#dist_check_id').val($(this).data('id'));
                $('#dist_check_selected_label').text($(this).data('label'));
                $('#dist_check_selected_info').show();
                $('#dist_check_picker').hide();
            });

            $('#dist_check_clear_btn').click(function() {
                $('#dist_check_id').val('');
                $('#dist_check_selected_info').hide();
                $('#dist_check_picker').show();
            });

            $(document).on('click', '.dist-select-traite', function() {
                $('#dist_traite_id').val($(this).data('id'));
                $('#dist_traite_selected_label').text($(this).data('label'));
                $('#dist_traite_selected_info').show();
                $('#dist_traite_picker').hide();
            });

            $('#dist_traite_clear_btn').click(function() {
                $('#dist_traite_id').val('');
                $('#dist_traite_selected_info').hide();
                $('#dist_traite_picker').show();
            });

            function loadTraitesList(bodySelector, tabSelector) {
                $(bodySelector).html('<tr><td colspan="5" class="text-center">Chargement...</td></tr>');
                $.ajax({
                    url: "{{ route('raw-material-purchases.available-traites') }}",
                    type: 'GET',
                    success: function(res) {
                        var tbody = $(bodySelector);
                        tbody.empty();
                        if (!res.success || !res.data.length) {
                            tbody.html(
                                '<tr><td colspan="5" class="text-center text-muted">Aucune traite disponible</td></tr>'
                                );
                            return;
                        }
                        res.data.forEach(function(t) {
                            var due = t.due_date ? formatDate(t.due_date) : 'N/A';
                            tbody.append('<tr>' +
                                '<td>' + (t.traite_number || 'N/A') + '</td>' +
                                '<td>' + (t.bank_name || 'N/A') + '</td>' +
                                '<td>' + parseFloat(t.amount).toFixed(2) + ' DH</td>' +
                                '<td>' + due + '</td>' +
                                '<td><button type="button" class="btn btn-sm btn-warning select-traite-btn dist-select-traite" ' +
                                'data-traite-id="' + t.traite_id +
                                '" data-traite-number="' + t.traite_number + '" ' +
                                'data-id="' + t.traite_id + '" data-label="' + t
                                .traite_number + ' (' + parseFloat(t.amount).toFixed(2) +
                                ' DH)">Utiliser</button></td>' +
                                '</tr>');
                        });
                    },
                    error: function() {
                        $(bodySelector).html(
                            '<tr><td colspan="5" class="text-center text-danger">Erreur</td></tr>');
                    }
                });
            }

            $('#distributePaymentForm').submit(function(e) {
                e.preventDefault();
                if (!currentSupplierId) return;

                var method = $('#dist_method').val();
                if (method === 'check' && !$('#dist_check_id').val()) {
                    showToast('error', 'Veuillez sélectionner un chèque');
                    return;
                }

                var $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>...');

                var fd = new FormData(this);
                fd.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: "{{ url('raw-material-purchases/supplier') }}/" + currentSupplierId +
                        "/distribute-payment",
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            $('#distributePaymentModal').modal('hide');
                            showToast('success', res.message);
                            table.draw();
                        } else {
                            showToast('error', res.message);
                        }
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i>Enregistrer');
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors du paiement');
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i>Enregistrer');
                    }
                });
            });

            // ── Add Balance (global button) ───────────────────────────────────
            $(document).on('click', '.add-balance-btn', function() {
                currentSupplierId = $(this).data('id');
                currentSupplierName = $(this).data('name');
                $('#addBalanceForm')[0].reset();
                $('#bal_date').val(new Date().toISOString().split('T')[0]);
                $('#addBalanceModal').modal('show');
            });

            $('#addBalanceForm').submit(function(e) {
                e.preventDefault();
                if (!currentSupplierId) return;
                var $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>...');

                $.ajax({
                    url: "{{ url('suppliers/situation/supplier') }}/" + currentSupplierId +
                        "/add-balance",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        amount: $('#bal_amount').val().replace(',', '.'),
                        payment_date: $('#bal_date').val(),
                        notes: $('#bal_notes').val(),
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#addBalanceModal').modal('hide');
                            showToast('success', res.message);
                            table.draw();
                        } else {
                            showToast('error', res.message);
                        }
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i>Enregistrer');
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message || 'Erreur');
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i>Enregistrer');
                    }
                });
            });

            // ── Per-purchase payment button ───────────────────────────────────
            $(document).on('click', '.add-payment-btn', function() {
                currentPurchaseData = {
                    purchase_id: $(this).data('id'),
                    purchase_number: $(this).data('number'),
                    total_amount: parseFloat($(this).data('total')),
                    paid_amount: parseFloat($(this).data('paid'))
                };
                showPaymentMethodModal();
            });

            // ── Payment type selection ────────────────────────────────────────
            $('.payment-type-select').click(function() {
                var type = $(this).data('type');
                var checkType = $(this).data('check-type');
                var remaining = currentPurchaseData.total_amount - currentPurchaseData.paid_amount;

                $('#paymentMethodModal').modal('hide');

                switch (type) {
                    case 'cash':
                        $('#cash_amount').val(remaining.toFixed(2));
                        setTimeout(function() {
                            $('#cashModal').modal('show');
                        }, 300);
                        break;
                    case 'bank_transfer':
                        $('#transfer_amount').val(remaining.toFixed(2));
                        setTimeout(function() {
                            $('#bankTransferModal').modal('show');
                        }, 300);
                        break;
                    case 'traite':
                        $('#traite_amount').val(remaining.toFixed(2));
                        $('#traite_number').val('');
                        $('#traite_due_date').val('');
                        $('#traite_bank').val('');
                        $('#traite_notes').val('');
                        $('#traite_id_hidden').val('');
                        $('#traite_selected_info').hide();
                        $('#traite_picker_section').show();
                        loadTraitesList('#traite_existing_body', '#traite_existing_tab');
                        setTimeout(function() {
                            $('#traiteModal').modal('show');
                        }, 300);
                        break;
                    case 'check':
                        $('#availableChecksList').show();
                        $('#newCheckForm').hide();
                        $('#saveNewCheckBtn').hide();
                        $('#selected_check_type').val(checkType);
                        $('#selected_check_type_value').val(checkType);
                        $('#checkModalTitle').text(checkType === 'entreprise' ?
                            'Sélectionner un chèque Entreprise' : 'Sélectionner un chèque Client');
                        $('#availableChecksTitle').text(checkType === 'entreprise' ?
                            'Chèques entreprise disponibles' : 'Chèques client disponibles');
                        loadAvailableChecks(checkType);
                        setTimeout(function() {
                            $('#checkSelectionModal').modal('show');
                        }, 300);
                        break;
                    case 'new_check_entreprise':
                    case 'new_check_client':
                        var cType = type === 'new_check_entreprise' ? 'entreprise' : 'client';
                        $('#availableChecksList').hide();
                        $('#newCheckForm').show();
                        $('#saveNewCheckBtn').show();
                        $('#selected_check_type').val('new');
                        $('#selected_check_type_value').val(cType);
                        $('#new_check_type').val(cType);
                        $('#checkModalTitle').text('Ajouter un nouveau chèque ' + (cType === 'entreprise' ?
                            'Entreprise' : 'Client'));
                        $('#newCheckTitle').text('Ajouter un nouveau chèque ' + (cType === 'entreprise' ?
                            'Entreprise' : 'Client'));
                        $('#quickCheckForm')[0].reset();
                        var now = new Date();
                        $('#new_check_issue_date').val(now.toISOString().split('T')[0]);
                        var due = new Date();
                        due.setDate(due.getDate() + 30);
                        $('#new_check_due_date').val(due.toISOString().split('T')[0]);
                        setTimeout(function() {
                            $('#checkSelectionModal').modal('show');
                        }, 300);
                        break;
                    case 'credit_card':
                        $('#card_amount').val(remaining.toFixed(2));
                        setTimeout(function() {
                            $('#creditCardModal').modal('show');
                        }, 300);
                        break;
                    case 'solde':
                        $('#pbb_purchase_id').val(currentPurchaseData.purchase_id);
                        $('#pbb_purchase_number').text(currentPurchaseData.purchase_number);
                        $('#pbb_remaining').text(remaining.toFixed(2) + ' DH');
                        $('#pbb_available_credit').text(currentAvailableCredit.toFixed(2) + ' DH');
                        $('#pbb_amount').val(Math.min(remaining, currentAvailableCredit).toFixed(2));
                        $('#pbb_date').val(new Date().toISOString().split('T')[0]);
                        setTimeout(function() {
                            $('#payByBalanceModal').modal('show');
                        }, 300);
                        break;
                }
            });

            // ── Traite form submit ────────────────────────────────────────────
            $('#traiteForm').submit(function(e) {
                e.preventDefault();
                var amount = parseFloat($('#traite_amount').val());
                var remaining = currentPurchaseData.total_amount - currentPurchaseData.paid_amount;
                if (isNaN(amount) || amount <= 0) {
                    showToast('error', 'Montant invalide');
                    return;
                }
                if (amount > remaining + 0.005) {
                    showToast('error', 'Le montant dépasse le reste à payer (' + remaining.toFixed(2) +
                        ' DH)');
                    return;
                }

                var traiteId = $('#traite_id_hidden').val();
                // If using new traite, due date is required
                if (!traiteId && !$('#traite_due_date').val()) {
                    showToast('error', "Date d'échéance obligatoire pour une nouvelle traite");
                    return;
                }

                var fd = new FormData();
                fd.append('_token', '{{ csrf_token() }}');
                fd.append('purchase_id', currentPurchaseData.purchase_id);
                fd.append('amount', amount);
                fd.append('payment_method', 'traite');
                fd.append('payment_date', $('#traite_issue_date').val());
                if (traiteId) {
                    fd.append('traite_id', traiteId);
                } else {
                    fd.append('traite_number', $('#traite_number').val());
                    fd.append('traite_due_date', $('#traite_due_date').val());
                    fd.append('traite_bank', $('#traite_bank').val());
                    fd.append('notes', $('#traite_notes').val());
                }

                submitPayment(fd, '#traiteModal', '#traiteForm', table);
            });

            // ── Traite existing selection (per-purchase modal) ─────────────────
            $(document).on('click', '.select-traite-btn', function() {
                var traiteId = $(this).data('traite-id');
                var traiteNumber = $(this).data('traite-number');
                $('#traite_id_hidden').val(traiteId);
                $('#traite_selected_label').text(traiteNumber);
                $('#traite_selected_info').show();
                $('#traite_picker_section').hide();
            });

            $('#traite_clear_btn').click(function() {
                $('#traite_id_hidden').val('');
                $('#traite_selected_info').hide();
                $('#traite_picker_section').show();
            });

            // ── Cash form ─────────────────────────────────────────────────────
            $('#cashForm').submit(function(e) {
                e.preventDefault();
                var amount = parseFloat($('#cash_amount').val());
                var remaining = currentPurchaseData.total_amount - currentPurchaseData.paid_amount;
                if (isNaN(amount) || amount <= 0) {
                    showToast('error', 'Montant invalide');
                    return;
                }
                // Overpayment allowed — excess will be credited to supplier balance
                var fd = new FormData();
                fd.append('_token', '{{ csrf_token() }}');
                fd.append('purchase_id', currentPurchaseData.purchase_id);
                fd.append('amount', amount);
                fd.append('payment_method', 'cash');
                fd.append('payment_date', $('#cash_date').val());
                fd.append('notes', $('#cash_notes').val());
                if ($('#cash_file')[0].files[0]) fd.append('payment_file', $('#cash_file')[0].files[0]);
                submitPayment(fd, '#cashModal', '#cashForm', table);
            });

            // ── Bank transfer form ────────────────────────────────────────────
            $('#bankTransferForm').submit(function(e) {
                e.preventDefault();
                var amount = parseFloat($('#transfer_amount').val());
                var remaining = currentPurchaseData.total_amount - currentPurchaseData.paid_amount;
                if (isNaN(amount) || amount <= 0) {
                    showToast('error', 'Montant invalide');
                    return;
                }
                // Overpayment allowed — excess will be credited to supplier balance
                var fd = new FormData();
                fd.append('_token', '{{ csrf_token() }}');
                fd.append('purchase_id', currentPurchaseData.purchase_id);
                fd.append('amount', amount);
                fd.append('payment_method', 'bank_transfer');
                fd.append('payment_date', $('#transfer_date').val());
                fd.append('notes', $('#transfer_notes').val());
                if ($('#transfer_file')[0].files[0]) fd.append('payment_file', $('#transfer_file')[0].files[
                    0]);
                submitPayment(fd, '#bankTransferModal', '#bankTransferForm', table);
            });

            // ── Credit card form ──────────────────────────────────────────────
            $('#creditCardForm').submit(function(e) {
                e.preventDefault();
                var amount = parseFloat($('#card_amount').val());
                var remaining = currentPurchaseData.total_amount - currentPurchaseData.paid_amount;
                if (isNaN(amount) || amount <= 0) {
                    showToast('error', 'Montant invalide');
                    return;
                }
                // Overpayment allowed — excess will be credited to supplier balance
                var fd = new FormData();
                fd.append('_token', '{{ csrf_token() }}');
                fd.append('purchase_id', currentPurchaseData.purchase_id);
                fd.append('amount', amount);
                fd.append('payment_method', 'credit_card');
                fd.append('payment_date', $('#card_date').val());
                fd.append('notes', $('#card_notes').val());
                if ($('#card_file')[0].files[0]) fd.append('payment_file', $('#card_file')[0].files[0]);
                submitPayment(fd, '#creditCardModal', '#creditCardForm', table);
            });

            function submitPayment(formData, modalId, formId, dataTable) {
                $.ajax({
                    url: "{{ route('raw-material-purchases.add-payment') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $(modalId).modal('hide');
                            dataTable.ajax.reload();
                            showToast('success', response.message);
                            if (formId) $(formId)[0].reset();
                            refreshDetailsModal();
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors de l\'ajout du paiement');
                    }
                });
            }

            // ── Available checks ──────────────────────────────────────────────
            function loadAvailableChecks(type) {
                $('#checksBody').html('<tr><td colspan="6" class="text-center">Chargement...</td></tr>');
                $.ajax({
                    url: "{{ route('raw-material-purchases.available-checks') }}",
                    type: 'GET',
                    data: {
                        type: type
                    },
                    success: function(response) {
                        if (response.success) {
                            displayChecks(response.data, type);
                        } else {
                            $('#checksBody').html(
                                '<tr><td colspan="6" class="text-center text-danger">Erreur</td></tr>'
                                );
                        }
                    },
                    error: function() {
                        $('#checksBody').html(
                            '<tr><td colspan="6" class="text-center text-danger">Erreur</td></tr>');
                    }
                });
            }

            function displayChecks(checks, type) {
                var tbody = $('#checksBody');
                tbody.empty();
                var checkClass = type === 'client' ? 'client-check' : 'entreprise-check';
                if (checks.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center">Aucun chèque disponible</td></tr>');
                } else {
                    checks.forEach(function(check) {
                        tbody.append(`
                            <tr class="${checkClass}">
                                <td>${check.check_number || 'N/A'}</td>
                                <td>${check.bank_name || 'N/A'}</td>
                                <td>${check.amount} DH</td>
                                <td>${check.available_amount.toFixed(2)} DH</td>
                                <td>${check.issue_date ? formatDate(check.issue_date) : 'N/A'}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-check"
                                            data-check-id="${check.check_id}"
                                            data-check-number="${check.check_number || 'N/A'}"
                                            data-available="${check.available_amount}">
                                        Utiliser
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }
                bindSelectCheckEvent();
            }

            function bindSelectCheckEvent() {
                $('.select-check').off('click').on('click', function() {
                    var checkId = $(this).data('check-id');
                    var checkNumber = $(this).data('check-number');
                    var availableAmount = parseFloat($(this).data('available'));
                    var remaining = currentPurchaseData.total_amount - currentPurchaseData.paid_amount;
                    var amount = Math.min(availableAmount, remaining);

                    if (amount <= 0) {
                        showToast('error', 'Fonds insuffisants ou achat soldé');
                        return;
                    }

                    $('#checkSelectionModal').modal('hide');

                    var fd = new FormData();
                    fd.append('_token', '{{ csrf_token() }}');
                    fd.append('purchase_id', currentPurchaseData.purchase_id);
                    fd.append('amount', amount);
                    fd.append('payment_method', 'check');
                    fd.append('payment_date', new Date().toISOString().split('T')[0]);
                    fd.append('check_id', checkId);
                    fd.append('notes', 'Utilisation du chèque N° ' + checkNumber);

                    $.ajax({
                        url: "{{ route('raw-material-purchases.add-payment') }}",
                        type: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                showToast('success', response.message + ' Montant: ' + amount
                                    .toFixed(2) + ' DH');
                                refreshDetailsModal();
                            } else {
                                showToast('error', response.message);
                            }
                        },
                        error: function(xhr) {
                            showToast('error', xhr.responseJSON?.message || 'Erreur');
                        }
                    });
                });
            }

            $('#saveNewCheckBtn').click(function() {
                var fileInput = $('#new_check_file')[0].files[0];
                if (!fileInput) {
                    showToast('error', 'Veuillez sélectionner une image du chèque');
                    return;
                }
                var checkType = $('#new_check_type').val();
                var fd = new FormData();
                fd.append('check_type', checkType);
                fd.append('check_number', $('#new_check_number').val());
                fd.append('bank_name', $('#new_check_bank').val());
                fd.append('amount', $('#new_check_amount').val());
                fd.append('issue_date', $('#new_check_issue_date').val());
                fd.append('due_date', $('#new_check_due_date').val());
                fd.append('deposit_date', new Date().toISOString().split('T')[0]);
                fd.append('payee', $('#new_check_payee').val());
                fd.append('check_file', fileInput);
                fd.append('_token', '{{ csrf_token() }}');

                if (!fd.get('check_number') || !fd.get('bank_name') || !fd.get('amount') ||
                    !fd.get('issue_date') || !fd.get('due_date')) {
                    showToast('error', 'Veuillez remplir tous les champs obligatoires');
                    return;
                }

                $.ajax({
                    url: "{{ route('checks.store') }}",
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', 'Chèque ajouté avec succès');
                            var remaining = currentPurchaseData.total_amount -
                                currentPurchaseData.paid_amount;
                            var amount = Math.min(response.amount, remaining);
                            if (amount <= 0) {
                                showToast('error', 'Achat déjà soldé');
                                $('#checkSelectionModal').modal('hide');
                                return;
                            }
                            var payFd = new FormData();
                            payFd.append('_token', '{{ csrf_token() }}');
                            payFd.append('purchase_id', currentPurchaseData.purchase_id);
                            payFd.append('amount', amount);
                            payFd.append('payment_method', 'check');
                            payFd.append('payment_date', new Date().toISOString().split('T')[
                            0]);
                            payFd.append('check_id', response.check_id);
                            payFd.append('notes', 'Nouveau chèque créé et utilisé');
                            $.ajax({
                                url: "{{ route('raw-material-purchases.add-payment') }}",
                                type: 'POST',
                                data: payFd,
                                processData: false,
                                contentType: false,
                                success: function(pr) {
                                    if (pr.success) {
                                        $('#checkSelectionModal').modal('hide');
                                        table.ajax.reload();
                                        showToast('success',
                                            'Chèque créé et alloué: ' + amount
                                            .toFixed(2) + ' DH');
                                        refreshDetailsModal();
                                    } else {
                                        showToast('error', pr.message);
                                    }
                                },
                                error: function() {
                                    showToast('error',
                                        'Erreur lors de l\'allocation');
                                }
                            });
                        } else {
                            showToast('error', response.message || 'Erreur');
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message || 'Erreur');
                    }
                });
            });

            // ── Toggle payment docs sub-row ───────────────────────────────────
            $(document).on('click', '.toggle-docs-btn', function() {
                var target = $(this).data('target');
                $('#' + target).toggle();
                var icon = $(this).find('i');
                icon.toggleClass('fa-receipt fa-chevron-up');
            });

            // ── Edit payment document ─────────────────────────────────────────
            var allMethods = [{
                    value: 'cash',
                    label: 'Espèces'
                },
                {
                    value: 'bank_transfer',
                    label: 'Virement Bancaire'
                },
                {
                    value: 'check',
                    label: 'Chèque'
                },
                {
                    value: 'traite',
                    label: 'Traite'
                },
                {
                    value: 'credit_card',
                    label: 'Carte de crédit'
                },
            ];

            function resetEditPickers() {
                $('#edit_check_section').hide();
                $('#edit_traite_section').hide();
                $('#edit_check_id').val('');
                $('#edit_traite_id').val('');
                $('#edit_check_selected_info').hide();
                $('#edit_check_picker').show();
                $('#edit_traite_selected_info').hide();
                $('#edit_traite_picker').show();
            }

            $(document).on('click', '.edit-doc-btn', function() {
                var docId = $(this).data('doc-id');
                var method = $(this).data('method');
                var amount = $(this).data('amount');
                var date = $(this).data('date');
                var notes = $(this).data('notes');

                $('#edit_doc_id').val(docId);
                $('#edit_original_method').val(method);
                $('#edit_amount').val(parseFloat(amount).toFixed(2));
                $('#edit_date').val(date);
                $('#edit_notes').val(notes);
                $('#edit_file').val('');
                resetEditPickers();

                // Build method options
                var $sel = $('#edit_method').empty();
                var allowed = method === 'check' ?
                    ['check', 'cash'] :
                    allMethods.map(function(m) {
                        return m.value;
                    });
                allMethods.forEach(function(m) {
                    if (allowed.indexOf(m.value) !== -1) {
                        $sel.append('<option value="' + m.value + '"' + (m.value === method ?
                            ' selected' : '') + '>' + m.label + '</option>');
                    }
                });
                $('#edit_method_hint').toggle(method === 'check');

                $('#editPaymentModal').modal('show');
            });

            // Show/hide check or traite picker when method changes in edit modal
            $('#edit_method').on('change', function() {
                var origMethod = $('#edit_original_method').val();
                var newMethod = $(this).val();
                resetEditPickers();

                // Only show pickers when switching FROM a non-check/non-traite method
                if (origMethod !== 'check' && origMethod !== 'traite') {
                    if (newMethod === 'check') {
                        $('#edit_check_section').show();
                        loadEditChecks();
                    } else if (newMethod === 'traite') {
                        $('#edit_traite_section').show();
                        loadTraitesList('#edit_traites_body', '#edit_traite_existing');
                    }
                }
            });

            function loadEditChecks() {
                $('#edit_checks_body').html('<tr><td colspan="5" class="text-center">Chargement...</td></tr>');
                $.ajax({
                    url: "{{ route('raw-material-purchases.available-checks') }}",
                    type: 'GET',
                    success: function(res) {
                        var tbody = $('#edit_checks_body');
                        tbody.empty();
                        if (!res.success || !res.data.length) {
                            tbody.html(
                                '<tr><td colspan="5" class="text-center text-muted">Aucun chèque disponible</td></tr>'
                                );
                            return;
                        }
                        res.data.forEach(function(c) {
                            tbody.append('<tr>' +
                                '<td>' + (c.check_number || 'N/A') + '</td>' +
                                '<td>' + (c.bank_name || 'N/A') + '</td>' +
                                '<td>' + parseFloat(c.amount).toFixed(2) + ' DH</td>' +
                                '<td>' + parseFloat(c.available_amount).toFixed(2) +
                                ' DH</td>' +
                                '<td><button type="button" class="btn btn-sm btn-primary edit-select-check" ' +
                                'data-id="' + c.check_id + '" ' +
                                'data-label="' + c.check_number + ' (' + parseFloat(c
                                    .available_amount).toFixed(2) +
                                ' DH)">Utiliser</button></td>' +
                                '</tr>');
                        });
                    },
                    error: function() {
                        $('#edit_checks_body').html(
                            '<tr><td colspan="5" class="text-center text-danger">Erreur</td></tr>');
                    }
                });
            }

            $(document).on('click', '.edit-select-check', function() {
                $('#edit_check_id').val($(this).data('id'));
                $('#edit_check_selected_label').text($(this).data('label'));
                $('#edit_check_selected_info').show();
                $('#edit_check_picker').hide();
            });

            $('#edit_check_clear_btn').click(function() {
                $('#edit_check_id').val('');
                $('#edit_check_selected_info').hide();
                $('#edit_check_picker').show();
            });

            $(document).on('click', '.edit-select-traite', function() {
                $('#edit_traite_id').val($(this).data('traite-id'));
                $('#edit_traite_selected_label').text($(this).data('traite-number'));
                $('#edit_traite_selected_info').show();
                $('#edit_traite_picker').hide();
            });

            $('#edit_traite_clear_btn').click(function() {
                $('#edit_traite_id').val('');
                $('#edit_traite_selected_info').hide();
                $('#edit_traite_picker').show();
            });

            // Reuse loadTraitesList for edit modal — wire up edit-select-traite dynamically
            // (loadTraitesList generates .dist-select-traite and .select-traite-btn; we need .edit-select-traite)
            function loadEditTraites() {
                $('#edit_traites_body').html('<tr><td colspan="5" class="text-center">Chargement...</td></tr>');
                $.ajax({
                    url: "{{ route('raw-material-purchases.available-traites') }}",
                    type: 'GET',
                    success: function(res) {
                        var tbody = $('#edit_traites_body');
                        tbody.empty();
                        if (!res.success || !res.data.length) {
                            tbody.html(
                                '<tr><td colspan="5" class="text-center text-muted">Aucune traite disponible</td></tr>'
                                );
                            return;
                        }
                        res.data.forEach(function(t) {
                            var due = t.due_date ? formatDate(t.due_date) : 'N/A';
                            tbody.append('<tr>' +
                                '<td>' + (t.traite_number || 'N/A') + '</td>' +
                                '<td>' + (t.bank_name || 'N/A') + '</td>' +
                                '<td>' + parseFloat(t.amount).toFixed(2) + ' DH</td>' +
                                '<td>' + due + '</td>' +
                                '<td><button type="button" class="btn btn-sm btn-warning edit-select-traite" ' +
                                'data-traite-id="' + t.traite_id +
                                '" data-traite-number="' + t.traite_number +
                                '">Utiliser</button></td>' +
                                '</tr>');
                        });
                    },
                    error: function() {
                        $('#edit_traites_body').html(
                            '<tr><td colspan="5" class="text-center text-danger">Erreur</td></tr>');
                    }
                });
            }

            // Override the change handler to use loadEditTraites instead of loadTraitesList
            $('#edit_method').off('change').on('change', function() {
                var origMethod = $('#edit_original_method').val();
                var newMethod = $(this).val();
                resetEditPickers();

                if (origMethod !== 'check' && origMethod !== 'traite') {
                    if (newMethod === 'check') {
                        $('#edit_check_section').show();
                        loadEditChecks();
                    } else if (newMethod === 'traite') {
                        $('#edit_traite_section').show();
                        loadEditTraites();
                    }
                }
            });

            $('#editPaymentForm').submit(function(e) {
                e.preventDefault();
                var docId = $('#edit_doc_id').val();
                var newMethod = $('#edit_method').val();
                var origMethod = $('#edit_original_method').val();

                // Validate check/traite selection when switching to those methods
                if (origMethod !== 'check' && origMethod !== 'traite') {
                    if (newMethod === 'check' && !$('#edit_check_id').val()) {
                        showToast('error', 'Veuillez sélectionner un chèque');
                        return;
                    }
                    if (newMethod === 'traite' && !$('#edit_traite_id').val() && !$('#edit_traite_due')
                        .val()) {
                        showToast('error', "Date d'échéance obligatoire pour une nouvelle traite");
                        return;
                    }
                }

                var $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>...');

                var fd = new FormData(this);
                fd.append('_token', '{{ csrf_token() }}');
                fd.append('_method', 'PATCH');

                $.ajax({
                    url: '/raw-material-purchases/payment-documents/' + docId + '/payment-method',
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            $('#editPaymentModal').modal('hide');
                            showToast('success', res.message);
                            table.draw();
                            refreshDetailsModal();
                        } else {
                            showToast('error', res.message);
                        }
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i>Enregistrer');
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors de la modification');
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i>Enregistrer');
                    }
                });
            });

            // ── Delete payment document ───────────────────────────────────────
            $(document).on('click', '.delete-doc-btn', function() {
                var docId = $(this).data('doc-id');
                var number = $(this).data('number');
                if (!confirm('Supprimer le paiement ' + number + ' ?')) return;

                $.ajax({
                    url: '/raw-material-purchases/payment-documents/' + docId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success) {
                            showToast('success', res.message);
                            table.draw();
                            refreshDetailsModal();
                        } else {
                            showToast('error', res.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors de la suppression');
                    }
                });
            });

            // ── Reset on close ────────────────────────────────────────────────
            $('#checkSelectionModal').on('hidden.bs.modal', function() {
                $('#availableChecksList').hide();
                $('#newCheckForm').hide();
                $('#saveNewCheckBtn').hide();
            });
            $('#cashModal').on('hidden.bs.modal', function() {
                $('#cashForm')[0].reset();
            });
            $('#bankTransferModal').on('hidden.bs.modal', function() {
                $('#bankTransferForm')[0].reset();
            });
            $('#creditCardModal').on('hidden.bs.modal', function() {
                $('#creditCardForm')[0].reset();
            });
            $('#traiteModal').on('hidden.bs.modal', function() {
                $('#traiteForm')[0].reset();
                $('#traite_id_hidden').val('');
                $('#traite_selected_info').hide();
                $('#traite_picker_section').show();
            });
            $('#supplierDetailsModal').on('hidden.bs.modal', function() {
                currentAvailableCredit = 0;
            });
        });
    </script>
@endpush
