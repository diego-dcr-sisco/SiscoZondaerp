<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementType extends Model
{
    use HasFactory;

    protected $table = 'movement_type';

    protected $fillable = [
        'name',
        'type' => 'in' | 'out' // Define the type of movement, either 'in' for entries or 'out' for exits
    ];

    
}
