FROM php:7.1-fpm

RUN docker-php-source extract \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-source delete

WORKDIR /opt/app
