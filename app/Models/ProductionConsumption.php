<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionConsumption extends Model
{
    use HasFactory;

    protected $table = 'production_consumption';
    protected $primaryKey = 'consumption_id';
    public $timestamps = false;

    protected $fillable = [
        'production_order_id',
        'material_id',
        'planned_quantity',
        'actual_quantity_used',
        'waste_quantity',
        'unit_cost',
        'total_cost',
        'notes',
        'is_stock_consumed',
        'stock_consumed_quantity',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:2',
        'actual_quantity_used' => 'decimal:2',
        'waste_quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'is_stock_consumed' => 'boolean',
        'stock_consumed_quantity' => 'decimal:4',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'material_id');
    }
}
