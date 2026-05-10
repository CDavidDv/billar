#!/bin/bash
set -e

git pull

composer install --no-dev --optimize-autoloader --no-interaction

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan storage:link

echo "Deploy OK"
