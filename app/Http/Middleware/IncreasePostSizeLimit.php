<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IncreasePostSizeLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Aumentar límites para rutas que manejan imágenes base64
        if ($request->is('report/notes/update')) {
            // Intentar aumentar límites antes de ValidatePostSize
            @ini_set('post_max_size', '50M');
            @ini_set('upload_max_filesize', '10M');
            @ini_set('memory_limit', '256M');
            @ini_set('max_execution_time', '300');
            @ini_set('max_input_time', '300');
        }
        
        return $next($request);
    }
}
