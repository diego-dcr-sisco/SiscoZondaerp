<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosage extends Model
{
    use HasFactory;
    protected $table = 'dosage';

    protected $fillable = [
        'id',
        'prod_id',
        'methd_id',
        'zone_id'
    ];
}
