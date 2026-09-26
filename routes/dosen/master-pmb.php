<?php

use Illuminate\Support\Facades\Route;

// Master PMB Dosen.
// Operasional PMB dibuat dapat dilihat oleh Dosen, tanpa membuka aksi perubahan data master.
Route::get('/pmb/periode',[App\Http\Controllers\Master\PMB\PeriodePendaftaranController::class, 'renderPeriode'])->name('pmb.periode-render');
Route::get('/pmb/jalur',[App\Http\Controllers\Master\PMB\JalurPendaftaranController::class, 'renderJalur'])->name('pmb.jalur-render');
Route::get('/pmb/biaya',[App\Http\Controllers\Master\PMB\BiayaPendaftaranController::class, 'renderBiaya'])->name('pmb.biaya-render');
Route::get('/pmb/syarat',[App\Http\Controllers\Master\PMB\SyaratPendaftaranController::class, 'renderSyarat'])->name('pmb.syarat-render');
Route::get('/pmb/gelombang',[App\Http\Controllers\Master\PMB\GelombangPendaftaranController::class, 'renderGelombang'])->name('pmb.gelombang-render');
Route::get('/pmb/jadwal',[App\Http\Controllers\Master\PMB\JadwalPMBController::class, 'renderJadwal'])->name('pmb.jadwal-render');
Route::get('/pmb/pendaftar',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'renderPendaftar'])->name('pmb.pendaftar-render');
Route::get('/pmb/pendaftar/{code}',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'renderDetail'])->name('pmb.pendaftar-detail');
Route::get('/pmb/pendaftar/export/excel',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'exportPendaftarExcel'])->name('pmb.pendaftar-export-excel');
Route::get('/pmb/pendaftar/export/pdf',[App\Http\Controllers\Master\PMB\PendaftarController::class, 'exportPendaftarPDF'])->name('pmb.pendaftar-export-pdf');
