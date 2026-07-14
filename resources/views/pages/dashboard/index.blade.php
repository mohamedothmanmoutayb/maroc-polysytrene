@extends('layouts.app')

@section('title', 'Tableau de Bord')

@push('stylesheets')
    <style>
        /* ── Responsive Design Variables ─────────────────────────────────────── */
        :root {
            --mobile-breakpoint: 768px;
            --card-spacing: 0.75rem;
        }

        /* ── KPI cards ─────────────────────────────────────────────── */
        .kpi-card {
            border-radius: 12px;
            border: none;
            transition: transform 0.2s ease;
        }

        .kpi-card:active {
            transform: scale(0.98);
        }

        .kpi-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .kpi-value {
            font-size: 1.3rem;
            font-weight: 700;
            line-height: 1.1;
            word-break: break-word;
        }

        .kpi-label {
            font-size: 0.7rem;
            color: #6c757d;
            letter-spacing: 0.3px;
        }

        .kpi-sub {
            font-size: 0.7rem;
        }

        /* ── Responsive Typography ───────────────────────────────── */
        @media (min-width: 768px) {
            .kpi-value {
                font-size: 1.6rem;
            }

            .kpi-icon {
                width: 48px;
                height: 48px;
            }

            .kpi-label {
                font-size: 0.78rem;
            }
        }

        /* ── Alert banner ──────────────────────────────────────────── */
        .alert-banner {
            border-radius: 12px;
            font-size: 0.85rem;
        }

        @media (max-width: 768px) {
            .alert-banner {
                font-size: 0.75rem;
            }

            .alert-banner .badge {
                font-size: 0.7rem;
                padding: 4px 8px;
            }
        }

        /* ── Progress production ───────────────────────────────────── */
        .progress-sm {
            height: 4px;
            border-radius: 3px;
        }

        @media (min-width: 768px) {
            .progress-sm {
                height: 6px;
            }
        }

        /* ── Timeline ──────────────────────────────────────────────── */
        .timeline-widget {
            padding-left: 0;
            list-style: none;
        }

        .timeline-item {
            border-bottom: 1px solid rgba(0, 0, 0, .05);
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .timeline-badge {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .timeline-badge-border {
            width: 1px;
            flex-grow: 1;
            background: rgba(0, 0, 0, .1);
            min-height: 20px;
        }

        .timeline-badge-wrap {
            gap: 4px;
            padding: 0 8px;
        }

        @media (min-width: 768px) {
            .timeline-badge {
                width: 10px;
                height: 10px;
            }

            .timeline-badge-wrap {
                padding: 0 12px;
            }
        }

        /* ── Responsive Tables ─────────────────────────────────────── */
        .table-responsive-stack {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 768px) {

            .table-custom td,
            .table-custom th {
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
            }
        }

        /* ── Responsive Cards ──────────────────────────────────────── */
        .card {
            border-radius: 12px;
            border: none;
            transition: all 0.3s ease;
        }

        .card-body {
            padding: 1rem;
        }

        @media (min-width: 768px) {
            .card-body {
                padding: 1.25rem;
            }
        }

        /* ── Responsive Charts ─────────────────────────────────────── */
        .chart-container {
            position: relative;
            width: 100%;
            min-height: 200px;
        }

        @media (max-width: 768px) {
            .chart-container {
                min-height: 180px;
            }
        }

        /* ── Touch-friendly buttons ────────────────────────────────── */
        .btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            border-radius: 8px;
        }

        @media (min-width: 768px) {
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
        }

        /* ── Mobile Navigation Tabs ───────────────────────────────── */
        .theme-tab {
            overflow-x: auto;
            flex-wrap: nowrap;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .theme-tab .nav-link {
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            color: #6c757d;
            white-space: nowrap;
            font-size: 0.85rem;
        }

        .theme-tab .nav-link .fs-4 {
            font-size: 1.2rem !important;
        }

        @media (min-width: 768px) {
            .theme-tab .nav-link {
                padding: 8px 16px;
                font-size: 1rem;
            }

            .theme-tab .nav-link .fs-4 {
                font-size: 1.5rem !important;
            }
        }

        /* ── Stock Badges Responsive ───────────────────────────────── */
        .stock-badge-critical,
        .stock-badge-warning {
            padding: 2px 6px;
            border-radius: 16px;
            font-size: 0.7rem;
            display: inline-block;
        }

        @media (min-width: 768px) {

            .stock-badge-critical,
            .stock-badge-warning {
                padding: 2px 8px;
                font-size: 0.75rem;
            }
        }

        /* ── Section Title ─────────────────────────────────────────── */
        .section-title {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #adb5bd;
            margin-bottom: 0.75rem;
        }

        @media (min-width: 768px) {
            .section-title {
                font-size: 0.7rem;
                margin-bottom: 1rem;
            }
        }

        /* ── Gradient Backgrounds ──────────────────────────────────── */
        .bg-primary-gt {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        }

        .bg-welcome-gt {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
        }

        /* ── Hover Effects for Desktop Only ────────────────────────── */
        @media (min-width: 768px) {
            .card-hover:hover {
                box-shadow: 0 4px 20px rgba(0, 0, 0, .12);
                transition: .2s;
                transform: translateY(-2px);
            }
        }

        /* ── Responsive Spacing Utilities ──────────────────────────── */
        .gap-3 {
            gap: 0.75rem !important;
        }

        @media (min-width: 768px) {
            .gap-3 {
                gap: 1rem !important;
            }
        }

        /* ── Mobile-specific adjustments ───────────────────────────── */
        @media (max-width: 576px) {
            .container-fluid {
                padding-left: 10px;
                padding-right: 10px;
            }

            .badge {
                font-size: 0.7rem;
                padding: 3px 6px;
            }

            h5,
            .h5 {
                font-size: 1rem;
            }

            h3,
            .h3 {
                font-size: 1.3rem;
            }

            .fs-7 {
                font-size: 1.2rem;
            }
        }

        /* ── Smooth Scrolling for Mobile ───────────────────────────── */
        .overflow-auto-mobile {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ── Loading States ────────────────────────────────────────── */
        .loading-spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid pb-3 pb-md-4">

        {{-- ═══════════════════════════════════════════════════════════════════════
             ALERTES IMPORTANTES - Mobile Responsive
        ═══════════════════════════════════════════════════════════════════════ --}}
        @if ($stats['total_alerts'] > 0)
            <div class="alert alert-danger alert-banner d-flex flex-wrap align-items-center gap-2 gap-md-3 mb-3 mb-md-4"
                role="alert">
                <iconify-icon icon="solar:danger-triangle-bold" class="fs-5 flex-shrink-0"></iconify-icon>
                <strong class="fs-6 fs-md-5">{{ $stats['total_alerts'] }} alerte(s)</strong>
                <div class="d-flex flex-wrap gap-1 gap-md-2 mt-2 mt-md-0">
                    @if ($lowStockProducts->count() > 0)
                        <span class="badge bg-danger">{{ $lowStockProducts->count() }} produit(s)</span>
                    @endif
                    @if ($lowStockMaterials->count() > 0)
                        <span class="badge bg-warning text-dark">{{ $lowStockMaterials->count() }} matière(s)</span>
                    @endif
                    @if ($stats['late_production_orders'] > 0)
                        <span class="badge bg-danger">{{ $stats['late_production_orders'] }} retard production</span>
                    @endif
                    @if ($stats['overdue_sales_orders'] > 0)
                        <span class="badge bg-danger">{{ $stats['overdue_sales_orders'] }} retard cmd. client</span>
                    @endif
                    @if ($stats['machines_breakdown'] > 0)
                        <span class="badge bg-warning text-dark">{{ $stats['machines_breakdown'] }} machines</span>
                    @endif
                </div>
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════════
             1. SECTION KPIs – Mobile First Grid
        ═══════════════════════════════════════════════════════════════════════ --}}
        <p class="section-title">Vue d'ensemble · {{ date('d F Y') }}</p>
        <div class="row g-2 g-md-3 mb-3 mb-md-4">

            {{-- CA Aujourd'hui --}}
            <div class="col-6 col-md-3">
                <div class="card kpi-card card-hover h-100">
                    <div class="card-body p-2 p-md-3">
                        <div class="d-flex align-items-center gap-2 gap-md-3 mb-2">
                            <div class="kpi-icon bg-primary-subtle">
                                <iconify-icon icon="solar:calendar-bold" class="text-primary fs-5"></iconify-icon>
                            </div>
                            <a href="{{ route('sales.orders.index') }}"
                                class="kpi-label text-decoration-none text-reset stretched-link">CA Aujourd'hui</a>
                        </div>
                        <div class="kpi-value text-primary mb-1">{{ number_format($stats['today_sales'], 0) }}<small
                                class="fs-11 fw-normal text-muted"> DH</small></div>
                        <div class="kpi-sub text-muted">Mois: <strong
                                class="text-dark">{{ number_format($stats['month_sales'], 0) }} DH</strong></div>
                    </div>
                </div>
            </div>

            {{-- Production Jour vs Objectif --}}
            <div class="col-6 col-md-3">
                <div class="card kpi-card card-hover h-100">
                    <div class="card-body p-2 p-md-3">
                        <div class="d-flex align-items-center gap-2 gap-md-3 mb-2">
                            <div class="kpi-icon bg-success-subtle">
                                <iconify-icon icon="solar:box-bold" class="text-success fs-5"></iconify-icon>
                            </div>
                            <a href="{{ route('production-output.index') }}"
                                class="kpi-label text-decoration-none text-reset stretched-link">Production</a>
                        </div>
                        <div class="kpi-value text-success mb-1">{{ number_format($stats['today_qty_produced']) }}<small
                                class="fs-11 fw-normal text-muted"> u.</small></div>
                        <div class="kpi-sub text-muted">
                            @if ($stats['today_volume_m3'] > 0)
                                {{ $stats['today_volume_m3'] }} m³ ·
                            @endif
                            Rendement <strong
                                class="{{ $stats['production_yield'] >= 90 ? 'text-success' : ($stats['production_yield'] >= 70 ? 'text-warning' : 'text-danger') }}">{{ $stats['production_yield'] }}%</strong>
                        </div>
                        @if ($stats['production_objective'] > 0)
                            <div class="mt-2">
                                <div class="d-flex justify-content-between mb-1" style="font-size:.65rem;">
                                    <span class="text-muted">Obj:
                                        {{ number_format($stats['production_objective']) }}</span>
                                    <span class="fw-semibold">{{ $stats['production_progress'] }}%</span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-success"
                                        style="width:{{ $stats['production_progress'] }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Commandes en cours --}}
            <div class="col-6 col-md-3">
                <div class="card kpi-card card-hover h-100">
                    <div class="card-body p-2 p-md-3">
                        <div class="d-flex align-items-center gap-2 gap-md-3 mb-2">
                            <div class="kpi-icon bg-warning-subtle">
                                <iconify-icon icon="solar:cart-bold" class="text-warning fs-5"></iconify-icon>
                            </div>
                            <a href="{{ route('sales.orders.index') }}"
                                class="kpi-label text-decoration-none text-reset stretched-link">Ventes</a>
                        </div>
                        <div class="kpi-value text-warning mb-2">{{ $stats['pending_sales_orders'] }}<small
                                class="fs-11 fw-normal text-muted"> attente</small></div>
                        <div class="d-flex gap-1 gap-md-2">
                            <span class="badge bg-danger rounded-pill">{{ $stats['overdue_sales_orders'] }} retard</span>
                            <span class="badge bg-primary rounded-pill">{{ $stats['in_progress_orders'] }} prod.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alertes Stock --}}
            <div class="col-6 col-md-3">
                <div
                    class="card kpi-card card-hover h-100 {{ $stats['total_alerts'] > 0 ? 'border-danger border-opacity-50' : '' }}">
                    <div class="card-body p-2 p-md-3">
                        <div class="d-flex align-items-center gap-2 gap-md-3 mb-2">
                            <div
                                class="kpi-icon {{ $stats['total_alerts'] > 0 ? 'bg-danger-subtle' : 'bg-success-subtle' }}">
                                <iconify-icon icon="solar:bell-bold"
                                    class="{{ $stats['total_alerts'] > 0 ? 'text-danger' : 'text-success' }} fs-5"></iconify-icon>
                            </div>
                            <a href="{{ route('inventory.index') }}"
                                class="kpi-label text-decoration-none text-reset stretched-link">Alertes</a>
                        </div>
                        <div class="kpi-value {{ $stats['total_alerts'] > 0 ? 'text-danger' : 'text-success' }} mb-1">
                            {{ $stats['total_alerts'] }}</div>
                        <div class="kpi-sub text-muted">
                            @if ($stats['total_alerts'] == 0)
                                <iconify-icon icon="solar:check-circle-linear" class="text-success"></iconify-icon> Tout OK
                            @else
                                Stock: {{ $lowStockProducts->count() + $lowStockMaterials->count() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════
             2. WELCOME + PRODUCTION ORDERS + PAYMENTS - Responsive Stack
        ═══════════════════════════════════════════════════════════════════════ --}}
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            {{-- Bloc 1 : Carte de bienvenue --}}
            <div class="col-lg-6">
                <div class="card text-white bg-welcome-gt overflow-hidden h-100">
                    <div class="card-body p-3 p-md-4">
                        <span class="badge bg-white bg-opacity-25 d-inline-flex align-items-center gap-2 mb-3">
                            <iconify-icon icon="solar:check-circle-outline" class="fs-5"></iconify-icon>
                            <span class="fw-normal">Ce mois <span
                                    class="fw-semibold">+{{ $stats['new_clients_this_month'] }} nouveaux
                                    clients</span></span>
                        </span>
                        <h4 class="text-white fw-normal mt-2 mt-md-3 mb-1">
                            Bonjour, <span
                                class="fw-bolder">{{ $stats['user']->employee->full_name ?? $stats['user']->username }}</span>
                        </h4>
                        <h6 class="opacity-75 fw-normal text-white mb-3 mb-md-4">
                            @if ($stats['user']->role)
                                <span style="color:black !important;" class="badge bg-white">{{ ucfirst($stats['user']->role) }}</span>
                            @endif
                            · {{ date('l d F Y') }}
                        </h6>
                        <div class="row g-2 mt-2">
                            <div class="col-4 text-center">
                                <div class="fs-5 fw-bold">{{ number_format($stats['today_sales'], 0) }}</div>
                                <small class="opacity-75" style="font-size: 0.7rem;">CA Jour</small>
                            </div>
                            <div class="col-4 text-center border-start border-end border-white border-opacity-25">
                                <div class="fs-5 fw-bold">{{ $stats['in_progress_orders'] }}</div>
                                <small class="opacity-75" style="font-size: 0.7rem;">Prod. cours</small>
                            </div>
                            <div class="col-4 text-center">
                                <div class="fs-5 fw-bold">{{ $stats['active_clients'] }}</div>
                                <small class="opacity-75" style="font-size: 0.7rem;">Clients actifs</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bloc 2 : Ordres Production + Total Paiements (empilés, pleine largeur) --}}
            <div class="col-lg-6">
                <div class="d-flex flex-column gap-2 gap-md-3 h-100">
                    <div class="card bg-info-subtle overflow-hidden shadow-none flex-fill mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <a href="{{ route('production-orders.index') }}"
                                        class="text-dark fw-semibold text-decoration-none stretched-link"
                                        style="font-size: 0.8rem;">Ordres
                                        Production</a>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <h5 class="fw-semibold mb-0">
                                            {{ number_format($stats['total_production_orders']) }}
                                        </h5>
                                        <span class="fs-11 text-muted">{{ $stats['in_progress_orders'] }} en
                                            cours</span>
                                    </div>
                                </div>
                                <span class="round-48 d-flex align-items-center justify-content-center bg-white rounded">
                                    <iconify-icon icon="solar:box-linear" class="text-info"></iconify-icon>
                                </span>
                            </div>
                            <div class="d-flex flex-wrap justify-content-between gap-1">
                                <span class="badge bg-success">{{ $stats['completed_production_orders'] }} term.</span>
                                <span class="badge bg-warning text-dark">{{ $stats['pending_production_orders'] }}
                                    attente</span>
                                @if ($stats['late_production_orders'] > 0)
                                    <span class="badge bg-danger">{{ $stats['late_production_orders'] }} retard</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card bg-success-subtle overflow-hidden shadow-none flex-fill mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 gap-md-3 mb-3">
                                <span
                                    class="round-48 d-flex align-items-center justify-content-center rounded bg-white">
                                    <iconify-icon icon="solar:wallet-linear" class="fs-7 text-success"></iconify-icon>
                                </span>
                                <a href="{{ route('purchases.index') }}"
                                    class="mb-0 fw-medium h6 text-reset text-decoration-none stretched-link">Total
                                    Paiements</a>
                            </div>
                            <h4 class="mb-2">{{ number_format($stats['completed_payments'], 0) }} <small
                                    class="fs-12 text-muted">DH</small></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════
             3. GRAPHIQUE VENTES + RÉSUMÉ FINANCE - Mobile Responsive Charts
        ═══════════════════════════════════════════════════════════════════════ --}}
        <p class="section-title">Finance</p>
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-body p-2 p-md-3">
                        <div class="d-md-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h5 class="card-title mb-0">Chiffre d'Affaires &amp; Dépenses</h5>
                                <p class="card-subtitle mb-0 text-muted">Ventes vs Dépenses</p>
                            </div>
                            <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
                                <div class="d-flex gap-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="round-10 bg-primary rounded-circle d-block"></span>
                                        <span class="text-muted" style="font-size:.75rem;">Ventes</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="round-10 bg-danger rounded-circle d-block"></span>
                                        <span class="text-muted" style="font-size:.75rem;">Dépenses</span>
                                    </div>
                                </div>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Période CA">
                                    <button type="button" class="btn btn-outline-primary active" id="caModeMonth"
                                        data-mode="month">Mois</button>
                                    <button type="button" class="btn btn-outline-primary" id="caModeDay"
                                        data-mode="day">Jour</button>
                                </div>
                            </div>
                        </div>
                        <div class="chart-container" style="height: 220px;">
                            <div id="revenue-chart"></div>
                        </div>
                        <div class="row g-2 mt-3">
                            <div class="col-4">
                                <div class="d-flex align-items-center gap-1 gap-md-2">
                                    <span
                                        class="d-flex align-items-center justify-content-center rounded p-1 p-md-2 bg-primary-subtle">
                                        <iconify-icon icon="solar:pie-chart-2-linear" class="text-primary"></iconify-icon>
                                    </span>
                                    <div>
                                        <div class="kpi-label">Total Ventes</div>
                                        <div class="fw-semibold small">
                                            {{ number_format($stats['total_sales_amount'], 0) }} DH</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-flex align-items-center gap-1 gap-md-2">
                                    <span
                                        class="d-flex align-items-center justify-content-center rounded p-1 p-md-2 bg-danger-subtle">
                                        <iconify-icon icon="solar:database-linear" class="text-danger"></iconify-icon>
                                    </span>
                                    <div>
                                        <div class="kpi-label">Total Dépenses</div>
                                        <div class="fw-semibold small">{{ number_format($stats['total_expenses'], 0) }} DH
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-flex align-items-center gap-1 gap-md-2">
                                    <span
                                        class="d-flex align-items-center justify-content-center rounded p-1 p-md-2 {{ $stats['profit_month'] >= 0 ? 'bg-success-subtle' : 'bg-danger-subtle' }}">
                                        <iconify-icon icon="solar:dollar-minimalistic-linear"
                                            class="{{ $stats['profit_month'] >= 0 ? 'text-success' : 'text-danger' }}"></iconify-icon>
                                    </span>
                                    <div>
                                        <div class="kpi-label">Bénéfice</div>
                                        <div
                                            class="fw-semibold small {{ $stats['profit_month'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($stats['profit_month'], 0) }} DH
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <h5 class="card-title mb-3">Résumé Financier</h5>

                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="kpi-icon bg-primary-subtle" style="width:32px;height:32px;">
                                    <iconify-icon icon="solar:chart-bold" class="text-primary"></iconify-icon>
                                </span>
                                <span class="fw-medium small">CA Ce Mois</span>
                            </div>
                            <span class="fw-bold text-primary small">{{ number_format($stats['month_sales'], 0) }}
                                DH</span>
                        </div>

                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="kpi-icon bg-danger-subtle" style="width:32px;height:32px;">
                                    <iconify-icon icon="solar:bill-bold" class="text-danger"></iconify-icon>
                                </span>
                                <span class="fw-medium small">Dépenses Ce Mois</span>
                            </div>
                            <span class="fw-bold text-danger small">{{ number_format($stats['month_expenses'], 0) }}
                                DH</span>
                        </div>

                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span
                                    class="kpi-icon {{ $stats['profit_month'] >= 0 ? 'bg-success-subtle' : 'bg-danger-subtle' }}"
                                    style="width:32px;height:32px;">
                                    <iconify-icon icon="solar:hand-money-bold"
                                        class="{{ $stats['profit_month'] >= 0 ? 'text-success' : 'text-danger' }}"></iconify-icon>
                                </span>
                                <span class="fw-medium small">Bénéfice</span>
                            </div>
                            <span
                                class="fw-bold small {{ $stats['profit_month'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($stats['profit_month'], 0) }}
                                DH</span>
                        </div>

                        <div class="py-2 border-bottom">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-medium small">Marge bénéficiaire</span>
                                <span
                                    class="fw-bold small {{ $stats['margin_pct'] >= 15 ? 'text-success' : ($stats['margin_pct'] >= 5 ? 'text-warning' : 'text-danger') }}">{{ $stats['margin_pct'] }}%</span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar {{ $stats['margin_pct'] >= 15 ? 'bg-success' : ($stats['margin_pct'] >= 5 ? 'bg-warning' : 'bg-danger') }}"
                                    style="width:{{ max(0, min(100, $stats['margin_pct'])) }}%"></div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between py-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="kpi-icon bg-info-subtle" style="width:32px;height:32px;">
                                    <iconify-icon icon="solar:sun-bold" class="text-info"></iconify-icon>
                                </span>
                                <span class="fw-medium small">Aujourd'hui</span>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary small">{{ number_format($stats['today_sales'], 0) }} DH
                                </div>
                                <div class="kpi-label" style="font-size: 0.65rem;">Dép:
                                    {{ number_format($stats['today_expenses'], 0) }} DH</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════
             RÈGLEMENTS PAR JOUR (espèce / chèque / traite / virement)
        ═══════════════════════════════════════════════════════════════════════ --}}
        <p class="section-title">Règlements par Jour</p>
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table align-middle table-custom mb-0" style="font-size:.8rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="font-size:.75rem;">Date</th>
                                        <th class="text-end" style="font-size:.75rem;">Espèces</th>
                                        <th class="text-end" style="font-size:.75rem;">Chèque</th>
                                        <th class="text-end" style="font-size:.75rem;">Traite</th>
                                        <th class="text-end" style="font-size:.75rem;">Virement</th>
                                        <th class="text-end" style="font-size:.75rem;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sumCash = 0; $sumCheck = 0; $sumTraite = 0; $sumTransfer = 0; $sumTotal = 0;
                                    @endphp
                                    @foreach ($dailyPayments as $row)
                                        @php
                                            $sumCash += $row['cash']; $sumCheck += $row['check'];
                                            $sumTraite += $row['traite']; $sumTransfer += $row['transfer'];
                                            $sumTotal += $row['total'];
                                        @endphp
                                        <tr class="{{ $row['is_today'] ? 'table-primary' : '' }}">
                                            <td class="fw-semibold">{{ $row['date'] }}
                                                @if ($row['is_today'])
                                                    <span class="badge bg-primary ms-1"
                                                        style="font-size:.6rem;">Aujourd'hui</span>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ number_format($row['cash'], 0, ',', ' ') }}</td>
                                            <td class="text-end">{{ number_format($row['check'], 0, ',', ' ') }}</td>
                                            <td class="text-end">{{ number_format($row['traite'], 0, ',', ' ') }}</td>
                                            <td class="text-end">{{ number_format($row['transfer'], 0, ',', ' ') }}</td>
                                            <td class="text-end fw-bold">{{ number_format($row['total'], 0, ',', ' ') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr class="fw-bold">
                                        <td>Total 7 jours</td>
                                        <td class="text-end text-success">{{ number_format($sumCash, 0, ',', ' ') }}</td>
                                        <td class="text-end">{{ number_format($sumCheck, 0, ',', ' ') }}</td>
                                        <td class="text-end">{{ number_format($sumTraite, 0, ',', ' ') }}</td>
                                        <td class="text-end">{{ number_format($sumTransfer, 0, ',', ' ') }}</td>
                                        <td class="text-end text-primary">{{ number_format($sumTotal, 0, ',', ' ') }} DH</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════
             COÛT DE PRODUCTION (jour / mois) + Qté produite m³ · Matière première
        ═══════════════════════════════════════════════════════════════════════ --}}
        <p class="section-title">Coût de Production</p>
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            {{-- KPIs coût de production --}}
            <div class="col-lg-3">
                <div class="d-flex flex-column gap-2 gap-md-3 h-100">
                    <div class="card kpi-card card-hover flex-fill">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="kpi-icon bg-info-subtle">
                                    <iconify-icon icon="solar:calendar-bold" class="text-info fs-5"></iconify-icon>
                                </div>
                                <a href="{{ route('production-consumption.index') }}"
                                    class="kpi-label text-decoration-none text-reset stretched-link">Coût Production Jour</a>
                            </div>
                            <div class="kpi-value text-info mb-0">{{ number_format($prodCostToday, 0, ',', ' ') }}<small
                                    class="fs-11 fw-normal text-muted"> DH</small></div>
                        </div>
                    </div>
                    <div class="card kpi-card card-hover flex-fill">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="kpi-icon bg-primary-subtle">
                                    <iconify-icon icon="solar:calendar-mark-bold" class="text-primary fs-5"></iconify-icon>
                                </div>
                                <a href="{{ route('production-consumption.index') }}"
                                    class="kpi-label text-decoration-none text-reset stretched-link">Coût Production Mois</a>
                            </div>
                            <div class="kpi-value text-primary mb-0">{{ number_format($prodCostMonth, 0, ',', ' ') }}<small
                                    class="fs-11 fw-normal text-muted"> DH</small></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Qté produite en m³ par article --}}
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <h5 class="card-title mb-3" style="font-size:1rem;">
                            <a href="{{ route('production-output.index') }}"
                                class="text-reset text-decoration-none stretched-link">
                                <iconify-icon icon="solar:box-bold" class="text-success me-1"></iconify-icon>
                                Qté Produite en m³ par Article <small class="text-muted fw-normal">· ce mois</small>
                            </a>
                        </h5>
                        <div class="table-responsive-stack">
                            <table class="table align-middle table-custom mb-0" style="font-size:.8rem;">
                                <thead>
                                    <tr>
                                        <th class="fw-normal ps-0" style="font-size:.75rem;">Article</th>
                                        <th class="fw-normal text-end" style="font-size:.75rem;">Qté</th>
                                        <th class="fw-normal text-end" style="font-size:.75rem;">Volume (m³)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($productionByProduct as $p)
                                        <tr>
                                            <td class="ps-0">
                                                <div class="fw-semibold" style="font-size:.8rem;">
                                                    {{ $p->product_name }}</div>
                                                <small class="text-muted"
                                                    style="font-size:.65rem;">{{ $p->product_code }}</small>
                                            </td>
                                            <td class="text-end">{{ number_format($p->qty_produced, 0, ',', ' ') }}</td>
                                            <td class="text-end fw-bold text-info">
                                                {{ number_format($p->volume_m3, 3, ',', ' ') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-3 text-muted small">Aucune production ce
                                                mois</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Qté matière première consommée --}}
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <h5 class="card-title mb-3" style="font-size:1rem;">
                            <a href="{{ route('production-consumption.index') }}"
                                class="text-reset text-decoration-none stretched-link">
                                <iconify-icon icon="solar:test-tube-bold" class="text-warning me-1"></iconify-icon>
                                Matière Première Consommée <small class="text-muted fw-normal">· ce mois</small>
                            </a>
                        </h5>
                        <div class="table-responsive-stack">
                            <table class="table align-middle table-custom mb-0" style="font-size:.8rem;">
                                <thead>
                                    <tr>
                                        <th class="fw-normal ps-0" style="font-size:.75rem;">Matière</th>
                                        <th class="fw-normal text-end" style="font-size:.75rem;">Qté</th>
                                        <th class="fw-normal text-end" style="font-size:.75rem;">Coût (DH)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($materialConsumption as $m)
                                        <tr>
                                            <td class="ps-0">
                                                <div class="fw-semibold" style="font-size:.8rem;">
                                                    {{ $m->material_name }}</div>
                                                <small class="text-muted"
                                                    style="font-size:.65rem;">{{ $m->material_code }}</small>
                                            </td>
                                            <td class="text-end">
                                                {{ number_format($m->qty_used ?: $m->qty_planned, 2, ',', ' ') }}
                                                <small class="text-muted">{{ $m->unit_of_measure }}</small>
                                            </td>
                                            <td class="text-end fw-bold">
                                                {{ number_format($m->total_cost, 0, ',', ' ') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-3 text-muted small">Aucune consommation ce
                                                mois</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════
             CAPACITÉ DE PRODUCTION PAR ÉQUIPE (production / découpage) + rendement
        ═══════════════════════════════════════════════════════════════════════ --}}
        <p class="section-title">Capacité de Production par Équipe · Rendement</p>
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table align-middle table-custom mb-0" style="font-size:.8rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="font-size:.75rem;">Équipe / Type</th>
                                        <th class="text-end" style="font-size:.75rem;">Produites</th>
                                        <th class="text-end" style="font-size:.75rem;">Défectueuses</th>
                                        <th class="text-end" style="font-size:.75rem;">Volume (m³)</th>
                                        <th style="font-size:.75rem; min-width:140px;">Rendement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($capacityByType as $c)
                                        <tr>
                                            <td class="fw-semibold">{{ $c->label }}</td>
                                            <td class="text-end">{{ number_format($c->qty_produced, 0, ',', ' ') }}</td>
                                            <td class="text-end text-danger">
                                                {{ number_format($c->qty_defective, 0, ',', ' ') }}</td>
                                            <td class="text-end text-info">
                                                {{ number_format($c->volume_m3, 3, ',', ' ') }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress progress-sm flex-grow-1">
                                                        <div class="progress-bar {{ $c->yield >= 90 ? 'bg-success' : ($c->yield >= 70 ? 'bg-warning' : 'bg-danger') }}"
                                                            style="width:{{ $c->yield }}%"></div>
                                                    </div>
                                                    <span
                                                        class="fw-bold small {{ $c->yield >= 90 ? 'text-success' : ($c->yield >= 70 ? 'text-warning' : 'text-danger') }}">{{ $c->yield }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-3 text-muted small">Aucune production ce
                                                mois</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════
            ÉTAT DE TRÉSORERIE (Cash Flow Statement)
            Formules:
            Résultat NET = (Crédit Client + La Caisse + Stock MP + Stock Produit)
                        - (Crédit Fournisseur + Charges Fixes)

            Taux de couverture = (Résultat NET / (Crédit Client + La Caisse + Stock MP + Stock Produit)) × 100
        ═══════════════════════════════════════════════════════════════════════ --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h5 class="card-title mb-0 text-white">
                                <iconify-icon icon="solar:wallet-money-bold" class="me-2"></iconify-icon>
                                État de Trésorerie
                            </h5>
                            <div class="d-flex align-items-center gap-3 mt-2 mt-sm-0">
                                <small class="text-white-50">
                                    <iconify-icon icon="solar:calendar-linear"></iconify-icon>
                                    Période: {{ $cashFlowData['date_from'] }} - {{ $cashFlowData['date_to'] }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" style="font-size: 0.9rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 45%">Désignation</th>
                                        <th style="width: 25%">Montant (DH)</th>
                                        <th style="width: 30%">Détails / Calcul</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- SECTION 1: DETTES & CHARGES (Ce qui diminue la trésorerie) -->
                                    <tr class="table-danger">
                                        <td colspan="3">
                                            <strong>
                                                <iconify-icon icon="solar:arrow-down-bold" class="me-1"></iconify-icon>
                                                I - DETTES & CHARGES
                                            </strong>
                                            <small class="text-muted ms-2">Ce qui diminue la trésorerie</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 30px;">
                                            <strong>Crédit Fournisseur</strong>
                                            <small class="text-muted d-block">Dettes fournisseurs (achats non
                                                réglés)</small>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            {{ number_format($cashFlowData['credit_fournisseur'], 2, ',', '.') }}
                                        </td>
                                        <td class="text-muted small">Achats du mois non encore payés</td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 30px;">
                                            <strong>Charges Fixes</strong>
                                            <small class="text-muted d-block">Dépenses du mois + Salaires employés</small>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            {{ number_format($cashFlowData['charges_fixes'], 2, ',', '.') }}
                                        </td>
                                        <td class="text-muted small">
                                            Dépenses: {{ number_format($cashFlowData['depenses_mois'], 2, ',', '.') }} +
                                            Salaires: {{ number_format($cashFlowData['salaires_employes'], 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr class="table-light">
                                        <td class="fw-bold">Total Dettes & Charges</td>
                                        <td class="text-end fw-bold bg-danger text-white fs-5">
                                            {{ number_format($cashFlowData['total_negatif'], 2, ',', '.') }}
                                        </td>
                                        <td class="text-muted">= Crédit Fournisseur + Charges Fixes</td>
                                    </tr>

                                    <!-- SECTION 2: RESSOURCES (Ce qui augmente la trésorerie) -->
                                    <tr class="table-success">
                                        <td colspan="3">
                                            <strong>
                                                <iconify-icon icon="solar:arrow-up-bold" class="me-1"></iconify-icon>
                                                II - RESSOURCES (Sources)
                                            </strong>
                                            <small class="text-muted ms-2">Ce qui augmente la trésorerie</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 30px;">
                                            <strong>Crédit Client</strong>
                                            <small class="text-muted d-block">Ventes impayées (créances clients)</small>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            {{ number_format($cashFlowData['credit_client'], 2, ',', '.') }}
                                        </td>
                                        <td class="text-muted small">Factures clients non encaissées</td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 30px;">
                                            <strong>La Caisse</strong>
                                            <small class="text-muted d-block">Encaissements espèces</small>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            {{ number_format($cashFlowData['la_caisse'], 2, ',', '.') }}
                                        </td>
                                        <td class="text-muted small">Paiements espèces reçus</td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 30px;">
                                            <strong>Stock MP</strong>
                                            <small class="text-muted d-block">Valeur matières premières (Coût moyen
                                                pondéré)</small>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            {{ number_format($cashFlowData['stock_mp'], 2, ',', '.') }}
                                        </td>
                                        <td class="text-muted small">
                                            @if (!empty($cashFlowData['stock_mp_details']))
                                                <span class="d-inline-block" tabindex="0" data-bs-toggle="popover"
                                                    data-bs-trigger="hover" data-bs-html="true"
                                                    data-bs-content="
                                                @foreach ($cashFlowData['stock_mp_details'] as $detail)
                                                    <strong>{{ $detail['material_name'] }}</strong><br>
                                                    Qté: {{ number_format($detail['total_quantity'], 2, ',', '.') }} ×
                                                    CMUP: {{ number_format($detail['weighted_average_cost'], 2, ',', '.') }} =
                                                    {{ number_format($detail['total_value'], 2, ',', '.') }} DH<br> @endforeach">
                                                    <iconify-icon icon="solar:info-circle-linear"></iconify-icon> Détail
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 30px;">
                                            <strong>Stock Produit</strong>
                                            <small class="text-muted d-block">Valeur produits finis (Prix de vente)</small>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            {{ number_format($cashFlowData['stock_produit'], 2, ',', '.') }}
                                        </td>
                                        <td class="text-muted small">
                                            @if (!empty($cashFlowData['stock_produit_details']))
                                                <span class="d-inline-block" tabindex="0" data-bs-toggle="popover"
                                                    data-bs-trigger="hover" data-bs-html="true"
                                                    data-bs-content="
                                                @foreach ($cashFlowData['stock_produit_details'] as $detail)
                                                    <strong>{{ $detail['product_name'] }}</strong><br>
                                                    Qté: {{ number_format($detail['quantity'], 2, ',', '.') }} ×
                                                    Prix: {{ number_format($detail['unit_price'], 2, ',', '.') }} =
                                                    {{ number_format($detail['total_value'], 2, ',', '.') }} DH<br> @endforeach">
                                                    <iconify-icon icon="solar:info-circle-linear"></iconify-icon> Détail
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="table-light">
                                        <td class="fw-bold">Total Ressources + Stock</td>
                                        <td class="text-end fw-bold bg-success text-white fs-5">
                                            {{ number_format($cashFlowData['total_positif'], 2, ',', '.') }}
                                        </td>
                                        <td class="text-muted">= Crédit Client + La Caisse + Stock MP + Stock Produit</td>
                                    </tr>

                                    <!-- SECTION 3: RÉSULTAT NET -->
                                    <tr class="table-info">
                                        <td colspan="3">
                                            <strong>
                                                <iconify-icon icon="solar:calculator-bold" class="me-1"></iconify-icon>
                                                III - RÉSULTAT NET
                                            </strong>
                                        </td>
                                    </tr>
                                    <tr
                                        class="{{ $cashFlowData['resultat_net'] >= 0 ? 'table-success' : 'table-danger' }}">
                                        <td class="fw-bold">
                                            Résultat NET
                                            <small class="text-muted d-block">= II − I</small>
                                        </td>
                                        <td class="text-end fw-bold fs-4">
                                            {{ $cashFlowData['resultat_net'] > 0 ? '+' : '' }}{{ number_format($cashFlowData['resultat_net'], 2, ',', '.') }}
                                        </td>
                                        <td class="small">
                                            @if ($cashFlowData['resultat_net'] >= 0)
                                                <span class="text-success">✓ Situation financière saine</span>
                                            @else
                                                <span class="text-danger">⚠ Nécessite des ajustements de trésorerie</span>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- SECTION 4: TAUX DE COUVERTURE -->
                                    <tr class="table-secondary">
                                        <td colspan="3">
                                            <strong>
                                                <iconify-icon icon="solar:chart-bold" class="me-1"></iconify-icon>
                                                IV - TAUX DE COUVERTURE
                                            </strong>
                                        </td>
                                    </tr>

                                    <tr
                                        class="{{ $cashFlowData['taux_couverture_class'] == 'success' ? 'table-success' : ($cashFlowData['taux_couverture_class'] == 'warning' ? 'table-warning' : ($cashFlowData['taux_couverture_class'] == 'info' ? 'table-info' : 'table-danger')) }}">
                                        <td class="fw-bold">
                                            Taux de couverture
                                            <small class="text-muted d-block">= (Résultat NET ÷  II) × 100</small>
                                        </td>
                                        <td class="text-end fw-bold fs-3">
                                            {{ $cashFlowData['taux_couverture'] > 0 ? '+' : '' }}{{ number_format($cashFlowData['taux_couverture'], 2, ',', '.') }}%
                                        </td>
                                        <td>
                                            @php
                                                $coverageMsg = '';
                                                if ($cashFlowData['taux_couverture'] >= 70) {
                                                    $coverageMsg = 'Excellent - Trésorerie très saine';
                                                    $badgeClass = 'success';
                                                } elseif ($cashFlowData['taux_couverture'] >= 50) {
                                                    $coverageMsg = 'Bon - Trésorerie satisfaisante';
                                                    $badgeClass = 'warning';
                                                } elseif ($cashFlowData['taux_couverture'] >= 30) {
                                                    $coverageMsg = 'Moyen - Nécessite une attention';
                                                    $badgeClass = 'info';
                                                } else {
                                                    $coverageMsg = 'Critique - Action requise immédiate';
                                                    $badgeClass = 'danger';
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $badgeClass }} p-2">
                                                <iconify-icon
                                                    icon="solar:{{ $cashFlowData['taux_couverture'] >= 50 ? 'check-circle-bold' : 'chart-bold' }}"></iconify-icon>
                                                {{ $coverageMsg }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="py-3">
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <div class="d-flex flex-wrap gap-3">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-success p-2 rounded-circle">①</span>
                                                            <small class="text-muted">Ressources > Dettes = Trésorerie
                                                                Positive</small>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-danger p-2 rounded-circle">②</span>
                                                            <small class="text-muted">Ressources < Dettes=Trésorerie
                                                                    Négative</small>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-info p-2 rounded-circle">③</span>
                                                            <small class="text-muted">Taux de couverture ≥ 50% = Situation
                                                                Satisfaisante</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="window.location.reload();">
                                                        <iconify-icon icon="solar:refresh-bold"></iconify-icon>
                                                        Actualiser
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                // Initialize popovers for stock details
                $(document).ready(function() {
                    $('[data-bs-toggle="popover"]').popover({
                        trigger: 'hover',
                        placement: 'left',
                        html: true
                    });
                });
            </script>
        @endpush
        {{-- ═══════════════════════════════════════════════════════════════════════
             ÉCHÉANCES : Chèques / Traites / Virements (fournisseur / client) avec dates
        ═══════════════════════════════════════════════════════════════════════ --}}
        <p class="section-title">Chèques / Traites / Virements · Échéances</p>
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table align-middle table-custom mb-0" style="font-size:.8rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="font-size:.75rem;">Instrument</th>
                                        <th style="font-size:.75rem;">Sens</th>
                                        <th style="font-size:.75rem;">Tiers</th>
                                        <th style="font-size:.75rem;">Référence</th>
                                        <th class="text-end" style="font-size:.75rem;">Montant (DH)</th>
                                        <th style="font-size:.75rem;">Échéance</th>
                                        <th style="font-size:.75rem;">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($echeances as $e)
                                        @php
                                            $isOverdue = $e['date'] && $e['date']->isPast() && !$e['date']->isToday();
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge {{ $e['instrument'] === 'Chèque' ? 'bg-primary' : 'bg-info' }}"
                                                    style="font-size:.65rem;">{{ $e['instrument'] }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $e['sens'] === 'Client' ? 'bg-success text-success' : 'bg-danger text-danger' }}"
                                                    style="font-size:.65rem;">{{ $e['sens'] }}</span>
                                            </td>
                                            <td class="fw-semibold">{{ $e['party'] }}</td>
                                            <td class="text-muted" style="font-size:.72rem;">{{ $e['reference'] }}</td>
                                            <td class="text-end fw-bold">{{ number_format($e['amount'], 2, ',', ' ') }}</td>
                                            <td class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                                @if ($e['date'])
                                                    {{ $e['date']->format('d/m/Y') }}
                                                    @if ($isOverdue)
                                                        <iconify-icon icon="solar:danger-triangle-bold"
                                                            class="ms-1"></iconify-icon>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td><span class="badge bg-secondary"
                                                    style="font-size:.65rem;">{{ ucfirst($e['status']) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-3 text-muted small">Aucune échéance en
                                                attente</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════
             4. STOCK CRITIQUE - Mobile Responsive
        ═══════════════════════════════════════════════════════════════════════ --}}
        <p class="section-title">Stock Actuel · Matière Première / Produit Fini</p>
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0" style="font-size: 1rem;">
                                <iconify-icon icon="solar:box-minimalistic-bold" class="text-warning me-2"></iconify-icon>
                                Stock Matières
                            </h5>
                            <a href="{{ route('raw-materials.index') }}"
                                class="btn btn-sm btn-outline-secondary">Voir</a>
                        </div>

                        @if ($lowStockMaterials->count() > 0)
                            <div class="alert alert-warning py-2 mb-3" style="font-size:.75rem;">
                                <iconify-icon icon="solar:danger-triangle-bold"></iconify-icon>
                                {{ $lowStockMaterials->count() }} matière(s) sous seuil
                            </div>
                            @foreach ($lowStockMaterials as $material)
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                    <div>
                                        <span class="fw-semibold small">{{ $material->material_name }}</span>
                                        <small class="d-block text-muted"
                                            style="font-size: 0.7rem;">{{ $material->material_code }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span
                                            class="stock-badge-{{ $material->current_stock <= 0 ? 'critical' : 'warning' }}"
                                            style="font-size: 0.7rem;">
                                            {{ number_format($material->current_stock, 2, ',', '.') }}
                                            {{ $material->unit_of_measure }}
                                        </span>
                                        <small class="d-block text-muted mt-1" style="font-size: 0.65rem;">Min:
                                            {{ $material->min_stock_level }}</small>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <iconify-icon icon="solar:check-circle-linear" class="fs-2 text-success"></iconify-icon>
                                <p class="text-muted mb-0 mt-2 small">Stock matières correct</p>
                            </div>
                        @endif

                        <div class="border-top pt-2 mt-2">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted" style="font-size: 0.7rem;">Valeur stock matières</small>
                                <small class="fw-semibold"
                                    style="font-size: 0.7rem;">{{ number_format($stats['total_material_value'], 0) }}
                                    DH</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0" style="font-size: 1rem;">
                                <iconify-icon icon="solar:box-bold" class="text-danger me-2"></iconify-icon>
                                Stock Produits
                            </h5>
                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">Voir</a>
                        </div>

                        @if ($lowStockProducts->count() > 0)
                            <div class="alert alert-danger py-2 mb-3" style="font-size:.75rem;">
                                <iconify-icon icon="solar:danger-triangle-bold"></iconify-icon>
                                {{ $lowStockProducts->count() }} produit(s) stock critique
                            </div>
                            @foreach ($lowStockProducts as $product)
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                    <div>
                                        <span class="fw-semibold small">{{ $product->product_name }}</span>
                                        <small class="d-block text-muted"
                                            style="font-size: 0.7rem;">{{ $product->product_code }}</small>
                                        @if (isset($product->famille_name))
                                            <span class="badge bg-light"
                                                style="font-size:.6rem;color:black !important;">{{ $product->famille_name }}</span>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <span class="stock-badge-critical" style="font-size: 0.7rem;">
                                            {{ number_format($product->available_stock ?? $product->current_stock, 0) }}
                                        </span>
                                        <small class="d-block text-muted mt-1" style="font-size: 0.65rem;">Min:
                                            {{ $product->min_stock_level }}</small>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <iconify-icon icon="solar:check-circle-linear" class="fs-2 text-success"></iconify-icon>
                                <p class="text-muted mb-0 mt-2 small">Stock produits correct</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════
             5. PRODUCTION DU JOUR - Mobile Optimized
        ═══════════════════════════════════════════════════════════════════════ --}}
        <p class="section-title">Production</p>
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <div class="col-md-4">
                <div
                    class="card text-center kpi-card h-100 {{ $stats['production_yield'] < 80 ? 'border-warning' : '' }}">
                    <div class="card-body p-3 py-md-4">
                        <div class="bg-success-subtle rounded-circle p-2 d-inline-flex mb-2">
                            <iconify-icon icon="solar:box-bold" class="text-success"></iconify-icon>
                        </div>
                        <h5 class="mb-2" style="font-size: 1rem;"><a href="{{ route('production-output.index') }}"
                                class="text-reset text-decoration-none stretched-link">Production du Jour</a></h5>
                        <div class="row g-0 mt-2">
                            <div class="col-6 border-end">
                                <h3 class="text-success mb-0" style="font-size: 1.3rem;">
                                    {{ number_format($stats['today_qty_produced']) }}</h3>
                                <small class="text-muted" style="font-size: 0.7rem;">Unités</small>
                            </div>
                            <div class="col-6">
                                <h3 class="text-info mb-0" style="font-size: 1.3rem;">{{ $stats['today_volume_m3'] }}
                                </h3>
                                <small class="text-muted" style="font-size: 0.7rem;">m³</small>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top">
                            <div class="d-flex justify-content-between mb-1" style="font-size:.7rem;">
                                <span>Rendement</span>
                                <strong
                                    class="{{ $stats['production_yield'] >= 90 ? 'text-success' : ($stats['production_yield'] >= 70 ? 'text-warning' : 'text-danger') }}">{{ $stats['production_yield'] }}%</strong>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar {{ $stats['production_yield'] >= 90 ? 'bg-success' : ($stats['production_yield'] >= 70 ? 'bg-warning' : 'bg-danger') }}"
                                    style="width:{{ $stats['production_yield'] }}%"></div>
                            </div>
                        </div>
                        @if ($stats['production_objective'] > 0)
                            <div class="mt-2 pt-2 border-top">
                                <div class="d-flex justify-content-between mb-1" style="font-size:.7rem;">
                                    <span>Objectif ({{ number_format($stats['production_objective']) }} u.)</span>
                                    <strong>{{ $stats['production_progress'] }}%</strong>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-primary"
                                        style="width:{{ $stats['production_progress'] }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <iconify-icon icon="solar:box-outline" class="text-secondary"></iconify-icon>
                            <a href="{{ route('production-orders.index') }}"
                                class="card-title mb-0 text-reset text-decoration-none" style="font-size: 0.95rem;">Ordres Récents</a>
                        </div>
                        @forelse($recentProductionOrders as $order)
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                <div>
                                    <a href="{{ route('production-orders.show', $order->order_id) }}"
                                        class="fw-semibold text-decoration-none small">{{ $order->order_number }}</a>
                                    <small class="d-block text-muted"
                                        style="font-size: 0.7rem;">{{ $order->product->product_name ?? 'N/A' }}</small>
                                </div>
                                <div class="text-end">
                                    {!! $order->status_badge !!}
                                    <small class="d-block text-muted mt-1"
                                        style="font-size: 0.7rem;">{{ number_format($order->quantity_to_produce) }}
                                        u.</small>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3 small">Aucun ordre récent</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <iconify-icon icon="solar:settings-bold" class="text-warning"></iconify-icon>
                            <a href="{{ route('machines.index') }}"
                                class="card-title mb-0 text-reset text-decoration-none" style="font-size: 0.95rem;">Machines</a>
                        </div>
                        @if ($machinesInMaint->count() > 0)
                            @foreach ($machinesInMaint as $machine)
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                    <div>
                                        <span class="fw-semibold small">{{ $machine->name }}</span>
                                        <small class="d-block text-muted"
                                            style="font-size: 0.7rem;">{{ $machine->model ?? '' }}</small>
                                    </div>
                                    {!! $machine->status_badge !!}
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <iconify-icon icon="solar:check-circle-linear" class="fs-2 text-success"></iconify-icon>
                                <p class="text-muted mb-0 mt-2 small">Toutes les machines actives</p>
                            </div>
                        @endif
                        <div class="mt-2 border-top pt-2">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h6 class="mb-0 text-danger" style="font-size: 0.9rem;">
                                        {{ $stats['machines_breakdown'] }}</h6>
                                    <small class="text-muted" style="font-size: 0.65rem;">En panne</small>
                                </div>
                                <div class="col-4">
                                    <h6 class="mb-0 text-success" style="font-size: 0.9rem;">
                                        {{ $stats['late_production_orders'] == 0 ? '✓' : $stats['late_production_orders'] }}
                                    </h6>
                                    <small class="text-muted" style="font-size: 0.65rem;">Retards</small>
                                </div>
                                <div class="col-4">
                                    <h6 class="mb-0 text-primary" style="font-size: 0.9rem;">
                                        {{ $stats['in_progress_orders'] }}</h6>
                                    <small class="text-muted" style="font-size: 0.65rem;">En cours</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════
             6. COMMANDES & PAIEMENTS - Mobile Responsive
        ═══════════════════════════════════════════════════════════════════════ --}}
        <p class="section-title">Ventes & Paiements</p>
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <a href="{{ route('sales.orders.index') }}"
                                class="card-title mb-0 text-reset text-decoration-none" style="font-size: 1rem;">Ventes Récentes</a>
                            <a href="{{ route('sales.orders.index') }}" class="btn btn-sm btn-outline-primary">Voir</a>
                        </div>
                        <div class="overflow-auto-mobile" style="max-height:340px; overflow-y:auto;">
                            @forelse($recentSalesOrders as $order)
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <div>
                                        <a href="{{ route('sales.orders.show', $order->order_id) }}"
                                            class="fw-bold text-decoration-none small">{{ $order->order_number }}</a>
                                        <div class="small text-muted" style="font-size: 0.7rem;">
                                            {{ $order->client->display_name ?? 'N/A' }}</div>
                                        <div class="small text-muted" style="font-size: 0.65rem;">
                                            {{ $order->order_date ? $order->order_date->format('d/m/Y') : '' }}</div>
                                    </div>
                                    <div class="text-end">
                                        <span
                                            class="fw-bold text-success small">{{ number_format($order->final_amount, 0) }}
                                            DH</span>
                                        <div class="mt-1">{!! $order->status_badge !!}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <iconify-icon icon="solar:cart-linear" class="fs-2 text-muted"></iconify-icon>
                                    <p class="text-muted mb-0 mt-2 small">Aucune commande récente</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <a href="{{ route('sales.situation.index') }}"
                            class="card-title mb-2 d-block text-reset text-decoration-none" style="font-size: 1rem;">Suivi Paiements</a>
                        <p class="text-muted" style="font-size:.75rem;">
                            {{ number_format($stats['completed_payments'], 0) }} DH encaissés</p>

                        <div class="chart-container" style="height: 180px;">
                            <div id="paymentMethodChart"></div>
                        </div>

                        <div class="mt-3 border-top pt-2">
                            <div class="d-flex justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="round-10 bg-success rounded-circle d-block"></span>
                                    <small style="font-size: 0.7rem;">Payées</small>
                                </div>
                                <small class="fw-semibold"
                                    style="font-size: 0.7rem;">{{ $paymentStatusStats['paid']['count'] }} ·
                                    {{ number_format($paymentStatusStats['paid']['amount'], 0) }} DH</small>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="round-10 bg-warning rounded-circle d-block"></span>
                                    <small style="font-size: 0.7rem;">En attente</small>
                                </div>
                                <small class="fw-semibold"
                                    style="font-size: 0.7rem;">{{ $paymentStatusStats['pending']['count'] }} ·
                                    {{ number_format($paymentStatusStats['pending']['amount'], 0) }} DH</small>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="round-10 bg-info rounded-circle d-block"></span>
                                    <small style="font-size: 0.7rem;">Avances</small>
                                </div>
                                <small class="fw-semibold"
                                    style="font-size: 0.7rem;">{{ $paymentStatusStats['partial']['count'] }} ·
                                    {{ number_format($paymentStatusStats['partial']['amount'], 0) }} DH</small>
                            </div>
                            @if ($paymentStatusStats['overdue']['count'] > 0)
                                <div class="d-flex justify-content-between p-2 bg-danger-subtle rounded mt-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="round-10 bg-danger rounded-circle d-block"></span>
                                        <small class="fw-semibold text-danger" style="font-size: 0.7rem;">En
                                            retard</small>
                                    </div>
                                    <small class="fw-bold text-danger"
                                        style="font-size: 0.7rem;">{{ $paymentStatusStats['overdue']['count'] }} ·
                                        {{ number_format($paymentStatusStats['overdue']['amount'], 0) }} DH</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════
             7. ANALYSE PRODUITS - Responsive Tabs
        ═══════════════════════════════════════════════════════════════════════ --}}
        <p class="section-title">Analyse Produits · Rotation / Stock Mort</p>
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0" style="font-size: 1rem;">Performances</h5>
                        </div>

                        <ul class="nav nav-tabs theme-tab gap-1 gap-md-2 flex-nowrap mb-3 overflow-auto" role="tablist"
                            style="overflow-x: auto;">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#tab-top-products" role="tab">
                                    <div class="d-flex align-items-center gap-1 gap-md-2">
                                        <iconify-icon icon="solar:trending-up-bold" class="fs-6"></iconify-icon>
                                        <span class="d-none d-sm-inline">Plus Vendus · Rotation</span>
                                        <span class="d-inline d-sm-none">Rotation</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-low-products" role="tab">
                                    <div class="d-flex align-items-center gap-1 gap-md-2">
                                        <iconify-icon icon="solar:trending-down-bold" class="fs-6"></iconify-icon>
                                        <span class="d-none d-sm-inline">Moins Vendus · Stock Mort</span>
                                        <span class="d-inline d-sm-none">Stock Mort</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-clients" role="tab">
                                    <div class="d-flex align-items-center gap-1 gap-md-2">
                                        <iconify-icon icon="solar:users-group-rounded-linear"
                                            class="fs-6"></iconify-icon>
                                        <span class="d-none d-sm-inline">Top Clients</span>
                                        <span class="d-inline d-sm-none">Clients</span>
                                    </div>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active" id="tab-top-products" role="tabpanel">
                                <div class="table-responsive-stack">
                                    <table class="table align-middle table-custom mb-0">
                                        <thead>
                                            <tr>
                                                <th class="fw-normal ps-0" style="font-size: 0.75rem;">Produit</th>
                                                <th class="fw-normal" style="font-size: 0.75rem;">Qté</th>
                                                <th class="fw-normal" style="font-size: 0.75rem;">CA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($topSellingProducts as $product)
                                                <tr>
                                                    <td class="ps-0">
                                                        <div class="d-flex align-items-center gap-1 gap-md-2">
                                                            <div class="bg-primary-subtle rounded p-1 flex-shrink-0">
                                                                <iconify-icon icon="solar:box-linear"
                                                                    class="text-primary"></iconify-icon>
                                                            </div>
                                                            <div>
                                                                <div class="fw-semibold" style="font-size: 0.8rem;">
                                                                    {{ $product->product_name }}</div>
                                                                <small class="text-muted"
                                                                    style="font-size: 0.65rem;">{{ $product->product_code }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><span class="badge bg-primary rounded-pill"
                                                            style="font-size: 0.7rem;">{{ number_format($product->total_quantity) }}</span>
                                                    </td>
                                                    <td><strong
                                                            style="font-size: 0.8rem;">{{ number_format($product->total_revenue, 0) }}
                                                            DH</strong></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-3 text-muted small">Aucune
                                                        donnée</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab-low-products" role="tabpanel">
                                <div class="table-responsive-stack">
                                    <table class="table align-middle table-custom mb-0">
                                        <thead>
                                            <tr>
                                                <th class="fw-normal ps-0" style="font-size: 0.75rem;">Produit</th>
                                                <th class="fw-normal" style="font-size: 0.75rem;">Qté</th>
                                                <th class="fw-normal" style="font-size: 0.75rem;">CA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($lowSellingProducts as $product)
                                                <tr>
                                                    <td class="ps-0">
                                                        <div class="d-flex align-items-center gap-1 gap-md-2">
                                                            <div class="bg-warning-subtle rounded p-1 flex-shrink-0">
                                                                <iconify-icon icon="solar:box-linear"
                                                                    class="text-warning"></iconify-icon>
                                                            </div>
                                                            <div>
                                                                <div class="fw-semibold" style="font-size: 0.8rem;">
                                                                    {{ $product->product_name }}</div>
                                                                <small class="text-muted"
                                                                    style="font-size: 0.65rem;">{{ $product->product_code }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><span class="badge bg-warning text-dark rounded-pill"
                                                            style="font-size: 0.7rem;">{{ number_format($product->total_quantity) }}</span>
                                                    </td>
                                                    <td><strong class="text-warning"
                                                            style="font-size: 0.8rem;">{{ number_format($product->total_revenue, 0) }}
                                                            DH</strong></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-3 text-muted small">Aucune
                                                        donnée</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab-clients" role="tabpanel">
                                <div class="table-responsive-stack">
                                    <table class="table align-middle table-custom mb-0">
                                        <thead>
                                            <tr>
                                                <th class="fw-normal ps-0" style="font-size: 0.75rem;">Client</th>
                                                <th class="fw-normal" style="font-size: 0.75rem;">Type</th>
                                                <th class="fw-normal" style="font-size: 0.75rem;">Achats</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($topClients as $client)
                                                <tr>
                                                    <td class="ps-0">
                                                        <div class="d-flex align-items-center gap-1 gap-md-2">
                                                            <div class="bg-success-subtle rounded p-1 flex-shrink-0">
                                                                <iconify-icon icon="solar:user-circle-linear"
                                                                    class="text-success"></iconify-icon>
                                                            </div>
                                                            <div>
                                                                <div class="fw-semibold" style="font-size: 0.8rem;">
                                                                    {{ $client->display_name }}</div>
                                                                <small class="text-muted"
                                                                    style="font-size: 0.65rem;">{{ $client->client_type_label }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><span class="badge bg-info"
                                                            style="font-size: 0.7rem;">{{ $client->person_type_label }}</span>
                                                    </td>
                                                    <td><strong
                                                            style="font-size: 0.8rem;">{{ number_format($client->total_purchases, 0) }}
                                                            DH</strong></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-3 text-muted small">Aucun
                                                        client</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <a href="{{ route('clients.index') }}"
                            class="card-title mb-3 d-block text-reset text-decoration-none" style="font-size: 1rem;">Répartition Clients</a>
                        <div class="bg-primary bg-opacity-10 rounded overflow-hidden mb-3">
                            <div class="p-2 p-md-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-muted small">Total Clients</span>
                                    <h4 class="mb-0">{{ $stats['total_clients'] }}</h4>
                                </div>
                            </div>
                            <div id="client-distribution-chart" style="height: 60px;"></div>
                        </div>
                        @foreach ($clientTypeStats as $type)
                            <div class="d-flex align-items-center justify-content-between pb-2 mb-1 border-bottom">
                                <div>
                                    <span class="fw-medium small">{{ $type['label'] }}</span>
                                    <span class="d-block text-muted mt-1"
                                        style="font-size:.65rem;">{{ $type['percentage'] }}%</span>
                                </div>
                                <div class="text-end">
                                    <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">{{ $type['count'] }}</h6>
                                    <span style="font-size:.65rem; color:{{ $type['color'] }};">actifs</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════
             8. ACTIVITÉS RÉCENTES + APERÇU RAPIDE
        ═══════════════════════════════════════════════════════════════════════ --}}
        <div class="row g-2 g-md-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body p-3">
                        <h5 class="card-title mb-3" style="font-size: 1rem;">Activités Récentes</h5>
                        <ul class="timeline-widget mb-0 position-relative" style="max-height:320px;overflow-y:auto;">
                            @foreach ($recentProductionOrders->take(3) as $order)
                                <li class="timeline-item d-flex position-relative overflow-hidden">
                                    <div class="text-muted flex-shrink-0 text-end me-2"
                                        style="min-width:52px;font-size:.7rem;">
                                        <div class="fw-semibold text-dark">{{ $order->created_at->format('d/m') }}</div>
                                        {{ $order->created_at->format('H:i') }}
                                    </div>
                                    <div class="timeline-badge-wrap d-flex flex-column align-items-center">
                                        <span class="timeline-badge bg-primary flex-shrink-0 mt-1"></span>
                                        <span class="timeline-badge-border d-block flex-shrink-0"></span>
                                    </div>
                                    <div class="ms-2" style="font-size: 0.75rem;">Ordre production
                                        <strong>{{ $order->order_number }}</strong> créé
                                    </div>
                                </li>
                            @endforeach
                            @forelse($recentSalesOrders->take(4) as $order)
                                <li class="timeline-item d-flex position-relative overflow-hidden">
                                    <div class="text-muted flex-shrink-0 text-end me-2"
                                        style="min-width:52px;font-size:.7rem;">
                                        <div class="fw-semibold text-dark">{{ $order->created_at->format('d/m') }}</div>
                                        {{ $order->created_at->format('H:i') }}
                                    </div>
                                    <div class="timeline-badge-wrap d-flex flex-column align-items-center">
                                        <span class="timeline-badge bg-success flex-shrink-0 mt-1"></span>
                                        <span class="timeline-badge-border d-block flex-shrink-0"></span>
                                    </div>
                                    <div class="ms-2" style="font-size: 0.75rem;">
                                        <strong>{{ $order->order_number }}</strong>
                                        <span class="text-success">{{ number_format($order->final_amount, 0) }} DH</span>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center py-3 text-muted small">Aucune activité récente</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body p-3">
                        <h5 class="card-title mb-3" style="font-size: 1rem;">Aperçu Rapide</h5>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-2 p-md-3 bg-primary-subtle rounded text-center">
                                    <h4 class="text-primary mb-1" style="font-size: 1.2rem;">
                                        {{ $stats['active_clients'] }}</h4>
                                    <small class="text-muted" style="font-size: 0.65rem;">Clients actifs</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 p-md-3 bg-success-subtle rounded text-center">
                                    <h4 class="text-success mb-1" style="font-size: 1.2rem;">
                                        {{ $stats['completed_production_orders'] }}</h4>
                                    <small class="text-muted" style="font-size: 0.65rem;">Ordres terminés</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 p-md-3 bg-warning-subtle rounded text-center">
                                    <h4 class="text-warning mb-1" style="font-size: 1.2rem;">
                                        {{ $stats['in_progress_orders'] }}</h4>
                                    <small class="text-muted" style="font-size: 0.65rem;">En production</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div
                                    class="p-2 p-md-3 {{ $stats['late_production_orders'] > 0 ? 'bg-danger-subtle' : 'bg-light' }} rounded text-center">
                                    <h4 class="{{ $stats['late_production_orders'] > 0 ? 'text-danger' : 'text-muted' }} mb-1"
                                        style="font-size: 1.2rem;">{{ $stats['late_production_orders'] }}</h4>
                                    <small class="text-muted" style="font-size: 0.65rem;">Retards prod.</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-2 p-md-3 border rounded">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-medium small">Encaissements</span>
                                        <span
                                            class="fw-bold text-success small">{{ number_format($stats['completed_payments'], 0) }}
                                            DH</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-medium small">En attente</span>
                                        <span
                                            class="fw-bold text-warning small">{{ number_format($paymentStatusStats['pending']['amount'], 0) }}
                                            DH</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.45.2/apexcharts.min.js"></script>
    <script>
        $(document).ready(function() {
            // Function to check if mobile
            const isMobile = window.innerWidth < 768;

            // ── Revenue + Expenses chart (bascule Mois / Jour) ───────────────────
            const caData = {
                month: {
                    sales: @json($monthlySalesData),
                    expenses: @json($monthlyExpensesData),
                    labels: @json($monthsLabels)
                },
                day: {
                    sales: @json($dailySalesData),
                    expenses: @json($dailyExpensesData),
                    labels: @json($dailyLabels)
                }
            };
            const revenueChart = new ApexCharts(document.querySelector("#revenue-chart"), {
                series: [{
                        name: 'Ventes (DH)',
                        data: caData.month.sales
                    },
                    {
                        name: 'Dépenses (DH)',
                        data: caData.month.expenses
                    }
                ],
                chart: {
                    type: 'bar',
                    height: isMobile ? 200 : 255,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit',
                    foreColor: '#adb0bb',
                    offsetX: isMobile ? -5 : -10
                },
                colors: ['var(--bs-primary)', 'var(--bs-danger)'],
                plotOptions: {
                    bar: {
                        columnWidth: isMobile ? '60%' : '40%',
                        borderRadius: [4],
                        borderRadiusApplication: 'end',
                        groupPadding: 0.1
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    show: false
                },
                grid: {
                    show: true,
                    borderColor: 'rgba(0,0,0,.05)',
                    xaxis: {
                        lines: {
                            show: false
                        }
                    },
                    yaxis: {
                        lines: {
                            show: true
                        }
                    },
                    padding: {
                        top: 0,
                        bottom: 0,
                        right: 0
                    }
                },
                xaxis: {
                    categories: caData.month.labels,
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    labels: {
                        style: {
                            fontSize: isMobile ? '10px' : '12px',
                            colors: '#adb0bb'
                        },
                        rotate: isMobile ? -45 : 0
                    }
                },
                yaxis: {
                    tickAmount: isMobile ? 3 : 4,
                    labels: {
                        formatter: val => val.toFixed(0)
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: val => val.toFixed(0) + ' DH'
                    }
                }
            });
            revenueChart.render();

            // Toggle Mois / Jour
            document.querySelectorAll('#caModeMonth, #caModeDay').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const mode = this.dataset.mode;
                    document.getElementById('caModeMonth').classList.toggle('active', mode === 'month');
                    document.getElementById('caModeDay').classList.toggle('active', mode === 'day');
                    revenueChart.updateOptions({
                        series: [{
                                name: 'Ventes (DH)',
                                data: caData[mode].sales
                            },
                            {
                                name: 'Dépenses (DH)',
                                data: caData[mode].expenses
                            }
                        ],
                        xaxis: {
                            categories: caData[mode].labels
                        }
                    });
                });
            });

            // ── Client distribution sparkline ────────────────────────────────────
            new ApexCharts(document.querySelector("#client-distribution-chart"), {
                chart: {
                    type: 'area',
                    height: isMobile ? 50 : 70,
                    sparkline: {
                        enabled: true
                    },
                    fontFamily: 'inherit'
                },
                series: [{
                    name: 'Clients',
                    color: 'var(--bs-primary)',
                    data: @json($clientMonthlyGrowth)
                }],
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        opacityFrom: 0.2,
                        opacityTo: 0.05,
                        stops: [100]
                    }
                },
                markers: {
                    size: 0
                },
                tooltip: {
                    theme: 'dark',
                    x: {
                        show: false
                    }
                }
            }).render();

            // ── Total income sparkline ────────────────────────────────────────────
            if (document.querySelector("#total-income")) {
                new ApexCharts(document.querySelector("#total-income"), {
                    chart: {
                        type: 'line',
                        height: isMobile ? 35 : 40,
                        sparkline: {
                            enabled: true
                        },
                        fontFamily: 'inherit'
                    },
                    series: [{
                        name: 'Paiements',
                        color: 'var(--bs-danger)',
                        data: [30, 25, 35, 20, 30, 40, 35]
                    }],
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    markers: {
                        size: 0
                    },
                    tooltip: {
                        theme: 'dark',
                        x: {
                            show: false
                        }
                    }
                }).render();
            }

            // ── Payment method pie chart ─────────────────────────────────────────
            new ApexCharts(document.querySelector("#paymentMethodChart"), {
                series: @json($paymentMethodStats->pluck('total')),
                chart: {
                    type: 'pie',
                    height: isMobile ? 160 : 200,
                    toolbar: {
                        show: false
                    }
                },
                colors: @json($paymentMethodStats->pluck('color')),
                labels: @json($paymentMethodStats->pluck('label')),
                dataLabels: {
                    enabled: !isMobile,
                    style: {
                        fontSize: isMobile ? '8px' : '10px'
                    },
                    formatter: (val, opts) => isMobile ? '' : opts.w.globals.labels[opts.seriesIndex]
                },
                legend: {
                    show: false
                },
                stroke: {
                    show: false
                },
                tooltip: {
                    y: {
                        formatter: val => val.toFixed(0) + ' DH'
                    }
                }
            }).render();

            // Handle resize events to update charts
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    location.reload();
                }, 250);
            });
        });
    </script>
@endpush
