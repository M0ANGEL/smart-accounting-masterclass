<?php

// Script simple de health check
header('Content-Type: text/plain');

echo "=== SMART ACCOUNTING HEALTH CHECK ===\n\n";

// 1. Verificar que Laravel se ejecuta
echo "✅ Laravel está ejecutándose\n";

// 2. Verificar storage
if (is_writable(storage_path())) {
    echo "✅ Storage es escribible\n";
} else {
    echo "❌ Storage NO es escribible\n";
}

// 3. Verificar .env
if (file_exists(base_path('.env'))) {
    echo "✅ Archivo .env encontrado\n";
    echo "   Environment: " . app()->environment() . "\n";
} else {
    echo "❌ Archivo .env NO encontrado\n";
}

// 4. Verificar vistas
try {
    view()->exists('welcome');
    echo "✅ Sistema de vistas funcionando\n";
} catch (Exception $e) {
    echo "❌ Error en vistas: " . $e->getMessage() . "\n";
}

echo "\n=== SERVER INFO ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Laravel Version: " . app()->version() . "\n";
echo "Server Time: " . date('Y-m-d H:i:s') . "\n";

// HTTP 200 OK
http_response_code(200);