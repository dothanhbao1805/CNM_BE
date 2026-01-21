#!/bin/bash
set -e

echo "Starting application..."

# Clear cache
echo "Clearing cache..."
php artisan config:clear
php artisan cache:clear

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Optimize
echo "Optimizing..."
php artisan optimize

# Start server
echo "Starting server on port 10000..."
exec php artisan serve --host=0.0.0.0 --port=10000