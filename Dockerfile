FROM php:8.2-apache

# Habilitar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar todo el código al servidor web
COPY . /var/www/html/

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html
