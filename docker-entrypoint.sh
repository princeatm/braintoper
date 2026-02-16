#!/bin/bash

echo "🚀 Starting Braintoper initialization..."

# Wait for database to be ready (with retries)
echo "⏳ Waiting for MySQL database to be ready..."
MAX_RETRIES=30
RETRY_COUNT=0
while [ $RETRY_COUNT -lt $MAX_RETRIES ]; do
    if php -r "new PDO('mysql:host=${DB_HOST:-mysql};port=${DB_PORT:-3306}', '${DB_USER}', '${DB_PASS}');" 2>/dev/null; then
        echo "✅ Database is ready!"
        break
    fi
    RETRY_COUNT=$((RETRY_COUNT + 1))
    echo "⏳ Database not ready yet... retry $RETRY_COUNT/$MAX_RETRIES"
    sleep 2
done

# Initialize database (optional - continue even if it fails)
if [ $RETRY_COUNT -lt $MAX_RETRIES ]; then
    echo "📊 Initializing database schema..."
    php /var/www/html/database/seeds/seed.php 2>&1 || echo "⚠️  Database initialization completed (with warnings)"
else
    echo "⚠️  Database not available, skipping initialization"
fi

# Ensure socket directory exists with proper permissions
mkdir -p /var/run
chmod 755 /var/run

# Start PHP-FPM in background (daemonize mode)
echo "🔧 Starting PHP-FPM..."
php-fpm --daemonize --fpm-config /usr/local/etc/php-fpm.conf

# Wait for PHP-FPM socket to be created
echo "⏳ Waiting for PHP-FPM socket to be ready..."
for i in {1..30}; do
    if [ -S /var/run/php-fpm.sock ]; then
        echo "✅ PHP-FPM socket is ready!"
        ls -la /var/run/php-fpm.sock
        break
    fi
    echo "⏳ Waiting for socket... ($i/30)"
    sleep 1
done

# Start Nginx in foreground (so container doesn't exit)
echo "🌐 Starting Nginx..."
nginx -g "daemon off;"
