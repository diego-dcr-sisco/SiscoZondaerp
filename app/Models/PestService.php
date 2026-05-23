<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PestService extends Model
{
    use HasFactory;

    protected $table = 'pest_service';

    protected $fillable = [
        'service_id',
        'pest_id',
    ];
}
