<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Magazine extends Model
{
    use HasFactory;

    protected $table = 'magazines';
    protected $primaryKey = 'magazine_id';
    public $timestamps = true;

    protected $fillable = [
        'magazine_code',
        'magazine_name',
        'location',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function rawMaterialPurchases()
    {
        return $this->hasMany(RawMaterialPurchase::class, 'magazine_id');
    }
}
