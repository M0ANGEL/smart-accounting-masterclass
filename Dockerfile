# Usa una imagen más ligera y específica para Laravel
FROM webdevops/php-nginx:8.2-alpine

# Directorio de trabajo
WORKDIR /app

# Copiar archivos de configuración primero
COPY docker/nginx.conf /opt/docker/etc/nginx/vhost.conf
COPY docker/supervisord.conf /opt/docker/etc/supervisor.d/laravel.conf

# Copiar el resto del código
COPY . .

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Configurar permisos
RUN chown -R application:application /app \
    && chmod -R 755 /app/storage \
    && chmod -R 755 /app/bootstrap/cache

# Exponer puerto
EXPOSE 80

# Comando de inicio (esta imagen ya tiene supervisord configurado)
CMD ["supervisord"]