#!/bin/sh
set -e

echo "========================================="
echo " INICIANDO LARAVEL EN RAILWAY"
echo "========================================="

# Mostrar variables de entorno (para debugging)
echo "Variables de entorno:"
echo "APP_ENV: $APP_ENV"
echo "DB_HOST: $DB_HOST"
echo "DB_DATABASE: $DB_DATABASE"
echo "MYSQLHOST: $MYSQLHOST"

# Usar variables MYSQL* si DB_* no están definidas
if [ -z "$DB_HOST" ] && [ -n "$MYSQLHOST" ]; then
    echo "Usando variables MYSQL*..."
    export DB_HOST=$MYSQLHOST
    export DB_PORT=$MYSQLPORT
    export DB_DATABASE=$MYSQLDATABASE
    export DB_USERNAME=$MYSQLUSER
    export DB_PASSWORD=$MYSQLPASSWORD
fi

# Esperar a MySQL (máximo 30 segundos)
if [ -n "$DB_HOST" ]; then
    echo "Esperando MySQL en $DB_HOST:$DB_PORT..."
    timeout=30
    while ! nc -z $DB_HOST $DB_PORT; do
        timeout=$((timeout-1))
        if [ $timeout -eq 0 ]; then
            echo "❌ MySQL no está disponible después de 30 segundos"
            break
        fi
        echo "⏳ Esperando MySQL... ($timeout segundos restantes)"
        sleep 1
    done
    
    if nc -z $DB_HOST $DB_PORT; then
        echo "✅ MySQL está disponible"
    fi
fi

# Generar APP_KEY si no existe
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null || [ -z "$(php artisan key:generate --show 2>/dev/null)" ]; then
    echo "Generando APP_KEY..."
    php artisan key:generate --force
fi

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force || echo "⚠️  Nota: Migraciones fallaron o ya ejecutadas"

# Crear storage link
echo "Creando enlace de storage..."
php artisan storage:link || true

# Limpiar caché
echo "Limpiando caché..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# Cachear para producción
echo "Optimizando para producción..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Verificar permisos
echo "Verificando permisos..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "========================================="
echo " INICIANDO SERVICIOS (NGINX + PHP-FPM)"
echo "========================================="

# Iniciar servicios
exec /usr/bin/supervisord -c /etc/supervisord.conf