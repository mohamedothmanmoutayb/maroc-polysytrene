<?php
    
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MachineDocumentType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'machine_document_types';
    protected $primaryKey = 'document_type_id';
    public $timestamps = true;

    protected $fillable = [
        'type_code',
        'type_name',
        'description',
        'reminder_days_before',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'reminder_days_before' => 'integer',
        'sort_order' => 'integer',
    ];

    public function documents()
    {
        return $this->hasMany(MachineDocument::class, 'document_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
