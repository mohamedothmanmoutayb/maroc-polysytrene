@extends('layouts.app')

@section('title', 'Situation — ' . $supplier->display_name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">{{ $supplier->display_name }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Téléphone:</strong><br>
                            {{ $supplier->phone ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Email:</strong><br>
                            {{ $supplier->email ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>ICE / RC:</strong><br>
                            {{ $supplier->ice ?? 'N/A' }} / {{ $supplier->rc ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Solde Actuel:</strong><br>
                            {!! $supplier->balance_formatted !!}
                            {!! $supplier->balance_badge !!}
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <strong>Adresse:</strong><br>
                            {{ $supplier->address ?? 'Non renseignée' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#purchases" data-bs-toggle="tab">Achats</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#history" data-bs-toggle="tab">Historique Solde</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#summary" data-bs-toggle="tab">Résumé</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Purchases Tab -->
                        <div class="tab-pane active" id="purchases">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>N° Achat</th>
                                            <th>Montant</th>
                                            <th>Payé</th>
                                            <th>Reste</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchases as $purchase)
                                            <tr>
                                                <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                                                <td>{{ $purchase->purchase_number }}</td>
                                                <td>{{ number_format($purchase->final_amount, 2, ',', '.') }} DH</td>
                                                <td class="text-success">{{ number_format($purchase->total_paid, 2, ',', '.') }}
                                                    DH</td>
                                                <td
                                                    class="{{ $purchase->final_amount - $purchase->total_paid > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($purchase->final_amount - $purchase->total_paid, 2, ',', '.') }}
                                                    DH
                                                </td>
                                                <td>{!! $purchase->payment_status_label !!}</td>
                                                <td>
                                                    <a href="{{ route('raw-material-purchases.show', $purchase->purchase_id) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $purchases->links() }}
                            </div>
                        </div>

                        <!-- Balance History Tab -->
                        <div class="tab-pane" id="history">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>Montant</th>
                                            <th>Solde Avant</th>
                                            <th>Solde Après</th>
                                            <th>Par</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($supplier->balanceHistory as $history)
                                            <tr>
                                                <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $history->type_label }}</td>
                                                <td>{{ $history->description }}</td>
                                                <td class="{{ $history->amount_class }}">
                                                    {{ $history->amount_formatted }}</td>
                                                <td>{{ number_format($history->previous_balance, 2, ',', '.') }} DH</td>
                                                <td>{{ number_format($history->new_balance, 2, ',', '.') }} DH</td>
                                                <td>{{ $history->creator->name ?? 'Système' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Aucun historique</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Summary Tab -->
                        <div class="tab-pane" id="summary">
                            @php
                                $summary = $supplier->purchase_summary;
                            @endphp
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Résumé des Achats</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td>Total Achats:</td>
                                                    <td class="text-end fw-bold">
                                                        {{ number_format($summary['total'], 2, ',', '.') }} DH</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Payé:</td>
                                                    <td class="text-end text-success fw-bold">
                                                        {{ number_format($summary['paid'], 2, ',', '.') }} DH</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Impayé:</td>
                                                    <td class="text-end text-danger fw-bold">
                                                        {{ number_format($summary['unpaid'], 2, ',', '.') }} DH</td>
                                                </tr>
                                                <tr>
                                                    <td>Solde Actuel:</td>
                                                    <td
                                                        class="text-end fw-bold {{ $summary['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                                        {{ number_format($summary['balance'], 2, ',', '.') }} DH
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Statistiques</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td>Nombre d'achats:</td>
                                                    <td class="text-end fw-bold">{{ $summary['purchases_count'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Dernier achat:</td>
                                                    <td class="text-end">{{ $supplier->last_purchase_date }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Valeur moyenne:</td>
                                                    <td class="text-end">
                                                        {{ number_format($supplier->average_purchase_value, 2, ',', '.') }} DH
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Progression paiement:</td>
                                                    <td class="text-end">
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar bg-{{ $summary['progress_class'] }}"
                                                                style="width: {{ $summary['progress'] }}%">
                                                                {{ $summary['progress'] }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($paymentMethods->count() > 0)
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Répartition des paiements</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Méthode</th>
                                                                <th class="text-end">Nombre</th>
                                                                <th class="text-end">Montant</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($paymentMethods as $method)
                                                                <tr>
                                                                    <td>
                                                                        @switch($method->payment_method)
                                                                            @case('cash')
                                                                                <i
                                                                                    class="fas fa-money-bill-wave text-success me-2"></i>Espèces
                                                                            @break

                                                                            @case('check')
                                                                                <i
                                                                                    class="fas fa-money-check-alt text-primary me-2"></i>Chèque
                                                                            @break

                                                                            @case('bank_transfer')
                                                                                <i
                                                                                    class="fas fa-university text-info me-2"></i>Virement
                                                                            @break

                                                                            @case('traite')
                                                                                <i
                                                                                    class="fas fa-file-invoice text-warning me-2"></i>Traite
                                                                            @break

                                                                            @default
                                                                                <i
                                                                                    class="fas fa-circle text-secondary me-2"></i>{{ ucfirst($method->payment_method) }}
                                                                        @endswitch
                                                                    </td>
                                                                    <td class="text-end">{{ $method->count }}</td>
                                                                    <td class="text-end fw-bold">
                                                                        {{ number_format($method->total, 2, ',', '.') }} DH</td>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('stylesheets')
    <style>
        .card-header-tabs .nav-link.active {
  background-color: #141313;
        }
    </style>
@endpush
