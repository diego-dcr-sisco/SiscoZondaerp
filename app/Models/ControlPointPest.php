<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlPointPest extends Model
{
    use HasFactory;

    protected $table = 'control_point_pest';

    protected $fillable = [
        'id',
        'control_point_id',
        'pest_id'
    ];

}
