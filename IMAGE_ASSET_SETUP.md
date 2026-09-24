# Perbaikan Asset Gambar STIT Darul Ilmi

Repository ini telah dibersihkan dari asset gambar demo/template dan referensi gambar eksternal pada halaman publik.

## Setelah deploy

Jalankan dari root project Laravel:

```bash
php artisan storage:link
php artisan optimize:clear
```

`storage:link` diperlukan agar seluruh gambar aplikasi di `storage/app/public/images` dapat diakses melalui `/storage/images/...`.

## Asset gambar

Seluruh gambar aplikasi dipusatkan di `storage/app/public/images/`, termasuk logo, foto profil, berita, pengumuman, galeri, dan placeholder. Aplikasi menggunakan URL `/storage/images/...` setelah `storage:link`.

## Catatan

Jangan mengisi foto dosen/mahasiswa/pimpinan dengan foto random. Gunakan foto resmi STIT Darul Ilmi atau biarkan placeholder sampai asset resmi tersedia.
