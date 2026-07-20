@extends('layouts.app')

@section('title', 'Nouvelle Vente')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Nouvelle Vente</h4>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-primary p-2">N° {{ $nextOrderNumber }}</span>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item d-flex align-items-center">
                                        <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                            <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a class="text-muted text-decoration-none" href="{{ route('sales.orders.index') }}">
                                            Ventes
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
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-plus-circle me-2"></i>Créer une Nouvelle Vente
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="orderForm">
                            @csrf
                            <input type="hidden" id="order_number" name="order_number" value="{{ $nextOrderNumber }}">

                            <!-- Basic Info Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="order_date" class="form-label">Date Vente *</label>
                                        <input type="date" class="form-control" id="order_date" name="order_date"
                                            value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="client_id" class="form-label">Client *</label>
                                        <div class="input-group">
                                            <select class="form-control select2" id="client_id" name="client_id" required
                                                style="width: 90%;">
                                                <option value="">Sélectionner un client</option>
                                                @foreach ($clients as $client)
                                                    <option value="{{ $client->client_id }}"
                                                        data-client-type="{{ $client->client_type }}">
                                                        {{ $client->display_name }} ({{ $client->phone }}) -
                                                        {{ $client->client_type_label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-success" id="addClientBtn"
                                                title="Ajouter un client">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Client Credit & Balance Info -->
                            <div id="client-credit-info" class="mb-3"></div>

                            <!-- Order Items Section -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Articles de la Vente</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-success" id="addProductFromTopBtn">
                                            <i class="fas fa-box-plus me-1"></i> Ajouter un Produit
                                        </button>
                                        <button type="button" class="btn btn-primary" id="add-item">
                                            <i class="fas fa-plus me-1"></i> Ajouter une ligne
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="items-table">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="20%">Type</th>
                                                    <th width="25%">Article</th>
                                                    <th width="10%">Quantité</th>
                                                    <th width="15%">Prix Unitaire (DH)</th>
                                                    <th width="15%">Total (DH)</th>
                                                    <th width="10%">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="items-body">
                                                <!-- First row will be added automatically -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                                    <td><strong id="order-total">0.00 DH</strong></td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Section -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Règlement</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="payments-table">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="20%">Méthode</th>
                                                    <th width="15%">Montant (DH)</th>
                                                    <th width="15%">Date</th>
                                                    <th width="25%">Détails</th>
                                                    <th width="10%">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="payments-body">
                                                <!-- Payment rows will be added here -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2" class="text-end"><strong>Total Payé:</strong></td>
                                                    <td><strong id="total-paid">0.00 DH</strong></td>
                                                    <td colspan="3"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-end"><strong>Crédit utilisé:</strong>
                                                    </td>
                                                    <td><strong id="credit-used">0.00 DH</strong></td>
                                                    <td colspan="3" id="credit-info"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-end"><strong>Reste à Payer (sera sur
                                                            crédit):</strong></td>
                                                    <td><strong id="remaining-amount">0.00 DH</strong></td>
                                                    <td colspan="3" id="payment-breakdown"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-end"><strong>Statut:</strong></td>
                                                    <td id="payment-status-display">
                                                        <span class="badge bg-danger p-2">Non Payé</span>
                                                    </td>
                                                    <td colspan="3"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-success" id="add-payment">
                                                <i class="fas fa-plus me-1"></i> Ajouter un règlement
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="display_advance" class="form-label">Avance</label>
                                        <input type="number" class="form-control" id="display_advance"
                                            name="display_advance" step="0.01" min="0"
                                            placeholder="Montant de l'avance (affichage uniquement)">
                                        <small class="form-text text-muted">
                                            Ne modifie aucun calcul — sera simplement affiché sur le bon de livraison.
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Notes supplémentaires..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('sales.orders.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Credit Exceeded Modal -->
    <div class="modal fade" id="creditExceededModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">Limite de crédit dépassée</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="creditExceededMessage"></p>
                    <div id="creditExceededDetails" class="alert alert-info mt-3"></div>
                    <p class="mt-3">Options disponibles:</p>
                    <div class="list-group">
                        <button type="button" class="list-group-item list-group-item-action"
                            onclick="proceedWithExcess()">
                            <i class="fas fa-check-circle text-success me-2"></i> Continuer avec dépassement
                            <small class="d-block text-muted">Dépassement autorisé pour cette vente</small>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action"
                            onclick="requestPaymentFirst()">
                            <i class="fas fa-money-bill-wave text-primary me-2"></i> Exiger un paiement avant
                            <small class="d-block text-muted">Le client doit payer le dépassement</small>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action"
                            onclick="reduceOrderAmount()">
                            <i class="fas fa-minus-circle text-warning me-2"></i> Réduire le montant de la vente
                            <small class="d-block text-muted">Modifier les articles pour respecter la limite</small>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler la vente</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Creating Client -->
    <div class="modal fade" id="addClientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Ajouter un Nouveau Client
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="quickClientForm">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Type de Personne *</label>
                                <select class="form-control" id="quick_person_type" name="person_type" required>
                                    <option value="">Sélectionner</option>
                                    <option value="physique">Physique</option>
                                    <option value="morale">Morale</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type de Client *</label>
                                <select class="form-control" id="quick_client_type" name="client_type" required>
                                    <option value="">Sélectionner</option>
                                    <option value="client">Client</option>
                                    <option value="commerciale">Commerciale</option>
                                    <option value="grossiste">Grossiste</option>
                                    <option value="special">Spécial</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12" id="quick_name_section">
                                <label class="form-label">Nom Complet *</label>
                                <input type="text" class="form-control" id="quick_name" name="name">
                            </div>
                            <div class="col-md-12" id="quick_entreprise_section" style="display: none;">
                                <label class="form-label">Nom de l'Entreprise *</label>
                                <input type="text" class="form-control" id="quick_entreprise_name"
                                    name="entreprise_name">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Téléphone *</label>
                                <input type="text" class="form-control" id="quick_phone" name="phone" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="quick_email" name="email">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Adresse</label>
                                <textarea class="form-control" id="quick_address" name="address" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Plafond de Crédit (DH)</label>
                                <input type="number" class="form-control" id="quick_credit_limit" name="credit_limit"
                                    value="0" step="0.01">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-success" id="saveQuickClientBtn">
                        <i class="fas fa-save me-1"></i> Créer Client
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Creating Product -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-box-plus me-2"></i>Ajouter un Nouveau Produit
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="quickProductForm">
                        @csrf

                        <!-- Basic Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Code Produit *</label>
                                <input type="text" class="form-control" id="quick_product_code" name="product_code"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom du Produit *</label>
                                <input type="text" class="form-control" id="quick_product_name" name="product_name"
                                    required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Type de Production *</label>
                                <select class="form-control" id="quick_product_type" name="product_type" required>
                                    <option value="">Sélectionner</option>
                                    <option value="production">Production (Bloc)</option>
                                    <option value="decoupage">Découpage (Sous Bloc)</option>
                                    <option value="finale">Produit Final (Volume)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Unité (affichée dans les ventes)</label>
                                <input type="text" class="form-control" id="quick_unit_of_measure"
                                    name="unit_of_measure" maxlength="50" value="pièce">
                                <small class="text-muted">Par défaut "pièce", modifiable (Ex: m3, kg...)</small>
                            </div>
                        </div>

                        <!-- Dimensions -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Hauteur (m)</label>
                                <input type="number" class="form-control" id="quick_height_m" name="height_m"
                                    step="0.001">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Largeur (m)</label>
                                <input type="number" class="form-control" id="quick_width_m" name="width_m"
                                    step="0.001">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Profondeur (m)</label>
                                <input type="number" class="form-control" id="quick_depth_m" name="depth_m"
                                    step="0.001">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Volume (m³)</label>
                                <input type="number" class="form-control" id="quick_volume_m3" name="volume_m3"
                                    step="0.0001" readonly>
                                <small class="text-muted">Calculé automatiquement</small>
                            </div>
                        </div>

                        <!-- Familles et Prix Spécifiques Section -->
                        <div class="section-header mb-3">
                            <h6 class="section-title bg-info text-white p-2 rounded">
                                <i class="fas fa-layer-group me-2"></i>Familles et Prix Spécifiques
                            </h6>
                        </div>

                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Ajoutez les familles pour ce produit et définissez les prix pour chaque type de client.
                        </div>

                        <div id="quickFamillesContainer">
                            <!-- Famille rows will be added here -->
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="quickAddFamilleBtn">
                                    <i class="fas fa-plus me-1"></i> Ajouter une Famille
                                </button>
                            </div>
                        </div>

                        <!-- Stock Levels -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Stock Minimum</label>
                                <input type="number" class="form-control" id="quick_min_stock_level"
                                    name="min_stock_level" step="0.01" placeholder="Ex: 10.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stock Maximum</label>
                                <input type="number" class="form-control" id="quick_max_stock_level"
                                    name="max_stock_level" step="0.01" placeholder="Ex: 100.00">
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="form-group mb-3">
                            <label for="quick_description" class="form-label">Description</label>
                            <textarea class="form-control" id="quick_description" name="description" rows="2"
                                placeholder="Description du produit..."></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Statut</label>
                                <select class="form-control" id="quick_is_active" name="is_active">
                                    <option value="1" selected>Actif</option>
                                    <option value="0">Inactif</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-success" id="saveQuickProductBtn">
                        <i class="fas fa-save me-1"></i> Créer Produit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <template id="quickFamilleRowTemplate">
        <div class="quick-famille-row mb-3 border rounded p-3 bg-light">
            <div class="row mb-2">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Famille</h6>
                        <button type="button" class="btn btn-sm btn-danger remove-quick-famille-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-2">
                    <label class="form-label">Famille *</label>
                    <select class="form-control quick-famille-select" name="familles[INDEX][famille_id]" required
                        style="width: 100%;">
                        <option value="">Sélectionner une famille</option>
                        @foreach ($familles as $famille)
                            <option value="{{ $famille->famille_id }}" data-prix-client="{{ $famille->prix_client }}"
                                data-prix-grossiste="{{ $famille->prix_grossiste }}"
                                data-prix-commercial="{{ $famille->prix_commercial }}"
                                data-prix-special="{{ $famille->prix_special }}">
                                {{ $famille->famille_name }} ({{ $famille->famille_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Prix Client (DH) *</label>
                    <input type="number" class="form-control quick-famille-prix-client"
                        name="familles[INDEX][prix_client]" min="0" step="0.01" required>
                    <small class="text-muted quick-prix-client-standard"></small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Prix Grossiste (DH) *</label>
                    <input type="number" class="form-control quick-famille-prix-grossiste"
                        name="familles[INDEX][prix_grossiste]" min="0" step="0.01" required>
                    <small class="text-muted quick-prix-grossiste-standard"></small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Prix Commercial (DH) *</label>
                    <input type="number" class="form-control quick-famille-prix-commercial"
                        name="familles[INDEX][prix_commercial]" min="0" step="0.01" required>
                    <small class="text-muted quick-prix-commercial-standard"></small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Prix Spécial (DH) *</label>
                    <input type="number" class="form-control quick-famille-prix-special"
                        name="familles[INDEX][prix_special]" min="0" step="0.01" required>
                    <small class="text-muted quick-prix-special-standard"></small>
                </div>
            </div>
        </div>
    </template>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .duplicate-item-highlight {
            animation: duplicateItemPulse 1.4s ease;
        }

        @keyframes duplicateItemPulse {
            0% {
                box-shadow: inset 0 0 0 2px rgba(220, 53, 69, 0);
                background-color: transparent;
            }

            15% {
                box-shadow: inset 0 0 0 2px rgba(220, 53, 69, 1);
                background-color: rgba(220, 53, 69, 0.12);
            }

            85% {
                box-shadow: inset 0 0 0 2px rgba(220, 53, 69, 1);
                background-color: rgba(220, 53, 69, 0.12);
            }

            100% {
                box-shadow: inset 0 0 0 2px rgba(220, 53, 69, 0);
                background-color: transparent;
            }
        }

        .quick-famille-row {
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .quick-famille-row.removing {
            opacity: 0;
            transform: translateX(-100%);
        }

        .section-header {
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }

        .section-title {
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .quick-prix-client-standard,
        .quick-prix-grossiste-standard,
        .quick-prix-commercial-standard,
        .quick-prix-special-standard {
            font-size: 0.7rem;
            display: block;
            margin-top: 2px;
            color: #6c757d;
        }

        .payment-details {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
        }

        .check-fields {
            border-left: 3px solid #17a2b8;
            padding-left: 10px;
        }

        .transfer-fields {
            border-left: 3px solid #007bff;
            padding-left: 10px;
        }

        .traite-fields {
            border-left: 3px solid #28a745;
            padding-left: 10px;
        }

        .cash-fields {
            border-left: 3px solid #ffc107;
            padding-left: 10px;
        }

        .advance-fields {
            border-left: 3px solid #6f42c1;
            padding-left: 10px;
        }

        .client-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            border-right: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0 15px;
        }

        .info-item:last-child {
            border-right: none;
        }

        .info-label {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .info-value {
            font-size: 1.2rem;
            font-weight: bold;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
        $(document).ready(function() {

            let itemCounter = 0;
            let paymentCounter = 0;
            let clientCreditData = null;
            let bypassCredit = false;
            let quickFamilleRowIndex = 0;

            $('.select2').select2({
                language: "fr",
                placeholder: "Sélectionner...",
                allowClear: true
            });

            addItemRow();

            function toggleQuickPersonTypeFields(personType) {
                if (personType === 'morale') {
                    $('#quick_entreprise_section').show();
                    $('#quick_name_section').hide();
                    $('#quick_entreprise_name').prop('required', true);
                    $('#quick_name').prop('required', false);
                } else if (personType === 'physique') {
                    $('#quick_name_section').show();
                    $('#quick_entreprise_section').hide();
                    $('#quick_name').prop('required', true);
                    $('#quick_entreprise_name').prop('required', false);
                } else {
                    $('#quick_name_section, #quick_entreprise_section').hide();
                }
            }

            $('#addClientBtn').click(function() {
                $('#quickClientForm')[0].reset();
                $('#quick_person_type').val('');
                toggleQuickPersonTypeFields('');
                $('#addClientModal').modal('show');
            });

            $('#quick_person_type').change(function() {
                toggleQuickPersonTypeFields($(this).val());
            });

            function calculateQuickVolume() {
                var height = parseFloat($('#quick_height_m').val()) || 0;
                var width = parseFloat($('#quick_width_m').val()) || 0;
                var depth = parseFloat($('#quick_depth_m').val()) || 0;

                if (height > 0 && width > 0 && depth > 0) {
                    var volume = height * width * depth;
                    $('#quick_volume_m3').val(volume.toFixed(4));
                } else {
                    $('#quick_volume_m3').val('');
                }
            }


            $('#quick_height_m, #quick_width_m, #quick_depth_m').on('input', calculateQuickVolume);

            function addQuickFamilleRow(data = null) {
                const template = document.getElementById('quickFamilleRowTemplate');
                if (!template) return;

                const clone = template.content.cloneNode(true);
                const row = clone.querySelector('.quick-famille-row');
                const index = quickFamilleRowIndex++;

                row.innerHTML = row.innerHTML.replace(/INDEX/g, index);

                $('#quickFamillesContainer').append(row);

                const $familleSelect = $(row).find('.quick-famille-select');

                let preselectedValue = null;
                if (data && data.famille_id) {
                    preselectedValue = data.famille_id;
                }

                // Initialize Select2
                // $familleSelect.select2({
                //     language: "fr",
                //     placeholder: "Sélectionner une famille...",
                //     allowClear: true,
                //     width: '100%',
                //     dropdownParent: $('#addProductModal'),
                //     minimumResultsForSearch: 1
                // });

                // If there's a preselected value, set it
                if (preselectedValue) {
                    $familleSelect.val(preselectedValue).trigger('change');
                }

                // Add change event to famille select
                $familleSelect.off('change').on('change', function(e) {
                    e.preventDefault();
                    const selectedOption = $(this).find('option:selected');
                    const familleId = $(this).val();

                    if (familleId) {
                        const prixClient = selectedOption.data('prix-client') || 0;
                        const prixGrossiste = selectedOption.data('prix-grossiste') || 0;
                        const prixCommercial = selectedOption.data('prix-commercial') || 0;
                        const prixSpecial = selectedOption.data('prix-special') || 0;

                        // Set the price fields
                        $(row).find('.quick-famille-prix-client').val(prixClient);
                        $(row).find('.quick-famille-prix-grossiste').val(prixGrossiste);
                        $(row).find('.quick-famille-prix-commercial').val(prixCommercial);
                        $(row).find('.quick-famille-prix-special').val(prixSpecial);

                        // Show standard prices as reference
                        $(row).find('.quick-prix-client-standard').text('Std: ' + prixClient.toFixed(2) +
                            ' DH');
                        $(row).find('.quick-prix-grossiste-standard').text('Std: ' + prixGrossiste.toFixed(
                            2) + ' DH');
                        $(row).find('.quick-prix-commercial-standard').text('Std: ' + prixCommercial
                            .toFixed(2) + ' DH');
                        $(row).find('.quick-prix-special-standard').text('Std: ' + prixSpecial.toFixed(2) +
                            ' DH');
                    } else {
                        // Clear price fields if no famille selected
                        $(row).find('.quick-famille-prix-client').val('');
                        $(row).find('.quick-famille-prix-grossiste').val('');
                        $(row).find('.quick-famille-prix-commercial').val('');
                        $(row).find('.quick-famille-prix-special').val('');
                        $(row).find('.quick-prix-client-standard').text('');
                        $(row).find('.quick-prix-grossiste-standard').text('');
                        $(row).find('.quick-prix-commercial-standard').text('');
                        $(row).find('.quick-prix-special-standard').text('');
                    }

                    // Manually trigger the change event on the original select for form submission
                    $familleSelect.trigger('change.select2');
                });

                // Add remove functionality
                $(row).find('.remove-quick-famille-btn').off('click').on('click', function() {
                    // Destroy Select2 before removing
                    if ($familleSelect.data('select2')) {
                        $familleSelect.select2('destroy');
                    }
                    $(row).addClass('removing');
                    setTimeout(() => {
                        $(row).remove();
                    }, 300);
                });

                return row;
            }

            $('#addProductFromTopBtn, #addProductBtn').click(function() {
                $('#quickProductForm')[0].reset();
                $('#quick_volume_m3').val('');
                $('#quickFamillesContainer').empty();
                quickFamilleRowIndex = 0;
                addQuickFamilleRow(); // Add one default row

                // // Small delay to ensure modal is fully shown before Select2 initialization
                // setTimeout(function() {
                //     $('#addProductModal').modal('show');
                //     // Re-initialize any Select2 elements in the modal
                //     $('#addProductModal').find('.quick-famille-select').each(function() {
                //         if ($(this).data('select2')) {
                //             $(this).select2('destroy');
                //         }
                //         $(this).select2({
                //             language: "fr",
                //             placeholder: "Sélectionner une famille...",
                //             allowClear: true,
                //             width: '100%',
                //             dropdownParent: $('#addProductModal')
                //         });
                //     });
                // }, 100);
            });

            $(document).on('click', '#quickAddFamilleBtn', function() {
                addQuickFamilleRow();
            });


            // Save Quick Client
            $('#saveQuickClientBtn').click(function() {
                var formData = $('#quickClientForm').serialize();

                $.ajax({
                    url: "{{ route('clients.store') }}",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', 'Client créé avec succès');

                            $.ajax({
                                url: "{{ route('clients.list') }}",
                                type: "GET",
                                success: function(clientsResponse) {
                                    if (clientsResponse.success) {

                                        console.log('Clients list refreshed:',
                                            clientsResponse.data);
                                        var clientSelect = $('#client_id');
                                        clientSelect.empty().append(
                                            '<option value="">Sélectionner un client</option>'
                                        );

                                        clientsResponse.data.forEach(function(
                                            client) {
                                            clientSelect.append(
                                                `<option value="${client.client_id}" data-client-type="${client.client_type}">
                                        ${client.display_name} (${client.phone}) - ${client.client_type_label}
                                    </option>`
                                            );
                                        });

                                        // Select the newly created client
                                        if (response.client_id) {
                                            clientSelect.val(response.client_id)
                                                .trigger('change');
                                        }

                                        clientSelect.select2({
                                            language: "fr",
                                            placeholder: "Sélectionner un client",
                                            allowClear: true
                                        });
                                    }
                                }
                            });

                            $('#addClientModal').modal('hide');
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON?.errors;
                        var errorMessage = '';
                        if (errors) {
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        } else {
                            errorMessage = xhr.responseJSON?.message ||
                                'Erreur lors de la création du client';
                        }
                        showToast('error', errorMessage);
                    }
                });
            });

            // Save Quick Product
            $('#saveQuickProductBtn').click(function() {
                // Validate at least one famille
                if ($('#quickFamillesContainer .quick-famille-row').length === 0) {
                    showToast('error', 'Veuillez ajouter au moins une famille');
                    return;
                }

                // Create FormData object
                const formData = new FormData();

                // Add basic product fields
                formData.append('product_code', $('#quick_product_code').val());
                formData.append('product_name', $('#quick_product_name').val());
                formData.append('product_type', $('#quick_product_type').val());
                formData.append('unit_of_measure', $('#quick_unit_of_measure').val());
                formData.append('height_m', $('#quick_height_m').val() || 0);
                formData.append('width_m', $('#quick_width_m').val() || 0);
                formData.append('depth_m', $('#quick_depth_m').val() || 0);
                formData.append('volume_m3', $('#quick_volume_m3').val() || 0);
                formData.append('min_stock_level', $('#quick_min_stock_level').val() || 0);
                formData.append('max_stock_level', $('#quick_max_stock_level').val() || 0);
                formData.append('description', $('#quick_description').val());
                formData.append('is_active', $('#quick_is_active').val());
                formData.append('_token', $('meta[name="csrf-token"]').attr('content') ||
                    '{{ csrf_token() }}');

                // Collect famille data as array
                const famillesData = [];
                let hasError = false;

                $('#quickFamillesContainer .quick-famille-row').each(function(index) {
                    const familleId = $(this).find('.quick-famille-select').val();
                    const prixClient = $(this).find('.quick-famille-prix-client').val();
                    const prixGrossiste = $(this).find('.quick-famille-prix-grossiste').val();
                    const prixCommercial = $(this).find('.quick-famille-prix-commercial').val();
                    const prixSpecial = $(this).find('.quick-famille-prix-special').val();

                    if (!familleId) {
                        showToast('error', 'Veuillez sélectionner une famille pour chaque ligne');
                        hasError = true;
                        return false;
                    }

                    famillesData.push({
                        famille_id: parseInt(familleId),
                        prix_client: parseFloat(prixClient) || 0,
                        prix_grossiste: parseFloat(prixGrossiste) || 0,
                        prix_commercial: parseFloat(prixCommercial) || 0,
                        prix_special: parseFloat(prixSpecial) || 0
                    });
                });

                if (hasError) return;

                // Add familles as JSON string
                formData.append('familles', JSON.stringify(famillesData));

                // Show loading state
                const saveBtn = $('#saveQuickProductBtn');
                const originalText = saveBtn.html();
                saveBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Création...');

                $.ajax({
                    url: "{{ route('products.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', 'Produit créé avec succès');

                            // Reload all item selects to include the new product
                            $('#items-body tr').each(function() {
                                let rowId = $(this).attr('id');
                                let currentType = $(this).find('.item-type').val();

                                if (currentType && $('#client_id').val()) {
                                    loadItemsForType(currentType, rowId);
                                }
                            });

                            $('#addProductModal').modal('hide');
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        let errorMessage = '';
                        if (errors) {
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        } else {
                            errorMessage = xhr.responseJSON?.message ||
                                'Erreur lors de la création du produit';
                        }
                        showToast('error', errorMessage);
                    },
                    complete: function() {
                        saveBtn.prop('disabled', false).html(originalText);
                    }
                });
            });


            // Add item button
            $('#add-item').click(function() {
                addItemRow();
            });

            // Add payment button
            $('#add-payment').click(function() {
                addPaymentRow();
            });

            // Client change handler
            $('#client_id').change(function() {
                let clientId = $(this).val();

                if (clientId) {
                    checkClientCreditStatus(clientId);

                    // Reload items for each row
                    $('#items-body tr').each(function() {
                        let rowId = $(this).attr('id');
                        let currentType = $(this).find('.item-type').val();

                        if (currentType) {
                            loadItemsForType(currentType, rowId);
                        }
                    });
                } else {
                    $('#client-credit-info').empty();
                }
            });

            // Function to check client credit status
            function checkClientCreditStatus(clientId) {
                $.ajax({
                    url: "{{ route('clients.credit-status', ['id' => ':clientId']) }}".replace(':clientId',
                        clientId),
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            clientCreditData = response.data;
                            displayClientCreditInfo(response.data);

                            // Enable/disable advance option based on available advance
                            $('.advance-option').prop('disabled', !response.data.has_advance);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error checking credit:', xhr);
                        showToast('error', 'Erreur lors de la vérification du crédit client');
                    }
                });
            }

            // Function to display client credit info
            function displayClientCreditInfo(data) {
                let html = '<div class="client-info-card">';
                html += '<div class="row">';

                // Credit info
                html += '<div class="col-md-4 info-item">';
                html += '<div class="info-label">Crédit (Plafond)</div>';
                if (data.has_credit) {
                    html += '<div class="info-value">' + data.credit_formatted + '</div>';
                    html += '<small>Limite: ' + data.credit_limit + ' DH</small>';
                    html += '<div class="mt-2">';
                    html += '<small>Utilisé: ' + data.credit_usage + ' DH</small>';
                    html += '<div class="progress mt-1" style="height: 5px;">';
                    let percentage = Math.min(100, (data.credit_usage / data.credit_limit) * 100);
                    let barClass = percentage >= 90 ? 'bg-danger' : (percentage >= 70 ? 'bg-warning' :
                        'bg-success');
                    html += '<div class="progress-bar ' + barClass + '" ';
                    html += 'role="progressbar" style="width: ' + percentage + '%"></div>';
                    html += '</div></div>';
                } else {
                    html += '<div class="info-value">0,00 DH</div>';
                }
                html += '</div>';

                // Balance info
                html += '<div class="col-md-4 info-item">';
                html += '<div class="info-label">Solde</div>';

                if (data.balance > 0) {
                    // Positive balance = client has overpaid (advance)
                    html += '<div class="info-value text-success">+' + data.balance + ' DH</div>';
                    html += '<small><i class="fas fa-arrow-up me-1"></i>Trop-perçu (Nous devons)</small>';
                    html += '<div class="mt-2 small">Disponible: ' + data.advance_formatted + '</div>';
                } else if (data.balance < 0) {
                    // Negative balance = client owes us
                    html += '<div class="info-value text-danger">' + data.balance + ' DH</div>';
                    html += '<small><i class="fas fa-arrow-down me-1"></i>Impayé (Client doit)</small>';
                    html += '<div class="mt-2 small">Total dû: ' + data.debt_formatted + '</div>';
                } else {
                    html += '<div class="info-value">0,00 DH</div>';
                    html += '<small>Soldé</small>';
                }
                html += '</div>';

                // Status summary
                html += '<div class="col-md-4">';
                html += '<div class="info-label">Résumé</div>';
                html += '<div class="mt-2">';

                if (data.has_advance) {
                    html += '<span class="badge bg-success me-1">';
                    html += '<i class="fas fa-wallet me-1"></i>Avance: ' + data.advance_formatted;
                    html += '</span>';
                }

                if (data.has_debt) {
                    html += '<span class="badge bg-danger">';
                    html += '<i class="fas fa-exclamation-triangle me-1"></i>Dette: ' + data.debt_formatted;
                    html += '</span>';
                }

                if (!data.has_advance && !data.has_debt) {
                    html += '<span class="badge bg-secondary">';
                    html += '<i class="fas fa-check-circle me-1"></i>Compte soldé';
                    html += '</span>';
                }

                html += '</div></div>';

                html += '</div></div>';

                $('#client-credit-info').html(html);
            }

            // Function to add item row
            function addItemRow() {
                let rowId = 'item_' + Date.now() + '_' + itemCounter;
                let itemIndex = itemCounter;

                let typeOptions = `
                    <option value="">Sélectionner</option>
                    <option value="raw_material">Matière Première</option>
                    <option value="production">Bloc</option>
                    <option value="decoupage">Sous Bloc</option>
                    <option value="finale" selected>Vente</option>
                `;

                let row = `
                    <tr id="${rowId}" data-index="${itemIndex}">
                        <td>${itemCounter + 1}</td>
                        <td>
                            <select class="form-control item-type" data-row="${rowId}" required>
                                ${typeOptions}
                            </select>
                        </td>
                        <td>
                            <select class="form-control item-select" data-row="${rowId}" style="width:100%;" required>
                                <option value="">Sélectionner d'abord un client</option>
                            </select>
                            <input type="hidden" class="item-id" name="items[${itemIndex}][item_id]">
                            <input type="hidden" class="item-name" name="items[${itemIndex}][name]">
                            <input type="hidden" class="item-type-input" name="items[${itemIndex}][type]">
                            <input type="hidden" class="family-id" name="items[${itemIndex}][family_id]">
                            <input type="hidden" class="family-name" name="items[${itemIndex}][family_name]">
                        </td>
                        <td>
                            <input type="number" class="form-control item-quantity"
                                name="items[${itemIndex}][quantity]" min="0.0001" step="0.0001" value="1" required disabled>
                        </td>
                        <td>
                            <input type="number" class="form-control item-price"
                                name="items[${itemIndex}][unit_price]" min="0" step="0.01" value="0" required disabled>
                        </td>
                        <td class="item-total">0.00 DH</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger remove-item" data-row="${rowId}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    <tr>
                `;

                $('#items-body').append(row);

                // Initialize Select2 safely
                try {
                    const $select = $(`#${rowId} .item-select`);
                    if ($select.length) {
                        $select.select2({
                            language: "fr",
                            placeholder: "Sélectionner un article...",
                            width: '100%'
                        });
                    }
                } catch (e) {
                    console.warn('Select2 initialization error:', e);
                }

                $(`#${rowId} .item-type`).on('change', function() {
                    let type = $(this).val();
                    let row = $(this).data('row');
                    let clientId = $('#client_id').val();

                    if (!clientId) {
                        showToast('warning', 'Veuillez d\'abord sélectionner un client');
                        $(this).val('finale');
                        return;
                    }

                    loadItemsForType(type, row);
                });

                $(`#${rowId} .item-select`).on('change', function() {
                    let row = $(this).data('row');
                    let selectedOption = $(this).find(':selected');
                    let value = $(this).val();
                    let clientType = $('#client_id option:selected').data('client-type') || 'client';
                    let itemType = $(`#${row} .item-type`).val();

                    if (value) {
                        let itemData = {};

                        if (value.includes('_') && itemType !== 'raw_material') {
                            let parts = value.split('_');
                            let productId = parts[0];
                            let familyId = parts[1];

                            let price = 0;
                            switch (clientType) {
                                case 'grossiste':
                                    price = parseFloat(selectedOption.data('family-price-grossiste')) || 0;
                                    break;
                                case 'commerciale':
                                    price = parseFloat(selectedOption.data('family-price-commercial')) || 0;
                                    break;
                                case 'special':
                                    price = parseFloat(selectedOption.data('family-price-special')) || 0;
                                    break;
                                default:
                                    price = parseFloat(selectedOption.data('family-price-client')) || 0;
                            }

                            price = Math.ceil(price);


                            itemData = {
                                id: productId,
                                name: selectedOption.data('product-name'),
                                code: selectedOption.data('product-code'),
                                price: price,
                                hasFamilies: true,
                                familyId: familyId,
                                familyName: selectedOption.data('family-name')
                            };
                        } else {
                            let price = 0;

                            if (itemType === 'raw_material') {
                                switch (clientType) {
                                    case 'grossiste':
                                        price = parseFloat(selectedOption.data('price-grossiste')) || 0;
                                        break;
                                    case 'commerciale':
                                        price = parseFloat(selectedOption.data('price-commercial')) || 0;
                                        break;
                                    case 'special':
                                        price = parseFloat(selectedOption.data('price-special')) || 0;
                                        break;
                                    default:
                                        price = parseFloat(selectedOption.data('price-client')) || 0;
                                }
                            } else {
                                price = parseFloat(selectedOption.data('price')) || 0;
                            }

                            price = Math.ceil(price);

                            itemData = {
                                id: value,
                                name: selectedOption.data('name'),
                                code: selectedOption.data('code'),
                                price: price,
                                hasFamilies: false,
                                familyId: null,
                                familyName: null
                            };
                        }

                        let duplicateRowId = findDuplicateItemRow(row, itemData.id, itemData.familyId);
                        if (duplicateRowId) {
                            showToast('warning',
                                'Cet article est déjà sélectionné dans la liste. Veuillez en choisir un autre.'
                            );
                            $(this).val('').trigger('change');
                            $(`#${row} .item-id`).val('');
                            $(`#${row} .item-name`).val('');
                            $(`#${row} .family-id`).val('');
                            $(`#${row} .family-name`).val('');
                            $(`#${row} .item-price`).val(0).prop('disabled', true);
                            $(`#${row} .item-quantity`).prop('disabled', true);
                            calculateItemTotal(row);
                            updateOrderTotal();
                            highlightDuplicateRow(duplicateRowId);
                            return;
                        }

                        updateItemFromSelection(row, itemData);
                    }
                });

                $(`#${rowId} .item-quantity, #${rowId} .item-price`).on('input', function() {
                    calculateItemTotal(rowId);
                    updateOrderTotal();
                });

                $(`#${rowId} .remove-item`).on('click', function() {
                    let row = $(this).data('row');
                    $(`#${row}`).remove();
                    updateItemIndices();
                    updateOrderTotal();
                });

                let clientId = $('#client_id').val();
                if (clientId) {
                    loadItemsForType('finale', rowId);
                }

                itemCounter++;
            }

            $('#addProductFromTopBtn').click(function() {
                // Reset form
                $('#quickProductForm')[0].reset();
                $('#quick_volume_m3').val('');
                $('#quickFamillesContainer').empty();
                quickFamilleRowIndex = 0;

                // Add initial famille row
                addQuickFamilleRow();

                // Show modal
                $('#addProductModal').modal('show');

                // Ensure Select2 works properly after modal is shown
                // setTimeout(function() {
                //     $('#addProductModal').find('.quick-famille-select').each(function() {
                //         if ($(this).data('select2')) {
                //             $(this).select2('destroy');
                //         }
                //         $(this).select2({
                //             language: "fr",
                //             placeholder: "Sélectionner une famille...",
                //             allowClear: true,
                //             width: '100%',
                //             dropdownParent: $('#addProductModal'),
                //             minimumResultsForSearch: 1
                //         });
                //     });
                // }, 200);
            });

            function loadItemsForType(type, rowId) {
                let select = $(`#${rowId} .item-select`);
                let clientId = $('#client_id').val();
                let previousValue = select.val();

                if (!clientId) {
                    select.empty().append('<option value="">Veuillez d\'abord sélectionner un client</option>');
                    select.trigger('change');
                    return;
                }

                select.empty().append('<option value="">Chargement...</option>').prop('disabled', true);

                let url = type === 'raw_material' ? "{{ route('raw-materials.getListForSale') }}" :
                    "{{ route('products.by-type', '') }}/" + type;

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        select.empty().append('<option value="">Sélectionner un article</option>').prop(
                            'disabled', false);

                        let items = response.data || response;

                        if (items.length === 0) {
                            select.append(
                                '<option value="" disabled>Aucun article disponible</option>');
                        } else {
                            items.forEach(function(item) {
                                // Check if it's a raw material (no families)
                                if (type === 'raw_material' || !item.has_families) {
                                    // Raw material or product without families
                                    let option = $('<option></option>')
                                        .attr('value', item.id || item.material_id)
                                        .attr('data-name', item.name)
                                        .attr('data-code', item.code || '')
                                        .attr('data-price', item.price || 0)
                                        .attr('data-price-client', item.prix_client || 0)
                                        .attr('data-price-grossiste', item.prix_grossiste || 0)
                                        .attr('data-price-commercial', item.prix_commercial || 0)
                                        .attr('data-price-special', item.prix_special || 0)
                                        .attr('data-volume', item.volume || 0)
                                        .attr('data-has-families', false)
                                        .attr('data-type', type)
                                        .text(item.name + (item.code ? ' (' + item.code + ')' :
                                                '') +
                                            (item.current_stock ? ' - Stock: ' + item
                                                .current_stock : ''));

                                    select.append(option);
                                }
                                // Product with families
                                else if (item.has_families && item.families && item.families
                                    .length > 0) {
                                    item.families.forEach(function(family) {
                                        let optionValue = item.id + '_' + family.id;
                                        let displayName = item.name + ' - ' + family
                                            .name;
                                        let option = $('<option></option>')
                                            .attr('value', optionValue)
                                            .attr('data-product-id', item.id)
                                            .attr('data-product-name', item.name)
                                            .attr('data-product-code', item.code || '')
                                            .attr('data-family-id', family.id)
                                            .attr('data-family-name', family.name)
                                            .attr('data-family-price-client', family
                                                .prix_client || 0)
                                            .attr('data-family-price-grossiste', family
                                                .prix_grossiste || 0)
                                            .attr('data-family-price-commercial', family
                                                .prix_commercial || 0)
                                            .attr('data-family-price-special', family
                                                .prix_special || 0)
                                            .attr('data-volume', item.volume || 0)
                                            .attr('data-has-families', true)
                                            .text(displayName + (item.code ? ' (' + item
                                                .code + ')' : ''));

                                        select.append(option);
                                    });
                                }
                            });
                        }

                        if (previousValue && select.find(`option[value="${previousValue}"]`).length) {
                            select.val(previousValue);
                        }

                        select.trigger('change');
                        $(`#${rowId} .item-type-input`).val(type);
                    },
                    error: function(xhr) {
                        console.error('Error loading items:', xhr);
                        select.empty().append('<option value="">Erreur de chargement</option>').prop(
                            'disabled', false);
                        select.trigger('change');
                    }
                });
            }

            function findDuplicateItemRow(currentRowId, itemId, familyId) {
                let duplicateRowId = null;
                $('#items-body tr').each(function() {
                    if ($(this).attr('id') === currentRowId) return true;
                    let existingId = $(this).find('.item-id').val();
                    let existingFamilyId = $(this).find('.family-id').val() || '';
                    if (existingId && String(existingId) === String(itemId) &&
                        existingFamilyId === String(familyId || '')) {
                        duplicateRowId = $(this).attr('id');
                        return false;
                    }
                });
                return duplicateRowId;
            }

            function isItemAlreadySelected(currentRowId, itemId, familyId) {
                return !!findDuplicateItemRow(currentRowId, itemId, familyId);
            }

            function highlightDuplicateRow(rowId) {
                let $row = $(`#${rowId}`);
                if (!$row.length) return;

                $('html, body').animate({
                    scrollTop: $row.offset().top - 150
                }, 400);

                let $cells = $row.find('td');
                $cells.removeClass('duplicate-item-highlight');
                void $row[0].offsetWidth;
                $cells.addClass('duplicate-item-highlight');
                setTimeout(function() {
                    $cells.removeClass('duplicate-item-highlight');
                }, 1400);
            }

            function updateItemFromSelection(rowId, itemData) {
                $(`#${rowId} .item-id`).val(itemData.id);
                $(`#${rowId} .item-name`).val(itemData.name);

                if (itemData.hasFamilies && itemData.familyId) {
                    $(`#${rowId} .family-id`).val(itemData.familyId);
                    $(`#${rowId} .family-name`).val(itemData.familyName);
                } else {
                    $(`#${rowId} .family-id`).val('');
                    $(`#${rowId} .family-name`).val('');
                }

                let finalPrice = itemData.price;

                $(`#${rowId} .item-price`).val(finalPrice).prop('disabled', false);
                $(`#${rowId} .item-quantity`).prop('disabled', false);

                calculateItemTotal(rowId);
                updateOrderTotal();
            }

            function calculateItemTotal(rowId) {
                let quantity = parseFloat($(`#${rowId} .item-quantity`).val()) || 0;
                let price = parseFloat($(`#${rowId} .item-price`).val()) || 0;
                let total = quantity * price;
                $(`#${rowId} .item-total`).text(total.toFixed(2) + ' DH');
            }

            function updateItemIndices() {
                $('#items-body tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                    $(this).attr('data-index', index);

                    $(this).find('.item-id').attr('name', `items[${index}][item_id]`);
                    $(this).find('.item-name').attr('name', `items[${index}][name]`);
                    $(this).find('.item-type-input').attr('name', `items[${index}][type]`);
                    $(this).find('.item-quantity').attr('name', `items[${index}][quantity]`);
                    $(this).find('.item-price').attr('name', `items[${index}][unit_price]`);
                    $(this).find('.family-id').attr('name', `items[${index}][family_id]`);
                    $(this).find('.family-name').attr('name', `items[${index}][family_name]`);
                });
            }

            function updateOrderTotal() {
                let total = 0;
                $('.item-total').each(function() {
                    let text = $(this).text().replace(' DH', '').replace(/,/g, '');
                    total += parseFloat(text) || 0;
                });
                $('#order-total').text(total.toFixed(2) + ' DH');
                updatePaymentSummary();
            }

            function addPaymentRow() {
                let rowId = 'payment_' + Date.now() + '_' + paymentCounter;
                let paymentIndex = paymentCounter;

                let row = `
                    <tr id="${rowId}" data-payment-index="${paymentIndex}">
                        <td>${paymentCounter + 1}</td>
                        <td>
                            <select class="form-control payment-method" data-row="${rowId}" required>
                                <option value="">Sélectionner</option>
                                <option value="cash">Espèces</option>
                                <option value="check">Chèque</option>
                                <option value="transfer">Virement</option>
                                <option value="traite">Traite / Lettre de change</option>
                                <option value="advance" class="advance-option">Solde client (Avance)</option>
                            </select>
                            <input type="hidden" class="payment-method-input" name="payments[${paymentIndex}][method]">
                        </td>
                        <td>
                            <input type="number" class="form-control payment-amount"
                                name="payments[${paymentIndex}][amount]" min="0.01" step="0.01" required>
                        </td>
                        <td>
                            <input type="date" class="form-control payment-date"
                                name="payments[${paymentIndex}][date]" value="{{ date('Y-m-d') }}" required>
                        </td>
                        <td>
                            <div class="payment-details-container" id="details-${rowId}"></div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-payment" data-row="${rowId}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#payments-body').append(row);

                $(`#${rowId} .payment-method`).on('change', function() {
                    let method = $(this).val();
                    let row = $(this).data('row');
                    let paymentIndex = $(`#${row}`).data('payment-index');

                    $(`#${row} .payment-method-input`).val(method);
                    $(`#details-${row}`).empty();

                    switch (method) {
                        case 'check':
                            showCheckFields(row, paymentIndex);
                            break;
                        case 'transfer':
                            showTransferFields(row, paymentIndex);
                            break;
                        case 'traite':
                            showTraiteFields(row, paymentIndex);
                            break;
                        case 'cash':
                            showCashFields(row, paymentIndex);
                            break;
                        case 'advance':
                            showAdvanceFields(row, paymentIndex);
                            break;
                    }
                });

                $(`#${rowId} .payment-amount`).on('input', function() {
                    validateAdvanceAmount(rowId);
                    updatePaymentSummary();
                });

                $(`#${rowId} .remove-payment`).on('click', function() {
                    let row = $(this).data('row');
                    $(`#${row}`).remove();
                    updatePaymentIndices();
                    updatePaymentSummary();
                });

                paymentCounter++;
            }

            function showCheckFields(rowId, paymentIndex) {
                let html = `
                    <div class="check-fields">
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm"
                                   name="payments[${paymentIndex}][check_number]"
                                   placeholder="N° Chèque" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm"
                                   name="payments[${paymentIndex}][bank_name]"
                                   placeholder="Banque" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm"
                                   name="payments[${paymentIndex}][account_holder]"
                                   placeholder="Titulaire" required>
                        </div>
                        <div class="mb-2">
                            <input type="date" class="form-control form-control-sm"
                                   name="payments[${paymentIndex}][due_date]"
                                   value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Document (Image du chèque)</label>
                            <input type="file" class="form-control form-control-sm payment-file"
                                   name="payments[${paymentIndex}][document]" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Format: PDF, JPG, PNG (max: 5MB)</small>
                        </div>
                    </div>
                `;
                $(`#details-${rowId}`).html(html);
            }

            function showTransferFields(rowId, paymentIndex) {
                let html = `
                    <div class="transfer-fields">
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm"
                                   name="payments[${paymentIndex}][transfer_reference]"
                                   placeholder="Référence virement" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm"
                                   name="payments[${paymentIndex}][bank_name]"
                                   placeholder="Banque émettrice" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm"
                                   name="payments[${paymentIndex}][account_number]"
                                   placeholder="Compte bénéficiaire" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Justificatif de virement</label>
                            <input type="file" class="form-control form-control-sm payment-file"
                                   name="payments[${paymentIndex}][document]" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Format: PDF, JPG, PNG (max: 5MB)</small>
                        </div>
                    </div>
                `;
                $(`#details-${rowId}`).html(html);
            }

            function showTraiteFields(rowId, paymentIndex) {
                let html = `
                    <div class="traite-fields">
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm"
                                   name="payments[${paymentIndex}][traite_number]"
                                   placeholder="N° Traite" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm"
                                   name="payments[${paymentIndex}][drawee]"
                                   placeholder="Tiré (client)" required>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control form-control-sm"
                                      name="payments[${paymentIndex}][drawee_address]"
                                      placeholder="Adresse du tiré"
                                      rows="2"></textarea>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm"
                                   name="payments[${paymentIndex}][bank_name]"
                                   placeholder="Banque" required>
                        </div>
                        <div class="mb-2">
                            <input type="date" class="form-control form-control-sm"
                                   name="payments[${paymentIndex}][due_date]"
                                   value="{{ date('Y-m-d', strtotime('+60 days')) }}" required>
                            <small class="text-muted">Date d'échéance</small>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Document (Lettre de change)</label>
                            <input type="file" class="form-control form-control-sm payment-file"
                                   name="payments[${paymentIndex}][document]" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Format: PDF, JPG, PNG (max: 5MB)</small>
                        </div>
                    </div>
                `;
                $(`#details-${rowId}`).html(html);
            }

            function showCashFields(rowId, paymentIndex) {
                let html = `
                    <div class="cash-fields">
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm"
                                   name="payments[${paymentIndex}][cash_reference]"
                                   placeholder="Référence (optionnel)">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Reçu / Document</label>
                            <input type="file" class="form-control form-control-sm payment-file"
                                   name="payments[${paymentIndex}][document]" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Format: PDF, JPG, PNG (max: 5MB)</small>
                        </div>
                    </div>
                `;
                $(`#details-${rowId}`).html(html);
            }

            function showAdvanceFields(rowId, paymentIndex) {
                if (!clientCreditData) {
                    $(`#details-${rowId}`).html(
                        '<div class="alert alert-warning">Veuillez d\'abord sélectionner un client</div>'
                    );
                    return;
                }

                let availableAdvance = clientCreditData.available_advance;
                let advanceFormatted = clientCreditData.advance_formatted;

                let html = `
                    <div class="advance-fields">
                        <div class="mb-2">
                            <div class="alert alert-success py-2 mb-2">
                                <strong>Solde disponible:</strong>
                                <span class="text-success fw-bold">${advanceFormatted}</span>
                            </div>
                            <input type="hidden" class="max-advance-amount" value="${availableAdvance}">

                            ${availableAdvance <= 0 ? `
                                                                                                                                                                                                                                                                                                                                                                    <div class="alert alert-warning py-1 mb-2 small">
                                                                                                                                                                                                                                                                                                                                                                        <i class="fas fa-exclamation-triangle"></i> Aucun solde disponible
                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                ` : ''}
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Référence (optionnel)</label>
                            <input type="text" class="form-control form-control-sm"
                                name="payments[${paymentIndex}][advance_reference]"
                                placeholder="Ex: Utilisation solde">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Document justificatif</label>
                            <input type="file" class="form-control form-control-sm payment-file"
                                name="payments[${paymentIndex}][document]" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <small class="text-muted d-block">
                            <i class="fas fa-info-circle"></i>
                            Le solde sera déduit de l'avance du client
                        </small>
                    </div>
                `;
                $(`#details-${rowId}`).html(html);
            }

            function validateAdvanceAmount(rowId) {
                let method = $(`#${rowId} .payment-method`).val();
                if (method === 'advance') {
                    let amount = parseFloat($(`#${rowId} .payment-amount`).val()) || 0;
                    let maxAdvance = parseFloat($(`#${rowId} .max-advance-amount`).val()) || 0;

                    if (amount > maxAdvance) {
                        $(`#${rowId} .payment-amount`).addClass('is-invalid');
                        if (!$(`#${rowId} .advance-error`).length) {
                            $(`#${rowId} .payment-amount`).after(
                                '<div class="invalid-feedback advance-error">Le montant ne peut pas dépasser le solde disponible (' +
                                maxAdvance.toFixed(2) + ' DH)</div>'
                            );
                        }
                        return false;
                    } else {
                        $(`#${rowId} .payment-amount`).removeClass('is-invalid');
                        $(`#${rowId} .advance-error`).remove();
                        return true;
                    }
                }
                return true;
            }

            function updatePaymentIndices() {
                $('#payments-body tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                    $(this).attr('data-payment-index', index);

                    $(this).find('.payment-method-input').attr('name', `payments[${index}][method]`);
                    $(this).find('.payment-amount').attr('name', `payments[${index}][amount]`);
                    $(this).find('.payment-date').attr('name', `payments[${index}][date]`);
                    $(this).find('.payment-file').attr('name', `payments[${index}][document]`);

                    // Update check fields
                    $(this).find('input[name*="check_number"]').attr('name',
                        `payments[${index}][check_number]`);
                    $(this).find('input[name*="bank_name"]').attr('name', `payments[${index}][bank_name]`);
                    $(this).find('input[name*="account_holder"]').attr('name',
                        `payments[${index}][account_holder]`);
                    $(this).find('input[name*="due_date"]').attr('name', `payments[${index}][due_date]`);

                    // Update transfer fields
                    $(this).find('input[name*="transfer_reference"]').attr('name',
                        `payments[${index}][transfer_reference]`);
                    $(this).find('input[name*="account_number"]').attr('name',
                        `payments[${index}][account_number]`);

                    // Update traite fields
                    $(this).find('input[name*="traite_number"]').attr('name',
                        `payments[${index}][traite_number]`);
                    $(this).find('input[name*="drawee"]').attr('name', `payments[${index}][drawee]`);
                    $(this).find('textarea[name*="drawee_address"]').attr('name',
                        `payments[${index}][drawee_address]`);

                    // Update cash fields
                    $(this).find('input[name*="cash_reference"]').attr('name',
                        `payments[${index}][cash_reference]`);

                    // Update advance fields
                    $(this).find('input[name*="advance_reference"]').attr('name',
                        `payments[${index}][advance_reference]`);
                });
            }

            function updatePaymentSummary() {
                let orderTotal = parseFloat($('#order-total').text().replace(' DH', '').replace(/,/g, '')) || 0;
                let totalPaid = 0;
                let advanceUsed = 0;

                // Only calculate if there are payment rows
                if ($('#payments-body tr').length > 0) {
                    $('.payment-amount').each(function() {
                        let amount = parseFloat($(this).val()) || 0;
                        totalPaid += amount;

                        let method = $(this).closest('tr').find('.payment-method').val();
                        if (method === 'advance') {
                            advanceUsed += amount;
                        }
                    });
                }

                let remaining = orderTotal - totalPaid;
                let creditUsed = remaining > 0 ? remaining : 0;

                $('#total-paid').text(totalPaid.toFixed(2) + ' DH');
                $('#credit-used').text(creditUsed.toFixed(2) + ' DH');
                $('#remaining-amount').text(remaining.toFixed(2) + ' DH');

                // Check credit limit
                if (clientCreditData && clientCreditData.has_credit) {
                    let newTotalCredit = clientCreditData.credit_usage + creditUsed;
                    let creditAvailable = clientCreditData.credit_limit - clientCreditData.credit_usage;

                    if (creditUsed > 0) {
                        if (creditUsed <= creditAvailable) {
                            $('#credit-info').html(`
                    <span class="text-success">
                        <i class="fas fa-check-circle"></i>
                        Crédit disponible après vente: ${(creditAvailable - creditUsed).toFixed(2)} DH
                    </span>
                `);
                        } else {
                            let exceedsBy = creditUsed - creditAvailable;
                            $('#credit-info').html(`
                    <span class="text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Dépassement de crédit: ${exceedsBy.toFixed(2)} DH
                    </span>
                `);
                        }
                    } else {
                        // No new credit used
                        $('#credit-info').html(`
                <span class="text-info">
                    <i class="fas fa-info-circle"></i>
                    Crédit disponible: ${creditAvailable.toFixed(2)} DH
                </span>
            `);
                    }
                } else {
                    $('#credit-info').empty();
                }

                // Determine payment status
                let statusHtml = '';
                if (orderTotal === 0) {
                    statusHtml = '<span class="badge bg-warning p-2">Non déterminé</span>';
                } else if (totalPaid <= 0) {
                    statusHtml = '<span class="badge bg-danger p-2">Non Payé (sur crédit)</span>';
                } else if (totalPaid >= orderTotal - 0.01) {
                    statusHtml = '<span class="badge bg-success p-2">Payé</span>';
                } else {
                    let percentage = ((totalPaid / orderTotal) * 100).toFixed(1);
                    statusHtml = '<span class="badge bg-info p-2">Avance (' + percentage + '%)</span>';
                }
                $('#payment-status-display').html(statusHtml);

                // Show payment breakdown
                let breakdownHtml = '';
                if (creditUsed > 0) {
                    breakdownHtml +=
                        `<div><small>Crédit utilisé pour cette vente: ${creditUsed.toFixed(2)} DH</small></div>`;
                }
                if (advanceUsed > 0) {
                    breakdownHtml += `<div><small>Solde utilisé: ${advanceUsed.toFixed(2)} DH</small></div>`;
                }
                $('#payment-breakdown').html(breakdownHtml);
            }

            function checkClientCredit(clientId, orderTotal, totalPaid) {
                return new Promise((resolve, reject) => {
                    if (!clientCreditData) {
                        resolve({
                            can_proceed: true
                        });
                        return;
                    }

                    let remaining = orderTotal - totalPaid;

                    if (remaining <= 0) {
                        resolve({
                            can_proceed: true
                        });
                        return;
                    }

                    if (!clientCreditData.has_credit) {
                        resolve({
                            can_proceed: true,
                            warning: 'Ce client n\'a pas de limite de crédit'
                        });
                        return;
                    }

                    let creditAvailable = clientCreditData.credit_limit - clientCreditData.credit_usage;

                    if (remaining <= creditAvailable || bypassCredit) {
                        resolve({
                            can_proceed: true
                        });
                    } else {
                        // Not enough credit
                        let exceedsBy = remaining - creditAvailable;
                        let message = `Crédit insuffisant pour cette vente.`;
                        let details = `
                            <table class="table table-sm">
                                <tr>
                                    <td>Limite de crédit:</td>
                                    <td class="text-end"><strong>${clientCreditData.credit_limit.toFixed(2)} DH</strong></td>
                                </tr>
                                <tr>
                                    <td>Crédit déjà utilisé:</td>
                                    <td class="text-end"><strong>${clientCreditData.credit_usage.toFixed(2)} DH</strong></td>
                                </tr>
                                <tr>
                                    <td>Crédit disponible:</td>
                                    <td class="text-end"><strong class="${creditAvailable > 0 ? 'text-success' : 'text-danger'}">${creditAvailable.toFixed(2)} DH</strong></td>
                                </tr>
                                <tr>
                                    <td>Nouveau crédit nécessaire:</td>
                                    <td class="text-end"><strong>${remaining.toFixed(2)} DH</strong></td>
                                </tr>
                                <tr class="table-danger">
                                    <td>Manque:</td>
                                    <td class="text-end"><strong class="text-danger">${exceedsBy.toFixed(2)} DH</strong></td>
                                </tr>
                            </table>
                        `;

                        showCreditExceededModal(message, details, exceedsBy);
                        reject({
                            can_proceed: false
                        });
                    }
                });
            }

            function showCreditExceededModal(message, details, exceedsBy) {
                $('#creditExceededMessage').text(message);
                $('#creditExceededDetails').html(details);
                $('#creditExceededModal').modal('show');
            }

            // Form submission
            $('#orderForm').submit(function(e) {
                e.preventDefault();

                // Validate items
                if ($('#items-body tr').length === 0) {
                    showToast('error', 'Veuillez ajouter au moins un article');
                    return;
                }

                // Validate client
                let clientId = $('#client_id').val();
                if (!clientId) {
                    showToast('error', 'Veuillez sélectionner un client');
                    return;
                }

                // Validate each item has required fields
                let valid = true;
                // $('#items-body tr').each(function() {
                //     if (!$(this).find('.item-id').val()) {
                //         showToast('error', 'Veuillez sélectionner un article pour chaque ligne');
                //         valid = false;
                //         return false;
                //     }
                //     if (!$(this).find('.item-quantity').val() || parseFloat($(this).find(
                //             '.item-quantity').val()) <= 0) {
                //         showToast('error', 'Veuillez saisir une quantité valide');
                //         valid = false;
                //         return false;
                //     }
                // });

                if (!valid) return;

                // Validate payments - only if payment rows exist
                let paymentValid = true;
                let totalPaid = 0;

                if ($('#payments-body tr').length > 0) {
                    $('#payments-body tr').each(function() {
                        let method = $(this).find('.payment-method').val();
                        let amount = parseFloat($(this).find('.payment-amount').val());

                        // Check if method is selected
                        if (!method) {
                            showToast('error', 'Veuillez sélectionner une méthode de paiement');
                            paymentValid = false;
                            return false;
                        }

                        // Check if amount is valid
                        if (!amount || amount <= 0) {
                            showToast('error',
                                'Veuillez saisir un montant valide pour le paiement');
                            paymentValid = false;
                            return false;
                        }

                        totalPaid += amount;

                        // Validate advance amount
                        if (method === 'advance') {
                            if (!validateAdvanceAmount($(this).attr('id'))) {
                                paymentValid = false;
                                return false;
                            }
                        }

                        // Check fields validation based on method
                        if (method === 'check') {
                            let checkNumber = $(this).find('input[name*="check_number"]').val();
                            let bankName = $(this).find('input[name*="bank_name"]').val();
                            let accountHolder = $(this).find('input[name*="account_holder"]').val();
                            let dueDate = $(this).find('input[name*="due_date"]').val();

                            if (!checkNumber || !bankName || !accountHolder || !dueDate) {
                                showToast('error', 'Veuillez remplir tous les champs du chèque');
                                paymentValid = false;
                                return false;
                            }
                        } else if (method === 'transfer') {
                            let transferRef = $(this).find('input[name*="transfer_reference"]')
                                .val();
                            let bankName = $(this).find('input[name*="bank_name"]').val();
                            let accountNumber = $(this).find('input[name*="account_number"]').val();

                            if (!transferRef || !bankName || !accountNumber) {
                                showToast('error', 'Veuillez remplir tous les champs du virement');
                                paymentValid = false;
                                return false;
                            }
                        } else if (method === 'traite') {
                            let traiteNumber = $(this).find('input[name*="traite_number"]').val();
                            let drawee = $(this).find('input[name*="drawee"]').val();
                            let dueDate = $(this).find('input[name*="due_date"]').val();

                            if (!traiteNumber || !drawee || !dueDate) {
                                showToast('error', 'Veuillez remplir tous les champs de la traite');
                                paymentValid = false;
                                return false;
                            }
                        }
                    });
                }

                if (!paymentValid) return;

                // Get order total
                let orderTotal = parseFloat($('#order-total').text().replace(' DH', '').replace(/,/g,
                    '')) || 0;

                // Check credit
                checkClientCredit(clientId, orderTotal, totalPaid).then(() => {
                    submitForm(false);
                }).catch(() => {
                    // Modal already shown
                });
            });

            function submitForm(bypass = false) {
                // Show loading
                const submitBtn = $('#orderForm').find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                // Prepare form data
                const formData = new FormData($('#orderForm')[0]);

                // Add bypass credit flag if needed
                if (bypass) {
                    formData.append('bypass_credit', '1');
                }

                // Handle file uploads
                $('.payment-file').each(function() {
                    let file = $(this)[0].files[0];
                    let name = $(this).attr('name');
                    if (file) {
                        formData.append(name, file);
                    }
                });

                $.ajax({
                    url: "{{ route('sales.orders.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href = "{{ route('sales.orders.index') }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMessage = xhr.responseJSON?.message || 'Une erreur est survenue';

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
            }

            // Global functions for modal
            window.proceedWithExcess = function() {
                $('#creditExceededModal').modal('hide');
                bypassCredit = true;
                submitForm(true);
            };

            window.requestPaymentFirst = function() {
                $('#creditExceededModal').modal('hide');
                addPaymentRow();
                let excessAmount = parseFloat($('#creditExceededModal .table-danger td:last strong').text()
                    .replace(' DH', '')) || 0;
                if (excessAmount > 0) {
                    $('.payment-amount:last').val(excessAmount.toFixed(2));
                }
                showToast('info', 'Veuillez saisir un paiement pour couvrir le dépassement');
            };

            window.reduceOrderAmount = function() {
                $('#creditExceededModal').modal('hide');
                showToast('warning', 'Veuillez modifier les articles de la vente');
            };
        });

        // Toast function
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
    </script>
@endpush
