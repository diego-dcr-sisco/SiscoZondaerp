<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FloorplanVersion extends Model
{
    use HasFactory;

    protected $table = 'floorplan_version';

    protected $fillable = [
        'id',
        'floorplan_id',
        'version',
        'created_at',
        'updated_at'
    ];
}
