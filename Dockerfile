# Use PHP 8.3 Alpine as base image
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    linux-headers \
    bash \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    autoconf \
    g++ \
    make \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql bcmath exif pcntl gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code (respects .dockerignore)
COPY . .

# Install dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Create nginx configuration
RUN echo 'server { \
    listen 80; \
    server_name localhost; \
    root /var/www/html/public; \
    index index.php index.html index.htm; \
    \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
    \
    location ~ /\.ht { \
        deny all; \
    } \
}' > /etc/nginx/http.d/default.conf

# Create supervisor directories
RUN mkdir -p /etc/supervisor/conf.d /var/log/supervisor /var/run/supervisor

# Create supervisor configuration
RUN echo -e '[supervisord]\nnodaemon=true\nuser=root\nlogfile=/var/log/supervisor/supervisord.log\npidfile=/var/run/supervisor/supervisord.pid\n\n[program:php-fpm]\ncommand=php-fpm\nautostart=true\nautorestart=true\nstderr_logfile=/var/log/supervisor/php-fpm.err.log\nstdout_logfile=/var/log/supervisor/php-fpm.out.log\n\n[program:nginx]\ncommand=nginx -g "daemon off;"\nautostart=true\nautorestart=true\nstderr_logfile=/var/log/supervisor/nginx.err.log\nstdout_logfile=/var/log/supervisor/nginx.out.log\n\n[program:laravel-scheduler]\ncommand=php /var/www/html/artisan schedule:work\nautostart=true\nautorestart=true\nuser=www-data\ndirectory=/var/www/html\nstderr_logfile=/var/log/supervisor/laravel-scheduler.err.log\nstdout_logfile=/var/log/supervisor/laravel-scheduler.out.log' > /etc/supervisor/conf.d/supervisord.conf

# Expose port 80
EXPOSE 80

# Start supervisor to manage both nginx and php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"] 