@extends('layouts.app')

@section('title', 'Sorties de Production Groupées')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Sorties de Production Groupées</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('production-output.index') }}">
                                        Sorties de Production
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Groupées
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
                            <i class="fas fa-layer-group me-2"></i>Enregistrement Groupé de Sorties de Production
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="batchOutputForm">
                            @csrf

                            <!-- Instructions -->
                            <div class="alert alert-info mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading">Instructions</h6>
                                        <p class="mb-0">
                                            Sélectionnez plusieurs ordres de production en cours et enregistrez leurs
                                            sorties en une seule opération.
                                            Les stocks seront automatiquement mis à jour pour chaque produit.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Production Date -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="batch_production_date" class="form-label">Date de Production *</label>
                                        <input type="date" class="form-control" id="batch_production_date"
                                            name="production_date" required value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="alert alert-light">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Cette date sera appliquée à toutes les sorties groupées.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Orders Selection -->
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-clipboard-list me-2"></i>Sélection des Ordres de Production
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if ($orders->isEmpty())
                                        <div class="text-center py-5">
                                            <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Aucun ordre en cours disponible</h5>
                                            <p class="text-muted">Tous les ordres de production sont terminés ou n'ont pas
                                                de production restante.</p>
                                            <a href="{{ route('production-orders.index') }}" class="btn btn-primary mt-2">
                                                <i class="fas fa-plus me-1"></i> Créer un Nouvel Ordre
                                            </a>
                                        </div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="ordersTable">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th width="5%">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="selectAllOrders">
                                                            </div>
                                                        </th>
                                                        <th>Ordre</th>
                                                        <th>Produit</th>
                                                        <th>Type</th>
                                                        <th>Planifié</th>
                                                        <th>Produit</th>
                                                        <th>Restant</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($orders as $order)
                                                        @php
                                                            $totalProduced = $order->outputs->sum('quantity_produced');
                                                            $remaining = $order->quantity_to_produce - $totalProduced;
                                                            $progress =
                                                                ($totalProduced / $order->quantity_to_produce) * 100;
                                                            $hasConversions = $order->product->conversions->count() > 0;
                                                        @endphp
                                                        <tr data-order-id="{{ $order->order_id }}"
                                                            data-product-id="{{ $order->product_id }}"
                                                            data-product-type="{{ $order->product->product_type }}"
                                                            data-remaining="{{ $remaining }}"
                                                            data-has-conversions="{{ $hasConversions ? '1' : '0' }}">
                                                            <td>
                                                                <div class="form-check">
                                                                    <input class="form-check-input order-checkbox"
                                                                        type="checkbox" value="{{ $order->order_id }}"
                                                                        data-max="{{ $remaining }}">
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="fw-medium">{{ $order->order_number }}</div>
                                                                <small class="text-muted">Début:
                                                                    {{ $order->start_date->format('d/m/Y') }}</small>
                                                            </td>
                                                            <td>
                                                                <div class="fw-medium">{{ $order->product->product_name }}
                                                                </div>
                                                                <small
                                                                    class="text-muted">{{ $order->product->product_code }}</small>
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($order->product->product_type === 'production')
                                                                    <span class="badge bg-primary">Production</span>
                                                                @elseif($order->product->product_type === 'sales')
                                                                    <span class="badge bg-success">Vente</span>
                                                                @else
                                                                    <span class="badge bg-info">Mixte</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                {{ $order->quantity_to_produce }}
                                                            </td>
                                                            <td class="text-center">
                                                                {{ $totalProduced }}
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge bg-warning">{{ $remaining }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-primary view-order-btn"
                                                                    data-id="{{ $order->order_id }}">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Output Details Section -->
                            <div class="card mb-4 d-none" id="outputDetailsCard">
                                <div class="card-header bg-info text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-edit me-2"></i>Détails des Sorties
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        Remplissez les quantités pour chaque ordre sélectionné. Les quantités défectueuses
                                        seront automatiquement déduites.
                                    </div>

                                    <div id="outputFormsContainer">
                                        <!-- Forms will be dynamically added here -->
                                    </div>

                                    <!-- Summary -->
                                    <div class="card mt-4" id="batchSummaryCard">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-chart-pie me-2"></i>Résumé du Lot
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Statistiques</h6>
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <td>Nombre d'ordres:</td>
                                                            <td class="text-end" id="summaryOrderCount">0</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Total à produire:</td>
                                                            <td class="text-end" id="summaryTotalToProduce">0 unités</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Total produit:</td>
                                                            <td class="text-end" id="summaryTotalProduced">0 unités</td>
                                                        </tr>
                                                        <tr class="table-success">
                                                            <td><strong>Total bon:</strong></td>
                                                            <td class="text-end"><strong id="summaryTotalGood">0
                                                                    unités</strong></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Répartition par Type</h6>
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <td>Production:</td>
                                                            <td class="text-end" id="summaryProductionCount">0</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Vente:</td>
                                                            <td class="text-end" id="summarySalesCount">0</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Mixte:</td>
                                                            <td class="text-end" id="summaryBothCount">0</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Avec conversion:</td>
                                                            <td class="text-end" id="summaryConversionCount">0</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Validation Messages -->
                            <div class="alert alert-danger d-none" id="batchValidationAlert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div id="batchValidationMessage"></div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary" id="submitBatchBtn" disabled>
                                    <i class="fas fa-save me-1"></i> Enregistrer les Sorties Groupées
                                </button>
                                <a href="{{ route('production-output.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Annuler
                                </a>
                                <button type="button" class="btn btn-outline-info" id="previewBatchBtn" disabled>
                                    <i class="fas fa-eye me-1"></i> Prévisualiser
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Détails de l'Ordre</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Prévisualisation des Sorties</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="previewContent">
                    <!-- Preview content -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="confirmBatchBtn">
                        <i class="fas fa-check me-1"></i> Confirmer l'Enregistrement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.075);
        }

        .output-form {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }

        .output-form:hover {
            background-color: #e9ecef;
        }

        .progress-sm {
            height: 8px;
        }

        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let selectedOrders = new Map();
            let outputForms = new Map();

            // Select all orders
            $('#selectAllOrders').change(function() {
                const isChecked = $(this).prop('checked');
                $('.order-checkbox').prop('checked', isChecked).trigger('change');
            });

            // Order checkbox change
            $(document).on('change', '.order-checkbox', function() {
                const orderId = $(this).val();
                const isChecked = $(this).prop('checked');
                const row = $(this).closest('tr');

                if (isChecked) {
                    // Add order to selection
                    selectedOrders.set(orderId, {
                        orderId: orderId,
                        productId: row.data('product-id'),
                        productType: row.data('product-type'),
                        remaining: row.data('remaining'),
                        hasConversions: row.data('has-conversions') === '1',
                        orderNumber: row.find('td:nth-child(2) .fw-medium').text(),
                        productName: row.find('td:nth-child(3) .fw-medium').text()
                    });

                    // Create output form
                    createOutputForm(orderId, selectedOrders.get(orderId));
                } else {
                    // Remove order from selection
                    selectedOrders.delete(orderId);
                    removeOutputForm(orderId);
                }

                updateUI();
            });

            // Create output form for an order
            function createOutputForm(orderId, orderData) {
                const formId = `outputForm_${orderId}`;
                const formHtml = `
                    <div class="output-form" id="${formId}">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>${orderData.orderNumber}</h6>
                                <p class="mb-1 small">${orderData.productName}</p>
                                <span class="badge ${getProductTypeBadge(orderData.productType)}">
                                    ${getProductTypeLabel(orderData.productType)}
                                </span>
                                ${orderData.hasConversions ?
                                    '<span class="badge bg-warning ms-1">Conversion</span>' : ''}
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Quantité Produite *</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control quantity-produced"
                                               data-order-id="${orderId}"
                                               min="0.01" step="0.01" max="${orderData.remaining}"
                                               value="${Math.min(1, orderData.remaining)}"
                                               required>
                                        <span class="input-group-text">unités</span>
                                    </div>
                                    <small class="form-text text-muted">
                                        Maximum: ${orderData.remaining} unités
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Quantité Défectueuse *</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control quantity-defective"
                                               data-order-id="${orderId}"
                                               min="0" step="0.01" max="${orderData.remaining}"
                                               value="0" required>
                                        <span class="input-group-text">unités</span>
                                    </div>
                                    <small class="form-text text-muted">
                                        Déduite du stock final
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Qualité</label>
                                    <select class="form-control quality-grade" data-order-id="${orderId}">
                                        <option value="excellent">Excellent</option>
                                        <option value="good" selected>Bon</option>
                                        <option value="average">Moyen</option>
                                        <option value="poor">Mauvais</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Notes</label>
                                    <input type="text" class="form-control notes"
                                           data-order-id="${orderId}"
                                           placeholder="Notes optionnelles...">
                                </div>
                            </div>
                        </div>
                        ${orderData.hasConversions ?
                            `<div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="alert alert-info py-2 mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Ce produit sera automatiquement converti selon les taux définis.
                                        </div>
                                    </div>
                                </div>` : ''}
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-order-btn"
                                        data-order-id="${orderId}">
                                    <i class="fas fa-times me-1"></i> Retirer
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                $('#outputFormsContainer').append(formHtml);
                outputForms.set(orderId, formId);
            }

            // Remove output form
            function removeOutputForm(orderId) {
                const formId = outputForms.get(orderId);
                if (formId) {
                    $(`#${formId}`).remove();
                    outputForms.delete(orderId);
                }
            }

            // Get product type badge
            function getProductTypeBadge(type) {
                switch (type) {
                    case 'production':
                        return 'bg-primary';
                    case 'sales':
                        return 'bg-success';
                    case 'both':
                        return 'bg-info';
                    default:
                        return 'bg-secondary';
                }
            }

            // Get product type label
            function getProductTypeLabel(type) {
                switch (type) {
                    case 'production':
                        return 'Production';
                    case 'sales':
                        return 'Vente';
                    case 'both':
                        return 'Mixte';
                    default:
                        return 'Inconnu';
                }
            }

            // Update UI based on selection
            function updateUI() {
                const hasSelection = selectedOrders.size > 0;

                // Show/hide output details card
                if (hasSelection) {
                    $('#outputDetailsCard').removeClass('d-none');
                    $('#submitBatchBtn').prop('disabled', false);
                    $('#previewBatchBtn').prop('disabled', false);
                } else {
                    $('#outputDetailsCard').addClass('d-none');
                    $('#submitBatchBtn').prop('disabled', true);
                    $('#previewBatchBtn').prop('disabled', true);
                }

                // Update summary
                updateBatchSummary();
            }

            // Update batch summary
            function updateBatchSummary() {
                let orderCount = 0;
                let totalToProduce = 0;
                let totalProduced = 0;
                let totalGood = 0;
                let productionCount = 0;
                let salesCount = 0;
                let bothCount = 0;
                let conversionCount = 0;

                selectedOrders.forEach((order, orderId) => {
                    orderCount++;
                    totalToProduce += order.remaining;

                    // Get form values
                    const produced = parseFloat($(`.quantity-produced[data-order-id="${orderId}"]`).val()) ||
                        0;
                    const defective = parseFloat($(`.quantity-defective[data-order-id="${orderId}"]`)
                    .val()) || 0;

                    totalProduced += produced;
                    totalGood += (produced - defective);

                    // Count by type
                    switch (order.productType) {
                        case 'production':
                            productionCount++;
                            break;
                        case 'sales':
                            salesCount++;
                            break;
                        case 'both':
                            bothCount++;
                            break;
                    }

                    if (order.hasConversions) conversionCount++;
                });

                // Update summary display
                $('#summaryOrderCount').text(orderCount);
                $('#summaryTotalToProduce').text(totalToProduce + ' unités');
                $('#summaryTotalProduced').text(totalProduced + ' unités');
                $('#summaryTotalGood').text(totalGood + ' unités');
                $('#summaryProductionCount').text(productionCount);
                $('#summarySalesCount').text(salesCount);
                $('#summaryBothCount').text(bothCount);
                $('#summaryConversionCount').text(conversionCount);
            }

            // Remove order button
            $(document).on('click', '.remove-order-btn', function() {
                const orderId = $(this).data('order-id');
                $(`.order-checkbox[value="${orderId}"]`).prop('checked', false).trigger('change');
            });

            // View order details
            $(document).on('click', '.view-order-btn', function() {
                const orderId = $(this).data('id');

                $.ajax({
                    url: "{{ url('production-orders') }}/" + orderId,
                    type: "GET",
                    success: function(response) {
                        $('#orderDetailsContent').html(response);
                        $('#orderDetailsModal').modal('show');
                    }
                });
            });

            // Input change events
            $(document).on('input', '.quantity-produced, .quantity-defective', function() {
                const orderId = $(this).data('order-id');
                const producedInput = $(`.quantity-produced[data-order-id="${orderId}"]`);
                const defectiveInput = $(`.quantity-defective[data-order-id="${orderId}"]`);

                const produced = parseFloat(producedInput.val()) || 0;
                const defective = parseFloat(defectiveInput.val()) || 0;
                const maxProduced = parseFloat(producedInput.attr('max')) || 0;

                // Validate defective doesn't exceed produced
                if (defective > produced) {
                    defectiveInput.addClass('is-invalid');
                    defectiveInput.next('.invalid-feedback').remove();
                    defectiveInput.after(
                        '<div class="invalid-feedback">Ne peut pas dépasser la quantité produite</div>');
                } else {
                    defectiveInput.removeClass('is-invalid');
                    defectiveInput.next('.invalid-feedback').remove();
                }

                // Validate produced doesn't exceed max
                if (produced > maxProduced) {
                    producedInput.addClass('is-invalid');
                    producedInput.next('.invalid-feedback').remove();
                    producedInput.after(
                    `<div class="invalid-feedback">Maximum ${maxProduced} unités</div>`);
                } else {
                    producedInput.removeClass('is-invalid');
                    producedInput.next('.invalid-feedback').remove();
                }

                updateBatchSummary();
            });

            // Preview batch
            $('#previewBatchBtn').click(function() {
                if (!validateForms()) {
                    return;
                }

                const previewContent = generatePreviewContent();
                $('#previewContent').html(previewContent);
                $('#previewModal').modal('show');
            });

            // Validate all forms
            function validateForms() {
                let isValid = true;
                const errors = [];

                selectedOrders.forEach((order, orderId) => {
                    const produced = parseFloat($(`.quantity-produced[data-order-id="${orderId}"]`).val()) ||
                        0;
                    const defective = parseFloat($(`.quantity-defective[data-order-id="${orderId}"]`)
                    .val()) || 0;

                    if (produced <= 0) {
                        isValid = false;
                        errors.push(
                            `Ordre ${order.orderNumber}: La quantité produite doit être supérieure à 0`);
                    }

                    if (defective > produced) {
                        isValid = false;
                        errors.push(
                            `Ordre ${order.orderNumber}: La quantité défectueuse ne peut pas dépasser la quantité produite`
                            );
                    }

                    if (produced > order.remaining) {
                        isValid = false;
                        errors.push(
                            `Ordre ${order.orderNumber}: La quantité produite (${produced}) dépasse le maximum autorisé (${order.remaining})`
                            );
                    }
                });

                if (!isValid) {
                    showValidationError(errors.join('<br>'));
                    return false;
                }

                hideValidationError();
                return true;
            }

            // Generate preview content
            function generatePreviewContent() {
                let html = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Prévisualisation de ${selectedOrders.size} sortie(s) de production
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Ordre</th>
                                    <th>Produit</th>
                                    <th>Type</th>
                                    <th>Quantité Produite</th>
                                    <th>Quantité Défectueuse</th>
                                    <th>Quantité Bonne</th>
                                    <th>Qualité</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                let totalProduced = 0;
                let totalDefective = 0;
                let totalGood = 0;

                selectedOrders.forEach((order, orderId) => {
                    const produced = parseFloat($(`.quantity-produced[data-order-id="${orderId}"]`).val()) ||
                        0;
                    const defective = parseFloat($(`.quantity-defective[data-order-id="${orderId}"]`)
                    .val()) || 0;
                    const good = produced - defective;
                    const quality = $(`.quality-grade[data-order-id="${orderId}"]`).val();
                    const notes = $(`.notes[data-order-id="${orderId}"]`).val();

                    totalProduced += produced;
                    totalDefective += defective;
                    totalGood += good;

                    html += `
                        <tr>
                            <td>${order.orderNumber}</td>
                            <td>${order.productName}</td>
                            <td><span class="badge ${getProductTypeBadge(order.productType)}">${getProductTypeLabel(order.productType)}</span></td>
                            <td class="text-end">${produced}</td>
                            <td class="text-end">${defective}</td>
                            <td class="text-end"><strong>${good}</strong></td>
                            <td>${getQualityLabel(quality)}</td>
                            <td><small>${notes || '-'}</small></td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                            <tfoot class="table-success">
                                <tr>
                                    <th colspan="3" class="text-end">Totaux:</th>
                                    <th class="text-end">${totalProduced}</th>
                                    <th class="text-end">${totalDefective}</th>
                                    <th class="text-end"><strong>${totalGood}</strong></th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Confirmation requise:</strong> En cliquant sur "Confirmer l'Enregistrement", vous allez :
                        <ul class="mb-0 mt-2">
                            <li>Créer ${selectedOrders.size} sortie(s) de production</li>
                            <li>Mettre à jour les stocks des produits</li>
                            <li>Appliquer les conversions automatiques si nécessaire</li>
                            <li>Mettre à jour l'état des ordres de production</li>
                        </ul>
                    </div>
                `;

                return html;
            }

            // Get quality label
            function getQualityLabel(grade) {
                const labels = {
                    'excellent': '<span class="badge bg-success">Excellent</span>',
                    'good': '<span class="badge bg-info">Bon</span>',
                    'average': '<span class="badge bg-warning">Moyen</span>',
                    'poor': '<span class="badge bg-danger">Mauvais</span>'
                };
                return labels[grade] || '-';
            }

            // Confirm batch submission
            $('#confirmBatchBtn').click(function() {
                $('#previewModal').modal('hide');
                submitBatchForm();
            });

            // Form submission
            $('#batchOutputForm').submit(function(e) {
                e.preventDefault();

                if (!validateForms()) {
                    return;
                }

                submitBatchForm();
            });

            // Submit batch form
            function submitBatchForm() {
                // Prepare data
                const outputs = [];
                const productionDate = $('#batch_production_date').val();

                selectedOrders.forEach((order, orderId) => {
                    const produced = parseFloat($(`.quantity-produced[data-order-id="${orderId}"]`).val()) ||
                        0;
                    const defective = parseFloat($(`.quantity-defective[data-order-id="${orderId}"]`)
                    .val()) || 0;
                    const quality = $(`.quality-grade[data-order-id="${orderId}"]`).val();
                    const notes = $(`.notes[data-order-id="${orderId}"]`).val();

                    outputs.push({
                        production_order_id: orderId,
                        product_id: order.productId,
                        quantity_produced: produced,
                        quantity_defective: defective,
                        quality_grade: quality,
                        production_date: productionDate,
                        notes: notes
                    });
                });

                // Disable submit button
                const submitBtn = $('#submitBatchBtn');
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');

                // Submit via AJAX
                $.ajax({
                    url: "{{ route('production-output.batch-store') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        outputs: outputs,
                        production_date: productionDate
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                "{{ route('production-output.index') }}";
                            }, 2000);
                        } else {
                            showValidationError(response.message);
                            submitBtn.prop('disabled', false).html(
                                '<i class="fas fa-save me-1"></i> Enregistrer les Sorties Groupées');
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        let errorMessage = 'Une erreur est survenue';

                        if (errors) {
                            errorMessage = Object.values(errors).flat().join('<br>');
                        } else if (xhr.responseJSON?.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        showValidationError(errorMessage);
                        showToast('error', errorMessage);
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i> Enregistrer les Sorties Groupées');
                    }
                });
            }

            // Show validation error
            function showValidationError(message) {
                $('#batchValidationAlert').removeClass('d-none');
                $('#batchValidationMessage').html(message);
                $('html, body').animate({
                    scrollTop: $('#batchValidationAlert').offset().top - 100
                }, 500);
            }

            // Hide validation error
            function hideValidationError() {
                $('#batchValidationAlert').addClass('d-none');
            }

            // Toast notification function
            function showToast(type, message) {
                const toast = $(`
                    <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0"
                         role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">${message}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `);

                $('#toast-container').append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();

                setTimeout(() => toast.remove(), 5000);
            }
        });
    </script>
@endpush
