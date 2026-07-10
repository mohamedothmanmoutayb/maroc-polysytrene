<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOutput extends Model
{
    use HasFactory;

    protected $table = 'production_output';
    protected $primaryKey = 'output_id';
    public $timestamps = false;

    protected $fillable = [
        'production_order_id',
        'product_id',
        'famille_id',
        'famille_name',
        'source_famille_id',
        'output_type', // 'type1', 'type2', 'type3' to match production types
        'quantity_produced',
        'quantity_consumed', // For type2/type3: source material consumed
        'quantity_defective',
        'quality_grade',
        'production_date',
        'notes',
        'approved_by',
        'approved_at',
        'is_final_output',
        'related_output_id', // Link between type2 and type3 outputs
        'apply_conversion',
        'conversion_data',
        'unit_volume_m3',
        'total_volume_m3',
        'waste_volume_m3',
        'recyclable_waste_volume',
        'pure_waste_volume',
        'waste_declaration_completed',
    ];

    protected $casts = [
        'quantity_produced' => 'decimal:2',
        'quantity_consumed' => 'decimal:2',
        'quantity_defective' => 'decimal:2',
        'production_date' => 'date',
        'approved_at' => 'datetime',
        'is_final_output' => 'boolean',
        'apply_conversion' => 'boolean',
        'waste_declaration_completed' => 'boolean',
        'conversion_data' => 'array',
        'unit_volume_m3' => 'decimal:4',
        'total_volume_m3' => 'decimal:4',
        'waste_volume_m3' => 'decimal:4',
        'recyclable_waste_volume' => 'decimal:4',
        'pure_waste_volume' => 'decimal:4',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function famille()
    {
        return $this->belongsTo(Famille::class, 'famille_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function relatedOutput()
    {
        return $this->belongsTo(ProductionOutput::class, 'related_output_id');
    }

    public function getGoodQuantityAttribute()
    {
        return $this->quantity_produced - $this->quantity_defective;
    }

    // Scopes for different production types
    public function scopeType1($query)
    {
        return $query->where('output_type', 'type1');
    }

    public function scopeType2($query)
    {
        return $query->where('output_type', 'type2');
    }

    public function scopeType3($query)
    {
        return $query->where('output_type', 'type3');
    }

    // Helper methods to check output type
    public function isType1()
    {
        return $this->output_type === 'type1';
    }

    public function isType2()
    {
        return $this->output_type === 'type2';
    }

    public function isType3()
    {
        return $this->output_type === 'type3';
    }

    // Calculate conversion efficiency
    public function getConversionEfficiencyAttribute()
    {
        if ($this->quantity_consumed > 0) {
            return ($this->quantity_produced / $this->quantity_consumed) * 100;
        }
        return 100;
    }

    // Get waste percentage
    public function getWastePercentageAttribute()
    {
        if ($this->quantity_produced > 0) {
            return ($this->quantity_defective / $this->quantity_produced) * 100;
        }
        return 0;
    }

    // Get net quantity (good - defective)
    public function getNetQuantityAttribute()
    {
        return $this->getGoodQuantityAttribute();
    }

    // Check if this output is for découpage phase
    public function isDecoupagePhase()
    {
        return $this->isType2() && $this->productionOrder && $this->productionOrder->production_type === 'type2';
    }

    // Check if this output is for conversion phase
    public function isConversionPhase()
    {
        return $this->isType3() && $this->productionOrder && $this->productionOrder->production_type === 'type3';
    }

    // Get linked outputs (for type2->type3 relationships)
    public function linkedOutputs()
    {
        if ($this->isType2()) {
            // If this is a type2 output, find related type3 outputs
            return ProductionOutput::where('related_output_id', $this->output_id)
                ->where('output_type', 'type3')
                ->get();
        } elseif ($this->isType3()) {
            // If this is a type3 output, find the parent type2 output
            if ($this->related_output_id) {
                return ProductionOutput::where('output_id', $this->related_output_id)->get();
            }
        }
        return collect();
    }

    public function calculateVolume()
    {
        if ($this->product) {
            $unitVolume = $this->product->getVolumePerUnitInM3();
            $this->total_volume_m3 = $unitVolume * $this->quantity_produced;

            $this->waste_volume_m3 = $unitVolume * $this->quantity_defective;

            $this->save();
        }
        return $this;
    }
}
