@extends('layouts.app')

@section('title', 'Réception Commande')

@push('stylesheets')
    <style>
        .card-header-custom {
            background: linear-gradient(45deg, #2c3e50, #4a6491);
            color: white;
            border-bottom: none;
        }

        .item-row {
            transition: background-color 0.2s;
        }

        .item-row:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .quantity-input {
            max-width: 120px;
        }

        .alert-custom {
            border-left: 4px solid #ffc107;
        }

        .total-ht {
            color: #2c3e50;
            font-weight: 600;
        }

        .total-ttc {
            color: #27ae60;
            font-weight: 600;
        }

        .fixed-total {
            background-color: #f8f9fa;
            font-size: 1.1em;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Réception de Commande</h4>
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
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('raw-material-purchases.show', $purchase->purchase_id) }}">
                                        Commande {{ $purchase->purchase_number }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-warning text-warning">
                                        Réception
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
                            <i class="fas fa-truck-loading me-2"></i>
                            Réception Commande : {{ $purchase->purchase_number }}
                        </h5>
                        <div>
                            <a href="{{ route('raw-material-purchases.show', $purchase->purchase_id) }}"
                                class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Purchase Information -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted">Informations Commande</h6>
                                        <p class="mb-1"><strong>N°:</strong> {{ $purchase->purchase_number }}</p>
                                        <p class="mb-1"><strong>Date:</strong>
                                            {{ $purchase->purchase_date->format('d/m/Y') }}</p>
                                        <p class="mb-1"><strong>Livraison prévue:</strong>
                                            {{ $purchase->expected_delivery_date->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted">Fournisseur</h6>
                                        <p class="mb-1"><strong>Nom:</strong>
                                            {{ $purchase->supplier->company_name ?? ($purchase->supplier->full_name ?? 'N/A') }}
                                        </p>
                                        <p class="mb-1"><strong>Tél:</strong> {{ $purchase->supplier->phone ?? 'N/A' }}
                                        </p>
                                        <p class="mb-1"><strong>Email:</strong> {{ $purchase->supplier->email ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted">Magasin de Destination</h6>
                                        <p class="mb-1"><strong>Magasin:</strong>
                                            {{ $purchase->magazine->magazine_name ?? 'N/A' }}</p>
                                        <p class="mb-1"><strong>Adresse:</strong>
                                            {{ $purchase->magazine->location ?? 'N/A' }}</p>
                                        <p class="mb-1"><strong>Responsable:</strong>
                                            {{ $purchase->magazine->manager_name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted">Montants (Fixes)</h6>
                                        <p class="mb-1"><strong>Total H.T:</strong>
                                            <span
                                                class="fw-bold text-primary">{{ number_format($purchase->total_amount, 2, ',', '.') }}
                                                DH</span>
                                        </p>
                                        <p class="mb-1"><strong>Taxe ({{ $purchase->tax_percentage ?? 20 }}%):</strong>
                                            + {{ number_format($purchase->tax_amount, 2, ',', '.') }} DH</p>
                                        <p class="mb-1"><strong>Remise
                                                ({{ $purchase->discount_percentage ?? 0 }}%):</strong>
                                            - {{ number_format($purchase->discount_amount, 2, ',', '.') }} DH</p>
                                        <p class="mb-1"><strong>Total T.T.C:</strong>
                                            <span
                                                class="fw-bold text-success">{{ number_format($purchase->final_amount, 2, ',', '.') }}
                                                DH</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Receipt Form -->
                        <form id="receiptForm">
                            @csrf
                            <input type="hidden" name="purchase_id" value="{{ $purchase->purchase_id }}">
                            <input type="hidden" name="magazine_id" value="{{ $purchase->magazine_id }}">
                            <input type="hidden" name="tax_percentage" id="tax_percentage"
                                value="{{ $purchase->tax_percentage ?? 20 }}">
                            <input type="hidden" name="discount_percentage" id="discount_percentage"
                                value="{{ $purchase->discount_percentage ?? 0 }}">
                            <input type="hidden" name="fixed_total_amount" id="fixed_total_amount"
                                value="{{ $purchase->final_amount }}">

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="actual_delivery_date" class="form-label">Date de Réception *</label>
                                        <input type="date" class="form-control" id="actual_delivery_date"
                                            name="actual_delivery_date" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="delivery_notes" class="form-label">Notes de Livraison</label>
                                        <textarea class="form-control" id="delivery_notes" name="delivery_notes" rows="1"
                                            placeholder="Ex: Bon état, colis endommagé, etc."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th>Matière Première</th>
                                            <th>Stock Actuel</th>
                                            <th>Unité</th>
                                            <th>Prix Unitaire (DH)</th>
                                            <th>Quantité Commandée</th>
                                            <th>Quantité Reçue</th>
                                            <th>Stock Final</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $taxPercentage = $purchase->tax_percentage ?? 20;
                                            $discountPercentage = $purchase->discount_percentage ?? 0;
                                        @endphp
                                        @foreach ($purchase->items as $index => $item)
                                            @continue($item->isChargeDiverse())
                                            @php
                                                $currentStock = $item->rawMaterial->current_stock ?? 0;
                                                $orderedQty = $item->quantity;
                                                $unitPrice = $item->unit_price;
                                                // Default to ordered quantity for display
                                                $receivedQty = $orderedQty;
                                            @endphp
                                            <tr class="item-row">
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $item->rawMaterial->material_name }}</strong><br>
                                                    <small class="text-muted">Code:
                                                        {{ $item->rawMaterial->material_code }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-info">{{ number_format($currentStock, 2, ',', '.') }}</span>
                                                </td>
                                                <td class="text-center">{{ $item->rawMaterial->unit_of_measure }}</td>
                                                <td class="text-end unit-price-display"
                                                    id="unit_price_{{ $index }}">
                                                    {{ number_format($unitPrice, 2, ',', '.') }}
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary ordered-qty"
                                                        id="ordered_qty_{{ $index }}">{{ number_format($orderedQty, 2, ',', '.') }}</span>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="number"
                                                            class="form-control quantity-input received-quantity"
                                                            name="items[{{ $index }}][received_quantity]"
                                                            id="received_quantity_{{ $index }}"
                                                            value="{{ $orderedQty }}" min="0" step="0.01"
                                                            data-max="{{ $orderedQty }}"
                                                            data-current-stock="{{ $currentStock }}"
                                                            data-unit-price="{{ $unitPrice }}"
                                                            data-material-id="{{ $item->material_id }}"
                                                            data-index="{{ $index }}">
                                                        <span
                                                            class="input-group-text">{{ $item->rawMaterial->unit_of_measure }}</span>
                                                    </div>
                                                    <input type="hidden"
                                                        name="items[{{ $index }}][purchase_item_id]"
                                                        value="{{ $item->purchase_item_id }}">
                                                    <input type="hidden" name="items[{{ $index }}][material_id]"
                                                        value="{{ $item->material_id }}">
                                                    <input type="hidden" name="items[{{ $index }}][unit_price]"
                                                        value="{{ $unitPrice }}">
                                                    <input type="hidden"
                                                        name="items[{{ $index }}][ordered_quantity]"
                                                        value="{{ $orderedQty }}">
                                                    <small class="form-text text-muted">Quantité commandée:
                                                        {{ number_format($orderedQty, 2, ',', '.') }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success final-stock"
                                                        id="final_stock_{{ $index }}">
                                                        {{ number_format($currentStock + $orderedQty, 2, ',', '.') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <!-- Fixed Total Row -->
                                        <tr class="table-info fixed-total">
                                            <td colspan="7" class="text-end fw-bold">
                                                <h5 class="mb-0">TOTAL COMMANDE - TTC:</h5>
                                            </td>
                                            <td class="text-center">
                                                <h5 class="mb-0 text-success fw-bold" id="fixedTotalDisplay">
                                                    {{ number_format($purchase->final_amount, 2, ',', '.') }} DH
                                                </h5>
                                            </td>
                                        </tr>

                                        <!-- Quantities Summary -->
                                        <tr class="table-light">
                                            <td colspan="5" class="text-end"><strong>Résumé Quantités:</strong></td>
                                            <td class="text-center">
                                                <strong
                                                    id="totalOrderedQuantity">{{ number_format($purchase->items->sum('quantity'), 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <strong
                                                    id="totalReceivedQuantity">{{ number_format($purchase->items->sum('quantity'), 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <strong id="receptionPercentage">100%</strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="quality_check" class="form-label">Contrôle Qualité</label>
                                        <select class="form-control" id="quality_check" name="quality_check">
                                            <option value="excellent">Excellent - Aucun défaut</option>
                                            <option value="good" selected>Bon - Quelques défauts mineurs</option>
                                            <option value="average">Moyen - Défauts visibles</option>
                                            <option value="poor">Mauvais - Problèmes majeurs</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="receipt_notes" class="form-label">Notes Générales</label>
                                        <textarea class="form-control" id="receipt_notes" name="receipt_notes" rows="2"
                                            placeholder="Notes supplémentaires sur la réception..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    <a href="{{ route('raw-material-purchases.show', $purchase->purchase_id) }}"
                                        class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Annuler
                                    </a>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-info me-2" id="fillAllBtn">
                                        <i class="fas fa-check-circle me-1"></i> Quantité commandée
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check-double me-1"></i> Enregistrer la réception
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Partial Reception Modal -->
    <div class="modal fade" id="partialReceptionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Réception Partielle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="percentage" class="form-label">Pourcentage de réception</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="percentage" value="100" min="0"
                                max="200" step="5">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="form-text text-muted">
                            Entrez le pourcentage à appliquer à toutes les quantités commandées
                            (peut être supérieur à 100% pour une sur-livraison)
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="applyPercentageBtn">Appliquer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Fixed total amount from purchase
            var fixedTotalAmount = parseFloat("{{ $purchase->final_amount }}");

            // Store original values
            var orderedQtys = [];
            var unitPrices = [];
            var currentStocks = [];

            // Initialize arrays with values from each row
            $('.received-quantity').each(function() {
                var index = $(this).data('index');
                orderedQtys[index] = parseFloat($(this).data('max'));
                unitPrices[index] = parseFloat($(this).data('unit-price'));
                currentStocks[index] = parseFloat($(this).data('current-stock'));
            });

            // Display fixed total
            $('#fixedTotalDisplay').text(fixedTotalAmount.toFixed(2) + ' DH');

            // Update quantities and stock only (not totals)
            function updateQuantities() {
                var totalOrdered = 0;
                var totalReceived = 0;

                $('.received-quantity').each(function() {
                    var index = $(this).data('index');
                    var ordered = orderedQtys[index] || 0;
                    var received = parseFloat($(this).val()) || 0;
                    var currentStock = currentStocks[index] || 0;

                    totalOrdered += ordered;
                    totalReceived += received;

                    // Update final stock display
                    var finalStock = currentStock + received;
                    $('#final_stock_' + index).text(finalStock.toFixed(2));
                });

                // Update quantities summary
                $('#totalOrderedQuantity').text(totalOrdered.toFixed(2));
                $('#totalReceivedQuantity').text(totalReceived.toFixed(2));

                // Calculate percentage
                var percentage = totalOrdered > 0 ? (totalReceived / totalOrdered) * 100 : 0;
                $('#receptionPercentage').text(percentage.toFixed(1) + '%');
            }

            // Initialize on page load
            updateQuantities();

            // Update when quantities change (only stock, not prices)
            $('.received-quantity').on('input', function() {
                updateQuantities();
            });

            $('.received-quantity').on('blur', function() {
                var value = parseFloat($(this).val()) || 0;
                if (value < 0) {
                    $(this).val(0);
                    updateQuantities();
                }
            });

            // Fill all button - set to ordered quantity
            $('#fillAllBtn').click(function() {
                $('.received-quantity').each(function() {
                    var ordered = parseFloat($(this).data('max'));
                    $(this).val(ordered);
                });
                updateQuantities();
                showToast('info', 'Quantités réinitialisées aux quantités commandées');
            });

            // Partial reception button
            $('#fillPartialBtn').click(function() {
                $('#partialReceptionModal').modal('show');
            });

            // Apply percentage button
            $('#applyPercentageBtn').click(function() {
                var percentage = parseFloat($('#percentage').val()) || 100;
                if (percentage < 0) {
                    showToast('error', 'Le pourcentage doit être positif');
                    return;
                }

                $('.received-quantity').each(function() {
                    var ordered = parseFloat($(this).data('max'));
                    // Allow any percentage, no max limit
                    var quantity = (ordered * percentage) / 100;
                    $(this).val(quantity.toFixed(2));
                });

                updateQuantities();
                $('#partialReceptionModal').modal('hide');
                showToast('info', 'Quantités ajustées à ' + percentage + '% de la commande');
            });

            // Form submission
            $('#receiptForm').submit(function(e) {
                e.preventDefault();

                // Validate at least one item has quantity
                var hasQuantity = false;
                $('.received-quantity').each(function() {
                    if (parseFloat($(this).val()) > 0) {
                        hasQuantity = true;
                    }
                });

                if (!hasQuantity) {
                    showToast('warning', 'Veuillez saisir au moins une quantité reçue');
                    return;
                }

                // Confirm before submission
                if (!confirm(
                        'Êtes-vous sûr de vouloir enregistrer cette réception ?\n' +
                        'Le montant total reste fixe à ' + fixedTotalAmount.toFixed(2) + ' DH.\n' +
                        'Le stock sera mis à jour avec les quantités reçues.\n\n' +
                        'Cette action est irréversible.'
                    )) {
                    return;
                }

                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('raw-material-purchases.process-receipt', $purchase->purchase_id) }}",
                    type: "POST",
                    data: formData,
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
                        var errors = xhr.responseJSON?.errors;
                        var errorMessage = '';

                        if (errors) {
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        } else {
                            errorMessage = xhr.responseJSON?.message ||
                                'Une erreur est survenue lors de l\'enregistrement de la réception.';
                        }

                        showToast('error', errorMessage);
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
