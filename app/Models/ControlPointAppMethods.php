<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlPointAppMethods extends Model
{
    use HasFactory;

    protected $table = 'control_point_app_methods';

    protected $fillable = [
        'id',
        'application_method_id',
        'control_point_id'
    ];
    
}
