@extends('layouts.app')

@section('title', 'Modifier Devis')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Devis</h4>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-primary p-2">N° {{ $quotation->quote_number }}</span>
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
                            <i class="fas fa-edit me-2"></i>Modifier le devis N° {{ $quotation->quote_number }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="quotationForm">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="quote_id" value="{{ $quotation->quote_id }}">

                            <!-- Basic Info Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="quote_date" class="form-label">Date Devis *</label>
                                        <input type="date" class="form-control" id="quote_date" name="quote_date"
                                            value="{{ $quotation->quote_date->format('Y-m-d') }}" required>
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
                                                    {{ $quotation->client_id == $client->client_id ? 'selected' : '' }}>
                                                    {{ $client->display_name }} ({{ $client->phone }}) -
                                                    {{ $client->client_type_label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="status" class="form-label">Statut *</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="draft" {{ $quotation->status == 'draft' ? 'selected' : '' }}>
                                                Brouillon</option>
                                            <option value="sent" {{ $quotation->status == 'sent' ? 'selected' : '' }}>
                                                Envoyé</option>
                                            <option value="accepted"
                                                {{ $quotation->status == 'accepted' ? 'selected' : '' }}>Accepté</option>
                                            <option value="rejected"
                                                {{ $quotation->status == 'rejected' ? 'selected' : '' }}>Refusé</option>
                                            <option value="expired"
                                                {{ $quotation->status == 'expired' ? 'selected' : '' }}>Expiré</option>
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
                                                <!-- Items will be loaded here -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-end"><strong>Sous-total:</strong></td>
                                                    <td><strong
                                                            id="subtotal">{{ number_format($quotation->total_amount, 2, ',', '.') }}
                                                            DH</strong></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" class="text-end"><strong>Remise:</strong></td>
                                                    <td>
                                                        <div class="input-group">
                                                            <input type="number"
                                                                class="form-control form-control-sm text-end" id="discount"
                                                                name="discount" min="0" step="0.01"
                                                                value="{{ $quotation->discount }}" style="width: 100px;">
                                                            <span class="input-group-text">DH</span>
                                                        </div>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" class="text-end"><strong>Total TTC:</strong></td>
                                                    <td><strong
                                                            id="order-total">{{ number_format($quotation->final_amount, 2, ',', '.') }}
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

                            <!-- Notes and Terms Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Notes internes...">{{ $quotation->notes }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="terms_conditions" class="form-label">Conditions particulières</label>
                                        <textarea class="form-control" id="terms_conditions" name="terms_conditions" rows="3"
                                            placeholder="Conditions générales de vente...">{{ $quotation->terms_conditions }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <div class="form-group">
                                        <label for="observation" class="form-label">Observation</label>
                                        <textarea class="form-control" id="observation" name="observation" rows="3"
                                            placeholder="Observation affichée sur le devis...">{{ $quotation->observation }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-1"></i> Mettre à jour
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
            let existingItems = @json($quotation->items);

            // Load existing items
            if (existingItems && existingItems.length > 0) {
                existingItems.forEach(function(item, index) {
                    addItemRow(item);
                });
            } else {
                // Add first item row if no items
                addItemRow();
            }

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

                let selectedType = item ? item.item_type : 'finale';

                let row = `
                <tr id="${rowId}" data-index="${itemIndex}">
                    <td>${itemCounter + 1}</td>
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

                $(`#${rowId} .item-select`).select2({
                    language: "fr",
                    placeholder: "Sélectionner un article...",
                    width: '100%'
                });

                $(`#${rowId} .item-type`).change(function() {
                    let type = $(this).val();
                    let row = $(this).data('row');
                    loadItemsForType(type, row);
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

                $(`#${rowId} .item-quantity, #${rowId} .item-price`).on('input', function() {
                    calculateItemTotal(rowId);
                    updateOrderTotal();
                });

                $(`#${rowId} .remove-item`).click(function() {
                    let row = $(this).data('row');
                    $(`#${row}`).remove();
                    updateItemIndices();
                    updateOrderTotal();
                });

                // Load items for the type
                loadItemsForType(selectedType, rowId, item);

                itemCounter++;
            }

            function loadItemsForType(type, rowId, existingItem = null) {
                let select = $(`#${rowId} .item-select`);
                let clientId = $('#client_id').val();

                if (!clientId) {
                    showToast('warning', 'Veuillez d\'abord sélectionner un client');
                    select.empty().append('<option value="">Sélectionner d\'abord un client</option>');
                    return;
                }

                select.empty().append('<option value="">Chargement...</option>').prop('disabled', false);

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

                                    // Check if this is the existing item
                                    if (existingItem &&
                                        existingItem.item_id == item.id &&
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

                                // Check if this is the existing item
                                if (existingItem && existingItem.item_id == item.id && !
                                    existingItem.family_id) {
                                    option.attr('selected', 'selected');
                                }

                                select.append(option);
                            }
                        });

                        select.trigger('change');
                        $(`#${rowId} .item-type-input`).val(type);
                    },
                    error: function() {
                        select.empty().append('<option value="">Erreur de chargement</option>');
                    }
                });
            }

            $('#client_id').change(function() {
                let clientId = $(this).val();
                let clientType = $(this).find(':selected').data('client-type') || 'client';

                if (clientId) {
                    $('#items-body tr').each(function() {
                        let rowId = $(this).attr('id');
                        let itemSelect = $(this).find('.item-select');
                        let selectedValue = itemSelect.val();

                        if (selectedValue) {
                            let selectedOption = itemSelect.find(':selected');
                            let itemData = {};

                            if (selectedValue.includes('_')) {
                                let parts = selectedValue.split('_');
                                let price = 0;

                                switch (clientType) {
                                    case 'grossiste':
                                        price = selectedOption.data('family-price-grossiste') || 0;
                                        break;
                                    case 'commerciale':
                                        price = selectedOption.data('family-price-commercial') || 0;
                                        break;
                                    case 'special':
                                        price = selectedOption.data('family-price-special') || 0;
                                        break;
                                    default:
                                        price = selectedOption.data('family-price-client') || 0;
                                }

                                $(this).find('.item-price').val(price);
                            } else {
                                $(this).find('.item-price').val(selectedOption.data('price') || 0);
                            }

                            calculateItemTotal(rowId);
                        }
                    });

                    updateOrderTotal();
                }
            });

            function updateItemFromSelection(rowId, itemData) {
                let clientType = $('#client_id option:selected').data('client-type') || 'client';

                $(`#${rowId} .item-id`).val(itemData.id);
                $(`#${rowId} .item-name`).val(itemData.name);

                let price = 0;
                if (itemData.hasFamilies && itemData.familyId) {
                    $(`#${rowId} .family-id`).val(itemData.familyId);
                    $(`#${rowId} .family-name`).val(itemData.familyName);

                    switch (clientType) {
                        case 'grossiste':
                            price = itemData.familyPriceGrossiste || 0;
                            break;
                        case 'commerciale':
                            price = itemData.familyPriceCommercial || 0;
                            break;
                        case 'special':
                            price = itemData.familyPriceSpecial || 0;
                            break;
                        default:
                            price = itemData.familyPriceClient || 0;
                    }
                } else {
                    $(`#${rowId} .family-id`).val('');
                    $(`#${rowId} .family-name`).val('');
                    price = itemData.price;
                }

                $(`#${rowId} .item-price`).val(price).prop('disabled', false);
                $(`#${rowId} .item-quantity`).prop('disabled', false);

                calculateItemTotal(rowId);
                updateOrderTotal();
            }

            function calculateItemTotal(rowId) {
                let quantity = parseFloat($(`#${rowId} .item-quantity`).val()) || 0;
                let price = parseFloat($(`#${rowId} .item-price`).val()) || 0;
                let total = quantity * price;
                $(`#${rowId} .item-total`).text(total.toFixed(2) + ' DH');
            }

            function updateItemIndices() {
                $('#items-body tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                    $(this).attr('data-index', index);

                    $(this).find('.item-id').attr('name', `items[${index}][item_id]`);
                    $(this).find('.item-name').attr('name', `items[${index}][name]`);
                    $(this).find('.item-type-input').attr('name', `items[${index}][type]`);
                    $(this).find('.item-quantity').attr('name', `items[${index}][quantity]`);
                    $(this).find('.item-price').attr('name', `items[${index}][unit_price]`);
                    $(this).find('.family-id').attr('name', `items[${index}][family_id]`);
                    $(this).find('.family-name').attr('name', `items[${index}][family_name]`);
                });
            }

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
                });

                if (!valid) return;

                // Show loading
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Mise à jour...');

                // Prepare form data
                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                let quoteId = $('#quote_id').val();

                $.ajax({
                    url: "{{ route('sales.quotations.update', '') }}/" + quoteId,
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
