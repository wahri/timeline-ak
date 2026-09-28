#!/bin/sh
set -e

echo "========================================================"
echo " Starting Timeline AK Application Initialization"
echo "========================================================"

# 1. Pastikan seluruh folder penyimpanan dan cache tersedia
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# 2. Pastikan symbolic link storage ada untuk media/file publik
if [ ! -L /var/www/html/public/storage ]; then
    echo "Creating storage symbolic link..."
    php /var/www/html/artisan storage:link || true
fi

# 3. Tunggu koneksi database siap (terutama saat cold boot container database)
if [ -n "$DB_HOST" ] && [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Menunggu koneksi database (${DB_HOST}:${DB_PORT:-3306})..."
    MAX_RETRIES=20
    COUNT=0
    until php -r "
        try {
            new PDO(
                'mysql:host='.getenv('DB_HOST').';port='.(getenv('DB_PORT') ?: 3306).';dbname='.getenv('DB_DATABASE'),
                getenv('DB_USERNAME'),
                getenv('DB_PASSWORD'),
                [PDO::ATTR_TIMEOUT => 2, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            exit(0);
        } catch (\Throwable \$e) {
            exit(1);
        }
    " 2>/dev/null || [ $COUNT -ge $MAX_RETRIES ]; do
        COUNT=$((COUNT + 1))
        echo "Database belum merespons... percobaan $COUNT/$MAX_RETRIES (tunggu 2 detik)"
        sleep 2
    done

    if [ $COUNT -ge $MAX_RETRIES ]; then
        echo "PERINGATAN: Batas percobaan koneksi database terlampaui. Melanjutkan inisialisasi..."
    else
        echo "Koneksi database berhasil terhubung!"
    fi
fi

# 4. Jalankan migrasi database otomatis jika diaktifkan (default: true)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Menjalankan database migration (artisan migrate --force)..."
    php /var/www/html/artisan migrate --force || echo "Peringatan: Migrasi database gagal atau ditunda."
fi

# 5. Cek apakah database baru/kosong untuk seeding otomatis
if [ "${SEED_IF_EMPTY:-true}" = "true" ] || [ "${RUN_SEEDERS:-false}" = "true" ]; then
    USER_COUNT=$(php -r "
        require '/var/www/html/vendor/autoload.php';
        \$app = require_once '/var/www/html/bootstrap/app.php';
        \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
        \$kernel->bootstrap();
        try {
            echo \App\Models\User::count();
        } catch (\Throwable \$e) {
            echo '-1';
        }
    " 2>/dev/null || echo "-1")

    if [ "$USER_COUNT" = "0" ] || [ "${RUN_SEEDERS:-false}" = "true" ]; then
        echo "Database terdeteksi baru/kosong (atau RUN_SEEDERS=true). Mengisi data awal (artisan db:seed --force)..."
        php /var/www/html/artisan db:seed --force || echo "Peringatan: Seeding database gagal."
    else
        echo "Database sudah berisi data (User count: $USER_COUNT). Melewati seeding otomatis."
    fi
fi

# 6. Kompilasi cache Laravel untuk performa maksimal di production
if [ "${APP_ENV}" = "production" ]; then
    echo "Mengompilasi cache konfigurasi, route, dan view untuk production..."
    php /var/www/html/artisan package:discover --ansi || true
    php /var/www/html/artisan config:cache || true
    php /var/www/html/artisan route:cache || true
    php /var/www/html/artisan view:cache || true
fi

# 7. Pastikan hak akses file dan direktori dimiliki www-data
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "========================================================"
echo " Inisialisasi Selesai! Menjalankan Supervisord"
echo "========================================================"

exec "$@"
