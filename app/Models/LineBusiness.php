<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineBusiness extends Model
{
    use HasFactory;
    
    protected $table = 'line_business';

    protected $fillable = [
        'name',
    ];
}
