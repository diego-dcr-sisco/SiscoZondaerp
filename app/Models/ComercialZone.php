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
        'name',
        'description',
        'created_at',
        'updateed_at'
    ];

    public function customers() {
        return $this->hasManyThrough(
            Customer::class,            // Modelo final (Clientes)
            ComercialZoneCustomer::class, // Modelo intermedio
            'comercial_zone_id',        // FK en la tabla intermedia que referencia a ComercialZone
            'id',                       // FK en la tabla Customer que referencia a la tabla intermedia
            'id',                       // Clave local en ComercialZone
            'customer_id'               // FK en la tabla intermedia que referencia a Customer
        );
    }
}
