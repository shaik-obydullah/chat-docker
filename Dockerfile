FROM php:8.2-fpm-alpine

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html/uploads

EXPOSE 9000
