FROM php:8.5-fpm-alpine

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli pdo pdo_mysql

WORKDIR /var/www/html

COPY . /var/www/html

RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html/uploads

EXPOSE 9000
