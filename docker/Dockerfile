ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/xdebug.ini
