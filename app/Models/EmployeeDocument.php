<?php
// app/Models/EmployeeDocument.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $table = 'employee_documents';
    protected $primaryKey = 'document_id';

    protected $fillable = [
        'employee_id',
        'document_name',
        'document_type',
        'file_path',
        'category',
        'description',
        'mime_type',
        'file_size',
        'is_confidentiel',
        'uploaded_at'
    ];

    protected $casts = [
        'is_confidentiel' => 'boolean',
        'uploaded_at' => 'datetime',
        'file_size' => 'integer'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Helper method to get formatted file size
    public function getFormattedFileSizeAttribute()
    {
        if ($this->file_size < 1024) {
            return $this->file_size . ' KB';
        } elseif ($this->file_size < 1024 * 1024) {
            return round($this->file_size / 1024, 2) . ' MB';
        } else {
            return round($this->file_size / (1024 * 1024), 2) . ' GB';
        }
    }

    // Helper method to get icon based on mime type
    public function getFileIconAttribute()
    {
        $mimeToIcon = [
            'application/pdf' => 'fa-file-pdf',
            'image/' => 'fa-file-image',
            'application/msword' => 'fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
            'application/vnd.ms-excel' => 'fa-file-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa-file-excel',
            'text/plain' => 'fa-file-alt',
        ];

        foreach ($mimeToIcon as $pattern => $icon) {
            if (strpos($this->mime_type, $pattern) === 0) {
                return $icon;
            }
        }

        return 'fa-file';
    }
}
