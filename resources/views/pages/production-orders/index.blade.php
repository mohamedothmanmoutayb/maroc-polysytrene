@extends('layouts.app')

@section('title', 'Ordres de Production')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Ordres de Production</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Ordres de Production
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="statistics row mb-4">
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">En Attente</span>
                                <h3 class="mb-0" id="pendingCount">0</h3>
                                <small class="text-muted">Ordres à traiter</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fs-1 text-warning"></i>
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
                                <span class="text-muted">En Cours</span>
                                <h3 class="mb-0" id="inProgressCount">0</h3>
                                <small class="text-muted">En production</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-cogs fs-1 text-primary"></i>
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
                                <span class="text-muted">Terminés</span>
                                <h3 class="mb-0" id="completedCount">0</h3>
                                <small class="text-muted">Ce mois</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fs-1 text-success"></i>
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
                                <span class="text-muted">Avec Chutes</span>
                                <h3 class="mb-0" id="withWasteCount">0</h3>
                                <small class="text-muted">Déclaration en attente</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-trash-restore fs-1 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Numéro d'Ordre</label>
                            <input type="text" class="form-control" id="filterOrderNumber" placeholder="PO-202401-0001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Produit</label>
                            <select class="form-control select2" id="filterProductId">
                                <option value="">Tous les produits</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->product_id }}">
                                        {{ $product->product_code }} - {{ $product->product_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type Production</label>
                            <select class="form-control" id="filterProductionType">
                                <option value="">Tous</option>
                                <option value="type1">Production</option>
                                <option value="type2">Découpage</option>
                                <option value="type3">Conversion</option>
                                <option value="type4">Transformation</option>
                                <option value="type5">Chutes → Produits Finis</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Statut</label>
                            <select class="form-control" id="filterStatus">
                                <option value="">Tous</option>
                                <option value="pending">En attente</option>
                                <option value="approved">Approuvé</option>
                                <option value="in_progress">En cours</option>
                                <option value="completed">Terminé</option>
                                <option value="cancelled">Annulé</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label class="form-label">Priorité</label>
                            <select class="form-control" id="filterPriority">
                                <option value="">Toutes</option>
                                <option value="low">Basse</option>
                                <option value="medium">Moyenne</option>
                                <option value="high">Haute</option>
                                <option value="urgent">Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Avec chutes</label>
                            <select class="form-control" id="filterHasWaste">
                                <option value="">Tous</option>
                                <option value="has_waste">Avec chutes</option>
                                <option value="needs_waste">Déclaration chutes requise</option>
                                <option value="no_waste">Sans chutes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Début</label>
                            <input type="text" class="form-control date-range-picker" id="filterDateRange"
                                placeholder="Sélectionner une période">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary w-50" id="resetFilters">
                                    <i class="fas fa-redo me-1"></i> Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary w-50">
                                    <i class="fas fa-filter me-1"></i> Filtrer
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-clipboard-list me-2"></i>Liste des Ordres de Production
                        </h5>
                        @can('create_production_orders')
                        <div class="btn-group">
                            <a href="{{ route('production-orders.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Nouvel Ordre
                            </a>
                        </div>
                        @endcan
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="production-orders-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Numéro</th>
                                        <th>Produit</th>
                                        <th>Quantité</th>
                                        <th>Reste</th>
                                        <th>Statut</th>
                                        <th>Priorité</th>
                                        <th>Progression</th>
                                        <th>Chutes</th>
                                        <th>Date Début</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Approuver l'ordre de production
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Voulez-vous approuver l'ordre de production suivant ?</p>
                    <div class="alert alert-info">
                        <strong id="approveOrderNumberDisplay"></strong>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Une fois approuvé, l'ordre sera prêt pour la production.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="button" class="btn btn-info" id="confirmApprove">
                        <i class="fas fa-check-circle me-2"></i>Approuver
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-ban me-2"></i>Annuler l'ordre de production
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Veuillez confirmer l'annulation de l'ordre :</p>
                    <div class="alert alert-warning d-flex align-items-center gap-2">
                        <i class="fas fa-hashtag"></i>
                        <strong id="cancelOrderNumberDisplay"></strong>
                    </div>

                    <div class="alert alert-secondary d-flex align-items-center gap-2 d-none" id="cancelProductionDateAlert">
                        <i class="fas fa-calendar-check"></i>
                        <span>Ordre déjà terminé — Date de production : <strong id="cancelProductionDateDisplay"></strong></span>
                    </div>

                    <!-- Stock restore preview -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body py-2">
                            <h6 class="card-title mb-2">
                                <i class="fas fa-boxes me-1 text-success"></i>
                                Stock qui sera restauré
                            </h6>

                            <!-- Loading -->
                            <div id="cancelStockLoading" class="text-center py-2">
                                <span class="spinner-border spinner-border-sm me-2 text-secondary"></span>
                                <small class="text-muted">Chargement des consommations...</small>
                            </div>

                            <!-- No consumption recorded -->
                            <div id="cancelStockNone" class="alert alert-info mb-0 d-none" style="font-size:.875rem">
                                <i class="fas fa-info-circle me-1"></i>
                                Aucune consommation de matière première enregistrée pour cet ordre — aucun stock à restaurer.
                            </div>

                            <!-- Table of items to restore -->
                            <div id="cancelStockTable" class="d-none">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:36px" class="text-center">
                                                <input type="checkbox" id="cancelSelectAll" checked title="Tout sélectionner">
                                            </th>
                                            <th>Type</th>
                                            <th>Code</th>
                                            <th>Article</th>
                                            <th class="text-end" style="min-width:120px">Quantité</th>
                                            <th>Unité</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cancelStockTableBody"></tbody>
                                </table>
                                <small class="text-muted mt-1 d-block">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Décochez les articles à ignorer ou modifiez la quantité à ajuster.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="cancellationReason" class="form-label">
                            <i class="fas fa-comment-dots me-2"></i>Raison de l'annulation *
                        </label>
                        <select class="form-control" id="cancellationReason" required>
                            <option value="">Sélectionner une raison</option>
                            <option value="stock_insufficient">Stock insuffisant</option>
                            <option value="customer_cancelled">Commande client annulée</option>
                            <option value="technical_issue">Problème technique</option>
                            <option value="schedule_conflict">Conflit d'horaire</option>
                            <option value="quality_concerns">Problèmes de qualité</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="additionalNotes" class="form-label">
                            <i class="fas fa-sticky-note me-2"></i>Notes supplémentaires
                        </label>
                        <textarea class="form-control" id="additionalNotes" rows="2"
                            placeholder="Ajouter des détails sur l'annulation..."></textarea>
                    </div>

                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention :</strong> Cette action ne peut être annulée.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Fermer
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmCancel">
                        <i class="fas fa-ban me-2"></i>Confirmer l'annulation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Start Modal -->
    <div class="modal fade" id="startModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-play me-2"></i>Démarrer la production
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Confirmer le démarrage de la production pour l'ordre :</p>
                    <div class="alert alert-primary">
                        <strong id="startOrderNumberDisplay"></strong>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Cette action consommera les matériaux/réserves nécessaires et ne peut être annulée.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmStart">
                        <i class="fas fa-play me-2"></i>Démarrer la production
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Attention!</strong> Cette action est irréversible.
                    </div>
                    <p>Êtes-vous sûr de vouloir supprimer l'ordre de production :</p>
                    <div class="alert alert-light">
                        <strong id="deleteOrderNumberDisplay"></strong>
                    </div>
                    <p class="text-danger mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Toutes les données associées seront définitivement supprimées.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Order Modal -->
    <div class="modal fade" id="completeOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Terminer la Production
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="completeOrderForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Veuillez renseigner les quantités réellement consommées pour chaque matière première et les
                            résultats de production.
                        </div>

                        <!-- Order Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Informations de l'Ordre</h6>
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <td width="40%">Numéro d'Ordre:</td>
                                        <td><strong id="completeOrderNumber"></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Produit:</td>
                                        <td><strong id="completeProductName"></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Quantité Planifiée:</td>
                                        <td><strong id="completePlannedQuantity"></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Déjà Produit:</td>
                                        <td><strong id="completeAlreadyProduced"></strong></td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td>Restant à Produire:</td>
                                        <td><strong id="completeRemaining"></strong></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Résultats de Production</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="quantity_produced" class="form-label">Quantité Produite *</label>
                                        <input type="number" class="form-control" id="quantity_produced"
                                            name="quantity_produced" required min="0.01" step="0.01">
                                        <small class="form-text text-muted" id="maxQuantityHelp"></small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="quantity_defective" class="form-label">Quantité Défectueuse *</label>
                                        <input type="number" class="form-control" id="quantity_defective"
                                            name="quantity_defective" required min="0" step="0.01" value="0">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="production_date" class="form-label">Date de Production *</label>
                                        <input type="date" class="form-control" id="production_date"
                                            name="production_date" required value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="quality_grade" class="form-label">Qualité</label>
                                        <select class="form-control" id="quality_grade" name="quality_grade">
                                            <option value="excellent">Excellent</option>
                                            <option value="good" selected>Bon</option>
                                            <option value="average">Moyen</option>
                                            <option value="poor">Mauvais</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BOM Consumption Table -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-list-alt me-2"></i>Consommation des Matières Premières
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Important:</strong> Les quantités consommées seront déduites du stock des
                                    matières premières.
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Matière Première</th>
                                                <th>Code</th>
                                                <th class="text-center">Stock Disponible</th>
                                                <th class="text-center">Quantité Planifiée</th>
                                                <th class="text-center">Quantité Réellement Utilisée</th>
                                                <th class="text-center">Déchets/Pertes</th>
                                                <th class="text-center">Unité</th>
                                                <th class="text-center">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bomConsumptionTable">
                                            <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="form-group">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note me-2"></i>Notes
                            </label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                placeholder="Observations, problèmes rencontrés..."></textarea>
                        </div>

                        <!-- Validation Alert -->
                        <div class="alert alert-danger d-none" id="completeValidationAlert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div id="completeValidationMessage"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-success" id="completeSubmitBtn">
                            <i class="fas fa-check me-1"></i> Terminer la Production
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Waste Declaration Modal --}}
    <div class="modal fade" id="wasteDeclarationModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-trash-restore me-2"></i>Déclaration de Chutes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="wasteDeclarationForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Production terminée!</strong> Veuillez déclarer les chutes pour finaliser cet ordre de
                            production.
                        </div>

                        <!-- Order Info -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">Informations de l'Ordre</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div><strong>Ordre:</strong> <span id="wasteOrderNumber">-</span></div>
                                        <div><strong>Produit:</strong> <span id="wasteProductName">-</span></div>
                                        <div><strong>Type Production:</strong> <span id="wasteProductionType">-</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div><strong>Quantité Cible:</strong> <span id="wasteTargetQuantity">0</span></div>
                                        <div><strong>Déjà Produit:</strong> <span id="wasteTotalProduced">0</span></div>
                                        <div><strong>Volume Total Produit:</strong> <span id="wasteTotalVolume">0 m³</span>
                                        </div>
                                        <div><strong>Volume Source:</strong> <span id="wasteSourceVolume" class="text-primary fw-semibold">0.0000 m³</span></div>
                                        {{-- <div><strong>Volume Final:</strong> <span id="wasteFinalVolume">0.0000 m³</span></div> --}}
                                        <div><strong>Taux de Défaut:</strong> <span id="wasteDefectRate">0%</span></div>
                                        <div><strong>Chute/Déchet Estimé:</strong>
                                            <span id="wasteEstimatedChute" class="text-warning fw-semibold">0.0000 m³</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Existing Wastes -->
                        <div class="card mb-4" id="existingWastesContainer" style="display: none;">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>Chutes Déjà Déclarées
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="existingWastesList">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>

                        <!-- Waste Declaration Form -->
                        <input type="hidden" id="wasteOrderId" name="order_id" value="">

                        <!-- Waste List -->
                        <div id="wasteListContainer">
                            <!-- Will be populated by JavaScript -->
                        </div>

                        <!-- Add Waste Button -->
                        <div class="text-center mb-4">
                            <button type="button" class="btn btn-outline-primary" id="addWasteItemBtn">
                                <i class="fas fa-plus me-1"></i>Ajouter une Chute
                            </button>
                        </div>

                        <!-- Waste Statistics -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">Récapitulatif des Chutes</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="alert alert-success">
                                            <i class="fas fa-recycle me-2"></i>
                                            <div>
                                                <small class="d-block text-muted">Volume Recyclable:</small>
                                                <h5 class="mb-0" id="totalRecyclableVolume">0.0000 m³</h5>
                                                <small id="recyclableCount">0 chute(s)</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-danger">
                                            <i class="fas fa-trash me-2"></i>
                                            <div>
                                                <small class="d-block text-muted">Volume Déchet:</small>
                                                <h5 class="mb-0" id="totalWasteVolume">0.0000 m³</h5>
                                                <small id="wasteCount">0 déchet(s)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-warning">
                                    <i class="fas fa-calculator me-2"></i>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="d-block text-muted">Volume Total des Chutes:</small>
                                            <h5 class="mb-0" id="totalAllWasteVolume">0.0000 m³</h5>
                                        </div>
                                        <div class="text-end">
                                            <small class="d-block text-muted">% du volume produit</small>
                                            <h5 class="mb-0" id="totalWastePercentage">0.0%</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning" id="submitWasteDeclarationBtn">
                            <i class="fas fa-save me-1"></i>Enregistrer Chutes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- View Waste Modal --}}
    <div class="modal fade" id="viewWasteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>Voir les Chutes Déclarées
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Chutes déjà déclarées</strong> pour cet ordre de production.
                    </div>

                    <!-- Order Info -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">Informations de l'Ordre</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div><strong>Ordre:</strong> <span id="viewWasteOrderNumber">-</span></div>
                                    <div><strong>Produit:</strong> <span id="viewWasteProductName">-</span></div>
                                </div>
                                <div class="col-md-6">
                                    <div><strong>Volume Total Produit:</strong> <span id="viewWasteTotalVolume">0 m³</span>
                                    </div>
                                    <div><strong>Volume Source:</strong> <span id="viewWasteSourceVolume" class="text-primary fw-semibold">0.0000 m³</span></div>
                                    <div><strong>Volume Final:</strong> <span id="viewWasteFinalVolume">0.0000 m³</span></div>
                                    <div><strong>Chute Estimé:</strong> <span id="viewWasteEstimatedChute" class="text-warning fw-semibold">0.0000 m³</span></div>
                                    <div><strong>Statut:</strong> <span id="viewWasteStatus">-</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Wastes List -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">Liste des Chutes</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Dimensions (m)</th>
                                            <th>Volume (m³)</th>
                                            <th>Source</th>
                                            <th>Catégorie</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody id="viewWastesList">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totals -->
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="alert alert-success p-2">
                                        <small class="d-block text-muted">Recyclable:</small>
                                        <div class="d-flex justify-content-between">
                                            <span id="viewRecyclableCount">0 chutes</span>
                                            <strong id="viewRecyclableVolume">0.0000 m³</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-danger p-2">
                                        <small class="d-block text-muted">Déchet:</small>
                                        <div class="d-flex justify-content-between">
                                            <span id="viewWasteCount">0 chutes</span>
                                            <strong id="viewWasteVolume">0.0000 m³</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-warning p-2">
                                        <small class="d-block text-muted">Total:</small>
                                        <div class="d-flex justify-content-between">
                                            <span id="viewTotalCount">0 chutes</span>
                                            <strong id="viewTotalVolume">0.0000 m³</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    @if (auth()->user()->can('edit_production_orders'))
                        <button type="button" class="btn btn-primary" id="editWastesBtn">
                            <i class="fas fa-edit me-1"></i>Modifier les Chutes
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <style>
        .progress {
            height: 20px;
        }

        .badge.bg-success {
            background-color: #198754 !important;
        }

        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #000 !important;
        }

        .badge.bg-primary {
            background-color: #0d6efd !important;
        }

        .badge.bg-danger {
            background-color: #dc3545 !important;
        }

        .badge.bg-info {
            background-color: #0dcaf0 !important;
        }

        .badge.bg-secondary {
            background-color: #6c757d !important;
        }

        .card-header-custom {
            background-color: #4e73df;
        }

        #production-orders-table th,
        #production-orders-table td {
            vertical-align: middle;
        }

        .waste-info-cell {
            cursor: help;
        }

        .table-info {
            --bs-table-bg: #cff4fc;
            --bs-table-striped-bg: #c5e9f2;
            --bs-table-striped-color: #000;
            --bs-table-active-bg: #badce3;
            --bs-table-active-color: #000;
            --bs-table-hover-bg: #bfe2e9;
            --bs-table-hover-color: #000;
            color: #000;
            border-color: #badce3;
        }

        .progress {
            border-radius: 10px;
        }

        .progress-bar {
            border-radius: 10px;
            transition: width 0.6s ease;
        }

        .badge {
            font-size: 0.8em;
            padding: 0.4em 0.8em;
        }

        .statistics .fas {
            font-size: 38px !important;
        }

        .waste-item {
            border-left: 4px solid #6c757d;
        }

        .waste-item.recyclable {
            border-left-color: #198754;
        }

        .waste-item.waste {
            border-left-color: #dc3545;
        }

        .dimensions-input-group .input-group-text {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {

            #production-orders-table td:nth-child(9),
            #production-orders-table th:nth-child(9) {
                display: none;
            }

            .dimensions-input-group .input-group {
                margin-bottom: 0.5rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(document).ready(function() {
            let wasteItems = [];
            let wasteCounter = 0;
            let currentOrderId = null;
            let existingWastes = [];

            // Initialize Select2
            $('.select2').select2({
                language: "fr",
                placeholder: "Sélectionner...",
                allowClear: true
            });

            // Initialize Date Range Picker
            $('#filterDateRange').daterangepicker({
                locale: {
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    applyLabel: 'Appliquer',
                    cancelLabel: 'Annuler',
                    customRangeLabel: 'Personnalisé',
                    daysOfWeek: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
                    monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
                    ],
                    firstDay: 1
                },
                autoApply: false,
                autoUpdateInput: false,
                showDropdowns: true,
                opens: 'right',
                ranges: {
                    'Aujourd\'hui': [moment(), moment()],
                    'Hier': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Cette semaine': [moment().startOf('week'), moment().endOf('week')],
                    'La semaine dernière': [moment().subtract(1, 'week').startOf('week'), moment().subtract(
                        1, 'week').endOf('week')],
                    'Ce mois-ci': [moment().startOf('month'), moment().endOf('month')],
                    'Le mois dernier': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    '30 derniers jours': [moment().subtract(29, 'days'), moment()],
                    '90 derniers jours': [moment().subtract(89, 'days'), moment()],
                    'Cette année': [moment().startOf('year'), moment().endOf('year')],
                    'L\'année dernière': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1,
                        'year').endOf('year')]
                }
            }, function(start, end, label) {
                $('#filterDateRange').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
            });

            var table = $('#production-orders-table').DataTable({ paging: false, lengthChange: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('production-orders.index') }}",
                    data: function(d) {
                        d.order_number = $('#filterOrderNumber').val();
                        d.product_id = $('#filterProductId').val();
                        d.status = $('#filterStatus').val();
                        d.priority = $('#filterPriority').val();
                        d.production_type = $('#filterProductionType').val();
                        d.has_waste = $('#filterHasWaste').val();
                        d.date_range = $('#filterDateRange').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '5%'
                    },
                    {
                        data: 'order_number',
                        name: 'order_number',
                        className: 'text-center'
                    },
                    {
                        data: 'product_name',
                        name: 'product.product_name',
                        className: 'text-center'
                    },
                    {
                        data: 'quantity_to_produce',
                        name: 'quantity_to_produce',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return data + ' unités';
                        }
                    },
                    {
                        data: 'remaining_quantity',
                        name: 'remaining_quantity',
                        className: 'text-center',
                        render: function(data, type, row) {
                            const remainingText = data || '0 unités';
                            if (parseFloat(remainingText) === 0 && row.status !== 'completed') {
                                return '<span class="badge bg-info">Déclarer chutes</span>';
                            } else if (parseFloat(remainingText) === 0) {
                                return '<span class="badge bg-success">Terminé</span>';
                            }
                            return '<span class="badge bg-warning">' + remainingText + '</span>';
                        }
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        className: 'text-center',
                        width: '10%'
                    },
                    {
                        data: 'priority_badge',
                        name: 'priority',
                        className: 'text-center',
                        width: '8%'
                    },
                    {
                        data: 'progress',
                        name: 'progress',
                        className: 'text-center',
                        width: '12%',
                        render: function(data, type, row) {
                            const progress = parseFloat(data) || 0;
                            const progressColor = progress >= 100 ? 'bg-success' :
                                progress >= 75 ? 'bg-info' :
                                progress >= 50 ? 'bg-primary' :
                                progress >= 25 ? 'bg-warning' : 'bg-danger';

                            return `
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1" style="height: 20px;">
                                    <div class="progress-bar ${progressColor}"
                                        role="progressbar"
                                        style="width: ${Math.min(progress, 100)}%"
                                        aria-valuenow="${data}"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                                <div class="ms-2 text-nowrap" style="font-size: 0.85rem; width: 50px;">
                                    ${data}%
                                </div>
                            </div>
                        `;
                        }
                    },
                    {
                        data: 'waste_info',
                        name: 'waste_info',
                        className: 'text-center',
                        width: '15%',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                return data || '<span class="text-muted">Aucune chute</span>';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'start_date',
                        name: 'start_date',
                        className: 'text-center',
                        width: '10%'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '15%'
                    }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                },
                order: [
                    [1, 'desc']
                ],
                responsive: true,
                dom: '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "Tous"]
                ],
                createdRow: function(row, data, dataIndex) {
                    // Highlight based on priority
                    if (data.priority === 'urgent') {
                        $(row).addClass('table-danger');
                    } else if (data.priority === 'high') {
                        $(row).addClass('table-warning');
                    }

                    // Highlight completed orders
                    if (data.status === 'completed') {
                        $(row).addClass('table-success');
                    }

                    // Highlight orders that need waste declaration
                    const remainingMatch = data.remaining_quantity ? data.remaining_quantity.match(
                        /([\d.]+)/) : null;
                    const remaining = remainingMatch ? parseFloat(remainingMatch[1]) : 0;
                    if (remaining === 0 && data.status !== 'completed' && !data.has_waste_declaration) {
                        $(row).addClass('table-info');
                    }

                    // Highlight orders with waste declaration
                    if (data.has_waste_declaration) {
                        $(row).addClass('table-warning');
                    }
                },
                drawCallback: function() {
                    // Update tooltips
                    $('[data-bs-toggle="tooltip"]').tooltip();

                    // Add tooltips to waste info cells
                    $('.waste-info-cell').each(function() {
                        const content = $(this).html();
                        if (content && content !==
                            '<span class="text-muted">Aucune chute</span>') {
                            $(this).attr('data-bs-toggle', 'tooltip')
                                .attr('data-bs-html', 'true')
                                .attr('title', content)
                                .attr('data-bs-placement', 'top');
                        }
                    });

                    // Initialize tooltips
                    new bootstrap.Tooltip(document.body, {
                        selector: '[data-bs-toggle="tooltip"]'
                    });
                }
            });

            // Apply filters
            $('#filterForm').submit(function(e) {
                e.preventDefault();
                table.draw();
            });

            // Reset filters
            $('#resetFilters').click(function() {
                $('#filterOrderNumber').val('');
                $('#filterProductId').val('').trigger('change');
                $('#filterProductionType').val('');
                $('#filterStatus').val('');
                $('#filterPriority').val('');
                $('#filterHasWaste').val('');
                $('#filterDateRange').val('');
                table.draw();
            });

            // Load statistics
            loadStatistics();

            // ====================================
            // ACTION BUTTON HANDLERS
            // ====================================

            // Approve button handler
            $(document).on('click', '.btn-approve', function() {
                var orderId = $(this).data('id');
                var orderNumber = $(this).data('order-number');

                $('#approveOrderNumberDisplay').text(orderNumber);
                $('#approveModal').data('order-id', orderId);
                $('#approveModal').modal('show');
            });

            // Start button handler
            $(document).on('click', '.btn-start', function() {
                var orderId = $(this).data('id');
                var orderNumber = $(this).data('order-number');

                $('#startOrderNumberDisplay').text(orderNumber);
                $('#startModal').data('order-id', orderId);
                $('#startModal').modal('show');
            });

            // Cancel button handler
            $(document).on('click', '.btn-cancel-production', function() {
                var orderId = $(this).data('id');
                var orderNumber = $(this).data('order-number');
                var status = $(this).data('status');
                var productionDate = $(this).data('production-date');

                $('#cancelOrderNumberDisplay').text(orderNumber);
                $('#cancelModal').data('order-id', orderId);

                if (status === 'completed' && productionDate) {
                    $('#cancelProductionDateDisplay').text(productionDate);
                    $('#cancelProductionDateAlert').removeClass('d-none');
                } else {
                    $('#cancelProductionDateAlert').addClass('d-none');
                }

                // Reset preview state
                $('#cancelStockLoading').removeClass('d-none');
                $('#cancelStockNone').addClass('d-none');
                $('#cancelStockTable').addClass('d-none');
                $('#cancelStockTableBody').empty();
                $('#cancellationReason').val('');
                $('#additionalNotes').val('');

                $('#cancelModal').modal('show');

                // Fetch stock that will be restored
                $.ajax({
                    url: "{{ url('production-orders') }}/" + orderId + "/cancellation-preview",
                    type: "GET",
                    success: function(response) {
                        $('#cancelStockLoading').addClass('d-none');
                        if (response.success && response.items.length > 0) {
                            var rows = '';
                            response.items.forEach(function(item) {
                                var qty = parseFloat(item.qty);
                                var maxQty = item.max !== undefined ? parseFloat(item.max) : qty;
                                var isRestore = item.direction === 'restore';

                                var typeBadge;
                                if (item.type === 'raw_material') {
                                    typeBadge = '<span class="badge bg-warning text-dark">MP</span>';
                                } else if (item.type === 'produced_product') {
                                    typeBadge = '<span class="badge bg-danger">' + item.label + '</span>';
                                } else if (item.label === 'Source Planifiée') {
                                    typeBadge = '<span class="badge bg-warning text-dark" title="Planifié — consommation non enregistrée">' + item.label + '</span>';
                                } else {
                                    typeBadge = '<span class="badge bg-info text-dark">' + item.label + '</span>';
                                }

                                var dirIcon = isRestore
                                    ? '<span class="text-success fw-bold me-1">+</span>'
                                    : '<span class="text-danger fw-bold me-1">−</span>';

                                var inputClass = isRestore ? 'border-success' : 'border-danger';

                                var realQtyNote = (item.type === 'raw_material' && maxQty > qty)
                                    ? '<br><small class="text-muted">Qté réelle utilisée (sur ' + maxQty.toFixed(4) + ' au total)</small>'
                                    : (item.label === 'Source Planifiée'
                                        ? '<br><small class="text-warning">Planifié — aucun mouvement de stock enregistré</small>'
                                        : '');

                                var qtyCell = '<td class="text-end">' +
                                    dirIcon +
                                    '<input type="number" class="cancel-qty-input form-control form-control-sm d-inline-block ' + inputClass + '" ' +
                                    'data-key="' + item.key + '" ' +
                                    'data-max="' + maxQty + '" ' +
                                    'data-direction="' + item.direction + '" ' +
                                    'value="' + qty.toFixed(4) + '" ' +
                                    'min="0" max="' + maxQty + '" step="0.0001" style="width:110px">' +
                                    realQtyNote +
                                    '</td>';

                                rows += '<tr>' +
                                    '<td class="text-center align-middle">' +
                                    '<input type="checkbox" class="cancel-item-check" data-key="' + item.key + '" checked>' +
                                    '</td>' +
                                    '<td>' + typeBadge + '</td>' +
                                    '<td><span class="badge bg-secondary">' + item.code + '</span></td>' +
                                    '<td>' + item.name + '</td>' +
                                    qtyCell +
                                    '<td><small class="text-muted">' + item.unit + '</small></td>' +
                                    '</tr>';
                            });
                            $('#cancelStockTableBody').html(rows);
                            $('#cancelStockTable').removeClass('d-none');
                        } else {
                            $('#cancelStockNone').removeClass('d-none');
                        }
                    },
                    error: function() {
                        $('#cancelStockLoading').addClass('d-none');
                        $('#cancelStockNone').removeClass('d-none')
                            .html('<i class="fas fa-exclamation-triangle me-1"></i> Impossible de charger les données de stock.');
                    }
                });
            });

            // Delete button handler
            $(document).on('click', '.btn-delete', function() {
                var orderId = $(this).data('id');
                var orderNumber = $(this).data('order-number');

                $('#deleteOrderNumberDisplay').text(orderNumber);
                $('#deleteForm').attr('action', "{{ url('production-orders') }}/" + orderId);
                $('#deleteModal').modal('show');
            });

            // Complete button handler
            $(document).on('click', '.btn-complete', function() {
                var orderId = $(this).data('id');

                $('#completeOrderModal').data('order-id', orderId);

                $.ajax({
                    url: "{{ url('api/production-orders') }}/" + orderId,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            const order = response.order;

                            $('#completeOrderNumber').text(order.order_number);
                            $('#completeProductName').text(order.product.product_name);
                            $('#completePlannedQuantity').text(order.quantity_to_produce +
                                ' unités');
                            $('#completeAlreadyProduced').text(order.statistics.total_produced +
                                ' unités');
                            const remaining = order.quantity_to_produce - order.statistics
                                .total_produced;
                            $('#completeRemaining').text(remaining + ' unités');

                            $('#quantity_produced').attr('max', remaining);
                            $('#maxQuantityHelp').text(`Maximum: ${remaining} unités`);
                            $('#quantity_produced').val(remaining > 0 ? remaining : 1);

                            loadBOMConsumptionTable(order);

                            $('#completeOrderModal').modal('show');
                        } else {
                            showToast('error',
                                'Erreur lors du chargement des détails de l\'ordre');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading order details:', xhr);
                        showToast('error', 'Erreur lors du chargement des détails de l\'ordre');
                    }
                });
            });

            // ====================================
            // WASTE DECLARATION HANDLERS
            // ====================================

            // Declare waste button handler
            $(document).on('click', '.btn-declare-waste', function() {
                const orderId = $(this).data('order-id');
                const orderNumber = $(this).data('order-number');
                const productName = $(this).data('product-name');
                const productionType = $(this).data('production-type');
                const targetQuantity = $(this).data('target-quantity');
                const totalProduced = $(this).data('total-produced');
                const totalVolume = parseFloat($(this).data('total-volume')) || 0;
                const defectRate = $(this).data('defect-rate');
                const sourceVolume = parseFloat($(this).data('source-volume')) || 0;
                const finalVolume = parseFloat($(this).data('final-volume')) || 0;
                const estimatedChute = parseFloat($(this).data('estimated-chute')) || 0;

                currentOrderId = orderId;

                // Populate order info
                $('#wasteOrderId').val(orderId);
                $('#wasteOrderNumber').text(orderNumber);
                $('#wasteProductName').text(productName);
                $('#wasteProductionType').text(
                    productionType === 'type1' ? 'Production Directe' :
                    productionType === 'type2' ? 'Découpage' :
                    productionType === 'type3' ? 'Conversion' :
                    productionType === 'type4' ? 'Transformation' :
                    productionType === 'type5' ? 'Chutes → Produits Finis' : 'Conversion'
                );
                $('#wasteTargetQuantity').text(targetQuantity + ' unités');
                $('#wasteTotalProduced').text(totalProduced + ' unités');
                $('#wasteTotalVolume').text(totalVolume.toFixed(4) + ' m³');
                $('#wasteSourceVolume').text(sourceVolume.toFixed(4) + ' m³');
                $('#wasteFinalVolume').text(finalVolume.toFixed(4) + ' m³');
                $('#wasteDefectRate').text(defectRate + '%');
                $('#wasteEstimatedChute').text(estimatedChute.toFixed(4) + ' m³');

                // Load existing wastes
                loadExistingWastes(orderId);

                // Reset waste items
                wasteCounter = 0;
                wasteItems = [];
                $('#wasteListContainer').empty();

                // Add one default waste item, pre-filled with the estimated chute volume
                // so the user can just confirm when the estimation is correct
                if (estimatedChute > 0) {
                    addWasteItem({
                        waste_type: 'recyclable',
                        height: 1,
                        width: 1,
                        depth: estimatedChute.toFixed(3),
                        volume_m3: estimatedChute.toFixed(4),
                        waste_source: 'Chute résiduelle (estimation automatique)'
                    });
                } else {
                    addWasteItem();
                }

                // Show modal
                $('#wasteDeclarationModal').modal('show');
            });

            // View waste button handler
            $(document).on('click', '.btn-view-waste', function() {
                const orderId = $(this).data('order-id');
                const orderNumber = $(this).data('order-number');
                const productName = $(this).data('product-name');
                const totalVolume = parseFloat($(this).data('total-volume')) || 0;
                const status = $(this).data('status');
                const sourceVolume = parseFloat($(this).data('source-volume')) || 0;
                const finalVolume = parseFloat($(this).data('final-volume')) || 0;
                const estimatedChute = parseFloat($(this).data('estimated-chute')) || 0;

                currentOrderId = orderId;

                // Populate order info
                $('#viewWasteOrderNumber').text(orderNumber);
                $('#viewWasteProductName').text(productName);
                $('#viewWasteTotalVolume').text(totalVolume.toFixed(4) + ' m³');
                $('#viewWasteSourceVolume').text(sourceVolume.toFixed(4) + ' m³');
                $('#viewWasteFinalVolume').text(finalVolume.toFixed(4) + ' m³');
                $('#viewWasteEstimatedChute').text(estimatedChute.toFixed(4) + ' m³');
                $('#viewWasteStatus').text(status === 'completed' ? 'Terminé' : 'En cours');

                // Load existing wastes for viewing
                $.ajax({
                    url: "{{ url('api/production-orders') }}/" + orderId + "/wastes",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            populateViewWastes(response.wastes);
                        } else {
                            showToast('error', 'Erreur lors du chargement des chutes');
                        }
                    },
                    error: function() {
                        showToast('error', 'Erreur lors du chargement des chutes');
                    }
                });

                $('#viewWasteModal').modal('show');
            });

            // Edit wastes button (from view modal)
            $('#editWastesBtn').click(function() {
                $('#viewWasteModal').modal('hide');

                // Load the same data into declaration modal
                setTimeout(() => {
                    $('.btn-declare-waste[data-order-id="' + currentOrderId + '"]').click();
                }, 500);
            });

            // ====================================
            // MODAL ACTION CONFIRMATIONS
            // ====================================

            // Confirm Approve
            $('#confirmApprove').click(function() {
                var orderId = $('#approveModal').data('order-id');
                var modal = $('#approveModal');

                $.ajax({
                    url: "{{ url('production-orders') }}/" + orderId + "/approve",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    beforeSend: function() {
                        $('#confirmApprove').prop('disabled', true)
                            .html(
                                '<span class="spinner-border spinner-border-sm me-2"></span> Approbation...'
                            );
                    },
                    success: function(response) {
                        if (response.success) {
                            modal.modal('hide');
                            table.draw();
                            loadStatistics();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors de l\'approbation');
                    },
                    complete: function() {
                        $('#confirmApprove').prop('disabled', false)
                            .html('<i class="fas fa-check-circle me-2"></i>Approuver');
                    }
                });
            });

            // Confirm Start
            $('#confirmStart').click(function() {
                var orderId = $('#startModal').data('order-id');
                var modal = $('#startModal');

                $.ajax({
                    url: "{{ url('production-orders') }}/" + orderId + "/start",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    beforeSend: function() {
                        $('#confirmStart').prop('disabled', true)
                            .html(
                                '<span class="spinner-border spinner-border-sm me-2"></span> Démarrage...'
                            );
                    },
                    success: function(response) {
                        if (response.success) {
                            modal.modal('hide');
                            table.draw();
                            loadStatistics();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors du démarrage');
                    },
                    complete: function() {
                        $('#confirmStart').prop('disabled', false)
                            .html('<i class="fas fa-play me-2"></i>Démarrer la production');
                    }
                });
            });

            // Select-all checkbox for cancel stock table
            $(document).on('change', '#cancelSelectAll', function() {
                var checked = $(this).prop('checked');
                $('.cancel-item-check').prop('checked', checked);
                $('.cancel-qty-input').prop('disabled', !checked);
            });

            $(document).on('change', '.cancel-item-check', function() {
                var key = $(this).data('key');
                var checked = $(this).prop('checked');
                var input = $('.cancel-qty-input[data-key="' + key + '"]');
                input.prop('disabled', !checked);
                // Sync master checkbox
                var total = $('.cancel-item-check').length;
                var checkedCount = $('.cancel-item-check:checked').length;
                $('#cancelSelectAll').prop('indeterminate', checkedCount > 0 && checkedCount < total);
                $('#cancelSelectAll').prop('checked', checkedCount === total);
            });

            // Confirm Cancel
            $('#confirmCancel').click(function() {
                var orderId = $('#cancelModal').data('order-id');
                var reason = $('#cancellationReason').val();
                var notes = $('#additionalNotes').val();

                if (!reason) {
                    showToast('error', 'Veuillez sélectionner une raison d\'annulation');
                    return;
                }

                // Collect selected stock items with user-specified quantities
                var stockItems = [];
                $('.cancel-item-check:checked').each(function() {
                    var key = $(this).data('key');
                    var qty = parseFloat($('.cancel-qty-input[data-key="' + key + '"]').val()) || 0;
                    var maxQty = parseFloat($('.cancel-qty-input[data-key="' + key + '"]').data('max')) || 0;
                    if (qty > 0) {
                        stockItems.push({ key: key, qty: Math.min(qty, maxQty) });
                    }
                });

                $.ajax({
                    url: "{{ url('production-orders') }}/" + orderId + "/cancel",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        reason: reason,
                        additional_notes: notes,
                        stock_items: JSON.stringify(stockItems)
                    },
                    beforeSend: function() {
                        $('#confirmCancel').prop('disabled', true)
                            .html('<span class="spinner-border spinner-border-sm me-2"></span> Annulation...');
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#cancelModal').modal('hide');
                            table.draw();
                            loadStatistics();
                            showToast('success', response.message);

                            // Show a detail toast per material with direction indicator
                            if (response.restored && response.restored.length > 0) {
                                response.restored.forEach(function(item) {
                                    var qty = parseFloat(item.qty).toFixed(4);
                                    if (item.direction === 'remove') {
                                        showToast('warning',
                                            '↺ ' + item.name + ': − ' + qty + ' ' + item.unit + ' retiré du stock');
                                    } else {
                                        showToast('success',
                                            '↩ ' + item.name + ': + ' + qty + ' ' + item.unit + ' restauré');
                                    }
                                });
                            }
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message || 'Erreur lors de l\'annulation');
                    },
                    complete: function() {
                        $('#confirmCancel').prop('disabled', false)
                            .html('<i class="fas fa-ban me-2"></i>Confirmer l\'annulation');
                    }
                });
            });

            // Delete form submission
            $('#deleteForm').submit(function(e) {
                e.preventDefault();

                var form = $(this);
                var modal = $('#deleteModal');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    beforeSend: function() {
                        form.find('button[type="submit"]').prop('disabled', true)
                            .html(
                                '<span class="spinner-border spinner-border-sm me-2"></span> Suppression...'
                            );
                    },
                    success: function(response) {
                        if (response.success) {
                            modal.modal('hide');
                            table.draw();
                            loadStatistics();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors de la suppression');
                    }
                });
            });

            // ====================================
            // WASTE DECLARATION FUNCTIONS
            // ====================================

            // Waste item template
            function getWasteItemTemplate(id, wasteData = {}) {
                const wasteType = wasteData.waste_type || 'recyclable';
                const height = wasteData.height || 0.0000;
                const width = wasteData.width || 0.0000;
                const depth = wasteData.depth || 0.0000;
                const volume = wasteData.volume_m3 || '';
                const wasteSource = wasteData.waste_source || 'Découpage';
                const wasteCategory = wasteData.waste_category || '';
                const notes = wasteData.notes || '';
                const wasteId = wasteData.id || '';

                return `
        <div class="card mb-3 waste-item ${wasteType}" id="wasteItem_${id}">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Chute #${id + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-waste-item" data-waste-id="${id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                ${wasteId ? '<input type="hidden" class="waste-id" name="wastes[${id}][id]" value="' + wasteId + '">' : ''}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Type de Chute *</label>
                        <select class="form-control waste-type" name="wastes[${id}][waste_type]" required>
                            <option value="recyclable" ${wasteType === 'recyclable' ? 'selected' : ''}>Recyclable</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Source *</label>
                        <input type="text" class="form-control waste-source" name="wastes[${id}][waste_source]"
                               value="${wasteSource}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Hauteur (m) *</label>
                        <input type="number" class="form-control waste-height" name="wastes[${id}][height]"
                               step="0.001" min="0.0000" value="${height}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Largeur (m) *</label>
                        <input type="number" class="form-control waste-width" name="wastes[${id}][width]"
                               step="0.001" min="0.0000" value="${width}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Profondeur (m) *</label>
                        <input type="number" class="form-control waste-depth" name="wastes[${id}][depth]"
                               step="0.001" min="0.0000" value="${depth}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Volume Calculé (m³)</label>
                        <input type="text" class="form-control waste-volume" readonly
                               value="${volume || '0.0000'}">
                    </div>
                    <div class="col-md-6 mb-3 waste-category-field" style="${wasteType === 'recyclable' ? 'display: none;' : ''}">
                        <label class="form-label">Catégorie de Déchet *</label>
                        <input type="text" class="form-control waste-category" name="wastes[${id}][waste_category]"
                               value="${wasteCategory}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control waste-notes" name="wastes[${id}][notes]" rows="2">${notes}</textarea>
                    </div>
                </div>
                <div class="alert ${wasteType === 'recyclable' ? 'alert-success' : 'alert-danger'} p-2 mb-0">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        <span class="waste-type-info">
                            ${wasteType === 'recyclable' ? 'Recyclable: Stockée comme matière première' : 'Déchet: Pour déclaration de production'}
                        </span>
                    </small>
                </div>
            </div>
        </div>
    `;
            }

            function addWasteItem(wasteData = {}) {
                const wasteListContainer = $('#wasteListContainer');
                const wasteItemHtml = getWasteItemTemplate(wasteCounter, wasteData);
                wasteListContainer.append(wasteItemHtml);

                const itemElement = $(`#wasteItem_${wasteCounter}`);
                calculateWasteVolume(itemElement);

                wasteCounter++;
                updateWasteStatistics();
            }

            function removeWasteItem(id) {
                $(`#wasteItem_${id}`).remove();
                updateWasteStatistics();
            }

            function calculateWasteVolume(wasteItemElement) {
                const height = parseFloat(wasteItemElement.find('.waste-height').val()) || 0;
                const width = parseFloat(wasteItemElement.find('.waste-width').val()) || 0;
                const depth = parseFloat(wasteItemElement.find('.waste-depth').val()) || 0;

                if (height > 0 && width > 0 && depth > 0) {
                    const volume = height * width * depth;
                    wasteItemElement.find('.waste-volume').val(volume.toFixed(4));
                } else {
                    wasteItemElement.find('.waste-volume').val('0.0000');
                }

                updateWasteStatistics();
            }

            function updateWasteStatistics() {
                let totalRecyclableVolume = 0;
                let totalWasteVolume = 0;
                let recyclableCount = 0;
                let wasteCount = 0;

                $('.waste-item').each(function() {
                    const wasteType = $(this).find('.waste-type').val();
                    const volume = parseFloat($(this).find('.waste-volume').val()) || 0;

                    if (wasteType === 'recyclable') {
                        totalRecyclableVolume += volume;
                        recyclableCount++;
                    } else {
                        totalWasteVolume += volume;
                        wasteCount++;
                    }
                });

                const totalAllVolume = totalRecyclableVolume + totalWasteVolume;
                const totalVolume = parseFloat($('#wasteTotalVolume').text()) || 0;
                const wastePercentage = totalVolume > 0 ? (totalAllVolume / totalVolume) * 100 : 0;

                $('#totalRecyclableVolume').text(totalRecyclableVolume.toFixed(4) + ' m³');
                $('#totalWasteVolume').text(totalWasteVolume.toFixed(4) + ' m³');
                $('#totalAllWasteVolume').text(totalAllVolume.toFixed(4) + ' m³');
                $('#recyclableCount').text(recyclableCount + ' chute(s)');
                $('#wasteCount').text(wasteCount + ' déchet(s)');
                $('#totalWastePercentage').text(wastePercentage.toFixed(1) + '%');
            }

            // Load existing wastes
            function loadExistingWastes(orderId) {
                $.ajax({
                    url: "{{ url('api/production-orders') }}/" + orderId + "/wastes",
                    type: "GET",
                    success: function(response) {
                        if (response.success && response.wastes.length > 0) {
                            existingWastes = response.wastes;

                            // Show existing wastes section
                            $('#existingWastesContainer').show();

                            // Populate existing wastes list
                            let existingWastesHtml = '';
                            existingWastes.forEach((waste, index) => {
                                existingWastesHtml += `
                                    <div class="alert ${waste.waste_type === 'recyclable' ? 'alert-success' : 'alert-danger'} mb-2 p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>#${index + 1}</strong> -
                                                ${waste.waste_type === 'recyclable' ? 'Recyclable' : 'Déchet'} |
                                                ${waste.height}m × ${waste.width}m × ${waste.depth}m =
                                                ${parseFloat(waste.volume_m3).toFixed(4)} m³
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary load-existing-waste"
                                                    data-waste-index="${index}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                        ${waste.notes ? `<small class="d-block mt-1">${waste.notes}</small>` : ''}
                                    </div>
                                `;
                            });

                            $('#existingWastesList').html(existingWastesHtml);
                        } else {
                            $('#existingWastesContainer').hide();
                        }
                    },
                    error: function() {
                        $('#existingWastesContainer').hide();
                    }
                });
            }

            // Populate view wastes
            function populateViewWastes(wastes) {
                let wastesHtml = '';
                let recyclableCount = 0;
                let wasteCount = 0;
                let totalRecyclableVolume = 0;
                let totalWasteVolume = 0;

                if (wastes.length === 0) {
                    wastesHtml =
                        '<tr><td colspan="6" class="text-center text-muted">Aucune chute déclarée</td></tr>';
                } else {
                    wastes.forEach((waste, index) => {
                        if (waste.waste_type === 'recyclable') {
                            recyclableCount++;
                            totalRecyclableVolume += parseFloat(waste.volume_m3);
                        } else {
                            wasteCount++;
                            totalWasteVolume += parseFloat(waste.volume_m3);
                        }

                        wastesHtml += `
                            <tr>
                                <td>
                                    <span class="badge ${waste.waste_type === 'recyclable' ? 'bg-success' : 'bg-danger'}">
                                        ${waste.waste_type === 'recyclable' ? 'Recyclable' : 'Déchet'}
                                    </span>
                                </td>
                                <td>${waste.height} × ${waste.width} × ${waste.depth}</td>
                                <td>${parseFloat(waste.volume_m3).toFixed(4)}</td>
                                <td>${waste.waste_source}</td>
                                <td>${waste.waste_category || '-'}</td>
                                <td>${waste.notes || '-'}</td>
                            </tr>
                        `;
                    });
                }

                $('#viewWastesList').html(wastesHtml);
                $('#viewRecyclableCount').text(recyclableCount + ' chute(s)');
                $('#viewWasteCount').text(wasteCount + ' chute(s)');
                $('#viewTotalCount').text((recyclableCount + wasteCount) + ' chute(s)');
                $('#viewRecyclableVolume').text(totalRecyclableVolume.toFixed(4) + ' m³');
                $('#viewWasteVolume').text(totalWasteVolume.toFixed(4) + ' m³');
                $('#viewTotalVolume').text((totalRecyclableVolume + totalWasteVolume).toFixed(4) + ' m³');
            }

            // Handle waste type change
            function handleWasteTypeChange() {
                const wasteItem = $(this).closest('.waste-item');
                const wasteType = $(this).val();
                const categoryField = wasteItem.find('.waste-category-field');

                wasteItem.removeClass('recyclable waste').addClass(wasteType);

                updateWasteStatistics();
            }

            // Handle dimension input
            function handleDimensionInput() {
                const wasteItem = $(this).closest('.waste-item');
                calculateWasteVolume(wasteItem);
            }

            // Waste declaration form submission
            $('#wasteDeclarationForm').submit(function(e) {
                e.preventDefault();

                const orderId = $('#wasteOrderId').val();
                const formData = new FormData(this);

                // Validate at least one waste item
                const wasteCount = $('.waste-item').length;
                if (wasteCount === 0) {
                    showToast('error', 'Veuillez ajouter au moins une chute');
                    return;
                }

                // Validate all dimensions
                let hasValidDimensions = true;
                $('.waste-item').each(function() {
                    const height = parseFloat($(this).find('.waste-height').val()) || 0;
                    const width = parseFloat($(this).find('.waste-width').val()) || 0;
                    const depth = parseFloat($(this).find('.waste-depth').val()) || 0;

                    if (height <= 0 || width <= 0 || depth <= 0) {
                        hasValidDimensions = false;
                        return false;
                    }
                });

                const submitBtn = $('#submitWasteDeclarationBtn');
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...'
                );

                // Calculate and add volume for each waste item
                $('.waste-item').each(function(index) {
                    const volume = parseFloat($(this).find('.waste-volume').val()) || 0;
                    formData.set(`wastes[${index}][volume_m3]`, volume.toFixed(4));
                });

                $.ajax({
                    url: "{{ url('production-orders') }}/" + orderId + "/waste-declaration",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#wasteDeclarationModal').modal('hide');
                            showToast('success', response.message);

                            // Reset form
                            wasteCounter = 0;
                            existingWastes = [];
                            $('#wasteListContainer').empty();
                            $('#existingWastesContainer').hide();

                            // Reload the table
                            table.draw();
                            loadStatistics();
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        let errorMessage = '';

                        if (errors) {
                            Object.values(errors).forEach(function(errorArray) {
                                errorArray.forEach(function(error) {
                                    errorMessage += error + '<br>';
                                });
                            });
                        } else {
                            errorMessage = xhr.responseJSON?.message ||
                                'Une erreur est survenue lors de l\'enregistrement';
                        }

                        showToast('error', errorMessage);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i> Enregistrer Chutes'
                        );
                    }
                });
            });

            // Event listeners for waste declaration
            $(document).on('click', '#addWasteItemBtn', function() {
                addWasteItem();
            });

            $(document).on('click', '.remove-waste-item', function() {
                const wasteId = $(this).data('waste-id');
                removeWasteItem(wasteId);
            });

            $(document).on('click', '.load-existing-waste', function() {
                const wasteIndex = $(this).data('waste-index');
                if (existingWastes[wasteIndex]) {
                    addWasteItem(existingWastes[wasteIndex]);
                }
            });

            $(document).on('change', '.waste-type', handleWasteTypeChange);
            $(document).on('input', '.waste-height, .waste-width, .waste-depth', handleDimensionInput);

            // ====================================
            // UTILITY FUNCTIONS
            // ====================================

            // Load statistics
            function loadStatistics() {
                $.ajax({
                    url: "{{ route('production-orders.dashboard-statistics') }}",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            $('#pendingCount').text(data.by_status?.pending || 0);
                            $('#inProgressCount').text(data.by_status?.in_progress || 0);
                            $('#completedCount').text(data.this_month?.completed || 0);
                            $('#withWasteCount').text(data.needs_waste_declaration || 0);
                        }
                    },
                    error: function() {
                        console.error('Failed to load statistics');
                    }
                });
            }

            // Toast notification function
            function showToast(type, message) {
                const bgMap = { success: 'success', error: 'danger', warning: 'warning', info: 'info' };
                const bg = bgMap[type] || 'danger';
                const textClass = (bg === 'warning' || bg === 'info') ? 'text-dark' : 'text-white';
                const toast = $(`
                    <div class="toast align-items-center ${textClass} bg-${bg} border-0"
                         role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">${message}</div>
                            <button type="button" class="btn-close ${textClass === 'text-dark' ? '' : 'btn-close-white'} me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `);

                $('#toast-container').append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();

                setTimeout(() => toast.remove(), 5000);
            }

            // Reset waste modal when closed
            $('#wasteDeclarationModal').on('hidden.bs.modal', function() {
                wasteCounter = 0;
                wasteItems = [];
                existingWastes = [];
                $('#wasteListContainer').empty();
                $('#existingWastesContainer').hide();
            });

            // Reset cancel modal when closed
            $('#cancelModal').on('hidden.bs.modal', function() {
                $('#cancellationReason').val('');
                $('#additionalNotes').val('');
                $('#cancelSelectAll').prop('checked', true).prop('indeterminate', false);
                $('#confirmCancel').prop('disabled', false)
                    .html('<i class="fas fa-ban me-2"></i>Confirmer l\'annulation');
            });

            // Initialize Bootstrap tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush
