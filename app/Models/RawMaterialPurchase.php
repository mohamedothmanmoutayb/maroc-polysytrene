<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialPurchase extends Model
{
    use HasFactory;

    protected $table = 'raw_material_purchases';
    protected $primaryKey = 'purchase_id';
    public $timestamps = true;

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'magazine_id',
        'purchase_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'total_amount',
        'include_tva',
        'discount_percentage',
        'discount_amount',
        'final_amount',
        'paid_amount',
        'payment_status',
        'payment_method',
        'notes',
        'created_by',
    ];


    protected $casts = [
        'purchase_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'include_tva' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function magazine()
    {
        return $this->belongsTo(Magazine::class, 'magazine_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(RawMaterialPurchaseItem::class, 'purchase_id');
    }

    public function paymentDocuments()
    {
        return $this->hasMany(PurchasePaymentDocument::class, 'purchase_id');
    }

    public function getTotalPaidAttribute()
    {
        return $this->paymentDocuments()->sum('amount');
    }

    public function getRemainingAmountAttribute()
    {
        $remaining = $this->final_amount - $this->total_paid;
        return max(0, $remaining);
    }

    public function checkAllocations()
    {
        return $this->hasMany(CheckAllocation::class, 'purchase_id', 'purchase_id');
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->total_paid <= 0) {
            return 'pending';
        } elseif ($this->total_paid >= $this->total_amount) {
            return 'paid';
        } else {
            return 'partial';
        }
    }

    public function getPaymentStatusLabelAttribute()
    {
        $status = $this->payment_status;
        switch ($status) {
            case 'pending':
                return '<span class="badge bg-warning">Non Payé</span>';
            case 'partial':
                return '<span class="badge bg-info">Avance</span>';
            case 'paid':
                return '<span class="badge bg-success">Payé</span>';
            default:
                return '<span class="badge bg-secondary">Inconnu</span>';
        }
    }

    public function isFullyPaid()
    {
        return $this->total_paid >= $this->total_amount;
    }

    public function isPartiallyPaid()
    {
        return $this->total_paid > 0 && $this->total_paid < $this->total_amount;
    }
}
