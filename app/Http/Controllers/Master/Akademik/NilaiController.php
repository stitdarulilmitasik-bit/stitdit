<?php

namespace App\Http\Controllers\Master\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// Use System
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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
        $user = Auth::user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $user ? $user->prefix : '';
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
            ]);

            // Cek apakah nilai untuk kombinasi ini sudah ada
            $existingNilai = Nilai::where('mahasiswa_id', $request->mahasiswa_id)
                ->where('matkul_id', $request->matkul_id)
                ->where('taka_id', $request->tahun_akademik_id)
                ->where('semester', $request->semester)
                ->first();

            if ($existingNilai) {
                Alert::error('Error', 'Nilai untuk kombinasi mahasiswa, mata kuliah, tahun akademik, dan semester ini sudah ada');
                return redirect()->back()->withInput();
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

            $nilai = Nilai::create([
                'code' => 'NIL-' . date('Ymd') . '-' . Str::random(8),
                'mahasiswa_id' => $request->mahasiswa_id,
                'matkul_id' => $request->matkul_id,
                'taka_id' => $request->tahun_akademik_id,
                'semester' => $request->semester,
                'krs_detail_id' => $request->krs_detail_id,
                'sks' => $mataKuliah->sks,
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            Alert::success('Success', 'Data nilai berhasil dibuat');
            return redirect()->route(Auth::user()->prefix . 'akademik.nilai-view', $nilai->code);

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

            $nilai->update([
                'tugas_1' => $request->tugas_1,
                'tugas_2' => $request->tugas_2,
                'tugas_3' => $request->tugas_3,
                'quiz_1' => $request->quiz_1,
                'quiz_2' => $request->quiz_2,
                'uts' => $request->uts,
                'uas' => $request->uas,
                'praktikum' => $request->praktikum,
                'kehadiran' => $request->kehadiran,
                'bobot_tugas' => $request->bobot_tugas ?? $nilai->bobot_tugas,
                'bobot_quiz' => $request->bobot_quiz ?? $nilai->bobot_quiz,
                'bobot_uts' => $request->bobot_uts ?? $nilai->bobot_uts,
                'bobot_uas' => $request->bobot_uas ?? $nilai->bobot_uas,
                'bobot_praktikum' => $request->bobot_praktikum ?? $nilai->bobot_praktikum,
                'bobot_kehadiran' => $request->bobot_kehadiran ?? $nilai->bobot_kehadiran,
                'notes' => $request->notes,
                'is_remidi' => $request->boolean('is_remidi'),
                'nilai_remidi' => $request->nilai_remidi,
                'is_susulan' => $request->boolean('is_susulan'),
                'updated_by' => Auth::id(),
            ]);

            // Hitung ulang nilai akhir
            $nilai->hitungNilaiAkhir();

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

            // Validasi nilai sudah lengkap
            if (is_null($nilai->nilai_angka) || $nilai->nilai_angka == 0) {
                Alert::error('Error', 'Nilai belum lengkap. Pastikan semua komponen nilai sudah diisi');
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
