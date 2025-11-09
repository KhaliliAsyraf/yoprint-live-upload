#!/bin/sh
set -e

echo "Waiting for database to be ready..."
until bash -c ">/dev/tcp/yoprint_db/3306" 2>/dev/null; do
  echo "Waiting for MySQL..."
  sleep 2
done

echo "Running migrations..."
php artisan migrate --force || true

echo "Running npm install & build..."
npm install && npm run build

echo "Starting PHP-FPM in foreground..."
exec php-fpm