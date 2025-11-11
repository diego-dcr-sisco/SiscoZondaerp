<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationArea extends Model
{
    use HasFactory;

    protected $table = "application_areas";

    protected $fillable = [
        'id',
        'customer_id',
        'zone_type_id',
        'm2',
        'name'
    ];

    public function zoneType() {
        return $this->belongsTo(ZoneType::class, 'zone_type_id');
    }
}
