FROM php:8.0-apache

# Install the PHP extensions the storefront needs.
#   - mysqli: database access used throughout the app
# (curl/openssl, used for Google OAuth, ship enabled in the base image.)
RUN docker-php-ext-install mysqli

# Enable Apache's rewrite module (clean URLs / future routing).
RUN a2enmod rewrite

# Copy the application into Apache's document root and hand ownership to the
# web-server user so it can read every file.
COPY --chown=www-data:www-data . /var/www/html/

EXPOSE 80
