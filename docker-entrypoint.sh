#!/bin/sh

# Exit on error
set -e

# Wait for database
# Wait for database
echo "Waiting for database ($DB_HOST:3306)..."
while ! nc -z $DB_HOST 3306; do
  echo "Still waiting for mysql..."
  sleep 2
done
echo "Database is ready!"

# Show PHP version and modules
php -v
php -m

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

# Link storage
php artisan storage:link

# Fix permissions for storage (since we are running as root now)
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Start PHP-FPM
exec php-fpm
