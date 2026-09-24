<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'dosen', 'middleware' => ['checkUser:Dosen Aktif', 'dosen.guard'], 'as' => 'dosen.'],function(){
    Route::get('/logout', [App\Http\Controllers\AuthController::class, 'handleLogout'])->name('handle-logout');
    Route::get('/home',[App\Http\Controllers\Private\Dosen\RootController::class, 'renderDashboard'])->name('dashboard-render');
    Route::get('/profile',[App\Http\Controllers\Private\Dosen\RootController::class, 'renderProfile'])->name('profile-render');
    Route::patch('/profile',[App\Http\Controllers\Private\Dosen\RootController::class, 'handleProfile'])->name('profile-handle');
    Route::get('/akademik/jadwal/export-pdf',[App\Http\Controllers\Private\Dosen\RootController::class, 'exportJadwalPdf'])->name('akademik.jadwal.export-pdf');

    // GRADEBOOK: controlled grade entry and workflow (loaded before legacy nilai routes)
    require __DIR__.'/../gradebook.php';

    require __DIR__.'/master-akademik.php';

    Route::get('/akademik/jadwal',[App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'jadwal'])->name('akademik.jadwal');
    Route::post('/akademik/jadwal',[App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'simpanJadwal'])->name('akademik.jadwal.store');
    Route::patch('/akademik/jadwal/{code}',[App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'updateJadwal'])->name('akademik.jadwal.update');

    Route::get('/akademik/nilai-operasional',[App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'nilai'])->name('akademik.nilai-operasional');
    Route::patch('/akademik/nilai-operasional/{code}',[App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'updateNilai'])->name('akademik.nilai-operasional.update');

    Route::get('/akademik/kehadiran',[App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'kehadiran'])->name('akademik.kehadiran');
    Route::get('/akademik/kehadiran/mata-kuliah/{mataKuliahId}/pdf',[App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'dosenKehadiranMataKuliahPdf'])->name('akademik.kehadiran.mata-kuliah.pdf');
    Route::get('/akademik/kehadiran/{mahasiswaId}/pdf',[App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'dosenKehadiranPdf'])->name('akademik.kehadiran.pdf');
    Route::post('/akademik/kehadiran',[App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'simpanKehadiran'])->name('akademik.kehadiran.store');

    Route::get('/akademik/krs-operasional',[App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'krs'])->name('akademik.krs-operasional');
    Route::get('/akademik/krs-operasional/{code}/preview',[App\Http\Controllers\Master\Akademik\KRSController::class, 'previewKRS'])->name('akademik.krs.preview');
    Route::post('/akademik/krs-operasional/{code}/approve',[App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'approveKrs'])->name('akademik.krs-operasional.approve');
    Route::post('/akademik/krs-operasional/{code}/reject',[App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'rejectKrs'])->name('akademik.krs-operasional.reject');
});
