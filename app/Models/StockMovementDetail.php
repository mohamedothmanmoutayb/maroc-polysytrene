<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovementDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_movement_details';
    protected $primaryKey = 'stock_detail_id';
    public $timestamps = true;

    protected $fillable = [
        'stock_movement_id',
        'material_id',
        'quantity',
        'unit_price',
        'total_price',
        'remaining_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
    ];

    public function stockMovement()
    {
        return $this->belongsTo(RawMaterialStockMovement::class, 'stock_movement_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'material_id');
    }
}
