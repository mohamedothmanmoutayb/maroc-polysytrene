@extends('layouts.app')

@section('title', 'Détails Devis')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails du Devis</h4>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-info p-2">N° {{ $quotation->quote_number }}</span>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item d-flex align-items-center">
                                        <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                            <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a class="text-muted text-decoration-none"
                                            href="{{ route('sales.quotations.index') }}">
                                            Devis
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item" aria-current="page">
                                        <span class="badge fw-medium fs-2 bg-success text-success">
                                            Détails
                                        </span>
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Devis N° {{ $quotation->quote_number }}</h5>
                        <div class="d-flex gap-2">
                            @can('edit_sales_quotations')
                            <a href="{{ route('sales.quotations.edit', $quotation->quote_id) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            @endcan
                            <button type="button" class="btn btn-primary"
                                onclick="openPdfOptions({{ $quotation->quote_id }})">
                                <i class="fas fa-print me-1"></i> Imprimer
                            </button>
                            <a href="{{ route('sales.quotations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Status Badge -->
                        <div class="row mb-4">
                            <div class="col-12">
                                @php
                                    $statusLabels = [
                                        'draft' => ['label' => 'Brouillon', 'class' => 'bg-secondary'],
                                        'sent' => ['label' => 'Envoyé', 'class' => 'bg-primary'],
                                        'accepted' => ['label' => 'Accepté', 'class' => 'bg-success'],
                                        'rejected' => ['label' => 'Refusé', 'class' => 'bg-danger'],
                                        'expired' => ['label' => 'Expiré', 'class' => 'bg-warning'],
                                    ];
                                    $status = $statusLabels[$quotation->status] ?? [
                                        'label' => $quotation->status,
                                        'class' => 'bg-info',
                                    ];
                                @endphp
                                @can('edit_sales_quotations')
                                    <div class="dropdown d-inline-block">
                                        <button class="btn {{ $status['class'] }} text-white dropdown-toggle p-3 fs-6"
                                            type="button" id="statusDropdownBtn" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            Statut: {{ $status['label'] }}
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="statusDropdownBtn">
                                            @foreach ($statusLabels as $value => $info)
                                                <li>
                                                    <a class="dropdown-item change-status-btn {{ $quotation->status == $value ? 'active' : '' }}"
                                                        href="javascript:void(0)" data-status="{{ $value }}">
                                                        <span class="badge {{ $info['class'] }}">&nbsp;&nbsp;</span>
                                                        {{ $info['label'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <span class="badge {{ $status['class'] }} p-3 fs-6">Statut: {{ $status['label'] }}</span>
                                @endcan
                            </div>
                        </div>

                        <!-- Client Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">Informations Client</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <th width="40%">Nom:</th>
                                                <td>{{ $quotation->client->display_name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Téléphone:</th>
                                                <td>{{ $quotation->client->phone }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td>{{ $quotation->client->email ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Adresse:</th>
                                                <td>{{ $quotation->client->address ?? '-' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">Informations Devis</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <th width="40%">Date Devis:</th>
                                                <td>{{ $quotation->quote_date->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Valable jusqu'au:</th>
                                                <td>{{ $quotation->valid_until ? $quotation->valid_until->format('d/m/Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Créé par:</th>
                                                <td>{{ $quotation->creator->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Date création:</th>
                                                <td>{{ $quotation->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <h6 class="mt-4 mb-3">Articles du devis</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>Article</th>
                                        <th>Famille</th>
                                        <th class="text-center">Quantité</th>
                                        <th class="text-right">Prix Unitaire (DH)</th>
                                        <th class="text-right">Total (DH)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($quotation->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{!! $item->type_badge !!}</td>
                                            <td>{{ $item->item_name }}</td>
                                            <td>{{ $item->family_name ?? '-' }}</td>
                                            <td class="text-center">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format($item->total_price, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="6" class="text-end">Sous-total:</th>
                                        <th class="text-right">{{ number_format($quotation->total_amount, 2, ',', '.') }} DH</th>
                                    </tr>
                                    @if ($quotation->discount > 0)
                                        <tr>
                                            <th colspan="6" class="text-end">Remise:</th>
                                            <th class="text-right">- {{ number_format($quotation->discount, 2, ',', '.') }} DH</th>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th colspan="6" class="text-end">Total TTC:</th>
                                        <th class="text-right">{{ number_format($quotation->final_amount, 2, ',', '.') }} DH</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Notes and Terms -->
                        @if ($quotation->notes || $quotation->terms_conditions || $quotation->observation)
                            <div class="row mt-4">
                                @if ($quotation->notes)
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Notes</h6>
                                            </div>
                                            <div class="card-body">
                                                {{ $quotation->notes }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if ($quotation->terms_conditions)
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Conditions particulières</h6>
                                            </div>
                                            <div class="card-body">
                                                {{ $quotation->terms_conditions }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if ($quotation->observation)
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Observation</h6>
                                            </div>
                                            <div class="card-body">
                                                {{ $quotation->observation }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Amount in words -->
                        @if ($quotation->final_amount > 0)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <strong>Arrêté le présent devis à la somme de :</strong><br>
                                        {{ ucfirst($numberToFrench($quotation->final_amount)) }} DIRHAMS
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF Options Modal -->
    <div class="modal fade" id="pdfOptionsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Options du PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="pdfOptionsForm">
                        <div class="mb-3">
                            <label class="form-label">Afficher les prix</label>
                            <select id="showPrices" class="form-select">
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Afficher le logo</label>
                            <select id="showLogo" class="form-select">
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type d'affichage</label>
                            <select id="displayType" class="form-select">
                                <option value="unite">Unité</option>
                                <option value="volume">Volume</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="generatePdfBtn">
                        <i class="fas fa-print me-1"></i> Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('scripts')
    <script>
        $(document).on('click', '.change-status-btn', function() {
            const newStatus = $(this).data('status');

            $.ajax({
                url: "{{ route('sales.quotations.update-status', $quotation->quote_id) }}",
                type: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        setTimeout(function() {
                            window.location.reload();
                        }, 800);
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Erreur lors de la mise à jour du statut';
                    showToast('error', msg);
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
            }, 3000);
        }

        function openPdfOptions(quoteId) {
            $('#pdfOptionsModal').modal('show');
            $('#generatePdfBtn').off('click').on('click', function() {
                const showPrices = $('#showPrices').val();
                const showLogo = $('#showLogo').val();
                const displayType = $('#displayType').val();

                const url =
                    `/sales/quotations/${quoteId}/pdf?show_prices=${showPrices}&show_logo=${showLogo}&display_type=${displayType}`;

                const printWindow = window.open(url, '_blank', 'width=800,height=600');

                if (printWindow) {
                    printWindow.focus();

                    printWindow.onload = function() {
                        setTimeout(function() {
                            printWindow.print();
                            printWindow.onafterprint = function() {
                                printWindow.close();
                            };
                        }, 1000);
                    };
                }

                $('#pdfOptionsModal').modal('hide');
            });
        }
    </script>
@endpush
