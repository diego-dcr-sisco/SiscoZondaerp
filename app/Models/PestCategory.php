<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PestCategory extends Model
{

    use HasFactory;
    protected $table = 'pest_category';
    protected $fillable = [
        'id',
        'category',
    ];

    public function pests() {
        return $this->hasMany(PestCatalog::class, 'pest_category_id', 'id');
    }
}
