@extends('layouts.app')

@section('title', 'Statistiques Production Employés')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Statistiques Production par Employé</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('employees.index') }}">
                                        Employés
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Statistiques Production
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('employees.production-statistics') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="year" class="form-label">Année</label>
                        <select name="year" id="year" class="form-select">
                            @foreach ($availableYears as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="type" class="form-label">Type de production</label>
                        <select name="type" id="type" class="form-select">
                            <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Tous les types (volume total)</option>
                            <option value="type2" {{ $type === 'type2' ? 'selected' : '' }}>Sous-bloc (Bloc → Sous-bloc)</option>
                            <option value="type3" {{ $type === 'type3' ? 'selected' : '' }}>Produit fini (Sous-bloc → Produit fini)</option>
                            <option value="type5" {{ $type === 'type5' ? 'selected' : '' }}>Chute (Chute → Produit fini)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('employees.production-statistics') }}" class="btn btn-light">Réinitialiser</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-primary-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary rounded-circle p-3 text-white">
                                <i class="fas fa-cubes fs-6"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ number_format($grandTotals['total'], 3, ',', '.') }} m³</h4>
                                <span class="text-muted">Volume Total {{ $year }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-info-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-info rounded-circle p-3 text-white">
                                <i class="fas fa-th-large fs-6"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ number_format($grandTotals['sous_bloc'], 3, ',', '.') }} m³</h4>
                                <span class="text-muted">Sous-blocs (Découpage)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-success-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success rounded-circle p-3 text-white">
                                <i class="fas fa-box fs-6"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ number_format($grandTotals['produit_fini'], 3, ',', '.') }} m³</h4>
                                <span class="text-muted">Produits finis</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-warning-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning rounded-circle p-3 text-white">
                                <i class="fas fa-recycle fs-6"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ number_format($grandTotals['chute'], 3, ',', '.') }} m³</h4>
                                <span class="text-muted">Chutes → Produits finis</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employees Table -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <h5 class="card-title mb-0">Production par employé — {{ $year }}</h5>
                    <span class="text-muted small">Volumes en m³</span>
                </div>

                <ul class="nav nav-pills mb-4" id="statsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="chart-tab" data-bs-toggle="pill" data-bs-target="#chart-pane"
                            type="button" role="tab" aria-controls="chart-pane" aria-selected="true">
                            <i class="fas fa-chart-bar me-1"></i> Graphique
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="table-tab" data-bs-toggle="pill" data-bs-target="#table-pane"
                            type="button" role="tab" aria-controls="table-pane" aria-selected="false">
                            <i class="fas fa-table me-1"></i> Tableau
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="statsTabContent">
                    <!-- Chart Tab -->
                    <div class="tab-pane fade show active" id="chart-pane" role="tabpanel" aria-labelledby="chart-tab" tabindex="0">
                        @if (count($employees) > 0)
                            <div class="mb-4">
                                <h6 class="fw-semibold mb-2">Volume par employé et par mois</h6>
                                <div id="monthlyTrendChart"></div>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-2">Volume total par employé</h6>
                                <div id="employeeTotalChart"></div>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-chart-bar fs-1 mb-3 d-block"></i>
                                Aucune production enregistrée pour {{ $year }}.
                            </div>
                        @endif
                    </div>

                    <!-- Table Tab -->
                    <div class="tab-pane fade" id="table-pane" role="tabpanel" aria-labelledby="table-tab" tabindex="0">
                @forelse ($employees as $employee)
                    <div class="mb-4 border rounded p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-user me-2 text-primary"></i>{{ $employee['full_name'] }}
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                @if ($type === 'all' || $type === 'type2')
                                    <span class="badge bg-info-subtle text-info">Sous-blocs: {{ number_format($employee['totals']['sous_bloc'], 3, ',', '.') }} m³</span>
                                @endif
                                @if ($type === 'all' || $type === 'type3')
                                    <span class="badge bg-success-subtle text-success">Produits finis: {{ number_format($employee['totals']['produit_fini'], 3, ',', '.') }} m³</span>
                                @endif
                                @if ($type === 'all' || $type === 'type5')
                                    <span class="badge bg-warning-subtle text-warning">Chutes: {{ number_format($employee['totals']['chute'], 3, ',', '.') }} m³</span>
                                @endif
                                <span class="badge bg-primary">Total: {{ number_format($employee['totals']['total'], 3, ',', '.') }} m³</span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mois</th>
                                        @if ($type === 'all' || $type === 'type2')
                                            <th class="text-end">Sous-blocs (m³)</th>
                                        @endif
                                        @if ($type === 'all' || $type === 'type3')
                                            <th class="text-end">Produits finis (m³)</th>
                                        @endif
                                        @if ($type === 'all' || $type === 'type5')
                                            <th class="text-end">Chutes (m³)</th>
                                        @endif
                                        <th class="text-end">Total (m³)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($months as $num => $label)
                                        @php $m = $employee['months'][$num]; @endphp
                                        @if ($m['total'] > 0)
                                            <tr>
                                                <td>{{ $label }}</td>
                                                @if ($type === 'all' || $type === 'type2')
                                                    <td class="text-end">{{ number_format($m['sous_bloc'], 3, ',', '.') }}</td>
                                                @endif
                                                @if ($type === 'all' || $type === 'type3')
                                                    <td class="text-end">{{ number_format($m['produit_fini'], 3, ',', '.') }}</td>
                                                @endif
                                                @if ($type === 'all' || $type === 'type5')
                                                    <td class="text-end">{{ number_format($m['chute'], 3, ',', '.') }}</td>
                                                @endif
                                                <td class="text-end fw-semibold">{{ number_format($m['total'], 3, ',', '.') }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light fw-bold">
                                        <td>Total {{ $year }}</td>
                                        @if ($type === 'all' || $type === 'type2')
                                            <td class="text-end">{{ number_format($employee['totals']['sous_bloc'], 3, ',', '.') }}</td>
                                        @endif
                                        @if ($type === 'all' || $type === 'type3')
                                            <td class="text-end">{{ number_format($employee['totals']['produit_fini'], 3, ',', '.') }}</td>
                                        @endif
                                        @if ($type === 'all' || $type === 'type5')
                                            <td class="text-end">{{ number_format($employee['totals']['chute'], 3, ',', '.') }}</td>
                                        @endif
                                        <td class="text-end">{{ number_format($employee['totals']['total'], 3, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-chart-bar fs-1 mb-3 d-block"></i>
                        Aucune production enregistrée pour {{ $year }}.
                    </div>
                @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const type = @json($type);
            const months = @json(array_values($months));
            const employees = @json($employees);
            const monthlyTotals = @json(array_values($monthlyTotals));

            const seriesConfig = [];
            if (type === 'all' || type === 'type2') {
                seriesConfig.push({ key: 'sous_bloc', name: 'Sous-blocs', color: '#0dcaf0' });
            }
            if (type === 'all' || type === 'type3') {
                seriesConfig.push({ key: 'produit_fini', name: 'Produits finis', color: '#198754' });
            }
            if (type === 'all' || type === 'type5') {
                seriesConfig.push({ key: 'chute', name: 'Chutes', color: '#ffc107' });
            }

            const currency = (val) => (val || 0).toLocaleString('fr-FR', {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            }) + ' m³';

            let totalChart = null;
            let trendChart = null;

            function renderCharts() {
                if (!employees.length) return;

                // ── Volume by employee (stacked bar) ──────────────────────────
                if (!totalChart) {
                    const categories = employees.map(e => e.full_name);
                    const totalSeries = seriesConfig.map(s => ({
                        name: s.name,
                        data: employees.map(e => Number(e.totals[s.key].toFixed(3)))
                    }));

                    totalChart = new ApexCharts(document.querySelector("#employeeTotalChart"), {
                        chart: {
                            type: 'bar',
                            height: Math.max(320, categories.length * 46),
                            stacked: seriesConfig.length > 1,
                            toolbar: { show: true }
                        },
                        series: totalSeries,
                        colors: seriesConfig.map(s => s.color),
                        plotOptions: {
                            bar: { horizontal: true, borderRadius: 4, barHeight: '70%' }
                        },
                        dataLabels: { enabled: false },
                        xaxis: {
                            categories: categories,
                            labels: { formatter: (v) => Number(v).toLocaleString('fr-FR') }
                        },
                        legend: { position: 'top' },
                        tooltip: { y: { formatter: currency } }
                    });
                    totalChart.render();
                }

                // ── Heatmap: X = employé, Y = mois, couleur = volume ──────────
                if (!trendChart) {
                    const categories = employees.map(e => e.full_name);
                    // One series per month; reversed so Janvier is on top of the Y axis
                    const trendSeries = months.map((label, idx) => ({
                        name: label,
                        data: employees.map(e => ({
                            x: e.full_name,
                            y: Number(e.months[idx + 1].total.toFixed(3))
                        }))
                    })).reverse();

                    trendChart = new ApexCharts(document.querySelector("#monthlyTrendChart"), {
                        chart: {
                            type: 'heatmap',
                            height: 480,
                            toolbar: { show: true }
                        },
                        series: trendSeries,
                        plotOptions: {
                            heatmap: {
                                radius: 2,
                                enableShades: false,
                                colorScale: {
                                    ranges: [
                                        { from: 0, to: 0, color: '#eef1f6', name: '0' },
                                        { from: 0.001, to: 5, color: '#38b000', name: '0 - 5' },
                                        { from: 5, to: 20, color: '#ffbe0b', name: '5 - 20' },
                                        { from: 20, to: 50, color: '#fb8500', name: '20 - 50' },
                                        { from: 50, to: 999999, color: '#d00000', name: '50+' }
                                    ]
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: (v) => v > 0 ? Number(v).toLocaleString('fr-FR') : '',
                            style: { fontSize: '10px', colors: ['#111'] }
                        },
                        xaxis: {
                            type: 'category',
                            categories: categories,
                            title: { text: 'Employé' }
                        },
                        yaxis: { title: { text: 'Mois' } },
                        tooltip: { y: { formatter: currency } }
                    });
                    trendChart.render();
                }
            }

            renderCharts();

            // Trigger a resize on tab switch so ApexCharts recomputes its
            // dimensions without re-rendering (which would duplicate the chart)
            document.getElementById('chart-tab').addEventListener('shown.bs.tab', function() {
                window.dispatchEvent(new Event('resize'));
            });
        });
    </script>
@endpush
