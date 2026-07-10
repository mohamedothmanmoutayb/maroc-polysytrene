<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientBalanceHistory extends Model
{
    use HasFactory;

    protected $table = 'client_balance_history';
    protected $primaryKey = 'history_id';
    public $timestamps = true;

    protected $fillable = [
        'client_id',
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

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
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
            'order_created'    => 'Commande créée',
            'order_updated'    => 'Commande modifiée',
            'payment_added'    => 'Paiement ajouté',
            'payment_updated'  => 'Paiement modifié',
            'payment_deleted'  => 'Paiement supprimé',
            'payment_excess'   => 'Excédent de paiement',
            'credit_used'      => 'Crédit utilisé',
            'credit_released'  => 'Crédit libéré',
            'credit_note'      => 'Avoir (Note de crédit)',
            'credit_note_refund' => 'Remboursement avoir',
            'credit_note_balance' => 'Avoir ajouté au solde',
            'manual_adjustment'=> 'Ajustement manuel',
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
