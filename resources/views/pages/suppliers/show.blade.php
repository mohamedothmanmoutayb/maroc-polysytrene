@extends('layouts.app')

@section('title', 'Détails Fournisseur')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails du Fournisseur</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('suppliers.index') }}">
                                        Fournisseurs
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
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-info-circle me-2"></i>Informations du Fournisseur
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Type:</label>
                                    @if ($supplier->supplier_type == 'morale')
                                        <span class="badge badge-primary">Morale (Entreprise)</span>
                                    @else
                                        <span class="badge badge-info">Physique (Individuel)</span>
                                    @endif
                                </div>

                                @if ($supplier->supplier_type == 'morale')
                                    <div class="mb-3">
                                        <label class="form-label">Nom de l'Entreprise:</label>
                                        <p class="form-control-static">{{ $supplier->company_name }}</p>
                                    </div>

                                    @if ($supplier->ice)
                                        <div class="mb-3">
                                            <label class="form-label">ICE:</label>
                                            <p class="form-control-static">{{ $supplier->ice }}</p>
                                        </div>
                                    @endif

                                    @if ($supplier->rc)
                                        <div class="mb-3">
                                            <label class="form-label">RC:</label>
                                            <p class="form-control-static">{{ $supplier->rc }}</p>
                                        </div>
                                    @endif

                                    @if ($supplier->patente)
                                        <div class="mb-3">
                                            <label class="form-label">Patente:</label>
                                            <p class="form-control-static">{{ $supplier->patente }}</p>
                                        </div>
                                    @endif

                                    @if ($supplier->representative_name)
                                        <div class="mb-3">
                                            <label class="form-label">Représentant:</label>
                                            <p class="form-control-static">{{ $supplier->representative_name }}</p>
                                        </div>
                                    @endif
                                @else
                                    <div class="mb-3">
                                        <label class="form-label">Nom Complet:</label>
                                        <p class="form-control-static">{{ $supplier->full_name }}</p>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Téléphone:</label>
                                    <p class="form-control-static">{{ $supplier->phone }}</p>
                                </div>

                                @if ($supplier->email)
                                    <div class="mb-3">
                                        <label class="form-label">Email:</label>
                                        <p class="form-control-static">{{ $supplier->email }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                @if ($supplier->address)
                                    <div class="mb-3">
                                        <label class="form-label">Adresse:</label>
                                        <p class="form-control-static">{{ $supplier->address }}</p>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Statut:</label>
                                    @if ($supplier->is_active)
                                        <span class="badge badge-success">Actif</span>
                                    @else
                                        <span class="badge badge-danger">Inactif</span>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Date de création:</label>
                                    <p class="form-control-static">{{ $supplier->created_at->format('d/m/Y H:i') }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Dernière mise à jour:</label>
                                    <p class="form-control-static">{{ $supplier->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-chart-line me-2"></i>Statistiques
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div class="mb-4">
                                <i class="fas fa-truck fa-3x text-primary mb-3"></i>
                                <h4>{{ $supplier->company_name ?: $supplier->full_name }}</h4>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span>Total Achats:</span>
                            <strong>{{ $supplier->rawMaterialPurchases->count() }}</strong>
                        </div>

                        @php
                            $totalAmount = $supplier->rawMaterialPurchases->sum('total_amount');
                        @endphp

                        <div class="d-flex justify-content-between mb-3">
                            <span>Montant Total:</span>
                            <strong>{{ number_format($totalAmount, 2, ',', '.') }} DH</strong>
                        </div>

                        <hr>

                        <div class="text-center mt-3">
                            @can('edit_suppliers')
                            <a href="{{ route('suppliers.edit', $supplier->supplier_id) }}" class="btn btn-primary me-2">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            @endcan
                            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($supplier->rawMaterialPurchases->count() > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-shopping-cart me-2"></i>Historique des Achats
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <th>Date</th>
                                            <th>Matériel</th>
                                            <th>Quantité</th>
                                            <th>Prix Unitaire</th>
                                            <th>Total</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($supplier->rawMaterialPurchases as $purchase)
                                            <tr>
                                                <td>{{ $purchase->purchase_reference }}</td>
                                                <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                                                <td>{{ $purchase->rawMaterial->name ?? 'N/A' }}</td>
                                                <td>{{ number_format($purchase->quantity, 2, ',', '.') }}</td>
                                                <td>{{ number_format($purchase->unit_price, 2, ',', '.') }} DH</td>
                                                <td>{{ number_format($purchase->total_amount, 2, ',', '.') }} DH</td>
                                                <td>
                                                    @if ($purchase->status == 'completed')
                                                        <span class="badge badge-success">Terminé</span>
                                                    @elseif($purchase->status == 'pending')
                                                        <span class="badge badge-warning">En attente</span>
                                                    @else
                                                        <span class="badge badge-danger">Annulé</span>
                                                    @endif
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
    </div>
@endsection
