<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'username',
        'password',
        'email',
        'role',
        'is_active',
        'profile_photo',
        'phone',
        'email_verified_at',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $guard_name = 'web';

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'recorded_by');
    }

    public function approvedExpenses()
    {
        return $this->hasMany(Expense::class, 'approved_by');
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class, 'created_by');
    }

    public function productionOutputs()
    {
        return $this->hasMany(ProductionOutput::class, 'approved_by');
    }

    public function rawMaterialPurchases()
    {
        return $this->hasMany(RawMaterialPurchase::class, 'created_by');
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'recorded_by');
    }

    public function stockMovements()
    {
        return $this->hasMany(RawMaterialStockMovement::class, 'performed_by');
    }

    public function stockAuditLogs()
    {
        return $this->hasMany(StockAuditLog::class, 'performed_by');
    }

    public function resolvedStockAlerts()
    {
        return $this->hasMany(StockAlert::class, 'resolved_by');
    }

    public function isAdmin()
    {
        return $this->hasRole(['admin', 'Super Admin']) || $this->role === 'admin';
    }

    public function isManager()
    {
        return $this->hasRole(['manager', 'Manager']) || $this->role === 'manager';
    }

    public function isSales()
    {
        return $this->hasRole(['sales', 'Vente et Caisse']) || $this->role === 'sales';
    }

    public function isProduction()
    {
        return $this->hasRole(['production', 'Production', 'Decoupage']) || $this->role === 'production';
    }

    /**
     * Sync roles from old role column to Spatie roles
     */
    public function syncRoleFromLegacy()
    {
        if ($this->role && !$this->roles()->count()) {
            $role = match($this->role) {
                'admin' => 'admin',
                'manager' => 'manager',
                'supervisor' => 'supervisor',
                'accountant' => 'accountant',
                'sales' => 'sales',
                'production' => 'production',
                default => 'user'
            };
            $this->assignRole($role);
        }
    }
}
