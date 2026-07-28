@extends('layouts.app')

@section('title', 'Statistiques Article')

@php
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
    $poStatusLabels = [
        'pending' => 'En attente',
        'in_progress' => 'En cours',
        'completed' => 'Terminé',
        'cancelled' => 'Annulé',
    ];
    $poStatusBadges = [
        'pending' => 'bg-secondary',
        'in_progress' => 'bg-warning',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
    ];
    $paymentStatusLabels = [
        'pending' => 'Non payé',
        'partial' => 'Partiel',
        'paid' => 'Payé',
    ];
    $paymentStatusBadges = [
        'pending' => 'bg-danger',
        'partial' => 'bg-warning',
        'paid' => 'bg-success',
    ];
    $movementTypeLabels = [
        'sales' => 'Vente',
        'production_output' => 'Production (entrée)',
        'production_start' => 'Production (réservation)',
        'type2_consumption' => 'Consommation découpage',
        'type4_consumption' => 'Consommation chutes',
        'cancellation_reversal' => 'Annulation (retour)',
        'cancellation_output_reversal' => 'Annulation production',
        'adjustment' => 'Ajustement',
        'manual_addition' => 'Ajout manuel',
        'credit_note' => 'Avoir (retour)',
    ];

    $soldQty = (float) ($stats['sales']->total_qty ?? 0);
    $soldAmount = (float) ($stats['sales']->total_amount ?? 0);
    $creditQty = (float) ($stats['credits']->total_qty ?? 0);
    $creditAmount = (float) ($stats['credits']->total_amount ?? 0);
    $netQty = $soldQty - $creditQty;
    $netAmount = $soldAmount - $creditAmount;
    $avgPrice = $soldQty > 0 ? $soldAmount / $soldQty : 0;
    $producedQty = (float) ($stats['production']->total_produced ?? 0);
    $defectiveQty = (float) ($stats['production']->total_defective ?? 0);
    $consumedQty = (float) ($stats['consumption']->total_consumed ?? 0);
@endphp

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Statistiques de l'Article</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('products.index') }}">
                                        Produits
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('products.show', $product->product_id) }}">
                                        {{ $product->product_name }}
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

        <!-- Article header + filters -->
        <div class="card mb-4">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0" style="color:white">
                    <i class="fas fa-chart-line me-2"></i>{{ $product->product_code }} — {{ $product->product_name }}
                </h5>
                <a href="{{ route('products.show', $product->product_id) }}" class="btn btn-light btn-sm">
                    <i class="fas fa-eye me-1"></i> Fiche produit
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-2 col-6">
                        <div class="text-muted small">Type</div>
                        <div class="fw-semibold">{{ $product->product_type_label }}</div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="text-muted small">Unité</div>
                        <div class="fw-semibold text-capitalize">{{ $unitLabel }}</div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="text-muted small">Dimensions</div>
                        <div class="fw-semibold">{{ $product->dimensions }}</div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="text-muted small">Volume / unité</div>
                        <div class="fw-semibold">{{ number_format($volumePerUnit, 4, ',', '.') }} m³</div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="text-muted small">Poids / unité</div>
                        <div class="fw-semibold">{{ number_format($weightPerUnit, 2, ',', '.') }} kg</div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="text-muted small">Stock actuel</div>
                        <div class="fw-semibold">
                            {{ number_format($product->total_stock, 2, ',', '.') }} {{ $unitLabel }}
                        </div>
                    </div>
                </div>

                <hr>

                <form method="GET" action="{{ route('products.article-statistics', $product->product_id) }}"
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
                        <a href="{{ route('products.article-statistics', $product->product_id) }}"
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

        <!-- KPI: Ventes -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-start border-4 border-primary h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Quantité vendue</div>
                        <h3 class="mb-0 mt-1">{{ number_format($soldQty, 2, ',', '.') }}</h3>
                        <div class="text-muted small text-capitalize">{{ $unitLabel }}</div>
                        <hr class="my-2">
                        <div class="small">
                            <span class="text-muted">Net après avoirs :</span>
                            <strong>{{ number_format($netQty, 2, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-start border-4 border-success h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Chiffre d'affaires</div>
                        <h3 class="mb-0 mt-1">{{ number_format($soldAmount, 2, ',', '.') }} DH</h3>
                        <div class="text-muted small">PU moyen : {{ number_format($avgPrice, 2, ',', '.') }} DH</div>
                        <hr class="my-2">
                        <div class="small">
                            <span class="text-muted">Net après avoirs :</span>
                            <strong>{{ number_format($netAmount, 2, ',', '.') }} DH</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-start border-4 border-info h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Clients</div>
                        <h3 class="mb-0 mt-1">{{ (int) ($stats['sales']->clients_count ?? 0) }}</h3>
                        <div class="text-muted small">
                            {{ (int) ($stats['sales']->orders_count ?? 0) }} vente(s) —
                            {{ (int) ($stats['sales']->lines_count ?? 0) }} ligne(s)
                        </div>
                        <hr class="my-2">
                        <div class="small text-muted">
                            @if ($stats['sales']->first_sale_date ?? null)
                                {{ \Carbon\Carbon::parse($stats['sales']->first_sale_date)->format('d/m/Y') }}
                                → {{ \Carbon\Carbon::parse($stats['sales']->last_sale_date)->format('d/m/Y') }}
                            @else
                                Aucune vente
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-start border-4 border-warning h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Volume / poids vendu</div>
                        <h3 class="mb-0 mt-1">{{ number_format($soldQty * $volumePerUnit, 3, ',', '.') }} m³</h3>
                        <div class="text-muted small">
                            {{ number_format($soldQty * $weightPerUnit, 2, ',', '.') }} kg
                        </div>
                        <hr class="my-2">
                        <div class="small text-muted">
                            Basé sur {{ number_format($volumePerUnit, 4, ',', '.') }} m³ / {{ $unitLabel }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI: Documents & production -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-file-invoice text-primary"></i>
                            <span class="text-muted small text-uppercase">Facturé</span>
                        </div>
                        <h4 class="mb-0">{{ number_format((float) ($stats['invoices']->total_qty ?? 0), 2, ',', '.') }}
                            <small class="text-muted fs-6 text-capitalize">{{ $unitLabel }}</small>
                        </h4>
                        <div class="small text-muted">
                            {{ number_format((float) ($stats['invoices']->total_amount ?? 0), 2, ',', '.') }} DH —
                            {{ (int) ($stats['invoices']->invoices_count ?? 0) }} facture(s)
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-rotate-left text-danger"></i>
                            <span class="text-muted small text-uppercase">Avoirs</span>
                        </div>
                        <h4 class="mb-0 text-danger">{{ number_format($creditQty, 2, ',', '.') }}
                            <small class="text-muted fs-6 text-capitalize">{{ $unitLabel }}</small>
                        </h4>
                        <div class="small text-muted">
                            {{ number_format($creditAmount, 2, ',', '.') }} DH —
                            {{ (int) ($stats['credits']->credit_notes_count ?? 0) }} avoir(s)
                            @if ($soldQty > 0)
                                <span class="badge bg-light text-danger ms-1" style="color: #dc3545 !important">
                                    {{ number_format(($creditQty / $soldQty) * 100, 2, ',', '.') }} %
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-industry text-success"></i>
                            <span class="text-muted small text-uppercase">Produit</span>
                        </div>
                        <h4 class="mb-0 text-success">{{ number_format($producedQty, 2, ',', '.') }}
                            <small class="text-muted fs-6 text-capitalize">{{ $unitLabel }}</small>
                        </h4>
                        <div class="small text-muted">
                            {{ (int) ($stats['production']->orders_count ?? 0) }} ordre(s) —
                            {{ number_format((float) ($stats['production']->total_volume ?? 0), 3, ',', '.') }} m³
                            @if ($defectiveQty > 0)
                                <span class="badge bg-light text-danger ms-1" style="color: #dc3545 !important">
                                    {{ number_format($defectiveQty, 2, ',', '.') }} défectueux
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-scissors text-warning"></i>
                            <span class="text-muted small text-uppercase">Consommé en production</span>
                        </div>
                        <h4 class="mb-0 text-warning">{{ number_format($consumedQty, 2, ',', '.') }}
                            <small class="text-muted fs-6 text-capitalize">{{ $unitLabel }}</small>
                        </h4>
                        <div class="small text-muted">
                            Utilisé comme source dans
                            {{ (int) ($stats['consumption']->orders_count ?? 0) }} ordre(s)
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
                        @if ($salesMonthly->count() || $productionMonthly->count())
                            <div id="articleTrendChart"></div>
                        @else
                            <div class="text-center text-muted py-5">Aucune donnée sur la période.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="fas fa-users me-2"></i>Répartition par type de client</h6>
                    </div>
                    <div class="card-body">
                        @if ($salesByClientType->count())
                            <div id="clientTypeChart"></div>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach ($salesByClientType as $row)
                                            <tr>
                                                <td>
                                                    <span
                                                        class="badge {{ $clientTypeBadges[$row->client_type] ?? 'bg-secondary' }}">
                                                        {{ $clientTypeLabels[$row->client_type] ?? $row->client_type }}
                                                    </span>
                                                </td>
                                                <td class="text-end">{{ number_format((float) $row->total_qty, 2, ',', '.') }}</td>
                                                <td class="text-end text-muted">
                                                    {{ number_format((float) $row->total_amount, 2, ',', '.') }} DH
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">Aucune vente sur la période.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-pills mb-3 flex-wrap gap-1" id="articleStatsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-clients"
                            type="button" role="tab">
                            <i class="fas fa-user-tie me-1"></i> Clients
                            <span class="badge bg-light text-dark ms-1" style="color: #000 !important">{{ $topClients->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ventes" type="button"
                            role="tab">
                            <i class="fas fa-cart-shopping me-1"></i> Ventes
                            <span class="badge bg-light text-dark ms-1" style="color: #000 !important">{{ $salesLines->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-factures" type="button"
                            role="tab">
                            <i class="fas fa-file-invoice me-1"></i> Factures
                            <span class="badge bg-light text-dark ms-1" style="color: #000 !important">{{ $invoiceLines->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-avoirs" type="button"
                            role="tab">
                            <i class="fas fa-rotate-left me-1"></i> Avoirs
                            <span class="badge bg-light text-dark ms-1" style="color: #000 !important">{{ $creditLines->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-production" type="button"
                            role="tab">
                            <i class="fas fa-industry me-1"></i> Production
                            <span class="badge bg-light text-dark ms-1" style="color: #000 !important">{{ $productionLines->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-consommation" type="button"
                            role="tab">
                            <i class="fas fa-scissors me-1"></i> Consommation
                            <span class="badge bg-light text-dark ms-1" style="color: #000 !important">{{ $consumptionLines->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-familles" type="button"
                            role="tab">
                            <i class="fas fa-layer-group me-1"></i> Familles
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-mouvements" type="button"
                            role="tab">
                            <i class="fas fa-right-left me-1"></i> Mouvements de stock
                            <span class="badge bg-light text-dark ms-1" style="color: #000 !important">{{ $movements->count() }}</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- CLIENTS -->
                    <div class="tab-pane fade show active" id="tab-clients" role="tabpanel">
                        @if ($topClients->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Client</th>
                                            <th>Type</th>
                                            <th class="text-end">Quantité</th>
                                            <th class="text-end">Part</th>
                                            <th class="text-end">Montant</th>
                                            <th class="text-end">PU moyen</th>
                                            <th class="text-end">Ventes</th>
                                            <th>Volume</th>
                                            <th>1er achat</th>
                                            <th>Dernier achat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($topClients as $i => $client)
                                            @php
                                                $qty = (float) $client->total_qty;
                                                $share = $soldQty > 0 ? ($qty / $soldQty) * 100 : 0;
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
                                                    <a href="{{ route('clients.show', $client->client_id) }}"
                                                        class="text-decoration-none fw-semibold">
                                                        {{ $client->name }}
                                                    </a>
                                                    @if ($client->entreprise_name)
                                                        <div class="text-muted small">{{ $client->entreprise_name }}</div>
                                                    @endif
                                                    @if ($client->phone)
                                                        <div class="text-muted small"><i
                                                                class="fas fa-phone fa-xs me-1"></i>{{ $client->phone }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge {{ $clientTypeBadges[$client->client_type] ?? 'bg-secondary' }}">
                                                        {{ $clientTypeLabels[$client->client_type] ?? $client->client_type }}
                                                    </span>
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
                                                        <span class="small text-muted">{{ number_format($share, 1, ',', '.') }}%</span>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $client->total_amount, 2, ',', '.') }} DH
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ $qty > 0 ? number_format($client->total_amount / $qty, 2, ',', '.') : '—' }}
                                                </td>
                                                <td class="text-end">
                                                    <span class="badge bg-light text-dark" style="color: #000 !important">{{ $client->orders_count }}</span>
                                                </td>
                                                <td>{{ number_format($qty * $volumePerUnit, 3, ',', '.') }} m³</td>
                                                <td>{{ \Carbon\Carbon::parse($client->first_order_date)->format('d/m/Y') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($client->last_order_date)->format('d/m/Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td colspan="3">Total — {{ $topClients->count() }} client(s)</td>
                                            <td class="text-end">{{ number_format($soldQty, 2, ',', '.') }}</td>
                                            <td></td>
                                            <td class="text-end">{{ number_format($soldAmount, 2, ',', '.') }} DH</td>
                                            <td colspan="5"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-user-slash fs-1 d-block mb-3"></i>
                                Aucun client n'a acheté cet article sur la période.
                            </div>
                        @endif
                    </div>

                    <!-- VENTES -->
                    <div class="tab-pane fade" id="tab-ventes" role="tabpanel">
                        @if ($salesLines->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>N° Vente</th>
                                            <th>Date</th>
                                            <th>Client</th>
                                            <th>Famille</th>
                                            <th class="text-end">Quantité</th>
                                            <th class="text-end">PU</th>
                                            <th class="text-end">Total</th>
                                            <th>Volume</th>
                                            <th>Paiement</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($salesLines as $line)
                                            <tr>
                                                <td class="fw-semibold">{{ $line->order_number }}</td>
                                                <td>{{ \Carbon\Carbon::parse($line->order_date)->format('d/m/Y') }}</td>
                                                <td>
                                                    @if ($line->client_id)
                                                        <a href="{{ route('clients.show', $line->client_id) }}"
                                                            class="text-decoration-none">{{ $line->client_name }}</a>
                                                        @if ($line->entreprise_name)
                                                            <div class="text-muted small">{{ $line->entreprise_name }}</div>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $line->family_name ?: '—' }}</td>
                                                <td class="text-end fw-semibold">
                                                    {{ number_format((float) $line->quantity, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $line->unit_price, 2, ',', '.') }} DH
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $line->total_price, 2, ',', '.') }} DH
                                                </td>
                                                <td>{{ number_format((float) $line->quantity * $volumePerUnit, 3, ',', '.') }} m³
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge {{ $paymentStatusBadges[$line->payment_status] ?? 'bg-secondary' }}">
                                                        {{ $paymentStatusLabels[$line->payment_status] ?? $line->payment_status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('sales.orders.show', $line->order_id) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td colspan="4">Total</td>
                                            <td class="text-end">{{ number_format($soldQty, 2, ',', '.') }}</td>
                                            <td></td>
                                            <td class="text-end">{{ number_format($soldAmount, 2, ',', '.') }} DH</td>
                                            <td>{{ number_format($soldQty * $volumePerUnit, 3, ',', '.') }} m³</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-cart-shopping fs-1 d-block mb-3"></i>
                                Aucune vente pour cet article sur la période.
                            </div>
                        @endif
                    </div>

                    <!-- FACTURES -->
                    <div class="tab-pane fade" id="tab-factures" role="tabpanel">
                        @if ($invoiceLines->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>N° Facture</th>
                                            <th>Date</th>
                                            <th>Client</th>
                                            <th>Famille</th>
                                            <th class="text-end">Quantité</th>
                                            <th class="text-end">PU</th>
                                            <th class="text-end">Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoiceLines as $line)
                                            <tr>
                                                <td class="fw-semibold">{{ $line->invoice_number }}</td>
                                                <td>{{ \Carbon\Carbon::parse($line->invoice_date)->format('d/m/Y') }}</td>
                                                <td>
                                                    @if ($line->client_id)
                                                        <a href="{{ route('clients.show', $line->client_id) }}"
                                                            class="text-decoration-none">{{ $line->client_name }}</a>
                                                        @if ($line->entreprise_name)
                                                            <div class="text-muted small">{{ $line->entreprise_name }}</div>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $line->family_name ?: '—' }}</td>
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
                                                    <a href="{{ route('sales.invoices.show', $line->invoice_id) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td colspan="4">Total</td>
                                            <td class="text-end">
                                                {{ number_format((float) ($stats['invoices']->total_qty ?? 0), 2, ',', '.') }}
                                            </td>
                                            <td></td>
                                            <td class="text-end">
                                                {{ number_format((float) ($stats['invoices']->total_amount ?? 0), 2, ',', '.') }} DH
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-file-invoice fs-1 d-block mb-3"></i>
                                Aucune facture contenant cet article sur la période.
                            </div>
                        @endif
                    </div>

                    <!-- AVOIRS -->
                    <div class="tab-pane fade" id="tab-avoirs" role="tabpanel">
                        @if ($creditLines->count())
                            @if ($creditByClient->count())
                                <div class="row mb-3">
                                    <div class="col-lg-6">
                                        <div class="card border">
                                            <div class="card-header py-2">
                                                <h6 class="card-title mb-0">Avoirs par client</h6>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Client</th>
                                                            <th class="text-end">Quantité</th>
                                                            <th class="text-end">Montant</th>
                                                            <th class="text-end">Avoirs</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($creditByClient as $row)
                                                            <tr>
                                                                <td>
                                                                    <a href="{{ route('clients.show', $row->client_id) }}"
                                                                        class="text-decoration-none">{{ $row->name }}</a>
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format((float) $row->total_qty, 2, ',', '.') }}
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format((float) $row->total_amount, 2, ',', '.') }} DH
                                                                </td>
                                                                <td class="text-end">{{ $row->credit_notes_count }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>N° Avoir</th>
                                            <th>Date</th>
                                            <th>Client</th>
                                            <th>Vente liée</th>
                                            <th>Famille</th>
                                            <th class="text-end">Quantité</th>
                                            <th class="text-end">Montant</th>
                                            <th>Motif</th>
                                            <th>Statut</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($creditLines as $line)
                                            <tr>
                                                <td class="fw-semibold">{{ $line->credit_note_number }}</td>
                                                <td>{{ \Carbon\Carbon::parse($line->credit_note_date)->format('d/m/Y') }}</td>
                                                <td>
                                                    @if ($line->client_id)
                                                        <a href="{{ route('clients.show', $line->client_id) }}"
                                                            class="text-decoration-none">{{ $line->client_name }}</a>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($line->order_id)
                                                        <a href="{{ route('sales.orders.show', $line->order_id) }}"
                                                            class="text-decoration-none">{{ $line->order_number }}</a>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $line->family_name ?: '—' }}</td>
                                                <td class="text-end fw-semibold text-danger">
                                                    {{ number_format((float) $line->quantity, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end text-danger">
                                                    {{ number_format((float) $line->total_price, 2, ',', '.') }} DH
                                                </td>
                                                <td class="small text-muted">
                                                    {{ $line->item_reason ?: ($line->credit_reason ?: '—') }}
                                                </td>
                                                <td><span class="badge bg-info">{{ $line->status }}</span></td>
                                                <td>
                                                    <a href="{{ route('credit-notes.show', $line->credit_note_id) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td colspan="5">Total</td>
                                            <td class="text-end text-danger">{{ number_format($creditQty, 2, ',', '.') }}</td>
                                            <td class="text-end text-danger">
                                                {{ number_format($creditAmount, 2, ',', '.') }} DH
                                            </td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-rotate-left fs-1 d-block mb-3"></i>
                                Aucun avoir pour cet article sur la période.
                            </div>
                        @endif
                    </div>

                    <!-- PRODUCTION -->
                    <div class="tab-pane fade" id="tab-production" role="tabpanel">
                        <div class="row g-3 mb-3">
                            @if ($productionOrdersByStatus->count())
                                <div class="col-lg-5">
                                    <div class="card border h-100">
                                        <div class="card-header py-2">
                                            <h6 class="card-title mb-0">Ordres de production (toutes périodes)</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Statut</th>
                                                        <th class="text-end">Ordres</th>
                                                        <th class="text-end">Qté planifiée</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($productionOrdersByStatus as $row)
                                                        <tr>
                                                            <td>
                                                                <span
                                                                    class="badge {{ $poStatusBadges[$row->status] ?? 'bg-secondary' }}">
                                                                    {{ $poStatusLabels[$row->status] ?? $row->status }}
                                                                </span>
                                                            </td>
                                                            <td class="text-end">{{ $row->orders_count }}</td>
                                                            <td class="text-end">
                                                                {{ number_format((float) $row->planned_qty, 2, ',', '.') }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($productionByFamille->count())
                                <div class="col-lg-7">
                                    <div class="card border h-100">
                                        <div class="card-header py-2">
                                            <h6 class="card-title mb-0">Production par famille</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Famille</th>
                                                        <th class="text-end">Produit</th>
                                                        <th class="text-end">Défectueux</th>
                                                        <th class="text-end">Ordres</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($productionByFamille as $row)
                                                        <tr>
                                                            <td>{{ $row->famille_name }}</td>
                                                            <td class="text-end fw-semibold">
                                                                {{ number_format((float) $row->total_produced, 2, ',', '.') }}
                                                            </td>
                                                            <td class="text-end text-danger">
                                                                {{ number_format((float) $row->total_defective, 2, ',', '.') }}
                                                            </td>
                                                            <td class="text-end">{{ $row->orders_count }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($productionLines->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>N° Ordre</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Source</th>
                                            <th>Famille</th>
                                            <th class="text-end">Produit</th>
                                            <th class="text-end">Défectueux</th>
                                            <th class="text-end">Volume</th>
                                            <th>Qualité</th>
                                            <th>Statut</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($productionLines as $line)
                                            <tr>
                                                <td class="fw-semibold">{{ $line->order_number }}</td>
                                                <td>
                                                    {{ $line->production_date ? \Carbon\Carbon::parse($line->production_date)->format('d/m/Y') : '—' }}
                                                </td>
                                                <td><span class="badge bg-light text-dark" style="color: #000 !important">{{ $line->production_type }}</span></td>
                                                <td>{{ $line->source_product_name ?: '—' }}</td>
                                                <td>{{ $line->famille_name ?: '—' }}</td>
                                                <td class="text-end fw-semibold text-success">
                                                    {{ number_format((float) $line->quantity_produced, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end text-danger">
                                                    {{ number_format((float) $line->quantity_defective, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $line->total_volume_m3, 3, ',', '.') }} m³
                                                </td>
                                                <td>{{ $line->quality_grade ?: '—' }}</td>
                                                <td>
                                                    <span class="badge {{ $poStatusBadges[$line->status] ?? 'bg-secondary' }}">
                                                        {{ $poStatusLabels[$line->status] ?? $line->status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('production-orders.show', $line->order_id) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td colspan="5">Total</td>
                                            <td class="text-end text-success">{{ number_format($producedQty, 2, ',', '.') }}</td>
                                            <td class="text-end text-danger">{{ number_format($defectiveQty, 2, ',', '.') }}</td>
                                            <td class="text-end">
                                                {{ number_format((float) ($stats['production']->total_volume ?? 0), 3, ',', '.') }} m³
                                            </td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-industry fs-1 d-block mb-3"></i>
                                Aucune production enregistrée pour cet article sur la période.
                            </div>
                        @endif
                    </div>

                    <!-- CONSOMMATION -->
                    <div class="tab-pane fade" id="tab-consommation" role="tabpanel">
                        @if ($consumptionLines->count())
                            <p class="text-muted small">
                                Ordres de production où cet article a été utilisé comme matière source.
                            </p>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>N° Ordre</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Article produit</th>
                                            <th class="text-end">Consommé</th>
                                            <th class="text-end">Produit</th>
                                            <th>Statut</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($consumptionLines as $line)
                                            <tr>
                                                <td class="fw-semibold">{{ $line->order_number }}</td>
                                                <td>
                                                    {{ $line->production_date ? \Carbon\Carbon::parse($line->production_date)->format('d/m/Y') : '—' }}
                                                </td>
                                                <td><span class="badge bg-light text-dark" style="color: #000 !important">{{ $line->production_type }}</span></td>
                                                <td>
                                                    @if ($line->produced_product_id)
                                                        <a href="{{ route('products.article-statistics', $line->produced_product_id) }}"
                                                            class="text-decoration-none">{{ $line->produced_product_name }}</a>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-end fw-semibold text-warning">
                                                    {{ number_format((float) $line->quantity_consumed, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $line->quantity_produced, 2, ',', '.') }}
                                                </td>
                                                <td>
                                                    <span class="badge {{ $poStatusBadges[$line->status] ?? 'bg-secondary' }}">
                                                        {{ $poStatusLabels[$line->status] ?? $line->status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('production-orders.show', $line->order_id) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td colspan="4">Total consommé</td>
                                            <td class="text-end text-warning">{{ number_format($consumedQty, 2, ',', '.') }}</td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-scissors fs-1 d-block mb-3"></i>
                                Cet article n'a jamais été consommé comme source de production sur la période.
                            </div>
                        @endif
                    </div>

                    <!-- FAMILLES -->
                    <div class="tab-pane fade" id="tab-familles" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-lg-7">
                                <div class="card border h-100">
                                    <div class="card-header py-2">
                                        <h6 class="card-title mb-0">Ventes par famille</h6>
                                    </div>
                                    @if ($salesByFamille->count())
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0 align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Famille</th>
                                                        <th class="text-end">Quantité</th>
                                                        <th class="text-end">Part</th>
                                                        <th class="text-end">Montant</th>
                                                        <th class="text-end">Clients</th>
                                                        <th class="text-end">Ventes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($salesByFamille as $row)
                                                        @php
                                                            $qty = (float) $row->total_qty;
                                                            $share = $soldQty > 0 ? ($qty / $soldQty) * 100 : 0;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $row->family_name }}</td>
                                                            <td class="text-end fw-semibold">
                                                                {{ number_format($qty, 2, ',', '.') }}
                                                            </td>
                                                            <td class="text-end text-muted">
                                                                {{ number_format($share, 1, ',', '.') }}%
                                                            </td>
                                                            <td class="text-end">
                                                                {{ number_format((float) $row->total_amount, 2, ',', '.') }} DH
                                                            </td>
                                                            <td class="text-end">{{ $row->clients_count }}</td>
                                                            <td class="text-end">{{ $row->orders_count }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="card-body text-center text-muted">Aucune vente sur la période.</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="card border h-100">
                                    <div class="card-header py-2">
                                        <h6 class="card-title mb-0">Stock actuel par famille</h6>
                                    </div>
                                    @if ($product->familleStocks->count())
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Famille</th>
                                                        <th class="text-end">Stock</th>
                                                        <th class="text-end">Réservé</th>
                                                        <th class="text-end">Disponible</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($product->familleStocks as $fs)
                                                        <tr>
                                                            <td>{{ $fs->famille->famille_name ?? $fs->famille_name }}</td>
                                                            <td class="text-end">
                                                                {{ number_format((float) $fs->current_quantity, 2, ',', '.') }}
                                                            </td>
                                                            <td class="text-end text-muted">
                                                                {{ number_format((float) $fs->reserved_quantity, 2, ',', '.') }}
                                                            </td>
                                                            <td class="text-end fw-semibold">
                                                                {{ number_format((float) $fs->available_quantity, 2, ',', '.') }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Stock actuel</span>
                                                <strong>{{ number_format((float) ($product->stock->current_quantity ?? 0), 2, ',', '.') }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Réservé</span>
                                                <strong>{{ number_format((float) ($product->stock->reserved_quantity ?? 0), 2, ',', '.') }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Disponible</span>
                                                <strong>{{ number_format((float) ($product->stock->available_quantity ?? 0), 2, ',', '.') }}</strong>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MOUVEMENTS -->
                    <div class="tab-pane fade" id="tab-mouvements" role="tabpanel">
                        @if ($movementsByType->count())
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach ($movementsByType as $row)
                                    <span class="badge bg-light text-dark border p-2" style="color: #000 !important">
                                        {{ $movementTypeLabels[$row->movement_type] ?? $row->movement_type }} :
                                        <strong>{{ number_format((float) $row->total_qty, 2, ',', '.') }}</strong>
                                        <span class="text-muted">({{ $row->movements_count }})</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if ($movements->count())
                            <p class="text-muted small">300 derniers mouvements sur la période.</p>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Famille</th>
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
                                                $isOut = $qty < 0 || in_array($mv->movement_type, ['sales', 'type2_consumption', 'type4_consumption', 'production_start']);
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
                                                <td>{{ $mv->famille_name ?: '—' }}</td>
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
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const salesMonthly = @json($salesMonthly);
            const productionMonthly = @json($productionMonthly);
            const clientTypes = @json($salesByClientType);
            const clientTypeLabels = @json($clientTypeLabels);
            const unitLabel = @json($unitLabel);

            // Merge both series onto a single, ordered month axis.
            const periods = Array.from(new Set([
                ...salesMonthly.map(r => r.period),
                ...productionMonthly.map(r => r.period)
            ])).filter(Boolean).sort();

            const salesMap = Object.fromEntries(salesMonthly.map(r => [r.period, r]));
            const productionMap = Object.fromEntries(productionMonthly.map(r => [r.period, r]));

            const nf = (val, digits = 2) => Number(val || 0).toLocaleString('fr-FR', {
                minimumFractionDigits: digits,
                maximumFractionDigits: digits
            });

            if (periods.length) {
                new ApexCharts(document.querySelector("#articleTrendChart"), {
                    chart: {
                        height: 360,
                        type: 'line',
                        toolbar: { show: true }
                    },
                    series: [{
                        name: 'Vendu (' + unitLabel + ')',
                        type: 'column',
                        data: periods.map(p => Number(salesMap[p]?.total_qty || 0))
                    }, {
                        name: 'Produit (' + unitLabel + ')',
                        type: 'column',
                        data: periods.map(p => Number(productionMap[p]?.total_produced || 0))
                    }, {
                        name: 'CA (DH)',
                        type: 'line',
                        data: periods.map(p => Number(salesMap[p]?.total_amount || 0))
                    }],
                    colors: ['#0d6efd', '#198754', '#ffc107'],
                    stroke: { width: [0, 0, 3], curve: 'smooth' },
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: periods },
                    yaxis: [{
                        seriesName: 'Vendu (' + unitLabel + ')',
                        title: { text: 'Quantité' },
                        labels: { formatter: (v) => nf(v, 0) }
                    }, {
                        seriesName: 'Vendu (' + unitLabel + ')',
                        show: false
                    }, {
                        opposite: true,
                        title: { text: 'Chiffre d\'affaires (DH)' },
                        labels: { formatter: (v) => nf(v, 0) }
                    }],
                    legend: { position: 'top' },
                    tooltip: { shared: true, intersect: false }
                }).render();
            }

            if (clientTypes.length) {
                new ApexCharts(document.querySelector("#clientTypeChart"), {
                    chart: { type: 'donut', height: 260 },
                    series: clientTypes.map(r => Number(r.total_qty || 0)),
                    labels: clientTypes.map(r => clientTypeLabels[r.client_type] || r.client_type),
                    colors: ['#0d6efd', '#0dcaf0', '#ffc107', '#212529'],
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: true },
                    tooltip: { y: { formatter: (v) => nf(v) + ' ' + unitLabel } }
                }).render();
            }

            // ApexCharts mis-measures while a tab pane is hidden; nudge on show.
            $('#articleStatsTab button[data-bs-toggle="pill"]').on('shown.bs.tab', function() {
                window.dispatchEvent(new Event('resize'));
            });
        });
    </script>
@endpush
