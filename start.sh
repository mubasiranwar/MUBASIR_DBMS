#!/bin/bash

echo "=== Laravel Railway Startup ==="

# Create .env from environment variables if it doesn't exist
if [ ! -f .env ]; then
    echo "--- Creating .env file..."
    cat > .env << EOF
APP_NAME="${APP_NAME:-DBMS Exam Result System}"
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY:-}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}

LOG_CHANNEL=stderr
LOG_LEVEL=error


DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-hayabusa.proxy.rlwy.net}
DB_PORT=${DB_PORT:-48689}
DB_DATABASE=${DB_DATABASE:-railway}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD:-kllfJMIyAqCuGQCapIkryMOcMhaOifiv}

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
BCRYPT_ROUNDS=12

MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="${APP_NAME:-App}"
EOF
fi

# Generate app key if not set
if grep -q "APP_KEY=$" .env || grep -q "APP_KEY= *$" .env; then
    echo "--- Generating APP_KEY..."
    php artisan key:generate --force
fi

# Run database migrations (exit on failure so Railway logs show the real error)
echo "--- Running migrations..."
php artisan migrate --force
if [ $? -ne 0 ]; then
    echo "[ERROR] Migration failed! Check DB credentials and connectivity."
    exit 1
fi

# Seed admin user
echo "--- Seeding admin..."
php artisan db:seed --class=AdminSeeder --force 2>&1 || echo "[WARN] Seed issue - may already exist"

# Clear views cache only (avoid caching config since env is read at runtime)
php artisan view:clear 2>&1 || true
php artisan route:clear 2>&1 || true

# Storage link
php artisan storage:link 2>&1 || true

echo "=== Starting server on port ${PORT:-8000} ==="
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}