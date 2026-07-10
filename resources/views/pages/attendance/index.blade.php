@extends('layouts.app')

@section('title', 'Gestion des Présences')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">

        {{-- Breadcrumb --}}
        <div class="card card-body py-3 mb-4">
            <div class="d-sm-flex align-items-center justify-space-between">
                <h4 class="mb-0 card-title"><i class="fas fa-user-clock me-2 text-primary"></i>Gestion des Présences</h4>
                <nav aria-label="breadcrumb" class="ms-auto">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item d-flex align-items-center">
                            <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                            </a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            <span class="badge fw-medium fs-2 bg-primary text-primary">Présences</span>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        {{-- Date toolbar --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-body py-3">
                <div class="row align-items-center g-2">
                    <div class="col-auto">
                        <button class="btn btn-outline-secondary btn-sm" id="prev-day">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                    <div class="col-auto">
                        <input type="date" id="attendance-date" class="form-control form-control-sm fw-bold"
                            value="{{ $today->format('Y-m-d') }}" style="min-width:160px">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-secondary btn-sm" id="next-day">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-primary btn-sm" id="today-btn">Aujourd'hui</button>
                    </div>
                    <div class="col-auto ms-2">
                        <button class="btn btn-primary btn-sm" id="load-attendance">
                            <i class="fas fa-sync-alt me-1"></i>Charger
                        </button>
                    </div>
                    <div class="col-auto ms-auto">
                        <button class="btn btn-success" id="save-attendance">
                            <i class="fas fa-save me-1"></i>Enregistrer les présences
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance card --}}
        <div class="card shadow-sm">
            <div class="card-header card-header-custom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-users me-2"></i>Liste des Présences
                    <span class="badge text-dark ms-2 fs-6" id="date-badge">{{ $today->format('d/m/Y') }}</span>
                </h5>
                <span class="badge text-dark" id="employee-count">0 employé(s)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" id="attendance-table">
                        <thead class="">
                            <tr>
                                <th class="text-center" style="width:40px">
                                    <input type="checkbox" id="select-all-employees" class="form-check-input">
                                </th>
                                <th class="text-center" style="width:40px">#</th>
                                <th style="width:170px">Employé</th>
                                <th style="min-width:260px">Horaires travaillés</th>
                                <th style="min-width:200px">Pauses</th>
                                <th class="text-center" style="width:100px">Total Heures</th>
                                <th class="text-center" style="width:150px">Statut</th>
                                <th style="min-width:160px">Motif</th>
                            </tr>
                        </thead>
                        <tbody id="attendance-tbody">
                            <tr id="loading-row">
                                <td colspan="7" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <div class="text-muted mt-2 small">Chargement des présences…</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <style>
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Time / break entry rows */
        .time-entry-row,
        .break-entry-row {
            display: flex;
            gap: 4px;
            align-items: center;
            margin-bottom: 5px;
        }

        .time-entry-row input,
        .break-entry-row input {
            width: 90px;
            font-size: 12px;
            padding: 3px 6px;
        }

        .add-time-btn,
        .add-break-btn {
            font-size: 11px;
            padding: 3px 8px;
        }

        .remove-entry-btn {
            padding: 2px 6px;
            font-size: 11px;
        }

        /* Total hours badge */
        .total-hours-badge {
            font-size: 13px;
            font-weight: 700;
            padding: 6px 10px;
            border-radius: 20px;
            letter-spacing: 0.3px;
        }

        /* Status select colouring */
        .status-select {
            font-size: 12px;
            min-width: 130px;
        }

        .status-select option[value="present"] {
            background: #d1fae5;
        }

        .status-select option[value="absent"] {
            background: #fee2e2;
        }

        .status-select option[value="late"] {
            background: #fef9c3;
        }

        .status-select option[value="half_day"] {
            background: #e0f2fe;
        }

        .status-select option[value="holiday"] {
            background: #ede9fe;
        }

        .status-select option[value="sick_leave"] {
            background: #f1f5f9;
        }

        .status-select option[value="paid_leave"] {
            background: #fce7f3;
        }

        .status-select option[value="unpaid_leave"] {
            background: #f5f5f5;
        }

        /* Row tinting by status */
        tr.row-present td {
            background: #f0fdf4 !important;
        }

        tr.row-absent td {
            background: #fff5f5 !important;
        }

        tr.row-late td {
            background: #fefce8 !important;
        }

        tr.row-half_day td {
            background: #f0f9ff !important;
        }

        tr.row-holiday td {
            background: #faf5ff !important;
        }

        tr.row-sick_leave td {
            background: #f8fafc !important;
        }

        tr.row-paid_leave td {
            background: #fdf4ff !important;
        }

        tr.row-unpaid_leave td {
            background: #fafafa !important;
        }

        .reason-input {
            font-size: 12px;
            resize: none;
        }

        .emp-name {
            font-weight: 600;
            font-size: 13px;
        }

        .emp-dept {
            font-size: 11px;
            color: #6c757d;
        }

        .section-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let currentDate = '{{ $today->format('Y-m-d') }}';
        const DEFAULT_START = '08:00';
        const DEFAULT_END = '20:00';

        $(document).ready(function() {
            loadAttendance();

            $('#load-attendance').click(function() {
                currentDate = $('#attendance-date').val();
                loadAttendance();
            });

            $('#attendance-date').change(function() {
                currentDate = $(this).val();
                loadAttendance();
            });

            $('#prev-day').click(function() {
                let d = new Date(currentDate + 'T00:00:00');
                d.setDate(d.getDate() - 1);
                currentDate = d.toISOString().split('T')[0];
                $('#attendance-date').val(currentDate);
                loadAttendance();
            });

            $('#next-day').click(function() {
                let d = new Date(currentDate + 'T00:00:00');
                d.setDate(d.getDate() + 1);
                currentDate = d.toISOString().split('T')[0];
                $('#attendance-date').val(currentDate);
                loadAttendance();
            });

            $('#today-btn').click(function() {
                currentDate = new Date().toISOString().split('T')[0];
                $('#attendance-date').val(currentDate);
                loadAttendance();
            });

            $('#save-attendance').click(saveAllAttendance);
        });

        /* ──────────────────────────────────────────────────────────────── */
        /* LOAD ATTENDANCE DATA
        /* ──────────────────────────────────────────────────────────────── */
        function loadAttendance() {
            $('#attendance-tbody').html(`
        <tr id="loading-row">
            <td colspan="8" class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="text-muted mt-2 small">Chargement…</div>
            </td>
        </tr>`);

            let parts = currentDate.split('-');
            let displayDate = parts[2] + '/' + parts[1] + '/' + parts[0];
            $('#date-badge').text(displayDate);

            $.ajax({
                url: '{{ route('attendance.get-by-date') }}',
                type: 'GET',
                data: {
                    date: currentDate
                },
                success: function(res) {
                    if (res.success) renderTable(res.data);
                },
                error: function() {
                    showToast('error', 'Erreur lors du chargement des présences');
                }
            });
        }

        /* ──────────────────────────────────────────────────────────────── */
        /* RENDER TABLE - CHECKBOXES START UNCHECKED
        /* ──────────────────────────────────────────────────────────────── */
        function renderTable(data) {
            let html = '';
            $('#employee-count').text(data.length + ' employé(s)');

            data.forEach(function(rec, idx) {
                let timeEntries = rec.time_entries || [];
                let breakEntries = rec.break_entries || [];
                let empId = rec.employee_id;

                // Check if attendance already exists and was marked as present
                // Only pre-fill if data was previously saved
                let hasExistingAttendance = rec.attendance_id !== null;
                let wasPresent = (rec.status === 'present' || rec.status === 'late' || rec.status === 'half_day');

                // On initial load, if there's NO existing attendance record, keep everything empty
                // If there IS existing attendance, show saved data
                let showDefaultSchedule = false;
                let initialStatus = 'present';
                let initialCheckboxState = false;

                if (hasExistingAttendance) {
                    // Data was previously saved, show saved state
                    initialCheckboxState = wasPresent;
                    initialStatus = rec.status;

                    if (wasPresent && timeEntries.length === 0) {
                        // If previously present but no time entries, add default
                        timeEntries = [{
                            check_in: DEFAULT_START,
                            check_out: DEFAULT_END
                        }];
                        showDefaultSchedule = true;
                    }
                } else {
                    // New day with no saved attendance - everything empty
                    initialStatus = 'present';
                    initialCheckboxState = false;
                    timeEntries = [];
                    breakEntries = [];
                }

                // If no time entries at all, add empty row
                if (timeEntries.length === 0) {
                    timeEntries = [{
                        check_in: '',
                        check_out: ''
                    }];
                }

                // Generate time entries HTML
                let timeHtml = `<div class="time-entries-container" data-employee="${empId}">`;
                timeEntries.forEach(function(e, i) {
                    timeHtml += generateTimeRow(empId, i, e.check_in || '', e.check_out || '');
                });
                timeHtml += `<button type="button" class="btn btn-outline-primary btn-sm add-time-btn mt-1" onclick="addTimeEntry(${empId})">
                        <i class="fas fa-plus me-1"></i>Ajouter
                     </button></div>`;

                // Generate break entries HTML
                let breakHtml = `<div class="break-entries-container" data-employee="${empId}">`;
                breakEntries.forEach(function(b, i) {
                    breakHtml += generateBreakRow(empId, i, b.start || '', b.end || '');
                });
                breakHtml += `<button type="button" class="btn btn-outline-warning btn-sm add-break-btn mt-1" onclick="addBreakEntry(${empId})">
                         <i class="fas fa-plus me-1"></i>Pause
                      </button></div>`;

                let hrs = parseFloat(rec.hours_worked) || 0;

                html += `
        <tr data-employee-id="${empId}" class="row-${initialStatus}">
            <td class="text-center" style="width:40px">
                <input type="checkbox" class="form-check-input employee-checkbox"
                       data-employee-id="${empId}" ${initialCheckboxState ? 'checked' : ''}>
            </td>
            <td class="text-center text-muted small" style="width:40px">${idx + 1}</td>
            <td style="width:170px">
                <div class="emp-name">${escapeHtml(rec.full_name)}</div>
                <div class="emp-dept"><i class="fas fa-building me-1"></i>${escapeHtml(rec.department || '-')}</div>
            </td>
            <td style="min-width:260px">
                <div class="section-label"><i class="fas fa-clock me-1"></i>Entrée / Sortie</div>
                ${timeHtml}
            </td>
            <td style="min-width:200px">
                <div class="section-label"><i class="fas fa-coffee me-1"></i>Pause</div>
                ${breakHtml}
            </td>
            <td class="text-center" style="width:100px">
                <span class="badge total-hours-badge ${getHoursBadgeClass(hrs)}" data-employee="${empId}">
                    ${formatMinutes(hrs * 60)}
                </span>
            </td>
            <td class="text-center" style="width:150px">
                <select class="form-select form-select-sm status-select" data-employee-id="${empId}"
                        onchange="handleStatusChange(${empId}, this.value)">
                    ${generateStatusOptions(initialStatus)}
                </select>
            </td>
            <td style="min-width:160px">
                <textarea class="form-control form-control-sm reason-input" data-employee="${empId}"
                          rows="2" placeholder="Motif…">${escapeHtml(rec.reason || '')}</textarea>
            </td>
        </tr>`;
            });

            $('#attendance-tbody').html(html);

            // Initially disable time inputs for unchecked checkboxes
            $('.employee-checkbox').each(function() {
                let empId = $(this).data('employee-id');
                let isChecked = $(this).is(':checked');
                let timeContainer = $(`.time-entries-container[data-employee="${empId}"]`);

                if (!isChecked) {
                    timeContainer.find('.time-in, .time-out').prop('disabled', true);
                } else {
                    timeContainer.find('.time-in, .time-out').prop('disabled', false);
                }
            });

            // Bind events
            bindTimeEntryEvents();
            bindCheckboxEvents();
            bindSelectAllEvents();

            // Initial calculation for all rows
            $('tr[data-employee-id]').each(function() {
                recalculateHours($(this).data('employee-id'));
            });
        }

        /* ──────────────────────────────────────────────────────────────── */
        /* GENERATE HTML HELPERS
        /* ──────────────────────────────────────────────────────────────── */
        function generateTimeRow(empId, idx, checkIn, checkOut) {
            return `<div class="time-entry-row" data-idx="${idx}">
        <input type="time" class="form-control form-control-sm time-in" value="${checkIn}" title="Arrivée">
        <span class="text-muted small">→</span>
        <input type="time" class="form-control form-control-sm time-out" value="${checkOut}" title="Départ">
        <button type="button" class="btn btn-outline-danger btn-sm remove-entry-btn"
                onclick="removeTimeEntry(${empId}, ${idx})">
            <i class="fas fa-times"></i>
        </button>
    </div>`;
        }

        function generateBreakRow(empId, idx, start, end) {
            return `<div class="break-entry-row" data-idx="${idx}">
        <input type="time" class="form-control form-control-sm break-start" value="${start}" title="Début pause">
        <span class="text-muted small">→</span>
        <input type="time" class="form-control form-control-sm break-end" value="${end}" title="Fin pause">
        <button type="button" class="btn btn-outline-danger btn-sm remove-entry-btn"
                onclick="removeBreakEntry(${empId}, ${idx})">
            <i class="fas fa-times"></i>
        </button>
    </div>`;
        }

        function generateStatusOptions(current) {
            const statuses = [
                ['present', 'Présent'],
                ['absent', 'Absent'],
                ['late', 'Retard'],
                ['half_day', 'Demi-journée'],
                ['holiday', 'Congé'],
                ['sick_leave', 'Arrêt maladie'],
                ['paid_leave', 'Congé payé'],
                ['unpaid_leave', 'Congé sans solde']
            ];

            return statuses.map(([value, label]) => {
                const selected = current === value ? 'selected' : '';
                return `<option value="${value}" ${selected}>${label}</option>`;
            }).join('');
        }

        function getHoursBadgeClass(hours) {
            if (hours <= 0) return 'bg-secondary';
            if (hours < 4) return 'bg-warning text-dark';
            if (hours < 7) return 'bg-info text-white';
            return 'bg-success';
        }

        function formatMinutes(totalMinutes) {
            totalMinutes = Math.max(0, Math.round(totalMinutes));
            let h = Math.floor(totalMinutes / 60);
            let m = totalMinutes % 60;
            return h + 'h ' + String(m).padStart(2, '0') + 'm';
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        /* ──────────────────────────────────────────────────────────────── */
        /* EVENT HANDLERS
        /* ──────────────────────────────────────────────────────────────── */
        function bindTimeEntryEvents() {
            $(document).off('change input', '.time-in, .time-out, .break-start, .break-end')
                .on('change input', '.time-in, .time-out, .break-start, .break-end', function() {
                    let empId = $(this).closest('tr').data('employee-id');
                    if (empId) recalculateHours(empId);
                });
        }

        function bindCheckboxEvents() {
            $(document).off('change', '.employee-checkbox')
                .on('change', '.employee-checkbox', function() {
                    let empId = $(this).data('employee-id');
                    let isChecked = $(this).is(':checked');
                    handleCheckboxChange(empId, isChecked);
                });
        }

        function bindSelectAllEvents() {
            $('#select-all-employees').off('change').on('change', function() {
                let isChecked = $(this).is(':checked');
                $('.employee-checkbox').prop('checked', isChecked).trigger('change');
            });
        }

        /* ──────────────────────────────────────────────────────────────── */
        /* CHECKBOX HANDLER - ONLY sets 8-20 schedule when CHECKED by user
        /* ──────────────────────────────────────────────────────────────── */
        window.handleCheckboxChange = function(empId, isChecked) {
            let statusSelect = $(`select.status-select[data-employee-id="${empId}"]`);
            let timeContainer = $(`.time-entries-container[data-employee="${empId}"]`);

            if (isChecked) {
                // User checked the box - set to present with default schedule
                statusSelect.val('present');

                // Clear existing and add default 8-20 schedule
                timeContainer.find('.time-entry-row').remove();
                timeContainer.find('.add-time-btn').before(generateTimeRow(empId, 0, DEFAULT_START, DEFAULT_END));
                renumberTimeEntries(empId);

                // Enable time inputs
                timeContainer.find('.time-in, .time-out').prop('disabled', false);

                showToast('success', '✓ Présent marqué - Horaire 8h-20h appliqué');
            } else {
                // User unchecked the box - set to absent
                statusSelect.val('absent');

                // Clear time entries
                timeContainer.find('.time-entry-row').remove();
                timeContainer.find('.add-time-btn').before(generateTimeRow(empId, 0, '', ''));
                renumberTimeEntries(empId);

                // Disable time inputs
                timeContainer.find('.time-in, .time-out').prop('disabled', true);

                showToast('info', '✗ Absent - Horaire effacé');
            }

            recalculateHours(empId);
            applyRowColor(empId, statusSelect.val());
        };

        /* ──────────────────────────────────────────────────────────────── */
        /* STATUS CHANGE HANDLER
        /* ──────────────────────────────────────────────────────────────── */
        window.handleStatusChange = function(empId, status) {
            let checkbox = $(`input.employee-checkbox[data-employee-id="${empId}"]`);
            let timeContainer = $(`.time-entries-container[data-employee="${empId}"]`);

            // Determine if status requires presence
            const isPresentStatus = (status === 'present' || status === 'late' || status === 'half_day');

            // Update checkbox based on status
            checkbox.prop('checked', isPresentStatus);

            if (isPresentStatus) {
                // Check if there are any time entries with values
                let hasValidTimeEntries = false;
                timeContainer.find('.time-entry-row').each(function() {
                    let checkIn = $(this).find('.time-in').val();
                    let checkOut = $(this).find('.time-out').val();
                    if (checkIn && checkOut) hasValidTimeEntries = true;
                });

                // Only add default schedule if no valid time entries exist
                if (!hasValidTimeEntries) {
                    timeContainer.find('.time-entry-row').remove();
                    timeContainer.find('.add-time-btn').before(generateTimeRow(empId, 0, DEFAULT_START, DEFAULT_END));
                    renumberTimeEntries(empId);
                }

                timeContainer.find('.time-in, .time-out').prop('disabled', false);
            } else {
                // Clear time entries for non-present statuses
                timeContainer.find('.time-entry-row').remove();
                timeContainer.find('.add-time-btn').before(generateTimeRow(empId, 0, '', ''));
                renumberTimeEntries(empId);
                timeContainer.find('.time-in, .time-out').prop('disabled', true);
            }

            recalculateHours(empId);
            applyRowColor(empId, status);
        };

        /* ──────────────────────────────────────────────────────────────── */
        /* TIME ENTRIES MANAGEMENT
        /* ──────────────────────────────────────────────────────────────── */
        window.addTimeEntry = function(empId) {
            let container = $(`.time-entries-container[data-employee="${empId}"]`);
            let idx = container.find('.time-entry-row').length;
            container.find('.add-time-btn').before(generateTimeRow(empId, idx, '', ''));
            renumberTimeEntries(empId);
            recalculateHours(empId);
        };

        window.removeTimeEntry = function(empId, idx) {
            let container = $(`.time-entries-container[data-employee="${empId}"]`);
            container.find(`.time-entry-row[data-idx="${idx}"]`).remove();

            // If no time entries left, add an empty one
            if (container.find('.time-entry-row').length === 0) {
                container.find('.add-time-btn').before(generateTimeRow(empId, 0, '', ''));
            }

            renumberTimeEntries(empId);
            recalculateHours(empId);
        };

        window.addBreakEntry = function(empId) {
            let container = $(`.break-entries-container[data-employee="${empId}"]`);
            let idx = container.find('.break-entry-row').length;
            container.find('.add-break-btn').before(generateBreakRow(empId, idx, '', ''));
            renumberBreakEntries(empId);
            recalculateHours(empId);
        };

        window.removeBreakEntry = function(empId, idx) {
            let container = $(`.break-entries-container[data-employee="${empId}"]`);
            container.find(`.break-entry-row[data-idx="${idx}"]`).remove();
            renumberBreakEntries(empId);
            recalculateHours(empId);
        };

        function renumberTimeEntries(empId) {
            let container = $(`.time-entries-container[data-employee="${empId}"]`);
            container.find('.time-entry-row').each(function(i) {
                $(this).attr('data-idx', i);
                $(this).find('.remove-entry-btn').attr('onclick', `removeTimeEntry(${empId}, ${i})`);
            });
        }

        function renumberBreakEntries(empId) {
            let container = $(`.break-entries-container[data-employee="${empId}"]`);
            container.find('.break-entry-row').each(function(i) {
                $(this).attr('data-idx', i);
                $(this).find('.remove-entry-btn').attr('onclick', `removeBreakEntry(${empId}, ${i})`);
            });
        }

        /* ──────────────────────────────────────────────────────────────── */
        /* HOURS CALCULATION
        /* ──────────────────────────────────────────────────────────────── */
        window.recalculateHours = function(empId) {
            let totalMinutes = 0;

            // Calculate work time
            $(`.time-entries-container[data-employee="${empId}"] .time-entry-row`).each(function() {
                let checkIn = $(this).find('.time-in').val();
                let checkOut = $(this).find('.time-out').val();

                if (checkIn && checkOut) {
                    let start = new Date('2000-01-01T' + checkIn + ':00');
                    let end = new Date('2000-01-01T' + checkOut + ':00');
                    if (end < start) end.setDate(end.getDate() + 1);
                    totalMinutes += (end - start) / 60000;
                }
            });

            // Subtract break time
            $(`.break-entries-container[data-employee="${empId}"] .break-entry-row`).each(function() {
                let breakStart = $(this).find('.break-start').val();
                let breakEnd = $(this).find('.break-end').val();

                if (breakStart && breakEnd) {
                    let start = new Date('2000-01-01T' + breakStart + ':00');
                    let end = new Date('2000-01-01T' + breakEnd + ':00');
                    if (end < start) end.setDate(end.getDate() + 1);
                    totalMinutes -= (end - start) / 60000;
                }
            });

            totalMinutes = Math.max(0, totalMinutes);
            let hours = totalMinutes / 60;

            let badge = $(`.total-hours-badge[data-employee="${empId}"]`);
            if (badge.length) {
                badge.text(formatMinutes(totalMinutes));
                badge.attr('class', 'badge total-hours-badge ' + getHoursBadgeClass(hours));
            }
        };

        /* ──────────────────────────────────────────────────────────────── */
        /* APPLY ROW COLOR
        /* ──────────────────────────────────────────────────────────────── */
        window.applyRowColor = function(empId, status) {
            let tr = $(`tr[data-employee-id="${empId}"]`);
            tr.removeClass(function(index, className) {
                return (className.match(/row-\S+/g) || []).join(' ');
            });
            tr.addClass('row-' + status);
        };

        /* ──────────────────────────────────────────────────────────────── */
        /* COLLECT DATA FOR SAVING
        /* ──────────────────────────────────────────────────────────────── */
        function collectTimeEntries(empId) {
            let entries = [];
            $(`.time-entries-container[data-employee="${empId}"] .time-entry-row`).each(function() {
                let checkIn = $(this).find('.time-in').val();
                let checkOut = $(this).find('.time-out').val();
                if (checkIn && checkOut) {
                    entries.push({
                        check_in: checkIn,
                        check_out: checkOut
                    });
                }
            });
            return entries;
        }

        function collectBreakEntries(empId) {
            let entries = [];
            $(`.break-entries-container[data-employee="${empId}"] .break-entry-row`).each(function() {
                let start = $(this).find('.break-start').val();
                let end = $(this).find('.break-end').val();
                if (start && end) {
                    entries.push({
                        start: start,
                        end: end
                    });
                }
            });
            return entries;
        }

        /* ──────────────────────────────────────────────────────────────── */
        /* SAVE ALL ATTENDANCE DATA
        /* ──────────────────────────────────────────────────────────────── */
        function saveAllAttendance() {
            let attendances = [];

            $('tr[data-employee-id]').each(function() {
                let empId = $(this).data('employee-id');
                let isChecked = $(this).find('.employee-checkbox').is(':checked');
                let status = $(this).find('.status-select').val();
                let reason = $(this).find('.reason-input').val();
                let timeEntries = collectTimeEntries(empId);
                let breakEntries = collectBreakEntries(empId);

                // If checkbox is checked but no time entries, add default 8-20
                if (isChecked && timeEntries.length === 0 && (status === 'present' || status === 'late' ||
                        status === 'half_day')) {
                    timeEntries = [{
                        check_in: DEFAULT_START,
                        check_out: DEFAULT_END
                    }];
                }

                // If checkbox is unchecked, force status to absent
                if (!isChecked && (status === 'present' || status === 'late' || status === 'half_day')) {
                    status = 'absent';
                    timeEntries = [];
                    breakEntries = [];
                }

                // Calculate total hours
                let totalMinutes = 0;
                timeEntries.forEach(function(entry) {
                    if (entry.check_in && entry.check_out) {
                        let start = new Date('2000-01-01T' + entry.check_in + ':00');
                        let end = new Date('2000-01-01T' + entry.check_out + ':00');
                        if (end < start) end.setDate(end.getDate() + 1);
                        totalMinutes += (end - start) / 60000;
                    }
                });

                breakEntries.forEach(function(breakEntry) {
                    if (breakEntry.start && breakEntry.end) {
                        let start = new Date('2000-01-01T' + breakEntry.start + ':00');
                        let end = new Date('2000-01-01T' + breakEntry.end + ':00');
                        if (end < start) end.setDate(end.getDate() + 1);
                        totalMinutes -= (end - start) / 60000;
                    }
                });

                attendances.push({
                    employee_id: empId,
                    time_entries: timeEntries,
                    break_entries: breakEntries,
                    hours_worked: Math.max(0, totalMinutes / 60),
                    status: status,
                    reason: reason
                });
            });

            let btn = $('#save-attendance');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Enregistrement…');

            $.ajax({
                url: '{{ route('attendance.mark-today') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    date: currentDate,
                    attendances: attendances
                },
                success: function(res) {
                    if (res.success) {
                        showToast('success', '✓ ' + res.message);
                        loadAttendance();
                    } else {
                        showToast('error', res.message);
                    }
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Erreur lors de l\'enregistrement';
                    showToast('error', message);
                },
                complete: function() {
                    btn.prop('disabled', false).html(
                        '<i class="fas fa-save me-1"></i>Enregistrer les présences');
                }
            });
        }

        /* ──────────────────────────────────────────────────────────────── */
        /* TOAST NOTIFICATIONS
        /* ──────────────────────────────────────────────────────────────── */
        function showToast(type, message) {
            let bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
            let icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

            let toast = $(`
        <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" data-bs-autohide="true" data-bs-delay="3000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas ${icon} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);

            $('#toast-container').append(toast);
            let bsToast = new bootstrap.Toast(toast[0]);
            bsToast.show();

            setTimeout(() => toast.remove(), 1500);
        }
    </script>
@endpush
