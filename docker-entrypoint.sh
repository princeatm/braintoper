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

# Initialize database (optional - MUST NOT fail or crash container)
if [ $RETRY_COUNT -lt $MAX_RETRIES ]; then
    echo "📊 Initializing database..."
    # Run initialization script but suppress all errors - it's optional
    php /var/www/html/database/init.php > /dev/null 2>&1 || true
    echo "⚠️  Database initialization attempted (may have warnings but container continues)"
else
    echo "⚠️  Database not available, skipping initialization"
fi

# Setup runtime directories
echo "🔧 Setting up runtime directories..."
mkdir -p /var/run /var/log/php-fpm /var/log/nginx
chmod 755 /var/run

# Start PHP-FPM
echo "🔧 Starting PHP-FPM..."
php-fpm --daemonize --fpm-config /usr/local/etc/php-fpm.conf
php_status=$?

if [ $php_status -eq 0 ]; then
    echo "✅ PHP-FPM started successfully"
else
    echo "⚠️  PHP-FPM had an issue (exit code: $php_status) but container will continue"
fi

# Give PHP-FPM time to be ready
sleep 2

# Start Nginx in foreground (so container doesn't exit)
echo "🌐 Starting Nginx..."
nginx -g "daemon off;"
