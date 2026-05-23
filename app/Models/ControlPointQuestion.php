<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlPointQuestion extends Model
{
    use HasFactory;
    protected $table = 'control_point_question';

    protected $fillable = [
        'id',
        'control_point_id',
        'question_id',
    ];
}
