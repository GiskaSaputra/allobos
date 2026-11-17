# syntax=docker/dockerfile:1.7

# 1. Dependensi PHP. Dipakai juga oleh build aset karena app.js mengimpor Livewire dari vendor.
FROM composer:2.8 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist --ignore-platform-req=ext-*
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts

# 2. Aset front-end
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY --from=vendor /app/vendor ./vendor
COPY resources ./resources
COPY public ./public
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

# 3. Skrip ethers.js untuk pencatatan sertifikat (tanpa Hardhat)
FROM node:22-bookworm-slim AS web3
WORKDIR /web3
COPY web3/package.json web3/package-lock.json ./
RUN npm ci --omit=dev --no-audit --no-fund
COPY web3/scripts ./scripts

# 4. Aplikasi
FROM php:8.4-apache-bookworm AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev libzip-dev libicu-dev default-mysql-client unzip \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd zip intl bcmath exif pcntl opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY docker/php.ini /usr/local/etc/php/conf.d/allobos.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build
COPY --from=web3 --chown=www-data:www-data /web3/node_modules ./web3/node_modules

RUN rm -rf web3/contracts web3/test web3/hardhat.config.cjs \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x docker/entrypoint.sh

ENV NODE_BINARY=/usr/local/bin/node
EXPOSE 80
ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["apache2-foreground"]
