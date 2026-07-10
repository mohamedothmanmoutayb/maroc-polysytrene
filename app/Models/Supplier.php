<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'suppliers';
    protected $primaryKey = 'supplier_id';
    public $timestamps = true;

    protected $fillable = [
        'full_name',
        'representative_name',
        'company_name',
        'ice',
        'rc',
        'patente',
        'phone',
        'email',
        'address',
        'is_active',
        'supplier_type',
        'balance', // Add this field to database
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'balance' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * RELATIONSHIPS
     */
    public function rawMaterialPurchases()
    {
        return $this->hasMany(RawMaterialPurchase::class, 'supplier_id');
    }

    public function purchases()
    {
        return $this->hasMany(RawMaterialPurchase::class, 'supplier_id');
    }

    public function balanceHistory()
    {
        return $this->hasMany(SupplierBalanceHistory::class, 'supplier_id')->orderBy('created_at', 'desc');
    }

    public function paymentDocuments()
    {
        return $this->hasManyThrough(
            PurchasePaymentDocument::class,
            RawMaterialPurchase::class,
            'supplier_id',
            'purchase_id',
            'supplier_id',
            'purchase_id'
        );
    }

    public function checkAllocations()
    {
        return $this->hasManyThrough(
            CheckAllocation::class,
            RawMaterialPurchase::class,
            'supplier_id',
            'purchase_id',
            'supplier_id',
            'purchase_id'
        );
    }

    /**
     * ACCESSORS - INFORMATIONS
     */
    public function getDisplayNameAttribute()
    {
        if ($this->company_name) {
            return $this->company_name;
        }
        return $this->full_name ?: 'N/A';
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active
            ? '<span class="badge bg-success">Actif</span>'
            : '<span class="badge bg-danger">Inactif</span>';
    }

    public function getSupplierTypeLabelAttribute()
    {
        $labels = [
            'local' => 'Local',
            'international' => 'International',
            'distributor' => 'Distributeur',
            'manufacturer' => 'Fabricant',
        ];
        return $labels[$this->supplier_type] ?? $this->supplier_type;
    }

    public function getSupplierTypeBadgeAttribute()
    {
        $badgeClass = match($this->supplier_type) {
            'local' => 'success',
            'international' => 'primary',
            'distributor' => 'info',
            'manufacturer' => 'warning',
            default => 'secondary',
        };
        return '<span class="badge bg-' . $badgeClass . '">' . $this->supplier_type_label . '</span>';
    }

    /**
     * BALANCE ACCESSORS
     */
    public function getBalanceFormattedAttribute()
    {
        $class = $this->balance >= 0 ? 'text-success' : 'text-danger';
        return '<span class="' . $class . '">' . number_format($this->balance, 2) . ' DH</span>';
    }

    public function getBalanceStatusAttribute()
    {
        if ($this->balance > 0) {
            return ['label' => 'Fournisseur créditeur (nous devons)', 'class' => 'danger'];
        } elseif ($this->balance < 0) {
            return ['label' => 'Nous créditeurs (fournisseur doit)', 'class' => 'success'];
        }
        return ['label' => 'Soldé', 'class' => 'secondary'];
    }

    public function getBalanceBadgeAttribute()
    {
        $status = $this->balance_status;
        return '<span class="badge bg-' . $status['class'] . '">' . $status['label'] . '</span>';
    }

    /**
     * PURCHASE SUMMARY
     */
    public function getTotalPurchasesAttribute()
    {
        return $this->purchases()->sum('final_amount') ?: 0;
    }

    public function getTotalPaidAttribute()
    {
        return $this->purchases()->sum('paid_amount') ?: 0;
    }

    public function getTotalUnpaidAttribute()
    {
        return $this->total_purchases - $this->total_paid;
    }

    public function getPaymentProgressAttribute()
    {
        if ($this->total_purchases <= 0) return 0;
        return round(($this->total_paid / $this->total_purchases) * 100, 1);
    }

    public function getPaymentProgressClassAttribute()
    {
        $progress = $this->payment_progress;
        if ($progress >= 90) return 'success';
        if ($progress >= 50) return 'info';
        if ($progress >= 25) return 'warning';
        return 'danger';
    }

    public function getPurchaseSummaryAttribute()
    {
        return [
            'total' => $this->total_purchases,
            'paid' => $this->total_paid,
            'unpaid' => $this->total_unpaid,
            'balance' => $this->balance,
            'progress' => $this->payment_progress,
            'progress_class' => $this->payment_progress_class,
            'purchases_count' => $this->purchases()->count(),
        ];
    }

    public function getLastPurchaseDateAttribute()
    {
        $lastPurchase = $this->purchases()
            ->orderBy('purchase_date', 'desc')
            ->first();

        return $lastPurchase ? $lastPurchase->purchase_date->format('d/m/Y') : 'N/A';
    }

    public function getAveragePurchaseValueAttribute()
    {
        $count = $this->purchases()->count();
        if ($count === 0) return 0;

        return $this->total_purchases / $count;
    }

    /**
     * Get outstanding purchases (not fully paid)
     */
    public function getOutstandingPurchasesAttribute()
    {
        return $this->purchases()
            ->where(function($query) {
                $query->whereRaw('paid_amount < final_amount')
                    ->orWhereNull('paid_amount')
                    ->orWhere('paid_amount', 0);
            })
            ->orderBy('purchase_date', 'desc')
            ->get();
    }

    /**
     * Get payment methods summary
     */
    public function getPaymentMethodsSummaryAttribute()
    {
        return DB::table('purchase_payment_documents')
            ->join('raw_material_purchases', 'raw_material_purchases.purchase_id', '=', 'purchase_payment_documents.purchase_id')
            ->where('raw_material_purchases.supplier_id', $this->supplier_id)
            ->select('purchase_payment_documents.payment_method',
                     DB::raw('COUNT(*) as count'),
                     DB::raw('SUM(purchase_payment_documents.amount) as total'))
            ->groupBy('purchase_payment_documents.payment_method')
            ->get();
    }

    /**
     * BALANCE UPDATE METHODS
     */
    public function updateBalanceFromPurchase($purchase, $type, $oldAmount = null)
    {
        $impact = 0;
        $description = '';

        switch ($type) {
            case 'purchase_created':
                $impact = $purchase->final_amount;
                $description = "Achat #{$purchase->purchase_number} créé: " . number_format($purchase->final_amount, 2) . " DH";
                break;

            case 'purchase_updated':
                $impact = $purchase->final_amount - ($oldAmount ?? 0);
                $description = "Achat #{$purchase->purchase_number} modifié: " .
                              ($impact >= 0 ? '+' : '') . number_format($impact, 2) . " DH";
                break;

            case 'payment_added':
                $totalPaid = $purchase->total_paid;
                $impact = -$totalPaid;
                $description = "Paiement ajouté sur achat #{$purchase->purchase_number}: -" .
                              number_format($totalPaid, 2) . " DH";
                break;

            case 'payment_updated':
                $impact = -($purchase->total_paid - ($oldAmount ?? 0));
                $description = "Paiement modifié sur achat #{$purchase->purchase_number}: " .
                              ($impact >= 0 ? '+' : '') . number_format($impact, 2) . " DH";
                break;

            case 'payment_deleted':
                $impact = $oldAmount;
                $description = "Paiement supprimé sur achat #{$purchase->purchase_number}: +" .
                              number_format($oldAmount, 2) . " DH";
                break;
        }

        if ($impact != 0) {
            $previousBalance = $this->balance;
            $newBalance = $previousBalance + $impact;

            DB::transaction(function () use ($previousBalance, $newBalance, $impact, $type, $purchase, $description) {
                $this->update(['balance' => $newBalance]);

                $this->balanceHistory()->create([
                    'previous_balance' => $previousBalance,
                    'new_balance' => $newBalance,
                    'amount' => $impact,
                    'type' => $type,
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->purchase_id,
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

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeLocal($query)
    {
        return $query->where('supplier_type', 'local');
    }

    public function scopeInternational($query)
    {
        return $query->where('supplier_type', 'international');
    }

    public function scopeWithPositiveBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

    public function scopeWithNegativeBalance($query)
    {
        return $query->where('balance', '<', 0);
    }

    public function scopeWithZeroBalance($query)
    {
        return $query->where('balance', '=', 0);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('company_name', 'like', "%{$search}%")
              ->orWhere('full_name', 'like', "%{$search}%")
              ->orWhere('representative_name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('ice', 'like', "%{$search}%");
        });
    }
}
