FROM php:8.2-apache

# Disable mpm_event, enable mpm_prefork (fix "More than one MPM loaded")
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Install ekstensi mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan mod_rewrite
RUN a2enmod rewrite

# Copy semua file ke direktori Apache
COPY . /var/www/html/

# Izin folder
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
