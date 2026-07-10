<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialCategory extends Model
{
    use HasFactory;

    protected $table = 'raw_material_categories';
    protected $primaryKey = 'category_id';
    public $timestamps = false;

    protected $fillable = [
        'category_name',
        'description',
        'parent_category_id',
    ];

    public function parent()
    {
        return $this->belongsTo(RawMaterialCategory::class, 'parent_category_id');
    }

    public function children()
    {
        return $this->hasMany(RawMaterialCategory::class, 'parent_category_id');
    }

    public function rawMaterials()
    {
        return $this->hasMany(RawMaterial::class, 'category_id');
    }
}
