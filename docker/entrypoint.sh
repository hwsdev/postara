#!/bin/sh
set -e

# Clear any stale cache from previous builds/deployments
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations automatically on startup
php artisan migrate --force --no-interaction

# Re-cache for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Hand off to the main process
exec "$@"
