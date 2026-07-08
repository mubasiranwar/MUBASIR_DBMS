#!/bin/bash

echo "=== Laravel Railway Startup ==="

# Generate app key if not set
php artisan key:generate --force 2>/dev/null || true

# Run migrations (non-fatal)
echo "--- Running migrations..."
php artisan migrate --force 2>/dev/null || echo "Migration warning (may already be up to date)"

# Seed admin user (non-fatal)
echo "--- Seeding admin..."
php artisan db:seed --class=AdminSeeder --force 2>/dev/null || echo "Seeder warning (may already exist)"

# Clear caches (don't cache config in case of missing env vars)
php artisan view:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

# Storage link
php artisan storage:link 2>/dev/null || true

echo "=== Starting server on port ${PORT:-8000} ==="
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}