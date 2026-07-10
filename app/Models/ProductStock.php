<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    use HasFactory;

    protected $table = 'product_stock';
    protected $primaryKey = 'stock_id';
    public $timestamps = true;

    protected $fillable = [
        'product_id',
        'current_quantity',
        'reserved_quantity',
        'location',
        'avalaible_quantity',
        'last_updated',
        'last_restocked',
    ];

    protected $casts = [
        'current_quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'available_quantity' => 'integer',
        'last_updated' => 'datetime',
        'last_restocked' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
