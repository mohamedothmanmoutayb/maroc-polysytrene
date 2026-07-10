<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckAllocation extends Model
{
    use HasFactory;

    protected $table = 'check_allocations';
    protected $primaryKey = 'allocation_id';
    public $timestamps = true;

    protected $fillable = [
        'check_id',
        'purchase_id',
        'allocated_amount',
        'notes',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function check()
    {
        return $this->belongsTo(Check::class, 'check_id');
    }

    public function purchase()
    {
        return $this->belongsTo(RawMaterialPurchase::class, 'purchase_id');
    }
}
