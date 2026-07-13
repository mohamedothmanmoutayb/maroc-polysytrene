<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class MachineMaintenanceSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'machine_maintenance_schedules';
    public $timestamps = true;

    protected $fillable = [
        'machine_id',
        'label',
        'description',
        'interval_days',
        'reminder_days_before',
        'last_completed_at',
        'next_due_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'last_completed_at' => 'date',
        'next_due_at' => 'date',
        'interval_days' => 'integer',
        'reminder_days_before' => 'integer',
        'is_active' => 'boolean',
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class, 'machine_id');
    }

    public function completions()
    {
        return $this->hasMany(MachineMaintenanceCompletion::class, 'schedule_id')->latest('completed_at');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDaysLeftAttribute(): int
    {
        return (int) Carbon::now()->startOfDay()->diffInDays($this->next_due_at, false);
    }

    public function getStatusAttribute(): string
    {
        $daysLeft = $this->days_left;

        if ($daysLeft < 0) {
            return 'overdue';
        }

        if ($daysLeft <= $this->reminder_days_before) {
            return 'due_soon';
        }

        return 'ok';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'overdue' => '<span class="badge bg-danger">En retard</span>',
            'due_soon' => '<span class="badge bg-warning">Bientôt</span>',
            default => '<span class="badge bg-success">OK</span>',
        };
    }

    public function complete(?string $notes, ?int $userId): MachineMaintenanceCompletion
    {
        $previousDueAt = $this->next_due_at;
        $completedAt = Carbon::now()->startOfDay();
        $nextDueAt = $completedAt->copy()->addDays($this->interval_days);

        $completion = $this->completions()->create([
            'completed_at' => $completedAt,
            'previous_due_at' => $previousDueAt,
            'next_due_at' => $nextDueAt,
            'notes' => $notes,
            'completed_by' => $userId,
        ]);

        $this->update([
            'last_completed_at' => $completedAt,
            'next_due_at' => $nextDueAt,
        ]);

        return $completion;
    }
}
