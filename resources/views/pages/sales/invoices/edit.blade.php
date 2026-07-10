@extends('layouts.app')

@section('title', 'Modifier Facture')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Facture</h4>
                        <div class="d-flex align-items-center gap-3">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item d-flex align-items-center">
                                        <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                            <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a class="text-muted text-decoration-none"
                                            href="{{ route('sales.invoices.index') }}">
                                            Factures
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item" aria-current="page">
                                        <span class="badge fw-medium fs-2 bg-warning text-warning">
                                            Modification
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
                            <i class="fas fa-edit me-2"></i>Modifier la facture N° {{ $invoice->invoice_number }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="invoiceForm">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="invoice_id" value="{{ $invoice->invoice_id }}">

                            <!-- Basic Info Section -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="invoice_number" class="form-label">N° Facture *</label>
                                        <input type="text" class="form-control" id="invoice_number"
                                            name="invoice_number" value="{{ $invoice->invoice_number }}" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="invoice_date" class="form-label">Date Facture *</label>
                                        <input type="date" class="form-control" id="invoice_date" name="invoice_date"
                                            value="{{ $invoice->invoice_date->format('Y-m-d') }}" required>
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="client_id" class="form-label">Client *</label>
                                        <select class="form-control select2" id="client_id" name="client_id" required>
                                            <option value="">Sélectionner un client</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->client_id }}"
                                                    data-client-type="{{ $client->client_type }}"
                                                    {{ $invoice->client_id == $client->client_id ? 'selected' : '' }}>
                                                    {{ $client->display_name }} ({{ $client->phone }}) -
                                                    {{ $client->client_type_label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Multiple Sales Selection Section (for adding more sales) -->
                            <div class="row mb-4" id="salesSelectionSection"
                                style="display: {{ $invoice->client_id ? 'block' : 'none' }};">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="fas fa-shopping-cart me-2"></i>
                                                Ajouter des articles depuis des ventes
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
                                                        <i class="fas fa-download me-1"></i> Ajouter les ventes
                                                        sélectionnées
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger ms-2"
                                                        id="clearSaleItemsBtn" style="display: none;">
                                                        <i class="fas fa-trash me-1"></i> Effacer les articles des ventes
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="selectedSalesInfo" class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Sélectionnez une ou plusieurs ventes pour ajouter leurs articles à la
                                                    facture.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
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
                                                        <th width="15%">Source</th>
                                                        <th width="15%">Type</th>
                                                        <th width="25%">Article</th>
                                                        <th width="10%">Quantité</th>
                                                        <th width="15%">Prix Unitaire (DH)</th>
                                                        <th width="15%">Total (DH)</th>
                                                        <th width="5%">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="items-body">
                                                    <!-- Items will be loaded here -->
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="6" class="text-end"><strong>Sous-total:</strong>
                                                        </td>
                                                        <td><strong
                                                                id="subtotal">{{ number_format($invoice->total_amount, 2, ',', '.') }}
                                                                DH</strong></td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6" class="text-end"><strong>Remise:</strong></td>
                                                        <td>
                                                            <div class="input-group">
                                                                <input type="number"
                                                                    class="form-control form-control-sm text-end"
                                                                    id="discount" name="discount" min="0"
                                                                    step="0.01" value="{{ $invoice->discount }}"
                                                                    style="width: 100px;">
                                                                <span class="input-group-text">DH</span>
                                                            </div>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6" class="text-end"><strong>Total TTC:</strong>
                                                        </td>
                                                        <td><strong
                                                                id="invoice-total">{{ number_format($invoice->final_amount, 2, ',', '.') }}
                                                                DH</strong></td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <button type="button" class="btn btn-primary" id="add-item">
                                                    <i class="fas fa-plus me-1"></i> Ajouter une ligne manuelle
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
                                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Notes internes...">{{ $invoice->notes }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="terms_conditions" class="form-label">Conditions
                                                particulières</label>
                                            <textarea class="form-control" id="terms_conditions" name="terms_conditions" rows="3"
                                                placeholder="Conditions générales de vente...">{{ $invoice->terms_conditions }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save me-1"></i> Mettre à jour
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
            display: inline-block;
        }

        .source-manual-badge {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .item-row-loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .item-display {
            background-color: #f8f9fa;
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

            // Initialize multiple select2 for sales
            $('.select2-multiple').select2({
                language: "fr",
                placeholder: "Sélectionner des ventes...",
                allowClear: true,
                theme: "bootstrap-5",
                width: '100%'
            });

            let itemCounter = 0;
            let existingItems = @json($invoice->items);
            let salesOrdersData = {}; // Store sale order data for source display

            // First, load all sale order numbers for source display
            function loadSaleOrderNumbers() {
                let saleIds = [...new Set(existingItems.flatMap(i => Object.keys(i.source_sales_map || {})))];
                if (saleIds.length === 0) return Promise.resolve();

                return $.ajax({
                    url: "{{ route('sales.invoices.get-sales-by-ids') }}",
                    type: "POST",
                    data: {
                        order_ids: saleIds,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    async: false
                }).then(function(response) {
                    if (response.success) {
                        response.data.forEach(function(sale) {
                            salesOrdersData[sale.order_id] = sale;
                        });
                    }
                });
            }

            // Load existing items - SEPARATE handlers for sale items vs manual items.
            // Each saved line already carries its own source_sales_map (order_id ->
            // quantity contributed), so a line with one or several sources is
            // rendered directly - no need to re-merge rows on load.
            function loadExistingItems() {
                if (existingItems && existingItems.length > 0) {
                    // First load sale order numbers
                    loadSaleOrderNumbers().then(function() {
                        existingItems.forEach(function(item) {
                            if (item.source_sales_map && Object.keys(item.source_sales_map).length > 0) {
                                // This is a sale item - use sale row format
                                addSaleItemRowFromExisting(item);
                            } else {
                                // This is a manual item - use manual row format
                                addManualItemRow(item);
                            }
                        });
                        updateClearButtonVisibility();
                        updateInvoiceTotal();
                    });
                } else {
                    addManualItemRow();
                }
            }

            // Add existing SALE item row
            function addSaleItemRowFromExisting(item) {
                let rowId = 'item_' + Date.now() + '_' + itemCounter + '_' + Math.random().toString(36).substr(2,
                    6);
                let itemIndex = itemCounter;

                let sourceIds = Object.keys(item.source_sales_map || {});
                let labels = sourceIds.map(function(id) {
                    let sd = salesOrdersData[id];
                    return sd ? (sd.order_number + ' du ' + sd.order_date_formatted) : ('Vente #' + id);
                });
                let primaryLabel = sourceIds.length ?
                    (salesOrdersData[sourceIds[0]] ? salesOrdersData[sourceIds[0]].order_number : 'Vente #' +
                        sourceIds[0]) : '';

                let typeOptions = `
                    <option value="">Sélectionner</option>
                    <option value="raw_material" ${item.item_type === 'raw_material' ? 'selected' : ''}>Matière Première</option>
                    <option value="production" ${item.item_type === 'production' ? 'selected' : ''}>Production</option>
                    <option value="decoupage" ${item.item_type === 'decoupage' ? 'selected' : ''}>Découpage</option>
                    <option value="finale" ${item.item_type === 'finale' ? 'selected' : ''}>Vente</option>
                `;

                let row = `
                    <tr id="${rowId}" class="item-row" data-source="sale" data-index="${itemIndex}">
                        <td class="item-number">${itemCounter + 1}</td>
                        <td>
                            <span class="source-sale-badge" title="${labels.length > 1 ? 'Ventes : ' + labels.join(', ') : 'Vente: ' + labels[0]}">
                                ${labels.length > 1 ? '<i class="fas fa-tags me-1"></i>' + labels.length + ' ventes' : '<i class="fas fa-tag me-1"></i>' + primaryLabel}
                            </span>
                            <span class="source-sale-inputs"></span>
                        </td>
                        <td>
                            <select class="form-control item-type" data-row="${rowId}" required>
                                ${typeOptions}
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control item-display" value="${item.item_name.replace(/'/g, "\\'")}" readonly>
                            <input type="hidden" class="item-id" name="items[${itemIndex}][item_id]" value="${item.item_id}">
                            <input type="hidden" class="item-name" name="items[${itemIndex}][name]" value="${item.item_name.replace(/'/g, "\\'")}">
                            <input type="hidden" class="item-type-input" name="items[${itemIndex}][type]" value="${item.item_type}">
                            <input type="hidden" class="family-id" name="items[${itemIndex}][family_id]" value="${item.family_id || ''}">
                            <input type="hidden" class="family-name" name="items[${itemIndex}][family_name]" value="${item.family_name || ''}">
                        </td>
                        <td>
                            <input type="number" class="form-control item-quantity"
                                name="items[${itemIndex}][quantity]" min="0.0001" step="0.0001"
                                value="${item.quantity}" required>
                        </td>
                        <td>
                            <input type="number" class="form-control item-price"
                                name="items[${itemIndex}][unit_price]" min="0" step="0.01"
                                value="${item.unit_price}" required>
                        </td>
                        <td class="item-total">${(item.quantity * item.unit_price).toFixed(2)} DH</td>
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
                $(`#${rowId}`).data('source-labels', labels);
                setRowSourceQuantities($(`#${rowId}`), itemIndex, item.source_sales_map || {});

                // Bind events for sale items
                $(`#${rowId} .item-quantity, #${rowId} .item-price`).on('input', function() {
                    calculateItemTotal(rowId);
                    updateInvoiceTotal();
                });

                $(`#${rowId} .item-type`).on('change', function() {
                    let newType = $(this).val();
                    let row = $(this).data('row');
                    // For sale items, type change might affect price logic
                    if (newType) {
                        $(`#${row} .item-type-input`).val(newType);
                    }
                });

                $(`#${rowId} .remove-item`).off('click').on('click', function() {
                    let row = $(this).data('row');
                    $(`#${row}`).remove();
                    updateItemIndices();
                    updateInvoiceTotal();
                    updateClearButtonVisibility();
                });

                itemCounter++;
            }

            // Add existing MANUAL item row
            function addManualItemRow(item = null) {
                let rowId = 'item_' + Date.now() + '_' + itemCounter;
                let itemIndex = itemCounter;

                let typeOptions = `
                    <option value="">Sélectionner</option>
                    <option value="raw_material">Matière Première</option>
                    <option value="production">Production</option>
                    <option value="decoupage">Découpage</option>
                    <option value="finale" selected>Vente</option>
                `;

                let selectedType = item ? item.item_type : 'finale';

                let row = `
                    <tr id="${rowId}" class="item-row" data-source="manual" data-index="${itemIndex}">
                        <td class="item-number">${itemCounter + 1}</td>
                        <td>
                            <span class="source-sale-badge source-manual-badge">
                                <i class="fas fa-plus-circle me-1"></i>Ajout manuel
                            </span>
                        </td>
                        <td>
                            <select class="form-control item-type" data-row="${rowId}" required>
                                ${typeOptions.replace('value="'+selectedType+'"', 'value="'+selectedType+'" selected')}
                            </select>
                        </td>
                        <td>
                            <select class="form-control item-select" data-row="${rowId}" style="width:100%;" required>
                                <option value="">Sélectionner un article</option>
                            </select>
                            <input type="hidden" class="item-id" name="items[${itemIndex}][item_id]" value="${item ? item.item_id : ''}">
                            <input type="hidden" class="item-name" name="items[${itemIndex}][name]" value="${item ? item.item_name : ''}">
                            <input type="hidden" class="item-type-input" name="items[${itemIndex}][type]" value="${item ? item.item_type : ''}">
                            <input type="hidden" class="family-id" name="items[${itemIndex}][family_id]" value="${item ? item.family_id || '' : ''}">
                            <input type="hidden" class="family-name" name="items[${itemIndex}][family_name]" value="${item ? item.family_name || '' : ''}">
                        </td>
                        <td>
                            <input type="number" class="form-control item-quantity"
                                name="items[${itemIndex}][quantity]" min="0.0001" step="0.0001"
                                value="${item ? item.quantity : 1}" required ${item ? '' : 'disabled'}>
                        </td>
                        <td>
                            <input type="number" class="form-control item-price"
                                name="items[${itemIndex}][unit_price]" min="0" step="0.01"
                                value="${item ? item.unit_price : 0}" required ${item ? '' : 'disabled'}>
                        </td>
                        <td class="item-total">${item ? (item.quantity * item.unit_price).toFixed(2) : '0.00'} DH</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-item" data-row="${rowId}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#items-body').append(row);

                // Initialize Select2 for manual items
                $(`#${rowId} .item-select`).select2({
                    language: "fr",
                    placeholder: "Sélectionner un article...",
                    width: '100%'
                });

                $(`#${rowId} .item-type`).change(function() {
                    let type = $(this).val();
                    let row = $(this).data('row');
                    loadProductsForType(type, row);
                });

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

                        updateManualItemFromSelection(row, itemData);
                    }
                });

                $(`#${rowId} .item-quantity, #${rowId} .item-price`).on('input', function() {
                    calculateItemTotal(rowId);
                    updateInvoiceTotal();
                });

                $(`#${rowId} .remove-item`).click(function() {
                    let row = $(this).data('row');
                    $(`#${row}`).remove();
                    updateItemIndices();
                    updateInvoiceTotal();
                });

                // Load products for the type if we have a client
                let clientId = $('#client_id').val();
                if (clientId) {
                    loadProductsForType(selectedType, rowId, item);
                } else if (item) {
                    // If we have an existing item but no client selected yet, just set the values
                    $(`#${rowId} .item-quantity`).prop('disabled', false);
                    $(`#${rowId} .item-price`).prop('disabled', false);
                    calculateItemTotal(rowId);
                } else {
                    $(`#${rowId} .item-select`).empty().append(
                        '<option value="">Veuillez d\'abord sélectionner un client</option>');
                }

                itemCounter++;
            }

            // Add new sale item from selected sale
            function addItemRowFromSaleData(itemData, saleData) {
                let rowId = 'item_' + Date.now() + '_' + itemCounter + '_' + Math.random().toString(36).substr(2,
                    6);
                let itemIndex = itemCounter;

                let typeOptions = `
                    <option value="">Sélectionner</option>
                    <option value="raw_material" ${itemData.type === 'raw_material' ? 'selected' : ''}>Matière Première</option>
                    <option value="production" ${itemData.type === 'production' ? 'selected' : ''}>Production</option>
                    <option value="decoupage" ${itemData.type === 'decoupage' ? 'selected' : ''}>Découpage</option>
                    <option value="finale" ${itemData.type === 'finale' ? 'selected' : ''}>Vente</option>
                `;

                let row = `
                    <tr id="${rowId}" class="item-row" data-source="sale" data-index="${itemIndex}">
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
                            <input type="text" class="form-control item-display" value="${itemData.name.replace(/'/g, "\\'")}" readonly>
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

                $(`#${rowId} .item-quantity, #${rowId} .item-price`).on('input', function() {
                    calculateItemTotal(rowId);
                    updateInvoiceTotal();
                });

                $(`#${rowId} .remove-item`).off('click').on('click', function() {
                    let row = $(this).data('row');
                    $(`#${row}`).remove();
                    updateItemIndices();
                    updateInvoiceTotal();
                    updateClearButtonVisibility();
                });

                itemCounter++;
                updateInvoiceTotal();
                updateClearButtonVisibility();
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
            // item_id + family_id). If found, add `quantity` to it and return
            // true. The row keeps track of every vente that contributed to it
            // (and how much), so the badge lists them all instead of pointing to
            // a single vente.
            function mergeQuantityIntoExistingSaleRow(itemId, type, familyId, quantity, saleLabel, orderId) {
                familyId = familyId || '';
                let $match = null;

                $('#items-body tr[data-source="sale"]').each(function() {
                    let $row = $(this);
                    let rowItemId = $row.find('.item-id').val();
                    let rowType = $row.find('.item-type-input').val();
                    let rowFamilyId = $row.find('.family-id').val() || '';

                    if (rowItemId == itemId && rowType === type &&
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
                    parseFloat(quantity);
                $match.find('.item-quantity').val(newQuantity);
                calculateItemTotal(rowId);
                updateInvoiceTotal();

                // Record this vente's contribution alongside whatever this row already had.
                let quantities = $.extend({}, $match.data('source-quantities') || {});
                quantities[orderId] = (quantities[orderId] || 0) + parseFloat(quantity);
                setRowSourceQuantities($match, getRowIndex($match), quantities);

                let sources = ($match.data('source-labels') || []).concat(saleLabel ? [saleLabel] : []);
                $match.data('source-labels', sources);

                $match.find('.source-sale-badge')
                    .attr('title', 'Ventes : ' + sources.join(', '))
                    .html('<i class="fas fa-tags me-1"></i>' + sources.length + ' ventes');

                return true;
            }

            // Add items from a sale to the invoice. If a product already has a row
            // (from a previously-loaded vente, or already saved on the invoice),
            // merge into it (sum the quantity) instead of adding a duplicate line.
            function addItemsFromSale(sale) {
                let itemsAdded = 0;
                let saleLabel = sale.order_number + ' du ' + sale.order_date_formatted;

                sale.items.forEach(function(item) {
                    let merged = mergeQuantityIntoExistingSaleRow(item.item_id, item.type, item
                        .family_id, item.quantity, saleLabel, sale.order_id);
                    if (!merged) {
                        addItemRowFromSaleData(item, sale);
                        itemsAdded++;
                    }
                });
                return itemsAdded;
            }

            // Show/hide clear button based on sale items
            function updateClearButtonVisibility() {
                let hasSaleItems = $('#items-body tr[data-source="sale"]').length > 0;
                $('#clearSaleItemsBtn').toggle(hasSaleItems);
            }

            // Load sales orders for selected client
            function loadSalesOrdersForClient(clientId) {
                let $select = $('#sales_order_ids');
                $select.empty().append('<option value="">Chargement des ventes...</option>');
                $select.prop('disabled', true);

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
                                    .text(order.order_number + ' - ' + order
                                        .order_date_formatted + ' (' + order.items_count +
                                        ' articles) - ' + formatMoney(order.total_amount));
                                $select.append(option);
                            });
                            $select.prop('disabled', false);
                        } else {
                            $select.append(
                                '<option value="">Aucune vente trouvée pour ce client</option>');
                            $select.prop('disabled', false);
                        }
                    },
                    error: function() {
                        $select.empty().append('<option value="">Erreur de chargement</option>');
                        $select.prop('disabled', false);
                        showToast('error', 'Erreur lors du chargement des ventes');
                    }
                });
            }

            // Load selected sales and add to invoice
            $('#loadSelectedSalesBtn').click(function() {
                let selectedOrders = $('#sales_order_ids').val();
                if (!selectedOrders || selectedOrders.length === 0) {
                    showToast('warning', 'Veuillez sélectionner au moins une vente');
                    return;
                }

                let $btn = $(this);
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Chargement...');
                $('body').append(
                    '<div class="loading-overlay"><div class="loading-spinner"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Chargement des ventes...</p></div></div>'
                );

                $.ajax({
                    url: "{{ route('sales.invoices.get-multiple-sales') }}",
                    type: "POST",
                    data: {
                        order_ids: selectedOrders,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            let totalItemsAdded = 0;
                            response.data.forEach(function(sale) {
                                totalItemsAdded += addItemsFromSale(sale);
                            });
                            showToast('success', totalItemsAdded +
                                ' articles ajoutés avec succès!');
                            $('#sales_order_ids').val(null).trigger('change');
                            updateClearButtonVisibility();
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
                        $('.loading-overlay').remove();
                    }
                });
            });

            // Clear all sale items
            $('#clearSaleItemsBtn').click(function() {
                if (confirm('Supprimer tous les articles provenant des ventes?')) {
                    $('#items-body tr[data-source="sale"]').remove();
                    updateItemIndices();
                    updateInvoiceTotal();
                    updateClearButtonVisibility();
                    showToast('success', 'Articles des ventes supprimés');
                }
            });

            // Load products for type (for manual rows)
            function loadProductsForType(type, rowId, existingItem = null) {
                let select = $(`#${rowId} .item-select`);
                let clientId = $('#client_id').val();

                if (!clientId) {
                    showToast('warning', 'Veuillez d\'abord sélectionner un client');
                    select.empty().append('<option value="">Sélectionner d\'abord un client</option>');
                    return;
                }

                select.empty().append('<option value="">Chargement...</option>').prop('disabled', false);

                let url = type === 'raw_material' ? "{{ route('sales.invoices.raw-materials.list') }}" :
                    "{{ route('sales.invoices.products.by-type', '') }}/" + type;

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        select.empty().append('<option value="">Sélectionner un article</option>');
                        let items = response.data || response;
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
                                        .id && existingItem.family_id == family.id) {
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
                        select.trigger('change');
                        $(`#${rowId} .item-type-input`).val(type);
                        $(`#${rowId} .item-quantity`).prop('disabled', false);
                    },
                    error: function() {
                        select.empty().append('<option value="">Erreur de chargement</option>');
                    }
                });
            }

            // Update manual item from selection
            function updateManualItemFromSelection(rowId, itemData) {
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

            // Get price based on client type
            function getPriceByClientType(selectedOption) {
                let clientType = $('#client_id option:selected').data('client-type') || 'client';
                switch (clientType) {
                    case 'grossiste':
                        return selectedOption.data('family-price-grossiste') || selectedOption.data('price') || 0;
                    case 'commerciale':
                        return selectedOption.data('family-price-commercial') || selectedOption.data('price') || 0;
                    case 'special':
                        return selectedOption.data('family-price-special') || selectedOption.data('price') || 0;
                    default:
                        return selectedOption.data('family-price-client') || selectedOption.data('price') || 0;
                }
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

            // Client change handlers
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

            $('#discount').on('input', function() {
                updateInvoiceTotal();
            });
            $('#add-item').click(function() {
                addManualItemRow();
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
                    '<i class="fas fa-spinner fa-spin me-1"></i> Mise à jour...');
                const formData = new FormData(this);
                formData.append('_method', 'PUT');
                let invoiceId = $('#invoice_id').val();

                $.ajax({
                    url: "{{ route('sales.invoices.update', '') }}/" + invoiceId,
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
                var toast = $('<div class="toast align-items-center text-white bg-' + (type === 'success' ?
                        'success' : (type === 'warning' ? 'warning' : 'danger')) +
                    ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                    '<div class="d-flex"><div class="toast-body">' + message +
                    '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>'
                );
                $('#toast-container').append(toast);
                var bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();
                setTimeout(function() {
                    toast.remove();
                }, 5000);
            }

            // Load existing items
            loadExistingItems();

            let initialClientId = $('#client_id').val();
            if (initialClientId) {
                loadSalesOrdersForClient(initialClientId);
                $('#salesSelectionSection').show();
            }
        });
    </script>
@endpush
