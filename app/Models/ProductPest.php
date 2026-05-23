<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPest extends Model
{
    use HasFactory;

    protected $table = "product_pest";

    protected $fillable = [
        'id',
        'product_id',
        'pest_id',
    ];
}
