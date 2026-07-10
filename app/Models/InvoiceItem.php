<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'invoice_items';
    protected $primaryKey = 'invoice_item_id';
    public $timestamps = true;

    protected $fillable = [
        'invoice_id',
        'item_type',
        'item_id',
        'item_name',
        'quantity',
        'unit_price',
        'total_price',
        'family_id',
        'family_name',
        'source_sale_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected $appends = ['source_sales_map'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function sourceSale()
    {
        return $this->belongsTo(SalesOrder::class, 'source_sale_id', 'order_id');
    }

    /**
     * Ventes that contributed to this line. A single line can be sourced from
     * several ventes when identical products loaded from different ventes get
     * merged into one row (see invoice create/edit pages). The pivot's
     * `quantity` records how much of this line's quantity came from each vente.
     */
    public function sourceSales()
    {
        return $this->belongsToMany(
            SalesOrder::class,
            'invoice_item_sales',
            'invoice_item_id',
            'sales_order_id',
            'invoice_item_id',
            'order_id'
        )->withPivot('quantity')->withTimestamps();
    }

    /**
     * Map of order_id => quantity contributed, for the create/edit pages.
     * Requires `sourceSales` to be eager loaded; returns an empty array otherwise.
     */
    public function getSourceSalesMapAttribute()
    {
        if (!$this->relationLoaded('sourceSales')) {
            return [];
        }

        return $this->sourceSales->mapWithKeys(function ($order) {
            return [$order->order_id => (float) $order->pivot->quantity];
        })->toArray();
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
