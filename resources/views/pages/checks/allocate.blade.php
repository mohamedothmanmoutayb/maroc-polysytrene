@extends('layouts.app')

@section('title', 'Allouer Chèque')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Allouer Chèque</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('checks.index') }}">
                                        Chèques
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('checks.show', $check->check_id) }}">
                                        Chèque: {{ $check->check_number }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Allouer
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
                            <i class="fas fa-hand-holding-usd me-2"></i>Allouer le Chèque: {{ $check->check_number }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Check Information -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Informations du Chèque</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>Numéro:</strong> {{ $check->check_number }}</p>
                                        <p><strong>Banque:</strong> {{ $check->bank_name }}</p>
                                        <p><strong>Tireur:</strong> {{ $check->account_holder }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Montant Total:</strong> {{ number_format($check->amount, 2, ',', '.') }} DH</p>
                                        <p><strong>Montant Alloué:</strong>
                                            {{ number_format($check->amount - $check->available_amount, 2, ',', '.') }} DH</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Montant Disponible:</strong>
                                            <span class="badge bg-success">{{ number_format($check->available_amount, 2, ',', '.') }}
                                                DH</span>
                                        </p>
                                        <p><strong>Statut:</strong>
                                            @php
                                                $badges = [
                                                    'pending' => 'warning',
                                                    'deposited' => 'info',
                                                    'cleared' => 'success',
                                                    'bounced' => 'danger',
                                                    'cancelled' => 'secondary',
                                                ];
                                                $labels = [
                                                    'pending' => 'En attente',
                                                    'deposited' => 'Déposé',
                                                    'cleared' => 'Encaissé',
                                                    'bounced' => 'Rebondi',
                                                    'cancelled' => 'Annulé',
                                                ];
                                                $color = $badges[$check->status] ?? 'secondary';
                                                $label = $labels[$check->status] ?? $check->status;
                                            @endphp
                                            <span class="badge badge-{{ $color }}">{{ $label }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Allocation Form -->
                        <form id="allocationForm">
                            @csrf

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="purchase_id" class="form-label">Achat à Payer *</label>
                                        <select class="form-control select2" id="purchase_id" name="purchase_id" required>
                                            <option value="">Sélectionner un achat...</option>
                                            @foreach ($purchases as $purchase)
                                                @php
                                                    $allocated = $purchase->checkAllocations->sum('allocated_amount');
                                                    $balance = $purchase->final_amount - $allocated;
                                                @endphp
                                                <option value="{{ $purchase->purchase_id }}"
                                                    data-balance="{{ $balance }}"
                                                    data-supplier="{{ $purchase->supplier->display_name }}"
                                                    data-total="{{ $purchase->final_amount }}"
                                                    data-allocated="{{ $allocated }}">
                                                    {{ $purchase->purchase_number }} -
                                                    {{ $purchase->supplier->display_name }} -
                                                    Total: {{ number_format($purchase->final_amount, 2, ',', '.') }} DH -
                                                    Solde: {{ number_format($balance, 2, ',', '.') }} DH
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="allocated_amount" class="form-label">Montant à Allouer * (DH)</label>
                                        <input type="number" class="form-control" id="allocated_amount"
                                            name="allocated_amount" required min="0.01" step="0.01"
                                            placeholder="0.00">
                                        <small class="form-text text-muted">
                                            Maximum: <span
                                                id="max-allocation">{{ number_format($check->available_amount, 2, ',', '.') }}</span>
                                            DH
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Purchase Information -->
                            <div class="card mb-4" id="purchase-info" style="display: none;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Informations de l'Achat</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Fournisseur:</strong> <span id="purchase-supplier"></span></p>
                                            <p><strong>Montant Total:</strong> <span id="purchase-total">0.00</span> DH</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Montant Alloué:</strong> <span id="purchase-allocated">0.00</span> DH
                                            </p>
                                            <p><strong>Solde Restant:</strong> <span id="purchase-balance">0.00</span> DH
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Notes sur l'allocation..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer l'Allocation
                                </button>
                                <a href="{{ route('checks.show', $check->check_id) }}" class="btn btn-secondary">
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

            // Handle purchase selection
            $('#purchase_id').change(function() {
                const selectedOption = $(this).find('option:selected');
                const balance = parseFloat(selectedOption.data('balance')) || 0;
                const supplier = selectedOption.data('supplier') || '';
                const total = parseFloat(selectedOption.data('total')) || 0;
                const allocated = parseFloat(selectedOption.data('allocated')) || 0;

                if (selectedOption.val()) {
                    // Update purchase info
                    $('#purchase-supplier').text(supplier);
                    $('#purchase-total').text(total.toFixed(2));
                    $('#purchase-allocated').text(allocated.toFixed(2));
                    $('#purchase-balance').text(balance.toFixed(2));

                    // Show purchase info
                    $('#purchase-info').show();

                    // Set max allocation (minimum of check available and purchase balance)
                    const checkAvailable = parseFloat('{{ $check->available_amount }}') || 0;
                    const maxAllocation = Math.min(checkAvailable, balance);
                    $('#allocated_amount').attr('max', maxAllocation);
                    $('#max-allocation').text(maxAllocation.toFixed(2));
                } else {
                    $('#purchase-info').hide();
                }
            });

            // Validate allocation amount
            $('#allocated_amount').on('change', function() {
                const amount = parseFloat($(this).val()) || 0;
                const maxAmount = parseFloat($(this).attr('max')) || 0;
                const checkAvailable = parseFloat('{{ $check->available_amount }}') || 0;

                if (amount > checkAvailable) {
                    showToast('error', 'Le montant ne peut pas dépasser le montant disponible du chèque');
                    $(this).val(checkAvailable.toFixed(2));
                } else if (amount > maxAmount) {
                    showToast('error', 'Le montant ne peut pas dépasser le solde restant de l\'achat');
                    $(this).val(maxAmount.toFixed(2));
                }
            });

            // Allocation Form Submit
            $('#allocationForm').submit(function(e) {
                e.preventDefault();

                const purchaseId = $('#purchase_id').val();
                const amount = parseFloat($('#allocated_amount').val()) || 0;
                const maxAmount = parseFloat($('#allocated_amount').attr('max')) || 0;
                const checkAvailable = parseFloat('{{ $check->available_amount }}') || 0;

                // Validate purchase selection
                if (!purchaseId) {
                    showToast('error', 'Veuillez sélectionner un achat');
                    return;
                }

                // Validate amount
                if (amount <= 0) {
                    showToast('error', 'Veuillez entrer un montant valide');
                    return;
                }

                if (amount > checkAvailable) {
                    showToast('error', 'Le montant ne peut pas dépasser le montant disponible du chèque');
                    return;
                }

                if (amount > maxAmount) {
                    showToast('error', 'Le montant ne peut pas dépasser le solde restant de l\'achat');
                    return;
                }

                // Show loading
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                $.ajax({
                    url: "{{ route('checks.allocations.store', $check->check_id) }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('checks.show', $check->check_id) }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON?.errors;
                        var errorMessage = xhr.responseJSON?.message ||
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
                    (type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger') +
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
