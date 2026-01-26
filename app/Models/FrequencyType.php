<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrequencyType extends Model
{
    use HasFactory;

    protected $table = 'frequency_type';

    protected $fillable = [
        'frequency_type',
    ];
}
