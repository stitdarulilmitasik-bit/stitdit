<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MASTER PMB DOSEN
|--------------------------------------------------------------------------
| Menggunakan controller dan view Master PMB yang sama persis dengan
| Web Administrator. Seluruh CRUD, upload/validasi dokumen, batch action,
| dan export tersedia untuk Dosen.
|
| Prefix URL tetap /dosen/pmb/* dan nama route menjadi dosen.pmb.*.
| View Master PMB sudah menggunakan $spref dari user sehingga otomatis
| mengarah ke route sesuai panel yang sedang digunakan.
|--------------------------------------------------------------------------
*/

// MASTER PMB => PERIODE PENDAFTARAN
Route::get('/pmb/periode',[App\Http\Controllers\Master\PMB\PeriodePendaftaranController::class, 'renderPeriode'])->name('pmb.periode-render');
Route::post('/pmb/periode',[App\Http\Controllers\Master\PMB\PeriodePendaftaranController::class, 'handlePeriode'])->name('pmb.periode-handle');
Route::patch('/pmb/periode/{code}',[App\Http\Controllers\Master\PMB\PeriodePendaftaranController::class, 'updatePeriode'])->name('pmb.periode-update');
Route::delete('/pmb/periode/{code}',[App\Http\Controllers\Master\PMB\PeriodePendaftaranController::class, 'deletePeriode'])->name('pmb.periode-delete');

// MASTER PMB => JALUR PENDAFTARAN
Route::get('/pmb/jalur',[App\Http\Controllers\Master\PMB\JalurPendaftaranController::class, 'renderJalur'])->name('pmb.jalur-render');
Route::post('/pmb/jalur',[App\Http\Controllers\Master\PMB\JalurPendaftaranController::class, 'handleJalur'])->name('pmb.jalur-handle');
Route::patch('/pmb/jalur/{code}',[App\Http\Controllers\Master\PMB\JalurPendaftaranController::class, 'updateJalur'])->name('pmb.jalur-update');
Route::delete('/pmb/jalur/{code}',[App\Http\Controllers\Master\PMB\JalurPendaftaranController::class, 'deleteJalur'])->name('pmb.jalur-delete');

// MASTER PMB => BIAYA PENDAFTARAN
Route::get('/pmb/biaya',[App\Http\Controllers\Master\PMB\BiayaPendaftaranController::class, 'renderBiaya'])->name('pmb.biaya-render');
Route::post('/pmb/biaya',[App\Http\Controllers\Master\PMB\BiayaPendaftaranController::class, 'handleBiaya'])->name('pmb.biaya-handle');
Route::patch('/pmb/biaya/{code}',[App\Http\Controllers\Master\PMB\BiayaPendaftaranController::class, 'updateBiaya'])->name('pmb.biaya-update');
Route::delete('/pmb/biaya/{code}',[App\Http\Controllers\Master\PMB\BiayaPendaftaranController::class, 'deleteBiaya'])->name('pmb.biaya-delete');

// MASTER PMB => SYARAT PENDAFTARAN
Route::get('/pmb/syarat',[App\Http\Controllers\Master\PMB\SyaratPendaftaranController::class, 'renderSyarat'])->name('pmb.syarat-render');
Route::post('/pmb/syarat',[App\Http\Controllers\Master\PMB\SyaratPendaftaranController::class, 'handleSyarat'])->name('pmb.syarat-handle');
Route::patch('/pmb/syarat/{code}',[App\Http\Controllers\Master\PMB\SyaratPendaftaranController::class, 'updateSyarat'])->name('pmb.syarat-update');
Route::delete('/pmb/syarat/{code}',[App\Http\Controllers\Master\PMB\SyaratPendaftaranController::class, 'deleteSyarat'])->name('pmb.syarat-delete');

// MASTER PMB => GELOMBANG PENDAFTARAN
Route::get('/pmb/gelombang',[App\Http\Controllers\Master\PMB\GelombangPendaftaranController::class, 'renderGelombang'])->name('pmb.gelombang-render');
Route::post('/pmb/gelombang',[App\Http\Controllers\Master\PMB\GelombangPendaftaranController::class, 'handleGelombang'])->name('pmb.gelombang-handle');
Route::patch('/pmb/gelombang/{code}',[App\Http\Controllers\Master\PMB\GelombangPendaftaranController::class, 'updateGelombang'])->name('pmb.gelombang-update');
Route::delete('/pmb/gelombang/{code}',[App\Http\Controllers\Master\PMB\GelombangPendaftaranController::class, 'deleteGelombang'])->name('pmb.gelombang-delete');

// MASTER PMB => JADWAL PMB
Route::get('/pmb/jadwal',[App\Http\Controllers\Master\PMB\JadwalPMBController::class, 'renderJadwal'])->name('pmb.jadwal-render');
Route::post('/pmb/jadwal',[App\Http\Controllers\Master\PMB\JadwalPMBController::class, 'handleJadwal'])->name('pmb.jadwal-handle');
Route::patch('/pmb/jadwal/{code}',[App\Http\Controllers\Master\PMB\JadwalPMBController::class, 'updateJadwal'])->name('pmb.jadwal-update');
Route::delete('/pmb/jadwal/{code}',[App\Http\Controllers\Master\PMB\JadwalPMBController::class, 'deleteJadwal'])->name('pmb.jadwal-delete');

// MASTER PMB => PENDAFTAR
Route::get('/pmb/pendaftar',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'renderPendaftar'])->name('pmb.pendaftar-render');
Route::post('/pmb/pendaftar',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'handlePendaftar'])->name('pmb.pendaftar-handle');
Route::patch('/pmb/pendaftar/{code}',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'updatePendaftar'])->name('pmb.pendaftar-update');
Route::delete('/pmb/pendaftar/{code}',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'deletePendaftar'])->name('pmb.pendaftar-delete');
Route::get('/pmb/pendaftar/{code}',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'renderDetail'])->name('pmb.pendaftar-detail');
Route::post('/pmb/pendaftar/{code}/dokumen',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'handleDokumen'])->name('pmb.pendaftar-dokumen-handle');
Route::patch('/pmb/pendaftar/{code}/validasi',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'validasiDokumen'])->name('pmb.pendaftar-validasi');
Route::get('/pmb/pendaftar/export/excel',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'exportPendaftarExcel'])->name('pmb.pendaftar-export-excel');
Route::get('/pmb/pendaftar/export/pdf',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'exportPendaftarPDF'])->name('pmb.pendaftar-export-pdf');
Route::post('/pmb/pendaftar/batch/validasi',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'batchValidasiDokumen'])->name('pmb.pendaftar-batch-validasi');
Route::post('/pmb/pendaftar/batch/status',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'batchUpdateStatus'])->name('pmb.pendaftar-batch-status');
