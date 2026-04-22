# syntax=docker/dockerfile:1

########################
# Composer base
########################
FROM php:8.4-cli-bookworm AS composer_base

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install intl exif zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

########################
# Composer dependencies (dev)
########################
FROM composer_base AS composer_deps_dev

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

########################
# Composer dependencies (prod)
########################
FROM composer_base AS composer_deps_prod

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

########################
# Frontend build
########################
FROM composer_base AS frontend_build

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# Dev vendor for artisan / boost / wayfinder
COPY --from=composer_deps_dev /app/vendor /app/vendor

COPY composer.json composer.lock ./
COPY package.json package-lock.json ./
RUN npm ci

# Laravel app files needed for artisan + vite
COPY artisan ./
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY routes ./routes
COPY resources ./resources
COPY public ./public
COPY database ./database

COPY vite.config.* ./
COPY tsconfig.json ./
COPY components.json ./
COPY eslint.config.* ./
COPY postcss.config.* ./
COPY tailwind.config.* ./

COPY .env.example ./.env

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=/app/database/database.sqlite
ENV CACHE_STORE=file
ENV SESSION_DRIVER=file
ENV QUEUE_CONNECTION=sync

RUN mkdir -p \
    database \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    && touch database/database.sqlite \
    && php artisan key:generate --force

#RUN php artisan wayfinder:generate --with-form
RUN npm run build

########################
# Production image
########################
FROM webdevops/php-nginx:8.4 AS prod

ENV WEB_DOCUMENT_ROOT=/app/public
ENV APP_ENV=production
ENV PHP_DISPLAY_ERRORS=0
ENV PHP_MEMORY_LIMIT=512M
ENV PHP_POST_MAX_SIZE=100M
ENV PHP_UPLOAD_MAX_FILESIZE=100M
ENV PHP_DISMOD=ioncube

WORKDIR /app

USER root

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . /app
COPY --from=composer_deps_prod /app/vendor /app/vendor
COPY --from=frontend_build /app/public/build /app/public/build

COPY docker/init-app.sh /opt/docker/provision/entrypoint.d/99-init-app.sh
RUN chmod +x /opt/docker/provision/entrypoint.d/99-init-app.sh

RUN mkdir -p \
    /app/storage/framework/cache \
    /app/storage/framework/sessions \
    /app/storage/framework/views \
    /app/storage/logs \
    /app/bootstrap/cache \
    /app/database \
    && chown -R application:application /app

#USER application
