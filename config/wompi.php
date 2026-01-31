<?php

return [
    'environment' => env('WOMPI_ENVIRONMENT', 'sandbox'),
    
    'keys' => [
        'public' => env('WOMPI_PUBLIC_KEY'),
        'private' => env('WOMPI_PRIVATE_KEY'),
        'events' => env('WOMPI_EVENTS_KEY'),
    ],
    
    'urls' => [
        'sandbox' => [
            'base' => 'https://sandbox.wompi.co/v1/',
            'api' => 'https://api-sandbox.wompi.co/v1/',
            'widget' => 'https://checkout.wompi.co/widget.js',
        ],
        'production' => [
            'base' => 'https://production.wompi.co/v1/',
            'api' => 'https://api-production.wompi.co/v1/',
            'widget' => 'https://checkout.wompi.co/widget.js',
        ],
        'local' => [
            'base' => 'https://sandbox.wompi.co/v1/',
            'api' => 'https://api-sandbox.wompi.co/v1/',
            'widget' => 'https://checkout.wompi.co/widget.js',
        ],
    ],
    
    'products' => [
        'masterclass' => [
            'name' => 'Masterclass Auditoría Analítica y Power BI',
            'amount' => 150000,
            'currency' => 'COP',
            'description' => 'Acceso completo + materiales + certificado',
        ],
    ],
    
    'amounts' => [
        'masterclass' => 150000,
    ],
    
    'currency' => 'COP',
    
    'supported_countries' => [
        'CO' => [
            'name' => 'Colombia',
            'code' => '+57',
            'currency' => 'COP',
            'legal_id_type' => 'CC',
        ],
        'EC' => [
            'name' => 'Ecuador', 
            'code' => '+593',
            'currency' => 'USD',
            'legal_id_type' => 'CI',
        ],
        'PE' => [
            'name' => 'Perú',
            'code' => '+51',
            'currency' => 'PEN',
            'legal_id_type' => 'DNI',
        ],
        'CL' => [
            'name' => 'Chile',
            'code' => '+56',
            'currency' => 'CLP',
            'legal_id_type' => 'RUT',
        ],
    ],
    
    'webhook_secret' => env('WOMPI_WEBHOOK_SECRET'),
    
    // Configuración específica para desarrollo local
    'development' => [
        'local_domain' => env('APP_URL', 'http://localhost:8000'),
        'force_https' => false, // Desactivar HTTPS forzado en local
    ],
];