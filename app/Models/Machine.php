<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Machine extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'machines';
    protected $primaryKey = 'machine_id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'serial_number',
        'model',
        'manufacturer',
        'purchase_date',
        'operating_hours',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    public function documents()
    {
        return $this->hasMany(MachineDocument::class, 'machine_id');
    }

    public function currentDocuments()
    {
        return $this->hasMany(MachineDocument::class, 'machine_id')->where('is_current', true);
    }

    public function getCurrentDocument($documentTypeId)
    {
        return $this->documents()
            ->where('document_type_id', $documentTypeId)
            ->where('is_current', true)
            ->first();
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'active' => 'Actif',
            'maintenance' => 'En maintenance',
            'inactive' => 'Inactif',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => 'success',
            'maintenance' => 'warning',
            'inactive' => 'danger',
        ];
        $color = $badges[$this->status] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . $this->status_label . '</span>';
    }

    public function getNotificationsAttribute()
    {
        $notifications = [];

        foreach ($this->documents as $document) {
            if (!$document->end_date || !$document->is_current) {
                continue;
            }

            $daysLeft = Carbon::now()->diffInDays($document->end_date, false);
            $documentType = $document->documentType;

            if ($daysLeft < 0) {
                $notifications[] = [
                    'type' => 'machine_document_expired',
                    'title' => 'Document expiré - ' . $documentType->type_name,
                    'message' => "Le document {$documentType->type_name} de la machine {$this->name} est expiré depuis " . abs($daysLeft) . " jours.",
                    'date' => $document->end_date,
                    'color' => 'danger',
                    'icon' => 'fas fa-file-alt'
                ];
            } elseif ($daysLeft <= ($documentType->reminder_days_before ?? 30) && $daysLeft >= 0) {
                $color = $daysLeft <= 10 ? 'danger' : ($daysLeft <= 20 ? 'warning' : 'info');
                $notifications[] = [
                    'type' => 'machine_document_expiring',
                    'title' => 'Document expirant bientôt - ' . $documentType->type_name,
                    'message' => "Le document {$documentType->type_name} de la machine {$this->name} expire dans {$daysLeft} jours.",
                    'date' => $document->end_date,
                    'color' => $color,
                    'icon' => 'fas fa-clock'
                ];
            }
        }

        return $notifications;
    }
}
