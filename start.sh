#!/bin/bash
set -e

echo "=== Starting Laravel Deployment ==="

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run database migrations
echo "--- Running migrations..."
php artisan migrate --force

# Seed admin user
echo "--- Seeding database..."
php artisan db:seed --class=AdminSeeder --force

# Cache for production
echo "--- Caching config/routes/views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage link
php artisan storage:link 2>/dev/null || true

echo "=== Starting Server on port ${PORT:-8000} ==="
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}