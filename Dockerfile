# syntax=docker/dockerfile:1
FROM webdevops/php-nginx-dev:8.4

ENV APP_ENV=local

WORKDIR /app

# Install PHP extensions and Node.js
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libicu-dev \
    libzip-dev \
    curl \
    gnupg \
    netcat-openbsd \
    telnet \
    iproute2 \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install intl exif zip \
    && rm -rf /var/lib/apt/lists/*

# Copy composer files
COPY composer.json composer.lock ./
RUN composer install --no-scripts

# Copy application code (excluding git and node_modules)
COPY --exclude='.git' --exclude='node_modules' . .

# Setup autoloader
RUN composer dump-autoload --no-scripts

# Copy and install frontend dependencies
COPY package.json package-lock.json ./
RUN npm ci

# Copy vite config
COPY vite.config.js ./
COPY tsconfig.json ./
COPY components.json ./
COPY eslint.config.js ./
COPY postcss.config.js ./
COPY tailwind.config.js ./

# Create storage directories
RUN mkdir -p /app/storage/framework/cache \
    /app/storage/framework/sessions \
    /app/storage/framework/views \
    /app/storage/logs \
    /app/bootstrap/cache

# Build frontend
RUN npm run build

# Set permissions
RUN chown -R application:application /app/storage \
    /app/bootstrap/cache \
    /app/runtime

USER application

EXPOSE 80

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
