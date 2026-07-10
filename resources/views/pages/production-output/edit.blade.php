@extends('layouts.app')

@section('title', 'Modifier Sortie de Production')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Sortie de Production</h4>
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
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('production-output.show', $output->output_id) }}">
                                        {{ $output->productionOrder->order_number }}
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
                            <i class="fas fa-edit me-2"></i>Modifier Sortie de Production
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="productionOutputForm">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Ordre de Production</label>
                                        <input type="text" class="form-control" readonly
                                            value="{{ $output->productionOrder->order_number }} - {{ $output->productionOrder->product->product_name }}">
                                        <input type="hidden" name="production_order_id"
                                            value="{{ $output->production_order_id }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="production_date" class="form-label">Date de Production *</label>
                                        <input type="date" class="form-control" id="production_date"
                                            name="production_date" required
                                            value="{{ \Carbon\Carbon::parse($output->production_date)->format('Y-m-d') }}">
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
                                                    <td>{{ $output->product->product_name }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Code:</th>
                                                    <td>{{ $output->product->product_code }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Quantité Planifiée:</th>
                                                    <td>{{ $output->productionOrder->quantity_to_produce }} unités</td>
                                                </tr>
                                                <tr>
                                                    <th>Unité:</th>
                                                    <td>{{ $output->product->unit_of_measure }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                @php
                                                    $alreadyProduced = \App\Models\ProductionOutput::where(
                                                        'production_order_id',
                                                        $output->production_order_id,
                                                    )
                                                        ->where('output_id', '!=', $output->output_id)
                                                        ->sum('quantity_produced');
                                                    $remaining =
                                                        $output->productionOrder->quantity_to_produce -
                                                        $alreadyProduced;
                                                @endphp
                                                <tr>
                                                    <th>Date Début:</th>
                                                    <td>{{ $output->productionOrder->start_date->format('d/m/Y') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Date Fin Prévue:</th>
                                                    <td>{{ $output->productionOrder->expected_completion_date->format('d/m/Y') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Déjà Produit (autres):</th>
                                                    <td>{{ $alreadyProduced }} unités</td>
                                                </tr>
                                                <tr>
                                                    <th>Maximum autorisé:</th>
                                                    <td>{{ $remaining + $output->quantity_produced }} unités</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="quantity_produced" class="form-label">Quantité Produite *</label>
                                        <input type="number" class="form-control" id="quantity_produced"
                                            name="quantity_produced" required min="0.01" step="0.01"
                                            value="{{ $output->quantity_produced }}"
                                            max="{{ $remaining + $output->quantity_produced }}">
                                        <small class="form-text text-muted">Quantité totale sortie de production</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="quantity_defective" class="form-label">Quantité Défectueuse *</label>
                                        <input type="number" class="form-control" id="quantity_defective"
                                            name="quantity_defective" required min="0" step="0.01"
                                            value="{{ $output->quantity_defective }}">
                                        <small class="form-text text-muted">Quantité avec défauts</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="quality_grade" class="form-label">Note Qualité *</label>
                                        <select class="form-control" id="quality_grade" name="quality_grade" required>
                                            <option value="excellent"
                                                {{ $output->quality_grade == 'excellent' ? 'selected' : '' }}>Excellent
                                            </option>
                                            <option value="good"
                                                {{ $output->quality_grade == 'good' ? 'selected' : '' }}>Bon</option>
                                            <option value="average"
                                                {{ $output->quality_grade == 'average' ? 'selected' : '' }}>Moyen</option>
                                            <option value="poor"
                                                {{ $output->quality_grade == 'poor' ? 'selected' : '' }}>Mauvais</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-calculator me-2"></i>Résumé
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td>Quantité Produite:</td>
                                                    <td class="text-right" id="summaryQuantity">
                                                        {{ $output->quantity_produced }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Quantité Défectueuse:</td>
                                                    <td class="text-right" id="summaryDefective">
                                                        {{ $output->quantity_defective }}</td>
                                                </tr>
                                                <tr class="table-success">
                                                    <td><strong>Quantité Bonne:</strong></td>
                                                    <td class="text-right"><strong
                                                            id="summaryGood">{{ $output->quantity_produced - $output->quantity_defective }}</strong>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Taux de Défaut:</td>
                                                    <td class="text-right" id="summaryDefectRate">
                                                        @php
                                                            $defectRate =
                                                                $output->quantity_produced > 0
                                                                    ? ($output->quantity_defective /
                                                                            $output->quantity_produced) *
                                                                        100
                                                                    : 0;
                                                        @endphp
                                                        {{ number_format($defectRate, 2, ',', '.') }}%
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Impact Stock:</td>
                                                    <td class="text-right" id="stockImpact">
                                                        @php
                                                            $productStock = $output->product->stock;
                                                            $currentStock = $productStock
                                                                ? $productStock->current_quantity
                                                                : 0;
                                                            $oldGoodQuantity =
                                                                $output->quantity_produced -
                                                                $output->quantity_defective;
                                                        @endphp
                                                        {{ $oldGoodQuantity }} → <span id="newStockImpact">0</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="6"
                                            placeholder="Notes sur la qualité, problèmes rencontrés, observations...">{{ $output->notes }}</textarea>
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
                                <a href="{{ route('production-output.show', $output->output_id) }}"
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
            // Calculate summary in real-time
            function updateSummary() {
                var quantityProduced = parseFloat($('#quantity_produced').val()) || 0;
                var quantityDefective = parseFloat($('#quantity_defective').val()) || 0;
                var maxAllowed = parseFloat($('#quantity_produced').attr('max')) || quantityProduced;

                // Validate against max allowed
                if (quantityProduced > maxAllowed) {
                    $('#quantity_produced').addClass('is-invalid');
                    $('#validationAlert').removeClass('d-none');
                    $('#validationMessage').html('Quantité excessive! Maximum autorisé: ' + maxAllowed + ' unités');
                    return false;
                } else {
                    $('#quantity_produced').removeClass('is-invalid');
                }

                if (quantityDefective > quantityProduced) {
                    $('#quantity_defective').addClass('is-invalid');
                    $('#validationAlert').removeClass('d-none');
                    $('#validationMessage').html(
                        'La quantité défectueuse ne peut pas être supérieure à la quantité produite');
                    return false;
                } else {
                    $('#quantity_defective').removeClass('is-invalid');
                    $('#validationAlert').addClass('d-none');
                }

                var goodQuantity = quantityProduced - quantityDefective;
                var defectRate = quantityProduced > 0 ? (quantityDefective / quantityProduced) * 100 : 0;
                var oldGoodQuantity = {{ $output->quantity_produced - $output->quantity_defective }};
                var stockDifference = goodQuantity - oldGoodQuantity;

                $('#summaryQuantity').text(quantityProduced + ' unités');
                $('#summaryDefective').text(quantityDefective + ' unités');
                $('#summaryGood').text(goodQuantity + ' unités');
                $('#summaryDefectRate').text(defectRate.toFixed(2) + '%');

                var impactText = oldGoodQuantity + ' → ' + goodQuantity;
                if (stockDifference > 0) {
                    impactText += ' (+' + stockDifference + ')';
                    $('#stockImpact').addClass('text-success');
                } else if (stockDifference < 0) {
                    impactText += ' (' + stockDifference + ')';
                    $('#stockImpact').addClass('text-danger');
                } else {
                    $('#stockImpact').removeClass('text-success text-danger');
                }
                $('#stockImpact').html(impactText);

                return true;
            }

            // Update summary when quantities change
            $('#quantity_produced, #quantity_defective').on('input', function() {
                updateSummary();
            });

            // Production Output Form Submit
            $('#productionOutputForm').submit(function(e) {
                e.preventDefault();

                if (!updateSummary()) {
                    return;
                }

                var quantityProduced = parseFloat($('#quantity_produced').val());
                var quantityDefective = parseFloat($('#quantity_defective').val());
                var maxAllowed = parseFloat($('#quantity_produced').attr('max'));

                // Validate quantities
                if (quantityProduced > maxAllowed) {
                    showToast('error', 'Quantité excessive! Maximum autorisé: ' + maxAllowed + ' unités');
                    return;
                }

                if (quantityDefective > quantityProduced) {
                    showToast('error',
                        'La quantité défectueuse ne peut pas être supérieure à la quantité produite');
                    return;
                }

                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('production-output.update', $output->output_id) }}",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('production-output.show', $output->output_id) }}";
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
@extends('layouts.app')

@section('title', 'Modifier Ordre de Production')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Ordre de Production</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('production-orders.index') }}">
                                        Ordres de Production
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
                            <i class="fas fa-edit me-2"></i>Modifier l'Ordre de Production #{{ $order->order_number }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="editProductionOrderForm">
                            @csrf
                            @method('PUT')

                            <!-- Order Information -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Ordre:</strong> {{ $order->order_number }}
                                        | <strong>Type:</strong>
                                        @if ($order->production_type === 'type1')
                                            <span class="badge bg-primary">Production Directe</span>
                                        @elseif($order->production_type === 'type2')
                                            <span class="badge bg-warning">Découpage</span>
                                        @elseif($order->production_type === 'type3')
                                            <span class="badge bg-success">Conversion</span>
                                        @endif
                                        | <strong>Statut:</strong>
                                        <span
                                            class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'approved' ? 'info' : 'secondary') }}">
                                            {{ $order->status === 'pending' ? 'En attente' : ($order->status === 'approved' ? 'Approuvé' : $order->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Production Type Selection (Disabled for editing) -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3">
                                        <i class="fas fa-cogs me-2"></i>Type de Production
                                    </h6>
                                    <div class="row">
                                        <!-- Type 1: Production Directe -->
                                        <div class="col-md-4 mb-3">
                                            <div
                                                class="card h-100 {{ $order->production_type === 'type1' ? 'border-primary' : 'opacity-50' }}">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <i
                                                                class="fas fa-industry fa-2x {{ $order->production_type === 'type1' ? 'text-primary' : 'text-muted' }}"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Type 1: Production Directe</h6>
                                                            <p class="text-muted mb-0 small">
                                                                Matières premières → Produit de production
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Type 2: Production -> Découpage -->
                                        <div class="col-md-4 mb-3">
                                            <div
                                                class="card h-100 {{ $order->production_type === 'type2' ? 'border-warning' : 'opacity-50' }}">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <i
                                                                class="fas fa-cut fa-2x {{ $order->production_type === 'type2' ? 'text-warning' : 'text-muted' }}"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Type 2: Découpage</h6>
                                                            <p class="text-muted mb-0 small">
                                                                Bloc production → Produit découpage
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Type 3: Découpage -> Vente -->
                                        <div class="col-md-4 mb-3">
                                            <div
                                                class="card h-100 {{ $order->production_type === 'type3' ? 'border-success' : 'opacity-50' }}">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <i
                                                                class="fas fa-exchange-alt fa-2x {{ $order->production_type === 'type3' ? 'text-success' : 'text-muted' }}"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Type 3: Conversion</h6>
                                                            <p class="text-muted mb-0 small">
                                                                Sous-blocs → Produit de vente (Multiple)
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="production_type" value="{{ $order->production_type }}">
                                </div>
                            </div>

                            <!-- Product Selection Section -->
                            <div class="row mb-4">
                                @if ($order->production_type === 'type1')
                                    <!-- Type 1: Production Directe -->
                                    <div class="col-md-12" id="type1Section">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="type1_product_id" class="form-label">Produit à Produire
                                                        *</label>
                                                    <select class="form-control select2" id="type1_product_id"
                                                        name="type1_product_id" disabled>
                                                        <option value="">Sélectionner un produit de production
                                                        </option>
                                                        @foreach ($productionProducts as $product)
                                                            @if ($product->product_type === 'production' || $product->product_type === 'both')
                                                                <option value="{{ $product->product_id }}"
                                                                    {{ $order->product_id == $product->product_id ? 'selected' : '' }}
                                                                    data-has-familles="{{ $product->has_familles }}">
                                                                    {{ $product->product_code }} -
                                                                    {{ $product->product_name }}
                                                                    @if ($product->product_type === 'production')
                                                                        <span class="badge bg-primary">Production</span>
                                                                    @elseif($product->product_type === 'both')
                                                                        <span class="badge bg-info">Production &
                                                                            Vente</span>
                                                                    @endif
                                                                    @if ($product->has_familles)
                                                                        <span class="badge bg-warning">Avec Familles</span>
                                                                    @endif
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <small class="form-text text-muted">
                                                        Produit de type production à fabriquer (Non modifiable)
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="type1_quantity" class="form-label">Quantité à Produire
                                                        *</label>
                                                    <input type="number" class="form-control" id="type1_quantity"
                                                        name="type1_quantity" min="1" step="1"
                                                        value="{{ $order->quantity_to_produce }}">
                                                    <small class="form-text text-muted">
                                                        Quantité de produit de production à fabriquer
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="form-label">Volume/Unité</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control"
                                                            id="type1_volume_per_unit" step="0.0001" min="0"
                                                            readonly value="{{ $order->product->volume_m3 ?? 0 }}">
                                                        <span class="input-group-text">m³</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($order->production_type === 'type2')
                                    <!-- Type 2: Production -> Découpage -->
                                    <div class="col-md-12" id="type2Section">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="type2_source_product_id" class="form-label">Produit Source
                                                        (Bloc) *</label>
                                                    <select class="form-control select2" id="type2_source_product_id"
                                                        name="type2_source_product_id" disabled>
                                                        <option value="">Sélectionner un produit source</option>
                                                        @foreach ($productionProducts as $product)
                                                            @if ($product->product_type === 'production' || $product->product_type === 'both')
                                                                <option value="{{ $product->product_id }}"
                                                                    {{ $order->source_product_id == $product->product_id ? 'selected' : '' }}
                                                                    data-has-familles="{{ $product->has_familles }}">
                                                                    {{ $product->product_code }} -
                                                                    {{ $product->product_name }}
                                                                    @if ($product->product_type === 'production')
                                                                        <span class="badge bg-primary">Production</span>
                                                                    @elseif($product->product_type === 'both')
                                                                        <span class="badge bg-info">Production &
                                                                            Vente</span>
                                                                    @endif
                                                                    @if ($product->has_familles)
                                                                        <span class="badge bg-warning">Avec Familles</span>
                                                                    @endif
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <small class="form-text text-muted">
                                                        Bloc de production à découper (Non modifiable)
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="type2_final_product_id" class="form-label">Produit
                                                        Découpage *</label>
                                                    <select class="form-control select2" id="type2_final_product_id"
                                                        name="type2_final_product_id" disabled>
                                                        <option value="">Sélectionner un produit découpage</option>
                                                        @foreach ($decoupageProducts as $product)
                                                            @if ($product->product_type === 'decoupage')
                                                                <option value="{{ $product->product_id }}"
                                                                    {{ $order->product_id == $product->product_id ? 'selected' : '' }}
                                                                    data-has-familles="{{ $product->has_familles }}">
                                                                    {{ $product->product_code }} -
                                                                    {{ $product->product_name }}
                                                                    <span class="badge bg-warning">Découpage</span>
                                                                    @if ($product->has_familles)
                                                                        <span class="badge bg-info">Avec Familles</span>
                                                                    @endif
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <small class="form-text text-muted">
                                                        Produit de type découpage à produire (Non modifiable)
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="type2_quantity" class="form-label">Quantité de Blocs
                                                        *</label>
                                                    <input type="number" class="form-control" id="type2_quantity"
                                                        name="type2_quantity" min="1" step="1"
                                                        value="{{ $order->quantity_to_produce }}">
                                                    <small class="form-text text-muted">
                                                        Quantité de blocs à découper
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="decoupage_ratio" class="form-label">Ratio de Découpage
                                                        *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">1 bloc =</span>
                                                        <input type="number" class="form-control" id="decoupage_ratio"
                                                            name="decoupage_ratio" step="1" min="1"
                                                            value="{{ $order->decoupage_ratio ?? 1 }}">
                                                        <span class="input-group-text">sous bloc</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="waste_percentage" class="form-label">Pourcentage de
                                                        Déchet</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="waste_percentage"
                                                            name="waste_percentage" step="0.01" min="0"
                                                            max="100" value="{{ $order->waste_percentage ?? 0 }}">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($order->production_type === 'type3')
                                    <!-- Type 3: Découpage -> Vente (Multiple Products) -->
                                    <div class="col-md-12" id="type3Section">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label for="type3_source_product_id" class="form-label">Produit Source
                                                        (Sous-bloc) *</label>
                                                    <select class="form-control select2" id="type3_source_product_id"
                                                        name="type3_source_product_id" disabled>
                                                        <option value="">Sélectionner un sous-bloc</option>
                                                        @foreach ($decoupageProducts as $product)
                                                            @if ($product->product_type === 'decoupage')
                                                                <option value="{{ $product->product_id }}"
                                                                    {{ $order->source_product_id == $product->product_id ? 'selected' : '' }}
                                                                    data-has-familles="{{ $product->has_familles }}">
                                                                    {{ $product->product_code }} -
                                                                    {{ $product->product_name }}
                                                                    <span class="badge bg-warning">Découpage</span>
                                                                    @if ($product->has_familles)
                                                                        <span class="badge bg-info">Avec Familles</span>
                                                                    @endif
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <small class="form-text text-muted">
                                                        Sous-bloc de type découpage à convertir (Non modifiable)
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Products List (Read-only for editing) -->
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <h6>Produits Finaux à Produire</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Produit</th>
                                                                <th>Ratio de Conversion</th>
                                                                <th>Quantité à Produire</th>
                                                                <th>Volume/Unité (m³)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($type3Products as $product)
                                                                <tr>
                                                                    <td>{{ $product->product_name }}
                                                                        ({{ $product->product_code }})
                                                                    </td>
                                                                    <td>{{ $product->conversion_rate }}</td>
                                                                    <td>{{ $product->quantity_to_produce }}</td>
                                                                    <td>{{ $product->volume_per_unit ?? '0.0000' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <small class="text-muted">Les produits ne peuvent pas être modifiés après
                                                    création</small>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="waste_percentage" class="form-label">Pourcentage de
                                                        Déchet</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="waste_percentage"
                                                            name="waste_percentage" step="0.01" min="0"
                                                            max="100" value="{{ $order->waste_percentage ?? 0 }}">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Famille Selection -->
                            @if ($order->production_type === 'type1' && $order->product->has_familles)
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="famille_id" class="form-label">Famille de Destination</label>
                                            <select class="form-control select2" id="famille_id" name="famille_id">
                                                <option value="">Sélectionner une famille</option>
                                                @foreach ($order->product->familles as $famille)
                                                    <option value="{{ $famille->famille_id }}"
                                                        {{ $order->famille_id == $famille->famille_id ? 'selected' : '' }}>
                                                        {{ $famille->famille_name }} ({{ $famille->famille_code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Production Details -->
                            <div class="row mb-4" id="productionDetailsSection">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="priority" class="form-label">Priorité *</label>
                                        <select class="form-control" id="priority" name="priority" required>
                                            <option value="low" {{ $order->priority == 'low' ? 'selected' : '' }}>Basse
                                            </option>
                                            <option value="medium" {{ $order->priority == 'medium' ? 'selected' : '' }}>
                                                Moyenne</option>
                                            <option value="high" {{ $order->priority == 'high' ? 'selected' : '' }}>
                                                Haute</option>
                                            <option value="urgent" {{ $order->priority == 'urgent' ? 'selected' : '' }}>
                                                Urgente</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="start_date" class="form-label">Date de Début *</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date"
                                            required
                                            value="{{ $order->start_date ? $order->start_date->format('Y-m-d') : date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="expected_completion_date" class="form-label">Date de Fin Prévue
                                            *</label>
                                        <input type="date" class="form-control" id="expected_completion_date"
                                            name="expected_completion_date" required
                                            value="{{ $order->expected_completion_date ? $order->expected_completion_date->format('Y-m-d') : '' }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="form-group mb-4">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note me-2"></i>Notes
                                </label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"
                                    placeholder="Instructions spéciales, notes de production...">{{ $order->notes }}</textarea>
                            </div>

                            <!-- Hidden fields -->
                            <input type="hidden" name="product_id" value="{{ $order->product_id }}">
                            <input type="hidden" name="source_product_id" value="{{ $order->source_product_id }}">
                            <input type="hidden" name="quantity_to_produce" value="{{ $order->quantity_to_produce }}">

                            <!-- Form Actions -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> Mettre à jour
                                </button>
                                <button type="button" class="btn btn-warning" id="cancelProductionBtn">
                                    <i class="fas fa-ban me-1"></i> Annuler la Production
                                </button>
                                <a href="{{ route('production-orders.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Retour
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Production Modal -->
    <div class="modal fade" id="cancelProductionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-ban me-2"></i>Annuler la Production
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Veuillez confirmer l'annulation de la production :</p>
                    <div class="alert alert-warning">
                        <strong>{{ $order->order_number }}</strong>
                    </div>

                    <div class="mb-3">
                        <label for="cancellationReason" class="form-label">
                            <i class="fas fa-comment-dots me-2"></i>Raison de l'annulation *
                        </label>
                        <select class="form-control" id="cancellationReason" required>
                            <option value="">Sélectionner une raison</option>
                            <option value="stock_insufficient">Stock insuffisant</option>
                            <option value="customer_cancelled">Commande client annulée</option>
                            <option value="technical_issue">Problème technique</option>
                            <option value="schedule_conflict">Conflit d'horaire</option>
                            <option value="quality_concerns">Problèmes de qualité</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="additionalNotes" class="form-label">
                            <i class="fas fa-sticky-note me-2"></i>Notes supplémentaires
                        </label>
                        <textarea class="form-control" id="additionalNotes" rows="3"
                            placeholder="Ajouter des détails sur l'annulation..."></textarea>
                    </div>

                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention :</strong> Cette action marquera la commande comme annulée.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Fermer
                    </button>
                    <button type="button" class="btn btn-warning" id="confirmCancelProduction">
                        <i class="fas fa-ban me-2"></i>Annuler la Production
                    </button>
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
        .select2-container--disabled .select2-selection--single {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .opacity-50 {
            opacity: 0.5;
        }

        .form-control:disabled {
            background-color: #e9ecef;
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

            // Disable product selection fields
            $('#type1_product_id, #type2_source_product_id, #type2_final_product_id, #type3_source_product_id')
                .prop('disabled', true).trigger('change.select2');

            // Cancel production button
            $('#cancelProductionBtn').click(function() {
                $('#cancelProductionModal').modal('show');
            });

            // Confirm cancel production
            $('#confirmCancelProduction').click(function() {
                const reason = $('#cancellationReason').val();
                const notes = $('#additionalNotes').val();

                if (!reason) {
                    showToast('error', 'Veuillez sélectionner une raison d\'annulation');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span> Annulation...'
                );

                $.ajax({
                    url: "{{ route('production-orders.cancel-production', $order->order_id) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        reason: reason,
                        additional_notes: notes
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#cancelProductionModal').modal('hide');
                            showToast('success', response.message);
                            setTimeout(() => {
                                window.location.href =
                                    "{{ route('production-orders.index') }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors de l\'annulation');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-ban me-2"></i>Annuler la Production'
                        );
                    }
                });
            });

            // Update order form submission
            $('#editProductionOrderForm').submit(function(e) {
                e.preventDefault();

                const formData = $(this).serialize();
                const submitBtn = $('#submitBtn');

                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Mise à jour...'
                );

                // Validate dates
                const startDate = new Date($('#start_date').val());
                const endDate = new Date($('#expected_completion_date').val());

                if (endDate < startDate) {
                    showToast('error', 'La date de fin ne peut pas être antérieure à la date de début');
                    submitBtn.prop('disabled', false).html(
                        '<i class="fas fa-save me-1"></i> Mettre à jour'
                    );
                    return;
                }

                $.ajax({
                    url: "{{ route('production-orders.update', $order->order_id) }}",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(() => {
                                window.location.href =
                                    "{{ route('production-orders.show', $order->order_id) }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(
                                '<i class="fas fa-save me-1"></i> Mettre à jour'
                            );
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        let errorMessage = '';

                        if (errors) {
                            Object.values(errors).forEach(function(errorArray) {
                                errorArray.forEach(function(error) {
                                    errorMessage += error + '\n';
                                });
                            });
                        } else {
                            errorMessage = xhr.responseJSON?.message ||
                                'Une erreur est survenue lors de la mise à jour';
                        }

                        showToast('error', errorMessage);
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i> Mettre à jour'
                        );
                    }
                });
            });

            // Toast notification function
            function showToast(type, message) {
                const toast = $(`
                    <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
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
