<?php

return [
    'sat' => [
        'key_password' => env('SAT_KEY_PASSWORD', ''),
        'certificate_path' => storage_path('app/certificates/certificate.cer'),
        'private_key_path' => storage_path('app/certificates/private_key.key'),
    ]
];

