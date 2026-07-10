<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'payment_id';
    public $timestamps = true;

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'client_id',
        'payment_date',
        'amount',
        'payment_method',
        'check_id',
        'check_number',
        'bank_name',
        'check_image',
        'transaction_reference',
        'status',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function check()
    {
        return $this->belongsTo(Check::class, 'check_id');
    }
}
