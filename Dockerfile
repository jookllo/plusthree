# syntax=docker/dockerfile:1

# ---- Frontend assets -------------------------------------------------------
FROM node:22-alpine AS assets
WORKDIR /build
RUN npm install --global pnpm@10
COPY app/package.json app/pnpm-lock.yaml app/pnpm-workspace.yaml ./
RUN pnpm install --frozen-lockfile
COPY app/ ./
RUN pnpm build

# ---- PHP dependencies ------------------------------------------------------
# The official composer image has no PHP extensions; Filament requires ext-intl,
# so dependencies are resolved on a PHP image that has them installed.
FROM php:8.5-cli AS vendor

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libzip-dev unzip \
    && docker-php-ext-install -j"$(nproc)" intl zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /build
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY app/composer.json app/composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --no-progress
COPY app/ ./
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts

# ---- Runtime ---------------------------------------------------------------
FROM php:8.5-apache AS runtime

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev libzip-dev libonig-dev libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev curl unzip \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring intl zip bcmath gd opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache-global.conf /etc/apache2/conf-available/plusthree-hardening.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-plusthree.ini

RUN a2enconf plusthree-hardening

COPY --from=vendor /build /var/www/html
COPY --from=assets /build/public/build /var/www/html/public/build

RUN rm -f bootstrap/cache/*.php \
    && mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        storage/app/public/articles \
        bootstrap/cache \
    && php artisan package:discover --ansi \
    && php artisan filament:assets \
    && php artisan storage:link \
    && mkdir -p public/css/filament public/js/filament public/fonts/filament \
    && chown -R www-data:www-data storage bootstrap/cache public/css/filament public/js/filament public/fonts/filament

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl -fsS http://127.0.0.1/up || exit 1
