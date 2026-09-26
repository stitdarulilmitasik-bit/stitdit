<?php

namespace App\Http\Controllers\Private\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Layanan\CutiAkademik;
use App\Models\Pengaturan\WebSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LayananController extends Controller
{
    private function mahasiswa()
    {
        return Auth::guard('mahasiswa')->user();
    }

    private function layoutData($title, array $extra = [])
    {
        $u = $this->mahasiswa();
        $webs = WebSetting::first();

        return array_merge([
            'w' => $webs,
            'webs' => $webs,
            'user' => $u,
            'spref' => $u?->prefix ?? 'mahasiswa.',
            'menus' => 'Layanan',
            'pages' => $title,
            'academy' => 'STIT Darul Ilmi Tasikmalaya',
            'title' => $title,
            'message' => 'Silakan pilih layanan yang tersedia.',
        ], $extra);
    }

    public function page(Request $request, $title = 'Layanan')
    {
        return view('private.mahasiswa.menu-page', $this->layoutData($title));
    }

    /**
     * Build the student's cumulative transcript directly from published/locked
     * grades. A course is represented once; when the same course is repeated,
     * the best published grade is retained.
     */
    private function transkripData($user): array
    {
        $nilai = \App\Models\Akademik\Nilai::query()
            ->with(['mataKuliah', 'tahunAkademik'])
            ->where('mahasiswa_id', $user->id)
            ->whereBetween('semester', [1, 8])
            ->whereIn('status', ['Published', 'Locked'])
            ->orderBy('semester')
            ->orderBy('matkul_id')
            ->get();

        $gradePoint = static function ($n): float {
            $huruf = strtoupper(trim((string) ($n->nilai_huruf ?? '')));
            $map = \App\Models\Akademik\Nilai::NILAI_HURUF_MAP;

            if ($huruf !== '' && isset($map[$huruf])) {
                return (float) $map[$huruf]['mutu'];
            }

            return (float) ($n->nilai_mutu ?? 0);
        };

        $nilaiTerbaik = $nilai
            ->groupBy(function ($n) {
                return (string) ($n->matkul_id ?? $n->mataKuliah?->id ?? $n->id);
            })
            ->map(function ($items) use ($gradePoint) {
                return $items
                    ->sort(function ($a, $b) use ($gradePoint) {
                        $pointCompare = $gradePoint($b) <=> $gradePoint($a);
                        if ($pointCompare !== 0) {
                            return $pointCompare;
                        }

                        $angkaCompare = (float) ($b->nilai_angka ?? 0) <=> (float) ($a->nilai_angka ?? 0);
                        if ($angkaCompare !== 0) {
                            return $angkaCompare;
                        }

                        return (int) ($a->semester ?? 0) <=> (int) ($b->semester ?? 0);
                    })
                    ->first();
            })
            ->sortBy([
                ['semester', 'asc'],
                ['matkul_id', 'asc'],
            ])
            ->values();

        $totalSks = (float) $nilaiTerbaik->sum(function ($n) {
            return (float) ($n->sks ?? $n->mataKuliah?->sks ?? $n->mataKuliah?->bsks ?? 0);
        });

        $totalMutu = (float) $nilaiTerbaik->sum(function ($n) use ($gradePoint) {
            $sks = (float) ($n->sks ?? $n->mataKuliah?->sks ?? $n->mataKuliah?->bsks ?? 0);
            return $gradePoint($n) * $sks;
        });

        $ipk = $totalSks > 0 ? round($totalMutu / $totalSks, 2) : 0.00;

        return [
            'nilai' => $nilaiTerbaik,
            'totalSks' => $totalSks,
            'totalMutu' => $totalMutu,
            'ipk' => $ipk,
        ];
    }

    public function transkripNilai()
    {
        $user = $this->mahasiswa();

        if (!$user) {
            abort(403, 'Sesi mahasiswa tidak ditemukan. Silakan login kembali sebagai mahasiswa.');
        }

        $data = $this->transkripData($user);

        return view('private.mahasiswa.layanan.transkrip', $this->layoutData('Transkrip Nilai', [
            'nilai' => $data['nilai'],
            'totalSks' => $data['totalSks'],
            'totalMutu' => $data['totalMutu'],
            'ipk' => $data['ipk'],
        ]));
    }

    public function cetakTranskrip()
    {
        $user = $this->mahasiswa();

        if (!$user) {
            abort(403, 'Sesi mahasiswa tidak ditemukan. Silakan login kembali sebagai mahasiswa.');
        }

        $data = $this->transkripData($user);
        $webs = WebSetting::first();

        $logoDataUri = null;
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        foreach (['images/logo/logo-vert1.png', 'images/logo/logo-hori.png'] as $logoPath) {
            if (!$disk->exists($logoPath)) {
                continue;
            }

            try {
                $bytes = $disk->get($logoPath);
                if ($bytes !== '') {
                    $mime = $disk->mimeType($logoPath) ?: 'image/png';
                    $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode($bytes);
                    break;
                }
            } catch (\Throwable $e) {
                // Continue with the PDF without a logo if storage is unavailable.
            }
        }

        $pdf = Pdf::loadView('private.mahasiswa.layanan.transkrip-pdf', [
            'webs' => $webs,
            'mahasiswa' => $user,
            'nilai' => $data['nilai'],
            'totalSks' => $data['totalSks'],
            'totalMutu' => $data['totalMutu'],
            'ipk' => $data['ipk'],
            'logoDataUri' => $logoDataUri,
        ])->setPaper('a4', 'portrait')->setOptions([
            'defaultFont' => 'Helvetica',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'dpi' => 96,
            'enable_font_subsetting' => true,
        ]);

        $filename = 'Transkrip-Nilai-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $user->numb_nim ?? $user->name ?? 'mahasiswa') . '.pdf';

        return $pdf->download($filename);
    }

    public function legalisirDokumen()
    {
        return view('private.mahasiswa.menu-page', $this->layoutData('Legalisir Dokumen', [
            'message' => 'Halaman pengajuan legalisir dokumen mahasiswa.',
        ]));
    }

    public function ajukanLegalisir(Request $request)
    {
        return back()->with('success', 'Pengajuan legalisir dokumen berhasil dikirim.');
    }

    public function ajukanCuti(Request $request)
    {
        $user = $this->mahasiswa();

        if (!$user) {
            abort(403, 'Sesi mahasiswa tidak ditemukan. Silakan login kembali sebagai mahasiswa.');
        }

        $data = $request->validate([
            'semester' => 'required|string|max:30',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:2000',
            'alamat_selama_cuti' => 'nullable|string|max:500',
            'no_telepon' => 'nullable|string|max:30',
            'file_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('file_pendukung')) {
            $data['file_pendukung'] = $request->file('file_pendukung')->store('cuti-akademik', 'public');
        }

        $data['mahasiswa_id'] = $user->id;
        $data['tanggal_pengajuan'] = now()->toDateString();
        $data['status'] = 'Diajukan';

        CutiAkademik::create($data);

        return redirect()
            ->route('mahasiswa.layanan.cuti')
            ->with('success', 'Pengajuan cuti akademik berhasil dikirim dan menunggu verifikasi bagian akademik.');
    }

    public function cutiAkademik()
    {
        $user = $this->mahasiswa();
        $pengajuan = $user
            ? CutiAkademik::where('mahasiswa_id', $user->id)->latest('tanggal_pengajuan')->latest('id')->get()
            : collect();

        return view('private.mahasiswa.layanan.cuti-akademik', $this->layoutData('Cuti Akademik', [
            'pengajuan' => $pengajuan,
        ]));
    }

    public function suratAktifKuliah()
    {
        $jabatanDosen = collect([
            (object)['id' => 'ketua', 'name' => 'Ketua STIT Darul Ilmi Tasikmalaya'],
            (object)['id' => 'wakil-ketua', 'name' => 'Wakil Ketua STIT Darul Ilmi Tasikmalaya'],
        ]);

        return view('private.mahasiswa.layanan.surat-aktif-kuliah', $this->layoutData('Surat Keterangan Aktif Kuliah', [
            'jabatanDosen' => $jabatanDosen,
        ]));
    }

    public function ajukanSuratAktifKuliah(Request $request)
    {
        $user = $this->mahasiswa();

        $data = $request->validate([
            'nomor_surat' => 'nullable|string|max:100',
            'tanggal_surat' => 'required|date',
            'pejabat_jabatan' => 'required|in:Ketua STIT Darul Ilmi Tasikmalaya,Wakil Ketua STIT Darul Ilmi Tasikmalaya',
            'nik' => 'required|string|max:50',
            'ttl' => 'required|string|max:200',
            'alamat' => 'required|string|max:500',
            'jenjang' => 'required|in:S1,S2,S3',
            'program_studi' => 'required|string|max:150',
            'periode_mulai' => 'required|in:Ganjil,Genap',
            'tahun_akademik' => 'nullable|string|max:50',
            'keperluan' => 'required|string|max:200',
        ]);

        if (!$user) {
            abort(403, 'Sesi mahasiswa tidak ditemukan. Silakan login kembali sebagai mahasiswa.');
        }

        $penandaTangan = [
            'Ketua STIT Darul Ilmi Tasikmalaya' => [
                'nama' => 'Dr. H. Dudung Rahmat Hidayat, M.Pd.',
                'nip' => '12000',
            ],
            'Wakil Ketua STIT Darul Ilmi Tasikmalaya' => [
                'nama' => 'Aa Sudirman, M.Pd.I',
                'nip' => '12001',
            ],
        ];

        $ttd = $penandaTangan[$data['pejabat_jabatan']];
        $data['pejabat_nama'] = $ttd['nama'];
        $data['pejabat_nip'] = $ttd['nip'];

        $nim = preg_replace('/\D+/', '', (string) $user->numb_nim);
        $kodeAngkatan = substr($nim, 0, 2);

        if (strlen($kodeAngkatan) !== 2) {
            return back()
                ->withErrors(['tahun_akademik' => 'NIM mahasiswa tidak memiliki format dua digit tahun angkatan yang valid.'])
                ->withInput();
        }

        $tahunMulai = 2000 + (int) $kodeAngkatan;
        $data['tahun_akademik'] = $tahunMulai . '/' . ($tahunMulai + 1);
        $data['nomor_surat'] = $data['nomor_surat'] ?: 'SKAK/' . now()->format('m/Y') . '/' . $user->numb_nim;
        $data['nama'] = $user->name;
        $data['nim'] = $user->numb_nim;
        $data['mahasiswa'] = $user;
        $data['webs'] = WebSetting::first();

        return Pdf::loadView('private.mahasiswa.layanan.surat-aktif-kuliah-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download('Surat-Keterangan-Aktif-Kuliah-' . $user->numb_nim . '.pdf');
    }
}
