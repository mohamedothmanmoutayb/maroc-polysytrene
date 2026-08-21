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
        // Amount is the raw balance delta: positive means what we owe the
        // supplier went up (bad, shown as a debit "-"), negative means it
        // went down (good, shown as a credit "+").
        if ($this->amount > 0) {
            return '-' . number_format($this->amount, 2) . ' DH';
        } elseif ($this->amount < 0) {
            return '+' . number_format(abs($this->amount), 2) . ' DH';
        }
        return number_format(0, 2) . ' DH';
    }

    public function getAmountClassAttribute()
    {
        return $this->amount > 0 ? 'text-danger' : 'text-success';
    }

    public function getPreviousBalanceFormattedAttribute()
    {
        return $this->formatBalance($this->previous_balance);
    }

    public function getNewBalanceFormattedAttribute()
    {
        return $this->formatBalance($this->new_balance);
    }

    public function getPreviousBalanceClassAttribute()
    {
        return $this->balanceClass($this->previous_balance);
    }

    public function getNewBalanceClassAttribute()
    {
        return $this->balanceClass($this->new_balance);
    }

    private function balanceClass($balance)
    {
        if ($balance > 0) {
            return 'text-danger';
        } elseif ($balance < 0) {
            return 'text-success';
        }
        return 'text-muted';
    }

    private function formatBalance($balance)
    {
        // Same "debt shown as -" convention as amount_formatted.
        if ($balance > 0) {
            return '-' . number_format($balance, 2, ',', '.') . ' DH';
        } elseif ($balance < 0) {
            return '+' . number_format(abs($balance), 2, ',', '.') . ' DH';
        }
        return number_format(0, 2, ',', '.') . ' DH';
    }
}
