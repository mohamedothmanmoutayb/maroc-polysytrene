@extends('layouts.app')

@section('title', 'Détails Vente')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails de la Vente</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('sales.orders.index') }}">
                                        Ventes
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
                            <i class="fas fa-info-circle me-2"></i>Vente: {{ $order->order_number }}
                        </h5>
                        <div>
                            @if ($order->payment_status != 'paid')
                                @can('create_purchases')
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#paymentModal">
                                    <i class="fas fa-money-bill-wave me-1"></i> Ajouter paiement
                                </button>
                                @endcan
                            @endif
                            @can('edit_sales_orders')
                            <a href="{{ route('sales.orders.edit', $order->order_id) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            @endcan
                            <button type="button" class="btn btn-info btn-sm" onclick="openDeliveryNoteModal({{ $order->order_id }}, '{{ $order->order_number }}')">
                                <i class="fas fa-truck me-1"></i> Bon de livraison
                            </button>
                            @if ($order->status == 'completed')
                                @can('create_sales_invoices')
                                <a href="{{ route('sales.orders.create-invoice', $order->order_id) }}"
                                    class="btn btn-info btn-sm">
                                    <i class="fas fa-file-invoice me-1"></i> Créer Facture
                                </a>
                                @endcan
                            @endif
                            <a href="{{ route('sales.orders.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column - Order Information -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Informations de la Vente</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="40%">Numéro Vente</th>
                                                <td>{{ $order->order_number }}</td>
                                            </tr>
                                            <tr>
                                                <th>Client</th>
                                                <td>
                                                    <strong>{{ $order->client->display_name }}</strong><br>
                                                    <small class="text-muted">{{ $order->client->phone }}</small>
                                                    @if ($order->client->email)
                                                        <br><small class="text-muted">{{ $order->client->email }}</small>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Solde Client</th>
                                                <td>
                                                    @php
                                                        $balance = $order->client->balance;
                                                        $balanceClass =
                                                            $balance > 0
                                                                ? 'text-success'
                                                                : ($balance < 0
                                                                    ? 'text-danger'
                                                                    : 'text-secondary');
                                                        $balanceText =
                                                            $balance > 0
                                                                ? '+' . number_format($balance, 2, ',', '.')
                                                                : number_format($balance, 2, ',', '.');
                                                    @endphp
                                                    <span class="{{ $balanceClass }} fw-bold">{{ $balanceText }}
                                                        DH</span>
                                                    @if ($order->client->available_advance > 0)
                                                        <br><small class="text-success">Avance disponible:
                                                            {{ number_format($order->client->available_advance, 2, ',', '.') }}
                                                            DH</small>
                                                    @endif
                                                    @if ($order->client->credit_usage > 0)
                                                        <br><small class="text-warning">Crédit utilisé:
                                                            {{ number_format($order->client->credit_usage, 2, ',', '.') }} /
                                                            {{ number_format($order->client->credit_limit, 2, ',', '.') }} DH</small>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Date Vente</th>
                                                <td>{{ $order->order_date->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Statut</th>
                                                <td>
                                                    @php
                                                        $badges = [
                                                            'draft' => 'secondary',
                                                            'pending' => 'warning',
                                                            'confirmed' => 'info',
                                                            'processing' => 'primary',
                                                            'completed' => 'success',
                                                            'cancelled' => 'danger',
                                                        ];
                                                        $labels = [
                                                            'draft' => 'Brouillon',
                                                            'pending' => 'En attente',
                                                            'confirmed' => 'Confirmé',
                                                            'processing' => 'En cours',
                                                            'completed' => 'Terminé',
                                                            'cancelled' => 'Annulé',
                                                        ];
                                                        $color = $badges[$order->status] ?? 'secondary';
                                                        $label = $labels[$order->status] ?? $order->status;
                                                    @endphp
                                                    <span
                                                        class="badge badge-{{ $color }}">{{ $label }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Statut Paiement</th>
                                                <td>
                                                    @php
                                                        $badges = [
                                                            'pending' => 'danger',
                                                            'partial' => 'warning',
                                                            'paid' => 'success',
                                                        ];
                                                        $labels = [
                                                            'pending' => 'Non Payé',
                                                            'partial' => 'Avance',
                                                            'paid' => 'Payé',
                                                        ];
                                                        $color = $badges[$order->payment_status] ?? 'secondary';
                                                        $label =
                                                            $labels[$order->payment_status] ?? $order->payment_status;
                                                    @endphp
                                                    <span class="badge badge-{{ $color }}"
                                                        id="payment-status-badge">{{ $label }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Montant Total</th>
                                                <td><strong>{{ number_format($order->final_amount, 2, ',', '.') }} DH</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Montant Payé</th>
                                                <td>
                                                    @php
                                                        $totalReceived = $order->payments->sum('display_amount');
                                                        $excessToBalance = max(0, $totalReceived - $order->paid_amount);
                                                    @endphp
                                                    <span class="text-success"
                                                        id="paid-amount">{{ number_format($totalReceived, 2, ',', '.') }}
                                                        DH</span>
                                                    @if ($excessToBalance > 0.005)
                                                        <br><small class="text-muted" id="paid-amount-excess-note">
                                                            (dont {{ number_format($excessToBalance, 2, ',', '.') }} DH ajoutés au solde client)
                                                        </small>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Reste à Payer</th>
                                                <td>
                                                    @php
                                                        $rest = max(0, $order->final_amount - $order->paid_amount);
                                                        $restClass = $rest > 0 ? 'text-danger' : 'text-success';
                                                    @endphp
                                                    <span class="{{ $restClass }} fw-bold"
                                                        id="rest-amount">{{ number_format($rest, 2, ',', '.') }} DH</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Créée par</th>
                                                <td>{{ $order->creator->username ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Date Création</th>
                                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Dernière Mise à Jour</th>
                                                <td>{{ $order->updated_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <!-- Notes Card -->
                                @if ($order->notes)
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Notes</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-0">{{ $order->notes }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Right Column - Order Items and Payments -->
                            <div class="col-md-6">
                                <!-- Order Items Card -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Articles de la Vente</h6>
                                        <span class="badge bg-primary">Total: {{ number_format($order->final_amount, 2, ',', '.') }}
                                            DH</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Produit</th>
                                                        <th class="text-center">Qté</th>
                                                        <th class="text-center">Unité</th>
                                                        <th class="text-end">Prix Unitaire</th>
                                                        <th class="text-end">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($order->items as $item)
                                                        <tr>
                                                            <td>
                                                                {{ $item->item_name }}<br>
                                                                <small class="text-muted">{{ $item->type_label }}</small>
                                                                @if ($item->family_name)
                                                                    <br><small class="text-info">Famille:
                                                                        {{ $item->family_name }}</small>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">{{ number_format($item->quantity, 2, ',', '.') }}
                                                            </td>
                                                            <td class="text-center text-uppercase">{{ $item->unit_of_measure ?? '-' }}</td>
                                                            <td class="text-end">{{ number_format($item->unit_price, 2, ',', '.') }}
                                                                DH</td>
                                                            <td class="text-end">{{ number_format($item->total_price, 2, ',', '.') }}
                                                                DH</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- Avoirs liés à cette commande --}}
                                @if ($order->creditNotes->count() > 0)
                                <div class="card mb-4">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fas fa-undo-alt me-2 text-warning"></i>Avoirs associés</h6>
                                        <span class="badge bg-warning text-dark">{{ $order->creditNotes->count() }}</span>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>N° Avoir</th>
                                                        <th>Date</th>
                                                        <th>Articles retournés</th>
                                                        <th class="text-end">Montant</th>
                                                        <th>Statut</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($order->creditNotes as $avoir)
                                                    <tr>
                                                        <td><strong>{{ $avoir->credit_note_number }}</strong></td>
                                                        <td>{{ $avoir->credit_note_date->format('d/m/Y') }}</td>
                                                        <td>
                                                            @foreach ($avoir->items as $ai)
                                                                <small class="d-block">
                                                                    {{ $ai->item_name }}
                                                                    @if ($ai->family_name) ({{ $ai->family_name }}) @endif
                                                                    — {{ number_format($ai->quantity, 2, ',', '.') }} u.
                                                                </small>
                                                            @endforeach
                                                        </td>
                                                        <td class="text-end fw-bold">{{ number_format($avoir->total_amount, 2, ',', '.') }} DH</td>
                                                        <td>
                                                            @php
                                                                $aBadges = ['draft'=>'secondary','pending'=>'warning','approved'=>'info','rejected'=>'danger','processed'=>'success'];
                                                                $aLabels = ['draft'=>'Brouillon','pending'=>'En attente','approved'=>'Approuvé','rejected'=>'Rejeté','processed'=>'Traité'];
                                                            @endphp
                                                            <span class="badge badge-{{ $aBadges[$avoir->status] ?? 'secondary' }}">
                                                                {{ $aLabels[$avoir->status] ?? $avoir->status }}
                                                            </span>
                                                            @if ($avoir->disposition === 'refund')
                                                                <span class="badge bg-success ms-1">Remboursement</span>
                                                            @else
                                                                <span class="badge bg-primary ms-1">Avoir   </span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('credit-notes.show', $avoir->credit_note_id) }}"
                                                               class="btn btn-xs btn-outline-secondary" title="Voir l'avoir">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <td colspan="3" class="text-end fw-bold">Total avoirs :</td>
                                                        <td class="text-end fw-bold text-danger">
                                                            {{ number_format($order->creditNotes->sum('total_amount'), 2, ',', '.') }} DH
                                                        </td>
                                                        <td colspan="2"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Payments Card -->
                                <div class="card">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Paiements</h6>
                                        @if ($order->payment_status != 'paid')
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#paymentModal">
                                                <i class="fas fa-plus me-1"></i> Ajouter
                                            </button>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        @if ($order->payments->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm" id="payments-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Méthode</th>
                                                            <th class="text-end">Montant</th>
                                                            <th>Référence</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($order->payments as $payment)
                                                            <tr>
                                                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                                                <td>
                                                                    @switch($payment->payment_method)
                                                                        @case('cash')
                                                                            <span class="badge bg-success">Espèces</span>
                                                                        @break

                                                                        @case('check')
                                                                            <span class="badge bg-info">Chèque</span>
                                                                        @break

                                                                        @case('transfer')
                                                                            <span class="badge bg-primary">Virement</span>
                                                                        @break

                                                                        @case('traite')
                                                                            <span class="badge bg-warning">Traite</span>
                                                                        @break

                                                                        @case('advance')
                                                                            <span class="badge bg-purple">Solde client</span>
                                                                        @break

                                                                        @case('avoir')
                                                                            <span class="badge bg-warning text-dark">Avoir</span>
                                                                        @break
                                                                    @endswitch
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($payment->display_amount, 2, ',', '.') }} DH</td>
                                                                <td>{{ $payment->notes ?? '-' }}</td>
                                                                <td>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger delete-payment"
                                                                        data-payment-id="{{ $payment->payment_id }}">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="table-active">
                                                            <td colspan="2" class="text-end"><strong>Total
                                                                    Payé:</strong></td>
                                                            <td class="text-end">
                                                                <strong>{{ number_format($order->payments->sum('display_amount'), 2, ',', '.') }}
                                                                    DH</strong>
                                                            </td>
                                                            <td colspan="2"></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Aucun paiement enregistré pour cette commande.
                                                @if ($order->payment_status != 'paid')
                                                    <button type="button" class="btn btn-success btn-sm ms-2"
                                                        data-bs-toggle="modal" data-bs-target="#paymentModal">
                                                        Ajouter un paiement
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    @include('pages.sales.situation.partials.payment-modal')

    <!-- Delete Payment Confirmation Modal -->
    <div class="modal fade" id="deletePaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer ce paiement ?</p>
                    <p class="text-danger"><small>Cette action est irréversible.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeletePayment">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Note Options Modal -->
    <div class="modal fade" id="deliveryNoteModal" tabindex="-1" aria-labelledby="deliveryNoteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deliveryNoteModalLabel">
                        <i class="fas fa-truck me-2"></i>Options du Bon de Livraison
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="delivery_order_id" name="order_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Type d'affichage des prix</label>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="show_prices" id="showPricesYes"
                                        value="1" checked>
                                    <label class="form-check-label" for="showPricesYes">
                                        <i class="fas fa-eye text-success me-1"></i>Avec prix
                                    </label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="show_prices" id="showPricesNo"
                                        value="0">
                                    <label class="form-check-label" for="showPricesNo">
                                        <i class="fas fa-eye-slash text-warning me-1"></i>Sans prix
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Type d'affichage</label>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="display_type"
                                        id="displayTypeUnite" value="unite" checked>
                                    <label class="form-check-label" for="displayTypeUnite">
                                        <i class="fas fa-weight-hanging text-primary me-1"></i>Avec unité (U)
                                    </label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="display_type"
                                        id="displayTypeVolume" value="volume">
                                    <label class="form-check-label" for="displayTypeVolume">
                                        <i class="fas fa-cube text-success me-1"></i>Avec volume
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Type de prix</label>
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_type" id="priceTypeTTC"
                                        value="ttc" checked>
                                    <label class="form-check-label" for="priceTypeTTC">
                                        TTC
                                    </label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_type" id="priceTypeHT"
                                        value="ht">
                                    <label class="form-check-label" for="priceTypeHT">
                                        HT
                                    </label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_type" id="priceTypeBoth"
                                        value="both">
                                    <label class="form-check-label" for="priceTypeBoth">
                                        Les deux
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                    <button type="button" class="btn btn-info" id="printDeliveryNoteBtn">
                        <i class="fas fa-print me-1"></i>Imprimer
                    </button>
                    <button type="submit" class="btn btn-primary" form="deliveryNoteForm">
                        <i class="fas fa-file-pdf me-1"></i>Générer le PDF
                    </button>
                </div>
            </div>
            <!-- Hidden form for download -->
            <form id="deliveryNoteForm" method="GET" target="_blank" style="display: none;">
                <input type="hidden" name="order_id" id="form_order_id">
                <input type="hidden" name="show_prices" id="form_show_prices">
                <input type="hidden" name="show_logo" id="form_show_logo">
                <input type="hidden" name="display_type" id="form_display_type">
                <input type="hidden" name="price_type" id="form_price_type">
            </form>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <style>
        .bg-purple {
            background-color: #6f42c1 !important;
            color: white;
        }

        .check-fields,
        .transfer-fields,
        .traite-fields,
        .advance-fields,
        .cash-fields {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
        }

        .toast-container {
            z-index: 9999;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let paymentToDelete = null;

            // Delete payment
            $(document).on('click', '.delete-payment', function() {
                paymentToDelete = $(this).data('payment-id');
                $('#deletePaymentModal').modal('show');
            });

            $('#confirmDeletePayment').click(function() {
                if (!paymentToDelete) return;

                let orderId = '{{ $order->order_id }}';
                let $btn = $(this);
                let originalText = $btn.html();

                $btn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Suppression...');

                $.ajax({
                    url: "{{ route('sales.orders.delete-payment', ['orderId' => $order->order_id, 'paymentId' => ':paymentId']) }}"
                        .replace(':paymentId', paymentToDelete),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', 'Paiement supprimé avec succès');

                            // Update order summary
                            updatePaidAmountDisplay(response.order);
                            $('#rest-amount').text(response.order.remaining + ' DH')
                                .removeClass('text-danger text-success')
                                .addClass(parseFloat(response.order.remaining) > 0 ?
                                    'text-danger' : 'text-success');

                            // Update status badge
                            let statusBadge = $('#payment-status-badge');
                            if (response.order.payment_status === 'paid') {
                                statusBadge.text('Payé').removeClass(
                                    'badge-danger badge-warning').addClass('badge-success');
                            } else if (response.order.payment_status === 'partial') {
                                statusBadge.text('Avance').removeClass(
                                    'badge-danger badge-success').addClass('badge-warning');
                            } else {
                                statusBadge.text('Non Payé').removeClass(
                                    'badge-warning badge-success').addClass('badge-danger');
                            }

                            // Remove the payment row
                            $('button[data-payment-id="' + paymentToDelete + '"]').closest('tr')
                                .remove();

                            $('#deletePaymentModal').modal('hide');
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = xhr.responseJSON?.message ||
                            'Erreur lors de la suppression';
                        showToast('error', errorMessage);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalText);
                        paymentToDelete = null;
                    }
                });
            });

            // Handle payment modal events
            $('#paymentModal').on('hidden.bs.modal', function() {
                $('#paymentForm')[0].reset();
                $('#payment-details-container').empty();
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            });

            function showToast(type, message) {
                let toast = $('<div class="toast align-items-center text-white bg-' +
                    (type === 'success' ? 'success' : 'danger') +
                    ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                    '<div class="d-flex">' +
                    '<div class="toast-body">' + message + '</div>' +
                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
                    '</div>' +
                    '</div>');

                $('#toast-container').append(toast);
                let bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();

                setTimeout(function() {
                    toast.remove();
                }, 5000);
            }

            // Shows the full amount received (order paid_amount + any excess credited to
            // the client's balance), with a small note when there's an excess.
            function updatePaidAmountDisplay(order) {
                $('#paid-amount').text((order.total_received || order.paid_amount) + ' DH');

                let received = parseFloat(order.total_received || order.paid_amount) || 0;
                let applied = parseFloat(order.paid_amount) || 0;
                let excess = received - applied;

                let $note = $('#paid-amount-excess-note');
                if (excess > 0.005) {
                    let noteHtml = '(dont ' + excess.toFixed(2).replace('.', ',') + ' DH ajoutés au solde client)';
                    if ($note.length) {
                        $note.text(noteHtml);
                    } else {
                        $('#paid-amount').after('<br><small class="text-muted" id="paid-amount-excess-note">' + noteHtml + '</small>');
                    }
                } else {
                    $note.remove();
                }
            }

            let maxAdvance = {{ $order->client->available_advance ?? 0 }};
            let orderRemaining = {{ $order->final_amount - $order->paid_amount }};
            let orderId = '{{ $order->order_id }}';

            // Payment method change handler
            $('#payment_method').change(function() {
                let method = $(this).val();
                let container = $('#payment-details-container');
                container.empty();

                let html = '';

                switch (method) {
                    case 'check':
                        html = `
                    <div class="check-fields">
                        <h6 class="mb-3">Détails du chèque</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" name="check_number" placeholder="N° Chèque" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" name="bank_name" placeholder="Banque" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" name="account_holder" placeholder="Titulaire" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="date" class="form-control" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label small">Document (Image du chèque)</label>
                                <input type="file" class="form-control" name="document" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>
                `;
                        break;

                    case 'transfer':
                        html = `
                    <div class="transfer-fields">
                        <h6 class="mb-3">Détails du virement</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" name="transfer_reference" placeholder="Référence virement" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" name="bank_name" placeholder="Banque émettrice" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" name="account_number" placeholder="Compte bénéficiaire" required>
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label small">Justificatif de virement</label>
                                <input type="file" class="form-control" name="document" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>
                `;
                        break;

                    case 'traite':
                        html = `
                    <div class="traite-fields">
                        <h6 class="mb-3">Détails de la traite</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" name="traite_number" placeholder="N° Traite" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" name="drawee" placeholder="Tiré (client)" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" name="bank_name" placeholder="Banque" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="date" class="form-control" name="due_date" value="{{ date('Y-m-d', strtotime('+60 days')) }}" required>
                            </div>
                            <div class="col-12 mb-2">
                                <textarea class="form-control" name="drawee_address" placeholder="Adresse du tiré" rows="2"></textarea>
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label small">Document (Lettre de change)</label>
                                <input type="file" class="form-control" name="document" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>
                `;
                        break;

                    case 'advance':
                        html = `
                    <div class="advance-fields">
                        <h6 class="mb-3">Utilisation du solde</h6>
                        <div class="alert alert-info py-2">
                            <strong>Solde disponible:</strong> {{ number_format($order->client->available_advance, 2, ',', '.') }} DH
                        </div>
                        <input type="hidden" class="max-advance-amount" value="${maxAdvance}">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <input type="text" class="form-control" name="advance_reference" placeholder="Référence (optionnel)">
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label small">Document justificatif</label>
                                <input type="file" class="form-control" name="document" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        <small class="text-muted">Le montant sera déduit du solde client</small>
                    </div>
                `;
                        break;

                    case 'cash':
                        html = `
                    <div class="cash-fields">
                        <h6 class="mb-3">Paiement en espèces</h6>
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <input type="text" class="form-control" name="cash_reference" placeholder="Référence (optionnel)">
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label small">Reçu / Document</label>
                                <input type="file" class="form-control" name="document" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>
                `;
                        break;
                }

                container.html(html);
            });

            // Validate amount
            $('#payment_amount').on('input', function() {
                let amount = parseFloat($(this).val()) || 0;
                let method = $('#payment_method').val();
                let errorDiv = $('#amount-error');

                if (errorDiv.length) errorDiv.remove();
                $(this).removeClass('is-invalid');

                if (amount > orderRemaining && method !== 'advance') {
                    showExcessSection(amount - orderRemaining);
                } else {
                    hideExcessSection();
                }

                if (method === 'advance' && amount > maxAdvance) {
                    $(this).addClass('is-invalid');
                    $(this).after(
                        '<div id="amount-error" class="invalid-feedback">Le montant ne peut pas dépasser le solde disponible (' +
                        maxAdvance.toFixed(2) + ' DH)</div>');
                } else if (amount <= 0) {
                    $(this).addClass('is-invalid');
                    $(this).after(
                        '<div id="amount-error" class="invalid-feedback">Veuillez saisir un montant valide</div>'
                    );
                }
            });

            // Excess action radio toggle
            $(document).on('change', 'input[name="excess_action"]', function() {
                if ($(this).val() === 'orders') {
                    let excess = (parseFloat($('#payment_amount').val()) || 0) - orderRemaining;
                    if (excess > 0) loadUnpaidOrdersForExcess(excess);
                } else {
                    $('#excess-orders-section').hide();
                }
            });

            // Excess order amount inputs
            $(document).on('input', '.excess-order-amount', function() {
                let max = parseFloat($(this).data('max'));
                let val = parseFloat($(this).val()) || 0;
                if (val > max) $(this).val(max.toFixed(2));
                if (val < 0) $(this).val('0.00');
                updateExcessAllocation();
            });

            // Form submission
            $('#paymentForm').submit(function(e) {
                e.preventDefault();

                let amount = parseFloat($('#payment_amount').val()) || 0;
                let method = $('#payment_method').val();

                // Validate amount
                if (amount <= 0) {
                    showToast('error', 'Veuillez saisir un montant valide');
                    return;
                }

                if (!method) {
                    showToast('error', 'Veuillez sélectionner une méthode de paiement');
                    return;
                }

                if (method === 'advance' && amount > maxAdvance) {
                    showToast('error', 'Le montant ne peut pas dépasser le solde disponible');
                    return;
                }

                // Validate required fields based on method
                if (method === 'check') {
                    if (!$('input[name="check_number"]').val() || !$('input[name="bank_name"]').val() ||
                        !$('input[name="account_holder"]').val() || !$('input[name="due_date"]').val()) {
                        showToast('error', 'Veuillez remplir tous les champs du chèque');
                        return;
                    }
                } else if (method === 'transfer') {
                    if (!$('input[name="transfer_reference"]').val() || !$('input[name="bank_name"]')
                        .val() ||
                        !$('input[name="account_number"]').val()) {
                        showToast('error', 'Veuillez remplir tous les champs du virement');
                        return;
                    }
                } else if (method === 'traite') {
                    if (!$('input[name="traite_number"]').val() || !$('input[name="drawee"]').val() ||
                        !$('input[name="due_date"]').val()) {
                        showToast('error', 'Veuillez remplir tous les champs de la traite');
                        return;
                    }
                }

                // Capture excess state before AJAX so success handler can use it
                let excessAtSubmit = Math.max(0, amount - orderRemaining);
                let excessToOrders = excessAtSubmit > 0 &&
                    $('input[name="excess_action"]:checked').val() === 'orders';

                // Show loading
                const submitBtn = $('#submitPayment');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Ajout...');

                // Prepare form data
                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('sales.orders.add-payment', $order->order_id) }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);

                            // If excess was applied to other orders, reload to reflect all changes
                            if (excessToOrders) {
                                setTimeout(function() { location.reload(); }, 1200);
                                return;
                            }

                            // Update order summary in main view
                            updatePaidAmountDisplay(response.order);
                            $('#rest-amount').text(response.order.rest_amount + ' DH')
                                .removeClass('text-danger text-success')
                                .addClass(response.order.rest_amount_class);

                            // Update status badge
                            let statusBadge = $('#payment-status-badge');
                            statusBadge.text(response.order.payment_status_label);
                            if (response.order.payment_status === 'paid') {
                                statusBadge.removeClass('badge-danger badge-warning').addClass(
                                    'badge-success');
                            } else if (response.order.payment_status === 'partial') {
                                statusBadge.removeClass('badge-danger badge-success').addClass(
                                    'badge-warning');
                            } else {
                                statusBadge.removeClass('badge-warning badge-success').addClass(
                                    'badge-danger');
                            }

                            // Update modal remaining
                            $('#modal-remaining').text(response.order.rest_amount + ' DH');
                            orderRemaining = parseFloat(response.order.rest_amount);

                            // Add payment to the table
                            let methodBadgeClass = getPaymentMethodBadge(response.payment
                                .method);
                            let methodLabel = getPaymentMethodLabel(response.payment.method);

                            let newRow = `
                        <tr>
                            <td>${response.payment.date}</td>
                            <td>
                                <span class="badge ${methodBadgeClass}">${methodLabel}</span>
                            </td>
                            <td class="text-end">${response.payment.amount} DH</td>
                            <td>${response.payment.notes || '-'}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger delete-payment" data-payment-id="${response.payment.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                            if ($('#payments-table tbody').length) {
                                $('#payments-table tbody').append(newRow);
                            } else {
                                location.reload();
                            }

                            // Close modal and reset
                            $('#paymentModal').modal('hide');
                            $('#paymentForm')[0].reset();
                            $('#payment-details-container').empty();
                            hideExcessSection();

                            // Update remaining hint
                            $('#hint-remaining').text(response.order.rest_amount + ' DH');
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html(originalText);
                    },
                    error: function(xhr) {
                        let errorMessage = xhr.responseJSON?.message ||
                            'Une erreur est survenue';
                        showToast('error', errorMessage);
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            function getPaymentMethodBadge(method) {
                switch (method) {
                    case 'cash':
                        return 'bg-success';
                    case 'check':
                        return 'bg-info';
                    case 'transfer':
                        return 'bg-primary';
                    case 'traite':
                        return 'bg-warning';
                    case 'advance':
                        return 'bg-purple';
                    default:
                        return 'bg-secondary';
                }
            }

            function getPaymentMethodLabel(method) {
                switch (method) {
                    case 'cash':
                        return 'Espèces';
                    case 'check':
                        return 'Chèque';
                    case 'transfer':
                        return 'Virement';
                    case 'traite':
                        return 'Traite';
                    case 'advance':
                        return 'Solde client';
                    default:
                        return method;
                }
            }

            function showExcessSection(excess) {
                let fmt = excess.toFixed(2).replace('.', ',');
                $('#excess-amount-badge').text(fmt + ' DH');
                $('#ex-total').text(fmt + ' DH');
                $('#excess-section').show();

                if ($('#excess_orders_radio').is(':checked')) {
                    loadUnpaidOrdersForExcess(excess);
                }
            }

            function hideExcessSection() {
                $('#excess-section').hide();
                $('#excess-orders-section').hide();
                $('#excess-orders-tbody').empty();
                $('input[name="excess_action"][value="balance"]').prop('checked', true);
            }

            function loadUnpaidOrdersForExcess(excess) {
                $('#excess-orders-section').show();
                $('#excess-orders-loading').show();
                $('#excess-orders-table-wrap').hide();
                $('#excess-orders-empty').hide();

                $.ajax({
                    url: '{{ route("sales.orders.client.unpaid", $order->client_id) }}',
                    type: 'GET',
                    success: function(response) {
                        $('#excess-orders-loading').hide();
                        if (!response.success) { $('#excess-orders-empty').show(); return; }

                        let orders = response.orders.filter(function(o) { return o.order_id != {{ $order->order_id }}; });
                        if (orders.length === 0) {
                            $('#excess-orders-empty').show();
                            return;
                        }

                        let tbody = $('#excess-orders-tbody');
                        tbody.empty();
                        let remaining = excess;

                        orders.forEach(function(order) {
                            let suggested = Math.min(parseFloat(order.unpaid_amount), remaining);
                            remaining -= suggested;

                            tbody.append('<tr>' +
                                '<td><strong>' + order.order_number + '</strong></td>' +
                                '<td class="text-center">' + order.order_date + '</td>' +
                                '<td class="text-end">' + parseFloat(order.unpaid_amount).toFixed(2) + ' DH</td>' +
                                '<td class="text-end">' +
                                '<input type="number" class="form-control form-control-sm text-end excess-order-amount"' +
                                ' name="excess_orders[' + order.order_id + ']"' +
                                ' min="0" max="' + parseFloat(order.unpaid_amount).toFixed(2) + '"' +
                                ' step="0.01" value="' + suggested.toFixed(2) + '"' +
                                ' data-max="' + parseFloat(order.unpaid_amount) + '">' +
                                '</td></tr>');
                        });

                        $('#excess-orders-table-wrap').show();
                        updateExcessAllocation();
                    },
                    error: function() {
                        $('#excess-orders-loading').hide();
                        $('#excess-orders-empty').show();
                    }
                });
            }

            function updateExcessAllocation() {
                let amount = parseFloat($('#payment_amount').val()) || 0;
                let excess = Math.max(0, amount - orderRemaining);
                let allocated = 0;
                $('.excess-order-amount').each(function() { allocated += parseFloat($(this).val()) || 0; });
                let unallocated = Math.max(0, excess - allocated);

                $('#ex-total').text(excess.toFixed(2).replace('.', ',') + ' DH');
                $('#ex-allocated').text(allocated.toFixed(2).replace('.', ',') + ' DH');
                $('#ex-unallocated').text(unallocated.toFixed(2).replace('.', ',') + ' DH');
            }

            function showToast(type, message) {
                let toast = $('<div class="toast align-items-center text-white bg-' +
                    (type === 'success' ? 'success' : 'danger') +
                    ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                    '<div class="d-flex">' +
                    '<div class="toast-body">' + message + '</div>' +
                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
                    '</div>' +
                    '</div>');

                $('#toast-container').append(toast);
                let bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();

                setTimeout(function() {
                    toast.remove();
                }, 5000);
            }

            // Handle Download button (Delivery Note)
            $('#deliveryNoteForm').submit(function(e) {
                e.preventDefault();

                var orderId = $('#delivery_order_id').val();
                var showPrices = $('input[name="show_prices"]:checked').val();
                var showLogo = $('input[name="show_logo"]:checked').val();
                var displayType = $('input[name="display_type"]:checked').val();
                var priceType = $('input[name="price_type"]:checked').val();

                var baseUrl = "{{ url('sales/orders/delivery-note') }}";
                var url = baseUrl + "/" + orderId +
                    "?show_prices=" + showPrices +
                    "&show_logo=" + showLogo +
                    "&display_type=" + displayType +
                    "&price_type=" + priceType;

                var printWindow = window.open(url, '_blank');

                if (printWindow) {
                    printWindow.focus();
                }

                $('#deliveryNoteModal').modal('hide');
            });

            // Handle Print button (Delivery Note)
            $(document).on('click', '#printDeliveryNoteBtn', function() {
                var orderId = $('#delivery_order_id').val();
                var showPrices = $('input[name="show_prices"]:checked').val();
                var showLogo = $('input[name="show_logo"]:checked').val();
                var displayType = $('input[name="display_type"]:checked').val();
                var priceType = $('input[name="price_type"]:checked').val();

                var baseUrl = "{{ route('sales.orders.delivery-note.view', ['id' => '__ID__']) }}";
                var url = baseUrl.replace('__ID__', orderId) +
                    "?show_prices=" + showPrices +
                    "&show_logo=" + showLogo +
                    "&display_type=" + displayType +
                    "&price_type=" + priceType;

                var printWindow = window.open(url, '_blank', 'width=800,height=600');

                if (printWindow) {
                    printWindow.focus();

                    printWindow.onload = function() {
                        setTimeout(function() {
                            printWindow.print();
                            printWindow.onafterprint = function() {
                                printWindow.close();
                            };
                        }, 1000);
                    };
                }

                $('#deliveryNoteModal').modal('hide');
            });
        });

        function openDeliveryNoteModal(orderId, orderNumber) {
            $('#delivery_order_id').val(orderId);
            $('#deliveryNoteModal').modal('show');
        }
    </script>
@endpush
