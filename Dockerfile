###############################################################
# eCommerce PyME Panamá — Dockerfile
# PHP 8.4-FPM | Composer 2 | Node 22
# El VPS tiene Nginx propio; este contenedor solo expone FPM.
###############################################################

#############################
# Stage 1: Composer
#############################
FROM php:8.4-cli-alpine AS vendor

RUN apk add --no-cache git unzip libzip icu-libs libpng libjpeg-turbo freetype \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS postgresql-dev libzip-dev icu-dev \
        libpng-dev libjpeg-turbo-dev freetype-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd pdo_pgsql pgsql zip intl \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --no-progress --prefer-dist

#############################
# Stage 2: Vite (assets)
#############################
FROM node:22-alpine AS frontend

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts
COPY . .
RUN npm run build

#############################
# Stage 3: PHP-FPM runtime
#############################
FROM php:8.4-fpm-alpine AS runtime

WORKDIR /var/www/html

RUN apk add --no-cache postgresql-client libzip icu-libs libpng libjpeg-turbo freetype \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS postgresql-dev libzip-dev icu-dev \
        libpng-dev libjpeg-turbo-dev freetype-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath exif gd intl opcache pcntl pdo_pgsql pgsql zip \
    && apk del .build-deps \
    && rm -rf /tmp/pear

COPY docker/php/php.ini   /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/www.conf  /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
