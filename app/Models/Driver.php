<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'drivers';
    protected $primaryKey = 'driver_id';
    public $timestamps = true;

    protected $fillable = [
        'employee_id',
        'license_number',
        'license_expiry_date',
        'license_category',
        'medical_visit_date',
        'next_medical_visit_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
        'medical_visit_date' => 'date',
        'next_medical_visit_date' => 'date',
    ];

    // Relationship with Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function assignments()
    {
        return $this->hasMany(VehicleAssignment::class, 'driver_id');
    }

    public function currentVehicle()
    {
        return $this->hasOne(VehicleAssignment::class, 'driver_id')
            ->where('status', 'active')
            ->with('vehicle');
    }

    // Accessors to get employee information
    public function getFullNameAttribute()
    {
        return $this->employee ? $this->employee->full_name : 'N/A';
    }

    public function getCinAttribute()
    {
        return $this->employee ? $this->employee->cin : 'N/A';
    }

    public function getPhoneAttribute()
    {
        return $this->employee ? $this->employee->phone : 'N/A';
    }

    public function getEmailAttribute()
    {
        return $this->employee ? $this->employee->email : 'N/A';
    }

    public function getAddressAttribute()
    {
        return $this->employee ? $this->employee->address : 'N/A';
    }

    public function getPhotoAttribute()
    {
        return $this->employee ? $this->employee->photo : null;
    }

    public function getHireDateAttribute()
    {
        return $this->employee ? $this->employee->hire_date : null;
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'active' => 'Actif',
            'inactive' => 'Inactif',
            'suspended' => 'Suspendu',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => 'success',
            'inactive' => 'danger',
            'suspended' => 'warning',
        ];
        $color = $badges[$this->status] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . $this->status_label . '</span>';
    }

    // Check if license is expiring soon (within 10 days)
    public function getLicenseExpiringSoonAttribute()
    {
        if (!$this->license_expiry_date) return false;
        $daysLeft = Carbon::now()->diffInDays($this->license_expiry_date, false);
        return $daysLeft <= 10 && $daysLeft >= 0;
    }

    // Check if medical visit is due soon (within 10 days)
    public function getMedicalVisitDueSoonAttribute()
    {
        if (!$this->next_medical_visit_date) return false;
        $daysLeft = Carbon::now()->diffInDays($this->next_medical_visit_date, false);
        return $daysLeft <= 10 && $daysLeft >= 0;
    }

    /**
     * Get all notifications for driver
     */
    public function getNotificationsAttribute()
    {
        $notifications = [];

        // Check license expiration
        if ($this->license_expiry_date) {
            $daysLeft = Carbon::now()->diffInDays($this->license_expiry_date, false);

            if ($daysLeft < 0) {
                $notifications[] = [
                    'type' => 'driver_license_expired',
                    'title' => 'Permis de conduire expiré',
                    'message' => "Le permis de conduire de {$this->full_name} est expiré depuis " . abs($daysLeft) . " jours.",
                    'date' => $this->license_expiry_date,
                    'color' => 'danger',
                    'icon' => 'fas fa-id-card'
                ];
            } elseif ($daysLeft <= 30 && $daysLeft >= 0) {
                $color = $daysLeft <= 10 ? 'danger' : ($daysLeft <= 20 ? 'warning' : 'info');
                $notifications[] = [
                    'type' => 'driver_license_expiring',
                    'title' => 'Permis de conduire expirant bientôt',
                    'message' => "Le permis de conduire de {$this->full_name} expire dans {$daysLeft} jours.",
                    'date' => $this->license_expiry_date,
                    'color' => $color,
                    'icon' => 'fas fa-id-card'
                ];
            }
        }

        // Check medical visit
        if ($this->next_medical_visit_date) {
            $daysLeft = Carbon::now()->diffInDays($this->next_medical_visit_date, false);

            if ($daysLeft < 0) {
                $notifications[] = [
                    'type' => 'driver_medical_overdue',
                    'title' => 'Visite médicale en retard',
                    'message' => "La visite médicale de {$this->full_name} est en retard de " . abs($daysLeft) . " jours.",
                    'date' => $this->next_medical_visit_date,
                    'color' => 'danger',
                    'icon' => 'fas fa-stethoscope'
                ];
            } elseif ($daysLeft <= 30 && $daysLeft >= 0) {
                $color = $daysLeft <= 10 ? 'warning' : 'info';
                $notifications[] = [
                    'type' => 'driver_medical_upcoming',
                    'title' => 'Visite médicale prévue',
                    'message' => "La visite médicale de {$this->full_name} est prévue dans {$daysLeft} jours.",
                    'date' => $this->next_medical_visit_date,
                    'color' => $color,
                    'icon' => 'fas fa-stethoscope'
                ];
            }
        }

        return $notifications;
    }
    }
