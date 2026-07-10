@extends('layouts.app')

@section('title', 'Situation Client – ' . $client->display_name)

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">

        {{-- Breadcrumb --}}
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Situation Client</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('sales.situation.index') }}">
                                        Situation
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        {{ $client->display_name }}
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        {{-- Client info card --}}
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div
                        class="card-header bg-info text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="card-title mb-0">{{ $client->display_name }}</h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <label class="mb-0 small fw-semibold">Du :</label>
                            <input type="date" id="sit_date_from" value="{{ $dateFrom ?? '' }}"
                                style="padding:3px 8px; border:1px solid #ced4da; border-radius:4px; font-size:12px; background:#fff; color:#333;">
                            <label class="mb-0 small fw-semibold">Au :</label>
                            <input type="date" id="sit_date_to" value="{{ $dateTo ?? '' }}"
                                style="padding:3px 8px; border:1px solid #ced4da; border-radius:4px; font-size:12px; background:#fff; color:#333;">
                            <button type="button" class="btn btn-sm btn-secondary" id="resetDateBtn"
                                title="Réinitialiser les dates">
                                <i class="fas fa-times me-1"></i> Réinitialiser
                            </button>
                            <button type="button" class="btn btn-sm btn-light" id="printSituationBtn">
                                <i class="fas fa-print me-1"></i> Imprimer Situation
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Téléphone:</strong><br>
                                {{ $client->phone ?? 'N/A' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Téléphone:</strong><br>
                                {{ $client->phone ?? 'N/A' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Solde Actuel:</strong><br>
                                {!! $client->balance_formatted !!}
                                {!! $client->balance_badge !!}
                            </div>
                            <div class="col-md-3">
                                <strong>Limite Crédit:</strong><br>
                                {{ number_format($client->credit_limit, 2, ',', '.') }} DH
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar bg-{{ $client->credit_progress_class }}"
                                        style="width: {{ $client->credit_percentage }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs card --}}
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" href="#orders" data-bs-toggle="tab"
                                    style="background: #6f8486; margin-right:4px">Vente</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#mixed" data-bs-toggle="tab"
                                    style="background: #6f8486; margin-right:4px">
                                    Vente &amp; Facture
                                    <span class="badge bg-info ms-1">{{ $mixedPaginated->total() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#direct-payments" data-bs-toggle="tab"
                                    style="background: #6f8486; margin-right:4px">
                                    Paiements Directs
                                    <span class="badge bg-success ms-1">{{ $standalonePayments->total() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#history" data-bs-toggle="tab"
                                    style="background: #6f8486;">Historique Solde</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">

                            {{-- Orders Tab --}}
                            <div class="tab-pane active" id="orders">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>N° Commande</th>
                                                <th>Montant</th>
                                                <th>Payé</th>
                                                <th>Reste</th>
                                                <th>Statut</th>
                                                <th>Facture(s)</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="orders-tbody">
                                            @foreach ($orders as $order)
                                                <tr>
                                                    <td>{{ $order->order_date->format('d/m/Y') }}</td>
                                                    <td>{{ $order->order_number }}</td>
                                                    <td>{{ number_format($order->final_amount, 2, ',', '.') }} DH</td>
                                                    <td>{{ number_format($order->paid_amount, 2, ',', '.') }} DH</td>
                                                    <td
                                                        class="{{ $order->final_amount - $order->paid_amount > 0 ? 'text-danger' : 'text-success' }}">
                                                        {{ number_format($order->final_amount - $order->paid_amount, 2, ',', '.') }}
                                                        DH
                                                    </td>
                                                    <td>
                                                        @php
                                                            $badges = [
                                                                'pending' => 'warning',
                                                                'partial' => 'info',
                                                                'paid' => 'success',
                                                            ];
                                                            $labels = [
                                                                'pending' => 'Non Payé',
                                                                'partial' => 'Avance',
                                                                'paid' => 'Payé',
                                                            ];
                                                        @endphp
                                                        <span
                                                            class="badge bg-{{ $badges[$order->payment_status] ?? 'secondary' }}">
                                                            {{ $labels[$order->payment_status] ?? $order->payment_status }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @forelse ($order->related_invoices as $invoice)
                                                            <a href="{{ route('sales.invoices.show', $invoice->invoice_id) }}"
                                                                class="badge bg-primary text-decoration-none mb-1 d-inline-block">
                                                                {{ $invoice->invoice_number }}
                                                            </a>
                                                        @empty
                                                            <span class="text-muted">-</span>
                                                        @endforelse
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('sales.orders.show', $order->order_id) }}"
                                                            class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            @php
                                                $clientBalance = $client->balance;
                                                $balanceClass =
                                                    $clientBalance > 0
                                                        ? 'text-success'
                                                        : ($clientBalance < 0
                                                            ? 'text-danger'
                                                            : 'text-secondary');
                                            @endphp
                                            <tr class="table-light fw-bold">
                                                <td colspan="2" class="text-end">TOTAL</td>
                                                <td id="total-montant">
                                                    {{ number_format($totalsQuery->total_montant ?? 0, 2, ',', '.') }} DH
                                                </td>
                                                <td id="total-paye">
                                                    {{ number_format($totalsQuery->total_paye ?? 0, 2, ',', '.') }} DH</td>
                                                <td>
                                                    <div id="total-reste" class="{{ $balanceClass }}">
                                                        Reste Solde : {{ number_format($clientBalance, 2, ',', '.') }} DH
                                                    </div>
                                                </td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="mt-3" id="orders-pagination">
                                    {{ $orders->links() }}
                                </div>
                            </div>

                            {{-- Vente & Facture Mixed Tab --}}
                            <div class="tab-pane" id="mixed">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>N°</th>
                                                <th>Montant</th>
                                                <th>Payé</th>
                                                <th>Reste</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="mixed-tbody">
                                            @forelse ($mixedPaginated as $row)
                                                <tr>
                                                    <td>{{ $row['date'] }}</td>
                                                    <td><span class="badge bg-{{ $row['type_badge'] }}">{{ $row['type_label'] }}</span>
                                                    </td>
                                                    <td>{{ $row['number'] }}</td>
                                                    <td>{{ $row['amount'] }}</td>
                                                    <td>{{ $row['paid'] }}</td>
                                                    <td class="{{ $row['reste_class'] }}">{{ $row['reste'] }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $row['status_badge'] }}">
                                                            {{ $row['status_label'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ $row['show_url'] }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-3">Aucune vente ni
                                                        facture trouvée</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3" id="mixed-pagination">
                                    {{ $mixedPaginated->links() }}
                                </div>
                            </div>

                            {{-- Direct Payments Tab --}}
                            <div class="tab-pane" id="direct-payments">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Référence</th>
                                                <th>Mode de Paiement</th>
                                                <th class="text-end">Montant</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody id="payments-tbody">
                                            @forelse($standalonePayments as $payment)
                                                <tr>
                                                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                                    <td>REG-{{ str_pad($payment->payment_id, 4, '0', STR_PAD_LEFT) }}</td>
                                                    <td>
                                                        @php
                                                            $methodIcons = [
                                                                'cash' => [
                                                                    'icon' => 'fa-money-bill-wave',
                                                                    'color' => 'text-success',
                                                                    'label' => 'Espèces',
                                                                ],
                                                                'check' => [
                                                                    'icon' => 'fa-money-check-alt',
                                                                    'color' => 'text-primary',
                                                                    'label' => 'Chèque',
                                                                ],
                                                                'transfer' => [
                                                                    'icon' => 'fa-university',
                                                                    'color' => 'text-info',
                                                                    'label' => 'Virement',
                                                                ],
                                                                'traite' => [
                                                                    'icon' => 'fa-file-invoice',
                                                                    'color' => 'text-warning',
                                                                    'label' => 'Traite',
                                                                ],
                                                                'advance' => [
                                                                    'icon' => 'fa-wallet',
                                                                    'color' => 'text-secondary',
                                                                    'label' => 'Avance',
                                                                ],
                                                                'avoir' => [
                                                                    'icon' => 'fa-undo',
                                                                    'color' => 'text-danger',
                                                                    'label' => 'Avoir',
                                                                ],
                                                            ];
                                                            $m = $methodIcons[$payment->payment_method] ?? [
                                                                'icon' => 'fa-circle',
                                                                'color' => 'text-secondary',
                                                                'label' => ucfirst($payment->payment_method),
                                                            ];
                                                        @endphp
                                                        <i
                                                            class="fas {{ $m['icon'] }} {{ $m['color'] }} me-1"></i>{{ $m['label'] }}
                                                    </td>
                                                    <td class="text-end fw-bold text-success">
                                                        {{ number_format($payment->amount, 2, ',', '.') }} DH
                                                    </td>
                                                    <td class="text-muted small">{{ $payment->notes ?? '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-3">Aucun paiement
                                                        direct enregistré</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if ($standalonePayments->count() > 0)
                                            <tfoot>
                                                <tr class="table-light fw-bold">
                                                    <td colspan="3" class="text-end">TOTAL</td>
                                                    <td class="text-end text-success" id="total-payments">
                                                        {{ number_format($totalStandalone, 2, ',', '.') }} DH
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                                <div class="mt-3" id="payments-pagination">
                                    {{ $standalonePayments->links() }}
                                </div>
                            </div>

                            {{-- Balance History Tab --}}
                            <div class="tab-pane" id="history">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Description</th>
                                                <th>Montant</th>
                                                <th>Solde Avant</th>
                                                <th>Solde Après</th>
                                                <th>Par</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($client->balanceHistory->sortByDesc('history_id') as $history)
                                                @php
                                                    $isCreditTracking = in_array($history->type, [
                                                        'credit_used',
                                                        'credit_released',
                                                    ]);
                                                @endphp
                                                <tr class="{{ $isCreditTracking ? 'table-light text-muted' : '' }}">
                                                    <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        @if ($isCreditTracking)
                                                            <span
                                                                class="badge bg-secondary">{{ $history->type_label }}</span>
                                                        @else
                                                            {{ $history->type_label }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $history->description }}</td>
                                                    <td
                                                        class="{{ $isCreditTracking ? 'text-muted' : $history->amount_class }}">
                                                        {{ $history->amount_formatted }}
                                                    </td>
                                                    @if ($isCreditTracking)
                                                        <td class="text-muted fst-italic small"
                                                            title="Suivi plafond crédit (non le solde)">
                                                            Crédit:
                                                            {{ number_format($history->previous_balance, 2, ',', '.') }} DH
                                                        </td>
                                                        <td class="text-muted fst-italic small"
                                                            title="Suivi plafond crédit (non le solde)">
                                                            Crédit: {{ number_format($history->new_balance, 2, ',', '.') }}
                                                            DH
                                                        </td>
                                                    @else
                                                        <td>{{ number_format($history->previous_balance, 2, ',', '.') }} DH
                                                        </td>
                                                        <td>{{ number_format($history->new_balance, 2, ',', '.') }} DH</td>
                                                    @endif
                                                    <td>{{ $history->creator->name ?? 'Système' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">Aucun historique</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <p class="text-muted small mt-1 mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Les lignes grisées <span class="badge bg-secondary">Crédit utilisé / Crédit
                                            libéré</span>
                                        suivent l'utilisation du plafond de crédit, pas le solde du compte client.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('stylesheets')
    <style>
        .card-header-tabs .nav-link {
            color: #495057;
        }

        .card-header-tabs .nav-link.active {
            font-weight: 600;
        }
    </style>
@endpush

@push('scripts')
    <script>
        var filterUrl = "{{ route('sales.situation.client', $client->client_id) }}";
        var printUrl = "{{ route('sales.situation.client.print', $client->client_id) }}";

        var methodIconMap = {
            'Espèces': '<i class="fas fa-money-bill-wave text-success me-1"></i>',
            'Chèque': '<i class="fas fa-money-check-alt text-primary me-1"></i>',
            'Virement': '<i class="fas fa-university text-info me-1"></i>',
            'Traite': '<i class="fas fa-file-invoice text-warning me-1"></i>',
            'Avance': '<i class="fas fa-wallet text-secondary me-1"></i>',
            'Avoir': '<i class="fas fa-undo text-danger me-1"></i>',
        };

        function applyDateFilter() {
            var dateFrom = document.getElementById('sit_date_from').value;
            var dateTo = document.getElementById('sit_date_to').value;

            var params = new URLSearchParams();
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);

            fetch(filterUrl + '?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    // — Orders tab —
                    var tbody = document.getElementById('orders-tbody');
                    var pagination = document.getElementById('orders-pagination');

                    if (data.orders.length === 0) {
                        tbody.innerHTML =
                            '<tr><td colspan="8" class="text-center text-muted py-3">Aucune commande trouvée</td></tr>';
                    } else {
                        tbody.innerHTML = data.orders.map(function(o) {
                            var invoicesHtml = (o.invoices && o.invoices.length) ?
                                o.invoices.map(function(inv) {
                                    return '<a href="' + inv.show_url +
                                        '" class="badge bg-primary text-decoration-none mb-1 d-inline-block">' +
                                        inv.invoice_number + '</a>';
                                }).join(' ') :
                                '<span class="text-muted">-</span>';

                            return '<tr>' +
                                '<td>' + o.date + '</td>' +
                                '<td>' + o.order_number + '</td>' +
                                '<td>' + o.final_amount + '</td>' +
                                '<td>' + o.paid_amount + '</td>' +
                                '<td class="' + o.reste_class + '">' + o.reste + '</td>' +
                                '<td><span class="badge bg-' + o.status_badge + '">' + o.status_label +
                                '</span></td>' +
                                '<td>' + invoicesHtml + '</td>' +
                                '<td><a href="' + o.show_url +
                                '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>' +
                                '</tr>';
                        }).join('');
                    }

                    document.getElementById('total-montant').textContent = data.totals.montant;
                    document.getElementById('total-paye').textContent = data.totals.paye;
                    document.getElementById('total-impaye').textContent = data.totals.reste;

                    pagination.style.display = (dateFrom || dateTo) ? 'none' : '';

                    // — Vente & Facture mixed tab —
                    var mixedTbody = document.getElementById('mixed-tbody');
                    var mixedPagination = document.getElementById('mixed-pagination');

                    if (!data.mixed || data.mixed.length === 0) {
                        mixedTbody.innerHTML =
                            '<tr><td colspan="8" class="text-center text-muted py-3">Aucune vente ni facture trouvée</td></tr>';
                    } else {
                        mixedTbody.innerHTML = data.mixed.map(function(row) {
                            return '<tr>' +
                                '<td>' + row.date + '</td>' +
                                '<td><span class="badge bg-' + row.type_badge + '">' + row.type_label +
                                '</span></td>' +
                                '<td>' + row.number + '</td>' +
                                '<td>' + row.amount + '</td>' +
                                '<td>' + row.paid + '</td>' +
                                '<td class="' + row.reste_class + '">' + row.reste + '</td>' +
                                '<td><span class="badge bg-' + row.status_badge + '">' + row.status_label +
                                '</span></td>' +
                                '<td><a href="' + row.show_url +
                                '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>' +
                                '</tr>';
                        }).join('');
                    }

                    mixedPagination.style.display = (dateFrom || dateTo) ? 'none' : '';

                    // — Direct Payments tab —
                    var payTbody = document.getElementById('payments-tbody');
                    var payPagination = document.getElementById('payments-pagination');

                    if (!data.payments || data.payments.length === 0) {
                        payTbody.innerHTML =
                            '<tr><td colspan="5" class="text-center text-muted py-3">Aucun paiement direct enregistré</td></tr>';
                    } else {
                        payTbody.innerHTML = data.payments.map(function(p) {
                            var icon = methodIconMap[p.method] ||
                                '<i class="fas fa-circle text-secondary me-1"></i>';
                            return '<tr>' +
                                '<td>' + p.date + '</td>' +
                                '<td>' + p.ref + '</td>' +
                                '<td>' + icon + p.method + '</td>' +
                                '<td class="text-end fw-bold text-success">' + p.amount + '</td>' +
                                '<td class="text-muted small">' + (p.notes || '-') + '</td>' +
                                '</tr>';
                        }).join('');
                    }

                    var totalPayEl = document.getElementById('total-payments');
                    if (totalPayEl) {
                        totalPayEl.textContent = data.total_standalone;
                    }

                    if (payPagination) {
                        payPagination.style.display = (dateFrom || dateTo) ? 'none' : '';
                    }
                });
        }

        document.getElementById('resetDateBtn').addEventListener('click', function() {
            document.getElementById('sit_date_from').value = '';
            document.getElementById('sit_date_to').value = '';
            applyDateFilter();
        });

        document.getElementById('sit_date_from').addEventListener('change', function() {
            var toInput = document.getElementById('sit_date_to');
            if (!toInput.value) {
                toInput.value = new Date().toISOString().slice(0, 10);
            }
            applyDateFilter();
        });

        document.getElementById('sit_date_to').addEventListener('change', function() {
            applyDateFilter();
        });

        document.getElementById('printSituationBtn').addEventListener('click', function() {
            var dateFrom = document.getElementById('sit_date_from').value;
            var dateTo = document.getElementById('sit_date_to').value;
            var params = new URLSearchParams({
                show_logo: 1
            });
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);
            window.open(printUrl + '?' + params.toString(), '_blank');
        });
    </script>
@endpush
