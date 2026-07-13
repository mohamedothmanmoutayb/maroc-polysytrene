<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $table = 'production_orders';
    protected $primaryKey = 'order_id';
    public $timestamps = true;

    protected $fillable = [
        'order_number',
        'product_id',
        'famille_id',
        'source_product_id',
        'source_famille_id',
        'quantity_to_produce',
        'required_quantity',
        'status',
        'priority',
        'start_date',
        'expected_completion_date',
        'actual_completion_date',
        'notes',
        'created_by',
        'responsible_employee_id',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
        'production_type',
        'waste_percentage',
        'is_decoupage_completed',
        'is_conversion_completed',
        'source_volume',
        'final_volume',
        'total_volume_produced',
        'waste_volume',
        'waste_declaration_required',
        'waste_declaration_completed',
        'material_source',
        'chutes_volume',
        'bom_percentage',
        'quality_status',
        'quality_score',
        'raw_material_weight_kg',
        'product_weight_kg',
        'weight_difference_percent',
        'quality_notes',
        'quality_checked_at',
        'quality_checked_by',
        'quality_override',
        'quality_override_reason',
        'quality_override_at',
        'quality_override_by',
        'defect_rate_percent',
        'total_good_quantity',
        'total_defective_quantity',
        'efficiency_percent',
        'additional_data',
    ];

    protected $casts = [
        'quantity_to_produce' => 'decimal:2',
        'required_quantity' => 'decimal:2',
        'sous_bloc_count' => 'decimal:2',
        'start_date' => 'date',
        'expected_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'created_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'decoupage_ratio' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'waste_percentage' => 'decimal:2',
        'is_decoupage_completed' => 'boolean',
        'is_conversion_completed' => 'boolean',
        'source_volume' => 'decimal:4',
        'final_volume' => 'decimal:4',
        'total_volume_produced' => 'decimal:4',
        'waste_volume' => 'decimal:4',
        'bom_percentage' => 'decimal:4',
        'chutes_volume' => 'decimal:4',
        'waste_declaration_required' => 'boolean',
        'waste_declaration_completed' => 'boolean',
        'quality_score' => 'decimal:2',
        'raw_material_weight_kg' => 'decimal:2',
        'product_weight_kg' => 'decimal:2',
        'weight_difference_percent' => 'decimal:2',
        'quality_checked_at' => 'datetime',
        'quality_override' => 'boolean',
        'quality_override_at' => 'datetime',
        'defect_rate_percent' => 'decimal:2',
        'efficiency_percent' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function wastes()
    {
        return $this->hasMany(ProductionWaste::class, 'production_order_id');
    }

    public function famille()
    {
        return $this->belongsTo(Famille::class, 'famille_id');
    }

    public function sourceFamille()
    {
        return $this->belongsTo(Famille::class, 'source_famille_id');
    }

    public function sourceProduct()
    {
        return $this->belongsTo(Product::class, 'source_product_id', 'product_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responsibleEmployee()
    {
        return $this->belongsTo(Employee::class, 'responsible_employee_id');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function consumptions()
    {
        return $this->hasMany(ProductionConsumption::class, 'production_order_id');
    }

    public function outputs()
    {
        return $this->hasMany(ProductionOutput::class, 'production_order_id');
    }

    public function decoupageOutputs()
    {
        return $this->hasMany(ProductionOutput::class, 'production_order_id')
            ->where('output_type', 'decoupage');
    }

    public function conversionOutputs()
    {
        return $this->hasMany(ProductionOutput::class, 'production_order_id')
            ->where('output_type', 'conversion');
    }

    public function isDecoupageType()
    {
        return $this->production_type === 'decoupage';
    }

    public function isDirectType()
    {
        return $this->production_type === 'direct';
    }

    public function isType1()
    {
        return $this->production_type === 'type1' || $this->production_type === 'direct';
    }

    public function isType2()
    {
        return $this->production_type === 'type2' || ($this->production_type === 'decoupage' && $this->product && $this->product->product_type === 'decoupage');
    }

    public function isType3()
    {
        return $this->production_type === 'type3' || ($this->production_type === 'decoupage' && $this->product && ($this->product->product_type === 'sales' || $this->product->product_type === 'finale'));
    }

    public function getTotalSousBlocsAttribute()
    {
        if ($this->isDecoupageType() && $this->decoupageOutputs->isNotEmpty()) {
            return $this->decoupageOutputs->sum('quantity_produced');
        }
        return 0;
    }

    public function getTotalFinalProductsAttribute()
    {
        if ($this->isDecoupageType() && $this->conversionOutputs->isNotEmpty()) {
            return $this->conversionOutputs->sum('quantity_produced');
        }
        return 0;
    }

    public function getRemainingSousBlocsAttribute()
    {
        if (!$this->isDecoupageType()) {
            return 0;
        }

        $totalSousBlocs = $this->getTotalSousBlocsAttribute();
        $sousBlocsUsed = $this->conversionOutputs->sum('quantity_consumed');

        return $totalSousBlocs - $sousBlocsUsed;
    }

    public function hasMultipleProducts()
    {
        if ($this->production_type !== 'type3') {
            return false;
        }

        return DB::table('production_order_products')
            ->where('production_order_id', $this->order_id)
            ->count() > 1;
    }

    public function getType3Products()
    {
        if ($this->production_type !== 'type3') {
            return collect();
        }

        return DB::table('production_order_products')
            ->where('production_order_id', $this->order_id)
            ->join('products', 'production_order_products.product_id', '=', 'products.product_id')
            ->select(
                'production_order_products.*',
                'products.product_name',
                'products.product_code',
                'products.volume_m3'
            )
            ->get();
    }

    public function getType3Product($productId)
    {
        return DB::table('production_order_products')
            ->where('production_order_id', $this->order_id)
            ->where('production_order_products.product_id', $productId)
            ->join('products', 'production_order_products.product_id', '=', 'products.product_id')
            ->select(
                'production_order_products.*',
                'products.product_name',
                'products.product_code',
                'products.volume_m3'
            )
            ->first();
    }

    public function getType3ProductSummary($productId)
    {
        if ($this->production_type !== 'type3') {
            return null;
        }

        $product = $this->getType3Product($productId);

        if (!$product) {
            return null;
        }

        // Get production outputs for this specific product
        $produced = ProductionOutput::where('production_order_id', $this->order_id)
            ->where('product_id', $productId)
            ->sum('quantity_produced');

        $defective = ProductionOutput::where('production_order_id', $this->order_id)
            ->where('product_id', $productId)
            ->sum('quantity_defective');

        $consumed = ProductionOutput::where('production_order_id', $this->order_id)
            ->where('product_id', $productId)
            ->sum('quantity_consumed');

        // Ensure volume_per_unit is a number
        $volumePerUnit = is_numeric($product->volume_per_unit)
            ? (float)$product->volume_per_unit
            : (is_numeric($product->volume_m3) ? (float)$product->volume_m3 : 0);

        return [
            'product_id' => $product->product_id,
            'product_name' => $product->product_name,
            'product_code' => $product->product_code,
            'conversion_rate' => (float)$product->conversion_rate,
            'planned_quantity' => (float)$product->quantity_to_produce,
            'produced_quantity' => (float)$produced,
            'defective_quantity' => (float)$defective,
            'consumed_quantity' => (float)$consumed,
            'remaining_quantity' => max(0, (float)$product->quantity_to_produce - (float)$produced),
            'volume_per_unit' => $volumePerUnit,
            'total_volume' => is_numeric($product->total_volume) ? (float)$product->total_volume : 0,
        ];
    }

    public function getType3ProductionSummary()
    {
        if ($this->production_type !== 'type3') {
            return [];
        }

        $products = $this->getType3Products();
        $summary = [];

        foreach ($products as $product) {
            $produced = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->sum('quantity_produced');

            $defective = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->sum('quantity_defective');

            $consumed = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->sum('quantity_consumed');

            $remaining = max(0, $product->quantity_to_produce - $produced);

            $summary[] = [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'conversion_rate' => $product->conversion_rate,
                'planned_quantity' => $product->quantity_to_produce,
                'produced_quantity' => $produced,
                'defective_quantity' => $defective,
                'consumed_quantity' => $consumed,
                'remaining_quantity' => $remaining,
                'good_quantity' => $produced - $defective,
                'progress' => $product->quantity_to_produce > 0 ? ($produced / $product->quantity_to_produce * 100) : 0,
                'source_required' => $product->source_required,
                'volume_per_unit' => $product->volume_per_unit,
                'total_volume' => $product->total_volume,
            ];
        }

        return $summary;
    }

    /**
     * Get multiple products for Type 2 orders
     */
    public function getType2Products()
    {
        if ($this->production_type !== 'type2') {
            return collect();
        }

        return DB::table('production_order_products')
            ->where('production_order_id', $this->order_id)
            ->join('products', 'production_order_products.product_id', '=', 'products.product_id')
            ->select(
                'production_order_products.*',
                'products.product_name',
                'products.product_code',
                'products.volume_m3'
            )
            ->get();
    }

    /**
     * Get summary for a specific Type 2 product
     */
    public function getType2ProductSummary($productId)
    {
        if ($this->production_type !== 'type2') {
            return null;
        }

        $product = DB::table('production_order_products')
            ->where('production_order_id', $this->order_id)
            ->where('production_order_products.product_id', $productId) // Specify table
            ->join('products', 'production_order_products.product_id', '=', 'products.product_id')
            ->select(
                'production_order_products.*',
                'products.product_name',
                'products.product_code',
                'products.volume_m3',
            )
            ->first();

        if (!$product) {
            return null;
        }

        // Get production outputs for this specific product
        $produced = ProductionOutput::where('production_order_id', $this->order_id)
            ->where('product_id', $productId)
            ->sum('quantity_produced');

        $defective = ProductionOutput::where('production_order_id', $this->order_id)
            ->where('product_id', $productId)
            ->sum('quantity_defective');

        $consumed = ProductionOutput::where('production_order_id', $this->order_id)
            ->where('product_id', $productId)
            ->sum('quantity_consumed');

        // Ensure volume_per_unit is a number
        $volumePerUnit = is_numeric($product->volume_per_unit)
            ? (float)$product->volume_per_unit
            : (is_numeric($product->volume_m3) ? (float)$product->volume_m3 : 0);

        return [
            'product_id' => $product->product_id,
            'product_name' => $product->product_name,
            'product_code' => $product->product_code,
            'decoupage_ratio' => (float)$product->decoupage_ratio,
            'planned_quantity' => (float)$product->quantity_to_produce,
            'produced_quantity' => (float)$produced,
            'defective_quantity' => (float)$defective,
            'consumed_quantity' => (float)$consumed,
            'remaining_quantity' => max(0, (float)$product->quantity_to_produce - (float)$produced),
            'volume_per_unit' => $volumePerUnit,
            'total_volume' => is_numeric($product->total_volume) ? (float)$product->total_volume : 0,
            'good_quantity' => (float)$produced - (float)$defective,
            'progress' => $product->quantity_to_produce > 0
                ? round(((float)$produced / (float)$product->quantity_to_produce) * 100, 1)
                : 0,
        ];
    }

    /**
     * Get complete production summary for Type 2 (matching Type 3 structure)
     */
    public function getType2ProductionSummary()
    {
        if ($this->production_type !== 'type2') {
            return [];
        }

        $products = $this->getType2Products();
        $summary = [];

        foreach ($products as $product) {
            $produced = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->sum('quantity_produced');

            $defective = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->sum('quantity_defective');

            $consumed = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->sum('quantity_consumed');

            $remaining = max(0, $product->quantity_to_produce - $produced);

            // Calculate volume
            $volumePerUnit = is_numeric($product->volume_per_unit)
                ? (float)$product->volume_per_unit
                : (is_numeric($product->volume_m3) ? (float)$product->volume_m3 : 0);

            $totalVolume = $produced * $volumePerUnit;

            $summary[] = [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'decoupage_ratio' => (float)$product->decoupage_ratio,
                'planned_quantity' => (float)$product->quantity_to_produce,
                'produced_quantity' => (float)$produced,
                'defective_quantity' => (float)$defective,
                'consumed_quantity' => (float)$consumed,
                'remaining_quantity' => (float)$remaining,
                'good_quantity' => (float)$produced - (float)$defective,
                'progress' => $product->quantity_to_produce > 0
                    ? round(($produced / $product->quantity_to_produce) * 100, 1)
                    : 0,
                'source_required' => (float)($product->source_required ?? 0),
                'volume_per_unit' => $volumePerUnit,
                'total_volume' => $totalVolume,
            ];
        }

        return $summary;
    }

    /**
     * Get total source blocks required for Type 2
     */
    public function getTotalSourceBlocksAttribute()
    {
        if ($this->production_type !== 'type2') {
            return 0;
        }

        $products = $this->getType2Products();
        $total = 0;

        foreach ($products as $product) {
            $total += $product->source_required ?? 0;
        }

        return $total;
    }

    /**
     * Get total sous-blocs produced for Type 2
     */
    public function getTotalSousBlocsProducedAttribute()
    {
        if ($this->production_type !== 'type2') {
            return 0;
        }

        $products = $this->getType2Products();
        $total = 0;

        foreach ($products as $product) {
            $produced = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->sum('quantity_produced');
            $total += $produced;
        }

        return $total;
    }

    /**
     * Check if Type 2 order is completed
     */
    public function isType2Completed()
    {
        if ($this->production_type !== 'type2') {
            return false;
        }

        $products = $this->getType2Products();

        foreach ($products as $product) {
            $produced = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->sum('quantity_produced');

            if ($produced < $product->quantity_to_produce) {
                return false;
            }
        }

        return true;
    }

    public function isType4()
    {
        return $this->production_type === 'type4';
    }

    public function isType5()
    {
        return $this->production_type === 'type5';
    }

    public function getType5Products()
    {
        if ($this->production_type !== 'type5') {
            return collect();
        }

        return DB::table('production_order_products')
            ->where('production_order_id', $this->order_id)
            ->join('products', 'production_order_products.product_id', '=', 'products.product_id')
            ->select(
                'production_order_products.*',
                'products.product_name',
                'products.product_code',
                'products.volume_m3'
            )
            ->get();
    }

    public function getType5Product($productId)
    {
        return DB::table('production_order_products')
            ->where('production_order_id', $this->order_id)
            ->where('production_order_products.product_id', $productId)
            ->join('products', 'production_order_products.product_id', '=', 'products.product_id')
            ->select(
                'production_order_products.*',
                'products.product_name',
                'products.product_code',
                'products.volume_m3'
            )
            ->first();
    }

    public function getType5ProductionSummary()
    {
        if ($this->production_type !== 'type5') {
            return [];
        }

        $products = $this->getType5Products();
        $summary = [];

        foreach ($products as $product) {
            $produced = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->where('output_type', 'type5')
                ->sum('quantity_produced');

            $defective = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->where('output_type', 'type5')
                ->sum('quantity_defective');

            $consumed = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->where('output_type', 'type5')
                ->sum('quantity_consumed');

            $remaining = max(0, $product->quantity_to_produce - $produced);

            $summary[] = [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'planned_quantity' => (float) $product->quantity_to_produce,
                'produced_quantity' => (float) $produced,
                'defective_quantity' => (float) $defective,
                'consumed_quantity' => (float) $consumed,
                'remaining_quantity' => (float) $remaining,
                'good_quantity' => (float) $produced - (float) $defective,
                'progress' => $product->quantity_to_produce > 0 ? ($produced / $product->quantity_to_produce * 100) : 0,
                'volume_per_unit' => (float) $product->volume_per_unit,
                'total_volume' => (float) $product->total_volume,
            ];
        }

        return $summary;
    }

    /**
     * Get source product for Type 4 orders
     */
    public function getType4SourceProduct()
    {
        if ($this->production_type !== 'type4') {
            return null;
        }

        return DB::table('production_order_products')
            ->where('production_order_id', $this->order_id)
            ->where('product_type', 'source')
            ->join('products', 'production_order_products.product_id', '=', 'products.product_id')
            ->select(
                'production_order_products.*',
                'products.product_name',
                'products.product_code',
                'products.volume_m3',
                'products.height_m',
                'products.width_m',
                'products.depth_m',
                'products.product_type as product_type'
            )
            ->first();
    }

    /**
     * Get target products for Type 4 orders
     */
    public function getType4TargetProducts()
    {
        if ($this->production_type !== 'type4') {
            return collect();
        }

        return DB::table('production_order_products')
            ->where('production_order_id', $this->order_id)
            ->where('product_type', 'target')
            ->join('products', 'production_order_products.product_id', '=', 'products.product_id')
            ->select(
                'production_order_products.*',
                'products.product_name',
                'products.product_code',
                'products.volume_m3',
                'products.height_m',
                'products.width_m',
                'products.depth_m'
            )
            ->get();
    }

    /**
     * Get complete production summary for Type 4
     */
    public function getType4ProductionSummary()
    {
        if ($this->production_type !== 'type4') {
            return [];
        }

        $products = $this->getType4Products();
        $summary = [];

        foreach ($products as $product) {
            $produced = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->where('output_type', 'type4_target')
                ->sum('quantity_produced');

            $defective = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->where('output_type', 'type4_target')
                ->sum('quantity_defective');

            $consumed = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->where('output_type', 'type4_target')
                ->sum('quantity_consumed');

            $remaining = max(0, $product->quantity_to_produce - $produced);

            $summary[] = [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'planned_quantity' => (float)$product->quantity_to_produce,
                'produced_quantity' => (float)$produced,
                'defective_quantity' => (float)$defective,
                'consumed_quantity' => (float)$consumed,
                'remaining_quantity' => $remaining,
                'good_quantity' => (float)$produced - (float)$defective,
                'progress' => $product->quantity_to_produce > 0 ? ($produced / $product->quantity_to_produce * 100) : 0,
                'source_required' => (float)($product->source_required ?? 0),
                'volume_per_unit' => (float)($product->volume_per_unit ?? 0),
                'total_volume' => (float)($product->total_volume ?? 0),
            ];
        }

        return $summary;
    }

    /**
     * Get multiple products for Type 4 orders
     */
    public function getType4Products()
    {
        if ($this->production_type !== 'type4') {
            return collect();
        }

        return DB::table('production_order_products')
            ->where('production_order_id', $this->order_id)
            ->join('products', 'production_order_products.product_id', '=', 'products.product_id')
            ->select(
                'production_order_products.*',
                'products.product_name',
                'products.product_code',
                'products.volume_m3'
            )
            ->get();
    }

    private function calculateProductVolumeFromData($product)
    {
        if ($product->volume_m3 && $product->volume_m3 > 0) {
            return (float)$product->volume_m3;
        }

        if ($product->height_m && $product->width_m && $product->depth_m) {
            return (float)($product->height_m * $product->width_m * $product->depth_m);
        }

        return 0;
    }

    private function getDimensionsFromData($product)
    {
        if ($product->height_m && $product->width_m && $product->depth_m) {
            return "{$product->height_m} × {$product->width_m} × {$product->depth_m} m";
        }
        return 'Dimensions non spécifiées';
    }

    /**
     * Check if Type 4 order is completed
     */
    public function isType4Completed()
    {
        if ($this->production_type !== 'type4') {
            return false;
        }

        $targetProducts = $this->getType4TargetProducts();

        foreach ($targetProducts as $product) {
            $produced = ProductionOutput::where('production_order_id', $this->order_id)
                ->where('product_id', $product->product_id)
                ->where('output_type', 'type4_target')
                ->sum('quantity_produced');

            if ($produced < $product->quantity_to_produce) {
                return false;
            }
        }

        return true;
    }

    public function qualityCheckedBy()
    {
        return $this->belongsTo(User::class, 'quality_checked_by');
    }

    public function qualityOverrideBy()
    {
        return $this->belongsTo(User::class, 'quality_override_by');
    }

    public function updateQualityMetrics()
    {
        $totalProduced = $this->outputs->sum('quantity_produced');
        $totalDefective = $this->outputs->sum('quantity_defective');
        $totalGood = $totalProduced - $totalDefective;

        $this->total_good_quantity = $totalGood;
        $this->total_defective_quantity = $totalDefective;
        $this->defect_rate_percent = $totalProduced > 0
            ? ($totalDefective / $totalProduced) * 100
            : 0;

        if ($this->start_date && $this->actual_completion_date) {
            $plannedDays = $this->expected_completion_date->diffInDays($this->start_date);
            $actualDays = $this->actual_completion_date->diffInDays($this->start_date);
            $this->efficiency_percent = $plannedDays > 0
                ? ($plannedDays / max($actualDays, 1)) * 100
                : 100;
        }

        $this->saveQuietly();
    }

    public function hasQualityIssue()
    {
        return in_array($this->quality_status, ['warning', 'critical']);
    }

    public function canCompleteWithQualityIssue()
    {
        return $this->quality_override || $this->quality_status === 'good';
    }
}

