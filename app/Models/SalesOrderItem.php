<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    use HasFactory;

    protected $table = 'sales_order_items';
    protected $primaryKey = 'order_item_id';
    public $timestamps = true;

    protected $fillable = [
        'order_id',
        'item_type',
        'item_id',
        'item_name',
        'quantity',
        'unit_price',
        'total_price',
        'family_id',
        'family_name',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected $appends = ['total_volume'];

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }

    /**
     * Get the parent item model
     */
    public function item()
    {
        return $this->morphTo('item', 'item_type', 'item_id');
    }

    /**
     * Get the product if this is a product item
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id')
            ->where('item_type', 'production')
            ->orWhere('item_type', 'decoupage')
            ->orWhere('item_type', 'finale');
    }

    public function getTotalVolumeAttribute()
    {
        $volumePerUnit = 0;

        if ($this->item_type != 'raw_material') {
            $product = Product::find($this->item_id);
            if ($product) {
                $volumePerUnit = $product->getVolumePerUnitInM3();
            }
        }

        return $this->quantity * $volumePerUnit;
    }

    /**
     * Get the raw material if this is a raw material item
     */
    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'item_id')
            ->where('item_type', 'raw_material');
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'raw_material' => 'Matière Première',
            'production' => 'Production',
            'decoupage' => 'Découpage',
            'finale' => 'Finale',
        ];
        return $labels[$this->item_type] ?? $this->item_type;
    }

    public function getUnitOfMeasureAttribute()
    {
        if ($this->item_type == 'raw_material') {
            $rawMaterial = RawMaterial::find($this->item_id);
            return $rawMaterial ? $rawMaterial->unit_of_measure : null;
        }
        $product = Product::find($this->item_id);
        return $product ? $product->unit_of_measure : null;
    }

    /**
     * Get the item type label with badge
     */
    public function getTypeBadgeAttribute()
    {
        $badges = [
            'raw_material' => 'bg-secondary',
            'production' => 'bg-primary',
            'decoupage' => 'bg-warning',
            'finale' => 'bg-success',
        ];
        $class = $badges[$this->item_type] ?? 'bg-info';
        $label = $this->type_label;
        return '<span class="badge ' . $class . '">' . $label . '</span>';
    }
}
