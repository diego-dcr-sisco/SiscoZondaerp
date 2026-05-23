<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PestCatalog extends Model
{
    use HasFactory;

    protected $table = 'pest_catalog';

    protected $fillable = [
        'id',
        'pest_category_id',
        'pest_code',
        'name',
        'description',
        'image',
    ];

    public function pestCategory() {
        return $this->belongsTo(PestCategory::class, 'pest_category_id');
    }
}
