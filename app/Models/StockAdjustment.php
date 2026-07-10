<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $table = 'stock_adjustments';
    protected $primaryKey = 'adjustment_id';
    public $timestamps = true;

    protected $fillable = [
        'adjustment_type',
        'reference_id',
        'famille_id',
        'old_quantity',
        'new_quantity',
        'adjusted_quantity',
        'unit_price',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'admin_notes',
    ];

    protected $casts = [
        'old_quantity' => 'decimal:4',
        'new_quantity' => 'decimal:4',
        'adjusted_quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function famille()
    {
        return $this->belongsTo(Famille::class, 'famille_id');
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
        return '<span class="badge badge-' . $color . '">' . $this->status_label . '</span>';
    }
}
