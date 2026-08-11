#!/bin/sh
set -e

# Crear .env desde .env.example si no existe
# (el contenedor recibe las vars por env_file, pero artisan necesita el archivo físico)
if [ ! -f .env ]; then
    echo ">> Creando .env desde .env.example"
    cp .env.example .env
fi

# Permisos de almacenamiento
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

# Generar APP_KEY si falta o está vacío
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo ">> Generando APP_KEY..."
    php artisan key:generate --force
else
    # Escribir la key del entorno en el .env para que artisan la vea
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
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

exec /usr/bin/supervisord -c /etc/supervisord.conf
