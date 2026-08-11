#!/bin/sh
set -e

# Permisos de almacenamiento
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

# Generar APP_KEY si falta
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    php artisan key:generate --force
fi

# Enlace simbólico de storage
php artisan storage:link --force 2>/dev/null || true

# Migraciones
if [ "$RUN_MIGRATIONS" != "false" ]; then
    echo ">> Ejecutando migraciones..."
    php artisan migrate --force
fi

# Optimizar en producción
if [ "$APP_ENV" = "production" ]; then
    php artisan optimize 2>/dev/null || true
fi

exec php-fpm
