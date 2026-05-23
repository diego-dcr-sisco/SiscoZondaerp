<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesticide_Restriction extends Model
{
    use HasFactory;
    protected $table = 'pesticide_restriction';
    protected $fillable = [
        'pesticide_id',
        'restriction',
    ];
}
