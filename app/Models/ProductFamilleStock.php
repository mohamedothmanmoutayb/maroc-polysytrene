<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFamilleStock extends Model
{
    use HasFactory;

    protected $table = 'product_famille_stock';
    protected $primaryKey = 'famille_stock_id';
    public $timestamps = true;

    protected $fillable = [
        'product_id',
        'famille_id',
        'famille_name',
        'current_quantity',
        'reserved_quantity',
        'available_quantity',
        'location',
        'last_updated',
        'last_restocked',
    ];

    protected $casts = [
        'current_quantity' => 'decimal:4',
        'reserved_quantity' => 'decimal:4',
        'available_quantity' => 'decimal:4',
        'last_updated' => 'datetime',
        'last_restocked' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function famille()
    {
        return $this->belongsTo(Famille::class, 'famille_id');
    }

    public function updateStock()
    {
        $this->available_quantity = $this->current_quantity - $this->reserved_quantity;
        $this->last_updated = now();
        $this->save();
    }

    public function incrementStock($quantity)
    {
        $this->current_quantity += $quantity;
        $this->last_restocked = now();
        $this->updateStock();
    }

    public function decrementStock($quantity)
    {
        if ($this->available_quantity < $quantity) {
            throw new \Exception("Stock insuffisant pour la famille {$this->famille_name}");
        }

        $this->current_quantity -= $quantity;
        $this->updateStock();
    }

    public function reserveStock($quantity)
    {
        if ($this->available_quantity < $quantity) {
            throw new \Exception("Stock disponible insuffisant pour la famille {$this->famille_name}");
        }

        $this->reserved_quantity += $quantity;
        $this->updateStock();
    }

    public function releaseReservation($quantity)
    {
        if ($this->reserved_quantity < $quantity) {
            throw new \Exception("Quantité de réservation incorrecte pour la famille {$this->famille_name}");
        }

        $this->reserved_quantity -= $quantity;
        $this->updateStock();
    }
}
