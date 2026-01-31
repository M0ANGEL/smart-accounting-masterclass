<?php

return [
    'environment' => env('WOMPI_ENVIRONMENT', 'sandbox'),
    
    'keys' => [
        'public' => env('WOMPI_PUBLIC_KEY', 'pub_test_XXXXX'),
        'private' => env('WOMPI_PRIVATE_KEY', 'prv_test_XXXXX'),
    ],
    
    'urls' => [
        'sandbox' => [
            'base' => 'https://sandbox.wompi.co',
            'api' => 'https://sandbox.wompi.co/v1',
        ],
        'production' => [
            'base' => 'https://wompi.co',
            'api' => 'https://api.wompi.co/v1',
        ],
    ],
    
    'currency' => 'COP',
    'amounts' => [
        'masterclass' => 15000000, // 150,000 COP en centavos
    ],
    
    'qr_link' => env('WOMPI_QR_LINK', 'https://checkout.wompi.co/l/etHnm3'),
    
    'supported_countries' => [
        'CO' => ['name' => 'Colombia', 'code' => '+57'],
        'EC' => ['name' => 'Ecuador', 'code' => '+593'],
        'PE' => ['name' => 'Perú', 'code' => '+51'],
        'MX' => ['name' => 'México', 'code' => '+52'],
        'CL' => ['name' => 'Chile', 'code' => '+56'],
    ],
    
    'webhook_secret' => env('WOMPI_WEBHOOK_SECRET'),
    
    'email' => [
        'from' => env('MAIL_FROM_ADDRESS', 'info@smartaccounting.com'),
        'from_name' => env('MAIL_FROM_NAME', 'Smart Accounting'),
        'support' => 'soporte@smartaccounting.com',
    ],
];