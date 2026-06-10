FROM php:8.2-apache

# Habilitamos el módulo de reescritura de Apache
RUN a2enmod rewrite

# Instalamos la extensión PDO de MySQL para que conecte con internet
RUN docker-php-ext-install pdo pdo_mysql

# Copiamos todos los archivos del proyecto al servidor
COPY . /var/www/html/

# Le damos los permisos necesarios a la carpeta para poder subir imágenes
RUN chown -R www-data:www-data /var/www/html/ && chmod -R 755 /var/www/html/

EXPOSE 80