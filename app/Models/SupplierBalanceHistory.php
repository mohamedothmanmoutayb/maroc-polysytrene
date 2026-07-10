<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierBalanceHistory extends Model
{
    use HasFactory;

    protected $table = 'supplier_balance_history';
    protected $primaryKey = 'history_id';
    public $timestamps = true;

    protected $fillable = [
        'supplier_id',
        'previous_balance',
        'new_balance',
        'amount',
        'type',
        'reference_type',
        'reference_id',
        'description',
        'created_by',
    ];

    protected $casts = [
        'previous_balance' => 'decimal:2',
        'new_balance' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'purchase_created'    => 'Achat créé',
            'purchase_updated'    => 'Achat modifié',
            'purchase_unpaid'     => 'Achat non payé',
            'payment_added'       => 'Paiement ajouté',
            'payment_updated'     => 'Paiement modifié',
            'payment_deleted'     => 'Paiement supprimé',
            'overpayment_credit'  => 'Excédent crédité',
            'direct_payment'      => 'Paiement direct',
            'payment_from_balance' => 'Paiement par solde',
        ];
        return $labels[$this->type] ?? $this->type;
    }

    public function getAmountFormattedAttribute()
    {
        $prefix = $this->amount >= 0 ? '+' : '';
        return $prefix . number_format($this->amount, 2) . ' DH';
    }

    public function getAmountClassAttribute()
    {
        return $this->amount >= 0 ? 'text-success' : 'text-danger';
    }
}
