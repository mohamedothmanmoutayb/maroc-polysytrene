<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clients';
    protected $primaryKey = 'client_id';
    public $timestamps = true;

    protected $fillable = [
        'client_type',
        'person_type',
        'name',
        'entreprise_name',
        'phone',
        'email',
        'address',
        'cin',
        'ice',
        'rc',
        'patente',
        'credit_limit',
        'credit_usage',
        'balance',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_usage' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * RELATIONSHIPS
     */
    public function documents()
    {
        return $this->hasMany(ClientDocument::class, 'client_id');
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'client_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'client_id');
    }

    public function balanceHistory()
    {
        return $this->hasMany(ClientBalanceHistory::class, 'client_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get payments through sales orders
     */
    public function payments()
    {
        return $this->hasManyThrough(
            SalesOrderPayment::class,
            SalesOrder::class,
            'client_id',
            'order_id',
            'client_id',
            'order_id'
        );
    }

    /**
     * ACCESSORS - INFORMATIONS
     */
    public function getDisplayNameAttribute()
    {
        if ($this->person_type == 'morale' && $this->entreprise_name) {
            return $this->entreprise_name;
        }
        return $this->name ?: 'N/A';
    }

    public function getClientTypeLabelAttribute()
    {
        return match($this->client_type) {
            'client' => 'Client',
            'commerciale' => 'Commerciale',
            'grossiste' => 'Grossiste',
            'special' => 'Client spécial',
            default => 'Non défini',
        };
    }

    /**
     * CREDIT ACCESSORS
     */
    public function getCreditUsedAttribute()
    {
        // Sum of all unpaid orders (pending or partial)
        return $this->salesOrders()
            ->whereIn('payment_status', ['pending', 'partial'])
            ->sum(DB::raw('final_amount - paid_amount'));
    }

    public function getCreditAvailableAttribute()
    {
        return max(0, $this->credit_limit - $this->credit_usage);
    }

    public function getCreditPercentageAttribute()
    {
        if ($this->credit_limit <= 0) return 0;
        return round(($this->credit_usage / $this->credit_limit) * 100, 1);
    }

    public function getCreditProgressClassAttribute()
    {
        $percentage = $this->credit_percentage;
        if ($percentage >= 90) return 'danger';
        if ($percentage >= 70) return 'warning';
        return 'success';
    }

    public function getCreditInfoAttribute()
    {
        $used = $this->credit_usage;
        $limit = $this->credit_limit;
        $percentage = $this->credit_percentage;
        $progressClass = $this->credit_progress_class;

        return '<div class="progress" style="height: 20px;">
            <div class="progress-bar bg-' . $progressClass . '"
                 role="progressbar"
                 style="width: ' . $percentage . '%"
                 aria-valuenow="' . $percentage . '"
                 aria-valuemin="0"
                 aria-valuemax="100">
                ' . number_format($used, 2) . ' / ' . number_format($limit, 2) . ' DH
            </div>
        </div>';
    }

    /**
     * Get purchase summary for the client
     */
    public function getPurchaseSummaryAttribute()
    {
        $orders = $this->salesOrders;
        $total = $orders->sum('final_amount');
        $paid = $orders->sum('paid_amount');
        $unpaid = $total - $paid;
        $ordersCount = $orders->count();

        $progress = $total > 0 ? round(($paid / $total) * 100, 2) : 0;
        $progressClass = $progress >= 100 ? 'success' : ($progress >= 50 ? 'warning' : 'danger');

        return [
            'total' => $total,
            'paid' => $paid,
            'unpaid' => $unpaid,
            'orders_count' => $ordersCount,
            'progress' => $progress,
            'progress_class' => $progressClass,
        ];
    }

    /**
     * Get outstanding invoices (unpaid or partially paid orders)
     */
    public function getOutstandingInvoicesAttribute()
    {
        return $this->salesOrders()
            ->whereIn('payment_status', ['pending', 'partial'])
            ->whereRaw('final_amount > paid_amount')
            ->orderBy('order_date', 'asc')
            ->get();
    }

    /**
     * Get last purchase date
     */
    public function getLastPurchaseDateAttribute()
    {
        $lastOrder = $this->salesOrders()->orderBy('order_date', 'desc')->first();
        return $lastOrder ? $lastOrder->order_date->format('d/m/Y') : 'Aucun achat';
    }

    /**
     * Get average purchase value
     */
    public function getAveragePurchaseValueAttribute()
    {
        $ordersCount = $this->salesOrders()->count();
        if ($ordersCount == 0) return 0;

        $total = $this->salesOrders()->sum('final_amount');
        return $total / $ordersCount;
    }

    /**
     * Check if client has credit available
     */
    public function getHasCreditAttribute()
    {
        return $this->credit_limit > 0;
    }

    /**
     * Get formatted credit available
     */
    public function getCreditFormattedAttribute()
    {
        return $this->has_credit ? number_format($this->credit_available, 2) . ' DH' : '0,00 DH';
    }

    /**
     * Get credit warning message
     */
    public function getCreditWarningAttribute()
    {
        if (!$this->has_credit) {
            return "Ce client n'a pas de limite de crédit définie";
        }

        if ($this->credit_available <= 0) {
            return "Limite de crédit atteinte. Utilisé: " . number_format($this->credit_usage, 2) . " DH sur " . number_format($this->credit_limit, 2) . " DH";
        }

        return "Crédit disponible: " . $this->credit_formatted . " sur " . number_format($this->credit_limit, 2) . " DH";
    }

    /**
     * Use credit (when creating an unpaid order)
     */
    public function useCredit($amount, $order, $reference = null)
    {
        $previousUsage = $this->credit_usage;
        $newUsage = $previousUsage + $amount;

        // if ($newUsage > $this->credit_limit) {
        //     throw new \Exception('Limite de crédit dépassée. Maximum: ' . number_format($this->credit_limit, 2) . ' DH');
        // }

        $this->credit_usage = $newUsage;
        $this->save();

        // Log credit usage in balance history
        $this->balanceHistory()->create([
            'previous_balance' => $previousUsage,
            'new_balance' => $newUsage,
            'amount' => $amount,
            'type' => 'credit_used',
            'reference_type' => 'sales_order',
            'reference_id' => $order->order_id,
            'description' => $reference ?: 'Utilisation crédit pour commande #' . $order->order_number,
            'created_by' => auth()->id(),
        ]);

        return $this;
    }

    /**
     * Release credit (when order is paid or deleted)
     */
    public function releaseCredit($amount, $order, $reference = null)
    {
        $previousUsage = $this->credit_usage;
        $newUsage = max(0, $previousUsage - $amount);

        $this->credit_usage = $newUsage;
        $this->save();

        // Log credit release
        $this->balanceHistory()->create([
            'previous_balance' => $previousUsage,
            'new_balance' => $newUsage,
            'amount' => -$amount,
            'type' => 'credit_released',
            'reference_type' => 'sales_order',
            'reference_id' => $order->order_id,
            'description' => $reference ?: 'Libération crédit pour commande #' . $order->order_number,
            'created_by' => auth()->id(),
        ]);

        return $this;
    }

    /**
     * Update credit usage based on orders
     */
    public function updateCreditUsage()
    {
        $unpaidTotal = $this->salesOrders()
            ->whereIn('payment_status', ['pending', 'partial'])
            ->sum(DB::raw('final_amount - paid_amount'));

        $this->credit_usage = $unpaidTotal;
        $this->save();

        return $this;
    }

    /**
     * BALANCE ACCESSORS
     */
    public function getBalanceFormattedAttribute()
    {
        if ($this->balance > 0) {
            return '<span class="text-success">' . number_format($this->balance, 2) . ' DH</span>';
        } elseif ($this->balance < 0) {
            return '<span class="text-danger">' . number_format($this->balance, 2) . ' DH</span>';
        }
        return '<span class="text-secondary">0,00 DH</span>';
    }

    public function getBalanceStatusAttribute()
    {
        if ($this->balance > 0) {
            return [
                'label' => 'Trop-perçu (Nous devons)',
                'class' => 'success',
                'icon' => 'fas fa-arrow-up'
            ];
        } elseif ($this->balance < 0) {
            return [
                'label' => 'Impayé (Client doit)',
                'class' => 'danger',
                'icon' => 'fas fa-arrow-down'
            ];
        }
        return [
            'label' => 'Soldé',
            'class' => 'secondary',
            'icon' => 'fas fa-check'
        ];
    }

    public function getBalanceBadgeAttribute()
    {
        $status = $this->balance_status;
        return '<span class="badge bg-' . $status['class'] . '">' .
            '<i class="' . $status['icon'] . ' me-1"></i>' .
            $status['label'] . ': ' . number_format(abs($this->balance), 2) . ' DH</span>';
    }

    /**
     * Get available advance (positive balance = client has overpaid)
     */
    public function getAvailableAdvanceAttribute()
    {
        return $this->balance > 0 ? $this->balance : 0;
    }

    /**
     * Get total amount client owes us (negative balance)
     */
    public function getTotalDebtAttribute()
    {
        return $this->balance < 0 ? abs($this->balance) : 0;
    }

    /**
     * Check if client has advance to use
     */
    public function getHasAdvanceAttribute()
    {
        return $this->available_advance > 0;
    }

    /**
     * Get formatted advance
     */
    public function getAdvanceFormattedAttribute()
    {
        return $this->has_advance ? number_format($this->available_advance, 2) . ' DH' : '0,00 DH';
    }

    /**
     * Check if client has debt
     */
    public function getHasDebtAttribute()
    {
        return $this->balance < 0;
    }

    /**
     * Get formatted debt
     */
    public function getDebtFormattedAttribute()
    {
        return $this->has_debt ? number_format($this->total_debt, 2) . ' DH' : '0,00 DH';
    }
    /**
     * Use advance for payment
     */
    public function useAdvance($amount, $order, $reference = null)
    {
        if ($amount > $this->available_advance) {
            throw new \Exception('Solde insuffisant. Disponible: ' . $this->advance_formatted);
        }

        $previousBalance = $this->balance;
        $this->balance -= $amount;
        $this->save();

        $this->balanceHistory()->create([
            'previous_balance' => $previousBalance,
            'new_balance' => $this->balance,
            'amount' => -$amount,
            'type' => 'advance_used',
            'reference_type' => 'sales_order',
            'reference_id' => $order->order_id,
            'description' => $reference ?: 'Utilisation solde pour commande #' . $order->order_number,
            'created_by' => auth()->id(),
        ]);

        return $this;
    }

    /**
     * Reverse advance usage
     */
    public function reverseAdvance($amount, $order, $reference = null)
    {
        $previousBalance = $this->balance;
        $this->balance += $amount;
        $this->save();

        $this->balanceHistory()->create([
            'previous_balance' => $previousBalance,
            'new_balance' => $this->balance,
            'amount' => $amount,
            'type' => 'advance_reversed',
            'reference_type' => 'sales_order',
            'reference_id' => $order->order_id,
            'description' => $reference ?: 'Annulation utilisation solde pour commande #' . $order->order_number,
            'created_by' => auth()->id(),
        ]);

        return $this;
    }

    /**
     * BALANCE UPDATE METHODS
     */
    public function updateBalanceFromOrder($order, $type, $oldAmount = null)
    {
        $impact = 0;
        $description = '';

        switch ($type) {
            case 'order_created':
                $unpaidAmount = $order->final_amount - $order->paid_amount;
                if ($unpaidAmount > 0) {
                    $impact = -$unpaidAmount;
                    $description = "Commande #{$order->order_number} créée: " .
                                number_format($order->final_amount, 2) . " DH (Non payé: " .
                                number_format($unpaidAmount, 2) . " DH)";
                } elseif ($order->paid_amount > $order->final_amount) {
                    $overpaid = $order->paid_amount - $order->final_amount;
                    $impact = $overpaid;
                    $description = "Commande #{$order->order_number} créée avec trop-perçu: " .
                                number_format($overpaid, 2) . " DH";
                }
                break;

            case 'order_updated':
                // $oldAmount = old unpaid amount (final_amount - paid_amount before the update)
                $oldUnpaid = $oldAmount ?? ($order->final_amount - $order->paid_amount);
                $newUnpaid = $order->final_amount - $order->paid_amount;
                $unpaidDiff = $newUnpaid - $oldUnpaid;

                if ($unpaidDiff != 0) {
                    $impact = -$unpaidDiff;
                    $description = "Commande #{$order->order_number} modifiée: " .
                                ($unpaidDiff > 0 ? 'Augmentation' : 'Diminution') .
                                " du montant dû de " . number_format(abs($unpaidDiff), 2) . " DH";
                }
                break;

            case 'payment_added':
                $oldPaid = $oldAmount ?? 0;
                $newPaid = $order->paid_amount;
                $paymentAmount = $newPaid - $oldPaid;

                $totalPaid = $order->paid_amount;
                $orderTotal = $order->final_amount;

                if ($totalPaid > $orderTotal) {
                    $overpaid = $totalPaid - $orderTotal;
                    $previousOverpaid = max(0, ($oldPaid - $orderTotal));
                    $newOverpaid = $overpaid;

                    if ($newOverpaid > $previousOverpaid) {
                        $impact = $newOverpaid - $previousOverpaid;
                        $description = "Trop-perçu sur commande #{$order->order_number}: +" .
                                    number_format($impact, 2) . " DH";
                    }
                } else {
                    $impact = $paymentAmount;
                    $description = "Paiement ajouté sur commande #{$order->order_number}: +" .
                                number_format($paymentAmount, 2) . " DH";
                }
                break;

            case 'payment_deleted':
                $paymentAmount = $oldAmount ?? 0;
                $totalPaid = $order->paid_amount;
                $orderTotal = $order->final_amount;

                if ($totalPaid + $paymentAmount > $orderTotal) {
                    $oldOverpaid = ($totalPaid + $paymentAmount) - $orderTotal;
                    $newOverpaid = max(0, $totalPaid - $orderTotal);
                    $overpaidReduction = $oldOverpaid - $newOverpaid;

                    if ($overpaidReduction > 0) {
                        $impact = -$overpaidReduction;
                        $description = "Suppression paiement réduit le trop-perçu de " .
                                    number_format($overpaidReduction, 2) . " DH";
                    }
                } else {
                    $impact = -$paymentAmount;
                    $description = "Paiement supprimé sur commande #{$order->order_number}: -" .
                                number_format($paymentAmount, 2) . " DH";
                }
                break;
        }

        if ($impact != 0) {
            $previousBalance = $this->balance;
            $newBalance = $previousBalance + $impact;

            DB::transaction(function () use ($previousBalance, $newBalance, $impact, $type, $order, $description) {
                $this->update(['balance' => $newBalance]);

                $this->balanceHistory()->create([
                    'previous_balance' => $previousBalance,
                    'new_balance' => $newBalance,
                    'amount' => $impact,
                    'type' => $type,
                    'reference_type' => 'sales_order',
                    'reference_id' => $order->order_id,
                    'description' => $description,
                    'created_by' => auth()->id(),
                ]);
            });
        }

        return $this;
    }

    /**
     * SCOPES
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithCredit($query)
    {
        return $query->where('credit_limit', '>', 0);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('entreprise_name', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
}
