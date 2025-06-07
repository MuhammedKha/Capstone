FROM php:8.2-apache

# Install necessary PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache rewrite module
RUN a2enmod rewrite

# ✅ Set correct document root (since files are in root)
ENV APACHE_DOCUMENT_ROOT /var/www/html

# ✅ No need to change Apache config if using default root
# (you can remove or leave this, it doesn't affect the default path)
# RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf

# ✅ Copy everything from Capstone into container web root
COPY . /var/www/html/

EXPOSE 80
