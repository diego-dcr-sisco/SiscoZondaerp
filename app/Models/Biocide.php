<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biocide extends Model
{
    use HasFactory;
    
    protected $table = 'biocide';

    protected $fillable = [
        'id',
        'type',
        'group',
        'created_at',
        'updated_at',
    ];
}
