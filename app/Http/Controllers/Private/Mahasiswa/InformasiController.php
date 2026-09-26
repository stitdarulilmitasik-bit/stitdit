<?php
namespace App\Http\Controllers\Private\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Akademik\JadwalKuliah;
use App\Models\Akademik\KRS;
use App\Models\Akademik\TahunAkademik;
use App\Models\Pengaturan\WebSetting;
use App\Models\Publikasi\Pengumuman;
use App\Models\Publikasi\KalenderAkademik;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class InformasiController extends Controller
{
    private function page($title, $extra = [])
    {
        $user = Auth::guard('mahasiswa')->user();
        return view('private.mahasiswa.menu-page', array_merge([
            'webs' => WebSetting::first(), 'user' => $user,
            'menus' => 'Informasi', 'pages' => $title,
            'academy' => ($w = WebSetting::first()) ? $w->school_apps . ' by ' . $w->school_name : 'SIAKAD',
            'title' => $title, 'message' => 'Informasi akan ditampilkan di halaman ini.'
        ], $extra));
    }

    public function pengumuman()
    {
        $items = Pengumuman::where('status','Publish')->latest()->take(10)->get();
        return $this->page('Pengumuman', ['items'=>$items,'message'=>'Daftar pengumuman kampus.']);
    }

    public function detailPengumuman($id)
    {
        $item = Pengumuman::where('status','Publish')->findOrFail($id);
        return $this->page('Detail Pengumuman', ['item'=>$item,'message'=>$item->title ?? $item->name ?? 'Pengumuman']);
    }

    public function kalenderAkademik()
    {
        $user = Auth::guard('mahasiswa')->user();
        abort_unless($user, 403);

        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $year = (int) request()->query('year', $today->year);
        $month = (int) request()->query('month', $today->month);

        if ($year < 2000 || $year > 2100) {
            $year = $today->year;
        }
        if ($month < 1 || $month > 12) {
            $month = $today->month;
        }

        $monthStart = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');
        $monthEnd = $monthStart->copy()->endOfMonth();
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $days = [];
        for ($date = $gridStart->copy(); $date <= $gridEnd; $date->addDay()) {
            $days[] = $date->copy();
        }

        $items = KalenderAkademik::where('status', 'Publish')
            ->whereDate('start_date', '<=', $monthEnd->toDateString())
            ->where(function ($q) use ($monthStart) {
                $q->where(function ($q) use ($monthStart) {
                    $q->whereNull('ended_date')
                        ->whereDate('start_date', '>=', $monthStart->toDateString());
                })->orWhere(function ($q) use ($monthStart) {
                    $q->whereNotNull('ended_date')
                        ->whereDate('ended_date', '>=', $monthStart->toDateString());
                });
            })
            ->orderBy('start_date')
            ->get();

        $currentSemester = TahunAkademik::where('status', 'Aktif')
            ->where('start_date', '<=', $today)
            ->where('ended_date', '>=', $today)
            ->first() ?? TahunAkademik::latest('start_date')->first();

        $scheduleByDate = collect();

        if ($currentSemester) {
            $krs = KRS::where('mahasiswa_id', $user->id)
                ->where('taka_id', $currentSemester->id)
                ->with('details')
                ->first();

            $details = $krs?->details
                ->whereIn('status', ['Aktif', 'Mengulang'])
                ->values() ?? collect();

            $scheduleIds = $details->pluck('jadwal_kuliah_id')->filter()->unique()->values();

            // KRS lama mungkin belum memiliki jadwal_kuliah_id. Untuk detail seperti itu,
            // gunakan pasangan mata kuliah + kelas sebagai fallback agar jadwal mahasiswa
            // tetap muncul di kalender.
            $fallbackDetails = $details->filter(fn ($detail) => empty($detail->jadwal_kuliah_id));

            $jadwalQuery = JadwalKuliah::with([
                'mataKuliah', 'dosen', 'ruang', 'waktuKuliah', 'kelas'
            ])->whereBetween('tanggal', [
                $monthStart->toDateString(),
                $monthEnd->toDateString(),
            ]);

            $jadwalQuery->where(function ($q) use ($scheduleIds, $fallbackDetails) {
                if ($scheduleIds->isNotEmpty()) {
                    $q->whereIn('id', $scheduleIds->all());
                }

                foreach ($fallbackDetails as $detail) {
                    $q->orWhere(function ($q) use ($detail) {
                        $q->where('matkul_id', $detail->matkul_id)
                            ->whereHas('kelas', function ($q) use ($detail) {
                                $q->where('kelas.id', $detail->kelas_id);
                            });
                    });
                }

                // Jika tidak ada KRS detail, jangan tampilkan jadwal mahasiswa lain.
                if ($scheduleIds->isEmpty() && $fallbackDetails->isEmpty()) {
                    $q->whereRaw('1 = 0');
                }
            });

            $scheduleByDate = $jadwalQuery
                ->orderBy('tanggal')
                ->orderBy('waktu_kuliah_id')
                ->get()
                ->groupBy(fn ($jadwal) => Carbon::parse($jadwal->tanggal)->toDateString());
        }

        return view('private.mahasiswa.informasi.kalender-akademik', [
            'webs' => WebSetting::first(),
            'user' => $user,
            'menus' => 'Informasi',
            'pages' => 'Kalender Akademik',
            'academy' => ($w = WebSetting::first()) ? $w->school_apps . ' by ' . $w->school_name : 'SIAKAD',
            'today' => $today,
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'days' => $days,
            'items' => $items,
            'scheduleByDate' => $scheduleByDate,
            'currentSemester' => $currentSemester,
        ]);
    }

    public function beasiswa() { return $this->page('Beasiswa'); }
    public function detailBeasiswa($id) { return $this->page('Detail Beasiswa', ['item'=>null]); }
    public function kontakKampus() { return $this->page('Kontak Kampus', ['message'=>WebSetting::first()?->school_address ?: 'Kontak kampus belum diisi.']); }
}
