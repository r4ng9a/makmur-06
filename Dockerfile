FROM php:8.2-apache

# Install ekstensi mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy semua file ke direktori Apache
COPY . /var/www/html/

# Aktifkan mod_rewrite
RUN a2enmod rewrite

# Izin folder
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80