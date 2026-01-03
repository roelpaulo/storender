FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    sqlite-dev \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install \
    intl \
    pdo_mysql \
    pdo_sqlite \
    zip \
    bcmath \
    opcache

# Set working directory
WORKDIR /var/www/html

# Copy composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/database

# Nginx config
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Setup runtime directories
RUN mkdir -p /run/nginx /run/supervisord

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
