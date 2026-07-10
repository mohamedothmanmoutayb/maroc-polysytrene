<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePaymentDocument extends Model
{
    use HasFactory;

    protected $table = 'purchase_payment_documents';
    protected $primaryKey = 'document_id';
    public $timestamps = true;

    protected $fillable = [
        'purchase_id',
        'document_number',
        'document_type',
        'check_id',
        'traite_id',
        'file_path',
        'original_filename',
        'amount',
        'payment_method',
        'payment_date',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function purchase()
    {
        return $this->belongsTo(RawMaterialPurchase::class, 'purchase_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function generateDocumentNumber()
    {
        $prefix = 'PAY';
        $year = date('Y');
        $month = date('m');
        $lastDoc = self::whereYear('created_at', $year)
                      ->whereMonth('created_at', $month)
                      ->orderBy('document_id', 'desc')
                      ->first();

        if ($lastDoc) {
            $lastNumber = intval(substr($lastDoc->document_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $year . $month . $newNumber;
    }
}
