<?php

use Illuminate\Support\Facades\Route;

// HAK AKSES MAHASISWA
Route::group(['prefix' => 'mahasiswa', 'middleware' => ['checkUser:Mahasiswa Aktif'], 'as' => 'mahasiswa.'], function() {
    Route::get('/logout', [App\Http\Controllers\AuthController::class, 'handleLogout'])->name('handle-logout');
    Route::get('/home', [App\Http\Controllers\Private\Mahasiswa\DashboardController::class, 'index'])->name('dashboard-render');
    Route::get('/profile', [App\Http\Controllers\Private\Mahasiswa\RootController::class, 'renderProfile'])->name('profile-render');
    Route::patch('/profile', [App\Http\Controllers\Private\Mahasiswa\RootController::class, 'handleProfile'])->name('profile-handle');
    Route::prefix('akademik')->name('akademik.')->group(function () {
        Route::get('/krs', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'krsRender'])->name('krs-render');
        Route::get('/krs/cetak', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'cetakKrs'])->name('krs-cetak');
        Route::post('/krs', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'storeKrs'])->name('krs.store');
        Route::post('/krs/submit', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'submitKrs'])->name('krs.submit');
        Route::delete('/krs/{detailId}', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'destroyKrs'])->name('krs.destroy');
        // Jadwal kuliah sekarang ditampilkan langsung di dashboard mahasiswa.\n        Route::get('/jadwal', fn () => redirect()->route('mahasiswa.dashboard-render'))->name('jadwal');
        Route::get('/jadwal-kuliah', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'jadwalKuliah'])->name('jadwal-kuliah');
        Route::get('/jadwal/{semester}', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'jadwalBySemester'])->name('jadwal.semester');
        Route::get('/jadwal-semester/{semester}', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'jadwalBySemester'])->name('jadwal-by-semester');
        Route::get('/presensi', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'presensi'])->name('presensi');
        Route::get('/presensi/{kode_mk}', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'detailPresensi'])->name('presensi.detail');
        Route::get('/presensi-detail/{kode_mk}', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'detailPresensi'])->name('detail-presensi');
        Route::get('/khs', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'khs'])->name('khs');
        Route::get('/khs/cetak', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'cetakKhs'])->name('khs.cetak');
        Route::get('/nilai', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'nilai'])->name('nilai');
        Route::get('/nilai/{semester}', [App\Http\Controllers\Private\Mahasiswa\AkademikController::class, 'nilaiBySemester'])->name('nilai.semester');
    });
    Route::prefix('keuangan')->name('keuangan.')->group(function () {
        Route::get('/tagihan', [App\Http\Controllers\Private\Mahasiswa\KeuanganController::class, 'tagihan'])->name('tagihan');
        Route::get('/tagihan/{id}/detail', [App\Http\Controllers\Private\Mahasiswa\KeuanganController::class, 'detailTagihan'])->name('tagihan.detail');
        Route::get('/riwayat', [App\Http\Controllers\Private\Mahasiswa\KeuanganController::class, 'riwayatPembayaran'])->name('riwayat');
        Route::get('/virtual-account', [App\Http\Controllers\Private\Mahasiswa\KeuanganController::class, 'virtualAccount'])->name('virtual-account');
        Route::get('/bukti-pembayaran', [App\Http\Controllers\Private\Mahasiswa\KeuanganController::class, 'buktiPembayaran'])->name('bukti-pembayaran');
        Route::post('/upload-bukti', [App\Http\Controllers\Private\Mahasiswa\KeuanganController::class, 'uploadBuktiPembayaran'])->name('upload-bukti');
    });
    Route::prefix('layanan')->name('layanan.')->group(function () {
        Route::get('/transkrip', [App\Http\Controllers\Private\Mahasiswa\LayananController::class, 'transkripNilai'])->name('transkrip');
        Route::get('/transkrip/cetak', [App\Http\Controllers\Private\Mahasiswa\LayananController::class, 'cetakTranskrip'])->name('transkrip.cetak');
        Route::get('/surat-keterangan', [App\Http\Controllers\Private\Mahasiswa\LayananController::class, 'page'])->defaults('title', 'Surat Keterangan')->name('surat-keterangan');
        Route::get('/surat-aktif-kuliah', [App\Http\Controllers\Private\Mahasiswa\LayananController::class, 'suratAktifKuliah'])->name('surat-aktif-kuliah');
        Route::post('/surat-aktif-kuliah', [App\Http\Controllers\Private\Mahasiswa\LayananController::class, 'ajukanSuratAktifKuliah'])->name('ajukan-surat-aktif');
        Route::get('/legalisir', [App\Http\Controllers\Private\Mahasiswa\LayananController::class, 'legalisirDokumen'])->name('legalisir');
        Route::post('/ajukan-legalisir', [App\Http\Controllers\Private\Mahasiswa\LayananController::class, 'ajukanLegalisir'])->name('ajukan-legalisir');
        Route::get('/cuti', [App\Http\Controllers\Private\Mahasiswa\LayananController::class, 'cutiAkademik'])->name('cuti');
        Route::post('/ajukan-cuti', [App\Http\Controllers\Private\Mahasiswa\LayananController::class, 'ajukanCuti'])->name('ajukan-cuti');
    });
    Route::prefix('informasi')->name('informasi.')->group(function () {
        Route::get('/pengumuman', [App\Http\Controllers\Private\Mahasiswa\InformasiController::class, 'pengumuman'])->name('pengumuman');
        Route::get('/pengumuman/{id}', [App\Http\Controllers\Private\Mahasiswa\InformasiController::class, 'detailPengumuman'])->name('pengumuman.detail');
        Route::get('/kalender-akademik', [App\Http\Controllers\Private\Mahasiswa\InformasiController::class, 'kalenderAkademik'])->name('kalender-akademik');
        Route::get('/beasiswa', [App\Http\Controllers\Private\Mahasiswa\InformasiController::class, 'beasiswa'])->name('beasiswa');
        Route::get('/beasiswa/{id}/detail', [App\Http\Controllers\Private\Mahasiswa\InformasiController::class, 'detailBeasiswa'])->name('beasiswa.detail');
        Route::get('/kontak-kampus', [App\Http\Controllers\Private\Mahasiswa\InformasiController::class, 'kontakKampus'])->name('kontak-kampus');
    });
    Route::get('/bantuan', [App\Http\Controllers\Private\Mahasiswa\BantuanController::class, 'index'])->name('bantuan');
    Route::post('/kirim-pesan-bantuan', [App\Http\Controllers\Private\Mahasiswa\BantuanController::class, 'kirimPesan'])->name('bantuan.kirim-pesan');
});
