@extends('layouts.app')

@section('title', 'Suivi Pointage Personnel')

@section('content')
    <div class="pointage-wrap">

        {{-- Header breadcrumb --}}
        <div class="card card-body py-2 mb-3">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-calendar-check me-2 text-primary"></i>Suivi Pointage Personnel
                </h5>
                <nav aria-label="breadcrumb" class="ms-auto">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted text-decoration-none">
                                <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            <span class="badge fw-medium fs-2 bg-primary text-primary">Présences</span>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        {{-- Controls bar --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex align-items-center gap-3 flex-wrap">

                    <button class="btn btn-primary btn-sm px-3" id="btn-calculer">
                        <i class="fas fa-calculator me-1"></i>Calculer
                    </button>

                    <div class="vr"></div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold small text-muted">Année :</span>
                        <select id="sel-year" class="form-select form-select-sm" style="width:88px">
                            @for ($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold small text-muted">Mois :</span>
                        @php $monthNames = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']; @endphp
                        <select id="sel-month" class="form-select form-select-sm" style="width:128px">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ $monthNames[$m] }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="vr"></div>

                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <span class="badge bg-light text-dark border"
                            style="color:black !important">01/{{ sprintf('%02d', $month) }}/{{ $year }}</span>
                        <i class="fas fa-arrow-right fa-xs"></i>
                        <span class="badge bg-light text-dark border"
                            style="color:black !important">{{ sprintf('%02d', $daysInMonth) }}/{{ sprintf('%02d', $month) }}/{{ $year }}</span>
                    </div>

                    <div class="ms-auto fw-bold fs-5 text-primary">
                        {{ $monthNames[$month] }}/{{ $year }}
                    </div>

                </div>
            </div>
        </div>

        {{-- Legend --}}
        <div class="d-flex gap-3 mb-2 align-items-center flex-wrap">
            <span class="legend-pill legend-absent"><i class="fas fa-circle fa-xs me-1"></i>0h — Absent</span>
            <span class="legend-pill legend-partial"><i class="fas fa-circle fa-xs me-1"></i>1 à 7h — Partiel</span>
            <span class="legend-pill legend-present"><i class="fas fa-circle fa-xs me-1"></i>8h — Présent</span>
            <span class="legend-pill legend-sunday"><i class="fas fa-circle fa-xs me-1"></i>Dimanche</span>
            <span class="legend-pill legend-today"><i class="fas fa-circle fa-xs me-1"></i>Aujourd'hui</span>
        </div>

        {{-- Table card --}}
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="ptg-scroll">
                    <table class="ptg-table" id="ptg-table">
                        <thead>
                            <tr>
                                <th class="th-nom th-sticky-left">Nom</th>
                                @php
                                    $dayAbbr = ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'];
                                    $todayDate = \Carbon\Carbon::today();
                                @endphp
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $dt = \Carbon\Carbon::create($year, $month, $d);
                                        $dow = $dt->dayOfWeek; // 0=Sun
                                        $isSun = $dow === 0;
                                        $isToday = $dt->isSameDay($todayDate);
                                    @endphp
                                    <th class="th-day text-center
                                    {{ $isSun ? 'th-sunday' : '' }}
                                    {{ $isToday ? 'th-today' : '' }}"
                                        data-date="{{ $dt->format('Y-m-d') }}" title="{{ $dt->format('d/m/Y') }}">
                                        {{ $dayAbbr[$dow] }}-{{ $d }}
                                    </th>
                                @endfor
                                <th class="th-total th-sticky-right text-center">Total H</th>
                                <th class="th-rate th-sticky-right text-center">Prix/H</th>
                                <th class="th-total-dh th-sticky-right text-center">Total</th>
                                <th class="th-avance th-sticky-right text-center">Avance</th>
                                <th class="th-reste th-sticky-right text-center">Reste</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $colTotals = array_fill(1, $daysInMonth, 0);
                                $grandTotal = 0;
                                $grandTotalDH = 0;
                                $grandAvance = 0;
                                $grandReste = 0;
                            @endphp

                            @foreach ($employees as $emp)
                                @php $rowTotal = 0; $rate = (float) ($emp->hourly_salary ?? 0); @endphp
                                <tr data-emp="{{ $emp->employee_id }}" data-rate="{{ $rate }}">
                                    <td class="td-nom td-sticky-left">{{ $emp->full_name }}</td>

                                    @for ($d = 1; $d <= $daysInMonth; $d++)
                                        @php
                                            $dt = \Carbon\Carbon::create($year, $month, $d);
                                            $dow = $dt->dayOfWeek;
                                            $isSun = $dow === 0;
                                            $isToday = $dt->isSameDay($todayDate);
                                            $hrs = $attendanceData[$emp->employee_id][$d] ?? null;

                                            if ($hrs !== null) {
                                                $rowTotal += $hrs;
                                                $colTotals[$d] += $hrs;
                                            }

                                            $tdCls = 'td-day';
                                            if ($isSun) {
                                                $tdCls .= ' td-sunday';
                                            } elseif ($hrs !== null) {
                                                if ($hrs == 0) {
                                                    $tdCls .= ' td-absent';
                                                } elseif ($hrs < 8) {
                                                    $tdCls .= ' td-partial';
                                                }
                                                // 8+ = no extra class (white/normal)
                                            }
                                            if ($isToday) {
                                                $tdCls .= ' td-today';
                                            }
                                        @endphp
                                        <td class="{{ $tdCls }}" data-emp="{{ $emp->employee_id }}"
                                            data-date="{{ $dt->format('Y-m-d') }}">
                                            <input type="number" class="cell-inp"
                                                value="{{ $hrs !== null ? (intval($hrs) == $hrs ? intval($hrs) : $hrs) : '' }}"
                                                min="0" max="24" step="1"
                                                data-emp="{{ $emp->employee_id }}"
                                                data-date="{{ $dt->format('Y-m-d') }}" data-orig="{{ $hrs ?? '' }}"
                                                placeholder="">
                                        </td>
                                    @endfor

                                    @php
                                        $grandTotal += $rowTotal;
                                        $avance = (float) ($advances[$emp->employee_id] ?? 0);
                                        $totalDH = $rowTotal * $rate;
                                        $reste = $totalDH - $avance;
                                        $grandTotalDH += $totalDH;
                                        $grandAvance += $avance;
                                        $grandReste += $reste;
                                    @endphp
                                    <td class="td-total td-sticky-right text-center fw-bold">
                                        {{ $rowTotal > 0 ? intval($rowTotal) : '' }}
                                    </td>
                                    <td class="td-rate td-sticky-right text-center">
                                        {{ number_format($rate, 2, ',', '.') }}
                                    </td>
                                    <td class="td-total-dh td-sticky-right text-center fw-bold">
                                        {{ number_format($totalDH, 2, ',', '.') }}
                                    </td>
                                    <td class="td-avance td-sticky-right text-center">
                                        <input type="number" class="avance-inp" min="0" step="0.01"
                                            value="{{ $avance > 0 ? $avance : '' }}"
                                            data-emp="{{ $emp->employee_id }}" data-orig="{{ $avance }}"
                                            placeholder="0">
                                    </td>
                                    <td class="td-reste td-sticky-right text-center fw-bold">
                                        {{ number_format($reste, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr class="tr-somme">
                                <td class="td-nom td-sticky-left fw-bold text-center">Somme</td>
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $dt = \Carbon\Carbon::create($year, $month, $d);
                                        $dow = $dt->dayOfWeek;
                                        $isSun = $dow === 0;
                                    @endphp
                                    <td class="td-day text-center fw-bold {{ $isSun ? 'td-sunday' : '' }}"
                                        data-sum-date="{{ $dt->format('Y-m-d') }}">
                                        {{ $colTotals[$d] > 0 ? intval($colTotals[$d]) : '' }}
                                    </td>
                                @endfor
                                <td class="td-total td-sticky-right text-center fw-bold" id="grand-total">
                                    {{ $grandTotal > 0 ? intval($grandTotal) : '' }}
                                </td>
                                <td class="td-rate td-sticky-right text-center fw-bold">—</td>
                                <td class="td-total-dh td-sticky-right text-center fw-bold" id="grand-total-dh">
                                    {{ number_format($grandTotalDH, 2, ',', '.') }}
                                </td>
                                <td class="td-avance td-sticky-right text-center fw-bold" id="grand-avance">
                                    {{ number_format($grandAvance, 2, ',', '.') }}
                                </td>
                                <td class="td-reste td-sticky-right text-center fw-bold" id="grand-reste">
                                    {{ number_format($grandReste, 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Save toast --}}
    <div id="save-toast" class="save-toast" style="display:none"></div>
@endsection

@push('stylesheets')
    <style>
        /* ── Wrapper ── */
        .pointage-wrap {
            max-width: 100% !important;
            padding: 0 12px;
        }

        /* ── Scrollable container ── */
        .ptg-scroll {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 72vh;
        }

        /* ── Table base ── */
        .ptg-table {
            border-collapse: collapse;
            font-size: 12px;
            min-width: 100%;
            table-layout: fixed;
        }

        .ptg-table th,
        .ptg-table td {
            border: 1px solid #c8cdd2;
            padding: 0;
            white-space: nowrap;
        }

        /* ── Header ── */
        .ptg-table thead th {
            background: #2d3e50;
            color: #fff;
            font-weight: 600;
            font-size: 11px;
            padding: 5px 2px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .th-nom {
            width: 170px;
            min-width: 170px;
            text-align: left;
            padding-left: 8px !important;
            z-index: 20 !important;
        }

        .th-day {
            width: 40px;
            min-width: 40px;
            max-width: 40px;
        }

        .th-total {
            width: 54px;
            min-width: 54px;
            background: #1a2734 !important;
            z-index: 20 !important;
        }

        .th-rate,
        .th-total-dh,
        .th-avance,
        .th-reste {
            background: #1a2734 !important;
            z-index: 20 !important;
        }

        .th-rate,
        .td-rate {
            width: 60px;
            min-width: 60px;
        }

        .th-total-dh,
        .td-total-dh {
            width: 78px;
            min-width: 78px;
        }

        .th-avance,
        .td-avance {
            width: 78px;
            min-width: 78px;
        }

        .th-reste,
        .td-reste {
            width: 78px;
            min-width: 78px;
        }

        /* ── Stack sticky-right columns (Reste is rightmost) ── */
        .th-reste, .td-reste { right: 0; }
        .th-avance, .td-avance { right: 78px; }
        .th-total-dh, .td-total-dh { right: 156px; }
        .th-rate, .td-rate { right: 234px; }
        .th-total, .td-total { right: 294px; }

        .th-sunday {
            background: #546e7a !important;
        }

        .th-today {
            background: #2e7d32 !important;
        }

        /* ── Sticky columns ── */
        .th-sticky-left,
        .td-sticky-left {
            position: sticky;
            left: 0;
            z-index: 5;
        }

        .th-sticky-right,
        .td-sticky-right {
            position: sticky;
            right: 0;
            z-index: 5;
        }

        .ptg-table thead .th-sticky-left,
        .ptg-table thead .th-sticky-right {
            z-index: 15;
        }

        /* ── Body cells ── */
        .td-nom {
            text-align: left;
            padding: 3px 6px;
            font-size: 12px;
            font-weight: 500;
            background: #fff;
            min-width: 170px;
        }

        .td-day {
            width: 40px;
            min-width: 40px;
            max-width: 40px;
            height: 26px;
            text-align: center;
            vertical-align: middle;
            padding: 0;
            background: #fff;
        }

        .td-total {
            min-width: 54px;
            padding: 3px 4px;
            font-size: 12px;
            background: #f1f3f5;
        }

        .td-rate,
        .td-total-dh,
        .td-reste {
            padding: 3px 4px;
            font-size: 12px;
            background: #f1f3f5;
        }

        .td-avance {
            padding: 0;
            font-size: 12px;
            background: #fff8e1;
        }

        .avance-inp {
            display: block;
            width: 100%;
            height: 26px;
            border: none;
            background: transparent;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            padding: 0;
            outline: none;
            color: #ef6c00;
            -moz-appearance: textfield;
        }

        .avance-inp::-webkit-outer-spin-button,
        .avance-inp::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .avance-inp:focus {
            background: rgba(255, 255, 255, 0.9);
            outline: 2px solid #ef6c00;
            border-radius: 2px;
            position: relative;
            z-index: 1;
        }

        /* ── Color coding ── */
        .td-absent {
            background-color: #e53935 !important;
        }

        .td-partial {
            background-color: #fdd835 !important;
        }

        .td-sunday {
            background-color: #b0bec5 !important;
        }

        .td-today {
            outline: 2px solid #2e7d32;
            outline-offset: -2px;
        }

        /* ── Row zebra ── */
        .ptg-table tbody tr:nth-child(even) .td-nom,
        .ptg-table tbody tr:nth-child(even) .td-total {
            background: #f8f9fa;
        }

        .ptg-table tbody tr:nth-child(even) .td-day:not(.td-absent):not(.td-partial):not(.td-sunday) {
            background: #f8f9fa;
        }

        .ptg-table tbody tr:hover .td-nom {
            background: #e3f2fd !important;
        }

        /* ── Number inputs ── */
        .cell-inp {
            display: block;
            width: 100%;
            height: 26px;
            border: none;
            background: transparent;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            padding: 0;
            outline: none;
            color: inherit;
            -moz-appearance: textfield;
        }

        .cell-inp::-webkit-outer-spin-button,
        .cell-inp::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .cell-inp:focus {
            background: rgba(255, 255, 255, 0.9);
            outline: 2px solid #1976d2;
            border-radius: 2px;
            position: relative;
            z-index: 1;
        }

        .td-absent .cell-inp {
            color: #000;
        }

        .td-partial .cell-inp {
            color: #212529;
        }

        /* ── Somme row ── */
        .tr-somme td {
            background: #dde3e9 !important;
            font-size: 12px;
            padding: 5px 3px;
        }

        .tr-somme .td-sunday {
            background: #b0bec5 !important;
        }

        .tr-somme .td-sticky-left {
            background: #cfd8dc !important;
        }

        .tr-somme .td-sticky-right {
            background: #cfd8dc !important;
        }

        /* ── Legend ── */
        .legend-pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .2px;
        }

        .legend-absent {
            background: #e53935;
            color: #fff;
        }

        .legend-partial {
            background: #fdd835;
            color: #212529;
        }

        .legend-present {
            background: #f0f0f0;
            color: #212529;
            border: 1px solid #ccc;
        }

        .legend-sunday {
            background: #b0bec5;
            color: #fff;
        }

        .legend-today {
            background: #2e7d32;
            color: #fff;
        }

        /* ── Save toast ── */
        .save-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 9px 18px;
            border-radius: 24px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            background: #2e7d32;
            z-index: 9999;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .25);
            pointer-events: none;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const SAVE_URL = '{{ route('attendance.save-hours-calendar') }}';
        const DELETE_CELL_URL = '{{ route('attendance.delete-cell-calendar') }}';
        const SAVE_AVANCE_URL = '{{ route('attendance.save-avance') }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const PTG_YEAR = {{ $year }};
        const PTG_MONTH = {{ $month }};

        /* ─── Navigate months ─── */
        $('#btn-calculer').on('click', function() {
            const y = $('#sel-year').val();
            const m = $('#sel-month').val();
            window.location.href = '{{ route('attendance.index') }}?year=' + y + '&month=' + m;
        });

        /* ─── Color a td based on numeric value ─── */
        function applyTdColor(td, numVal, rawVal) {
            td.removeClass('td-absent td-partial');
            if (rawVal === '' || rawVal === null || isNaN(numVal)) return;
            if (numVal === 0) td.addClass('td-absent');
            else if (numVal > 0 && numVal < 8) td.addClass('td-partial');
            // 8+ = no extra class (normal white)
        }

        /* ─── Live color on typing ─── */
        $(document).on('input', '.cell-inp', function() {
            const val = $(this).val();
            const numVal = parseFloat(val);
            const td = $(this).closest('td');
            const tr = $(this).closest('tr');
            applyTdColor(td, numVal, val);
            refreshRowTotal(tr);
            refreshColTotal($(this).data('date'));
            refreshRowMoney(tr);
        });

        /* ─── Avance: live recalculation of Reste ─── */
        $(document).on('input', '.avance-inp', function() {
            refreshRowMoney($(this).closest('tr'));
        });

        /* ─── Auto-save on blur ─── */
        $(document).on('blur', '.cell-inp', function() {
            const rawVal = $(this).val().trim();
            const origVal = $(this).data('orig');
            const empId  = $(this).data('emp');
            const date   = $(this).data('date');
            const input  = $(this);

            if (rawVal === '') {
                // Cell was cleared — delete the record if there was one before
                if (origVal !== '' && origVal !== null && origVal !== undefined) {
                    deleteSingleCell(empId, date, input);
                }
                return;
            }

            const newNum  = parseFloat(rawVal);
            const origNum = parseFloat(origVal) || 0;
            if (isNaN(newNum) || newNum < 0 || newNum > 24) return;
            if (newNum === origNum) return;

            saveSingleCell(empId, date, newNum, input);
        });

        /* ─── Avance: auto-save on blur ─── */
        $(document).on('blur', '.avance-inp', function() {
            const rawVal = $(this).val().trim();
            const origVal = String($(this).data('orig'));

            if (rawVal === origVal) return;

            const amount = rawVal === '' ? 0 : parseFloat(rawVal);
            if (isNaN(amount) || amount < 0) return;

            const empId = $(this).data('emp');
            const input = $(this);

            saveAvance(empId, amount, input);
        });

        /* ─── Tab / Enter moves to next cell ─── */
        $(document).on('keydown', '.cell-inp', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const all = $('.cell-inp');
                const idx = all.index(this);
                if (idx + 1 < all.length) all.eq(idx + 1).focus();
            }
        });

        /* ─── AJAX save ─── */
        function saveSingleCell(empId, date, hours, inputEl) {
            showToast('<i class="fas fa-spinner fa-spin me-1"></i>Enregistrement…', '#1976d2');

            $.ajax({
                url: SAVE_URL,
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    employee_id: empId,
                    date: date,
                    hours: hours
                },
                success: function(res) {
                    if (res.success) {
                        inputEl.data('orig', hours); // store as number for consistent comparison
                        showToast('<i class="fas fa-check me-1"></i>Enregistré', '#2e7d32');
                    } else {
                        showToast('<i class="fas fa-times me-1"></i>Erreur', '#c62828');
                    }
                },
                error: function() {
                    showToast('<i class="fas fa-times me-1"></i>Erreur serveur', '#c62828');
                }
            });
        }

        /* ─── AJAX delete cell (when cleared) ─── */
        function deleteSingleCell(empId, date, inputEl) {
            showToast('<i class="fas fa-spinner fa-spin me-1"></i>Enregistrement…', '#1976d2');

            $.ajax({
                url: DELETE_CELL_URL,
                type: 'DELETE',
                data: {
                    _token: CSRF_TOKEN,
                    employee_id: empId,
                    date: date
                },
                success: function(res) {
                    if (res.success) {
                        inputEl.data('orig', '');
                        showToast('<i class="fas fa-check me-1"></i>Enregistré', '#2e7d32');
                    } else {
                        showToast('<i class="fas fa-times me-1"></i>Erreur', '#c62828');
                    }
                },
                error: function() {
                    showToast('<i class="fas fa-times me-1"></i>Erreur serveur', '#c62828');
                }
            });
        }

        /* ─── AJAX save (avance) ─── */
        function saveAvance(empId, amount, inputEl) {
            showToast('<i class="fas fa-spinner fa-spin me-1"></i>Enregistrement…', '#1976d2');

            $.ajax({
                url: SAVE_AVANCE_URL,
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    employee_id: empId,
                    year: PTG_YEAR,
                    month: PTG_MONTH,
                    amount: amount
                },
                success: function(res) {
                    if (res.success) {
                        inputEl.data('orig', amount);
                        showToast('<i class="fas fa-check me-1"></i>Enregistré', '#2e7d32');
                    } else {
                        showToast('<i class="fas fa-times me-1"></i>Erreur', '#c62828');
                    }
                },
                error: function() {
                    showToast('<i class="fas fa-times me-1"></i>Erreur serveur', '#c62828');
                }
            });
        }

        /* ─── Format a number as money (fr-FR, 2 decimals) ─── */
        function fmtMoney(v) {
            return v.toLocaleString('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        /* ─── Recalculate Total(DH)/Reste for a row, then the grand totals ─── */
        function refreshRowMoney(tr) {
            const rate = parseFloat(tr.data('rate')) || 0;
            let hoursSum = 0;
            tr.find('.cell-inp').each(function() {
                const v = parseFloat($(this).val());
                if (!isNaN(v)) hoursSum += v;
            });
            const totalDH = hoursSum * rate;
            const avance = parseFloat(tr.find('.avance-inp').val()) || 0;
            const reste = totalDH - avance;

            tr.find('.td-total-dh').text(fmtMoney(totalDH));
            tr.find('.td-reste').text(fmtMoney(reste));

            refreshGrandMoney();
        }

        /* ─── Recalculate grand totals for Total(DH)/Avance/Reste ─── */
        function refreshGrandMoney() {
            let sumDH = 0, sumAvance = 0, sumReste = 0;

            $('#ptg-table tbody tr').each(function() {
                const tr = $(this);
                const rate = parseFloat(tr.data('rate')) || 0;
                let hoursSum = 0;
                tr.find('.cell-inp').each(function() {
                    const v = parseFloat($(this).val());
                    if (!isNaN(v)) hoursSum += v;
                });
                const totalDH = hoursSum * rate;
                const avance = parseFloat(tr.find('.avance-inp').val()) || 0;
                sumDH += totalDH;
                sumAvance += avance;
                sumReste += (totalDH - avance);
            });

            $('#grand-total-dh').text(fmtMoney(sumDH));
            $('#grand-avance').text(fmtMoney(sumAvance));
            $('#grand-reste').text(fmtMoney(sumReste));
        }

        /* ─── Recalculate row total ─── */
        function refreshRowTotal(tr) {
            let sum = 0;
            tr.find('.cell-inp').each(function() {
                const v = parseFloat($(this).val());
                if (!isNaN(v)) sum += v;
            });
            tr.find('.td-total').text(sum > 0 ? Math.round(sum) : '');
        }

        /* ─── Recalculate column total ─── */
        function refreshColTotal(date) {
            let sum = 0;
            $('tbody .cell-inp[data-date="' + date + '"]').each(function() {
                const v = parseFloat($(this).val());
                if (!isNaN(v)) sum += v;
            });
            $('tfoot td[data-sum-date="' + date + '"]').text(sum > 0 ? Math.round(sum) : '');
            refreshGrandTotal();
        }

        /* ─── Grand total (sum of all column totals) ─── */
        function refreshGrandTotal() {
            let grand = 0;
            $('tfoot td[data-sum-date]').each(function() {
                const v = parseInt($(this).text());
                if (!isNaN(v)) grand += v;
            });
            $('#grand-total').text(grand > 0 ? grand : '');
        }

        /* ─── Toast helper ─── */
        let toastTimer;

        function showToast(html, bg) {
            const el = $('#save-toast');
            el.html(html).css('background', bg).stop(true).fadeIn(150);
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => el.fadeOut(400), 2200);
        }
    </script>
@endpush
