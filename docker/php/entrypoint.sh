#!/bin/sh
set -e

if [ ! -f .env ]; then
    echo ">> Creating .env from .env.example"
    cp .env.example .env
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo ">> Generating application key..."
    php artisan key:generate --force
fi

php artisan storage:link --force 2>/dev/null || true

if [ "$RUN_MIGRATIONS" != "false" ]; then
    echo ">> Running database migrations..."
    php artisan migrate --force
fi

php artisan package:discover --ansi 2>/dev/null || true

if [ "$APP_ENV" = "production" ]; then
    echo ">> Caching config, routes and views..."
    php artisan optimize 2>/dev/null || true
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
