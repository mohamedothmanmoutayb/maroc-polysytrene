<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineMaintenanceCompletion extends Model
{
    use HasFactory;

    protected $table = 'machine_maintenance_completions';
    public $timestamps = true;

    protected $fillable = [
        'schedule_id',
        'completed_at',
        'previous_due_at',
        'next_due_at',
        'notes',
        'completed_by',
    ];

    protected $casts = [
        'completed_at' => 'date',
        'previous_due_at' => 'date',
        'next_due_at' => 'date',
    ];

    public function schedule()
    {
        return $this->belongsTo(MachineMaintenanceSchedule::class, 'schedule_id');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
