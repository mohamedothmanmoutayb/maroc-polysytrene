<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionWaste extends Model
{
    use HasFactory;

    protected $table = 'production_wastes';
    protected $primaryKey = 'waste_id';

    protected $fillable = [
        'production_order_id',
        'waste_type',
        'waste_source',
        'waste_category',
        'height',
        'width',
        'depth',
        'quantity',
        'volume_m3',
        'notes',
        'is_recovered',
        'recovery_date',
        'created_by',
        'material_id'
    ];

    protected $casts = [
        'height' => 'decimal:4',
        'width' => 'decimal:4',
        'depth' => 'decimal:4',
        'quantity' => 'decimal:4',
        'volume_m3' => 'decimal:4',
        'is_recovered' => 'boolean',
        'recovery_date' => 'datetime'
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'material_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getWasteTypeLabelAttribute()
    {
        return match($this->waste_type) {
            'recyclable' => 'Recyclable',
            'waste' => 'Déchet',
            'auto_defective' => 'Auto-défaut',
            default => 'Inconnu'
        };
    }

    public function getDimensionsAttribute()
    {
        if ($this->height && $this->width && $this->depth) {
            return "{$this->height} × {$this->width} × {$this->depth} m";
        }
        return null;
    }

    public function getTotalVolumeAttribute()
    {
        if ($this->volume_m3) {
            return $this->volume_m3;
        }

        if ($this->height && $this->width && $this->depth) {
            return $this->height * $this->width * $this->depth;
        }

        return 0;
    }
}
