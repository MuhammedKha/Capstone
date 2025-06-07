FROM php:8.2-apache

# Install necessary PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache rewrite module
RUN a2enmod rewrite

#correct document root (since files are in root)
ENV APACHE_DOCUMENT_ROOT /var/www/html



#Copy everything from Capstone into container web root
COPY . /var/www/html/

EXPOSE 80
