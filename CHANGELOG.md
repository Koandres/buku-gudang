# Changelog — Buku Gudang

Catatan perubahan aplikasi. **Aturan:** sebelum mengubah kode, tulis rencananya dulu di bagian **Rencana**. Setelah selesai dieksekusi dan diuji, pindahkan baris yang sama ke bagian **Selesai** (paling atas) dan hapus dari **Rencana**.

Format satu baris:

`- YYYY-MM-DD · Berkas: <file terdampak> · Ubah: <apa yang diubah>`

## Rencana


## Selesai

- 2026-11-19 · Berkas: .github/workflows/ci.yml, CHANGELOG.md · Ubah: Menambah workflow GitHub Actions untuk menjalankan Pint, tes, dan build pada push/PR (diverifikasi dengan membaca berkas, keberadaan composer.lock/package-lock.json, dan cabang main; belum dijalankan di GitHub Actions)
- 2026-11-19 · Berkas: tests/Feature/PurchaseOrderFlowTest.php, tests/Feature/PurchaseRequestFlowTest.php · Ubah: Menambah tes regresi pembatalan PO setelah penerimaan sebagian dan larangan mengubah PR non-draft (diuji `php artisan test` 41/41)
- 2026-11-19 · Berkas: resources/js/Components/Pagination.jsx, resources/js/Pages/Dashboard.jsx, resources/js/Pages/Reports/Index.jsx, resources/js/Pages/PurchaseOrders/Show.jsx, resources/css/app.css · Ubah: Mengganti inline style berulang dengan kelas/token yang sudah ada dan membuat pagination merender teks tanpa dangerouslySetInnerHTML (diuji `npm run build`)
- 2026-11-19 · Berkas: resources/js/app.js, resources/views/welcome.blade.php · Ubah: Menghapus entri starter kit yang tidak dipakai (diverifikasi tidak ada referensi sumber tersisa; hanya cache Blade otomatis)
- 2026-11-19 · Berkas: README.md · Ubah: Menyelaraskan dokumentasi: dashboard/laporan untuk semua peran aktif, Boost sebagai dev dependency, dan kata sandi default pengguna baru (diverifikasi dengan membaca berkas)
- 2026-11-19 · Berkas: resources/js/Pages/PurchaseOrders/Show.jsx · Ubah: Membatasi hasil cetak PO hanya dokumen PO dengan memberi `no-print` pada panel penerimaan dan riwayat (diverifikasi `npm run build`; print.css sudah memiliki aturan `.no-print` sehingga tidak diubah)
- 2026-11-19 · Berkas: app/Services/CsvExportService.php, tests/Feature/ReportsActivityTest.php · Ubah: Menetralisir nilai CSV yang diawali rumus spreadsheet dan menambah tes ekspor (diuji `php artisan test` 39/39)
- 2026-11-19 · Berkas: app/Http/Requests/PurchaseRequestRequest.php, app/Http/Requests/StockAdjustmentRequest.php, tests/Feature/PurchaseRequestFlowTest.php, tests/Feature/GoodsReceiptStockTest.php · Ubah: Membatasi barang aktif dan melarang baris duplikat pada PR/penyesuaian stok disertai tes validasi (diuji `php artisan test` 38/38)
- 2026-11-19 · Berkas: app/Providers/AppServiceProvider, bootstrap/app.php, app/Http/Middleware/EnsureRole.php, tests/Feature/ActiveUserAccessTest.php · Ubah: Menolak seluruh akses akun nonaktif lewat Gate::before, menghapus middleware peran yang tidak dipakai, dan menambah tes otorisasi (diuji `php artisan test` 36/36)
- 2026-11-19 · Berkas: .agents/skills · Ubah: Memasang skill ponytail, modern-web-guidance, grilling, dan teach dari repositori GitHub yang ditentukan (diverifikasi keempat skill berhasil dimuat lewat tool skill)
- 2026-11-18 · Berkas: docs/PANDUAN-MODIFIKASI.md, .gitignore · Ubah: Menambah panduan modifikasi lokal dan mengabaikannya dari Git (diverifikasi `git check-ignore -v`: `.gitignore:28`)
- 2026-11-18 · Berkas: README.md · Ubah: Mengganti README bawaan dengan panduan aplikasi Buku Gudang (fitur, instalasi, akun peran, alur kerja, penomoran, dan perintah pengembangan) (diverifikasi dengan membaca berkas)
- 2026-11-18 · Berkas: app/Http/Controllers/StockController, tests/Feature/GoodsReceiptStockTest · Ubah: Memisahkan saldo berjalan kartu stok per barang dan gudang agar tidak menggabungkan mutasi lintas barang (diuji `php artisan test` 23/23 dan preview: BRG-C03 60→56, BRG-C01 cabang 12→7)
- 2026-11-18 · Berkas: tests/Feature/SampleDataSeederTest.php · Ubah: Menambah tes cakupan seluruh status, idempotensi, sinkronisasi nomor, serta saldo dan laporan dari data contoh (diuji `php artisan test` 23/23)
- 2026-11-18 · Berkas: database/seeders/SampleDataSeeder, database/seeders/DatabaseSeeder · Ubah: Menambah data contoh fiktif enam bulan dengan semua status dokumen, mutasi stok, dan log aktivitas serta menyelaraskan document_sequences (diuji `php artisan db:seed` dan preview dashboard/laporan; model DocumentSequence sudah tepat tanpa perubahan)
- 2026-11-18 · Berkas: tests/Feature/ReportsActivityTest.php · Ubah: Menambah tes dashboard, lima laporan, ekspor CSV, dan pembatasan log aktivitas (diuji `php artisan test` 18/18)
- 2026-11-18 · Berkas: resources/js/Pages/Dashboard.jsx, resources/js/Pages/Reports, resources/js/Pages/Activities, resources/js/Components/ReportShell.jsx · Ubah: Menampilkan data dashboard nyata, tabel laporan, tombol ekspor CSV, dan log aktivitas (diuji `npm run build` dan preview)
- 2026-11-18 · Berkas: app/Http/Controllers/ReportController, app/Http/Controllers/ActivityLogController, app/Http/Controllers/DashboardController, routes/web.php · Ubah: Menambah rute laporan/ekspor dan halaman log aktivitas dengan otorisasi (diuji di preview)
- 2026-11-18 · Berkas: app/Services/ReportService, app/Services/CsvExportService · Ubah: Menghitung statistik dashboard, lima laporan, dan ekspor CSV dengan BOM tanpa paket baru (diuji `php artisan test`)

- 2026-11-18 · Berkas: app/Providers/AppServiceProvider · Ubah: Mendaftarkan StockPolicy untuk model StockMovement agar otorisasi penyesuaian stok tidak 403 (diuji `php artisan test` dan preview)
- 2026-11-18 · Berkas: tests/Feature/GoodsReceiptStockTest.php · Ubah: Menambah tes alur GR sebagian hingga selesai, kelebihan jumlah, PO draft, dan penyesuaian stok (diuji `php artisan test` 14/14)
- 2026-11-18 · Berkas: resources/js/Pages/GoodsReceipts, resources/js/Pages/Stock, resources/js/Components/statusMeta.js · Ubah: Menambah halaman daftar/form/detail GR, kartu stok, dan formulir penyesuaian (diuji `npm run build` dan alur di preview)
- 2026-11-18 · Berkas: app/Policies/GoodsReceiptPolicy, app/Policies/StockPolicy, app/Http/Requests/GoodsReceiptRequest, app/Http/Requests/StockAdjustmentRequest, app/Http/Controllers/GoodsReceiptController, app/Http/Controllers/StockController, routes/web.php · Ubah: Menambah otorisasi, validasi, dan rute penerimaan barang serta kartu/penyesuaian stok (diuji di preview)
- 2026-11-18 · Berkas: config/erp.php, app/Services/StockService, app/Services/GoodsReceiptService · Ubah: Menambah prefix ADJ, saldo stok dari buku besar, penerimaan sebagian, dan penyesuaian stok (diuji `php artisan test` dan preview: GR 20 + 30, saldo 47 setelah ADJ -3)

- 2026-11-18 · Berkas: app/Http/Controllers/PurchaseRequestController, app/Http/Controllers/PurchaseOrderController · Ubah: Menyertakan foreign key pada eager load agar daftar PO di detail PR tampil (diuji di preview)
- 2026-11-18 · Berkas: tests/Feature/PurchaseOrderFlowTest.php · Ubah: Menambah tes alur PO dari PR (bertahap, terbit, batal kembalikan sisa, kelebihan jumlah, larangan pemohon) (diuji `php artisan test`)
- 2026-11-18 · Berkas: resources/js/Pages/PurchaseOrders, resources/js/Components/ItemRows.jsx, resources/js/Pages/PurchaseRequests · Ubah: Menambah halaman daftar/form/detail PO, tombol cetak, dan tautan buat PO dari PR (diuji `npm run build` dan alur di preview)
- 2026-11-18 · Berkas: app/Policies/PurchaseOrderPolicy, app/Http/Requests/PurchaseOrderRequest, app/Http/Controllers/PurchaseOrderController, routes/web.php · Ubah: Menambah otorisasi, validasi, rute, dan kontroler pesanan pembelian (diuji di preview)
- 2026-11-18 · Berkas: app/Services/PurchaseOrderService, app/Services/PurchaseRequestService · Ubah: Menambah aturan bisnis PO dari PR (potong sisa, tandai diproses, terbit, batalkan dan kembalikan sisa) (diuji `php artisan test` dan preview)

- 2026-11-18 · Berkas: tests/Feature/ExampleTest.php, tests/Feature/PurchaseRequestFlowTest.php · Ubah: Menyesuaikan tes bawaan dan menambah tes alur PR (buat/ajukan, tolak setujui diri sendiri, setujui, tolak dengan alasan) (diuji `php artisan test`)
- 2026-11-18 · Berkas: app/Models/DocumentSequence · Ubah: Menonaktifkan timestamps karena tabel sequence tidak memiliki kolom waktu (diuji membuat PR di preview dan `php artisan test`)
- 2026-11-18 · Berkas: app/Services/DocumentNumberService · Ubah: Membuat penomoran PR terkunci yang toleran konflik insert dan menghapus log diagnostik (diuji membuat PR di preview)
- 2026-11-18 · Berkas: app/Http/Controllers/PurchaseRequestController, resources/js/app.jsx · Ubah: Membersihkan pencatat diagnostik respons Inertia dari awal fase PR
- 2026-11-18 · Berkas: app/Services/PurchaseRequestService · Ubah: Aturan bisnis PR (draft/update/submit/approve/reject/destroy/tandaiDiproses) dan penomoran di transaksi
- 2026-11-18 · Berkas: app/Policies/PurchaseRequestPolicy, app/Http/Requests/PurchaseRequestRequest, app/Http/Requests/RejectRequest, routes/web.php · Ubah: Otorisasi, validasi, dan rute PR (submit/approve/reject) (diuji alur di preview)
- 2026-11-18 · Berkas: resources/js/Pages/PurchaseRequests · Ubah: Halaman daftar, formulir, dan detail PR (diuji buat draft dan ajukan di preview)

- 2026-11-18 · Berkas: resources/js/Pages/Master, resources/js/Components · Ubah: Menambah halaman daftar dan formulir master data dengan komponen bersama (diuji tambah supplier lewat preview)
- 2026-11-18 · Berkas: app/Policies, app/Traits, app/Http/Requests, app/Http/Controllers, routes/web.php · Ubah: Menambah otorisasi Policy dan aturan master data (supplier, barang, gudang, pengguna) (diuji `npm run build` dan alur tambah supplier)

- 2026-11-18 · Berkas: database/seeders · Ubah: Menambah seeder lima akun per peran (diuji dengan `php artisan db:seed`)
- 2026-11-18 · Berkas: app/Http/Middleware, app/Http/Controllers/AuthController, routes/web.php, resources/js, resources/css, resources/views · Ubah: Membuat autentikasi, middleware peran, layout Inertia React, dan token tema 'Buku Besar' (diuji login admin di preview)
- 2026-11-18 · Berkas: database/migrations, app/Models, app/Enums · Ubah: Membuat skema lengkap (peran, master, dokumen, stok, log) beserta model dan Enum (diuji dengan `php artisan migrate`)
- 2026-11-18 · Berkas: config/erp.php, config/database.php, .env, .env.example · Ubah: Menambah konfigurasi aplikasi dan SQLite WAL, busy_timeout, foreign key (diuji dengan migrasi berhasil)
- 2026-11-18 · Berkas: composer.json, package.json, vite.config.js · Ubah: Memasang Inertia + React + Vite sebagai kerangka frontend starter kit (diuji dengan `npm run build`)
- 2026-11-18 · Berkas: CHANGELOG.md, AGENTS.md, CLAUDE.md · Ubah: Membuat changelog dua bagian, aturan kerja agen, dan penunjuk CLAUDE.md (diverifikasi dengan membaca berkas)
