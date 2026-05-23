<?php
/**
 * DIAGNÓSTICO DE PERMISOS - TEMPORAL
 * Eliminar este archivo después de diagnosticar
 * Acceder vía: http://tudominio.com/diagnostico_permisos.php
 */

// Cargar Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Permisos</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #fff; }
        .section { background: #2d2d2d; padding: 15px; margin: 10px 0; border-left: 4px solid #007acc; }
        .ok { color: #4ec9b0; }
        .error { color: #f44747; }
        .warning { color: #dcdcaa; }
        h2 { color: #569cd6; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; border-bottom: 1px solid #3e3e3e; }
        td:first-child { color: #9cdcfe; width: 200px; }
    </style>
</head>
<body>
    <h1>🔍 Diagnóstico de Permisos - Sistema ERP</h1>
";

// ============================================
// VERIFICAR AUTENTICACIÓN
// ============================================
echo "<div class='section'>";
echo "<h2>1. Estado de Autenticación</h2>";
echo "<table>";

if (Auth::check()) {
    echo "<tr><td>✅ Usuario logueado:</td><td class='ok'>SÍ</td></tr>";
    
    $user = Auth::user();
    echo "<tr><td>ID:</td><td>{$user->id}</td></tr>";
    echo "<tr><td>Nombre:</td><td>{$user->name}</td></tr>";
    echo "<tr><td>Username:</td><td>{$user->username}</td></tr>";
    echo "<tr><td>Email:</td><td>{$user->email}</td></tr>";
    echo "<tr><td><strong>type_id:</strong></td><td class='";
    echo ($user->type_id == 1) ? "ok'><strong>✅ {$user->type_id} (INTEGRAL)</strong>" : "error'><strong>❌ {$user->type_id} (NO ES INTEGRAL)</strong>";
    echo "</td></tr>";
    echo "<tr><td>status_id:</td><td>{$user->status_id}</td></tr>";
    echo "<tr><td>role_id:</td><td>{$user->role_id}</td></tr>";
    
} else {
    echo "<tr><td class='error'>❌ Usuario logueado:</td><td class='error'>NO - Debes iniciar sesión primero</td></tr>";
}

echo "</table></div>";

// ============================================
// VERIFICAR GATES
// ============================================
if (Auth::check()) {
    echo "<div class='section'>";
    echo "<h2>2. Verificación de Gates (Permisos)</h2>";
    echo "<table>";
    
    $user = Auth::user();
    
    // Gate integral
    $integralCheck = Gate::allows('integral');
    echo "<tr><td>Gate 'integral':</td><td class='";
    echo $integralCheck ? "ok'>✅ PERMITIDO" : "error'>❌ DENEGADO";
    echo "</td></tr>";
    
    // Gate client
    $clientCheck = Gate::allows('client');
    echo "<tr><td>Gate 'client':</td><td class='";
    echo $clientCheck ? "ok'>✅ PERMITIDO" : "error'>❌ DENEGADO";
    echo "</td></tr>";
    
    // Gate only-integral
    $onlyIntegralCheck = Gate::allows('only-integral');
    echo "<tr><td>Gate 'only-integral':</td><td class='";
    echo $onlyIntegralCheck ? "ok'>✅ PERMITIDO" : "error'>❌ DENEGADO";
    echo "</td></tr>";
    
    echo "</table></div>";
}

// ============================================
// VERIFICAR SESIÓN
// ============================================
echo "<div class='section'>";
echo "<h2>3. Información de Sesión</h2>";
echo "<table>";

if (Auth::check()) {
    $user = Auth::user();
    $sessionToken = session('user_session_token');
    $dbToken = $user->session_token;
    
    echo "<tr><td>Token de sesión:</td><td>" . substr($sessionToken ?? 'NULL', 0, 20) . "...</td></tr>";
    echo "<tr><td>Token en BD:</td><td>" . substr($dbToken ?? 'NULL', 0, 20) . "...</td></tr>";
    echo "<tr><td>Tokens coinciden:</td><td class='";
    echo ($sessionToken == $dbToken) ? "ok'>✅ SÍ" : "warning'>⚠️ NO";
    echo "</td></tr>";
}

echo "<tr><td>Session ID:</td><td>" . session()->getId() . "</td></tr>";
echo "<tr><td>Session Driver:</td><td>" . config('session.driver') . "</td></tr>";

echo "</table></div>";

// ============================================
// DIAGNÓSTICO Y RECOMENDACIONES
// ============================================
echo "<div class='section'>";
echo "<h2>4. Diagnóstico y Solución</h2>";

if (Auth::check()) {
    $user = Auth::user();
    
    if ($user->type_id != 1) {
        echo "<div class='error' style='padding: 15px; background: #5a1a1a; border-left: 4px solid #f44747;'>";
        echo "<h3>❌ PROBLEMA IDENTIFICADO</h3>";
        echo "<p><strong>El usuario tiene type_id = {$user->type_id}</strong></p>";
        echo "<p>Para acceder a las rutas con middleware 'can:integral', el usuario necesita <strong>type_id = 1</strong></p>";
        echo "<br>";
        echo "<h4>Solución SQL:</h4>";
        echo "<pre style='background: #1e1e1e; padding: 10px; overflow-x: auto;'>";
        echo "-- Cambiar el type_id del usuario a 1 (Integral)\n";
        echo "UPDATE user SET type_id = 1 WHERE id = {$user->id};\n\n";
        echo "-- O por username\n";
        echo "UPDATE user SET type_id = 1 WHERE username = '{$user->username}';\n\n";
        echo "-- Verificar el cambio\n";
        echo "SELECT id, username, name, type_id FROM user WHERE id = {$user->id};";
        echo "</pre>";
        echo "<p class='warning'><strong>⚠️ Importante:</strong> Después de ejecutar el SQL, el usuario debe cerrar sesión y volver a iniciar sesión.</p>";
        echo "</div>";
    } else {
        echo "<div class='ok' style='padding: 15px; background: #1a3a1a; border-left: 4px solid #4ec9b0;'>";
        echo "<h3>✅ TODO CORRECTO</h3>";
        echo "<p>El usuario tiene los permisos correctos (type_id = 1)</p>";
        echo "<p>Si aún ves el error 403, considera:</p>";
        echo "<ul>";
        echo "<li>Limpiar caché: <code>php artisan cache:clear</code></li>";
        echo "<li>Limpiar sesión: <code>php artisan session:clear</code></li>";
        echo "<li>Cerrar sesión y volver a iniciar</li>";
        echo "<li>Verificar permisos de archivos en el servidor</li>";
        echo "</ul>";
        echo "</div>";
    }
} else {
    echo "<div class='warning' style='padding: 15px; background: #3a3a1a; border-left: 4px solid #dcdcaa;'>";
    echo "<h3>⚠️ Usuario no autenticado</h3>";
    echo "<p>Primero debes iniciar sesión en el sistema para diagnosticar permisos.</p>";
    echo "<p><a href='/login' style='color: #569cd6;'>← Ir al Login</a></p>";
    echo "</div>";
}

echo "</div>";

// ============================================
// TIPOS DE USUARIO EN BD
// ============================================
echo "<div class='section'>";
echo "<h2>5. Información de Tipos de Usuario</h2>";
echo "<table>";

try {
    $userTypes = DB::table('user_type')->get();
    foreach ($userTypes as $type) {
        echo "<tr><td>type_id = {$type->id}:</td><td>{$type->name}</td></tr>";
    }
} catch (\Exception $e) {
    echo "<tr><td class='warning'>⚠️</td><td>No se pudo cargar la tabla user_type</td></tr>";
}

echo "</table></div>";

// ============================================
// FOOTER
// ============================================
echo "<div class='section' style='border-left-color: #ce9178;'>";
echo "<h2>⚠️ Recordatorio de Seguridad</h2>";
echo "<p><strong>Este archivo expone información sensible del sistema.</strong></p>";
echo "<p class='error'>❌ ELIMINAR este archivo después de resolver el problema:</p>";
echo "<pre style='background: #1e1e1e; padding: 10px;'>rm public/diagnostico_permisos.php</pre>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 20px; color: #858585;'>";
echo "<p>Generado: " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

echo "</body></html>";
