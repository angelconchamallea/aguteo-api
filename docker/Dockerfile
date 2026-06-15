# syntax=docker/dockerfile:1
# Dockerfile de producción — aguteo-api (Laravel 11, PHP 8.3)
# Multi-stage: dependencias se construyen aparte, la imagen final queda liviana.

# ---------- Stage 1: dependencias PHP ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ---------- Stage 2: assets (Filament publica CSS/JS vía Vite) ----------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---------- Stage 3: imagen final ----------
FROM php:8.3-fpm-alpine AS app

RUN apk add --no-cache \
    postgresql-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    icu-dev oniguruma-dev curl \
 && docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install -j$(nproc) \
    pdo_pgsql zip gd intl mbstring bcmath opcache pcntl

# Opcache para producción
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
  } > /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

# storage y cache escribibles
RUN chown -R www-data:www-data storage bootstrap/cache

USER www-data

# Las caches de Laravel (config/route/view) se generan en el entrypoint,
# porque dependen del .env que solo existe en el servidor.
COPY docker/entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/bin/sh", "/entrypoint.sh"]
CMD ["php-fpm"]
