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
    Route::post('/akademik/kehadiran', [App\Http\Controllers\Private\Dosen\AkademikOperasionalController::class, 'webAdminSimpanKehadiran'])->name('akademik.kehadiran.store');

    // MASTER AUTHORITY
    require __DIR__.'/../master-core.php';
});
