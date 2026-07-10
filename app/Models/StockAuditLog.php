<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAuditLog extends Model
{
    use HasFactory;

    protected $table = 'stock_audit_logs';
    protected $primaryKey = 'audit_id';
    public $timestamps = true;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'field_name',
        'old_value',
        'new_value',
        'quantity_change',
        'reference_id',
        'reference_type',
        'performed_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'quantity_change' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
