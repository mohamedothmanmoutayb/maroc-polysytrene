<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Check extends Model
{
    use HasFactory;

    protected $table = 'checks';
    protected $primaryKey = 'check_id';
    public $timestamps = true;

    protected $fillable = [
        'check_number',
        'check_type',
        'client_id',
        'order_id',
        'payment_id',
        'amount',
        'remaining_amount',
        'bank_name',
        'account_holder',
        'issue_date',
        'deposit_date',
        'clearing_date',
        'check_image',
        'status',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'issue_date' => 'date',
        'deposit_date' => 'date',
        'clearing_date' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }

    public function salesOrderPayment()
    {
        return $this->belongsTo(SalesOrderPayment::class, 'payment_id');
    }

    public function allocations()
    {
        return $this->hasMany(CheckAllocation::class, 'check_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'check_id');
    }

    public function getAvailableAmountAttribute()
    {
        $allocated = $this->allocations()->sum('allocated_amount');
        return $this->amount - $allocated;
    }

    public function getDaysToClearingAttribute()
    {
        if ($this->status === 'cleared') {
            return 0;
        }

        if (!$this->deposit_date) {
            return null;
        }

        $depositDate = $this->deposit_date instanceof \Carbon\Carbon
            ? $this->deposit_date
            : \Carbon\Carbon::parse($this->deposit_date);

        $clearingDate = $this->deposit_date
            ? ($this->deposit_date instanceof \Carbon\Carbon ? $this->deposit_date : \Carbon\Carbon::parse($this->deposit_date))
            : $depositDate->copy()->addDays(3);
        $today = now()->startOfDay();
        $clearingDateStart = $clearingDate->startOfDay();

        if ($today->greaterThanOrEqualTo($clearingDateStart)) {
            return 0;
        }

        return $today->diffInDays($clearingDateStart);
    }

    public function getCanClearAttribute()
    {
        return $this->status === 'deposited' && $this->days_to_clearing === 0;
    }

    public function scopeByType($query, $type)
    {
        return $query->where('check_type', $type);
    }

    public function scopeEnterprise($query)
    {
        return $query->where('check_type', 'entreprise');
    }

    public function scopeClient($query)
    {
        return $query->where('check_type', 'client');
    }

    public function scopeWithAvailableAmount($query)
    {
        return $query->where('is_active', true)
            ->whereIn('status', ['pending', 'deposited', 'cleared', 'allocated'])
            ->where('remaining_amount', '>', 0);
    }


    public function scopeExceptRejected($query)
    {
        return $query->where('status', '!=', 'bounced');
    }

    public static function getActiveChecks($type = null)
    {
        $query = self::exceptRejected()->where('is_active', true);

        if ($type) {
            $query->where('check_type', $type);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public static function getAvailableChecks($type = null)
    {
        $query = self::withAvailableAmount();

        if ($type) {
            $query->where('check_type', $type);
        }

        return $query->get()->filter(function($check) {
            return $check->available_amount > 0;
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($check) {
            if (empty($check->remaining_amount)) {
                $check->remaining_amount = $check->amount;
            }
        });
    }
}
