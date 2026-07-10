@extends('layouts.app')

@section('title', 'Détails Sortie de Production')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails de la Sortie de Production</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('production-output.index') }}">
                                        Sorties de Production
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
                            <i class="fas fa-info-circle me-2"></i>Sortie de Production
                        </h5>
                        <div>
                            @php
                                $status = $output->productionOrder->status ?? 'unknown';
                                $canEdit = in_array($status, ['in_progress', 'pending', 'approved']);
                            @endphp

                            @if ($canEdit)
                                <a href="{{ route('production-output.edit', $output->output_id) }}"
                                    class="btn btn-light btn-sm me-2">
                                    <i class="fas fa-edit me-1"></i> Modifier
                                </a>
                            @endif

                            <a href="{{ route('production-orders.show', $output->production_order_id) }}"
                                class="btn btn-info btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Voir l'Ordre
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card info-card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-2"></i>Informations Générales
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="45%">Ordre de Production:</th>
                                                <td>
                                                    <a
                                                        href="{{ route('production-orders.show', $output->production_order_id) }}">
                                                        {{ $output->productionOrder->order_number }}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Produit:</th>
                                                <td>
                                                    {{ $output->product->product_name }}
                                                    <br>
                                                    <small class="text-muted">{{ $output->product->product_code }}</small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Famille:</th>
                                                <td>{{ $output->famille->famille_name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Type de Sortie:</th>
                                                <td>
                                                    @if ($output->output_type === 'type1')
                                                        <span class="badge bg-primary">Production</span>
                                                    @elseif($output->output_type === 'type2')
                                                        <span class="badge bg-info">Découpage</span>
                                                    @elseif($output->output_type === 'type3')
                                                        <span class="badge bg-success">Conversion</span>
                                                    @elseif($output->output_type === 'mixed_family')
                                                        <span class="badge bg-warning">Mixte Famille</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Date de Production:</th>
                                                <td>{{ \Carbon\Carbon::parse($output->production_date)->format('d/m/Y') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Approuvé par:</th>
                                                <td>{{ $output->approver->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Créé le:</th>
                                                <td>
                                                    @php
                                                        // Safely format the created_at date
                                                        $createdAt = $output->created_at;
                                                        if (is_string($createdAt)) {
                                                            $createdAt = \Carbon\Carbon::parse($createdAt);
                                                        }
                                                    @endphp
                                                    {{ $createdAt->format('d/m/Y H:i') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card info-card mb-4">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-chart-bar me-2"></i>Statistiques de Production
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Quantité Produite:</strong></td>
                                                <td class="text-end">
                                                    <span class="badge bg-primary fs-6">{{ $output->quantity_produced }}
                                                        unités</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Quantité Défectueuse:</td>
                                                <td class="text-end">
                                                    <span class="badge bg-danger">{{ $output->quantity_defective }}
                                                        unités</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Quantité Bonne:</td>
                                                <td class="text-end">
                                                    <span class="badge bg-success">{{ $goodQuantity }} unités</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Taux de Défaut:</td>
                                                <td class="text-end">
                                                    @if ($defectRate >= 10)
                                                        <span
                                                            class="badge bg-danger">{{ number_format($defectRate, 2, ',', '.') }}%</span>
                                                    @elseif($defectRate >= 5)
                                                        <span
                                                            class="badge bg-warning">{{ number_format($defectRate, 2, ',', '.') }}%</span>
                                                    @else
                                                        <span
                                                            class="badge bg-success">{{ number_format($defectRate, 2, ',', '.') }}%</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Volume Total:</strong></td>
                                                <td class="text-end">
                                                    <span
                                                        class="badge bg-info fs-6">{{ number_format($output->total_volume_m3 ?? 0, 4) }}
                                                        m³</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Volume Utile:</td>
                                                <td class="text-end">{{ number_format($goodVolume, 4) }} m³</td>
                                            </tr>
                                            <tr>
                                                <td>Volume Déchet:</td>
                                                <td class="text-end">{{ number_format($wasteVolume, 4) }} m³</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card info-card mb-4">
                                    <div class="card-header bg-warning text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-cube me-2"></i>Informations Volume
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <td width="60%">Volume Unitaire:</td>
                                                <td class="text-end">{{ number_format($unitVolume, 4) }} m³</td>
                                            </tr>
                                            <tr>
                                                <td>Volume Total Calculé:</td>
                                                <td class="text-end">
                                                    {{ number_format($unitVolume * $output->quantity_produced, 4) }} m³
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Volume Total Enregistré:</td>
                                                <td class="text-end">{{ number_format($output->total_volume_m3 ?? 0, 4) }}
                                                    m³</td>
                                            </tr>
                                            <tr>
                                                <td>Efficacité Volume:</td>
                                                <td class="text-end">
                                                    @php
                                                        $calculatedVolume = $unitVolume * $output->quantity_produced;
                                                        $recordedVolume = $output->total_volume_m3 ?? 0;
                                                        $volumeEfficiency =
                                                            $calculatedVolume > 0
                                                                ? ($recordedVolume / $calculatedVolume) * 100
                                                                : 0;
                                                    @endphp
                                                    @if ($volumeEfficiency >= 95)
                                                        <span
                                                            class="badge bg-success">{{ number_format($volumeEfficiency, 1) }}%</span>
                                                    @elseif($volumeEfficiency >= 90)
                                                        <span
                                                            class="badge bg-warning">{{ number_format($volumeEfficiency, 1) }}%</span>
                                                    @else
                                                        <span
                                                            class="badge bg-danger">{{ number_format($volumeEfficiency, 1) }}%</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Déchets Recyclables:</td>
                                                <td class="text-end">
                                                    {{ number_format($output->recyclable_waste_volume ?? 0, 4) }} m³</td>
                                            </tr>
                                            <tr>
                                                <td>Déchets Purs:</td>
                                                <td class="text-end">
                                                    {{ number_format($output->pure_waste_volume ?? 0, 4) }} m³</td>
                                            </tr>
                                            <tr>
                                                <td>Total Déchets:</td>
                                                <td class="text-end">{{ number_format($output->waste_volume_m3 ?? 0, 4) }}
                                                    m³</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Production Order Summary -->
                        @if (is_array($productionSummary))
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-clipboard-list me-2"></i>Résumé de l'Ordre de Production
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h4>{{ $productionSummary['target_quantity'] ?? 0 }}</h4>
                                                        <p class="text-muted">Quantité Cible</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h4 class="text-success">
                                                            {{ $productionSummary['target_produced'] ?? 0 }}</h4>
                                                        <p class="text-muted">Produit Total</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h4 class="text-warning">
                                                            {{ $productionSummary['remaining'] ?? 0 }}</h4>
                                                        <p class="text-muted">Reste à Produire</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h4 class="text-primary">
                                                            {{ $productionSummary['total_produced'] ?? 0 }}</h4>
                                                        <p class="text-muted">Total Toutes Familles</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Wastes Information -->
                        @if ($wastes && $wastes->isNotEmpty())
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-danger text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-trash me-2"></i>Déchets Associés à l'Ordre
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Type</th>
                                                            <th>Source</th>
                                                            <th>Catégorie</th>
                                                            <th>Volume</th>
                                                            <th>Dimensions</th>
                                                            <th>Notes</th>
                                                            <th>Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($wastes as $waste)
                                                            <tr>
                                                                <td>
                                                                    @if ($waste->waste_type === 'recyclable')
                                                                        <span class="badge bg-success">♻️ Recyclable</span>
                                                                    @elseif($waste->waste_type === 'auto_defective')
                                                                        <span class="badge bg-info">Auto-défaut</span>
                                                                    @elseif($waste->waste_type === 'waste')
                                                                        <span class="badge bg-danger">🗑️ Déchet</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $waste->waste_source }}</td>
                                                                <td>{{ $waste->waste_category ?? 'N/A' }}</td>
                                                                <td>{{ number_format($waste->volume_m3, 4) }} m³</td>
                                                                <td>
                                                                    @if ($waste->height && $waste->width && $waste->depth)
                                                                        {{ $waste->height }}m × {{ $waste->width }}m ×
                                                                        {{ $waste->depth }}m
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                                <td>{{ $waste->notes ?? 'N/A' }}</td>
                                                                <td>
                                                                    @php
                                                                        $wasteDate = $waste->created_at;
                                                                        if (is_string($wasteDate)) {
                                                                            $wasteDate = \Carbon\Carbon::parse(
                                                                                $wasteDate,
                                                                            );
                                                                        }
                                                                    @endphp
                                                                    {{ $wasteDate->format('d/m/Y H:i') }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="table-primary">
                                                            <td colspan="3"><strong>TOTAL DÉCHETS</strong></td>
                                                            <td><strong>{{ number_format($wastes->sum('volume_m3'), 4) }}
                                                                    m³</strong></td>
                                                            <td colspan="3"></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Impact on Stock -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-boxes me-2"></i>Impact sur le Stock
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="alert alert-success">
                                                    <i class="fas fa-arrow-up me-2"></i>
                                                    <strong>Stock augmenté de {{ $goodQuantity }} unités</strong>
                                                    <br>
                                                    <small>Quantité bonne ajoutée au stock de la famille
                                                        {{ $output->famille->famille_name ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="alert alert-danger">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    <strong>{{ $output->quantity_defective }} unités défectueuses</strong>
                                                    <br>
                                                    <small>Non ajoutées au stock - traitées comme déchets</small>
                                                </div>
                                            </div>
                                        </div>

                                        @php
                                            $familleStock = \App\Models\ProductFamilleStock::where(
                                                'product_id',
                                                $output->product_id,
                                            )
                                                ->where('famille_id', $output->famille_id)
                                                ->first();

                                            if ($familleStock) {
                                                $currentStock = $familleStock->current_quantity ?? 0;
                                                $reservedStock = $familleStock->reserved_quantity ?? 0;
                                                $availableStock = $currentStock - $reservedStock;
                                            }
                                        @endphp

                                        @if (isset($familleStock))
                                            <div class="mt-3">
                                                <h6>Stock Actuel de la Famille:</h6>
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-success"
                                                        style="width: {{ min(100, ($availableStock / max(1, $currentStock)) * 100) }}%"
                                                        role="progressbar">
                                                        {{ $availableStock }} unités disponibles
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    Disponible: {{ $availableStock }} unités |
                                                    Réservé: {{ $reservedStock }} unités |
                                                    Total: {{ $currentStock }} unités
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Conversion Data (for Type 3) -->
                        @if ($output->output_type === 'type3' && $output->conversion_data)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-purple text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-exchange-alt me-2"></i>Données de Conversion
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th width="30%">Taux de Conversion:</th>
                                                    <td>{{ $output->conversion_data['conversion_rate'] ?? 1 }} produit(s)
                                                        par sous-bloc</td>
                                                </tr>
                                                <tr>
                                                    <th>Sous-blocs Consommés:</th>
                                                    <td>{{ $output->quantity_consumed }} unités</td>
                                                </tr>
                                                <tr>
                                                    <th>Produits Générés:</th>
                                                    <td>{{ $output->quantity_produced }} unités</td>
                                                </tr>
                                                <tr>
                                                    <th>Efficacité de Conversion:</th>
                                                    <td>
                                                        @php
                                                            $conversionEfficiency =
                                                                $output->quantity_consumed > 0
                                                                    ? ($output->quantity_produced /
                                                                            $output->quantity_consumed) *
                                                                        100
                                                                    : 0;
                                                        @endphp
                                                        @if ($conversionEfficiency >= 95)
                                                            <span
                                                                class="badge bg-success">{{ number_format($conversionEfficiency, 1) }}%</span>
                                                        @elseif($conversionEfficiency >= 90)
                                                            <span
                                                                class="badge bg-warning">{{ number_format($conversionEfficiency, 1) }}%</span>
                                                        @else
                                                            <span
                                                                class="badge bg-danger">{{ number_format($conversionEfficiency, 1) }}%</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($output->notes)
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
                                            <p>{{ $output->notes }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('production-output.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Retour à la Liste
                                    </a>
                                    <div>
                                        <a href="{{ route('production-output.create', ['order_id' => $output->production_order_id]) }}"
                                            class="btn btn-primary me-2">
                                            <i class="fas fa-plus me-1"></i> Nouvelle Sortie
                                        </a>
                                        <button class="btn btn-info" onclick="window.print()">
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

@push('styles')
    <style>
        .info-card .card-header {
            border-bottom: none;
        }

        .badge {
            font-size: 0.85em;
            padding: 0.4em 0.7em;
        }

        .bg-purple {
            background-color: #6f42c1 !important;
        }

        .progress {
            background-color: #e9ecef;
        }

        .progress-bar {
            transition: width 0.6s ease;
        }
    </style>
@endpush
