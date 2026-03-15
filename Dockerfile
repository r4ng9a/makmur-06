FROM php:8.2-apache

# Fix "More than one MPM loaded" error
RUN apt-get update && apt-get install -y \
    && a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

# Copy semua file ke direktori Apache
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
