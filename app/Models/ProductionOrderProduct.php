<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOrderProduct extends Model
{
    use HasFactory;

    protected $table = 'production_order_products';

    protected $fillable = [
        'production_order_id',
        'product_id',
        'decoupage_ratio',
        'conversion_rate',
        'quantity_to_produce',
        'source_required',
        'volume_per_unit',
        'total_volume',
    ];

    protected $casts = [
        'quantity_to_produce' => 'decimal:2',
        'decoupage_ratio' => 'decimal:4',
        'conversion_rate' => 'decimal:4',
        'source_required' => 'decimal:4',
        'volume_per_unit' => 'decimal:4',
        'total_volume' => 'decimal:4',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
