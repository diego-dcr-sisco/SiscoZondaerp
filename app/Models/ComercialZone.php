<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComercialZone extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo
     */
    protected $table = 'comercial_zones';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'id',
        'code',
        'name',
        'description',
        'created_at',
        'updated_at'
    ];

    public function customers()
    {
        return $this->belongsToMany(
            Customer::class,
            'comercial_zone_customers',
            'comercial_zone_id',
            'customer_id'
        )->withTimestamps();
    }
}
