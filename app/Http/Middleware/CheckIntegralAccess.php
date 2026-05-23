<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;

class CheckIntegralAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (!Gate::allows('integral')) {
            // Opción 1: Retornar vista directamente
            return response()->view('errors.403', [
                'message' => 'Requieres privilegios Integral'
            ], 403);

            // Opción 2: Redireccionar
            // return redirect()->route('quality.unauthorized');
        }

        return $next($request);
    }
}
