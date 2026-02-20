<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/'
    ];
    
    /**
     * Handle an incoming request.
     * 
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // Aumentar límite de tamaño para peticiones con imágenes base64
        if ($request->is('report/notes/update')) {
            ini_set('post_max_size', '50M');
            ini_set('upload_max_filesize', '10M');
            ini_set('memory_limit', '256M');
        }
        
        return parent::handle($request, $next);
    }
