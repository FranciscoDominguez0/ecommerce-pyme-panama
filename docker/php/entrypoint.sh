#!/bin/sh
set -e

# Crear .env desde .env.example si no existe
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Permisos de almacenamiento
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

# Escribir APP_KEY en el .env del contenedor
if [ -n "$APP_KEY" ] && [ "$APP_KEY" != "base64:" ]; then
    # Usar la key que viene del .env del VPS
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
else
    # No hay key: generar una nueva
    echo ">> APP_KEY no definida, generando..."
    php artisan key:generate --force
    # Leer la key recién generada y exportarla al entorno actual
    APP_KEY=$(grep '^APP_KEY=' .env | cut -d'=' -f2-)
    export APP_KEY
    echo ">> Copia esta key en el .env del VPS: APP_KEY=${APP_KEY}"
fi

# Enlace simbólico de storage
php artisan storage:link --force 2>/dev/null || true

# Migraciones
if [ "$RUN_MIGRATIONS" != "false" ]; then
    echo ">> Ejecutando migraciones..."
    php artisan migrate --force
fi

# Optimizar en producción (config:cache usa la key ya exportada)
if [ "$APP_ENV" = "production" ]; then
    php artisan optimize 2>/dev/null || true
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
