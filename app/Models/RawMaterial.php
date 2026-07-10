<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RawMaterial extends Model
{
    use HasFactory;

    protected $table = 'raw_materials';
    protected $primaryKey = 'material_id';
    public $timestamps = false;

    protected $fillable = [
        'material_code',
        'material_name',
        'category_id',
        'unit_of_measure',
        'current_stock',
        'min_stock_level',
        'max_stock_level',
        'prix_client',
        'prix_grossiste',
        'prix_commercial',
        'prix_special',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_active' => 'boolean',
        'min_stock_level' => 'float',
        'max_stock_level' => 'float',
        'prix_client' => 'float',
        'prix_grossiste' => 'float',
        'prix_commercial' => 'float',
        'prix_special' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(RawMaterialCategory::class, 'category_id');
    }

    public function billOfMaterials()
    {
        return $this->hasMany(BillOfMaterial::class, 'material_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(RawMaterialPurchaseItem::class, 'material_id');
    }

    public function productionConsumptions()
    {
        return $this->hasMany(ProductionConsumption::class, 'material_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(RawMaterialStockMovement::class, 'material_id');
    }


    /**
     * Get the FIFO cost for a specific quantity
     */
    public function getFifoCost($quantity)
    {
        $totalCost = 0;
        $remainingQuantity = $quantity;

        // Get stock details ordered by FIFO (oldest first)
        $stockDetails = StockMovementDetail::where('material_id', $this->material_id)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($stockDetails as $stockDetail) {
            if ($remainingQuantity <= 0) break;

            $quantityToUse = min($stockDetail->remaining_quantity, $remainingQuantity);
            $totalCost += $quantityToUse * $stockDetail->unit_price;
            $remainingQuantity -= $quantityToUse;
        }

        return $totalCost;
    }

    /**
     * Get stock movement details for this material
     */
    public function stockMovementDetails()
    {
        return $this->hasMany(StockMovementDetail::class, 'material_id');
    }

    /**
     * Get average unit cost based on remaining stock
     */
    public function getAverageUnitCost()
    {
        $totalValue = StockMovementDetail::where('material_id', $this->material_id)
            ->where('remaining_quantity', '>', 0)
            ->sum(DB::raw('remaining_quantity * unit_price'));

        $totalStock = $this->current_stock;

        return $totalStock > 0 ? $totalValue / $totalStock : 0;
    }

    /**
     * Get the unit cost to display (FIFO or average)
     */
    public function getDisplayUnitCost($method = 'fifo')
    {
        if ($method === 'fifo') {
            $oldestStock = StockMovementDetail::where('material_id', $this->material_id)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('created_at', 'asc')
                ->first();

            return $oldestStock ? $oldestStock->unit_price : $this->getAverageUnitCost();
        }

        return $this->getAverageUnitCost();
    }

    public function getCurrentStockAttribute()
    {
        return StockMovementDetail::where('material_id', $this->material_id)
            ->sum('remaining_quantity');
    }

    public function getAverageUnitCostAttribute()
    {
        $totalValue = StockMovementDetail::where('material_id', $this->material_id)
            ->where('remaining_quantity', '>', 0)
            ->sum(DB::raw('remaining_quantity * unit_price'));

        $totalStock = $this->current_stock;

        return $totalStock > 0 ? $totalValue / $totalStock : 0;
    }

    public function wasteMaterials()
    {
        return $this->hasMany(ProductionWaste::class, 'material_id');
    }

    public function getIsWasteAttribute()
    {
        return $this->category_id === config('constants.waste_category_id', 99); // ID de la catégorie "Chutes"
    }
}
