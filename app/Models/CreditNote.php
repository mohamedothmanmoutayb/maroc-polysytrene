<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CreditNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'credit_notes';
    protected $primaryKey = 'credit_note_id';
    public $timestamps = true;

    protected $fillable = [
        'credit_note_number',
        'client_id',
        'sales_order_id',
        'credit_note_date',
        'total_amount',
        'disposition',
        'status',
        'reason',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'credit_note_date' => 'date',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * RELATIONSHIPS
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function items()
    {
        return $this->hasMany(CreditNoteItem::class, 'credit_note_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * ACCESSORS
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => 'Brouillon',
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'processed' => 'Traité',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'secondary',
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'processed' => 'success',
        ];
        $class = $badges[$this->status] ?? 'secondary';
        return '<span class="badge badge-' . $class . '">' . $this->status_label . '</span>';
    }

    /**
     * Update client balance when credit note is processed
     */
    public function process()
    {
        DB::beginTransaction();
        try {
            $this->status = 'processed';
            $this->save();

            // Update client balance (add credit to client)
            $client = $this->client;
            $previousBalance = $client->balance;
            $client->balance += $this->total_amount;
            $client->save();

            // Record balance history
            $client->balanceHistory()->create([
                'previous_balance' => $previousBalance,
                'new_balance' => $client->balance,
                'amount' => $this->total_amount,
                'type' => 'credit_note',
                'reference_type' => 'credit_note',
                'reference_id' => $this->credit_note_id,
                'description' => "Avoir N°{$this->credit_note_number} - " . ($this->reason ?? 'Retour produit'),
                'created_by' => auth()->id(),
            ]);

            // If this credit note is linked to an order, update order status?
            if ($this->sales_order_id) {
                // Optional: Update order payment status or add note
                $order = $this->salesOrder;
                $order->notes = ($order->notes ? $order->notes . "\n" : '') .
                    "Avoir N°{$this->credit_note_number} créé le " . now()->format('d/m/Y') .
                    " pour un montant de " . number_format($this->total_amount, 2) . " DH";
                $order->save();
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate credit note number
     */
    public static function generateNumber()
    {
        $year = date('Y');
        $month = date('m');
        $last = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        return $year . $month . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
