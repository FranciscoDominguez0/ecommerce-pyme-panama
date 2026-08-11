#!/bin/sh
set -e

# Crear .env desde .env.example si no existe
# (artisan necesita el archivo físico para algunos comandos)
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Permisos de almacenamiento
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

# Directorios de subida de imágenes (marcas, categorías, promociones, avatares, productos)
mkdir -p public/images/Marcas public/uploads/categorias public/uploads/promociones public/uploads/avatars public/uploads/productos
chown -R www-data:www-data public/images public/uploads 2>/dev/null || true
chmod -R ug+rwX public/images public/uploads 2>/dev/null || true

# Enlace simbólico de storage
php artisan storage:link --force 2>/dev/null || true

# Migraciones
if [ "$RUN_MIGRATIONS" != "false" ]; then
    echo ">> Ejecutando migraciones..."
    php artisan migrate --force
    echo ">> Ejecutando seeders (roles y datos base)..."
    php artisan db:seed --force
fi


# Cachear solo rutas y vistas (NO config:cache — Docker inyecta las env vars directamente)
php artisan route:cache  2>/dev/null || true
php artisan view:cache   2>/dev/null || true

exec /usr/bin/supervisord -c /etc/supervisord.conf
