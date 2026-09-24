# Audit Asset & Cleanup — STIT Darul Ilmi

## Perubahan yang diterapkan

- Menambahkan resolver gambar `app/Support/ImageHelper.php` untuk memvalidasi file pada `Storage::disk('public')`/`public/` dan otomatis memakai placeholder STIT ketika file hilang.
- Menormalisasi pemanggilan gambar berita, pengumuman, galeri, foto galeri, profil, fasilitas/infrastruktur, dan absensi.
- Memperbaiki accessor foto user/dosen/mahasiswa agar tidak menghasilkan URL `/storage/...` ke file yang tidak ada.
- Memperbaiki accessor logo agar fallback ke branding STIT ketika file upload logo tidak tersedia.
- Memperbaiki controller pengaturan logo agar menggunakan nilai database mentah saat menghapus file lama.
- Menghapus asset demo/template yang tidak direferensikan aplikasi: `public/auth/`, halaman demo HTML Tabler, `public/dashboard/marketing/`, `public/dashboard/static/`, `public/dashboard/docs/`, dan `public/dashboard/preview/`.
- Menghapus banner RT/RW yang tidak relevan dari `storage/app/public/images/galeri/`.
- Memperbaiki branding publik dari `Universitas Masa Depan` menjadi `STIT Darul Ilmi Tasikmalaya`.
- Menghilangkan teks `universitas` yang tidak sesuai dari halaman publik.
- Menambahkan fallback dan `alt` text yang lebih jelas pada gambar.
- Menghapus seeder demo/fiktif yang sebelumnya memasukkan user, dosen, mahasiswa, kalender, program studi, PMB, fasilitas, berita, dan pengumuman contoh.
- `DatabaseSeeder` sekarang production-safe dan tidak membuat akun/password demo. Administrator dibuat melalui Setup Wizard.
- Memperbaiki `setup.bat` dan `setup.sh` agar tidak gagal ketika `.env` belum ada; `.env` hanya dibuat jika belum tersedia dan tidak ditimpa.
- Menghindari `migrate:refresh` pada script setup sehingga instalasi tidak menghapus database secara tidak sengaja.

## Asset resmi yang dipertahankan

- `storage/app/public/images/logo/logo-hori.png`
- `storage/app/public/images/logo/logo-vert.png`
- `storage/app/public/images/default/logo-horizontal.png`
- `storage/app/public/images/default/logo-vertical.png`
- `storage/app/public/images/placeholders/news-placeholder.svg`
- `storage/app/public/images/placeholders/profile-placeholder.svg`
- asset foto upload pada subdirektori `storage/app/public/images/`

## Storage

Setelah deployment, jalankan:

```bash
php artisan storage:link
php artisan optimize:clear
```

## Validasi yang dilakukan

- PHP syntax check seluruh `app/`, `bootstrap/`, `config/`, `database/`, `routes/`, dan `tests/`: **lulus**.
- Blade/PHP syntax check seluruh `resources/views/**/*.blade.php`: **lulus**.
- Referensi gambar hard-coded pada halaman publik diaudit dan diarahkan melalui helper/fallback.
- Demo HTML/assets yang tidak dipakai oleh aplikasi dihapus.

## Catatan testing

Environment kerja tidak memiliki Composer/vendor, sehingga `php artisan test` dan build Laravel penuh tidak dapat dijalankan di sesi ini. `npm ci` juga tidak selesai karena timeout jaringan; `node_modules` hasil percobaan sudah dihapus agar tidak ikut masuk repository.

Pada mesin deployment, jalankan:

```bash
composer install
npm ci
npm run build
php artisan optimize:clear
php artisan storage:link
php artisan test
```
