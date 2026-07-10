<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductConversion extends Model
{
    use HasFactory;

    protected $table = 'product_conversions';
    protected $primaryKey = 'conversion_id';
    public $timestamps = true;

    protected $fillable = [
        'parent_product_id',
        'child_product_id',
        'conversion_rate',
        'waste_percentage',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'conversion_rate' => 'decimal:4',
        'waste_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function parentProduct()
    {
        return $this->belongsTo(Product::class, 'parent_product_id', 'product_id');
    }

    public function childProduct()
    {
        return $this->belongsTo(Product::class, 'child_product_id', 'product_id');
    }
}
