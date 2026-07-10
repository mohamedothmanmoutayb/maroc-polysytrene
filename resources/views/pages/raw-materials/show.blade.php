@extends('layouts.app')

@section('title', 'Détails Matière Première')

@push('stylesheets')
    <style>
        .info-card {
            border-left: 2px solid #007bff;
        }

        .stock-card {
            border-left: 2px solid #28a745;
        }

        .cost-card {
            border-left: 2px solid #ffc107;
        }

        .detail-card {
            border-left: 2px solid #6f42c1;
        }

        .card-header-custom {
            background: linear-gradient(45deg, #2c3e50, #4a6491);
            color: white;
            border-bottom: none;
        }

        .stock-detail-row:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails de la Matière Première</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Détails Matière
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Information Card -->
            <div class="col-md-6">
                <div class="card info-card mb-4">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-info-circle me-2"></i>Informations Générales
                        </h5>
                        <div>
                            @can('edit_raw_materials')
                            <a href="{{ route('raw-materials.edit', $material->material_id) }}"
                                class="btn btn-light btn-sm">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Code:</th>
                                        <td><strong>{{ $material->material_code }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Nom:</th>
                                        <td>{{ $material->material_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Catégorie:</th>
                                        <td>{{ $material->category->category_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Unité:</th>
                                        <td>{{ $material->unit_of_measure }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Fournisseur:</th>
                                        <td>{{ $material->supplier->company_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Magasin:</th>
                                        <td>{{ $material->magazine->magazine_name ?? 'Non spécifié' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Statut:</th>
                                        <td>
                                            @if ($material->is_active)
                                                <span class="badge badge-success">Actif</span>
                                            @else
                                                <span class="badge badge-danger">Inactif</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Créé le:</th>
                                        <td>{{ $material->created_at ? $material->created_at->format('d/m/Y H:i') : 'N/A' }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if ($material->notes)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Notes:</h6>
                                    <div class="alert alert-light">
                                        {{ $material->notes }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stock and Cost Cards -->
            <div class="col-md-6">
                <!-- Stock Card -->
                <div class="card stock-card mb-4">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-boxes me-2"></i>Stock
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h2
                                class="{{ $material->current_stock <= $material->min_stock_level ? 'text-danger' : ($material->current_stock >= $material->max_stock_level ? 'text-warning' : 'text-success') }}">
                                {{ number_format($material->current_stock, 2, ',', '.') }} {{ $material->unit_of_measure }}
                            </h2>
                            <p class="text-muted">Stock Total Disponible</p>
                        </div>

                        <table class="table table-sm">
                            <tr>
                                <td>Stock Minimum:</td>
                                <td class="text-right">
                                    <span
                                        class="badge badge-info">{{ number_format($material->min_stock_level, 2, ',', '.') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Stock Maximum:</td>
                                <td class="text-right">
                                    <span
                                        class="badge badge-info">{{ number_format($material->max_stock_level, 2, ',', '.') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Statut:</td>
                                <td class="text-right">
                                    @if ($material->current_stock <= $material->min_stock_level)
                                        <span class="badge badge-danger">Stock Bas</span>
                                    @elseif($material->current_stock >= $material->max_stock_level)
                                        <span class="badge badge-warning">Stock Élevé</span>
                                    @else
                                        <span class="badge badge-success">Normal</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Cost Card -->
                <div class="card cost-card mb-4">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-money-bill-wave me-2"></i>Coût
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h2 class="text-primary">
                                {{ number_format($averageCost, 2, ',', '.') }} DH
                            </h2>
                            <p class="text-muted">Coût Moyen</p>
                        </div>

                        <table class="table table-sm">
                            <tr>
                                <td>Valeur Totale:</td>
                                <td class="text-right">
                                    <span class="badge badge-success">{{ number_format($totalValue, 2, ',', '.') }} DH</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Dernier achat:</td>
                                <td class="text-right">
                                    @php
                                        $lastPurchase = $stockDetails->first();
                                    @endphp
                                    @if ($lastPurchase)
                                        <span class="badge badge-info">{{ number_format($lastPurchase->unit_price, 2, ',', '.') }}
                                            DH</span>
                                    @else
                                        <span class="badge badge-secondary">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Details by Price -->
        <div class="row">
            <div class="col-12">
                <div class="card detail-card">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-layer-group me-2"></i>Détail du Stock par Prix d'Achat
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($stockDetails->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Prix d'Achat (DH)</th>
                                            <th>Quantité Disponible</th>
                                            <th>Valeur Totale (DH)</th>
                                            <th>Dernière Réception</th>
                                            <th>Pourcentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stockDetails as $detail)
                                            <tr class="stock-detail-row">
                                                <td>
                                                    <strong>{{ number_format($detail->unit_price, 2, ',', '.') }} DH</strong>
                                                </td>
                                                <td>
                                                    {{ number_format($detail->remaining_quantity, 2, ',', '.') }}
                                                    {{ $material->unit_of_measure }}
                                                </td>
                                                <td>
                                                    {{ number_format($detail->remaining_quantity * $detail->unit_price, 2, ',', '.') }}
                                                    DH
                                                </td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($detail->movement_date)->format('d/m/Y') }}
                                                </td>
                                                <td>
                                                    @php
                                                        $percentage = ($detail->remaining_quantity / $totalStock) * 100;
                                                    @endphp
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-info" role="progressbar"
                                                            style="width: {{ $percentage }}%">
                                                            {{ round($percentage, 1) }}%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <th>Total:</th>
                                            <th>{{ number_format($totalStock, 2, ',', '.') }} {{ $material->unit_of_measure }}</th>
                                            <th>{{ number_format($totalValue, 2, ',', '.') }} DH</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Aucun stock disponible. Veuillez créer un achat pour ajouter du stock.
                                <a href="{{ route('raw-material-purchases.create') }}" class="btn btn-sm btn-primary ms-2">
                                    <i class="fas fa-shopping-cart me-1"></i> Créer un achat
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Movements History -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-history me-2"></i>Historique des Mouvements de Stock (10 derniers)
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($material->stockMovements->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Quantité</th>
                                            <th>Stock Avant</th>
                                            <th>Stock Après</th>
                                            <th>Effectué par</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stockMovements as $movement)
                                            <tr>
                                                <td>{{ $movement->movement_date->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    @switch($movement->movement_type)
                                                        @case('purchase')
                                                            <span class="badge badge-success">Achat</span>
                                                        @break

                                                        @case('production_consumption')
                                                            <span class="badge badge-warning">Consommation</span>
                                                        @break

                                                        @case('adjustment')
                                                            <span class="badge badge-info">Ajustement</span>
                                                        @break

                                                        @case('return')
                                                            <span class="badge badge-danger">Retour</span>
                                                        @break

                                                        @default
                                                            <span
                                                                class="badge badge-secondary">{{ $movement->movement_type }}</span>
                                                    @endswitch
                                                </td>
                                                <td>
                                                    @if ($movement->quantity > 0)
                                                        <span
                                                            class="text-success">+{{ number_format($movement->quantity, 2, ',', '.') }}</span>
                                                    @else
                                                        <span
                                                            class="text-danger">{{ number_format($movement->quantity, 2, ',', '.') }}</span>
                                                    @endif
                                                    {{ $material->unit_of_measure }}
                                                </td>
                                                <td>{{ number_format($movement->previous_stock, 2, ',', '.') }}</td>
                                                <td>{{ number_format($movement->new_stock, 2, ',', '.') }}</td>
                                                <td>{{ $movement->performer->username ?? 'Système' }}</td>
                                                <td>{{ $movement->notes }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{-- <div class="text-center mt-3">
                                <a href="{{ route('raw-materials.stock-movements', $material->material_id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-list me-1"></i> Voir tout l'historique
                                </a>
                            </div> --}}
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Aucun mouvement de stock enregistré pour cette matière.
                                <a href="{{ route('raw-material-purchases.create') }}"
                                    class="btn btn-sm btn-primary ms-2">
                                    <i class="fas fa-shopping-cart me-1"></i> Créer un achat
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Buttons -->
    <div class="fixed-bottom" style="bottom: 20px; right: 20px;">
        <div class="btn-group dropup">
            <button type="button" class="btn btn-primary btn-lg rounded-circle shadow-lg" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="fas fa-bolt"></i>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="{{ route('raw-material-purchases.create') }}">
                        <i class="fas fa-shopping-cart text-primary me-2"></i> Créer un achat
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('raw-materials.edit', $material->material_id) }}">
                        <i class="fas fa-edit text-warning me-2"></i> Modifier
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('raw-materials.index') }}">
                        <i class="fas fa-list text-info me-2"></i> Retour à la liste
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Container pour les toasts -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Toast function
            function showToast(type, message) {
                var toast = $('<div class="toast align-items-center text-white bg-' +
                    (type === 'success' ? 'success' : 'danger') +
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
