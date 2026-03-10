<?php

use App\Http\Controllers\Api\LocationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes para Ubicaciones
|--------------------------------------------------------------------------
|
| Estas rutas manejan el seguimiento de ubicaciones GPS de los usuarios
| desde la aplicación móvil. Todas las rutas requieren autenticación 
| mediante Laravel Sanctum.
|
*/

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    // Actualizar ubicación del usuario autenticado
    Route::post('/location/update', [LocationController::class, 'update'])
        ->name('api.location.update');

    // Obtener última ubicación de un usuario
    Route::get('/location/last/{userId}', [LocationController::class, 'getLast'])
        ->name('api.location.last');

    // Obtener historial de ubicaciones de un usuario
    Route::get('/location/history/{userId}', [LocationController::class, 'getHistory'])
        ->name('api.location.history');

    // Obtener ubicaciones recientes de todos los usuarios
    Route::get('/location/recent', [LocationController::class, 'getRecent'])
        ->name('api.location.recent');

    // Obtener última ubicación de todos los usuarios activos
    Route::get('/location/all-users', [LocationController::class, 'getAllUsers'])
        ->name('api.location.all');
});
