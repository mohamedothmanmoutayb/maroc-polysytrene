@extends('layouts.app')

@section('title', 'Modifier Ordre de Production')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Ordre de Production</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('production-orders.index') }}">
                                        Ordres de Production
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
                            <i class="fas fa-edit me-2"></i>Modifier l'Ordre de Production #{{ $order->order_number }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="productionOrderForm">
                            @csrf

                            <!-- Order Information -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Ordre:</strong> {{ $order->order_number }}
                                        | <strong>Statut:</strong>
                                        <span
                                            class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'approved' ? 'info' : 'secondary') }}">
                                            {{ $order->status === 'pending' ? 'En attente' : ($order->status === 'approved' ? 'Approuvé' : $order->status) }}
                                        </span>
                                        | Le type de production ne peut pas être modifié.
                                    </div>
                                </div>
                            </div>

                            <!-- Production Type Selection -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3">
                                        <i class="fas fa-cogs me-2"></i>Type de Production
                                    </h6>
                                    <div class="row">
                                        <!-- Type 1: Production Directe -->
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check card h-100">
                                                <input class="form-check-input" type="radio" name="production_type"
                                                    id="type1Production" value="type1"
                                                    {{ $order->production_type === 'type1' ? 'checked' : 'disabled' }}>
                                                <label class="form-check-label card-body" for="type1Production">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <i class="fas fa-industry fa-2x text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Production Directe</h6>
                                                            <p class="text-muted mb-0 small">
                                                                Matières premières → Bloc production
                                                            </p>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Type 2: Production -> Découpage (Multiple Products) -->
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check card h-100">
                                                <input class="form-check-input" type="radio" name="production_type"
                                                    id="type2Production" value="type2"
                                                    {{ $order->production_type === 'type2' ? 'checked' : 'disabled' }}>
                                                <label class="form-check-label card-body" for="type2Production">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <i class="fas fa-cut fa-2x text-warning"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Découpage</h6>
                                                            <p class="text-muted mb-0 small">
                                                                Bloc production → Sous-blocs
                                                            </p>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Type 3: Découpage -> Vente -->
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check card h-100">
                                                <input class="form-check-input" type="radio" name="production_type"
                                                    id="type3Production" value="type3"
                                                    {{ $order->production_type === 'type3' ? 'checked' : 'disabled' }}>
                                                <label class="form-check-label card-body" for="type3Production">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <i class="fas fa-exchange-alt fa-2x text-success"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Produits Finaux</h6>
                                                            <p class="text-muted mb-0 small">
                                                                Sous-blocs → Produits finaux
                                                            </p>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <div class="form-check card h-100">
                                                <input class="form-check-input" type="radio" name="production_type"
                                                    id="type4Production" value="type4"
                                                    {{ $order->production_type === 'type4' ? 'checked' : 'disabled' }}>
                                                <label class="form-check-label card-body" for="type4Production">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <i class="fas fa-exchange-alt fa-2x text-info"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Transformation Vente → Vente</h6>
                                                            <p class="text-muted mb-0 small">
                                                                Produits vente → Nouveaux produits vente
                                                            </p>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Type 5: Chutes -> Produits Finis -->
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check card h-100">
                                                <input class="form-check-input" type="radio" name="production_type"
                                                    id="type5Production" value="type5"
                                                    {{ $order->production_type === 'type5' ? 'checked' : 'disabled' }}>
                                                <label class="form-check-label card-body" for="type5Production">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <i class="fas fa-recycle fa-2x text-success"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Chutes → Produits Finis</h6>
                                                            <p class="text-muted mb-0 small">
                                                                Chutes de production → Produits finaux
                                                            </p>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Selection Section -->
                            <div class="row mb-4">
                                <!-- Type 1: Production Directe -->
                                <div class="col-md-12 {{ $order->production_type !== 'type1' ? 'd-none' : '' }}" id="type1Section">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="d-flex align-items-center mb-2 gap-2">
                                                    <label for="type1_product_id" class="form-label">Produit à Produire
                                                        *</label>
                                                    <button type="button" class="btn btn-success" style="padding:3px;"
                                                        id="addProductFromTopBtn">
                                                        <i class="fas fa-box-plus me-1"></i> Ajouter un Produit
                                                    </button>
                                                </div>

                                                <select class="form-control select2" id="type1_product_id"
                                                    name="type1_product_id">
                                                    <option value="">Sélectionner un produit de production</option>
                                                    @if ($order->production_type === 'type1' && $order->product && !$productionProducts->contains('product_id', $order->product_id))
                                                        <option value="{{ $order->product_id }}"
                                                            data-has-familles="{{ $order->product->has_familles }}" selected>
                                                            {{ $order->product->product_code }} -
                                                            {{ $order->product->product_name }}
                                                        </option>
                                                    @endif
                                                    @foreach ($productionProducts as $product)
                                                        @if ($product->product_type === 'production' || $product->product_type === 'both')
                                                            <option value="{{ $product->product_id }}"
                                                                data-has-familles="{{ $product->has_familles }}"
                                                                {{ $order->production_type === 'type1' && $order->product_id == $product->product_id ? 'selected' : '' }}>
                                                                {{ $product->product_code }} - {{ $product->product_name }}
                                                                @if ($product->product_type === 'production')
                                                                    <span class="badge bg-primary">Production</span>
                                                                @elseif($product->product_type === 'both')
                                                                    <span class="badge bg-info">Production & Vente</span>
                                                                @endif
                                                                @if ($product->has_familles)
                                                                    <span class="badge bg-warning">Avec Familles</span>
                                                                @endif
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">
                                                    Produit de type production à fabriquer
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="type1_quantity" class="form-label">Quantité à Produire
                                                    *</label>
                                                <input type="number" class="form-control" id="type1_quantity"
                                                    name="type1_quantity" min="0.01" step="0.01"
                                                    placeholder="Ex: 100"
                                                    value="{{ $order->production_type === 'type1' ? $order->quantity_to_produce : '' }}">
                                                <small class="form-text text-muted">
                                                    Quantité de produit de production à fabriquer
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Volume/Unité</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type1_volume_per_unit"
                                                        step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                                <small class="form-text text-muted" id="type1_volume_info"></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6 offset-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Volume Total Produit</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type1_total_volume"
                                                        step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Type 2: Production -> Découpage (Multiple Products) -->
                                <div class="col-md-12 {{ $order->production_type !== 'type2' ? 'd-none' : '' }}" id="type2Section">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="type2_source_product_id" class="form-label">Produit Source
                                                    (Bloc) *</label>
                                                <select class="form-control select2" id="type2_source_product_id"
                                                    name="type2_source_product_id">
                                                    <option value="">Sélectionner un produit source</option>
                                                    @if ($order->production_type === 'type2' && $order->sourceProduct && !$productionProducts->contains('product_id', $order->source_product_id))
                                                        <option value="{{ $order->source_product_id }}"
                                                            data-has-familles="{{ $order->sourceProduct->has_familles }}" selected>
                                                            {{ $order->sourceProduct->product_code }} -
                                                            {{ $order->sourceProduct->product_name }}
                                                        </option>
                                                    @endif
                                                    @foreach ($productionProducts as $product)
                                                        @if ($product->product_type === 'production' || $product->product_type === 'both')
                                                            <option value="{{ $product->product_id }}"
                                                                data-has-familles="{{ $product->has_familles }}"
                                                                {{ $order->production_type === 'type2' && $order->source_product_id == $product->product_id ? 'selected' : '' }}>
                                                                {{ $product->product_code }} - {{ $product->product_name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">
                                                    Bloc de production à découper
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="type2_total_blocks" class="form-label">Total Blocs Requis
                                                    *</label>
                                                <input type="number" class="form-control" id="type2_total_blocks"
                                                    name="type2_total_blocks" min="0.01" step="0.01"
                                                    value="{{ $order->production_type === 'type2' ? $order->required_quantity : 1 }}">
                                                <small class="form-text text-muted">Nombre de blocs à découper</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Volume/Bloc</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type2_source_volume"
                                                        step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                                <small class="form-text text-muted" id="type2_source_volume_info"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Volume Total Source</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control"
                                                        id="type2_total_source_volume" step="0.0001" min="0"
                                                        readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                                <small class="form-text text-muted">Volume total des blocs à
                                                    découper</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label">Produits Découpage à Produire *</label>
                                                <button type="button" class="btn btn-sm btn-primary mb-2"
                                                    id="addType2Product">
                                                    <i class="fas fa-plus me-1"></i> Ajouter un Produit
                                                </button>
                                                <div id="type2ProductsContainer">
                                                    <div class="alert alert-info" id="noType2ProductsMessage">
                                                        Cliquez sur "Ajouter un Produit" pour ajouter des produits découpage
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Volume Total Produits Découpage</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type2_total_volume"
                                                        step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Chute/Déchet Estimé (Volume)</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type2_waste_volume"
                                                        step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                                <small class="form-text text-muted" id="type2_waste_info"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Type 3: Découpage -> Vente -->
                                <div class="col-md-12 {{ $order->production_type !== 'type3' ? 'd-none' : '' }}" id="type3Section">
                                    <!-- Multiple Sous-blocs Sources -->
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold">Sous-blocs Sources *</label>
                                                <button type="button" class="btn btn-sm btn-success mb-2 ms-2"
                                                    id="addType3SousBloc">
                                                    <i class="fas fa-plus me-1"></i> Ajouter un Sous-bloc
                                                </button>
                                                <div id="type3SousBlocsContainer">
                                                    <div class="alert alert-info" id="noSousBlocsMessage">
                                                        Cliquez sur "Ajouter un Sous-bloc" pour ajouter des sous-blocs sources
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Multiple Produits Finaux -->
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold">Produits Finaux à Produire *</label>
                                                <button type="button" class="btn btn-sm btn-primary mb-2 ms-2"
                                                    id="addType3Product">
                                                    <i class="fas fa-plus me-1"></i> Ajouter un Produit
                                                </button>
                                                <div id="type3ProductsContainer">
                                                    <div class="alert alert-info" id="noProductsMessage">
                                                        Cliquez sur "Ajouter un Produit" pour ajouter des produits finaux
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Volume Summary -->
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Volume Total Sous-blocs</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control"
                                                        id="type3_total_source_volume" step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Volume Total Produits Finaux</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type3_total_volume"
                                                        step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Chute/Déchet Estimé (Volume)</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type3_waste_volume"
                                                        step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                                <small class="form-text text-muted" id="type3_waste_info"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Type 4: Vente -> Vente (Transformation) -->
                                <div class="col-md-12 {{ $order->production_type !== 'type4' ? 'd-none' : '' }}" id="type4Section">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="type4_source_product_id" class="form-label">Produit Source
                                                    (Vente) *</label>
                                                <select class="form-control select2" id="type4_source_product_id"
                                                    name="source_product_id">
                                                    <option value="">Sélectionner un produit source</option>
                                                    @if ($order->production_type === 'type4' && $order->sourceProduct && !$salesProducts->contains('product_id', $order->source_product_id))
                                                        <option value="{{ $order->source_product_id }}"
                                                            data-has-familles="{{ $order->sourceProduct->has_familles }}" selected>
                                                            {{ $order->sourceProduct->product_code }} -
                                                            {{ $order->sourceProduct->product_name }}
                                                        </option>
                                                    @endif
                                                    @foreach ($salesProducts as $product)
                                                        @if ($product->product_type === 'finale' || $product->product_type === 'both')
                                                            <option value="{{ $product->product_id }}"
                                                                data-has-familles="{{ $product->has_familles }}"
                                                                {{ $order->production_type === 'type4' && $order->source_product_id == $product->product_id ? 'selected' : '' }}>
                                                                {{ $product->product_code }} -
                                                                {{ $product->product_name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">Produit vente à transformer</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="type4_total_units" class="form-label">Total Unités Requises
                                                    *</label>
                                                <input type="number" class="form-control" id="type4_total_units"
                                                    name="type4_total_units" min="0.01" step="0.01"
                                                    value="{{ $order->production_type === 'type4' ? $order->required_quantity : 1 }}">
                                                <small class="form-text text-muted">Nombre d'unités source à
                                                    transformer</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Volume/Unité Source</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type4_source_volume"
                                                        step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                                <small class="form-text text-muted" id="type4_source_volume_info"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Volume Total Source</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control"
                                                        id="type4_total_source_volume" step="0.0001" min="0"
                                                        readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Famille Selection (SINGLE - same as Type 2 and Type 3) -->
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="type4_famille_id" class="form-label">Famille *</label>
                                                <select class="form-control select2" id="type4_famille_id"
                                                    name="famille_id">
                                                    <option value="">Sélectionner la famille...</option>
                                                </select>
                                                <small class="form-text text-muted">Famille où seront retirés les produits
                                                    source et ajoutés les nouveaux produits</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Products to Produce -->
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label">Produits à Produire *</label>
                                                <button type="button" class="btn btn-sm btn-primary mb-2"
                                                    id="addType4Product">
                                                    <i class="fas fa-plus me-1"></i> Ajouter un Produit
                                                </button>
                                                <div id="type4ProductsContainer">
                                                    <div class="alert alert-info" id="noType4ProductsMessage">
                                                        Cliquez sur "Ajouter un Produit" pour ajouter des produits
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Volume Calculations -->
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Volume Total Produits</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type4_total_volume"
                                                        step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Chute/Déchet Estimé (Volume)</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type4_waste_volume"
                                                        step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                                <small class="form-text text-muted" id="type4_waste_info"></small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stock Alerts -->
                                    <div id="type4InsufficientStockAlert" class="alert alert-danger d-none mt-3">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Attention: Stock insuffisant!</strong>
                                        <div id="type4InsufficientStockList" class="mt-2"></div>
                                    </div>

                                    <div id="type4VolumeExceedAlert" class="alert alert-danger d-none mt-3">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Erreur: Volume source insuffisant!</strong>
                                        <div id="type4VolumeExceedMessage" class="mt-2"></div>
                                    </div>
                                </div>

                                <!-- Type 5: Chutes -> Produits Finis -->
                                <div class="col-md-12 {{ $order->production_type !== 'type5' ? 'd-none' : '' }}" id="type5Section">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="type5_chutes_volume" class="form-label">Volume de Chutes à
                                                    Utiliser *</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type5_chutes_volume"
                                                        min="0" step="0.0001" placeholder="Ex: 10.0000"
                                                        value="{{ $order->production_type === 'type5' ? $order->chutes_volume : '' }}">
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                                <small class="form-text text-muted">Volume de chutes à consommer pour cet
                                                    ordre</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div id="type5ChutesStockInfo"></div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold">Produits Finaux à Produire *</label>
                                                <button type="button" class="btn btn-sm btn-primary mb-2 ms-2"
                                                    id="addType5Product">
                                                    <i class="fas fa-plus me-1"></i> Ajouter un Produit
                                                </button>
                                                <div id="type5ProductsContainer">
                                                    <div class="alert alert-info" id="noType5ProductsMessage">
                                                        Cliquez sur "Ajouter un Produit" pour ajouter des produits finaux
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Volume Summary -->
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Volume Chutes Alloué</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control"
                                                        id="type5_chutes_volume_display" step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Volume Total Produits</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type5_total_volume"
                                                        step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Chute Résiduelle Estimée</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type5_waste_volume"
                                                        step="0.0001" min="0" readonly>
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                                <small class="form-text text-muted" id="type5_waste_info"></small>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="type5VolumeExceedAlert" class="alert alert-danger d-none mt-3">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Erreur: Volume de chutes insuffisant!</strong>
                                        <div id="type5VolumeExceedMessage" class="mt-2">
                                            Le volume total des produits dépasse le volume de chutes alloué. Veuillez
                                            ajouter plus de chutes ou réduire les quantités.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Famille Selection Section -->
                            <div class="row mb-4" id="familleSection">
                                <div class="col-md-12">
                                    <div id="familleContainer">
                                        <!-- Famille selection will be loaded here via AJAX -->
                                    </div>
                                </div>
                            </div>

                            <!-- BOM Information (for Type 1 only) -->
                            <div class="row mb-4 d-none" id="bomCard">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-list-alt me-2"></i>Nomenclature (BOM) - Matières Premières
                                                Requises
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Calcul:</strong> Pour <span id="bom-quantity-display">1</span>
                                                produit, il faut:
                                            </div>

                                            <div class="mb-3 text-start">
                                                <button type="button" class="btn btn-success"
                                                    id="addRawMaterialToBomBtn">
                                                    <i class="fas fa-plus me-1"></i> Ajouter une matière première
                                                </button>
                                                <button type="button" class="btn btn-danger ms-2" id="clearBomBtn">
                                                    <i class="fas fa-trash me-1"></i> Vider la nomenclature
                                                </button>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th width="25%">Matière Première</th>
                                                            <th width="10%">Code</th>
                                                            <th width="10%" class="text-center">Quantité/Unité</th>
                                                            <th width="10%" class="text-center">Stock Disponible</th>
                                                            <th width="10%" class="text-center">Quantité Requise</th>
                                                            <th width="8%" class="text-center">Unités</th>
                                                            <th width="10%" class="text-center">Coût Unitaire</th>
                                                            <th width="10%" class="text-center">Coût Total</th>
                                                            <th width="7%" class="text-center">Statut</th>
                                                            <th width="10%" class="text-center">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="bomTableBody">
                                                        <tr>
                                                            <td colspan="10" class="text-center text-muted py-4">
                                                                <i class="fas fa-box-open me-2"></i>
                                                                Aucune matière première. Cliquez sur "Ajouter une matière
                                                                première" pour ajouter.
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot id="bomTableFooter"></tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Production Details -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="priority" class="form-label">Priorité *</label>
                                        <select class="form-control" id="priority" name="priority" required>
                                            <option value="low" {{ $order->priority === 'low' ? 'selected' : '' }}>Basse</option>
                                            <option value="medium" {{ ($order->priority ?? 'medium') === 'medium' ? 'selected' : '' }}>Moyenne</option>
                                            <option value="high" {{ $order->priority === 'high' ? 'selected' : '' }}>Haute</option>
                                            <option value="urgent" {{ $order->priority === 'urgent' ? 'selected' : '' }}>Urgente</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="start_date" class="form-label">Date de Début *</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date"
                                            required value="{{ $order->start_date ? $order->start_date->format('Y-m-d') : date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="expected_completion_date" class="form-label">Date de Fin Prévue
                                            *</label>
                                        <input type="date" class="form-control" id="expected_completion_date"
                                            name="expected_completion_date" required
                                            value="{{ $order->expected_completion_date ? $order->expected_completion_date->format('Y-m-d') : '' }}">
                                        <small class="form-text text-muted" id="productionTimeInfo">
                                            <!-- Production time info will be loaded via AJAX -->
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-4 mt-3">
                                    <div class="form-group">
                                        <label for="responsible_employee_id" class="form-label">Responsable</label>
                                        <select class="form-control select2" id="responsible_employee_id"
                                            name="responsible_employee_id" style="width: 100%;">
                                            <option value="">Sélectionner un employé...</option>
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->employee_id }}"
                                                    {{ $order->responsible_employee_id == $employee->employee_id ? 'selected' : '' }}>
                                                    {{ $employee->full_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Stock Information Alerts -->
                            <div id="insufficientStockAlert" class="alert alert-danger d-none mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Attention: Stock insuffisant!</strong>
                                <div id="insufficientStockList" class="mt-2"></div>
                                <p class="mb-0 mt-2"><small>Vous pouvez toujours créer l'ordre, mais le démarrage de la
                                        production nécessitera un stock suffisant.</small></p>
                            </div>

                            <div id="noBomAlert" class="alert alert-warning d-none mb-4">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Aucune nomenclature définie!</strong>
                                <p class="mb-0">Ce produit n'a pas de BOM configuré. Vous devez d'abord définir la
                                    nomenclature dans la fiche produit.</p>
                            </div>

                            <!-- Notes -->
                            <div class="form-group mb-4">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note me-2"></i>Notes
                                </label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"
                                    placeholder="Instructions spéciales, notes de production...">{{ old('notes', $order->notes) }}</textarea>
                            </div>

                            <!-- Hidden fields for final form submission -->
                            <input type="hidden" id="actual_product_id" name="product_id"
                                value="{{ $order->product_id }}">
                            <input type="hidden" id="actual_source_product_id" name="source_product_id"
                                value="{{ $order->source_product_id }}">
                            <input type="hidden" id="actual_quantity_to_produce" name="quantity_to_produce"
                                value="{{ $order->quantity_to_produce }}">
                            <input type="hidden" id="actual_required_quantity" name="required_quantity"
                                value="{{ $order->required_quantity }}">
                            <input type="hidden" id="actual_decoupage_ratio" name="decoupage_ratio"
                                value="{{ $order->decoupage_ratio }}">
                            <input type="hidden" id="actual_conversion_rate" name="conversion_rate"
                                value="{{ $order->conversion_rate }}">
                            <input type="hidden" id="actual_waste_percentage" name="waste_percentage"
                                value="{{ $order->waste_percentage ?? 0 }}">
                            <input type="hidden" id="actual_material_source" name="material_source"
                                value="{{ $order->material_source ?? 'bom_only' }}">
                            <input type="hidden" id="actual_bom_percentage" name="bom_percentage"
                                value="{{ $order->bom_percentage ?? 100 }}">
                            <input type="hidden" id="actual_chutes_volume" name="chutes_volume"
                                value="{{ $order->chutes_volume ?? 0 }}">
                            <input type="hidden" id="actual_total_cost" name="total_cost"
                                value="{{ $order->total_cost ?? 0 }}">
                            <input type="hidden" id="actual_total_source_volume" name="total_source_volume"
                                value="{{ $order->source_volume ?? 0 }}">
                            <input type="hidden" id="actual_total_produced_volume" name="total_produced_volume"
                                value="{{ $order->total_volume_produced ?? 0 }}">

                            <!-- Form Actions -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> Mettre à jour l'Ordre
                                </button>
                                <a href="{{ route('production-orders.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
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
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .form-check .card {
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .form-check .card:hover {
            border-color: #dee2e6;
            transform: translateY(-2px);
        }

        .form-check-input:checked+.card {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
        }

        #type2Production:checked+.card {
            border-color: #ffc107;
            background-color: rgba(255, 193, 7, 0.05);
        }

        #type3Production:checked+.card {
            border-color: #198754;
            background-color: rgba(25, 135, 84, 0.05);
        }

        #type5Production:checked+.card {
            border-color: #198754;
            background-color: rgba(25, 135, 84, 0.05);
        }

        .badge.bg-warning {
            color: #000 !important;
        }

        .type2-product-row,
        .type3-product-row {
            border-left: 3px solid #ffc107;
            margin-bottom: 1rem;
        }

        .type3-product-row {
            border-left-color: #198754;
        }

        .type2-product-row .card-body,
        .type3-product-row .card-body {
            padding: 1rem;
        }

        /* Chutes stock info styling */
        #chutesStockInfo .alert {
            padding: 0.5rem 1rem;
            margin-bottom: 0;
        }

        .chutes-stock-ok {
            background-color: rgba(25, 135, 84, 0.1);
            border-color: #198754;
            color: #0f5132;
        }

        .chutes-stock-low {
            background-color: rgba(255, 193, 7, 0.1);
            border-color: #ffc107;
            color: #664d03;
        }

        .chutes-stock-none {
            background-color: rgba(220, 53, 69, 0.1);
            border-color: #dc3545;
            color: #842029;
        }

        #type2VolumeExceedAlert,
        #type3VolumeExceedAlert,
        #type4VolumeExceedAlert {
            border-left: 4px solid #dc3545;
        }

        #type2VolumeExceedAlert .alert,
        #type3VolumeExceedAlert .alert,
        #type4VolumeExceedAlert .alert {
            background-color: #f8d7da;
        }

        /* Chute info styling */
        .text-warning,
        .text-success,
        .text-danger {
            font-weight: 500;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let rawMaterialsList = [];
            let type4Products = [];
            let quickFamilleRowIndex = 0;
            const CHUTES_DENSITY = 8.2;

            // Edit mode: the order's saved famille is applied to the first famille
            // load, then cleared so later product changes behave like on create.
            let editFamillePreselectId = null;

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
                        $(row).find('.quick-prix-client-standard').text('Std: ' + prixClient +
                            ' DH');
                        $(row).find('.quick-prix-grossiste-standard').text('Std: ' + prixGrossiste + ' DH');
                        $(row).find('.quick-prix-commercial-standard').text('Std: ' + prixCommercial +
                            ' DH');
                        $(row).find('.quick-prix-special-standard').text('Std: ' + prixSpecial +
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


            // Helper function to round to 2 decimal places
            function roundTo2Decimals(value) {
                return Math.round(value * 100) / 100;
            }

            // Helper function to format volume with 2 decimals
            function formatVolume(value) {
                return roundTo2Decimals(value).toFixed(2);
            }

            // Helper function to format kg with 2 decimals
            function formatKg(value) {
                return roundTo2Decimals(value).toFixed(2);
            }

            // Initialize Select2
            $('.select2').select2({
                language: "fr",
                placeholder: "Sélectionner...",
                allowClear: true
            });

            // Set default expected completion date
            const setDefaultCompletionDate = () => {
                const startDate = $('#start_date').val();
                if (startDate && !$('#expected_completion_date').val()) {
                    const expectedDate = new Date(startDate);
                    expectedDate.setDate(expectedDate.getDate() + 7);
                    $('#expected_completion_date').val(expectedDate.toISOString().split('T')[0]);
                }
            };
            setDefaultCompletionDate();

            // Production type change handler
            $('input[name="production_type"]').change(function() {
                const productionType = $(this).val();
                toggleProductionTypeSections(productionType);
            });

            // Material source change handler (for chutes)
            $('input[name="material_source"]').change(function() {
                updateMaterialSourceSections();
            });

            // Chutes volume change handler
            $('#chutes_volume').on('input', function() {
                let volumeM3 = parseFloat($(this).val()) || 0;
                // Round to 2 decimals
                volumeM3 = roundTo2Decimals(volumeM3);
                $(this).val(volumeM3);

                const kg = volumeM3 * CHUTES_DENSITY;
                $('#chutes_kg').val(formatKg(kg));

                if (volumeM3 < 0) {
                    $('#chutes_volume').val(0);
                    $('#chutes_kg').val('0.00');
                    showToast('warning', 'Le volume ne peut pas être négatif');
                    return;
                }

                const materialSource = $('input[name="material_source"]:checked').val();
                if (materialSource === 'both') {
                    $('#actual_chutes_volume').val(volumeM3);
                }
                updateCalculations();
            });

            // Type 5 chutes volume/kg inputs
            $('#type5_chutes_volume').on('input', function() {
                let volumeM3 = parseFloat($(this).val()) || 0;
                if (volumeM3 < 0) {
                    volumeM3 = 0;
                    $(this).val(0);
                    showToast('warning', 'Le volume ne peut pas être négatif');
                }

                $('#actual_chutes_volume').val(volumeM3);
                checkType5ChutesStock();
                updateType5Calculation();
            });

            function checkType5ChutesStock() {
                $.ajax({
                    url: "{{ route('raw-materials.get-by-code') }}",
                    type: "GET",
                    data: {
                        material_code: 'CHUTE-PRODUCTION'
                    },
                    success: function(response) {
                        if (response.success && response.material) {
                            const chutesMaterial = response.material;
                            const availableStockM3 = parseFloat(chutesMaterial.current_stock) || 0;
                            const requestedM3 = parseFloat($('#type5_chutes_volume').val()) || 0;

                            let stockInfoHtml = '';
                            const isSufficient = availableStockM3 >= requestedM3;
                            const stockClass = availableStockM3 <= 0 ?
                                'chutes-stock-none' : (isSufficient ? 'chutes-stock-ok' : 'chutes-stock-low');

                            stockInfoHtml = `
                                <div class="alert ${stockClass}">
                                    <i class="fas ${isSufficient ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>
                                    <strong>Stock disponible:</strong> ${availableStockM3.toFixed(4)} m³<br>
                                    <strong>Demandé:</strong> ${requestedM3.toFixed(4)} m³<br>
                                    ${!isSufficient ? `<small class="text-danger">⚠️ Stock insuffisant de ${Math.abs(availableStockM3 - requestedM3).toFixed(4)} m³</small>` :
                                                      `<small class="text-success">✓ Stock suffisant</small>`}
                                </div>
                            `;

                            $('#type5ChutesStockInfo').html(stockInfoHtml);
                        } else {
                            $('#type5ChutesStockInfo').html(`
                                <div class="alert chutes-stock-none">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <strong>Produit "CHUTE-PRODUCTION" non trouvé</strong>
                                </div>
                            `);
                        }
                    },
                    error: function() {
                        $('#type5ChutesStockInfo').html(`
                            <div class="alert chutes-stock-none">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Erreur de chargement du stock</strong>
                            </div>
                        `);
                    }
                });
            }

            // Get product details including volume
            async function getProductDetails(productId) {
                try {
                    const response = await $.ajax({
                        url: `/api/products/${productId}`,
                        type: "GET"
                    });

                    if (response.success && response.product) {
                        const product = response.product;

                        // Calculate volume from dimensions if volume_m3 is 0
                        let volumePerUnit = parseFloat(product.volume_m3) || 0;
                        if (volumePerUnit === 0 && product.height_mm && product.width_mm && product.depth_mm) {
                            const height = parseFloat(product.height_mm) / 1000;
                            const width = parseFloat(product.width_mm) / 1000;
                            const depth = parseFloat(product.depth_mm) / 1000;
                            volumePerUnit = height * width * depth;
                        }

                        return {
                            product_id: product.product_id,
                            product_name: product.product_name,
                            product_code: product.product_code,
                            product_type: product.product_type,
                            unit_of_measure: product.unit_of_measure,
                            has_familles: product.has_familles || false,
                            volume_per_unit: volumePerUnit,
                            total_volume: volumePerUnit,
                            display_volume: volumePerUnit > 0 ? volumePerUnit.toFixed(4) + ' m³' : 'Non défini',
                            dimensions: product.height_mm && product.width_mm && product.depth_mm ?
                                `${product.height_mm} × ${product.width_mm} × ${product.depth_mm} mm` :
                                'Non défini'
                        };
                    }
                } catch (error) {
                    console.error('Error fetching product details:', error);
                }

                return null;
            }

            // Get stock information for specific famille
            async function getStockInfo(productId, familleId) {
                try {
                    if (familleId) {
                        // Get stock for specific famille
                        const response = await $.ajax({
                            url: `/api/products/${productId}/famille/${familleId}/stock`,
                            type: "GET"
                        });

                        if (response.success) {
                            const stockData = response.data || response;
                            return {
                                available: parseFloat(stockData.available_quantity || 0),
                                famille_name: stockData.famille_name || null,
                                current_quantity: parseFloat(stockData.current_quantity || 0),
                                reserved_quantity: parseFloat(stockData.reserved_quantity || 0),
                                location: stockData.location || null
                            };
                        }
                    }

                    // If no famille selected or famille stock not found, get general product stock
                    const productResponse = await $.ajax({
                        url: `/api/products/${productId}`,
                        type: "GET"
                    });

                    if (productResponse.success && productResponse.product) {
                        const product = productResponse.product;
                        const stockData = product.stock || {};
                        const available = (parseFloat(stockData.current_quantity || 0) -
                            parseFloat(stockData.reserved_quantity || 0)) || 0;

                        return {
                            available: available,
                            famille_name: null,
                            current_quantity: parseFloat(stockData.current_quantity || 0),
                            reserved_quantity: parseFloat(stockData.reserved_quantity || 0),
                            location: stockData.location || null
                        };
                    }
                } catch (error) {
                    console.error('Error fetching stock info:', error);
                }

                return {
                    available: 0,
                    famille_name: null,
                    current_quantity: 0,
                    reserved_quantity: 0,
                    location: null
                };
            }

            // Load familles for product
            function loadFamilles(productId, productionType) {
                let familleType = 'final';
                let selectId = 'famille_id';
                let selectName = 'famille_id';
                let label = 'Sélectionner la famille de destination';

                if (productionType === 'type2' || productionType === 'type3') {
                    familleType = 'source';
                    selectId = 'source_famille_id';
                    selectName = 'source_famille_id';
                    label = 'Sélectionner la famille source';
                }

                $.ajax({
                    url: "{{ route('production-orders.get-familles') }}",
                    type: "GET",
                    data: {
                        product_id: productId,
                        famille_type: familleType
                    },
                    beforeSend: function() {
                        $('#familleContainer').html(`
                <div class="form-group">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    Chargement des familles...
                </div>
            `);
                    },
                    success: function(response) {
                        if (response.success) {
                            if (response.html) {
                                let updatedHtml = response.html.replace(/id="famille_id"/g,
                                    `id="${selectId}"`);
                                updatedHtml = updatedHtml.replace(/name="famille_id"/g,
                                    `name="${selectName}"`);

                                $('#familleContainer').html(updatedHtml);

                                if (editFamillePreselectId) {
                                    $(`#${selectId}`).val(String(editFamillePreselectId));
                                    editFamillePreselectId = null;
                                }

                                if (response.has_familles) {
                                    $(`#${selectId}`).prev('label').text(label);

                                    $(`#${selectId}`).select2({
                                        language: "fr",
                                        placeholder: label,
                                        allowClear: false
                                    });

                                    $(`#${selectId}`).prop('required', true);

                                    // Trigger calculation when famille changes
                                    $(`#${selectId}`).on('change', function() {
                                        updateCalculations();
                                    });

                                    // Trigger initial calculation
                                    updateCalculations();
                                } else {
                                    $(`#${selectId}`).prop('required', false);
                                    updateCalculations();
                                }
                            } else if (response.has_familles === false) {
                                $('#familleContainer').html(
                                    '<div class="alert alert-info">Ce produit n\'a pas de familles configurées.</div>'
                                );
                                updateCalculations();
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading familles:', xhr);
                        $('#familleContainer').html(
                            '<div class="alert alert-info">Ce produit n\'a pas de familles configurées.</div>'
                        );
                        updateCalculations();
                    }
                });
            }

            function getFamilleId() {
                const productionType = $('input[name="production_type"]:checked').val();

                if (productionType === 'type2' || productionType === 'type3') {
                    return $('#source_famille_id').val();
                } else {
                    return $('#famille_id').val();
                }
            }

            // Update all calculations based on current production type
            function updateCalculations() {
                const productionType = $('input[name="production_type"]:checked').val();

                if (productionType === 'type1') {
                    updateType1Calculation();
                } else if (productionType === 'type2') {
                    updateType2Calculation();
                } else if (productionType === 'type3') {
                    updateType3Calculation();
                } else if (productionType === 'type5') {
                    updateType5Calculation();
                }
            }

            // Update volume calculations
            function updateVolumeCalculations(productionType) {
                if (productionType === 'type1') {
                    const productId = $('#type1_product_id').val();
                    const quantity = $('#type1_quantity').val() || 1;

                    if (productId) {
                        getProductDetails(productId).then(product => {
                            if (product) {
                                const totalVolume = product.volume_per_unit * quantity;
                                $('#type1_volume_per_unit').val(product.volume_per_unit.toFixed(4));
                                $('#type1_volume_info').text(
                                    `${product.display_volume} (${product.dimensions})`);
                                $('#type1_total_volume').val(totalVolume.toFixed(4));
                            }
                        });
                    }
                } else if (productionType === 'type2') {
                    const sourceProductId = $('#type2_source_product_id').val();

                    if (sourceProductId) {
                        getProductDetails(sourceProductId).then(sourceProduct => {
                            if (sourceProduct) {
                                $('#type2_source_volume').val(sourceProduct.volume_per_unit.toFixed(4));
                                $('#type2_source_volume_info').text(
                                    `${sourceProduct.display_volume} (${sourceProduct.dimensions})`);
                            }
                        });
                    }
                } else if (productionType === 'type3') {
                    updateType3Calculation();
                }
            }

            // Type 1: Direct production calculation with editable BOM
            function updateType1Calculation() {
                const productId = $('#type1_product_id').val();
                const quantity = $('#type1_quantity').val() || 1;

                if (productId && quantity >= 1) {
                    // loadBOM(productId, quantity);
                    checkChutesStock();
                    updateVolumeCalculations('type1');

                    // Set hidden fields
                    $('#actual_product_id').val(productId);
                    $('#actual_quantity_to_produce').val(quantity);
                    $('#actual_required_quantity').val(quantity);
                }
            }

            // Replace the updateType2Calculation function with this:

            function updateType2Calculation() {
                const sourceProductId = $('#type2_source_product_id').val();
                const familleId = getFamilleId();

                if (!sourceProductId) {
                    $('#insufficientStockAlert').addClass('d-none');
                    $('#volumeExceedAlert').addClass('d-none');
                    return;
                }

                const productInputs = $('.type2-product-row');

                if (productInputs.length === 0) {
                    return;
                }

                const products = [];
                let totalFinalProducts = 0;
                let totalVolume = 0;

                productInputs.each(function() {
                    const productId = $(this).find('.type2-product-select').val();
                    const quantityToProduce = parseFloat($(this).find('.type2-quantity').val()) || 0;
                    const volumePerUnit = parseFloat($(this).find('.type2-volume').val()) || 0;

                    if (productId) {
                        const productVolume = quantityToProduce * volumePerUnit;

                        products.push({
                            product_id: productId,
                            product_name: $(this).find('.type2-product-select option:selected')
                                .text(),
                            quantity_to_produce: quantityToProduce,
                            volume_per_unit: volumePerUnit,
                            total_volume: productVolume
                        });

                        totalFinalProducts += quantityToProduce;
                        totalVolume += productVolume;
                    }
                });

                $('#type2_total_decoupage_products').val(totalFinalProducts);
                $('#type2_total_volume').val(totalVolume.toFixed(4));

                const totalBlocksRequired = parseFloat($('#type2_total_blocks').val()) || 0;

                getProductDetails(sourceProductId).then(sourceProduct => {
                    if (sourceProduct) {
                        const sourceVolumePerUnit = sourceProduct.volume_per_unit;
                        const totalSourceVolume = totalBlocksRequired * sourceVolumePerUnit;
                        const wasteVolume = totalSourceVolume - totalVolume;

                        $('#type2_source_volume').val(sourceVolumePerUnit.toFixed(4));
                        $('#type2_source_volume_info').text(
                            `${sourceProduct.display_volume} (${sourceProduct.dimensions})`);
                        $('#type2_total_source_volume').val(totalSourceVolume.toFixed(4));

                        // Check if target volume exceeds source volume
                        if (totalVolume > totalSourceVolume) {
                            const deficit = totalVolume - totalSourceVolume;
                            $('#type2VolumeExceedMessage').html(`
                    <strong>⚠️ Le volume total des produits découpage (${totalVolume.toFixed(4)} m³)
                    dépasse le volume source disponible (${totalSourceVolume.toFixed(4)} m³).</strong>
                    <br>Déficit: ${deficit.toFixed(4)} m³
                    <br>Veuillez réduire la quantité à produire ou augmenter le nombre de blocs.
                `);
                            $('#type2VolumeExceedAlert').removeClass('d-none');
                            $('#type2_waste_volume').val('');
                            $('#type2_waste_info').html(
                                '<span class="text-danger">❌ Volume source insuffisant!</span>');
                        } else {
                            $('#type2VolumeExceedAlert').addClass('d-none');
                            const wasteVolumePositive = wasteVolume;
                            $('#type2_waste_volume').val(wasteVolumePositive.toFixed(4));

                            if (wasteVolumePositive > 0) {
                                const wastePercentage = (wasteVolumePositive / totalSourceVolume * 100)
                                    .toFixed(2);
                                $('#type2_waste_info').html(
                                    `<span class="text-warning">⚠️ Chute estimée: ${wasteVolumePositive.toFixed(4)} m³ (${wastePercentage}%)</span>`
                                );
                            } else if (wasteVolumePositive === 0) {
                                $('#type2_waste_info').html(
                                    `<span class="text-success">✓ Aucune chute (volume parfaitement optimisé)</span>`
                                );
                            }
                        }

                        $('#actual_waste_percentage').val(wasteVolume > 0 ? (wasteVolume /
                            totalSourceVolume * 100).toFixed(2) : 0);
                        $('#actual_total_source_volume').val(totalSourceVolume);
                        $('#actual_total_produced_volume').val(totalVolume);
                    }
                });

                getStockInfo(sourceProductId, familleId).then(stockInfo => {
                    const isSufficient = stockInfo.available >= totalBlocksRequired;

                    if (!isSufficient && stockInfo.available > 0) {
                        $('#insufficientStockList').html(`
                <ul class="mb-0 mt-2">
                    <li>${stockInfo.famille_name ? 'Famille: ' + stockInfo.famille_name : 'Produit source'}:
                        Requis ${totalBlocksRequired.toFixed(2)},
                        Disponible ${stockInfo.available.toFixed(2)} unités
                    </li>
                </ul>
            `);
                        $('#insufficientStockAlert').removeClass('d-none');
                    } else if (stockInfo.available === 0 && totalBlocksRequired > 0) {
                        $('#insufficientStockList').html(`
                <ul class="mb-0 mt-2">
                    <li>${stockInfo.famille_name ? 'Famille: ' + stockInfo.famille_name : 'Produit source'}:
                        Stock vide (0 unités disponible)
                    </li>
                </ul>
            `);
                        $('#insufficientStockAlert').removeClass('d-none');
                    } else {
                        $('#insufficientStockAlert').addClass('d-none');
                    }
                });

                if (products.length > 0) {
                    $('#actual_product_id').val(products[0].product_id);
                }
                $('#actual_source_product_id').val(sourceProductId);
                $('#actual_quantity_to_produce').val(totalFinalProducts);
                $('#actual_required_quantity').val(totalBlocksRequired);
            }

            // Type 3: Multiple sous-blocs → multiple produits finaux
            function updateType3Calculation() {
                // Sum total source volume from all sous-bloc rows
                let totalSourceVolume = 0;
                let totalSousBlocsCount = 0;
                let firstSourceProductId = null;
                const familleId = getFamilleId();

                $('.type3-sous-bloc-row').each(function() {
                    const qty = parseFloat($(this).find('.type3-sous-bloc-quantity').val()) || 0;
                    const vol = parseFloat($(this).find('.type3-sous-bloc-volume').val()) || 0;
                    totalSourceVolume += qty * vol;
                    totalSousBlocsCount += qty;
                    if (!firstSourceProductId) {
                        firstSourceProductId = $(this).find('.type3-sous-bloc-select').val();
                    }
                });

                $('#type3_total_source_volume').val(totalSourceVolume.toFixed(4));

                // Sum total final products volume
                let totalFinalProducts = 0;
                let totalVolume = 0;

                $('.type3-product-row').each(function() {
                    const productId = $(this).find('.type3-product-select').val();
                    const qty = parseFloat($(this).find('.type3-quantity').val()) || 0;
                    const vol = parseFloat($(this).find('.type3-volume').val()) || 0;
                    if (productId) {
                        totalFinalProducts += qty;
                        totalVolume += qty * vol;
                    }
                });

                $('#type3_total_volume').val(totalVolume.toFixed(4));

                // Chute = sum sous-blocs volume - sum produits finis volume
                const wasteVolume = totalSourceVolume - totalVolume;

                if (totalVolume > totalSourceVolume && totalSourceVolume > 0) {
                    const deficit = totalVolume - totalSourceVolume;
                    $('#type3VolumeExceedMessage').html(`
                        <strong>⚠️ Le volume total des produits finaux (${totalVolume.toFixed(4)} m³)
                        dépasse le volume source disponible (${totalSourceVolume.toFixed(4)} m³).</strong>
                        <br>Déficit: ${deficit.toFixed(4)} m³
                        <br>Veuillez réduire la quantité ou ajouter des sous-blocs.
                    `);
                    $('#type3VolumeExceedAlert').removeClass('d-none');
                    $('#type3_waste_volume').val('');
                    $('#type3_waste_info').html('<span class="text-danger">❌ Volume source insuffisant!</span>');
                } else {
                    $('#type3VolumeExceedAlert').addClass('d-none');
                    $('#type3_waste_volume').val(wasteVolume >= 0 ? wasteVolume.toFixed(4) : '0.0000');

                    if (wasteVolume > 0 && totalSourceVolume > 0) {
                        const wastePercentage = (wasteVolume / totalSourceVolume * 100).toFixed(2);
                        $('#type3_waste_info').html(
                            `<span class="text-warning">⚠️ Chute estimée: ${wasteVolume.toFixed(4)} m³ (${wastePercentage}%)</span>`
                        );
                    } else if (wasteVolume === 0 && totalSourceVolume > 0) {
                        $('#type3_waste_info').html(
                            `<span class="text-success">✓ Aucune chute (volume parfaitement optimisé)</span>`
                        );
                    } else {
                        $('#type3_waste_info').html('');
                    }
                }

                $('#actual_total_source_volume').val(totalSourceVolume);
                $('#actual_total_produced_volume').val(totalVolume);
                if (totalSourceVolume > 0) {
                    $('#actual_waste_percentage').val(wasteVolume > 0 ? (wasteVolume / totalSourceVolume * 100).toFixed(2) : 0);
                }
                $('#actual_source_product_id').val(firstSourceProductId || '');
                $('#actual_required_quantity').val(totalSousBlocsCount);

                // Stock check per sous-bloc
                $('#insufficientStockAlert').addClass('d-none');
                let stockChecks = [];
                $('.type3-sous-bloc-row').each(function() {
                    const productId = $(this).find('.type3-sous-bloc-select').val();
                    const qty = parseFloat($(this).find('.type3-sous-bloc-quantity').val()) || 0;
                    if (productId && qty > 0) {
                        stockChecks.push(getStockInfo(productId, familleId).then(stockInfo => {
                            if (stockInfo.available < qty) {
                                return `${stockInfo.famille_name || 'Sous-bloc'}: Requis ${qty}, Disponible ${stockInfo.available.toFixed(2)}`;
                            }
                            return null;
                        }));
                    }
                });

                if (stockChecks.length > 0) {
                    Promise.all(stockChecks).then(results => {
                        const issues = results.filter(r => r !== null);
                        if (issues.length > 0) {
                            $('#insufficientStockList').html('<ul class="mb-0 mt-2">' + issues.map(i => `<li>${i}</li>`).join('') + '</ul>');
                            $('#insufficientStockAlert').removeClass('d-none');
                        }
                    });
                }

            }

            function updateType5Calculation() {
                const chutesVolume = parseFloat($('#type5_chutes_volume').val()) || 0;
                $('#type5_chutes_volume_display').val(chutesVolume.toFixed(4));

                let totalVolume = 0;

                $('.type5-product-row').each(function() {
                    const productId = $(this).find('.type5-product-select').val();
                    const qty = parseFloat($(this).find('.type5-quantity').val()) || 0;
                    const vol = parseFloat($(this).find('.type5-volume').val()) || 0;
                    if (productId) {
                        totalVolume += qty * vol;
                    }
                });

                $('#type5_total_volume').val(totalVolume.toFixed(4));

                const wasteVolume = chutesVolume - totalVolume;

                if (totalVolume > chutesVolume && chutesVolume > 0) {
                    const deficit = totalVolume - chutesVolume;
                    $('#type5VolumeExceedMessage').html(`
                        <strong>⚠️ Le volume total des produits (${totalVolume.toFixed(4)} m³)
                        dépasse le volume de chutes alloué (${chutesVolume.toFixed(4)} m³).</strong>
                        <br>Déficit: ${deficit.toFixed(4)} m³
                        <br>Veuillez ajouter plus de chutes ou réduire les quantités.
                    `);
                    $('#type5VolumeExceedAlert').removeClass('d-none');
                    $('#type5_waste_volume').val('');
                    $('#type5_waste_info').html('<span class="text-danger">❌ Volume de chutes insuffisant!</span>');
                } else {
                    $('#type5VolumeExceedAlert').addClass('d-none');
                    $('#type5_waste_volume').val(wasteVolume >= 0 ? wasteVolume.toFixed(4) : '0.0000');

                    if (wasteVolume > 0 && chutesVolume > 0) {
                        const wastePercentage = (wasteVolume / chutesVolume * 100).toFixed(2);
                        $('#type5_waste_info').html(
                            `<span class="text-warning">⚠️ Chute résiduelle estimée: ${wasteVolume.toFixed(4)} m³ (${wastePercentage}%)</span>`
                        );
                    } else if (wasteVolume === 0 && chutesVolume > 0) {
                        $('#type5_waste_info').html(
                            `<span class="text-success">✓ Aucune chute résiduelle (volume parfaitement optimisé)</span>`
                        );
                    } else {
                        $('#type5_waste_info').html('');
                    }
                }

                $('#actual_material_source').val('chutes_only');
                $('#actual_chutes_volume').val(chutesVolume);
                $('#actual_total_source_volume').val(chutesVolume);
                $('#actual_total_produced_volume').val(totalVolume);
                if (chutesVolume > 0) {
                    $('#actual_waste_percentage').val(wasteVolume > 0 ? (wasteVolume / chutesVolume * 100).toFixed(2) : 0);
                }
                $('#actual_required_quantity').val(chutesVolume);
            }

            // Load BOM for Type 1 with editable table
            $(document).on('input', '.bom-planned-quantity', function() {
                const $row = $(this).closest('tr');
                const plannedQuantity = parseFloat($(this).val()) || 0;
                const productionQuantity = parseFloat($('#type1_quantity').val()) || 1;

                // Calculate quantity per unit
                const quantityPerUnit = plannedQuantity / productionQuantity;

                // Update quantity per unit field
                $row.find('.bom-quantity-required').val(quantityPerUnit.toFixed(4));

                // Update total cost
                const unitCost = parseFloat($row.find('.bom-unit-cost').val()) || 0;
                const totalCost = plannedQuantity * unitCost;
                $row.find('.bom-item-total').text(totalCost.toFixed(2) + ' DH');

                // Update hidden inputs
                $row.find('input[name*="planned_quantity"]').val(plannedQuantity);
                $row.find('input[name*="quantity_required"]').val(quantityPerUnit);

                updateBomTotalCost();
            });

            $(document).on('input', '.bom-quantity-required', function() {
                const $row = $(this).closest('tr');
                const quantityPerUnit = parseFloat($(this).val()) || 0;
                const productionQuantity = parseFloat($('#type1_quantity').val()) || 1;

                // Calculate planned quantity
                const plannedQuantity = quantityPerUnit * productionQuantity;

                // Update planned quantity field
                $row.find('.bom-planned-quantity').val(plannedQuantity.toFixed(4));

                // Update total cost
                const unitCost = parseFloat($row.find('.bom-unit-cost').val()) || 0;
                const totalCost = plannedQuantity * unitCost;
                $row.find('.bom-item-total').text(totalCost.toFixed(2) + ' DH');

                // Update hidden inputs
                $row.find('input[name*="planned_quantity"]').val(plannedQuantity);
                $row.find('input[name*="quantity_required"]').val(quantityPerUnit);

                updateBomTotalCost();
            });

            // function loadBOM(productId, quantity) {
            //     const materialSource = $('input[name="material_source"]:checked').val();
            //     const chutesVolume = $('#chutes_volume').val() || 0;

            //     // Show loading state
            //     $('#bomTableBody').html(`
        //         <tr>
        //             <td colspan="10" class="text-center py-4">
        //                 <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
        //                 Chargement de la nomenclature...
        //             </td>
        //         </tr>
        //     `);
            //     $('#bomCard').removeClass('d-none');
            //     $('#bom-quantity-display').text(quantity);

            //     let sourceInfo = '';
            //     if (materialSource === 'bom_only') sourceInfo =
            //         '<span class="badge bg-primary">100% Matières Nouvelles</span>';
            //     else if (materialSource === 'chutes_only') sourceInfo =
            //         '<span class="badge bg-warning">100% Chutes Recyclées</span>';
            //     else if (materialSource === 'both') sourceInfo =
            //         '<span class="badge bg-success">Mixte (MP + Chutes)</span>';
            //     $('#materialSourceInfo').html(sourceInfo);

            //     $.ajax({
            //         url: "{{ route('production-orders.get-bom') }}",
            //         type: "GET",
            //         data: {
            //             product_id: productId,
            //             quantity: quantity,
            //             material_source: materialSource,
            //             chutes_volume: chutesVolume,
            //             bom_percentage: 100
            //         },
            //         success: function(response) {
            //             if (response.success) {
            //                 let editableBomHtml = '';
            //                 let totalCost = 0;

            //                 // Check if there are any items to display
            //                 const hasChutes = response.chutes_material && response.chutes_material
            //                     .chutes_volume > 0;
            //                 const hasBomItems = response.bom_items && response.bom_items.length > 0;

            //                 if (!hasChutes && !hasBomItems) {
            //                     $('#bomTableBody').html(`
        //                         <tr>
        //                             <td colspan="10" class="text-center text-warning py-4">
        //                                 <i class="fas fa-exclamation-triangle me-2"></i>
        //                                 Aucune nomenclature définie pour ce produit. Cliquez sur "Ajouter une matière première" pour ajouter des matières premières.
        //                             </td>
        //                         </tr>
        //                     `);
            //                     $('#bomTableFooter').html('');
            //                     $('#actual_total_cost').val(0);
            //                     return;
            //                 }

            //                 if (hasChutes) {
            //                     const chutesRequired = response.chutes_material.chutes_volume;
            //                     const chutesStock = response.chutes_material.current_stock || 0;
            //                     editableBomHtml += `
        //                         <tr class="table-warning bom-item-row" data-material-id="${response.chutes_material.material_id}">
        //                             <td><div class="d-flex align-items-center"><i class="fas fa-recycle text-warning me-2"></i><div><div class="fw-medium">${escapeHtml(response.chutes_material.material_name)}</div><small class="text-muted">${escapeHtml(response.chutes_material.material_code)}</small></div></div></td>
        //                             <td><code>${escapeHtml(response.chutes_material.material_code)}</code></td>
        //                             <td class="text-center">-</td>
        //                             <td class="text-center bom-stock-available">${parseFloat(chutesStock).toFixed(4)}</td>
        //                             <td class="text-center"><input type="number" class="form-control form-control-sm bom-planned-quantity" value="${parseFloat(chutesRequired).toFixed(4)}" step="0.0001" min="0" style="width: 120px; display: inline-block;"><input type="hidden" name="bom_consumptions[${response.chutes_material.material_id}][material_id]" value="${response.chutes_material.material_id}"><input type="hidden" name="bom_consumptions[${response.chutes_material.material_id}][planned_quantity]" value="${chutesRequired}"></td>
        //                             <td class="text-center">m³</td>
        //                             <td class="text-center bom-unit-cost">0.00 DH</td>
        //                             <td class="text-center bom-item-total">0.00 DH</td>
        //                             <td class="text-center"><span class="badge ${chutesStock >= chutesRequired ? 'bg-success' : 'bg-warning'}">${chutesStock >= chutesRequired ? '✓ Suffisant' : '⚠️ Stock faible'}</span></td>
        //                             <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-bom-item" data-material-id="${response.chutes_material.material_id}"><i class="fas fa-trash"></i></button></td>
        //                         </tr>`;
            //                 }

            //                 if (hasBomItems) {
            //                     response.bom_items.forEach((item) => {
            //                         const unitCost = item.raw_material.unit_cost || 0;
            //                         const requiredQty = item.quantity_required * quantity;
            //                         const itemCost = requiredQty * unitCost;
            //                         totalCost += itemCost;

            //                         editableBomHtml += `
        //                             <tr class="bom-item-row" data-material-id="${item.material_id}">
        //                                 <td><div class="d-flex align-items-center"><i class="fas fa-box text-primary me-2"></i><div><div class="fw-medium">${escapeHtml(item.raw_material.material_name)}</div><small class="text-muted">${escapeHtml(item.raw_material.material_code)}</small></div></div></td>
        //                                 <td><code>${escapeHtml(item.raw_material.material_code)}</code></td>
        //                                 <td class="text-center"><input type="number" class="form-control form-control-sm bom-quantity-required" value="${item.quantity_required}" step="0.0001" min="0" style="width: 100px; display: inline-block;"></td>
        //                                 <td class="text-center bom-stock-available">${parseFloat(item.raw_material.current_stock || 0).toFixed(4)}</td>
        //                                 <td class="text-center"><input type="number" class="form-control form-control-sm bom-planned-quantity" value="${requiredQty.toFixed(4)}" step="0.0001" min="0" style="width: 120px; display: inline-block;"><input type="hidden" name="bom_consumptions[${item.material_id}][material_id]" value="${item.material_id}"><input type="hidden" name="bom_consumptions[${item.material_id}][planned_quantity]" value="${requiredQty}"><input type="hidden" name="bom_consumptions[${item.material_id}][quantity_required]" value="${item.quantity_required}"></td>
        //                                 <td class="text-center">${escapeHtml(item.raw_material.unit_of_measure)}</td>
        //                                 <td class="text-center bom-unit-cost">${unitCost.toFixed(2)} DH</td>
        //                                 <td class="text-center bom-item-total">${itemCost.toFixed(2)} DH</td>
        //                                 <td class="text-center"><span class="badge ${(item.raw_material.current_stock || 0) >= requiredQty ? 'bg-success' : 'bg-warning'}">${(item.raw_material.current_stock || 0) >= requiredQty ? '✓ Suffisant' : '⚠️ Stock faible'}</span></td>
        //                                 <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-bom-item" data-material-id="${item.material_id}"><i class="fas fa-trash"></i></button></td>
        //                             </tr>`;
            //                     });
            //                 }

            //                 if (editableBomHtml) {
            //                     $('#bomTableBody').html(editableBomHtml);
            //                     $('#bomTableFooter').html(
            //                         `<tr class="table-primary"><td colspan="7" class="text-end"><strong>Coût Total Estimé:</strong></td><td class="text-center"><strong>${totalCost.toFixed(2)} DH</strong></td><td colspan="2"></td></tr>`
            //                     );
            //                     $('#actual_total_cost').val(totalCost);

            //                     // Attach event handlers for editable fields
            //                     attachBomEventHandlers();
            //                     showToast('success', 'Nomenclature chargée avec succès');
            //                 }
            //             } else {
            //                 $('#bomTableBody').html(
            //                     `<tr><td colspan="10" class="text-center text-warning py-4"><i class="fas fa-exclamation-triangle me-2"></i>${response.message || 'Erreur lors du chargement de la nomenclature'}</td></tr>`
            //                 );
            //                 $('#bomTableFooter').html('');
            //                 $('#actual_total_cost').val(0);
            //             }
            //         },
            //         error: function(xhr) {
            //             console.error('BOM loading error:', xhr);
            //             $('#bomTableBody').html(
            //                 '<tr><td colspan="10" class="text-center text-danger py-4"><i class="fas fa-exclamation-circle me-2"></i>Erreur lors du chargement de la nomenclature</td></tr>'
            //             );
            //             $('#bomCard').removeClass('d-none');
            //         }
            //     });
            // }

            // Function to load all raw materials
            async function loadRawMaterials() {
                try {
                    const response = await $.ajax({
                        url: "{{ route('raw-materials.list') }}",
                        type: "GET",
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.success && response.data) {
                        rawMaterialsList = response.data;
                    } else if (Array.isArray(response)) {
                        rawMaterialsList = response;
                    } else if (response.data && Array.isArray(response.data)) {
                        rawMaterialsList = response.data;
                    }
                    return rawMaterialsList;
                } catch (error) {
                    console.error('Error loading raw materials:', error);
                    showToast('error', 'Erreur lors du chargement des matières premières');
                    return [];
                }
            }

            async function showAddRawMaterialModal() {
                if (rawMaterialsList.length === 0) await loadRawMaterials();
                if (!rawMaterialsList || rawMaterialsList.length === 0) {
                    showToast('error', 'Aucune matière première disponible');
                    return;
                }

                if ($('#addRawMaterialModal').length) $('#addRawMaterialModal').remove();

                let options = '<option value="">Sélectionner une matière première</option>';
                rawMaterialsList.forEach(material => {
                    const materialId = material.material_id || material.id;
                    const materialName = material.material_name || material.name;
                    const materialCode = material.material_code || material.code;
                    const currentStock = parseFloat(material.current_stock || material.stock || 0);
                    const unitOfMeasure = material.unit_of_measure || material.unit || 'unité';
                    const unitCost = parseFloat(material.average_unit_cost || material.unit_cost ||
                        material.cost || 0);

                    // Check if this is Chutes de Production
                    const isChutes = materialName.toLowerCase().includes('chute') ||
                        materialCode === 'CHUTE-PRODUCTION' ||
                        materialName === 'Chutes de Production';

                    // Determine stock status class
                    let stockClass = '';
                    let stockIcon = '';
                    if (currentStock <= 0) {
                        stockClass = 'text-danger';
                        stockIcon = '<i class="fas fa-times-circle text-danger me-1"></i>';
                    } else if (currentStock < 10) {
                        stockClass = 'text-warning';
                        stockIcon = '<i class="fas fa-exclamation-triangle text-warning me-1"></i>';
                    } else {
                        stockIcon = '<i class="fas fa-check-circle text-success me-1"></i>';
                    }

                    options += `<option value="${materialId}"
                        data-code="${materialCode || ''}"
                        data-unit="${unitOfMeasure}"
                        data-stock="${currentStock}"
                        data-cost="${unitCost}"
                        data-is-chutes="${isChutes}"
                        data-stock-class="${stockClass}"
                        data-stock-icon='${stockIcon}'>
                        ${materialName} - ${stockIcon} Stock: ${currentStock.toFixed(2)} ${unitOfMeasure}
                    </option>`;
                });

                const productionQuantity = parseFloat($('#type1_quantity').val()) || 1;

                const modalHtml = `
                <div class="modal fade" id="addRawMaterialModal" tabindex="-1" data-bs-backdrop="static">
                    <div class="modal-dialog"><div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Ajouter une matière première</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Matière Première *</label>
                                <select class="form-control" id="modal_material_id" name="modal_material_id" required style="width: 100%;">
                                    ${options}
                                </select>
                            </div>
                            <div id="stockAlertContainer" class="mb-3" style="display: none;">
                                <div class="alert" id="stockAlert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <span id="stockAlertMessage"></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" id="quantityLabel">Quantité Totale à Utiliser *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="modal_quantity_total" value="${productionQuantity}" step="0.0001" min="0.0001" required>
                                    <span class="input-group-text" id="quantityUnit">unité(s)</span>
                                </div>
                                <small class="text-muted" id="quantityHelp">Quantité totale pour cette production (${productionQuantity} unités)</small>
                                <div id="volumeEquivalentContainer" class="mt-2 small text-muted" style="display: none;">
                                    <i class="fas fa-cube me-1"></i>
                                    <span id="volumeEquivalent"></span>
                                </div>
                            </div>
                            <div class="mb-3 d-none">
                                <label class="form-label">Quantité par Unité (calculée automatiquement)</label>
                                <input type="number" class="form-control" id="modal_quantity_per_unit" readonly step="0.0001" min="0">
                                <small class="text-muted">Quantité par produit: Total ÷ Quantité à produire</small>
                            </div>
                            <div class="mb-3 d-none form-check">
                                <input type="checkbox" checked="true" class="form-check-input" id="modal_save_to_product" value="1">
                                <label class="form-check-label" for="modal_save_to_product">
                                    <strong>Enregistrer cette matière dans la nomenclature du produit</strong>
                                </label>
                                <small class="form-text text-muted d-block">Cocher cette case pour ajouter cette matière à la BOM permanente du produit.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn btn-success" id="confirmAddMaterialBtn">
                                <i class="fas fa-plus me-1"></i> Ajouter
                            </button>
                        </div>
                    </div></div>
                </div>`;

                $('body').append(modalHtml);

                setTimeout(function() {
                    if ($('#modal_material_id').length) {
                        $('#modal_material_id').select2({
                            language: "fr",
                            placeholder: "Sélectionner une matière première",
                            dropdownParent: $('#addRawMaterialModal'),
                            width: '100%'
                        });
                    }
                }, 100);

                // Function to update UI based on material type (chutes or regular)
                function updateModalForMaterialType() {
                    const materialId = $('#modal_material_id').val();
                    if (!materialId) return;

                    const selectedOption = $('#modal_material_id option:selected');
                    const isChutes = selectedOption.data('is-chutes') === true;
                    const unit = selectedOption.data('unit') || 'unité';

                    if (isChutes) {
                        // For chutes: show kg input, convert to volume
                        $('#quantityLabel').html('Poids à Utiliser *');
                        $('#quantityUnit').text('kg');
                        $('#quantityHelp').html('Poids des chutes à utiliser pour cette production');
                        $('#volumeEquivalentContainer').show();

                        // Update the input - make it free typing without auto-conversion
                        $('#modal_quantity_total').attr({
                            'step': 'any',
                            'placeholder': 'Ex: 1.23',
                            'min': '0.0001',
                        });

                        const currentValue = $('#modal_quantity_total').val();
                        if (currentValue && !isNaN(parseFloat(currentValue))) {
                            // Keep as is, don't convert
                        } else {
                            $('#modal_quantity_total').val('');
                        }

                        $('#volumeEquivalent').html('');
                    } else {
                        // For regular materials: show unit input
                        $('#quantityLabel').html('Quantité Totale à Utiliser *');
                        $('#quantityUnit').text(unit);
                        $('#quantityHelp').html(
                            `Quantité totale pour cette production (${productionQuantity} unités)`);
                        $('#volumeEquivalentContainer').hide();

                        // Reset step
                        $('#modal_quantity_total').attr('step', '0.0001');
                        $('#modal_quantity_total').attr('placeholder', '');
                    }
                }

                // Function to update volume equivalent display for chutes
                function updateVolumeEquivalent(kgValue) {
                    kgValue = roundTo2Decimals(kgValue);
                    const volumeM3 = roundTo2Decimals(kgValue / CHUTES_DENSITY);
                    $('#volumeEquivalent').html(`${formatKg(kgValue)} kg = ${formatVolume(volumeM3)} m³`);
                }

                // Function to check stock and show alert (modified for chutes)
                function checkStockAndShowAlert() {
                    const materialId = $('#modal_material_id').val();
                    if (!materialId) {
                        $('#stockAlertContainer').hide();
                        return;
                    }

                    const selectedOption = $('#modal_material_id option:selected');
                    const isChutes = selectedOption.data('is-chutes') === true;
                    const currentStock = parseFloat(selectedOption.data('stock')) || 0;
                    const materialName = selectedOption.text().split('(')[0].trim();
                    const unit = selectedOption.data('unit') || 'unité';
                    let totalQuantity = parseFloat($('#modal_quantity_total').val()) || 0;

                    // For chutes, convert kg to m³ for stock comparison
                    let stockQuantity = currentStock;
                    let displayQuantity = totalQuantity;
                    let displayUnit = unit;

                    if (isChutes) {
                        // Convert kg to m³ for stock comparison (stock is in m³)
                        const volumeM3 = totalQuantity / CHUTES_DENSITY;
                        stockQuantity = currentStock; // Stock is in m³
                        displayQuantity = volumeM3;
                        displayUnit = 'm³';
                    }

                    const stockStatus = stockQuantity <= 0 ? 'no_stock' :
                        (stockQuantity < displayQuantity ? 'insufficient' :
                            (stockQuantity < 10 ? 'low' : 'sufficient'));

                    let alertClass = '';
                    let alertMessage = '';
                    let showAlert = true;

                    if (stockQuantity <= 0) {
                        alertClass = 'alert-danger';
                        alertMessage = `<strong>⚠️ Stock épuisé !</strong><br>
                                La matière première "${materialName}" n'a pas de stock disponible.<br>
                                Stock actuel: <strong>0 ${displayUnit}</strong><br>
                                Vous pouvez quand même ajouter cette matière, mais le stock deviendra négatif.`;
                    } else if (stockQuantity < displayQuantity) {
                        const deficit = displayQuantity - stockQuantity;
                        alertClass = 'alert-warning';
                        alertMessage = `<strong>⚠️ Stock insuffisant !</strong><br>
                                La quantité demandée (${displayQuantity.toFixed(4)} ${displayUnit}) dépasse le stock disponible.<br>
                                Stock actuel: <strong>${stockQuantity.toFixed(4)} ${displayUnit}</strong><br>
                                Manque: <strong>${deficit.toFixed(4)} ${displayUnit}</strong><br>
                                Vous pouvez quand même ajouter cette matière, le stock deviendra négatif.`;
                    } else if (stockQuantity < 10) {
                        alertClass = 'alert-warning';
                        alertMessage = `<strong>⚠️ Stock faible !</strong><br>
                                La matière première "${materialName}" a un stock faible.<br>
                                Stock actuel: <strong>${stockQuantity.toFixed(4)} ${displayUnit}</strong><br>
                                Vous pouvez toujours l'utiliser, mais pensez à réapprovisionner.`;
                    } else {
                        showAlert = false;
                    }

                    if (showAlert) {
                        $('#stockAlert').removeClass('alert-danger alert-warning alert-info').addClass(
                            alertClass);
                        $('#stockAlertMessage').html(alertMessage);
                        $('#stockAlertContainer').show();
                    } else {
                        $('#stockAlertContainer').hide();
                    }
                }

                $('#modal_quantity_total').on('blur', function() {
                    let rawValue = $(this).val();

                    if (!rawValue || rawValue === '') {
                        return;
                    }

                    let numericValue = parseFloat(rawValue);

                    if (!isNaN(numericValue) && numericValue > 0) {
                        // Round to 2 decimal places
                        const roundedValue = roundTo2Decimals(numericValue);
                        $(this).val(formatKg(roundedValue));

                        // Trigger the calculation with the rounded value
                        const materialId = $('#modal_material_id').val();
                        if (materialId) {
                            const selectedOption = $('#modal_material_id option:selected');
                            const isChutes = selectedOption.data('is-chutes') === true;
                            const productionQty = parseFloat($('#type1_quantity').val()) || 1;

                            if (isChutes) {
                                const volumeM3 = roundedValue / CHUTES_DENSITY;
                                $('#volumeEquivalent').html(
                                    `${formatKg(roundedValue)} kg = ${formatVolume(volumeM3)} m³`);
                                const quantityPerUnit = volumeM3 / productionQty;
                                $('#modal_quantity_per_unit').val(quantityPerUnit.toFixed(6));
                            } else {
                                const quantityPerUnit = roundedValue / productionQty;
                                $('#modal_quantity_per_unit').val(quantityPerUnit.toFixed(4));
                            }

                            checkStockAndShowAlert();
                        }
                    }
                });

                // Calculate quantity per unit when total quantity changes
                $('#modal_quantity_total').on('input', function() {
                    let totalQuantity = parseFloat($(this).val()) || 0;
                    const productionQty = parseFloat($('#type1_quantity').val()) || 1;
                    const materialId = $('#modal_material_id').val();

                    if (materialId) {
                        const selectedOption = $('#modal_material_id option:selected');
                        const isChutes = selectedOption.data('is-chutes') === true;

                        if (isChutes) {
                            // For chutes, we store the volume in m³, but user enters kg
                            const kgValue = totalQuantity;
                            const volumeM3 = kgValue / CHUTES_DENSITY;
                            updateVolumeEquivalent(kgValue);
                            // quantityPerUnit will be in m³ per unit
                            const quantityPerUnit = volumeM3 / productionQty;
                            $('#modal_quantity_per_unit').val(quantityPerUnit.toFixed(6));
                        } else {
                            const quantityPerUnit = totalQuantity / productionQty;
                            $('#modal_quantity_per_unit').val(quantityPerUnit.toFixed(4));
                        }
                    }

                    checkStockAndShowAlert();
                });

                // Check stock when material selection changes
                // In the modal material selection change handler:
                $('#modal_material_id').on('change', function() {
                    updateModalForMaterialType();
                    checkStockAndShowAlert();

                    const selectedOption = $(this).find(':selected');
                    const isChutes = selectedOption.data('is-chutes') === true;
                    const productionQty = parseFloat($('#type1_quantity').val()) || 1;

                    if (isChutes) {
                        // For chutes - set empty, no default value
                        $('#modal_quantity_total').val('');
                        $('#modal_quantity_total').attr('placeholder', 'Ex: 1.23');
                        $('#volumeEquivalent').html('');
                        $('#modal_quantity_per_unit').val('');
                    } else {
                        $('#modal_quantity_total').val(productionQty);
                        $('#modal_quantity_total').trigger('input');
                    }
                });

                // Trigger initial UI setup
                updateModalForMaterialType();
                $('#modal_quantity_total').trigger('input');

                const modal = new bootstrap.Modal(document.getElementById('addRawMaterialModal'));
                modal.show();

                $('#addRawMaterialModal').on('hidden.bs.modal', function() {
                    if ($('#modal_material_id').length && $('#modal_material_id').data('select2')) {
                        $('#modal_material_id').select2('destroy');
                    }
                    $('#addRawMaterialModal').remove();
                });

                $('#confirmAddMaterialBtn').off('click').on('click', function() {
                    const materialId = $('#modal_material_id').val();
                    let totalQuantity = parseFloat($('#modal_quantity_total').val());
                    const quantityPerUnit = parseFloat($('#modal_quantity_per_unit').val());
                    const saveToProduct = $('#modal_save_to_product').is(':checked');

                    if (!materialId) {
                        showToast('error', 'Veuillez sélectionner une matière première');
                        return;
                    }
                    if (!totalQuantity || totalQuantity <= 0) {
                        showToast('error', 'Veuillez saisir une quantité valide');
                        return;
                    }

                    const selectedOption = $('#modal_material_id option:selected');
                    if (!selectedOption.length) {
                        showToast('error', 'Erreur: Matière première non valide');
                        return;
                    }

                    const materialName = selectedOption.text().split('(')[0].trim();
                    const materialCode = selectedOption.data('code') || '';
                    const unit = selectedOption.data('unit') || 'unité';
                    const stock = parseFloat(selectedOption.data('stock')) || 0;
                    const unitCost = parseFloat(selectedOption.data('cost')) || 0;
                    const isChutes = selectedOption.data('is-chutes') === true;
                    const productionQuantity = parseFloat($('#type1_quantity').val()) || 1;

                    let plannedQuantity = totalQuantity;
                    let displayUnit = unit;
                    let stockComparison = stock;

                    if (isChutes) {
                        plannedQuantity = totalQuantity / CHUTES_DENSITY;
                        displayUnit = 'm³';
                        stockComparison = stock;
                    }

                    const totalCost = plannedQuantity * unitCost;

                    if (stockComparison < plannedQuantity && stockComparison > 0) {
                        const deficit = plannedQuantity - stockComparison;
                        if (!confirm(
                                `⚠️ Attention: Stock insuffisant!\n\nMatière: ${materialName}\nStock disponible: ${stockComparison.toFixed(4)} ${displayUnit}\nQuantité demandée: ${plannedQuantity.toFixed(4)} ${displayUnit}\nManque: ${deficit.toFixed(4)} ${displayUnit}\n\nLe stock deviendra négatif.\n\nVoulez-vous continuer ?`
                            )) {
                            return;
                        }
                    } else if (stockComparison <= 0 && plannedQuantity > 0) {
                        if (!confirm(
                                `⚠️ Attention: Stock épuisé!\n\nMatière: ${materialName}\nStock actuel: 0 ${displayUnit}\nQuantité demandée: ${plannedQuantity.toFixed(4)} ${displayUnit}\n\nLe stock deviendra négatif.\n\nVoulez-vous continuer ?`
                            )) {
                            return;
                        }
                    } else if (stockComparison < 10 && stockComparison > 0) {
                        if (!confirm(
                                `⚠️ Attention: Stock faible!\n\nMatière: ${materialName}\nStock disponible: ${stockComparison.toFixed(4)} ${displayUnit}\nQuantité demandée: ${plannedQuantity.toFixed(4)} ${displayUnit}\n\nVoulez-vous continuer ?`
                            )) {
                            return;
                        }
                    }

                    // Check if material already exists in BOM
                    let exists = false;
                    let existingRow = null;
                    $('.bom-item-row').each(function() {
                        if ($(this).data('material-id') == materialId) {
                            exists = true;
                            existingRow = $(this);
                            return false;
                        }
                    });

                    if (exists) {
                        // Update existing row
                        existingRow.find('.bom-quantity-required').val(quantityPerUnit.toFixed(2));
                        existingRow.find('.bom-planned-quantity').val(plannedQuantity.toFixed(2));
                        existingRow.find('.bom-item-total').text(totalCost.toFixed(2) + ' DH');

                        $('<input>').attr({
                            type: 'hidden',
                            name: `bom_consumptions[${materialId}][update_quantity]`,
                            value: '1'
                        }).appendTo('#productionOrderForm');

                        updateBomTotalCost();
                        modal.hide();
                        showToast('success', 'Quantité mise à jour avec succès');
                        return;
                    }

                    // Remove "no data" message if exists
                    if ($('#bomTableBody tr').length === 1 && $('#bomTableBody tr').find(
                            'td[colspan="10"]').length) {
                        $('#bomTableBody').empty();
                    }

                    // Determine stock status badge
                    let stockBadge = '';
                    if (stockComparison <= 0) {
                        stockBadge = '<span class="badge bg-danger">Épuisé</span>';
                    } else if (stockComparison < plannedQuantity) {
                        stockBadge = '<span class="badge bg-warning">Stock insuffisant</span>';
                    } else if (stockComparison < 10) {
                        stockBadge = '<span class="badge bg-warning">Stock faible</span>';
                    } else {
                        stockBadge = '<span class="badge bg-success">Stock OK</span>';
                    }

                    // Add chutes specific styling
                    const rowClass = isChutes ? 'table-warning' : '';
                    const iconClass = isChutes ? 'fas fa-recycle text-warning' :
                        'fas fa-box text-primary';

                    const newRowHtml = `
    <tr class="bom-item-row ${rowClass}" data-material-id="${materialId}">
        <td><div class="d-flex align-items-center"><i class="${iconClass} me-2"></i><div><div class="fw-medium">${escapeHtml(materialName)}</div><small class="text-muted">${escapeHtml(materialCode)}</small></div></div></td>
        <td><code>${escapeHtml(materialCode)}</code></td>
        <td class="text-center"><input type="number" class="form-control form-control-sm bom-quantity-required" value="${quantityPerUnit.toFixed(2)}" step="0.0001" min="0" style="width: 100px; display: inline-block;"></td>
        <td class="text-center bom-stock-available">${stockComparison.toFixed(2)} ${stockBadge}</td> <!-- Changed to 2 decimals -->
        <td class="text-center"><input type="number" class="form-control form-control-sm bom-planned-quantity" value="${plannedQuantity.toFixed(2)}" step="0.0001" min="0" style="width: 120px; display: inline-block;"><input type="hidden" name="bom_consumptions[${materialId}][material_id]" value="${materialId}"><input type="hidden" name="bom_consumptions[${materialId}][planned_quantity]" value="${plannedQuantity.toFixed(2)}"><input type="hidden" name="bom_consumptions[${materialId}][quantity_required]" value="${quantityPerUnit.toFixed(2)}">${saveToProduct ? `<input type="hidden" name="bom_consumptions[${materialId}][save_to_product]" value="1">` : ''}${saveToProduct ? '' : `<input type="hidden" name="bom_consumptions[${materialId}][save_to_product]" value="0">`}</td>
        <td class="text-center">${escapeHtml(displayUnit)}</td>
        <td class="text-center bom-unit-cost">${unitCost.toFixed(2)} DH</td>
        <td class="text-center bom-item-total">${totalCost.toFixed(2)} DH</td>
        <td class="text-center"><span class="badge ${stockComparison >= plannedQuantity ? (stockComparison < 10 ? 'bg-warning' : 'bg-success') : 'bg-danger'}">${stockComparison >= plannedQuantity ? (stockComparison < 10 ? '⚠️ Stock faible' : '✓ Suffisant') : '⚠️ Stock insuffisant'}</span></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-bom-item" data-material-id="${materialId}"><i class="fas fa-trash"></i></button></td>
    </tr>
`;

                    $('#bomTableBody').append(newRowHtml);
                    attachBomEventHandlers();
                    updateBomTotalCost();
                    modal.hide();

                    if (stockComparison < plannedQuantity) {
                        showToast('warning',
                            `⚠️ Stock insuffisant pour ${materialName}. Le stock deviendra négatif.`
                        );
                    } else {
                        showToast('success', 'Matière première ajoutée avec succès');
                    }
                });
            }

            function attachBomEventHandlers() {
                $('.bom-planned-quantity').off('input').on('input', function() {
                    const $row = $(this).closest('tr');
                    const plannedQuantity = parseFloat($(this).val()) || 0;
                    const productionQty = parseFloat($('#type1_quantity').val()) || 1;
                    const quantityPerUnit = plannedQuantity / productionQty;
                    $row.find('.bom-quantity-required').val(quantityPerUnit.toFixed(4));
                    const unitCost = parseFloat($row.find('.bom-unit-cost').text()) || 0;
                    const totalCost = plannedQuantity * unitCost;
                    $row.find('.bom-item-total').text(totalCost.toFixed(2) + ' DH');
                    $row.find('input[name*="planned_quantity"]').val(plannedQuantity);
                    $row.find('input[name*="quantity_required"]').val(quantityPerUnit);
                    updateBomTotalCost();
                });

                $('.bom-quantity-required').off('input').on('input', function() {
                    const $row = $(this).closest('tr');
                    const quantityPerUnit = parseFloat($(this).val()) || 0;
                    const productionQty = parseFloat($('#type1_quantity').val()) || 1;
                    const plannedQuantity = quantityPerUnit * productionQty;
                    $row.find('.bom-planned-quantity').val(plannedQuantity.toFixed(4));
                    const unitCost = parseFloat($row.find('.bom-unit-cost').text()) || 0;
                    const totalCost = plannedQuantity * unitCost;
                    $row.find('.bom-item-total').text(totalCost.toFixed(2) + ' DH');
                    $row.find('input[name*="planned_quantity"]').val(plannedQuantity);
                    $row.find('input[name*="quantity_required"]').val(quantityPerUnit);
                    updateBomTotalCost();
                });
            }

            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Update BOM total cost
            function updateBomTotalCost() {
                let totalCost = 0;
                $('.bom-item-row').each(function() {
                    const totalText = $(this).find('td:nth-child(8)').text().replace(' DH', '');
                    totalCost += parseFloat(totalText) || 0;
                });

                if ($('.bom-item-row').length > 0) {
                    $('#bomTableFooter').html(`
            <tr class="table-primary">
                <td colspan="7" class="text-end"><strong>Coût Total Estimé:</strong></td>
                <td class="text-center"><strong>${totalCost.toFixed(2)} DH</strong></td>
                <td colspan="2"></td>
            </tr>
        `);
                } else {
                    $('#bomTableFooter').empty();
                }

                $('#actual_total_cost').val(totalCost);
            }

            // Remove BOM item
            $(document).on('click', '.remove-bom-item', function() {
                const $row = $(this).closest('tr');
                const materialId = $row.data('material-id');

                if (confirm(
                        'Êtes-vous sûr de vouloir supprimer cette matière première de la nomenclature ?')) {
                    // Add a hidden input to mark this material for removal from product BOM
                    if (materialId) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: `bom_consumptions[${materialId}][remove_from_product]`,
                            value: '1'
                        }).appendTo('#productionOrderForm');
                    }

                    $row.remove();

                    if ($('.bom-item-row').length === 0) {
                        $('#bomTableBody').html(`
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-box-open me-2"></i>
                                Aucune matière première. Cliquez sur "Ajouter une matière première" pour commencer.
                            </td>
                        </tr>
                        `);
                    }
                    updateBomTotalCost();
                }
            });

            // Event handler for add material button
            $(document).on('click', '#addRawMaterialToBomBtn', function() {
                showAddRawMaterialModal();
            });

            $('#chutes_kg').on('input', function() {
                let kg = parseFloat($(this).val()) || 0;
                kg = roundTo2Decimals(kg);
                $(this).val(formatKg(kg));

                const volumeM3 = kg / CHUTES_DENSITY;
                $('#chutes_volume').val(formatVolume(volumeM3));

                if (kg < 0) {
                    $('#chutes_kg').val('0.00');
                    $('#chutes_volume').val('0.00');
                    showToast('warning', 'La quantité ne peut pas être négative');
                    return;
                }

                const materialSource = $('input[name="material_source"]:checked').val();
                if (materialSource === 'both') {
                    $('#actual_chutes_volume').val(volumeM3);
                }
                updateCalculations();
            });

            function checkChutesStock() {
                const materialSource = $('input[name="material_source"]:checked').val();

                if (materialSource === 'both') {
                    $.ajax({
                        url: "{{ route('raw-materials.get-by-code') }}",
                        type: "GET",
                        data: {
                            material_code: 'CHUTE-PRODUCTION'
                        },
                        success: function(response) {
                            if (response.success && response.material) {
                                const chutesMaterial = response.material;
                                const availableStockM3 = parseFloat(chutesMaterial.current_stock) || 0;
                                const availableStockKg = availableStockM3 * CHUTES_DENSITY;
                                const requestedKg = parseFloat($('#chutes_kg').val()) || 0;
                                const requestedM3 = requestedKg / CHUTES_DENSITY;

                                let stockInfoHtml = '';
                                let stockClass = 'chutes-stock-none';

                                if (availableStockM3 > 0) {
                                    const isSufficient = availableStockM3 >= requestedM3;
                                    const percentage = requestedM3 > 0 ? (requestedM3 /
                                        availableStockM3 * 100).toFixed(1) : 0;

                                    stockInfoHtml = `
                            <div class="alert ${isSufficient ? 'chutes-stock-ok' : 'chutes-stock-low'}">
                                <i class="fas ${isSufficient ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>
                                <strong>Stock disponible:</strong><br>
                                ${availableStockM3.toFixed(4)} m³ (${availableStockKg.toFixed(2)} kg)<br>
                                <strong>Demandé:</strong> ${requestedM3.toFixed(4)} m³ (${requestedKg.toFixed(2)} kg)<br>
                                ${!isSufficient ? `<small class="text-danger">⚠️ Stock insuffisant de ${Math.abs(availableStockM3 - requestedM3).toFixed(4)} m³ (${Math.abs(availableStockKg - requestedKg).toFixed(2)} kg)</small>` :
                                                  `<small class="text-success">✓ Stock suffisant (${percentage}% utilisé)</small>`}
                            </div>
                        `;
                                } else if (availableStockM3 === 0) {
                                    stockInfoHtml = `
                            <div class="alert chutes-stock-none">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Aucun stock disponible</strong><br>
                                <small>Les chutes de production ne sont pas disponibles en stock.</small>
                                ${requestedM3 > 0 ? `<br><small>Demandé: ${requestedM3.toFixed(4)} m³ (${requestedKg.toFixed(2)} kg)</small>` : ''}
                            </div>
                        `;
                                } else {
                                    stockInfoHtml = `
                            <div class="alert chutes-stock-none">
                                <i class="fas fa-database me-2"></i>
                                <strong>Stock négatif détecté</strong><br>
                                <small>Veuillez vérifier l'inventaire des chutes.</small>
                            </div>
                        `;
                                }

                                $('#chutesStockInfo').html(stockInfoHtml);
                            } else {
                                $('#chutesStockInfo').html(`
                        <div class="alert chutes-stock-none">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Produit "CHUTE-PRODUCTION" non trouvé</strong><br>
                            <small>Veuillez créer ce produit dans la gestion des matières premières.</small>
                        </div>
                    `);
                            }
                        },
                        error: function() {
                            $('#chutesStockInfo').html(`
                    <div class="alert chutes-stock-none">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Erreur de chargement du stock</strong>
                    </div>
                `);
                        }
                    });
                }
            }

            function addType2ProductRow(productData = null) {
                const index = $('.type2-product-row').length;

                const decoupageProducts = @json($decoupageProducts->where('product_type', 'decoupage')->values());

                let options = '<option value="">Sélectionner un produit découpage</option>';
                decoupageProducts.forEach(product => {
                    const selected = productData && productData.product_id == product.product_id ?
                        'selected' : '';
                    options += `<option value="${product.product_id}" ${selected}>
                    ${product.product_code} - ${product.product_name}
                </option>`;
                });

                const rowHtml = `
            <div class="type2-product-row card mb-3" data-index="${index}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Produit Découpage *</label>
                                <select class="form-control select2 type2-product-select"
                                        name="type2_products[${index}][product_id]"
                                        data-index="${index}" required>
                                    ${options}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Quantité à Produire *</label>
                                <input type="number" class="form-control type2-quantity"
                                       name="type2_products[${index}][quantity_to_produce]"
                                       value="${productData ? productData.quantity_to_produce : ''}"
                                       min="0.01" step="0.01"  required>
                                <small class="form-text text-muted">Nombre de produits découpage</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Volume/Unité</label>
                                <div class="input-group">
                                    <input type="number" class="form-control type2-volume"
                                           data-index="${index}"
                                           step="0.0001" min="0" readonly>
                                    <span class="input-group-text">m³</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Actions</label>
                                <button type="button" class="btn btn-sm btn-danger w-100 remove-type2-product" data-index="${index}">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <small class="form-text text-muted type2-volume-info" data-index="${index}">
                                Sélectionnez un produit pour voir son volume
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;

                $('#noType2ProductsMessage').addClass('d-none');
                $('#type2ProductsContainer').append(rowHtml);

                $(`select[name="type2_products[${index}][product_id]"]`).select2({
                    language: "fr",
                    placeholder: "Sélectionner un produit",
                    allowClear: true
                });

                updateType2Calculation();
            }

            function addType3ProductRow(productData = null) {
                const index = $('.type3-product-row').length;

                const salesProducts = @json($salesProducts->whereIn('product_type', ['finale', 'both'])->values());

                let options = '<option value="">Sélectionner un produit</option>';
                salesProducts.forEach(product => {
                    const selected = productData && productData.product_id == product.product_id ?
                        'selected' : '';
                    options += `<option value="${product.product_id}" ${selected}>
                    ${product.product_code} - ${product.product_name}
                </option>`;
                });

                const rowHtml = `
            <div class="type3-product-row card mb-3" data-index="${index}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Produit Final *</label>
                                <select class="form-control select2 type3-product-select"
                                        name="type3_products[${index}][product_id]"
                                        data-index="${index}" required>
                                    ${options}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Quantité à Produire *</label>
                                <input type="number" class="form-control type3-quantity"
                                       name="type3_products[${index}][quantity_to_produce]"
                                       value="${productData ? productData.quantity_to_produce : ''}"
                                       min="0.01" step="0.01"  required>
                                <small class="form-text text-muted">Nombre de produits finaux</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Volume/Unité</label>
                                <div class="input-group">
                                    <input type="number" class="form-control type3-volume"
                                           data-index="${index}"
                                           step="0.0001" min="0" readonly>
                                    <span class="input-group-text">m³</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Actions</label>
                                <button type="button" class="btn btn-sm btn-danger w-100 remove-type3-product" data-index="${index}">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <small class="form-text text-muted type3-volume-info" data-index="${index}">
                                Sélectionnez un produit pour voir son volume
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;

                $('#noProductsMessage').addClass('d-none');
                $('#type3ProductsContainer').append(rowHtml);

                $('#type3ProductsContainer .type3-product-row').last().find('.type3-product-select').select2({
                    language: "fr",
                    placeholder: "Sélectionner un produit",
                    allowClear: true
                });

                updateType3Calculation();
            }

            function addType5ProductRow(productData = null) {
                const index = $('.type5-product-row').length;

                const salesProducts = @json($salesProducts->whereIn('product_type', ['finale', 'both'])->values());

                let options = '<option value="">Sélectionner un produit</option>';
                salesProducts.forEach(product => {
                    const selected = productData && productData.product_id == product.product_id ?
                        'selected' : '';
                    options += `<option value="${product.product_id}" ${selected}>
                    ${product.product_code} - ${product.product_name}
                </option>`;
                });

                const rowHtml = `
            <div class="type5-product-row card mb-3" data-index="${index}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Produit Final *</label>
                                <select class="form-control select2 type5-product-select"
                                        name="type5_products[${index}][product_id]"
                                        data-index="${index}" required>
                                    ${options}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Quantité à Produire *</label>
                                <input type="number" class="form-control type5-quantity"
                                       name="type5_products[${index}][quantity_to_produce]"
                                       value="${productData ? productData.quantity_to_produce : ''}"
                                       min="0.01" step="0.01"  required>
                                <small class="form-text text-muted">Nombre de produits finaux</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Volume/Unité</label>
                                <div class="input-group">
                                    <input type="number" class="form-control type5-volume"
                                           data-index="${index}"
                                           step="0.0001" min="0" readonly>
                                    <span class="input-group-text">m³</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Actions</label>
                                <button type="button" class="btn btn-sm btn-danger w-100 remove-type5-product" data-index="${index}">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <small class="form-text text-muted type5-volume-info" data-index="${index}">
                                Sélectionnez un produit pour voir son volume
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;

                $('#noType5ProductsMessage').addClass('d-none');
                $('#type5ProductsContainer').append(rowHtml);

                $('#type5ProductsContainer .type5-product-row').last().find('.type5-product-select').select2({
                    language: "fr",
                    placeholder: "Sélectionner un produit",
                    allowClear: true
                });

                updateType5Calculation();
            }

            // Event handlers for adding products
            $('#addType2Product').on('click', function() {
                addType2ProductRow();
            });

            $('#addType3Product').on('click', function() {
                addType3ProductRow();
            });

            $('#addType5Product').on('click', function() {
                addType5ProductRow();
            });

            // Event handlers for removing products
            $(document).on('click', '.remove-type2-product', function() {
                const index = $(this).data('index');
                $(`.type2-product-row[data-index="${index}"]`).remove();

                $('.type2-product-row').each(function(newIndex) {
                    $(this).attr('data-index', newIndex);
                    $(this).find('.type2-product-select').attr('name',
                        `type2_products[${newIndex}][product_id]`).data('index', newIndex);
                    $(this).find('.type2-quantity').attr('name',
                        `type2_products[${newIndex}][quantity_to_produce]`);
                    $(this).find('.type2-volume').attr('data-index', newIndex);
                    $(this).find('.type2-volume-info').attr('data-index', newIndex);
                    $(this).find('.remove-type2-product').data('index', newIndex);
                });

                if ($('.type2-product-row').length === 0) {
                    $('#noType2ProductsMessage').removeClass('d-none');
                }

                updateType2Calculation();
            });

            $(document).on('click', '.remove-type3-product', function() {
                const index = $(this).data('index');
                $(`.type3-product-row[data-index="${index}"]`).remove();

                $('.type3-product-row').each(function(newIndex) {
                    $(this).attr('data-index', newIndex);
                    $(this).find('.type3-product-select').attr('name',
                        `type3_products[${newIndex}][product_id]`).data('index', newIndex);
                    $(this).find('.type3-quantity').attr('name',
                        `type3_products[${newIndex}][quantity_to_produce]`);
                    $(this).find('.type3-volume').attr('data-index', newIndex);
                    $(this).find('.type3-volume-info').attr('data-index', newIndex);
                    $(this).find('.remove-type3-product').data('index', newIndex);
                });

                if ($('.type3-product-row').length === 0) {
                    $('#noProductsMessage').removeClass('d-none');
                }

                updateType3Calculation();
            });

            $(document).on('click', '.remove-type5-product', function() {
                const index = $(this).data('index');
                $(`.type5-product-row[data-index="${index}"]`).remove();

                $('.type5-product-row').each(function(newIndex) {
                    $(this).attr('data-index', newIndex);
                    $(this).find('.type5-product-select').attr('name',
                        `type5_products[${newIndex}][product_id]`).data('index', newIndex);
                    $(this).find('.type5-quantity').attr('name',
                        `type5_products[${newIndex}][quantity_to_produce]`);
                    $(this).find('.type5-volume').attr('data-index', newIndex);
                    $(this).find('.type5-volume-info').attr('data-index', newIndex);
                    $(this).find('.remove-type5-product').data('index', newIndex);
                });

                if ($('.type5-product-row').length === 0) {
                    $('#noType5ProductsMessage').removeClass('d-none');
                }

                updateType5Calculation();
            });

            // Add sous-bloc row for Type 3
            function addType3SousBlocRow(productData = null) {
                const index = $('.type3-sous-bloc-row').length;
                const decoupageProducts = @json($decoupageProducts->where('product_type', 'decoupage')->values());

                let options = '<option value="">Sélectionner un sous-bloc</option>';
                decoupageProducts.forEach(product => {
                    const selected = productData && productData.product_id == product.product_id ? 'selected' : '';
                    options += `<option value="${product.product_id}" ${selected}>${product.product_code} - ${product.product_name}</option>`;
                });

                const rowHtml = `
                    <div class="type3-sous-bloc-row card mb-2" data-index="${index}">
                        <div class="card-body py-2">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label small">Sous-bloc *</label>
                                    <select class="form-control select2 type3-sous-bloc-select"
                                            name="type3_source_products[${index}][product_id]"
                                            data-index="${index}" required>
                                        ${options}
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Quantité *</label>
                                    <input type="number" class="form-control type3-sous-bloc-quantity"
                                           name="type3_source_products[${index}][quantity]"
                                           value="${productData ? productData.quantity : '1'}"
                                           min="0.01" step="0.01" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Volume/Unité</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control type3-sous-bloc-volume"
                                               data-index="${index}" step="0.0001" min="0" readonly>
                                        <span class="input-group-text">m³</span>
                                    </div>
                                    <small class="form-text text-muted type3-sous-bloc-info" data-index="${index}">
                                        Sélectionnez un sous-bloc
                                    </small>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Action</label>
                                    <button type="button" class="btn btn-sm btn-danger w-100 remove-type3-sous-bloc" data-index="${index}">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('#noSousBlocsMessage').addClass('d-none');
                $('#type3SousBlocsContainer').append(rowHtml);

                $('#type3SousBlocsContainer .type3-sous-bloc-row').last().find('.type3-sous-bloc-select').select2({
                    language: "fr",
                    placeholder: "Sélectionner un sous-bloc",
                    allowClear: true
                });

                // Load familles from first sous-bloc
                if (index === 0 && productData && productData.product_id) {
                    loadFamilles(productData.product_id, 'type3');
                }

                updateType3Calculation();
            }

            $('#addType3SousBloc').on('click', function() {
                addType3SousBlocRow();
            });

            $(document).on('click', '.remove-type3-sous-bloc', function() {
                const index = $(this).data('index');
                $(`.type3-sous-bloc-row[data-index="${index}"]`).remove();

                // Re-index remaining sous-bloc rows
                $('.type3-sous-bloc-row').each(function(newIndex) {
                    $(this).attr('data-index', newIndex);
                    $(this).find('.type3-sous-bloc-select').attr('name',
                        `type3_source_products[${newIndex}][product_id]`).data('index', newIndex);
                    $(this).find('.type3-sous-bloc-quantity').attr('name',
                        `type3_source_products[${newIndex}][quantity]`);
                    $(this).find('.type3-sous-bloc-volume').attr('data-index', newIndex);
                    $(this).find('.type3-sous-bloc-info').attr('data-index', newIndex);
                    $(this).find('.remove-type3-sous-bloc').data('index', newIndex);
                });

                if ($('.type3-sous-bloc-row').length === 0) {
                    $('#noSousBlocsMessage').removeClass('d-none');
                }

                updateType3Calculation();
            });

            $(document).on('change', '.type3-sous-bloc-select', function() {
                const productId = $(this).val();
                const index = $(this).data('index');

                if (productId) {
                    getProductDetails(productId).then(product => {
                        if (product) {
                            $(`.type3-sous-bloc-volume[data-index="${index}"]`).val(product.volume_per_unit.toFixed(4));
                            $(`.type3-sous-bloc-info[data-index="${index}"]`).text(
                                `${product.display_volume} (${product.dimensions})`
                            );
                        }
                        updateType3Calculation();
                    });
                    // Load familles from first sous-bloc only
                    if (index === 0) {
                        loadFamilles(productId, 'type3');
                    }
                } else {
                    $(`.type3-sous-bloc-volume[data-index="${index}"]`).val('');
                    $(`.type3-sous-bloc-info[data-index="${index}"]`).text('Sélectionnez un sous-bloc');
                    updateType3Calculation();
                }
            });

            $(document).on('input', '.type3-sous-bloc-quantity', function() {
                updateType3Calculation();
            });

            // Event handlers for product selection
            $(document).on('change', '.type2-product-select', function() {
                const productId = $(this).val();
                const index = $(this).data('index');

                if (productId) {
                    getProductDetails(productId).then(product => {
                        if (product) {
                            $(`.type2-volume[data-index="${index}"]`).val(product.volume_per_unit
                                .toFixed(4));
                            $(`.type2-volume-info[data-index="${index}"]`).text(
                                `${product.display_volume} (${product.dimensions})`
                            );
                        }
                        updateType2Calculation();
                    });
                } else {
                    $(`.type2-volume[data-index="${index}"]`).val('');
                    $(`.type2-volume-info[data-index="${index}"]`).text(
                        'Sélectionnez un produit pour voir son volume');
                    updateType2Calculation();
                }
            });

            $(document).on('change', '.type3-product-select', function() {
                const productId = $(this).val();
                const index = $(this).data('index');

                if (productId) {
                    getProductDetails(productId).then(product => {
                        if (product) {
                            $(`.type3-volume[data-index="${index}"]`).val(product.volume_per_unit
                                .toFixed(4));
                            $(`.type3-volume-info[data-index="${index}"]`).text(
                                `${product.display_volume} (${product.dimensions})`
                            );
                        }
                        updateType3Calculation();
                    });
                } else {
                    $(`.type3-volume[data-index="${index}"]`).val('');
                    $(`.type3-volume-info[data-index="${index}"]`).text(
                        'Sélectionnez un produit pour voir son volume');
                    updateType3Calculation();
                }
            });

            $(document).on('change', '.type5-product-select', function() {
                const productId = $(this).val();
                const index = $(this).data('index');

                if (productId) {
                    getProductDetails(productId).then(product => {
                        if (product) {
                            $(`.type5-volume[data-index="${index}"]`).val(product.volume_per_unit
                                .toFixed(4));
                            $(`.type5-volume-info[data-index="${index}"]`).text(
                                `${product.display_volume} (${product.dimensions})`
                            );
                        }
                        updateType5Calculation();
                    });
                    // Load destination famille from the first product row only
                    if (index === 0) {
                        loadFamilles(productId, 'type5');
                    }
                } else {
                    $(`.type5-volume[data-index="${index}"]`).val('');
                    $(`.type5-volume-info[data-index="${index}"]`).text(
                        'Sélectionnez un produit pour voir son volume');
                    updateType5Calculation();
                }
            });

            $(document).on('input', '.type2-quantity', function() {
                updateType2Calculation();
            });

            $(document).on('input', '.type3-quantity', function() {
                updateType3Calculation();
            });

            $(document).on('input', '.type5-quantity', function() {
                updateType5Calculation();
            });

            $('#type2_total_blocks').on('input', function() {
                updateType2Calculation();
            });

            // Function to add Type 4 source product row
            function addType4SourceRow(productData = null) {
                const index = $('.type4-source-row').length;

                const salesProducts = @json($salesProducts);

                let options = '<option value="">Sélectionner un produit vente source</option>';
                salesProducts.forEach(product => {
                    if (product.product_type === 'finale' || product.product_type === 'both') {
                        const selected = productData && productData.product_id == product.product_id ?
                            'selected' : '';
                        options += `<option value="${product.product_id}" ${selected}>
                ${product.product_code} - ${product.product_name}
            </option>`;
                    }
                });

                const rowHtml = `
        <div class="type4-source-row card mb-3" data-index="${index}">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="form-label">Produit Source *</label>
                            <select class="form-control select2 type4-source-select"
                                name="type4_source_products[${index}][product_id]"
                                data-index="${index}" required>
                                ${options}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Quantité à Utiliser *</label>
                            <input type="number" class="form-control type4-source-quantity"
                                name="type4_source_products[${index}][quantity_to_use]"
                                value="${productData ? productData.quantity_to_use : '1'}"
                                min="0.01" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Volume/Unité</label>
                            <div class="input-group">
                                <input type="number" class="form-control type4-source-volume"
                                    data-index="${index}"
                                    step="0.0001" min="0" readonly>
                                <span class="input-group-text">m³</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label class="form-label">Actions</label>
                            <button type="button" class="btn btn-sm btn-danger w-100 remove-type4-source" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <small class="form-text text-muted type4-source-info" data-index="${index}">
                            Sélectionnez un produit pour voir ses dimensions
                        </small>
                    </div>
                </div>
            </div>
        </div>
    `;

                $('#noType4SourceProductsMessage').addClass('d-none');
                $('#type4SourceProductsContainer').append(rowHtml);

                $(`select[name="type4_source_products[${index}][product_id]"]`).select2({
                    language: "fr",
                    placeholder: "Sélectionner un produit",
                    allowClear: true
                });

                updateType4Calculation();
            }

            // Function to add Type 4 target product row
            function addType4TargetRow(productData = null) {
                const index = $('.type4-target-row').length;

                const salesProducts = @json($salesProducts);

                let options = '<option value="">Sélectionner un produit vente cible</option>';
                salesProducts.forEach(product => {
                    if (product.product_type === 'finale' || product.product_type === 'both') {
                        const selected = productData && productData.product_id == product.product_id ?
                            'selected' : '';
                        options += `<option value="${product.product_id}" ${selected}>
                ${product.product_code} - ${product.product_name}
            </option>`;
                    }
                });

                const rowHtml = `
                    <div class="type4-target-row card mb-3" data-index="${index}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label class="form-label">Produit Cible *</label>
                                        <select class="form-control select2 type4-target-select"
                                            name="type4_target_products[${index}][product_id]"
                                            data-index="${index}" required>
                                            ${options}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Quantité à Produire *</label>
                                        <input type="number" class="form-control type4-target-quantity"
                                            name="type4_target_products[${index}][quantity_to_produce]"
                                            value="${productData ? productData.quantity_to_produce : ''}"
                                            min="0.01" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Volume/Unité</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control type4-target-volume"
                                                data-index="${index}"
                                                step="0.0001" min="0" readonly>
                                            <span class="input-group-text">m³</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label">Actions</label>
                                        <button type="button" class="btn btn-sm btn-danger w-100 remove-type4-target" data-index="${index}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <small class="form-text text-muted type4-target-info" data-index="${index}">
                                        Sélectionnez un produit pour voir ses dimensions
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('#noType4TargetProductsMessage').addClass('d-none');
                $('#type4TargetProductsContainer').append(rowHtml);

                $(`select[name="type4_target_products[${index}][product_id]"]`).select2({
                    language: "fr",
                    placeholder: "Sélectionner un produit",
                    allowClear: true
                });

                updateType4Calculation();
            }

            function addType4ProductRow(productData = null) {
                const index = $('.type4-product-row').length;

                const salesProducts = @json($salesProducts);

                let options = '<option value="">Sélectionner un produit vente cible</option>';
                salesProducts.forEach(product => {
                    if (product.product_type === 'finale' || product.product_type === 'both') {
                        const selected = productData && productData.product_id == product.product_id ?
                            'selected' : '';
                        options += `<option value="${product.product_id}" ${selected}>
                ${product.product_code} - ${product.product_name}
            </option>`;
                    }
                });

                const rowHtml = `
                    <div class="type4-product-row card mb-3" data-index="${index}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label class="form-label">Produit Cible *</label>
                                        <select class="form-control select2 type4-product-select"
                                            name="type4_products[${index}][product_id]"
                                            data-index="${index}" required>
                                            ${options}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Quantité à Produire *</label>
                                        <input type="number" class="form-control type4-quantity"
                                            name="type4_products[${index}][quantity_to_produce]"
                                            value="${productData ? productData.quantity_to_produce : ''}"
                                            min="0.01" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Volume/Unité</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control type4-volume"
                                                data-index="${index}"
                                                step="0.0001" min="0" readonly>
                                            <span class="input-group-text">m³</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label">Actions</label>
                                        <button type="button" class="btn btn-sm btn-danger w-100 remove-type4-product" data-index="${index}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <small class="form-text text-muted type4-volume-info" data-index="${index}">
                                        Sélectionnez un produit pour voir ses dimensions
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('#noType4ProductsMessage').addClass('d-none');
                $('#type4ProductsContainer').append(rowHtml);

                $(`select[name="type4_products[${index}][product_id]"]`).select2({
                    language: "fr",
                    placeholder: "Sélectionner un produit",
                    allowClear: true
                });

                updateType4Calculation();
            }

            // Update Type 4 source details
            function updateType4SourceDetails() {
                const productId = $('#type4_source_product_id').val();
                const totalUnits = parseFloat($('#type4_total_units').val()) || 0;

                if (productId) {
                    getProductDetails(productId).then(product => {
                        if (product) {
                            const volumePerUnit = product.volume_per_unit;
                            const totalVolume = volumePerUnit * totalUnits;

                            $('#type4_source_volume').val(volumePerUnit.toFixed(4));
                            $('#type4_source_volume_info').text(
                                `${product.display_volume} (${product.dimensions})`);
                            $('#type4_total_source_volume').val(totalVolume.toFixed(4));

                            updateType4Calculation();
                            checkType4StockAvailability();
                        }
                    });
                }
            }

            $(document).on('input', '.type4-quantity', function() {
                updateType4Calculation();
            });

            $('#type4_source_product_id').on('change', function() {
                updateType4SourceDetails();
                loadType4Familles();
            });

            $('#type4_total_units').on('input', function() {
                updateType4SourceDetails();
                checkType4StockAvailability();
            });

            $('#addType4Product').on('click', function() {
                addType4ProductRow();
            });

            $(document).on('change', '.type4-product-select', function() {
                const productId = $(this).val();
                const index = $(this).data('index');

                if (productId) {
                    getProductDetails(productId).then(product => {
                        if (product) {
                            $(`.type4-volume[data-index="${index}"]`).val(product.volume_per_unit
                                .toFixed(4));
                            $(`.type4-volume-info[data-index="${index}"]`).text(
                                `${product.display_volume} (${product.dimensions})`
                            );
                            updateType4Calculation();
                        }
                    });
                } else {
                    $(`.type4-volume[data-index="${index}"]`).val('');
                    $(`.type4-volume-info[data-index="${index}"]`).text(
                        'Sélectionnez un produit pour voir ses dimensions');
                    updateType4Calculation();
                }
            });

            function loadType4Familles() {
                const sourceProductId = $('#type4_source_product_id').val();

                if (sourceProductId) {
                    $.ajax({
                        url: "{{ route('production-orders.get-familles') }}",
                        type: "GET",
                        data: {
                            product_id: sourceProductId,
                            famille_type: 'source'
                        },
                        success: function(response) {
                            if (response.success && response.html) {
                                const tempDiv = $('<div>').html(response.html);
                                const selectHtml = tempDiv.find('select').prop('outerHTML');
                                const newSelect = $(selectHtml);
                                newSelect.attr('id', 'type4_famille_id');
                                newSelect.attr('name', 'famille_id');
                                newSelect.find('option').each(function() {
                                    if ($(this).val() === '') {
                                        $(this).text('Sélectionner la famille...');
                                    }
                                });
                                $('#type4_famille_id').replaceWith(newSelect);
                                if (editFamillePreselectId) {
                                    $('#type4_famille_id').val(String(editFamillePreselectId));
                                    editFamillePreselectId = null;
                                }
                                $('#type4_famille_id').select2({
                                    language: "fr",
                                    placeholder: "Sélectionner la famille",
                                    allowClear: false
                                });

                                // Trigger stock check after famille is loaded
                                if ($('#type4_famille_id').val()) {
                                    checkType4StockAvailability();
                                }
                            }
                        }
                    });
                }
            }
            async function checkType4StockAvailability() {
                const sourceProductId = $('#type4_source_product_id').val();
                const sourceQuantity = parseFloat($('#type4_total_units').val()) || 0;
                const familleId = $('#type4_famille_id').val();

                if (!sourceProductId || sourceQuantity === 0) {
                    $('#type4InsufficientStockAlert').addClass('d-none');
                    return;
                }

                const stockInfo = await getStockInfo(sourceProductId, familleId);
                const isSufficient = stockInfo.available >= sourceQuantity;

                if (!isSufficient && stockInfo.available > 0) {
                    $('#type4InsufficientStockList').html(`
            <ul class="mb-0 mt-2">
                <li>Produit source: Requis ${sourceQuantity} unités, Disponible ${stockInfo.available} unités</li>
            </ul>
        `);
                    $('#type4InsufficientStockAlert').removeClass('d-none');
                } else if (stockInfo.available === 0 && sourceQuantity > 0) {
                    $('#type4InsufficientStockList').html(`
            <ul class="mb-0 mt-2">
                <li>Produit source: Stock vide (0 unités disponible)</li>
            </ul>
        `);
                    $('#type4InsufficientStockAlert').removeClass('d-none');
                } else {
                    $('#type4InsufficientStockAlert').addClass('d-none');
                }
            }

            // Update Type 4 calculation
            function updateType4Calculation() {
                const sourceProductId = $('#type4_source_product_id').val();
                const totalUnitsRequired = parseFloat($('#type4_total_units').val()) || 0;
                const sourceVolumePerUnit = parseFloat($('#type4_source_volume').val()) || 0;
                const totalSourceVolume = totalUnitsRequired * sourceVolumePerUnit;
                const familleId = $('#type4_famille_id').val();

                let totalFinalProducts = 0;
                let totalVolume = 0;

                $('.type4-product-row').each(function() {
                    const quantityToProduce = parseFloat($(this).find('.type4-quantity').val()) || 0;
                    const volumePerUnit = parseFloat($(this).find('.type4-volume').val()) || 0;

                    totalFinalProducts += quantityToProduce;
                    totalVolume += quantityToProduce * volumePerUnit;
                });

                $('#type4_total_volume').val(totalVolume.toFixed(4));

                const wasteVolume = totalSourceVolume - totalVolume;

                // Check if target volume exceeds source volume
                if (totalVolume > totalSourceVolume && totalSourceVolume > 0) {
                    const deficit = totalVolume - totalSourceVolume;
                    $('#type4VolumeExceedMessage').html(`
            <strong>⚠️ Le volume total des produits (${totalVolume.toFixed(4)} m³)
            dépasse le volume source disponible (${totalSourceVolume.toFixed(4)} m³).</strong>
            <br>Déficit: ${deficit.toFixed(4)} m³
            <br>Veuillez réduire les quantités.
        `);
                    $('#type4VolumeExceedAlert').removeClass('d-none');
                    $('#type4_waste_volume').val('');
                    $('#type4_waste_info').html('<span class="text-danger">❌ Volume source insuffisant!</span>');
                } else {
                    $('#type4VolumeExceedAlert').addClass('d-none');
                    $('#type4_waste_volume').val(wasteVolume.toFixed(4));

                    if (wasteVolume > 0) {
                        const wastePercentage = (wasteVolume / totalSourceVolume * 100).toFixed(2);
                        $('#type4_waste_info').html(
                            `<span class="text-warning">⚠️ Chute estimée: ${wasteVolume.toFixed(4)} m³ (${wastePercentage}%)</span>`
                        );
                    } else if (wasteVolume === 0 && totalSourceVolume > 0) {
                        $('#type4_waste_info').html(
                            `<span class="text-success">✓ Aucune chute (volume parfaitement optimisé)</span>`
                        );
                    }
                }

                $('#actual_waste_percentage').val(wasteVolume > 0 ? (wasteVolume / totalSourceVolume * 100).toFixed(
                    2) : 0);
                $('#actual_total_source_volume').val(totalSourceVolume);
                $('#actual_total_produced_volume').val(totalVolume);
                $('#actual_quantity_to_produce').val(totalFinalProducts);
                $('#actual_required_quantity').val(totalUnitsRequired);

                if (sourceProductId && familleId) {
                    getStockInfo(sourceProductId, familleId).then(stockInfo => {
                        const isSufficient = stockInfo.available >= totalUnitsRequired;

                        if (!isSufficient && stockInfo.available > 0) {
                            $('#type4InsufficientStockList').html(`
                    <ul class="mb-0 mt-2">
                        <li>Produit source: Requis ${totalUnitsRequired} unités, Disponible ${stockInfo.available} unités</li>
                    </ul>
                `);
                            $('#type4InsufficientStockAlert').removeClass('d-none');
                        } else if (stockInfo.available === 0 && totalUnitsRequired > 0) {
                            $('#type4InsufficientStockList').html(`
                    <ul class="mb-0 mt-2">
                        <li>Produit source: Stock vide (0 unités disponible)</li>
                    </ul>
                `);
                            $('#type4InsufficientStockAlert').removeClass('d-none');
                        } else {
                            $('#type4InsufficientStockAlert').addClass('d-none');
                        }
                    });
                }
            }



            // Event handlers for Type 4
            $(document).on('change', '.type4-source-select', function() {
                const productId = $(this).val();
                const index = $(this).data('index');

                if (productId) {
                    getProductDetails(productId).then(product => {
                        if (product) {
                            $(`.type4-source-volume[data-index="${index}"]`).val(product
                                .volume_per_unit.toFixed(4));
                            $(`.type4-source-info[data-index="${index}"]`).text(
                                `${product.display_volume} (${product.dimensions})`
                            );
                            updateType4Calculation();
                        }
                    });
                }
            });

            $(document).on('change', '.type4-target-select', function() {
                const productId = $(this).val();
                const index = $(this).data('index');

                if (productId) {
                    getProductDetails(productId).then(product => {
                        if (product) {
                            $(`.type4-target-volume[data-index="${index}"]`).val(product
                                .volume_per_unit.toFixed(4));
                            $(`.type4-target-info[data-index="${index}"]`).text(
                                `${product.display_volume} (${product.dimensions})`
                            );
                            updateType4Calculation();
                        }
                    });
                } else {
                    $(`.type4-target-volume[data-index="${index}"]`).val('');
                    $(`.type4-target-info[data-index="${index}"]`).text(
                        'Sélectionnez un produit pour voir ses dimensions');
                    updateType4Calculation();
                }
            });


            $(document).on('input', '.type4-source-quantity, .type4-target-quantity', function() {
                updateType4Calculation();
            });

            $(document).on('input', '.type4-target-quantity', function() {
                updateType4Calculation();
            });

            $('#addType4TargetProduct').on('click', function() {
                addType4TargetRow();
            });

            // Remove handlers
            $(document).on('click', '.remove-type4-product', function() {
                const index = $(this).data('index');
                $(`.type4-product-row[data-index="${index}"]`).remove();

                $('.type4-product-row').each(function(newIndex) {
                    $(this).attr('data-index', newIndex);
                    $(this).find('.type4-product-select').attr('name',
                        `type4_products[${newIndex}][product_id]`).data('index', newIndex);
                    $(this).find('.type4-quantity').attr('name',
                        `type4_products[${newIndex}][quantity_to_produce]`);
                    $(this).find('.type4-volume').attr('data-index', newIndex);
                    $(this).find('.type4-volume-info').attr('data-index', newIndex);
                    $(this).find('.remove-type4-product').data('index', newIndex);
                });

                if ($('.type4-product-row').length === 0) {
                    $('#noType4ProductsMessage').removeClass('d-none');
                }

                updateType4Calculation();
            });

            $(document).on('click', '.remove-type4-target', function() {
                const index = $(this).data('index');
                $(`.type4-target-row[data-index="${index}"]`).remove();

                $('.type4-target-row').each(function(newIndex) {
                    $(this).attr('data-index', newIndex);
                    $(this).find('.type4-target-select').attr('name',
                        `type4_target_products[${newIndex}][product_id]`).data('index',
                        newIndex);
                    $(this).find('.type4-target-quantity').attr('name',
                        `type4_target_products[${newIndex}][quantity_to_produce]`);
                    $(this).find('.type4-target-volume').attr('data-index', newIndex);
                    $(this).find('.type4-target-info').attr('data-index', newIndex);
                    $(this).find('.remove-type4-target').data('index', newIndex);
                });

                if ($('.type4-target-row').length === 0) {
                    $('#noType4TargetProductsMessage').removeClass('d-none');
                }

                updateType4Calculation();
            });

            function updateMaterialSourceSections() {
                const materialSource = $('input[name="material_source"]:checked').val();
                $('#actual_material_source').val(materialSource);

                if (materialSource === 'bom_only') {
                    $('#chutesVolumeSection').addClass('d-none');
                    $('#actual_bom_percentage').val('100');
                    $('#actual_chutes_volume').val('0');
                } else if (materialSource === 'chutes_only') {
                    $('#chutesVolumeSection').removeClass('d-none');
                    $('#actual_bom_percentage').val('0');
                    $('#actual_chutes_volume').val($('#chutes_volume').val() || '0');
                    checkChutesStock();
                } else if (materialSource === 'both') {
                    $('#chutesVolumeSection').removeClass('d-none');
                    $('#actual_bom_percentage').val('0');
                    $('#actual_chutes_volume').val($('#chutes_volume').val() || '0');
                    checkChutesStock();
                }

                const productId = $('#type1_product_id').val();
                const quantity = $('#type1_quantity').val() || 1;
                // if (productId && quantity >= 1) {
                //     loadBOM(productId, quantity);
                // }
            }

            function toggleProductionTypeSections(productionType) {
                // Hide all sections first
                $('#type1Section').addClass('d-none');
                $('#type2Section').addClass('d-none');
                $('#type3Section').addClass('d-none');
                $('#type4Section').addClass('d-none');
                $('#type5Section').addClass('d-none');
                $('#bomCard').addClass('d-none');
                $('#conversionSection').addClass('d-none');
                $('#calculatedResultsSection').addClass('d-none');

                // Clear containers
                $('#familleContainer').empty();
                $('#insufficientStockAlert').addClass('d-none');
                $('#noBomAlert').addClass('d-none');
                $('#type2VolumeExceedAlert').addClass('d-none');
                $('#type3VolumeExceedAlert').addClass('d-none');
                $('#type4VolumeExceedAlert').addClass('d-none');
                $('#type4InsufficientStockAlert').addClass('d-none');
                $('#type5VolumeExceedAlert').addClass('d-none');

                // Remove required attributes from all Type 4 fields
                $('#type4_source_product_id, #type4_source_quantity, #type4_famille_id').removeAttr('required');
                $('#type4_source_product_id, #type4_famille_id').prop('disabled', false);

                // Reset BOM table with empty state message
                $('#bomTableBody').html(`
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="fas fa-box-open me-2"></i>
                            Aucune matière première. Utilisez "Charger la nomenclature" pour charger les matières du produit, ou "Ajouter" pour ajouter manuellement.
                        </td>
                    </tr>
                `);

                $('#bomTableFooter').html('');
                $('#actual_total_cost').val(0);

                // Reset Type 2 products container
                $('#type2ProductsContainer').html(`
                    <div class="alert alert-info" id="noType2ProductsMessage">
                        Cliquez sur "Ajouter un Produit" pour ajouter des produits découpage
                    </div>
                `);

                // Reset Type 3 containers
                $('#type3SousBlocsContainer').html(`
                    <div class="alert alert-info" id="noSousBlocsMessage">
                        Cliquez sur "Ajouter un Sous-bloc" pour ajouter des sous-blocs sources
                    </div>
                `);
                $('#type3ProductsContainer').html(`
                    <div class="alert alert-info" id="noProductsMessage">
                        Cliquez sur "Ajouter un Produit" pour ajouter des produits finaux
                    </div>
                `);

                // Reset Type 4 containers
                $('#type4SourceProductsContainer').html(`
                    <div class="alert alert-info" id="noType4SourceProductsMessage">
                        Cliquez sur "Ajouter un produit source" pour sélectionner les produits à transformer
                    </div>
                `);
                $('#type4TargetProductsContainer').html(`
                    <div class="alert alert-info" id="noType4TargetProductsMessage">
                        Cliquez sur "Ajouter un produit cible" pour ajouter les produits à produire
                    </div>
                `);

                // Reset Type 5 container
                $('#type5ProductsContainer').html(`
                    <div class="alert alert-info" id="noType5ProductsMessage">
                        Cliquez sur "Ajouter un Produit" pour ajouter des produits finaux
                    </div>
                `);
                $('#type5_chutes_volume').val('');
                $('#type5_chutes_volume_display').val('');
                $('#type5ChutesStockInfo').html('');

                // Reset input values
                $('#type2_total_blocks').val('');
                $('#type2_total_decoupage_products').val('');
                $('#type2_total_volume').val('');
                $('#type3_total_final_products').val('');
                $('#type3_total_volume').val('');
                $('#type4_total_source_volume').val('');
                $('#type4_total_target_volume').val('');
                $('#type4_waste_volume').val('');
                $('#type5_total_volume').val('');
                $('#type5_waste_volume').val('');
                $('#type5_waste_info').html('');

                // Reset hidden fields
                $('#actual_product_id').val('');
                $('#actual_source_product_id').val('');
                $('#actual_quantity_to_produce').val('');
                $('#actual_required_quantity').val('');
                $('#actual_decoupage_ratio').val('');
                $('#actual_conversion_rate').val('');
                $('#actual_waste_percentage').val('0');
                $('#actual_material_source').val('bom_only');
                $('#actual_bom_percentage').val('100');
                $('#actual_chutes_volume').val('0');
                $('#actual_total_cost').val('0');
                $('#actual_total_source_volume').val('0');
                $('#actual_total_produced_volume').val('0');

                $('#bomOnly').prop('checked', true);
                updateMaterialSourceSections();

                if (productionType === 'type1') {
                    $('#type1Section').removeClass('d-none');

                    $('#type4_source_product_id, #type4_source_quantity, #type4_famille_id').removeAttr('required');

                    const productId = $('#type1_product_id').val();
                    const quantity = $('#type1_quantity').val() || 1;

                    if (productId) {
                        loadFamilles(productId, 'type1');
                        updateVolumeCalculations('type1');
                        checkChutesStock();
                        $('#bomCard').removeClass('d-none');
                    }

                } else if (productionType === 'type2') {
                    $('#type2Section').removeClass('d-none');
                    $('#bomCard').addClass('d-none');
                    $('#noBomAlert').addClass('d-none');

                    $('#type4_source_product_id, #type4_source_quantity, #type4_famille_id').removeAttr('required');

                    $('.type2-product-row').remove();
                    addType2ProductRow();

                    const sourceProductId = $('#type2_source_product_id').val();

                    if (sourceProductId) {
                        loadFamilles(sourceProductId, 'type2');
                        updateType2Calculation();
                        updateVolumeCalculations('type2');
                    } else {
                        updateType2Calculation();
                    }

                } else if (productionType === 'type3') {
                    $('#type3Section').removeClass('d-none');
                    $('#bomCard').addClass('d-none');
                    $('#noBomAlert').addClass('d-none');

                    $('#type4_source_product_id, #type4_source_quantity, #type4_famille_id').removeAttr('required');

                    $('.type3-sous-bloc-row').remove();
                    $('.type3-product-row').remove();
                    addType3SousBlocRow();
                    addType3ProductRow();

                    updateType3Calculation();

                } else if (productionType === 'type4') {
                    $('#type4Section').removeClass('d-none');
                    $('#bomCard').addClass('d-none');

                    $('#type4ProductsContainer').html(`
                        <div class="alert alert-info" id="noType4ProductsMessage">
                            Cliquez sur "Ajouter un Produit" pour ajouter des produits
                        </div>
                    `);

                    $('.type4-product-row').remove();
                    addType4ProductRow();

                    if ($('#type4_source_product_id').val()) {
                        updateType4SourceDetails();
                        loadType4Familles();
                    }

                    updateType4Calculation();

                } else if (productionType === 'type5') {
                    $('#type5Section').removeClass('d-none');
                    $('#bomCard').addClass('d-none');
                    $('#noBomAlert').addClass('d-none');

                    $('#type4_source_product_id, #type4_source_quantity, #type4_famille_id').removeAttr('required');

                    $('.type5-product-row').remove();
                    addType5ProductRow();

                    updateType5Calculation();
                }
            }

            $('#type1_product_id').change(function() {
                const productId = $(this).val();
                if (productId) {
                    loadFamilles(productId, 'type1');

                    $('#bomTableBody').html(`
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-box-open me-2"></i>
                                Aucune matière première. Cliquez sur "Charger la nomenclature" pour charger les matières du produit.
                            </td>
                        </tr>
                    `);
                    $('#bomTableFooter').html('');
                    $('#bomCard').removeClass('d-none');
                    updateVolumeCalculations('type1');
                    checkChutesStock();
                } else {
                    $('#familleContainer').empty();
                    $('#bomCard').addClass('d-none');
                }
            });

            // $('#loadBomBtn').on('click', function() {
            //     const productId = $('#type1_product_id').val();
            //     const quantity = $('#type1_quantity').val() || 1;

            //     if (!productId) {
            //         showToast('error', 'Veuillez d\'abord sélectionner un produit');
            //         return;
            //     }

            //     if (confirm('Charger la nomenclature va remplacer les matières actuelles. Continuer ?')) {
            //         loadBOM(productId, quantity);
            //     }
            // });

            $('#clearBomBtn').on('click', function() {
                if (confirm(
                        'Vider la nomenclature va supprimer toutes les matières actuelles. Continuer ?')) {
                    $('#bomTableBody').html(`
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="fas fa-box-open me-2"></i>
                            Aucune matière première. Utilisez "Charger la nomenclature" pour charger les matières du produit, ou "Ajouter" pour ajouter manuellement.
                        </td>
                    </tr>
                `);
                    $('#bomTableFooter').html('');
                    $('#actual_total_cost').val(0);

                    $('input[name^="bom_consumptions"]').remove();

                    showToast('success', 'Nomenclature vidée avec succès');
                }
            });

            $('#type1_quantity').on('input', function() {
                const productId = $('#type1_product_id').val();
                const quantity = $(this).val();
                if (productId && quantity >= 1) {
                    // loadBOM(productId, quantity);
                    updateVolumeCalculations('type1');
                }
            });

            $('#type2_source_product_id').change(function() {
                const productId = $(this).val();

                $('#source_famille_id').val('').trigger('change');
                $('#familleContainer').empty();

                if (productId) {
                    loadFamilles(productId, 'type2');
                    updateType2Calculation();
                    updateVolumeCalculations('type2');
                } else {
                    $('#calculatedResultsSection').addClass('d-none');
                    $('#insufficientStockAlert').addClass('d-none');
                }
            });


            $('#productionOrderForm').submit(function(e) {
                e.preventDefault();

                const productionType = $('input[name="production_type"]:checked').val();
                let hasValidationError = false;

                // First, remove required attributes from all Type 4 fields to prevent browser validation
                $('#type4_source_product_id, #type4_source_quantity, #type4_famille_id').removeAttr(
                    'required');

                if (productionType === 'type1') {
                    const productId = $('#type1_product_id').val();
                    const quantity = $('#type1_quantity').val();
                    const materialSource = $('input[name="material_source"]:checked').val();

                    if (!productId) {
                        showToast('error', 'Veuillez sélectionner un produit à produire');
                        hasValidationError = true;
                    }
                    if (!quantity || parseFloat(quantity) < 0.01) {
                        showToast('error', 'Veuillez saisir une quantité valide');
                        hasValidationError = true;
                    }

                    if (materialSource === 'chutes_only') {
                        const chutesVolume = $('#chutes_volume').val() || 0;
                        if (!chutesVolume || chutesVolume <= 0) {
                            showToast('error', 'Veuillez saisir un volume de chutes valide');
                            hasValidationError = true;
                        }
                    }
                } else if (productionType === 'type2') {
                    // Add required attributes back for Type 2 validation
                    const sourceProductId = $('#type2_source_product_id').val();
                    const productRows = $('.type2-product-row');
                    const totalBlocksRequired = $('#type2_total_blocks').val();

                    const totalVolume = parseFloat($('#type2_total_volume').val()) || 0;
                    const sourceProduct = $('#type2_source_product_id option:selected').text();

                    let sourceVolumePerUnit = 0;
                    let sourceVolumeTotal = 0;

                    const sourceVolumeValue = $('#type2_source_volume').val();
                    if (sourceVolumeValue && totalBlocksRequired) {
                        sourceVolumePerUnit = parseFloat(sourceVolumeValue) || 0;
                        sourceVolumeTotal = sourceVolumePerUnit * totalBlocksRequired;
                    }

                    if (!sourceProductId) {
                        showToast('error', 'Veuillez sélectionner un produit source');
                        hasValidationError = true;
                    }

                    if (productRows.length === 0) {
                        showToast('error', 'Veuillez ajouter au moins un produit découpage');
                        hasValidationError = true;
                    }

                    if (!totalBlocksRequired || parseFloat(totalBlocksRequired) < 0.01) {
                        showToast('error', 'Veuillez saisir le nombre total de blocs requis');
                        hasValidationError = true;
                    }

                    if (sourceVolumeTotal > 0 && totalVolume > sourceVolumeTotal) {
                        showToast('error',
                            `Le volume total des produits (${totalVolume.toFixed(4)} m³) dépasse le volume source disponible (${sourceVolumeTotal.toFixed(4)} m³). ` +
                            `Veuillez réduire la quantité à produire ou augmenter le nombre de blocs.`
                        );
                        hasValidationError = true;
                    }

                    productRows.each(function(index) {
                        const productSelect = $(this).find('.type2-product-select');
                        const quantityToProduce = $(this).find('.type2-quantity').val();

                        if (!productSelect.val()) {
                            showToast('error',
                                'Veuillez sélectionner un produit découpage pour la ligne ' + (
                                    index + 1));
                            hasValidationError = true;
                        }
                        if (!quantityToProduce || parseFloat(quantityToProduce) < 0.01) {
                            showToast('error',
                                'Veuillez saisir une quantité à produire valide pour la ligne ' +
                                (index + 1));
                            hasValidationError = true;
                        }
                    });
                } else if (productionType === 'type3') {
                    const sousBlocRows = $('.type3-sous-bloc-row');
                    const productRows = $('.type3-product-row');
                    const totalVolume = parseFloat($('#type3_total_volume').val()) || 0;
                    const totalSourceVolume = parseFloat($('#type3_total_source_volume').val()) || 0;

                    if (sousBlocRows.length === 0) {
                        showToast('error', 'Veuillez ajouter au moins un sous-bloc source');
                        hasValidationError = true;
                    }

                    if (productRows.length === 0) {
                        showToast('error', 'Veuillez ajouter au moins un produit final');
                        hasValidationError = true;
                    }

                    if (totalSourceVolume > 0 && totalVolume > totalSourceVolume) {
                        showToast('error',
                            `Le volume total des produits finaux (${totalVolume.toFixed(4)} m³) dépasse le volume source disponible (${totalSourceVolume.toFixed(4)} m³). ` +
                            `Veuillez réduire les quantités ou ajouter des sous-blocs.`
                        );
                        hasValidationError = true;
                    }

                    sousBlocRows.each(function(index) {
                        const select = $(this).find('.type3-sous-bloc-select');
                        const qty = $(this).find('.type3-sous-bloc-quantity').val();
                        if (!select.val()) {
                            showToast('error', 'Veuillez sélectionner un sous-bloc pour la ligne ' + (index + 1));
                            hasValidationError = true;
                        }
                        if (!qty || parseFloat(qty) < 0.01) {
                            showToast('error', 'Veuillez saisir une quantité valide pour le sous-bloc ligne ' + (index + 1));
                            hasValidationError = true;
                        }
                    });

                    productRows.each(function(index) {
                        const productSelect = $(this).find('.type3-product-select');
                        const quantityToProduce = $(this).find('.type3-quantity').val();

                        if (!productSelect.val()) {
                            showToast('error',
                                'Veuillez sélectionner un produit final pour la ligne ' + (index + 1));
                            hasValidationError = true;
                        }
                        if (!quantityToProduce || parseFloat(quantityToProduce) < 0.01) {
                            showToast('error',
                                'Veuillez saisir une quantité à produire valide pour la ligne ' + (index + 1));
                            hasValidationError = true;
                        }
                    });
                } else if (productionType === 'type4') {
                    const sourceProductId = $('#type4_source_product_id').val();
                    const totalUnitsRequired = $('#type4_total_units').val();
                    const familleId = $('#type4_famille_id').val();
                    const productRows = $('.type4-product-row');

                    const totalVolume = parseFloat($('#type4_total_volume').val()) || 0;
                    const totalSourceVolume = parseFloat($('#type4_total_source_volume').val()) || 0;

                    if (!sourceProductId) {
                        showToast('error', 'Veuillez sélectionner un produit source');
                        hasValidationError = true;
                    }

                    if (sourceProductId) {
                        $('input[name="source_product_id"]').remove();
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'source_product_id',
                            value: sourceProductId
                        }).appendTo('#productionOrderForm');
                    }

                    if (!totalUnitsRequired || parseFloat(totalUnitsRequired) < 0.01) {
                        showToast('error', 'Veuillez saisir le nombre total d\'unités requis');
                        hasValidationError = true;
                    }

                    if (!familleId) {
                        showToast('error', 'Veuillez sélectionner une famille');
                        hasValidationError = true;
                    }

                    if (productRows.length === 0) {
                        showToast('error', 'Veuillez ajouter au moins un produit');
                        hasValidationError = true;
                    }

                    if (totalVolume > totalSourceVolume && totalSourceVolume > 0) {
                        showToast('error',
                            `Le volume total des produits (${totalVolume.toFixed(4)} m³) dépasse le volume source disponible (${totalSourceVolume.toFixed(4)} m³).`
                        );
                        hasValidationError = true;
                    }

                    productRows.each(function(index) {
                        const productSelect = $(this).find('.type4-product-select');
                        const quantityToProduce = $(this).find('.type4-quantity').val();

                        if (!productSelect.val()) {
                            showToast('error', 'Veuillez sélectionner un produit pour la ligne ' + (
                                index + 1));
                            hasValidationError = true;
                        }
                        if (!quantityToProduce || parseFloat(quantityToProduce) < 0.01) {
                            showToast('error',
                                'Veuillez saisir une quantité à produire valide pour la ligne ' +
                                (index + 1));
                            hasValidationError = true;
                        }
                    });
                } else if (productionType === 'type5') {
                    const chutesVolume = parseFloat($('#type5_chutes_volume').val()) || 0;
                    const familleId = $('#famille_id').val();
                    const productRows = $('.type5-product-row');
                    const totalVolume = parseFloat($('#type5_total_volume').val()) || 0;

                    if (!chutesVolume || chutesVolume <= 0) {
                        showToast('error', 'Veuillez saisir un volume de chutes valide');
                        hasValidationError = true;
                    }

                    if (!familleId) {
                        showToast('error', 'Veuillez sélectionner une famille de destination');
                        hasValidationError = true;
                    }

                    if (productRows.length === 0) {
                        showToast('error', 'Veuillez ajouter au moins un produit final');
                        hasValidationError = true;
                    }

                    if (chutesVolume > 0 && totalVolume > chutesVolume) {
                        showToast('error',
                            `Le volume total des produits (${totalVolume.toFixed(4)} m³) dépasse le volume de chutes alloué (${chutesVolume.toFixed(4)} m³). ` +
                            `Veuillez ajouter plus de chutes ou réduire les quantités.`
                        );
                        hasValidationError = true;
                    }

                    productRows.each(function(index) {
                        const productSelect = $(this).find('.type5-product-select');
                        const quantityToProduce = $(this).find('.type5-quantity').val();

                        if (!productSelect.val()) {
                            showToast('error',
                                'Veuillez sélectionner un produit final pour la ligne ' + (index + 1));
                            hasValidationError = true;
                        }
                        if (!quantityToProduce || parseFloat(quantityToProduce) < 0.01) {
                            showToast('error',
                                'Veuillez saisir une quantité à produire valide pour la ligne ' + (index + 1));
                            hasValidationError = true;
                        }
                    });
                }

                if (hasValidationError) {
                    return;
                }

                const $form = $(this);

                if (productionType === 'type5') {
                    const chutesVolume = parseFloat($('#type5_chutes_volume').val()) || 0;

                    $.ajax({
                        url: "{{ route('raw-materials.get-by-code') }}",
                        type: "GET",
                        data: {
                            material_code: 'CHUTE-PRODUCTION'
                        },
                        success: function(response) {
                            const availableStockM3 = (response.success && response.material) ?
                                (parseFloat(response.material.current_stock) || 0) : 0;

                            if (availableStockM3 >= chutesVolume) {
                                submitProductionOrder($form, false);
                            } else {
                                confirmChutesOverride($form, chutesVolume, availableStockM3);
                            }
                        },
                        error: function() {
                            // Can't verify stock right now; don't block submission on a network hiccup.
                            submitProductionOrder($form, false);
                        }
                    });
                } else {
                    submitProductionOrder($form, false);
                }
            });

            function confirmChutesOverride($form, chutesVolume, availableStockM3) {
                const deficit = chutesVolume - availableStockM3;

                Swal.fire({
                    title: 'Stock de chutes insuffisant',
                    html: `Disponible: <strong>${availableStockM3.toFixed(4)} m³</strong><br>` +
                        `Demandé: <strong>${chutesVolume.toFixed(4)} m³</strong><br>` +
                        `Manquant: <strong>${deficit.toFixed(4)} m³</strong>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Continuer quand même',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    Swal.fire({
                        title: 'Confirmez à nouveau',
                        html: 'Le stock de chutes deviendra <strong>négatif</strong> de ' +
                            `${deficit.toFixed(4)} m³ si vous continuez.<br>Voulez-vous vraiment continuer ?`,
                        icon: 'error',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Oui, je confirme',
                        cancelButtonText: 'Annuler'
                    }).then((result2) => {
                        if (result2.isConfirmed) {
                            submitProductionOrder($form, true);
                        }
                    });
                });
            }

            function submitProductionOrder($form, forceChutes) {
                const submitBtn = $('#submitBtn');
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-2"></i>Mise à jour en cours...');

                const formData = $form.serializeArray();
                formData.push({
                    name: '_method',
                    value: 'PUT'
                });
                if (forceChutes) {
                    formData.push({
                        name: 'force_chutes',
                        value: '1'
                    });
                }

                $.ajax({
                    url: "{{ route('production-orders.update', $order->order_id) }}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: $.param(formData),
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('production-orders.show', $order->order_id) }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(
                                '<i class="fas fa-save me-2"></i>Mettre à jour l\'Ordre');
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        let errorMessage = '';

                        if (errors) {
                            Object.values(errors).forEach(function(errorArray) {
                                errorArray.forEach(function(error) {
                                    errorMessage += error + '\n';
                                });
                            });
                        } else {
                            errorMessage = xhr.responseJSON?.message ||
                                'Une erreur est survenue';
                        }

                        showToast('error', errorMessage);
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save me-2"></i>Mettre à jour l\'Ordre');
                    }
                });
            }

            function showToast(type, message) {
                const toast = $(`
                    <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">${message}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `);

                if ($('#toast-container').length === 0) {
                    $('body').append(
                        '<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>'
                    );
                }

                $('#toast-container').append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();
                setTimeout(() => toast.remove(), 5000);
            }

            // ============================================================
            // EDIT MODE: render the order's saved articles (sources and
            // produced products with their quantities) for every type.
            // toggleProductionTypeSections() is NOT called on load: it
            // resets the containers and would wipe the saved data.
            // ============================================================
            const editData = {
                productionType: @json($order->production_type),
                familleId: @json($order->famille_id),
                sourceFamilleId: @json($order->source_famille_id),
                orderProducts: @json($orderProducts->values()),
                sourceProducts: @json($sourceProducts->values()),
                bomConsumptions: @json($bomConsumptions),
            };

            // Fetch volumes for prefilled rows (the change handler normally does
            // this when a user picks a product; prefilled selects never fire it).
            function refreshPrefilledRowVolumes(selectClass, volumeClass, infoClass, recalculate) {
                $(selectClass).each(function() {
                    const productId = $(this).val();
                    const index = $(this).data('index');
                    if (productId) {
                        getProductDetails(productId).then(product => {
                            if (product) {
                                $(`${volumeClass}[data-index="${index}"]`).val(product.volume_per_unit.toFixed(4));
                                $(`${infoClass}[data-index="${index}"]`).text(
                                    `${product.display_volume} (${product.dimensions})`);
                            }
                            recalculate();
                        });
                    }
                });
            }

            function prefillBomRows(consumptions) {
                if (!consumptions || consumptions.length === 0) return;

                $('#bomTableBody').empty();
                const productionQty = parseFloat($('#type1_quantity').val()) || 1;

                consumptions.forEach(c => {
                    const materialId = c.material_id;
                    const plannedQuantity = parseFloat(c.planned_quantity) || 0;
                    const quantityPerUnit = plannedQuantity / productionQty;
                    const unitCost = parseFloat(c.unit_cost) || 0;
                    const totalCost = plannedQuantity * unitCost;
                    const stock = parseFloat(c.current_stock) || 0;
                    const isChutes = c.material_code === 'CHUTE-PRODUCTION';
                    const displayUnit = isChutes ? 'm³' : (c.unit || 'unité');

                    let stockBadge = '';
                    if (stock <= 0) {
                        stockBadge = '<span class="badge bg-danger">Épuisé</span>';
                    } else if (stock < plannedQuantity) {
                        stockBadge = '<span class="badge bg-warning">Stock insuffisant</span>';
                    } else if (stock < 10) {
                        stockBadge = '<span class="badge bg-warning">Stock faible</span>';
                    } else {
                        stockBadge = '<span class="badge bg-success">Stock OK</span>';
                    }

                    const rowClass = isChutes ? 'table-warning' : '';
                    const iconClass = isChutes ? 'fas fa-recycle text-warning' : 'fas fa-box text-primary';

                    const rowHtml = `
    <tr class="bom-item-row ${rowClass}" data-material-id="${materialId}">
        <td><div class="d-flex align-items-center"><i class="${iconClass} me-2"></i><div><div class="fw-medium">${escapeHtml(c.material_name)}</div><small class="text-muted">${escapeHtml(c.material_code)}</small></div></div></td>
        <td><code>${escapeHtml(c.material_code)}</code></td>
        <td class="text-center"><input type="number" class="form-control form-control-sm bom-quantity-required" value="${quantityPerUnit.toFixed(2)}" step="0.0001" min="0" style="width: 100px; display: inline-block;"></td>
        <td class="text-center bom-stock-available">${stock.toFixed(2)} ${stockBadge}</td>
        <td class="text-center"><input type="number" class="form-control form-control-sm bom-planned-quantity" value="${plannedQuantity.toFixed(2)}" step="0.0001" min="0" style="width: 120px; display: inline-block;"><input type="hidden" name="bom_consumptions[${materialId}][material_id]" value="${materialId}"><input type="hidden" name="bom_consumptions[${materialId}][planned_quantity]" value="${plannedQuantity.toFixed(2)}"><input type="hidden" name="bom_consumptions[${materialId}][quantity_required]" value="${quantityPerUnit.toFixed(2)}"><input type="hidden" name="bom_consumptions[${materialId}][save_to_product]" value="0"></td>
        <td class="text-center">${escapeHtml(displayUnit)}</td>
        <td class="text-center bom-unit-cost">${unitCost.toFixed(2)} DH</td>
        <td class="text-center bom-item-total">${totalCost.toFixed(2)} DH</td>
        <td class="text-center"><span class="badge ${stock >= plannedQuantity ? (stock < 10 ? 'bg-warning' : 'bg-success') : 'bg-danger'}">${stock >= plannedQuantity ? (stock < 10 ? '⚠️ Stock faible' : '✓ Suffisant') : '⚠️ Stock insuffisant'}</span></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-bom-item" data-material-id="${materialId}"><i class="fas fa-trash"></i></button></td>
    </tr>
`;

                    $('#bomTableBody').append(rowHtml);
                });

                attachBomEventHandlers();
                updateBomTotalCost();
            }

            (function initializeEditMode() {
                const type = editData.productionType;

                if (type === 'type1') {
                    editFamillePreselectId = editData.familleId;
                    const productId = $('#type1_product_id').val();
                    if (productId) {
                        loadFamilles(productId, 'type1');
                        updateVolumeCalculations('type1');
                    }
                    $('#bomCard').removeClass('d-none');
                    prefillBomRows(editData.bomConsumptions);
                    updateType1Calculation();

                } else if (type === 'type2') {
                    editFamillePreselectId = editData.sourceFamilleId;
                    const sourceProductId = $('#type2_source_product_id').val();
                    if (sourceProductId) {
                        loadFamilles(sourceProductId, 'type2');
                        updateVolumeCalculations('type2');
                    }
                    editData.orderProducts.forEach(p => addType2ProductRow(p));
                    refreshPrefilledRowVolumes('.type2-product-select', '.type2-volume',
                        '.type2-volume-info', updateType2Calculation);

                } else if (type === 'type3') {
                    editFamillePreselectId = editData.sourceFamilleId;
                    // First sous-bloc row loads the source familles (see addType3SousBlocRow)
                    editData.sourceProducts.forEach(sb => addType3SousBlocRow(sb));
                    editData.orderProducts.forEach(p => addType3ProductRow(p));
                    refreshPrefilledRowVolumes('.type3-sous-bloc-select', '.type3-sous-bloc-volume',
                        '.type3-sous-bloc-info', updateType3Calculation);
                    refreshPrefilledRowVolumes('.type3-product-select', '.type3-volume',
                        '.type3-volume-info', updateType3Calculation);

                } else if (type === 'type4') {
                    editFamillePreselectId = editData.familleId;
                    if ($('#type4_source_product_id').val()) {
                        updateType4SourceDetails();
                        loadType4Familles();
                    }
                    editData.orderProducts.forEach(p => addType4ProductRow(p));
                    refreshPrefilledRowVolumes('.type4-product-select', '.type4-volume',
                        '.type4-volume-info', updateType4Calculation);

                } else if (type === 'type5') {
                    editFamillePreselectId = editData.familleId;
                    checkType5ChutesStock();
                    editData.orderProducts.forEach(p => addType5ProductRow(p));
                    refreshPrefilledRowVolumes('.type5-product-select', '.type5-volume',
                        '.type5-volume-info', updateType5Calculation);
                    // Destination famille comes from the first produced article
                    if (editData.orderProducts.length > 0) {
                        loadFamilles(editData.orderProducts[0].product_id, 'type5');
                    }
                    updateType5Calculation();
                }
            })();
        });
    </script>
@endpush
