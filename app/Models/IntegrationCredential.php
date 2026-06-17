<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationCredential extends Model
{
    protected $fillable = [
        'service',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'encrypted',
    ];
}
