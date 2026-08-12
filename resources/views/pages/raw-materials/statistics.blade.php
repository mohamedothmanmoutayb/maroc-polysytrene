@extends('layouts.app')

@section('title', 'Statistiques Matière Première')

@php
    $movementTypeLabels = [
        'purchase' => 'Achat',
        'production_consumption' => 'Consommation production',
        'adjustment' => 'Ajustement',
        'return' => 'Retour',
        'transfer' => 'Transfert',
        'waste_recovery' => 'Récupération chutes',
        'sale' => 'Vente',
        'cancellation' => 'Annulation',
    ];
    $outMovements = ['production_consumption', 'sale', 'transfer'];

    $poStatusLabels = [
        'pending' => 'En attente',
        'approved' => 'Approuvé',
        'in_progress' => 'En cours',
        'completed' => 'Terminé',
        'cancelled' => 'Annulé',
    ];
    $poStatusBadges = [
        'pending' => 'bg-secondary',
        'approved' => 'bg-info',
        'in_progress' => 'bg-warning',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
    ];
    $paymentStatusLabels = [
        'pending' => 'Non payé',
        'partial' => 'Avance',
        'paid' => 'Payé',
    ];
    $paymentStatusBadges = [
        'pending' => 'bg-danger',
        'partial' => 'bg-warning',
        'paid' => 'bg-success',
    ];
    $clientTypeLabels = [
        'client' => 'Client',
        'grossiste' => 'Grossiste',
        'commerciale' => 'Commerciale',
        'special' => 'Spécial',
    ];
    $clientTypeBadges = [
        'client' => 'bg-primary',
        'grossiste' => 'bg-info',
        'commerciale' => 'bg-warning',
        'special' => 'bg-dark',
    ];

    $purchasedQty = (float) ($stats['purchases']->total_qty ?? 0);
    $purchasedAmount = (float) ($stats['purchases']->total_amount ?? 0);
    $receivedQty = (float) ($stats['purchases']->received_qty ?? 0);
    $avgPurchasePrice = $purchasedQty > 0 ? $purchasedAmount / $purchasedQty : 0;

    $plannedQty = (float) ($stats['consumption']->planned_qty ?? 0);
    $consumedQty = (float) ($stats['consumption']->actual_qty ?? 0);
    $wasteQty = (float) ($stats['consumption']->waste_qty ?? 0);
    $consumedCost = (float) ($stats['consumption']->total_cost ?? 0);

    $soldQty = (float) ($stats['sales']->total_qty ?? 0);
    $soldAmount = (float) ($stats['sales']->total_amount ?? 0);
    $creditQty = (float) ($stats['credits']->total_qty ?? 0);
    $creditAmount = (float) ($stats['credits']->total_amount ?? 0);
    $avgSalePrice = $soldQty > 0 ? $soldAmount / $soldQty : 0;

    $currentStock = (float) $stats['current_stock'];
    $stockValue = (float) $stats['stock_value'];
    $averageCost = (float) $stats['average_cost'];

    $minLevel = (float) $material->min_stock_level;
    $maxLevel = (float) $material->max_stock_level;
    if ($currentStock <= 0) {
        $stockBadge = ['bg-danger', 'Rupture'];
    } elseif ($currentStock <= $minLevel) {
        $stockBadge = ['bg-danger', 'Stock bas'];
    } elseif ($maxLevel > 0 && $currentStock >= $maxLevel) {
        $stockBadge = ['bg-warning', 'Stock élevé'];
    } else {
        $stockBadge = ['bg-success', 'Normal'];
    }
@endphp

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Statistiques de la Matière Première</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('raw-materials.index') }}">
                                        Matières Premières
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('raw-materials.show', $material->material_id) }}">
                                        {{ $material->material_name }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">Statistiques</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Material header + filters -->
        <div class="card mb-4">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0" style="color:white">
                    <i class="fas fa-chart-line me-2"></i>{{ $material->material_code }} — {{ $material->material_name }}
                </h5>
                <a href="{{ route('raw-materials.show', $material->material_id) }}" class="btn btn-light btn-sm">
                    <i class="fas fa-eye me-1"></i> Fiche matière
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-2 col-6">
                        <div class="text-muted small">Catégorie</div>
                        <div class="fw-semibold">{{ $material->category->category_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="text-muted small">Unité</div>
                        <div class="fw-semibold text-capitalize">{{ $unitLabel }}</div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="text-muted small">Stock actuel</div>
                        <div class="fw-semibold">
                            {{ number_format($currentStock, 2, ',', '.') }} {{ $unitLabel }}
                            <span class="badge {{ $stockBadge[0] }} ms-1">{{ $stockBadge[1] }}</span>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="text-muted small">Seuils min / max</div>
                        <div class="fw-semibold">
                            {{ number_format($minLevel, 2, ',', '.') }} / {{ number_format($maxLevel, 2, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="text-muted small">Coût moyen (FIFO)</div>
                        <div class="fw-semibold">{{ number_format($averageCost, 2, ',', '.') }} DH</div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="text-muted small">Statut</div>
                        <div class="fw-semibold">
                            <span class="badge {{ $material->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $material->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <hr>

                <form method="GET" action="{{ route('raw-materials.material-statistics', $material->material_id) }}"
                    class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Du</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Au</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('raw-materials.material-statistics', $material->material_id) }}"
                            class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Réinitialiser
                        </a>
                        @if ($dateFrom || $dateTo)
                            <span class="badge bg-info ms-2">
                                Période : {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '…' }}
                                → {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : '…' }}
                            </span>
                        @else
                            <span class="badge bg-secondary ms-2">Toute la période</span>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- KPI -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-start border-4 border-primary h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Quantité achetée</div>
                        <h3 class="mb-0 mt-1">{{ number_format($purchasedQty, 2, ',', '.') }}</h3>
                        <div class="text-muted small text-capitalize">{{ $unitLabel }}</div>
                        <hr class="my-2">
                        <div class="small">
                            <strong>{{ number_format($purchasedAmount, 2, ',', '.') }} DH</strong>
                            <span class="text-muted">—
                                {{ (int) ($stats['purchases']->purchases_count ?? 0) }} achat(s),
                                {{ (int) ($stats['purchases']->suppliers_count ?? 0) }} fournisseur(s)
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-start border-4 border-warning h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Consommé en production</div>
                        <h3 class="mb-0 mt-1">{{ number_format($consumedQty, 2, ',', '.') }}</h3>
                        <div class="text-muted small text-capitalize">{{ $unitLabel }}</div>
                        <hr class="my-2">
                        <div class="small">
                            <strong>{{ number_format($consumedCost, 2, ',', '.') }} DH</strong>
                            <span class="text-muted">—
                                {{ (int) ($stats['consumption']->orders_count ?? 0) }} ordre(s)
                            </span>
                            @if ($wasteQty > 0)
                                <span class="badge bg-light text-danger ms-1" style="color: #dc3545 !important">
                                    {{ number_format($wasteQty, 2, ',', '.') }} chutes
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-start border-4 border-success h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Vendu</div>
                        <h3 class="mb-0 mt-1">{{ number_format($soldQty, 2, ',', '.') }}</h3>
                        <div class="text-muted small text-capitalize">{{ $unitLabel }}</div>
                        <hr class="my-2">
                        <div class="small">
                            <strong>{{ number_format($soldAmount, 2, ',', '.') }} DH</strong>
                            <span class="text-muted">—
                                {{ (int) ($stats['sales']->orders_count ?? 0) }} vente(s)
                            </span>
                            @if ($creditQty > 0)
                                <div class="text-danger">
                                    Avoirs : −{{ number_format($creditQty, 2, ',', '.') }} {{ $unitLabel }}
                                    (−{{ number_format($creditAmount, 2, ',', '.') }} DH)
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-start border-4 border-info h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Stock actuel</div>
                        <h3 class="mb-0 mt-1">{{ number_format($currentStock, 2, ',', '.') }}</h3>
                        <div class="text-muted small text-capitalize">{{ $unitLabel }}</div>
                        <hr class="my-2">
                        <div class="small">
                            <strong>{{ number_format($stockValue, 2, ',', '.') }} DH</strong>
                            <span class="text-muted">— {{ $stockLots->count() }} lot(s) restant(s)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI secondaires -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-tags text-primary"></i>
                            <span class="text-muted small text-uppercase">Prix d'achat moyen</span>
                        </div>
                        <h4 class="mb-0">{{ number_format($avgPurchasePrice, 2, ',', '.') }} DH</h4>
                        <div class="small text-muted">
                            Min {{ number_format((float) ($stats['purchases']->min_unit_price ?? 0), 2, ',', '.') }} —
                            Max {{ number_format((float) ($stats['purchases']->max_unit_price ?? 0), 2, ',', '.') }} DH
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-dolly text-secondary"></i>
                            <span class="text-muted small text-uppercase">Reçu / commandé</span>
                        </div>
                        <h4 class="mb-0">{{ number_format($receivedQty, 2, ',', '.') }}
                            <small class="text-muted fs-6 text-capitalize">{{ $unitLabel }}</small>
                        </h4>
                        <div class="small text-muted">
                            @if ($purchasedQty > 0)
                                {{ number_format(($receivedQty / $purchasedQty) * 100, 1, ',', '.') }} % de
                                {{ number_format($purchasedQty, 2, ',', '.') }} commandé(s)
                            @else
                                Aucun achat sur la période
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-scale-balanced text-warning"></i>
                            <span class="text-muted small text-uppercase">Planifié vs consommé</span>
                        </div>
                        <h4 class="mb-0">{{ number_format($plannedQty, 2, ',', '.') }}
                            <small class="text-muted fs-6">→ {{ number_format($consumedQty, 2, ',', '.') }}</small>
                        </h4>
                        <div class="small text-muted">
                            @if ($plannedQty > 0)
                                Écart : {{ number_format($consumedQty - $plannedQty, 2, ',', '.') }} {{ $unitLabel }}
                                ({{ number_format((($consumedQty - $plannedQty) / $plannedQty) * 100, 1, ',', '.') }} %)
                            @else
                                Aucune consommation planifiée
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-money-bill-trend-up text-success"></i>
                            <span class="text-muted small text-uppercase">Prix de vente moyen</span>
                        </div>
                        <h4 class="mb-0">{{ number_format($avgSalePrice, 2, ',', '.') }} DH</h4>
                        <div class="small text-muted">
                            @if ($avgSalePrice > 0 && $avgPurchasePrice > 0)
                                Marge unitaire :
                                <span class="{{ $avgSalePrice >= $avgPurchasePrice ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($avgSalePrice - $avgPurchasePrice, 2, ',', '.') }} DH
                                </span>
                            @else
                                Pas de vente sur la période
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="fas fa-chart-area me-2"></i>Évolution mensuelle</h6>
                    </div>
                    <div class="card-body">
                        @if ($purchasesMonthly->count() || $consumptionMonthly->count() || $salesMonthly->count())
                            <div id="materialTrendChart"></div>
                        @else
                            <div class="text-center text-muted py-5">Aucune donnée sur la période.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="fas fa-truck me-2"></i>Achats par fournisseur</h6>
                    </div>
                    <div class="card-body">
                        @if ($purchasesBySupplier->count())
                            <div id="supplierChart"></div>
                        @else
                            <div class="text-center text-muted py-5">Aucun achat sur la période.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-pills mb-3 flex-wrap gap-1" id="materialStatsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-fournisseurs"
                            type="button" role="tab">
                            <i class="fas fa-truck me-1"></i> Fournisseurs
                            <span class="badge bg-light text-dark ms-1"
                                style="color: #000 !important">{{ $purchasesBySupplier->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-achats" type="button"
                            role="tab">
                            <i class="fas fa-cart-shopping me-1"></i> Achats
                            <span class="badge bg-light text-dark ms-1"
                                style="color: #000 !important">{{ $purchaseLines->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-consommation" type="button"
                            role="tab">
                            <i class="fas fa-industry me-1"></i> Consommation
                            <span class="badge bg-light text-dark ms-1"
                                style="color: #000 !important">{{ $consumptionLines->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ventes" type="button"
                            role="tab">
                            <i class="fas fa-cash-register me-1"></i> Ventes
                            <span class="badge bg-light text-dark ms-1"
                                style="color: #000 !important">{{ $salesLines->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-mouvements" type="button"
                            role="tab">
                            <i class="fas fa-right-left me-1"></i> Mouvements
                            <span class="badge bg-light text-dark ms-1"
                                style="color: #000 !important">{{ $movements->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-lots" type="button"
                            role="tab">
                            <i class="fas fa-layer-group me-1"></i> Lots en stock
                            <span class="badge bg-light text-dark ms-1"
                                style="color: #000 !important">{{ $stockLots->count() }}</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- FOURNISSEURS -->
                    <div class="tab-pane fade show active" id="tab-fournisseurs" role="tabpanel">
                        @if ($purchasesBySupplier->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Fournisseur</th>
                                            <th class="text-end">Quantité</th>
                                            <th class="text-end">Part</th>
                                            <th class="text-end">Montant</th>
                                            <th class="text-end">PU moyen</th>
                                            <th class="text-end">PU min / max</th>
                                            <th class="text-end">Achats</th>
                                            <th>Dernier achat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchasesBySupplier as $i => $supplier)
                                            @php
                                                $qty = (float) $supplier->total_qty;
                                                $share = $purchasedQty > 0 ? ($qty / $purchasedQty) * 100 : 0;
                                            @endphp
                                            <tr>
                                                <td>
                                                    @if ($i === 0)
                                                        <span class="badge bg-warning"><i class="fas fa-crown"></i></span>
                                                    @else
                                                        {{ $i + 1 }}
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('suppliers.show', $supplier->supplier_id) }}"
                                                        class="text-decoration-none fw-semibold">
                                                        {{ $supplier->supplier_name }}
                                                    </a>
                                                    @if ($supplier->phone)
                                                        <div class="text-muted small">
                                                            <i class="fas fa-phone fa-xs me-1"></i>{{ $supplier->phone }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="text-end fw-semibold">
                                                    {{ number_format($qty, 2, ',', '.') }}
                                                    <span class="text-muted small">{{ $unitLabel }}</span>
                                                </td>
                                                <td class="text-end" style="min-width:110px">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1" style="height:6px">
                                                            <div class="progress-bar bg-primary"
                                                                style="width: {{ min(100, $share) }}%"></div>
                                                        </div>
                                                        <span
                                                            class="small text-muted">{{ number_format($share, 1, ',', '.') }}%</span>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $supplier->total_amount, 2, ',', '.') }} DH
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ $qty > 0 ? number_format($supplier->total_amount / $qty, 2, ',', '.') : '—' }}
                                                </td>
                                                <td class="text-end text-muted small">
                                                    {{ number_format((float) $supplier->min_unit_price, 2, ',', '.') }} /
                                                    {{ number_format((float) $supplier->max_unit_price, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end">
                                                    <span class="badge bg-light text-dark"
                                                        style="color: #000 !important">{{ $supplier->purchases_count }}</span>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($supplier->last_purchase_date)->format('d/m/Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td colspan="2">Total — {{ $purchasesBySupplier->count() }} fournisseur(s)</td>
                                            <td class="text-end">{{ number_format($purchasedQty, 2, ',', '.') }}</td>
                                            <td></td>
                                            <td class="text-end">{{ number_format($purchasedAmount, 2, ',', '.') }} DH</td>
                                            <td colspan="4"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-truck fs-1 d-block mb-3"></i>
                                Aucun achat de cette matière sur la période.
                            </div>
                        @endif
                    </div>

                    <!-- ACHATS -->
                    <div class="tab-pane fade" id="tab-achats" role="tabpanel">
                        @if ($purchaseLines->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>N° Achat</th>
                                            <th>Date</th>
                                            <th>Fournisseur</th>
                                            <th class="text-end">Quantité</th>
                                            <th class="text-end">Reçu</th>
                                            <th class="text-end">PU</th>
                                            <th class="text-end">Total</th>
                                            <th>Réception</th>
                                            <th>Paiement</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchaseLines as $line)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('raw-material-purchases.show', $line->purchase_id) }}"
                                                        class="text-decoration-none fw-semibold">
                                                        {{ $line->purchase_number }}
                                                    </a>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($line->purchase_date)->format('d/m/Y') }}</td>
                                                <td>
                                                    @if ($line->supplier_id)
                                                        <a href="{{ route('suppliers.show', $line->supplier_id) }}"
                                                            class="text-decoration-none">{{ $line->supplier_name }}</a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="text-end fw-semibold">
                                                    {{ number_format((float) $line->quantity, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $line->received_quantity, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $line->unit_price, 2, ',', '.') }} DH
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $line->total_price, 2, ',', '.') }} DH
                                                </td>
                                                <td>
                                                    @if ($line->actual_delivery_date)
                                                        <span class="badge bg-success">
                                                            {{ \Carbon\Carbon::parse($line->actual_delivery_date)->format('d/m/Y') }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">En attente</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge {{ $paymentStatusBadges[$line->payment_status] ?? 'bg-secondary' }}">
                                                        {{ $paymentStatusLabels[$line->payment_status] ?? $line->payment_status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td colspan="3">Total — {{ $purchaseLines->count() }} ligne(s)</td>
                                            <td class="text-end">{{ number_format($purchasedQty, 2, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($receivedQty, 2, ',', '.') }}</td>
                                            <td></td>
                                            <td class="text-end">{{ number_format($purchasedAmount, 2, ',', '.') }} DH</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-cart-shopping fs-1 d-block mb-3"></i>
                                Aucun achat sur la période.
                            </div>
                        @endif
                    </div>

                    <!-- CONSOMMATION -->
                    <div class="tab-pane fade" id="tab-consommation" role="tabpanel">
                        @if ($consumptionLines->count())
                            <div class="alert alert-light border small">
                                <i class="fas fa-circle-info me-1"></i>
                                La date affichée est celle de l'ordre de fabrication (fin réelle, sinon début).
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>N° Ordre</th>
                                            <th>Date</th>
                                            <th>Article produit</th>
                                            <th class="text-end">Planifié</th>
                                            <th class="text-end">Consommé</th>
                                            <th class="text-end">Chutes</th>
                                            <th class="text-end">Coût unitaire</th>
                                            <th class="text-end">Coût total</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($consumptionLines as $line)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('production-orders.show', $line->order_id) }}"
                                                        class="text-decoration-none fw-semibold">
                                                        {{ $line->order_number }}
                                                    </a>
                                                </td>
                                                <td>
                                                    {{ $line->consumption_date ? \Carbon\Carbon::parse($line->consumption_date)->format('d/m/Y') : '—' }}
                                                </td>
                                                <td>{{ $line->product_name ?: '—' }}</td>
                                                <td class="text-end text-muted">
                                                    {{ number_format((float) $line->planned_quantity, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end fw-semibold">
                                                    {{ number_format((float) $line->actual_quantity_used, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end {{ (float) $line->waste_quantity > 0 ? 'text-danger' : 'text-muted' }}">
                                                    {{ number_format((float) $line->waste_quantity, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ number_format((float) $line->unit_cost, 2, ',', '.') }} DH
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $line->total_cost, 2, ',', '.') }} DH
                                                </td>
                                                <td>
                                                    <span class="badge {{ $poStatusBadges[$line->status] ?? 'bg-secondary' }}">
                                                        {{ $poStatusLabels[$line->status] ?? $line->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td colspan="3">Total — {{ $consumptionLines->count() }} ligne(s)</td>
                                            <td class="text-end">{{ number_format($plannedQty, 2, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($consumedQty, 2, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($wasteQty, 2, ',', '.') }}</td>
                                            <td></td>
                                            <td class="text-end">{{ number_format($consumedCost, 2, ',', '.') }} DH</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-industry fs-1 d-block mb-3"></i>
                                Cette matière n'a été consommée dans aucun ordre de fabrication sur la période.
                            </div>
                        @endif
                    </div>

                    <!-- VENTES -->
                    <div class="tab-pane fade" id="tab-ventes" role="tabpanel">
                        @if ($salesLines->count())
                            @if ($salesByClient->count())
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Client</th>
                                                <th>Type</th>
                                                <th class="text-end">Quantité</th>
                                                <th class="text-end">Montant</th>
                                                <th class="text-end">Ventes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($salesByClient as $client)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('clients.show', $client->client_id) }}"
                                                            class="text-decoration-none fw-semibold">
                                                            {{ $client->name }}
                                                        </a>
                                                        @if ($client->entreprise_name)
                                                            <div class="text-muted small">{{ $client->entreprise_name }}</div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge {{ $clientTypeBadges[$client->client_type] ?? 'bg-secondary' }}">
                                                            {{ $clientTypeLabels[$client->client_type] ?? $client->client_type }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $client->total_qty, 2, ',', '.') }}
                                                    </td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $client->total_amount, 2, ',', '.') }} DH
                                                    </td>
                                                    <td class="text-end">{{ $client->orders_count }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>N° Vente</th>
                                            <th>Date</th>
                                            <th>Client</th>
                                            <th class="text-end">Quantité</th>
                                            <th class="text-end">PU</th>
                                            <th class="text-end">Total</th>
                                            <th>Paiement</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($salesLines as $line)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('sales.orders.show', $line->order_id) }}"
                                                        class="text-decoration-none fw-semibold">
                                                        {{ $line->order_number }}
                                                    </a>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($line->order_date)->format('d/m/Y') }}</td>
                                                <td>
                                                    @if ($line->client_id)
                                                        <a href="{{ route('clients.show', $line->client_id) }}"
                                                            class="text-decoration-none">{{ $line->client_name }}</a>
                                                        @if ($line->entreprise_name)
                                                            <div class="text-muted small">{{ $line->entreprise_name }}</div>
                                                        @endif
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="text-end fw-semibold">
                                                    {{ number_format((float) $line->quantity, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $line->unit_price, 2, ',', '.') }} DH
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $line->total_price, 2, ',', '.') }} DH
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge {{ $paymentStatusBadges[$line->payment_status] ?? 'bg-secondary' }}">
                                                        {{ $paymentStatusLabels[$line->payment_status] ?? $line->payment_status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td colspan="3">Total — {{ $salesLines->count() }} ligne(s)</td>
                                            <td class="text-end">{{ number_format($soldQty, 2, ',', '.') }}</td>
                                            <td></td>
                                            <td class="text-end">{{ number_format($soldAmount, 2, ',', '.') }} DH</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-cash-register fs-1 d-block mb-3"></i>
                                Cette matière n'a pas été vendue sur la période.
                            </div>
                        @endif
                    </div>

                    <!-- MOUVEMENTS -->
                    <div class="tab-pane fade" id="tab-mouvements" role="tabpanel">
                        @if ($movements->count())
                            @if ($movementsByType->count())
                                <div class="row g-2 mb-3">
                                    @foreach ($movementsByType as $type)
                                        <div class="col-md-3 col-6">
                                            <div class="border rounded p-2">
                                                <div class="text-muted small">
                                                    {{ $movementTypeLabels[$type->movement_type] ?? $type->movement_type }}
                                                </div>
                                                <div class="fw-semibold">
                                                    {{ number_format((float) $type->total_qty, 2, ',', '.') }}
                                                    <span class="text-muted small">{{ $unitLabel }}</span>
                                                </div>
                                                <div class="text-muted small">{{ $type->movements_count }} mouvement(s)</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th class="text-end">Quantité</th>
                                            <th class="text-end">Avant</th>
                                            <th class="text-end">Après</th>
                                            <th>Référence</th>
                                            <th>Par</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($movements as $mv)
                                            @php
                                                $qty = (float) $mv->quantity;
                                                $isOut = $qty < 0 || in_array($mv->movement_type, $outMovements);
                                            @endphp
                                            <tr>
                                                <td>
                                                    {{ $mv->movement_date ? \Carbon\Carbon::parse($mv->movement_date)->format('d/m/Y H:i') : '—' }}
                                                </td>
                                                <td>
                                                    <span class="badge {{ $isOut ? 'bg-danger' : 'bg-success' }}">
                                                        {{ $movementTypeLabels[$mv->movement_type] ?? $mv->movement_type }}
                                                    </span>
                                                </td>
                                                <td class="text-end fw-semibold {{ $isOut ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($qty, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ number_format((float) $mv->previous_stock, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $mv->new_stock, 2, ',', '.') }}
                                                </td>
                                                <td class="small">
                                                    {{ $mv->reference_number ?: '—' }}
                                                    @if ($mv->reference_type)
                                                        <div class="text-muted">{{ $mv->reference_type }}</div>
                                                    @endif
                                                </td>
                                                <td class="small">{{ $mv->performed_by_name ?: '—' }}</td>
                                                <td class="small text-muted">{{ $mv->notes ?: '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-right-left fs-1 d-block mb-3"></i>
                                Aucun mouvement de stock sur la période.
                            </div>
                        @endif
                    </div>

                    <!-- LOTS -->
                    <div class="tab-pane fade" id="tab-lots" role="tabpanel">
                        <div class="alert alert-light border small">
                            <i class="fas fa-circle-info me-1"></i>
                            Lots FIFO encore en stock aujourd'hui — indépendants du filtre de période.
                        </div>
                        @if ($stockLots->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Entrée</th>
                                            <th>Référence</th>
                                            <th class="text-end">Prix d'achat</th>
                                            <th class="text-end">Quantité initiale</th>
                                            <th class="text-end">Restant</th>
                                            <th class="text-end">Valeur restante</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stockLots as $lot)
                                            <tr>
                                                <td>
                                                    {{ $lot->movement_date ? \Carbon\Carbon::parse($lot->movement_date)->format('d/m/Y') : '—' }}
                                                </td>
                                                <td class="small">{{ $lot->reference_number ?: '—' }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $lot->unit_price, 2, ',', '.') }} DH
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ number_format((float) $lot->quantity, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end fw-semibold">
                                                    {{ number_format((float) $lot->remaining_quantity, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $lot->remaining_quantity * (float) $lot->unit_price, 2, ',', '.') }}
                                                    DH
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td colspan="4">Total — {{ $stockLots->count() }} lot(s)</td>
                                            <td class="text-end">
                                                {{ number_format((float) $stockLots->sum('remaining_quantity'), 2, ',', '.') }}
                                            </td>
                                            <td class="text-end">{{ number_format($stockValue, 2, ',', '.') }} DH</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-layer-group fs-1 d-block mb-3"></i>
                                Aucun lot en stock pour cette matière.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const purchasesMonthly = @json($purchasesMonthly);
            const consumptionMonthly = @json($consumptionMonthly);
            const salesMonthly = @json($salesMonthly);
            const bySupplier = @json($purchasesBySupplier);
            const unitLabel = @json($unitLabel);

            // Un seul axe de mois, ordonné, pour les trois séries.
            const periods = Array.from(new Set([
                ...purchasesMonthly.map(r => r.period),
                ...consumptionMonthly.map(r => r.period),
                ...salesMonthly.map(r => r.period)
            ])).filter(Boolean).sort();

            const purchaseMap = Object.fromEntries(purchasesMonthly.map(r => [r.period, r]));
            const consumptionMap = Object.fromEntries(consumptionMonthly.map(r => [r.period, r]));
            const salesMap = Object.fromEntries(salesMonthly.map(r => [r.period, r]));

            const nf = (val, digits = 2) => Number(val || 0).toLocaleString('fr-FR', {
                minimumFractionDigits: digits,
                maximumFractionDigits: digits
            });

            if (periods.length) {
                new ApexCharts(document.querySelector("#materialTrendChart"), {
                    chart: {
                        height: 360,
                        type: 'line',
                        toolbar: { show: true }
                    },
                    series: [{
                        name: 'Acheté (' + unitLabel + ')',
                        type: 'column',
                        data: periods.map(p => Number(purchaseMap[p]?.total_qty || 0))
                    }, {
                        name: 'Consommé (' + unitLabel + ')',
                        type: 'column',
                        data: periods.map(p => Number(consumptionMap[p]?.actual_qty || 0))
                    }, {
                        name: 'Vendu (' + unitLabel + ')',
                        type: 'column',
                        data: periods.map(p => Number(salesMap[p]?.total_qty || 0))
                    }, {
                        name: 'Montant acheté (DH)',
                        type: 'line',
                        data: periods.map(p => Number(purchaseMap[p]?.total_amount || 0))
                    }],
                    colors: ['#0d6efd', '#ffc107', '#198754', '#6f42c1'],
                    stroke: { width: [0, 0, 0, 3], curve: 'smooth' },
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: periods },
                    yaxis: [{
                        seriesName: 'Acheté (' + unitLabel + ')',
                        title: { text: 'Quantité' },
                        labels: { formatter: (v) => nf(v, 0) }
                    }, {
                        seriesName: 'Acheté (' + unitLabel + ')',
                        show: false
                    }, {
                        seriesName: 'Acheté (' + unitLabel + ')',
                        show: false
                    }, {
                        opposite: true,
                        title: { text: 'Montant (DH)' },
                        labels: { formatter: (v) => nf(v, 0) }
                    }],
                    legend: { position: 'top' },
                    tooltip: { shared: true, intersect: false }
                }).render();
            }

            if (bySupplier.length) {
                new ApexCharts(document.querySelector("#supplierChart"), {
                    chart: { type: 'donut', height: 300 },
                    series: bySupplier.map(r => Number(r.total_amount || 0)),
                    labels: bySupplier.map(r => r.supplier_name),
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: true },
                    tooltip: { y: { formatter: (v) => nf(v) + ' DH' } }
                }).render();
            }

            // ApexCharts se mesure mal dans un onglet caché: on le relance à l'affichage.
            $('#materialStatsTab button[data-bs-toggle="pill"]').on('shown.bs.tab', function() {
                window.dispatchEvent(new Event('resize'));
            });
        });
    </script>
@endpush
