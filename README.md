# 🚀 Timeline Monitoring Task & Roadmap Apotek Keluarga

Sistem monitoring interaktif untuk memantau progres implementasi, task list, timeline roadmap 7 fase, dan rollout 30 outlet Apotek Keluarga berbasis **Laravel 11/12 + Tailwind CSS v4 + Alpine.js + ApexCharts**.

---

## 🏗️ Arsitektur Docker Production

Aplikasi dikemas dalam arsitektur container produksi mandiri dan efisien:
- **Stage 1 (Frontend Builder)**: Node.js 20 Alpine untuk mengompilasi Vite & Tailwind CSS v4 (`public/build`).
- **Stage 2 (Production Runtime)**: PHP 8.3 FPM Alpine + Nginx + Supervisord.
- **Process Manager**: Supervisord menjalankan **PHP-FPM**, **Nginx**, dan **Laravel Queue Worker** secara simultan dalam 1 container terpadu.
- **Reverse Proxy / Tunnel Ready**: Dilengkapi dengan konfigurasi `trustProxies` dan Nginx FastCGI header forwarding untuk menangani terminasi SSL HTTPS dari Cloudflare Tunnel / Coolify Traefik tanpa loop redirect atau mixed content.

---

## 🌐 Konfigurasi Port & Tunnel

| Komponen | Port | Keterangan |
| :--- | :--- | :--- |
| **Container Port** | `80` | Port HTTP internal yang dilayani oleh Nginx di dalam container. |
| **Host Port (Server VPS)** | `4040` | Port yang dipublikasikan ke host server (dapat diubah via `APP_PORT`). |
| **Tunnel Target URL** | `http://localhost:4040` | Alamat tujuan yang dimasukkan ke konfigurasi Tunnel (Cloudflare Tunnel / Ngrok). |
| **Public Domain** | `https://timeline.apotekkeluarga.id` | Domain publik yang diakses oleh pengguna melalui HTTPS. |

### 🛰️ Cara Menghubungkan Cloudflare Tunnel

Jika menggunakan **Cloudflare Tunnel (`cloudflared`)**:
1. Buka dashboard **Cloudflare Zero Trust** > **Networks** > **Tunnels**.
2. Pilih Tunnel Anda > **Configure** > **Public Hostname**.
3. Tambahkan rincian hostname:
   - **Subdomain**: `timeline`
   - **Domain**: `apotekkeluarga.id`
   - **Type**: `HTTP`
   - **URL**: `localhost:4040` *(atau `127.0.0.1:4040`)*
4. Simpan konfigurasi. Cloudflare Tunnel akan otomatis meneruskan traffic HTTPS publik langsung ke container aplikasi.

---

## 📋 Variabel Environment Produksi (.env)

Salin konfigurasi berikut ke tab **Environment Variables** di Coolify:

```env
APP_NAME="Timeline Apotek Keluarga"
APP_ENV=production
APP_KEY=base64:pFgrTBy7WOrkSMUHhiw0d3BZ0CkgVj1+L+FfVREV/88=
APP_DEBUG=false
APP_URL=https://timeline.apotekkeluarga.id
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=kgmk7g5eq0iqsz0ddoyutha3
DB_PORT=3306
DB_DATABASE=timeline_ak
DB_USERNAME=root
DB_PASSWORD=wyn8pJ5M3pqR6ORVX348P3LxVpgxycRrShJl4gpH0cyt7uAP9nUGwRJHE1dURzmP

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

# Menjalankan migrasi database otomatis saat container start
RUN_MIGRATIONS=true

# Otomatis mengisi seeder awal (Admin & 70 task timeline) jika database masih kosong
SEED_IF_EMPTY=true

# Port yang dipublikasikan ke server host (untuk Tunnel)
APP_PORT=4040
```

---

## 🚢 Panduan Deployment di Coolify

### Metode 1: Deploy Menggunakan Docker Compose (Direkomendasikan)

1. Di dashboard Coolify, buka **Projects** > Pilih environment Anda.
2. Klik **+ Add Resource** > Pilih **Docker Compose**.
3. Masukkan repository Git Anda atau salin isi file `docker-compose.yml`.
4. Masuk ke menu **Environment Variables**, lalu tempel konfigurasi di atas.
5. Pastikan network `coolify` terhubung agar container aplikasi dapat mengakses database MySQL (`DB_HOST=kgmk7g5eq0iqsz0ddoyutha3`).
6. Klik **Deploy**.

### Metode 2: Deploy Menggunakan Dockerfile

1. Di dashboard Coolify, klik **+ Add Resource** > Pilih **Application** (dari Git Repository).
2. Pada **Build Pack**, pilih **Dockerfile**.
3. Atur **Ports Exposes** ke `80`.
4. Jika ingin mengakses via tunnel host port, atur **Ports Mappings** ke `4040:80`.
5. Masukkan seluruh environment variables di tab **Environment Variables**.
6. Atur Persistent Storage:
   - Volume: `timeline_ak_storage` -> `/var/www/html/storage`
7. Klik **Deploy**.

---

## 🔐 Kredensial Default Aplikasi

- **URL Akses Publik**: `https://timeline.apotekkeluarga.id`
- **Password Klien (Screen Lock Gate)**: `keluarga2026`
- **Admin Panel URL**: `https://timeline.apotekkeluarga.id/admin/login`
- **Username Admin**: `admin`
- **Password Admin**: `admin123`

---

## 🧪 Pengujian & Verifikasi Lokal

Untuk menjalankan pengujian unit & fitur:
```bash
php artisan test
```

Untuk menjalankan container di komputer lokal (SQLite standalone):
```bash
docker compose -f docker-compose.local.yml up --build
```
Akses di browser: `http://localhost:4040`
