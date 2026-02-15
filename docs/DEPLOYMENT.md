# BrainToper - Deployment Guide

## System Requirements

- **PHP**: 8.2 or higher with PDO MySQL extension
- **MySQL**: 8.0 or higher
- **Web Server**: Apache 2.4+ with mod_rewrite or Nginx
- **Node.js**: 14+ (for WebSocket server, optional)
- **SSL/TLS**: Required for production (HTTPS)

## Installation Steps

### 1. Prerequisites

```bash
# Install PHP 8.2
sudo apt-get update
sudo apt-get install php8.2 php8.2-cli php8.2-pdo php8.2-mysql php8.2-mbstring php8.2-zip php8.2-gd php8.2-curl

# Install MySQL Server
sudo apt-get install mysql-server

# Install Node.js (for WebSocket)
sudo apt-get install nodejs npm
```

### 2. Clone Repository

```bash
cd /var/www
git clone https://github.com/princeatm/braintoper.git
cd braintoper
```

### 3. Environment Configuration

```bash
cp .env.example .env
# Edit .env with your configuration
nano .env
```

Edit these critical settings in `.env`:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://braintoper.example.com

DB_HOST=localhost
DB_PORT=3306
DB_NAME=braintoper
DB_USER=braintoper
DB_PASS=secure_password_here

SESSION_DOMAIN=.braintoper.example.com

WEBSOCKET_HOST=localhost
WEBSOCKET_PORT=8080
WEBSOCKET_PROTOCOL=ws
```

### 4. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE braintoper CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p -e "CREATE USER 'braintoper'@'localhost' IDENTIFIED BY 'secure_password';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON braintoper.* TO 'braintoper'@'localhost';"
mysql -u root -p -e "FLUSH PRIVILEGES;"

# Run migrations and seed data
php database/seeds/seed.php
```

### 5. Directory Permissions

```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/braintoper
sudo chmod -R 755 /var/www/braintoper
sudo chmod -R 755 /var/www/braintoper/storage
sudo chmod -R 755 /var/www/braintoper/storage/logs
sudo chmod -R 755 /var/www/braintoper/storage/uploads
sudo chmod -R 755 /var/www/braintoper/storage/cache
```

### 6. Apache Configuration

Create `/etc/apache2/sites-available/braintoper.conf`:

```apache
<VirtualHost *:80>
    ServerName braintoper.example.com
    ServerAlias www.braintoper.example.com
    DocumentRoot /var/www/braintoper/public

    # Redirect HTTP to HTTPS
    Redirect permanent / https://braintoper.example.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName braintoper.example.com
    ServerAlias www.braintoper.example.com
    DocumentRoot /var/www/braintoper/public

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/braintoper.example.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/braintoper.example.com/privkey.pem
    SSLCertificateChainFile /etc/letsencrypt/live/braintoper.example.com/chain.pem

    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    # Enable mod_rewrite
    <Directory /var/www/braintoper/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted

        # Route all requests to index.php
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php [QSA,L]
        </IfModule>
    </Directory>

    # PHP Configuration
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # Disable directory listing
    <Directory /var/www/braintoper>
        Options -Indexes
    </Directory>

    # Prevent access to sensitive files
    <Files "*.env">
        Deny from all
    </Files>
    <Files "*.php">
        Allow from all
    </Files>

    # Gzip compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
    </IfModule>

    ErrorLog ${APACHE_LOG_DIR}/braintoper_error.log
    CustomLog ${APACHE_LOG_DIR}/braintoper_access.log combined
</VirtualHost>
```

Enable the site:

```bash
sudo a2ensite braintoper.conf
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo systemctl restart apache2
```

### 7. SSL Certificate (Let's Encrypt)

```bash
sudo apt-get install certbot python3-certbot-apache
sudo certbot certonly --apache -d braintoper.example.com -d www.braintoper.example.com
```

### 8. PHP-FPM Configuration

```bash
# Install PHP-FPM
sudo apt-get install php8.2-fpm

# Edit PHP-FPM pool configuration
sudo nano /etc/php/8.2/fpm/pool.d/www.conf

# Set appropriate limits:
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 35

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### 9. WebSocket Server Setup (Optional)

```bash
cd /var/www/braintoper/websocket

# Install Node.js dependencies
npm install ws

# Create systemd service
sudo nano /etc/systemd/system/braintoper-websocket.service
```

Add to `braintoper-websocket.service`:

```ini
[Unit]
Description=BrainToper WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/braintoper/websocket
ExecStart=/usr/bin/node server.js
Restart=always
RestartSec=10

Environment="WEBSOCKET_HOST=localhost"
Environment="WEBSOCKET_PORT=8080"

[Install]
WantedBy=multi-user.target
```

Enable and start:

```bash
sudo systemctl daemon-reload
sudo systemctl enable braintoper-websocket
sudo systemctl start braintoper-websocket
```

### 10. Nginx Configuration (Alternative)

If using Nginx instead of Apache:

```nginx
server {
    listen 443 ssl http2;
    server_name braintoper.example.com www.braintoper.example.com;
    root /var/www/braintoper/public;

    ssl_certificate /etc/letsencrypt/live/braintoper.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/braintoper.example.com/privkey.pem;

    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;

    gzip on;
    gzip_types text/plain text/css text/javascript application/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}

# HTTP redirect
server {
    listen 80;
    server_name braintoper.example.com www.braintoper.example.com;
    return 301 https://$server_name$request_uri;
}
```

## Production Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Enable HTTPS with valid SSL certificate
- [ ] Set secure database password
- [ ] Configure session secure cookies
- [ ] Set proper file permissions (755 for dirs, 644 for files)
- [ ] Enable mod_rewrite and necessary Apache modules
- [ ] Configure PHP-FPM with appropriate pool settings
- [ ] Set up proper logging and monitoring
- [ ] Configure automated backups
- [ ] Set up monitoring and alerting
- [ ] Configure firewall rules
- [ ] Enable rate limiting
- [ ] Regular security updates

## Monitoring and Maintenance

### Log Files

- PHP Error Log: `/var/log/php8.2-fpm.log`
- Apache Access Log: `/var/log/apache2/braintoper_access.log`
- Apache Error Log: `/var/log/apache2/braintoper_error.log`
- Application Log: `/var/www/braintoper/storage/logs/`

### Database Backup

```bash
# Daily backup script
#!/bin/bash
BACKUP_DIR="/backups/braintoper"
mkdir -p $BACKUP_DIR
mysqldump -u braintoper -p braintoper > $BACKUP_DIR/braintoper_$(date +%Y%m%d_%H%M%S).sql
gzip $BACKUP_DIR/*.sql
```

### Updates and Patching

```bash
# Keep system updated
sudo apt-get update
sudo apt-get upgrade

# Update PHP extensions
sudo apt-get install --only-upgrade php8.2*
```

## Troubleshooting

### Database Connection Error

- Check `.env` database credentials
- Verify MySQL service is running: `sudo systemctl status mysql`
- Check MySQL user permissions

### File Upload Issues

- Verify storage directories are writable: `sudo chown -R www-data:www-data storage/`
- Check PHP upload limits in `php.ini`

### WebSocket Connection Issues

- Verify Node.js is running: `sudo systemctl status braintoper-websocket`
- Check firewall rules allow port 8080
- Check WebSocket server logs

## Security Hardening

1. **Firewall Rules**
   ```bash
   sudo ufw allow 22/tcp  # SSH
   sudo ufw allow 80/tcp  # HTTP
   sudo ufw allow 443/tcp # HTTPS
   sudo ufw enable
   ```

2. **Fail2Ban for Login Protection**
   ```bash
   sudo apt-get install fail2ban
   sudo systemctl restart fail2ban
   ```

3. **Regular Security Audits**
   - Monitor access logs for suspicious activity
   - Review database for unauthorized access
   - Check file integrity regularly

## Support

For issues or questions, contact the development team or consult the documentation.
