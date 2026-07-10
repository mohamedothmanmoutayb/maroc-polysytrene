<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStockMovement extends Model
{
    use HasFactory;

    protected $table = 'product_stock_movements';
    protected $primaryKey = 'movement_id';
    public $timestamps = true;

    protected $fillable = [
        'product_id',
        'famille_id',
        'famille_name',
        'movement_type',
        'quantity',
        'previous_stock',
        'new_stock',
        'reference_type',
        'reference_id',
        'reference_number',
        'famille',
        'movement_date',
        'performed_by',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'previous_stock' => 'decimal:4',
        'new_stock' => 'decimal:4',
        'movement_date' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function famille()
    {
        return $this->belongsTo(Famille::class, 'famille_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
