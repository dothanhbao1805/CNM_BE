FROM php:8.2-cli

# Cài system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    default-mysql-client \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        gd

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy source code
COPY . .

# Cài PHP dependencies (KHÔNG dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Phân quyền cho Laravel
RUN chmod -R 775 storage bootstrap/cache

# Clear cache (tránh lỗi env)
RUN php artisan config:clear \
 && php artisan route:clear \
 && php artisan view:clear

# Render dùng port này
EXPOSE 10000

# Chạy Laravel (Render không có Nginx)
CMD php artisan serve --host=0.0.0.0 --port=10000
