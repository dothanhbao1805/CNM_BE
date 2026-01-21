#!/bin/bash
set -e

echo "Starting application..."

# Chỉ clear config, không clear cache
echo "Clearing config..."
php artisan config:clear

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Clear cache sau khi đã có bảng
echo "Clearing cache..."
php artisan cache:clear || true

# Optimize
echo "Optimizing..."
php artisan optimize

# Start server
echo "Starting server on port 10000..."
exec php artisan serve --host=0.0.0.0 --port=10000