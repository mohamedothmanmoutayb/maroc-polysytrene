@extends('layouts.app')

@section('title', 'Détails Chèque')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails du Chèque</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('checks.index') }}">
                                        Chèques
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
                            <i class="fas fa-money-check me-2"></i>Chèque: {{ $check->check_number }}
                        </h5>
                        <div>
                            @if ($check->status == 'pending' && $check->available_amount > 0)
                                <a href="{{ route('checks.allocate', $check->check_id) }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-hand-holding-usd me-1"></i> Allouer
                                </a>
                            @endif
                            <a href="{{ route('checks.edit', $check->check_id) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            <a href="{{ route('checks.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column - Check Information -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Informations du Chèque</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="40%">Numéro Chèque</th>
                                                <td>{{ $check->check_number }}</td>
                                            </tr>
                                            <tr>
                                                <th>Type</th>
                                                <td>
                                                    @php
                                                        $badges = [
                                                            'client' => 'primary',
                                                            'personal' => 'info',
                                                        ];
                                                        $labels = [
                                                            'client' => 'Client',
                                                            'personal' => 'Personnel',
                                                        ];
                                                        $color = $badges[$check->check_type] ?? 'secondary';
                                                        $label = $labels[$check->check_type] ?? $check->check_type;
                                                    @endphp
                                                    <span class="badge badge-{{ $color }}">{{ $label }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Montant</th>
                                                <td class="text-success">
                                                    <strong>{{ number_format($check->amount, 2, ',', '.') }} DH</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Montant Disponible</th>
                                                <td>
                                                    <span
                                                        class="badge {{ $check->available_amount > 0 ? 'bg-success' : 'bg-warning' }}">
                                                        {{ number_format($check->available_amount, 2, ',', '.') }} DH
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Banque</th>
                                                <td>{{ $check->bank_name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Tireur</th>
                                                <td>{{ $check->account_holder }}</td>
                                            </tr>
                                            <tr>
                                                <th>Date d'Émission</th>
                                                <td>{{ $check->issue_date->format('d/m/Y') }}</td>
                                            </tr>
                                            @if ($check->deposit_date)
                                                <tr>
                                                    <th>Date d'échéance</th>
                                                    <td>{{ $check->deposit_date->format('d/m/Y') }}</td>
                                                </tr>
                                            @endif
                                            @if ($check->clearing_date)
                                                <tr>
                                                    <th>Date d'Encaissement</th>
                                                    <td>{{ $check->clearing_date->format('d/m/Y') }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <th>Statut</th>
                                                <td>
                                                    @php
                                                        $badges = [
                                                            'pending' => 'warning',
                                                            'deposited' => 'info',
                                                            'cleared' => 'success',
                                                            'bounced' => 'danger',
                                                            'cancelled' => 'danger',
                                                        ];
                                                        $labels = [
                                                            'pending' => 'En attente',
                                                            'deposited' => 'Déposé',
                                                            'cleared' => 'Encaissé',
                                                            'bounced' => 'Rebondi',
                                                            'cancelled' => 'Annulé',
                                                        ];
                                                        $color = $badges[$check->status] ?? 'secondary';
                                                        $label = $labels[$check->status] ?? $check->status;
                                                    @endphp
                                                    <span
                                                        class="badge badge-{{ $color }}">{{ $label }}</span>
                                                </td>
                                            </tr>
                                            @if ($check->check_image)
                                                <tr>
                                                    <th>Image du Chèque</th>
                                                    <td>
                                                        <img src="{{ asset('storage/' . $check->check_image) }}"
                                                            alt="Image du chèque {{ $check->check_number }}"
                                                            class="img-thumbnail"
                                                            style="max-height: 150px; max-width: 200px; cursor: pointer;"
                                                            data-bs-toggle="modal" data-bs-target="#checkImageModal">
                                                        <br>
                                                        <small class="text-muted">Cliquez pour agrandir</small>
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <th>Statut Actif</th>
                                                <td>
                                                    @if ($check->is_active)
                                                        <span class="badge bg-success">Actif</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactif</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Enregistré par</th>
                                                <td>{{ $check->creator->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Date Enregistrement</th>
                                                <td>{{ $check->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Dernière Mise à Jour</th>
                                                <td>{{ $check->updated_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <!-- Notes Card -->
                                @if ($check->notes)
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Notes</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-0">{{ $check->notes }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Right Column - Allocations -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Allocations</h6>
                                        @if ($check->status == 'pending' && $check->available_amount > 0)
                                            <a href="{{ route('checks.allocate', $check->check_id) }}"
                                                class="btn btn-sm btn-success">
                                                <i class="fas fa-plus me-1"></i> Nouvelle Allocation
                                            </a>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        @if ($check->allocations->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Achat</th>
                                                            <th>Montant</th>
                                                            <th>Fournisseur</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($check->allocations as $allocation)
                                                            <tr>
                                                                <td>{{ $allocation->created_at->format('d/m/Y') }}</td>
                                                                <td>
                                                                    @if ($allocation->purchase)
                                                                        <a href="{{ route('raw-material-purchases.show', $allocation->purchase_id) }}"
                                                                            class="text-decoration-none">
                                                                            {{ $allocation->purchase->purchase_number }}
                                                                        </a>
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($allocation->allocated_amount, 2, ',', '.') }}
                                                                    DH</td>
                                                                <td>
                                                                    @if ($allocation->purchase && $allocation->purchase->supplier)
                                                                        {{ $allocation->purchase->supplier->display_name }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="table-success">
                                                            <td colspan="2"><strong>Total Alloué</strong></td>
                                                            <td class="text-end">
                                                                <strong>{{ number_format($check->amount - $check->available_amount, 2, ',', '.') }}
                                                                    DH</strong>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Aucune allocation pour ce chèque</p>
                                                @if ($check->status == 'pending' && $check->available_amount > 0)
                                                    <a href="{{ route('checks.allocate', $check->check_id) }}"
                                                        class="btn btn-success">
                                                        <i class="fas fa-hand-holding-usd me-1"></i> Allouer ce chèque
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Summary Card -->
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Résumé</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <td><strong>Montant Chèque</strong></td>
                                                <td class="text-end">{{ number_format($check->amount, 2, ',', '.') }} DH</td>
                                            </tr>
                                            <tr class="table-success">
                                                <td><strong>Montant Alloué</strong></td>
                                                <td class="text-end">
                                                    {{ number_format($check->amount - $check->available_amount, 2, ',', '.') }} DH
                                                </td>
                                            </tr>
                                            <tr class="table-{{ $check->available_amount > 0 ? 'info' : 'success' }}">
                                                <td><strong>Montant Disponible</strong></td>
                                                <td class="text-end">
                                                    <strong>{{ number_format($check->available_amount, 2, ',', '.') }} DH</strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Check Image Modal -->
    @if ($check->check_image)
        <div class="modal fade" id="checkImageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Image du Chèque - {{ $check->check_number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="{{ asset('storage/' . $check->check_image) }}"
                            alt="Image du chèque {{ $check->check_number }}" class="img-fluid">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <a href="{{ asset('storage/' . $check->check_image) }}"
                            download="cheque-{{ $check->check_number }}.jpg" class="btn btn-primary">
                            <i class="fas fa-download me-1"></i> Télécharger
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .img-thumbnail:hover {
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }
    </style>
@endpush
