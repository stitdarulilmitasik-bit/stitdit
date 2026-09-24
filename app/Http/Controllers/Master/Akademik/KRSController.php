<?php

namespace App\Http\Controllers\Master\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// Use System
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
// Use Models
use App\Models\Akademik\KRS;
use App\Models\Jabatan;
use App\Models\Akademik\KrsDetail;
use App\Models\Akademik\MataKuliah;
use App\Models\Akademik\Kelas;
use App\Models\Akademik\JadwalKuliah;
use App\Models\Akademik\TahunAkademik;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Pengaturan\WebSetting;
// Use Plugins
use Alert;
use Barryvdh\DomPDF\Facade\Pdf;

class KRSController extends Controller
{
    public function renderKRS()
    {
        $user = Auth::user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $user ? $user->prefix : '';
        $data['menus'] = "Master";
        $data['pages'] = "KRS (Kartu Rencana Studi)";
        $data['academy'] = $data['webs']->school_apps . ' by ' . $data['webs']->school_name;

        $data['krs_list'] = KRS::with(['mahasiswa', 'tahunAkademik', 'dosenPA'])
            ->latest()
            ->paginate(20);
        $data['tahun_akademik'] = TahunAkademik::all();
        $data['mahasiswa'] = Mahasiswa::where('type', 1)->get();
        $data['dosens'] = Dosen::where('type', 1)->get();

        return view('master.akademik.krs-index', $data, compact('user'));
    }

    public function viewKRS($code)
    {
        $user = Auth::user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $user ? $user->prefix : '';
        $data['menus'] = "Master";
        $data['pages'] = "Detail KRS";
        $data['academy'] = $data['webs']->school_apps . ' by ' . $data['webs']->school_name;

        $data['krs'] = KRS::with(['mahasiswa', 'tahunAkademik', 'dosenPA', 'details.mataKuliah', 'details.kelas.jadwalKuliah.waktuKuliah', 'details.kelas.jadwalKuliah.ruang', 'details.dosen'])
            ->where('code', $code)
            ->firstOrFail();

        // KRS lama bisa memiliki mahasiswa yang sudah tidak tersedia.
        // Tetap tampilkan detail KRS tanpa memicu error saat relasi mahasiswa null.
        $data['available_matakuliah'] = $data['krs']->mahasiswa
            ? MataKuliah::where('prodi_id', $data['krs']->mahasiswa->prodi_id)->get()
            : collect();
        $data['kelas'] = Kelas::all();
        $data['jadwal_kuliah'] = JadwalKuliah::with(['kelas', 'dosen', 'waktuKuliah', 'ruang'])
            ->whereIn('matkul_id', $data['available_matakuliah']->pluck('id'))
            ->get();
        $data['dosens'] = Dosen::where('type', 1)->get();

        return view('master.akademik.krs-detail', $data, compact('user'));
    }

    public function handleKRS(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'mahasiswa_id' => 'required|exists:mahasiswas,id',
                'tahun_akademik_id' => 'required|exists:tahun_akademiks,id',
                'semester' => 'required|integer|min:1|max:14',
                'dosen_pa_id' => 'nullable|exists:dosens,id',
                'periode_mulai' => 'nullable|date',
                'periode_selesai' => 'nullable|date|after:periode_mulai',
                'notes' => 'nullable|string',
            ]);

            $existingKRS = KRS::where('mahasiswa_id', $request->mahasiswa_id)
                ->where('taka_id', $request->tahun_akademik_id)
                ->where('semester', $request->semester)
                ->first();

            if ($existingKRS) {
                Alert::error('Error', 'KRS untuk mahasiswa, tahun akademik, dan semester ini sudah ada');
                return redirect()->back()->withInput();
            }

            $mahasiswa = Mahasiswa::find($request->mahasiswa_id);
            $ipkSebelumnya = $this->getIPKSebelumnya($mahasiswa->id, $request->semester);

            $nim = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $mahasiswa->numb_nim);
            if ($nim === '') {
                throw new \RuntimeException('NIM mahasiswa tidak tersedia sehingga kode KRS tidak dapat dibuat.');
            }

            $krs = KRS::create([
                'code' => 'KRS-' . date('Ymd') . '-' . $nim . '-S' . $request->semester,
                'mahasiswa_id' => $request->mahasiswa_id,
                'taka_id' => $request->tahun_akademik_id,
                'semester' => $request->semester,
                'dosen_pa_id' => $request->dosen_pa_id,
                'periode_mulai' => $request->periode_mulai,
                'periode_selesai' => $request->periode_selesai,
                'notes' => $request->notes,
                'ipk_sebelumnya' => $ipkSebelumnya,
                'max_sks' => $this->hitungBatasSKS($ipkSebelumnya),
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            Alert::success('Success', 'KRS berhasil dibuat');
            return redirect()->route(Auth::user()->prefix . 'akademik.krs-view', $krs->code);

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal membuat KRS: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function updateKRS(Request $request, $code)
    {
        try {
            DB::beginTransaction();

            $krs = KRS::where('code', $code)->firstOrFail();

            if (!$krs->is_editable) {
                Alert::error('Error', 'KRS tidak dapat diedit. Perubahan hanya dapat dilakukan saat KRS berstatus Draft atau Diajukan.');
                return redirect()->back();
            }

            $request->validate([
                'dosen_pa_id' => 'nullable|exists:dosens,id',
                'periode_mulai' => 'nullable|date',
                'periode_selesai' => 'nullable|date|after:periode_mulai',
                'notes' => 'nullable|string',
                'status' => 'nullable|in:Draft,Diajukan,Disetujui,Ditolak,Dikunci,Dicetak',
            ]);

            $krs->update([
                'dosen_pa_id' => $request->dosen_pa_id,
                'periode_mulai' => $request->periode_mulai,
                'periode_selesai' => $request->periode_selesai,
                'notes' => $request->notes,
                'status' => $request->status ?: $krs->status,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();
            Alert::success('Success', 'KRS berhasil diperbarui');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal memperbarui KRS: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function addMatakuliah(Request $request, $code)
    {
        try {
            DB::beginTransaction();

            $krs = KRS::where('code', $code)->firstOrFail();

            if (!$krs->is_editable) {
                Alert::error('Error', 'KRS tidak dapat diedit');
                return redirect()->back();
            }

            $request->validate([
                'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
                'kelas_id' => 'required|exists:kelas,id',
                'jadwal_kuliah_id' => 'required|exists:jadwal_kuliahs,id',
                'notes' => 'nullable|string',
            ]);

            $existingDetail = KrsDetail::where('krs_id', $krs->id)
                ->where('matkul_id', $request->mata_kuliah_id)
                ->where('status', 'Aktif')
                ->first();

            if ($existingDetail) {
                Alert::error('Error', 'Mata kuliah sudah diambil dalam KRS ini');
                return redirect()->back();
            }

            $mataKuliah = MataKuliah::findOrFail($request->mata_kuliah_id);
            $jadwalKuliah = JadwalKuliah::with(['kelas', 'dosen'])
                ->where('id', $request->jadwal_kuliah_id)
                ->where('matkul_id', $mataKuliah->id)
                ->firstOrFail();

            if (!$jadwalKuliah->kelas->contains('id', (int) $request->kelas_id)) {
                throw new \Exception('Kelas yang dipilih tidak terdaftar pada jadwal mata kuliah tersebut.');
            }

            $dosenId = $jadwalKuliah->dosen_id;

            if (!$krs->canAddMatakuliah($mataKuliah->sks)) {
                Alert::error('Error', 'Menambah mata kuliah ini akan melebihi batas SKS yang diizinkan (' . $krs->batas_sks . ' SKS)');
                return redirect()->back();
            }

            KrsDetail::create([
                'code' => 'KRSD-' . date('Ymd') . '-' . Str::random(6),
                'krs_id' => $krs->id,
                'matkul_id' => $request->mata_kuliah_id,
                'kelas_id' => $request->kelas_id,
                'jadwal_kuliah_id' => $jadwalKuliah->id,
                'dosen_id' => $dosenId,
                'sks' => $mataKuliah->sks,
                'notes' => $request->notes,
                'prasyarat_terpenuhi' => true,
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            Alert::success('Success', 'Mata kuliah berhasil ditambahkan ke KRS');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal menambahkan mata kuliah: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function updateMatakuliah(Request $request, $code, $detailId)
    {
        try {
            DB::beginTransaction();

            $krs = KRS::where('code', $code)->firstOrFail();
            if (!$krs->is_editable) {
                Alert::error('Error', 'KRS tidak dapat diedit karena sudah disetujui atau dikunci');
                return redirect()->back();
            }

            $detail = KrsDetail::where('id', $detailId)->where('krs_id', $krs->id)->firstOrFail();

            $request->validate([
                'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
                'kelas_id' => 'nullable|exists:kelas,id',
                'dosen_id' => 'nullable|exists:dosens,id',
                'notes' => 'nullable|string',
            ]);

            $mataKuliah = MataKuliah::findOrFail($request->mata_kuliah_id);

            $duplicate = KrsDetail::where('krs_id', $krs->id)
                ->where('matkul_id', $mataKuliah->id)
                ->where('id', '!=', $detail->id)
                ->where('status', 'Aktif')
                ->exists();

            if ($duplicate) {
                Alert::error('Error', 'Mata kuliah tersebut sudah ada dalam KRS ini.');
                return redirect()->back()->withInput();
            }

            $currentSks = $krs->details()
                ->where('id', '!=', $detail->id)
                ->whereIn('status', ['Aktif', 'Mengulang'])
                ->sum('sks');
            $newTotal = $currentSks + (int) $mataKuliah->sks;

            if ($newTotal > $krs->batas_sks) {
                Alert::error('Error', 'Perubahan mata kuliah melebihi batas maksimal ' . $krs->batas_sks . ' SKS.');
                return redirect()->back()->withInput();
            }

            $detail->update([
                'matkul_id' => $mataKuliah->id,
                'kelas_id' => $request->kelas_id,
                'dosen_id' => $request->dosen_id,
                'sks' => $mataKuliah->sks,
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();
            Alert::success('Success', 'Mata kuliah dalam KRS berhasil diperbarui.');
            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollBack();
            Alert::error('Error', 'Gagal memperbarui mata kuliah: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function removeMatakuliah($code, $detailId)
    {
        try {
            DB::beginTransaction();

            $krs = KRS::where('code', $code)->firstOrFail();
            $krsDetail = KrsDetail::where('id', $detailId)
                ->where('krs_id', $krs->id)
                ->firstOrFail();

            if (!$krs->is_editable) {
                Alert::error('Error', 'KRS tidak dapat diedit');
                return redirect()->back();
            }

            $krsDetail->delete();

            DB::commit();
            Alert::success('Success', 'Mata kuliah berhasil dihapus dari KRS');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal menghapus mata kuliah dari KRS: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function approveKRS(Request $request, $code)
    {
        try {
            DB::beginTransaction();

            $krs = KRS::where('code', $code)->firstOrFail();

            if (!$krs->is_approvable) {
                Alert::error('Error', 'KRS tidak dapat disetujui');
                return redirect()->back();
            }

            $request->validate([
                'notes' => 'nullable|string',
                'dosen_pa_id' => 'nullable|exists:dosens,id',
            ]);

            $krs->approve($request->dosen_pa_id, $request->notes);

            DB::commit();
            Alert::success('Success', 'KRS berhasil disetujui');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal menyetujui KRS: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function rejectKRS(Request $request, $code)
    {
        try {
            DB::beginTransaction();

            $krs = KRS::where('code', $code)->firstOrFail();

            if (!$krs->is_approvable) {
                Alert::error('Error', 'KRS tidak dapat ditolak');
                return redirect()->back();
            }

            $request->validate([
                'notes' => 'nullable|string',
            ]);

            $krs->reject($request->notes ?: $request->reason);

            DB::commit();
            Alert::success('Success', 'KRS berhasil ditolak');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal menolak KRS: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function deleteKRS($code)
    {
        try {
            DB::beginTransaction();

            $krs = KRS::where('code', $code)->firstOrFail();

            if (strtolower(trim((string) $krs->status)) !== 'draft') {
                Alert::error('Error', 'Hanya KRS dengan status Draft yang dapat dihapus');
                return redirect()->back();
            }

            $krs->delete();

            DB::commit();
            Alert::success('Success', 'KRS berhasil dihapus');
            return redirect()->route(Auth::user()->prefix . 'akademik.krs-render');

        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal menghapus KRS: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function printKRS($code, bool $stream = false)
    {
        $krs = KRS::with([
            'mahasiswa.programStudi.fakultas',
            'mahasiswa.tahunAkademikRegistrasi',
            'tahunAkademik',
            'dosenPA',
            'details.mataKuliah',
            'details.kelas.jadwalKuliah.ruang',
            'details.dosen',
        ])->where('code', $code)->firstOrFail();

        if (!$krs->mahasiswa) {
            return redirect()
                ->route((Auth::user()?->prefix ?? '') . 'akademik.krs-render')
                ->with('error', 'KRS ini tidak dapat dicetak karena data mahasiswa sudah tidak ditemukan.');
        }

        $kaprodi = Jabatan::with('dosen')
            ->whereIn('name', ['Ketua Program Studi', 'Ketua Prodi'])
            ->where('prodi_id', $krs->mahasiswa->prodi_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();

        $webs = WebSetting::first();

        // KRS PDF uses a logo copied into the public tree so Dompdf can read it
        // as a local file on shared hosting. Storage paths may be blocked by
        // open_basedir and remote URLs may be disabled by the PDF renderer.
        $logoDataUri = null;
        $logoPath = public_path('images/logo/logo-vert1.png');
        if (is_file($logoPath) && is_readable($logoPath)) {
            try {
                $bytes = file_get_contents($logoPath);
                if (is_string($bytes) && $bytes !== '') {
                    $logoDataUri = 'data:image/png;base64,' . base64_encode($bytes);
                }
            } catch (\Throwable $e) {
                $logoDataUri = null;
            }
        }

        $data = [
            'krs' => $krs,
            'webs' => $webs,
            'kaprodi' => $kaprodi?->dosen,
            'ketuaSTIT' => Jabatan::with('dosen')
                ->whereIn('name', ['Ketua STIT', 'Ketua'])
                ->whereNull('prodi_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->first()?->dosen,
            'logoDataUri' => $logoDataUri,
        ];

        $pdf = Pdf::loadView('master.akademik.krs-print', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Helvetica',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
                'dpi' => 96,
                'enable_font_subsetting' => true,
            ]);

        $filename = 'KRS-' . preg_replace(
            '/[^A-Za-z0-9_-]+/',
            '-',
            $krs->mahasiswa->name ?? $krs->mahasiswa->numb_nim ?? $krs->code
        ) . '.pdf';

        return $stream ? $pdf->stream($filename) : $pdf->download($filename);
    }

    public function previewKRS($code)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if (($user->prefix ?? '') === 'dosen') {
            $dosen = Dosen::where('user_id', $user->id)->first()
                ?? Dosen::where('id', $user->id)->first();

            abort_unless(
                $dosen && KrsDetail::where('dosen_id', $dosen->id)
                    ->whereHas('krs', fn ($q) => $q->where('code', $code))
                    ->exists(),
                403
            );
        }

        return $this->printKRS($code, true);
    }

    public function detailKRS($code)
    {
        return $this->viewKRS($code);
    }

    private function getIPKSebelumnya($mahasiswaId, $semester)
    {
        if ($semester <= 1) {
            return 0.00;
        }

        $khsSebelumnya = \App\Models\Akademik\KHS::byMahasiswa($mahasiswaId)
            ->where('semester', $semester - 1)
            ->orderBy('semester', 'desc')
            ->first();

        return $khsSebelumnya ? $khsSebelumnya->ipk : 0.00;
    }

    private function hitungBatasSKS($ipk)
    {
        if ($ipk >= 3.50) return 24;
        if ($ipk >= 3.00) return 22;
        if ($ipk >= 2.50) return 20;
        if ($ipk >= 2.00) return 18;
        return 15;
    }

    public function publishKRS($code)
    {
        $krs = KRS::where('code',$code)->firstOrFail();
        if (strtolower(trim((string) $krs->status)) !== 'disetujui') {
            return redirect()->back()->with('error','KRS belum siap dipublish.');
        }
        $krs->update(['status'=>'Dicetak', 'updated_by' => Auth::id()]);
        return redirect()->back()->with('success','KRS berhasil dipublish.');
    }

    public function lockKRS($code)
    {
        $krs = KRS::where('code',$code)->firstOrFail();
        if (strtolower(trim((string) $krs->status)) !== 'disetujui') return redirect()->back()->with('error','KRS harus disetujui terlebih dahulu.');
        $krs->update(['status' => 'Dikunci', 'updated_by' => Auth::id()]);
        return redirect()->back()->with('success','KRS berhasil dikunci. Status menjadi Dikunci.');
    }

    public function bulkApprove(Request $request)
    {
        $ids = $request->input('codes', $request->input('krs_codes', []));
        $ids = array_values(array_filter((array) $ids));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada KRS yang dipilih.');
        }

        $processed = 0;
        $skipped = 0;

        DB::transaction(function () use ($ids, &$processed, &$skipped) {
            $krsList = KRS::whereIn('code', $ids)->get();

            foreach ($krsList as $krs) {
                if (strtolower(trim((string) $krs->status)) !== 'diajukan') {
                    $skipped++;
                    continue;
                }

                $krs->approve();
                $processed++;
            }
        });

        if ($processed === 0) {
            return redirect()->back()->with(
                'error',
                'Tidak ada KRS yang dapat disetujui. Pastikan status KRS adalah Diajukan.'
            );
        }

        $message = $processed . ' KRS berhasil disetujui dan statusnya menjadi Disetujui.';
        if ($skipped > 0) {
            $message .= ' ' . $skipped . ' KRS dilewati karena statusnya bukan Diajukan.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function bulkPublish(Request $request)
    {
        $ids = $request->input('codes', $request->input('krs_codes', []));
        KRS::whereIn('code',(array)$ids)->where('status','Disetujui')->update(['status'=>'Dikunci', 'updated_by' => Auth::id()]);
        return redirect()->back()->with('success','KRS terpilih berhasil dikunci.');
    }

    /**
     * Menyalin satu atau beberapa KRS terpilih sebagai template ke mahasiswa lain.
     * Detail KRS digabung, duplikat mata kuliah di target diabaikan.
     * KRS target yang sudah diajukan/disetujui/dipublish/dikunci tidak diubah.
     */
    public function copyBulkKrs(Request $request)
    {
        $request->validate([
            'source_codes' => 'required|array|min:1',
            'source_codes.*' => 'string',
            'target_mahasiswa_ids' => 'required|array|min:1',
            'target_mahasiswa_ids.*' => 'integer|exists:mahasiswas,id',
        ]);

        $sourceCodes = array_values(array_unique(array_filter($request->input('source_codes', []))));
        $targetIds = array_values(array_unique(array_map('intval', $request->input('target_mahasiswa_ids', []))));

        if (!$sourceCodes || !$targetIds) {
            return redirect()->back()->with('error', 'Pilih KRS sumber dan minimal satu mahasiswa tujuan.');
        }

        $dosen = Auth::guard('dosen')->user();
        $sourceQuery = KRS::with('details')
            ->whereIn('code', $sourceCodes);

        // Jika dijalankan dari akun Dosen, hanya KRS yang memiliki
        // mata kuliah yang benar-benar diampu dosen tersebut yang boleh
        // dijadikan template.
        if ($dosen) {
            $sourceQuery->whereHas('details', function ($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id)
                    ->orWhereHas('mataKuliah', function ($mk) use ($dosen) {
                        $mk->where('dosen1_id', $dosen->id)
                            ->orWhere('dosen2_id', $dosen->id)
                            ->orWhere('dosen3_id', $dosen->id);
                    });
            });
        }

        $sources = $sourceQuery->get();

        if ($sources->isEmpty()) {
            return redirect()->back()->with('error', 'KRS sumber tidak ditemukan atau bukan KRS yang dapat digunakan oleh dosen ini.');
        }

        $targetStudents = Mahasiswa::whereIn('id', $targetIds)
            ->where('type', 1)
            ->get();

        $createdKrs = 0;
        $addedDetails = 0;
        $skippedTargets = 0;
        $skippedDetails = 0;
        $sourceDetailCount = 0;

        DB::transaction(function () use ($sources, $targetStudents, &$createdKrs, &$addedDetails, &$skippedTargets, &$skippedDetails, &$sourceDetailCount) {
            foreach ($sources as $source) {
                foreach ($source->details->whereIn('status', ['Aktif', 'Mengulang']) as $detail) {
                    $sourceDetailCount++;

                    foreach ($targetStudents as $student) {
                        // Jangan menyalin KRS mahasiswa ke dirinya sendiri.
                        if ((int) $student->id === (int) $source->mahasiswa_id) {
                            $skippedTargets++;
                            continue;
                        }

                        $rawStatus = $source->getRawOriginal('status') ?: 'draft';
                        $target = KRS::firstOrCreate(
                            [
                                'mahasiswa_id' => $student->id,
                                'taka_id' => $source->taka_id,
                                'semester' => $source->semester,
                            ],
                            [
                                'code' => 'KRS-' . date('Ymd') . '-' . preg_replace('/[^A-Za-z0-9_-]/', '', (string) $student->numb_nim) . '-S' . $source->semester . '-' . Str::upper(Str::random(4)),
                                'dosen_pa_id' => $source->dosen_pa_id,
                                'periode_mulai' => $source->periode_mulai,
                                'periode_selesai' => $source->periode_selesai,
                                'notes' => 'Template KRS dari ' . ($source->mahasiswa->name ?? $source->code),
                                'ipk_sebelumnya' => $source->ipk_sebelumnya,
                                'max_sks' => $source->max_sks ?: 15,
                                'status' => 'Draft',
                                'created_by' => Auth::id(),
                            ]
                        );

                        $targetStatus = $target->getRawOriginal('status');
                        if (in_array($targetStatus, ['Diajukan', 'Disetujui', 'Dikunci', 'Dicetak'], true)) {
                            $skippedTargets++;
                            continue;
                        }

                        $exists = $target->details()
                            ->where('matkul_id', $detail->matkul_id)
                            ->whereIn('status', ['Aktif', 'Mengulang'])
                            ->exists();

                        if ($exists) {
                            $skippedDetails++;
                            continue;
                        }

                        $target->details()->create([
                            'code' => 'KRSD-' . date('Ymd') . '-' . Str::upper(Str::random(8)),
                            'matkul_id' => $detail->matkul_id,
                            'kelas_id' => $detail->kelas_id,
                            'jadwal_kuliah_id' => $detail->jadwal_kuliah_id,
                            'dosen_id' => $detail->dosen_id,
                            'sks' => $detail->sks,
                            'notes' => $detail->notes,
                            'prasyarat_terpenuhi' => $detail->prasyarat_terpenuhi ?? true,
                            'status' => 'Aktif',
                            'created_by' => Auth::id(),
                        ]);

                        $target->hitungTotalSks();
                        $addedDetails++;
                    }
                }
            }
        });

        if ($addedDetails === 0) {
            return redirect()->back()->with('error', 'Tidak ada mata kuliah yang disalin. Periksa KRS tujuan atau status KRS tujuan.');
        }

        $message = $addedDetails . ' mata kuliah berhasil disalin ke ' . $targetStudents->count() . ' mahasiswa sebagai template KRS.';
        if ($createdKrs > 0) {
            $message .= ' ' . $createdKrs . ' KRS baru dibuat.';
        }
        if ($skippedDetails > 0) {
            $message .= ' ' . $skippedDetails . ' mata kuliah dilewati karena sudah ada.';
        }
        if ($skippedTargets > 0) {
            $message .= ' Beberapa KRS tujuan dilewati karena sudah diajukan, disetujui, dipublish, atau dikunci.';
        }

        return redirect()->back()->with('success', $message);
    }
}
