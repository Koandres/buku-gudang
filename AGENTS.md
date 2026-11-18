<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

## Aturan Kerja Buku Gudang (wajib, berlaku sampai proyek selesai)

Aturan ini mengikat setiap sesi agen. Baca `CHANGELOG.md` lebih dulu di awal sesi; jika bagian **Rencana** berisi baris, itu pekerjaan yang terputus — periksa kode yang sebenarnya, lanjutkan, jangan ulang dari nol dan jangan buat rencana tumpang tindih.

### Changelog dua bagian

1. Sebelum mengubah kode, tulis satu baris rencana di `CHANGELOG.md` bagian **Rencana**. Satu baris = satu unit kerja yang bisa diuji sendiri (misalnya satu migrasi beserta modelnya), bukan satu baris per berkas.
2. Format: `- YYYY-MM-DD · Berkas: <file terdampak> · Ubah: <apa yang diubah>`.
3. Eksekusi, lalu uji dengan tes otomatis atau pemeriksaan manual yang relevan.
4. Setelah lulus uji, pindahkan baris yang sama ke **Selesai** (paling atas), sesuaikan dengan hasil nyata, dan hapus dari **Rencana**.
5. Jangan tandai Selesai sebelum diuji. Jika terblokir, biarkan di **Rencana** dan tambahkan `· Catatan: <penyebab singkat>`.
6. Perubahan `.env`, `.gitignore`, dependensi, dan dokumentasi juga dicatat. Bahasa Indonesia, ringkas.

### Persetujuan perintah dan batas area kerja

- Agen **tidak boleh** menjalankan perintah terminal apa pun (`composer`, `npm`, `php artisan`, `git`, dan lain-lain) sebelum menjelaskan perintahnya dan mendapat **persetujuan eksplisit** dari pengguna. Persetujuan hanya untuk perintah/rangkaian perintah yang disebutkan, bukan izin umum.
- Semua perintah dijalankan di dalam direktori proyek ini. Agen tidak boleh membaca, menulis, atau menghapus berkas di luar direktori proyek, tidak memakai path ke luar proyek, tidak memasang paket global, dan tidak mengubah konfigurasi sistem.
- Perintah merusak/sulit dipulihkan (`migrate:fresh`, `db:wipe`, `rm -rf`, `git reset --hard`, `git push --force`, menghapus berkas database, dll.) butuh konfirmasi terpisah yang menyebut dampaknya secara jelas.

### Urutan pengerjaan (kerjakan bertahap, uji, baru lanjut)

1. **Fondasi:** `CHANGELOG.md`, `AGENTS.md`, `CLAUDE.md`; starter kit (Inertia + React atau Vue + Vite), SQLite WAL + foreign key, kolom peran, middleware peran, `tokens.css`, layout dasar, seeder akun per peran.
2. **Master data:** supplier, barang, gudang, pengguna.
3. **Permintaan pembelian (PR):** buat, ajukan, setujui/tolak.
4. **Pesanan pembelian (PO):** dari PR, terbit, batalkan, cetak.
5. **Penerimaan dan stok:** GR bertahap, pergerakan stok, kartu stok, penyesuaian, stok minimum.
6. **Laporan dan audit:** dashboard, laporan + ekspor CSV, log aktivitas.
7. **Data contoh dan tes:** seeder fiktif beberapa bulan dengan semua status, tes alur inti.
8. **Dokumentasi:** `README.md` dan `docs/PANDUAN-MODIFIKASI.md` (file kedua masuk `.gitignore`).

Seluruh data fiktif untuk studi kasus portofolio. Tidak ada paket Composer/npm baru di luar starter kit. Gunakan Eloquent/Migration agar agnostik database, aturan bisnis di `app/Services`, validasi di Form Request, status sebagai Enum, transaksi untuk proses multi-tabel, nomor dokumen dibuat di dalam transaksi dengan penguncian, uang dan jumlah sebagai bilangan bulat, aset lokal tanpa CDN, konfigurasi di `config/erp.php`.
