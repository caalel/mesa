FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts


FROM node:22.12.0-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./

RUN npm run build


FROM php:8.3-apache-bookworm

WORKDIR /var/www/html

RUN apt-get update \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

COPY docker/apache-laravel.conf /etc/apache2/sites-available/000-default.conf
COPY . ./
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache \
    && find storage bootstrap/cache -type d -exec chmod 775 {} \; \
    && find storage bootstrap/cache -type f -exec chmod 664 {} \;

EXPOSE 80

CMD ["apache2-foreground"]
