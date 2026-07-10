{{-- resources/views/pages/sales/credit-notes/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nouvel Avoir')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Créer un Avoir</h4>
                        <span class="badge bg-primary p-2">N° {{ $nextNumber }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="creditNoteForm">
                    @csrf
                    <input type="hidden" id="credit_note_number" name="credit_note_number" value="{{ $nextNumber }}">
                    <input type="hidden" id="sales_order_id" name="sales_order_id" value="">

                    {{-- STEP 1: Date + Client --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="credit_note_date" class="form-label">Date Avoir *</label>
                            <input type="date" class="form-control" id="credit_note_date" name="credit_note_date"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="client_id" class="form-label">Client *</label>
                            <select class="form-control select2" id="client_id" name="client_id" required>
                                <option value="">Sélectionner un client</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->client_id }}">
                                        {{ $client->display_name }} ({{ $client->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Client info banner --}}
                    <div id="client-info-card" style="display:none;" class="mb-4">
                        <div class="card bg-gradients">
                            <div class="card-body">
                                <div id="client-info-content"></div>
                            </div>
                        </div>
                    </div>

                    {{-- STEP 2: Select commande --}}
                    <div id="order-selection-card" class="card mb-4" style="display:none;">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Commande à retourner</h6>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label">Sélectionner la commande *</label>
                                    <select class="form-control select2-order" id="order_select">
                                        <option value="">-- Choisir une commande --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div id="order-status-badge"></div>
                                </div>
                            </div>
                            <div id="order-summary" class="mt-3" style="display:none;">
                                <div class="row text-center">
                                    <div class="col-3">
                                        <div class="text-muted small">Total commande</div>
                                        <div class="fw-bold" id="ord-total"></div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-muted small">Montant payé</div>
                                        <div class="fw-bold text-success" id="ord-paid"></div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-muted small">Reste à payer</div>
                                        <div class="fw-bold text-danger" id="ord-rest"></div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-muted small">Statut paiement</div>
                                        <div id="ord-payment-status"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- STEP 3: Items from the selected order --}}
                    <div id="items-card" class="card mb-4" style="display:none;">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-box-open me-2"></i>Articles à retourner</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle" id="items-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%" class="text-center">
                                                <input type="checkbox" id="select-all-items" title="Tout sélectionner">
                                            </th>
                                            <th>Article</th>
                                            <th width="12%" class="text-center">Qté commandée</th>
                                            <th width="14%" class="text-center">Qté à retourner</th>
                                            <th width="13%" class="text-end">Prix Unit.</th>
                                            <th width="13%" class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-body">
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-info">
                                            <td colspan="5" class="text-end fw-bold">Total à rembourser :</td>
                                            <td class="text-end fw-bold" id="total-amount">0.00 DH</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div id="no-items-alert" class="alert alert-warning" style="display:none;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Aucun article trouvé pour cette commande.
                            </div>
                        </div>
                    </div>

                    {{-- STEP 4: Disposition --}}
                    <div id="disposition-card" class="card mb-4" style="display:none;">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Destination de l'Avoir</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body text-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="disposition"
                                                    id="disposition_refund" value="refund" checked>
                                                <label class="form-check-label fw-bold" for="disposition_refund">
                                                    <i class="fas fa-money-bill-wave text-success fs-3 d-block mb-2"></i>
                                                    Remboursement
                                                </label>
                                                <p class="small text-muted mt-2">
                                                    Le montant sera remboursé au client après traitement de l'avoir.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body text-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="disposition"
                                                    id="disposition_credit" value="credit">
                                                <label class="form-check-label fw-bold" for="disposition_credit">
                                                    <i class="fas fa-wallet text-primary fs-3 d-block mb-2"></i>
                                                    Payer une autre vente
                                                </label>
                                                <p class="small text-muted mt-2">
                                                    Le montant sera appliqué sur une autre vente impayée du client.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body text-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="disposition"
                                                    id="disposition_balance" value="balance">
                                                <label class="form-check-label fw-bold" for="disposition_balance">
                                                    <i class="fas fa-piggy-bank text-warning fs-3 d-block mb-2"></i>
                                                    Ajouter au solde client
                                                </label>
                                                <p class="small text-muted mt-2">
                                                    Le montant sera ajouté au solde du client, sans remboursement ni application sur une vente.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Order selector for "credit" disposition --}}
                            <div id="apply-to-order-section" style="display:none;" class="mt-4">
                                <hr>
                                <h6 class="mb-3">Sélectionner la vente à créditer</h6>
                                <div id="no-unpaid-orders-msg" class="alert alert-warning" style="display:none;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Aucune autre vente impayée pour ce client.
                                </div>
                                <div id="unpaid-order-selector" style="display:none;">
                                    <div class="row align-items-end">
                                        <div class="col-md-8">
                                            <label class="form-label">Vente impayée *</label>
                                            <select class="form-control select2-unpaid" id="apply_to_order_id" name="apply_to_order_id">
                                                <option value="">-- Choisir une vente --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <div id="credit-summary" class="alert alert-info py-2 mb-0" style="display:none;">
                                                <small>
                                                    Montant avoir : <strong id="cs-avoir">0.00 DH</strong><br>
                                                    Reste à payer : <strong id="cs-rest">0.00 DH</strong><br>
                                                    Montant appliqué : <strong id="cs-apply" class="text-success">0.00 DH</strong>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Reason + Notes --}}
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="reason" class="form-label">Raison principale</label>
                            <select class="form-control" id="reason" name="reason">
                                <option value="">Sélectionner une raison</option>
                                <option value="Défaut de qualité">Défaut de qualité</option>
                                <option value="Erreur de commande">Erreur de commande</option>
                                <option value="Produit non conforme">Produit non conforme</option>
                                <option value="Retour client">Retour client</option>
                                <option value="Annulation commande">Annulation commande</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-12 mt-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                placeholder="Informations supplémentaires..."></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                        <a href="{{ route('credit-notes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="toast-container" style="position:fixed;top:20px;right:20px;z-index:9999;"></div>
@endsection

@push('stylesheets')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        .bg-gradients {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .client-stat { padding: 0 15px; border-right: 1px solid rgba(255,255,255,.3); }
        .client-stat:last-child { border-right: none; }
        .client-stat .label { font-size:.8rem; opacity:.9; }
        .client-stat .value { font-size:1.1rem; font-weight:bold; }
        .item-row-selected { background-color: #f0fff4 !important; }
        .return-qty { width: 90px; }
        .select2-container { width: 100% !important; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
    $(document).ready(function () {

        let totalAmount = 0;
        let unpaidOrders = [];       // other unpaid orders of the client (excluding current order)
        let selectedOrderPaymentStatus = '';

        // ── Init select2 ────────────────────────────────────────────────
        $('#client_id').select2({ language: 'fr', placeholder: 'Sélectionner un client', allowClear: true });

        function initOrderSelect2() {
            $('.select2-order').select2({ language: 'fr', placeholder: '-- Choisir une commande --', allowClear: true, width: '100%' });
        }
        function initUnpaidSelect2() {
            $('.select2-unpaid').select2({ language: 'fr', placeholder: '-- Choisir une vente --', allowClear: true, width: '100%' });
        }

        // ── Client change ────────────────────────────────────────────────
        $('#client_id').on('change', function () {
            let clientId = $(this).val();
            resetOrderSection();
            resetItemsSection();
            resetDispositionSection();

            if (!clientId) {
                $('#client-info-card').hide();
                $('#order-selection-card').hide();
                return;
            }
            loadClientInfo(clientId);
            loadClientOrders(clientId);
            $('#order-selection-card').show();
        });

        // ── Order change ─────────────────────────────────────────────────
        $(document).on('change', '#order_select', function () {
            let orderId = $(this).val();
            resetItemsSection();
            resetDispositionSection();

            if (!orderId) {
                $('#order-summary').hide();
                $('#items-card').hide();
                return;
            }

            $('#sales_order_id').val(orderId);
            loadOrderItems(orderId);
        });

        // ── Disposition change ───────────────────────────────────────────
        $('input[name="disposition"]').on('change', function () {
            if ($(this).val() === 'credit') {
                $('#apply-to-order-section').show();
                renderUnpaidOrderOptions();
            } else {
                $('#apply-to-order-section').hide();
                $('#apply_to_order_id').val('').trigger('change');
            }
        });

        // ── Unpaid order selection change ────────────────────────────────
        $(document).on('change', '#apply_to_order_id', function () {
            let orderId = $(this).val();
            if (!orderId) {
                $('#credit-summary').hide();
                return;
            }
            let order = unpaidOrders.find(o => o.order_id == orderId);
            if (!order) return;

            let amountApplied = Math.min(totalAmount, order.remaining);
            $('#cs-avoir').text(totalAmount.toFixed(2) + ' DH');
            $('#cs-rest').text(order.remaining_formatted);
            $('#cs-apply').text(amountApplied.toFixed(2) + ' DH');
            $('#credit-summary').show();
        });

        // ── Select all items checkbox ────────────────────────────────────
        $(document).on('change', '#select-all-items', function () {
            let checked = $(this).is(':checked');
            $('.item-checkbox').prop('checked', checked).trigger('change');
        });

        // ── Individual item checkbox ─────────────────────────────────────
        $(document).on('change', '.item-checkbox', function () {
            let row = $(this).closest('tr');
            let qtyInput = row.find('.return-qty');
            if ($(this).is(':checked')) {
                row.addClass('item-row-selected');
                qtyInput.prop('disabled', false);
            } else {
                row.removeClass('item-row-selected');
                qtyInput.prop('disabled', true).val(0);
            }
            updateTotal();
            toggleSubmit();
        });

        // ── Return qty input ─────────────────────────────────────────────
        $(document).on('input', '.return-qty', function () {
            let max = parseFloat($(this).data('max')) || 0;
            let val = parseFloat($(this).val()) || 0;
            if (val > max) $(this).val(max);
            else if (val < 0) $(this).val(0);
            updateTotal();
            toggleSubmit();
        });

        // ── AJAX helpers ─────────────────────────────────────────────────
        function loadClientInfo(clientId) {
            $.get('/credit-notes/client/' + clientId + '/info', function (res) {
                if (!res.success) return;
                let d = res.data;
                $('#client-info-content').html(`
                    <div class="row text-white">
                        <div class="col-md-3 client-stat">
                            <div class="label">Client</div>
                            <div class="value">${escapeHtml(d.client_name)}</div>
                        </div>
                        <div class="col-md-3 client-stat">
                            <div class="label">Solde</div>
                            <div class="value">${d.balance_formatted}</div>
                            <small>${d.balance_status.label}</small>
                        </div>
                        <div class="col-md-3 client-stat">
                            <div class="label">Crédit disponible</div>
                            <div class="value">${d.has_credit ? d.credit_available.toFixed(2) + ' DH' : '—'}</div>
                        </div>
                        <div class="col-md-3 client-stat">
                            <div class="label">Total impayé</div>
                            <div class="value">${d.total_unpaid_formatted}</div>
                            <small>${d.unpaid_orders.length} commande(s)</small>
                        </div>
                    </div>
                `);
                $('#client-info-card').show();
            });
        }

        function loadClientOrders(clientId) {
            $.get('/credit-notes/client/' + clientId + '/orders', function (res) {
                if (!res.success) return;
                let orders = res.data;
                let orderSelect = $('#order_select');
                orderSelect.empty().append('<option value="">-- Choisir une commande --</option>');

                if (orders.length === 0) {
                    orderSelect.append('<option value="" disabled>Aucune commande pour ce client</option>');
                } else {
                    orders.forEach(function (o) {
                        let statusLabel = o.payment_status === 'paid' ? '✔ Payé'
                            : (o.payment_status === 'partial' ? '⚑ Avance' : '✗ Non payé');
                        orderSelect.append(
                            $('<option>', {
                                value: o.order_id,
                                text: o.order_number + ' — ' + o.order_date + ' — ' + o.total_formatted + ' (' + statusLabel + ')',
                                'data-status': o.payment_status,
                                'data-remaining': o.remaining,
                                'data-remaining-formatted': o.remaining_formatted,
                                'data-total': o.total_amount,
                                'data-total-formatted': o.total_formatted,
                                'data-paid': o.paid_amount,
                                'data-paid-formatted': o.paid_formatted,
                            })
                        );
                    });
                }

                if ($('.select2-order').data('select2')) {
                    $('.select2-order').select2('destroy');
                }
                initOrderSelect2();
            });
        }

        function loadOrderItems(orderId) {
            $('#items-body').html('<tr><td colspan="6" class="text-center text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Chargement...</td></tr>');
            $('#items-card').show();

            $.get('/credit-notes/order/' + orderId + '/items', function (res) {
                if (!res.success) { showToast('error', 'Erreur chargement articles'); return; }

                let order = res.order;
                let items  = res.items;

                // Show order summary
                let payStatus = order.payment_status;
                selectedOrderPaymentStatus = payStatus;
                let badgeMap = { paid: 'success', partial: 'warning', pending: 'danger' };
                let labelMap = { paid: 'Payé', partial: 'Avance', pending: 'Non payé' };
                $('#ord-total').text(order.total_formatted);
                $('#ord-paid').text(order.paid_formatted);
                $('#ord-rest').text(order.remaining_formatted);
                $('#ord-payment-status').html('<span class="badge badge-' + (badgeMap[payStatus] || 'secondary') + '">' + (labelMap[payStatus] || payStatus) + '</span>');
                $('#order-summary').show();

                $('#items-body').empty();
                if (items.length === 0) {
                    $('#items-card').hide();
                    $('#no-items-alert').show();
                    return;
                }

                items.forEach(function (item) {
                    let row = `
                        <tr class="item-row"
                            data-order-item-id="${item.order_item_id}"
                            data-item-type="${item.item_type}"
                            data-item-id="${item.item_id}"
                            data-item-name="${escapeHtml(item.item_name)}"
                            data-unit-price="${item.unit_price}"
                            data-family-id="${item.family_id || ''}"
                            data-family-name="${escapeHtml(item.family_name || '')}">
                            <td class="text-center">
                                <input type="checkbox" class="item-checkbox">
                            </td>
                            <td>
                                <strong>${escapeHtml(item.item_name)}</strong>
                                ${item.family_name ? '<br><small class="text-info">Famille : ' + escapeHtml(item.family_name) + '</small>' : ''}
                                <br><small class="text-muted">${escapeHtml(item.type_label)}</small>
                            </td>
                            <td class="text-center">${item.quantity_formatted}</td>
                            <td class="text-center">
                                <input type="number" class="form-control form-control-sm return-qty text-center"
                                    min="0" max="${item.quantity}" step="0.0001"
                                    data-max="${item.quantity}" value="0" disabled>
                            </td>
                            <td class="text-end">${item.unit_price_formatted}</td>
                            <td class="text-end item-total">0.00 DH</td>
                        </tr>
                    `;
                    $('#items-body').append(row);
                });

                $('#disposition-card').show();
                loadUnpaidOrdersExcluding(orderId);
            }).fail(function () {
                showToast('error', 'Impossible de charger les articles');
                $('#items-card').hide();
            });
        }

        function loadUnpaidOrdersExcluding(excludeOrderId) {
            let clientId = $('#client_id').val();
            if (!clientId) return;
            $.get('/credit-notes/client/' + clientId + '/orders', function (res) {
                if (!res.success) return;
                unpaidOrders = res.data.filter(function (o) {
                    return o.payment_status !== 'paid' && o.order_id != excludeOrderId;
                });
            });
        }

        function renderUnpaidOrderOptions() {
            let select = $('#apply_to_order_id');
            select.empty().append('<option value="">-- Choisir une vente --</option>');

            if (unpaidOrders.length === 0) {
                $('#no-unpaid-orders-msg').show();
                $('#unpaid-order-selector').hide();
                return;
            }

            $('#no-unpaid-orders-msg').hide();
            $('#unpaid-order-selector').show();

            unpaidOrders.forEach(function (o) {
                let label = o.payment_status === 'partial' ? '⚑ Avance' : '✗ Non payé';
                select.append($('<option>', {
                    value: o.order_id,
                    text: o.order_number + ' — ' + o.order_date + ' — Reste: ' + o.remaining_formatted + ' (' + label + ')',
                    'data-remaining': o.remaining,
                    'data-remaining-formatted': o.remaining_formatted,
                }));
            });

            if ($('.select2-unpaid').data('select2')) {
                $('.select2-unpaid').select2('destroy');
            }
            initUnpaidSelect2();
        }

        // ── Totals & state ───────────────────────────────────────────────
        function updateTotal() {
            totalAmount = 0;
            $('#items-body tr').each(function () {
                let cb = $(this).find('.item-checkbox');
                if (!cb.is(':checked')) { $(this).find('.item-total').text('0.00 DH'); return; }
                let qty   = parseFloat($(this).find('.return-qty').val()) || 0;
                let price = parseFloat($(this).data('unit-price')) || 0;
                let line  = qty * price;
                totalAmount += line;
                $(this).find('.item-total').text(line.toFixed(2) + ' DH');
            });
            $('#total-amount').text(totalAmount.toFixed(2) + ' DH');

            // Update credit summary if credit disposition is active
            let selectedUnpaidId = $('#apply_to_order_id').val();
            if (selectedUnpaidId) {
                $('#apply_to_order_id').trigger('change');
            }
        }

        function toggleSubmit() {
            let hasChecked = $('.item-checkbox:checked').length > 0;
            let hasQty = false;
            $('.item-checkbox:checked').each(function () {
                let qty = parseFloat($(this).closest('tr').find('.return-qty').val()) || 0;
                if (qty > 0) hasQty = true;
            });
            $('#submitBtn').prop('disabled', !(hasChecked && hasQty && totalAmount > 0));
        }

        function resetOrderSection() {
            $('#order_select').empty().append('<option value="">-- Choisir une commande --</option>');
            $('#order-summary').hide();
            $('#sales_order_id').val('');
            selectedOrderPaymentStatus = '';
        }

        function resetItemsSection() {
            $('#items-body').empty();
            $('#items-card').hide();
            $('#no-items-alert').hide();
            totalAmount = 0;
            $('#total-amount').text('0.00 DH');
        }

        function resetDispositionSection() {
            $('#disposition-card').hide();
            $('#apply-to-order-section').hide();
            $('#apply_to_order_id').val('');
            $('#credit-summary').hide();
            $('#disposition_refund').prop('checked', true);
            unpaidOrders = [];
            $('#submitBtn').prop('disabled', true);
        }

        // ── Form submit ──────────────────────────────────────────────────
        $('#creditNoteForm').submit(function (e) {
            e.preventDefault();

            // Collect selected items
            let items = [];
            let orderId = $('#sales_order_id').val();
            $('#items-body tr').each(function () {
                let cb = $(this).find('.item-checkbox');
                if (!cb.is(':checked')) return;
                let qty = parseFloat($(this).find('.return-qty').val()) || 0;
                if (qty <= 0) return;
                items.push({
                    order_id: orderId,
                    order_item_id: $(this).data('order-item-id'),
                    item_type: $(this).data('item-type'),
                    item_id: $(this).data('item-id'),
                    item_name: $(this).data('item-name'),
                    quantity: qty,
                    unit_price: $(this).data('unit-price'),
                    family_id: $(this).data('family-id') || null,
                    family_name: $(this).data('family-name') || null,
                });
            });

            if (items.length === 0) { showToast('error', 'Veuillez sélectionner au moins un article'); return; }
            if (totalAmount <= 0)   { showToast('error', 'Le montant total doit être supérieur à 0'); return; }

            let disposition = $('input[name="disposition"]:checked').val();
            let applyToOrderId = null;

            if (disposition === 'credit') {
                applyToOrderId = $('#apply_to_order_id').val();
                if (!applyToOrderId) { showToast('error', 'Veuillez sélectionner la vente à créditer'); return; }
            }

            let btn = $('#submitBtn');
            let orig = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

            $.ajax({
                url: "{{ route('credit-notes.store') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    credit_note_number: $('#credit_note_number').val(),
                    client_id: $('#client_id').val(),
                    sales_order_id: orderId,
                    credit_note_date: $('#credit_note_date').val(),
                    reason: $('#reason').val(),
                    notes: $('#notes').val(),
                    items: items,
                    disposition: disposition,
                    apply_to_order_id: applyToOrderId,
                },
                success: function (res) {
                    if (res.success) {
                        showToast('success', res.message);
                        setTimeout(function () { window.location.href = "{{ route('credit-notes.index') }}"; }, 1500);
                    } else {
                        showToast('error', res.message);
                        btn.prop('disabled', false).html(orig);
                    }
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON?.message || 'Erreur lors de la création';
                    showToast('error', msg);
                    btn.prop('disabled', false).html(orig);
                }
            });
        });

        // ── Utilities ────────────────────────────────────────────────────
        function escapeHtml(t) {
            if (!t) return '';
            return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
        }

        function showToast(type, message) {
            let bg = type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger');
            let toast = $('<div class="toast align-items-center text-white bg-' + bg + ' border-0" role="alert" aria-atomic="true">' +
                '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>');
            $('#toast-container').append(toast);
            new bootstrap.Toast(toast[0]).show();
            setTimeout(function () { toast.remove(); }, 5000);
        }
    });
    </script>
@endpush
