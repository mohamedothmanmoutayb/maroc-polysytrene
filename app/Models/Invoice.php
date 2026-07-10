<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';
    protected $primaryKey = 'invoice_id';
    public $timestamps = true;

    protected $fillable = [
        'invoice_number',
        'client_id',
        'invoice_date',
        'total_amount',
        'discount',
        'final_amount',
        'amount_paid',
        'notes',
        'terms_conditions',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
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
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    /**
     * Generate invoice number with format 001/YYYY (starts from 001 each year)
     */
    public static function generateInvoiceNumber()
    {
        $year = date('Y');

        // Get the last invoice number for the current year
        $lastInvoice = self::whereYear('created_at', $year)
            ->orderBy('invoice_id', 'desc')
            ->first();

        if ($lastInvoice && str_contains($lastInvoice->invoice_number, '/')) {
            // Extract the sequence number from the last invoice
            $lastNumber = intval(explode('/', $lastInvoice->invoice_number)[0]);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            // Start from 001 for new year
            $newNumber = '001';
        }

        return $newNumber . '/' . $year;
    }

    /**
     * Validate and set invoice number manually
     */
    public static function validateInvoiceNumber($invoiceNumber, $excludeId = null)
    {
        $query = self::where('invoice_number', $invoiceNumber);

        if ($excludeId) {
            $query->where('invoice_id', '!=', $excludeId);
        }

        return !$query->exists();
    }
}
