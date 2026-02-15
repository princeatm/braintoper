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

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
