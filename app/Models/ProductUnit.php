<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    use HasFactory;
    protected $table = 'product_unit';
    
    protected $fillable = [
        'product_id',
        'measure_type',
        'measure',
        'measure_unit',
        'unit_number'
    ];
}
