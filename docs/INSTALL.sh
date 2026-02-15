# BrainToper - Installation Script

Run this script to set up BrainToper on a fresh Linux server.

```bash
#!/bin/bash

set -e

echo "🚀 BrainToper Setup Script"
echo "=========================="

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ This script must be run as root"
    exit 1
fi

PROJECT_DIR="/var/www/braintoper"
DB_NAME="braintoper"
DB_USER="braintoper"
DB_PASS=$(openssl rand -base64 32)

echo "📦 Installing system dependencies..."
apt-get update && apt-get upgrade -y

# Install PHP 8.2
echo "📥 Installing PHP 8.2..."
apt-get install -y php8.2 php8.2-cli php8.2-fpm php8.2-pdo php8.2-mysql \
    php8.2-mbstring php8.2-zip php8.2-gd php8.2-curl php8.2-xml

# Install MySQL
echo "📥 Installing MySQL 8.0..."
apt-get install -y mysql-server

# Install Apache
echo "📥 Installing Apache2..."
apt-get install -y apache2 libapache2-mod-php8.2 php8.2-mysql

# Install Node.js for WebSocket
echo "📥 Installing Node.js..."
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
apt-get install -y nodejs

# Enable Apache modules
echo "⚙️  Enabling Apache modules..."
a2enmod rewrite
a2enmod ssl
a2enmod headers
a2enmod proxy_fcgi
a2enmod setenvif

# Create database
echo "🔧 Setting up database..."
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

echo "✅ Database created!"
echo "   Database: $DB_NAME"
echo "   User: $DB_USER"
echo "   Password: $DB_PASS"

# Clone and setup project
echo "📂 Cloning BrainToper..."
if [ -d "$PROJECT_DIR" ]; then
    echo "⚠️  Directory already exists. Skipping clone."
else
    git clone https://github.com/princeatm/braintoper.git $PROJECT_DIR
fi

cd $PROJECT_DIR

# Setup environment
echo "⚙️  Configuring environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    sed -i "s/DB_USER=.*/DB_USER=$DB_USER/" .env
    sed -i "s/DB_PASS=.*/DB_PASS=$DB_PASS/" .env
fi

# Set permissions
echo "🔐 Setting file permissions..."
chown -R www-data:www-data $PROJECT_DIR
chmod -R 755 $PROJECT_DIR
chmod -R 755 storage/
chmod -R 755 storage/logs
chmod -R 755 storage/uploads
chmod -R 755 storage/cache

# Run migrations
echo "🗄️  Running database migrations..."
cd $PROJECT_DIR
php database/seeds/seed.php

# Install WebSocket dependencies
echo "📦 Installing WebSocket dependencies..."
cd $PROJECT_DIR/websocket
npm install

# Create Apache virtualhost
echo "🌐 Configuring Apache..."
cat > /etc/apache2/sites-available/braintoper.conf <<'APACHE'
<VirtualHost *:80>
    ServerName braintoper.local
    DocumentRoot /var/www/braintoper/public

    <Directory /var/www/braintoper/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted

        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php [QSA,L]
        </IfModule>
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost"
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/braintoper_error.log
    CustomLog ${APACHE_LOG_DIR}/braintoper_access.log combined
</VirtualHost>
APACHE

a2ensite braintoper.conf
a2dissite 000-default

# Restart services
echo "🔄 Restarting services..."
systemctl restart apache2
systemctl restart php8.2-fpm
systemctl restart mysql

echo ""
echo "✅ Installation complete!"
echo ""
echo "📋 Next steps:"
echo "1. Update /etc/hosts: 127.0.0.1 braintoper.local"
echo "2. Access: http://braintoper.local"
echo ""
echo "🔐 Default Credentials:"
echo "   Super Admin: SUPAD-01-0001 / 1234"
echo "   Admin: AD-01-001 / 1234"
echo "   Teacher: TEA-01-0001 / 1234"
echo ""
echo "📚 Database Credentials Saved to: $PROJECT_DIR/.env"
echo ""
```

Save this as `install.sh` and run:

```bash
sudo bash install.sh
```
