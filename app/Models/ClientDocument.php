<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientDocument extends Model
{
    use HasFactory;

    protected $table = 'client_documents';
    protected $primaryKey = 'document_id';
    public $timestamps = true;

    protected $fillable = [
        'client_id',
        'document_type',
        'document_name',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getDocumentTypeLabelAttribute()
    {
        return match($this->document_type) {
            'cin' => 'Carte Nationale d\'Identité',
            'ice' => 'ICE',
            'rc' => 'Registre de Commerce',
            'patente' => 'Patente',
            'contrat' => 'Contrat',
            'facture' => 'Facture',
            'autre' => 'Autre',
            default => ucfirst($this->document_type),
        };
    }
}
