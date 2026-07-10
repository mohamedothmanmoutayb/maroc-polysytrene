<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillOfMaterial extends Model
{
    use HasFactory;

    protected $table = 'bill_of_materials';
    protected $primaryKey = 'bom_id';
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'material_id',
        'quantity_required',
        'unit_of_measure',
        'scrap_factor',
        'is_active',
    ];

    protected $casts = [
        'quantity_required' => 'decimal:2',
        'scrap_factor' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'material_id');
    }
}
