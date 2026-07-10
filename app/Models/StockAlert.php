<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAlert extends Model
{
    use HasFactory;

    protected $table = 'stock_alerts';
    protected $primaryKey = 'alert_id';
    public $timestamps = true;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'alert_type',
        'current_value',
        'threshold_value',
        'severity',
        'is_resolved',
        'resolved_by',
        'resolved_at',
        'notes',
    ];

    protected $casts = [
        'current_value' => 'decimal:2',
        'threshold_value' => 'decimal:2',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
