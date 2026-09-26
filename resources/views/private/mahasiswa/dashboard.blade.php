@extends('core-themes.core-backpage')

@section('custom-css')
<style>
    .student-hero { border:0; overflow:hidden; background:linear-gradient(135deg,#206bc4,#4263eb); color:#fff; }
    .stat-card { height:100%; transition:transform .2s ease,box-shadow .2s ease; }
    .stat-card:hover { transform:translateY(-2px); box-shadow:0 .5rem 1rem rgba(0,0,0,.08); }
    .schedule-item { border-left:3px solid #206bc4; padding:1rem 0 1rem 1rem; }
    .schedule-item + .schedule-item { border-top:1px solid var(--tblr-border-color); }
    .quick-action { min-height:74px; }
</style>
@endsection

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card student-hero"><div class="card-body py-4"><div class="row align-items-center"><div class="col">
            <div class="text-white-50 text-uppercase small fw-bold mb-1">SIAKAD STIT Darul Ilmi Tasikmalaya</div>
            <h2 class="text-white mb-1">Selamat datang, {{ $user->name }} 👋</h2>
            <div class="text-white-50">NIM {{ $user->numb_nim ?? '-' }} · Semester {{ $user->semester ?? '-' }}</div>
            <div class="mt-3 d-flex flex-wrap gap-2"><span class="badge bg-white-lt text-blue">IPK {{ number_format($ipk ?? 0,2) }}</span><span class="badge bg-white-lt text-blue">{{ $total_sks_lulus ?? 0 }} / {{ $sks_kebutuhan ?? 144 }} SKS</span><span class="badge bg-white-lt text-blue">{{ $user->type ?? 'Mahasiswa Aktif' }}</span></div>
        </div><div class="col-auto d-none d-md-block"><span class="avatar avatar-xl rounded-circle" style="background-image:url('{{ $user->photo }}')"></span></div></div></div></div>
    </div>

    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="card-body"><div class="subheader">IPK Kumulatif</div><div class="h1 mb-2">{{ number_format($ipk ?? 0,2) }}</div><div class="text-secondary">IPS terakhir: <strong>{{ number_format($ips ?? 0,2) }}</strong></div></div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="card-body"><div class="subheader">SKS Lulus</div><div class="h1 mb-2">{{ $total_sks_lulus ?? 0 }}</div><div class="progress progress-sm mb-1"><div class="progress-bar" style="width:{{ $progress_sks ?? 0 }}%"></div></div><div class="text-secondary">{{ $progress_sks ?? 0 }}% dari {{ $sks_kebutuhan ?? 144 }} SKS</div></div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="card-body"><div class="subheader">Tagihan Aktif</div><div class="h2 mb-2">Rp {{ number_format($total_tagihan ?? 0,0,',','.') }}</div><div class="text-secondary">{{ count($tagihan_aktif ?? []) }} tagihan belum dibayar</div></div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="card-body"><div class="subheader">Kehadiran</div>@if($kehadiran_tersedia ?? false)<div class="h1 mb-2">{{ $kehadiran_bulan_ini }}%</div><div class="text-secondary">{{ $hadir }} hadir dari {{ $total_pertemuan }} pertemuan</div><div class="mt-2 small"><span class="text-success">{{ $hadir }} Hadir</span> · <span class="text-primary">{{ $izin }} Izin</span> · <span class="text-warning">{{ $sakit }} Sakit</span> · <span class="text-danger">{{ $alpha }} Alpa</span></div>@else<div class="h2 mb-2">Belum tersedia</div><div class="text-secondary">Data presensi akan tampil setelah dosen mengisi.</div>@endif</div></div></div>

    <div class="col-lg-8"><div class="card h-100"><div class="card-header"><div><h3 class="card-title mb-1">Jadwal Kuliah</h3><div class="text-secondary small">Jadwal kuliah Anda ditampilkan langsung di dashboard</div></div></div><div class="card-body">
        @if(!empty($jadwal_dashboard))
            @php
                $hariDashboard = [
                    'Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu',
                    'Kamis' => 'Kamis', 'Jumat' => 'Jumat', 'Sabtu' => 'Sabtu', 'Minggu' => 'Minggu'
                ];
                $jadwalGrouped = collect($jadwal_dashboard)->groupBy('hari');
            @endphp
            @foreach($hariDashboard as $namaHari => $labelHari)
                @php $jadwalHari = $jadwalGrouped->get($namaHari, collect()); @endphp
                @if($jadwalHari->isNotEmpty())
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2 px-2 py-2 rounded bg-blue-lt">
                            <div class="fw-bold">{{ $labelHari }}</div>
                            <span class="badge bg-blue-lt">{{ $jadwalHari->count() }} Mata Kuliah</span>
                        </div>
                        @foreach($jadwalHari as $jadwal)
                            <div class="schedule-item">
                                <div class="row align-items-center g-2">
                                    <div class="col">
                                        <div class="fw-bold">{{ $jadwal['mata_kuliah'] }}</div>
                                        <div class="text-secondary small mt-1">{{ $jadwal['kode'] }} · {{ $jadwal['bsks'] }} SKS · {{ $jadwal['dosen'] }}</div>
                                        <div class="text-secondary small">{{ $jadwal['tanggal'] }} · {{ $jadwal['ruang'] }} · {{ $jadwal['metode'] }}</div>
                                    </div>
                                    <div class="col-auto text-end">
                                        <div class="fw-bold">{{ $jadwal['time_start'] }} - {{ $jadwal['time_ended'] }}</div>
                                        @php
                                            $statusClass = $jadwal['status'] === 'berlangsung' ? 'bg-green' : ($jadwal['status'] === 'akan_datang' ? 'bg-orange' : 'bg-secondary');
                                            $statusText = $jadwal['status'] === 'berlangsung' ? 'Sedang berlangsung' : ($jadwal['status'] === 'akan_datang' ? 'Akan datang' : 'Sudah dilaksanakan');
                                        @endphp
                                        <span class="badge {{ $statusClass }} text-white mt-1">{{ $statusText }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach
        @else
            <div class="empty py-5"><div class="empty-icon">📅</div><p class="empty-title">Belum ada jadwal kuliah</p><p class="empty-subtitle text-secondary">Jadwal yang dibuat untuk kelas Anda akan tampil di sini.</p></div>
        @endif
    </div></div></div>

    <div class="col-lg-8"><div class="card h-100"><div class="card-header"><div><h3 class="card-title mb-1">Rekap Kehadiran</h3><div class="text-secondary small">@if($kehadiran_semester){{ $kehadiran_semester->name }}@else Semester aktif @endif</div></div><div class="card-actions"><a href="{{ route('mahasiswa.akademik.presensi') }}" class="btn btn-sm btn-outline-primary">Lihat Presensi</a></div></div><div class="table-responsive"><table class="table table-vcenter card-table"><thead><tr><th>Mata Kuliah</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpa</th><th class="text-end">Persentase</th></tr></thead><tbody>@forelse($kehadiran_rekap ?? [] as $item)<tr><td><div class="fw-medium">{{ $item['mata_kuliah'] }}</div><div class="text-secondary small">{{ $item['kode_mk'] }}</div></td><td class="text-success">{{ $item['hadir'] }}</td><td class="text-primary">{{ $item['izin'] }}</td><td class="text-warning">{{ $item['sakit'] }}</td><td class="text-danger">{{ $item['alpha'] }}</td><td class="text-end fw-semibold">{{ $item['persentase'] }}%</td></tr>@empty<tr><td colspan="6" class="text-center text-secondary py-4">Belum ada rekap kehadiran yang diinput.</td></tr>@endforelse</tbody></table></div></div></div>

    <div class="col-lg-4"><div class="card mb-3"><div class="card-header"><h3 class="card-title">Akses Cepat</h3></div><div class="card-body"><div class="row g-2">
        <div class="col-6"><a class="btn btn-outline-primary w-100 quick-action" href="{{ route('mahasiswa.akademik.krs-render') }}">KRS</a></div><div class="col-6"><a class="btn btn-outline-primary w-100 quick-action" href="{{ route('mahasiswa.akademik.nilai') }}">Nilai</a></div><div class="col-6"><a class="btn btn-outline-primary w-100 quick-action" href="{{ route('mahasiswa.akademik.khs') }}">KHS</a></div><div class="col-6"><a class="btn btn-outline-primary w-100 quick-action" href="{{ route('mahasiswa.akademik.presensi') }}">Presensi</a></div><div class="col-6"><a class="btn btn-outline-primary w-100 quick-action" href="{{ route('mahasiswa.keuangan.tagihan') }}">Tagihan</a></div><div class="col-6"><a class="btn btn-outline-primary w-100 quick-action" href="{{ route('mahasiswa.informasi.kalender-akademik') }}">Kalender</a></div>
    </div></div></div>
    <div class="card"><div class="card-header"><h3 class="card-title">Aktivitas Terbaru</h3></div><div class="card-body">
        @forelse($aktivitas_terbaru ?? [] as $activity)<div class="d-flex mb-3"><span class="badge bg-{{ $activity['badge_color'] ?? 'secondary' }} me-2">{{ $activity['badge'] ?? 'Info' }}</span><div><div class="fw-medium">{{ $activity['title'] }}</div><div class="text-secondary small">{{ $activity['description'] }}</div><div class="text-secondary small">{{ $activity['time'] ? \Carbon\Carbon::parse($activity['time'])->locale('id')->diffForHumans() : '' }}</div></div></div>@empty<div class="text-secondary text-center py-3">Belum ada aktivitas terbaru.</div>@endforelse
    </div></div></div>

    <div class="col-lg-6"><div class="card h-100"><div class="card-header"><h3 class="card-title">Tagihan Aktif</h3><div class="card-actions"><a href="{{ route('mahasiswa.keuangan.tagihan') }}">Lihat semua</a></div></div><div class="table-responsive"><table class="table table-vcenter card-table"><thead><tr><th>Keterangan</th><th>Jatuh Tempo</th><th class="text-end">Jumlah</th></tr></thead><tbody>@forelse($tagihan_aktif ?? [] as $tagihan)<tr><td>{{ $tagihan['desc'] }}</td><td>{{ $tagihan['due_date'] ? \Carbon\Carbon::parse($tagihan['due_date'])->locale('id')->translatedFormat('d F Y') : '-' }}</td><td class="text-end">Rp {{ number_format($tagihan['amount'],0,',','.') }}</td></tr>@empty<tr><td colspan="3" class="text-center text-secondary py-4">Tidak ada tagihan aktif.</td></tr>@endforelse</tbody></table></div></div></div>

    <div class="col-lg-6"><div class="card h-100"><div class="card-header"><h3 class="card-title">Pengumuman</h3><div class="card-actions"><a href="{{ route('mahasiswa.informasi.pengumuman') }}">Lihat semua</a></div></div><div class="list-group list-group-flush">@forelse($pengumuman ?? [] as $item)<div class="list-group-item"><div class="fw-medium">{{ $item->name ?? '-' }}</div><div class="text-secondary small mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($item->content ?? ''),120) }}</div><div class="text-secondary small mt-1">{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->locale('id')->diffForHumans() : '' }}</div></div>@empty<div class="p-4 text-center text-secondary">Belum ada pengumuman.</div>@endforelse</div></div></div>
</div>
@endsection
