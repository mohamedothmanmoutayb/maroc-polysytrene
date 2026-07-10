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
                        <form id="editProductionOrderForm" novalidate>
                            @csrf
                            @method('PUT')

                            <!-- Order Information -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Ordre:</strong> {{ $order->order_number }}
                                        | <strong>Type:</strong>
                                        @if ($order->production_type === 'type1')
                                            <span class="badge bg-primary">Production Directe</span>
                                        @elseif($order->production_type === 'type2')
                                            <span class="badge bg-warning">Découpage</span>
                                        @elseif($order->production_type === 'type3')
                                            <span class="badge bg-success">Conversion</span>
                                        @endif
                                        | <strong>Statut:</strong>
                                        <span
                                            class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'approved' ? 'info' : ($order->status === 'in_progress' ? 'primary' : 'secondary')) }}">
                                            {{ $order->status === 'pending' ? 'En attente' : ($order->status === 'approved' ? 'Approuvé' : ($order->status === 'in_progress' ? 'En cours' : $order->status)) }}
                                        </span>
                                        @if ($order->outputs()->count() > 0)
                                            <span class="badge bg-danger ms-2">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Avec Sorties
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Production Type Selection (Show only the selected type) -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3">
                                        <i class="fas fa-cogs me-2"></i>Type de Production
                                    </h6>
                                    <div class="row">
                                        @php
                                            $isDisabled = $order->outputs()->count() > 0;
                                            $typeClass = '';
                                            $typeIcon = '';
                                            $typeText = '';

                                            if ($order->production_type === 'type1') {
                                                $typeClass = 'border-primary';
                                                $typeIcon = 'fa-industry text-primary';
                                                $typeText = 'Type 1: Production Directe';
                                            } elseif ($order->production_type === 'type2') {
                                                $typeClass = 'border-warning';
                                                $typeIcon = 'fa-cut text-warning';
                                                $typeText = 'Type 2: Découpage';
                                            } elseif ($order->production_type === 'type3') {
                                                $typeClass = 'border-success';
                                                $typeIcon = 'fa-exchange-alt text-success';
                                                $typeText = 'Type 3: Conversion';
                                            }
                                        @endphp

                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 {{ $typeClass }}">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <i class="fas {{ $typeIcon }} fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">{{ $typeText }}</h6>
                                                            <p class="text-muted mb-0 small">
                                                                @if ($order->production_type === 'type1')
                                                                    Matières premières → Produit de production
                                                                @elseif($order->production_type === 'type2')
                                                                    Bloc production → Produit découpage
                                                                @elseif($order->production_type === 'type3')
                                                                    Sous-blocs → Produit de vente (Multiple)
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="production_type"
                                                        value="{{ $order->production_type }}">
                                                    @if ($isDisabled)
                                                        <small class="text-danger d-block mt-2">
                                                            <i class="fas fa-lock me-1"></i> Le type ne peut pas être
                                                            modifié car des sorties existent
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Selection Section -->
                            <div class="row mb-4">
                                <!-- Type 1: Production Directe -->
                                <div class="col-md-12" id="type1Section"
                                    style="{{ $order->production_type !== 'type1' ? 'display:none;' : '' }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="type1_product_id" class="form-label">Produit à Produire
                                                    *</label>
                                                <select class="form-control select2" id="type1_product_id"
                                                    name="type1_product_id" {{ $isDisabled ? 'disabled' : '' }}>
                                                    <option value="">Sélectionner un produit de production</option>
                                                    @foreach ($productionProducts as $product)
                                                        @if ($product->product_type === 'production' || $product->product_type === 'both')
                                                            <option value="{{ $product->product_id }}"
                                                                data-has-familles="{{ $product->has_familles }}"
                                                                data-volume="{{ $product->volume_m3 ?? 0 }}"
                                                                {{ $order->product_id == $product->product_id ? 'selected' : '' }}>
                                                                {{ $product->product_code }} -
                                                                {{ $product->product_name }}
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
                                                <label for="type1_quantity" class="form-label">Quantité à Produire *</label>
                                                <input type="number" class="form-control" id="type1_quantity"
                                                    name="type1_quantity" min="0.01" step="0.01"
                                                    value="{{ $order->quantity_to_produce }}"
                                                    {{ $isDisabled ? 'readonly' : '' }}>
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
                                                        step="0.0001" min="0" readonly
                                                        value="{{ $order->product->volume_m3 ?? 0 }}">
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                                <small class="form-text text-muted" id="type1_volume_info">
                                                    {{ $order->product->volume_m3 ?? 0 }} m³
                                                    @if ($order->product->height_m && $order->product->width_m && $order->product->depth_m)
                                                        ({{ $order->product->height_m }} ×
                                                        {{ $order->product->width_m }} × {{ $order->product->depth_m }}
                                                        m)
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6 offset-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Volume Total Produit</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type1_total_volume"
                                                        step="0.0001" min="0" readonly
                                                        value="{{ $order->product ? $order->product->volume_m3 * $order->quantity_to_produce : 0 }}">
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Type 2: Production -> Découpage -->
                                <div class="col-md-12" id="type2Section"
                                    style="{{ $order->production_type !== 'type2' ? 'display:none;' : '' }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="type2_source_product_id" class="form-label">Produit Source
                                                    (Bloc) *</label>
                                                <select class="form-control select2" id="type2_source_product_id"
                                                    name="type2_source_product_id" {{ $isDisabled ? 'disabled' : '' }}>
                                                    <option value="">Sélectionner un produit source</option>
                                                    @foreach ($productionProducts as $product)
                                                        @if ($product->product_type === 'production' || $product->product_type === 'both')
                                                            <option value="{{ $product->product_id }}"
                                                                data-has-familles="{{ $product->has_familles }}"
                                                                data-volume="{{ $product->volume_m3 ?? 0 }}"
                                                                {{ $order->source_product_id == $product->product_id ? 'selected' : '' }}>
                                                                {{ $product->product_code }} -
                                                                {{ $product->product_name }}
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
                                                    Bloc de production à découper
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="type2_final_product_id" class="form-label">Produit Découpage
                                                    *</label>
                                                <select class="form-control select2" id="type2_final_product_id"
                                                    name="type2_final_product_id" {{ $isDisabled ? 'disabled' : '' }}>
                                                    <option value="">Sélectionner un produit découpage</option>
                                                    @foreach ($decoupageProducts as $product)
                                                        @if ($product->product_type === 'decoupage')
                                                            <option value="{{ $product->product_id }}"
                                                                data-has-familles="{{ $product->has_familles }}"
                                                                data-volume="{{ $product->volume_m3 ?? 0 }}"
                                                                {{ $order->product_id == $product->product_id ? 'selected' : '' }}>
                                                                {{ $product->product_code }} -
                                                                {{ $product->product_name }}
                                                                <span class="badge bg-warning">Découpage</span>
                                                                @if ($product->has_familles)
                                                                    <span class="badge bg-info">Avec Familles</span>
                                                                @endif
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">
                                                    Produit de type découpage à produire
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="type2_quantity" class="form-label">Quantité de Blocs *</label>
                                                <input type="number" class="form-control" id="type2_quantity"
                                                    name="type2_quantity" min="0.01" step="0.01"
                                                    value="{{ $order->quantity_to_produce }}"
                                                    {{ $isDisabled ? 'readonly' : '' }}>
                                                <small class="form-text text-muted">
                                                    Quantité de blocs à découper
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Volume/Bloc</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type2_source_volume"
                                                        step="0.0001" min="0" readonly
                                                        value="{{ $order->sourceProduct->volume_m3 ?? 0 }}">
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                                <small class="form-text text-muted" id="type2_source_volume_info">
                                                    {{ $order->sourceProduct->volume_m3 ?? 0 }} m³
                                                    @if ($order->sourceProduct)
                                                        @if ($order->sourceProduct->height_m && $order->sourceProduct->width_m && $order->sourceProduct->depth_m)
                                                            ({{ $order->sourceProduct->height_m }} ×
                                                            {{ $order->sourceProduct->width_m }} ×
                                                            {{ $order->sourceProduct->depth_m }} m)
                                                        @endif
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Volume/Produit Découpage</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type2_final_volume"
                                                        step="0.0001" min="0" readonly
                                                        value="{{ $order->product->volume_m3 ?? 0 }}">
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                                <small class="form-text text-muted" id="type2_final_volume_info">
                                                    {{ $order->product->volume_m3 ?? 0 }} m³
                                                    @if ($order->product->height_m && $order->product->width_mm && $order->product->depth_mm)
                                                        ({{ $order->product->height_m }} ×
                                                        {{ $order->product->width_m }} × {{ $order->product->depth_m }}
                                                        m)
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Volume Total Produit</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type2_total_volume"
                                                        step="0.0001" min="0" readonly
                                                        value="{{ $order->product ? $order->product->volume_m3 * $order->quantity_to_produce : 0 }}">
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Type 3: Découpage -> Vente (Multiple Products) -->
                                <div class="col-md-12" id="type3Section"
                                    style="{{ $order->production_type !== 'type3' ? 'display:none;' : '' }}">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="type3_source_product_id" class="form-label">Produit Source
                                                    (Sous-bloc) *</label>
                                                <select class="form-control select2" id="type3_source_product_id"
                                                    name="type3_source_product_id" {{ $isDisabled ? 'disabled' : '' }}>
                                                    <option value="">Sélectionner un sous-bloc</option>
                                                    @foreach ($decoupageProducts as $product)
                                                        @if ($product->product_type === 'decoupage')
                                                            <option value="{{ $product->product_id }}"
                                                                data-has-familles="{{ $product->has_familles }}"
                                                                data-volume="{{ $product->volume_m3 ?? 0 }}"
                                                                {{ $order->source_product_id == $product->product_id ? 'selected' : '' }}>
                                                                {{ $product->product_code }} -
                                                                {{ $product->product_name }}
                                                                <span class="badge bg-warning">Découpage</span>
                                                                @if ($product->has_familles)
                                                                    <span class="badge bg-info">Avec Familles</span>
                                                                @endif
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">
                                                    Sous-bloc de type découpage à convertir
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-12" style="margin-top: 17px">
                                            <div class="form-group">
                                                <label class="form-label">Produits Finaux à Produire *</label>
                                                @if (!$isDisabled)
                                                    <button type="button" class="btn btn-sm btn-primary mb-2"
                                                        id="addType3Product">
                                                        <i class="fas fa-plus me-1"></i> Ajouter un Produit
                                                    </button>
                                                @endif
                                                <div id="type3ProductsContainer">
                                                    @if ($type3Products && count($type3Products) > 0)
                                                        @foreach ($type3Products as $index => $product)
                                                            <div class="type3-product-row card mb-3"
                                                                data-index="{{ $index }}">
                                                                <div class="card-body">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                <label class="form-label">Produit Final
                                                                                    *</label>
                                                                                <select
                                                                                    class="form-control select2 type3-product-select"
                                                                                    name="type3_products[{{ $index }}][product_id]"
                                                                                    data-index="{{ $index }}"
                                                                                    {{ $isDisabled ? 'disabled' : '' }}>
                                                                                    @foreach ($salesProducts->whereIn('product_type', ['finale', 'both']) as $salesProduct)
                                                                                        <option
                                                                                            value="{{ $salesProduct->product_id }}"
                                                                                            data-volume="{{ $salesProduct->volume_m3 ?? 0 }}"
                                                                                            {{ $product->product_id == $salesProduct->product_id ? 'selected' : '' }}>
                                                                                            {{ $salesProduct->product_code }}
                                                                                            -
                                                                                            {{ $salesProduct->product_name }}
                                                                                            <span
                                                                                                class="badge bg-success">Vente</span>
                                                                                            @if ($salesProduct->has_familles)
                                                                                                <span
                                                                                                    class="badge bg-info">Avec
                                                                                                    Familles</span>
                                                                                            @endif
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-2" style="margin-top: 20px;">
                                                                            <div class="form-group">
                                                                                <label class="form-label">Ratio *</label>
                                                                                <input type="number"
                                                                                    class="form-control type3-conversion-rate"
                                                                                    name="type3_products[{{ $index }}][conversion_rate]"
                                                                                    value="{{ $product->conversion_rate }}"
                                                                                    step="0.01" min="0.01"
                                                                                    placeholder="1.0"
                                                                                    {{ $isDisabled ? 'readonly' : '' }}>
                                                                                <small
                                                                                    class="form-text text-muted">sous-bloc
                                                                                    → produit</small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-2">
                                                                            <div class="form-group">
                                                                                <label class="form-label">Quantité
                                                                                    *</label>
                                                                                <input type="number"
                                                                                    class="form-control type3-quantity"
                                                                                    name="type3_products[{{ $index }}][quantity_to_produce]"
                                                                                    value="{{ $product->quantity_to_produce }}"
                                                                                    min="0.01" step="0.01"
                                                                                    placeholder="100"
                                                                                    {{ $isDisabled ? 'readonly' : '' }}>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-2">
                                                                            <div class="form-group">
                                                                                <label
                                                                                    class="form-label">Volume/Unité</label>
                                                                                <div class="input-group">
                                                                                    <input type="number"
                                                                                        class="form-control type3-volume"
                                                                                        data-index="{{ $index }}"
                                                                                        step="0.0001" min="0"
                                                                                        readonly
                                                                                        value="{{ $product->volume_per_unit ?? ($product->volume_m3 ?? 0) }}">
                                                                                    <span
                                                                                        class="input-group-text">m³</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        @if (!$isDisabled)
                                                                            <div class="col-md-2">
                                                                                <div class="form-group">
                                                                                    <label
                                                                                        class="form-label">Actions</label>
                                                                                    <button type="button"
                                                                                        class="btn btn-sm btn-danger w-100 remove-type3-product"
                                                                                        data-index="{{ $index }}">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Supprimer
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <small
                                                                                class="form-text text-muted type3-volume-info"
                                                                                data-index="{{ $index }}">
                                                                                {{ number_format($product->volume_per_unit ?? ($product->volume_m3 ?? 0), 4) }}
                                                                                m³
                                                                                @if (isset($product->height_mm) && isset($product->width_mm) && isset($product->depth_mm))
                                                                                    ({{ $product->height_mm }} ×
                                                                                    {{ $product->width_mm }} ×
                                                                                    {{ $product->depth_mm }} mm)
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="alert alert-info" id="noProductsMessage">
                                                            Cliquez sur "Ajouter un Produit" pour ajouter des produits
                                                            finaux
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="type3_total_sous_blocs" class="form-label">Total Sous-blocs
                                                    Requis</label>
                                                <input type="number" class="form-control" id="type3_total_sous_blocs"
                                                    name="type3_total_sous_blocs" min="0.01" step="0.01"
                                                    placeholder="Calculé automatiquement" readonly
                                                    value="{{ $order->required_quantity }}">
                                                <small class="form-text text-muted">
                                                    Total des sous-blocs nécessaires pour tous les produits
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Volume/Sous-bloc</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type3_source_volume"
                                                        step="0.0001" min="0" readonly
                                                        value="{{ $order->sourceProduct->volume_m3 ?? 0 }}">
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                                <small class="form-text text-muted" id="type3_source_volume_info">
                                                    {{ $order->sourceProduct->volume_m3 ?? 0 }} m³
                                                    @if ($order->sourceProduct)
                                                        @if ($order->sourceProduct->height_m && $order->sourceProduct->width_m && $order->sourceProduct->depth_m)
                                                            ({{ $order->sourceProduct->height_m }} ×
                                                            {{ $order->sourceProduct->width_m }} ×
                                                            {{ $order->sourceProduct->depth_m }} m)
                                                        @endif
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Volume Total Produits</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="type3_total_volume"
                                                        step="0.0001" min="0" readonly
                                                        value="{{ $order->total_volume_produced ?? 0 }}">
                                                    <span class="input-group-text">m³</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Total Produits Finaux</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control"
                                                        id="type3_total_final_products" step="0.0001" min="0"
                                                        readonly value="{{ $order->quantity_to_produce }}">
                                                    <span class="input-group-text">unités</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chutes Selection Section (Only for Type 1) -->
                            <div class="row mb-4" id="chutesSection"
                                style="{{ $order->production_type !== 'type1' ? 'display:none;' : '' }}">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header bg-warning">
                                            <h6 class="card-title mb-0" style="color:white">
                                                <i class="fas fa-recycle me-2"></i>Utilisation des Chutes de Production
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-check card h-100">
                                                        <input class="form-check-input" type="radio"
                                                            name="material_source" id="bomOnly" value="bom_only"
                                                            {{ $order->material_source === 'bom_only' ? 'checked' : '' }}
                                                            {{ $isDisabled ? 'disabled' : '' }}>
                                                        <label class="form-check-label card-body" for="bomOnly">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-3">
                                                                    <i class="fas fa-boxes fa-2x text-primary"></i>
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-1">Nomenclature Standard</h6>
                                                                    <p class="text-muted mb-0 small">
                                                                        Utiliser uniquement les matières premières standards
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check card h-100">
                                                        <input class="form-check-input" type="radio"
                                                            name="material_source" id="chutesOnly" value="chutes_only"
                                                            {{ $order->material_source === 'chutes_only' ? 'checked' : '' }}
                                                            {{ $isDisabled ? 'disabled' : '' }}>
                                                        <label class="form-check-label card-body" for="chutesOnly">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-3">
                                                                    <i class="fas fa-trash-restore fa-2x text-warning"></i>
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-1">Chutes uniquement</h6>
                                                                    <p class="text-muted mb-0 small">
                                                                        Recycler uniquement des chutes existantes
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check card h-100">
                                                        <input class="form-check-input" type="radio"
                                                            name="material_source" id="bothSources" value="both"
                                                            {{ $order->material_source === 'both' ? 'checked' : '' }}
                                                            {{ $isDisabled ? 'disabled' : '' }}>
                                                        <label class="form-check-label card-body" for="bothSources">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-3">
                                                                    <i class="fas fa-layer-group fa-2x text-success"></i>
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-1">Mixte (BOM + Chutes)</h6>
                                                                    <p class="text-muted mb-0 small">
                                                                        Utiliser les deux sources de matière
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Chutes volume input -->
                                            <div class="row mt-3" id="chutesVolumeSection"
                                                style="{{ !in_array($order->material_source, ['chutes_only', 'both']) ? 'display:none;' : '' }}">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="chutes_volume" class="form-label">
                                                            <i class="fas fa-recycle me-2"></i>Volume de Chutes à Utiliser
                                                            (m³)
                                                            @if (in_array($order->material_source, ['chutes_only', 'both']))
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control" id="chutes_volume"
                                                                name="chutes_volume" step="0.0001" min="0.0001"
                                                                value="{{ $order->chutes_volume ?? 0 }}"
                                                                {{ $isDisabled ? 'readonly' : '' }}>
                                                            <span class="input-group-text">m³</span>
                                                        </div>
                                                        <small class="form-text text-muted">
                                                            Volume de chutes de production à recycler
                                                        </small>
                                                        <div class="mt-2" id="chutesStockInfo">
                                                            <!-- Stock info will be loaded here -->
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="alert alert-info">
                                                        <h6><i class="fas fa-info-circle me-2"></i>Informations</h6>
                                                        <p class="mb-1"><small>Les chutes seront consommées comme matière
                                                                première.</small></p>
                                                        <p class="mb-0"><small>Coût: 0 DH (recyclage interne)</small></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- BOM percentage when using both -->
                                            <div class="row mt-3" id="bomPercentageSection"
                                                style="{{ $order->material_source !== 'both' ? 'display:none;' : '' }}">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="bom_percentage" class="form-label">
                                                            <i class="fas fa-percentage me-2"></i>Pourcentage de Matières
                                                            Nouvelles
                                                            @if ($order->material_source === 'both')
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control"
                                                                id="bom_percentage" name="bom_percentage" step="1"
                                                                min="0" max="100" placeholder="Ex: 60"
                                                                value="{{ $order->bom_percentage ?? 60 }}"
                                                                {{ $isDisabled ? 'readonly' : '' }}>
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                        <small class="form-text text-muted">
                                                            Pourcentage de matières premières standards (reste = chutes)
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="alert alert-info">
                                                        <h6><i class="fas fa-calculator me-2"></i>Calcul</h6>
                                                        <p class="mb-1"><small id="bomCalcInfo">
                                                                {{ $order->material_source === 'both' ? $order->bom_percentage ?? 60 : 60 }}%
                                                                matières nouvelles +
                                                                {{ $order->material_source === 'both' ? 100 - ($order->bom_percentage ?? 60) : 40 }}%
                                                                chutes
                                                            </small></p>
                                                        <p class="mb-0"><small>Les coûts seront calculés
                                                                proportionnellement</small></p>
                                                    </div>
                                                </div>
                                            </div>
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

                            <!-- Conversion Ratios Section -->
                            <div class="row mb-4" id="conversionSection"
                                style="{{ $order->production_type !== 'type2' ? 'display:none;' : '' }}">
                                <div class="col-md-12">
                                    <h6 class="mb-3">
                                        <i class="fas fa-exchange-alt me-2"></i>Paramètres de Conversion
                                    </h6>

                                    <!-- Type 2: Découpage Ratio -->
                                    <div class="row" id="type2ConversionSection">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="decoupage_ratio" class="form-label">Ratio de Découpage
                                                    *</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">1 bloc =</span>
                                                    <input type="number" class="form-control" id="decoupage_ratio"
                                                        name="decoupage_ratio" step="1" min="1"
                                                        placeholder="Ex: 4" value="{{ $order->decoupage_ratio ?? 1 }}"
                                                        {{ $isDisabled ? 'readonly' : '' }}>
                                                    <span class="input-group-text">sous bloc</span>
                                                </div>
                                                <small class="form-text text-muted">
                                                    Nombre de sous bloc obtenus à partir d'1 bloc
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Calculated Results Section -->
                            <div class="row mb-4 d-none" id="calculatedResultsSection">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-calculator me-2"></i>Résultats Calculés
                                            </h6>
                                        </div>
                                        <div class="card-body" id="calculatedResults">
                                            <!-- Results will be displayed here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BOM Information (for Type 1 only) -->
                            <div class="card mb-4 d-none" id="bomCard">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-list-alt me-2"></i>Nomenclature (BOM) - Matières Premières
                                        Requises
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Calcul:</strong> Pour <span id="bom-quantity-display">1</span> produit, il
                                        faut:
                                        <span id="materialSourceInfo" class="ms-2"></span>
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
                                                </tr>
                                            </thead>
                                            <tbody id="bomTableBody">
                                                <!-- BOM items will be loaded here via AJAX -->
                                            </tbody>
                                            <tfoot id="bomTableFooter">
                                                <!-- Total cost will be loaded via AJAX -->
                                            </tfoot>
                                        </table>
                                    </div>

                                    <!-- Stock Summary -->
                                    <div id="bomStockSummary" class="mt-3 d-none">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header bg-light">
                                                        <h6 class="card-title mb-0">
                                                            <i class="fas fa-clipboard-check me-2"></i>Résumé du Stock
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div id="stockSummaryContent">
                                                            <!-- Stock summary will be loaded here -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header bg-light">
                                                        <h6 class="card-title mb-0">
                                                            <i class="fas fa-calculator me-2"></i>Récapitulatif Coûts
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div id="costSummaryContent">
                                                            <!-- Cost summary will be loaded here -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Production Details -->
                            <div class="row mb-4" id="productionDetailsSection">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="priority" class="form-label">Priorité *</label>
                                        <select class="form-control" id="priority" name="priority" required
                                            {{ $isDisabled ? 'disabled' : '' }}>
                                            <option value="low" {{ $order->priority == 'low' ? 'selected' : '' }}>
                                                Basse</option>
                                            <option value="medium" {{ $order->priority == 'medium' ? 'selected' : '' }}>
                                                Moyenne</option>
                                            <option value="high" {{ $order->priority == 'high' ? 'selected' : '' }}>
                                                Haute</option>
                                            <option value="urgent" {{ $order->priority == 'urgent' ? 'selected' : '' }}>
                                                Urgente</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="start_date" class="form-label">Date de Début *</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date"
                                            required
                                            value="{{ $order->start_date ? $order->start_date->format('Y-m-d') : date('Y-m-d') }}"
                                            {{ $isDisabled ? 'readonly' : '' }}>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="expected_completion_date" class="form-label">Date de Fin Prévue
                                            *</label>
                                        <input type="date" class="form-control" id="expected_completion_date"
                                            name="expected_completion_date" required
                                            value="{{ $order->expected_completion_date ? $order->expected_completion_date->format('Y-m-d') : '' }}"
                                            {{ $isDisabled ? 'readonly' : '' }}>
                                        <small class="form-text text-muted" id="productionTimeInfo">
                                            <!-- Production time info will be loaded via AJAX -->
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Waste Percentage -->
                            <div class="row mb-4" id="wasteSection"
                                style="{{ !in_array($order->production_type, ['type2', 'type3']) ? 'display:none;' : '' }}">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="waste_percentage" class="form-label">Pourcentage de Déchet</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="waste_percentage"
                                                name="waste_percentage" step="0.01" min="0" max="100"
                                                value="{{ $order->waste_percentage ?? 0 }}"
                                                {{ $isDisabled ? 'readonly' : '' }}>
                                            <span class="input-group-text">%</span>
                                        </div>
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
                                    placeholder="Instructions spéciales, notes de production..." {{ $isDisabled ? 'readonly' : '' }}>{{ $order->notes }}</textarea>
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
                                value="{{ $order->waste_percentage }}">
                            <input type="hidden" id="actual_material_source" name="material_source"
                                value="{{ $order->material_source ?? 'bom_only' }}">
                            <input type="hidden" id="actual_bom_percentage" name="bom_percentage"
                                value="{{ $order->bom_percentage ?? 100 }}">
                            <input type="hidden" id="actual_chutes_volume" name="chutes_volume"
                                value="{{ $order->chutes_volume ?? 0 }}">

                            <!-- Form Actions -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary" id="submitBtn"
                                    {{ $isDisabled ? 'disabled' : '' }}>
                                    <i class="fas fa-save me-1"></i> Mettre à jour
                                </button>
                                @if (in_array($order->status, ['pending', 'approved']) && !$isDisabled)
                                    <button type="button" class="btn btn-warning" id="cancelProductionBtn">
                                        <i class="fas fa-ban me-1"></i> Annuler la Production
                                    </button>
                                @endif
                                <a href="{{ route('production-orders.show', $order->order_id) }}"
                                    class="btn btn-secondary">
                                    <i class="fas fa-eye me-1"></i> Voir Détails
                                </a>
                                <a href="{{ route('production-orders.index') }}" class="btn btn-light">
                                    <i class="fas fa-arrow-left me-1"></i> Retour
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Production Modal -->
    @if (in_array($order->status, ['pending', 'approved']) && !$isDisabled)
        <div class="modal fade" id="cancelProductionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-ban me-2"></i>Annuler la Production
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Veuillez confirmer l'annulation de la production :</p>
                        <div class="alert alert-warning">
                            <strong>{{ $order->order_number }}</strong>
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
                            <textarea class="form-control" id="additionalNotes" rows="3"
                                placeholder="Ajouter des détails sur l'annulation..."></textarea>
                        </div>

                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Attention :</strong> Cette action marquera la commande comme annulée et ne pourra pas
                            être
                            annulée.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Fermer
                        </button>
                        <button type="button" class="btn btn-warning" id="confirmCancelProduction">
                            <i class="fas fa-ban me-2"></i>Annuler la Production
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div id="product-options-data"
        data-products="{{ htmlspecialchars(
            json_encode(
                $salesProducts->whereIn('product_type', ['finale', 'both'])->map(function ($product) {
                        return [
                            'id' => $product->product_id,
                            'text' => $product->product_code . ' - ' . $product->product_name,
                            'volume_m3' => $product->volume_m3 ?? 0,
                        ];
                    })->values(),
            ),
            ENT_QUOTES,
            'UTF-8',
            true,
        ) }}">
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
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

        .form-check-input:disabled+.card {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .select2-container--disabled .select2-selection--single {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        input:read-only,
        select:disabled,
        textarea:read-only {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .badge.bg-warning {
            color: #000 !important;
        }

        .calculation-result {
            font-weight: bold;
            color: #198754;
        }

        .stock-info {
            font-size: 0.9em;
            color: #6c757d;
        }

        .volume-display {
            background-color: #f8f9fa;
            border-left: 3px solid #0d6efd;
        }

        .volume-display.warning {
            border-left-color: #ffc107;
        }

        .volume-display.success {
            border-left-color: #198754;
        }

        .type3-product-row {
            border-left: 3px solid #198754;
        }

        .type3-product-row .card-body {
            padding: 1rem;
        }

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

        /* Chutes section styling */
        #bomOnly:checked+.card {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
        }

        #chutesOnly:checked+.card {
            border-color: #ffc107;
            background-color: rgba(255, 193, 7, 0.05);
        }

        #bothSources:checked+.card {
            border-color: #198754;
            background-color: rgba(25, 135, 84, 0.05);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                language: "fr",
                placeholder: "Sélectionner...",
                allowClear: true
            });

            // Check if form is disabled (outputs exist)
            const isDisabled = {{ $order->outputs()->count() > 0 ? 'true' : 'false' }};
            const currentProductionType = '{{ $order->production_type }}';
            const orderId = {{ $order->order_id }};

            // Store product options for Type 3
            const productOptionsData = {!! json_encode(
                $salesProducts->whereIn('product_type', ['finale', 'both'])->map(function ($product) {
                        return [
                            'id' => $product->product_id,
                            'text' => $product->product_code . ' - ' . $product->product_name,
                            'volume_m3' => $product->volume_m3 ?? 0,
                        ];
                    })->values(),
            ) !!};

            // Set default expected completion date
            const setDefaultCompletionDate = () => {
                const startDate = $('#start_date').val();
                if (startDate) {
                    const expectedDate = new Date(startDate);
                    expectedDate.setDate(expectedDate.getDate() + 7);
                    if (!$('#expected_completion_date').val()) {
                        $('#expected_completion_date').val(expectedDate.toISOString().split('T')[0]);
                    }
                }
            };
            setDefaultCompletionDate();

            // Material source change handler
            $('input[name="material_source"]').change(function() {
                updateMaterialSourceSections();
            });

            // BOM percentage change handler
            $('#bom_percentage').on('input', function() {
                updateBomPercentageInfo();
            });

            // Chutes volume change handler
            $('#chutes_volume').on('input', function() {
                const materialSource = $('input[name="material_source"]:checked').val();
                if (materialSource === 'chutes_only' || materialSource === 'both') {
                    $('#actual_chutes_volume').val($(this).val());
                }

                const productId = $('#type1_product_id').val();
                const quantity = $('#type1_quantity').val() || 1;
                if (productId && quantity >= 1 && currentProductionType === 'type1') {
                    loadBOM(productId, quantity);
                }
            });

            // Toggle material source sections
            function updateMaterialSourceSections() {
                const materialSource = $('input[name="material_source"]:checked').val();
                $('#actual_material_source').val(materialSource);

                if (materialSource === 'bom_only') {
                    $('#chutesVolumeSection, #bomPercentageSection').hide();
                    $('#actual_bom_percentage').val('100');
                    $('#actual_chutes_volume').val('0');
                } else if (materialSource === 'chutes_only') {
                    $('#chutesVolumeSection').show();
                    $('#bomPercentageSection').hide();
                    $('#actual_bom_percentage').val('0');
                } else if (materialSource === 'both') {
                    $('#chutesVolumeSection, #bomPercentageSection').show();
                }

                updateBomPercentageInfo();
            }

            // Update BOM percentage info
            function updateBomPercentageInfo() {
                let bomPercentage = $('#bom_percentage').val() || 60;
                bomPercentage = Math.round(parseFloat(bomPercentage) || 60);
                const chutesPercentage = 100 - bomPercentage;
                $('#bomCalcInfo').text(`${bomPercentage}% matières nouvelles + ${chutesPercentage}% chutes`);
                $('#actual_bom_percentage').val(bomPercentage);
            }

            // Check chutes stock availability
            function checkChutesStock() {
                const materialSource = $('input[name="material_source"]:checked').val();

                if (materialSource === 'chutes_only' || materialSource === 'both') {
                    $.ajax({
                        url: "{{ route('raw-materials.get-by-code') }}",
                        type: "GET",
                        data: {
                            material_code: 'CHUTE-PRODUCTION'
                        },
                        success: function(response) {
                            if (response.success && response.material) {
                                const chutesMaterial = response.material;
                                const availableStock = parseFloat(chutesMaterial.current_stock) || 0;

                                let stockInfoHtml = '';
                                let stockClass = 'chutes-stock-none';

                                if (availableStock > 0) {
                                    stockInfoHtml = `
                                        <div class="alert ${availableStock >= 10 ? 'chutes-stock-ok' : 'chutes-stock-low'}">
                                            <i class="fas ${availableStock >= 10 ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>
                                            <strong>Stock disponible:</strong> ${availableStock.toFixed(4)} m³
                                            ${availableStock < 10 ? '<br><small>Stock faible</small>' : ''}
                                        </div>
                                    `;
                                } else {
                                    stockInfoHtml = `
                                        <div class="alert chutes-stock-none">
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            <strong>Aucun stock disponible</strong>
                                            <br><small>Les chutes de production ne sont pas disponibles</small>
                                        </div>
                                    `;
                                }

                                $('#chutesStockInfo').html(stockInfoHtml);
                            } else {
                                $('#chutesStockInfo').html(`
                                    <div class="alert chutes-stock-none">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <strong>Matière non trouvée</strong>
                                        <br><small>Les chutes de production (CHUTE-PRODUCTION) ne sont pas configurées</small>
                                </div>
                                `);
                            }
                        },
                        error: function() {
                            $('#chutesStockInfo').html(`
                                <div class="alert chutes-stock-none">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <strong>Erreur de chargement</strong>
                                    <br><small>Impossible de vérifier le stock des chutes</small>
                                </div>
                            `);
                        }
                    });
                }
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
                const productionType = currentProductionType;

                if (productionType === 'type2' || productionType === 'type3') {
                    return $('#source_famille_id').val();
                } else {
                    return $('#famille_id').val();
                }
            }

            // Update all calculations based on current production type
            function updateCalculations() {
                if (currentProductionType === 'type1') {
                    updateType1Calculation();
                } else if (currentProductionType === 'type2') {
                    updateType2Calculation();
                } else if (currentProductionType === 'type3') {
                    updateType3Calculation();
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
                    const finalProductId = $('#type2_final_product_id').val();
                    const quantity = $('#type2_quantity').val() || 1;
                    const decoupageRatio = $('#decoupage_ratio').val() || 1;

                    if (sourceProductId && finalProductId) {
                        Promise.all([
                            getProductDetails(sourceProductId),
                            getProductDetails(finalProductId)
                        ]).then(([sourceProduct, finalProduct]) => {
                            if (sourceProduct && finalProduct) {
                                const decoupageProductsProduced = quantity * decoupageRatio;
                                const totalVolume = decoupageProductsProduced * finalProduct
                                    .volume_per_unit;

                                $('#type2_source_volume').val(sourceProduct.volume_per_unit.toFixed(4));
                                $('#type2_source_volume_info').text(
                                    `${sourceProduct.display_volume} (${sourceProduct.dimensions})`);
                                $('#type2_final_volume').val(finalProduct.volume_per_unit.toFixed(4));
                                $('#type2_final_volume_info').text(
                                    `${finalProduct.display_volume} (${finalProduct.dimensions})`);
                                $('#type2_total_volume').val(totalVolume.toFixed(4));
                            }
                        });
                    }
                } else if (productionType === 'type3') {
                    const sourceProductId = $('#type3_source_product_id').val();

                    if (sourceProductId) {
                        getProductDetails(sourceProductId).then(sourceProduct => {
                            if (sourceProduct) {
                                $('#type3_source_volume').val(sourceProduct.volume_per_unit.toFixed(4));
                                $('#type3_source_volume_info').text(
                                    `${sourceProduct.display_volume} (${sourceProduct.dimensions})`);

                                // Calculate total volume from all products
                                let totalVolume = 0;
                                $('.type3-product-row').each(function() {
                                    const volumeInput = $(this).find('.type3-volume').val();
                                    const quantity = $(this).find('.type3-quantity').val() || 0;
                                    const volume = parseFloat(volumeInput) || 0;
                                    totalVolume += volume * quantity;
                                });

                                $('#type3_total_volume').val(totalVolume.toFixed(4));
                            }
                        });
                    }
                }
            }

            // Type 1: Direct production calculation
            function updateType1Calculation() {
                const productId = $('#type1_product_id').val();
                const quantity = $('#type1_quantity').val() || 1;

                if (productId && quantity >= 1) {
                    loadBOM(productId, quantity);
                    checkChutesStock();

                    // Set hidden fields
                    $('#actual_product_id').val(productId);
                    $('#actual_quantity_to_produce').val(quantity);
                    $('#actual_required_quantity').val(quantity);
                }
            }

            // Type 2: Découpage calculation
            function updateType2Calculation() {
                const sourceProductId = $('#type2_source_product_id').val();
                const finalProductId = $('#type2_final_product_id').val();
                const quantity = $('#type2_quantity').val() || 1;
                const decoupageRatio = $('#decoupage_ratio').val() || 1;
                const familleId = getFamilleId();

                if (sourceProductId && finalProductId && quantity && decoupageRatio) {
                    // Calculate decoupage products produced
                    const decoupageProductsProduced = quantity * decoupageRatio;
                    const sourceBlocksNeeded = quantity;

                    // Get source stock information for selected famille
                    getStockInfo(sourceProductId, familleId).then(stockInfo => {
                        const isSufficient = stockInfo.available >= sourceBlocksNeeded;

                        // Now get product details for waste calculation
                        Promise.all([
                            getProductDetails(sourceProductId),
                            getProductDetails(finalProductId)
                        ]).then(([sourceProduct, finalProduct]) => {
                            if (sourceProduct && finalProduct) {
                                // Calculate volumes
                                const sourceVolume = sourceProduct.volume_per_unit;
                                const finalVolume = finalProduct.volume_per_unit;
                                const totalSourceVolume = sourceBlocksNeeded * sourceVolume;
                                const totalFinalVolume = decoupageProductsProduced * finalVolume;
                                const wasteVolume = Math.max(0, totalSourceVolume -
                                    totalFinalVolume);
                                const wastePercentage = totalSourceVolume > 0 ?
                                    (wasteVolume / totalSourceVolume * 100).toFixed(2) : 0;

                                // Display results
                                const resultsHtml = `
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Entrée:</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td>Blocs à découper:</td>
                                                    <td class="text-end"><strong>${parseFloat(quantity).toFixed(2)} unités</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Ratio de découpage:</td>
                                                    <td class="text-end">1 bloc = ${parseFloat(decoupageRatio).toFixed(2)} produits découpage</td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <td><strong>Total blocs requis:</strong></td>
                                                    <td class="text-end"><strong class="calculation-result">${parseFloat(sourceBlocksNeeded).toFixed(2)} unités</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Sortie:</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td>Produits découpage produits:</td>
                                                    <td class="text-end"><strong class="calculation-result">${parseFloat(decoupageProductsProduced).toFixed(2)} unités</strong></td>
                                                </tr>
                                            </table>
                                            <h6 class="mt-3">Stock Source:</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td>Produit source:</td>
                                                    <td class="text-end">${$('#type2_source_product_id option:selected').text().split('-')[1]?.trim() || 'N/A'}</td>
                                                </tr>
                                                ${stockInfo.famille_name ? `
                                                                                                                                                                                        <tr>
                                                                                                                                                                                            <td>Famille:</td>
                                                                                                                                                                                            <td class="text-end">${stockInfo.famille_name}</td>
                                                                                                                                                                                        </tr>
                                                                                                                                                                                        ${stockInfo.location ? `
                                                    <tr>
                                                        <td>Location:</td>
                                                        <td class="text-end">${stockInfo.location}</td>
                                                    </tr>
                                                ` : ''}
                                                                                                                                                                                    ` : ''}
                                                <tr>
                                                    <td>Stock disponible:</td>
                                                    <td class="text-end">${parseFloat(stockInfo.available).toFixed(2)} unités</td>
                                                </tr>
                                                <tr>
                                                    <td>Quantité totale:</td>
                                                    <td class="text-end">${parseFloat(stockInfo.current_quantity).toFixed(2)} unités</td>
                                                </tr>
                                                <tr>
                                                    <td>Quantité réservée:</td>
                                                    <td class="text-end">${parseFloat(stockInfo.reserved_quantity).toFixed(2)} unités</td>
                                                </tr>
                                                <tr class="${isSufficient ? 'table-success' : 'table-danger'}">
                                                    <td><strong>Statut:</strong></td>
                                                    <td class="text-end">
                                                        <strong>
                                                            ${isSufficient ?
                                                                '<span class="text-success">✓ Suffisant</span>' :
                                                                '<span class="text-danger">✗ Insuffisant</span>'}
                                                        </strong>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- WASTE CALCULATION SECTION -->
                                    <div id="wasteCalculationSection" class="alert alert-warning mt-3">
                                        <h6><i class="fas fa-trash me-2"></i>Calcul du Chute/Déchet (Type 2)</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-sm mb-0">
                                                    <tr>
                                                        <td>Volume du bloc source:</td>
                                                        <td class="text-end">${sourceVolume.toFixed(4)} m³</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Volume produit découpage:</td>
                                                        <td class="text-end">${finalVolume.toFixed(4)} m³</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Volume total source:</td>
                                                        <td class="text-end">${totalSourceVolume.toFixed(4)} m³</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Volume total produit:</td>
                                                        <td class="text-end">${totalFinalVolume.toFixed(4)} m³</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-sm mb-0">
                                                    <tr class="table-danger">
                                                        <td><strong>Volume chute/déchet:</strong></td>
                                                        <td class="text-end"><strong>${wasteVolume.toFixed(4)} m³</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Pourcentage chute:</td>
                                                        <td class="text-end">${wastePercentage}%</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Différence:</td>
                                                        <td class="text-end">${(totalSourceVolume - totalFinalVolume).toFixed(4)} m³</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        ${totalFinalVolume > totalSourceVolume ?
                                            '<div class="alert alert-danger mt-2 mb-0"><i class="fas fa-exclamation-triangle"></i> Attention: Le volume produit dépasse le volume source!</div>' :
                                            ''}
                                        <small class="text-muted mt-2 d-block">
                                            <i class="fas fa-info-circle"></i>
                                            Calcul: (Volume total des blocs) - (Volume total des produits découpage)
                                        </small>
                                    </div>
                                `;

                                $('#calculatedResults').html(resultsHtml);
                                $('#calculatedResultsSection').removeClass('d-none');

                                // Update waste percentage input
                                $('#waste_percentage').val(wastePercentage);
                                $('#actual_waste_percentage').val(wastePercentage);
                            }
                        }).catch(error => {
                            console.error('Error fetching product details for waste calculation:',
                                error);
                        });

                        // Update insufficient stock alert
                        if (!isSufficient && stockInfo.available > 0) {
                            $('#insufficientStockList').html(`
                                <ul class="mb-0 mt-2">
                                    <li>${stockInfo.famille_name ? 'Famille: ' + stockInfo.famille_name : 'Produit source'}:
                                        Requis ${sourceBlocksNeeded.toFixed(2)},
                                        Disponible ${stockInfo.available.toFixed(2)} unités
                                    </li>
                                </ul>
                            `);
                            $('#insufficientStockAlert').removeClass('d-none');
                        } else if (stockInfo.available === 0) {
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

                        // Set hidden fields
                        $('#actual_product_id').val(finalProductId);
                        $('#actual_source_product_id').val(sourceProductId);
                        $('#actual_quantity_to_produce').val(decoupageProductsProduced);
                        $('#actual_required_quantity').val(sourceBlocksNeeded);
                        $('#actual_decoupage_ratio').val(decoupageRatio);

                        // Update volume calculations
                        updateVolumeCalculations('type2');
                    });
                }
            }

            // Type 3: Conversion calculation for multiple products
            function updateType3Calculation() {
                const sourceProductId = $('#type3_source_product_id').val();
                const familleId = getFamilleId();

                if (sourceProductId) {
                    // Get all product inputs
                    const productInputs = $('.type3-product-row');

                    if (productInputs.length === 0) {
                        // Hide results if no products added
                        $('#calculatedResultsSection').addClass('d-none');
                        $('#insufficientStockAlert').addClass('d-none');
                        return;
                    }

                    // Collect all product data
                    const products = [];
                    let totalFinalProducts = 0;
                    let totalSourceRequired = 0;
                    let totalVolume = 0;

                    productInputs.each(function() {
                        const productId = $(this).find('.type3-product-select').val();
                        const conversionRate = parseFloat($(this).find('.type3-conversion-rate').val()) ||
                            1;
                        const quantityToProduce = parseFloat($(this).find('.type3-quantity').val()) || 1;
                        const volumePerUnit = parseFloat($(this).find('.type3-volume').val()) || 0;

                        if (productId) {
                            const sourceRequired = quantityToProduce / conversionRate;
                            const productVolume = quantityToProduce * volumePerUnit;

                            products.push({
                                product_id: productId,
                                product_name: $(this).find('.type3-product-select option:selected')
                                    .text(),
                                conversion_rate: conversionRate,
                                quantity_to_produce: quantityToProduce,
                                source_required: sourceRequired,
                                volume_per_unit: volumePerUnit,
                                total_volume: productVolume
                            });

                            totalFinalProducts += quantityToProduce;
                            totalSourceRequired += sourceRequired;
                            totalVolume += productVolume;
                        }
                    });

                    // Update totals display
                    $('#type3_total_sous_blocs').val(totalSourceRequired.toFixed(2));
                    $('#type3_total_final_products').val(totalFinalProducts);
                    $('#type3_total_volume').val(totalVolume.toFixed(4));

                    // Get source stock information and product details
                    Promise.all([
                        getStockInfo(sourceProductId, familleId),
                        getProductDetails(sourceProductId)
                    ]).then(([stockInfo, sourceProduct]) => {
                        const isSufficient = stockInfo.available >= totalSourceRequired;

                        // Calculate waste for Type 3
                        let wasteHtml = '';
                        if (sourceProduct && sourceProduct.volume_per_unit > 0) {
                            const sourceVolumePerUnit = sourceProduct.volume_per_unit;
                            const totalSourceVolume = totalSourceRequired * sourceVolumePerUnit;
                            const wasteVolume = Math.max(0, totalSourceVolume - totalVolume);
                            const wastePercentage = totalSourceVolume > 0 ?
                                (wasteVolume / totalSourceVolume * 100).toFixed(2) : 0;

                            // Store waste percentage in hidden field and input
                            $('#actual_waste_percentage').val(wastePercentage);
                            $('#waste_percentage').val(wastePercentage);

                            wasteHtml = `
                                <div id="wasteCalculationSection" class="alert alert-warning mt-3">
                                    <h6><i class="fas fa-trash me-2"></i>Calcul du Chute/Déchet (Type 3)</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm mb-0">
                                                <tr>
                                                    <td>Volume du sous-bloc source:</td>
                                                    <td class="text-end">${sourceVolumePerUnit.toFixed(4)} m³</td>
                                                </tr>
                                                <tr>
                                                    <td>Sous-blocs totaux requis:</td>
                                                    <td class="text-end">${totalSourceRequired.toFixed(2)} unités</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Volume total source:</strong></td>
                                                    <td class="text-end"><strong>${totalSourceVolume.toFixed(4)} m³</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm mb-0">
                                                <tr>
                                                    <td><strong>Volume total produits finaux:</strong></td>
                                                    <td class="text-end"><strong>${totalVolume.toFixed(4)} m³</strong></td>
                                                </tr>
                                                <tr class="table-danger">
                                                    <td><strong>Volume chute/déchet:</strong></td>
                                                    <td class="text-end"><strong>${wasteVolume.toFixed(4)} m³</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Pourcentage chute:</td>
                                                    <td class="text-end">${wastePercentage}%</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    ${totalVolume > totalSourceVolume ?
                                        '<div class="alert alert-danger mt-2 mb-0"><i class="fas fa-exclamation-triangle"></i> Attention: Le volume produit dépasse le volume source!</div>' :
                                        ''}
                                    <small class="text-muted mt-2 d-block">
                                        <i class="fas fa-info-circle"></i>
                                        Calcul: (Volume total des sous-blocs) - (Volume total des produits finaux)
                                    </small>
                                </div>
                            `;
                        }

                        // Display results
                        let productsHtml = '';
                        products.forEach((product, index) => {
                            productsHtml += `
                    <tr>
                        <td>${product.product_name}</td>
                        <td class="text-end">${product.quantity_to_produce} unités</td>
                        <td class="text-end">${product.conversion_rate}</td>
                        <td class="text-end">${product.source_required.toFixed(2)}</td>
                        <td class="text-end">${product.volume_per_unit.toFixed(4)} m³</td>
                        <td class="text-end">${product.total_volume.toFixed(4)} m³</td>
                    </tr>
                `;
                        });

                        const resultsHtml = `
                <div class="row">
                    <div class="col-md-12">
                        <h6>Récapitulatif des Produits:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Produit Final</th>
                                        <th class="text-end">Quantité à Produire</th>
                                        <th class="text-end">Ratio (sous-bloc/produit)</th>
                                        <th class="text-end">Sous-blocs Requis</th>
                                        <th class="text-end">Volume/Unité</th>
                                        <th class="text-end">Volume Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${productsHtml}
                                </tbody>
                                <tfoot class="table-primary">
                                    <tr>
                                        <td><strong>Totaux:</strong></td>
                                        <td class="text-end"><strong>${totalFinalProducts} unités</strong></td>
                                        <td class="text-end">-</td>
                                        <td class="text-end"><strong>${totalSourceRequired.toFixed(2)} sous-blocs</strong></td>
                                        <td class="text-end">-</td>
                                        <td class="text-end"><strong>${totalVolume.toFixed(4)} m³</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6>Source Requise:</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>Sous-blocs totaux requis:</td>
                                <td class="text-end"><strong class="calculation-result">${totalSourceRequired.toFixed(2)} unités</strong></td>
                            </tr>
                            <tr>
                                <td>Produits finaux totaux:</td>
                                <td class="text-end"><strong>${totalFinalProducts} unités</strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Stock Source:</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>Produit source:</td>
                                <td class="text-end">${$('#type3_source_product_id option:selected').text().split('-')[1]?.trim() || 'N/A'}</td>
                            </tr>
                            ${stockInfo.famille_name ? `
                                                                                                                                            <tr>
                                                                                                                                                <td>Famille:</td>
                                                                                                                                                <td class="text-end">${stockInfo.famille_name}</td>
                                                                                                                                            </tr>
                                                                                                                                            ${stockInfo.location ? `
                                    <tr>
                                        <td>Location:</td>
                                        <td class="text-end">${stockInfo.location}</td>
                                    </tr>
                                ` : ''}
                                                                                                                                        ` : ''}
                            <tr>
                                <td>Stock disponible:</td>
                                <td class="text-end">${parseFloat(stockInfo.available).toFixed(2)} unités</td>
                            </tr>
                            <tr>
                                <td>Quantité totale:</td>
                                <td class="text-end">${parseFloat(stockInfo.current_quantity).toFixed(2)} unités</td>
                            </tr>
                            <tr>
                                <td>Quantité réservée:</td>
                                <td class="text-end">${parseFloat(stockInfo.reserved_quantity).toFixed(2)} unités</td>
                            </tr>
                            <tr class="${isSufficient ? 'table-success' : 'table-danger'}">
                                <td><strong>Statut:</strong></td>
                                <td class="text-end">
                                    <strong>
                                        ${isSufficient ?
                                            '<span class="text-success">✓ Suffisant</span>' :
                                            '<span class="text-danger">✗ Insuffisant</span>'}
                                    </strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                ${wasteHtml}
            `;

                        $('#calculatedResults').html(resultsHtml);
                        $('#calculatedResultsSection').removeClass('d-none');

                        // Update insufficient stock alert
                        if (!isSufficient && stockInfo.available > 0) {
                            $('#insufficientStockList').html(`
                    <ul class="mb-0 mt-2">
                        <li>${stockInfo.famille_name ? 'Famille: ' + stockInfo.famille_name : 'Produit source'}:
                            Requis ${totalSourceRequired.toFixed(2)},
                            Disponible ${stockInfo.available.toFixed(2)} unités
                        </li>
                    </ul>
                `);
                            $('#insufficientStockAlert').removeClass('d-none');
                        } else if (stockInfo.available === 0) {
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

                        // Update hidden fields
                        if (products.length > 0) {
                            $('#actual_product_id').val(products[0].product_id);
                            $('#actual_conversion_rate').val(products[0].conversion_rate);
                        }
                        $('#actual_source_product_id').val(sourceProductId);
                        $('#actual_quantity_to_produce').val(totalFinalProducts);
                        $('#actual_required_quantity').val(totalSourceRequired);

                    }).catch(error => {
                        console.error('Error in Type 3 calculation:', error);
                        $('#calculatedResults').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Erreur lors du calcul
                </div>
            `);
                        $('#calculatedResultsSection').removeClass('d-none');
                    });
                } else {
                    $('#calculatedResultsSection').addClass('d-none');
                    $('#insufficientStockAlert').addClass('d-none');
                }
            }

            // Function to add a new Type 3 product row
            function addType3ProductRow(productData = null) {
                const index = $('.type3-product-row').length;

                // Get all available final products
                const salesProducts = productOptionsData;

                // Build options for select
                let options = '<option value="">Sélectionner un produit</option>';
                salesProducts.forEach(product => {
                    const selected = productData && productData.product_id == product.id ?
                        'selected' : '';
                    options += `<option value="${product.id}" ${selected} data-volume="${product.volume_m3}">
                        ${product.text}
                        <span class="badge bg-success">Vente</span>
                    </option>`;
                });

                const rowHtml = `
                    <div class="type3-product-row card mb-3" data-index="${index}">
                        <div class="card-body">
                            <div class="row align-items-center">
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
                                <div class="col-md-2" style="margin-top: 20px;">
                                    <div class="form-group">
                                        <label class="form-label">Ratio *</label>
                                        <input type="number" class="form-control type3-conversion-rate"
                                               name="type3_products[${index}][conversion_rate]"
                                               value="${productData ? productData.conversion_rate : '1'}"
                                               step="0.01" min="0.01" placeholder="1.0" required>
                                        <small class="form-text text-muted">sous-bloc → produit</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label">Quantité *</label>
                                        <input type="number" class="form-control type3-quantity"
                                               name="type3_products[${index}][quantity_to_produce]"
                                               value="${productData ? productData.quantity_to_produce : '1'}"
                                               min="0.01" step="0.01" placeholder="100" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label">Volume/Unité</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control type3-volume"
                                                   data-index="${index}"
                                                   step="0.0001" min="0" readonly
                                                   value="${productData ? productData.volume_per_unit : '0'}">
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
                            <div class="row">
                                <div class="col-md-12">
                                    <small class="form-text text-muted type3-volume-info" data-index="${index}">
                                        ${productData ? productData.volume_per_unit + ' m³' : 'Sélectionnez un produit pour voir son volume'}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Add row to container
                $('#noProductsMessage').addClass('d-none');
                $('#type3ProductsContainer').append(rowHtml);

                // Initialize Select2 for the new select
                $(`select[name="type3_products[${index}][product_id]"]`).select2({
                    language: "fr",
                    placeholder: "Sélectionner un produit",
                    allowClear: true
                });

                // Trigger calculation
                updateType3Calculation();
            }

            // Event handler for adding Type 3 products
            $('#addType3Product').on('click', function() {
                if (!isDisabled) {
                    addType3ProductRow();
                }
            });

            // Event handler for removing Type 3 products
            $(document).on('click', '.remove-type3-product', function() {
                if (!isDisabled) {
                    const index = $(this).data('index');
                    $(`.type3-product-row[data-index="${index}"]`).remove();

                    // Reindex remaining rows
                    $('.type3-product-row').each(function(newIndex) {
                        $(this).attr('data-index', newIndex);
                        $(this).find('.type3-product-select').attr('name',
                            `type3_products[${newIndex}][product_id]`).data('index', newIndex);
                        $(this).find('.type3-conversion-rate').attr('name',
                            `type3_products[${newIndex}][conversion_rate]`);
                        $(this).find('.type3-quantity').attr('name',
                            `type3_products[${newIndex}][quantity_to_produce]`);
                        $(this).find('.type3-volume').attr('data-index', newIndex);
                        $(this).find('.type3-volume-info').attr('data-index', newIndex);
                        $(this).find('.remove-type3-product').data('index', newIndex);
                    });

                    // Show message if no products left
                    if ($('.type3-product-row').length === 0) {
                        $('#noProductsMessage').removeClass('d-none');
                    }

                    updateType3Calculation();
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

            function loadBOM(productId, quantity) {
                const materialSource = $('input[name="material_source"]:checked').val();
                const chutesVolume = $('#chutes_volume').val() || 0;

                let bomPercentage = $('#bom_percentage').val() || 100;
                bomPercentage = Math.round(parseFloat(bomPercentage) || 100);

                $('#bom_percentage').val(bomPercentage);
                $('#actual_bom_percentage').val(bomPercentage);

                $.ajax({
                    url: "{{ route('production-orders.get-bom') }}",
                    type: "GET",
                    data: {
                        product_id: productId,
                        quantity: quantity,
                        material_source: materialSource,
                        chutes_volume: chutesVolume,
                        bom_percentage: bomPercentage,
                        order_id: orderId
                    },
                    beforeSend: function() {
                        $('#bomTableBody').html(`
                            <tr>
                                <td colspan="9" class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    Chargement de la nomenclature...
                                </td>
                            </tr>
                        `);
                        $('#bomCard').removeClass('d-none');
                        $('#bom-quantity-display').text(quantity);

                        // Update material source info
                        let sourceInfo = '';
                        if (materialSource === 'bom_only') {
                            sourceInfo =
                                '<span class="badge bg-primary">100% Matières Nouvelles</span>';
                        } else if (materialSource === 'chutes_only') {
                            sourceInfo = '<span class="badge bg-warning">100% Chutes Recyclées</span>';
                        } else if (materialSource === 'both') {
                            sourceInfo =
                                `<span class="badge bg-success">${bomPercentage}% Matières Nouvelles + ${100-bomPercentage}% Chutes</span>`;
                        }
                        $('#materialSourceInfo').html(sourceInfo);
                    },
                    success: function(response) {
                        if (response.success) {
                            if (response.html) {
                                $('#bomCard').removeClass('d-none');
                                $('#bomTableBody').html(response.html);
                                $('#noBomAlert').addClass('d-none');
                                $('#bomStockSummary').removeClass('d-none');

                                // Update total cost
                                if (response.total_cost) {
                                    $('#bomTableFooter').html(`
                            <tr class="table-primary">
                                <td colspan="7" class="text-end"><strong>Coût Total Estimé:</strong></td>
                                <td class="text-end"><strong>${response.total_cost} DH</strong></td>
                                <td></td>
                            </tr>
                        `);
                                }

                                // Show/hide insufficient materials alert
                                if (response.insufficient_materials && response.insufficient_materials
                                    .length > 0) {
                                    let alertHtml = '<ul class="mb-0 mt-2">';
                                    let insufficientCount = 0;
                                    response.insufficient_materials.forEach(function(material) {
                                        alertHtml +=
                                            `<li><strong>${material.material}:</strong> Requis ${material.required} ${material.unit}, Disponible ${material.available} ${material.unit}</li>`;
                                        insufficientCount++;
                                    });
                                    alertHtml += '</ul>';

                                    $('#insufficientStockList').html(alertHtml);
                                    $('#insufficientStockAlert').removeClass('d-none');

                                    // Update stock summary
                                    let stockSummaryHtml = `
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Stock Insuffisant</h6>
                                <p class="mb-2">${insufficientCount} matière(s) ont un stock insuffisant:</p>
                                <ul class="mb-0">
                        `;
                                    response.insufficient_materials.forEach(function(material) {
                                        const shortage = parseFloat(material.required.replace(
                                            ',', '')) - parseFloat(material.available
                                            .replace(',', ''));
                                        stockSummaryHtml += `
                                <li>
                                    <strong>${material.material}:</strong>
                                    <span class="text-danger">Manque ${shortage.toFixed(4)} ${material.unit}</span>
                                </li>
                            `;
                                    });
                                    stockSummaryHtml += `
                                </ul>
                            </div>
                        `;

                                    $('#stockSummaryContent').html(stockSummaryHtml);
                                } else {
                                    $('#insufficientStockAlert').addClass('d-none');

                                    // Show success stock summary
                                    let stockSummaryHtml = `
                            <div class="alert alert-success">
                                <h6><i class="fas fa-check-circle me-2"></i>Stock Suffisant</h6>
                                <p class="mb-0">Toutes les matières premières sont disponibles en stock.</p>
                            </div>
                            <div class="mt-3">
                                <h6>Détails Stock:</h6>
                                <p class="mb-1"><small>Quantité produite: ${quantity} unités</small></p>
                                <p class="mb-1"><small>Type de production: ${response.material_source === 'bom_only' ? 'Matières Nouvelles' : response.material_source === 'chutes_only' ? 'Chutes Recyclées' : 'Mixte'}</small></p>
                        `;

                                    if (response.material_source === 'both') {
                                        stockSummaryHtml += `
                                <p class="mb-0"><small>Ratio: ${bomPercentage}% matières nouvelles, ${100-bomPercentage}% chutes</small></p>
                            `;
                                    }

                                    stockSummaryHtml += `</div>`;

                                    $('#stockSummaryContent').html(stockSummaryHtml);
                                }

                                // Update cost summary
                                let costSummaryHtml = `
                        <h6>Coût de Production:</h6>
                        <table class="table table-sm">
                    `;

                                if (response.material_source === 'bom_only' || response
                                    .material_source === 'both') {
                                    costSummaryHtml += `
                            <tr>
                                <td>Coût matières:</td>
                                <td class="text-end">${response.total_cost} DH</td>
                            </tr>
                        `;
                                }

                                if (response.material_source === 'chutes_only' || response
                                    .material_source === 'both') {
                                    costSummaryHtml += `
                            <tr>
                                <td>Chutes recyclées:</td>
                                <td class="text-end">0 DH</td>
                            </tr>
                        `;
                                }

                                costSummaryHtml += `
                            <tr class="table-primary">
                                <td><strong>Coût total:</strong></td>
                                <td class="text-end"><strong>${response.total_cost} DH</strong></td>
                            </tr>
                            <tr>
                                <td>Coût unitaire:</td>
                                <td class="text-end">${(parseFloat(response.total_cost.replace(',', '')) / quantity).toFixed(2)} DH/unité</td>
                            </tr>
                        </table>
                    `;

                                $('#costSummaryContent').html(costSummaryHtml);

                            } else {
                                $('#bomCard').addClass('d-none');
                                $('#noBomAlert').removeClass('d-none');
                                $('#noBomAlert').find('p').text(response.message ||
                                    'Aucune nomenclature disponible');
                                $('#insufficientStockAlert').addClass('d-none');
                                $('#bomStockSummary').addClass('d-none');
                            }
                        } else {
                            $('#bomCard').addClass('d-none');
                            $('#noBomAlert').removeClass('d-none');
                            $('#noBomAlert').find('p').text(response.message ||
                                'Erreur lors du chargement de la BOM');
                            $('#insufficientStockAlert').addClass('d-none');
                            $('#bomStockSummary').addClass('d-none');
                        }
                    },
                    error: function(xhr) {
                        console.error('BOM loading error:', xhr);
                        $('#bomCard').addClass('d-none');
                        $('#noBomAlert').removeClass('d-none');
                        $('#noBomAlert').find('p').text('Erreur lors du chargement de la nomenclature');
                        $('#insufficientStockAlert').addClass('d-none');
                        $('#bomStockSummary').addClass('d-none');
                    }
                });
            }

            // Event handlers for Type 1
            $('#type1_product_id').change(function() {
                const productId = $(this).val();
                if (productId) {
                    loadFamilles(productId, 'type1');
                    const quantity = $('#type1_quantity').val() || 1;
                    loadBOM(productId, quantity);
                    updateVolumeCalculations('type1');
                    checkChutesStock();
                } else {
                    $('#familleContainer').empty();
                    $('#bomCard').addClass('d-none');
                }
            });

            $('#type1_quantity').on('input', function() {
                const productId = $('#type1_product_id').val();
                const quantity = $(this).val();
                if (productId && quantity >= 1) {
                    loadBOM(productId, quantity);
                    updateVolumeCalculations('type1');
                }
            });

            // Event handlers for Type 2
            $('#type2_source_product_id').change(function() {
                const productId = $(this).val();

                // Reset famille selection
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

            $('#type2_final_product_id').change(function() {
                updateType2Calculation();
                updateVolumeCalculations('type2');
            });

            $('#type2_quantity, #decoupage_ratio').on('input', function() {
                updateType2Calculation();
                updateVolumeCalculations('type2');
            });

            // Event handlers for Type 3
            $('#type3_source_product_id').change(function() {
                const productId = $(this).val();

                // Reset famille selection
                $('#source_famille_id').val('').trigger('change');
                $('#familleContainer').empty();

                if (productId) {
                    loadFamilles(productId, 'type3');
                    updateType3Calculation();
                    updateVolumeCalculations('type3');
                } else {
                    $('#calculatedResultsSection').addClass('d-none');
                    $('#insufficientStockAlert').addClass('d-none');
                }
            });

            $(document).on('input', '.type3-conversion-rate, .type3-quantity', function() {
                if (!isDisabled) {
                    updateType3Calculation();
                }
            });

            // Cancel production button
            $('#cancelProductionBtn').click(function() {
                $('#cancelProductionModal').modal('show');
            });

            // Confirm cancel production
            $('#confirmCancelProduction').click(function() {
                const reason = $('#cancellationReason').val();
                const notes = $('#additionalNotes').val();

                if (!reason) {
                    showToast('error', 'Veuillez sélectionner une raison d\'annulation');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span> Annulation...'
                );

                $.ajax({
                    url: "{{ route('production-orders.cancel-production', $order->order_id) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        reason: reason,
                        additional_notes: notes
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#cancelProductionModal').modal('hide');
                            showToast('success', response.message);
                            setTimeout(() => {
                                window.location.href =
                                    "{{ route('production-orders.index') }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors de l\'annulation');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-ban me-2"></i>Annuler la Production'
                        );
                    }
                });
            });

            // Form submission
            $('#editProductionOrderForm').submit(function(e) {
                e.preventDefault();

                // Clear any previous validation errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').hide();

                let isValid = true;
                let firstInvalidField = null;

                // Common validation for all types
                if (!$('#priority').val()) {
                    $('#priority').addClass('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = $('#priority');
                }

                if (!$('#start_date').val()) {
                    $('#start_date').addClass('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = $('#start_date');
                }

                if (!$('#expected_completion_date').val()) {
                    $('#expected_completion_date').addClass('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = $('#expected_completion_date');
                }

                // Validate dates
                const startDate = new Date($('#start_date').val());
                const endDate = new Date($('#expected_completion_date').val());
                if (endDate < startDate) {
                    $('#expected_completion_date').addClass('is-invalid');
                    $('#expected_completion_date').siblings('.invalid-feedback').text(
                        'La date de fin ne peut pas être antérieure à la date de début').show();
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = $('#expected_completion_date');
                }

                // Type-specific validation - ONLY VALIDATE VISIBLE AND ENABLED FIELDS
                if (currentProductionType === 'type1') {
                    // Validate Type 1 fields (only if visible and not disabled)
                    if ($('#type1_product_id').is(':visible') && !$('#type1_product_id').is(':disabled') &&
                        !$('#type1_product_id').val()) {
                        $('#type1_product_id').addClass('is-invalid');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = $('#type1_product_id');
                    }

                    const type1Quantity = $('#type1_quantity').val();
                    if ($('#type1_quantity').is(':visible') && !$('#type1_quantity').is(':disabled') && (!
                            type1Quantity || type1Quantity < 1)) {
                        $('#type1_quantity').addClass('is-invalid');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = $('#type1_quantity');
                    }

                    // Validate material source fields
                    const materialSource = $('input[name="material_source"]:checked').val();
                    if ((materialSource === 'chutes_only' || materialSource === 'both') &&
                        $('#chutes_volume').is(':visible') && !$('#chutes_volume').is(':disabled')) {
                        const chutesVolume = $('#chutes_volume').val();
                        if (!chutesVolume || parseFloat(chutesVolume) <= 0) {
                            $('#chutes_volume').addClass('is-invalid');
                            isValid = false;
                            if (!firstInvalidField) firstInvalidField = $('#chutes_volume');
                        }
                    }

                    if (materialSource === 'both' && $('#bom_percentage').is(':visible') && !$(
                            '#bom_percentage').is(':disabled')) {
                        const bomPercentage = $('#bom_percentage').val();
                        if (!bomPercentage || bomPercentage < 0 || bomPercentage > 100) {
                            $('#bom_percentage').addClass('is-invalid');
                            isValid = false;
                            if (!firstInvalidField) firstInvalidField = $('#bom_percentage');
                        }
                    }

                } else if (currentProductionType === 'type2') {
                    // Validate Type 2 fields (only if visible and not disabled)
                    if ($('#type2_source_product_id').is(':visible') && !$('#type2_source_product_id').is(
                            ':disabled') &&
                        !$('#type2_source_product_id').val()) {
                        $('#type2_source_product_id').addClass('is-invalid');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = $('#type2_source_product_id');
                    }

                    if ($('#type2_final_product_id').is(':visible') && !$('#type2_final_product_id').is(
                            ':disabled') &&
                        !$('#type2_final_product_id').val()) {
                        $('#type2_final_product_id').addClass('is-invalid');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = $('#type2_final_product_id');
                    }

                    const type2Quantity = $('#type2_quantity').val();
                    if ($('#type2_quantity').is(':visible') && !$('#type2_quantity').is(':disabled') &&
                        (!type2Quantity || type2Quantity < 1)) {
                        $('#type2_quantity').addClass('is-invalid');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = $('#type2_quantity');
                    }

                    const decoupageRatio = $('#decoupage_ratio').val();
                    if ($('#decoupage_ratio').is(':visible') && !$('#decoupage_ratio').is(':disabled') &&
                        (!decoupageRatio || decoupageRatio < 1)) {
                        $('#decoupage_ratio').addClass('is-invalid');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = $('#decoupage_ratio');
                    }

                } else if (currentProductionType === 'type3') {
                    // Validate Type 3 fields (only if visible and not disabled)
                    if ($('#type3_source_product_id').is(':visible') && !$('#type3_source_product_id').is(
                            ':disabled') &&
                        !$('#type3_source_product_id').val()) {
                        $('#type3_source_product_id').addClass('is-invalid');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = $('#type3_source_product_id');
                    }

                    // Validate Type 3 products
                    const productRows = $('.type3-product-row');
                    if (productRows.length === 0) {
                        showToast('error', 'Veuillez ajouter au moins un produit final');
                        return;
                    }

                    productRows.each(function() {
                        const productSelect = $(this).find('.type3-product-select');
                        const conversionRate = $(this).find('.type3-conversion-rate').val();
                        const quantity = $(this).find('.type3-quantity').val();

                        if (productSelect.is(':visible') && !productSelect.is(':disabled') && !
                            productSelect.val()) {
                            productSelect.addClass('is-invalid');
                            isValid = false;
                            if (!firstInvalidField) firstInvalidField = productSelect;
                        }

                        if ($(this).find('.type3-conversion-rate').is(':visible') && !$(this).find(
                                '.type3-conversion-rate').is(':disabled') &&
                            (!conversionRate || conversionRate < 0.01)) {
                            $(this).find('.type3-conversion-rate').addClass('is-invalid');
                            isValid = false;
                            if (!firstInvalidField) firstInvalidField = $(this).find(
                                '.type3-conversion-rate');
                        }

                        if ($(this).find('.type3-quantity').is(':visible') && !$(this).find(
                                '.type3-quantity').is(':disabled') &&
                            (!quantity || quantity < 1)) {
                            $(this).find('.type3-quantity').addClass('is-invalid');
                            isValid = false;
                            if (!firstInvalidField) firstInvalidField = $(this).find(
                                '.type3-quantity');
                        }
                    });
                }

                if (!isValid) {
                    showToast('error', 'Veuillez corriger les erreurs dans le formulaire.');
                    if (firstInvalidField) {
                        firstInvalidField.focus();
                    }
                    return;
                }

                // Prepare data - ensure bom_percentage is integer
                let bomPercentage = $('#bom_percentage').val();
                if (bomPercentage) {
                    bomPercentage = Math.round(parseFloat(bomPercentage) || 0);
                    $('#bom_percentage').val(bomPercentage);
                    $('#actual_bom_percentage').val(bomPercentage);
                }

                // Ensure decoupage_ratio is integer
                let decoupageRatio = $('#decoupage_ratio').val();
                if (decoupageRatio) {
                    decoupageRatio = parseInt(decoupageRatio) || 1;
                    $('#decoupage_ratio').val(decoupageRatio);
                    $('#actual_decoupage_ratio').val(decoupageRatio);
                }

                const formData = $(this).serialize();
                const submitBtn = $('#submitBtn');

                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Mise à jour...'
                );

                $.ajax({
                    url: "{{ route('production-orders.update', $order->order_id) }}",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(() => {
                                window.location.href =
                                    "{{ route('production-orders.show', $order->order_id) }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(
                                '<i class="fas fa-save me-1"></i> Mettre à jour'
                            );
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
                                'Une erreur est survenue lors de la mise à jour';
                        }

                        showToast('error', errorMessage);
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i> Mettre à jour'
                        );
                    }
                });
            });

            function updateRequiredFields() {
                const productionType = currentProductionType;
                const materialSource = $('input[name="material_source"]:checked').val();

                // Remove all required attributes first
                $('input, select, textarea').removeAttr('required');

                // Add novalidate to form to disable HTML5 validation
                $('#editProductionOrderForm').attr('novalidate', 'novalidate');

                // Add required attributes based on production type - ONLY FOR VISIBLE AND ENABLED FIELDS
                if (productionType === 'type1') {
                    // Type 1 specific fields
                    if ($('#type1_product_id').is(':visible') && !$('#type1_product_id').is(':disabled')) {
                        $('#type1_product_id').attr('required', 'required');
                    }
                    if ($('#type1_quantity').is(':visible') && !$('#type1_quantity').is(':disabled')) {
                        $('#type1_quantity').attr('required', 'required');
                    }

                    // Material source specific requirements
                    if ((materialSource === 'chutes_only' || materialSource === 'both') &&
                        $('#chutes_volume').is(':visible') && !$('#chutes_volume').is(':disabled')) {
                        $('#chutes_volume').attr('required', 'required');
                    }
                    if (materialSource === 'both' &&
                        $('#bom_percentage').is(':visible') && !$('#bom_percentage').is(':disabled')) {
                        $('#bom_percentage').attr('required', 'required');
                    }

                } else if (productionType === 'type2') {
                    // Type 2 specific fields
                    if ($('#type2_source_product_id').is(':visible') && !$('#type2_source_product_id').is(
                            ':disabled')) {
                        $('#type2_source_product_id').attr('required', 'required');
                    }
                    if ($('#type2_final_product_id').is(':visible') && !$('#type2_final_product_id').is(
                            ':disabled')) {
                        $('#type2_final_product_id').attr('required', 'required');
                    }
                    if ($('#type2_quantity').is(':visible') && !$('#type2_quantity').is(':disabled')) {
                        $('#type2_quantity').attr('required', 'required');
                    }
                    if ($('#decoupage_ratio').is(':visible') && !$('#decoupage_ratio').is(':disabled')) {
                        $('#decoupage_ratio').attr('required', 'required');
                    }

                } else if (productionType === 'type3') {
                    // Type 3 specific fields
                    if ($('#type3_source_product_id').is(':visible') && !$('#type3_source_product_id').is(
                            ':disabled')) {
                        $('#type3_source_product_id').attr('required', 'required');
                    }

                    // Type 3 product rows - only if visible and not disabled
                    $('.type3-product-select:visible:not(:disabled), .type3-conversion-rate:visible:not(:disabled), .type3-quantity:visible:not(:disabled)')
                        .each(function() {
                            $(this).attr('required', 'required');
                        });
                }

                // Common required fields (always visible and not disabled)
                if (!$('#priority').is(':disabled')) $('#priority').attr('required', 'required');
                if (!$('#start_date').is(':disabled')) $('#start_date').attr('required', 'required');
                if (!$('#expected_completion_date').is(':disabled')) $('#expected_completion_date').attr('required',
                    'required');
            }

            function toggleProductionTypeSections(productionType) {
                // Reset all dynamic sections
                $('#type1Section').addClass('d-none');
                $('#type2Section').addClass('d-none');
                $('#type3Section').addClass('d-none');
                $('#chutesSection').addClass('d-none');
                $('#bomCard').addClass('d-none');
                $('#conversionSection').addClass('d-none');
                $('#type2ConversionSection').addClass('d-none');
                $('#calculatedResultsSection').addClass('d-none');
                $('#familleContainer').empty();
                $('#insufficientStockAlert').addClass('d-none');
                $('#noBomAlert').addClass('d-none');

                // Clear Type 3 products container
                $('#type3ProductsContainer').html(`
                    <div class="alert alert-info" id="noProductsMessage">
                        Cliquez sur "Ajouter un Produit" pour ajouter des produits finaux
                    </div>
                `);

                // Clear hidden fields
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

                // Reset material source to BOM only for Type 1
                $('#bomOnly').prop('checked', true);
                updateMaterialSourceSections();

                // Show selected type section
                if (productionType === 'type1') {
                    $('#type1Section').removeClass('d-none');
                    $('#chutesSection').removeClass('d-none'); // Show chutes section only for Type 1

                    // Load BOM for selected product if exists
                    const productId = $('#type1_product_id').val();
                    if (productId) {
                        loadFamilles(productId, 'type1');
                        const quantity = $('#type1_quantity').val() || 1;
                        loadBOM(productId, quantity);
                        updateVolumeCalculations('type1');
                        checkChutesStock();
                    }
                } else if (productionType === 'type2') {
                    $('#type2Section').removeClass('d-none');
                    $('#conversionSection').removeClass('d-none');
                    $('#type2ConversionSection').removeClass('d-none');
                    // DO NOT show chutes section or BOM card for Type 2
                    $('#chutesSection').addClass('d-none');
                    $('#bomCard').addClass('d-none');

                    // Reset values
                    $('#decoupage_ratio').val(1);

                    const productId = $('#type2_source_product_id').val();
                    if (productId) {
                        loadFamilles(productId, 'type2');
                        updateType2Calculation();
                        updateVolumeCalculations('type2');
                    }
                } else if (productionType === 'type3') {
                    $('#type3Section').removeClass('d-none');
                    $('#conversionSection').addClass('d-none');
                    // DO NOT show chutes section or BOM card for Type 3
                    $('#chutesSection').addClass('d-none');
                    $('#bomCard').addClass('d-none');

                    // Reset values
                    $('#type3_total_sous_blocs').val('');
                    $('#type3_total_volume').val('');
                    $('#type3_total_final_products').val('');

                    const sourceProductId = $('#type3_source_product_id').val();
                    if (sourceProductId) {
                        loadFamilles(sourceProductId, 'type3');
                        updateType3Calculation();
                        updateVolumeCalculations('type3');
                    }
                }
            }


            // Production type change handler
            $('input[name="production_type"]').change(function() {
                currentProductionType = $(this).val();
                toggleProductionTypeSections(currentProductionType);
                updateRequiredFields();
            });

            // Material source change handler
            $('input[name="material_source"]').change(function() {
                const materialSource = $(this).val();
                toggleMaterialSourceSections(materialSource);
                updateRequiredFields();
            });

            toggleProductionTypeSections(currentProductionType);
            updateRequiredFields();

            // Toast notification function
            function showToast(type, message) {
                const toast = $(`
                    <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">${message}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `);

                $('#toast-container').append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();
                setTimeout(() => toast.remove(), 5000);
            }

            // Initial setup based on current production type
            if (currentProductionType === 'type1') {
                // Load Type 1 initial data
                const productId = $('#type1_product_id').val();
                const quantity = $('#type1_quantity').val() || 1;
                if (productId) {
                    loadFamilles(productId, 'type1');
                    loadBOM(productId, quantity);
                    checkChutesStock();
                }
                updateMaterialSourceSections();
            } else if (currentProductionType === 'type2') {
                // Load Type 2 initial data and trigger calculation
                const sourceProductId = $('#type2_source_product_id').val();
                if (sourceProductId) {
                    loadFamilles(sourceProductId, 'type2');
                    setTimeout(() => {
                        updateType2Calculation();
                    }, 500);
                }
            } else if (currentProductionType === 'type3') {
                // Load Type 3 initial data and trigger calculation
                const sourceProductId = $('#type3_source_product_id').val();
                if (sourceProductId) {
                    loadFamilles(sourceProductId, 'type3');
                    setTimeout(() => {
                        updateType3Calculation();
                    }, 500);
                }
            }

            // Initialize material source sections
            updateMaterialSourceSections();
            updateBomPercentageInfo();
        });
    </script>
@endpush
