<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiocidesGYT extends Model
{
    use HasFactory;
    protected $table = 'biocidesgyt';

    protected $fillable = [
        'type_biocide_id',
        'biocide_grup_id'
    ];
}
