#!/bin/sh
set -e

cd /var/www/html

# Ensure environment file exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Ensure APP_KEY exists
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Ensure database exists if using sqlite
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
fi

# Run migrations and seed data
php artisan migrate --force
php artisan db:seed --force || true

# Cache optimization
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec /usr/bin/supervisord -c /etc/supervisord.conf
