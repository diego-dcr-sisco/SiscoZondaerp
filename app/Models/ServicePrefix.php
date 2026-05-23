<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePrefix extends Model
{
    use HasFactory;

    protected $table = 'service_prefix';

    protected $fillable = [
        'id',
        'name',
    ];
}
