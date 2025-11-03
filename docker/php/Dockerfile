FROM php:8.3-fpm-alpine

# System deps
RUN apk add --no-cache \
    bash git curl zip unzip icu-dev oniguruma-dev libpq-dev libzip-dev

RUN apk add --no-cache $PHPIZE_DEPS

# PHP extensions
RUN docker-php-ext-install -j$(nproc) intl pdo pdo_pgsql opcache mbstring zip
RUN docker-php-ext-install pcntl posix

# install phpredis via PECL and enable it
RUN pecl install redis \
 && docker-php-ext-enable redis

# Install Node.js (LTS 20) + npm
RUN apk add --no-cache nodejs npm

# Opcache DEV defaults (auto-reload changed files)
RUN { \
  echo 'opcache.enable=1'; \
  echo 'opcache.validate_timestamps=1'; \
  echo 'opcache.revalidate_freq=0'; \
  echo 'opcache.jit=1255'; \
  echo 'opcache.jit_buffer_size=64M'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# Create a non-root user (optional, nicer for volumes)
RUN addgroup -S app && adduser -S app -G app
USER app

