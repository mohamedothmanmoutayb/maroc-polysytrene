@extends('layouts.app')

@section('title', 'Nouveau Devis')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Nouveau Devis</h4>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-primary p-2">N° {{ $nextQuoteNumber }}</span>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item d-flex align-items-center">
                                        <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                            <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a class="text-muted text-decoration-none"
                                            href="{{ route('sales.quotations.index') }}">
                                            Devis
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
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-plus-circle me-2"></i>Créer un nouveau devis
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="quotationForm">
                            @csrf
                            <input type="hidden" id="quote_number" name="quote_number" value="{{ $nextQuoteNumber }}">

                            <!-- Basic Info Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="quote_date" class="form-label">Date Devis *</label>
                                        <input type="date" class="form-control" id="quote_date" name="quote_date"
                                            value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>

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

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status" class="form-label">Statut *</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="draft" selected>Brouillon</option>
                                            <option value="sent">Envoyé</option>
                                            <option value="accepted">Accepté</option>
                                            <option value="rejected">Refusé</option>
                                            <option value="expired">Expiré</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items Section -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Articles du Devis</h6>
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
                                                <!-- First row will be added automatically -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-end"><strong>Sous-total:</strong></td>
                                                    <td><strong id="subtotal">0.00 DH</strong></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" class="text-end"><strong>Remise:</strong></td>
                                                    <td>
                                                        <div class="input-group">
                                                            <input type="number"
                                                                class="form-control form-control-sm text-end" id="discount"
                                                                name="discount" min="0" step="0.01" value="0"
                                                                style="width: 100px;">
                                                            <span class="input-group-text">DH</span>
                                                        </div>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" class="text-end"><strong>Total TTC:</strong></td>
                                                    <td><strong id="order-total">0.00 DH</strong></td>
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

                            <!-- Notes Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Notes internes..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="observation" class="form-label">Observation</label>
                                        <textarea class="form-control" id="observation" name="observation" rows="3" placeholder="Observation affichée sur le devis..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('sales.quotations.index') }}" class="btn btn-secondary">
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

            // Add first item row automatically with "finale" type selected
            addItemRow();

            // Add item button
            $('#add-item').click(function() {
                addItemRow();
            });

            // Discount change handler
            $('#discount').on('input', function() {
                updateOrderTotal();
            });

            // Function to add item row
            function addItemRow(item = null) {
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
            <tr id="${rowId}" data-index="${itemIndex}">
                <td>${itemCounter + 1}</td>
                <td>
                    <select class="form-control item-type" data-row="${rowId}" required>
                        ${typeOptions}
                    </select>
                </td>
                <td>
                    <select class="form-control item-select" data-row="${rowId}" style="width:100%;" required disabled>
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
                        name="items[${itemIndex}][unit_price]" min="0" step="1" value="0" required disabled>
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

                // Initialize Select2 but keep it disabled
                $(`#${rowId} .item-select`).select2({
                    language: "fr",
                    placeholder: "Sélectionner un article...",
                    width: '100%',
                    disabled: true
                });

                // Bind type change event
                $(`#${rowId} .item-type`).change(function() {
                    let type = $(this).val();
                    let row = $(this).data('row');

                    if (type) {
                        // Disable the select immediately and show loading
                        let itemSelect = $(`#${row} .item-select`);
                        itemSelect.empty().append('<option value="">Chargement...</option>');
                        itemSelect.prop('disabled', true);

                        // Also disable quantity and price inputs
                        $(`#${row} .item-quantity`).prop('disabled', true);
                        $(`#${row} .item-price`).prop('disabled', true);

                        // Clear existing values
                        $(`#${row} .item-id`).val('');
                        $(`#${row} .item-name`).val('');
                        $(`#${row} .family-id`).val('');
                        $(`#${row} .family-name`).val('');
                        $(`#${row} .item-total`).text('0.00 DH');

                        loadItemsForType(type, row);
                    } else {
                        // If no type selected, disable everything
                        $(`#${row} .item-select`).empty().append(
                            '<option value="">Sélectionner un type d\'abord</option>').prop('disabled',
                            true);
                        $(`#${row} .item-quantity`).prop('disabled', true);
                        $(`#${row} .item-price`).prop('disabled', true);
                        $(`#${row} .item-id`).val('');
                        $(`#${row} .item-name`).val('');
                        $(`#${row} .family-id`).val('');
                        $(`#${row} .family-name`).val('');
                        $(`#${row} .item-total`).text('0.00 DH');
                    }

                    updateOrderTotal();
                });

                // Bind item select change event
                $(`#${rowId} .item-select`).change(function() {
                    let row = $(this).data('row');
                    let selectedOption = $(this).find(':selected');
                    let value = $(this).val();

                    if (value) {
                        let itemData = {};

                        if (value.includes('_')) {
                            // This is a product with family
                            let parts = value.split('_');
                            let productId = parts[0];
                            let familyId = parts[1];

                            itemData = {
                                id: productId,
                                name: selectedOption.data('product-name'),
                                code: selectedOption.data('product-code'),
                                price: selectedOption.data('price') || 0,
                                hasFamilies: true,
                                familyId: familyId,
                                familyName: selectedOption.data('family-name'),
                                familyPriceClient: selectedOption.data('family-price-client'),
                                familyPriceGrossiste: selectedOption.data('family-price-grossiste'),
                                familyPriceCommercial: selectedOption.data('family-price-commercial'),
                                familyPriceSpecial: selectedOption.data('family-price-special')
                            };
                        } else {
                            // This is a product without family or raw material
                            itemData = {
                                id: value,
                                name: selectedOption.data('name'),
                                code: selectedOption.data('code'),
                                price: selectedOption.data('price') || 0,
                                hasFamilies: false,
                                familyId: null,
                                familyName: null
                            };
                        }

                        updateItemFromSelection(row, itemData);
                    }
                });

                // Bind quantity and price change events
                $(`#${rowId} .item-quantity, #${rowId} .item-price`).on('input', function() {
                    calculateItemTotal(rowId);
                    updateOrderTotal();
                });

                // Bind remove button
                $(`#${rowId} .remove-item`).click(function() {
                    let row = $(this).data('row');
                    $(`#${row}`).remove();
                    updateItemIndices();
                    updateOrderTotal();
                });

                // Load products for "finale" type by default (since it's selected)
                let clientId = $('#client_id').val();
                if (clientId) {
                    loadItemsForType('finale', rowId);
                } else {
                    // If no client selected, show message and keep disabled
                    $(`#${rowId} .item-select`).empty().append(
                        '<option value="">Veuillez d\'abord sélectionner un client</option>');
                    $(`#${rowId} .item-select`).prop('disabled', true);
                }

                itemCounter++;
            }

            function loadItemsForType(type, rowId) {
                let select = $(`#${rowId} .item-select`);
                let clientId = $('#client_id').val();

                if (!clientId) {
                    showToast('warning', 'Veuillez d\'abord sélectionner un client');
                    select.empty().append('<option value="">Sélectionner d\'abord un client</option>');
                    select.prop('disabled', true);
                    return;
                }

                // Keep select disabled and show loading state
                select.empty().append('<option value="">Chargement...</option>');
                select.prop('disabled', true);

                // Also disable quantity and price while loading
                $(`#${rowId} .item-quantity`).prop('disabled', true);
                $(`#${rowId} .item-price`).prop('disabled', true);

                let url = '';
                if (type === 'raw_material') {
                    url = "{{ route('sales.quotations.raw-materials.list') }}";
                } else {
                    url = "{{ route('sales.quotations.products.by-type', '') }}/" + type;
                }

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        select.empty().append('<option value="">Sélectionner un article</option>');

                        let items = response.data || response;
                        if (items.length === 0) {
                            select.append('<option value="">Aucun article trouvé</option>');
                        } else {
                            items.forEach(function(item) {
                                if (item.has_families && item.families && item.families.length >
                                    0) {
                                    item.families.forEach(function(family) {
                                        let optionValue = item.id + '_' + family.id;
                                        let displayName = item.name + ' - ' + family
                                            .name;

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
                                            .attr('data-price', family.prix_client ||
                                                item.price || 0)
                                            .attr('data-has-families', true)
                                            .text(displayName + (item.code ? ' (' + item
                                                .code + ')' : ''));

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

                                    select.append(option);
                                }
                            });
                        }

                        // Enable the select after loading
                        select.prop('disabled', false);
                        $(`#${rowId} .item-type-input`).val(type);

                        // Refresh Select2 if it's initialized
                        select.trigger('change.select2');
                    },
                    error: function() {
                        select.empty().append('<option value="">Erreur de chargement</option>');
                        select.prop('disabled', true);
                        showToast('error', 'Erreur lors du chargement des articles');
                    }
                });
            }

            $('#client_id').change(function() {
                let clientId = $(this).val();
                let clientType = $(this).find(':selected').data('client-type') || 'client';

                if (clientId) {
                    // Reload items for each row with the new client
                    $('#items-body tr').each(function() {
                        let rowId = $(this).attr('id');
                        let itemType = $(this).find('.item-type').val();

                        if (itemType) {
                            // Disable selects immediately while reloading
                            $(this).find('.item-select').prop('disabled', true).empty().append(
                                '<option value="">Chargement...</option>');
                            $(this).find('.item-quantity').prop('disabled', true);
                            $(this).find('.item-price').prop('disabled', true);
                            $(this).find('.item-total').text('0.00 DH');

                            // Reload the items for this row
                            loadItemsForType(itemType, rowId);
                        } else {
                            // If no type selected, just disable
                            $(this).find('.item-select').prop('disabled', true).empty().append(
                                '<option value="">Sélectionner un type</option>');
                            $(this).find('.item-quantity').prop('disabled', true);
                            $(this).find('.item-price').prop('disabled', true);
                            $(this).find('.item-total').text('0.00 DH');
                        }
                    });

                    // Update prices based on client type with rounding
                    setTimeout(function() {
                        $('#items-body tr').each(function() {
                            let rowId = $(this).attr('id');
                            let itemSelect = $(this).find('.item-select');
                            let selectedValue = itemSelect.val();

                            if (selectedValue) {
                                let selectedOption = itemSelect.find(':selected');

                                if (selectedValue.includes('_')) {
                                    // Product with family
                                    let price = 0;
                                    switch (clientType) {
                                        case 'grossiste':
                                            price = selectedOption.data(
                                                'family-price-grossiste') || 0;
                                            break;
                                        case 'commerciale':
                                            price = selectedOption.data(
                                                'family-price-commercial') || 0;
                                            break;
                                        case 'special':
                                            price = selectedOption.data(
                                                'family-price-special') || 0;
                                            break;
                                        default:
                                            price = selectedOption.data(
                                                'family-price-client') || 0;
                                    }
                                    // Round up the price
                                    $(this).find('.item-price').val(roundUpPrice(parseFloat(
                                        price)));
                                } else {
                                    // Simple product
                                    let price = selectedOption.data('price') || 0;
                                    // Round up the price
                                    $(this).find('.item-price').val(roundUpPrice(parseFloat(
                                        price)));
                                }

                                calculateItemTotal(rowId);
                            }
                        });
                        updateOrderTotal();
                    }, 500); // Small delay to ensure items are loaded
                } else {
                    // If no client selected, disable all item selects
                    $('#items-body tr').each(function() {
                        $(this).find('.item-select').prop('disabled', true).empty().append(
                            '<option value="">Sélectionner un client d\'abord < /option>');
                        $(this).find('.item-quantity').prop('disabled', true);
                        $(this).find(
                            '.item-price').prop('disabled', true);
                        $(this).find('.item-id')
                            .val('');
                        $(this).find('.item-name').val('');
                        $(this).find(
                            '.family-id').val('');
                        $(this).find('.family-name').val('');
                        $(
                            this).find('.item-total').text('0.00 DH');
                    });
                    updateOrderTotal();
                }
            });

            function roundUpPrice(price) {
                if (!price || price === 0) return 0;
                if (price !== Math.floor(price)) {
                    return Math.ceil(price);
                }
                return Math.floor(price);
            }

            function updateItemFromSelection(rowId, itemData) {
                let clientType = $('#client_id option:selected').data('client-type') || 'client';

                $(`#${rowId} .item-id`).val(itemData.id);
                $(`#${rowId} .item-name`).val(itemData.name);

                // Set price based on client type and family
                let price = 0;
                if (itemData.hasFamilies && itemData.familyId) {
                    $(`#${rowId} .family-id`).val(itemData.familyId);
                    $(`#${rowId} .family-name`).val(itemData.familyName);

                    // Get price based on client type
                    switch (clientType) {
                        case 'grossiste':
                            price = parseFloat(itemData.familyPriceGrossiste) || 0;
                            break;
                        case 'commerciale':
                            price = parseFloat(itemData.familyPriceCommercial) || 0;
                            break;
                        case 'special':
                            price = parseFloat(itemData.familyPriceSpecial) || 0;
                            break;
                        default: // client
                            price = parseFloat(itemData.familyPriceClient) || 0;
                    }
                } else {
                    $(`#${rowId} .family-id`).val('');
                    $(`#${rowId} .family-name`).val('');
                    price = parseFloat(itemData.price) || 0;
                }

                price = roundUpPrice(price);

                $(`#${rowId} .item-price`).val(price).prop('disabled', false);
                $(`#${rowId} .item-quantity`).prop('disabled', false);

                calculateItemTotal(rowId);
                updateOrderTotal();
            }

            // Calculate item total
            function calculateItemTotal(rowId) {
                let quantity = parseFloat($(`#${rowId} .item-quantity`).val()) || 0;
                let price = parseFloat($(`#${rowId} .item-price`).val()) || 0;
                let total = quantity * price;
                $(`#${rowId} .item-total`).text(total.toFixed(2) + ' DH');
            }

            // Update item indices after removal
            function updateItemIndices() {
                $('#items-body tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                    $(this).attr('data-index', index);

                    // Update name attributes
                    $(this).find('.item-id').attr('name', `items[${index}][item_id]`);
                    $(this).find('.item-name').attr('name', `items[${index}][name]`);
                    $(this).find('.item-type-input').attr('name', `items[${index}][type]`);
                    $(this).find('.item-quantity').attr('name', `items[${index}][quantity]`);
                    $(this).find('.item-price').attr('name', `items[${index}][unit_price]`);
                    $(this).find('.family-id').attr('name', `items[${index}][family_id]`);
                    $(this).find('.family-name').attr('name', `items[${index}][family_name]`);
                });
            }

            // Update order total
            function updateOrderTotal() {
                let subtotal = 0;
                $('.item-total').each(function() {
                    let text = $(this).text().replace(' DH', '').replace(/,/g, '');
                    subtotal += parseFloat(text) || 0;
                });

                $('#subtotal').text(subtotal.toFixed(2) + ' DH');

                let discount = parseFloat($('#discount').val()) || 0;
                let total = subtotal - discount;
                if (total < 0) total = 0;

                $('#order-total').text(total.toFixed(2) + ' DH');
            }

            // Form submission
            $('#quotationForm').submit(function(e) {
                e.preventDefault();

                // Validate items
                if ($('#items-body tr').length === 0) {
                    showToast('error', 'Veuillez ajouter au moins un article');
                    return;
                }

                // Validate client
                let clientId = $('#client_id').val();
                if (!clientId) {
                    showToast('error', 'Veuillez sélectionner un client');
                    return;
                }

                // Validate each item has required fields
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

                    // Round up price before submitting
                    let priceInput = $(this).find('.item-price');
                    let currentPrice = parseFloat(priceInput.val()) || 0;
                    priceInput.val(roundUpPrice(currentPrice));
                });

                if (!valid) return;

                // Show loading
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                // Prepare form data
                const formData = new FormData(this);

                $.ajax({
                    url: "{{ route('sales.quotations.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('sales.quotations.index') }}";
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

            // Toast function
            function showToast(type, message) {
                var toast = $('<div class="toast align-items-center text-white bg-' +
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
                    toast.remove();
                }, 5000);
            }
        });
    </script>
@endpush
