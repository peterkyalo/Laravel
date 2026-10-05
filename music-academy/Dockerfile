FROM php:8.4-fpm-alpine

# Install system dependencies, Nginx, and Supervisor
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    ca-certificates \
    && docker-php-ext-install pdo_mysql bcmath opcache

# Copy Composer binary from official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy the entire project into the container
COPY . .

# Install production PHP dependencies without dev packages
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set correct storage and cache permissions for the web server user
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy the Nginx and Supervisor configurations
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

# Cache configurations, run migrations automatically, and start Supervisor
CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force && supervisord -c /etc/supervisor/conf.d/supervisord.conf"]