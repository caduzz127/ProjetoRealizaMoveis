FROM php:8.2-apache

RUN a2enmod rewrite


RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80