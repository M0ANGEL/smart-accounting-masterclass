<?php

return [
    'environment' => env('WOMPI_ENVIRONMENT', 'production'),
    'qr_link' => env('WOMPI_QR_LINK', 'https://checkout.wompi.co/l/etHnm3'),
    'amount' => env('WOMPI_AMOUNT', 15000000), // 150,000 COP en centavos
    'currency' => env('WOMPI_CURRENCY', 'COP'),
    
    'keys' => [
        'public' => env('WOMPI_PUBLIC_KEY', 'pub_prod_5r4Bl04to4qWHV3dRsaITn4Oz45ocbR7'),
        'private' => env('WOMPI_PRIVATE_KEY', ''),
    ],
    
    'webhook_secret' => env('WOMPI_WEBHOOK_SECRET', ''),
    
    'countries' => [
        'CO' => ['name' => 'Colombia', 'code' => '+57'],
        'EC' => ['name' => 'Ecuador', 'code' => '+593'],
        'PE' => ['name' => 'Perú', 'code' => '+51'],
        'MX' => ['name' => 'México', 'code' => '+52'],
        'CL' => ['name' => 'Chile', 'code' => '+56'],
    ],
];