<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';
    protected $primaryKey = 'employee_id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'full_name',
        'cin',
        'cnss',
        'zk_uid',
        'phone',
        'email',
        'address',
        'photo',
        'hourly_salary',
        'monthly_salary',
        'hire_date',
        'resignation_date',
        'department',
        'position',
        'birth_date',
        'emergency_contact',
        'emergency_phone',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'resignation_date' => 'date',
        'birth_date' => 'date',
        'hourly_salary' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id');
    }

    public function documentsByCategory($category)
    {
        return $this->documents()->where('category', $category)->get();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function absenceRequests()
    {
        return $this->hasMany(AbsenceRequest::class, 'employee_id');
    }   

    public function getAttendanceForMonth($year, $month)
    {
        return $this->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();
    }

    /**
     * Employees who were still employed at some point during/after the
     * start of the given month — i.e. not resigned before that month.
     * Keeps a resigned employee visible on past months he worked, while
     * hiding him from the months following his resignation.
     */
    public function scopeEmployedDuring($query, $year, $month)
    {
        $start = \Carbon\Carbon::create($year, $month, 1)->startOfDay();

        return $query->where(function ($q) use ($start) {
            $q->whereNull('resignation_date')
                ->orWhere('resignation_date', '>=', $start);
        });
    }

    public function getAbsenceStatsForMonth($year, $month)
    {
        $attendances = $this->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $stats = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'half_day' => 0,
            'holiday' => 0,
            'sick_leave' => 0,
            'paid_leave' => 0,
            'unpaid_leave' => 0,
            'total_hours' => 0,
        ];

        foreach ($attendances as $attendance) {
            $stats[$attendance->status]++;
            $stats['total_hours'] += $attendance->hours_worked;
        }

        return $stats;
    }
}
