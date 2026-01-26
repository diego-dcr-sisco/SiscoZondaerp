<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceStates extends Model
{
    use HasFactory;

    protected $table = 'device_states';

    protected $fillable = [
        'order_id',
        'device_id',
        'is_scanned',
        'is_checked',
        'observations',
        'device_image',
        'created_at',
        'updated_at'
    ];
}
