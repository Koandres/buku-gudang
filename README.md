# Buku Gudang

Mini ERP untuk alur **permintaan pembelian → persetujuan → pesanan pembelian → penerimaan barang bertahap → kartu stok**, dilengkapi dashboard, laporan dengan ekspor CSV, dan log aktivitas.

Aplikasi ini dibuat sebagai **studi kasus portofolio**. Seluruh nama, alamat, harga, dan transaksi adalah **data fiktif**.

## Tumpukan teknologi

- Laravel 13 dengan SQLite (WAL, `foreign_keys`, `busy_timeout`), Eloquent dan migration agar tetap agnostik database.
- Inertia.js + React 19 + Vite (starter kit), tanpa paket Composer/npm tambahan di luar starter kit.
- Pest untuk tes otomatis dan Pint untuk format kode.
- Tampilan bertema "Buku Besar" melalui `resources/css/tokens.css`, font lokal, CSS cetak terpisah, dan tanpa CDN.

## Fitur utama

- **Master data:** supplier, barang, gudang, dan pengguna dengan hak akses per peran.
- **Permintaan pembelian (PR):** buat draft, ajukan, setujui/tolak (persetujuan diri sendiri dilarang), dan tandai sedang diproses.
- **Pesanan pembelian (PO):** dibuat dari PR secara sebagian maupun penuh, terbitkan, batalkan dengan pengembalian sisa PR, serta cetak PO.
- **Penerimaan barang (GR):** terima sebagian atau seluruh isi PO; status PO bergerak otomatis menjadi `Sebagian diterima` atau `Selesai`.
- **Stok:** kartu stok per barang dan gudang, saldo dihitung dari seluruh mutasi, serta penyesuaian stok bernomor `ADJ`.
- **Laporan:** ringkasan, PO outstanding, pembelian per supplier, stok per gudang, dan barang perlu dipesan; semuanya dapat diekspor ke CSV.
- **Audit:** log aktivitas untuk perubahan dokumen dan data master (khusus admin).
- Antarmuka responsif dengan mode terang/gelap.

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
# Pastikan berkas database/database.sqlite tersedia
php artisan migrate
npm install
npm run build
php artisan serve
```

Jika memakai Laravel Herd, buka `http://buku-gudang.test`. Rangkaian `composer setup` juga dapat dipakai untuk melakukan sebagian besar langkah di atas. Saat mengembangkan antarmuka, jalankan `npm run dev` di terminal terpisah.

## Data awal

```bash
php artisan db:seed
```

Perintah ini mengisi lima akun peran **dan** data contoh fiktif selama enam bulan dengan semua status dokumen, penerimaan, mutasi stok, dan log aktivitas. Keduanya idempoten sehingga aman dijalankan ulang. Untuk mengisi akun saja:

```bash
php artisan db:seed --class=RoleAccountSeeder
```

| Peran | Email | Kewenangan utama |
| --- | --- | --- |
| Pemohon | `pemohon@bukugudang.test` | Membuat dan mengajukan PR |
| Penyetuju | `penyetuju@bukugudang.test` | Menyetujui atau menolak PR (bukan PR milik sendiri) |
| Pembelian | `pembelian@bukugudang.test` | Membuat dan menerbitkan PO, membatalkan PO, mengelola supplier, mencetak PO |
| Gudang | `gudang@bukugudang.test` | Mencatat penerimaan barang dan penyesuaian stok |
| Admin | `admin@bukugudang.test` | Master data, pengguna, laporan, dan log aktivitas |

Kata sandi semua akun: **`password`**.

## Alur singkat

1. **Pemohon** membuat PR, memilih barang dan jumlah, lalu mengajukannya.
2. **Penyetuju** menyetujui atau menolak PR disertai alasan.
3. **Pembelian** membuat PO dari PR yang disetujui, lalu menerbitkannya.
4. **Gudang** mencatat penerimaan; satu PO dapat diterima beberapa kali hingga selesai.
5. Mutasi penerimaan masuk ke kartu stok; penyesuaian dapat dicatat bila ada selisih fisik.
6. **Admin** memantau dashboard, laporan, dan log aktivitas.

## Penomoran dan aturan bisnis

- Nomor dokumen mengikuti pola `PREFIX-YYYYMM-0001` dengan prefiks `PR`, `PO`, `GR`, dan `ADJ` dari `config/erp.php`.
- Nomor diambil dari tabel `document_sequences` di dalam transaksi dengan `lockForUpdate()` agar tidak ganda. Data contoh memakai blok `0101` supaya tidak bentrok dengan dokumen uji, dan sequences diselaraskan setelah seeding.
- Uang dan jumlah disimpan sebagai bilangan bulat (rupiah tanpa desimal).
- Jumlah penerimaan tidak boleh melebihi sisa PO. Pembatalan PO mengembalikan sisa ke PR.
- Saldo stok selalu dihitung ulang dari buku besar mutasi, bukan dari kolom saldo tersimpan.
- Perubahan yang menyentuh banyak tabel dijalankan di dalam transaksi; aturan bisnis ada di `app/Services`, validasi di `app/Http/Requests`, dan otorisasi di `app/Policies`.

## Struktur penting

| Lokasi | Isi |
| --- | --- |
| `app/Services` | Aturan bisnis PR, PO, GR, stok, penomoran, laporan, dan ekspor CSV |
| `app/Enums` | Status dokumen, jenis mutasi, dan peran pengguna |
| `app/Policies`, `app/Http/Requests` | Otorisasi dan validasi |
| `app/Http/Controllers` | Orkestrasi request dan props Inertia |
| `resources/js/Pages` | Halaman React per modul |
| `resources/css/tokens.css` | Token warna, tipografi, dan tema "Buku Besar" |
| `config/erp.php` | Prefiks dokumen, satuan, dan format mata uang |
| `database/seeders/SampleDataSeeder.php` | Data contoh fiktif |
| `docs/PANDUAN-MODIFIKASI.md` | Panduan modifikasi (berkas lokal, masuk `.gitignore`) |

## Perintah pengembangan

```bash
php artisan test              # menjalankan seluruh tes
vendor/bin/pint --format agent # memeriksa dan merapikan kode PHP
npm run build                 # membangun aset frontend untuk produksi
```

## Catatan

- Semua data adalah fiktif untuk keperluan demo dan portofolio.
- Tidak ada aset atau font yang dimuat dari CDN.
- `CHANGELOG.md` mencatat rencana dan hasil pekerjaan; `AGENTS.md` berisi aturan kerja agen pada proyek ini.
