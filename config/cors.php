<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // Tu API real
        'http://siscoplagas.zondaerp.mx',
        'https://siscoplagas.zondaerp.mx',
        
        // Para desarrollo web
        'http://localhost:8081',
        'http://localhost:3000',
        'http://127.0.0.1:8081',
        'http://127.0.0.1:3000',
        
        // Para emuladores Android
        'http://10.0.2.2:8081',
        'http://10.0.2.2:3000',
        
        // Para dispositivos físicos (cambia según tu red)
        'http://192.168.1.*:8081', // Wildcard para toda la red 192.168.1.x
        'http://192.168.0.*:8081',
        'http://10.0.0.*:8081',
        
        // Para Expo Go
        'exp://192.168.1.*:8081',
        'exp://10.0.0.*:8081',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
