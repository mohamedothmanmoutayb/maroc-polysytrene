<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechargePart extends Model
{
    use HasFactory;

    protected $table = 'recharge_parts_stock';

    protected $fillable = [
        'name',
        'current_stock',
        'min_stock',
        'max_stock',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
    ];

    public function movements()
    {
        return $this->hasMany(RechargePartStockMovement::class, 'part_id')->latest();
    }

    public function isLowStock()
    {
        return $this->current_stock <= $this->min_stock;
    }

    public function isExceedMaxStock()
    {
        return $this->max_stock && $this->current_stock > $this->max_stock;
    }

    public function getStockStatusAttribute()
    {
        if ($this->isLowStock()) {
            return '<span class="badge bg-danger">Stock Bas</span>';
        } elseif ($this->isExceedMaxStock()) {
            return '<span class="badge bg-warning">Dépassé</span>';
        } else {
            return '<span class="badge bg-success">Normal</span>';
        }
    }
}
