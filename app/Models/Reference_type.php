<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reference_type extends Model
{
    use HasFactory;
    protected $table = 'reference_type';

    protected $fillable =  [
        'name', 
    ];
}
