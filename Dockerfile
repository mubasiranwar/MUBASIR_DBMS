FROM php:8.3-cli

# Install system packages + Node.js
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Working directory
WORKDIR /var/www

# Copy project
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Build frontend (if using Vite)
RUN if [ -f package.json ]; then npm install && npm run build; fi

# Fix permissions
RUN chmod -R 775 storage bootstrap/cache

# Laravel optimization
RUN php artisan config:clear || true
RUN php artisan cache:clear || true
RUN php artisan route:clear || true
RUN php artisan view:clear || true

EXPOSE 8080

# Start PHP built-in server
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public"]
