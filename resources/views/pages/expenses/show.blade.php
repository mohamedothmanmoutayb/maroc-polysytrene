@extends('layouts.app')

@section('title', 'Détails Dépense')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails Dépense</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('expenses.index') }}">
                                        Dépenses
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
                            <i class="fas fa-file-invoice me-2"></i>{{ $expense->expense_number }}
                        </h5>
                        <div>
                            @can('create_expenses')
                                <a href="{{ route('expenses.create') }}"
                                    class="btn btn-warning btn-sm me-2">
                                    <i class="fas fa-plus me-1"></i> Ajouter dépense
                                </a>
                            @endcan
                            @if (!$expense->approved_by)
                                @can('edit_expenses')
                                    <a href="{{ route('expenses.edit', $expense->expense_id) }}"
                                        class="btn btn-light btn-sm me-2">
                                        <i class="fas fa-edit me-1"></i> Modifier
                                    </a>
                                @endcan
                                @can('approve_expenses')
                                    <form action="{{ route('expenses.approve', $expense->expense_id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm me-2"
                                            onclick="return confirm('Approuver cette dépense ?')">
                                            <i class="fas fa-check-circle me-1"></i> Approuver
                                        </button>
                                    </form>
                                @endcan
                            @endif
                            <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Informations Générales</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="40%">Numéro Dépense</th>
                                                <td><strong>{{ $expense->expense_number }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Date Dépense</th>
                                                <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Catégorie</th>
                                                <td>
                                                    <span
                                                        class="badge bg-info">{{ $expense->category->category_name }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Montant</th>
                                                <td class="text-danger fw-bold fs-5">
                                                    {{ number_format($expense->amount, 2, ',', '.') }} DH</td>
                                            </tr>
                                            <tr>
                                                <th>Mode de Paiement</th>
                                                <td>
                                                    @php
                                                        $paymentLabels = [
                                                            'cash' => 'Espèces',
                                                            'check' => 'Chèque',
                                                            'traite' => 'Traite',
                                                            'bank_transfer' => 'Virement',
                                                            'credit_card' => 'Carte',
                                                        ];
                                                    @endphp
                                                    {{ $paymentLabels[$expense->payment_method] ?? $expense->payment_method }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Bénéficiaire</th>
                                                <td>{{ $expense->paid_to ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>N° Reçu/Facture</th>
                                                <td>{{ $expense->receipt_number ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Statut</th>
                                                <td>
                                                    @if ($expense->approved_by)
                                                        <span class="badge bg-success">Approuvée</span>
                                                    @else
                                                        <span class="badge bg-warning">En attente</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Description</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">{{ $expense->description ?? 'Aucune description' }}</p>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Notes</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">{{ $expense->notes ?? 'Aucune note' }}</p>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Informations d'Audit</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Enregistré par:</th>
                                                <td>{{ $expense->recorder->username ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Date d'enregistrement:</th>
                                                <td>{{ $expense->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                            @if ($expense->approved_by)
                                                <tr>
                                                    <th>Approuvé par:</th>
                                                    <td>{{ $expense->approver->username ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Date d'approbation:</th>
                                                    <td>{{ $expense->updated_at->format('d/m/Y H:i') }}</td>
                                                </tr>
                                            @endif
                                            @if ($expense->updated_at != $expense->created_at)
                                                <tr>
                                                    <th>Dernière modification:</th>
                                                    <td>{{ $expense->updated_at->format('d/m/Y H:i') }}</td>
                                                </tr>
                                            @endif
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
@endsection
