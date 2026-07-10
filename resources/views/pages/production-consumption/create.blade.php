@extends('layouts.app')

@section('title', 'Nouvelle Consommation Matières Premières')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Nouvelle Consommation Matières Premières</h4>
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
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Nouvelle
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
                            <i class="fas fa-plus-circle me-2"></i>Enregistrer une Consommation Matières Premières
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="productionConsumptionForm">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="production_order_id" class="form-label">Ordre de Production *</label>
                                        <select class="form-control select2" id="production_order_id"
                                            name="production_order_id" required>
                                            <option value="">Sélectionner un ordre</option>
                                            @foreach ($productionOrders as $order)
                                                <option value="{{ $order->order_id }}"
                                                    {{ isset($productionOrder) && $productionOrder->order_id == $order->order_id ? 'selected' : '' }}>
                                                    {{ $order->order_number }} - {{ $order->product->product_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="material_id" class="form-label">Matière Première *</label>
                                        <select class="form-control select2" id="material_id" name="material_id" required>
                                            <option value="">Sélectionner une matière première</option>
                                            @foreach ($rawMaterials as $material)
                                                <option value="{{ $material->material_id }}">
                                                    {{ $material->material_code }} - {{ $material->material_name }}
                                                    (Stock: {{ $material->current_stock }}
                                                    {{ $material->unit_of_measure }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            @if (isset($productionOrder))
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
                                                        <td>{{ $productionOrder->product->product_name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Code:</th>
                                                        <td>{{ $productionOrder->product->product_code }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Quantité Planifiée:</th>
                                                        <td>{{ $productionOrder->quantity_to_produce }} unités</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Statut:</th>
                                                        <td>
                                                            @switch($productionOrder->status)
                                                                @case('in_progress')
                                                                    <span class="badge badge-primary">En cours</span>
                                                                @break

                                                                @case('completed')
                                                                    <span class="badge badge-success">Terminé</span>
                                                                @break
                                                            @endswitch
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Consommations Planifiées:</h6>
                                                @if ($productionOrder->consumptions && $productionOrder->consumptions->count() > 0)
                                                    <div class="table-responsive">
                                                        <table class="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th>Matière</th>
                                                                    <th>Planifié</th>
                                                                    <th>Réel</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($productionOrder->consumptions as $consumption)
                                                                    <tr>
                                                                        <td>{{ $consumption->rawMaterial->material_name }}
                                                                        </td>
                                                                        <td>{{ number_format($consumption->planned_quantity, 2, ',', '.') }}
                                                                        </td>
                                                                        <td>{{ number_format($consumption->actual_quantity_used, 2, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <p class="text-muted">Aucune consommation planifiée</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="actual_quantity_used" class="form-label">Quantité Réelle Utilisée
                                            *</label>
                                        <input type="number" class="form-control" id="actual_quantity_used"
                                            name="actual_quantity_used" required min="0" step="0.01"
                                            placeholder="Ex: 150.50">
                                        <small class="form-text text-muted">Quantité réellement consommée</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="waste_quantity" class="form-label">Quantité Déchet *</label>
                                        <input type="number" class="form-control" id="waste_quantity" name="waste_quantity"
                                            required min="0" step="0.01" placeholder="Ex: 5.25" value="0">
                                        <small class="form-text text-muted">Quantité gaspillée/perdue</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label id="plannedQuantityLabel" class="form-label">Quantité Planifiée</label>
                                        <input type="text" class="form-control" id="planned_quantity" readonly
                                            placeholder="0.00">
                                        <small class="form-text text-muted">Calculé à partir du BOM</small>
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
                                                    <td class="text-right" id="summaryTotal">0.00</td>
                                                </tr>
                                                <tr>
                                                    <td>Quantité Déchet:</td>
                                                    <td class="text-right" id="summaryWaste">0.00</td>
                                                </tr>
                                                <tr>
                                                    <td>Taux de Déchet:</td>
                                                    <td class="text-right" id="summaryWasteRate">0%</td>
                                                </tr>
                                                <tr>
                                                    <td>Planifié:</td>
                                                    <td class="text-right" id="summaryPlanned">0.00</td>
                                                </tr>
                                                <tr class="table-info">
                                                    <td><strong>Différence:</strong></td>
                                                    <td class="text-right"><strong id="summaryDifference">0.00</strong>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Coût Unitaire:</td>
                                                    <td class="text-right" id="summaryUnitCost">0.00 DH</td>
                                                </tr>
                                                <tr class="table-success">
                                                    <td><strong>Coût Total:</strong></td>
                                                    <td class="text-right"><strong id="summaryTotalCost">0.00 DH</strong>
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
                                            placeholder="Raisons de la surconsommation, cause des déchets, observations..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div id="validationAlert" class="alert alert-danger d-none">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div id="validationMessage"></div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('production-consumption.index') }}" class="btn btn-secondary">
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

            var unitCost = 0;
            var unitMeasure = '';

            // Load material details when material is selected
            $('#material_id').change(function() {
                var materialId = $(this).val();
                if (materialId) {
                    $.ajax({
                        url: "{{ url('raw-materials') }}/" + materialId + "/details",
                        type: "GET",
                        success: function(response) {
                            if (response.success) {
                                unitCost = response.unit_cost;
                                unitMeasure = response.unit_of_measure;
                                $('#summaryUnitCost').text(response.unit_cost.toFixed(2) +
                                    ' DH');

                                // Update label with unit measure
                                $('#plannedQuantityLabel').text('Quantité Planifiée (' +
                                    unitMeasure + ')');

                                updateSummary();
                            }
                        }
                    });
                }
            });

            // Load order details when order is selected
            $('#production_order_id').change(function() {
                var orderId = $(this).val();
                if (orderId) {
                    // Get planned quantity from BOM
                    var materialId = $('#material_id').val();
                    if (materialId) {
                        getPlannedQuantity(orderId, materialId);
                    }

                    // Update page with order details
                    window.location.href = "{{ route('production-consumption.create') }}?order_id=" +
                        orderId;
                }
            });

            // Get planned quantity from BOM
            function getPlannedQuantity(orderId, materialId) {
                $.ajax({
                    url: "{{ url('production-orders') }}/" + orderId + "/bom/" + materialId,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#planned_quantity').val(response.planned_quantity.toFixed(2));
                            updateSummary();
                        }
                    }
                });
            }

            // Calculate summary in real-time
            function updateSummary() {
                var actualUsed = parseFloat($('#actual_quantity_used').val()) || 0;
                var waste = parseFloat($('#waste_quantity').val()) || 0;
                var planned = parseFloat($('#planned_quantity').val()) || 0;

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

                $('#summaryTotal').text(total.toFixed(2) + ' ' + unitMeasure);
                $('#summaryWaste').text(waste.toFixed(2) + ' ' + unitMeasure);
                $('#summaryWasteRate').text(wasteRate.toFixed(2) + '%');
                $('#summaryPlanned').text(planned.toFixed(2) + ' ' + unitMeasure);
                $('#summaryDifference').text(difference.toFixed(2) + ' ' + unitMeasure);
                $('#summaryTotalCost').text(totalCost.toFixed(2) + ' DH');

                return true;
            }

            // Update summary when quantities change
            $('#actual_quantity_used, #waste_quantity').on('input', function() {
                updateSummary();
            });

            // Pre-load planned quantity if order and material are preselected
            @if (isset($productionOrder) && $productionOrder->consumptions)
                @foreach ($productionOrder->consumptions as $consumption)
                    @if ($loop->first)
                        $('#material_id').val('{{ $consumption->material_id }}').trigger('change');
                        $('#planned_quantity').val('{{ $consumption->planned_quantity }}');
                        setTimeout(function() {
                            unitCost = {{ $consumption->unit_cost }};
                            unitMeasure = '{{ $consumption->rawMaterial->unit_of_measure }}';
                            updateSummary();
                        }, 500);
                    @endif
                @endforeach
            @endif

            // Production Consumption Form Submit
            $('#productionConsumptionForm').submit(function(e) {
                e.preventDefault();

                if (!updateSummary()) {
                    return;
                }

                var actualUsed = parseFloat($('#actual_quantity_used').val());
                var waste = parseFloat($('#waste_quantity').val());
                var materialId = $('#material_id').val();
                var orderId = $('#production_order_id').val();

                // Validate quantities
                if (waste > actualUsed) {
                    showToast('error',
                        'La quantité déchet ne peut pas être supérieure à la quantité utilisée');
                    return;
                }

                // Check material stock
                $.ajax({
                    url: "{{ url('raw-materials') }}/" + materialId + "/stock",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            var totalNeeded = actualUsed + waste;
                            if (totalNeeded > response.available_stock) {
                                $('#validationAlert').removeClass('d-none');
                                $('#validationMessage').text(
                                    'Stock insuffisant! Disponible: ' + response
                                    .available_stock +
                                    ' ' + unitMeasure + ', Requis: ' + totalNeeded + ' ' +
                                    unitMeasure
                                );
                                showToast('error', 'Stock insuffisant');
                                return;
                            }

                            // Submit the form
                            submitForm();
                        }
                    }
                });

                function submitForm() {
                    var formData = $('#productionConsumptionForm').serialize();

                    $.ajax({
                        url: "{{ route('production-consumption.store') }}",
                        type: "POST",
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                setTimeout(function() {
                                    window.location.href =
                                        "{{ route('production-consumption.show', '') }}/" +
                                        response.consumption_id;
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
                }
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
