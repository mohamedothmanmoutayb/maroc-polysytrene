@extends('layouts.app')

@section('title', 'Modifier Consommation Matières Premières')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Consommation Matières Premières</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('production-consumption.index') }}">
                                        Consommation
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('production-consumption.show', $consumption->consumption_id) }}">
                                        {{ $consumption->productionOrder->order_number }}
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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-edit me-2"></i>Modifier Consommation Matières Premières
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="productionConsumptionForm">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Ordre de Production</label>
                                        <input type="text" class="form-control" readonly
                                            value="{{ $consumption->productionOrder->order_number }} - {{ $consumption->productionOrder->product->product_name }}">
                                        <input type="hidden" name="production_order_id"
                                            value="{{ $consumption->production_order_id }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Matière Première</label>
                                        <input type="text" class="form-control" readonly
                                            value="{{ $consumption->rawMaterial->material_code }} - {{ $consumption->rawMaterial->material_name }}">
                                        <input type="hidden" name="material_id" value="{{ $consumption->material_id }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Order Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Informations de l'Ordre
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th>Produit:</th>
                                                    <td>{{ $consumption->productionOrder->product->product_name }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Code:</th>
                                                    <td>{{ $consumption->productionOrder->product->product_code }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Quantité Planifiée:</th>
                                                    <td>{{ $consumption->productionOrder->quantity_to_produce }} unités
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Unité Matière:</th>
                                                    <td>{{ $consumption->rawMaterial->unit_of_measure }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th>Coût Unitaire:</th>
                                                    <td>{{ number_format($consumption->unit_cost, 2, ',', '.') }} DH</td>
                                                </tr>
                                                <tr>
                                                    <th>Stock Actuel:</th>
                                                    <td>
                                                        {{ number_format($consumption->rawMaterial->current_stock, 2, ',', '.') }}
                                                        {{ $consumption->rawMaterial->unit_of_measure }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Stock Minimum:</th>
                                                    <td>
                                                        {{ number_format($consumption->rawMaterial->min_stock_level, 2, ',', '.') }}
                                                        {{ $consumption->rawMaterial->unit_of_measure }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Consommation Planifiée:</th>
                                                    <td>
                                                        {{ number_format($consumption->planned_quantity, 2, ',', '.') }}
                                                        {{ $consumption->rawMaterial->unit_of_measure }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="actual_quantity_used" class="form-label">Quantité Réelle Utilisée
                                            *</label>
                                        <input type="number" class="form-control" id="actual_quantity_used"
                                            name="actual_quantity_used" required min="0" step="0.01"
                                            value="{{ $consumption->actual_quantity_used }}"
                                            max="{{ $consumption->rawMaterial->current_stock + $consumption->actual_quantity_used + $consumption->waste_quantity }}">
                                        <small class="form-text text-muted">Quantité réellement consommée</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="waste_quantity" class="form-label">Quantité Déchet *</label>
                                        <input type="number" class="form-control" id="waste_quantity" name="waste_quantity"
                                            required min="0" step="0.01"
                                            value="{{ $consumption->waste_quantity }}">
                                        <small class="form-text text-muted">Quantité gaspillée/perdue</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Quantité Planifiée</label>
                                        <input type="text" class="form-control" readonly
                                            value="{{ number_format($consumption->planned_quantity, 2, ',', '.') }} {{ $consumption->rawMaterial->unit_of_measure }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-calculator me-2"></i>Résumé & Analyse
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td>Quantité Totale:</td>
                                                    <td class="text-right" id="summaryTotal">
                                                        {{ number_format($consumption->actual_quantity_used + $consumption->waste_quantity, 2, ',', '.') }}
                                                        {{ $consumption->rawMaterial->unit_of_measure }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Quantité Déchet:</td>
                                                    <td class="text-right" id="summaryWaste">
                                                        {{ number_format($consumption->waste_quantity, 2, ',', '.') }}
                                                        {{ $consumption->rawMaterial->unit_of_measure }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Taux de Déchet:</td>
                                                    <td class="text-right" id="summaryWasteRate">
                                                        @php
                                                            $wasteRate =
                                                                $consumption->actual_quantity_used > 0
                                                                    ? ($consumption->waste_quantity /
                                                                            $consumption->actual_quantity_used) *
                                                                        100
                                                                    : 0;
                                                        @endphp
                                                        {{ number_format($wasteRate, 2, ',', '.') }}%
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Planifié:</td>
                                                    <td class="text-right" id="summaryPlanned">
                                                        {{ number_format($consumption->planned_quantity, 2, ',', '.') }}
                                                        {{ $consumption->rawMaterial->unit_of_measure }}
                                                    </td>
                                                </tr>
                                                <tr class="table-info">
                                                    <td><strong>Différence:</strong></td>
                                                    <td class="text-right"><strong id="summaryDifference">
                                                            @php
                                                                $difference =
                                                                    $consumption->actual_quantity_used -
                                                                    $consumption->planned_quantity;
                                                            @endphp
                                                            {{ number_format($difference, 2, ',', '.') }}
                                                            {{ $consumption->rawMaterial->unit_of_measure }}
                                                        </strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Coût Unitaire:</td>
                                                    <td class="text-right" id="summaryUnitCost">
                                                        {{ number_format($consumption->unit_cost, 2, ',', '.') }} DH
                                                    </td>
                                                </tr>
                                                <tr class="table-success">
                                                    <td><strong>Coût Total:</strong></td>
                                                    <td class="text-right"><strong id="summaryTotalCost">
                                                            {{ number_format($consumption->total_cost, 2, ',', '.') }} DH
                                                        </strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Impact Stock:</td>
                                                    <td class="text-right" id="stockImpact">
                                                        @php
                                                            $oldTotal =
                                                                $consumption->actual_quantity_used +
                                                                $consumption->waste_quantity;
                                                            $currentStock = $consumption->rawMaterial->current_stock;
                                                        @endphp
                                                        {{ $currentStock + $oldTotal }} → <span
                                                            id="newStockImpact">{{ $currentStock }}</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="8"
                                            placeholder="Raisons de la surconsommation, cause des déchets, observations...">{{ $consumption->notes }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div id="validationAlert" class="alert alert-danger d-none">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div id="validationMessage"></div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Mettre à jour
                                </button>
                                <a href="{{ route('production-consumption.show', $consumption->consumption_id) }}"
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
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var unitCost = {{ $consumption->unit_cost }};
            var unitMeasure = '{{ $consumption->rawMaterial->unit_of_measure }}';
            var currentStock = {{ $consumption->rawMaterial->current_stock }};
            var oldTotalUsed = {{ $consumption->actual_quantity_used + $consumption->waste_quantity }};
            var maxAllowed = currentStock + oldTotalUsed;

            // Set max attribute for quantity fields
            $('#actual_quantity_used').attr('max', maxAllowed);

            // Calculate summary in real-time
            function updateSummary() {
                var actualUsed = parseFloat($('#actual_quantity_used').val()) || 0;
                var waste = parseFloat($('#waste_quantity').val()) || 0;
                var planned = {{ $consumption->planned_quantity }};

                // Check if we exceed available stock
                var totalUsed = actualUsed + waste;
                if (totalUsed > maxAllowed) {
                    $('#actual_quantity_used').addClass('is-invalid');
                    $('#validationAlert').removeClass('d-none');
                    $('#validationMessage').html('Stock insuffisant! Maximum autorisé: ' + maxAllowed.toFixed(2) +
                        ' ' + unitMeasure);
                    return false;
                } else {
                    $('#actual_quantity_used').removeClass('is-invalid');
                }

                if (waste > actualUsed) {
                    $('#waste_quantity').addClass('is-invalid');
                    $('#validationAlert').removeClass('d-none');
                    $('#validationMessage').html(
                        'La quantité déchet ne peut pas être supérieure à la quantité utilisée');
                    return false;
                } else {
                    $('#waste_quantity').removeClass('is-invalid');
                    $('#validationAlert').addClass('d-none');
                }

                var total = actualUsed + waste;
                var wasteRate = actualUsed > 0 ? (waste / actualUsed) * 100 : 0;
                var difference = actualUsed - planned;
                var totalCost = actualUsed * unitCost;
                var newStock = currentStock + oldTotalUsed - total;
                var stockDifference = newStock - currentStock;

                $('#summaryTotal').text(total.toFixed(2) + ' ' + unitMeasure);
                $('#summaryWaste').text(waste.toFixed(2) + ' ' + unitMeasure);
                $('#summaryWasteRate').text(wasteRate.toFixed(2) + '%');
                $('#summaryPlanned').text(planned.toFixed(2) + ' ' + unitMeasure);
                $('#summaryDifference').text(difference.toFixed(2) + ' ' + unitMeasure);
                $('#summaryUnitCost').text(unitCost.toFixed(2) + ' DH');
                $('#summaryTotalCost').text(totalCost.toFixed(2) + ' DH');

                var impactText = (currentStock + oldTotalUsed).toFixed(2) + ' → ' + newStock.toFixed(2);
                if (stockDifference > 0) {
                    impactText += ' (+' + stockDifference.toFixed(2) + ')';
                    $('#stockImpact').addClass('text-success');
                } else if (stockDifference < 0) {
                    impactText += ' (' + stockDifference.toFixed(2) + ')';
                    $('#stockImpact').addClass('text-danger');
                } else {
                    $('#stockImpact').removeClass('text-success text-danger');
                }
                $('#stockImpact').html(impactText);

                return true;
            }

            // Update summary when quantities change
            $('#actual_quantity_used, #waste_quantity').on('input', function() {
                updateSummary();
            });

            // Production Consumption Form Submit
            $('#productionConsumptionForm').submit(function(e) {
                e.preventDefault();

                if (!updateSummary()) {
                    return;
                }

                var actualUsed = parseFloat($('#actual_quantity_used').val());
                var waste = parseFloat($('#waste_quantity').val());
                var totalUsed = actualUsed + waste;

                // Validate quantities
                if (totalUsed > maxAllowed) {
                    showToast('error', 'Stock insuffisant! Maximum autorisé: ' + maxAllowed.toFixed(2) +
                        ' ' + unitMeasure);
                    return;
                }

                if (waste > actualUsed) {
                    showToast('error',
                        'La quantité déchet ne peut pas être supérieure à la quantité utilisée');
                    return;
                }

                // Check minimum stock level
                var newStock = currentStock + oldTotalUsed - totalUsed;
                var minStock = {{ $consumption->rawMaterial->min_stock_level }};
                if (newStock < minStock) {
                    if (!confirm('Attention! Le stock deviendra ' + newStock.toFixed(2) + ' ' +
                            unitMeasure +
                            ', ce qui est en dessous du minimum (' + minStock + ' ' + unitMeasure +
                            '). Voulez-vous continuer?')) {
                        return;
                    }
                }

                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('production-consumption.update', $consumption->consumption_id) }}",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('production-consumption.show', $consumption->consumption_id) }}";
                            }, 1500);
                        } else {
                            $('#validationAlert').removeClass('d-none');
                            $('#validationMessage').text(response.message);
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = '';

                        if (errors) {
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        } else {
                            errorMessage = xhr.responseJSON?.message ||
                                'Une erreur est survenue';
                        }

                        $('#validationAlert').removeClass('d-none');
                        $('#validationMessage').text(errorMessage);
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
