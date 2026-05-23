<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndirectProduct extends Model
{
    use HasFactory;

    protected $table = 'indirect_products';

    protected $fillable = [
        'code',
        'description',
        'quantity',
        'purchase_value',
        'commercial_value',
        'base_stock'
    ];

    public $timestamps = true;

    
}
