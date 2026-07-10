@extends('layouts.app')

@section('title', 'Modifier Achat')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Commande d'Achat</h4>
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
                                        Modifier
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        @if ($hasCheckAllocation)
            <div class="alert alert-danger mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Attention!</strong> Cette commande a déjà des allocations de chèques. Vous ne pouvez plus la
                modifier.
                <a href="{{ route('raw-material-purchases.show', $purchase->purchase_id) }}" class="alert-link">Voir les
                    détails</a>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-edit me-2"></i>Modifier Commande : {{ $purchase->purchase_number }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="purchaseForm"
                            {{ $hasCheckAllocation ? 'style="pointer-events: none; opacity: 0.6;"' : '' }}>
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="purchase_number" name="purchase_number"
                                value="{{ $purchase->purchase_number }}">
                            <input type="hidden" id="has_check_allocation" value="{{ $hasCheckAllocation ? '1' : '0' }}">

                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">N° Commande</label>
                                        <input type="text" class="form-control" value="{{ $purchase->purchase_number }}"
                                            readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Fournisseur *</label>
                                        <select class="form-control select2" id="supplier_id" name="supplier_id" required>
                                            <option value="">Sélectionner un fournisseur</option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->supplier_id }}"
                                                    {{ $purchase->supplier_id == $supplier->supplier_id ? 'selected' : '' }}>
                                                    {{ $supplier->company_name ?? $supplier->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Magasin *</label>
                                        <select class="form-control select2" id="magazine_id" name="magazine_id" required>
                                            <option value="">Sélectionner un magasin</option>
                                            @foreach ($magazines as $magazine)
                                                <option value="{{ $magazine->magazine_id }}"
                                                    {{ $purchase->magazine_id == $magazine->magazine_id ? 'selected' : '' }}>
                                                    {{ $magazine->magazine_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Statut Paiement</label>
                                        <div>
                                            {!! $purchase->payment_status_label !!}
                                        </div>
                                        <small class="text-muted">Payé: {{ number_format($purchase->total_paid, 2, ',', '.') }} DH /
                                            {{ number_format($purchase->final_amount, 2, ',', '.') }} DH</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Date Commande *</label>
                                        <input type="date" class="form-control" id="purchase_date" name="purchase_date"
                                            value="{{ $purchase->purchase_date->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Date Livraison Prévue *</label>
                                        <input type="date" class="form-control" id="expected_delivery_date"
                                            name="expected_delivery_date"
                                            value="{{ $purchase->expected_delivery_date->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Date Livraison Réelle</label>
                                        <input type="text" class="form-control"
                                            value="{{ $purchase->actual_delivery_date ? $purchase->actual_delivery_date->format('d/m/Y') : 'Non livré' }}"
                                            readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" id="include_tva"
                                                name="include_tva" value="1"
                                                {{ $purchase->include_tva ? 'checked' : '' }}>
                                            <label class="form-check-label" for="include_tva">
                                                TVA incluse
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6>Articles Commandés</h6>
                                        <button type="button" class="btn btn-sm btn-primary" id="addItemBtn">
                                            <i class="fas fa-plus me-1"></i> Ajouter un Article
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="itemsTable">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="15%">Type *</th>
                                                    <th width="52%">Matière / Description *</th>
                                                    <th width="18%">Total (DH) *</th>
                                                    <th width="10%">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="itemsBody">
                                                <!-- Items will be loaded via JavaScript -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Sous-total:</strong></td>
                                                    <td><strong id="subtotal">0.00 DH</strong></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end">Remise (%):</td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" class="form-control"
                                                                id="discount_percentage" name="discount_percentage"
                                                                value="{{ $purchase->discount_percentage ?? 0 }}"
                                                                min="0" max="100" step="0.01">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                        <small class="text-muted">Montant: <span
                                                                id="discount_amount_display">0.00 DH</span></small>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Total Final:</strong></td>
                                                    <td><strong id="finalAmount">0.00 DH</strong></td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3">{{ $purchase->notes }}</textarea>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                @if (!$hasCheckAllocation)
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save me-1"></i> Mettre à jour
                                    </button>
                                @endif
                                <a href="{{ route('raw-material-purchases.show', $purchase->purchase_id) }}"
                                    class="btn btn-info">
                                    <i class="fas fa-eye me-1"></i> Voir détails
                                </a>
                                <a href="{{ route('raw-material-purchases.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Retour
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
    <style>
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 0;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
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

            // Load existing items
            @foreach ($purchase->items as $index => $item)
                addItemRow({
                    item_type: '{{ $item->item_type }}',
                    material_id: {{ $item->material_id ?? 'null' }},
                    description: {!! $item->description ? json_encode($item->description) : "''" !!},
                    quantity: {{ $item->quantity ?? 'null' }},
                    unit_price: {{ $item->unit_price ?? 'null' }},
                    total_price: {{ $item->total_price }}
                });
            @endforeach

            // Add item button
            $('#addItemBtn').click(function() {
                addItemRow();
            });

            function addItemRow(item = {}) {
                var rowCount = $('#itemsBody tr').length;
                var rowId = 'item_' + Date.now() + '_' + rowCount;
                var itemType = item.item_type || 'raw_material';

                var row = `
                    <tr id="${rowId}">
                        <td class="row-number">${rowCount + 1}</td>
                        <td>
                            <select class="form-control form-control-sm item-type-select" name="items[${rowCount}][item_type]" required>
                                <option value="raw_material" ${itemType === 'raw_material' ? 'selected' : ''}>Matière Première</option>
                                <option value="charge_diverse" ${itemType === 'charge_diverse' ? 'selected' : ''}>Charges Diverses</option>
                            </select>
                        </td>
                        <td class="detail-cell">
                            <select class="form-control form-control-sm material-select" name="items[${rowCount}][material_id]">
                                <option value="">Sélectionner</option>
                                @foreach ($materials as $material)
                                    <option value="{{ $material->material_id }}"
                                        data-unit="{{ $material->unit_of_measure }}"
                                        ${item.material_id == {{ $material->material_id }} ? 'selected' : ''}>
                                        {{ $material->material_name }} ({{ $material->material_code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="row g-1 mt-1 qty-price-group">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm quantity"
                                           name="items[${rowCount}][quantity]"
                                           value="${item.quantity || ''}"
                                           placeholder="Quantité" min="0.01" step="0.01">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm unit-price"
                                           name="items[${rowCount}][unit_price]"
                                           value="${item.unit_price || ''}"
                                           placeholder="Prix Unitaire (DH)" min="0" step="0.01">
                                </div>
                            </div>
                            <input type="text" class="form-control form-control-sm description-input d-none mt-1"
                                   name="items[${rowCount}][description]"
                                   placeholder="Charges diverses"
                                   value="${item.description || ''}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm item-total-input"
                                   name="items[${rowCount}][total_price]"
                                   value="${item.total_price || ''}"
                                   min="0.01" step="0.01" required>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#itemsBody').append(row);

                // Initialize select2 for the new row
                $('#' + rowId + ' .material-select').select2({
                    language: "fr",
                    placeholder: "Sélectionner...",
                    width: '100%'
                });

                applyItemTypeUI(rowId);
                calculateItemTotal(rowId);
                updateTotals();

                // Add event listeners
                $('#' + rowId + ' .item-type-select').on('change', function() {
                    applyItemTypeUI(rowId);
                    calculateItemTotal(rowId);
                    updateTotals();
                });

                $('#' + rowId + ' .quantity, #' + rowId + ' .unit-price').on('input', function() {
                    calculateItemTotal(rowId);
                    updateTotals();
                });

                $('#' + rowId + ' .item-total-input').on('input', function() {
                    updateTotals();
                });

                $('#' + rowId + ' .remove-item').click(function() {
                    $('#' + rowId).remove();
                    updateRowNumbers();
                    updateTotals();
                });
            }

            // Show/hide fields based on the selected item type. Quantité and Prix
            // Unitaire are not separate table columns — they're compact inputs nested
            // inside the Matière/Description cell, so toggling them never affects the
            // table's column layout.
            function applyItemTypeUI(rowId) {
                var $row = $('#' + rowId);
                var type = $row.find('.item-type-select').val();
                var $material = $row.find('.material-select');
                var $qtyPriceGroup = $row.find('.qty-price-group');
                var $description = $row.find('.description-input');
                var $total = $row.find('.item-total-input');

                if (type === 'charge_diverse') {
                    $material.next('.select2-container').addClass('d-none');
                    $qtyPriceGroup.addClass('d-none');
                    $row.find('.quantity').prop('required', false).val('');
                    $row.find('.unit-price').prop('required', false).val('');
                    $description.removeClass('d-none').prop('required', true);
                    if (!$description.val()) {
                        $description.val('Charges diverses');
                    }
                    $total.prop('readonly', false).prop('required', true);
                } else {
                    $material.next('.select2-container').removeClass('d-none');
                    $qtyPriceGroup.removeClass('d-none');
                    $row.find('.quantity').prop('required', true);
                    $row.find('.unit-price').prop('required', true);
                    $description.addClass('d-none').prop('required', false).val('');
                    $total.prop('readonly', true).prop('required', true);
                }
            }

            function calculateItemTotal(rowId) {
                var $row = $('#' + rowId);
                if ($row.find('.item-type-select').val() === 'charge_diverse') {
                    return;
                }
                var quantity = parseFloat($row.find('.quantity').val()) || 0;
                var unitPrice = parseFloat($row.find('.unit-price').val()) || 0;
                var total = quantity * unitPrice;
                $row.find('.item-total-input').val(total.toFixed(2));
            }

            function updateRowNumbers() {
                $('#itemsBody tr').each(function(index) {
                    $(this).find('.row-number').text(index + 1);
                    $(this).find('select, input').each(function() {
                        var name = $(this).attr('name');
                        if (name) {
                            var newName = name.replace(/items\[\d+\]/g, 'items[' + index + ']');
                            $(this).attr('name', newName);
                        }
                    });
                });
            }

            function updateTotals() {
                var subtotal = 0;
                $('.item-total-input').each(function() {
                    var total = parseFloat($(this).val()) || 0;
                    subtotal += total;
                });

                var discountPercentage = parseFloat($('#discount_percentage').val()) || 0;
                var discountAmount = (subtotal * discountPercentage) / 100;
                var finalAmount = subtotal - discountAmount;

                $('#subtotal').text(subtotal.toFixed(2) + ' DH');
                $('#discount_amount_display').text(discountAmount.toFixed(2) + ' DH');
                $('#finalAmount').text(finalAmount.toFixed(2) + ' DH');
            }

            $('#discount_percentage').on('input', updateTotals);

            // Form submission
            $('#purchaseForm').submit(function(e) {
                e.preventDefault();

                // Check if has check allocation
                if ($('#has_check_allocation').val() === '1') {
                    showToast('error',
                        'Cette commande a des allocations de chèques et ne peut plus être modifiée.');
                    return;
                }

                if ($('#itemsBody tr').length === 0) {
                    showToast('error', 'Veuillez ajouter au moins un article');
                    return;
                }

                var items = [];
                var valid = true;

                $('#itemsBody tr').each(function() {
                    var itemType = $(this).find('.item-type-select').val();
                    var totalPrice = $(this).find('.item-total-input').val();

                    if (itemType === 'charge_diverse') {
                        var description = $.trim($(this).find('.description-input').val());

                        if (!description) {
                            showToast('error', 'Veuillez saisir une description pour la charge diverse');
                            valid = false;
                            return false;
                        }
                        if (!totalPrice || totalPrice <= 0) {
                            showToast('error', 'Veuillez saisir un prix total valide pour la charge diverse');
                            valid = false;
                            return false;
                        }

                        items.push({
                            item_type: 'charge_diverse',
                            description: description,
                            total_price: totalPrice
                        });
                    } else {
                        var materialId = $(this).find('.material-select').val();
                        var quantity = $(this).find('.quantity').val();
                        var unitPrice = $(this).find('.unit-price').val();

                        if (!materialId) {
                            showToast('error', 'Veuillez sélectionner une matière première');
                            valid = false;
                            return false;
                        }
                        if (!quantity || quantity <= 0) {
                            showToast('error', 'Veuillez saisir une quantité valide');
                            valid = false;
                            return false;
                        }
                        if (!unitPrice || unitPrice <= 0) {
                            showToast('error', 'Veuillez saisir un prix unitaire valide');
                            valid = false;
                            return false;
                        }

                        items.push({
                            item_type: 'raw_material',
                            material_id: materialId,
                            quantity: quantity,
                            unit_price: unitPrice
                        });
                    }
                });

                if (!valid) return;

                var formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PUT');
                formData.append('supplier_id', $('#supplier_id').val());
                formData.append('magazine_id', $('#magazine_id').val());
                formData.append('purchase_date', $('#purchase_date').val());
                formData.append('expected_delivery_date', $('#expected_delivery_date').val());
                formData.append('include_tva', $('#include_tva').is(':checked') ? 1 : 0);
                formData.append('discount_percentage', $('#discount_percentage').val());
                formData.append('notes', $('#notes').val());
                formData.append('items', JSON.stringify(items));

                $.ajax({
                    url: "{{ route('raw-material-purchases.update', $purchase->purchase_id) }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('raw-material-purchases.show', $purchase->purchase_id) }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON?.message ||
                            'Une erreur est survenue';
                        showToast('error', errorMessage);
                    }
                });
            });
        });

        function showToast(type, message) {
            var toast = $('<div class="toast align-items-center text-white bg-' +
                (type === 'success' ? 'success' : 'danger') +
                ' border-0" role="alert">' +
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
    </script>
@endpush
