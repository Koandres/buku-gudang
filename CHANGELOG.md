# Changelog — Buku Gudang

Catatan perubahan aplikasi. **Aturan:** sebelum mengubah kode, tulis rencananya dulu di bagian **Rencana**. Setelah selesai dieksekusi dan diuji, pindahkan baris yang sama ke bagian **Selesai** (paling atas) dan hapus dari **Rencana**.

Format satu baris:

`- YYYY-MM-DD · Berkas: <file terdampak> · Ubah: <apa yang diubah>`

## Rencana

- 2026-11-18 · Berkas: tests/Feature/ExampleTest.php · Ubah: Menyesuaikan tes bawaan dengan pengalihan halaman awal ke login
- 2026-11-18 · Berkas: tests/Feature/PurchaseRequestFlowTest.php · Ubah: Menambah tes alur permintaan pembelian termasuk penolakan persetujuan diri sendiri
- 2026-11-18 · Berkas: app/Models/DocumentSequence, app/Services/DocumentNumberService, app/Http/Controllers/PurchaseRequestController, resources/js/app.jsx · Ubah: Memperbaiki penomoran dokumen (tabel sequence tanpa timestamps) dan menghapus pencatat diagnostik
- 2026-11-18 · Berkas: resources/js/app.jsx, app/Http/Controllers/PurchaseRequestController, app/Services/DocumentNumberService · Ubah: Menambah pencatat sementara untuk mendiagnosis respons HTTP yang memicu dialog error Inertia
- 2026-11-18 · Berkas: app/Services/DocumentNumberService, app/Services/PurchaseRequestService · Ubah: Menambah penomoran dokumen terkunci dan aturan bisnis permintaan pembelian
- 2026-11-18 · Berkas: app/Policies/PurchaseRequestPolicy, app/Http/Requests, app/Http/Controllers/PurchaseRequestController, routes/web.php · Ubah: Menambah rute, otorisasi, dan validasi permintaan pembelian
- 2026-11-18 · Berkas: resources/js/Pages/PurchaseRequests · Ubah: Menambah halaman daftar, formulir, dan detail permintaan pembelian

## Selesai

- 2026-11-18 · Berkas: resources/js/Pages/Master, resources/js/Components · Ubah: Menambah halaman daftar dan formulir master data dengan komponen bersama (diuji tambah supplier lewat preview)
- 2026-11-18 · Berkas: app/Policies, app/Traits, app/Http/Requests, app/Http/Controllers, routes/web.php · Ubah: Menambah otorisasi Policy dan aturan master data (supplier, barang, gudang, pengguna) (diuji `npm run build` dan alur tambah supplier)

- 2026-11-18 · Berkas: database/seeders · Ubah: Menambah seeder lima akun per peran (diuji dengan `php artisan db:seed`)
- 2026-11-18 · Berkas: app/Http/Middleware, app/Http/Controllers/AuthController, routes/web.php, resources/js, resources/css, resources/views · Ubah: Membuat autentikasi, middleware peran, layout Inertia React, dan token tema 'Buku Besar' (diuji login admin di preview)
- 2026-11-18 · Berkas: database/migrations, app/Models, app/Enums · Ubah: Membuat skema lengkap (peran, master, dokumen, stok, log) beserta model dan Enum (diuji dengan `php artisan migrate`)
- 2026-11-18 · Berkas: config/erp.php, config/database.php, .env, .env.example · Ubah: Menambah konfigurasi aplikasi dan SQLite WAL, busy_timeout, foreign key (diuji dengan migrasi berhasil)
- 2026-11-18 · Berkas: composer.json, package.json, vite.config.js · Ubah: Memasang Inertia + React + Vite sebagai kerangka frontend starter kit (diuji dengan `npm run build`)
- 2026-11-18 · Berkas: CHANGELOG.md, AGENTS.md, CLAUDE.md · Ubah: Membuat changelog dua bagian, aturan kerja agen, dan penunjuk CLAUDE.md (diverifikasi dengan membaca berkas)
