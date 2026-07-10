<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Traite extends Model
{
    use HasFactory;

    protected $table = 'traites';
    protected $primaryKey = 'traite_id';
    public $timestamps = true;

    protected $fillable = [
        'traite_number',
        'order_id',
        'payment_id',
        'client_id',
        'amount',
        'issue_date',
        'due_date',
        'payment_date',
        'bank_name',
        'drawee',
        'drawee_address',
        'notes',
        'status',
        'document_path',
        'original_filename',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * RELATIONSHIPS
     */
    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }

    public function payment()
    {
        return $this->belongsTo(SalesOrderPayment::class, 'payment_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ACCESSORS
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'paid' => 'success',
            'overdue' => 'danger',
            'bounced' => 'danger'
        ];
        $labels = [
            'pending' => 'En attente',
            'paid' => 'Payé',
            'overdue' => 'En retard',
            'bounced' => 'Rebondi'
        ];
        $color = $badges[$this->status] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . ($labels[$this->status] ?? $this->status) . '</span>';
    }

    public function getIsOverdueAttribute()
    {
        return $this->status === 'pending' && $this->due_date && $this->due_date->isPast();
    }

    public function getRemainingDaysAttribute()
    {
        if (!$this->due_date || $this->status === 'paid') {
            return null;
        }
        return now()->diffInDays($this->due_date, false);
    }

    public function getAmountFormattedAttribute()
    {
        return number_format($this->amount, 2) . ' DH';
    }
}
