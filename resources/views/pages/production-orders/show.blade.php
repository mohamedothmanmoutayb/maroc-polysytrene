@extends('layouts.app')

@section('title', 'Détails Ordre de Production')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails de l'Ordre de Production</h4>
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
                                        Détails
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
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-info-circle me-2"></i>Ordre : {{ $order->order_number }}
                        </h5>
                        <div>
                            @if ($order->status === 'pending' || $order->status === 'approved')
                                @can('start_production_orders')
                                <button class="btn btn-success btn-sm me-2 start-production"
                                    data-id="{{ $order->order_id }}" data-number="{{ $order->order_number }}">
                                    <i class="fas fa-play me-1"></i> Démarrer
                                </button>
                                @endcan
                            @endif

                            @if ($order->status === 'in_progress')
                                @can('create_production_output')
                                <a href="{{ route('production-output.create') }}?order_id={{ $order->order_id }}"
                                    class="btn btn-primary btn-sm me-2">
                                    <i class="fas fa-check me-1"></i> Enregistrer Sortie
                                </a>
                                @endcan
                            @endif
                            @if (!in_array($order->status, ['cancelled']))
                                @can('cancel_production_orders')
                                <button class="btn btn-danger btn-sm btn-cancel-production"
                                    data-id="{{ $order->order_id }}"
                                    data-order-number="{{ $order->order_number }}"
                                    data-status="{{ $order->status }}"
                                    data-production-date="{{ $order->actual_completion_date ? $order->actual_completion_date->format('d/m/Y') : '' }}">
                                    <i class="fas fa-ban me-1"></i> Annuler
                                </button>
                                @endcan
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- General Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card info-card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-2"></i>Informations Générales
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%">Numéro:</th>
                                                <td><strong>{{ $order->order_number }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Type Production:</th>
                                                <td>
                                                    @switch($order->production_type)
                                                        @case('type1')
                                                            <span class="badge bg-primary">Production Directe</span>
                                                        @break

                                                        @case('type2')
                                                            <span class="badge bg-info">Découpage</span>
                                                        @break

                                                        @case('type3')
                                                            <span class="badge bg-success">Conversion</span>
                                                        @break

                                                        @case('type4')
                                                            <span class="badge bg-warning">Transformation</span>
                                                        @break

                                                        @case('type5')
                                                            <span class="badge" style="background-color:#20c997;color:#fff;">Chutes → Produits Finis</span>
                                                        @break

                                                        @default
                                                            <span class="badge bg-secondary">{{ $order->production_type }}</span>
                                                    @endswitch
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Quantité:</th>
                                                <td>{{ $order->quantity_to_produce }} unités</td>
                                            </tr>
                                            <tr>
                                                <th>Priorité:</th>
                                                <td>
                                                    @switch($order->priority)
                                                        @case('low')
                                                            <span class="badge bg-secondary">Basse</span>
                                                        @break

                                                        @case('medium')
                                                            <span class="badge bg-info">Moyenne</span>
                                                        @break

                                                        @case('high')
                                                            <span class="badge bg-warning">Haute</span>
                                                        @break

                                                        @case('urgent')
                                                            <span class="badge bg-danger">Urgente</span>
                                                        @break
                                                    @endswitch
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Créé par:</th>
                                                <td>{{ $order->creator->username ?? ($order->created_by ? 'Utilisateur #' . $order->created_by : 'N/A') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Créé le:</th>
                                                <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card info-card mb-4">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-calendar-alt me-2"></i>Planning Production
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%">Statut:</th>
                                                <td>
                                                    @switch($order->status)
                                                        @case('pending')
                                                            <span class="badge bg-warning">En attente</span>
                                                        @break

                                                        @case('approved')
                                                            <span class="badge bg-info">Approuvé</span>
                                                        @break

                                                        @case('in_progress')
                                                            <span class="badge bg-primary">En cours</span>
                                                        @break

                                                        @case('completed')
                                                            <span class="badge bg-success">Terminé</span>
                                                        @break

                                                        @case('cancelled')
                                                            <span class="badge bg-danger">Annulé</span>
                                                        @break
                                                    @endswitch
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Date Début:</th>
                                                <td>{{ $order->start_date ? $order->start_date->format('d/m/Y') : 'N/A' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Date Fin Prévue:</th>
                                                <td>{{ $order->expected_completion_date->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Date Fin Réelle:</th>
                                                <td>
                                                    @if ($order->actual_completion_date)
                                                        {{ $order->actual_completion_date->format('d/m/Y') }}
                                                    @else
                                                        Pas encore terminée
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Progression:</th>
                                                <td>
                                                    @php
                                                        $totalProduced = $order->outputs->sum('quantity_produced');
                                                        $percentage =
                                                            $order->quantity_to_produce > 0
                                                                ? ($totalProduced / $order->quantity_to_produce) * 100
                                                                : 0;
                                                    @endphp
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-success" role="progressbar"
                                                            style="width: {{ $percentage }}%;"
                                                            aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                            aria-valuemax="100">
                                                            {{ number_format($percentage, 1) }}%
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $totalProduced }} / {{ $order->quantity_to_produce }} unités
                                                    </small>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SOURCE PRODUCTS & FINAL PRODUCTS (Type 2 / Type 3 / Type 5) -->
                        @if (in_array($order->production_type, ['type2', 'type3', 'type5']))
                            <div class="row mb-4">
                                <!-- Source Products / Chutes Utilisées -->
                                <div class="col-md-6">
                                    <div class="card info-card mb-4">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-box-open me-2"></i>
                                                {{ $order->production_type === 'type5' ? 'Chutes Utilisées' : 'Produits Sources' }}
                                            </h6>
                                        </div>
                                        <div class="card-body p-0">
                                            @if ($order->production_type === 'type5')
                                                <table class="table table-sm table-bordered mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <th>Volume de Chutes Alloué</th>
                                                            <td class="text-end">{{ number_format($order->chutes_volume ?? 0, 4) }} m³</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Volume Produits Prévu</th>
                                                            <td class="text-end">{{ number_format(($order->chutes_volume ?? 0) - ($order->waste_volume ?? 0), 4) }} m³</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Chute Résiduelle Estimée</th>
                                                            <td class="text-end">{{ number_format($order->waste_volume ?? 0, 4) }} m³</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            @elseif (!empty($sourceProducts))
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Code</th>
                                                            <th>Produit</th>
                                                            <th class="text-end">Quantité</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($sourceProducts as $sp)
                                                            <tr>
                                                                <td><span class="badge bg-secondary">{{ $sp['product_code'] ?: '—' }}</span></td>
                                                                <td>{{ $sp['product_name'] }}</td>
                                                                <td class="text-end">{{ number_format($sp['quantity'], 0, ',', ' ') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <div class="p-3 text-muted">
                                                    <i class="fas fa-info-circle me-1"></i> Aucun produit source enregistré.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Final Products to Produce -->
                                <div class="col-md-6">
                                    <div class="card info-card mb-4">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-cubes me-2"></i>Produits à Produire
                                            </h6>
                                        </div>
                                        <div class="card-body p-0">
                                            @if ($subProducts->count() > 0)
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Code</th>
                                                            <th>Produit</th>
                                                            <th class="text-end">Planifié</th>
                                                            <th class="text-end">Produit</th>
                                                            <th class="text-end">Restant</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($subProducts as $sp)
                                                            @php
                                                                $planned  = $sp['planned_quantity']   ?? $sp->quantity_to_produce ?? 0;
                                                                $produced = $sp['produced_quantity']  ?? 0;
                                                                $remaining= $sp['remaining_quantity'] ?? max(0, $planned - $produced);
                                                                $pct      = $planned > 0 ? round($produced / $planned * 100) : 0;
                                                            @endphp
                                                            <tr>
                                                                <td><span class="badge bg-secondary">{{ $sp['product_code'] ?? $sp->product_code ?? '—' }}</span></td>
                                                                <td>{{ $sp['product_name'] ?? $sp->product_name ?? '—' }}</td>
                                                                <td class="text-end">{{ number_format($planned) }}</td>
                                                                <td class="text-end">
                                                                    <span class="{{ $produced >= $planned ? 'text-success fw-bold' : '' }}">
                                                                        {{ number_format($produced) }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-end">
                                                                    <span class="{{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                                                                        {{ number_format($remaining) }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <div class="p-3 text-muted">
                                                    <i class="fas fa-info-circle me-1"></i> Aucun produit final enregistré.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- QUALITY SECTION (Only for Type 1) -->
                        @if ($order->production_type === 'type1' && isset($qualityMetrics))
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div
                                            class="card-header @if ($qualityMetrics['quality']['quality_status'] === 'good') bg-success
                                        @elseif($qualityMetrics['quality']['quality_status'] === 'warning') bg-warning
                                        @elseif($qualityMetrics['quality']['quality_status'] === 'critical') bg-danger
                                        @else bg-secondary @endif text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-chart-line me-2"></i>
                                                Contrôle Qualité - Poids / Matières Premières
                                                @if ($qualityMetrics['quality']['quality_override'])
                                                    <span class="badge bg-light text-dark ms-2">
                                                        <i class="fas fa-check-circle me-1"></i> Override Approuvé
                                                    </span>
                                                @endif
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Quality Status Badge -->
                                            <div class="row mb-4">
                                                <div class="col-12">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            @php
                                                                $statusText = [
                                                                    'good' => '✅ Qualité Conforme',
                                                                    'warning' => '⚠️ Qualité à Surveiller',
                                                                    'critical' => '🔴 Qualité Critique',
                                                                    'pending' => '⏳ En attente de contrôle',
                                                                    'reviewed' => '📋 Vérifié',
                                                                ][
                                                                    $qualityMetrics['quality']['quality_status'] ??
                                                                        'pending'
                                                                ];
                                                            @endphp
                                                            <h5>{{ $statusText }}</h5>
                                                        </div>
                                                        @if ($qualityMetrics['quality']['quality_score'])
                                                            <div class="text-center">
                                                                <h6>Score Qualité</h6>
                                                                <div class="progress" style="height: 30px; width: 200px;">
                                                                    <div class="progress-bar @if ($qualityMetrics['quality']['quality_score'] >= 90) bg-success
                                                                    @elseif($qualityMetrics['quality']['quality_score'] >= 70) bg-warning
                                                                    @else bg-danger @endif"
                                                                        role="progressbar"
                                                                        style="width: {{ $qualityMetrics['quality']['quality_score'] }}%">
                                                                        {{ number_format($qualityMetrics['quality']['quality_score'], 1) }}%
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Weight Balance Summary -->
                                            <div class="row mb-4">
                                                <div class="col-md-4">
                                                    <div class="alert alert-info text-center">
                                                        <i class="fas fa-truck me-2"></i>
                                                        <strong>Matières Premières</strong><br>
                                                        <h4 class="mb-0">
                                                            {{ number_format($qualityMetrics['quality']['raw_material_weight_kg'] ?? 0, 2, ',', '.') }}
                                                            kg</h4>
                                                        <small>Poids total consommé</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="alert alert-success text-center">
                                                        <i class="fas fa-cube me-2"></i>
                                                        <strong>Produits Finis (bons)</strong><br>
                                                        <h4 class="mb-0">
                                                            {{ number_format($qualityMetrics['quality']['product_weight_kg'] ?? 0, 2, ',', '.') }}
                                                            kg</h4>
                                                        <small>{{ $qualityMetrics['quality']['total_good_quantity'] ?? 0 }}
                                                            unités</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div
                                                        class="alert @if (($qualityMetrics['quality']['weight_difference_percent'] ?? 0) <= 1) alert-success
                                                    @elseif(($qualityMetrics['quality']['weight_difference_percent'] ?? 0) <= 5) alert-warning
                                                    @else alert-danger @endif text-center">
                                                        <i class="fas fa-balance-scale me-2"></i>
                                                        <strong>Écart Poids</strong><br>
                                                        <h4 class="mb-0">
                                                            {{ number_format($qualityMetrics['quality']['weight_difference_percent'] ?? 0, 2, ',', '.') }}%
                                                        </h4>
                                                        <small>Tolérance: 1% (Bon) / 5% (Alerte)</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Quality Metrics Table -->
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="card">
                                                        <div class="card-header bg-light">
                                                            <strong><i class="fas fa-chart-simple me-2"></i>Indicateurs
                                                                Qualité</strong>
                                                        </div>
                                                        <div class="card-body">
                                                            <table class="table table-sm table-borderless">
                                                                <tr>
                                                                    <th width="50%">Taux de Défauts:</th>
                                                                    <td>
                                                                        <span
                                                                            class="badge @if (($qualityMetrics['quality']['defect_rate_percent'] ?? 0) <= 2) bg-success
                                                                        @elseif(($qualityMetrics['quality']['defect_rate_percent'] ?? 0) <= 5) bg-warning
                                                                        @else bg-danger @endif">
                                                                            {{ number_format($qualityMetrics['quality']['defect_rate_percent'] ?? 0, 2, ',', '.') }}%
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Pièces Bonnes:</th>
                                                                    <td><strong>{{ number_format($qualityMetrics['quality']['total_good_quantity'] ?? 0) }}</strong>
                                                                        unités</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Pièces Défectueuses:</th>
                                                                    <td><strong>{{ number_format($qualityMetrics['quality']['total_defective_quantity'] ?? 0) }}</strong>
                                                                        unités</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Efficacité Production:</th>
                                                                    <td>
                                                                        @if ($qualityMetrics['quality']['efficiency_percent'])
                                                                            <span
                                                                                class="badge @if ($qualityMetrics['quality']['efficiency_percent'] >= 90) bg-success
                                                                            @elseif($qualityMetrics['quality']['efficiency_percent'] >= 70) bg-warning
                                                                            @else bg-danger @endif">
                                                                                {{ number_format($qualityMetrics['quality']['efficiency_percent'], 1) }}%
                                                                            </span>
                                                                        @else
                                                                            <span class="text-muted">Non calculé</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                @if ($qualityMetrics['quality']['quality_checked_at'])
                                                                    <tr>
                                                                        <th>Contrôle effectué le:</th>
                                                                        <td>{{ \Carbon\Carbon::parse($qualityMetrics['quality']['quality_checked_at'])->format('d/m/Y H:i') }}
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                                @if ($qualityMetrics['quality']['quality_checked_by'])
                                                                    <tr>
                                                                        <th>Contrôlé par:</th>
                                                                        <td>{{ $qualityMetrics['quality']['quality_checked_by'] }}
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="card">
                                                        <div class="card-header bg-light">
                                                            <strong><i class="fas fa-weight-hanging me-2"></i>Détail Poids
                                                                par Matière</strong>
                                                        </div>
                                                        <div class="card-body"
                                                            style="max-height: 250px; overflow-y: auto;">
                                                            @if (!empty($qualityMetrics['total_weight_by_material']))
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Matière</th>
                                                                            <th class="text-end">Poids (kg)</th>
                                                                            <th class="text-end">% du total</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @php
                                                                            $totalWeight = array_sum(
                                                                                array_column(
                                                                                    $qualityMetrics[
                                                                                        'total_weight_by_material'
                                                                                    ],
                                                                                    'weight_kg',
                                                                                ),
                                                                            );
                                                                        @endphp
                                                                        @foreach ($qualityMetrics['total_weight_by_material'] as $material)
                                                                            <tr>
                                                                                <td>{{ $material['material_name'] }}</td>
                                                                                <td class="text-end">
                                                                                    {{ number_format($material['weight_kg'], 2, ',', '.') }}
                                                                                </td>
                                                                                <td class="text-end">
                                                                                    @if ($totalWeight > 0)
                                                                                        {{ number_format(($material['weight_kg'] / $totalWeight) * 100, 1) }}%
                                                                                    @else
                                                                                        0%
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            @else
                                                                <p class="text-muted text-center">Aucune donnée de poids
                                                                    disponible</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Quality Notes -->
                                            @if ($qualityMetrics['quality']['quality_notes'])
                                                <div class="alert alert-secondary mt-3">
                                                    <i class="fas fa-sticky-note me-2"></i>
                                                    <strong>Notes Qualité:</strong><br>
                                                    {!! $qualityMetrics['quality']['quality_notes'] !!}
                                                </div>
                                            @endif

                                            <!-- Quality Override Section -->
                                            @if (in_array($qualityMetrics['quality']['quality_status'], ['warning', 'critical']) &&
                                                    !$qualityMetrics['quality']['quality_override']
                                            )
                                                <div class="alert alert-warning mt-3">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    <strong>Attention!</strong> Cet ordre présente des écarts qualité.
                                                    @can('edit_production_orders')
                                                        <button type="button" class="btn btn-sm btn-warning ms-3"
                                                            data-bs-toggle="modal" data-bs-target="#qualityOverrideModal">
                                                            <i class="fas fa-check-circle me-1"></i> Override qualité
                                                        </button>
                                                    @endcan
                                                </div>
                                            @endif

                                            @if ($qualityMetrics['quality']['quality_override'])
                                                <div class="alert alert-info mt-3">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <strong>Override Qualité:</strong>
                                                    {{ $qualityMetrics['quality']['quality_override_reason'] }}<br>
                                                    <small>Approuvé par
                                                        {{ $qualityMetrics['quality']['quality_override_by'] }} le
                                                        {{ \Carbon\Carbon::parse($qualityMetrics['quality']['quality_override_at'])->format('d/m/Y H:i') }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Material Consumption Details -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-chart-bar me-2"></i>Détail des Consommations
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Matière</th>
                                                            <th>Code</th>
                                                            <th class="text-end">Planifié</th>
                                                            <th class="text-end">Réel</th>
                                                            <th class="text-end">Déchet</th>
                                                            <th class="text-end">Écart %</th>
                                                            <th class="text-end">Poids (kg)</th>
                                                            <th class="text-end">Coût (DH)</th>
                                                            <th>Statut</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($qualityMetrics['consumptions'] as $consumption)
                                                            <tr>
                                                                <td>{{ $consumption['material_name'] }}</td>
                                                                <td>{{ $consumption['material_code'] }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($consumption['planned_quantity'], 2, ',', '.') }}
                                                                    {{ $consumption['unit_of_measure'] }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($consumption['actual_quantity_used'], 2, ',', '.') }}
                                                                    {{ $consumption['unit_of_measure'] }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($consumption['waste_quantity'], 2, ',', '.') }}
                                                                    {{ $consumption['unit_of_measure'] }}</td>
                                                                <td class="text-end">
                                                                    <span
                                                                        class="badge @if ($consumption['difference_percent'] <= 1) bg-success
                                                                    @elseif($consumption['difference_percent'] <= 5) bg-warning
                                                                    @else bg-danger @endif">
                                                                        {{ number_format($consumption['difference_percent'], 2, ',', '.') }}%
                                                                    </span>
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($consumption['weight_kg'], 2, ',', '.') }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($consumption['total_cost'], 2, ',', '.') }}</td>
                                                                <td>
                                                                    @if ($consumption['actual_quantity_used'] == 0)
                                                                        <span class="badge bg-secondary">Planifié</span>
                                                                    @elseif($consumption['difference_percent'] <= 1)
                                                                        <span class="badge bg-success">Conforme</span>
                                                                    @elseif($consumption['actual_quantity_used'] < $consumption['planned_quantity'])
                                                                        <span
                                                                            class="badge bg-info">Sous-consommation</span>
                                                                    @else
                                                                        <span
                                                                            class="badge bg-warning">Sur-consommation</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="9" class="text-center text-muted">Aucune
                                                                    donnée de consommation</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Production Outputs Quality Details -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-boxes me-2"></i>Détail des Sorties par Famille
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Famille</th>
                                                            <th class="text-end">Produites</th>
                                                            <th class="text-end">Défectueuses</th>
                                                            <th class="text-end">Bonnes</th>
                                                            <th class="text-end">Taux Défaut</th>
                                                            <th class="text-end">Volume (m³)</th>
                                                            <th>Cible</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($qualityMetrics['outputs'] as $output)
                                                            <tr
                                                                @if ($output['is_final']) class="table-success" @endif>
                                                                <td>{{ $output['date'] }}</td>
                                                                <td>{{ $output['famille'] ?? 'N/A' }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($output['quantity_produced']) }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($output['quantity_defective']) }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($output['good_quantity']) }}</td>
                                                                <td class="text-end">
                                                                    <span
                                                                        class="badge @if ($output['defect_rate'] <= 2) bg-success
                                                                    @elseif($output['defect_rate'] <= 5) bg-warning
                                                                    @else bg-danger @endif">
                                                                        {{ number_format($output['defect_rate'], 2, ',', '.') }}%
                                                                    </span>
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($output['total_volume'], 4) }}</td>
                                                                <td class="text-center">
                                                                    @if ($output['is_final'])
                                                                        <i class="fas fa-star text-warning"
                                                                            title="Famille cible"></i>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="8" class="text-center text-muted">Aucune
                                                                    sortie enregistrée</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Production Output Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div
                                        class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-boxes me-2"></i>Sorties de Production
                                        </h6>
                                        @if ($order->status === 'in_progress')
                                            <a href="{{ route('production-output.create') }}?order_id={{ $order->order_id }}"
                                                class="btn btn-light btn-sm">
                                                <i class="fas fa-plus me-1"></i> Ajouter Sortie
                                            </a>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        @if ($order->outputs->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Produit</th>
                                                            <th>Famille</th>
                                                            <th>Date Production</th>
                                                            <th>Quantité Produite</th>
                                                            <th>Quantité Défectueuse</th>
                                                            <th>Quantité Bonne</th>
                                                            <th>Approuvé par</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($order->outputs as $output)
                                                            <tr>
                                                                <td>{{ $loop->iteration }}</td>
                                                                <td>
                                                                    {{ $output->product->product_name ?? 'N/A' }}
                                                                    <br>
                                                                    <small
                                                                        class="text-muted">{{ $output->product->product_code ?? '' }}</small>
                                                                </td>
                                                                <td>
                                                                    @if ($output->famille)
                                                                        <span class="badge bg-info">
                                                                            {{ $output->famille->famille_name }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-muted">Non spécifiée</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ \Carbon\Carbon::parse($output->production_date)->format('d/m/Y') }}
                                                                </td>
                                                                <td>{{ $output->quantity_produced }} unités</td>
                                                                <td>{{ $output->quantity_defective }} unités</td>
                                                                <td>{{ $output->quantity_produced - $output->quantity_defective }}
                                                                    unités</td>
                                                                <td>{{ $output->approver->username ?? ($output->approved_by ? 'Utilisateur #' . $output->approved_by : 'N/A') }}
                                                                </td>
                                                                <td>
                                                                    <a href="{{ route('production-output.show', $output->output_id) }}"
                                                                        class="btn btn-sm btn-info">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="table-primary">
                                                            <td colspan="3"><strong>Total</strong></td>
                                                            <td colspan="1"></td>
                                                            <td><strong>{{ $order->outputs->sum('quantity_produced') }}
                                                                    unités</strong></td>
                                                            <td><strong>{{ $order->outputs->sum('quantity_defective') }}
                                                                    unités</strong></td>
                                                            <td><strong>{{ $order->outputs->sum('quantity_produced') - $order->outputs->sum('quantity_defective') }}
                                                                    unités</strong></td>
                                                            <td colspan="2"></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Aucune sortie de production enregistrée pour cet ordre.
                                                @if ($order->status === 'in_progress')
                                                    <a href="{{ route('production-output.create') }}?order_id={{ $order->order_id }}"
                                                        class="alert-link">
                                                        Ajouter la première sortie
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Material Consumption Section (Only for Type 1 when no quality metrics) -->
                        @if ($order->production_type === 'type1' && !isset($qualityMetrics))
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-list-alt me-2"></i>Consommation Matières Premières
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if ($order->consumptions->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Matière Première</th>
                                                                <th>Planifié</th>
                                                                <th>Réel</th>
                                                                <th>Déchet</th>
                                                                <th>Coût Unitaire</th>
                                                                <th>Coût Total</th>
                                                                <th>Statut</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $totalPlanned = 0;
                                                                $totalActual = 0;
                                                                $totalWaste = 0;
                                                                $totalCost = 0;
                                                            @endphp
                                                            @foreach ($order->consumptions as $consumption)
                                                                @php
                                                                    $totalPlanned += $consumption->planned_quantity;
                                                                    $totalActual += $consumption->actual_quantity_used;
                                                                    $totalWaste += $consumption->waste_quantity;
                                                                    $totalCost += $consumption->total_cost;
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>
                                                                        {{ $consumption->rawMaterial->material_name }}
                                                                        <br>
                                                                        <small
                                                                            class="text-muted">{{ $consumption->rawMaterial->material_code }}</small>
                                                                    </td>
                                                                    <td>{{ number_format($consumption->planned_quantity, 2, ',', '.') }}
                                                                        {{ $consumption->rawMaterial->unit_of_measure }}
                                                                    </td>
                                                                    <td>{{ number_format($consumption->actual_quantity_used, 2, ',', '.') }}
                                                                        {{ $consumption->rawMaterial->unit_of_measure }}
                                                                    </td>
                                                                    <td>{{ number_format($consumption->waste_quantity, 2, ',', '.') }}
                                                                        {{ $consumption->rawMaterial->unit_of_measure }}
                                                                    </td>
                                                                    <td>{{ number_format($consumption->unit_cost, 2, ',', '.') }} DH
                                                                    </td>
                                                                    <td>{{ number_format($consumption->total_cost, 2, ',', '.') }} DH
                                                                    </td>
                                                                    <td>
                                                                        @if ($consumption->actual_quantity_used == 0)
                                                                            <span
                                                                                class="badge bg-secondary">Planifié</span>
                                                                        @elseif(abs($consumption->actual_quantity_used - $consumption->planned_quantity) <= $consumption->planned_quantity * 0.05)
                                                                            <span class="badge bg-success">Conforme</span>
                                                                        @elseif($consumption->actual_quantity_used < $consumption->planned_quantity)
                                                                            <span
                                                                                class="badge bg-info">Sous-consommation</span>
                                                                        @else
                                                                            <span
                                                                                class="badge bg-warning">Sur-consommation</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="table-primary">
                                                                <td colspan="2"><strong>Total</strong></td>
                                                                <td><strong>{{ number_format($totalPlanned, 2, ',', '.') }}</strong>
                                                                </td>
                                                                <td><strong>{{ number_format($totalActual, 2, ',', '.') }}</strong>
                                                                </td>
                                                                <td><strong>{{ number_format($totalWaste, 2, ',', '.') }}</strong>
                                                                </td>
                                                                <td colspan="2">
                                                                    <strong>{{ number_format($totalCost, 2, ',', '.') }} DH</strong>
                                                                </td>
                                                                <td></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Aucune consommation enregistrée pour cet ordre.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Waste Section (Chutes) -->
                        @if ($order->wastes->count() > 0)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-warning text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-trash me-2"></i>Chutes et Déchets
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $totalRecyclableVolume = $order->wastes
                                                    ->where('waste_type', 'recyclable')
                                                    ->sum('volume_m3');
                                                $totalWasteVolume = $order->wastes
                                                    ->where('waste_type', 'waste')
                                                    ->sum('volume_m3');
                                                $totalVolume = $totalRecyclableVolume + $totalWasteVolume;
                                            @endphp

                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <div class="alert alert-success text-center">
                                                        <strong>Recyclable</strong>
                                                        <h5 class="mb-0">{{ number_format($totalRecyclableVolume, 4) }}
                                                            m³</h5>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="alert alert-danger text-center">
                                                        <strong>Déchet</strong>
                                                        <h5 class="mb-0">{{ number_format($totalWasteVolume, 4) }} m³
                                                        </h5>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="alert alert-info text-center">
                                                        <strong>Total Chutes</strong>
                                                        <h5 class="mb-0">{{ number_format($totalVolume, 4) }} m³</h5>
                                                        <small>{{ $order->wastes->count() }} chute(s)</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Type</th>
                                                            <th>Source</th>
                                                            <th>Dimensions</th>
                                                            <th>Volume</th>
                                                            <th>Catégorie</th>
                                                            <th>Notes</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($order->wastes as $waste)
                                                            <tr>
                                                                <td>{{ $loop->iteration }}</td>
                                                                <td>
                                                                    @if ($waste->waste_type === 'recyclable')
                                                                        <span class="badge bg-success">Recyclable</span>
                                                                    @elseif($waste->waste_type === 'auto_defective')
                                                                        <span class="badge bg-info">Auto-défaut</span>
                                                                    @else
                                                                        <span class="badge bg-danger">Déchet</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $waste->waste_source ?? '-' }}</td>
                                                                <td>
                                                                    @if ($waste->height && $waste->width && $waste->depth)
                                                                        {{ $waste->height }}m × {{ $waste->width }}m ×
                                                                        {{ $waste->depth }}m
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td>{{ number_format($waste->volume_m3 ?? 0, 4) }} m³</td>
                                                                <td>{{ $waste->waste_category ?? '-' }}</td>
                                                                <td>{{ $waste->notes ?? '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="table-primary">
                                                        <tr>
                                                            <td colspan="4"><strong>Total</strong></td>
                                                            <td><strong>{{ number_format($totalVolume, 4) }} m³</strong>
                                                            </td>
                                                            <td colspan="2"></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Notes Section -->
                        @if ($order->notes)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-sticky-note me-2"></i>Notes
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <p>{{ $order->notes }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('production-orders.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Retour
                                    </a>
                                    <div class="d-flex gap-2">
                                        @if ($order->status === 'pending' || $order->status === 'approved')
                                            <button class="btn btn-success start-production"
                                                data-id="{{ $order->order_id }}"
                                                data-number="{{ $order->order_number }}">
                                                <i class="fas fa-play me-1"></i> Démarrer la Production
                                            </button>
                                        @endif
                                        @if (!in_array($order->status, ['cancelled']))
                                            @can('cancel_production_orders')
                                            <button class="btn btn-danger btn-cancel-production"
                                                data-id="{{ $order->order_id }}"
                                                data-order-number="{{ $order->order_number }}"
                                                data-status="{{ $order->status }}"
                                                data-production-date="{{ $order->actual_completion_date ? $order->actual_completion_date->format('d/m/Y') : '' }}">
                                                <i class="fas fa-ban me-1"></i> Annuler
                                            </button>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quality Override Modal -->
    @if (
        $order->production_type === 'type1' &&
            isset($qualityMetrics) &&
            in_array($qualityMetrics['quality']['quality_status'] ?? 'pending', ['warning', 'critical']) &&
            !($qualityMetrics['quality']['quality_override'] ?? false))
        <div class="modal fade" id="qualityOverrideModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-check-circle me-2"></i>Override Qualité
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="qualityOverrideForm">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Attention!</strong> Cet ordre présente des écarts qualité.
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Détails de l'écart:</label>
                                <div class="alert alert-secondary">
                                    <strong>Écart poids:</strong>
                                    {{ number_format($qualityMetrics['quality']['weight_difference_percent'] ?? 0, 2, ',', '.') }}%<br>
                                    <strong>Statut qualité:</strong>
                                    @if (($qualityMetrics['quality']['quality_status'] ?? 'pending') === 'warning')
                                        <span class="badge bg-warning">Attention</span>
                                    @else
                                        <span class="badge bg-danger">Critique</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="override_reason" class="form-label">Raison de l'override *</label>
                                <textarea class="form-control" id="override_reason" name="reason" rows="3"
                                    placeholder="Expliquez pourquoi cet écart est acceptable..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="force_completion"
                                        name="force_completion" value="1">
                                    <label class="form-check-label" for="force_completion">
                                        Forcer la complétion de l'ordre (même si production non terminée)
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-check-circle me-1"></i>Confirmer l'Override
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

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

                            <div id="cancelStockLoading" class="text-center py-2">
                                <span class="spinner-border spinner-border-sm me-2 text-secondary"></span>
                                <small class="text-muted">Chargement des consommations...</small>
                            </div>

                            <div id="cancelStockNone" class="alert alert-info mb-0 d-none" style="font-size:.875rem">
                                <i class="fas fa-info-circle me-1"></i>
                                Aucune consommation enregistrée pour cet ordre — aucun stock à restaurer.
                            </div>

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

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <style>
        .card-header-custom {
            background-color: #4e73df;
        }

        .progress-bar {
            transition: width 0.6s ease;
        }

        .badge {
            font-size: 0.8em;
            padding: 0.4em 0.8em;
        }

        .toast-container {
            z-index: 9999;
        }

        .table-success {
            background-color: #d1e7dd !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Handle start production button click
            $('.start-production').click(function() {
                var orderId = $(this).data('id');
                var orderNumber = $(this).data('number');

                if (confirm('Voulez-vous démarrer la production pour l\'ordre ' + orderNumber + ' ?')) {
                    $.ajax({
                        url: "{{ url('production-orders') }}/" + orderId + "/start",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                if (response.insufficient_materials) {
                                    var message = response.message + '\n\n';
                                    response.insufficient_materials.forEach(function(material) {
                                        message += '• ' + material.material +
                                            ': Requis ' + material.required +
                                            ', Disponible ' + material.available + ' ' +
                                            material.unit + '\n';
                                    });
                                    alert(message);
                                } else {
                                    showToast('error', response.message);
                                }
                            }
                        },
                        error: function(xhr) {
                            var response = xhr.responseJSON;
                            showToast('error', response?.message ||
                                'Erreur lors du démarrage de la production');
                        }
                    });
                }
            });

            // Cancel button handler
            $('.btn-cancel-production').click(function() {
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

                // Reset state
                $('#cancelStockLoading').removeClass('d-none');
                $('#cancelStockNone').addClass('d-none');
                $('#cancelStockTable').addClass('d-none');
                $('#cancelStockTableBody').empty();
                $('#cancellationReason').val('');
                $('#additionalNotes').val('');

                $('#cancelModal').modal('show');

                // Fetch stock preview
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

                            $('#cancelSelectAll').off('change').on('change', function() {
                                $('.cancel-item-check').prop('checked', $(this).is(':checked'));
                            });
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

            // Confirm cancel
            $('#confirmCancel').click(function() {
                var orderId = $('#cancelModal').data('order-id');
                var reason = $('#cancellationReason').val();
                var notes = $('#additionalNotes').val();

                if (!reason) {
                    showToast('error', 'Veuillez sélectionner une raison d\'annulation');
                    return;
                }

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
                            showToast('success', response.message);
                            if (response.restored && response.restored.length > 0) {
                                response.restored.forEach(function(item) {
                                    var qty = parseFloat(item.qty).toFixed(4);
                                    if (item.direction === 'remove') {
                                        showToast('warning', '↺ ' + item.name + ': − ' + qty + ' ' + item.unit + ' retiré du stock');
                                    } else {
                                        showToast('success', '↩ ' + item.name + ': + ' + qty + ' ' + item.unit + ' restauré');
                                    }
                                });
                            }
                            setTimeout(function() { location.reload(); }, 2000);
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

            // Reset cancel modal on close
            $('#cancelModal').on('hidden.bs.modal', function() {
                $('#cancellationReason').val('');
                $('#additionalNotes').val('');
                $('#cancelSelectAll').prop('checked', true);
                $('#confirmCancel').prop('disabled', false)
                    .html('<i class="fas fa-ban me-2"></i>Confirmer l\'annulation');
            });

            // Quality Override Form Submission
            $('#qualityOverrideForm').on('submit', function(e) {
                e.preventDefault();

                var reason = $('#override_reason').val();
                var forceCompletion = $('#force_completion').is(':checked') ? 1 : 0;
                var orderId = {{ $order->order_id }};

                if (!reason) {
                    showToast('error', 'Veuillez entrer une raison pour l\'override');
                    return;
                }

                $.ajax({
                    url: "{{ url('production-orders') }}/" + orderId + "/override-quality",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        reason: reason,
                        force_completion: forceCompletion
                    },
                    beforeSend: function() {
                        $('#qualityOverrideForm button[type="submit"]').prop('disabled', true)
                            .html(
                                '<span class="spinner-border spinner-border-sm me-2"></span> Traitement...'
                            );
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#qualityOverrideModal').modal('hide');
                            showToast('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON;
                        showToast('error', response?.message || 'Erreur lors de l\'override');
                    },
                    complete: function() {
                        $('#qualityOverrideForm button[type="submit"]').prop('disabled', false)
                            .html(
                                '<i class="fas fa-check-circle me-1"></i>Confirmer l\'Override'
                            );
                    }
                });
            });

            // Toast notification function
            function showToast(type, message) {
                var toast = $('<div class="toast align-items-center text-white bg-' +
                    (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger')) +
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
        });
    </script>
@endpush
