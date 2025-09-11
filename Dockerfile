FROM php:8.2-apache

# Extensión para conectarse a MySQL
RUN docker-php-ext-install pdo_mysql

# Copiamos el código de la app
COPY src/ /var/www/html/
