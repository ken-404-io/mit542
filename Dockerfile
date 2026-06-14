FROM php:8.0-apache

# Install the PHP extensions the storefront needs.
#   - pdo_pgsql: PostgreSQL (Neon) access used throughout the app
# (curl/openssl, used for Google OAuth and Cloudinary uploads, ship enabled
#  in the base image.)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache's rewrite module (clean URLs / future routing).
RUN a2enmod rewrite

# Copy the application into Apache's document root and hand ownership to the
# web-server user so it can read every file.
COPY --chown=www-data:www-data . /var/www/html/

EXPOSE 80
