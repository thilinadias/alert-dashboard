#!/bin/sh

# Exit on error
set -e

# Wait for database
until nc -z -v -w30 $DB_HOST 3306
do
  echo "Waiting for database connection..."
  sleep 5
done

# Install dependencies if vendor is missing
if [ ! -d "vendor" ]; then
    composer install --no-interaction --optimize-autoloader --no-dev
fi

# Ensure .env exists
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Run migrations
php artisan migrate --force

# Link storage
php artisan storage:link

# Start PHP-FPM
exec php-fpm
