<?php
// app/Models/Quotation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $table = 'quotations';
    protected $primaryKey = 'quote_id';
    public $timestamps = true;

    protected $fillable = [
        'quote_number',
        'client_id',
        'quote_date',
        'valid_until',
        'total_amount',
        'discount',
        'final_amount',
        'status',
        'notes',
        'terms_conditions',
        'observation',
        'created_by',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
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
        return $this->hasMany(QuotationItem::class, 'quote_id');
    }

    /**
     * Generate quote number with format 001/YYYY (starts from 001 each year)
     */
    public static function generateQuoteNumber()
    {
        $year = date('Y');

        // Get the last quote number for the current year
        $lastQuote = self::whereYear('created_at', $year)
            ->orderBy('quote_id', 'desc')
            ->first();

        if ($lastQuote && str_contains($lastQuote->quote_number, '/')) {
            // Extract the sequence number from the last quote
            $lastNumber = intval(explode('/', $lastQuote->quote_number)[0]);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            // Start from 001 for new year
            $newNumber = '001';
        }

        return $newNumber . '/' . $year;
    }
}
