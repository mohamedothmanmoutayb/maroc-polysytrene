@extends('layouts.app')

@section('title', 'Détails de la Famille')

@section('content')
    <div class="container-fluid">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails de la Famille</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('familles.index') }}">
                                        Familles
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        {{ $famille->famille_name }}
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Famille Details -->
            <div class="col-lg-8">
                <div class="row">
                    <!-- Famille Information Card -->
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header card-header-custom">
                                <h5 class="card-title mb-0" style="color:white">
                                    <i class="fas fa-info-circle me-2"></i>Informations de la Famille
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="form-label text-muted">Code Famille</label>
                                            <div class="info-value">{{ $famille->famille_code }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="form-label text-muted">Nom de la Famille</label>
                                            <div class="info-value">{{ $famille->famille_name }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="form-label text-muted">Statut</label>
                                            <div class="info-value">
                                                @if ($famille->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Price Information Section -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h6 class="mb-3" style="color: #667eea;">
                                            <i class="fas fa-tags me-2"></i>Tarification
                                        </h6>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-item">
                                            <label class="form-label text-muted">Prix Client</label>
                                            <div class="info-value h5">{{ number_format($famille->prix_client, 2, ',', '.') }} DH
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-item">
                                            <label class="form-label text-muted">Prix Grossiste</label>
                                            <div class="info-value h5">{{ number_format($famille->prix_grossiste, 2, ',', '.') }} DH
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-item">
                                            <label class="form-label text-muted">Prix Commercial</label>
                                            <div class="info-value h5">{{ number_format($famille->prix_commercial, 2, ',', '.') }} DH
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-item">
                                            <label class="form-label text-muted">Prix Spécial</label>
                                            <div class="info-value h5">{{ number_format($famille->prix_special, 2, ',', '.') }} DH
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-item">
                                            <label class="form-label text-muted">Prix Revient</label>
                                            <div class="info-value h5">{{ number_format($famille->prix_revient, 2, ',', '.') }} DH
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($famille->description)
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="info-item">
                                                <label class="form-label text-muted">Description</label>
                                                <div class="info-value">{{ $famille->description }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="form-label text-muted">Date de Création</label>
                                            <div class="info-value">{{ $famille->created_at->format('d/m/Y H:i') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="form-label text-muted">Dernière Mise à Jour</label>
                                            <div class="info-value">{{ $famille->updated_at->format('d/m/Y H:i') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Associated Products Card -->
                    <div class="col-md-12 mt-4">
                        <div class="card">
                            <div class="card-header card-header-custom">
                                <h5 class="card-title mb-0" style="color:white">
                                    <i class="fas fa-cubes me-2"></i>Produits Associés
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="products-table" class="table table-bordered table-hover w-100">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="4%">#</th>
                                                <th>Produit</th>
                                                <th>Code</th>
                                                <th class="text-end">Prix Client</th>
                                                <th class="text-end">Prix Grossiste</th>
                                                <th class="text-end">Prix Commercial</th>
                                                <th class="text-end">Prix Spécial</th>
                                                <th class="text-end">Stock</th>
                                                <th width="10%" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Stock Movements Card -->
                    <div class="col-md-12 mt-4">
                        <div class="card">
                            <div class="card-header card-header-custom">
                                <h5 class="card-title mb-0" style="color:white">
                                    <i class="fas fa-history me-2"></i>Mouvements de Stock Récents
                                </h5>
                            </div>
                            <div class="card-body">
                                @if ($famille->stockMovements->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Produit</th>
                                                    <th>Quantité</th>
                                                    <th>Ancien</th>
                                                    <th>Nouveau</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($famille->stockMovements->take(10) as $movement)
                                                    <tr>
                                                        <td>{{ $movement->movement_date->format('d/m/Y H:i') }}</td>
                                                        <td>
                                                            @php
                                                                $typeLabels = [
                                                                    'initial_stock' => 'Stock Initial',
                                                                    'manual_addition' => 'Ajout Manuel',
                                                                    'manual_removal' => 'Retrait Manuel',
                                                                    'production_output' => 'Sortie Production',
                                                                    'sales' => 'Vente',
                                                                    'adjustment' => 'Ajustement',
                                                                ];
                                                            @endphp
                                                            <span
                                                                class="badge bg-{{ $movement->quantity >= 0 ? 'success' : 'danger' }}">
                                                                {{ $typeLabels[$movement->movement_type] ?? $movement->movement_type }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if ($movement->product)
                                                                {{ $movement->product->product_name }}
                                                            @else
                                                                N/A
                                                            @endif
                                                        </td>
                                                        <td
                                                            class="{{ $movement->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ $movement->quantity >= 0 ? '+' : '' }}{{ number_format($movement->quantity, 2, ',', '.') }}
                                                        </td>
                                                        <td>{{ number_format($movement->previous_stock, 2, ',', '.') }}</td>
                                                        <td>{{ number_format($movement->new_stock, 2, ',', '.') }}</td>
                                                        <td>{{ $movement->notes }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if ($famille->stockMovements->count() > 10)
                                        <div class="text-center mt-3">
                                            <a href="{{ route('product-stock-movements.index', ['famille_id' => $famille->famille_id]) }}"
                                                class="btn btn-sm btn-primary">
                                                Voir tous les mouvements
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Aucun mouvement de stock pour cette famille.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Stock Summary and Actions -->
            <div class="col-lg-4">
                <!-- Stock Summary Card -->
                <div class="card">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-boxes me-2"></i>Résumé du Stock
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $totalStock = $famille->stocks->sum('current_quantity');
                            $totalAvailable = $famille->stocks->sum('available_quantity');
                            $totalReserved = $famille->stocks->sum('reserved_quantity');
                        @endphp

                        <div class="info-item mb-4">
                            <label class="form-label text-muted">Stock Total</label>
                            <div class="info-value h4">
                                {{ number_format($totalStock, 2, ',', '.') }}
                                <small class="text-muted">unités</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-muted">Disponible</label>
                                    <div class="info-value h5 text-success">
                                        {{ number_format($totalAvailable, 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-muted">Réservé</label>
                                    <div class="info-value h5 text-warning">
                                        {{ number_format($totalReserved, 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($famille->stocks->count() > 0)
                            <hr>
                            <h6 class="mb-3">Stock par Produit</h6>
                            @foreach ($famille->stocks as $stock)
                                <div class="stock-item mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="small">{{ $stock->product->product_name ?? 'N/A' }}</span>
                                        <span class="small">
                                            <strong>{{ number_format($stock->current_quantity, 2, ',', '.') }}</strong>
                                            <small class="text-muted">(dispo:
                                                {{ number_format($stock->available_quantity, 2, ',', '.') }})</small>
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 5px;">
                                        @php
                                            $percentage =
                                                $stock->current_quantity > 0
                                                    ? min(100, ($stock->current_quantity / 100) * 100)
                                                    : 0;
                                        @endphp
                                        <div class="progress-bar bg-{{ $percentage > 50 ? 'success' : ($percentage > 20 ? 'warning' : 'danger') }}"
                                            role="progressbar" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Quick Stats Card -->
                <div class="card mt-4">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-chart-bar me-2"></i>Statistiques Rapides
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="stat-item">
                                    <div class="stat-value h3">{{ $famille->products()->count() }}</div>
                                    <div class="stat-label small text-muted">Produits Associés</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item">
                                    <div class="stat-value h3">{{ $famille->outputs->count() }}</div>
                                    <div class="stat-label small text-muted">Sorties Production</div>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center mt-3">
                            <div class="col-6">
                                <div class="stat-item">
                                    <div class="stat-value h3">{{ $famille->stockMovements->count() }}</div>
                                    <div class="stat-label small text-muted">Mouvements Stock</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item">
                                    <div class="stat-value h3">{{ $famille->stocks->count() }}</div>
                                    <div class="stat-label small text-muted">Stocks Par Produit</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('stylesheets')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    @endpush

    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
        <script>
        $(document).ready(function () {
            $('#products-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('familles.products-data', $famille->famille_id) }}",
                    type: 'GET',
                },
                columns: [
                    { data: 'DT_RowIndex',     name: 'DT_RowIndex',      orderable: false, searchable: false, className: 'text-center' },
                    { data: 'product_name',    name: 'p.product_name' },
                    { data: 'product_code',    name: 'p.product_code' },
                    { data: 'prix_client',     name: 'pf.prix_client',     className: 'text-end' },
                    { data: 'prix_grossiste',  name: 'pf.prix_grossiste',  className: 'text-end' },
                    { data: 'prix_commercial', name: 'pf.prix_commercial', className: 'text-end' },
                    { data: 'prix_special',    name: 'pf.prix_special',    className: 'text-end' },
                    { data: 'current_quantity',name: 'pfs.current_quantity',className: 'text-end', orderable: false },
                    { data: 'action',          name: 'action',             orderable: false, searchable: false, className: 'text-center' },
                ],
                language: { url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json" },
                order: [[1, 'asc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            });
        });
        </script>
    @endpush

    <style>
        .info-item {
            margin-bottom: 1rem;
        }

        .info-value {
            font-weight: 500;
            font-size: 1.1rem;
        }

        .stat-item {
            padding: 1rem;
            border-radius: 0.5rem;
            background-color: #f8f9fa;
        }

        .stat-value {
            color: #0d6efd;
            font-weight: 600;
        }

        .stock-item {
            padding: 0.5rem;
            border-radius: 0.25rem;
            background-color: #f8f9fa;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 0;
        }
    </style>
@endsection
