<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filenames extends Model
{
    use HasFactory;

    protected $table = 'filenames';
    protected $fillable = [
        'id',
        'name',
        'type',
    ];
}
