<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'status'];

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_zones', 'zone_id', 'customer_id');
    }

    public function consumptions()
    {
        return $this->hasMany(Consumption::class);
    }

    public function customerZones()
    {
        return $this->hasMany(CustomerZone::class);
    }

}
