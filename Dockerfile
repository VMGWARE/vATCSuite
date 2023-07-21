FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Install dependencies
COPY src/composer.lock src/composer.json /var/www/
RUN composer install --no-dev --no-scripts --no-progress --prefer-dist

# Copy existing application directory contents
COPY src/ /var/www

# Generate app encryption key
RUN php artisan key:generate

# Migrate database
RUN php artisan migrate

# Clear cache
RUN php artisan config:cache