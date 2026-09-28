# ==========================================
# STAGE 1: Build Frontend Assets (Vite & Tailwind CSS v4)
# ==========================================
FROM node:20-alpine AS frontend-builder

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# ==========================================
# STAGE 2: PHP 8.3 FPM + Nginx + Supervisord
# ==========================================
FROM php:8.3-fpm-alpine

# Install sistem dependensi & librari ekstensi PHP
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    bash \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_sqlite \
        bcmath \
        mbstring \
        zip \
        intl \
        gd \
        opcache \
        pcntl \
    && apk del $PHPIZE_DEPS

# Ambil binary Composer resmi
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Konfigurasi PHP Production
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 64M/g' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/post_max_size = 8M/post_max_size = 64M/g' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/memory_limit = 128M/memory_limit = 256M/g' "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

# Salin manifest dependensi Composer terlebih dahulu untuk cache layer Docker
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Salin seluruh kode aplikasi
COPY . .

# Salin compiled assets hasil build dari frontend-builder
COPY --from=frontend-builder /app/public/build ./public/build

# Generate autoload teroptimasi untuk production
RUN composer dump-autoload --optimize --no-dev

# Salin konfigurasi Nginx, Supervisord, dan Entrypoint
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Normalisasi line endings (mencegah CRLF Windows bug) dan set permissions
RUN tr -d '\r' < /usr/local/bin/entrypoint.sh > /usr/local/bin/entrypoint_unix.sh \
    && mv /usr/local/bin/entrypoint_unix.sh /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /var/log/supervisor /var/run /run/nginx \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Port default yang dideteksi oleh Coolify Traefik
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
