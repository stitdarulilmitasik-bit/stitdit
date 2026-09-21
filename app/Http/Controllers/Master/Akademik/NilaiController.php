<?php

namespace App\Http\Controllers\Master\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// Use System
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
// Use Models
use App\Models\Akademik\Nilai;
use App\Models\Akademik\KrsDetail;
use App\Models\Akademik\MataKuliah;
use App\Models\Akademik\TahunAkademik;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Pengaturan\WebSetting;
// Use Plugins
use Alert;

class NilaiController extends Controller
{
    private function isDosen()
    {
        return Auth::guard('dosen')->check();
    }

    private function dosenId()
    {
        return Auth::guard('dosen')->id();
    }

    private function mataKuliahDiampuQuery($query, $dosenId)
    {
        return $query->where(function ($q) use ($dosenId) {
            $q->where('dosen1_id', $dosenId)
                ->orWhere('dosen2_id', $dosenId)
                ->orWhere('dosen3_id', $dosenId);
        });
    }

    private function nilaiDosen($dosenId)
    {
        return Nilai::whereHas('mataKuliah', function ($q) use ($dosenId) {
            $this->mataKuliahDiampuQuery($q, $dosenId);
        });
    }

    public function renderNilai()
    {
        // Route ini dipakai Admin dan Dosen. Gunakan guard Dosen bila
        // halaman dibuka dari dashboard Dosen agar seluruh data/aksi
        // mengikuti identitas Dosen yang sedang login.
        $user = $this->isDosen() ? Auth::guard('dosen')->user() : Auth::user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $this->isDosen() ? 'dosen.' : ($user ? $user->prefix : '');
        $data['menus'] = "Master";
        $data['pages'] = "Nilai Mahasiswa";
        $data['academy'] = $data['webs']->school_apps . ' by ' . $data['webs']->school_name;

        if ($this->isDosen()) {
            $dosenId = $this->dosenId();

            // Dashboard Dosen hanya menerima mata kuliah yang benar-benar
            // tercatat pada dosen1_id/dosen2_id/dosen3_id.
            $data['nilai_list'] = $this->nilaiDosen($dosenId)
                ->with(['mahasiswa', 'mataKuliah', 'tahunAkademik'])
                ->latest()
                ->paginate(20);

            $data['mata_kuliah'] = MataKuliah::where(function ($q) use ($dosenId) {
                $this->mataKuliahDiampuQuery($q, $dosenId);
            })->orderBy('name')->get();

            $data['mahasiswa'] = Mahasiswa::where('type', 1)->get();
            $data['dosens'] = Dosen::whereKey($dosenId)->get();
        } else {
            $data['nilai_list'] = Nilai::with(['mahasiswa', 'mataKuliah', 'tahunAkademik'])
                ->latest()
                ->paginate(20);
            $data['mata_kuliah'] = MataKuliah::all();
            $data['mahasiswa'] = Mahasiswa::where('type', 1)->get();
            $data['dosens'] = Dosen::where('type', 1)->get(); // Dosen Aktif
        }
        $data['tahun_akademik'] = TahunAkademik::all();

        return view('master.akademik.nilai-index', $data, compact('user'));
    }

    public function renderImportNilai()
    {
        $user = Auth::user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $user ? $user->prefix : '';
        $data['menus'] = "Master";
        $data['pages'] = "Import Nilai dari KRS";
        $data['academy'] = $data['webs']->school_apps . ' by ' . $data['webs']->school_name;

        $data['tahun_akademik'] = TahunAkademik::all();
        $data['mahasiswa'] = Mahasiswa::where('type', 1)->get();
        $data['dosens'] = Dosen::where('type', 1)->get(); // Dosen Aktif

        return view('master.akademik.nilai-import', $data, compact('user'));
    }

    public function viewNilai($code)
    {
        $user = Auth::user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $user ? $user->prefix : '';
        $data['menus'] = "Master";
        $data['pages'] = "Detail Nilai";
        $data['academy'] = $data['webs']->school_apps . ' by ' . $data['webs']->school_name;

        $data['nilai'] = Nilai::with(['mahasiswa', 'mataKuliah', 'krsDetail', 'tahunAkademik'])
            ->where('code', $code)
            ->firstOrFail();

        return view('master.akademik.nilai-view', $data, compact('user'));
    }

    public function handleNilai(Request $request)
    {
        try {
            $actor = $this->isDosen() ? Auth::guard('dosen')->id() : Auth::id();
            DB::beginTransaction();

            $request->merge([
                'matkul_id' => $request->input('matkul_id', $request->input('mata_kuliah_id')),
            ]);

            $request->validate([
                'mahasiswa_id' => 'required|exists:mahasiswas,id',
                'matkul_id' => 'required|exists:mata_kuliahs,id',
                'tahun_akademik_id' => 'required|exists:tahun_akademiks,id',
                'semester' => 'required|integer|min:1|max:14',
                'krs_detail_id' => 'nullable|exists:krs_details,id',
                'bobot_tugas' => 'required|numeric|min:0|max:100',
                'bobot_quiz' => 'required|numeric|min:0|max:100',
                'bobot_uts' => 'required|numeric|min:0|max:100',
                'bobot_uas' => 'required|numeric|min:0|max:100',
                'bobot_praktikum' => 'required|numeric|min:0|max:100',
                'bobot_kehadiran' => 'required|numeric|min:0|max:100',
            ]);

            $totalBobot = (float) $request->bobot_tugas
                + (float) $request->bobot_quiz
                + (float) $request->bobot_uts
                + (float) $request->bobot_uas
                + (float) $request->bobot_praktikum
                + (float) $request->bobot_kehadiran;

            if (abs($totalBobot - 100) > 0.01) {
                throw new \InvalidArgumentException(
                    'Total bobot harus 100%. Saat ini: ' . rtrim(rtrim(number_format($totalBobot, 2, '.', ''), '0'), '.') . '%.'
                );
            }

            $mataKuliah = MataKuliah::findOrFail($request->matkul_id);

            // Dosen hanya boleh membuat nilai untuk mata kuliah yang diampunya.
            if ($this->isDosen()) {
                $allowed = $this->mataKuliahDiampuQuery(
                    MataKuliah::whereKey($mataKuliah->id),
                    $this->dosenId()
                )->exists();

                abort_unless($allowed, 403, 'Mata kuliah bukan mata kuliah yang Anda ampu.');
            }

            /*
             * Kombinasi mahasiswa + mata kuliah + tahun akademik + semester
             * adalah UNIQUE di tabel nilais. Gunakan firstOrCreate agar
             * pengiriman form ganda / request bersamaan tidak mencoba INSERT
             * baris kedua dan memicu SQLSTATE 23000.
             */
            $key = [
                'mahasiswa_id' => (int) $request->mahasiswa_id,
                'matkul_id' => (int) $request->matkul_id,
                'taka_id' => (int) $request->tahun_akademik_id,
                'semester' => (int) $request->semester,
            ];

            // Kombinasi ini sudah memiliki data Nilai.
            // Jangan INSERT ulang karena tabel nilais memiliki UNIQUE index.
            // Jika data sudah ada, cukup perbarui bobot yang dikirim dari form.
            $nilai = Nilai::withTrashed()->where($key)->first();

            if ($nilai) {
                // Record yang pernah dihapus (soft delete) tetap menempati UNIQUE index.
                // Pulihkan kembali agar tidak terjadi duplicate key saat membuat nilai baru.
                if ($nilai->trashed()) {
                    $nilai->restore();
                }

                $nilai->update([
                    'bobot_tugas' => $request->bobot_tugas,
                    'bobot_quiz' => $request->bobot_quiz,
                    'bobot_uts' => $request->bobot_uts,
                    'bobot_uas' => $request->bobot_uas,
                    'bobot_praktikum' => $request->bobot_praktikum,
                    'bobot_kehadiran' => $request->bobot_kehadiran,
                    'krs_detail_id' => $request->krs_detail_id ?: $nilai->krs_detail_id,
                    'sks' => $nilai->sks ?: $mataKuliah->sks,
                ]);

                DB::commit();

                Alert::info(
                    'Informasi',
                    'Data nilai untuk mahasiswa, mata kuliah, tahun akademik, dan semester tersebut sudah ada. Bobot nilai telah diperbarui.'
                );

                $prefix = $this->isDosen()
                    ? 'dosen.'
                    : (Auth::user()->prefix ?? '');

                return redirect()->route($prefix . 'akademik.nilai-view', $nilai->code);
            }

            // Belum ada data: buat record baru dengan bobot dari form.
            try {
                $nilai = Nilai::create(array_merge($key, [
                    'code' => 'NIL-' . date('Ymd') . '-' . Str::random(8),
                    'krs_detail_id' => $request->krs_detail_id,
                    'sks' => $mataKuliah->sks,
                    'bobot_tugas' => $request->bobot_tugas,
                    'bobot_quiz' => $request->bobot_quiz,
                    'bobot_uts' => $request->bobot_uts,
                    'bobot_uas' => $request->bobot_uas,
                    'bobot_praktikum' => $request->bobot_praktikum,
                    'bobot_kehadiran' => $request->bobot_kehadiran,
                    'created_by' => $actor,
                ]));
            } catch (QueryException $e) {
                // Jika request bersamaan membuat record yang sama,
                // ambil record yang sudah dibuat daripada menampilkan SQL error.
                if ((int) ($e->errorInfo[1] ?? 0) !== 1062) {
                    throw $e;
                }

                // Ambil ulang berdasarkan unique key setelah konflik insert.
                // Jika record belum terlihat karena transaksi bersamaan, ulangi beberapa kali.
                $nilai = null;
                for ($attempt = 0; $attempt < 3 && !$nilai; $attempt++) {
                    $nilai = Nilai::withTrashed()->where($key)->first();
                    if (!$nilai) {
                        usleep(100000);
                    }
                }

                if (!$nilai) {
                    throw new \RuntimeException('Data nilai sudah dibuat oleh proses lain, tetapi belum dapat ditemukan. Silakan buka ulang halaman Nilai dan coba lagi.');
                }

                if ($nilai->trashed()) {
                    $nilai->restore();
                }

                $nilai->update([
                    'bobot_tugas' => $request->bobot_tugas,
                    'bobot_quiz' => $request->bobot_quiz,
                    'bobot_uts' => $request->bobot_uts,
                    'bobot_uas' => $request->bobot_uas,
                    'bobot_praktikum' => $request->bobot_praktikum,
                    'bobot_kehadiran' => $request->bobot_kehadiran,
                ]);
            }

            DB::commit();

            if ($created) {
                Alert::success('Success', 'Data nilai berhasil dibuat');
            } else {
                Alert::info('Informasi', 'Data nilai untuk kombinasi tersebut sudah ada. Sistem membuka data yang sudah tersedia.');
            }

            $prefix = $this->isDosen()
                ? 'dosen.'
                : (Auth::user()->prefix ?? '');

            return redirect()->route($prefix . 'akademik.nilai-view', $nilai->code);

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal membuat data nilai: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function updateNilai(Request $request, $code)
    {
        try {
            DB::beginTransaction();

            $nilai = ($this->isDosen()
                ? $this->nilaiDosen($this->dosenId())
                : Nilai::query()
            )->where('code', $code)->firstOrFail();

            // Cek apakah nilai masih bisa diedit
            if (!$nilai->is_editable) {
                Alert::error('Error', 'Nilai tidak dapat diedit karena sudah dipublish atau dikunci');
                return redirect()->back();
            }

            $request->validate([
                'tugas_1' => 'nullable|numeric|min:0|max:100',
                'tugas_2' => 'nullable|numeric|min:0|max:100',
                'tugas_3' => 'nullable|numeric|min:0|max:100',
                'quiz_1' => 'nullable|numeric|min:0|max:100',
                'quiz_2' => 'nullable|numeric|min:0|max:100',
                'uts' => 'nullable|numeric|min:0|max:100',
                'uas' => 'nullable|numeric|min:0|max:100',
                'praktikum' => 'nullable|numeric|min:0|max:100',
                'kehadiran' => 'nullable|numeric|min:0|max:100',
                'bobot_tugas' => 'nullable|numeric|min:0|max:100',
                'bobot_quiz' => 'nullable|numeric|min:0|max:100',
                'bobot_uts' => 'nullable|numeric|min:0|max:100',
                'bobot_uas' => 'nullable|numeric|min:0|max:100',
                'bobot_praktikum' => 'nullable|numeric|min:0|max:100',
                'bobot_kehadiran' => 'nullable|numeric|min:0|max:100',
                'notes' => 'nullable|string',
                'is_remidi' => 'boolean',
                'nilai_remidi' => 'nullable|numeric|min:0|max:100',
                'is_susulan' => 'boolean',
            ]);

            // Validasi total bobot = 100%
            $totalBobot = ($request->bobot_tugas ?? $nilai->bobot_tugas) +
                         ($request->bobot_quiz ?? $nilai->bobot_quiz) +
                         ($request->bobot_uts ?? $nilai->bobot_uts) +
                         ($request->bobot_uas ?? $nilai->bobot_uas) +
                         ($request->bobot_praktikum ?? $nilai->bobot_praktikum) +
                         ($request->bobot_kehadiran ?? $nilai->bobot_kehadiran);

            if (abs($totalBobot - 100) > 0.01) {
                Alert::error('Error', 'Total bobot nilai harus 100%. Saat ini: ' . $totalBobot . '%');
                return redirect()->back()->withInput();
            }

            // Update hanya field yang benar-benar dikirim. Sebelumnya, form
            // inline yang hanya mengirim nilai_angka menyebabkan seluruh komponen
            // tugas/quiz/UTS/UAS/kehadiran ditimpa NULL.
            $updateData = [];

            foreach ([
                'tugas_1', 'tugas_2', 'tugas_3',
                'quiz_1', 'quiz_2',
                'uts', 'uas', 'praktikum', 'kehadiran',
                'bobot_tugas', 'bobot_quiz', 'bobot_uts',
                'bobot_uas', 'bobot_praktikum', 'bobot_kehadiran',
                'notes', 'nilai_remidi'
            ] as $field) {
                if ($request->has($field)) {
                    $updateData[$field] = $request->input($field);
                }
            }

            if ($request->has('is_remidi')) {
                $updateData['is_remidi'] = $request->boolean('is_remidi');
            }
            if ($request->has('is_susulan')) {
                $updateData['is_susulan'] = $request->boolean('is_susulan');
            }

            // Nilai akhir dari input langsung tetap didukung. Jika hanya
            // nilai_angka yang dikirim, jangan dihitung ulang dari komponen NULL.
            $directScore = $request->has('nilai_angka');
            $componentUpdate = collect([
                'tugas_1','tugas_2','tugas_3','quiz_1','quiz_2',
                'uts','uas','praktikum','kehadiran'
            ])->contains(fn ($field) => $request->has($field));

            if ($directScore && !$componentUpdate) {
                $score = $request->input('nilai_angka');
                $updateData['nilai_angka'] = $score;

                $huruf = 'E';
                foreach (Nilai::NILAI_HURUF_MAP as $grade => $range) {
                    if ((float) $score >= $range['min'] && (float) $score <= $range['max']) {
                        $huruf = $grade;
                        break;
                    }
                }
                $updateData['nilai_huruf'] = $huruf;
                $updateData['nilai_mutu'] = Nilai::NILAI_HURUF_MAP[$huruf]['mutu'];
                $updateData['mutu_x_sks'] = $updateData['nilai_mutu'] * (float) $nilai->sks;
            }

            $updateData['updated_by'] = Auth::id();
            $nilai->update($updateData);

            // Hitung ulang hanya jika komponen nilai memang diubah.
            if ($componentUpdate) {
                $nilai->refresh();
                $nilai->hitungNilaiAkhir();
            }

            DB::commit();
            Alert::success('Success', 'Nilai berhasil diperbarui');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal memperbarui nilai: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function publishNilai($code)
    {
        try {
            DB::beginTransaction();

            $nilai = ($this->isDosen()
                ? $this->nilaiDosen($this->dosenId())
                : Nilai::query()
            )->where('code', $code)->firstOrFail();

            if ($nilai->status !== 'Draft') {
                Alert::error('Error', 'Nilai sudah dipublish atau dikunci');
                return redirect()->back();
            }

            // Hitung ulang nilai akhir dari seluruh komponen sebelum publish.
            // Nilai 0 adalah nilai yang sah, jadi jangan dianggap sebagai "belum diisi".
            $nilai->refresh();
            $nilai->hitungNilaiAkhir();
            $nilai->refresh();

            // Kehadiran ditetapkan 15%. Lima komponen akademik harus
            // bersama-sama menjadi 80%. Data lama yang masih memakai
            // bobot akademik 95% akan dinormalisasi menjadi 80%.
            $bobotKehadiran = (float) ($nilai->bobot_kehadiran ?? 15);
            $bobotAkademik = (float) $nilai->bobot_tugas
                + (float) $nilai->bobot_quiz
                + (float) $nilai->bobot_uts
                + (float) $nilai->bobot_uas
                + (float) $nilai->bobot_praktikum;

            if (abs($bobotKehadiran - 15) <= 0.01 && abs($bobotAkademik - 85) <= 0.01) {
                $skala = 85 / $bobotAkademik;

                $nilai->bobot_tugas = round((float) $nilai->bobot_tugas * $skala, 2);
                $nilai->bobot_quiz = round((float) $nilai->bobot_quiz * $skala, 2);
                $nilai->bobot_uts = round((float) $nilai->bobot_uts * $skala, 2);
                $nilai->bobot_uas = round((float) $nilai->bobot_uas * $skala, 2);
                $nilai->bobot_praktikum = round((float) $nilai->bobot_praktikum * $skala, 2);

                // Koreksi pembulatan pada komponen terakhir agar tepat 80%.
                $jumlahAkademikBaru = (float) $nilai->bobot_tugas
                    + (float) $nilai->bobot_quiz
                    + (float) $nilai->bobot_uts
                    + (float) $nilai->bobot_uas
                    + (float) $nilai->bobot_praktikum;

                $nilai->bobot_praktikum = round(
                    (float) $nilai->bobot_praktikum + (85 - $jumlahAkademikBaru),
                    2
                );
                $nilai->bobot_kehadiran = 15;
                $nilai->saveQuietly();
                $nilai->refresh();
            }

            // Validasi akhir: bobot akademik 85% + kehadiran 15% = 100%.
            $totalBobot = (float) $nilai->bobot_tugas
                + (float) $nilai->bobot_quiz
                + (float) $nilai->bobot_uts
                + (float) $nilai->bobot_uas
                + (float) $nilai->bobot_praktikum
                + (float) $nilai->bobot_kehadiran;

            if (abs($totalBobot - 100) > 0.01) {
                Alert::error('Error', 'Nilai belum lengkap. Total bobot harus 100%. Saat ini: ' . $totalBobot . '%. Bobot akademik harus 85% dan kehadiran 15%.');
                return redirect()->back();
            }

            // Nilai 0 tetap valid. Yang dianggap belum lengkap hanya jika
            // nilai akhir benar-benar tidak terbentuk (NULL).
            if (is_null($nilai->nilai_angka)) {
                Alert::error('Error', 'Nilai belum lengkap. Pastikan komponen nilai sudah diisi dan disimpan.');
                return redirect()->back();
            }

            $nilai->publish();

            DB::commit();
            Alert::success('Success', 'Nilai berhasil dipublish');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal publish nilai: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function lockNilai($code)
    {
        try {
            DB::beginTransaction();

            $nilai = Nilai::where('code', $code)->firstOrFail();

            if ($nilai->status !== 'Published') {
                Alert::error('Error', 'Nilai harus dipublish terlebih dahulu sebelum dikunci');
                return redirect()->back();
            }

            $nilai->lock();

            DB::commit();
            Alert::success('Success', 'Nilai berhasil dikunci');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal mengunci nilai: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function unlockNilai($code)
    {
        try {
            DB::beginTransaction();

            $nilai = Nilai::where('code', $code)->firstOrFail();

            if ($nilai->status !== 'Locked') {
                Alert::error('Error', 'Nilai tidak dalam status terkunci');
                return redirect()->back();
            }

            $nilai->unlock();

            DB::commit();
            Alert::success('Success', 'Nilai berhasil dibuka kunci');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal membuka kunci nilai: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function deleteNilai($code)
    {
        try {
            DB::beginTransaction();

            $nilai = Nilai::where('code', $code)->firstOrFail();

            // Hanya bisa hapus jika status Draft
            if ($nilai->status !== 'Draft') {
                Alert::error('Error', 'Hanya nilai dengan status Draft yang dapat dihapus');
                return redirect()->back();
            }

            $nilai->delete();

            DB::commit();
            Alert::success('Success', 'Nilai berhasil dihapus');
            return redirect()->route(Auth::user()->prefix . 'akademik.nilai-render');

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal menghapus nilai: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    // BULK OPERATIONS
    public function bulkPublishNilai(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'nilai_ids' => 'required|array',
                'nilai_ids.*' => 'exists:nilais,id',
            ]);

            $nilaiList = Nilai::whereIn('id', $request->nilai_ids)
                ->where('status', 'Draft')
                ->get();

            foreach ($nilaiList as $nilai) {
                if (!is_null($nilai->nilai_angka) && $nilai->nilai_angka > 0) {
                    $nilai->publish();
                }
            }

            DB::commit();
            Alert::success('Success', 'Nilai terpilih berhasil dipublish');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal publish nilai: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function importNilai(Request $request)
    {
        // Wrapper for importNilaiFromKRS
        return $this->importNilaiFromKRS($request);
    }

    public function importNilaiFromKRS(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'krs_id' => 'required|exists:k_r_s,id',
            ]);

            $krs = \App\Models\Akademik\KRS::with('detailsAktif.mataKuliah')->find($request->krs_id);

            $created = 0;
            foreach ($krs->detailsAktif as $detail) {
                $existingNilai = Nilai::where('mahasiswa_id', $krs->mahasiswa_id)
                    ->where('matkul_id', $detail->matkul_id)
                    ->where('taka_id', $krs->taka_id)
                    ->where('semester', $krs->semester)
                    ->first();

                if (!$existingNilai) {
                    Nilai::create([
                        'code' => 'NIL-' . date('Ymd') . '-' . Str::random(8),
                        'mahasiswa_id' => $krs->mahasiswa_id,
                        'matkul_id' => $detail->matkul_id,
                        'krs_detail_id' => $detail->id,
                        'taka_id' => $krs->taka_id,
                        'semester' => $krs->semester,
                        'sks' => $detail->sks,
                        'created_by' => Auth::id(),
                    ]);
                    $created++;
                }
            }

            DB::commit();
            Alert::success('Success', "Berhasil membuat {$created} data nilai dari KRS");
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal import nilai dari KRS: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
    public function exportNilai(Request $request)
    {
        $nilai = Nilai::with(['mahasiswa','mataKuliah','tahunAkademik'])->get();
        $rows = $nilai->map(fn($n)=>['NIM'=>$n->mahasiswa->numb_nim ?? $n->mahasiswa->nim ?? '', 'Mahasiswa'=>$n->mahasiswa->name ?? '', 'Kode MK'=>$n->mataKuliah->code ?? '', 'Mata Kuliah'=>$n->mataKuliah->name ?? '', 'Semester'=>$n->semester, 'Nilai'=>$n->nilai_angka, 'Huruf'=>$n->nilai_huruf, 'Mutu'=>$n->nilai_mutu]);
        return response()->streamDownload(function() use ($rows){ $out=fopen('php://output','w'); if($rows->isNotEmpty()) fputcsv($out,array_keys($rows->first())); foreach($rows as $r) fputcsv($out,$r); fclose($out); }, 'nilai.csv', ['Content-Type'=>'text/csv']);
    }

    public function bulkUpdate(Request $request)
    {
        $codes=(array)$request->input('codes',$request->input('nilai_codes',[]));
        $data=$request->only(['status','nilai_angka','nilai_huruf','nilai_mutu']);
        foreach($codes as $code){$n=Nilai::where('code',$code)->first(); if($n && $n->status==='Draft') {$n->fill(array_filter($data,fn($v)=>$v!==null)); $n->save();}}
        return redirect()->back()->with('success','Nilai terpilih diperbarui.');
    }

    public function approveNilai($code)
    { return $this->publishNilai($code); }

}
