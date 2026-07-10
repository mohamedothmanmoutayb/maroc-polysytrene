@extends('layouts.app')

@section('title', 'Modifier Vente - ' . $order->order_number)

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Vente #{{ $order->order_number }}</h4>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-primary p-2">N° {{ $order->order_number }}</span>
                            <nav aria-label="breadcrumb">
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
                                    <li class="breadcrumb-item">
                                        <a class="text-muted text-decoration-none"
                                            href="{{ route('sales.orders.show', $order->order_id) }}">
                                            {{ $order->order_number }}
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item" aria-current="page">
                                        <span class="badge fw-medium fs-2 bg-warning text-warning">
                                            Modifier
                                        </span>
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom" style="background-color: #ffc107;">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-edit me-2"></i>Modifier la Vente
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="orderForm">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="order_number" name="order_number" value="{{ $order->order_number }}">
                            <input type="hidden" id="order_id" value="{{ $order->order_id }}">

                            <!-- Basic Info Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="order_date" class="form-label">Date Vente *</label>
                                        <input type="date" class="form-control" id="order_date" name="order_date"
                                            value="{{ $order->order_date->format('Y-m-d') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="client_id" class="form-label">Client *</label>
                                        <select class="form-control select2" id="client_id" name="client_id" required>
                                            <option value="">Sélectionner un client</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->client_id }}"
                                                    data-client-type="{{ $client->client_type }}"
                                                    {{ $order->client_id == $client->client_id ? 'selected' : '' }}>
                                                    {{ $client->display_name }} ({{ $client->phone }}) -
                                                    {{ $client->client_type_label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Client Credit & Balance Info -->
                            <div id="client-credit-info" class="mb-3"></div>

                            <!-- Order Items Section -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Articles de la Vente</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="items-table">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="20%">Type</th>
                                                    <th width="25%">Article</th>
                                                    <th width="10%">Quantité</th>
                                                    <th width="15%">Prix Unitaire (DH)</th>
                                                    <th width="15%">Total (DH)</th>
                                                    <th width="10%">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="items-body">
                                                @foreach ($order->items as $index => $item)
                                                    <tr id="item_{{ $item->order_item_id }}_{{ $index }}"
                                                        data-index="{{ $index }}">
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            <select class="form-control item-type"
                                                                data-row="item_{{ $item->order_item_id }}_{{ $index }}"
                                                                required>
                                                                <option value="">Sélectionner</option>
                                                                <option value="raw_material"
                                                                    {{ $item->item_type == 'raw_material' ? 'selected' : '' }}>
                                                                    Matière Première</option>
                                                                <option value="production"
                                                                    {{ $item->item_type == 'production' ? 'selected' : '' }}>
                                                                    Production</option>
                                                                <option value="decoupage"
                                                                    {{ $item->item_type == 'decoupage' ? 'selected' : '' }}>
                                                                    Découpage</option>
                                                                <option value="finale"
                                                                    {{ $item->item_type == 'finale' ? 'selected' : '' }}>
                                                                    Vente</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-control item-select"
                                                                data-row="item_{{ $item->order_item_id }}_{{ $index }}"
                                                                style="width:100%;" required>
                                                                <option value="">Chargement...</option>
                                                            </select>
                                                            <input type="hidden" class="item-id"
                                                                name="items[{{ $index }}][item_id]"
                                                                value="{{ $item->item_id }}">
                                                            <input type="hidden" class="item-name"
                                                                name="items[{{ $index }}][name]"
                                                                value="{{ $item->item_name }}">
                                                            <input type="hidden" class="item-type-input"
                                                                name="items[{{ $index }}][type]"
                                                                value="{{ $item->item_type }}">
                                                            <input type="hidden" class="family-id"
                                                                name="items[{{ $index }}][family_id]"
                                                                value="{{ $item->family_id }}">
                                                            <input type="hidden" class="family-name"
                                                                name="items[{{ $index }}][family_name]"
                                                                value="{{ $item->family_name }}">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control item-quantity"
                                                                name="items[{{ $index }}][quantity]"
                                                                min="0.0001" step="0.0001"
                                                                value="{{ $item->quantity }}" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control item-price"
                                                                name="items[{{ $index }}][unit_price]"
                                                                min="0" step="0.01"
                                                                value="{{ $item->unit_price }}" required>
                                                        </td>
                                                        <td class="item-total">{{ number_format($item->total_price, 2, ',', '.') }}
                                                            DH</td>
                                                        <td>
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger remove-item"
                                                                data-row="item_{{ $item->order_item_id }}_{{ $index }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                                    <td><strong
                                                            id="order-total">{{ number_format($order->total_amount, 2, ',', '.') }}
                                                            DH</strong></td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-primary" id="add-item">
                                                <i class="fas fa-plus me-1"></i> Ajouter une ligne
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Section -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Règlement</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Les paiements existants sont conservés. Vous pouvez ajouter de nouveaux paiements.
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="payments-table">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="20%">Méthode</th>
                                                    <th width="15%">Montant (DH)</th>
                                                    <th width="15%">Date</th>
                                                    <th width="25%">Détails</th>
                                                    <th width="10%">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="payments-body">
                                                <!-- New payment rows will be added here -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2" class="text-end"><strong>Total Payé
                                                            (existant):</strong></td>
                                                    <td><strong id="existing-paid"
                                                            data-value="{{ $order->paid_amount }}">{{ number_format($order->paid_amount, 2, ',', '.') }}
                                                            DH</strong></td>
                                                    <td colspan="3"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-end"><strong>Total Payé
                                                            (nouveau):</strong></td>
                                                    <td><strong id="total-paid">0.00 DH</strong></td>
                                                    <td colspan="3"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-end"><strong>Total Général:</strong>
                                                    </td>
                                                    <td><strong
                                                            id="total-paid-all">{{ number_format($order->paid_amount, 2, ',', '.') }}
                                                            DH</strong></td>
                                                    <td colspan="3"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-end"><strong>Crédit utilisé:</strong>
                                                    </td>
                                                    <td><strong id="credit-used">0.00 DH</strong></td>
                                                    <td colspan="3" id="credit-info"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-end"><strong>Reste à Payer (sera sur
                                                            crédit):</strong>
                                                    </td>
                                                    <td><strong
                                                            id="remaining-amount">{{ number_format($order->total_amount - $order->paid_amount, 2, ',', '.') }}
                                                            DH</strong></td>
                                                    <td colspan="3" id="payment-breakdown"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-end"><strong>Statut:</strong></td>
                                                    <td id="payment-status-display">
                                                        @php
                                                            $badges = [
                                                                'pending' => 'bg-danger',
                                                                'partial' => 'bg-warning',
                                                                'paid' => 'bg-success',
                                                            ];
                                                            $labels = [
                                                                'pending' => 'Non Payé',
                                                                'partial' => 'Avance',
                                                                'paid' => 'Payé',
                                                            ];
                                                        @endphp
                                                        <span
                                                            class="badge {{ $badges[$order->payment_status] ?? 'bg-secondary' }} p-2">
                                                            {{ $labels[$order->payment_status] ?? $order->payment_status }}
                                                        </span>
                                                    </td>
                                                    <td colspan="3"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-success" id="add-payment">
                                                <i class="fas fa-plus me-1"></i> Ajouter un nouveau règlement
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Display existing payments if any -->
                            @if ($order->payments->count() > 0)
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Paiements Existants</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Méthode</th>
                                                        <th>Montant</th>
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
                                                                        <span class="badge bg-purple"
                                                                            style="background-color: #6f42c1;">Avance</span>
                                                                    @break
                                                                @endswitch
                                                            </td>
                                                            <td>{{ number_format($payment->display_amount, 2, ',', '.') }} DH</td>
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
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Notes supplémentaires...">{{ $order->notes }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-1"></i> Mettre à jour
                                </button>
                                <a href="{{ route('sales.orders.show', $order->order_id) }}" class="btn btn-info">
                                    <i class="fas fa-eye me-1"></i> Voir
                                </a>
                                <a href="{{ route('sales.orders.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Credit Exceeded Modal -->
    <div class="modal fade" id="creditExceededModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">Limite de crédit dépassée</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="creditExceededMessage"></p>
                    <div id="creditExceededDetails" class="alert alert-info mt-3"></div>
                    <p class="mt-3">Options disponibles:</p>
                    <div class="list-group">
                        <button type="button" class="list-group-item list-group-item-action"
                            onclick="proceedWithExcess()">
                            <i class="fas fa-check-circle text-success me-2"></i> Continuer avec dépassement
                            <small class="d-block text-muted">Dépassement autorisé pour cette commande</small>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action"
                            onclick="requestPaymentFirst()">
                            <i class="fas fa-money-bill-wave text-primary me-2"></i> Exiger un paiement avant
                            <small class="d-block text-muted">Le client doit payer le dépassement</small>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action"
                            onclick="reduceOrderAmount()">
                            <i class="fas fa-minus-circle text-warning me-2"></i> Réduire le montant de la commande
                            <small class="d-block text-muted">Modifier les articles pour respecter la limite</small>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler la commande</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .duplicate-item-highlight {
            animation: duplicateItemPulse 1.4s ease;
        }

        @keyframes duplicateItemPulse {
            0% {
                box-shadow: inset 0 0 0 2px rgba(220, 53, 69, 0);
                background-color: transparent;
            }

            15% {
                box-shadow: inset 0 0 0 2px rgba(220, 53, 69, 1);
                background-color: rgba(220, 53, 69, 0.12);
            }

            85% {
                box-shadow: inset 0 0 0 2px rgba(220, 53, 69, 1);
                background-color: rgba(220, 53, 69, 0.12);
            }

            100% {
                box-shadow: inset 0 0 0 2px rgba(220, 53, 69, 0);
                background-color: transparent;
            }
        }

        .payment-details {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
        }

        .check-fields {
            border-left: 3px solid #17a2b8;
            padding-left: 10px;
        }

        .transfer-fields {
            border-left: 3px solid #007bff;
            padding-left: 10px;
        }

        .traite-fields {
            border-left: 3px solid #28a745;
            padding-left: 10px;
        }

        .cash-fields {
            border-left: 3px solid #ffc107;
            padding-left: 10px;
        }

        .advance-fields {
            border-left: 3px solid #6f42c1;
            padding-left: 10px;
        }

        .client-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            border-right: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0 15px;
        }

        .info-item:last-child {
            border-right: none;
        }

        .info-label {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .info-value {
            font-size: 1.2rem;
            font-weight: bold;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for client
            $('#client_id').select2({
                language: "fr",
                placeholder: "Sélectionner un client...",
                allowClear: true,
                width: '100%'
            });

            let itemCounter = {{ $order->items->count() }};
            let paymentCounter = 0;
            let clientCreditData = null;
            let bypassCredit = false;

            // Store original item prices to avoid recalculation
            let originalItemPrices = {};

            // Store original item data from the database
            @foreach ($order->items as $item)
                originalItemPrices['item_{{ $item->order_item_id }}_{{ $loop->index }}'] = {
                    price: {{ $item->unit_price }},
                    quantity: {{ $item->quantity }},
                    total: {{ $item->total_price }}
                };
            @endforeach

            // Initialize existing rows with their stored prices
            $('.item-total').each(function() {
                let rowId = $(this).closest('tr').attr('id');
                if (originalItemPrices[rowId]) {
                    $(this).text(originalItemPrices[rowId].total.toFixed(2) + ' DH');
                }
            });

            // Initialize Select2 for all existing rows
            $('.item-select').each(function() {
                let $this = $(this);
                let rowId = $this.closest('tr').attr('id');

                $this.select2({
                    language: "fr",
                    placeholder: "Sélectionner un article...",
                    allowClear: true,
                    width: '100%'
                });

                let itemId = $(`#${rowId} .item-id`).val();
                let familyId = $(`#${rowId} .family-id`).val();

                if (itemId) {
                    let type = $(`#${rowId} .item-type`).val();
                    loadProducts(rowId, type, true);
                }
            });

            // Check client credit and balance on page load
            let clientId = $('#client_id').val();
            if (clientId) {
                checkClientCreditStatus(clientId);
            }

            // Add item button
            $('#add-item').click(function() {
                addItemRow();
            });

            // Add payment button
            $('#add-payment').click(function() {
                addPaymentRow();
            });

            function checkClientCreditStatus(clientId) {
                $.ajax({
                    url: "{{ route('clients.credit-status', ['id' => ':clientId']) }}".replace(':clientId',
                        clientId),
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            clientCreditData = response.data;
                            displayClientCreditInfo(response.data);
                            $('.advance-option').prop('disabled', !response.data.has_advance);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error checking credit:', xhr);
                    }
                });
            }

            function displayClientCreditInfo(data) {
                let html = '<div class="client-info-card">';
                html += '<div class="row">';
                html += '<div class="col-md-4 info-item">';
                html += '<div class="info-label">Crédit (Plafond)</div>';
                if (data.has_credit) {
                    html += '<div class="info-value">' + data.credit_formatted + '</div>';
                    html += '<small>Limite: ' + data.credit_limit + ' DH</small>';
                    html += '<div class="mt-2">';
                    html += '<small>Utilisé: ' + data.credit_usage + ' DH</small>';
                    html += '<div class="progress mt-1" style="height: 5px;">';
                    let percentage = Math.min(100, (data.credit_usage / data.credit_limit) * 100);
                    let barClass = percentage >= 90 ? 'bg-danger' : (percentage >= 70 ? 'bg-warning' :
                        'bg-success');
                    html += '<div class="progress-bar ' + barClass + '" ';
                    html += 'role="progressbar" style="width: ' + percentage + '%"></div>';
                    html += '</div></div>';
                } else {
                    html += '<div class="info-value">Non défini</div>';
                }
                html += '</div>';
                html += '<div class="col-md-4 info-item">';
                html += '<div class="info-label">Solde</div>';
                if (data.balance > 0) {
                    html += '<div class="info-value text-success">+' + data.balance + ' DH</div>';
                    html += '<small><i class="fas fa-arrow-up me-1"></i>Trop-perçu (Nous devons)</small>';
                    html += '<div class="mt-2 small">Disponible: ' + data.advance_formatted + '</div>';
                } else if (data.balance < 0) {
                    html += '<div class="info-value text-danger">' + data.balance + ' DH</div>';
                    html += '<small><i class="fas fa-arrow-down me-1"></i>Impayé (Client doit)</small>';
                    html += '<div class="mt-2 small">Total dû: ' + data.debt_formatted + '</div>';
                } else {
                    html += '<div class="info-value">0,00 DH</div>';
                    html += '<small>Soldé</small>';
                }
                html += '</div>';
                html += '<div class="col-md-4">';
                html += '<div class="info-label">Résumé</div>';
                html += '<div class="mt-2">';
                if (data.has_advance) {
                    html += '<span class="badge bg-success me-1">';
                    html += '<i class="fas fa-wallet me-1"></i>Avance: ' + data.advance_formatted;
                    html += '</span>';
                }
                if (data.has_debt) {
                    html += '<span class="badge bg-danger">';
                    html += '<i class="fas fa-exclamation-triangle me-1"></i>Dette: ' + data.debt_formatted;
                    html += '</span>';
                }
                if (!data.has_advance && !data.has_debt) {
                    html += '<span class="badge bg-secondary">';
                    html += '<i class="fas fa-check-circle me-1"></i>Compte soldé';
                    html += '</span>';
                }
                html += '</div></div>';
                html += '</div></div>';
                $('#client-credit-info').html(html);
            }

            function addItemRow() {
                let rowId = 'row_' + Date.now() + '_' + itemCounter;
                let index = itemCounter;

                let typeOptions = `
                    <option value="">Type</option>
                    <option value="raw_material">Matière Première</option>
                    <option value="production">Production</option>
                    <option value="decoupage">Découpage</option>
                    <option value="finale" selected>Vente</option>
                `;

                let row = `
                    <tr id="${rowId}">
                        <td>${itemCounter + 1}</td>
                        <td>
                            <select class="form-control item-type" data-row="${rowId}" required>
                                ${typeOptions}
                            </select>
                        </td>
                        <td>
                            <select class="form-control item-select" data-row="${rowId}" style="width:100%;" required>
                                <option value="">Sélectionnez un client d'abord</option>
                            </select>
                            <input type="hidden" class="item-id" name="items[${index}][item_id]">
                            <input type="hidden" class="item-name" name="items[${index}][name]">
                            <input type="hidden" class="item-type-input" name="items[${index}][type]">
                            <input type="hidden" class="family-id" name="items[${index}][family_id]">
                            <input type="hidden" class="family-name" name="items[${index}][family_name]">
                        </td>
                        <td>
                            <input type="number" class="form-control item-quantity"
                                name="items[${index}][quantity]" min="0.0001" step="0.0001"
                                value="1" required disabled>
                        </td>
                        <td>
                            <input type="number" class="form-control item-price"
                                name="items[${index}][unit_price]" min="0" step="0.01"
                                value="0" required disabled>
                        </td>
                        <td class="item-total">0.00 DH</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-item" data-row="${rowId}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#items-body').append(row);

                $(`#${rowId} .item-select`).select2({
                    language: "fr",
                    placeholder: "Sélectionner un article...",
                    allowClear: true,
                    width: '100%'
                });

                if ($('#client_id').val()) {
                    loadProducts(rowId, 'finale');
                }

                itemCounter++;
            }

            $(document).on('change', '.item-type', function() {
                let rowId = $(this).data('row');
                let type = $(this).val();
                let clientId = $('#client_id').val();

                if (!clientId) {
                    showToast('warning', 'Veuillez d\'abord sélectionner un client');
                    $(this).val('finale');
                    return;
                }

                if (type) {
                    loadProducts(rowId, type);
                }
            });

            function loadProducts(rowId, type, setExistingValue = false) {
                let select = $(`#${rowId} .item-select`);

                select.html('<option value="">Chargement...</option>').prop('disabled', true);
                select.trigger('change');

                let url = type === 'raw_material' ?
                    "{{ route('raw-materials.getListForSale') }}" :
                    "{{ route('products.by-type', '') }}/" + type;

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        let products = response.data || response;

                        select.empty().prop('disabled', false);
                        select.append('<option value="">Sélectionner un article</option>');

                        if (products && products.length > 0) {
                            products.forEach(function(product) {
                                if (product.has_families && product.families && product.families
                                    .length > 0) {
                                    product.families.forEach(function(family) {
                                        let value = product.id + '_' + family.id;
                                        let text = product.name + ' - ' + family.name;
                                        if (product.code) text += ' (' + product.code +
                                            ')';

                                        let option = $('<option></option>')
                                            .attr('value', value)
                                            .attr('data-product-id', product.id)
                                            .attr('data-product-name', product.name)
                                            .attr('data-product-code', product.code ||
                                                '')
                                            .attr('data-family-id', family.id)
                                            .attr('data-family-name', family.name)
                                            .attr('data-family-price-client', family
                                                .prix_client || 0)
                                            .attr('data-family-price-grossiste', family
                                                .prix_grossiste || 0)
                                            .attr('data-family-price-commercial', family
                                                .prix_commercial || 0)
                                            .attr('data-family-price-special', family
                                                .prix_special || 0)
                                            .attr('data-volume', product.volume || 0)
                                            .attr('data-code', product.code || '')
                                            .text(text);

                                        select.append(option);
                                    });
                                } else {
                                    let text = product.name;
                                    if (product.code) text += ' (' + product.code + ')';

                                    let option = $('<option></option>')
                                        .attr('value', product.id)
                                        .attr('data-name', product.name)
                                        .attr('data-code', product.code || '')
                                        .attr('data-price', product.price || 0)
                                        .attr('data-price-client', product.prix_client || 0)
                                        .attr('data-price-grossiste', product.prix_grossiste || 0)
                                        .attr('data-price-commercial', product.prix_commercial || 0)
                                        .attr('data-price-special', product.prix_special || 0)
                                        .attr('data-volume', product.volume || 0)
                                        .attr('data-product-name', product.name)
                                        .attr('data-product-code', product.code || '')
                                        .text(text);

                                    select.append(option);
                                }
                            });
                        } else {
                            select.append('<option value="">Aucun article disponible</option>');
                        }

                        select.trigger('change');
                        $(`#${rowId} .item-type-input`).val(type);

                        if (setExistingValue) {
                            let itemId = $(`#${rowId} .item-id`).val();
                            let familyId = $(`#${rowId} .family-id`).val();

                            if (itemId) {
                                let valueToSet = familyId ? itemId + '_' + familyId : itemId;
                                select.data('skipDuplicateCheck', true);
                                select.val(valueToSet).trigger('change');

                                // CRITICAL FIX: Restore original price from database, don't recalculate
                                if (originalItemPrices[rowId]) {
                                    let originalPrice = originalItemPrices[rowId].price;
                                    let originalQuantity = originalItemPrices[rowId].quantity;

                                    $(`#${rowId} .item-price`).val(originalPrice);
                                    $(`#${rowId} .item-quantity`).val(originalQuantity);
                                    $(`#${rowId} .item-quantity`).prop('disabled', false);
                                    $(`#${rowId} .item-price`).prop('disabled', false);

                                    calculateRowTotal(rowId);
                                    updateTotal();
                                }
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading products:', xhr);
                        select.empty().append('<option value="">Erreur de chargement</option>').prop(
                            'disabled', false);
                        select.trigger('change');
                        showToast('error', 'Erreur lors du chargement des articles');
                    }
                });
            }

            function findDuplicateItemRow(currentRowId, itemId, familyId) {
                let duplicateRowId = null;
                $('#items-body tr').each(function() {
                    if ($(this).attr('id') === currentRowId) return true;
                    let existingId = $(this).find('.item-id').val();
                    let existingFamilyId = $(this).find('.family-id').val() || '';
                    if (existingId && String(existingId) === String(itemId) &&
                        existingFamilyId === String(familyId || '')) {
                        duplicateRowId = $(this).attr('id');
                        return false;
                    }
                });
                return duplicateRowId;
            }

            function isItemAlreadySelected(currentRowId, itemId, familyId) {
                return !!findDuplicateItemRow(currentRowId, itemId, familyId);
            }

            function highlightDuplicateRow(rowId) {
                let $row = $(`#${rowId}`);
                if (!$row.length) return;

                $('html, body').animate({
                    scrollTop: $row.offset().top - 150
                }, 400);

                let $cells = $row.find('td');
                $cells.removeClass('duplicate-item-highlight');
                void $row[0].offsetWidth;
                $cells.addClass('duplicate-item-highlight');
                setTimeout(function() {
                    $cells.removeClass('duplicate-item-highlight');
                }, 1400);
            }

            $(document).on('change', '.item-select', function() {
                let rowId = $(this).data('row');
                let selected = $(this).find(':selected');
                let value = $(this).val();
                let skipDuplicateCheck = $(this).data('skipDuplicateCheck');
                $(this).removeData('skipDuplicateCheck');

                if (!value) {
                    $(`#${rowId} .item-quantity`).prop('disabled', true);
                    $(`#${rowId} .item-price`).prop('disabled', true);
                    return;
                }

                let clientType = $('#client_id option:selected').data('client-type') || 'client';
                let itemType = $(`#${rowId} .item-type`).val();

                let checkProductId = value.includes('_') ? value.split('_')[0] : value;
                let checkFamilyId = value.includes('_') ? value.split('_')[1] : '';

                let duplicateRowId = skipDuplicateCheck ? null : findDuplicateItemRow(rowId,
                    checkProductId, checkFamilyId);
                if (duplicateRowId) {
                    showToast('warning',
                        'Cet article est déjà sélectionné dans la liste. Veuillez en choisir un autre.'
                    );
                    $(this).val('').trigger('change');
                    $(`#${rowId} .item-id`).val('');
                    $(`#${rowId} .item-name`).val('');
                    $(`#${rowId} .family-id`).val('');
                    $(`#${rowId} .family-name`).val('');
                    $(`#${rowId} .item-price`).val(0).prop('disabled', true);
                    $(`#${rowId} .item-quantity`).prop('disabled', true);
                    calculateRowTotal(rowId);
                    updateTotal();
                    highlightDuplicateRow(duplicateRowId);
                    return;
                }

                // Check if this is an existing item with a custom price
                let currentPrice = parseFloat($(`#${rowId} .item-price`).val()) || 0;
                let shouldUpdatePrice = true;

                if (originalItemPrices[rowId] && originalItemPrices[rowId].price !== currentPrice) {
                    // Price has been manually modified
                    if (!confirm(
                            'Le prix a été modifié manuellement. Voulez-vous le remplacer par le prix par défaut du produit sélectionné ?'
                        )) {
                        shouldUpdatePrice = false;
                    }
                }

                if (value.includes('_')) {
                    let parts = value.split('_');
                    let productId = parts[0];
                    let familyId = parts[1];

                    let price = 0;
                    switch (clientType) {
                        case 'grossiste':
                            price = parseFloat(selected.data('family-price-grossiste')) || 0;
                            break;
                        case 'commerciale':
                            price = parseFloat(selected.data('family-price-commercial')) || 0;
                            break;
                        case 'special':
                            price = parseFloat(selected.data('family-price-special')) || 0;
                            break;
                        default:
                            price = parseFloat(selected.data('family-price-client')) || 0;
                    }

                    price = Math.ceil(price);

                    $(`#${rowId} .item-id`).val(productId);
                    $(`#${rowId} .item-name`).val(selected.data('product-name'));
                    $(`#${rowId} .family-id`).val(familyId);
                    $(`#${rowId} .family-name`).val(selected.data('family-name'));

                    if (shouldUpdatePrice) {
                        $(`#${rowId} .item-price`).val(price);
                    }
                } else {
                    let price = 0;

                    if (itemType === 'raw_material') {
                        switch (clientType) {
                            case 'grossiste':
                                price = parseFloat(selected.data('price-grossiste')) || 0;
                                break;
                            case 'commerciale':
                                price = parseFloat(selected.data('price-commercial')) || 0;
                                break;
                            case 'special':
                                price = parseFloat(selected.data('price-special')) || 0;
                                break;
                            default:
                                price = parseFloat(selected.data('price-client')) || 0;
                        }
                    } else {
                        price = parseFloat(selected.data('price')) || 0;
                    }

                    price = Math.ceil(price);

                    $(`#${rowId} .item-id`).val(value);
                    $(`#${rowId} .item-name`).val(selected.data('name') || selected.data('product-name'));
                    $(`#${rowId} .family-id`).val('');
                    $(`#${rowId} .family-name`).val('');

                    if (shouldUpdatePrice) {
                        $(`#${rowId} .item-price`).val(price);
                    }
                }

                $(`#${rowId} .item-price`).prop('disabled', false);
                $(`#${rowId} .item-quantity`).prop('disabled', false);

                calculateRowTotal(rowId);
                updateTotal();
            });

            $(document).on('input', '.item-quantity, .item-price', function() {
                let rowId = $(this).closest('tr').attr('id');
                calculateRowTotal(rowId);
                updateTotal();
            });

            function calculateRowTotal(rowId) {
                let qty = parseFloat($(`#${rowId} .item-quantity`).val()) || 0;
                let price = parseFloat($(`#${rowId} .item-price`).val()) || 0;
                let total = qty * price;
                $(`#${rowId} .item-total`).text(total.toFixed(2) + ' DH');
            }

            function updateTotal() {
                let total = 0;
                $('.item-total').each(function() {
                    let val = parseFloat($(this).text().replace(' DH', '')) || 0;
                    total += val;
                });
                $('#order-total').text(total.toFixed(2) + ' DH');
                updatePaymentSummary();
            }

            $(document).on('click', '.remove-item', function() {
                let rowId = $(this).data('row');
                $(`#${rowId}`).remove();
                updateRowNumbers();
                updateTotal();
            });

            function updateRowNumbers() {
                $('#items-body tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                    $(this).find('.item-id').attr('name', `items[${index}][item_id]`);
                    $(this).find('.item-name').attr('name', `items[${index}][name]`);
                    $(this).find('.item-type-input').attr('name', `items[${index}][type]`);
                    $(this).find('.item-quantity').attr('name', `items[${index}][quantity]`);
                    $(this).find('.item-price').attr('name', `items[${index}][unit_price]`);
                    $(this).find('.family-id').attr('name', `items[${index}][family_id]`);
                    $(this).find('.family-name').attr('name', `items[${index}][family_name]`);
                });
            }

            $('#client_id').on('change', function() {
                let clientId = $(this).val();

                if (clientId) {
                    checkClientCreditStatus(clientId);

                    $('#items-body tr').each(function() {
                        let rowId = $(this).attr('id');
                        let type = $(this).find('.item-type').val();
                        if (type) {
                            loadProducts(rowId, type, true);
                        }
                    });
                } else {
                    $('#client-credit-info').empty();
                    $('.item-select').each(function() {
                        $(this).empty().append(
                                '<option value="">Sélectionnez un client d\'abord</option>')
                            .trigger('change');
                    });
                }
            });

            // Payment functions
            function addPaymentRow() {
                let rowId = 'payment_' + Date.now() + '_' + paymentCounter;
                let index = paymentCounter;

                let row = `
                    <tr id="${rowId}" data-payment-index="${index}">
                        <td>${paymentCounter + 1}</td>
                        <td>
                            <select class="form-control payment-method" data-row="${rowId}" required>
                                <option value="">Méthode</option>
                                <option value="cash">Espèces</option>
                                <option value="check">Chèque</option>
                                <option value="transfer">Virement</option>
                                <option value="traite">Traite</option>
                                <option value="advance" class="advance-option" ${!clientCreditData?.has_advance ? 'disabled' : ''}>Solde client (Avance)</option>
                            </select>
                            <input type="hidden" class="payment-method-input" name="payments[${index}][method]">
                        </td>
                        <td>
                            <input type="number" class="form-control payment-amount"
                                   name="payments[${index}][amount]" min="0.01" step="0.01" required>
                        </td>
                        <td>
                            <input type="date" class="form-control payment-date"
                                   name="payments[${index}][date]" value="{{ date('Y-m-d') }}" required>
                        </td>
                        <td>
                            <div class="payment-details" id="details-${rowId}"></div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-payment" data-row="${rowId}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#payments-body').append(row);
                paymentCounter++;
            }

            $(document).on('change', '.payment-method', function() {
                let rowId = $(this).data('row');
                let method = $(this).val();
                let index = $(`#${rowId}`).data('payment-index');

                $(`#${rowId} .payment-method-input`).val(method);

                let html = '';
                if (method === 'check') {
                    html = `
                        <div class="check-fields">
                            <input type="text" class="form-control form-control-sm mb-1" name="payments[${index}][check_number]" placeholder="N° Chèque" required>
                            <input type="text" class="form-control form-control-sm mb-1" name="payments[${index}][bank_name]" placeholder="Banque" required>
                            <input type="text" class="form-control form-control-sm mb-1" name="payments[${index}][account_holder]" placeholder="Titulaire" required>
                            <input type="date" class="form-control form-control-sm mb-1" name="payments[${index}][due_date]" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                            <input type="file" class="form-control form-control-sm payment-file" name="payments[${index}][document]" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    `;
                } else if (method === 'transfer') {
                    html = `
                        <div class="transfer-fields">
                            <input type="text" class="form-control form-control-sm mb-1" name="payments[${index}][transfer_reference]" placeholder="Référence" required>
                            <input type="text" class="form-control form-control-sm mb-1" name="payments[${index}][bank_name]" placeholder="Banque" required>
                            <input type="text" class="form-control form-control-sm mb-1" name="payments[${index}][account_number]" placeholder="Compte" required>
                            <input type="file" class="form-control form-control-sm payment-file" name="payments[${index}][document]" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    `;
                } else if (method === 'traite') {
                    html = `
                        <div class="traite-fields">
                            <input type="text" class="form-control form-control-sm mb-1" name="payments[${index}][traite_number]" placeholder="N° Traite" required>
                            <input type="text" class="form-control form-control-sm mb-1" name="payments[${index}][drawee]" placeholder="Tiré" required>
                            <input type="date" class="form-control form-control-sm mb-1" name="payments[${index}][due_date]" value="{{ date('Y-m-d', strtotime('+60 days')) }}" required>
                            <input type="file" class="form-control form-control-sm payment-file" name="payments[${index}][document]" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    `;
                } else if (method === 'cash') {
                    html = `
                        <div class="cash-fields">
                            <input type="text" class="form-control form-control-sm mb-1" name="payments[${index}][cash_reference]" placeholder="Référence">
                            <input type="file" class="form-control form-control-sm payment-file" name="payments[${index}][document]" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    `;
                } else if (method === 'advance') {
                    let maxAdvance = clientCreditData?.available_advance || 0;
                    html = `
                        <div class="advance-fields">
                            <div class="mb-2">
                                <div class="alert alert-success py-2 mb-2">
                                    <strong>Solde disponible:</strong>
                                    <span class="text-success fw-bold">${maxAdvance.toFixed(2)} DH</span>
                                </div>
                                <input type="hidden" class="max-advance-amount" value="${maxAdvance}">
                                ${maxAdvance <= 0 ? `<div class="alert alert-warning py-1 mb-2 small"><i class="fas fa-exclamation-triangle"></i> Aucun solde disponible</div>` : ''}
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Référence (optionnel)</label>
                                <input type="text" class="form-control form-control-sm"
                                       name="payments[${index}][advance_reference]"
                                       placeholder="Ex: Utilisation solde">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Document justificatif</label>
                                <input type="file" class="form-control form-control-sm payment-file"
                                       name="payments[${index}][document]" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <small class="text-muted d-block">
                                <i class="fas fa-info-circle"></i>
                                Le solde sera déduit de l'avance du client
                            </small>
                        </div>
                    `;
                }

                $(`#details-${rowId}`).html(html);
            });

            $(document).on('input', '.payment-amount', function() {
                let rowId = $(this).closest('tr').attr('id');
                let method = $(`#${rowId} .payment-method`).val();

                if (method === 'advance') {
                    let amount = parseFloat($(this).val()) || 0;
                    let maxAdvance = parseFloat($(`#${rowId} .max-advance-amount`).val()) || 0;

                    if (amount > maxAdvance) {
                        $(this).addClass('is-invalid');
                        if (!$(`#${rowId} .advance-error`).length) {
                            $(this).after(
                                '<div class="invalid-feedback advance-error">Le montant ne peut pas dépasser le solde disponible (' +
                                maxAdvance.toFixed(2) + ' DH)</div>'
                            );
                        }
                    } else {
                        $(this).removeClass('is-invalid');
                        $(`#${rowId} .advance-error`).remove();
                    }
                }

                updatePaymentSummary();
            });

            function updatePaymentSummary() {
                let orderTotal = parseFloat($('#order-total').text().replace(/[^\d.-]/g, '')) || 0;
                let existingPaid = parseFloat($('#existing-paid').data('value')) || 0;
                let newPaid = 0;
                let advanceUsed = 0;

                $('.payment-amount').each(function() {
                    let amount = parseFloat($(this).val()) || 0;
                    newPaid += amount;

                    let method = $(this).closest('tr').find('.payment-method').val();
                    if (method === 'advance') {
                        advanceUsed += amount;
                    }
                });

                let totalPaid = existingPaid + newPaid;
                let remaining = orderTotal - totalPaid;
                let creditUsed = remaining > 0 ? remaining : 0;

                $('#total-paid').text(newPaid.toFixed(2) + ' DH');
                $('#total-paid-all').text(totalPaid.toFixed(2) + ' DH');
                $('#credit-used').text(creditUsed.toFixed(2) + ' DH');
                $('#remaining-amount').text(remaining.toFixed(2) + ' DH');

                if (clientCreditData && clientCreditData.has_credit) {
                    let creditAvailable = clientCreditData.credit_limit - clientCreditData.credit_usage;

                    if (creditUsed > 0) {
                        if (creditUsed <= creditAvailable) {
                            $('#credit-info').html(`
                                <span class="text-success">
                                    <i class="fas fa-check-circle"></i>
                                    Crédit disponible après modification: ${(creditAvailable - creditUsed).toFixed(2)} DH
                                </span>
                            `);
                        } else {
                            let exceedsBy = creditUsed - creditAvailable;
                            $('#credit-info').html(`
                                <span class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Dépassement de crédit: ${exceedsBy.toFixed(2)} DH
                                </span>
                            `);
                        }
                    } else {
                        $('#credit-info').html(`
                            <span class="text-info">
                                <i class="fas fa-info-circle"></i>
                                Crédit disponible: ${creditAvailable.toFixed(2)} DH
                            </span>
                        `);
                    }
                } else {
                    $('#credit-info').empty();
                }

                let status = '';
                if (orderTotal === 0) {
                    status = '<span class="badge bg-warning">Non déterminé</span>';
                } else if (totalPaid <= 0) {
                    status = '<span class="badge bg-danger">Non Payé (sur crédit)</span>';
                } else if (totalPaid >= orderTotal - 0.01) {
                    status = '<span class="badge bg-success">Payé</span>';
                } else {
                    let percentage = ((totalPaid / orderTotal) * 100).toFixed(1);
                    status = '<span class="badge bg-info">Avance (' + percentage + '%)</span>';
                }
                $('#payment-status-display').html(status);

                let breakdownHtml = '';
                if (creditUsed > 0) {
                    breakdownHtml +=
                        `<div><small>Crédit utilisé pour cette vente: ${creditUsed.toFixed(2)} DH</small></div>`;
                }
                if (advanceUsed > 0) {
                    breakdownHtml += `<div><small>Solde utilisé: ${advanceUsed.toFixed(2)} DH</small></div>`;
                }
                $('#payment-breakdown').html(breakdownHtml);
            }

            $(document).on('click', '.remove-payment', function() {
                $(this).closest('tr').remove();
                updatePaymentIndices();
                updatePaymentSummary();
            });

            function updatePaymentIndices() {
                $('#payments-body tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                    $(this).attr('data-payment-index', index);
                    $(this).find('.payment-method-input').attr('name', `payments[${index}][method]`);
                    $(this).find('.payment-amount').attr('name', `payments[${index}][amount]`);
                    $(this).find('.payment-date').attr('name', `payments[${index}][date]`);
                    $(this).find('input[name*="check_number"]').attr('name',
                        `payments[${index}][check_number]`);
                    $(this).find('input[name*="bank_name"]').attr('name', `payments[${index}][bank_name]`);
                    $(this).find('input[name*="account_holder"]').attr('name',
                        `payments[${index}][account_holder]`);
                    $(this).find('input[name*="due_date"]').attr('name', `payments[${index}][due_date]`);
                    $(this).find('input[name*="transfer_reference"]').attr('name',
                        `payments[${index}][transfer_reference]`);
                    $(this).find('input[name*="account_number"]').attr('name',
                        `payments[${index}][account_number]`);
                    $(this).find('input[name*="traite_number"]').attr('name',
                        `payments[${index}][traite_number]`);
                    $(this).find('input[name*="drawee"]').attr('name', `payments[${index}][drawee]`);
                    $(this).find('textarea[name*="drawee_address"]').attr('name',
                        `payments[${index}][drawee_address]`);
                    $(this).find('input[name*="cash_reference"]').attr('name',
                        `payments[${index}][cash_reference]`);
                    $(this).find('input[name*="advance_reference"]').attr('name',
                        `payments[${index}][advance_reference]`);
                    $(this).find('.payment-file').attr('name', `payments[${index}][document]`);
                });
            }

            $(document).on('click', '.delete-payment', function() {
                let paymentId = $(this).data('payment-id');
                let orderId = $('#order_id').val();

                if (!confirm('Êtes-vous sûr de vouloir supprimer ce paiement ?')) {
                    return;
                }

                let $row = $(this).closest('tr');
                let $btn = $(this);
                let originalHtml = $btn.html();

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('sales.orders.delete-payment', ['orderId' => ':orderId', 'paymentId' => ':paymentId']) }}"
                        .replace(':orderId', orderId)
                        .replace(':paymentId', paymentId),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                $('#existing-paid').data('value', parseFloat(response
                                    .order.paid_amount));
                                $('#existing-paid').text(response.order.paid_amount +
                                    ' DH');
                                updatePaymentSummary();
                                showToast('success', response.message);
                            });
                        } else {
                            showToast('error', response.message);
                            $btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = xhr.responseJSON?.message ||
                            'Erreur lors de la suppression';
                        showToast('error', errorMessage);
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            function checkClientCreditForUpdate(orderTotal, totalPaid) {
                return new Promise((resolve, reject) => {
                    if (!clientCreditData) {
                        resolve({
                            can_proceed: true
                        });
                        return;
                    }

                    let remaining = orderTotal - totalPaid;

                    if (remaining <= 0) {
                        resolve({
                            can_proceed: true
                        });
                        return;
                    }

                    if (!clientCreditData.has_credit) {
                        resolve({
                            can_proceed: true,
                            warning: 'Ce client n\'a pas de limite de crédit'
                        });
                        return;
                    }

                    // Calculate current credit usage from this order
                    // Get the current unpaid amount of this order before update
                    let currentOrderUnpaid = parseFloat($('#existing-paid').data('value')) || 0;
                    let currentOrderRemaining = parseFloat($('#order-total').text().replace(/[^\d.-]/g,
                        '')) || 0;
                    currentOrderRemaining = currentOrderRemaining - currentOrderUnpaid;

                    // The new credit needed is the new remaining amount
                    let newCreditNeeded = remaining;

                    // Credit already used by this order (from the database)
                    let existingOrderCredit = currentOrderRemaining > 0 ? currentOrderRemaining : 0;

                    // Calculate how much additional credit we need for this order
                    let additionalCreditNeeded = newCreditNeeded - existingOrderCredit;

                    // If we're reducing credit usage, no problem
                    if (additionalCreditNeeded <= 0) {
                        resolve({
                            can_proceed: true
                        });
                        return;
                    }

                    // Check if client has enough credit including other orders
                    let totalCreditNeeded = clientCreditData.credit_usage + additionalCreditNeeded;

                    console.log('Credit Check:', {
                        existingOrderCredit,
                        newCreditNeeded,
                        additionalCreditNeeded,
                        currentCreditUsage: clientCreditData.credit_usage,
                        creditLimit: clientCreditData.credit_limit,
                        totalNeeded: totalCreditNeeded
                    });

                    if (totalCreditNeeded <= clientCreditData.credit_limit || bypassCredit) {
                        resolve({
                            can_proceed: true
                        });
                    } else {
                        let exceedsBy = totalCreditNeeded - clientCreditData.credit_limit;
                        let message = `Crédit insuffisant pour cette commande.`;
                        let details = `
                <table class="table table-sm">
                    <tr><td>Limite de crédit:</td><td class="text-end"><strong>${clientCreditData.credit_limit.toFixed(2)} DH</strong></td></tr>
                    <tr><td>Crédit déjà utilisé:</td><td class="text-end"><strong>${clientCreditData.credit_usage.toFixed(2)} DH</strong></td></tr>
                    <tr><td>Crédit disponible:</td><td class="text-end"><strong class="${(clientCreditData.credit_limit - clientCreditData.credit_usage) > 0 ? 'text-success' : 'text-danger'}">${(clientCreditData.credit_limit - clientCreditData.credit_usage).toFixed(2)} DH</strong></td></tr>
                    <tr><td>Nouveau crédit nécessaire:</td><td class="text-end"><strong>${newCreditNeeded.toFixed(2)} DH</strong></td></tr>
                    <tr class="table-danger"><td>Crédit total nécessaire:</td><td class="text-end"><strong class="text-danger">${totalCreditNeeded.toFixed(2)} DH</strong></td></tr>
                    <tr class="table-danger"><td>Manque:</td><td class="text-end"><strong class="text-danger">${exceedsBy.toFixed(2)} DH</strong></td></tr>
                </table>
            `;
                        showCreditExceededModal(message, details, exceedsBy);
                        reject({
                            can_proceed: false
                        });
                    }
                });
            }

            function showCreditExceededModal(message, details, exceedsBy) {
                $('#creditExceededMessage').text(message);
                $('#creditExceededDetails').html(details);
                $('#creditExceededModal').modal('show');
            }

            $('#orderForm').submit(function(e) {
                e.preventDefault();

                if ($('#items-body tr').length === 0) {
                    showToast('error', 'Ajoutez au moins un article');
                    return;
                }

                if (!$('#client_id').val()) {
                    showToast('error', 'Sélectionnez un client');
                    return;
                }

                let valid = true;
                let newPaid = 0;

                $('.payment-method').each(function() {
                    let method = $(this).val();
                    if (method) {
                        let rowId = $(this).data('row');
                        let amount = parseFloat($(`#${rowId} .payment-amount`).val()) || 0;
                        newPaid += amount;

                        if (method === 'advance') {
                            let maxAdvance = parseFloat($(`#${rowId} .max-advance-amount`).val()) ||
                                0;
                            if (amount > maxAdvance) {
                                showToast('error',
                                    'Un paiement par avance dépasse le montant disponible');
                                valid = false;
                                return false;
                            }
                        }
                    }
                });

                if (!valid) return;

                let orderTotal = parseFloat($('#order-total').text().replace(/[^\d.-]/g, '')) || 0;
                let existingPaid = parseFloat($('#existing-paid').data('value')) || 0;
                let totalPaid = existingPaid + newPaid;

                checkClientCreditForUpdate(orderTotal, totalPaid).then(() => {
                    submitForm(false);
                }).catch(() => {});
            });

            function submitForm(bypass = false) {
                const submitBtn = $('#orderForm').find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Mise à jour...');

                let formData = new FormData(document.getElementById('orderForm'));

                if (bypass) {
                    formData.append('bypass_credit', '1');
                }

                let orderId = $('#order_id').val();

                $.ajax({
                    url: "{{ route('sales.orders.update', '') }}/" + orderId,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-HTTP-Method-Override': 'PUT'
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('sales.orders.show', '') }}/" + response
                                    .order_id;
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = xhr.responseJSON?.message || 'Une erreur est survenue';
                        showToast('error', errorMessage);
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            }

            window.proceedWithExcess = function() {
                $('#creditExceededModal').modal('hide');
                bypassCredit = true;
                submitForm(true);
            };

            window.requestPaymentFirst = function() {
                $('#creditExceededModal').modal('hide');
                addPaymentRow();
                let excessAmount = parseFloat($('#creditExceededModal .table-danger td:last strong').text()
                    .replace(' DH', '')) || 0;
                if (excessAmount > 0) {
                    $('.payment-amount:last').val(excessAmount.toFixed(2));
                }
                showToast('info', 'Veuillez saisir un paiement pour couvrir le dépassement');
            };

            window.reduceOrderAmount = function() {
                $('#creditExceededModal').modal('hide');
                showToast('warning', 'Veuillez modifier les articles de la commande');
            };

            $(document).on('change', '.item-quantity', function() {
                let val = parseFloat($(this).val());
                if (!isNaN(val)) {
                    $(this).val(val.toFixed(1));
                }
            });

            if ($('#client_id').val()) {
                $('#items-body tr').each(function() {
                    let rowId = $(this).attr('id');
                    let type = $(this).find('.item-type').val();
                    if (type) {
                        loadProducts(rowId, type, true);
                    }
                });
            }

            updatePaymentSummary();

            function showToast(type, message) {
                var toast = $('<div class="toast align-items-center text-white bg-' +
                    (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger')) +
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
