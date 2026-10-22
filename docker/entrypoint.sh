#!/bin/sh
set -e

cd /var/www/html

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

# Ensure APP_KEY exists
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Ensure database exists if using sqlite
if [ "$DB_CONNECTION" = "sqlite" ]; then
    mkdir -p /var/www/html/database
    touch /var/www/html/database/database.sqlite
    chown -R www-data:www-data /var/www/html/database
    chmod -R 775 /var/www/html/database
fi

# Configure Nginx port from $PORT (Render provides $PORT=10000)
PORT="${PORT:-80}"
echo "Configuring Nginx on port ${PORT}..."
sed -i "s/listen 80 default_server;/listen ${PORT} default_server;/g" /etc/nginx/http.d/default.conf || true
sed -i "s/listen \[::\]:80 default_server;/listen \[::\]:${PORT} default_server;/g" /etc/nginx/http.d/default.conf || true

# Run migrations and seed data safely
php artisan migrate --force || true
php artisan db:seed --force || true

# Cache optimization
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Nginx and PHP-FPM via Supervisord..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
