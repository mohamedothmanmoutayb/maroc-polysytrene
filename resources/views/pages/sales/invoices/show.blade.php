@extends('layouts.app')

@section('title', 'Détails Facture')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails de la Facture</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('sales.invoices.index') }}">
                                        Factures
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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Facture N° {{ $invoice->invoice_number }}</h5>
                        <div class="d-flex gap-2">
                            @can('edit_sales_invoices')
                            <a href="{{ route('sales.invoices.edit', $invoice->invoice_id) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            @endcan
                            <button type="button" class="btn btn-primary"
                                onclick="openPdfOptions({{ $invoice->invoice_id }})">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                            <a href="{{ route('sales.invoices.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Status Badges -->
                        <div class="row mb-4">
                            <div class="col-12">
                                @php
                                    $statusLabels = [
                                        'draft' => ['label' => 'Brouillon', 'class' => 'bg-secondary'],
                                        'sent' => ['label' => 'Envoyé', 'class' => 'bg-primary'],
                                        'paid' => ['label' => 'Payé', 'class' => 'bg-success'],
                                        'cancelled' => ['label' => 'Annulé', 'class' => 'bg-danger'],
                                    ];
                                    $status = $statusLabels[$invoice->status] ?? [
                                        'label' => $invoice->status,
                                        'class' => 'bg-info',
                                    ];

                                @endphp
                                <span class="badge {{ $status['class'] }} p-3 fs-6">Statut:
                                    {{ $status['label'] }}</span>
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
                                                <td>{{ $invoice->client->display_name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Téléphone:</th>
                                                <td>{{ $invoice->client->phone }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td>{{ $invoice->client->email ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Adresse:</th>
                                                <td>{{ $invoice->client->address ?? '-' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">Informations Facture</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <th width="40%">Date Facture:</th>
                                                <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Créé par:</th>
                                                <td>{{ $invoice->creator->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Date création:</th>
                                                <td>{{ $invoice->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Summary -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Montant Payé</h6>
                                        <h3 class="mb-0">{{ number_format($invoice->paid_amount, 2, ',', '.') }} DH</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Reste à Payer</h6>
                                        <h3 class="mb-0">
                                            {{ number_format($invoice->final_amount - $invoice->paid_amount, 2, ',', '.') }} DH</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Total TTC</h6>
                                        <h3 class="mb-0">{{ number_format($invoice->final_amount, 2, ',', '.') }} DH</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <h6 class="mt-4 mb-3">Articles de la facture</h6>
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
                                    @foreach ($invoice->items as $index => $item)
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
                                        <th class="text-right">{{ number_format($invoice->total_amount, 2, ',', '.') }} DH</th>
                                    </tr>
                                    @if ($invoice->discount > 0)
                                        <tr>
                                            <th colspan="6" class="text-end">Remise:</th>
                                            <th class="text-right">- {{ number_format($invoice->discount, 2, ',', '.') }} DH</th>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th colspan="6" class="text-end">Total TTC:</th>
                                        <th class="text-right">{{ number_format($invoice->final_amount, 2, ',', '.') }} DH</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Notes and Terms -->
                        @if ($invoice->notes || $invoice->terms_conditions)
                            <div class="row mt-4">
                                @if ($invoice->notes)
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Notes</h6>
                                            </div>
                                            <div class="card-body">
                                                {{ $invoice->notes }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if ($invoice->terms_conditions)
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Conditions particulières</h6>
                                            </div>
                                            <div class="card-body">
                                                {{ $invoice->terms_conditions }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Amount in words -->
                        @if ($invoice->final_amount > 0)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <strong>Arrêtée la présente facture à la somme de :</strong><br>
                                        {{ ucfirst($numberToFrench($invoice->final_amount)) }} DIRHAMS
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
                    <h5 class="modal-title">
                        <i class="fas fa-file-pdf me-2"></i>Options du PDF
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="pdfOptionsForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Afficher les prix</label>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="show_prices"
                                            id="showPricesYes" value="1" checked>
                                        <label class="form-check-label" for="showPricesYes">
                                            <i class="fas fa-eye text-success me-1"></i>Avec prix
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="show_prices"
                                            id="showPricesNo" value="0">
                                        <label class="form-check-label" for="showPricesNo">
                                            <i class="fas fa-eye-slash text-warning me-1"></i>Sans prix
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">En-tête</label>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="show_logo" id="showLogoYes"
                                            value="1" checked>
                                        <label class="form-check-label" for="showLogoYes">
                                            <i class="fas fa-image text-info me-1"></i>Avec entête (logo)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="show_logo" id="showLogoNo"
                                            value="0">
                                        <label class="form-check-label" for="showLogoNo">
                                            <i class="fas fa-ban text-secondary me-1"></i>Sans entête
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Type d'affichage</label>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="display_type"
                                            id="displayTypeUnite" value="unite" checked>
                                        <label class="form-check-label" for="displayTypeUnite">
                                            <i class="fas fa-weight-hanging text-primary me-1"></i>Avec unité (U)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="display_type"
                                            id="displayTypeVolume" value="volume">
                                        <label class="form-check-label" for="displayTypeVolume">
                                            <i class="fas fa-cube text-success me-1"></i>Avec volume
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                <span id="displayTypeHelp">Unité: Affiche l'unité de mesure standard</span>
                            </small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="printPdfBtn">
                        <i class="fas fa-print me-1"></i>Imprimer
                    </button>
                    <button type="button" class="btn btn-primary" id="downloadPdfBtn">
                        <i class="fas fa-download me-1"></i>Télécharger
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function buildPdfUrl(invoiceId, extraParams = '') {
            const showPrices = $('input[name="show_prices"]:checked').val();
            const showLogo = $('input[name="show_logo"]:checked').val();
            const displayType = $('input[name="display_type"]:checked').val();

            return `/sales/invoices/${invoiceId}/pdf?show_prices=${showPrices}&show_logo=${showLogo}&display_type=${displayType}${extraParams}`;
        }

        function openPdfOptions(invoiceId) {
            $('#pdfOptionsModal').modal('show');

            $('#downloadPdfBtn').off('click').on('click', function() {
                window.open(buildPdfUrl(invoiceId), '_blank');
                $('#pdfOptionsModal').modal('hide');
            });

            // Show the PDF and trigger the browser's print dialog (like Ctrl+P)
            // automatically, instead of just opening it and leaving the user to
            // find the print button in the native PDF viewer.
            $('#printPdfBtn').off('click').on('click', function() {
                const url = buildPdfUrl(invoiceId, '&print=1');
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                        <head><title>Impression Facture</title></head>
                        <body style="margin:0;">
                            <iframe src="${url}" style="width:100%;height:100vh;border:none;"
                                onload="this.contentWindow.focus(); this.contentWindow.print();"></iframe>
                        </body>
                    </html>
                `);
                printWindow.document.close();

                $('#pdfOptionsModal').modal('hide');
            });
        }

        // Update help text based on display type selection
        $(document).ready(function() {
            $('input[name="display_type"]').change(function() {
                if ($(this).val() === 'unite') {
                    $('#displayTypeHelp').text(
                        'Unité: Affiche l\'unité de mesure standard (PIECE, KG, etc.)');
                } else {
                    $('#displayTypeHelp').text(
                        'Volume: Affiche le volume total (quantité × volume unitaire)');
                }
            });
        });
    </script>
@endpush
