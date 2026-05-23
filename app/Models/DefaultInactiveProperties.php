<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultInactiveProperties extends Model
{
    use HasFactory;
    protected $table = 'default_inactive_properties';

    protected $fillable =[
        'customer_id',
        'propertie_id'
    ];
}
