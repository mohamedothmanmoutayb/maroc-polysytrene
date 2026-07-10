<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleDocumentType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicle_document_types';
    protected $primaryKey = 'document_type_id';
    public $timestamps = true;

    protected $fillable = [
        'type_code',
        'type_name',
        'description',
        'is_active',
        'sort_order',
        'default_duration_days',
        'reminder_days_before',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'default_duration_days' => 'integer',
        'reminder_days_before' => 'integer',
    ];

    public function documents()
    {
        return $this->hasMany(VehicleDocument::class, 'document_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
