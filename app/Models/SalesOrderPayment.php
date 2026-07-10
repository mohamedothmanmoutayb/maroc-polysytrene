<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderPayment extends Model
{
    use HasFactory;

    protected $table = 'sales_order_payments';
    protected $primaryKey = 'payment_id';
    public $timestamps = true;

    protected $fillable = [
        'order_id',
        'client_id',
        'credit_note_id',
        'payment_method',
        'amount',
        'received_amount',
        'payment_date',
        'document_path',
        'original_filename',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Amount actually handed over by the client for this transaction. Falls back to
     * `amount` (the portion applied to this order) when no excess was involved.
     */
    public function getDisplayAmountAttribute()
    {
        return $this->received_amount ?? $this->amount;
    }

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class, 'credit_note_id');
    }

    public function getMethodLabelAttribute()
    {
        $labels = [
            'cash' => 'Espèces',
            'check' => 'Chèque',
            'transfer' => 'Virement',
            'traite' => 'Traite',
            'advance' => 'Avance',
            'avoir' => 'Avoir',
        ];
        return $labels[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get client name (prioritize client_id, fallback to order client)
     */
    public function getClientNameAttribute()
    {
        if ($this->client) {
            return $this->client->display_name;
        }

        if ($this->order && $this->order->client) {
            return $this->order->client->display_name;
        }

        if ($this->creditNote && $this->creditNote->client) {
            return $this->creditNote->client->display_name;
        }

        return '-';
    }

    /**
     * Get client ID (prioritize client_id, fallback to order client)
     */
    public function getRelatedClientIdAttribute()
    {
        if ($this->client_id) {
            return $this->client_id;
        }

        if ($this->order && $this->order->client_id) {
            return $this->order->client_id;
        }

        if ($this->creditNote && $this->creditNote->client_id) {
            return $this->creditNote->client_id;
        }

        return null;
    }
}
