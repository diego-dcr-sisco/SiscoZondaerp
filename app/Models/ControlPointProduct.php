<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlPointProduct extends Model
{
    use HasFactory;

    protected $table = 'control_point_product';

    protected $fillable = [
        'id',
        'control_point_id',
        'product_id'
    ]; 

    
}
