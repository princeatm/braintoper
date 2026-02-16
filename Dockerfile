FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    curl \
    git \
    mariadb-client \
    libcurl4-openssl-dev \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    nginx \
    zip \
    unzip \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install PHP Extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_mysql \
    mbstring \
    xml \
    curl \
    opcache

# Configure PHP-FPM to use Unix socket
RUN sed -i 's/^listen = 127.0.0.1:9000/listen = \/var\/run\/php-fpm.sock/' \
    /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/^;listen.owner = www-data/listen.owner = www-data/' \
    /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/^;listen.group = www-data/listen.group = www-data/' \
    /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/^;listen.mode = 0660/listen.mode = 0666/' \
    /usr/local/etc/php-fpm.d/www.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create app directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Create required directories and set permissions
RUN mkdir -p storage logs && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 storage logs

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy custom nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Copy entrypoint script
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# Expose port
EXPOSE 80

ENTRYPOINT ["/docker-entrypoint.sh"]
