#!/bin/bash
# start.sh

echo "=== Starting Laravel Application ==="
echo "Port: $PORT"
echo "Database URL: $DATABASE_URL"

# Configurar permisos
chmod -R 775 storage bootstrap/cache

# Ejecutar migraciones (con reintentos)
echo "Running migrations..."
for i in {1..5}; do
    php artisan migrate --force
    if [ $? -eq 0 ]; then
        echo "Migrations completed successfully"
        break
    else
        echo "Migration attempt $i failed, retrying in 5 seconds..."
        sleep 5
    fi
done

# Configurar storage
php artisan storage:link

# Limpiar cache
php artisan optimize:clear

# Mostrar información de debug
echo "=== Debug Information ==="
php artisan route:list --path=health
php artisan route:list --path=/

# Iniciar servidor
echo "Starting PHP server on 0.0.0.0:$PORT..."
exec php -S 0.0.0.0:$PORT -t public