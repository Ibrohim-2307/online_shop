#!/bin/sh
set -e

cd /var/www/html

# Ensure Nginx and Supervisor runtime and log directories exist (Alpine Linux requirement)
mkdir -p /run/nginx /var/log/nginx /var/log/supervisor /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure environment file exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Switch to SQLite unless a real external MySQL host is provided
if [ -z "$DB_HOST" ] || [ "$DB_HOST" = "127.0.0.1" ] || [ "$DB_HOST" = "localhost" ]; then
    echo "Configuring embedded SQLite database for zero-config cloud hosting..."
    export DB_CONNECTION=sqlite
    export DB_DATABASE=/var/www/html/database/database.sqlite
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env || true
    sed -i 's|^DB_DATABASE=.*|DB_DATABASE=/var/www/html/database/database.sqlite|' .env || true
fi

# Ensure SQLite file exists with proper permissions
if [ "$DB_CONNECTION" = "sqlite" ] || grep -q "DB_CONNECTION=sqlite" .env; then
    touch /var/www/html/database/database.sqlite
    chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache
fi

# Ensure APP_KEY exists
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Configure Nginx port from $PORT (Render sets $PORT=10000)
PORT="${PORT:-80}"
echo "Configuring Nginx on port ${PORT}..."
sed -i "s/listen 80 default_server;/listen ${PORT} default_server;/g" /etc/nginx/http.d/default.conf || true
sed -i "s/listen \[::\]:80 default_server;/listen \[::\]:${PORT} default_server;/g" /etc/nginx/http.d/default.conf || true

# Test Nginx configuration
nginx -t

# Run migrations and seed data safely
php artisan migrate --force || true
php artisan db:seed --force || true

# Ensure storage symlink
php artisan storage:link || true

# Cache optimization (clear first then rebuild)
php artisan optimize:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Nginx and PHP-FPM via Supervisord on port ${PORT}..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
