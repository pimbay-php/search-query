ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/* \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/xdebug.ini
