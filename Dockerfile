# Base image con PHP y Apache
FROM php:8.2-apache

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Instalar extensiones necesarias para MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar todos los archivos del proyecto al contenedor
COPY . /var/www/html/

# Permisos para Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exponer puerto 80
EXPOSE 80

# Comando por defecto
CMD ["apache2-foreground"]
