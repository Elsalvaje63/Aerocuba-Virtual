FROM php:8.2-apache

# Instalar extensiones necesarias incluyendo GD
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql

# Copiar todo el código al servidor web
COPY . /var/www/html/

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html
