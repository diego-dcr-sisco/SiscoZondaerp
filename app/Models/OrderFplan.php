<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderFplan extends Model
{
    use HasFactory;
    protected $table = 'order_floorplan';

    protected $fillable = [
        'id',
        'floorplan_id',
        'version_id',
    ];
}
