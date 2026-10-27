#!/bin/sh
set -e

cd /var/www/html

# Ensure Nginx, Supervisor, and Laravel storage directories exist with proper permissions
mkdir -p /run/nginx /var/log/nginx /var/log/supervisor \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/logs \
    /var/www/html/storage/app/public \
    /var/www/html/storage/data \
    /var/www/html/bootstrap/cache

# Ensure environment file exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# SQLite configuration handling
if [ "$DB_CONNECTION" = "sqlite" ] || grep -q "^DB_CONNECTION=sqlite" .env; then
    DB_PATH="${DB_DATABASE:-/var/www/html/storage/data/database.sqlite}"
    mkdir -p "$(dirname "$DB_PATH")"
    if [ ! -f "$DB_PATH" ]; then
        touch "$DB_PATH"
    fi
    chown -R www-data:www-data "$(dirname "$DB_PATH")"
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure APP_KEY exists
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Configure Nginx port from $PORT (Render / Cloud standard)
PORT="${PORT:-80}"
echo "Configuring Nginx on port ${PORT}..."
sed -i "s/listen 80 default_server;/listen ${PORT} default_server;/g" /etc/nginx/http.d/default.conf || true
sed -i "s/listen \[::\]:80 default_server;/listen \[::\]:${PORT} default_server;/g" /etc/nginx/http.d/default.conf || true

# Test Nginx configuration
nginx -t

# Run migrations: MUST NOT swallow errors. If migrations fail, halt deployment immediately.
echo "Running database migrations..."
php artisan migrate --force

# Run seeds ONLY if explicitly requested or if database is brand new (0 users)
USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tr -d '\r\n' || echo "0")
if [ "$INITIAL_SEED" = "true" ] || [ "$USER_COUNT" = "0" ]; then
    echo "Fresh database detected (Users: $USER_COUNT). Running initial setup seeds for roles, demo accounts, and catalog..."
    php artisan db:seed --force
fi

# Ensure storage symlink
php artisan storage:link || true

# Cache optimization (clear first then rebuild)
php artisan optimize:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Nginx and PHP-FPM via Supervisord on port ${PORT}..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
