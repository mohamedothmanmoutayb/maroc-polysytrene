<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialPurchaseItem extends Model
{
    use HasFactory;

    protected $table = 'raw_material_purchase_items';
    protected $primaryKey = 'purchase_item_id';
    public $timestamps = false;

    protected $fillable = [
        'purchase_id',
        'item_type',
        'material_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'received_quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'received_quantity' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(RawMaterialPurchase::class, 'purchase_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'material_id');
    }

    public function isChargeDiverse(): bool
    {
        return $this->item_type === 'charge_diverse';
    }
}
