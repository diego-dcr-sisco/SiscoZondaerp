<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimpleRole extends Model
{
    use HasFactory;
    protected $table = 'simple_role';
    protected $fillable = [
        'name',
    ];
}
