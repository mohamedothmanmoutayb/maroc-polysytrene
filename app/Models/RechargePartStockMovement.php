<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechargePartStockMovement extends Model
{
    use HasFactory;

    protected $table = 'recharge_part_stock_movements';

    protected $fillable = [
        'part_id',
        'movement_type',
        'quantity',
        'previous_stock',
        'new_stock',
        'reason',
        'performed_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_stock' => 'integer',
        'new_stock' => 'integer',
    ];

    public function part()
    {
        return $this->belongsTo(RechargePart::class, 'part_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
