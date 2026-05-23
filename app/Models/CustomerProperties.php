<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProperties extends Model
{
    use HasFactory;
    protected $table = 'customer_properties';
    
    protected $fillable =[
        'id',
        'customer_id',
        'property_id'
    ];
}
