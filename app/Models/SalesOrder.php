<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CreditNote;

class SalesOrder extends Model
{
    use HasFactory;

    protected $table = 'sales_orders';
    protected $primaryKey = 'order_id';
    public $timestamps = true;

    protected $fillable = [
        'order_number',
        'client_id',
        'order_date',
        'total_amount',
        'final_amount',
        'paid_amount',
        'payment_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class, 'order_id');
    }

    public function payments()
    {
        return $this->hasMany(SalesOrderPayment::class, 'order_id');
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class, 'sales_order_id');
    }

    /**
     * Invoice lines that include this vente among their sources (see
     * InvoiceItem::sourceSales() — a line can be sourced from several ventes
     * when identical products get merged into one row).
     */
    public function invoiceItems()
    {
        return $this->belongsToMany(
            InvoiceItem::class,
            'invoice_item_sales',
            'sales_order_id',
            'invoice_item_id',
            'order_id',
            'invoice_item_id'
        )->withPivot('quantity')->withTimestamps();
    }

    /**
     * Distinct factures that include at least one line item sourced from this vente.
     */
    public function getRelatedInvoicesAttribute()
    {
        return $this->invoiceItems
            ->pluck('invoice')
            ->filter()
            ->unique('invoice_id')
            ->values();
    }
}
