@extends('layouts.app')

@section('title', 'Détails Produit')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails du Produit</h4>
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
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        {{ $product->product_name }}
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
                            <i class="fas fa-info-circle me-2"></i>Produit : {{ $product->product_name }}
                        </h5>
                        <div>
                            @can('edit_products')
                            <a href="{{ route('products.edit', $product->product_id) }}" class="btn btn-light btn-sm me-2">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            @endcan
                            @if ($product->product_type == 'production')
                                @can('create_production_orders')
                                <a href="{{ route('production-orders.create') }}?product_id={{ $product->product_id }}"
                                    class="btn btn-success btn-sm">
                                    <i class="fas fa-plus me-1"></i> Produire
                                </a>
                                @endcan
                            @endif
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
                                                <th width="40%">Code:</th>
                                                <td><strong>{{ $product->product_code }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Nom:</th>
                                                <td>{{ $product->product_name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Type de Production:</th>
                                                <td>
                                                    @switch($product->product_type)
                                                        @case('production')
                                                            <span class="badge bg-info">Production (Bloc)</span>
                                                        @break

                                                        @case('decoupage')
                                                            <span class="badge bg-warning">Découpage (Sous Bloc)</span>
                                                        @break

                                                        @case('finale')
                                                            <span class="badge bg-success">Produit Final (Volume)</span>
                                                        @break

                                                        @default
                                                            <span class="badge bg-secondary">Non défini</span>
                                                    @endswitch
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Unité de Mesure:</th>
                                                <td>
                                                    @switch($product->product_type)
                                                        @case('production')
                                                            <span class="badge bg-info">bloc</span>
                                                        @break

                                                        @case('decoupage')
                                                            <span class="badge bg-warning">sous bloc</span>
                                                        @break

                                                        @case('finale')
                                                            <span class="badge bg-success">volume</span>
                                                        @break

                                                        @default
                                                            <span
                                                                class="badge bg-secondary">{{ $product->unit_of_measure ?? 'unité' }}</span>
                                                    @endswitch
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Coût de Revient:</th>
                                                <td>{{ $product->cost_price ? number_format($product->cost_price, 2, ',', '.') . ' DH' : 'Non défini' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Statut:</th>
                                                <td>
                                                    @if ($product->is_active)
                                                        <span class="badge bg-success">Actif</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactif</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card info-card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-money-bill-wave me-2"></i>Tarification Globale
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th>Créé le:</th>
                                                <td>{{ $product->created_at ? \Carbon\Carbon::parse($product->created_at)->format('d/m/Y H:i') : 'N/A' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Modifié le:</th>
                                                <td>{{ $product->updated_at ? \Carbon\Carbon::parse($product->updated_at)->format('d/m/Y H:i') : 'N/A' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card stock-card mb-4">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-boxes me-2"></i>Informations Stock
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $unitDisplay = '';
                                            switch ($product->product_type) {
                                                case 'production':
                                                    $unitDisplay = 'bloc';
                                                    break;
                                                case 'decoupage':
                                                    $unitDisplay = 'sous bloc';
                                                    break;
                                                case 'finale':
                                                    $unitDisplay = 'volume';
                                                    break;
                                                default:
                                                    $unitDisplay = $product->unit_of_measure ?? 'unité';
                                            }

                                            $currentStock = $product->total_stock;
                                            $availableStock = $product->total_available_stock;
                                            $reservedStock = $currentStock - $availableStock;
                                            $minStockLevel = $product->min_stock_level ?? 0;
                                            $maxStockLevel = $product->max_stock_level ?? 0;
                                        @endphp

                                        <div class="text-center mb-4">
                                            <h2
                                                class="{{ $availableStock <= $minStockLevel ? 'text-danger' : ($availableStock >= $maxStockLevel ? 'text-warning' : 'text-success') }}">
                                                {{ number_format($availableStock, 2, ',', '.') }}
                                                {{-- {{ $unitDisplay }} --}}
                                                Pièce(s)
                                            </h2>
                                            <p class="text-muted">Stock Disponible</p>
                                        </div>

                                        @if ($product->familles->count() > 0)
                                            <div class="alert alert-info mb-3">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Ce produit utilise le système de familles.
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-info ms-2 btn-view-famille-stock"
                                                    data-product-id="{{ $product->product_id }}"
                                                    data-product-name="{{ $product->product_name }}"
                                                    data-product-code="{{ $product->product_code }}"
                                                    data-unit="{{ $unitDisplay }}">
                                                    <i class="fas fa-eye me-1"></i>Voir détails par famille
                                                </button>
                                            </div>
                                        @endif

                                        <table class="table table-sm">
                                            <tr>
                                                <td>Stock Total:</td>
                                                <td class="text-end">{{ number_format($currentStock, 2, ',', '.') }}
                                                    {{ $unitDisplay }}</td>
                                            </tr>
                                            <tr>
                                                <td>Stock Disponible:</td>
                                                <td class="text-end">{{ number_format($availableStock, 2, ',', '.') }}
                                                    {{ $unitDisplay }}</td>
                                            </tr>
                                            <tr>
                                                <td>Stock Réservé:</td>
                                                <td class="text-end">{{ number_format($reservedStock, 2, ',', '.') }}
                                                    {{ $unitDisplay }}</td>
                                            </tr>
                                            @if ($minStockLevel > 0)
                                                <tr>
                                                    <td>Stock Minimum:</td>
                                                    <td class="text-end">
                                                        <span
                                                            class="badge bg-info">{{ number_format($minStockLevel, 2, ',', '.') }}</span>
                                                    </td>
                                                </tr>
                                            @endif
                                            @if ($maxStockLevel > 0)
                                                <tr>
                                                    <td>Stock Maximum:</td>
                                                    <td class="text-end">
                                                        <span
                                                            class="badge bg-info">{{ number_format($maxStockLevel, 2, ',', '.') }}</span>
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td>Statut Stock:</td>
                                                <td class="text-end">
                                                    @if ($availableStock <= 0)
                                                        <span class="badge bg-danger">Rupture</span>
                                                    @elseif ($minStockLevel > 0 && $availableStock <= $minStockLevel)
                                                        <span class="badge bg-warning">Stock Bas</span>
                                                    @elseif($maxStockLevel > 0 && $availableStock >= $maxStockLevel)
                                                        <span class="badge bg-info">Stock Élevé</span>
                                                    @else
                                                        <span class="badge bg-success">Normal</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>

                                        @if ($minStockLevel > 0 && $availableStock <= $minStockLevel)
                                            <div class="alert alert-danger mt-3">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <strong>Attention!</strong> Le stock est en dessous du niveau minimum.
                                                @if ($product->product_type == 'production')
                                                    <a href="{{ route('production-orders.create') }}?product_id={{ $product->product_id }}"
                                                        class="alert-link">
                                                        Créer un ordre de production
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card info-card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-cube me-2"></i>Caractéristiques Techniques
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            @if ($product->height_m && $product->width_m && $product->depth_m)
                                                <tr>
                                                    <th width="40%">Dimensions (L×l×h):</th>
                                                    <td>{{ number_format($product->width_m, 2, ',', '.') }} ×
                                                        {{ number_format($product->depth_m, 2, ',', '.') }} ×
                                                        {{ number_format($product->height_m, 2, ',', '.') }} m</td>
                                                </tr>
                                            @endif
                                            @if ($product->volume_m3)
                                                <tr>
                                                    <th>Volume:</th>
                                                    <td>{{ number_format($product->volume_m3, 3) }} m³</td>
                                                </tr>
                                            @endif
                                            @if ($product->weight_kg)
                                                <tr>
                                                    <th>Poids:</th>
                                                    <td>{{ number_format($product->weight_kg, 2, ',', '.') }} kg</td>
                                                </tr>
                                            @endif
                                        </table>

                                        @if ($product->description)
                                            <hr>
                                            <h6>Description:</h6>
                                            <p class="text-muted">{{ $product->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Familles Section with Prices -->
                        @if ($product->familles && $product->familles->count() > 0)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-layer-group me-2"></i>Familles Associées - Prix
                                                Spécifiques
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Code Famille</th>
                                                            <th>Nom Famille</th>
                                                            <th class="text-center bg-primary text-white">Prix Client</th>
                                                            <th class="text-center bg-info text-white">Prix Grossiste</th>
                                                            <th class="text-center bg-success text-white">Prix Commercial
                                                            </th>
                                                            <th class="text-center bg-warning text-white">Prix Spécial</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($product->familles as $index => $famille)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td><strong>{{ $famille->famille_code }}</strong></td>
                                                                <td>{{ $famille->famille_name }}</td>
                                                                <td class="text-center text-primary fw-bold">
                                                                    {{ number_format($famille->pivot->prix_client ?? 0, 2, ',', '.') }}
                                                                    DH</td>
                                                                <td class="text-center text-info fw-bold">
                                                                    {{ number_format($famille->pivot->prix_grossiste ?? 0, 2, ',', '.') }}
                                                                    DH</td>
                                                                <td class="text-center text-success fw-bold">
                                                                    {{ number_format($famille->pivot->prix_commercial ?? 0, 2, ',', '.') }}
                                                                    DH</td>
                                                                <td class="text-center text-warning fw-bold">
                                                                    {{ number_format($famille->pivot->prix_special ?? 0, 2, ',', '.') }}
                                                                    DH</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="table-secondary">
                                                        @php
                                                            $totalClient = $product->familles->sum(function ($famille) {
                                                                return $famille->pivot->prix_client ?? 0;
                                                            });
                                                            $totalGrossiste = $product->familles->sum(function (
                                                                $famille,
                                                            ) {
                                                                return $famille->pivot->prix_grossiste ?? 0;
                                                            });
                                                            $totalCommercial = $product->familles->sum(function (
                                                                $famille,
                                                            ) {
                                                                return $famille->pivot->prix_commercial ?? 0;
                                                            });
                                                            $totalSpecial = $product->familles->sum(function (
                                                                $famille,
                                                            ) {
                                                                return $famille->pivot->prix_special ?? 0;
                                                            });
                                                        @endphp
                                                    </tfoot>
                                                </table>
                                            </div>
                                            <div class="alert alert-info mt-3">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Note:</strong> Ces prix sont spécifiques à ce produit et peuvent
                                                différer des prix standards des familles.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Famille Stocks Section -->
                        @if ($product->familleStocks && $product->familleStocks->count() > 0)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-boxes me-2"></i>Stock par Famille
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Famille</th>
                                                            <th>Code</th>
                                                            <th class="text-center">Stock Total</th>
                                                            <th class="text-center text-success">Stock Disponible</th>
                                                            <th class="text-center text-warning">Stock Réservé</th>
                                                            <th class="text-center">Emplacement</th>
                                                            <th class="text-center">Dernier Restock</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($product->familleStocks as $index => $stock)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td><strong>{{ $stock->famille->famille_name ?? $stock->famille_name }}</strong>
                                                                </td>
                                                                <td>{{ $stock->famille->famille_code ?? 'N/A' }}</td>
                                                                <td class="text-center fw-bold">
                                                                    {{ number_format($stock->current_quantity, 2, ',', '.') }}</td>
                                                                <td class="text-center text-success fw-bold">
                                                                    {{ number_format($stock->available_quantity, 2, ',', '.') }}</td>
                                                                <td class="text-center text-warning fw-bold">
                                                                    {{ number_format($stock->reserved_quantity, 2, ',', '.') }}</td>
                                                                <td class="text-center">
                                                                    {{ $stock->location ?? 'Entrepôt Principal' }}</td>
                                                                <td class="text-center">
                                                                    {{ $stock->last_restocked ? \Carbon\Carbon::parse($stock->last_restocked)->format('d/m/Y') : 'Jamais' }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="table-secondary">
                                                        @php
                                                            $totalStock = $product->familleStocks->sum(
                                                                'current_quantity',
                                                            );
                                                            $totalAvailable = $product->familleStocks->sum(
                                                                'available_quantity',
                                                            );
                                                            $totalReserved = $product->familleStocks->sum(
                                                                'reserved_quantity',
                                                            );
                                                        @endphp
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Bill of Materials Section - Only show for production products -->
                        @if ($product->product_type == 'production' && $product->billOfMaterials && $product->billOfMaterials->count() > 0)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-list-alt me-2"></i>Nomenclature (BOM)
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Matière Première</th>
                                                            <th>Code</th>
                                                            <th>Quantité par Bloc</th>
                                                            <th>Unité</th>
                                                            @if ($product->cost_price)
                                                                <th class="text-end">Coût (DH)</th>
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $totalCost = 0;
                                                        @endphp
                                                        @foreach ($product->billOfMaterials as $index => $bom)
                                                            @php
                                                                $itemCost = 0;
                                                                if ($bom->rawMaterial && $bom->rawMaterial->unit_cost) {
                                                                    $itemCost =
                                                                        $bom->quantity_required *
                                                                        $bom->rawMaterial->unit_cost;
                                                                    $totalCost += $itemCost;
                                                                }
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $bom->rawMaterial->material_name ?? 'N/A' }}</td>
                                                                <td>{{ $bom->rawMaterial->material_code ?? 'N/A' }}</td>
                                                                <td>{{ number_format($bom->quantity_required, 4) }}</td>
                                                                <td>{{ $bom->unit_of_measure ?? ($bom->rawMaterial->unit_of_measure ?? 'N/A') }}
                                                                </td>
                                                                @if ($product->cost_price)
                                                                    <td class="text-end">
                                                                        {{ $itemCost > 0 ? number_format($itemCost, 2, ',', '.') : '-' }}
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    @if ($product->cost_price && $totalCost > 0)
                                                        <tfoot>
                                                            <tr class="table-primary">
                                                                <td colspan="{{ $product->cost_price ? '5' : '4' }}"
                                                                    class="text-end"><strong>Coût Total Matières:</strong>
                                                                </td>
                                                                <td class="text-end">
                                                                    <strong>{{ number_format($totalCost, 2, ',', '.') }} DH</strong>
                                                                </td>
                                                            </tr>
                                                            @if ($product->cost_price - $totalCost != 0)
                                                                <tr>
                                                                    <td colspan="{{ $product->cost_price ? '5' : '4' }}"
                                                                        class="text-end">Autres Coûts:</td>
                                                                    <td class="text-end">
                                                                        {{ number_format($product->cost_price - $totalCost, 2, ',', '.') }}
                                                                        DH</td>
                                                                </tr>
                                                            @endif
                                                            <tr class="table-success">
                                                                <td colspan="{{ $product->cost_price ? '5' : '4' }}"
                                                                    class="text-end"><strong>Coût Total Produit:</strong>
                                                                </td>
                                                                <td class="text-end">
                                                                    <strong>{{ number_format($product->cost_price, 2, ',', '.') }}
                                                                        DH</strong>
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    @endif
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Production Orders Section -->
                        @if ($product->productionOrders && $product->productionOrders->count() > 0)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-clipboard-list me-2"></i>Ordres de Production Récents
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>N° Ordre</th>
                                                            <th>Date</th>
                                                            <th>Quantité</th>
                                                            <th>Statut</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($product->productionOrders as $order)
                                                            <tr>
                                                                <td>{{ $order->order_number }}</td>
                                                                <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}
                                                                </td>
                                                                <td>{{ $order->quantity }}</td>
                                                                <td>
                                                                    @switch($order->status)
                                                                        @case('pending')
                                                                            <span class="badge bg-warning">En attente</span>
                                                                        @break

                                                                        @case('in_progress')
                                                                            <span class="badge bg-info">En cours</span>
                                                                        @break

                                                                        @case('completed')
                                                                            <span class="badge bg-success">Terminé</span>
                                                                        @break

                                                                        @case('cancelled')
                                                                            <span class="badge bg-danger">Annulé</span>
                                                                        @break

                                                                        @default
                                                                            <span
                                                                                class="badge bg-secondary">{{ $order->status }}</span>
                                                                    @endswitch
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Stock Movement Timeline -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-history me-2"></i>Historique des Mouvements de Stock
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        @if ($product->stockMovements->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Type</th>
                                                            <th>Famille</th>
                                                            <th>Qté</th>
                                                            <th>Stock Avant</th>
                                                            <th>Stock Après</th>
                                                            <th>Référence</th>
                                                            <th>Effectué par</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($product->stockMovements as $movement)
                                                            <tr>
                                                                <td class="text-nowrap">{{ $movement->movement_date->format('d/m/Y H:i') }}</td>
                                                                <td>
                                                                    @switch($movement->movement_type)
                                                                        @case('initial_stock')
                                                                            <span class="badge bg-secondary">Stock Initial</span>
                                                                            @break
                                                                        @case('manual_addition')
                                                                            <span class="badge bg-success">Ajout Manuel</span>
                                                                            @break
                                                                        @case('manual_removal')
                                                                            <span class="badge bg-danger">Retrait Manuel</span>
                                                                            @break
                                                                        @case('production_output')
                                                                            <span class="badge bg-primary">Production</span>
                                                                            @break
                                                                        @case('production_start')
                                                                            <span class="badge bg-warning text-dark">Conso. Production</span>
                                                                            @break
                                                                        @case('production_out')
                                                                            <span class="badge bg-warning text-dark">Sortie Production</span>
                                                                            @break
                                                                        @case('production_in')
                                                                            <span class="badge bg-success">Entrée Production</span>
                                                                            @break
                                                                        @case('production_adjustment')
                                                                            <span class="badge bg-info">Ajust. Production</span>
                                                                            @break
                                                                        @case('production_consumption')
                                                                            <span class="badge bg-warning text-dark">Conso. Production</span>
                                                                            @break
                                                                        @case('production_consumption_actual')
                                                                            <span class="badge bg-warning text-dark">Conso. Réelle</span>
                                                                            @break
                                                                        @case('production_consumption_adjustment')
                                                                            <span class="badge bg-info">Ajust. Conso.</span>
                                                                            @break
                                                                        @case('production_consumption_reversal')
                                                                            <span class="badge bg-secondary">Annul. Conso.</span>
                                                                            @break
                                                                        @case('sales')
                                                                            <span class="badge bg-danger">Vente</span>
                                                                            @break
                                                                        @case('type2_consumption')
                                                                            <span class="badge bg-warning text-dark">Conso. Découpage</span>
                                                                            @break
                                                                        @case('type3_consumption')
                                                                            <span class="badge bg-warning text-dark">Conso. Conversion</span>
                                                                            @break
                                                                        @case('type4_consumption')
                                                                            <span class="badge bg-warning text-dark">Conso. Transformation</span>
                                                                            @break
                                                                        @case('adjustment')
                                                                            <span class="badge bg-info">Ajustement</span>
                                                                            @break
                                                                        @case('cancellation')
                                                                            <span class="badge bg-dark">Annulation Cde</span>
                                                                            @break
                                                                        @case('cancellation_reversal')
                                                                            <span class="badge bg-secondary">Retour Annulation</span>
                                                                            @break
                                                                        @case('cancellation_output_reversal')
                                                                            <span class="badge bg-secondary">Annul. Sortie</span>
                                                                            @break
                                                                        @case('order_cancel_restore')
                                                                            <span class="badge bg-secondary">Rétablissement</span>
                                                                            @break
                                                                        @case('waste_recovery')
                                                                            <span class="badge bg-success">Récup. Chute</span>
                                                                            @break
                                                                        @case('return')
                                                                            <span class="badge bg-danger">Retour</span>
                                                                            @break
                                                                        @default
                                                                            <span class="badge bg-secondary">{{ $movement->movement_type }}</span>
                                                                    @endswitch
                                                                </td>
                                                                <td>{{ $movement->famille_name ?? '—' }}</td>
                                                                <td class="text-end">
                                                                    @if ($movement->quantity > 0)
                                                                        <span class="text-success fw-bold">+{{ number_format($movement->quantity, 4) }}</span>
                                                                    @else
                                                                        <span class="text-danger fw-bold">{{ number_format($movement->quantity, 4) }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-end">{{ number_format($movement->previous_stock, 4) }}</td>
                                                                <td class="text-end">{{ number_format($movement->new_stock, 4) }}</td>
                                                                <td class="text-nowrap small">{{ $movement->reference_number ?? '—' }}</td>
                                                                <td>{{ $movement->performer->username ?? 'Système' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fs-2 mb-2 d-block"></i>
                                                Aucun mouvement de stock enregistré pour cet article.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
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

    <!-- Famille Stock Details Modal -->
    <div class="modal fade" id="familleStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-boxes me-2"></i>Détail du Stock par Famille
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 id="stockModalProductName" class="fw-bold"></h5>
                            <div class="text-muted">
                                Code: <span id="stockModalProductCode" class="fw-bold"></span>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="alert alert-info mb-0 d-inline-block">
                                <i class="fas fa-cubes me-1"></i>
                                Stock Total: <strong id="stockModalTotalStock">0</strong>
                                <span id="stockModalUnit"></span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Famille</th>
                                    <th>Code</th>
                                    <th class="text-center">Stock Total</th>
                                    <th class="text-center">Stock Disponible</th>
                                    <th class="text-center">Stock Réservé</th>
                                    <th class="text-center">Emplacement</th>
                                    <th class="text-center">Dernier Restock</th>
                                </tr>
                            </thead>
                            <tbody id="familleStockTableBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Chargement...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('stylesheets')
    <style>
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 0;
        }

        .info-card .table th {
            font-weight: 600;
            color: #495057;
        }

        .stock-card .table td {
            padding: 0.5rem;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .btn-view-famille-stock {
            padding: 0.15rem 0.5rem;
            font-size: 0.875rem;
        }

        .bg-primary.text-white,
        .bg-info.text-white,
        .bg-success.text-white,
        .bg-warning.text-white {
            font-weight: 600;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Handle famille stock view button click
            $(document).on('click', '.btn-view-famille-stock', function() {
                var productId = $(this).data('product-id');
                var productName = $(this).data('product-name');
                var productCode = $(this).data('product-code');
                var unit = $(this).data('unit');

                // Set modal info
                $('#stockModalProductName').text(productName);
                $('#stockModalProductCode').text(productCode);
                $('#stockModalUnit').text(unit);
                $('#familleStockTableBody').html(
                    '<tr><td colspan="7" class="text-center text-muted">Chargement...</td></tr>');

                // Load famille stock details
                $.ajax({
                    url: "{{ route('products.famille-stock', ':id') }}".replace(':id', productId),
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#stockModalTotalStock').text(response.product.total_stock
                                .toFixed(2));

                            var tableBody = $('#familleStockTableBody');
                            tableBody.empty();

                            if (response.famille_stocks && response.famille_stocks.length > 0) {
                                $.each(response.famille_stocks, function(index, famille) {
                                    var row = '<tr>' +
                                        '<td class="fw-bold">' + (famille
                                            .famille_name || 'N/A') + '</td>' +
                                        '<td>' + (famille.famille_code || 'N/A') +
                                        '</td>' +
                                        '<td class="text-center fw-bold">' + parseFloat(
                                            famille.current_quantity || 0).toFixed(2) +
                                        '</td>' +
                                        '<td class="text-center text-success">' +
                                        parseFloat(famille.available_quantity || 0)
                                        .toFixed(2) + '</td>' +
                                        '<td class="text-center text-warning">' +
                                        parseFloat(famille.reserved_quantity || 0)
                                        .toFixed(2) + '</td>' +
                                        '<td class="text-center">' + (famille
                                            .location || 'Entrepôt Principal') +
                                        '</td>' +
                                        '<td class="text-center">' + (famille
                                            .last_restocked || 'Jamais') + '</td>' +
                                        '</tr>';
                                    tableBody.append(row);
                                });
                            } else {
                                tableBody.html(
                                    '<tr><td colspan="7" class="text-center text-muted">Aucun stock par famille</td></tr>'
                                );
                            }
                        } else {
                            $('#familleStockTableBody').html(
                                '<tr><td colspan="7" class="text-center text-danger">' +
                                response.message + '</td></tr>');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading famille stock:', xhr);
                        $('#familleStockTableBody').html(
                            '<tr><td colspan="7" class="text-center text-danger">Erreur lors du chargement des données</td></tr>'
                        );
                        showToast('error', 'Erreur lors du chargement du stock par famille');
                    }
                });

                // Show modal
                var modal = new bootstrap.Modal(document.getElementById('familleStockModal'));
                modal.show();
            });

            // Toast notification function
            function showToast(type, message) {
                // Create toast container if it doesn't exist
                if ($('#toast-container').length === 0) {
                    $('body').append(
                        '<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>'
                    );
                }

                var toastId = 'toast-' + Date.now();
                var bgColor = type === 'success' ? 'bg-success' : (type === 'warning' ? 'bg-warning' : 'bg-danger');

                var toast = $('<div id="' + toastId + '" class="toast align-items-center text-white ' + bgColor +
                    ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                    '<div class="d-flex">' +
                    '<div class="toast-body">' + message + '</div>' +
                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
                    '</div>' +
                    '</div>');

                $('#toast-container').append(toast);

                var bsToast = new bootstrap.Toast(toast[0], {
                    autohide: true,
                    delay: 5000
                });

                bsToast.show();

                // Remove toast after it's hidden
                toast.on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }
        });
    </script>
@endpush
