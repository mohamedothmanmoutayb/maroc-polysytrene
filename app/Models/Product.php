<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'product_id';
    public $timestamps = true;

    protected $fillable = [
        'product_code',
        'product_name',
        'product_type',
        'unit_of_measure',

        // Dimensions for volume
        'height_m',
        'width_m',
        'depth_m',
        'volume_m3',
        'weight_kg',

        // Stock levels
        'min_stock_level',
        'max_stock_level',

        'description',

        // Status
        'is_active',
    ];

   protected $casts = [
        'is_active' => 'boolean',
        'height_m' => 'decimal:3',
        'width_m' => 'decimal:3',
        'depth_m' => 'decimal:3',
        'volume_m3' => 'decimal:4',
        'weight_kg' => 'decimal:2',
        'price_client' => 'decimal:2',
        'price_revendeur' => 'decimal:2',
        'price_commercial' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'production_time_days' => 'integer',
        'min_stock_level' => 'integer',
        'max_stock_level' => 'integer',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function stock()
    {
        return $this->hasOne(ProductStock::class, 'product_id');
    }

    public function familleStocks()
    {
        return $this->hasMany(ProductFamilleStock::class, 'product_id');
    }

    public function familles()
    {
        return $this->belongsToMany(Famille::class, 'product_famille', 'product_id', 'famille_id')
            ->withPivot([
                'quantity_per_unit',
                'sort_order',
                'prix_client',
                'prix_grossiste',
                'prix_commercial',
                'prix_special'
            ])
            ->withTimestamps();
    }

    public function getFamilyPrices($familleId)
    {
        $famille = $this->familles()->where('famille_id', $familleId)->first();
        if ($famille) {
            return [
                'prix_client' => $famille->pivot->prix_client,
                'prix_grossiste' => $famille->pivot->prix_grossiste,
                'prix_commercial' => $famille->pivot->prix_commercial,
                'prix_special' => $famille->pivot->prix_special,
                'quantity_per_unit' => $famille->pivot->quantity_per_unit,
            ];
        }
        return null;
    }

    public function billOfMaterials()
    {
        return $this->hasMany(BillOfMaterial::class, 'product_id');
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class, 'product_id');
    }

    public function productionOutputs()
    {
        return $this->hasMany(ProductionOutput::class, 'product_id');
    }

    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class, 'product_id');
    }

    public function productConversions()
    {
        return $this->hasMany(ProductConversion::class, 'parent_product_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(ProductStockMovement::class, 'product_id');
    }

    // Accessors
    public function getProductTypeLabelAttribute()
    {
        $labels = [
            'production' => 'Production (avec BOM)',
            'decoupage' => 'Découpage',
            'finale' => 'Finale',
        ];
        return $labels[$this->product_type] ?? $this->product_type;
    }

    public function getHasFamillesAttribute()
    {
        return $this->familles()->exists();
    }

    public function getTotalStockAttribute()
    {
        if ($this->has_familles) {
            return $this->familleStocks->sum('current_quantity');
        }
        return $this->stock ? $this->stock->current_quantity : 0;
    }

    public function getTotalAvailableStockAttribute()
    {
        if ($this->has_familles) {
            return $this->familleStocks->sum('available_quantity');
        }
        return $this->stock ? $this->stock->available_quantity : 0;
    }

    public function getVolumeAttribute()
    {
        if ($this->height_m && $this->width_m && $this->depth_m) {
            return $this->height_m * $this->width_m * $this->depth_m;
        }
        return $this->volume_m3;
    }

    public function getDimensionsAttribute()
    {
        return $this->height_m && $this->width_m && $this->depth_m
            ? "{$this->height_m} × {$this->width_m} × {$this->depth_m} m"
            : 'Non spécifié';
    }

    public function isProductionProduct()
    {
        return $this->product_type === 'production';
    }

    public function isDecoupageProduct()
    {
        return $this->product_type === 'decoupage';
    }

    public function isFinaleProduct()
    {
        return $this->product_type === 'finale';
    }

    public function canBeSourceFor($productionType)
    {
        switch ($productionType) {
            case 'type1':
                return $this->isProductionProduct();
            case 'type2':
                return $this->isProductionProduct();
            case 'type3':
                return $this->isDecoupageProduct() || $this->isProductionProduct();
            default:
                return false;
        }
    }

    public function getSellingPrice($type = 'client')
    {
        switch ($type) {
            case 'revendeur':
                return $this->price_revendeur ?? $this->price_client;
            case 'commerciale':
                return $this->price_commercial ?? $this->price_client;
            default:
                return $this->price_client;
        }
    }

    public function getTotalVolumeAttribute()
    {
        $volume = $this->volume_m3 ?? 0;
        if ($volume == 0 && $this->height_m && $this->width_m && $this->depth_m) {
            $volume = $this->height_m * $this->width_m * $this->depth_m;
        }
        return round($volume, 4);
    }

    public function getVolumePerUnitAttribute()
    {
        return $this->total_volume;
    }

    public function getDisplayVolumeAttribute()
    {
        $volume = $this->total_volume;
        return $volume > 0 ? number_format($volume, 4) . ' m³' : 'Non défini';
    }

    // Scopes
    public function scopeWithFamilles($query)
    {
        return $query->whereHas('familles');
    }

    public function scopeProduction($query)
    {
        return $query->where('product_type', 'production');
    }

    public function scopeDecoupage($query)
    {
        return $query->where('product_type', 'decoupage');
    }

    public function scopeFinale($query)
    {
        return $query->where('product_type', 'finale');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get price based on client type
     */
    public function getPriceByClientType($clientType, $familyId = null)
    {
        if ($familyId) {
            $family = $this->familles()->where('famille_id', $familyId)->first();
            if ($family) {
                switch ($clientType) {
                    case 'grossiste':
                        return $family->pivot->prix_grossiste ?? $this->price_revendeur ?? $this->price_client;
                    case 'commerciale':
                        return $family->pivot->prix_commercial ?? $this->price_commercial ?? $this->price_client;
                    case 'special':
                        return $family->pivot->prix_special ?? $this->price_client;
                    default:
                        return $family->pivot->prix_client ?? $this->price_client;
                }
            }
        }

        switch ($clientType) {
            case 'grossiste':
                return $this->price_revendeur ?? $this->price_client;
            case 'commerciale':
                return $this->price_commercial ?? $this->price_client;
            case 'special':
                return $this->price_special ?? $this->price_client;
            default:
                return $this->price_client;
        }
    }

    /**
     * Get weight per unit in kg
     */
    public function getWeightPerUnitInKg()
    {
        if ($this->weight_kg && $this->weight_kg > 0) {
            return (float) $this->weight_kg;
        }

        if ($this->volume_m3 && $this->volume_m3 > 0) {
            $density = 650;
            return (float) ($this->volume_m3 * $density);
        }

        return 0.0;
    }


    /*
    * Get volume per unit in m³
    */
    public function getVolumePerUnitInM3()
    {
        if ($this->volume_m3 && $this->volume_m3 > 0) {
            return (float) $this->volume_m3;
        }

        if ($this->height_m && $this->width_m && $this->depth_m) {
            return (float) ($this->height_m * $this->width_m * $this->depth_m);
        }

        if (property_exists($this, 'height_mm') && $this->height_mm && $this->width_mm && $this->depth_mm) {
            return (float) (($this->height_mm / 1000) * ($this->width_mm / 1000) * ($this->depth_mm / 1000));
        }

        return 0.0;
    }
}
