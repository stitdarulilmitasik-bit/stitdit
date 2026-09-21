<?php

use Illuminate\Support\Facades\Route;

    // MASTER PENGATURAN => JABATAN
    Route::get('/pengaturan/jabatan',[App\Http\Controllers\Master\Pengaturan\JabatanController::class, 'renderJabatan'])->name('pengaturan.jabatan-render');
    Route::post('/pengaturan/jabatan',[App\Http\Controllers\Master\Pengaturan\JabatanController::class, 'handleJabatan'])->name('pengaturan.jabatan-handle');
    Route::patch('/pengaturan/jabatan/{code}',[App\Http\Controllers\Master\Pengaturan\JabatanController::class, 'updateJabatan'])->name('pengaturan.jabatan-update');
    Route::delete('/pengaturan/jabatan/{code}',[App\Http\Controllers\Master\Pengaturan\JabatanController::class, 'deleteJabatan'])->name('pengaturan.jabatan-delete');

    // MASTER PENGATURAN => FRONT PAGE CMS
    Route::get('/pengaturan/front-page', [App\Http\Controllers\Master\Pengaturan\HomepageController::class, 'index'])->name('pengaturan.front-page-render');
    Route::post('/pengaturan/front-page', [App\Http\Controllers\Master\Pengaturan\HomepageController::class, 'store'])->name('pengaturan.front-page-store');
    Route::patch('/pengaturan/front-page/{section}', [App\Http\Controllers\Master\Pengaturan\HomepageController::class, 'update'])->name('pengaturan.front-page-update');
    Route::delete('/pengaturan/front-page/{section}', [App\Http\Controllers\Master\Pengaturan\HomepageController::class, 'destroy'])->name('pengaturan.front-page-delete');

    // MASTER AKADEMIK => TAHUN AKADEMIK
    Route::get('/akademik/tahun-akademik',[App\Http\Controllers\Master\Akademik\TahunAkademikController::class, 'renderTaka'])->name('akademik.taka-render');
    Route::post('/akademik/tahun-akademik',[App\Http\Controllers\Master\Akademik\TahunAkademikController::class, 'handleTaka'])->name('akademik.taka-handle');
    Route::patch('/akademik/tahun-akademik/{code}',[App\Http\Controllers\Master\Akademik\TahunAkademikController::class, 'updateTaka'])->name('akademik.taka-update');
    Route::delete('/akademik/tahun-akademik/{code}',[App\Http\Controllers\Master\Akademik\TahunAkademikController::class, 'deleteTaka'])->name('akademik.taka-delete');

    // MASTER AKADEMIK => PROGRAM STUDI
    Route::get('/akademik/program-studi',[App\Http\Controllers\Master\Akademik\ProgramStudiController::class, 'renderProdi'])->name('akademik.prodi-render');
    Route::post('/akademik/program-studi',[App\Http\Controllers\Master\Akademik\ProgramStudiController::class, 'handleProdi'])->name('akademik.prodi-handle');
    Route::patch('/akademik/program-studi/{code}',[App\Http\Controllers\Master\Akademik\ProgramStudiController::class, 'updateProdi'])->name('akademik.prodi-update');
    Route::delete('/akademik/program-studi/{code}',[App\Http\Controllers\Master\Akademik\ProgramStudiController::class, 'deleteProdi'])->name('akademik.prodi-delete');

    // MASTER AKADEMIK => FAKULTAS
    Route::get('/akademik/fakultas',[App\Http\Controllers\Master\Akademik\FakultasController::class, 'renderFakultas'])->name('akademik.fakultas-render');
    Route::post('/akademik/fakultas',[App\Http\Controllers\Master\Akademik\FakultasController::class, 'handleFakultas'])->name('akademik.fakultas-handle');
    Route::patch('/akademik/fakultas/{code}',[App\Http\Controllers\Master\Akademik\FakultasController::class, 'updateFakultas'])->name('akademik.fakultas-update');
    Route::delete('/akademik/fakultas/{code}',[App\Http\Controllers\Master\Akademik\FakultasController::class, 'deleteFakultas'])->name('akademik.fakultas-delete');

    // MASTER AKADEMIK => KURIKULUM
    Route::get('/akademik/kurikulum',[App\Http\Controllers\Master\Akademik\KurikulumController::class, 'renderKurikulum'])->name('akademik.kurikulum-render');
    Route::post('/akademik/kurikulum',[App\Http\Controllers\Master\Akademik\KurikulumController::class, 'handleKurikulum'])->name('akademik.kurikulum-handle');
    Route::patch('/akademik/kurikulum/{code}',[App\Http\Controllers\Master\Akademik\KurikulumController::class, 'updateKurikulum'])->name('akademik.kurikulum-update');
    Route::delete('/akademik/kurikulum/{code}',[App\Http\Controllers\Master\Akademik\KurikulumController::class, 'deleteKurikulum'])->name('akademik.kurikulum-delete');

    // MASTER AKADEMIK => MATAKULIAH
    Route::get('/akademik/mata-kuliah',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'renderMataKuliah'])->name('akademik.mata-kuliah-render');
    Route::get('/akademik/mata-kuliah/export/excel',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'exportMataKuliahExcel'])->name('akademik.mata-kuliah-export-excel');
    Route::get('/akademik/mata-kuliah/export/full-excel',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'exportMataKuliahFullExcel'])->name('akademik.mata-kuliah-export-full-excel');
    Route::get('/akademik/mata-kuliah/import/template',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'downloadMataKuliahImportTemplate'])->name('akademik.mata-kuliah-import-template');
    Route::post('/akademik/mata-kuliah/import/excel',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'importMataKuliahExcel'])->name('akademik.mata-kuliah-import-excel');
    Route::post('/akademik/mata-kuliah',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'handleMataKuliah'])->name('akademik.mata-kuliah-handle');
    Route::patch('/akademik/mata-kuliah/{code}',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'updateMataKuliah'])->name('akademik.mata-kuliah-update');
    Route::delete('/akademik/mata-kuliah/{code}',[App\Http\Controllers\Master\Akademik\MataKuliahController::class, 'deleteMataKuliah'])->name('akademik.mata-kuliah-delete');

    // MASTER AKADEMIK => KELAS
    Route::get('/akademik/kelas',[App\Http\Controllers\Master\Akademik\KelasController::class, 'renderKelas'])->name('akademik.kelas-render');
    Route::post('/akademik/kelas',[App\Http\Controllers\Master\Akademik\KelasController::class, 'handleKelas'])->name('akademik.kelas-handle');
    Route::patch('/akademik/kelas/{code}',[App\Http\Controllers\Master\Akademik\KelasController::class, 'updateKelas'])->name('akademik.kelas-update');
    Route::delete('/akademik/kelas/{code}',[App\Http\Controllers\Master\Akademik\KelasController::class, 'deleteKelas'])->name('akademik.kelas-delete');

    // MASTER AKADEMIK => JADWAL KULIAH
    Route::get('/akademik/jadwal-kuliah',[App\Http\Controllers\Master\Akademik\JadwalKuliahController::class, 'renderJadwalKuliah'])->name('akademik.jadwal-kuliah-render');
    Route::post('/akademik/jadwal-kuliah',[App\Http\Controllers\Master\Akademik\JadwalKuliahController::class, 'handleJadwalKuliah'])->name('akademik.jadwal-kuliah-handle');
    Route::patch('/akademik/jadwal-kuliah/{code}',[App\Http\Controllers\Master\Akademik\JadwalKuliahController::class, 'updateJadwalKuliah'])->name('akademik.jadwal-kuliah-update');
    Route::delete('/akademik/jadwal-kuliah/{code}',[App\Http\Controllers\Master\Akademik\JadwalKuliahController::class, 'deleteJadwalKuliah'])->name('akademik.jadwal-kuliah-delete');
    Route::get('akademik/get-waktu-kuliah/{jenis_kelas_id}', [App\Http\Controllers\Master\Akademik\JadwalKuliahController::class, 'getWaktuKuliahByJenisKelas'])->name('akademik.get-waktu-kuliah');

    // MASTER AKADEMIK => KRS (KARTU RENCANA STUDI)
    Route::get('/akademik/krs',[App\Http\Controllers\Master\Akademik\KRSController::class, 'renderKRS'])->name('akademik.krs-render');
    Route::post('/akademik/krs',[App\Http\Controllers\Master\Akademik\KRSController::class, 'handleKRS'])->name('akademik.krs-handle');
    Route::get('/akademik/krs/{code}/detail',[App\Http\Controllers\Master\Akademik\KRSController::class, 'detailKRS'])->name('akademik.krs-detail');
    Route::get('/akademik/krs/{code}/print',[App\Http\Controllers\Master\Akademik\KRSController::class, 'printKRS'])->name('akademik.krs-print');
    Route::get('/akademik/krs/{code}',[App\Http\Controllers\Master\Akademik\KRSController::class, 'viewKRS'])->name('akademik.krs-view');
    Route::patch('/akademik/krs/{code}',[App\Http\Controllers\Master\Akademik\KRSController::class, 'updateKRS'])->name('akademik.krs-update');
    Route::patch('/akademik/krs/{code}/detail/{detailId}/matakuliah',[App\Http\Controllers\Master\Akademik\KRSController::class, 'updateMatakuliah'])->name('akademik.krs-update-matakuliah');
    Route::delete('/akademik/krs/{code}',[App\Http\Controllers\Master\Akademik\KRSController::class, 'deleteKRS'])->name('akademik.krs-delete');
    Route::post('/akademik/krs/{code}/add-matakuliah',[App\Http\Controllers\Master\Akademik\KRSController::class, 'addMatakuliah'])->name('akademik.krs-add-matakuliah');
    Route::delete('/akademik/krs/{code}/remove-matakuliah/{detailId}',[App\Http\Controllers\Master\Akademik\KRSController::class, 'removeMatakuliah'])->name('akademik.krs-remove-matakuliah');
    Route::post('/akademik/krs/{code}/approve',[App\Http\Controllers\Master\Akademik\KRSController::class, 'approveKRS'])->name('akademik.krs-approve');
    Route::post('/akademik/krs/{code}/reject',[AppHttpControllersMasterAkademikKRSController::class, 'rejectKRS'])->name('akademik.krs-reject');
    Route::post('/akademik/krs/{code}/publish',[AppHttpControllersMasterAkademikKRSController::class, 'publishKRS'])->name('akademik.krs-publish');
    Route::post('/akademik/krs/{code}/lock',[AppHttpControllersMasterAkademikKRSController::class, 'lockKRS'])->name('akademik.krs-lock');
    Route::post('/akademik/krs/bulk-approve',[AppHttpControllersMasterAkademikKRSController::class, 'bulkApprove'])->name('akademik.krs-bulk-approve');
    Route::post('/akademik/krs/bulk-publish',[AppHttpControllersMasterAkademikKRSController::class, 'bulkPublish'])->name('akademik.krs-bulk-publish');

    // MASTER AKADEMIK => NILAI
    Route::get('/akademik/nilai',[AppHttpControllersMasterAkademikNilaiController::class, 'renderNilai'])->name('akademik.nilai-render');
    Route::get('/akademik/nilai/import',[AppHttpControllersMasterAkademikNilaiController::class, 'renderImportNilai'])->name('akademik.nilai-import');
    Route::get('/akademik/nilai/export',[AppHttpControllersMasterAkademikNilaiController::class, 'exportNilai'])->name('akademik.nilai-export');
    Route::post('/akademik/nilai',[AppHttpControllersMasterAkademikNilaiController::class, 'handleNilai'])->name('akademik.nilai-handle');
    Route::get('/akademik/nilai/{code}',[AppHttpControllersMasterAkademikNilaiController::class, 'viewNilai'])->name('akademik.nilai-view');
    Route::patch('/akademik/nilai/{code}',[AppHttpControllersMasterAkademikNilaiController::class, 'updateNilai'])->name('akademik.nilai-update');
    Route::delete('/akademik/nilai/{code}',[AppHttpControllersMasterAkademikNilaiController::class, 'deleteNilai'])->name('akademik.nilai-delete');
    Route::post('/akademik/nilai/import',[AppHttpControllersMasterAkademikNilaiController::class, 'importNilai'])->name('akademik.nilai-import-process');
    Route::post('/akademik/nilai/bulk-update',[AppHttpControllersMasterAkademikNilaiController::class, 'bulkUpdate'])->name('akademik.nilai-bulk-update');
    Route::post('/akademik/nilai/{code}/approve',[AppHttpControllersMasterAkademikNilaiController::class, 'approveNilai'])->name('akademik.nilai-approve');
    Route::post('/akademik/nilai/{code}/publish',[AppHttpControllersMasterAkademikNilaiController::class, 'publishNilai'])->name('akademik.nilai-publish');
    Route::post('/akademik/nilai/{code}/lock',[AppHttpControllersMasterAkademikNilaiController::class, 'lockNilai'])->name('akademik.nilai-lock');

    // MASTER AKADEMIK => KHS (KARTU HASIL STUDI)
    Route::get('/akademik/khs',[AppHttpControllersMasterAkademikKHSController::class, 'renderKHS'])->name('akademik.khs-render');
    Route::post('/akademik/khs',[AppHttpControllersMasterAkademikKHSController::class, 'handleKHS'])->name('akademik.khs-handle');
    Route::get('/akademik/khs/{code}/detail',[AppHttpControllersMasterAkademikKHSController::class, 'detailKHS'])->name('akademik.khs-detail');
    Route::get('/akademik/khs/{code}/print',[AppHttpControllersMasterAkademikKHSController::class, 'printKHS'])->name('akademik.khs-print');
    Route::get('/akademik/khs/{code}',[AppHttpControllersMasterAkademikKHSController::class, 'viewKHS'])->name('akademik.khs-view');
    Route::patch('/akademik/khs/{code}',[AppHttpControllersMasterAkademikKHSController::class, 'updateKHS'])->name('akademik.khs-update');
    Route::delete('/akademik/khs/{code}',[AppHttpControllersMasterAkademikKHSController::class, 'deleteKHS'])->name('akademik.khs-delete');
    Route::post('/akademik/khs/generate',[AppHttpControllersMasterAkademikKHSController::class, 'generateKHS'])->name('akademik.khs-generate');
    Route::post('/akademik/khs/{code}/publish',[AppHttpControllersMasterAkademikKHSController::class, 'publishKHS'])->name('akademik.khs-publish');
    Route::post('/akademik/khs/{code}/lock',[AppHttpControllersMasterAkademikKHSController::class, 'lockKHS'])->name('akademik.khs-lock');
    Route::get('/akademik/khs/transkrip/{mahasiswa_code}',[AppHttpControllersMasterAkademikKHSController::class, 'transkrip'])->name('akademik.khs-transkrip');
    Route::post('/akademik/khs/bulk-generate',[AppHttpControllersMasterAkademikKHSController::class, 'bulkGenerate'])->name('akademik.khs-bulk-generate');
    Route::post('/akademik/khs/bulk-publish',[AppHttpControllersMasterAkademikKHSController::class, 'bulkPublish'])->name('akademik.khs-bulk-publish');

    // MASTER AKADEMIK => JENIS KELAS
    Route::get('/akademik/jenis-kelas',[AppHttpControllersMasterAkademikJenisKelasController::class, 'renderJenisKelas'])->name('akademik.jenis-kelas-render');
    Route::post('/akademik/jenis-kelas',[AppHttpControllersMasterAkademikJenisKelasController::class, 'handleJenisKelas'])->name('akademik.jenis-kelas-handle');
    Route::patch('/akademik/jenis-kelas/{code}',[AppHttpControllersMasterAkademikJenisKelasController::class, 'updateJenisKelas'])->name('akademik.jenis-kelas-update');
    Route::delete('/akademik/jenis-kelas/{code}',[AppHttpControllersMasterAkademikJenisKelasController::class, 'deleteJenisKelas'])->name('akademik.jenis-kelas-delete');

    // MASTER AKADEMIK => WAKTU KULIAH
    Route::get('/akademik/waktu-kuliah',[AppHttpControllersMasterAkademikWaktuKuliahController::class, 'renderWaktuKuliah'])->name('akademik.waktu-kuliah-render');
    Route::post('/akademik/waktu-kuliah',[AppHttpControllersMasterAkademikWaktuKuliahController::class, 'handleWaktuKuliah'])->name('akademik.waktu-kuliah-handle');
    Route::patch('/akademik/waktu-kuliah/{code}',[AppHttpControllersMasterAkademikWaktuKuliahController::class, 'updateWaktuKuliah'])->name('akademik.waktu-kuliah-update');
    Route::delete('/akademik/waktu-kuliah/{code}',[AppHttpControllersMasterAkademikWaktuKuliahController::class, 'deleteWaktuKuliah'])->name('akademik.waktu-kuliah-delete');

    // MASTER PMB => PERIODE PENDAFTARAN
    Route::get('/pmb/periode',[AppHttpControllersMasterPMBPeriodePendaftaranController::class, 'renderPeriode'])->name('pmb.periode-render');
    Route::post('/pmb/periode',[AppHttpControllersMasterPMBPeriodePendaftaranController::class, 'handlePeriode'])->name('pmb.periode-handle');
    Route::patch('/pmb/periode/{code}',[AppHttpControllersMasterPMBPeriodePendaftaranController::class, 'updatePeriode'])->name('pmb.periode-update');
    Route::delete('/pmb/periode/{code}',[AppHttpControllersMasterPMBPeriodePendaftaranController::class, 'deletePeriode'])->name('pmb.periode-delete');

    // MASTER PMB => JALUR PENDAFTARAN
    Route::get('/pmb/jalur',[AppHttpControllersMasterPMBJalurPendaftaranController::class, 'renderJalur'])->name('pmb.jalur-render');
    Route::post('/pmb/jalur',[AppHttpControllersMasterPMBJalurPendaftaranController::class, 'handleJalur'])->name('pmb.jalur-handle');
    Route::patch('/pmb/jalur/{code}',[AppHttpControllersMasterPMBJalurPendaftaranController::class, 'updateJalur'])->name('pmb.jalur-update');
    Route::delete('/pmb/jalur/{code}',[AppHttpControllersMasterPMBJalurPendaftaranController::class, 'deleteJalur'])->name('pmb.jalur-delete');

    // MASTER PMB => BIAYA PENDAFTARAN
    Route::get('/pmb/biaya',[AppHttpControllersMasterPMBBiayaPendaftaranController::class, 'renderBiaya'])->name('pmb.biaya-render');
    Route::post('/pmb/biaya',[AppHttpControllersMasterPMBBiayaPendaftaranController::class, 'handleBiaya'])->name('pmb.biaya-handle');
    Route::patch('/pmb/biaya/{code}',[AppHttpControllersMasterPMBBiayaPendaftaranController::class, 'updateBiaya'])->name('pmb.biaya-update');
    Route::delete('/pmb/biaya/{code}',[AppHttpControllersMasterPMBBiayaPendaftaranController::class, 'deleteBiaya'])->name('pmb.biaya-delete');

    // MASTER PMB => SYARAT PENDAFTARAN
    Route::get('/pmb/syarat',[AppHttpControllersMasterPMBSyaratPendaftaranController::class, 'renderSyarat'])->name('pmb.syarat-render');
    Route::post('/pmb/syarat',[AppHttpControllersMasterPMBSyaratPendaftaranController::class, 'handleSyarat'])->name('pmb.syarat-handle');
    Route::patch('/pmb/syarat/{code}',[AppHttpControllersMasterPMBSyaratPendaftaranController::class, 'updateSyarat'])->name('pmb.syarat-update');
    Route::delete('/pmb/syarat/{code}',[AppHttpControllersMasterPMBSyaratPendaftaranController::class, 'deleteSyarat'])->name('pmb.syarat-delete');

    // MASTER PMB => GELOMBANG PENDAFTARAN
    Route::get('/pmb/gelombang',[AppHttpControllersMasterPMBGelombangPendaftaranController::class, 'renderGelombang'])->name('pmb.gelombang-render');
    Route::post('/pmb/gelombang',[AppHttpControllersMasterPMBGelombangPendaftaranController::class, 'handleGelombang'])->name('pmb.gelombang-handle');
    Route::patch('/pmb/gelombang/{code}',[AppHttpControllersMasterPMBGelombangPendaftaranController::class, 'updateGelombang'])->name('pmb.gelombang-update');
    Route::delete('/pmb/gelombang/{code}',[AppHttpControllersMasterPMBGelombangPendaftaranController::class, 'deleteGelombang'])->name('pmb.gelombang-delete');

    // MASTER PMB => JADWAL PMB
    Route::get('/pmb/jadwal',[AppHttpControllersMasterPMBJadwalPMBController::class, 'renderJadwal'])->name('pmb.jadwal-render');
    Route::post('/pmb/jadwal',[AppHttpControllersMasterPMBJadwalPMBController::class, 'handleJadwal'])->name('pmb.jadwal-handle');
    Route::patch('/pmb/jadwal/{code}',[AppHttpControllersMasterPMBJadwalPMBController::class, 'updateJadwal'])->name('pmb.jadwal-update');
    Route::delete('/pmb/jadwal/{code}',[AppHttpControllersMasterPMBJadwalPMBController::class, 'deleteJadwal'])->name('pmb.jadwal-delete');

    // MASTER PMB => PENDAFTAR
    Route::get('/pmb/pendaftar', [App\Http\Controllers\Master\PMB\PendaftarController::class, 'renderPendaftar'])->name('pmb.pendaftar-render');
    Route::post('/pmb/pendaftar', [App\Http\Controllers\Master\PMB\PendaftarController::class, 'handlePendaftar'])->name('pmb.pendaftar-handle');
    Route::patch('/pmb/pendaftar/{code}', [App\Http\Controllers\Master\PMB\PendaftarController::class, 'updatePendaftar'])->name('pmb.pendaftar-update');
    Route::delete('/pmb/pendaftar/{code}', [App\Http\Controllers\Master\PMB\PendaftarController::class, 'deletePendaftar'])->name('pmb.pendaftar-delete');
    Route::get('/pmb/pendaftar/{code}', [App\Http\Controllers\Master\PMB\PendaftarController::class, 'renderDetail'])->name('pmb.pendaftar-detail');
    Route::post('/pmb/pendaftar/{code}/dokumen', [App\Http\Controllers\Master\PMB\PendaftarController::class, 'handleDokumen'])->name('pmb.pendaftar-dokumen-handle');
    Route::patch('/pmb/pendaftar/{code}/validasi', [App\Http\Controllers\Master\PMB\PendaftarController::class, 'validasiDokumen'])->name('pmb.pendaftar-validasi');
    Route::get('/pmb/pendaftar/export/excel', [App\Http\Controllers\Master\PMB\PendaftarController::class, 'exportPendaftarExcel'])->name('pmb.pendaftar-export-excel');
    Route::get('/pmb/pendaftar/export/pdf', [App\Http\Controllers\Master\PMB\PendaftarController::class, 'exportPendaftarPDF'])->name('pmb.pendaftar-export-pdf');
    Route::post('/pmb/pendaftar/batch/validasi', [App\Http\Controllers\Master\PMB\PendaftarController::class, 'batchValidasiDokumen'])->name('pmb.pendaftar-batch-validasi');
    Route::post('/pmb/pendaftar/batch/status', [App\Http\Controllers\Master\PMB\PendaftarController::class, 'batchUpdateStatus'])->name('pmb.pendaftar-batch-status');

    // MASTER PENGGUNA => USERS
    Route::get('/pengguna/users',[App\Http\Controllers\Master\Pengguna\UsersController::class, 'renderUsers'])->name('pengguna.users-render');
    Route::get('/pengguna/users/export/pdf',[App\Http\Controllers\Master\Pengguna\UsersController::class, 'exportUsersPDF'])->name('pengguna.users-export-pdf');
    Route::get('/pengguna/users/{code}/views',[App\Http\Controllers\Master\Pengguna\UsersController::class, 'viewUsers'])->name('pengguna.users-views');
    Route::post('/pengguna/users',[App\Http\Controllers\Master\Pengguna\UsersController::class, 'handleUsers'])->name('pengguna.users-handle');
    Route::patch('/pengguna/users/{code}',[App\Http\Controllers\Master\Pengguna\UsersController::class, 'updateUsers'])->name('pengguna.users-update');
    Route::patch('/pengguna/users/{code}/profile', [App\Http\Controllers\Master\Pengguna\UsersController::class, 'handleProfile'])->name('pengguna.users-profile');
    Route::delete('/pengguna/users/{code}',[App\Http\Controllers\Master\Pengguna\UsersController::class, 'deleteUsers'])->name('pengguna.users-delete');

    // MASTER PENGGUNA => DOSEN
    Route::get('/pengguna/dosen',[App\Http\Controllers\Master\Pengguna\DosenController::class, 'renderDosen'])->name('pengguna.dosen-render');
    Route::get('/pengguna/dosen/export/pdf',[App\Http\Controllers\Master\Pengguna\DosenController::class, 'exportDosenPDF'])->name('pengguna.dosen-export-pdf');
    Route::get('/pengguna/dosen/export/excel',[App\Http\Controllers\Master\Pengguna\DosenController::class, 'exportDosenExcel'])->name('pengguna.dosen-export-excel');
    Route::get('/pengguna/dosen/export/full-excel',[App\Http\Controllers\Master\Pengguna\DosenController::class, 'exportDosenFullExcel'])->name('pengguna.dosen-export-full-excel');
    Route::get('/pengguna/dosen/import/template',[App\Http\Controllers\Master\Pengguna\DosenController::class, 'downloadDosenImportTemplate'])->name('pengguna.dosen-import-template');
    Route::post('/pengguna/dosen/import/excel',[App\Http\Controllers\Master\Pengguna\DosenController::class, 'importDosenExcel'])->name('pengguna.dosen-import-excel');
    Route::get('/pengguna/dosen/{code}/views',[App\Http\Controllers\Master\Pengguna\DosenController::class, 'viewDosen'])->name('pengguna.dosen-views');
    Route::post('/pengguna/dosen',[App\Http\Controllers\Master\Pengguna\DosenController::class, 'handleDosen'])->name('pengguna.dosen-handle');
    Route::patch('/pengguna/dosen/{code}',[App\Http\Controllers\Master\Pengguna\DosenController::class, 'updateDosen'])->name('pengguna.dosen-update');
    Route::patch('/pengguna/dosen/{code}/profile', [App\Http\Controllers\Master\Pengguna\DosenController::class, 'handleProfile'])->name('pengguna.dosen-profile');
    Route::delete('/pengguna/dosen/{code}',[App\Http\Controllers\Master\Pengguna\DosenController::class, 'deleteDosen'])->name('pengguna.dosen-delete');

    // MASTER PENGGUNA => MAHASISWA
    Route::get('/pengguna/mahasiswa',[App\Http\Controllers\Master\Pengguna\MahasiswaController::class, 'renderMahasiswa'])->name('pengguna.mahasiswa-render');
    Route::get('/pengguna/mahasiswa/export/pdf',[App\Http\Controllers\Master\Pengguna\MahasiswaController::class, 'exportMahasiswaPDF'])->name('pengguna.mahasiswa-export-pdf');
    Route::get('/pengguna/mahasiswa/export/excel',[App\Http\Controllers\Master\Pengguna\MahasiswaController::class, 'exportMahasiswaExcel'])->name('pengguna.mahasiswa-export-excel');
    Route::get('/pengguna/mahasiswa/export/full-excel',[App\Http\Controllers\Master\Pengguna\MahasiswaController::class, 'exportMahasiswaFullExcel'])->name('pengguna.mahasiswa-export-full-excel');
    Route::get('/pengguna/mahasiswa/import/template',[App\Http\Controllers\Master\Pengguna\MahasiswaController::class, 'downloadMahasiswaImportTemplate'])->name('pengguna.mahasiswa-import-template');
    Route::post('/pengguna/mahasiswa/import/excel',[App\Http\Controllers\Master\Pengguna\MahasiswaController::class, 'importMahasiswaExcel'])->name('pengguna.mahasiswa-import-excel');
    Route::get('/pengguna/mahasiswa/{code}/views',[App\Http\Controllers\Master\Pengguna\MahasiswaController::class, 'viewMahasiswa'])->name('pengguna.mahasiswa-views');
    Route::post('/pengguna/mahasiswa',[App\Http\Controllers\Master\Pengguna\MahasiswaController::class, 'handleMahasiswa'])->name('pengguna.mahasiswa-handle');
    Route::patch('/pengguna/mahasiswa/{code}',[App\Http\Controllers\Master\Pengguna\MahasiswaController::class, 'updateMahasiswa'])->name('pengguna.mahasiswa-update');
    Route::patch('/pengguna/mahasiswa/{code}/profile', [App\Http\Controllers\Master\Pengguna\MahasiswaController::class, 'handleProfile'])->name('pengguna.mahasiswa-profile');
    Route::delete('/pengguna/mahasiswa/{code}',[App\Http\Controllers\Master\Pengguna\MahasiswaController::class, 'deleteMahasiswa'])->name('pengguna.mahasiswa-delete');

    // MASTER PUBLIKASI => KATEGORI
    Route::get('/publikasi/kategori', [App\Http\Controllers\Master\Publikasi\KategoriController::class, 'renderKategori'])->name('publikasi.kategori-render');
    Route::post('/publikasi/kategori', [App\Http\Controllers\Master\Publikasi\KategoriController::class, 'handleKategori'])->name('publikasi.kategori-handle');
    Route::patch('/publikasi/kategori/{code}', [App\Http\Controllers\Master\Publikasi\KategoriController::class, 'updateKategori'])->name('publikasi.kategori-update');
    Route::delete('/publikasi/kategori/{code}', [App\Http\Controllers\Master\Publikasi\KategoriController::class, 'deleteKategori'])->name('publikasi.kategori-delete');

    // MASTER PUBLIKASI => BERITA
    Route::get('/publikasi/berita', [App\Http\Controllers\Master\Publikasi\BeritaController::class, 'renderBerita'])->name('publikasi.berita-render');
    Route::get('/publikasi/berita/{code}/view', [App\Http\Controllers\Master\Publikasi\BeritaController::class, 'viewBerita'])->name('publikasi.berita-view');
    Route::post('/publikasi/berita', [App\Http\Controllers\Master\Publikasi\BeritaController::class, 'handleBerita'])->name('publikasi.berita-handle');
    Route::patch('/publikasi/berita/{code}', [AppHttpControllersMasterPublikasiBeritaController::class, 'updateBerita'])->name('publikasi.berita-update');
    Route::delete('/publikasi/berita/{code}', [AppHttpControllersMasterPublikasiBeritaController::class, 'deleteBerita'])->name('publikasi.berita-delete');

    // MASTER PUBLIKASI => PENGUMUMAN
    Route::get('/publikasi/pengumuman', [AppHttpControllersMasterPublikasiPengumumanController::class, 'renderPengumuman'])->name('publikasi.pengumuman-render');
    Route::get('/publikasi/pengumuman/{code}/view', [AppHttpControllersMasterPublikasiPengumumanController::class, 'viewPengumuman'])->name('publikasi.pengumuman-view');
    Route::post('/publikasi/pengumuman', [AppHttpControllersMasterPublikasiPengumumanController::class, 'handlePengumuman'])->name('publikasi.pengumuman-handle');
    Route::patch('/publikasi/pengumuman/{code}', [AppHttpControllersMasterPublikasiPengumumanController::class, 'updatePengumuman'])->name('publikasi.pengumuman-update');
    Route::delete('/publikasi/pengumuman/{code}', [AppHttpControllersMasterPublikasiPengumumanController::class, 'deletePengumuman'])->name('publikasi.pengumuman-delete');

    // MASTER PUBLIKASI => GALERI
    Route::get('/publikasi/galeri', [AppHttpControllersMasterPublikasiGaleriController::class, 'renderGaleri'])->name('publikasi.galeri-render');
    Route::get('/publikasi/galeri/{code}/view', [AppHttpControllersMasterPublikasiGaleriController::class, 'viewGaleri'])->name('publikasi.galeri-view');
    Route::post('/publikasi/galeri', [AppHttpControllersMasterPublikasiGaleriController::class, 'handleGaleri'])->name('publikasi.galeri-handle');
    Route::patch('/publikasi/galeri/{code}', [AppHttpControllersMasterPublikasiGaleriController::class, 'updateGaleri'])->name('publikasi.galeri-update');
    Route::delete('/publikasi/galeri/{code}', [AppHttpControllersMasterPublikasiGaleriController::class, 'deleteGaleri'])->name('publikasi.galeri-delete');

    // MASTER PUBLIKASI => GALERI FOTO
    Route::post('/publikasi/galeri/{code}/foto', [AppHttpControllersMasterPublikasiGaleriController::class, 'handleFoto'])->name('publikasi.galeri-foto-handle');
    Route::delete('/publikasi/galeri/foto/{code}', [AppHttpControllersMasterPublikasiGaleriController::class, 'deleteFoto'])->name('publikasi.galeri-foto-delete');

    // MASTER PENGATURAN => WEB SETTINGS
    Route::get('/pengaturan/web-settings', [AppHttpControllersMasterPengaturanWebSettingController::class, 'renderIndex'])->name('pengaturan.web-settings-render');
    Route::patch('/pengaturan/web-settings', [AppHttpControllersMasterPengaturanWebSettingController::class, 'handleSettings'])->name('pengaturan.web-settings-handle');
    Route::get('/pengaturan/export-settings', [AppHttpControllersMasterPengaturanWebSettingController::class, 'exportDatabase'])->name('pengaturan.export-database');
    Route::post('/pengaturan/import-settings', [AppHttpControllersMasterPengaturanWebSettingController::class, 'importDatabase'])->name('pengaturan.import-database');

    // MASTER PENGATURAN => LOG AKTIVITAS
    Route::get('/pengaturan/log-aktivitas', [AppHttpControllersMasterPengaturanLogAktivitasController::class, 'renderLogAktivitas'])->name('pengaturan.log-aktivitas-render');
    Route::get('/pengaturan/log-aktivitas/{id}/view', [AppHttpControllersMasterPengaturanLogAktivitasController::class, 'viewLogAktivitas'])->name('pengaturan.log-aktivitas-view');
    Route::get('/pengaturan/log-aktivitas/filter', [AppHttpControllersMasterPengaturanLogAktivitasController::class, 'filterLogAktivitas'])->name('pengaturan.log-aktivitas-filter');
    Route::delete('/pengaturan/log-aktivitas/{id}', [AppHttpControllersMasterPengaturanLogAktivitasController::class, 'deleteLogAktivitas'])->name('pengaturan.log-aktivitas-delete');

    // MASTER INFRASTRUKTUR => GEDUNG
    Route::get('/infrastruktur/gedung', [AppHttpControllersMasterInfrastrukturGedungController::class, 'renderGedung'])->name('infrastruktur.gedung-render');
    Route::post('/infrastruktur/gedung', [AppHttpControllersMasterInfrastrukturGedungController::class, 'handleGedung'])->name('infrastruktur.gedung-handle');
    Route::patch('/infrastruktur/gedung/{code}', [AppHttpControllersMasterInfrastrukturGedungController::class, 'updateGedung'])->name('infrastruktur.gedung-update');
    Route::delete('/infrastruktur/gedung/{code}', [AppHttpControllersMasterInfrastrukturGedungController::class, 'deleteGedung'])->name('infrastruktur.gedung-delete');

    // MASTER INFRASTRUKTUR => RUANG
    Route::get('/infrastruktur/ruang', [AppHttpControllersMasterInfrastrukturRuangController::class, 'renderRuang'])->name('infrastruktur.ruang-render');
    Route::post('/infrastruktur/ruang', [AppHttpControllersMasterInfrastrukturRuangController::class, 'handleRuang'])->name('infrastruktur.ruang-handle');
    Route::patch('/infrastruktur/ruang/{code}', [AppHttpControllersMasterInfrastrukturRuangController::class, 'updateRuang'])->name('infrastruktur.ruang-update');
    Route::delete('/infrastruktur/ruang/{code}', [AppHttpControllersMasterInfrastrukturRuangController::class, 'deleteRuang'])->name('infrastruktur.ruang-delete');

    // MASTER INFRASTRUKTUR => KATEGORI BARANG
    Route::get('/infrastruktur/kategori-barang', [AppHttpControllersMasterInfrastrukturKategoriBarangController::class, 'renderKategoriBarang'])->name('infrastruktur.kategori-barang-render');
    Route::post('/infrastruktur/kategori-barang', [AppHttpControllersMasterInfrastrukturKategoriBarangController::class, 'handleKategoriBarang'])->name('infrastruktur.kategori-barang-handle');
    Route::patch('/infrastruktur/kategori-barang/{code}', [AppHttpControllersMasterInfrastrukturKategoriBarangController::class, 'updateKategoriBarang'])->name('infrastruktur.kategori-barang-update');
    Route::delete('/infrastruktur/kategori-barang/{code}', [AppHttpControllersMasterInfrastrukturKategoriBarangController::class, 'deleteKategoriBarang'])->name('infrastruktur.kategori-barang-delete');

    // MASTER INFRASTRUKTUR => BARANG
    Route::get('/infrastruktur/barang', [AppHttpControllersMasterInfrastrukturBarangController::class, 'renderBarang'])->name('infrastruktur.barang-render');
    Route::post('/infrastruktur/barang', [AppHttpControllersMasterInfrastrukturBarangController::class, 'handleBarang'])->name('infrastruktur.barang-handle');
    Route::patch('/infrastruktur/barang/{code}', [AppHttpControllersMasterInfrastrukturBarangController::class, 'updateBarang'])->name('infrastruktur.barang-update');
    Route::delete('/infrastruktur/barang/{code}', [AppHttpControllersMasterInfrastrukturBarangController::class, 'deleteBarang'])->name('infrastruktur.barang-delete');

    // MASTER INFRASTRUKTUR => MUTASI BARANG
    Route::get('/infrastruktur/mutasi-barang', [AppHttpControllersMasterInfrastrukturMutasiBarangController::class, 'renderMutasiBarang'])->name('infrastruktur.mutasi-barang-render');
    Route::post('/infrastruktur/mutasi-barang', [AppHttpControllersMasterInfrastrukturMutasiBarangController::class, 'handleMutasiBarang'])->name('infrastruktur.mutasi-barang-handle');
    Route::patch('/infrastruktur/mutasi-barang/{code}', [AppHttpControllersMasterInfrastrukturMutasiBarangController::class, 'updateMutasiBarang'])->name('infrastruktur.mutasi-barang-update');
    Route::delete('/infrastruktur/mutasi-barang/{code}', [AppHttpControllersMasterInfrastrukturMutasiBarangController::class, 'deleteMutasiBarang'])->name('infrastruktur.mutasi-barang-delete');

    // MASTER INFRASTRUKTUR => PENGADAAN BARANG
    Route::get('/infrastruktur/pengadaan-barang', [AppHttpControllersMasterInfrastrukturPengadaanBarangController::class, 'renderPengadaanBarang'])->name('infrastruktur.pengadaan-barang-render');
    Route::post('/infrastruktur/pengadaan-barang', [AppHttpControllersMasterInfrastrukturPengadaanBarangController::class, 'handlePengadaanBarang'])->name('infrastruktur.pengadaan-barang-handle');
    Route::patch('/infrastruktur/pengadaan-barang/{code}', [AppHttpControllersMasterInfrastrukturPengadaanBarangController::class, 'updatePengadaanBarang'])->name('infrastruktur.pengadaan-barang-update');
    Route::delete('/infrastruktur/pengadaan-barang/{code}', [AppHttpControllersMasterInfrastrukturPengadaanBarangController::class, 'deletePengadaanBarang'])->name('infrastruktur.pengadaan-barang-delete');

    // MASTER INFRASTRUKTUR => INVENTARIS BARANG
    Route::get('/infrastruktur/inventaris-barang', [AppHttpControllersMasterInfrastrukturInventarisBarangController::class, 'renderInventarisBarang'])->name('infrastruktur.inventaris-barang-render');
    Route::post('/infrastruktur/inventaris-barang', [AppHttpControllersMasterInfrastrukturInventarisBarangController::class, 'handleInventarisBarang'])->name('infrastruktur.inventaris-barang-handle');
    Route::patch('/infrastruktur/inventaris-barang/{code}', [AppHttpControllersMasterInfrastrukturInventarisBarangController::class, 'updateInventarisBarang'])->name('infrastruktur.inventaris-barang-update');
    Route::delete('/infrastruktur/inventaris-barang/{code}', [AppHttpControllersMasterInfrastrukturInventarisBarangController::class, 'deleteInventarisBarang'])->name('infrastruktur.inventaris-barang-delete');

    // MASTER KEUANGAN => SALDO
    Route::get('/keuangan/saldo', [AppHttpControllersMasterKeuanganSaldoController::class, 'renderSaldo'])->name('keuangan.saldo-render');
    Route::post('/keuangan/saldo', [AppHttpControllersMasterKeuanganSaldoController::class, 'handleSaldo'])->name('keuangan.saldo-handle');
    Route::patch('/keuangan/saldo/{code}', [AppHttpControllersMasterKeuanganSaldoController::class, 'updateSaldo'])->name('keuangan.saldo-update');
    Route::delete('/keuangan/saldo/{code}', [AppHttpControllersMasterKeuanganSaldoController::class, 'deleteSaldo'])->name('keuangan.saldo-delete');

    // MASTER KEUANGAN => TAGIHAN KULIAH GROUP
    Route::get('/keuangan/tagihan-kuliah-group', [AppHttpControllersMasterKeuanganTagihanKuliahGroupController::class, 'renderTagihanKuliahGroup'])->name('keuangan.tagihan-kuliah-group-render');
    Route::post('/keuangan/tagihan-kuliah-group', [AppHttpControllersMasterKeuanganTagihanKuliahGroupController::class, 'handleTagihanKuliahGroup'])->name('keuangan.tagihan-kuliah-group-handle');
    Route::patch('/keuangan/tagihan-kuliah-group/{code}', [AppHttpControllersMasterKeuanganTagihanKuliahGroupController::class, 'updateTagihanKuliahGroup'])->name('keuangan.tagihan-kuliah-group-update');
    Route::delete('/keuangan/tagihan-kuliah-group/{code}', [AppHttpControllersMasterKeuanganTagihanKuliahGroupController::class, 'deleteTagihanKuliahGroup'])->name('keuangan.tagihan-kuliah-group-delete');
    Route::post('/keuangan/tagihan-kuliah-group/{code}/publish', [AppHttpControllersMasterKeuanganTagihanKuliahGroupController::class, 'publishTagihanKuliahGroup'])->name('keuangan.tagihan-kuliah-group-publish');
    Route::post('/keuangan/tagihan-kuliah-group/{code}/archive', [AppHttpControllersMasterKeuanganTagihanKuliahGroupController::class, 'archiveTagihanKuliahGroup'])->name('keuangan.tagihan-kuliah-group-archive');
    Route::get('/keuangan/tagihan-kuliah-group/{code}/detail', [AppHttpControllersMasterKeuanganTagihanKuliahGroupController::class, 'viewTagihanDetail'])->name('keuangan.tagihan-kuliah-group-detail');
