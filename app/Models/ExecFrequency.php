<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExecFrequency extends Model
{
    use HasFactory;

    protected $table = 'exec_frequency';

    protected $fillable = [
        'id',
        'name'
    ];
}
