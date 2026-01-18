FROM php:8.2-cli

WORKDIR /var/www

# Install system dependencies với đầy đủ libcurl
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    unzip \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    default-mysql-client \
    libxml2-dev \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure GD với freetype và jpeg
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions theo thứ tự ưu tiên
RUN docker-php-ext-install -j$(nproc) pdo_mysql
RUN docker-php-ext-install -j$(nproc) mbstring
RUN docker-php-ext-install -j$(nproc) zip
RUN docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-install -j$(nproc) bcmath
RUN docker-php-ext-install -j$(nproc) exif
RUN docker-php-ext-install -j$(nproc) pcntl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Composer environment
ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy composer files
COPY backend/composer.json backend/composer.lock ./

# Install dependencies (--no-scripts để tránh chạy post-install scripts)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-cache

# Copy application code
COPY backend/ .

# Run post-autoload scripts manually
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Create necessary directories
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

# Clear all cache (với error handling)
RUN php artisan config:clear 2>/dev/null || true
RUN php artisan route:clear 2>/dev/null || true
RUN php artisan view:clear 2>/dev/null || true
RUN php artisan cache:clear 2>/dev/null || true

EXPOSE 10000

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s \
    CMD curl -f http://localhost:10000/api/health || exit 1

# Start application với optimization
CMD php artisan optimize && \
    php artisan serve --host=0.0.0.0 --port=10000