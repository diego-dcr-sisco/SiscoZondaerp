<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $email = $request->email;
        $password = $request->password;

        if ($email) {
            $user = User::where('email', $email)->orWhere('username', $email)->first();
            
            if($user && Hash::check($password, $user->password)){
                // Generar nuevo token de sesión
                $newToken = Str::random(60);
                
                // Actualizar token en el usuario
                $user->session_token = $newToken;
                $user->save();
                
                // Autenticar al usuario
                $request->authenticate();
                
                // Guardar el token en la sesión actual
                $request->session()->put('user_session_token', $newToken);
                $request->session()->regenerate();
                
                // Determinar la ruta de destino según el rol
                $redirectRoute = Auth::user()->type_id == 1 
                    ? 'dashboard' 
                    : 'client.index';
                
                $routeParams = Auth::user()->type_id == 1 
                    ? [] 
                    : ['section' => 1];
                
                // Guardar la información de redirección en la sesión
                $request->session()->put('redirect_route', $redirectRoute);
                $request->session()->put('route_params', $routeParams);
                
                // Redirigir a la pantalla de carga
                return redirect()->route('loading-erp');
                
            } else {
                return redirect('/login')->withErrors([
                    'error' => __('auth.failed_user'),
                ]);
            }
        } else {
            return redirect('/login')->withErrors([
                'error' => __('auth.failed_email'),
            ]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Limpiar el token de sesión al cerrar sesión
        if (Auth::check()) {
            $session_user = Auth::user();
            $user = User::find($session_user->id);
            $user->session_token = null;
            $user->save();
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}