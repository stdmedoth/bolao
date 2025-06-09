FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip \
    git curl libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip


WORKDIR /var/www/html

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf


EXPOSE 80
