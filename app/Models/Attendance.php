<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';
    protected $primaryKey = 'attendance_id';
    public $timestamps = true;

    protected $fillable = [
        'employee_id',
        'date',
        'time_entries',
        'break_entries',
        'hours_worked',
        'status',
        'reason',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
        'hours_worked' => 'decimal:2',
        'time_entries' => 'array',
        'break_entries' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function marker()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    /**
     * Calculate total hours worked from time entries
     */
    public function calculateTotalHours()
    {
        if (empty($this->time_entries)) {
            return 0;
        }

        $totalMinutes = 0;

        foreach ($this->time_entries as $entry) {
            if (!empty($entry['check_in']) && !empty($entry['check_out'])) {
                $checkIn = Carbon::parse($entry['check_in']);
                $checkOut = Carbon::parse($entry['check_out']);

                // Handle overnight shifts
                if ($checkOut < $checkIn) {
                    $checkOut->addDay();
                }

                $minutes = $checkOut->diffInMinutes($checkIn);
                $totalMinutes += $minutes;
            }
        }

        // Subtract break minutes
        if (!empty($this->break_entries)) {
            foreach ($this->break_entries as $break) {
                if (!empty($break['start']) && !empty($break['end'])) {
                    $start = Carbon::parse($break['start']);
                    $end = Carbon::parse($break['end']);

                    if ($end < $start) {
                        $end->addDay();
                    }

                    $totalMinutes -= $end->diffInMinutes($start);
                }
            }
        }

        return round($totalMinutes / 60, 2);
    }

    /**
     * Get formatted time entries
     */
    public function getFormattedTimeEntriesAttribute()
    {
        if (empty($this->time_entries)) {
            return '-';
        }

        $entries = [];
        foreach ($this->time_entries as $entry) {
            if (!empty($entry['check_in']) && !empty($entry['check_out'])) {
                $entries[] = Carbon::parse($entry['check_in'])->format('H:i') . ' - ' .
                            Carbon::parse($entry['check_out'])->format('H:i');
            }
        }

        return implode(', ', $entries);
    }

    /**
     * Get formatted break entries
     */
    public function getFormattedBreakEntriesAttribute()
    {
        if (empty($this->break_entries)) {
            return '-';
        }

        $breaks = [];
        foreach ($this->break_entries as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
                $breaks[] = Carbon::parse($break['start'])->format('H:i') . ' - ' .
                           Carbon::parse($break['end'])->format('H:i');
            }
        }

        return implode(', ', $breaks);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'present' => 'Présent',
            'absent' => 'Absent',
            'late' => 'Retard',
            'half_day' => 'Demi-journée',
            'holiday' => 'Congé',
            'sick_leave' => 'Arrêt maladie',
            'paid_leave' => 'Congé payé',
            'unpaid_leave' => 'Congé sans solde',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'half_day' => 'info',
            'holiday' => 'primary',
            'sick_leave' => 'secondary',
            'paid_leave' => 'info',
            'unpaid_leave' => 'dark',
        ];
        $color = $badges[$this->status] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . $this->status_label . '</span>';
    }
}
