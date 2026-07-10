<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsenceRequest extends Model
{
    use HasFactory;

    protected $table = 'absence_requests';
    protected $primaryKey = 'request_id';
    public $timestamps = true;

    protected $fillable = [
        'employee_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'sick_leave' => 'Arrêt maladie',
            'paid_leave' => 'Congé payé',
            'unpaid_leave' => 'Congé sans solde',
            'other' => 'Autre',
        ];
        return $labels[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
        ];
        $color = $badges[$this->status] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . $this->status_label . '</span>';
    }
}
