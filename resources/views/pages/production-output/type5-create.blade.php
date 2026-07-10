@extends('layouts.app')

@section('title', 'Sortie de Production - Chutes → Produits Finis')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Sortie de Production - Chutes → Produits Finis</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('production-orders.index') }}">Ordres de
                                        Production</a></li>
                                <li class="breadcrumb-item active">Sortie Chutes → Produits Finis</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column - Order Information -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>Informations de l'Ordre
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="order-info-card">
                            <h6 class="mb-3"><i class="fas fa-box me-2"></i>{{ $order->order_number }}</h6>
                            <div class="order-info-row">
                                <span class="order-info-label">Famille de Destination:</span>
                                <span class="order-info-value">
                                    <span class="badge bg-success">{{ $order->famille->famille_name ?? 'Non spécifié' }}</span>
                                </span>
                            </div>
                            <div class="order-info-row">
                                <span class="order-info-label">Volume de Chutes Alloué:</span>
                                <span class="order-info-value"><strong>{{ number_format($order->chutes_volume ?? 0, 4) }} m³</strong></span>
                            </div>
                            <div class="order-info-row">
                                <span class="order-info-label">Volume Produits Prévu:</span>
                                <span class="order-info-value">{{ number_format(($order->chutes_volume ?? 0) - ($order->waste_volume ?? 0), 4) }} m³</span>
                            </div>
                            <div class="order-info-row">
                                <span class="order-info-label">Chute Résiduelle Estimée:</span>
                                <span class="order-info-value">{{ number_format($order->waste_volume ?? 0, 4) }} m³</span>
                            </div>
                            <div class="mt-3">
                                <div class="progress">
                                    @php
                                        $totalPlanned = collect($products)->sum('planned_quantity');
                                        $totalProducedAll = collect($products)->sum('produced_quantity');
                                        $orderPercentage = $totalPlanned > 0 ? ($totalProducedAll / $totalPlanned * 100) : 0;
                                    @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $orderPercentage }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Production Results -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-cogs me-2"></i>Résultats de Production
                        </h6>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="production_order_id" name="production_order_id"
                            value="{{ $order->order_id }}">

                        <form id="productionOutputForm">
                            @csrf

                            <!-- Products Selection (Multi-select) -->
                            <div class="mb-3">
                                <label class="form-label">Produits à Produire *</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">
                                                    <input type="checkbox" id="selectAllProducts" class="form-check-input">
                                                </th>
                                                <th>Produit</th>
                                                <th width="15%">Quantité Produite</th>
                                                <th width="15%">Défauts</th>
                                            </tr>
                                        </thead>
                                        <tbody id="productsTableBody">
                                            @foreach ($products as $product)
                                                @php
                                                    $produced = $product['produced_quantity'] ?? 0;
                                                    $remaining = max(0, $product['planned_quantity'] - $produced);
                                                    $volume =
                                                        isset($product['volume_per_unit']) &&
                                                        is_numeric($product['volume_per_unit'])
                                                            ? (float) $product['volume_per_unit']
                                                            : 0;
                                                @endphp
                                                <tr class="product-row" data-product-id="{{ $product['product_id'] }}"
                                                    data-product-name="{{ $product['product_name'] }}"
                                                    data-product-code="{{ $product['product_code'] }}"
                                                    data-planned-quantity="{{ $product['planned_quantity'] }}"
                                                    data-produced="{{ $produced }}"
                                                    data-remaining="{{ $remaining }}" data-volume="{{ $volume }}"
                                                    data-unit-volume="{{ $volume }}">
                                                    <td>
                                                        <input type="checkbox" class="form-check-input product-checkbox"
                                                            name="selected_products[]" value="{{ $product['product_id'] }}"
                                                            {{ $remaining <= 0 ? 'disabled' : '' }}>
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium">{{ $product['product_name'] }}</div>
                                                        <small class="text-muted">{{ $product['product_code'] }}</small>
                                                        @if ($remaining <= 0)
                                                            <span class="badge bg-success ms-2">Terminé</span>
                                                        @endif
                                                        <div class="small text-muted mt-1">
                                                            Volume: {{ number_format($volume, 4) }} m³/unité |
                                                            Restant: {{ $remaining }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            class="form-control form-control-sm product-quantity"
                                                            name="quantities[{{ $product['product_id'] }}][quantity_produced]"
                                                            data-product-id="{{ $product['product_id'] }}" min="0.01"
                                                            max="{{ $remaining }}" value="{{ $remaining }}" step="0.01"
                                                            disabled>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            class="form-control form-control-sm product-defective"
                                                            name="quantities[{{ $product['product_id'] }}][quantity_defective]"
                                                            data-product-id="{{ $product['product_id'] }}" min="0"
                                                            value="0" step="0.01" disabled>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <small class="form-text text-muted">Sélectionnez les produits à produire et saisissez les
                                    quantités</small>
                            </div>

                            <!-- Production Date -->
                            <div class="mb-3">
                                <label for="production_date" class="form-label">Date de Production *</label>
                                <input type="date" class="form-control" id="production_date" name="production_date"
                                    required value="{{ date('Y-m-d') }}">
                            </div>

                            <!-- Calculated Values -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="alert alert-success mb-0 text-center">
                                        <small class="text-muted">Produits bons</small>
                                        <h5 class="mb-0" id="calculatedGood">0</h5>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-info mb-0 text-center">
                                        <small class="text-muted">Volume total</small>
                                        <h5 class="mb-0" id="calculatedVolume">0.0000 m³</h5>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-danger mb-0 text-center">
                                        <small class="text-muted">Volume défectueux</small>
                                        <h5 class="mb-0" id="calculatedWasteVolume">0.0000 m³</h5>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden fields -->
                            <input type="hidden" id="total_volume_m3" name="total_volume_m3" value="0">
                            <input type="hidden" id="waste_volume_m3" name="waste_volume_m3" value="0">

                            <!-- Notes -->
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"
                                    placeholder="Observations sur la sortie..."></textarea>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-success btn-lg text-white" id="submitBtn">
                                    <i class="fas fa-save me-2"></i> Enregistrer la Sortie
                                </button>
                                <a href="{{ route('production-orders.show', $order->order_id) }}"
                                    class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> Retour
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
    <style>
        .order-info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .order-info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .order-info-label {
            font-weight: 600;
            color: #495057;
        }

        .order-info-value {
            color: #212529;
        }

        .progress {
            border-radius: 10px;
        }

        .product-row.selected {
            background-color: #d1e7dd;
        }

        .product-quantity:disabled,
        .product-defective:disabled {
            background-color: #e9ecef;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            const orderId = {{ $order->order_id }};

            // Select All checkbox
            $('#selectAllProducts').change(function() {
                const isChecked = $(this).is(':checked');
                $('.product-checkbox:not(:disabled)').each(function() {
                    $(this).prop('checked', isChecked).trigger('change');
                });
            });

            // Product checkbox change
            $('.product-checkbox').change(function() {
                const $row = $(this).closest('tr');
                const $quantityInput = $row.find('.product-quantity');
                const $defectiveInput = $row.find('.product-defective');
                const isChecked = $(this).is(':checked');

                if (isChecked) {
                    $quantityInput.prop('disabled', false);
                    $defectiveInput.prop('disabled', false);
                    $row.addClass('selected');

                    if (!$quantityInput.val() || parseFloat($quantityInput.val()) <= 0) {
                        const maxQty = parseFloat($quantityInput.attr('max')) || 1;
                        $quantityInput.val(maxQty);
                    }
                } else {
                    $quantityInput.prop('disabled', true).val('');
                    $defectiveInput.prop('disabled', true).val(0);
                    $row.removeClass('selected');
                }

                updateCalculations();
            });

            // Quantity change
            $('.product-quantity, .product-defective').on('input', function() {
                const $row = $(this).closest('tr');
                const $quantityInput = $row.find('.product-quantity');
                const $defectiveInput = $row.find('.product-defective');
                const maxQty = parseFloat($quantityInput.attr('max')) || 0;

                let quantity = parseFloat($quantityInput.val()) || 0;
                let defective = parseFloat($defectiveInput.val()) || 0;

                if (quantity > maxQty) {
                    $quantityInput.val(maxQty);
                    quantity = maxQty;
                    showToast('warning', `Quantité maximum pour ce produit: ${maxQty}`);
                }

                if (defective > quantity) {
                    $defectiveInput.val(quantity);
                    defective = quantity;
                    showToast('warning',
                        'La quantité défectueuse ne peut pas dépasser la quantité produite');
                }

                updateCalculations();
            });

            function updateCalculations() {
                let totalGood = 0;
                let totalVolume = 0;
                let totalWasteVolume = 0;
                let selectedProductsCount = 0;

                $('.product-row').each(function() {
                    const $checkbox = $(this).find('.product-checkbox');
                    if (!$checkbox.is(':checked')) return;

                    selectedProductsCount++;
                    const quantity = parseFloat($(this).find('.product-quantity').val()) || 0;
                    const defective = parseFloat($(this).find('.product-defective').val()) || 0;
                    const unitVolume = parseFloat($(this).data('unit-volume')) || 0;

                    const good = quantity - defective;
                    totalGood += good;
                    totalVolume += quantity * unitVolume;
                    totalWasteVolume += defective * unitVolume;
                });

                $('#calculatedGood').text(totalGood);
                $('#calculatedVolume').text(totalVolume.toFixed(4) + ' m³');
                $('#calculatedWasteVolume').text(totalWasteVolume.toFixed(4) + ' m³');

                $('#total_volume_m3').val(totalVolume);
                $('#waste_volume_m3').val(totalWasteVolume);

                $('#submitBtn').prop('disabled', selectedProductsCount === 0);
            }

            function showToast(type, message) {
                const toast = $(`
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'warning'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

                if (!$('#toast-container').length) {
                    $('body').append(
                        '<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>'
                    );
                }

                $('#toast-container').append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();
                setTimeout(() => toast.remove(), 5000);
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Form submission
            $('#productionOutputForm').submit(function(e) {
                e.preventDefault();

                const selectedProducts = [];
                let hasError = false;

                $('.product-row').each(function() {
                    const $checkbox = $(this).find('.product-checkbox');
                    if (!$checkbox.is(':checked')) return;

                    const productId = $(this).data('product-id');
                    const quantity = parseFloat($(this).find('.product-quantity').val()) || 0;
                    const defective = parseFloat($(this).find('.product-defective').val()) || 0;

                    if (quantity <= 0) {
                        showToast('error',
                            `Veuillez saisir une quantité valide pour ${$(this).data('product-name')}`
                        );
                        hasError = true;
                        return false;
                    }

                    if (defective > quantity) {
                        showToast('error',
                            `La quantité défectueuse ne peut pas dépasser la quantité produite pour ${$(this).data('product-name')}`
                        );
                        hasError = true;
                        return false;
                    }

                    selectedProducts.push({
                        product_id: productId,
                        quantity_produced: quantity,
                        quantity_defective: defective
                    });
                });

                if (hasError || selectedProducts.length === 0) {
                    if (selectedProducts.length === 0) {
                        showToast('error', 'Veuillez sélectionner au moins un produit');
                    }
                    return;
                }

                const submitBtn = $('#submitBtn');
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-2"></i> Enregistrement...');

                const formData = new FormData();
                formData.append('production_order_id', orderId);
                formData.append('production_date', $('#production_date').val());
                formData.append('notes', $('#notes').val());
                formData.append('total_volume_m3', $('#total_volume_m3').val());
                formData.append('waste_volume_m3', $('#waste_volume_m3').val());

                selectedProducts.forEach((product, index) => {
                    formData.append(`products[${index}][product_id]`, product.product_id);
                    formData.append(`products[${index}][quantity_produced]`, product
                        .quantity_produced);
                    formData.append(`products[${index}][quantity_defective]`, product
                        .quantity_defective);
                });

                $.ajax({
                    url: "{{ route('production-output.store-type5') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(() => {
                                window.location.href =
                                    "{{ route('production-orders.index') }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(
                                '<i class="fas fa-save me-2"></i> Enregistrer la Sortie'
                            );
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Une erreur est survenue';
                        showToast('error', message);
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save me-2"></i> Enregistrer la Sortie');
                    }
                });
            });
        });
    </script>
@endpush
