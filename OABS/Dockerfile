# Use the official PHP image with Apache
FROM php:8.2-apache

# Install PHP extensions required for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite for clean URLs (optional but recommended)
RUN a2enmod rewrite

# Copy all project files into Apache's web root directory
COPY . /var/www/html/

# Expose default Apache port
EXPOSE 80
