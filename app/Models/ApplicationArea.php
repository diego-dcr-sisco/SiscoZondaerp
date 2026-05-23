<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\MockObject\Stub\ReturnReference;

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

    public function devices() {
        return $this->hasMany(Device::class, 'application_area_id');
    }

    public function devicesByVersion($version) {
        return $this->devices()->where('version', $version)->get();
    }
}
