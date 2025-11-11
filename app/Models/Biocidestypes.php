<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biocidestypes extends Model
{
    use HasFactory;
    
    protected $table = 'biocides_types';

    protected $fillable = [
        'name',
    ];
}
