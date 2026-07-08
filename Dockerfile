FROM php:8.3-cli

# Install system dependencies
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
RUN docker-php-ext-install pdo pdo_mysql zip mbstring xml gd bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

# Run Composer and NPM with a temporary valid APP_URL to bypass the "Invalid URI" error
RUN APP_URL=http://localhost composer install --no-dev --optimize-autoloader --no-interaction
RUN npm ci
RUN npm run build

# Make the start script executable
RUN chmod +x start.sh

# Start the application using our custom start.sh script
CMD ["bash", "start.sh"]
