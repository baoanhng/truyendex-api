# Use PHP 8.2 Alpine as base image
FROM php:8.2-alpine

# Install system dependencies
RUN apk add --no-cache \
    linux-headers \
    bash \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql bcmath exif pcntl gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# remove .env
RUN rm -f .env

# Copy existing application directory
COPY . .

# Install dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 8000 (default Laravel port)
EXPOSE 8000

# php artisan config:clear
RUN php artisan config:clear
# RUN php artisan cache:clear
RUN php artisan config:cache

# Start Laravel development server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"] 