#!/bin/bash
set -e

echo "🚀 Starting Braintoper initialization..."

# Initialize database
echo "📊 Initializing database schema..."
php /var/www/html/database/seeds/seed.php

# Start PHP-FPM in background
echo "🔧 Starting PHP-FPM..."
php-fpm &
PHP_FPM_PID=$!

# Start Nginx in foreground (so container doesn't exit)
echo "🌐 Starting Nginx..."
nginx -g "daemon off;"

# If nginx exits, kill php-fpm too
wait $PHP_FPM_PID
