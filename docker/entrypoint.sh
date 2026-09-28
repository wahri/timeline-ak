#!/bin/sh
set -e

echo "=== Memulai Laravel Container Initialization ==="

# Pastikan folder penyimpanan dan cache ada
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Generate storage symlink jika belum ada
if [ ! -L /var/www/html/public/storage ]; then
    echo "Membuat symbolic link storage..."
    php /var/www/html/artisan storage:link || true
fi

# Jalankan migrasi database otomatis jika diaktifkan (default: true)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Menjalankan database migration..."
    php /var/www/html/artisan migrate --force || echo "Peringatan: Migrasi gagal atau ditunda. Periksa koneksi database."
fi

# Optimasi cache untuk production
if [ "${APP_ENV}" = "production" ]; then
    echo "Mengompilasi cache konfigurasi, route, dan view untuk production..."
    php /var/www/html/artisan config:cache || true
    php /var/www/html/artisan route:cache || true
    php /var/www/html/artisan view:cache || true
fi

# Pastikan hak akses storage dan cache dapat ditulis oleh www-data
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "=== Inisialisasi selesai. Menjalankan Supervisord ==="

exec "$@"
