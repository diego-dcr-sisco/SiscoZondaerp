<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLocation extends Model
{
    use HasFactory;

    protected $table = 'user_location';

    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'accuracy',
        'altitude',
        'speed',
        'source',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'altitude' => 'decimal:2',
        'speed' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene la última ubicación de un usuario
     */
    public static function getLastLocation($userId)
    {
        return self::where('user_id', $userId)
            ->orderBy('recorded_at', 'desc')
            ->first();
    }

    /**
     * Obtiene el historial de ubicaciones de un usuario en un rango de fechas
     */
    public static function getLocationHistory($userId, $startDate = null, $endDate = null)
    {
        $query = self::where('user_id', $userId);

        if ($startDate) {
            $query->where('recorded_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('recorded_at', '<=', $endDate);
        }

        return $query->orderBy('recorded_at', 'desc')->get();
    }

    /**
     * Obtiene ubicaciones recientes de todos los usuarios activos
     */
    public static function getRecentLocations($minutes = 60)
    {
        return self::select('user_location.*')
            ->join('user', 'user_location.user_id', '=', 'user.id')
            ->where('user_location.recorded_at', '>=', now()->subMinutes($minutes))
            ->where('user.status_id', 2) // Solo usuarios activos
            ->orderBy('user_location.recorded_at', 'desc')
            ->with('user')
            ->get();
    }

    /**
     * Calcula la distancia entre dos ubicaciones (en kilómetros)
     * Usando la fórmula de Haversine
     */
    public function distanceTo($otherLocation)
    {
        $earthRadius = 6371; // Radio de la Tierra en km

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($otherLocation->latitude);
        $lonTo = deg2rad($otherLocation->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
