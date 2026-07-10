<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Famille extends Model
{
    use HasFactory;

    protected $table = 'familles';
    protected $primaryKey = 'famille_id';
    public $timestamps = true;

    protected $fillable = [
        'famille_code',
        'famille_name',
        'description',
        'is_active',
        'sort_order',
        'prix_client',
        'prix_grossiste',
        'prix_commercial',
        'prix_special',
        'prix_revient',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'prix_client' => 'decimal:2',
        'prix_grossiste' => 'decimal:2',
        'prix_commercial' => 'decimal:2',
        'prix_special' => 'decimal:2',
        'prix_revient' => 'decimal:2',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_famille', 'famille_id', 'product_id')
            ->withPivot('quantity_per_unit', 'sort_order', 'prix_client', 'prix_grossiste', 'prix_commercial', 'prix_special')
            ->withTimestamps();
    }

    public function stocks()
    {
        return $this->hasMany(ProductFamilleStock::class, 'famille_id');
    }

    public function outputs()
    {
        return $this->hasMany(ProductionOutput::class, 'famille_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(ProductStockMovement::class, 'famille_id');
    }

    public function getDisplayNameAttribute()
    {
        return $this->famille_name . ' (' . $this->famille_code . ')';
    }

    public function getPriceInfoAttribute()
    {
        return [
            'client' => $this->prix_client,
            'grossiste' => $this->prix_grossiste,
            'commercial' => $this->prix_commercial,
            'special' => $this->prix_special,
        ];
    }

    public function getFormattedClientPriceAttribute()
    {
        return number_format($this->prix_client, 2) . ' DH';
    }

    public function getFormattedGrossistePriceAttribute()
    {
        return number_format($this->prix_grossiste, 2) . ' DH';
    }

    public function getFormattedCommercialPriceAttribute()
    {
        return number_format($this->prix_commercial, 2) . ' DH';
    }

    public function getFormattedSpecialPriceAttribute()
    {
        return number_format($this->prix_special, 2) . ' DH';
    }

    public function getFormattedRevientPriceAttribute()
    {
        return number_format($this->prix_revient, 2) . ' DH';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getAssociatedProductsAttribute()
    {
        return $this->products->pluck('product_name', 'product_id');
    }

    public function associateToProductIfNotExists($productId)
    {
        if (!$this->products()->where('products.product_id', $productId)->exists()) {
            $this->products()->attach($productId, [
                'quantity_per_unit' => 1,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return true;
        }
        return false;
    }
}
