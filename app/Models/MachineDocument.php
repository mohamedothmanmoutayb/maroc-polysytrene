<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class MachineDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'machine_documents';
    protected $primaryKey = 'document_id';
    public $timestamps = true;

    protected $fillable = [
        'machine_id',
        'document_type_id',
        'document_number',
        'start_date',
        'end_date',
        'issuing_authority',
        'notes',
        'is_current',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class, 'machine_id');
    }

    public function documentType()
    {
        return $this->belongsTo(MachineDocumentType::class, 'document_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusAttribute()
    {
        if (!$this->end_date) return 'unknown';

        $now = Carbon::now();
        if ($this->end_date < $now) {
            return 'expired';
        }

        $daysLeft = $now->diffInDays($this->end_date);
        if ($daysLeft <= ($this->documentType->reminder_days_before ?? 10)) {
            return 'expiring_soon';
        }

        return 'valid';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'valid' => '<span class="badge bg-success">Valide</span>',
            'expiring_soon' => '<span class="badge bg-warning text-dark">Expire bientôt</span>',
            'expired' => '<span class="badge bg-danger">Expiré</span>',
            'unknown' => '<span class="badge bg-secondary">Non renseigné</span>',
        ];

        return $badges[$this->status] ?? $badges['unknown'];
    }

    public function getDaysLeftAttribute()
    {
        if (!$this->end_date || $this->end_date < Carbon::now()) {
            return 0;
        }
        return Carbon::now()->diffInDays($this->end_date);
    }
}
