<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Recibe y almacena la ubicación de un usuario desde la app móvil
     * 
     * POST /api/location/update
     * Headers: Authorization: Bearer {token}
     * Body: {
     *   "latitude": -17.78629188,
     *   "longitude": -63.18116966,
     *   "accuracy": 15.5,
     *   "altitude": 420.5,
     *   "speed": 0.0,
     *   "timestamp": 1709654400000
     * }
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'altitude' => 'nullable|numeric',
            'speed' => 'nullable|numeric|min:0',
            'timestamp' => 'required|integer', // Unix timestamp en milisegundos
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de ubicación inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user(); // Usuario autenticado via Sanctum
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Convertir timestamp de milisegundos a DateTime
            $recordedAt = \Carbon\Carbon::createFromTimestampMs($request->timestamp);

            // Guardar en la tabla de historial
            $location = UserLocation::create([
                'user_id' => $user->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'altitude' => $request->altitude,
                'speed' => $request->speed,
                'source' => 'mobile_app',
                'recorded_at' => $recordedAt,
            ]);

            // Actualizar última ubicación en la tabla user (Opción 2)
            $user->last_latitude = $request->latitude;
            $user->last_longitude = $request->longitude;
            $user->last_location_accuracy = $request->accuracy;
            $user->last_location_at = $recordedAt;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Ubicación guardada exitosamente',
                'data' => [
                    'location_id' => $location->id,
                    'recorded_at' => $recordedAt->toIso8601String()
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar ubicación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene la última ubicación de un usuario
     * 
     * GET /api/location/last/{userId}
     */
    public function getLast($userId)
    {
        try {
            $location = UserLocation::getLastLocation($userId);

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró ubicación para este usuario'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $location
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ubicación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene el historial de ubicaciones de un usuario
     * 
     * GET /api/location/history/{userId}?start_date=2025-02-01&end_date=2025-02-24
     */
    public function getHistory(Request $request, $userId)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $locations = UserLocation::getLocationHistory($userId, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $locations,
                'count' => $locations->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene ubicaciones recientes de todos los usuarios (últimos 60 minutos)
     * 
     * GET /api/location/recent?minutes=60
     */
    public function getRecent(Request $request)
    {
        try {
            $minutes = $request->query('minutes', 60);
            $locations = UserLocation::getRecentLocations($minutes);

            $formattedLocations = $locations->map(function ($location) {
                return [
                    'user_id' => $location->user_id,
                    'user_name' => $location->user->name,
                    'user_email' => $location->user->email,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'accuracy' => $location->accuracy,
                    'recorded_at' => $location->recorded_at->toIso8601String(),
                    'minutes_ago' => $location->recorded_at->diffInMinutes(now()),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedLocations,
                'count' => $formattedLocations->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ubicaciones recientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene ubicaciones de todos los usuarios con su última posición conocida
     * 
     * GET /api/location/all-users
     */
    public function getAllUsers()
    {
        try {
            $users = User::where('status_id', 2) // Solo usuarios activos
                ->whereNotNull('last_latitude')
                ->select('id', 'name', 'email', 'last_latitude', 'last_longitude', 'last_location_accuracy', 'last_location_at')
                ->get()
                ->map(function ($user) {
                    return [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'latitude' => $user->last_latitude,
                        'longitude' => $user->last_longitude,
                        'accuracy' => $user->last_location_accuracy,
                        'last_update' => $user->last_location_at ? $user->last_location_at->toIso8601String() : null,
                        'minutes_ago' => $user->last_location_at ? $user->last_location_at->diffInMinutes(now()) : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $users,
                'count' => $users->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ubicaciones de usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
