<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PestOrder extends Model
{
    use HasFactory;

    protected $table = 'pest_order';

    protected $fillable = [
        'order_id',
        'pest_catalog_id',
    ];
}
