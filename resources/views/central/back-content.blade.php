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
@php
    $totalStudents = \App\Models\Mahasiswa::where('type', 1)->count();
    $activeCourses = \App\Models\Akademik\MataKuliah::count();
    $totalFaculty = \App\Models\Dosen::count();
    $totalEvents = \App\Models\Akademik\JadwalKuliah::count();

    $distribution = \App\Models\Mahasiswa::where('type', 1)
        ->with('programStudi')
        ->get()
        ->groupBy(fn($m) => $m->programStudi->name ?? $m->programStudi->nama ?? 'Belum Ditentukan')
        ->map(fn($items) => $items->count())
        ->sortDesc();

    $activities = collect();
    \App\Models\Mahasiswa::latest('created_at')->limit(5)->get()->each(function($item) use (&$activities){
        $activities->push(['title'=>'Mahasiswa Baru','description'=>($item->name ?? 'Mahasiswa').' ditambahkan ke sistem','time'=>$item->created_at,'icon'=>'fa-user-plus']);
    });
    \App\Models\Akademik\KRS::latest('created_at')->limit(5)->get()->each(function($item) use (&$activities){
        $activities->push(['title'=>'KRS Baru','description'=>'Pengajuan KRS baru tercatat','time'=>$item->created_at,'icon'=>'fa-file-alt']);
    });
    \App\Models\Akademik\JadwalKuliah::latest('created_at')->limit(5)->get()->each(function($item) use (&$activities){
        $activities->push(['title'=>'Jadwal Kuliah','description'=>'Jadwal perkuliahan baru ditambahkan','time'=>$item->created_at,'icon'=>'fa-calendar-alt']);
    });
    $activities = $activities->sortByDesc('time')->take(10);
@endphp

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0"><img src="{{ $user->photo }}" alt="Profile" class="rounded-circle" style="width:64px;height:64px;object-fit:cover;"></div>
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

@if(session('maintenance_success'))
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div><i class="fas fa-check-circle me-2"></i></div>
                <div>
                    <strong>{{ session('maintenance_success') }}</strong>
                    @if(session('maintenance_output'))
                        <div class="small mt-1"><code>{{ session('maintenance_output') }}</code></div>
                    @endif
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    </div>
</div>
@elseif(session('maintenance_error'))
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>{{ session('maintenance_error') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    </div>
</div>
@endif

@if($user && (int) $user->raw_type === 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="mb-1"><i class="fas fa-tools me-2"></i>Maintenance Sistem</h5>
                    <p class="text-muted mb-0">Kelola cache Laravel dan jalankan perintah Composer dari Maintenance Sistem.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('web-admin.maintenance.clear-cache') }}" onsubmit="return confirm('Bersihkan cache dan optimasi Laravel sekarang?');">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-broom me-2"></i>Bersihkan Cache Laravel
                        </button>
                    </form>
                    <form method="POST" action="{{ route('web-admin.maintenance.storage-link') }}" onsubmit="return confirm('Jalankan php artisan storage:link sekarang?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-info">
                            <i class="fas fa-link me-2"></i>Storage Link
                        </button>
                    </form>
                    <form method="POST" action="{{ route('web-admin.maintenance.clear-routes') }}" onsubmit="return confirm('Jalankan php artisan route:clear sekarang?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-route me-2"></i>Clear Route Cache
                        </button>
                    </form>
                    <form method="POST" action="{{ route('web-admin.maintenance.clear-views') }}" onsubmit="return confirm('Jalankan php artisan view:clear sekarang?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fas fa-file-code me-2"></i>Clear View Cache
                        </button>
                    </form>
                    <a href="{{ route('web-admin.maintenance.composer') }}" class="btn btn-dark">
                        <i class="fas fa-terminal me-2"></i>Terminal Composer
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card bg-primary text-white h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">Total Mahasiswa Aktif</h6><h2 class="mb-0">{{ number_format($totalStudents) }}</h2></div><div class="stat-icon bg-white bg-opacity-25"><i class="fas fa-users fa-2x"></i></div></div><div class="mt-3 small">Data mahasiswa aktif</div></div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card bg-success text-white h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">Mata Kuliah</h6><h2 class="mb-0">{{ number_format($activeCourses) }}</h2></div><div class="stat-icon bg-white bg-opacity-25"><i class="fas fa-book fa-2x"></i></div></div><div class="mt-3 small">Mata kuliah terdaftar</div></div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card bg-warning text-white h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">Total Dosen</h6><h2 class="mb-0">{{ number_format($totalFaculty) }}</h2></div><div class="stat-icon bg-white bg-opacity-25"><i class="fas fa-chalkboard-teacher fa-2x"></i></div></div><div class="mt-3 small">Dosen terdaftar</div></div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card bg-info text-white h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">Jadwal Kuliah</h6><h2 class="mb-0">{{ number_format($totalEvents) }}</h2></div><div class="stat-icon bg-white bg-opacity-25"><i class="fas fa-calendar-alt fa-2x"></i></div></div><div class="mt-3 small">Jadwal perkuliahan aktif</div></div></div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="card-title mb-0">Distribusi Mahasiswa per Program Studi</h5></div>
            <div class="card-body"><div class="chart-container"><canvas id="studentDistributionChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="card-title mb-0">Aktivitas Terbaru</h5></div>
            <div class="card-body recent-activity">
                @forelse($activities as $activity)
                    <div class="activity-item">
                        <div class="d-flex justify-content-between"><h6 class="mb-1"><i class="fas {{ $activity['icon'] }} me-2 text-primary"></i>{{ $activity['title'] }}</h6><small class="text-muted">{{ optional($activity['time'])->diffForHumans() }}</small></div>
                        <p class="mb-0 text-muted">{{ $activity['description'] }}</p>
                    </div>
                @empty
                    <div class="text-center py-4"><i class="fas fa-history fa-2x text-muted mb-2"></i><p class="text-muted mb-0">Belum ada aktivitas terbaru.</p></div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@section('custom-js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('studentDistributionChart');
    if (el && typeof Chart !== 'undefined') {
        new Chart(el.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($distribution->keys()->values()),
                datasets: [{ label: 'Mahasiswa', data: @json($distribution->values()->values()) }]
            },
            options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } } }
        });
    }

    // Ganti footer bawaan template dengan footer resmi STIT Darul Ilmi.
    const oldFooter = document.querySelector('footer.footer');
    if (oldFooter) {
        oldFooter.classList.add('stit-dashboard-footer');
        oldFooter.innerHTML = `
            <div class="container-xl">
                <div class="stit-footer-content">
                    <div class="stit-footer-title">STIT Darul Ilmi Tasikmalaya</div>
                    <div class="stit-footer-copy">Copyright © ${new Date().toLocaleDateString('id-ID', {month:'long', year:'numeric'})} STIT Darul Ilmi. All rights reserved.</div>
                </div>
            </div>`;
    }

    // Hilangkan kontrol demo/template yang tidak diperlukan pada dashboard.
    document.querySelectorAll('.settings').forEach(el => el.remove());
});
</script>
@endsection
@endsection
