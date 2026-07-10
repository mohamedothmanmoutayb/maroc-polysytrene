@extends('layouts.app')

@section('title', 'Modifier Avoir - ' . $creditNote->credit_note_number)

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Avoir</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('credit-notes.index') }}">
                                        Avoirs
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        {{ $creditNote->credit_note_number }}
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
                            <i class="fas fa-edit me-2"></i>Modifier l'Avoir
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="creditNoteForm">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="credit_note_id" value="{{ $creditNote->credit_note_id }}">
                            <input type="hidden" id="credit_note_number" name="credit_note_number"
                                value="{{ $creditNote->credit_note_number }}">

                            <!-- Basic Info -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="credit_note_date" class="form-label">Date Avoir *</label>
                                        <input type="date" class="form-control" id="credit_note_date"
                                            name="credit_note_date"
                                            value="{{ $creditNote->credit_note_date->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="client_id" class="form-label">Client *</label>
                                        <select class="form-control select2" id="client_id" name="client_id" required>
                                            <option value="">Sélectionner un client</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->client_id }}"
                                                    {{ $creditNote->client_id == $client->client_id ? 'selected' : '' }}>
                                                    {{ $client->display_name }} ({{ $client->phone }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Selection -->
                            <div class="row mb-4" id="order-selection"
                                style="display: {{ $creditNote->sales_order_id ? 'block' : 'none' }};">
                                <div class="col-md-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="sales_order_id" class="form-label">Ventes associée
                                                    (optionnel)</label>
                                                <select class="form-control select2" id="sales_order_id"
                                                    name="sales_order_id">
                                                    <option value="">Sélectionner une vente existante</option>
                                                    @if ($creditNote->sales_order_id && $creditNote->salesOrder)
                                                        <option value="{{ $creditNote->sales_order_id }}" selected>
                                                            {{ $creditNote->salesOrder->order_number }} -
                                                            {{ $creditNote->salesOrder->order_date->format('d/m/Y') }}
                                                            ({{ number_format($creditNote->salesOrder->final_amount, 2, ',', '.') }}
                                                            DH)
                                                        </option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div id="order-details" class="mt-3"
                                                style="display: {{ $creditNote->sales_order_id ? 'block' : 'none' }};">
                                                @if ($creditNote->sales_order_id && $creditNote->salesOrder)
                                                    <div class="alert alert-info">
                                                        <strong>Ventes:</strong>
                                                        {{ $creditNote->salesOrder->order_number }}<br>
                                                        <strong>Date:</strong>
                                                        {{ $creditNote->salesOrder->order_date->format('d/m/Y') }}<br>
                                                        <strong>Total:</strong>
                                                        {{ number_format($creditNote->salesOrder->final_amount, 2, ',', '.') }} DH
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Section -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Articles à retourner</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="items-table">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="35%">Article</th>
                                                    <th width="15%">Quantité disponible</th>
                                                    <th width="15%">Quantité à retourner</th>
                                                    <th width="15%">Prix Unitaire (DH)</th>
                                                    <th width="15%">Total (DH)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="items-body">
                                                @foreach ($creditNote->items as $index => $item)
                                                    <tr class="item-row" data-order-item-id="{{ $item->order_item_id }}"
                                                        data-item-id="{{ $item->item_id }}">
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            <strong>{{ $item->item_name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $item->type_label }}</small>
                                                            <input type="hidden" class="item-type"
                                                                value="{{ $item->item_type }}">
                                                            <input type="hidden" class="item-id"
                                                                value="{{ $item->item_id }}">
                                                            <input type="hidden" class="item-name"
                                                                value="{{ $item->item_name }}">
                                                            <input type="hidden" class="order-item-id"
                                                                value="{{ $item->order_item_id }}">
                                                            <input type="hidden" class="family-id"
                                                                value="{{ $item->family_id }}">
                                                            <input type="hidden" class="family-name"
                                                                value="{{ $item->family_name }}">
                                                        </td>
                                                        <td class="quantity-available">
                                                            <span class="max-quantity"
                                                                data-max="{{ $item->quantity }}">{{ number_format($item->quantity, 2, ',', '.') }}</span>
                                                            @if ($item->item_type != 'raw_material')
                                                                <br><small class="text-muted">unités</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control return-quantity"
                                                                min="0" max="{{ $item->quantity }}"
                                                                step="0.0001" value="{{ $item->quantity }}"
                                                                data-max="{{ $item->quantity }}">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control unit-price"
                                                                value="{{ ceil($item->unit_price) }}" step="0.01"
                                                                readonly>
                                                        </td>
                                                        <td class="item-total">
                                                            {{ number_format(ceil($item->unit_price) * $item->quantity, 2, ',', '.') }}
                                                            DH</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                                    <td><strong
                                                            id="total-amount">{{ number_format($creditNote->total_amount, 2, ',', '.') }}
                                                            DH</strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-primary" id="load-order-items"
                                                style="display: none;">
                                                <i class="fas fa-sync-alt me-1"></i> Charger les articles de la vente
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reason and Notes -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="reason" class="form-label">Raison principale</label>
                                        <select class="form-control" id="reason" name="reason">
                                            <option value="">Sélectionner une raison</option>
                                            <option value="Défaut de qualité"
                                                {{ $creditNote->reason == 'Défaut de qualité' ? 'selected' : '' }}>Défaut
                                                de qualité</option>
                                            <option value="Erreur de vente"
                                                {{ $creditNote->reason == 'Erreur de vente' ? 'selected' : '' }}>Erreur
                                                de vente</option>
                                            <option value="Produit non conforme"
                                                {{ $creditNote->reason == 'Produit non conforme' ? 'selected' : '' }}>
                                                Produit non conforme</option>
                                            <option value="Retour client"
                                                {{ $creditNote->reason == 'Retour client' ? 'selected' : '' }}>Retour
                                                client</option>
                                            <option value="Annulation vente"
                                                {{ $creditNote->reason == 'Annulation vente' ? 'selected' : '' }}>
                                                Annulation vente</option>
                                            <option value="Autre" {{ $creditNote->reason == 'Autre' ? 'selected' : '' }}>
                                                Autre</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                            placeholder="Informations supplémentaires...">{{ $creditNote->notes }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> La modification de cet avoir remplacera tous les articles existants.
                                Les quantités retournées seront mises à jour lors du traitement.
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Mettre à jour
                                </button>
                                <a href="{{ route('credit-notes.show', $creditNote->credit_note_id) }}"
                                    class="btn btn-secondary">
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
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
@endsection

@push('stylesheets')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        .item-row {
            transition: all 0.3s;
        }

        .quantity-available {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .return-quantity {
            border-left: 3px solid #dc3545;
        }

        .select2-container .select2-selection--single {
            height: 38px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
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

            let selectedOrderItems = {};

            // Load existing items data
            @foreach ($creditNote->items as $item)
                selectedOrderItems[{{ $item->order_item_id }}] = {
                    order_item_id: {{ $item->order_item_id }},
                    item_type: '{{ $item->item_type }}',
                    item_id: {{ $item->item_id }},
                    item_name: '{{ addslashes($item->item_name) }}',
                    quantity: {{ $item->quantity }},
                    unit_price: {{ $item->unit_price }},
                    family_id: '{{ $item->family_id }}',
                    family_name: '{{ addslashes($item->family_name) }}',
                    returned_quantity: {{ $item->quantity }}
                };
            @endforeach

            // Client change handler
            $('#client_id').change(function() {
                let clientId = $(this).val();

                if (clientId && clientId != {{ $creditNote->client_id }}) {
                    // If client changed, clear order selection and show warning
                    $('#sales_order_id').val('').trigger('change');
                    $('#order-details').hide();
                    $('#items-body').empty();
                    $('#total-amount').text('0.00 DH');
                    $('#load-order-items').hide();

                    // Load orders for new client
                    $.ajax({
                        url: `/credit-notes/client/${clientId}/orders`,
                        type: 'GET',
                        success: function(response) {
                            if (response.success && response.data.length > 0) {
                                let orderSelect = $('#sales_order_id');
                                orderSelect.empty().append(
                                    '<option value="">Sélectionner une vente existante</option>'
                                    );

                                response.data.forEach(function(order) {
                                    orderSelect.append(`<option value="${order.order_id}">
                                ${order.order_number} - ${order.order_date}
                                (${order.total_amount} DH)
                            </option>`);
                                });

                                $('#order-selection').show();
                                $('#load-order-items').show();
                                showToast('info',
                                    'Client modifié. Veuillez sélectionner une vente.');
                            } else {
                                $('#order-selection').hide();
                                showToast('info', 'Aucune vente trouvée pour ce client');
                            }
                        },
                        error: function(xhr) {
                            showToast('error', 'Erreur lors du chargement des ventes');
                        }
                    });
                } else if (clientId == {{ $creditNote->client_id }}) {
                    // Same client, keep existing items
                    $('#order-selection').show();
                    $('#load-order-items').show();
                }
            });

            // Order selection handler
            $('#sales_order_id').change(function() {
                let orderId = $(this).val();

                if (orderId) {
                    $.ajax({
                        url: `/credit-notes/order/${orderId}/items`,
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                // Store order items for later loading
                                window.currentOrderItems = response.items;

                                $('#order-details').html(`
                            <div class="alert alert-info">
                                <strong>Vente:</strong> ${response.order.order_number}<br>
                                <strong>Date:</strong> ${response.order.order_date}<br>
                                <strong>Total:</strong> ${response.order.total_amount} DH
                            </div>
                        `).show();

                                $('#load-order-items').show();
                                showToast('success',
                                    'Vente chargée. Cliquez sur "Charger les articles" pour voir les articles.'
                                );
                            }
                        },
                        error: function(xhr) {
                            showToast('error', 'Erreur lors du chargement des articles');
                        }
                    });
                } else {
                    $('#order-details').hide();
                    $('#load-order-items').hide();
                }
            });

            // Load order items button
            $('#load-order-items').click(function() {
                if (window.currentOrderItems && window.currentOrderItems.length > 0) {
                    displayOrderItems(window.currentOrderItems);
                    showToast('success', 'Articles chargés avec succès');
                } else if ($('#sales_order_id').val()) {
                    // Reload if not available
                    $('#sales_order_id').trigger('change');
                } else {
                    showToast('warning', 'Veuillez d\'abord sélectionner une vente');
                }
            });

            function displayOrderItems(items) {
                let html = '';
                items.forEach(function(item, index) {
                    let existingItem = selectedOrderItems[item.order_item_id];
                    let returnedQty = existingItem ? existingItem.returned_quantity : 0;

                    html += `
                <tr class="item-row" data-order-item-id="${item.order_item_id}" data-item-id="${item.item_id}">
                    <td>${index + 1}</td>
                    <td>
                        <strong>${escapeHtml(item.item_name)}</strong>
                        <br>
                        <small class="text-muted">${item.type_label}</small>
                        <input type="hidden" class="item-type" value="${item.item_type}">
                        <input type="hidden" class="item-id" value="${item.item_id}">
                        <input type="hidden" class="item-name" value="${escapeHtml(item.item_name)}">
                        <input type="hidden" class="order-item-id" value="${item.order_item_id}">
                        <input type="hidden" class="family-id" value="${item.family_id || ''}">
                        <input type="hidden" class="family-name" value="${escapeHtml(item.family_name || '')}">
                    </td>
                    <td class="quantity-available">
                        <span class="max-quantity" data-max="${item.quantity}">${formatNumber(item.quantity)}</span>
                        <br><small class="text-muted">unités</small>
                    </td>
                    <td>
                        <input type="number" class="form-control return-quantity"
                               min="0" max="${item.quantity}" step="0.0001"
                               value="${returnedQty}" data-max="${item.quantity}">
                    </td>
                    <td>
                        <input type="number" class="form-control unit-price"
                               value="${Math.ceil(item.unit_price)}" step="0.01" readonly>
                    </td>
                    <td class="item-total">${formatNumber(Math.ceil(item.unit_price) * (returnedQty || 0))} DH</td>
                </tr>
            `;
                });

                $('#items-body').html(html);

                // Bind events
                $('.return-quantity').on('input', function() {
                    let row = $(this).closest('tr');
                    let max = parseFloat($(this).data('max'));
                    let quantity = parseFloat($(this).val()) || 0;

                    if (quantity > max) {
                        $(this).val(max);
                        quantity = max;
                    }

                    let price = parseFloat(row.find('.unit-price').val()) || 0;
                    let total = quantity * price;
                    row.find('.item-total').text(formatNumber(total) + ' DH');

                    updateTotal();
                });

                updateTotal();
            }

            function updateTotal() {
                let total = 0;
                $('.item-total').each(function() {
                    let text = $(this).text().replace(' DH', '').replace(/,/g, '');
                    total += parseFloat(text) || 0;
                });
                $('#total-amount').text(formatNumber(total) + ' DH');
            }

            function formatNumber(number) {
                return new Intl.NumberFormat('fr-FR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(number);
            }

            function escapeHtml(text) {
                if (!text) return '';
                return text
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            // Form submission
            $('#creditNoteForm').submit(function(e) {
                e.preventDefault();

                // Collect items
                let items = [];
                let hasItems = false;

                $('.item-row').each(function() {
                    let quantity = parseFloat($(this).find('.return-quantity').val()) || 0;

                    if (quantity > 0) {
                        hasItems = true;
                        items.push({
                            order_item_id: $(this).find('.order-item-id').val(),
                            item_type: $(this).find('.item-type').val(),
                            item_id: $(this).find('.item-id').val(),
                            item_name: $(this).find('.item-name').val(),
                            quantity: quantity,
                            unit_price: $(this).find('.unit-price').val(),
                            family_id: $(this).find('.family-id').val(),
                            family_name: $(this).find('.family-name').val(),
                            reason: $('#reason').val()
                        });
                    }
                });

                if (!hasItems) {
                    showToast('error', 'Veuillez sélectionner au moins un article à retourner');
                    return;
                }

                // Disable submit button
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Mise à jour...');

                // Submit
                $.ajax({
                    url: "{{ route('credit-notes.update', $creditNote->credit_note_id) }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        credit_note_number: $('#credit_note_number').val(),
                        client_id: $('#client_id').val(),
                        sales_order_id: $('#sales_order_id').val(),
                        credit_note_date: $('#credit_note_date').val(),
                        reason: $('#reason').val(),
                        notes: $('#notes').val(),
                        items: items
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('credit-notes.show', $creditNote->credit_note_id) }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMessage = xhr.responseJSON?.message ||
                            'Erreur lors de la mise à jour';

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

            // Initialize if there's an existing order
            @if ($creditNote->sales_order_id)
                // Pre-load the order items if needed
                setTimeout(function() {
                    if ($('#items-body .item-row').length === 0) {
                        $('#sales_order_id').trigger('change');
                    }
                }, 500);
            @endif
        });
    </script>
@endpush
