@extends('core-themes.core-backpage')

@section('custom-css')
<style>
    .stat-card{border-radius:15px;transition:.25s}
    .stat-card:hover{transform:translateY(-3px)}
    .stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center}
    .recent-activity{max-height:400px;overflow-y:auto}
    .activity-item{padding:1rem;border-left:3px solid #206bc4;margin-bottom:1rem;background:#f8f9fa;border-radius:0 8px 8px 0}
    .chart-container{position:relative;height:360px}
    .stit-dashboard-footer{text-align:center!important}
    .stit-dashboard-footer .container-xl{display:flex;justify-content:center}
    .stit-dashboard-footer .stit-footer-content{width:100%;text-align:center;padding:.75rem 0}
    .stit-dashboard-footer .stit-footer-title{font-weight:600;color:var(--tblr-body-color)}
    .stit-dashboard-footer .stit-footer-copy{font-size:.875rem;color:var(--tblr-secondary)}
</style>
@endsection

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0"><img src="{{ stit_profile_image_url($user->photo) }}" alt="Profile" class="rounded-circle" style="width:64px;height:64px;object-fit:cover;"></div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-1">Selamat datang, {{ $user->name }}!</h4>
                        <p class="text-muted mb-0">Berikut ringkasan aktivitas dan data akademik STIT Darul Ilmi Tasikmalaya.</p>
                    </div>
                    <div class="flex-shrink-0"><span class="badge bg-primary">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="card-title mb-1">Jadwal Kuliah Saya</h5>
            <div class="text-muted small">Jadwal perkuliahan untuk mata kuliah yang Anda ampu</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('dosen.akademik.jadwal') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-calendar-alt me-1"></i> Kelola Jadwal
            </a>
            <a href="{{ route('dosen.akademik.jadwal.export-pdf') }}" class="btn btn-danger btn-sm">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Mata Kuliah</th>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Jam Kuliah</th>
                    <th>Kelas</th>
                    <th>Ruang</th>
                    <th>Metode</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwalSaya as $item)
                    @php
                        $tanggal = $item->tanggal ?? null;
                        $hari = $item->hari ?? null;
                        $waktu = $item->waktuKuliah;
                        $waktuAttributes = $waktu ? $waktu->getAttributes() : [];
                        $jadwalAttributes = $item->getAttributes();
                        $ambilJam = function (array $attributes, array $keys) {
                            foreach ($keys as $key) {
                                if (array_key_exists($key, $attributes) && filled($attributes[$key])) return $attributes[$key];
                            }
                            return null;
                        };
                        $mulai = $ambilJam($jadwalAttributes, ['jam_mulai','waktu_mulai','start_time','start','mulai','time_start']);
                        $selesai = $ambilJam($jadwalAttributes, ['jam_selesai','waktu_selesai','end_time','end','selesai','time_end','time_ended']);
                        if (!$mulai) $mulai = $ambilJam($waktuAttributes, ['jam_mulai','waktu_mulai','start_time','start','mulai','time_start']);
                        if (!$selesai) $selesai = $ambilJam($waktuAttributes, ['jam_selesai','waktu_selesai','end_time','end','selesai','time_end','time_ended']);
                        $labelWaktu = $ambilJam($jadwalAttributes, ['jam','waktu','time','range_jam','jam_kuliah']);
                        if (!$labelWaktu) $labelWaktu = $ambilJam($waktuAttributes, ['jam','waktu','time','range_jam','jam_kuliah','name']);
                        $formatJam = function ($value) {
                            if (!$value) return null;
                            try { return \Carbon\Carbon::parse($value)->format('H:i'); } catch (\Throwable $e) { return $value; }
                        };
                        $mulai = $formatJam($mulai);
                        $selesai = $formatJam($selesai);
                        $kelas = $item->kelas->pluck('name')->filter()->join(', ');
                        $metode = $item->metode ?? data_get($item, 'method') ?? '-';
                    @endphp
                    <tr>
                        <td><div class="fw-semibold">{{ $item->mataKuliah->name ?? '-' }}</div>@if(!empty($item->mataKuliah->code))<div class="text-muted small">{{ $item->mataKuliah->code }}</div>@endif</td>
                        <td>{{ $tanggal ? \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y') : '-' }}</td>
                        <td>{{ $hari ?: ($tanggal ? \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('l') : '-') }}</td>
                        <td><span class="badge bg-blue-lt">@if($mulai || $selesai){{ $mulai ?: '-' }} - {{ $selesai ?: '-' }}@elseif($labelWaktu){{ $labelWaktu }}@else-@endif</span></td>
                        <td>{{ $kelas ?: '-' }}</td>
                        <td>{{ $item->ruang->name ?? '-' }}</td>
                        <td><span class="badge bg-green-lt">{{ $metode }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada jadwal kuliah untuk mata kuliah yang Anda ampu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3"><div class="card stat-card bg-primary text-white h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">Mahasiswa Terjangkau</h6><h2 class="mb-0">{{ number_format($totalStudents) }}</h2></div><div class="stat-icon bg-white bg-opacity-25"><i class="fas fa-users fa-2x"></i></div></div><div class="mt-3 small">Mahasiswa pada kelas yang Anda ajar</div></div></div></div>
    <div class="col-md-3 col-sm-6 mb-3"><div class="card stat-card bg-success text-white h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">Mata Kuliah</h6><h2 class="mb-0">{{ number_format($activeCourses) }}</h2></div><div class="stat-icon bg-white bg-opacity-25"><i class="fas fa-book fa-2x"></i></div></div><div class="mt-3 small">Mata kuliah yang Anda ampu</div></div></div></div>
    <div class="col-md-3 col-sm-6 mb-3"><div class="card stat-card bg-warning text-white h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">KRS Menunggu</h6><h2 class="mb-0">{{ number_format($krsPending) }}</h2></div><div class="stat-icon bg-white bg-opacity-25"><i class="fas fa-file-signature fa-2x"></i></div></div><div class="mt-3 small">Pengajuan yang perlu ditinjau</div></div></div></div>
    <div class="col-md-3 col-sm-6 mb-3"><div class="card stat-card bg-info text-white h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">Jadwal Kuliah</h6><h2 class="mb-0">{{ number_format($totalEvents) }}</h2></div><div class="stat-icon bg-white bg-opacity-25"><i class="fas fa-calendar-alt fa-2x"></i></div></div><div class="mt-3 small">Jadwal perkuliahan Anda</div></div></div></div>
</div>

<div class="row">
    <div class="col-md-8 mb-4"><div class="card h-100"><div class="card-header"><h5 class="card-title mb-0">Mata Kuliah Berdasarkan Program Studi</h5></div><div class="card-body"><div class="chart-container"><canvas id="courseDistributionChart"></canvas></div></div></div></div>
    <div class="col-md-4 mb-4"><div class="card h-100"><div class="card-header"><h5 class="card-title mb-0">Aktivitas Terbaru</h5></div><div class="card-body recent-activity">@forelse($activities as $activity)<div class="activity-item"><div class="d-flex justify-content-between"><h6 class="mb-1"><i class="fas {{ $activity['icon'] }} me-2 text-primary"></i>{{ $activity['title'] }}</h6><small class="text-muted">{{ optional($activity['time'])->diffForHumans() }}</small></div><p class="mb-0 text-muted">{{ $activity['description'] }}</p></div>@empty<div class="text-center py-4"><i class="fas fa-history fa-2x text-muted mb-2"></i><p class="text-muted mb-0">Belum ada aktivitas terbaru.</p></div>@endforelse</div></div></div>
</div>
@endsection

@section('custom-js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('courseDistributionChart');
    if (el && typeof Chart !== 'undefined') {
        new Chart(el.getContext('2d'), {type:'bar',data:{labels:@json($distribution->keys()->values()),datasets:[{label:'Mata Kuliah',data:@json($distribution->values()->values())}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
    }
    const oldFooter = document.querySelector('footer.footer');
    if (oldFooter) {
        oldFooter.classList.add('stit-dashboard-footer');
        oldFooter.innerHTML = `<div class="container-xl"><div class="stit-footer-content"><div class="stit-footer-title">STIT Darul Ilmi Tasikmalaya</div><div class="stit-footer-copy">Copyright © ${new Date().toLocaleDateString('id-ID', {month:'long', year:'numeric'})} STIT Darul Ilmi. All rights reserved.</div></div></div>`;
    }
    document.querySelectorAll('.settings').forEach(el => el.remove());
});
</script>
@endsection
