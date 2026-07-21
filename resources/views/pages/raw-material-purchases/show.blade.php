@extends('layouts.app')

@section('title', 'Détails Achat')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails de la Commande d'Achat</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('raw-material-purchases.index') }}">
                                        Achats
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
                            <i class="fas fa-info-circle me-2"></i>Commande : {{ $purchase->purchase_number }}
                        </h5>
                        <div>
                            <a href="{{ route('raw-material-purchases.pdf', $purchase->purchase_id) }}"
                                class="btn btn-light btn-sm me-2" target="_blank">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </a>
                            <a href="{{ route('raw-material-purchases.edit', $purchase->purchase_id) }}"
                                class="btn btn-light btn-sm me-2">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            @if (!$purchase->actual_delivery_date)
                                <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#receiptModal">
                                    <i class="fas fa-truck me-1"></i> Réception
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="text-white">Total TTC</h6>
                                        <h3 class="text-white">{{ number_format($purchase->final_amount, 2, ',', '.') }} DH</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="text-white">Total Payé</h6>
                                        <h3 class="text-white">{{ number_format($purchase->total_paid, 2, ',', '.') }} DH</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div
                                    class="card {{ $purchase->remaining_amount > 0 ? 'bg-warning' : 'bg-secondary' }} text-white">
                                    <div class="card-body">
                                        <h6 class="text-white">Reste à Payer</h6>
                                        <h3 class="text-white">{{ number_format($purchase->remaining_amount, 2, ',', '.') }} DH</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="text-white">Statut Paiement</h6>
                                        <h3 class="text-white">{!! $purchase->payment_status_label !!}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card info-card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-building me-2"></i>Informations Fournisseur
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%">N° Commande:</th>
                                                <td><strong>{{ $purchase->purchase_number }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Fournisseur:</th>
                                                <td>{{ $purchase->supplier->company_name ?? ($purchase->supplier->full_name ?? 'N/A') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Contact:</th>
                                                <td>{{ $purchase->supplier->contact_person ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Téléphone:</th>
                                                <td>{{ $purchase->supplier->phone ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td>{{ $purchase->supplier->email ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card info-card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-warehouse me-2"></i>Magasin de Destination
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%">Magasin:</th>
                                                <td>{{ $purchase->magazine->magazine_name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Adresse:</th>
                                                <td>{{ $purchase->magazine->location ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Responsable:</th>
                                                <td>{{ $purchase->magazine->manager_name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Téléphone:</th>
                                                <td>{{ $purchase->magazine->phone ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Statut:</th>
                                                <td>
                                                    @if ($purchase->magazine->is_active ?? false)
                                                        <span class="badge badge-success">Actif</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactif</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card info-card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-calendar-alt me-2"></i>Dates et Statuts
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%">Date Commande:</th>
                                                <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Date Livraison Prévue:</th>
                                                <td>{{ \Carbon\Carbon::parse($purchase->expected_delivery_date)->format('d/m/Y') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Date Livraison Réelle:</th>
                                                <td>
                                                    @if ($purchase->actual_delivery_date)
                                                        {{ \Carbon\Carbon::parse($purchase->actual_delivery_date)->format('d/m/Y') }}
                                                        <span class="badge badge-success ms-2">Livré</span>
                                                    @else
                                                        <span class="badge badge-warning">En attente</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Statut Paiement:</th>
                                                <td>{!! $purchase->payment_status_label !!}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-boxes me-2"></i>Articles Commandés
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
                                                        <th>Quantité Commandée</th>
                                                        <th>Quantité Reçue</th>
                                                        <th>Prix Unitaire (DH)</th>
                                                        <th>Total (DH)</th>
                                                        <th>Statut</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($purchase->items as $index => $item)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            @if ($item->isChargeDiverse())
                                                                <td>{{ $item->description }} <span class="badge badge-secondary">Charge Diverse</span></td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>{{ number_format($item->total_price, 2, ',', '.') }}</td>
                                                                <td><span class="badge badge-secondary">N/A</span></td>
                                                            @else
                                                                <td>{{ $item->rawMaterial->material_name ?? 'N/A' }}</td>
                                                                <td>{{ $item->rawMaterial->material_code ?? 'N/A' }}</td>
                                                                <td>{{ number_format($item->quantity, 2, ',', '.') }}
                                                                    {{ $item->rawMaterial->unit_of_measure ?? '' }}</td>
                                                                <td>{{ number_format($item->received_quantity, 2, ',', '.') }}
                                                                    {{ $item->rawMaterial->unit_of_measure ?? '' }}</td>
                                                                <td>{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                                                <td>{{ number_format($item->total_price, 2, ',', '.') }}</td>
                                                                <td>
                                                                    @if ($item->received_quantity == 0)
                                                                        <span class="badge badge-warning">En attente</span>
                                                                    @elseif($item->received_quantity < $item->quantity)
                                                                        <span class="badge badge-info">Partiel</span>
                                                                    @else
                                                                        <span class="badge badge-success">Complet</span>
                                                                    @endif
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="6" class="text-end"><strong>Sous-total:</strong>
                                                        </td>
                                                        <td colspan="2">
                                                            <strong>{{ number_format($purchase->total_amount, 2, ',', '.') }}
                                                                DH</strong>
                                                        </td>
                                                    </tr>
                                                    @if ($purchase->discount_amount > 0)
                                                        <tr>
                                                            <td colspan="6" class="text-end">
                                                                Remise ({{ $purchase->discount_percentage }}%):
                                                            </td>
                                                            <td colspan="2">-
                                                                {{ number_format($purchase->discount_amount, 2, ',', '.') }} DH</td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <td colspan="6" class="text-end">
                                                            Taxe ({{ $purchase->tax_percentage }}%):
                                                        </td>
                                                        <td colspan="2">+ {{ number_format($purchase->tax_amount, 2, ',', '.') }}
                                                            DH</td>
                                                    </tr>
                                                    <tr class="table-primary">
                                                        <td colspan="6" class="text-end"><strong>Total TTC:</strong>
                                                        </td>
                                                        <td colspan="2">
                                                            <strong>{{ number_format($purchase->final_amount, 2, ',', '.') }}
                                                                DH</strong>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Documents Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-file-invoice me-2"></i>Documents de Paiement
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if ($purchase->paymentDocuments->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>N° Document</th>
                                                            <th>Moyen de Paiement</th>
                                                            <th>Montant (DH)</th>
                                                            <th>Date Paiement</th>
                                                            <th>Document</th>
                                                            <th>Notes</th>
                                                            <th>Uploadé par</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($purchase->paymentDocuments as $doc)
                                                            @php
                                                                // A payment distributed over several purchases is one
                                                                // payment: show its whole amount, edit/delete it whole.
                                                                $group = $doc->groupDocuments();
                                                                $isGrouped = $group->count() > 1;
                                                                $groupTotal = (float) $group->sum('amount');
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $doc->document_number }}</td>
                                                                <td>
                                                                    @switch($doc->payment_method)
                                                                        @case('cash')
                                                                            <span class="badge badge-success">Espèces</span>
                                                                        @break

                                                                        @case('bank_transfer')
                                                                        @case('transfer')
                                                                            <span class="badge badge-info">Virement</span>
                                                                        @break

                                                                        @case('check')
                                                                            <span class="badge badge-primary">Chèque</span>
                                                                        @break

                                                                        @case('traite')
                                                                            <span class="badge badge-warning">Traite</span>
                                                                        @break

                                                                        @case('credit_card')
                                                                            <span class="badge badge-warning">Carte</span>
                                                                        @break

                                                                        @default
                                                                            <span
                                                                                class="badge badge-secondary">{{ $doc->payment_method }}</span>
                                                                    @endswitch
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($doc->amount, 2, ',', '.') }}
                                                                    @if ($isGrouped)
                                                                        <br>
                                                                        <small class="text-info"
                                                                            title="{{ $group->map(fn($g) => optional($g->purchase)->purchase_number)->filter()->implode(', ') }}">
                                                                            <i class="fas fa-layer-group me-1"></i>
                                                                            part d'un paiement de
                                                                            {{ number_format($groupTotal, 2, ',', '.') }} DH
                                                                            sur {{ $group->count() }} achats
                                                                        </small>
                                                                    @endif
                                                                </td>
                                                                <td>{{ \Carbon\Carbon::parse($doc->payment_date)->format('d/m/Y') }}
                                                                </td>
                                                                <td>
                                                                    @if ($doc->file_path)
                                                                        @php
                                                                            $extension = pathinfo(
                                                                                $doc->file_path,
                                                                                PATHINFO_EXTENSION,
                                                                            );
                                                                        @endphp
                                                                        @if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']))
                                                                            <a href="{{ Storage::url($doc->file_path) }}"
                                                                                target="_blank"
                                                                                class="btn btn-sm btn-outline-primary">
                                                                                <i class="fas fa-image"></i> Voir
                                                                            </a>
                                                                        @else
                                                                            <a href="{{ Storage::url($doc->file_path) }}"
                                                                                target="_blank"
                                                                                class="btn btn-sm btn-outline-primary">
                                                                                <i class="fas fa-file-pdf"></i> PDF
                                                                            </a>
                                                                        @endif
                                                                    @else
                                                                        <span class="text-muted">Aucun document</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $doc->notes ?? '-' }}</td>
                                                                <td>{{ $doc->uploader->username ?? 'N/A' }}</td>
                                                                 <td>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-warning edit-payment-doc"
                                                                        data-doc-id="{{ $doc->document_id }}"
                                                                        data-group-id="{{ $doc->payment_group_id }}"
                                                                        data-group-count="{{ $group->count() }}"
                                                                        data-group-purchases="{{ $group->map(fn($g) => optional($g->purchase)->purchase_number)->filter()->implode(', ') }}"
                                                                        data-amount="{{ $doc->payment_group_id ? $groupTotal : $doc->amount }}"
                                                                        data-method="{{ $doc->payment_method }}"
                                                                        data-date="{{ \Carbon\Carbon::parse($doc->payment_date)->format('Y-m-d') }}"
                                                                        data-notes="{{ e($doc->notes ?? '') }}"
                                                                        data-has-file="{{ $doc->file_path ? 1 : 0 }}"
                                                                        title="Modifier">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger delete-payment-doc"
                                                                        data-doc-id="{{ $doc->document_id }}"
                                                                        data-group-id="{{ $doc->payment_group_id }}"
                                                                        data-group-count="{{ $group->count() }}"
                                                                        data-doc-number="{{ $doc->document_number }}"
                                                                        title="Supprimer">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="table-success">
                                                            <td colspan="2" class="text-end"><strong>Total
                                                                    Payé:</strong></td>
                                                            <td class="text-end">
                                                                <strong>{{ number_format($purchase->total_paid, 2, ',', '.') }}
                                                                    DH</strong>
                                                            </td>
                                                            <td colspan="5"></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted text-center mb-0">Aucun document de paiement</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Check Allocations Section -->
                        @if ($purchase->checkAllocations && $purchase->checkAllocations->count() > 0)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-money-check me-2"></i>Allocations de Chèques
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>N° Chèque</th>
                                                            <th>Type</th>
                                                            <th>Banque</th>
                                                            <th>Titulaire</th>
                                                            <th>Date Émission</th>
                                                            <th>Date Échéance</th>
                                                            <th>Montant Alloué (DH)</th>
                                                            <th>Statut</th>
                                                            <th>Image</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($purchase->checkAllocations as $allocation)
                                                            <tr>
                                                                <td>{{ $allocation->check->check_number ?? 'N/A' }}</td>
                                                                <td>
                                                                    @if ($allocation->check->check_type == 'client')
                                                                        <span class="badge badge-primary">Client</span>
                                                                    @else
                                                                        <span
                                                                            class="badge badge-secondary">Personnel</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $allocation->check->bank_name ?? 'N/A' }}</td>
                                                                <td>{{ $allocation->check->account_holder ?? 'N/A' }}</td>
                                                                <td>{{ $allocation->check->issue_date ? \Carbon\Carbon::parse($allocation->check->issue_date)->format('d/m/Y') : 'N/A' }}
                                                                </td>
                                                                <td>{{ $allocation->check->due_date ? \Carbon\Carbon::parse($allocation->check->due_date)->format('d/m/Y') : 'N/A' }}
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($allocation->allocated_amount, 2, ',', '.') }}
                                                                </td>
                                                                <td>
                                                                    @switch($allocation->check->status)
                                                                        @case('pending')
                                                                            <span class="badge badge-warning">En attente</span>
                                                                        @break

                                                                        @case('cleared')
                                                                            <span class="badge badge-success">Encaissé</span>
                                                                        @break

                                                                        @case('bounced')
                                                                            <span class="badge badge-danger">Rebondi</span>
                                                                        @break

                                                                        @default
                                                                            <span
                                                                                class="badge badge-secondary">{{ $allocation->check->status }}</span>
                                                                    @endswitch
                                                                </td>
                                                                <td>
                                                                    @if ($allocation->check->check_image)
                                                                        <a href="{{ Storage::url($allocation->check->check_image) }}"
                                                                            target="_blank"
                                                                            class="btn btn-sm btn-outline-info">
                                                                            <i class="fas fa-image"></i> Voir
                                                                        </a>
                                                                    @else
                                                                        <span class="text-muted">-</span>
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

                        @if ($purchase->notes)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-sticky-note me-2"></i>Notes
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            {{ $purchase->notes }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <span class="text-muted">Créé par:
                                            {{ $purchase->creator->username ?? 'Système' }}</span><br>
                                        <span class="text-muted">Le:
                                            {{ \Carbon\Carbon::parse($purchase->created_at)->format('d/m/Y H:i') }}</span>
                                        @if ($purchase->updated_at != $purchase->created_at)
                                            <br><span class="text-muted">Modifié le:
                                                {{ \Carbon\Carbon::parse($purchase->updated_at)->format('d/m/Y H:i') }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('raw-material-purchases.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-1"></i> Retour
                                        </a>
                                        <button class="btn btn-primary ms-2" onclick="window.print()">
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

    <!-- Receipt Modal -->
    @if (!$purchase->actual_delivery_date)
        <div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Réception de Marchandises</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="receiptForm">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="purchase_id" value="{{ $purchase->purchase_id }}">

                            <div class="form-group mb-3">
                                <label class="form-label">Date de Réception *</label>
                                <input type="date" class="form-control" name="actual_delivery_date"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm" id="receiptItemsTable">
                                    <thead>
                                        <tr>
                                            <th>Matière</th>
                                            <th>Quantité Commandée</th>
                                            <th>Quantité Reçue *</th>
                                            <th>Unité</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchase->items as $index => $item)
                                            @continue($item->isChargeDiverse())
                                            <tr>
                                                <td>
                                                    {{ $item->rawMaterial->material_name }}
                                                    <input type="hidden"
                                                        name="items[{{ $index }}][purchase_item_id]"
                                                        value="{{ $item->purchase_item_id }}">
                                                </td>
                                                <td>{{ number_format($item->quantity, 2, ',', '.') }}</td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm"
                                                        name="items[{{ $index }}][received_quantity]"
                                                        value="{{ $item->received_quantity }}" min="0"
                                                        max="{{ $item->quantity }}" step="0.01" required>
                                                </td>
                                                <td>{{ $item->rawMaterial->unit_of_measure }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success">Enregistrer la Réception</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>

    <!-- Edit Payment Document Modal -->
    <div class="modal fade" id="editPaymentDocModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Modifier le document de paiement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editPaymentDocForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_doc_id">
                    <input type="hidden" id="edit_doc_group_id">
                    <div class="modal-body">
                        {{-- One payment spread over several purchases: the amount is the whole payment --}}
                        <div id="edit_doc_group_info" class="alert alert-info py-2" style="display:none;">
                            <i class="fas fa-layer-group me-1"></i>
                            Ce paiement couvre <strong id="edit_doc_group_count"></strong> achats :
                            <span id="edit_doc_group_purchases" class="fw-semibold"></span>
                            <div class="small text-muted mt-1">
                                Le montant ci-dessous est le paiement complet. Il sera redistribué sur les achats
                                impayés du fournisseur (du plus ancien au plus récent).
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Montant (DH) *</label>
                                <input type="number" class="form-control" id="edit_doc_amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date *</label>
                                <input type="date" class="form-control" id="edit_doc_date" name="payment_date" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Méthode *</label>
                                <select class="form-control" id="edit_doc_method" name="payment_method" required>
                                    <option value="cash">Espèces</option>
                                    <option value="check">Chèque</option>
                                    <option value="bank_transfer">Virement bancaire</option>
                                    <option value="traite">Traite</option>
                                    <option value="credit_card">Carte</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Document (remplace l'existant)</label>
                                <input type="file" class="form-control" id="edit_doc_file" name="document" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="edit_doc_notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Payment Document Modal -->
    <div class="modal fade" id="deletePaymentDocModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Supprimer le document <strong id="delete_doc_number"></strong> ?</p>
                    <div class="alert alert-danger" id="delete_doc_group_warning" style="display:none;">
                        <i class="fas fa-layer-group me-1"></i>
                        Ce paiement couvre <strong id="delete_doc_group_count"></strong> achats : il sera supprimé
                        en entier et tous ces achats redeviendront impayés.
                    </div>
                    <div class="alert alert-warning">Le paiement sera annulé et le solde recalculé. Les allocations chèque seront libérées.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteDocBtn"><i class="fas fa-trash me-1"></i>Supprimer</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('stylesheets')
    <style>
        @media print {

            .card-header-custom,
            .btn,
            .modal,
            #toast-container {
                display: none !important;
            }

            .card {
                border: none !important;
            }
        }

        .payment-document-row {
            transition: all 0.3s ease;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Receipt Form Submit
            $('#receiptForm').submit(function(e) {
                e.preventDefault();

                var formData = $(this).serialize();
                var url = "{{ route('raw-material-purchases.receipt', $purchase->purchase_id) }}";

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#receiptModal').modal('hide');
                            showToast('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON?.errors;
                        var errorMessage = '';

                        if (errors) {
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        } else {
                            errorMessage = xhr.responseJSON?.message ||
                                'Une erreur est survenue';
                        }

                        showToast('error', errorMessage);
                    }
                });
            });

            // Open edit payment doc modal
            $(document).on('click', '.edit-payment-doc', function() {
                var $btn = $(this);
                var groupId = $btn.data('group-id') || '';
                var groupCount = parseInt($btn.data('group-count'), 10) || 1;

                $('#edit_doc_id').val($btn.data('doc-id'));
                $('#edit_doc_group_id').val(groupId);
                $('#edit_doc_amount').val($btn.data('amount'));
                $('#edit_doc_date').val($btn.data('date'));
                $('#edit_doc_method').val($btn.data('method'));
                $('#edit_doc_notes').val($btn.data('notes'));
                $('#edit_doc_file').val('');

                // Grouped payment: the amount above is the whole payment, not this slice
                if (groupId && groupCount > 1) {
                    $('#edit_doc_group_count').text(groupCount);
                    $('#edit_doc_group_purchases').text($btn.data('group-purchases') || '');
                    $('#edit_doc_group_info').show();
                } else {
                    $('#edit_doc_group_info').hide();
                }

                $('#editPaymentDocModal').modal('show');
            });

            // Submit edit payment doc
            $('#editPaymentDocForm').on('submit', function(e) {
                e.preventDefault();
                var docId = $('#edit_doc_id').val();
                var groupId = $('#edit_doc_group_id').val();
                var formData = new FormData(this);
                var url;

                if (groupId) {
                    // Replace the whole payment and re-spread it
                    url = "{{ url('raw-material-purchases/payments') }}/" + groupId;
                    var file = $('#edit_doc_file')[0].files[0];
                    formData.delete('document');
                    formData.delete('_method');
                    if (file) formData.append('payment_file', file);
                } else {
                    url = "{{ url('raw-material-purchases/payment-documents') }}/" + docId;
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            $('#editPaymentDocModal').modal('hide');
                            showToast('success', res.message);
                            setTimeout(function() { location.reload(); }, 800);
                        } else { showToast('error', res.message); }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message || 'Erreur');
                    }
                });
            });

            // Delete payment doc
            var deleteDocId = null;
            var deleteGroupId = '';
            $(document).on('click', '.delete-payment-doc', function() {
                deleteDocId = $(this).data('doc-id');
                deleteGroupId = $(this).data('group-id') || '';
                var groupCount = parseInt($(this).data('group-count'), 10) || 1;

                $('#delete_doc_number').text($(this).data('doc-number'));
                $('#delete_doc_group_warning')
                    .toggle(groupCount > 1)
                    .find('#delete_doc_group_count').text(groupCount);
                $('#deletePaymentDocModal').modal('show');
            });

            $('#confirmDeleteDocBtn').click(function() {
                if (!deleteDocId) return;
                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Suppression...');

                var url = deleteGroupId ?
                    "{{ url('raw-material-purchases/payments') }}/" + deleteGroupId :
                    "{{ url('raw-material-purchases/payment-documents') }}/" + deleteDocId;

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            $('#deletePaymentDocModal').modal('hide');
                            showToast('success', res.message);
                            setTimeout(function() { location.reload(); }, 800);
                        } else { showToast('error', res.message); }
                        $btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i>Supprimer');
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message || 'Erreur');
                        $btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i>Supprimer');
                    }
                });
            });

            function showToast(type, message) {
                var toastId = 'toast_' + Date.now();
                var toast = $('<div id="' + toastId + '" class="toast align-items-center text-white bg-' +
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
                    $('#' + toastId).remove();
                }, 5000);
            }
        });
    </script>
@endpush
