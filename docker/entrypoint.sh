#!/bin/sh
set -e

# Fix storage permissions (volumes may be mounted as root)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Docs
php artisan vendor:publish --tag=l5-swagger-assets
php artisan l5-swagger:generate --ansi --no-interaction || true

# Create storage link if not exists
php artisan storage:link --ansi --no-interaction || true

# Execute the main command
exec "$@"
