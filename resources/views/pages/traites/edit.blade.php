@extends('layouts.app')

@section('title', 'Modifier Traite')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Traite</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('traites.index') }}">
                                        Traites
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
                            <i class="fas fa-edit me-2"></i>Modifier la Traite: {{ $traite->traite_number }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($traite->status === 'paid')
                            <div class="alert alert-warning mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Attention:</strong> Cette traite est déjà payée. La modification du statut ou du
                                montant peut affecter le paiement associé et le solde client.
                            </div>
                        @endif

                        <form id="traiteForm" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="traite_number" class="form-label">Numéro Traite *</label>
                                        <input type="text" class="form-control" id="traite_number" name="traite_number"
                                            value="{{ $traite->traite_number }}" required maxlength="50">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount" class="form-label">Montant * (DH)</label>
                                        <input type="number" class="form-control" id="amount" name="amount" required
                                            min="0.01" step="0.01"
                                            value="{{ number_format($traite->amount, 2, '.', '') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="client_id" class="form-label">Client</label>
                                        <select class="form-control select2" id="client_id" name="client_id">
                                            <option value="">-- Sélectionner un client --</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->client_id }}"
                                                    data-balance="{{ $client->balance }}"
                                                    data-balance-status="{{ $client->balance > 0 ? 'credit' : ($client->balance < 0 ? 'debt' : 'zero') }}"
                                                    {{ $traite->client_id == $client->client_id ? 'selected' : '' }}>
                                                    {{ $client->display_name }} ({{ $client->client_type ?? 'N/A' }})
                                                    @if ($client->balance != 0)
                                                        - @if ($client->balance > 0)
                                                            Crédit: {{ number_format($client->balance, 2, ',', '.') }} DH
                                                        @else
                                                            Dette: {{ number_format(abs($client->balance), 2, ',', '.') }} DH
                                                        @endif
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Auto-rempli si vente sélectionnée</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="issue_date" class="form-label">Date d'Émission *</label>
                                        <input type="date" class="form-control" id="issue_date" name="issue_date"
                                            value="{{ $traite->issue_date ? $traite->issue_date->format('Y-m-d') : '' }}"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="due_date" class="form-label">Date d'Échéance *</label>
                                        <input type="date" class="form-control" id="due_date" name="due_date"
                                            value="{{ $traite->due_date ? $traite->due_date->format('Y-m-d') : '' }}"
                                            required>
                                        <small class="form-text text-muted">Date à laquelle la traite doit être
                                            payée</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bank_name" class="form-label">Banque</label>
                                        <input type="text" class="form-control" id="bank_name" name="bank_name"
                                            maxlength="100" value="{{ $traite->bank_name }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="drawee" class="form-label">Tiré (Nom)</label>
                                        <input type="text" class="form-control" id="drawee" name="drawee"
                                            maxlength="200" value="{{ $traite->drawee }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="drawee_address" class="form-label">Adresse du Tiré</label>
                                        <input type="text" class="form-control" id="drawee_address"
                                            name="drawee_address" value="{{ $traite->drawee_address }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status" class="form-label">Statut *</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="pending" {{ $traite->status == 'pending' ? 'selected' : '' }}>
                                                En attente</option>
                                            <option value="paid" {{ $traite->status == 'paid' ? 'selected' : '' }}>Payé
                                            </option>
                                            <option value="overdue" {{ $traite->status == 'overdue' ? 'selected' : '' }}>
                                                En retard</option>
                                            <option value="bounced" {{ $traite->status == 'bounced' ? 'selected' : '' }}>
                                                Rebondi</option>
                                        </select>
                                        <small class="form-text text-muted">Changer le statut affectera le paiement associé
                                            et le solde client</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="document" class="form-label">Document (Image/PDF)</label>
                                        <input type="file" class="form-control" id="document" name="document"
                                            accept="image/*,application/pdf">
                                        <small class="form-text text-muted">Formats: jpeg, png, jpg, gif, pdf (max:
                                            2MB)</small>

                                        @if ($traite->document_path)
                                            <div class="mt-2">
                                                @php
                                                    $extension = pathinfo($traite->document_path, PATHINFO_EXTENSION);
                                                    $isImage = in_array(strtolower($extension), [
                                                        'jpg',
                                                        'jpeg',
                                                        'png',
                                                        'gif',
                                                        'webp',
                                                    ]);
                                                @endphp
                                                @if ($isImage)
                                                    <img src="{{ asset('storage/' . $traite->document_path) }}"
                                                        alt="Document actuel" style="max-height: 80px; max-width: 120px;"
                                                        class="img-thumbnail">
                                                    <br>
                                                    <a href="{{ asset('storage/' . $traite->document_path) }}"
                                                        target="_blank" class="btn btn-sm btn-link">
                                                        <i class="fas fa-eye"></i> Voir l'image
                                                    </a>
                                                @else
                                                    <i class="fas fa-file-pdf text-danger"></i>
                                                    <span>{{ $traite->original_filename ?? 'Document PDF' }}</span>
                                                    <br>
                                                    <a href="{{ asset('storage/' . $traite->document_path) }}"
                                                        target="_blank" class="btn btn-sm btn-link">
                                                        <i class="fas fa-eye"></i> Voir le PDF
                                                    </a>
                                                @endif
                                                <small class="text-muted d-block">Document actuel. Téléchargez un nouveau
                                                    document pour le remplacer.</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ $traite->notes }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Information Alerts -->
                            <div class="alert alert-info mb-4" id="orderInfoAlert"
                                style="display: {{ $traite->order_id ? 'block' : 'none' }};">
                                <i class="fas fa-shopping-cart me-2"></i>
                                <strong>Information Vente:</strong>
                                <ul class="mb-0 mt-2" id="orderInfoList">
                                    <li>Vente sélectionnée: <span
                                            id="selectedOrderNumber">{{ $traite->order ? $traite->order->order_number : '-' }}</span>
                                    </li>
                                    <li>Montant total: <span
                                            id="selectedOrderTotal">{{ $traite->order ? number_format($traite->order->final_amount, 2, ',', '.') : '-' }}
                                            DH</span></li>
                                    <li>Reste à payer: <span
                                            id="selectedOrderRemaining">{{ $traite->order ? number_format($traite->order->remaining_amount, 2, ',', '.') : '-' }}
                                            DH</span></li>
                                    <li>Client: <span
                                            id="selectedOrderClient">{{ $traite->order && $traite->order->client ? $traite->order->client->display_name : '-' }}</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="alert alert-info mb-4" id="clientInfoAlert"
                                style="display: {{ $traite->client_id ? 'block' : 'none' }};">
                                <i class="fas fa-user me-2"></i>
                                <strong>Information Client:</strong>
                                <ul class="mb-0 mt-2" id="clientInfoList">
                                    <li>Client: <span
                                            id="selectedClientName">{{ $traite->client ? $traite->client->display_name : '-' }}</span>
                                    </li>
                                    <li>Solde actuel: <span
                                            id="selectedClientBalance">{{ $traite->client ? number_format(abs($traite->client->balance), 2, ',', '.') : '-' }}
                                            DH</span></li>
                                    <li>Situation: <span id="selectedClientStatus">
                                            @if ($traite->client)
                                                @if ($traite->client->balance > 0)
                                                    <span class="text-success">Crédit client (Nous devons)</span>
                                                @elseif($traite->client->balance < 0)
                                                    <span class="text-danger">Dette client (Client doit)</span>
                                                @else
                                                    <span class="text-secondary">Solde nul</span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </span></li>
                                </ul>
                            </div>

                            <div class="alert alert-warning mb-4" id="excessWarning" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Attention:</strong> Le montant de la traite (<span id="warningAmount"></span> DH)
                                dépasse le reste à payer de la vente (<span id="warningRemaining"></span> DH).
                                L'excédent (<span id="warningExcess"></span> DH) sera ajouté au crédit client.
                            </div>

                            <div class="alert alert-success mb-4" id="creditInfo" style="display: none;">
                                <i class="fas fa-coins me-2"></i>
                                <strong>Information:</strong> Cette traite n'est liée à aucune vente.
                                Le montant total (<span id="creditAmount"></span> DH) sera ajouté au crédit client.
                            </div>

                            @if ($traite->payment_id)
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-receipt me-2"></i>
                                    <strong>Paiement associé:</strong> Un paiement (#{{ $traite->payment_id }}) a été créé
                                    pour cette traite.
                                    Toute modification peut affecter ce paiement.
                                </div>
                            @endif

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('traites.show', $traite->traite_id) }}" class="btn btn-info">
                                    <i class="fas fa-eye me-1"></i> Voir Détails
                                </a>
                                <a href="{{ route('traites.index') }}" class="btn btn-secondary">
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- Sélectionner --',
                allowClear: true
            });

            // Auto-fill client when order is selected
            $('#order_id').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var clientId = selectedOption.data('client-id');
                var clientName = selectedOption.data('client-name');
                var remainingAmount = selectedOption.data('remaining');
                var totalAmount = selectedOption.data('total');
                var orderNumber = selectedOption.val() ? selectedOption.text().split(' - ')[0] : '-';

                if (clientId) {
                    $('#client_id').val(clientId).trigger('change');
                    $('#selectedOrderNumber').text(orderNumber);
                    $('#selectedOrderTotal').text(formatNumber(totalAmount) + ' DH');
                    $('#selectedOrderRemaining').text(formatNumber(remainingAmount) + ' DH');
                    $('#selectedOrderClient').text(clientName);
                    $('#orderInfoAlert').show();
                } else {
                    $('#orderInfoAlert').hide();
                }

                checkExcessAmount();
            });

            // Show client info when client is selected
            $('#client_id').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var clientName = selectedOption.text().split(' - ')[0];
                var balance = selectedOption.data('balance');
                var balanceStatus = selectedOption.data('balance-status');

                if (selectedOption.val()) {
                    $('#selectedClientName').text(clientName);
                    $('#selectedClientBalance').text(formatNumber(Math.abs(balance)) + ' DH');

                    if (balance > 0) {
                        $('#selectedClientStatus').html(
                            '<span class="text-success">Crédit client (Nous devons)</span>');
                    } else if (balance < 0) {
                        $('#selectedClientStatus').html(
                            '<span class="text-danger">Dette client (Client doit)</span>');
                    } else {
                        $('#selectedClientStatus').html('<span class="text-secondary">Solde nul</span>');
                    }
                    $('#clientInfoAlert').show();
                } else {
                    $('#clientInfoAlert').hide();
                }
            });

            // Check amount and show warnings
            $('#amount').on('change keyup', function() {
                checkExcessAmount();
                checkCreditInfo();
            });

            function checkExcessAmount() {
                var amount = parseFloat($('#amount').val()) || 0;
                var selectedOption = $('#order_id').find('option:selected');
                var remainingAmount = selectedOption.data('remaining') || 0;
                var hasOrder = $('#order_id').val();

                if (hasOrder && amount > remainingAmount) {
                    var excess = amount - remainingAmount;
                    $('#warningAmount').text(formatNumber(amount));
                    $('#warningRemaining').text(formatNumber(remainingAmount));
                    $('#warningExcess').text(formatNumber(excess));
                    $('#excessWarning').show();
                } else {
                    $('#excessWarning').hide();
                }
            }

            function checkCreditInfo() {
                var hasOrder = $('#order_id').val();
                var amount = parseFloat($('#amount').val()) || 0;

                if (!hasOrder && amount > 0) {
                    $('#creditAmount').text(formatNumber(amount));
                    $('#creditInfo').show();
                } else {
                    $('#creditInfo').hide();
                }
            }

            // Validate document size
            $('#document').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const fileSize = file.size / 1024 / 1024;
                    if (fileSize > 2) {
                        showToast('error', 'La taille du document ne doit pas dépasser 2MB');
                        $(this).val('');
                    }
                }
            });

            // Form Submit
            $('#traiteForm').submit(function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                $.ajax({
                    url: "{{ route('traites.update', $traite->traite_id) }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('traites.show', $traite->traite_id) }}";
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

            function formatNumber(value) {
                return parseFloat(value).toLocaleString('de-DE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function showToast(type, message) {
                var bgColor = type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger');
                var toast = $('<div class="toast align-items-center text-white bg-' + bgColor +
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

            // Trigger initial checks
            checkExcessAmount();
            checkCreditInfo();
        });
    </script>
@endpush
