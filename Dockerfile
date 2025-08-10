# Use official PHP image with Apache
FROM php:8.1-apache

# Enable Apache mod_rewrite (optional, for clean URLs)
RUN a2enmod rewrite

# Copy your entire app into the Apache server root
COPY . /var/www/html/

# (Optional) Set file permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80


RUN docker-php-ext-install pdo pdo_pgsql
RUN docker-php-ext-install pdo pdo_pgsql
