<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialStockMovement extends Model
{
    use HasFactory;

    protected $table = 'raw_material_stock_movements';
    protected $primaryKey = 'movement_id';
    public $timestamps = true;

    protected $fillable = [
        'material_id',
        'movement_type',
        'quantity',
        'previous_stock',
        'new_stock',
        'reference_id',
        'reference_type',
        'reference_number',
        'performed_by',
        'notes',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'quantity' => 'float',
        'previous_stock' => 'float',
        'new_stock' => 'float',
    ];

    public function details()
    {
        return $this->hasMany(StockMovementDetail::class, 'stock_movement_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'material_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
