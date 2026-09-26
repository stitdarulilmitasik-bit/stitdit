<?php

use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Route;

Route::get('/media/{path}', [App\Http\Controllers\StorageImageController::class, 'show'])->where('path', '.*')->name('media.image');
Route::get('/', [App\Http\Controllers\CmsHomeController::class, 'index'])->name('root.home-index');
// PUBLICATION ROUTES
Route::get('/pengumuman', [App\Http\Controllers\RootController::class, 'renderPengumuman'])->name('root.pengumuman-index');
Route::get('/pengumuman/{code}/view', [App\Http\Controllers\RootController::class, 'renderPengumumanView'])->name('root.pengumuman-view');
Route::get('/kalender-akademik', [App\Http\Controllers\AcademicCalendarPublicController::class, 'index'])->name('root.kalender-akademik-index');
Route::get('/kalender-akademik/{code}/view', [App\Http\Controllers\RootController::class, 'renderKalenderAkademikView'])->name('root.kalender-akademik-view');
Route::get('/berita', [App\Http\Controllers\RootController::class, 'renderBerita'])->name('root.berita-index');
Route::get('/berita/{slug}/view', [App\Http\Controllers\RootController::class, 'renderBeritaView'])->name('root.berita-view');
Route::get('/galeri', [App\Http\Controllers\RootController::class, 'renderGaleri'])->name('root.galeri-index');
Route::get('/galeri/{code}/view', [App\Http\Controllers\RootController::class, 'renderGaleriView'])->name('root.galeri-view');
Route::get('/galeri/{code}/cover-file', [App\Http\Controllers\Master\Publikasi\GaleriController::class, 'serveCover'])->name('root.galeri-cover-file');
Route::get('/galeri/foto/{code}/file', [App\Http\Controllers\Master\Publikasi\GaleriController::class, 'serveFoto'])->name('root.galeri-foto-file');
Route::get('/program-studi', [App\Http\Controllers\RootController::class, 'renderProgramStudi'])->name('root.prodi-index');
Route::get('/program-studi/{slug}/view', [App\Http\Controllers\RootController::class, 'renderProgramStudiView'])->name('root.prodi-view');

Route::get('/profil', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'profil')->name('root.profil');
Route::get('/visi-misi', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'visi-misi')->name('root.visi-misi');
Route::get('/struktur-organisasi', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'struktur-organisasi')->name('root.struktur');
Route::get('/fasilitas', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'fasilitas')->name('root.fasilitas');
Route::get('/kontak', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'kontak')->name('root.kontak');
Route::get('/jadwal-kuliah', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'jadwal-kuliah')->name('root.jadwal-kuliah');
Route::get('/silabus', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'silabus')->name('root.silabus');
Route::get('/e-learning', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'e-learning')->name('root.e-learning');
Route::get('/organisasi', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'organisasi')->name('root.organisasi');
Route::get('/beasiswa', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'beasiswa')->name('root.beasiswa');
Route::get('/prestasi', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'prestasi')->name('root.prestasi');
Route::get('/alumni', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'alumni')->name('root.alumni');

Route::get('/siakad', fn() => redirect()->route('auth.render-signin'))->name('root.siakad');
Route::get('/siakad/jadwal', fn() => redirect()->route('auth.render-signin'));
Route::get('/siakad/nilai', fn() => redirect()->route('auth.render-signin'));
Route::get('/siakad/pembayaran', fn() => redirect()->route('auth.render-signin'));
Route::get('/perpustakaan', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'perpustakaan')->name('root.perpustakaan');
Route::get('/laboratorium', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'laboratorium')->name('root.laboratorium');
Route::get('/kemahasiswaan', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'kemahasiswaan')->name('root.kemahasiswaan');
Route::get('/keuangan', [App\Http\Controllers\PublicInfoController::class, 'show'])->defaults('page', 'keuangan')->name('root.keuangan');

Route::get('/welcome', [App\Http\Controllers\RootController::class, 'renderWelcome'])->name('root.welcome');
Route::post('/api/setup', [App\Http\Controllers\SetupController::class, 'processSetup'])->name('setup.process');

Route::middleware(['guest', 'first.setup'])->group(function () {
    Route::get('/signin', [App\Http\Controllers\AuthController::class, 'renderSignin'])->name('auth.render-signin');
    Route::post('/signin', [App\Http\Controllers\AuthController::class, 'handleSignin'])->name('auth.handle-signin');
    Route::get('/forgot', [App\Http\Controllers\AuthController::class, 'renderForgot'])->name('auth.render-forgot');
    Route::post('/forgot', [App\Http\Controllers\AuthController::class, 'handleForgot'])->name('auth.handle-forgot');
});

// Logout must remain accessible to authenticated users.
Route::get('/logout', [App\Http\Controllers\AuthController::class, 'handleLogout'])->name('auth.handle-logout');

Route::get('/error/verify', [App\Http\Controllers\Root\ErrorController::class, 'ErrorVerify'])->name('error.verify');
Route::get('/error/access', [App\Http\Controllers\Root\ErrorController::class, 'ErrorAccess'])->name('error.access');
Route::get('/error/notfound', [App\Http\Controllers\Root\ErrorController::class, 'ErrorNotFound'])->name('error.notfound');

require __DIR__.'/users/route-web-admin.php';
require __DIR__.'/dosen/dosen-aktif.php';
require __DIR__.'/mahasiswa/mahasiswa-aktif.php';