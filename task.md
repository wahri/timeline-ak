# TASK IMPLEMENTATION PLAN: Sistem Monitoring Task & Outstanding Apotek Keluarga

## 📌 Deskripsi Projek
Sistem monitoring task list dan outstanding task untuk proyek Apotek Keluarga berbasis **Laravel + MySQL + Blade + Tailwind CSS + Alpine.js + ApexCharts**.

Sistem memiliki dua bagian utama:
1. **Client / Stakeholder Monitoring Dashboard**:
   - Dilindungi password akses yang tersimpan dan dapat diatur di database.
   - Dashboard analitik interaktif: Progress radial gauge, status donut chart, modul bar chart, alert blocker & overdue task.
   - Daftar task dinamis (filter, search, tab status, toggle tampilan Table / Kanban Card, modal detail task, export).
2. **Admin Management Panel**:
   - Login username & password.
   - Manajemen penuh Task (Create, Edit, Delete, Quick Status Update, Input Blocker/Kendala).
   - Pengaturan Proyek & Ganti Password Akses Klien.

---

## 🗂️ Rencana Fase Kerja

- [x] **Fase 1: Inisialisasi & Setup Laravel Database** <!-- id: 0 -->
  - Buat fresh project Laravel di `d:\laravel\timeline-AK`
  - Konfigurasi environment `.env` (koneksi MySQL `timeline_apotek_keluarga`)
  - Buat database `timeline_apotek_keluarga` di MySQL

- [x] **Fase 2: Skema Database, Model & Seeder Realistis** <!-- id: 1 -->
  - Migration & Model `ProjectSetting` (password klien, nama proyek, target deadline)
  - Migration & Model `Task` (judul, modul, milestone, status, prioritas, PIC, due_date, blocker_notes)
  - Seeder data akun admin default & sample tasks realistis operasional Apotek Keluarga

- [x] **Fase 3: Autentikasi & Middleware Akses Klien + Admin** <!-- id: 2 -->
  - Screen Lock / Password Gate untuk Client Dashboard
  - Session verification middleware untuk Client Access
  - Login & Session Auth untuk Admin Panel

- [x] **Fase 4: Client Monitoring Dashboard (Interactive & Charts)** <!-- id: 3 -->
  - Header & Stat KPI Cards (Total Task, Selesai %, Outstanding, Blocker Alert)
  - ApexCharts Integration (Radial Progress, Status Distribution, Module Breakdown)
  - Interactive Task List (Search, Multi-filter, View Switcher Table vs Kanban, Modal Detail, Export)

- [x] **Fase 5: Admin Panel & CRUD Task Management** <!-- id: 4 -->
  - Antarmuka Admin modern (Sidebar, Navbar, Toast alerts)
  - CRUD Task dengan modal/form lengkap
  - Quick action status & blocker updater
  - Halaman Pengaturan Proyek & Ubah Password Klien

- [x] **Fase 6: Verifikasi, Polish & End-to-End Testing** <!-- id: 5 -->
  - Pengujian alur akses password klien (salah/benar)
  - Pengujian CRUD & sinkronisasi data chart secara dinamis
  - Pengujian responsive UI (Desktop & Mobile)

- [x] **Fase 7: Penggabungan Timeline Roadmap & Rollout 30 Outlet (Desain Gambar)** <!-- id: 6 -->
  - Restrukturisasi Seeder Task ke dalam 7 Fase Timeline terpadu (53 Task riil)
  - Penambahan task & status Rollout 30 Outlet Apotek Keluarga (100% Selesai / Live)
  - Pembuatan komponen visual Interactive Timeline Roadmap 7 Fase di Dashboard Klien
  - Pembuatan widget status 4 Gelombang Rollout 30 Outlet (Wave 1 s/d Wave 4 lengkap dengan daftar 30 cabang)
  - Penambahan filter dinamis berdasarkan Fase Timeline pada Task Explorer & Admin Panel
  - Re-run test suite & verifikasi tampilan dashboard baru (14 test passed 100%)

- [x] **Fase 8: Integrasi Penuh 74 Task Timeline & Tanggal Mulai (start_date) Sesuai Gambar** <!-- id: 7 -->
  - Migrasi penambahan kolom `start_date` pada skema database `tasks`.
  - Update Model `Task` (`$fillable`, cast as `date`).
  - Update Validasi & Form Admin (`TaskController::store`, `update`, modal Create & Edit).
  - Ekstraksi dan konstruksi 74 task komprehensif dari gambar:
    * Fase 1: Planning & Setup Workspace (3 task, 01 - 14 Jan 2026)
    * Fase 2: Master Data & Supply Chain (10 task, 15 Jan - 05 Feb 2026)
    * Fase 3: Sales, POS & Multi-UoM (6 task, 01 - 15 Feb 2026)
    * Fase 4: Rollout 30 Outlet Apotek Keluarga 100% Live (34 task: 4 Wave Summary + 30 task outlet individual, 01 Feb - 31 Mar 2026)
    * Fase 5: Accounting & Financial Refinement (9 task, 01 Feb - 20 Apr 2026, termasuk blocker HPP & PnL)
    * Fase 6: HR & General Affair / HCGA (8 task, 01 Apr - 20 Mei 2026)
    * Fase 7: Digital Expansion & AI Intelligence (4 task, 01 Mar - 20 Apr 2026)
  - Tampilan visual rentang tanggal timeline (`start_date` s/d `due_date`) pada tabel Client Dashboard, kartu Kanban, dan Modal Detail.
  - Tampilan visual rentang tanggal timeline dan input `start_date` pada Admin Panel.
  - Penambahan kolom `Tanggal Mulai` pada fitur Export CSV/Excel.
- [x] **Fase 9: Pemisahan Rollout Outlet Menjadi 30 Outlet Individual (Apotek Keluarga 01 s/d 31, tanpa 10)** <!-- id: 8 -->
  - Memisahkan seluruh task rollout outlet menjadi satu per satu outlet individual di database (30 task mandiri untuk 30 outlet).
  - Standarisasi penamaan outlet resmi: `Apotek Keluarga 01` sampai `Apotek Keluarga 31`, dengan `Apotek Keluarga 10` dilewati/tidak ada (total pas 30 outlet).
  - Pemetaan gelombang rollout dan tanggal implementasi presisi:
    * Wave 1 (5 outlet, 01 Feb - 10 Feb 2026): Apotek Keluarga 01 s/d 05
    * Wave 2 (7 outlet, 11 Feb - 20 Feb 2026): Apotek Keluarga 06 s/d 09, 11 s/d 13 (tanpa 10)
    * Wave 3 (8 outlet, 21 Feb - 05 Mar 2026): Apotek Keluarga 14 s/d 21
    * Wave 4 (10 outlet, 06 Mar - 31 Mar 2026): Apotek Keluarga 22 s/d 31
  - Pembaruan data `$rolloutWaves` di `ClientDashboardController.php` dengan daftar nama 30 outlet resmi.
  - Re-seeding database MySQL dengan `php artisan db:seed` (Total 70 task terdaftar dengan 30 task rollout individual).
- [x] **Fase 10: Penjadwalan Ulang Task Outstanding Mulai Hari Ini (Overdue = 0)** <!-- id: 9 -->
  - Konfigurasi `APP_TIMEZONE=Asia/Jakarta` di `.env` dan `config/app.php` agar sinkron dengan waktu server lokal (WIB).
  - Penjadwalan ulang seluruh 14 task outstanding (`status != 'completed'`) dengan tanggal mulai hari ini (`start_date: 2026-09-25` / `$today`) dan tenggat waktu mendatang (`due_date` di bulan Oktober & November 2026).
  - Penyesuaian `Task::getIsOverdueAttribute` menjadi `$this->due_date->lt(Carbon::today())` yang konsisten dengan logika query dan waktu hari berjalan.
  - Penyesuaian `target_completion_date` proyek pada `ProjectSetting` menjadi 60 hari ke depan (November 2026) untuk mengakomodasi fase stabilisasi dan penyelesaian modul lanjutan.
  - Verifikasi: Overdue task = 0 (tidak ada task yang overdue).
  - Pengujian otomatis pada `MonitoringSystemTest.php` lulus 100% (16 tests, 72 assertions).
- [x] **Fase 11: Dockerization & Konfigurasi Deployment Coolify** <!-- id: 10 -->
  - Pembuatan konfigurasi Nginx container (`docker/nginx.conf`)
  - Pembuatan konfigurasi Process Manager Supervisor (`docker/supervisord.conf`)
  - Pembuatan script inisialisasi & startup container (`docker/entrypoint.sh`)
  - Pembuatan multi-stage build production image (`Dockerfile`)
  - Pembuatan file `.dockerignore` untuk efisiensi build
  - Panduan deploy step-by-step di Coolify (GitHub Integration, Environment Variables, Database, Persistent Storage)

