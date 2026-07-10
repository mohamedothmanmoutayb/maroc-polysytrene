<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $table = 'attendance_settings';
    protected $primaryKey = 'setting_id';
    public $timestamps = true;

    protected $fillable = [
        'check_in_time',
        'check_out_time',
        'late_threshold',
        'work_hours_per_day',
        'working_days',
        'weekend_days',
        'auto_mark_absent',
        'notes',
        'updated_by',
    ];

    protected $casts = [
        'working_days' => 'array',
        'weekend_days' => 'array',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
        'late_threshold' => 'datetime:H:i',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isWorkingDay($date)
    {
        $dayOfWeek = strtolower($date->format('l'));
        return in_array($dayOfWeek, $this->working_days);
    }

    public function isWeekend($date)
    {
        $dayOfWeek = strtolower($date->format('l'));
        return in_array($dayOfWeek, $this->weekend_days);
    }

    public static function getSettings()
    {
        return self::first() ?? self::create();
    }
}
