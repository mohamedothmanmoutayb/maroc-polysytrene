<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNoteItem extends Model
{
    use HasFactory;

    protected $table = 'credit_note_items';
    protected $primaryKey = 'credit_note_item_id';
    public $timestamps = true;

    protected $fillable = [
        'credit_note_id',
        'order_item_id',
        'item_type',
        'item_id',
        'item_name',
        'quantity',
        'unit_price',
        'total_price',
        'family_id',
        'family_name',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class, 'credit_note_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(SalesOrderItem::class, 'order_item_id');
    }

    /**
     * Get the parent item model
     */
    public function item()
    {
        return $this->morphTo('item', 'item_type', 'item_id');
    }
}
