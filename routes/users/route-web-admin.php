<?php

use Illuminate\Support\Facades\Route;

// HAK AKSES WEB ADMINISTRATOR
Route::group(['prefix' => 'web-admin', 'middleware' => ['checkUser:web'], 'as' => 'web-admin.'],function(){
    // GLOBAL MENU AUTHENTIKASI

    // GLOBAL ROUTE
    require __DIR__.'/../private-core.php';


    // GRADEBOOK: controlled grade entry and workflow (loaded before legacy nilai routes)
    require __DIR__.'/../gradebook.php';

    // Kehadiran mahasiswa dapat dikelola Administrator tanpa berpindah guard Dosen.
    Route::get('/akademik/kehadiran', [App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'webAdminKehadiran'])->name('akademik.kehadiran');
    Route::get('/akademik/kehadiran/export-pdf', [App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'webAdminKehadiranExportPdf'])->name('akademik.kehadiran.export-pdf');
    Route::get('/akademik/kehadiran/{mahasiswaId}/pdf', [App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'webAdminKehadiranPdf'])->name('akademik.kehadiran.pdf');
    Route::get('/akademik/kehadiran/mata-kuliah/{mataKuliahId}/pdf', [App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'webAdminKehadiranMataKuliahPdf'])->name('akademik.kehadiran.mata-kuliah.pdf');
    Route::post('/akademik/kehadiran', [App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'webAdminSimpanKehadiran'])->name('akademik.kehadiran.store');

    // LAYANAN MAHASISWA => LEGALISIR
    Route::get('/layanan/legalisir', [App\Http\Controllers\Master\Layanan\LegalisirController::class, 'index'])->name('layanan.legalisir');
    Route::get('/layanan/legalisir/{id}', [App\Http\Controllers\Master\Layanan\LegalisirController::class, 'show'])->name('layanan.legalisir.detail');
    Route::patch('/layanan/legalisir/{id}', [App\Http\Controllers\Master\Layanan\LegalisirController::class, 'update'])->name('layanan.legalisir.update');

    // TERMINAL COMPOSER: hanya Administrator (raw_type=0) yang dapat menjalankan Composer.
    Route::get('/maintenance/composer', [App\Http\Controllers\Private\User\MaintenanceController::class, 'composerTerminal'])->name('maintenance.composer');
    Route::post('/maintenance/composer', [App\Http\Controllers\Private\User\MaintenanceController::class, 'runComposer'])->name('maintenance.composer.run');

    // MAINTENANCE: bersihkan cache Laravel tanpa Terminal. Hanya type=0 yang diizinkan di controller.
    Route::post('/maintenance/migrate', [App\Http\Controllers\Private\User\MaintenanceController::class, 'migrate'])->name('maintenance.migrate');
    Route::post('/maintenance/clear-cache', [App\Http\Controllers\Private\User\MaintenanceController::class, 'clearCache'])->name('maintenance.clear-cache');
    Route::post('/maintenance/storage-link', [App\Http\Controllers\Private\User\MaintenanceController::class, 'storageLink'])->name('maintenance.storage-link');
    Route::post('/maintenance/clear-routes', [App\Http\Controllers\Private\User\MaintenanceController::class, 'clearRoutes'])->name('maintenance.clear-routes');
    Route::post('/maintenance/clear-views', [App\Http\Controllers\Private\User\MaintenanceController::class, 'clearViews'])->name('maintenance.clear-views');

    // MASTER AUTHORITY
    require __DIR__.'/../master-core.php';
});
