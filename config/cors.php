<?php

return [
    'paths'                    => ['api/*'],
    'allowed_methods'          => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins'          => ['*'],   // ganti dengan domain spesifik di production jika perlu
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['Content-Type', 'X-Requested-With', 'Authorization', 'X-CSRF-TOKEN'],
    'exposed_headers'          => [],
    'max_age'                  => 86400,
    'supports_credentials'     => false,
];
