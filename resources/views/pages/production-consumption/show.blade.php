@extends('layouts.app')

@section('title', 'Détails Consommation Matières Premières')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails de la Consommation Matières Premières</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('production-consumption.index') }}">
                                        Consommation
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
                            <i class="fas fa-info-circle me-2"></i>Consommation Matières Premières
                        </h5>
                        <div>
                            <a href="{{ route('production-consumption.edit', $consumption->consumption_id) }}"
                                class="btn btn-light btn-sm me-2">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            <a href="{{ route('production-orders.show', $consumption->production_order_id) }}"
                                class="btn btn-info btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Voir l'Ordre
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
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
                                                <th width="40%">Ordre de Production:</th>
                                                <td>
                                                    <a
                                                        href="{{ route('production-orders.show', $consumption->production_order_id) }}">
                                                        {{ $consumption->productionOrder->order_number }}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Produit:</th>
                                                <td>
                                                    {{ $consumption->productionOrder->product->product_name }}
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ $consumption->productionOrder->product->product_code }}</small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Matière Première:</th>
                                                <td>
                                                    {{ $consumption->rawMaterial->material_name }}
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ $consumption->rawMaterial->material_code }}</small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Unité:</th>
                                                <td>{{ $consumption->rawMaterial->unit_of_measure }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card info-card mb-4">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-chart-bar me-2"></i>Statistiques de Consommation
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <td>Quantité Planifiée:</td>
                                                <td class="text-right">
                                                    {{ number_format($consumption->planned_quantity, 2, ',', '.') }}
                                                    {{ $consumption->rawMaterial->unit_of_measure }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Quantité Réelle:</td>
                                                <td class="text-right">
                                                    {{ number_format($consumption->actual_quantity_used, 2, ',', '.') }}
                                                    {{ $consumption->rawMaterial->unit_of_measure }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Quantité Déchet:</td>
                                                <td class="text-right">
                                                    <span class="badge badge-danger">
                                                        {{ number_format($consumption->waste_quantity, 2, ',', '.') }}
                                                        {{ $consumption->rawMaterial->unit_of_measure }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Taux de Déchet:</td>
                                                <td class="text-right">
                                                    @php
                                                        $wasteRate =
                                                            $consumption->actual_quantity_used > 0
                                                                ? ($consumption->waste_quantity /
                                                                        $consumption->actual_quantity_used) *
                                                                    100
                                                                : 0;
                                                    @endphp
                                                    @if ($wasteRate >= 10)
                                                        <span
                                                            class="badge badge-danger">{{ number_format($wasteRate, 2, ',', '.') }}%</span>
                                                    @elseif($wasteRate >= 5)
                                                        <span
                                                            class="badge badge-warning">{{ number_format($wasteRate, 2, ',', '.') }}%</span>
                                                    @else
                                                        <span
                                                            class="badge badge-success">{{ number_format($wasteRate, 2, ',', '.') }}%</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Différence Plan/Réel:</td>
                                                <td class="text-right">
                                                    @php
                                                        $difference =
                                                            $consumption->actual_quantity_used -
                                                            $consumption->planned_quantity;
                                                        $differencePercent =
                                                            $consumption->planned_quantity > 0
                                                                ? ($difference / $consumption->planned_quantity) * 100
                                                                : 0;
                                                    @endphp
                                                    @if ($difference > 0)
                                                        <span class="badge badge-warning">
                                                            +{{ number_format($difference, 2, ',', '.') }}
                                                            ({{ number_format($differencePercent, 2, ',', '.') }}%)
                                                        </span>
                                                    @elseif($difference < 0)
                                                        <span class="badge badge-info">
                                                            {{ number_format($difference, 2, ',', '.') }}
                                                            ({{ number_format($differencePercent, 2, ',', '.') }}%)
                                                        </span>
                                                    @else
                                                        <span class="badge badge-success">Conforme</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Coût Unitaire:</td>
                                                <td class="text-right">
                                                    {{ number_format($consumption->unit_cost, 2, ',', '.') }} DH
                                                </td>
                                            </tr>
                                            <tr class="table-success">
                                                <td><strong>Coût Total:</strong></td>
                                                <td class="text-right">
                                                    <strong>{{ number_format($consumption->total_cost, 2, ',', '.') }} DH</strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Impact on Stock -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-boxes me-2"></i>Impact sur le Stock Matière Première
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="alert alert-danger">
                                                    <i class="fas fa-arrow-down me-2"></i>
                                                    <strong>Stock diminué de
                                                        {{ number_format($consumption->actual_quantity_used + $consumption->waste_quantity, 2, ',', '.') }}
                                                        {{ $consumption->rawMaterial->unit_of_measure }}</strong>
                                                    <br>
                                                    <small>Quantité consommée depuis le stock de la matière première</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-trash-alt me-2"></i>
                                                    <strong>{{ number_format($consumption->waste_quantity, 2, ',', '.') }}
                                                        {{ $consumption->rawMaterial->unit_of_measure }}
                                                        gaspillées</strong>
                                                    <br>
                                                    <small>Coût des déchets:
                                                        {{ number_format($consumption->waste_quantity * $consumption->unit_cost, 2, ',', '.') }}
                                                        DH</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <h6>Stock Actuel de la Matière Première:</h6>
                                            @php
                                                $currentStock = $consumption->rawMaterial->current_stock;
                                                $minStock = $consumption->rawMaterial->min_stock_level;
                                                $maxStock = $consumption->rawMaterial->max_stock_level;
                                            @endphp
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar bg-info"
                                                    style="width: {{ min(100, ($currentStock / max(1, $maxStock)) * 100) }}%"
                                                    role="progressbar">
                                                    {{ number_format($currentStock, 2, ',', '.') }}
                                                    {{ $consumption->rawMaterial->unit_of_measure }}
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                Actuel: {{ number_format($currentStock, 2, ',', '.') }} |
                                                Minimum: {{ $minStock }} |
                                                Maximum: {{ $maxStock }}
                                            </small>
                                            @if ($currentStock <= $minStock)
                                                <div class="alert alert-warning mt-2">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    Stock en dessous du niveau minimum!
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($consumption->notes)
                            <!-- Notes Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-sticky-note me-2"></i>Notes
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <p>{{ $consumption->notes }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('production-consumption.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Retour
                                    </a>
                                    <div>
                                        <button class="btn btn-primary" onclick="window.print()">
                                            <i class="fas fa-print me-1"></i> Imprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
