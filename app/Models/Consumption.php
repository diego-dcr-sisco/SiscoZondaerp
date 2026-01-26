<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Consumption extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo
     */
    protected $table = 'consumptions';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'customer_id',
        'zone_id',
        'product_id',
        'units',
        'user_id',
        'amount',
        'month',
        'year',
        'status',
        'observation',
        'file',
        'type'
    ];

    /**
     * Casts para los campos
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'year' => 'integer',
        'month' => 'integer'
    ];

    /**
     * Meses válidos (números de mes)
     */
    const MONTHS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

    /**
     * Nombres de meses en español
     */
    const MONTH_NAMES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];

    /**
     * Estados válidos
     */
    const STATUSES = [
        'pending' => 'Pendiente',
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado'
    ];

    /**
     * Relación con el modelo Customer
     * Un consumo pertenece a un cliente
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relación con el modelo Zone
     * Un consumo pertenece a una zona
     */
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    /**
     * Relación con el modelo ProductCatalog
     * Un consumo pertenece a un producto
     */
    public function product()
    {
        return $this->belongsTo(ProductCatalog::class, 'product_id');
    }

    /**
     * Relación con el modelo User
     * Un consumo es registrado por un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con el modelo ConsumptionSupply
     * Un consumo puede tener información de surtido
     */
    public function supply()
    {
        return $this->hasOne(ConsumptionSupply::class);
    }

    /**
     * Scope para consumos por cliente
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope para consumos por zona
     */
    public function scopeByZone($query, $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    /**
     * Scope para consumos por mes y año
     */
    public function scopeByPeriod($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    /**
     * Scope para consumos pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Accessor para el mes en español
     */
    public function getMonthSpanishAttribute()
    {
        if (!isset($this->month) || !in_array($this->month, self::MONTHS)) {
            return 'Mes desconocido';
        }
        return self::MONTH_NAMES[$this->month];
    }

    /**
     * Obtener el estado formateado
     */
    public function getStatusFormattedAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return 'Pendiente';
            case 'approved':
                return 'Aprobado';
            case 'rejected':
                return 'Rechazado';
            default:
                return 'Sin estado';
        }
    }

    /**
     * Accessor para el período formateado
     */
    public function getPeriodFormattedAttribute()
    {
        return $this->month_spanish . ' ' . $this->year;
    }
}
