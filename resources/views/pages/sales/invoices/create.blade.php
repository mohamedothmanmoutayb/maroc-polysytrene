@extends('layouts.app')

@section('title', 'Nouvelle Facture')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Nouvelle Facture</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('sales.invoices.index') }}">
                                        Factures
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Nouveau
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
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-plus-circle me-2"></i>Créer une nouvelle facture
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="invoiceForm">
                            @csrf

                            <!-- Invoice Number Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="invoice_number" class="form-label">N° Facture *</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="invoice_number"
                                                name="invoice_number" value="{{ $nextInvoiceNumber }}" required>
                                            <button type="button" class="btn btn-outline-secondary"
                                                id="regenerateInvoiceNumberBtn" title="Générer le prochain numéro">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">Vous pouvez modifier ce numéro si besoin.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="invoice_date" class="form-label">Date Facture *</label>
                                        <input type="date" class="form-control" id="invoice_date" name="invoice_date"
                                            value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Client Selection -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="client_id" class="form-label">Client *</label>
                                        <select class="form-control select2" id="client_id" name="client_id" required>
                                            <option value="">Sélectionner un client</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->client_id }}"
                                                    data-client-type="{{ $client->client_type }}">
                                                    {{ $client->display_name }} ({{ $client->phone }}) -
                                                    {{ $client->client_type_label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Multiple Sales Selection Section (appears after client selection) -->
                            <div class="row mb-4" id="salesSelectionSection" style="display: none;">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="fas fa-shopping-cart me-2"></i>
                                                Sélectionner des ventes à charger
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <select class="form-control select2-multiple" id="sales_order_ids"
                                                        multiple="multiple">
                                                        <!-- Will be populated dynamically -->
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="button" class="btn btn-success"
                                                        id="loadSelectedSalesBtn">
                                                        <i class="fas fa-download me-1"></i> Charger les ventes
                                                        sélectionnées
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary ms-2"
                                                        id="clearSalesBtn">
                                                        <i class="fas fa-times me-1"></i> Effacer
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="selectedSalesInfo" class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Sélectionnez une ou plusieurs ventes. Les articles seront ajoutés à la
                                                    facture.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Items Section -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Articles de la Facture</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="items-table">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="15%">Source Vente</th>
                                                    <th width="15%">Type</th>
                                                    <th width="25%">Article</th>
                                                    <th width="10%">Quantité</th>
                                                    <th width="15%">Prix Unitaire (DH)</th>
                                                    <th width="15%">Total (DH)</th>
                                                    <th width="5%">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="items-body">
                                                <!-- Items will be added here -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="6" class="text-end"><strong>Sous-total:</strong></td>
                                                    <td><strong id="subtotal">0.00 DH</strong></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="text-end"><strong>Remise:</strong></td>
                                                    <td>
                                                        <div class="input-group">
                                                            <input type="number"
                                                                class="form-control form-control-sm text-end"
                                                                id="discount" name="discount" min="0"
                                                                step="0.01" value="0" style="width: 100px;">
                                                            <span class="input-group-text">DH</span>
                                                        </div>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="text-end"><strong>Total TTC:</strong></td>
                                                    <td><strong id="invoice-total">0.00 DH</strong></td>
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

                            <!-- Notes and Terms Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Notes internes..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="terms_conditions" class="form-label">Conditions particulières</label>
                                        <textarea class="form-control" id="terms_conditions" name="terms_conditions" rows="3"
                                            placeholder="Conditions générales de vente..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('sales.invoices.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-spinner {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #4e73df;
            color: white;
            border: none;
        }

        .source-sale-badge {
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 12px;
            background-color: #e3f2fd;
            color: #1976d2;
            white-space: nowrap;
        }

        /* Add to your styles */
        .item-row-loading {
            opacity: 0.7;
            background-color: #f8f9fa;
            transition: opacity 0.3s ease;
        }

        .item-row-loading td {
            position: relative;
        }

        .item-row-loading td:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: shimmer 1.5s infinite;
            pointer-events: none;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-spinner {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            text-align: center;
            min-width: 300px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .loading-spinner i {
            color: #4e73df;
        }

        .progress {
            height: 6px;
            border-radius: 3px;
            background-color: #e9ecef;
        }

        .progress-bar {
            background-color: #4e73df;
            transition: width 0.3s ease;
        }

        .select2-container--disabled .select2-selection {
            background-color: #f8f9fa !important;
            opacity: 0.7;
        }
    </style>
@endpush    

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                language: "fr",
                placeholder: "Sélectionner...",
                allowClear: true
            });

            let itemCounter = 0;
            let loadedSalesOrders = []; // Store loaded sales order data

            // Regenerate the invoice number (in case the user edited it and wants the default back)
            $('#regenerateInvoiceNumberBtn').click(function() {
                $.ajax({
                    url: "{{ route('sales.invoices.generate-number') }}",
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#invoice_number').val(response.invoice_number);
                        }
                    }
                });
            });

            // Initialize multiple select2 for sales
            $('.select2-multiple').select2({
                language: "fr",
                placeholder: "Sélectionner des ventes...",
                allowClear: true,
                theme: "bootstrap-5",
                width: '100%'
            });

            // Client selection change - Load sales orders
            $('#client_id').change(function() {
                let clientId = $(this).val();

                if (clientId) {
                    loadSalesOrdersForClient(clientId);
                    $('#salesSelectionSection').show();
                    refreshManualItemProducts();
                } else {
                    $('#salesSelectionSection').hide();
                    $('#sales_order_ids').empty().trigger('change');
                }
            });

            function loadSalesOrdersForClient(clientId) {
                let $select = $('#sales_order_ids');
                let $info = $('#selectedSalesInfo');

                // Show loading state
                $select.empty().append('<option value="">🔃 Chargement des ventes...</option>');
                $select.prop('disabled', true);

                // Show loading info
                $info.html(
                    '<span class="text-info"><i class="fas fa-spinner fa-spin me-1"></i> Chargement des ventes du client...</span>'
                );

                $.ajax({
                    url: "{{ route('sales.invoices.get-client-sales') }}",
                    type: "GET",
                    data: {
                        client_id: clientId
                    },
                    success: function(response) {
                        $select.empty();

                        if (response.success && response.data.length > 0) {
                            response.data.forEach(function(order) {
                                let option = $('<option></option>')
                                    .attr('value', order.order_id)
                                    .attr('data-order-number', order.order_number)
                                    .attr('data-order-date', order.order_date)
                                    .attr('data-total-amount', order.total_amount)
                                    .attr('data-remaining-items', order.remaining_items_count ||
                                        0)
                                    .text(order.order_number + ' - ' + order
                                        .order_date_formatted +
                                        ' (' + order.items_count + ' articles) - ' +
                                        formatMoney(order.total_amount) +
                                        (order.remaining_items_count !== undefined && order
                                            .remaining_items_count !== order.items_count ?
                                            ' ⚡ ' + order.remaining_items_count + ' restants' :
                                            ''));
                                $select.append(option);
                            });

                            // Update info with count
                            $info.html(
                                '<span class="text-success"><i class="fas fa-check-circle me-1"></i> ' +
                                response.data.length +
                                ' vente(s) disponible(s) pour ce client</span>');

                            $select.prop('disabled', false);
                        } else {
                            $select.append(
                                '<option value="">📭 Aucune vente trouvée pour ce client</option>');
                            $select.prop('disabled', false);
                            $info.html(
                                '<span class="text-warning"><i class="fas fa-info-circle me-1"></i> Ce client n\'a pas de ventes disponibles</span>'
                            );
                        }
                    },
                    error: function() {
                        $select.empty().append('<option value="">❌ Erreur de chargement</option>');
                        $select.prop('disabled', false);
                        $info.html(
                            '<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i> Erreur lors du chargement des ventes</span>'
                        );
                        showToast('error', 'Erreur lors du chargement des ventes');
                    }
                });
            }

            $('#loadSelectedSalesBtn').click(function() {
                let selectedOrders = $('#sales_order_ids').val();

                if (!selectedOrders || selectedOrders.length === 0) {
                    showToast('warning', 'Veuillez sélectionner au moins une vente');
                    return;
                }

                if (!confirm('Charger ces ventes va ajouter leurs articles à la facture. Continuer?')) {
                    return;
                }

                let $btn = $(this);
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Chargement...');

                // Show loading overlay with progress
                $('body').append(`
        <div class="loading-overlay">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Chargement des ventes sélectionnées...</p>
                <p class="text-muted small">${selectedOrders.length} vente(s) en cours</p>
                <div class="progress mt-2" style="width: 200px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                         role="progressbar" style="width: 0%"></div>
                </div>
            </div>
        </div>
    `);

                let $progressBar = $('.loading-overlay .progress-bar');

                $.ajax({
                    url: "{{ route('sales.invoices.get-multiple-sales') }}",
                    type: "POST",
                    data: {
                        order_ids: selectedOrders,
                        _token: $('meta[name="csrf-token"]').attr('content') || $(
                            'input[name="_token"]').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            let totalItemsAdded = 0;
                            let totalSales = response.data.length;

                            // Process each sale with progress update
                            response.data.forEach(function(sale, index) {
                                let itemsAdded = addItemsFromSale(sale);
                                totalItemsAdded += itemsAdded;

                                // Update progress
                                let progress = ((index + 1) / totalSales) * 100;
                                $progressBar.css('width', progress + '%');
                            });

                            showToast('success', totalItemsAdded +
                                ' articles chargés avec succès!');
                            $('#sales_order_ids').val(null).trigger('change');

                            // Update info
                            $('#selectedSalesInfo').html(
                                '<span class="text-success"><i class="fas fa-check-circle me-1"></i> ' +
                                totalSales + ' vente(s) chargée(s) - ' + totalItemsAdded +
                                ' article(s) ajouté(s)</span>'
                            );
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors du chargement des ventes');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                        setTimeout(function() {
                            $('.loading-overlay').remove();
                        }, 500);
                    }
                });
            });

            // Clear loaded sales
            $('#clearSalesBtn').click(function() {
                if (loadedSalesOrders.length > 0 && confirm(
                        'Effacer tous les articles chargés depuis les ventes?')) {
                    // Remove items that came from sales
                    $('.item-row[data-source="sale"]').remove();
                    loadedSalesOrders = [];
                    updateItemIndices();
                    updateInvoiceTotal();
                    showToast('success', 'Articles des ventes effacés');
                }
            });

            // Add items from a sale to the invoice. If a product already has a row
            // from a previously-loaded vente, merge into it (sum the quantity)
            // instead of adding a duplicate line.
            function addItemsFromSale(sale) {
                let itemsAdded = 0;

                sale.items.forEach(function(item) {
                    if (!mergeIntoExistingSaleRow(item, sale)) {
                        addItemRowFromSaleData(item, sale);
                        itemsAdded++;
                    }
                });

                return itemsAdded;
            }

            // Render hidden inputs items[idx][source_sales][orderId] = quantity for
            // a sale-sourced row. `quantities` maps order_id -> quantity that vente
            // contributed to this row, so a row can carry more than one source
            // when identical products from different ventes get merged together.
            function setRowSourceQuantities($row, itemIndex, quantities) {
                $row.data('source-quantities', quantities);
                let html = '';
                Object.keys(quantities).forEach(function(orderId) {
                    html += `<input type="hidden" class="source-sale-input" data-order-id="${orderId}" ` +
                        `name="items[${itemIndex}][source_sales][${orderId}]" value="${quantities[orderId]}">`;
                });
                $row.find('.source-sale-inputs').html(html);
            }

            // The item index embedded in a row's field names, kept in sync by
            // updateItemIndices() - read from there rather than the data-index
            // attribute, since jQuery's .data() cache can go stale after .attr() updates.
            function getRowIndex($row) {
                let name = $row.find('.item-quantity').attr('name') || '';
                let match = name.match(/items\[(\d+)\]/);
                return match ? match[1] : $row.attr('data-index');
            }

            // Look for an existing sale-sourced row for the same product (type +
            // item_id + family_id). If found, add this item's quantity to it and
            // return true. The row keeps track of every vente that contributed to
            // it (and how much), so the badge lists them all instead of pointing
            // to a single vente.
            function mergeIntoExistingSaleRow(item, sale) {
                let familyId = item.family_id || '';
                let $match = null;

                $('.item-row[data-source="sale"]').each(function() {
                    let $row = $(this);
                    let rowItemId = $row.find('.item-id').val();
                    let rowType = $row.find('.item-type-input').val();
                    let rowFamilyId = $row.find('.family-id').val() || '';

                    if (rowItemId == item.item_id && rowType === item.type &&
                        String(rowFamilyId) === String(familyId)) {
                        $match = $row;
                        return false;
                    }
                });

                if (!$match) {
                    return false;
                }

                let rowId = $match.attr('id');
                let newQuantity = (parseFloat($match.find('.item-quantity').val()) || 0) +
                    parseFloat(item.quantity);
                $match.find('.item-quantity').val(newQuantity);
                calculateItemTotal(rowId);
                updateInvoiceTotal();

                // Record this vente's contribution alongside whatever this row already had.
                let quantities = $.extend({}, $match.data('source-quantities') || {});
                quantities[sale.order_id] = (quantities[sale.order_id] || 0) + parseFloat(item.quantity);
                setRowSourceQuantities($match, getRowIndex($match), quantities);

                let saleLabel = sale.order_number + ' du ' + sale.order_date_formatted;
                let sources = ($match.data('source-labels') || []).concat([saleLabel]);
                $match.data('source-labels', sources);

                $match.find('.source-sale-badge')
                    .attr('title', 'Ventes : ' + sources.join(', '))
                    .html('<i class="fas fa-tags me-1"></i>' + sources.length + ' ventes');

                return true;
            }

            // Add a single item row from sale data
            function addItemRowFromSaleData(itemData, saleData) {
                let rowId = 'item_' + Date.now() + '_' + itemCounter + '_' + Math.random().toString(36).substr(2,
                    6);
                let itemIndex = itemCounter;
                let clientType = $('#client_id option:selected').data('client-type') || 'client';

                let typeOptions = `
                    <option value="">Sélectionner</option>
                    <option value="raw_material" ${itemData.type === 'raw_material' ? 'selected' : ''}>Matière Première</option>
                    <option value="production" ${itemData.type === 'production' ? 'selected' : ''}>Production</option>
                    <option value="decoupage" ${itemData.type === 'decoupage' ? 'selected' : ''}>Découpage</option>
                    <option value="finale" ${itemData.type === 'finale' ? 'selected' : ''}>Vente</option>
                `;

                let row = `
                    <tr id="${rowId}" class="item-row" data-source="sale" data-sale-id="${saleData.order_id}" data-index="${itemIndex}">
                        <td class="item-number">${itemCounter + 1}</td>
                        <td>
                            <span class="source-sale-badge" title="Vente: ${saleData.order_number} du ${saleData.order_date_formatted}">
                                <i class="fas fa-tag me-1"></i>${saleData.order_number}
                            </span>
                            <span class="source-sale-inputs"></span>
                        </td>
                        <td>
                            <select class="form-control item-type" data-row="${rowId}" required>
                                ${typeOptions}
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control item-display" value="${itemData.name.replace(/'/g, "\\'")}" readonly disabled>
                            <input type="hidden" class="item-id" name="items[${itemIndex}][item_id]" value="${itemData.item_id}">
                            <input type="hidden" class="item-name" name="items[${itemIndex}][name]" value="${itemData.name.replace(/'/g, "\\'")}">
                            <input type="hidden" class="item-type-input" name="items[${itemIndex}][type]" value="${itemData.type}">
                            <input type="hidden" class="family-id" name="items[${itemIndex}][family_id]" value="${itemData.family_id || ''}">
                            <input type="hidden" class="family-name" name="items[${itemIndex}][family_name]" value="${itemData.family_name || ''}">
                        </td>
                        <td>
                            <input type="number" class="form-control item-quantity"
                                name="items[${itemIndex}][quantity]" min="0.0001" step="0.0001"
                                value="${itemData.quantity}" required>
                        </td>
                        <td>
                            <input type="number" class="form-control item-price"
                                name="items[${itemIndex}][unit_price]" min="0" step="0.01"
                                value="${itemData.unit_price}" required>
                        </td>
                        <td class="item-total">${(itemData.quantity * itemData.unit_price).toFixed(2)} DH</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-item" data-row="${rowId}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#items-body').append(row);

                // Track which vente(s) contributed to this row, so a later merge
                // can list all of them on the badge.
                $(`#${rowId}`).data('source-labels', [saleData.order_number + ' du ' + saleData
                    .order_date_formatted
                ]);
                setRowSourceQuantities($(`#${rowId}`), itemIndex, {
                    [saleData.order_id]: parseFloat(itemData.quantity)
                });

                // Bind events
                $(`#${rowId} .item-quantity, #${rowId} .item-price`).on('input', function() {
                    calculateItemTotal(rowId);
                    updateInvoiceTotal();
                });

                $(`#${rowId} .remove-item`).off('click').on('click', function() {
                    let row = $(this).data('row');
                    $(`#${row}`).remove();
                    updateItemIndices();
                    updateInvoiceTotal();
                });

                // Type change handler
                $(`#${rowId} .item-type`).off('change').on('change', function() {
                    let newType = $(this).val();
                    let rowId = $(this).data('row');
                    let clientId = $('#client_id').val();

                    if (clientId) {
                        loadProductForTypeAndUpdateRow(newType, rowId, function(price) {
                            $(`#${rowId} .item-price`).val(price).trigger('input');
                        });
                    }
                });

                itemCounter++;
                updateInvoiceTotal();
            }

            // Function to add manual item row (not from sale)
            function addItemRow() {
                let rowId = 'item_' + Date.now() + '_' + itemCounter;
                let itemIndex = itemCounter;

                let typeOptions = `
                    <option value="">Sélectionner</option>
                    <option value="raw_material">Matière Première</option>
                    <option value="production">Production</option>
                    <option value="decoupage">Découpage</option>
                    <option value="finale" selected>Vente</option>
                `;

                let row = `
                    <tr id="${rowId}" class="item-row" data-source="manual" data-index="${itemIndex}">
                        <td class="item-number">${itemCounter + 1}</td>
                        <td>
                            <span class="source-sale-badge" style="background-color:#e8f5e9; color:#2e7d32;">
                                <i class="fas fa-plus-circle me-1"></i>Ajout manuel
                            </span>
                        </td>
                        <td>
                            <select class="form-control item-type" data-row="${rowId}" required>
                                ${typeOptions}
                            </select>
                        </td>
                        <td>
                            <select class="form-control item-select" data-row="${rowId}" style="width:100%;" required>
                                <option value="">Chargement des produits...</option>
                            </select>
                            <input type="hidden" class="item-id" name="items[${itemIndex}][item_id]">
                            <input type="hidden" class="item-name" name="items[${itemIndex}][name]">
                            <input type="hidden" class="item-type-input" name="items[${itemIndex}][type]">
                            <input type="hidden" class="family-id" name="items[${itemIndex}][family_id]">
                            <input type="hidden" class="family-name" name="items[${itemIndex}][family_name]">
                        </td>
                        <td>
                            <input type="number" class="form-control item-quantity"
                                name="items[${itemIndex}][quantity]" min="0.0001" step="0.0001" value="1" required disabled>
                        </td>
                        <td>
                            <input type="number" class="form-control item-price"
                                name="items[${itemIndex}][unit_price]" min="0" step="0.01" value="0" required disabled>
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

                // Initialize Select2
                $(`#${rowId} .item-select`).select2({
                    language: "fr",
                    placeholder: "Sélectionner un article...",
                    width: '100%'
                });

                // Type change handler
                $(`#${rowId} .item-type`).change(function() {
                    let type = $(this).val();
                    let row = $(this).data('row');
                    loadProductsForType(type, row);
                });

                // Product selection handler
                $(`#${rowId} .item-select`).change(function() {
                    let row = $(this).data('row');
                    let selectedOption = $(this).find(':selected');
                    let value = $(this).val();

                    if (value) {
                        let itemData = {};

                        if (value.includes('_')) {
                            let parts = value.split('_');
                            let productId = parts[0];
                            let familyId = parts[1];

                            itemData = {
                                id: productId,
                                name: selectedOption.data('product-name'),
                                price: getPriceByClientType(selectedOption),
                                hasFamilies: true,
                                familyId: familyId,
                                familyName: selectedOption.data('family-name'),
                                familyPriceClient: selectedOption.data('family-price-client'),
                                familyPriceGrossiste: selectedOption.data('family-price-grossiste'),
                                familyPriceCommercial: selectedOption.data('family-price-commercial'),
                                familyPriceSpecial: selectedOption.data('family-price-special')
                            };
                        } else {
                            itemData = {
                                id: value,
                                name: selectedOption.data('name'),
                                price: selectedOption.data('price') || 0,
                                hasFamilies: false,
                                familyId: null,
                                familyName: null
                            };
                        }

                        updateItemFromSelection(row, itemData);
                    }
                });

                // Quantity/Price handlers
                $(`#${rowId} .item-quantity, #${rowId} .item-price`).on('input', function() {
                    calculateItemTotal(rowId);
                    updateInvoiceTotal();
                });

                // Remove handler
                $(`#${rowId} .remove-item`).click(function() {
                    let row = $(this).data('row');
                    $(`#${row}`).remove();
                    updateItemIndices();
                    updateInvoiceTotal();
                });

                // Load initial products if client is selected
                let clientId = $('#client_id').val();
                if (clientId) {
                    loadProductsForType('finale', rowId);
                } else {
                    $(`#${rowId} .item-select`).empty().append(
                        '<option value="">Veuillez d\'abord sélectionner un client</option>');
                }

                itemCounter++;
            }

            function ceilPrice(price) {
                return Math.ceil(parseFloat(price) || 0);
            }

            // Get price based on client type
            function getPriceByClientType(selectedOption) {
                let clientType = $('#client_id option:selected').data('client-type') || 'client';

                switch (clientType) {
                    case 'grossiste':
                        return ceilPrice(selectedOption.data('family-price-grossiste') || selectedOption.data(
                            'price') || 0);
                    case 'commerciale':
                        return ceilPrice(selectedOption.data('family-price-commercial') || selectedOption.data(
                            'price') || 0);
                    case 'special':
                        return ceilPrice(selectedOption.data('family-price-special') || selectedOption.data(
                            'price') || 0);
                    default:
                        return ceilPrice(selectedOption.data('family-price-client') || selectedOption.data(
                            'price') || 0);
                }
            }

            function loadProductsForType(type, rowId, existingItem = null) {
                let select = $(`#${rowId} .item-select`);
                let clientId = $('#client_id').val();

                if (!clientId) {
                    showToast('warning', 'Veuillez d\'abord sélectionner un client');
                    select.empty().append('<option value="">Sélectionner d\'abord un client</option>');
                    return;
                }

                // Show loading state in the dropdown
                select.empty().append('<option value="">🔃 Chargement des articles...</option>')
                    .prop('disabled', true)
                    .trigger('change');

                // Add a subtle loading indicator to the row
                let $row = $(`#${rowId}`);
                $row.addClass('item-row-loading');

                // Show a small spinner next to the select
                let spinnerHtml = '<span class="ms-2 text-muted" id="loading-spinner-' + rowId + '">' +
                    '<i class="fas fa-spinner fa-spin"></i> Chargement...</span>';

                // Remove any existing spinner
                $(`#loading-spinner-${rowId}`).remove();

                // Add spinner after the select
                select.after(spinnerHtml);

                let url = '';
                if (type === 'raw_material') {
                    url = "{{ route('sales.invoices.raw-materials.list') }}";
                } else {
                    url = "{{ route('sales.invoices.products.by-type', '') }}/" + type;
                }

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        // Remove loading spinner
                        $(`#loading-spinner-${rowId}`).remove();
                        $row.removeClass('item-row-loading');

                        select.empty().append('<option value="">Sélectionner un article</option>');

                        let items = response.data || response;

                        if (items.length === 0) {
                            select.append('<option value="">⚠️ Aucun article trouvé</option>');
                            select.prop('disabled', false);
                            return;
                        }

                        items.forEach(function(item) {
                            if (item.has_families && item.families && item.families.length >
                                0) {
                                item.families.forEach(function(family) {
                                    let optionValue = item.id + '_' + family.id;
                                    let displayName = item.name + ' - ' + family.name;

                                    let option = $('<option></option>')
                                        .attr('value', optionValue)
                                        .attr('data-product-id', item.id)
                                        .attr('data-product-name', item.name)
                                        .attr('data-product-code', item.code || '')
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
                                        .attr('data-price', family.prix_client || item
                                            .price || 0)
                                        .attr('data-has-families', true)
                                        .text(displayName + (item.code ? ' (' + item
                                            .code + ')' : ''));

                                    if (existingItem && existingItem.item_id == item
                                        .id &&
                                        existingItem.family_id == family.id) {
                                        option.attr('selected', 'selected');
                                    }

                                    select.append(option);
                                });
                            } else {
                                let option = $('<option></option>')
                                    .attr('value', item.id)
                                    .attr('data-name', item.name)
                                    .attr('data-code', item.code || '')
                                    .attr('data-price', item.price || 0)
                                    .attr('data-has-families', false)
                                    .text(item.name + (item.code ? ' (' + item.code + ')' :
                                        ''));

                                if (existingItem && existingItem.item_id == item.id && !
                                    existingItem.family_id) {
                                    option.attr('selected', 'selected');
                                }

                                select.append(option);
                            }
                        });

                        select.prop('disabled', false);

                        // If there was an existing item and it's not found, show a message
                        if (existingItem && existingItem.item_id) {
                            let found = false;
                            select.find('option').each(function() {
                                if ($(this).val() && $(this).val().includes(existingItem
                                        .item_id)) {
                                    found = true;
                                    return false;
                                }
                            });
                            if (!found) {
                                select.prepend(
                                    '<option value="" disabled>⚠️ Article précédent non disponible</option>'
                                );
                            }
                        }

                        select.trigger('change');
                        $(`#${rowId} .item-type-input`).val(type);
                        $(`#${rowId} .item-quantity`).prop('disabled', false);
                    },
                    error: function(xhr) {
                        // Remove loading spinner
                        $(`#loading-spinner-${rowId}`).remove();
                        $row.removeClass('item-row-loading');

                        select.empty().append('<option value="">❌ Erreur de chargement</option>');
                        select.prop('disabled', false);
                        showToast('error', 'Erreur lors du chargement des articles');
                    }
                });
            }

            // Load product for type and update row
            function loadProductForTypeAndUpdateRow(type, rowId, callback) {
                let clientId = $('#client_id').val();

                if (!clientId) {
                    if (callback) callback(0);
                    return;
                }

                let url = '';
                if (type === 'raw_material') {
                    url = "{{ route('sales.invoices.raw-materials.list') }}";
                } else {
                    url = "{{ route('sales.invoices.products.by-type', '') }}/" + type;
                }

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        let items = response.data || response;
                        let price = 0;

                        if (items.length > 0) {
                            let firstItem = items[0];
                            if (firstItem.has_families && firstItem.families && firstItem.families
                                .length > 0) {
                                let family = firstItem.families[0];
                                let clientType = $('#client_id option:selected').data('client-type') ||
                                    'client';

                                switch (clientType) {
                                    case 'grossiste':
                                        price = family.prix_grossiste || 0;
                                        break;
                                    case 'commerciale':
                                        price = family.prix_commercial || 0;
                                        break;
                                    case 'special':
                                        price = family.prix_special || 0;
                                        break;
                                    default:
                                        price = family.prix_client || 0;
                                }
                            } else {
                                price = firstItem.price || 0;
                            }
                        }

                        if (callback) callback(price);
                    },
                    error: function() {
                        if (callback) callback(0);
                    }
                });
            }

            // Update item from selection (for manual rows)
            function updateItemFromSelection(rowId, itemData) {
                $(`#${rowId} .item-id`).val(itemData.id);
                $(`#${rowId} .item-name`).val(itemData.name);

                if (itemData.hasFamilies && itemData.familyId) {
                    $(`#${rowId} .family-id`).val(itemData.familyId);
                    $(`#${rowId} .family-name`).val(itemData.familyName);
                } else {
                    $(`#${rowId} .family-id`).val('');
                    $(`#${rowId} .family-name`).val('');
                }

                $(`#${rowId} .item-price`).val(itemData.price).prop('disabled', false);
                $(`#${rowId} .item-quantity`).prop('disabled', false);

                calculateItemTotal(rowId);
                updateInvoiceTotal();
            }

            // Re-fetch the product list for every manual row's currently selected
            // type when the client changes, so the dropdown reflects the new
            // client (and its pricing) instead of staying on the old client's
            // product list or a stale "sélectionnez un client" placeholder.
            function refreshManualItemProducts() {
                $('#items-body tr[data-source="manual"]').each(function() {
                    let $row = $(this);
                    let rowId = $row.attr('id');
                    let type = $row.find('.item-type').val();

                    if (!type) {
                        return;
                    }

                    let existingItem = {
                        item_id: $row.find('.item-id').val(),
                        family_id: $row.find('.family-id').val() || null,
                    };

                    loadProductsForType(type, rowId, existingItem);
                });
            }

            function calculateItemTotal(rowId) {
                let quantity = parseFloat($(`#${rowId} .item-quantity`).val()) || 0;
                let price = parseFloat($(`#${rowId} .item-price`).val()) || 0;
                let total = quantity * price;
                $(`#${rowId} .item-total`).text(total.toFixed(2) + ' DH');
            }

            function updateItemIndices() {
                $('#items-body tr').each(function(index) {
                    $(this).find('.item-number').text(index + 1);
                    $(this).attr('data-index', index);
                    $(this).find('.item-id').attr('name', `items[${index}][item_id]`);
                    $(this).find('.item-name').attr('name', `items[${index}][name]`);
                    $(this).find('.item-type-input').attr('name', `items[${index}][type]`);
                    $(this).find('.item-quantity').attr('name', `items[${index}][quantity]`);
                    $(this).find('.item-price').attr('name', `items[${index}][unit_price]`);
                    $(this).find('.family-id').attr('name', `items[${index}][family_id]`);
                    $(this).find('.family-name').attr('name', `items[${index}][family_name]`);
                    $(this).find('.source-sale-input').each(function() {
                        $(this).attr('name', `items[${index}][source_sales][${$(this).data('order-id')}]`);
                    });
                });
            }

            function updateInvoiceTotal() {
                let subtotal = 0;
                $('.item-total').each(function() {
                    let text = $(this).text().replace(' DH', '').replace(/,/g, '');
                    subtotal += parseFloat(text) || 0;
                });

                $('#subtotal').text(subtotal.toFixed(2) + ' DH');

                let discount = parseFloat($('#discount').val()) || 0;
                let total = subtotal - discount;
                if (total < 0) total = 0;

                $('#invoice-total').text(total.toFixed(2) + ' DH');
            }

            function formatMoney(amount) {
                return parseFloat(amount).toLocaleString('de-DE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' DH';
            }

            // Discount change handler
            $('#discount').on('input', function() {
                updateInvoiceTotal();
            });

            // Add item button
            $('#add-item').click(function() {
                addItemRow();
            });

            // Form submission
            $('#invoiceForm').submit(function(e) {
                e.preventDefault();

                if ($('#items-body tr').length === 0) {
                    showToast('error', 'Veuillez ajouter au moins un article');
                    return;
                }

                let clientId = $('#client_id').val();
                if (!clientId) {
                    showToast('error', 'Veuillez sélectionner un client');
                    return;
                }

                let valid = true;
                $('#items-body tr').each(function() {
                    if (!$(this).find('.item-id').val()) {
                        showToast('error', 'Veuillez sélectionner un article pour chaque ligne');
                        valid = false;
                        return false;
                    }
                    if (!$(this).find('.item-quantity').val() || parseFloat($(this).find(
                            '.item-quantity').val()) <= 0) {
                        showToast('error', 'Veuillez saisir une quantité valide');
                        valid = false;
                        return false;
                    }
                });

                if (!valid) return;

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                const formData = new FormData(this);

                $.ajax({
                    url: "{{ route('sales.invoices.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('sales.invoices.index') }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMessage = xhr.responseJSON?.message ||
                            'Une erreur est survenue';

                        if (errors) {
                            errorMessage = '';
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        }

                        showToast('error', errorMessage);
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

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
