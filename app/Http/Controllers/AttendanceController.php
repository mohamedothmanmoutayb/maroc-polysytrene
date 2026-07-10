<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Rats\Zkteco\Lib\ZKTeco;

class AttendanceController extends Controller
{
    /**
     * Display attendance dashboard
     */
    public function index(Request $request)
    {

        $today = Carbon::today();

        if ($request->ajax()) {
            $date = $request->get('date', $today->format('Y-m-d'));
            $attendances = Attendance::with(['employee', 'marker'])
                ->whereDate('date', $date)
                ->get()
                ->keyBy('employee_id');

            return response()->json([
                'success' => true,
                'data' => $attendances,
                'date' => $date
            ]);
        }

        $employees = Employee::whereNull('resignation_date')->orderBy('full_name')->get();


        return view('pages.attendance.index', compact('employees', 'today'));
    }

    /**
     * Get attendance for a specific date
     */
    public function getByDate(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));

        $attendances = Attendance::with('employee')
            ->whereDate('date', $date)
            ->get()
            ->keyBy('employee_id');

        $employees = Employee::whereNull('resignation_date')
            ->orderBy('full_name')
            ->get()
            ->map(function ($employee) use ($attendances, $date) {
                $attendance = $attendances->get($employee->employee_id);
                return [
                    'employee_id' => $employee->employee_id,
                    'full_name' => $employee->full_name,
                    'department' => $employee->department,
                    'time_entries' => $attendance ? $attendance->time_entries : [],
                    'break_entries' => $attendance ? $attendance->break_entries : [],
                    'hours_worked' => $attendance ? $attendance->hours_worked : 0,
                    'status' => $attendance ? $attendance->status : 'present',
                    'reason' => $attendance ? $attendance->reason : '',
                    'attendance_id' => $attendance ? $attendance->attendance_id : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $employees,
            'date' => $date
        ]);
    }

    /**
     * Mark attendance with flexible time entries
     */
    public function markToday(Request $request)
    {
        $request->validate([
            'attendances' => 'required|array',
            'attendances.*.employee_id' => 'required|exists:employees,employee_id',
            'attendances.*.time_entries' => 'nullable|array',
            'attendances.*.break_entries' => 'nullable|array',
            'attendances.*.status' => 'required|in:present,absent,late,half_day,holiday,sick_leave,paid_leave,unpaid_leave',
            'attendances.*.reason' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $today = Carbon::today();

            foreach ($request->attendances as $data) {
                // Calculate total hours from time entries
                $totalMinutes = 0;
                $timeEntries = $data['time_entries'] ?? [];

                // If no time entries but status is present, add default 8-20 schedule
                if (empty($timeEntries) && in_array($data['status'], ['present', 'late', 'half_day'])) {
                    $timeEntries = [['check_in' => '08:00', 'check_out' => '20:00']];
                }

                foreach ($timeEntries as $entry) {
                    if (!empty($entry['check_in']) && !empty($entry['check_out'])) {
                        $checkIn = Carbon::parse($entry['check_in']);
                        $checkOut = Carbon::parse($entry['check_out']);

                        if ($checkOut < $checkIn) {
                            $checkOut->addDay();
                        }

                        $totalMinutes += $checkOut->diffInMinutes($checkIn);
                    }
                }

                $breakEntries = $data['break_entries'] ?? [];
                foreach ($breakEntries as $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        $start = Carbon::parse($break['start']);
                        $end = Carbon::parse($break['end']);

                        if ($end < $start) {
                            $end->addDay();
                        }

                        $totalMinutes -= $end->diffInMinutes($start);
                    }
                }

                $hoursWorked = round($totalMinutes / 60, 2);

                Attendance::updateOrCreate(
                    [
                        'employee_id' => $data['employee_id'],
                        'date' => $today,
                    ],
                    [
                        'time_entries' => $timeEntries,
                        'break_entries' => $breakEntries,
                        'hours_worked' => max(0, $hoursWorked),
                        'status' => $data['status'],
                        'reason' => $data['reason'] ?? null,
                        'marked_by' => Auth::id(),
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Présence enregistrée avec succès!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update attendance record
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'time_entries' => 'nullable|array',
            'break_entries' => 'nullable|array',
            'status' => 'required|in:present,absent,late,half_day,holiday,sick_leave,paid_leave,unpaid_leave',
            'reason' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $attendance = Attendance::findOrFail($id);

            // Calculate total hours
            $totalMinutes = 0;
            $timeEntries = $request->time_entries ?? [];

            foreach ($timeEntries as $entry) {
                if (!empty($entry['check_in']) && !empty($entry['check_out'])) {
                    $checkIn = Carbon::parse($entry['check_in']);
                    $checkOut = Carbon::parse($entry['check_out']);

                    if ($checkOut < $checkIn) {
                        $checkOut->addDay();
                    }

                    $totalMinutes += $checkOut->diffInMinutes($checkIn);
                }
            }

            $breakEntries = $request->break_entries ?? [];
            foreach ($breakEntries as $break) {
                if (!empty($break['start']) && !empty($break['end'])) {
                    $start = Carbon::parse($break['start']);
                    $end = Carbon::parse($break['end']);

                    if ($end < $start) {
                        $end->addDay();
                    }

                    $totalMinutes -= $end->diffInMinutes($start);
                }
            }

            $hoursWorked = round($totalMinutes / 60, 2);

            $attendance->update([
                'time_entries' => $timeEntries,
                'break_entries' => $breakEntries,
                'hours_worked' => max(0, $hoursWorked),
                'status' => $request->status,
                'reason' => $request->reason,
                'marked_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Présence mise à jour avec succès!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly report
     */
    public function monthlyReport(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);

        $employees = Employee::employedDuring($year, $month)->orderBy('full_name')->get();
        $report = [];

        foreach ($employees as $employee) {
            $stats = $employee->getAbsenceStatsForMonth($year, $month);
            $report[] = [
                'employee_id' => $employee->employee_id,
                'full_name' => $employee->full_name,
                'department' => $employee->department,
                'stats' => $stats,
                'absent_days' => $stats['absent'] + $stats['late'] + $stats['half_day'],
                'total_hours' => $stats['total_hours'],
            ];
        }

        if ($request->ajax()) {
            $rows = [];
            foreach ($report as $i => $row) {
                $totalMinutes = (int) round($row['total_hours'] * 60);
                $displayHours = intdiv($totalMinutes, 60) . 'h ' . str_pad($totalMinutes % 60, 2, '0', STR_PAD_LEFT) . 'm';
                $rows[] = [
                    'DT_RowIndex'  => $i + 1,
                    'full_name'    => $row['full_name'],
                    'department'   => $row['department'] ?? '-',
                    'present'      => $row['stats']['present'],
                    'absent'       => $row['stats']['absent'],
                    'late'         => $row['stats']['late'],
                    'half_day'     => $row['stats']['half_day'],
                    'leaves'       => $row['stats']['paid_leave'] + $row['stats']['sick_leave'],
                    'total_absent' => $row['absent_days'],
                    'total_hours'  => $displayHours,
                    'action'       => '<a href="' . route('attendance.employee.details', ['employeeId' => $row['employee_id']]) . '?year=' . $year . '&month=' . $month . '" class="btn btn-sm btn-info"><i class="fas fa-eye me-1"></i>Détails</a>',
                ];
            }

            return response()->json(['success' => true, 'data' => $rows]);
        }

        return view('pages.attendance.report', compact('year', 'month'));
    }

    /**
     * Get employee attendance details
     */
    public function employeeDetails($employeeId, Request $request)
    {
        $employee = Employee::findOrFail($employeeId);
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $stats = $employee->getAbsenceStatsForMonth($year, $month);
        $payment = $this->computeEmployeePayment($employee, $year, $month);

        return view('pages.attendance.employee-details', compact('employee', 'attendances', 'stats', 'year', 'month', 'payment'));
    }

    /**
     * Monthly payment summary (Prix/Heure, Total, Avance, Reste) for an employee
     */
    public function getEmployeePayment($employeeId, Request $request)
    {
        $employee = Employee::findOrFail($employeeId);
        $year = (int) $request->get('year', Carbon::now()->year);
        $month = (int) $request->get('month', Carbon::now()->month);

        return response()->json([
            'success' => true,
            'data'    => $this->computeEmployeePayment($employee, $year, $month),
        ]);
    }

    /**
     * Compute the monthly pay summary for an employee: hours worked × hourly rate,
     * minus the advance (avance) recorded for that month.
     */
    private function computeEmployeePayment(Employee $employee, $year, $month)
    {
        $rate = (float) ($employee->hourly_salary ?? 0);
        $totalHours = (float) Attendance::where('employee_id', $employee->employee_id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('hours_worked');

        $totalDH = $totalHours * $rate;

        $avance = (float) (EmployeeAdvance::where('employee_id', $employee->employee_id)
            ->where('year', $year)
            ->where('month', $month)
            ->value('amount') ?? 0);

        $reste = $totalDH - $avance;

        return [
            'year'        => $year,
            'month'       => $month,
            'rate'        => $rate,
            'total_hours' => $totalHours,
            'total_dh'    => $totalDH,
            'avance'      => $avance,
            'reste'       => $reste,
        ];
    }

    /**
     * Monthly pointage calendar (all employees × all days of month)
     */
    public function monthlyCalendar(Request $request)
    {

        $zk = new ZKTeco('192.168.1.13');
        $zk->connect();
        dd($zk);
        
        $year  = (int) $request->get('year',  Carbon::now()->year);
        $month = (int) $request->get('month', Carbon::now()->month);

        $employees   = Employee::employedDuring($year, $month)->orderBy('full_name')->get();
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        $rawAttendances = Attendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        // [employee_id][day_number] => hours_worked
        $attendanceData = [];
        foreach ($rawAttendances as $att) {
            $attendanceData[$att->employee_id][$att->date->day] = (float) $att->hours_worked;
        }

        // [employee_id] => amount avancé ce mois-ci
        $advances = EmployeeAdvance::where('year', $year)
            ->where('month', $month)
            ->pluck('amount', 'employee_id');

        return view('pages.attendance.monthly-calendar', compact(
            'employees',
            'year',
            'month',
            'daysInMonth',
            'attendanceData',
            'advances'
        ));
    }

    /**
     * Save/update the monthly advance (avance) for an employee
     */
    public function saveAvance(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'year'        => 'required|integer',
            'month'       => 'required|integer|min:1|max:12',
            'amount'      => 'required|numeric|min:0',
        ]);

        EmployeeAdvance::updateOrCreate(
            ['employee_id' => $request->employee_id, 'year' => $request->year, 'month' => $request->month],
            ['amount' => $request->amount, 'updated_by' => Auth::id()]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Save a single cell from the monthly calendar (employee + date + hours)
     */
    public function deleteCellCalendar(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'date'        => 'required|date',
        ]);

        Attendance::where('employee_id', $request->employee_id)
            ->whereDate('date', $request->date)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function saveHoursCalendar(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'date'        => 'required|date',
            'hours'       => 'required|numeric|min:0|max:24',
        ]);

        $hours = (float) $request->hours;

        if ($hours == 0) {
            $status = 'absent';
        } elseif ($hours >= 8) {
            $status = 'present';
        } else {
            $status = 'late';
        }

        Attendance::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $request->date],
            ['hours_worked' => $hours, 'status' => $status, 'marked_by' => Auth::id()]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Get employee history with detailed time entries
     */
    public function getEmployeeHistory($employeeId, Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get()
            ->map(function ($attendance) {
                $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

                // Format time entries for display
                $timeEntries = $attendance->time_entries ?? [];
                $timeHtml = '';
                foreach ($timeEntries as $entry) {
                    if (!empty($entry['check_in']) && !empty($entry['check_out'])) {
                        $timeHtml .= '<span class="time-badge">' . $entry['check_in'] . ' - ' . $entry['check_out'] . '</span> ';
                    }
                }
                if (empty($timeHtml)) $timeHtml = '-';

                // Format break entries for display
                $breakEntries = $attendance->break_entries ?? [];
                $breakHtml = '';
                foreach ($breakEntries as $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        $breakHtml .= '<span class="break-badge">' . $break['start'] . ' - ' . $break['end'] . '</span> ';
                    }
                }
                if (empty($breakHtml)) $breakHtml = '-';

                return [
                    'date' => $attendance->date->format('d/m/Y'),
                    'day_of_week' => $days[$attendance->date->dayOfWeekIso - 1],
                    'time_entries' => $timeHtml,
                    'break_entries' => $breakHtml,
                    'hours_worked' => number_format($attendance->hours_worked, 2, ',', '.') . ' h',
                    'status' => $attendance->status,
                    'reason' => $attendance->reason ?? '-',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $attendances
        ]);
    }
}
