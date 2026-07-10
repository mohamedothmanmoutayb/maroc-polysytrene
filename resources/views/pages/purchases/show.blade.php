@extends('layouts.app')

@section('title', 'Détails du Règlement')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails du Règlement</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}"><iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon></a></li>
                                <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Règlements</a></li>
                                <li class="breadcrumb-item active">#{{ str_pad($reglement->payment_id, 6, '0', STR_PAD_LEFT) }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Informations du Règlement</h5>
                        <div>
                            <button class="btn btn-sm btn-danger btn-delete-payment-detail me-1"
                                data-id="{{ $reglement->payment_id }}"
                                data-ref="#{{ str_pad($reglement->payment_id, 6, '0', STR_PAD_LEFT) }}">
                                <i class="fas fa-trash me-1"></i>Supprimer
                            </button>
                            <button class="btn btn-sm btn-warning btn-edit-payment-detail"
                                data-id="{{ $reglement->payment_id }}"
                                data-amount="{{ $reglement->amount }}"
                                data-method="{{ $reglement->payment_method }}"
                                data-date="{{ $reglement->payment_date ? $reglement->payment_date->format('Y-m-d') : now()->format('Y-m-d') }}"
                                data-notes="{{ e($reglement->notes ?? '') }}">
                                <i class="fas fa-edit me-1"></i>Modifier
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr><td width="40%"><strong>Référence:</strong></td><td>#{{ str_pad($reglement->payment_id, 6, '0', STR_PAD_LEFT) }}</td></tr>
                                    <tr><td><strong>Date de règlement:</strong></td><td>{{ $reglement->payment_date ? $reglement->payment_date->format('d/m/Y') : '-' }}</td></tr>
                                    <tr><td><strong>Mode de règlement:</strong></td><td><span class="badge badge-{{ $reglement->payment_method == 'cash' ? 'success' : ($reglement->payment_method == 'check' ? 'info' : ($reglement->payment_method == 'transfer' ? 'primary' : 'warning')) }}">{{ $reglement->method_label }}</span></td></tr>
                                    <tr><td><strong>Montant:</strong></td><td><h4 class="text-success mb-0">{{ number_format($reglement->amount, 2, ',', '.') }} DH</h4></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr><td width="40%"><strong>Client:</strong></td><td>
                                        @if ($client)<a href="{{ route('clients.show', $client->client_id) }}">{{ $client->display_name }}</a>@else-@endif
                                    </td></tr>
                                    <tr><td><strong>Commande liée:</strong></td><td>
                                        @if ($reglement->order)<a href="{{ route('sales.orders.show', $reglement->order->order_id) }}" class="text-primary">{{ $reglement->order->order_number }}</a>@else<span class="text-muted">Règlement direct client</span>@endif
                                    </td></tr>
                                    <tr><td><strong>Source:</strong></td><td>
                                        @if ($reglement->order)<span class="badge badge-info">Sur commande</span>
                                        @elseif($reglement->creditNote)<span class="badge badge-secondary">Sur avoir</span>
                                        @else<span class="badge badge-success">Règlement direct</span>@endif
                                    </td></tr>
                                </table>
                            </div>
                        </div>
                        @if ($reglement->notes)
                            <div class="row mt-3"><div class="col-12"><hr><strong>Notes:</strong><p class="mt-2">{{ nl2br(e($reglement->notes)) }}</p></div></div>
                        @endif
                    </div>
                </div>

                @if ($reglement->payment_method == 'check' && $check)
                    <div class="card mt-4">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-money-check me-2"></i>Détails du Chèque</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4"><strong>Numéro:</strong> {{ $check->check_number }}</div>
                                <div class="col-md-4"><strong>Banque:</strong> {{ $check->bank_name }}</div>
                                <div class="col-md-4"><strong>Titulaire:</strong> {{ $check->account_holder }}</div>
                                <div class="col-md-4 mt-2"><strong>Date d'émission:</strong> {{ \Carbon\Carbon::parse($check->issue_date)->format('d/m/Y') }}</div>
                                <div class="col-md-4 mt-2"><strong>Date d'échéance:</strong> {{ $check->due_date ? \Carbon\Carbon::parse($check->due_date)->format('d/m/Y') : '-' }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($reglement->payment_method == 'traite' && $traite)
                    <div class="card mt-4">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-file-invoice me-2"></i>Détails de la Traite</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4"><strong>Numéro:</strong> {{ $traite->traite_number }}</div>
                                <div class="col-md-4"><strong>Tiré:</strong> {{ $traite->drawee }}</div>
                                <div class="col-md-4"><strong>Banque:</strong> {{ $traite->bank_name ?? '-' }}</div>
                                <div class="col-md-4 mt-2"><strong>Date d'émission:</strong> {{ \Carbon\Carbon::parse($traite->issue_date)->format('d/m/Y') }}</div>
                                <div class="col-md-4 mt-2"><strong>Date d'échéance:</strong> {{ \Carbon\Carbon::parse($traite->due_date)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($reglement->order && $reglement->order->items && $reglement->order->items->count() > 0)
                    <div class="card mt-4">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-boxes me-2"></i>Produits commandés</h5></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead><tr><th>Produit</th><th>Quantité</th><th>Prix unitaire</th><th>Total</th></tr></thead>
                                    <tbody>
                                        @foreach ($reglement->order->items as $item)
                                            <tr><td>{{ $item->item_name }}</td><td>{{ number_format($item->quantity, 2, ',', '.') }}</td><td>{{ number_format($item->unit_price, 2, ',', '.') }} DH</td><td>{{ number_format($item->total_price, 2, ',', '.') }} DH</td></tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr><th colspan="3" class="text-end">Total commande:</th><th>{{ number_format($reglement->order->final_amount, 2, ',', '.') }} DH</th></tr>
                                        <tr><th colspan="3" class="text-end">Déjà réglé:</th><th>{{ number_format($reglement->order->paid_amount, 2, ',', '.') }} DH</th></tr>
                                        <tr><th colspan="3" class="text-end">Reste à régler:</th><th class="text-danger">{{ number_format($reglement->order->final_amount - $reglement->order->paid_amount, 2, ',', '.') }} DH</th></tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                @if ($reglement->document_path)
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-file-pdf me-2"></i>Document joint</h5></div>
                        <div class="card-body text-center">
                            <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                            <p>{{ $reglement->original_filename ?? 'document.pdf' }}</p>
                            <a href="{{ route('purchases.download', $reglement->payment_id) }}" class="btn btn-primary btn-sm" target="_blank"><i class="fas fa-download me-1"></i>Télécharger</a>
                        </div>
                    </div>
                @endif

                <div class="card mt-4">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-tools me-2"></i>Actions</h5></div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('purchases.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Retour à la liste</a>
                            @if ($client)
                                <button class="btn btn-primary" id="btnAddPaymentDetail"
                                    data-client-id="{{ $client->client_id }}"
                                    data-client-name="{{ $client->display_name }}">
                                    <i class="fas fa-money-bill-wave me-1"></i>Ajouter un paiement
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Payment Modal --}}
    <div class="modal fade" id="editPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Modifier le Règlement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editPaymentForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_payment_id" name="payment_id">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Montant (DH) *</label>
                                <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date *</label>
                                <input type="date" class="form-control" id="edit_date" name="payment_date" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Méthode *</label>
                                <select class="form-control" id="edit_method" name="payment_method" required>
                                    <option value="cash">Espèces</option>
                                    <option value="check">Chèque</option>
                                    <option value="transfer">Virement</option>
                                    <option value="traite">Traite</option>
                                    <option value="advance">Avance</option>
                                    <option value="avoir">Avoir</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Document</label>
                                <input type="file" class="form-control" id="edit_document" name="document" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deletePaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer le règlement <strong id="delete_ref"></strong> ?</p>
                    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Cette action va annuler le paiement et recalculer le solde.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn"><i class="fas fa-trash me-1"></i>Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Payment Modal (like client distribute-payment) --}}
    <div class="modal fade" id="addPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>Ajouter un paiement client</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addPaymentForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="ap_client_id" name="client_id">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong><i class="fas fa-user me-2"></i>Client:</strong>
                            <span id="ap_client_name"></span>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Montant (DH) *</label>
                                <input type="number" class="form-control" id="ap_amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date *</label>
                                <input type="date" class="form-control" id="ap_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Méthode *</label>
                                <select class="form-control" id="ap_method" name="payment_method" required>
                                    <option value="">Sélectionner</option>
                                    <option value="cash">Espèces</option>
                                    <option value="check">Chèque</option>
                                    <option value="transfer">Virement</option>
                                    <option value="traite">Traite</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Commande (optionnel)</label>
                                <select class="form-control" id="ap_order" name="target_order_id">
                                    <option value="">— Distribuer sur impayés —</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Document</label>
                            <input type="file" class="form-control" id="ap_document" name="document" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="ap_notes" name="notes" rows="2"></textarea>
                        </div>
                        <div class="alert alert-warning" id="ap_excess_warning" style="display:none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="toast-container" style="position:fixed;top:20px;right:20px;z-index:9999;"></div>
@endsection

@push('scripts')
<script>
function showToast(type, message) {
    var toast = $('<div class="toast align-items-center text-white bg-' + (type === 'success' ? 'success' : 'danger') + ' border-0" role="alert"><div class="d-flex"><div class="toast-body">' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>');
    $('#toast-container').append(toast);
    new bootstrap.Toast(toast[0]).show();
    setTimeout(function() { toast.remove(); }, 5000);
}

$(document).ready(function () {
    // ── Edit Payment (from detail page) ──────────────────────────────────
    $(document).on('click', '.btn-edit-payment-detail', function () {
        var $btn = $(this);
        $('#edit_payment_id').val($btn.data('id'));
        $('#edit_amount').val($btn.data('amount'));
        $('#edit_date').val($btn.data('date'));
        $('#edit_method').val($btn.data('method'));
        $('#edit_notes').val($btn.data('notes'));
        $('#edit_document').val('');
        $('#editPaymentModal').modal('show');
    });

    $('#editPaymentForm').submit(function (e) {
        e.preventDefault();
        var id = $('#edit_payment_id').val();
        var formData = new FormData(this);
        formData.append('_method', 'PUT');

        $.ajax({
            url: "{{ url('purchases') }}/" + id,
            type: 'POST', data: formData, processData: false, contentType: false,
            success: function (res) {
                if (res.success) {
                    $('#editPaymentModal').modal('hide');
                    showToast('success', res.message);
                    setTimeout(function() { location.reload(); }, 1000);
                } else { showToast('error', res.message); }
            },
            error: function (xhr) {
                showToast('error', xhr.responseJSON?.message || 'Erreur');
            }
        });
    });

    // ── Delete Payment (from detail page) ────────────────────────────────
    var deleteId = null;
    $(document).on('click', '.btn-delete-payment-detail', function () {
        deleteId = $(this).data('id');
        $('#delete_ref').text($(this).data('ref'));
        $('#deletePaymentModal').modal('show');
    });

    $('#confirmDeleteBtn').click(function () {
        if (!deleteId) return;
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Suppression...');

        $.ajax({
            url: "{{ url('purchases') }}/" + deleteId,
            type: 'POST',
            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
            success: function (res) {
                if (res.success) {
                    $('#deletePaymentModal').modal('hide');
                    showToast('success', res.message);
                    setTimeout(function() { window.location.href = "{{ route('purchases.index') }}"; }, 1000);
                } else { showToast('error', res.message); }
                $btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i>Supprimer');
            },
            error: function (xhr) {
                showToast('error', xhr.responseJSON?.message || 'Erreur');
                $btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i>Supprimer');
            }
        });
    });

    // ── Add Payment Modal ────────────────────────────────────────────────
    $('#btnAddPaymentDetail').click(function () {
        var clientId = $(this).data('client-id');
        var clientName = $(this).data('client-name');
        $('#ap_client_id').val(clientId);
        $('#ap_client_name').text(clientName);
        $('#ap_amount').val('');
        $('#ap_notes').val('');
        $('#ap_document').val('');
        $('#ap_excess_warning').hide();
        loadClientOrders(clientId);
        $('#addPaymentModal').modal('show');
    });

    function loadClientOrders(clientId) {
        var select = $('#ap_order');
        select.empty().append('<option value="">— Distribuer sur impayés —</option>');

        $.ajax({
            url: "{{ url('sales-orders') }}/client/" + clientId + "/unpaid",
            type: 'GET',
            data: { client_id: clientId, status: 'pending,partial' },
            success: function (res) {
                if (res && res.orders) {
                    res.orders.forEach(function (order) {
                        var remaining = parseFloat(order.unpaid_amount) || 0;
                        if (remaining > 0) {
                            select.append('<option value="' + order.order_id + '">' +
                                order.order_number + ' — ' +
                                'Reste: ' + remaining.toFixed(2) + ' DH</option>');
                        }
                    });
                }
            }
        });
    }

    $('#ap_method').change(function () {
        // No special handling needed for basic modal
    });

    // Preview excess warning when order selected and amount entered
    $('#ap_amount, #ap_order').on('input change', function () {
        var amount = parseFloat($('#ap_amount').val()) || 0;
        var selected = $('#ap_order option:selected');
        if (selected.val() && amount > 0) {
            var text = selected.text();
            var match = text.match(/Reste:\s*([\d.]+)\s*DH/);
            if (match) {
                var remaining = parseFloat(match[1]) || 0;
                if (amount > remaining) {
                    $('#ap_excess_warning').show().html('<i class="fas fa-info-circle me-1"></i>Montant supérieur au reste à payer (' +
                        remaining.toFixed(2) + ' DH). L\'excédent de ' + (amount - remaining).toFixed(2) +
                        ' DH sera ajouté au solde client.');
                } else {
                    $('#ap_excess_warning').hide();
                }
            }
        } else {
            $('#ap_excess_warning').hide();
        }
    });

    $('#addPaymentForm').submit(function (e) {
        e.preventDefault();
        var clientId = $('#ap_client_id').val();
        var formData = new FormData(this);

        $.ajax({
            url: "{{ route('purchases.add-payment') }}",
            type: 'POST', data: formData, processData: false, contentType: false,
            success: function (res) {
                if (res.success) {
                    $('#addPaymentModal').modal('hide');
                    showToast('success', res.message);
                    setTimeout(function() { location.reload(); }, 1000);
                } else { showToast('error', res.message); }
            },
            error: function (xhr) {
                showToast('error', xhr.responseJSON?.message || 'Erreur');
            }
        });
    });
});
</script>
@endpush
