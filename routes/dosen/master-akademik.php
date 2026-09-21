<?php

use Illuminate\Support\Facades\Route;

// Master Akademik Dosen: menggunakan controller Master yang sama dengan Admin.
// Seluruh route berada di dalam middleware Dosen Aktif dari dosen-aktif.php.

// Tahun Akademik
Route::get('/akademik/tahun-akademik',[App\Http\Controllers\Master\Akademik\TahunAkademikController::class, 'renderTaka'])->name('akademik.taka-render');
Route::post('/akademik/tahun-akademik',[App\Http\Controllers\Master\Akademik\TahunAkademikController::class, 'handleTaka'])->name('akademik.taka-handle');
Route::patch('/akademik/tahun-akademik/{code}',[App\Http\Controllers\Master\Akademik\TahunAkademikController::class, 'updateTaka'])->name('akademik.taka-update');
Route::delete('/akademik/tahun-akademik/{code}',[App\Http\Controllers\Master\Akademik\TahunAkademikController::class, 'deleteTaka'])->name('akademik.taka-delete');

// Fakultas
Route::get('/akademik/fakultas',[App\Http\Controllers\Master\Akademik\FakultasController::class, 'renderFakultas'])->name('akademik.fakultas-render');
Route::post('/akademik/fakultas',[App\Http\Controllers\Master\Akademik\FakultasController::class, 'handleFakultas'])->name('akademik.fakultas-handle');
Route::patch('/akademik/fakultas/{code}',[App\Http\Controllers\Master\Akademik\FakultasController::class, 'updateFakultas'])->name('akademik.fakultas-update');
Route::delete('/akademik/fakultas/{code}',[App\Http\Controllers\Master\Akademik\FakultasController::class, 'deleteFakultas'])->name('akademik.fakultas-delete');

// Program Studi
Route::get('/akademik/program-studi',[App\Http\Controllers\Master\Akademik\ProgramStudiController::class, 'renderProdi'])->name('akademik.prodi-render');
Route::post('/akademik/program-studi',[App\Http\Controllers\Master\Akademik\ProgramStudiController::class, 'handleProdi'])->name('akademik.prodi-handle');
Route::patch('/akademik/program-studi/{code}',[App\Http\Controllers\Master\Akademik\ProgramStudiController::class, 'updateProdi'])->name('akademik.prodi-update');
Route::delete('/akademik/program-studi/{code}',[App\Http\Controllers\Master\Akademik\ProgramStudiController::class, 'deleteProdi'])->name('akademik.prodi-delete');

// Kurikulum
Route::get('/akademik/kurikulum',[App\Http\Controllers\Master\Akademik\KurikulumController::class, 'renderKurikulum'])->name('akademik.kurikulum-render');
Route::post('/akademik/kurikulum',[App\Http\Controllers\Master\Akademik\KurikulumController::class, 'handleKurikulum'])->name('akademik.kurikulum-handle');
Route::patch('/akademik/kurikulum/{code}',[App\Http\Controllers\Master\Akademik\KurikulumController::class, 'updateKurikulum'])->name('akademik.kurikulum-update');
Route::delete('/akademik/kurikulum/{code}',[App\Http\Controllers\Master\Akademik\KurikulumController::class, 'deleteKurikulum'])->name('akademik.kurikulum-delete');

// Mata Kuliah
Route::get('/akademik/mata-kuliah',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'renderMataKuliah'])->name('akademik.mata-kuliah-render');
Route::get('/akademik/mata-kuliah/export-excel',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'exportMataKuliahExcel'])->name('akademik.mata-kuliah-export-excel');
Route::get('/akademik/mata-kuliah/export-full-excel',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'exportMataKuliahFullExcel'])->name('akademik.mata-kuliah-export-full-excel');
Route::get('/akademik/mata-kuliah/import-template',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'downloadMataKuliahImportTemplate'])->name('akademik.mata-kuliah-import-template');
Route::post('/akademik/mata-kuliah/import-excel',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'importMataKuliahExcel'])->name('akademik.mata-kuliah-import-excel');
Route::post('/akademik/mata-kuliah',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'handleMataKuliah'])->name('akademik.mata-kuliah-handle');
Route::patch('/akademik/mata-kuliah/{code}',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'updateMataKuliah'])->name('akademik.mata-kuliah-update');
Route::delete('/akademik/mata-kuliah/{code}',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'deleteMataKuliah'])->name('akademik.mata-kuliah-delete');

// Jenis Kelas
Route::get('/akademik/jenis-kelas',[App\Http\Controllers\Master\Akademik\JenisKelasController::class, 'renderJenisKelas'])->name('akademik.jenis-kelas-render');
Route::post('/akademik/jenis-kelas',[App\Http\Controllers\Master\Akademik\JenisKelasController::class, 'handleJenisKelas'])->name('akademik.jenis-kelas-handle');
Route::patch('/akademik/jenis-kelas/{code}',[App\Http\Controllers\Master\Akademik\JenisKelasController::class, 'updateJenisKelas'])->name('akademik.jenis-kelas-update');
Route::delete('/akademik/jenis-kelas/{code}',[App\Http\Controllers\Master\Akademik\JenisKelasController::class, 'deleteJenisKelas'])->name('akademik.jenis-kelas-delete');

// Kelas
Route::get('/akademik/kelas',[App\Http\Controllers\Master\Akademik\KelasController::class, 'renderKelas'])->name('akademik.kelas-render');
Route::post('/akademik/kelas',[App\Http\Controllers\Master\Akademik\KelasController::class, 'handleKelas'])->name('akademik.kelas-handle');
Route::patch('/akademik/kelas/{code}',[App\Http\Controllers\Master\Akademik\KelasController::class, 'updateKelas'])->name('akademik.kelas-update');
Route::delete('/akademik/kelas/{code}',[App\Http\Controllers\Master\Akademik\KelasController::class, 'deleteKelas'])->name('akademik.kelas-delete');

// Waktu Kuliah
Route::get('/akademik/waktu-kuliah',[App\Http\Controllers\Master\Akademik\WaktuKuliahController::class, 'renderWaktuKuliah'])->name('akademik.waktu-kuliah-render');
Route::post('/akademik/waktu-kuliah',[App\Http\Controllers\Master\Akademik\WaktuKuliahController::class, 'handleWaktuKuliah'])->name('akademik.waktu-kuliah-handle');
Route::patch('/akademik/waktu-kuliah/{code}',[App\Http\Controllers\Master\Akademik\WaktuKuliahController::class, 'updateWaktuKuliah'])->name('akademik.waktu-kuliah-update');
Route::delete('/akademik/waktu-kuliah/{code}',[App\Http\Controllers\Master\Akademik\WaktuKuliahController::class, 'deleteWaktuKuliah'])->name('akademik.waktu-kuliah-delete');

// Jadwal Kuliah
Route::get('/akademik/jadwal-kuliah',[App\Http\Controllers\Master\Akademik\JadwalKuliahController::class, 'renderJadwalKuliah'])->name('akademik.jadwal-kuliah-render');
Route::post('/akademik/jadwal-kuliah',[App\Http\Controllers\Master\Akademik\JadwalKuliahController::class, 'handleJadwalKuliah'])->name('akademik.jadwal-kuliah-handle');
Route::patch('/akademik/jadwal-kuliah/{code}',[App\Http\Controllers\Master\Akademik\JadwalKuliahController::class, 'updateJadwalKuliah'])->name('akademik.jadwal-kuliah-update');
Route::delete('/akademik/jadwal-kuliah/{code}',[App\Http\Controllers\Master\Akademik\JadwalKuliahController::class, 'deleteJadwalKuliah'])->name('akademik.jadwal-kuliah-delete');
Route::get('/akademik/get-waktu-kuliah/{jenis_kelas_id}',[App\Http\Controllers\Master\Akademik\JadwalKuliahController::class, 'getWaktuKuliahByJenisKelas'])->name('akademik.get-waktu-kuliah');

// KRS
Route::get('/akademik/krs',[App\Http\Controllers\Master\Akademik\KRSController::class, 'renderKRS'])->name('akademik.krs-render');
Route::post('/akademik/krs',[App\Http\Controllers\Master\Akademik\KRSController::class, 'handleKRS'])->name('akademik.krs-handle');
Route::get('/akademik/krs/{code}/detail',[App\Http\Controllers\Master\Akademik\KRSController::class, 'detailKRS'])->name('akademik.krs-detail');
Route::get('/akademik/krs/{code}/print',[App\Http\Controllers\Master\Akademik\KRSController::class, 'printKRS'])->name('akademik.krs-print');
Route::get('/akademik/krs/{code}',[App\Http\Controllers\Master\Akademik\KRSController::class, 'viewKRS'])->name('akademik.krs-view');
Route::patch('/akademik/krs/{code}',[App\Http\Controllers\Master\Akademik\KRSController::class, 'updateKRS'])->name('akademik.krs-update');
Route::delete('/akademik/krs/{code}',[App\Http\Controllers\Master\Akademik\KRSController::class, 'deleteKRS'])->name('akademik.krs-delete');
Route::post('/akademik/krs/{code}/add-matakuliah',[App\Http\Controllers\Master\Akademik\KRSController::class, 'addMatakuliah'])->name('akademik.krs-add-matakuliah');
Route::delete('/akademik/krs/{code}/remove-matakuliah/{detailId}',[App\Http\Controllers\Master\Akademik\KRSController::class, 'removeMatakuliah'])->name('akademik.krs-remove-matakuliah');
Route::post('/akademik/krs/{code}/approve',[App\Http\Controllers\Master\Akademik\KRSController::class, 'approveKRS'])->name('akademik.krs-approve');
Route::post('/akademik/krs/{code}/reject',[App\Http\Controllers\Master\Akademik\KRSController::class, 'rejectKRS'])->name('akademik.krs-reject');
Route::post('/akademik/krs/{code}/publish',[App\Http\Controllers\Master\Akademik\KRSController::class, 'publishKRS'])->name('akademik.krs-publish');
Route::post('/akademik/krs/{code}/lock',[App\Http\Controllers\Master\Akademik\KRSController::class, 'lockKRS'])->name('akademik.krs-lock');
Route::post('/akademik/krs/bulk-approve',[App\Http\Controllers\Master\Akademik\KRSController::class, 'bulkApprove'])->name('akademik.krs-bulk-approve');
Route::post('/akademik/krs/bulk-publish',[App\Http\Controllers\Master\Akademik\KRSController::class, 'bulkPublish'])->name('akademik.krs-bulk-publish');

// Nilai
Route::get('/akademik/nilai',[App\Http\Controllers\Master\Akademik\NilaiController::class, 'renderNilai'])->name('akademik.nilai-render');
Route::get('/akademik/nilai/import',[App\Http\Controllers\Master\Akademik\NilaiController::class, 'renderImportNilai'])->name('akademik.nilai-import');
Route::get('/akademik/nilai/export',[App\Http\Controllers\Master\Akademik\NilaiController::class, 'exportNilai'])->name('akademik.nilai-export');
Route::post('/akademik/nilai',[App\Http\Controllers\Master\Akademik\NilaiController::class, 'handleNilai'])->name('akademik.nilai-handle');
Route::get('/akademik/nilai/{code}',[App\Http\Controllers\Master\Akademik\NilaiController::class, 'viewNilai'])->name('akademik.nilai-view');
Route::patch('/akademik/nilai/{code}',[App\Http\Controllers\Master\Akademik\NilaiController::class, 'updateNilai'])->name('akademik.nilai-update');
Route::delete('/akademik/nilai/{code}',[App\Http\Controllers\Master\Akademik\NilaiController::class, 'deleteNilai'])->name('akademik.nilai-delete');
Route::post('/akademik/nilai/import',[App\Http\Controllers\Master\Akademik\NilaiController::class, 'importNilai'])->name('akademik.nilai-import-process');
Route::post('/akademik/nilai/bulk-update',[App\Http\Controllers\Master\Akademik\NilaiController::class, 'bulkUpdate'])->name('akademik.nilai-bulk-update');
Route::post('/akademik/nilai/{code}/approve',[App\Http\Controllers\Master\Akademik\NilaiController::class, 'approveNilai'])->name('akademik.nilai-approve');
Route::post('/akademik/nilai/{code}/publish',[App\Http\Controllers\Master\Akademik\NilaiController::class, 'publishNilai'])->name('akademik.nilai-publish');
Route::post('/akademik/nilai/{code}/lock',[App\Http\Controllers\Master\Akademik\NilaiController::class, 'lockNilai'])->name('akademik.nilai-lock');

// KHS
Route::get('/akademik/khs',[App\Http\Controllers\Master\Akademik\KHSController::class, 'renderKHS'])->name('akademik.khs-render');
Route::post('/akademik/khs',[App\Http\Controllers\Master\Akademik\KHSController::class, 'handleKHS'])->name('akademik.khs-handle');
Route::get('/akademik/khs/{code}/detail',[App\Http\Controllers\Master\Akademik\KHSController::class, 'detailKHS'])->name('akademik.khs-detail');
Route::get('/akademik/khs/{code}/print',[App\Http\Controllers\Master\Akademik\KHSController::class, 'printKHS'])->name('akademik.khs-print');
Route::get('/akademik/khs/{code}',[App\Http\Controllers\Master\Akademik\KHSController::class, 'viewKHS'])->name('akademik.khs-view');
Route::patch('/akademik/khs/{code}',[App\Http\Controllers\Master\Akademik\KHSController::class, 'updateKHS'])->name('akademik.khs-update');
Route::delete('/akademik/khs/{code}',[App\Http\Controllers\Master\Akademik\KHSController::class, 'deleteKHS'])->name('akademik.khs-delete');
Route::post('/akademik/khs/generate',[App\Http\Controllers\Master\Akademik\KHSController::class, 'generateKHS'])->name('akademik.khs-generate');
Route::post('/akademik/khs/{code}/publish',[App\Http\Controllers\Master\Akademik\KHSController::class, 'publishKHS'])->name('akademik.khs-publish');
Route::post('/akademik/khs/{code}/lock',[App\Http\Controllers\Master\Akademik\KHSController::class, 'lockKHS'])->name('akademik.khs-lock');
Route::get('/akademik/khs/transkrip/{mahasiswa_code}',[App\Http\Controllers\Master\Akademik\KHSController::class, 'transkrip'])->name('akademik.khs-transkrip');
Route::post('/akademik/khs/bulk-generate',[App\Http\Controllers\Master\Akademik\KHSController::class, 'bulkGenerate'])->name('akademik.khs-bulk-generate');
Route::post('/akademik/khs/bulk-publish',[App\Http\Controllers\Master\Akademik\KHSController::class, 'bulkPublish'])->name('akademik.khs-bulk-publish');
