FROM php:8.1-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache postgresql-dev

RUN docker-php-ext-install pdo pdo_pgsql

COPY . /var/www/html