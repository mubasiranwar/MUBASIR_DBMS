FROM php:8.3-cli

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

RUN docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN if [ -f package.json ]; then npm install && npm run build; fi

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8080
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public"]
