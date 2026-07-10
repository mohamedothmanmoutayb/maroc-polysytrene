@extends('layouts.app')

@section('title', 'Avoir - ' . $creditNote->credit_note_number)

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails de l'Avoir</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('credit-notes.index') }}">
                                        Avoirs
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ $creditNote->credit_note_number }}
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
                    <div class="card-body">
                        <!-- Status Badge -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div
                                    class="alert alert-{{ $creditNote->status === 'processed' ? 'success' : ($creditNote->status === 'rejected' ? 'danger' : ($creditNote->status === 'pending' ? 'warning' : 'info')) }}">
                                    <strong>Statut:</strong> {{ $creditNote->status_label }}
                                    @if ($creditNote->status === 'pending')
                                        <span class="float-end">En attente d'approbation</span>
                                    @elseif($creditNote->status === 'approved')
                                        <span class="float-end">Approuvé le
                                            {{ $creditNote->approved_at ? $creditNote->approved_at->format('d/m/Y H:i') : '' }}
                                            par {{ $creditNote->approver->name ?? '' }}</span>
                                    @elseif($creditNote->status === 'processed')
                                        <span class="float-end">Traité le
                                            {{ $creditNote->updated_at->format('d/m/Y H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Info Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">N° Avoir</h6>
                                        <h4>{{ $creditNote->credit_note_number }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Date</h6>
                                        <h4>{{ $creditNote->credit_note_date->format('d/m/Y') }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Client</h6>
                                        <h4>{{ $creditNote->client->display_name }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Montant Total</h6>
                                        <h4>{{ number_format($creditNote->total_amount, 2, ',', '.') }} DH</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="table-responsive mb-4">
                            <h5 class="mb-3">Articles retournés</h5>
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Article</th>
                                        <th class="text-right">Quantité</th>
                                        <th class="text-right">Prix Unitaire</th>
                                        <th class="text-right">Total</th>
                                        <th>Motif</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($creditNote->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                {{ $item->item_name }}
                                                <br><small class="text-muted">{{ $item->type_label }}</small>
                                            </td>
                                            <td class="text-right">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format($item->unit_price, 2, ',', '.') }} DH</td>
                                            <td class="text-right">{{ number_format($item->total_price, 2, ',', '.') }} DH</td>
                                            <td>{{ $item->reason ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                        <td class="text-right"><strong>{{ number_format($creditNote->total_amount, 2, ',', '.') }}
                                                DH</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Additional Info -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Raison principale</h6>
                                    </div>
                                    <div class="card-body">
                                        {{ $creditNote->reason ?: 'Non spécifiée' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Notes</h6>
                                    </div>
                                    <div class="card-body">
                                        {{ $creditNote->notes ?: 'Aucune note' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($creditNote->salesOrder)
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Commande associée</h6>
                                        </div>
                                        <div class="card-body">
                                            <a href="{{ route('sales.orders.show', $creditNote->salesOrder->order_id) }}">
                                                {{ $creditNote->salesOrder->order_number }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="d-flex gap-2 mt-4">
                            <a href="{{ route('credit-notes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>

                            @if ($creditNote->status === 'draft')
                                @can('edit_credit_notes')
                                <a href="{{ route('credit-notes.edit', $creditNote->credit_note_id) }}"
                                    class="btn btn-warning">
                                    <i class="fas fa-edit me-1"></i> Modifier
                                </a>
                                @endcan
                            @endif

                            @if ($creditNote->status === 'pending')
                                @can('edit_credit_notes')
                                <button type="button" class="btn btn-success"
                                    onclick="approveCreditNote({{ $creditNote->credit_note_id }})">
                                    <i class="fas fa-check-circle me-1"></i> Approuver
                                </button>
                                <button type="button" class="btn btn-danger"
                                    onclick="rejectCreditNote({{ $creditNote->credit_note_id }})">
                                    <i class="fas fa-times-circle me-1"></i> Rejeter
                                </button>
                                @endcan
                            @endif

                            @if ($creditNote->status === 'approved')
                                @can('edit_credit_notes')
                                <button type="button" class="btn btn-primary"
                                    onclick="processCreditNote({{ $creditNote->credit_note_id }})">
                                    <i class="fas fa-check-double me-1"></i> Traiter
                                </button>
                                @endcan
                            @endif

                            @if (in_array($creditNote->status, ['approved', 'processed']))
                                <a href="{{ route('credit-notes.pdf', $creditNote->credit_note_id) }}"
                                    class="btn btn-danger" target="_blank">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
@endsection

@push('scripts')
    <script>
        function showToast(type, message) {
            var toast = $('<div class="toast align-items-center text-white bg-' +
                (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger')) +
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

        function approveCreditNote(id) {
            if (confirm('Approuver cet avoir ?')) {
                $.ajax({
                    url: "{{ url('credit-notes') }}/" + id + "/approve",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT'
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message || 'Erreur lors de l\'approbation');
                    }
                });
            }
        }

        function rejectCreditNote(id) {
            if (confirm('Rejeter cet avoir ?')) {
                $.ajax({
                    url: "{{ url('credit-notes') }}/" + id + "/reject",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT'
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message || 'Erreur lors du rejet');
                    }
                });
            }
        }

        function processCreditNote(id) {
            if (confirm('Traiter cet avoir ? Cela mettra à jour le stock et créditera le compte client.')) {
                $.ajax({
                    url: "{{ url('credit-notes') }}/" + id + "/process",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT'
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message || 'Erreur lors du traitement');
                    }
                });
            }
        }
    </script>
@endpush
