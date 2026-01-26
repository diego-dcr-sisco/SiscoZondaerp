<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckSingleSession
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        
        if ($user) {
            // Obtener el token de sesión almacenado en la base de datos
            $currentToken = $user->session_token;
            
            // Obtener el token de la sesión actual
            $sessionToken = $request->session()->get('user_session_token');
            
            // Si los tokens no coinciden, cerrar la sesión
            if ($currentToken != $sessionToken) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect('/login')->withErrors([
                    'message' => 'Tu cuenta ha iniciado sesión en otro dispositivo.'
                ]);
            }
        }
        
        return $next($request);
    }
}