#!/bin/sh

# If the vendor directory doesn't exist, we likely haven't installed dependencies
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Wait for database to be ready (using a simple sleep for now to avoid mysqladmin auth issues)
echo "Waiting for database to be ready..."
sleep 5

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Start PHP built-in server for development
php artisan serve --host=0.0.0.0 --port=8000
