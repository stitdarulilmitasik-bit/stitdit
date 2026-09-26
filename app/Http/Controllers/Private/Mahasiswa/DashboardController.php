<?php

namespace App\Http\Controllers\Private\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Akademik\JadwalKuliah;
use App\Models\Akademik\KRS;
use App\Models\Akademik\Nilai;
use App\Models\Pengaturan\WebSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('mahasiswa')->user();
        abort_unless($user, 403);
        $webs = WebSetting::first();
        $data = [
            'webs' => $webs,
            'spref' => $user->prefix,
            'menus' => 'Dashboard',
            'pages' => 'Dashboard Mahasiswa',
            'academy' => ($webs->school_apps ?? 'SIAKAD') . ' by ' . ($webs->school_name ?? 'STIT Darul Ilmi Tasikmalaya'),
            'user' => $user,
        ];
        $this->academic($data, $user);
        $this->schedule($data, $user);
        $this->attendance($data, $user);
        $this->finance($data, $user);
        $this->announcements($data);
        $this->activities($data, $user);
        return view('private.mahasiswa.dashboard', $data);
    }

    private function academic(&$data, $user)
    {
        $data['ips'] = 0;
        $data['ipk'] = 0;
        $data['total_sks'] = 0;
        $data['total_sks_lulus'] = 0;
        $data['sks_kebutuhan'] = (int) ($user->programStudi?->sks_lulus ?? 144);
        $data['progress_sks'] = 0;
        $data['jumlah_krs'] = 0;

        try {
            $krs = KRS::where('mahasiswa_id', $user->id)->with(['details.mataKuliah', 'details.nilai', 'tahunAkademik'])->get();
        $totalSks = 0; $totalSksLulus = 0; $totalMutu = 0; $semesterResults = [];
        foreach ($krs as $item) {
            $semesterSks = 0; $semesterMutu = 0; $semesterKey = (string) ($item->semester ?? '0');
            foreach ($item->details as $detail) {
                $mk = $detail->mataKuliah; $nilai = $detail->nilai;
                $sks = (float) ($mk->bsks ?? $mk->sks ?? 0);
                if (!$nilai || $sks <= 0) continue;
                $bobot = $this->nilaiMutu($nilai);
                $semesterSks += $sks; $semesterMutu += $bobot * $sks; $totalSks += $sks; $totalMutu += $bobot * $sks;
                if ($bobot >= 2.00) $totalSksLulus += $sks;
            }
            if ($semesterSks > 0) $semesterResults[$semesterKey] = round($semesterMutu / $semesterSks, 2);
        }
        $data['ips'] = $semesterResults ? end($semesterResults) : 0;
        $data['ipk'] = $totalSks > 0 ? round($totalMutu / $totalSks, 2) : 0;
        $data['total_sks'] = $totalSks;
        $data['total_sks_lulus'] = $totalSksLulus;
        $data['sks_kebutuhan'] = (int) ($user->programStudi->sks_lulus ?? 144);
            $data['progress_sks'] = $data['sks_kebutuhan'] > 0 ? min(100, round(($totalSksLulus / $data['sks_kebutuhan']) * 100, 1)) : 0;
            $data['jumlah_krs'] = $krs->count();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function nilaiMutu($nilai)
    {
        if ($nilai->nilai_mutu !== null) return (float) $nilai->nilai_mutu;
        $angka = (float) ($nilai->nilai_angka ?? 0);
        return match (true) {
            $angka >= 85 => 4.00, $angka >= 80 => 3.67, $angka >= 75 => 3.33, $angka >= 70 => 3.00,
            $angka >= 65 => 2.67, $angka >= 60 => 2.33, $angka >= 55 => 2.00, $angka >= 50 => 1.67,
            $angka >= 45 => 1.33, $angka >= 40 => 1.00, default => 0.00,
        };
    }

    private function schedule(&$data, $user)
    {
        $today = Carbon::today();
        $data['tanggal_hari_ini'] = $today->locale('id')->translatedFormat('l, d F Y');
        $data['jadwal_hari_ini'] = [];
        $data['jadwal_dashboard'] = [];
        $data['jadwal_akan_datang'] = [];
        $data['jadwal_sudah_dilaksanakan'] = [];

        try {
        $day = $today->locale('id')->translatedFormat('l');
        $query = JadwalKuliah::with(['mataKuliah', 'dosen', 'ruang', 'waktuKuliah', 'kelas'])
            ->whereHas('kelas.mahasiswas', fn ($q) => $q->where('id', $user->id));
        $todayRows = (clone $query)->where('hari', $day)->get()->sortBy(fn ($item) => $item->waktuKuliah?->time_start ?? '99:99');
        $data['tanggal_hari_ini'] = $today->locale('id')->translatedFormat('l, d F Y');
        $data['jadwal_hari_ini'] = $todayRows->map(fn ($item) => $this->scheduleRow($item))->values()->all();

        // Tampilkan seluruh jadwal mahasiswa di dashboard, termasuk jadwal
        // yang akan datang dan yang sudah dilaksanakan.
        $allRows = (clone $query)
            ->orderBy('tanggal')
            ->orderBy('waktu_kuliah_id')
            ->get();

        $data['jadwal_dashboard'] = $allRows
            ->map(fn ($item) => $this->scheduleRow($item))
            ->values()
            ->all();

        $data['jadwal_akan_datang'] = collect($data['jadwal_dashboard'])
            ->whereIn('status', ['akan_datang', 'berlangsung'])
            ->values()
            ->all();

            $data['jadwal_sudah_dilaksanakan'] = collect($data['jadwal_dashboard'])
                ->where('status', 'selesai')
                ->sortByDesc(fn ($item) => $item['tanggal_sort'] ?? '')
                ->values()
                ->all();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function scheduleRow($item)
    {
        $start = $item->waktuKuliah?->time_start;
        $end = $item->waktuKuliah?->time_ended;
        $scheduleDate = $item->tanggal ? Carbon::parse($item->tanggal) : Carbon::today();
        $status = 'selesai';

        if ($start && $end) {
            $s = $scheduleDate->copy()->setTimeFromTimeString($start);
            $e = $scheduleDate->copy()->setTimeFromTimeString($end);
            $now = now();
            $status = $now->between($s, $e) ? 'berlangsung' : ($now->lt($s) ? 'akan_datang' : 'selesai');
        } elseif ($scheduleDate->isFuture()) {
            $status = 'akan_datang';
        }
        $dosen = $item->dosen; $namaDosen = $dosen?->name ?? $dosen?->nama ?? '-';
        return [
            'id' => $item->id,
            'mata_kuliah' => $item->mataKuliah?->name ?? $item->mataKuliah?->nama_mk ?? '-',
            'kode' => $item->mataKuliah?->code ?? '-', 'bsks' => $item->mataKuliah?->bsks ?? 0,
            'dosen' => trim($namaDosen), 'ruang' => $item->ruang?->name ?? $item->ruang?->nama_ruang ?? '-',
            'hari' => $item->hari ?? '-', 'tanggal' => $item->tanggal ? Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') : '-', 'tanggal_sort' => $scheduleDate->format('Y-m-d'),
            'time_start' => $start ? Carbon::parse($start)->format('H:i') : '-', 'time_ended' => $end ? Carbon::parse($end)->format('H:i') : '-',
            'metode' => $item->metode ?? $item->metode_pembelajaran ?? '-', 'status' => $status,
        ];
    }

    private function attendance(&$data, $user)
    {
        $data['kehadiran_bulan_ini'] = 0;
        $data['total_pertemuan'] = 0;
        $data['hadir'] = 0;
        $data['izin'] = 0;
        $data['sakit'] = 0;
        $data['alpha'] = 0;
        $data['kehadiran_tersedia'] = false;
        $data['kehadiran_rekap'] = [];
        $data['kehadiran_semester'] = null;

        try {
            $semester = \App\Models\Akademik\TahunAkademik::where('status', 'Aktif')
                ->where('start_date', '<=', now())
                ->where('ended_date', '>=', now())
                ->first()
                ?? \App\Models\Akademik\TahunAkademik::latest('start_date')->first();

            if (!$semester) {
                return;
            }

            $data['kehadiran_semester'] = $semester;
            $nilai = Nilai::where('mahasiswa_id', $user->id)
                ->where('taka_id', $semester->id)
                ->with(['mataKuliah', 'kehadiranMahasiswa'])
                ->get();

            $rows = collect();
            foreach ($nilai as $item) {
                foreach ($item->kehadiranMahasiswa as $attendance) {
                    $rows->push($attendance);
                }

                $att = $item->kehadiranMahasiswa;
                if (!$item->mataKuliah || $att->isEmpty()) {
                    continue;
                }

                $hadir = $att->where('status', 'Hadir')->count();
                $izin = $att->where('status', 'Izin')->count();
                $sakit = $att->where('status', 'Sakit')->count();
                $alpha = $att->where('status', 'Alpa')->count();
                $total = $att->count();

                $data['kehadiran_rekap'][] = [
                    'mata_kuliah' => $item->mataKuliah->name ?? $item->mataKuliah->nama ?? '-',
                    'kode_mk' => $item->mataKuliah->code ?? $item->mataKuliah->kode_mk ?? '-',
                    'total' => $total,
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpha' => $alpha,
                    'persentase' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
                ];
            }

            if ($rows->isEmpty()) {
                return;
            }

            $data['total_pertemuan'] = $rows->count();
            $data['hadir'] = $rows->where('status', 'Hadir')->count();
            $data['izin'] = $rows->where('status', 'Izin')->count();
            $data['sakit'] = $rows->where('status', 'Sakit')->count();
            $data['alpha'] = $rows->where('status', 'Alpa')->count();
            $data['kehadiran_bulan_ini'] = round(($data['hadir'] / $data['total_pertemuan']) * 100);
            $data['kehadiran_tersedia'] = true;
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function finance(&$data, $user)
    {
        $data['tagihan_aktif'] = []; $data['total_tagihan'] = 0; $data['riwayat_pembayaran'] = [];
        try {
            $tagihan = \App\Models\Keuangan\TagihanKuliah::where('mahasiswa_id', $user->id)->where('status', 'Pending')->orderBy('due_date')->get();
            $data['tagihan_aktif'] = $tagihan->map(fn ($item) => ['desc' => $item->desc ?? 'Tagihan kuliah', 'amount' => (float) ($item->amount ?? 0), 'due_date' => $item->due_date, 'status' => $item->status])->all();
            $data['total_tagihan'] = array_sum(array_column($data['tagihan_aktif'], 'amount'));
        } catch (\Throwable $e) {}
        try {
            $payments = \App\Models\Keuangan\RiwayatPembayaran::where('mahasiswa_id', $user->id)->where('status_pembayaran', 'Sukses')->orderByDesc('tgl_pembayaran')->limit(5)->get();
            $data['riwayat_pembayaran'] = $payments->map(fn ($item) => ['amount' => (float) ($item->jumlah_bayar ?? 0), 'updated_at' => $item->tgl_pembayaran, 'status' => $item->status_pembayaran])->all();
        } catch (\Throwable $e) {}
    }

    private function announcements(&$data)
    {
        try { $data['pengumuman'] = \App\Models\Publikasi\Pengumuman::where('status', 'Publish')->where('created_at', '<=', now())->latest()->limit(5)->get(['name', 'content', 'created_at'])->all(); }
        catch (\Throwable $e) { $data['pengumuman'] = []; }
    }

    private function activities(&$data, $user)
    {
        $activities = [];
        try {
        $krs = KRS::where('mahasiswa_id', $user->id)->latest()->first();
        if ($krs) $activities[] = ['title' => 'KRS terakhir diperbarui', 'description' => 'Data KRS mahasiswa telah tersimpan.', 'time' => $krs->updated_at, 'badge' => 'KRS', 'badge_color' => 'primary'];
        $nilai = Nilai::where('mahasiswa_id', $user->id)->latest('updated_at')->first();
        if ($nilai) $activities[] = ['title' => 'Nilai terbaru tersedia', 'description' => 'Ada data nilai yang baru diperbarui.', 'time' => $nilai->updated_at, 'badge' => 'Nilai', 'badge_color' => 'info'];
        foreach ($data['riwayat_pembayaran'] as $payment) $activities[] = ['title' => 'Pembayaran berhasil', 'description' => 'Pembayaran Rp ' . number_format($payment['amount'], 0, ',', '.') . ' tercatat.', 'time' => $payment['updated_at'], 'badge' => 'Keuangan', 'badge_color' => 'success'];
        usort($activities, fn ($a, $b) => strtotime((string) $b['time']) <=> strtotime((string) $a['time']));
            $data['aktivitas_terbaru'] = array_slice($activities, 0, 5);
        } catch (\Throwable $e) {
            report($e);
            $data['aktivitas_terbaru'] = [];
        }
    }
}
