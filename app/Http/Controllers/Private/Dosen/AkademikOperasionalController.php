<?php

namespace App\Http\Controllers\Private\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Akademik\JadwalKuliah;
use App\Models\Akademik\KRS;
use App\Models\Akademik\KrsDetail;
use App\Models\Akademik\Nilai;
use App\Models\Akademik\KehadiranMahasiswa;
use App\Models\Akademik\MataKuliah;
use App\Models\Akademik\Kelas;
use App\Models\Akademik\JenisKelas;
use App\Models\Akademik\WaktuKuliah;
use App\Models\Infrastruktur\Ruang;
use App\Models\Pengaturan\WebSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class AkademikOperasionalController extends Controller
{
    private function dosen()
    {
        $dosen = Auth::guard('dosen')->user();
        abort_unless($dosen, 403);
        return $dosen;
    }

    private function base($page)
    {
        $webs = WebSetting::first();
        return [
            'user' => $this->dosen(),
            'webs' => $webs,
            'spref' => 'dosen.',
            'menus' => 'Akademik',
            'pages' => $page,
            'academy' => $webs ? $webs->school_apps . ' by ' . $webs->school_name : 'SIAKAD',
        ];
    }

    /**
     * Mata kuliah yang benar-benar diampu oleh Dosen ditentukan dari
     * dosen1_id/dosen2_id/dosen3_id pada master MataKuliah.
     */
    private function mataKuliahDiampu($dosenId)
    {
        return function ($query) use ($dosenId) {
            $query->where(function ($q) use ($dosenId) {
                $q->where('dosen1_id', $dosenId)
                    ->orWhere('dosen2_id', $dosenId)
                    ->orWhere('dosen3_id', $dosenId);
            });
        };
    }

    /**
     * Sinkronkan KRS yang sudah disetujui ke tabel nilais.
     * Ini juga menangani KRS yang sudah approved sebelum fitur sinkronisasi dibuat.
     */
    private function syncNilaiDosen($dosenId)
    {
        // Sinkronisasi berdasarkan MATA KULIAH yang benar-benar diampu dosen.
        // Tidak bergantung pada dosen_id di krs_details, karena data KRS lama
        // dapat saja belum memiliki dosen_id yang sesuai dengan master mata kuliah.
        KRS::whereIn('status', ['Disetujui'])
            ->whereHas('details', function ($q) use ($dosenId) {
                $q->whereIn('status', ['Aktif', 'Mengulang'])
                    ->whereHas('mataKuliah', $this->mataKuliahDiampu($dosenId));
            })
            ->with('details')
            ->get()
            ->each(fn($krs) => $krs->syncNilai());
    }

    public function jadwal()
    {
        $data = $this->base('Jadwal Kuliah');
        $dosenId = $data['user']->id;
        $data['jadwal'] = JadwalKuliah::with(['mataKuliah','kelas','ruang','jenisKelas','waktuKuliah'])->where('dosen_id', $dosenId)->latest()->get();
        $data['mata_kuliah'] = MataKuliah::where(function($q) use ($dosenId) {
            $q->where('dosen1_id', $dosenId)->orWhere('dosen2_id', $dosenId)->orWhere('dosen3_id', $dosenId);
        })->get();
        $data['kelas'] = Kelas::latest()->get();
        $data['jenis_kelas'] = JenisKelas::with('waktuKuliah')->get();
        $data['waktu_kuliah'] = WaktuKuliah::all();
        $data['ruang'] = Ruang::all();
        return view('private.dosen.akademik-jadwal', $data);
    }

    public function simpanJadwal(Request $request)
    {
        $dosen = $this->dosen();
        $request->validate([
            'ruang_id'=>'required|integer|exists:ruangs,id', 'matkul_id'=>'required|integer|exists:mata_kuliahs,id',
            'jenis_kelas_id'=>'required|integer|exists:jenis_kelas,id', 'waktu_kuliah_id'=>'required|integer|exists:waktu_kuliahs,id',
            'bsks'=>'required|integer|min:1|max:6', 'pertemuan'=>'required|integer|min:1', 'hari'=>'required|string',
            'metode'=>'required|string|in:Tatap Muka,Teleconference', 'tanggal'=>'required|date', 'link'=>'nullable|url',
            'kelas_ids'=>'required|array', 'kelas_ids.*'=>'integer|exists:kelas,id'
        ]);
        $allowed = MataKuliah::where('id',$request->matkul_id)->where(function($q) use ($dosen) {
            $q->where('dosen1_id',$dosen->id)->orWhere('dosen2_id',$dosen->id)->orWhere('dosen3_id',$dosen->id);
        })->exists();
        abort_unless($allowed, 403, 'Mata kuliah bukan mata kuliah yang diampu.');
        $jadwal = JadwalKuliah::create([
            'code'=>'JDW-'.Str::random(8), 'dosen_id'=>$dosen->id, 'ruang_id'=>$request->ruang_id, 'matkul_id'=>$request->matkul_id,
            'jenis_kelas_id'=>$request->jenis_kelas_id, 'waktu_kuliah_id'=>$request->waktu_kuliah_id, 'bsks'=>$request->bsks,
            'pertemuan'=>$request->pertemuan, 'hari'=>$request->hari, 'metode'=>$request->metode, 'tanggal'=>$request->tanggal,
            'link'=>$request->link, 'created_by'=>Auth::guard('dosen')->id()
        ]);
        $jadwal->kelas()->attach($request->kelas_ids);
        return back()->with('success','Jadwal kuliah berhasil ditambahkan.');
    }

    public function updateJadwal(Request $request, $code)
    {
        $dosen = $this->dosen();
        $jadwal = JadwalKuliah::where('code',$code)->where('dosen_id',$dosen->id)->firstOrFail();
        $request->validate([
            'ruang_id'=>'required|integer|exists:ruangs,id', 'matkul_id'=>'required|integer|exists:mata_kuliahs,id',
            'jenis_kelas_id'=>'required|integer|exists:jenis_kelas,id', 'waktu_kuliah_id'=>'required|integer|exists:waktu_kuliahs,id',
            'bsks'=>'required|integer|min:1|max:6', 'pertemuan'=>'required|integer|min:1', 'hari'=>'required|string',
            'metode'=>'required|string|in:Tatap Muka,Teleconference', 'tanggal'=>'required|date', 'link'=>'nullable|url',
            'kelas_ids'=>'required|array', 'kelas_ids.*'=>'integer|exists:kelas,id'
        ]);
        $jadwal->update($request->only(['ruang_id','matkul_id','jenis_kelas_id','waktu_kuliah_id','bsks','pertemuan','hari','metode','tanggal','link']) + ['updated_by'=>Auth::guard('dosen')->id()]);
        $jadwal->kelas()->sync($request->kelas_ids);
        return back()->with('success','Jadwal kuliah berhasil diperbarui.');
    }

    public function nilai()
    {
        $data = $this->base('Nilai Mahasiswa');
        $id = $data['user']->id;

        // Pastikan nilai mahasiswa untuk mata kuliah yang diampu tersedia
        // otomatis ketika halaman Nilai dibuka.
        $this->syncNilaiDosen($id);

        // Daftar mata kuliah diambil langsung dari master MataKuliah.
        // Jadi setiap Dosen hanya melihat mata kuliah yang tercatat pada
        // dosen1_id, dosen2_id, atau dosen3_id miliknya.
        $data['mata_kuliah'] = MataKuliah::where($this->mataKuliahDiampu($id))
            ->orderBy('code')
            ->get();

        // Nilai ditampilkan berdasarkan mata kuliah yang diampu.
        // Tidak lagi bergantung pada dosen_id di krs_details.
        $data['nilai'] = Nilai::with(['mahasiswa','mataKuliah','tahunAkademik','krsDetail'])
            ->whereHas('mataKuliah', $this->mataKuliahDiampu($id))
            ->latest()
            ->paginate(30);

        return view('private.dosen.akademik-nilai', $data);
    }

    public function kehadiran(Request $request)
    {
        $data = $this->base('Input Kehadiran Mahasiswa');
        $dosenId = $data['user']->id;
        $semester = max(1, min(8, (int) $request->input('semester', 1)));
        $pertemuan = max(1, min(16, (int) $request->input('pertemuan', 1)));
        $mahasiswaId = $request->input('mahasiswa_id');
        $mataKuliahId = $request->input('mata_kuliah_id');

        // Pastikan data Nilai/KRS untuk mata kuliah yang diampu tersedia.
        $this->syncNilaiDosen($dosenId);

        // Dosen hanya boleh melihat mata kuliah yang tercatat sebagai dosen1/dosen2/dosen3.
        $mataKuliahOptions = MataKuliah::where($this->mataKuliahDiampu($dosenId))
            ->whereIn('id', Nilai::query()
                ->select('matkul_id')
                ->where('semester', $semester)
                ->whereNotNull('matkul_id')
                ->distinct())
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Daftar mahasiswa hanya berasal dari Nilai mata kuliah yang diampu dosen.
        $mahasiswaOptions = \App\Models\Mahasiswa::query()
            ->whereIn('id', Nilai::query()
                ->select('mahasiswa_id')
                ->where('semester', $semester)
                ->whereNotNull('mahasiswa_id')
                ->whereHas('mataKuliah', $this->mataKuliahDiampu($dosenId))
                ->when($mataKuliahId, fn ($q) => $q->where('matkul_id', $mataKuliahId))
                ->distinct())
            ->orderBy('name')
            ->get(['id', 'name', 'numb_nim']);

        $data['semester'] = $semester;
        $data['pertemuan'] = $pertemuan;
        $data['mahasiswaId'] = $mahasiswaId;
        $data['mataKuliahId'] = $mataKuliahId;
        $data['mahasiswaOptions'] = $mahasiswaOptions;
        $data['mataKuliahOptions'] = $mataKuliahOptions;

        $data['nilai'] = Nilai::with(['mahasiswa', 'mataKuliah', 'kehadiranMahasiswa'])
            ->where('semester', $semester)
            ->whereHas('mataKuliah', $this->mataKuliahDiampu($dosenId))
            ->when($mahasiswaId, fn ($q) => $q->where('mahasiswa_id', $mahasiswaId))
            ->when($mataKuliahId, fn ($q) => $q->where('matkul_id', $mataKuliahId))
            ->orderBy('matkul_id')
            ->orderBy('mahasiswa_id')
            ->paginate(100)
            ->withQueryString();

        return view('private.dosen.akademik-kehadiran', $data);
    }

    public function simpanKehadiran(Request $request)
    {
        $dosen = $this->dosen();

        $request->validate([
            'semester' => 'required|integer|min:1|max:8',
            'pertemuan' => 'required|integer|min:1|max:16',
            'mahasiswa_id' => 'required|integer|exists:mahasiswas,id',
            'mata_kuliah_id' => 'required|integer|exists:mata_kuliahs,id',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpa',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $semester = (int) $request->semester;
        $pertemuan = (int) $request->pertemuan;

        // Validasi otorisasi dilakukan melalui relasi MataKuliah:
        // Dosen hanya dapat menyimpan kehadiran untuk mata kuliah yang benar-benar diampunya.
        $nilai = Nilai::with(['mahasiswa', 'mataKuliah', 'kehadiranMahasiswa'])
            ->where('semester', $semester)
            ->where('mahasiswa_id', (int) $request->mahasiswa_id)
            ->where('matkul_id', (int) $request->mata_kuliah_id)
            ->whereHas('mataKuliah', $this->mataKuliahDiampu($dosen->id))
            ->firstOrFail();

        $attendance = KehadiranMahasiswa::updateOrCreate(
            ['nilai_id' => $nilai->id, 'pertemuan' => $pertemuan],
            [
                'code' => 'ABS-' . date('YmdHis') . '-' . Str::random(6),
                'semester' => $nilai->semester,
                'status' => $request->status,
                'catatan' => $request->catatan,
                'updated_by' => Auth::guard('dosen')->id(),
            ]
        );

        if (!$attendance->created_by) {
            $attendance->update(['created_by' => Auth::guard('dosen')->id()]);
        }

        $totalPertemuan = $nilai->kehadiranMahasiswa()->count();
        $jumlahHadir = $nilai->kehadiranMahasiswa()->where('status', 'Hadir')->count();
        $persentaseKehadiran = $totalPertemuan > 0
            ? round(($jumlahHadir / $totalPertemuan) * 100, 2)
            : 0;

        $nilai->kehadiran = $persentaseKehadiran;
        $nilai->bobot_kehadiran = 15;
        $nilai->save();

        return redirect()->route('dosen.akademik.kehadiran', [
            'semester' => $semester,
            'pertemuan' => $pertemuan,
            'mahasiswa_id' => $request->mahasiswa_id,
            'mata_kuliah_id' => $request->mata_kuliah_id,
        ])->with('success', 'Kehadiran ' . ($nilai->mahasiswa->name ?? 'mahasiswa') . ' berhasil disimpan.');
    }

    /** Export rekap kehadiran mahasiswa dari dashboard Dosen. */
    public function dosenKehadiranPdf(Request $request, $mahasiswaId)
    {
        $dosen = $this->dosen();
        $semester = max(1, min(8, (int) $request->input('semester', 1)));

        $nilai = Nilai::with([
            'mahasiswa.programStudi.fakultas',
            'mataKuliah',
            'kehadiranMahasiswa',
            'tahunAkademik',
        ])
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('semester', $semester)
            ->whereHas('mataKuliah', $this->mataKuliahDiampu($dosen->id))
            ->orderBy('id')
            ->get();

        abort_if($nilai->isEmpty(), 404, 'Data kehadiran mahasiswa tidak ditemukan untuk mata kuliah yang Anda ampu pada semester ini.');

        $mahasiswa = $nilai->first()->mahasiswa;
        $webs = WebSetting::first();

        $pdf = Pdf::loadView('private.dosen.kehadiran-mahasiswa-pdf', [
            'mahasiswa' => $mahasiswa,
            'nilai' => $nilai,
            'semester' => $semester,
            'webs' => $webs,
        ])->setPaper('a4', 'landscape');

        $filename = 'kehadiran-' . Str::slug($mahasiswa->name ?? 'mahasiswa') . '-semester-' . $semester . '.pdf';
        return $pdf->download($filename);
    }

    /** Export rekap kehadiran seluruh mahasiswa pada satu mata kuliah yang diampu Dosen. */
    public function dosenKehadiranMataKuliahPdf(Request $request, $mataKuliahId)
    {
        $dosen = $this->dosen();
        $semester = max(1, min(8, (int) $request->input('semester', 1)));

        $mataKuliah = MataKuliah::whereKey($mataKuliahId)
            ->where($this->mataKuliahDiampu($dosen->id))
            ->firstOrFail();

        $nilai = Nilai::with([
            'mahasiswa.programStudi.fakultas',
            'mataKuliah',
            'kehadiranMahasiswa',
            'tahunAkademik',
        ])
            ->where('matkul_id', $mataKuliah->id)
            ->where('semester', $semester)
            ->whereHas('mataKuliah', $this->mataKuliahDiampu($dosen->id))
            ->get()
            ->sortBy(fn($item) => mb_strtolower($item->mahasiswa->name ?? ''))
            ->values();

        abort_if($nilai->isEmpty(), 404, 'Belum ada mahasiswa untuk mata kuliah ini pada semester terpilih.');

        $webs = WebSetting::first();
        $pdf = Pdf::loadView('private.dosen.kehadiran-mata-kuliah-pdf', [
            'mataKuliah' => $mataKuliah,
            'nilai' => $nilai,
            'semester' => $semester,
            'webs' => $webs,
        ])->setPaper('a4', 'landscape');

        $filename = 'rekap-kehadiran-' . Str::slug($mataKuliah->name ?? 'mata-kuliah') . '-semester-' . $semester . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Halaman kehadiran untuk Administrator Web.
     * Administrator dapat memantau dan menginput kehadiran seluruh mahasiswa.
     */
    public function webAdminKehadiran(Request $request)
    {
        $webs = WebSetting::first();
        $semester = max(1, min(8, (int) $request->input('semester', 1)));
        $pertemuan = max(1, min(16, (int) $request->input('pertemuan', 1)));
        $mahasiswaId = $request->input('mahasiswa_id');
        $mataKuliahId = $request->input('mata_kuliah_id');

        // Report global mengambil seluruh nilai pada semester terpilih.
        // Filter mahasiswa dan mata kuliah diterapkan langsung pada query Nilai,
        // sehingga tidak bergantung pada relasi nilai() yang tidak tersedia
        // pada model Mahasiswa/MataKuliah.
        $nilai = Nilai::with([
            'mahasiswa.programStudi',
            'mataKuliah',
            'kehadiranMahasiswa',
            'tahunAkademik',
        ])
            ->where('semester', $semester)
            ->whereHas('mataKuliah')
            ->when($mahasiswaId, fn ($q) => $q->where('mahasiswa_id', $mahasiswaId))
            ->when($mataKuliahId, fn ($q) => $q->where('matkul_id', $mataKuliahId))
            ->orderBy('matkul_id')
            ->orderBy('mahasiswa_id')
            ->paginate(100)
            ->withQueryString();

        // Model Mahasiswa tidak memiliki relasi nilai(), jadi gunakan subquery
        // berdasarkan tabel nilais agar daftar filter tetap akurat.
        $mahasiswaOptions = \App\Models\Mahasiswa::query()
            ->whereIn('id', Nilai::query()
                ->select('mahasiswa_id')
                ->where('semester', $semester)
                ->whereNotNull('mahasiswa_id')
                ->distinct())
            ->orderBy('name')
            ->get(['id', 'name', 'numb_nim']);

        // Model MataKuliah juga tidak memiliki relasi nilai(), sehingga
        // gunakan subquery berdasarkan matkul_id pada tabel nilais.
        $mataKuliahOptions = MataKuliah::query()
            ->whereIn('id', Nilai::query()
                ->select('matkul_id')
                ->where('semester', $semester)
                ->whereNotNull('matkul_id')
                ->distinct())
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Rekap global mengikuti filter semester, mahasiswa, dan mata kuliah yang sama.
        // Grouping dapat dipilih: mata_kuliah atau mahasiswa.
        $groupBy = $request->input('group_by', 'mata_kuliah');
        if (!in_array($groupBy, ['mata_kuliah', 'mahasiswa'], true)) {
            $groupBy = 'mata_kuliah';
        }

        $rekapNilai = Nilai::with(['mahasiswa', 'mataKuliah', 'kehadiranMahasiswa'])
            ->where('semester', $semester)
            ->whereHas('mataKuliah')
            ->when($mahasiswaId, fn ($q) => $q->where('mahasiswa_id', $mahasiswaId))
            ->when($mataKuliahId, fn ($q) => $q->where('matkul_id', $mataKuliahId))
            ->get();

        $rekapKehadiran = $rekapNilai
            ->flatMap(function ($n) {
                return $n->kehadiranMahasiswa->map(function ($a) use ($n) {
                    $a->setRelation('nilai', $n);
                    return $a;
                });
            })
            ->groupBy(function ($a) use ($groupBy) {
                return $groupBy === 'mahasiswa'
                    ? (string) $a->nilai->mahasiswa_id
                    : (string) $a->nilai->matkul_id;
            })
            ->map(function ($items) use ($groupBy) {
                $first = $items->first();
                $nilai = $first->nilai;

                $hadir = $items->where('status', 'Hadir')->count();
                $izin = $items->where('status', 'Izin')->count();
                $sakit = $items->where('status', 'Sakit')->count();
                $alpa = $items->where('status', 'Alpa')->count();
                $total = $items->count();

                return [
                    'id' => $groupBy === 'mahasiswa' ? $nilai->mahasiswa_id : $nilai->matkul_id,
                    'kode' => $nilai->mataKuliah->code ?? '-',
                    'mata_kuliah' => $nilai->mataKuliah->name ?? '-',
                    'nim' => $nilai->mahasiswa->numb_nim ?? $nilai->mahasiswa->nim ?? $nilai->mahasiswa->code ?? '-',
                    'mahasiswa' => $nilai->mahasiswa->name ?? '-',
                    'jumlah_mata_kuliah' => $items->pluck('nilai.matkul_id')->unique()->count(),
                    'jumlah_mahasiswa' => $items->pluck('nilai.mahasiswa_id')->unique()->count(),
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpa' => $alpa,
                    'total' => $total,
                    'persentase' => $total > 0 ? round(($hadir / $total) * 100, 2) : 0,
                ];
            })
            ->sortBy(function ($item) use ($groupBy) {
                return mb_strtolower($groupBy === 'mahasiswa' ? $item['mahasiswa'] : $item['mata_kuliah']);
            })
            ->values();

        $data = [
            'user' => Auth::guard('web')->user(),
            'webs' => $webs,
            'spref' => 'web-admin.',
            'menus' => 'Akademik',
            'pages' => 'Report Global Kehadiran',
            'academy' => $webs ? $webs->school_apps . ' by ' . $webs->school_name : 'SIAKAD',
            'semester' => $semester,
            'pertemuan' => $pertemuan,
            'nilai' => $nilai,
            'mahasiswaOptions' => $mahasiswaOptions,
            'mataKuliahOptions' => $mataKuliahOptions,
            'mahasiswaId' => $mahasiswaId,
            'mataKuliahId' => $mataKuliahId,
            'groupBy' => $groupBy,
            'rekapKehadiran' => $rekapKehadiran,
        ];

        return view('private.dosen.akademik-kehadiran-global', $data);
    }

    /**
     * Export rekap kehadiran satu mahasiswa ke PDF.
     * Menampilkan seluruh mata kuliah mahasiswa pada semester terpilih
     * beserta status pertemuan 1-16.
     */
    public function webAdminKehadiranPdf(Request $request, $mahasiswaId)
    {
        abort_unless(Auth::guard('web')->check(), 403);

        $semester = max(1, min(8, (int) $request->input('semester', 1)));

        $nilai = Nilai::with([
            'mahasiswa.programStudi.fakultas',
            'mataKuliah',
            'kehadiranMahasiswa',
            'tahunAkademik',
        ])
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('semester', $semester)
            ->orderBy('id')
            ->get();

        abort_if($nilai->isEmpty(), 404, 'Data kehadiran mahasiswa tidak ditemukan untuk semester ini.');

        $mahasiswa = $nilai->first()->mahasiswa;
        $webs = WebSetting::first();

        $pdf = Pdf::loadView('private.dosen.kehadiran-mahasiswa-pdf', [
            'mahasiswa' => $mahasiswa,
            'nilai' => $nilai,
            'semester' => $semester,
            'webs' => $webs,
        ])->setPaper('a4', 'landscape');

        $filename = 'kehadiran-' . Str::slug($mahasiswa->name ?? 'mahasiswa') . '-semester-' . $semester . '.pdf';
        return $pdf->download($filename);
    }

    /** Export rekap kehadiran satu mata kuliah dari Web Admin. */
    public function webAdminKehadiranMataKuliahPdf(Request $request, $mataKuliahId)
    {
        abort_unless(Auth::guard('web')->check(), 403);

        $semester = max(1, min(8, (int) $request->input('semester', 1)));

        $mataKuliah = MataKuliah::whereKey($mataKuliahId)->firstOrFail();

        $nilai = Nilai::with([
            'mahasiswa.programStudi.fakultas',
            'mataKuliah',
            'kehadiranMahasiswa',
            'tahunAkademik',
        ])
            ->where('matkul_id', $mataKuliah->id)
            ->where('semester', $semester)
            ->get()
            ->sortBy(fn($item) => mb_strtolower($item->mahasiswa->name ?? ''))
            ->values();

        abort_if($nilai->isEmpty(), 404, 'Belum ada mahasiswa untuk mata kuliah ini pada semester terpilih.');

        $webs = WebSetting::first();
        $pdf = Pdf::loadView('private.dosen.kehadiran-mata-kuliah-pdf', [
            'mataKuliah' => $mataKuliah,
            'nilai' => $nilai,
            'semester' => $semester,
            'webs' => $webs,
        ])->setPaper('a4', 'landscape');

        $filename = 'rekap-kehadiran-' . Str::slug($mataKuliah->name ?? 'mata-kuliah') . '-semester-' . $semester . '.pdf';
        return $pdf->download($filename);
    }

    /** Simpan kehadiran dari menu Web Admin. */
    public function webAdminSimpanKehadiran(Request $request)
    {
        abort_unless(Auth::guard('web')->check(), 403);

        $request->validate([
            'semester' => 'required|integer|min:1|max:8',
            'pertemuan' => 'required|integer|min:1|max:16',
            'mahasiswa_id' => 'required|integer|exists:mahasiswas,id',
            'mata_kuliah_id' => 'required|integer|exists:mata_kuliahs,id',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpa',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $semester = (int) $request->semester;
        $pertemuan = (int) $request->pertemuan;

        $nilai = Nilai::with(['mahasiswa', 'mataKuliah', 'kehadiranMahasiswa'])
            ->where('semester', $semester)
            ->where('mahasiswa_id', (int) $request->mahasiswa_id)
            ->where('matkul_id', (int) $request->mata_kuliah_id)
            ->firstOrFail();

        $attendance = KehadiranMahasiswa::updateOrCreate(
            ['nilai_id' => $nilai->id, 'pertemuan' => $pertemuan],
            [
                'code' => 'ABS-' . date('YmdHis') . '-' . Str::random(6),
                'semester' => $nilai->semester,
                'status' => $request->status,
                'catatan' => $request->catatan,
                'updated_by' => Auth::guard('web')->id(),
            ]
        );

        if (!$attendance->created_by) {
            $attendance->update(['created_by' => Auth::guard('web')->id()]);
        }

        $totalPertemuan = $nilai->kehadiranMahasiswa()->count();
        $jumlahHadir = $nilai->kehadiranMahasiswa()->where('status', 'Hadir')->count();
        $nilai->kehadiran = $totalPertemuan > 0
            ? round(($jumlahHadir / $totalPertemuan) * 100, 2)
            : 0;
        $nilai->bobot_kehadiran = 15;
        $nilai->save();

        return redirect()->route('web-admin.akademik.kehadiran', [
            'semester' => $semester,
            'pertemuan' => $pertemuan,
            'mahasiswa_id' => $request->mahasiswa_id,
            'mata_kuliah_id' => $request->mata_kuliah_id,
        ])->with('success', 'Kehadiran berhasil disimpan.');
    }

    public function updateNilai(Request $request, $code)
    {
        $dosen = $this->dosen();
        $nilai = Nilai::where('code', $code)
            ->whereHas('mataKuliah', $this->mataKuliahDiampu($dosen->id))
            ->firstOrFail();
        abort_unless($nilai->status === 'Draft', 403, 'Nilai sudah dipublish atau dikunci.');
        $request->validate([
            'tugas_1'=>'nullable|numeric|min:0|max:100','tugas_2'=>'nullable|numeric|min:0|max:100','tugas_3'=>'nullable|numeric|min:0|max:100',
            'quiz_1'=>'nullable|numeric|min:0|max:100','quiz_2'=>'nullable|numeric|min:0|max:100','uts'=>'nullable|numeric|min:0|max:100',
            'uas'=>'nullable|numeric|min:0|max:100','praktikum'=>'nullable|numeric|min:0|max:100','kehadiran'=>'nullable|numeric|min:0|max:100',
            'notes'=>'nullable|string'
        ]);
        $nilai->update($request->only(['tugas_1','tugas_2','tugas_3','quiz_1','quiz_2','uts','uas','praktikum','kehadiran','notes']) + ['updated_by'=>Auth::guard('dosen')->id()]);
        $nilai->refresh();
        $nilai->hitungNilaiAkhir();
        return back()->with('success','Nilai berhasil diperbarui.');
    }

    public function krs()
    {
        $data = $this->base('Persetujuan KRS');
        $id = $data['user']->id;
        $data['krs'] = KRS::with(['mahasiswa','tahunAkademik','dosenPA','details.mataKuliah','details.kelas','details.dosen'])
            ->whereHas('details', fn($q) => $q->where('dosen_id',$id))->where('status','Diajukan')->latest()->paginate(20);
        return view('private.dosen.akademik-krs', $data);
    }

    public function approveKrs($code)
    {
        $dosen = $this->dosen();
        $krs = KRS::where('code',$code)->whereHas('details', fn($q) => $q->where('dosen_id',$dosen->id))->firstOrFail();
        abort_unless($krs->getRawOriginal('status') === 'Diajukan', 403, 'KRS tidak sedang menunggu persetujuan.');
        $krs->approve($dosen->id, request('notes'));
        return back()->with('success','KRS berhasil disetujui.');
    }

    public function rejectKrs(Request $request, $code)
    {
        $dosen = $this->dosen();
        $krs = KRS::where('code',$code)->whereHas('details', fn($q) => $q->where('dosen_id',$dosen->id))->firstOrFail();
        abort_unless($krs->getRawOriginal('status') === 'Diajukan', 403, 'KRS tidak sedang menunggu persetujuan.');
        $request->validate(['notes'=>'required|string']);
        $krs->reject($request->notes);
        return back()->with('success','KRS berhasil ditolak.');
    }
}
