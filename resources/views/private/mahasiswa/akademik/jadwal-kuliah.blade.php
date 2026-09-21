@extends('core-themes.core-mainpage')

@section('title', 'Jadwal Kuliah')

@section('custom-css')
<style>
    .schedule-day { margin-bottom: 2rem; }
    .schedule-day:last-child { margin-bottom: 0; }
    .schedule-card { margin-bottom: 1rem; transition: all .2s ease-in-out; }
    .schedule-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.08); }
    .day-header { background: #f5f7fb; padding: .75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-weight: 600; color: #3b82f6; display:flex; justify-content:space-between; align-items:center; }
    .time-badge { font-size:.75rem; font-weight:600; padding:.25rem .5rem; border-radius:4px; background:#e0f2fe; color:#0369a1; }
    .course-code,.lecturer,.room-info { font-size:.8125rem; color:#4b5563; margin-top:.5rem; }
    .room-info { display:flex; align-items:center; }
    .room-icon { margin-right:.375rem; color:#6b7280; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Jadwal Kuliah</h2>
                <div class="text-muted mt-1">Semester {{ $currentSemester->nama ?? $currentSemester->name ?? 'Aktif' }} - {{ $currentSemester->tahun_ajaran ?? $currentSemester->type ?? '' }}</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <div class="dropdown">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">Pilih Semester</button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item {{ !request()->has('semester') ? 'active' : '' }}" href="{{ route('mahasiswa.akademik.jadwal') }}">Semester Aktif</a>
                            <div class="dropdown-divider"></div>
                            @foreach(($availableSemesters ?? collect()) as $semester)
                                <a class="dropdown-item {{ request('semester') == $semester->id ? 'active' : '' }}" href="{{ route('mahasiswa.akademik.jadwal.semester', $semester->id) }}">{{ $semester->nama ?? $semester->name ?? '' }} - {{ $semester->tahun_ajaran ?? $semester->type ?? '' }}</a>
                            @endforeach
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="window.print()">Cetak Jadwal</button>
                </div>
            </div>
        </div>
    </div>

    @if(empty($jadwal) || $jadwal->isEmpty())
        <div class="card"><div class="card-body"><div class="empty"><p class="empty-title">Tidak ada jadwal kuliah</p><p class="empty-subtitle text-muted">Belum ada jadwal untuk semester ini atau KRS Anda belum memiliki kelas.</p></div></div></div>
    @else
        @php
            $days = ['Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Minggu'];
        @endphp
        <div class="row">
            @foreach($days as $engDay => $idnDay)
                @php $schedules = $jadwal->get($engDay, collect()); @endphp
                @if($schedules->isNotEmpty())
                    <div class="col-md-6 col-lg-4">
                        <div class="schedule-day">
                            <div class="day-header">
                                <span class="day-header-title">{{ $idnDay }}</span>
                                <span class="badge bg-blue-lt">{{ $schedules->count() }} Mata Kuliah</span>
                            </div>
                            @foreach($schedules as $schedule)
                                <div class="card schedule-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <h3 class="card-title mb-1">{{ $schedule->mataKuliah->nama ?? $schedule->mataKuliah->name ?? 'Mata Kuliah' }}</h3>
                                                <div class="course-code">{{ $schedule->mataKuliah->kode_mk ?? $schedule->mataKuliah->code ?? '-' }} - {{ $schedule->mataKuliah->sks ?? $schedule->mataKuliah->bsks ?? 0 }} SKS</div>
                                            </div>
                                            <span class="time-badge">{{ $schedule->waktuKuliah ? \Carbon\Carbon::parse($schedule->waktuKuliah->time_start)->format('H:i') : '-' }} - {{ $schedule->waktuKuliah ? \Carbon\Carbon::parse($schedule->waktuKuliah->time_ended)->format('H:i') : '-' }}</span>
                                        </div>
                                        @if($schedule->dosen)<div class="lecturer">👤 {{ $schedule->dosen->nama_lengkap ?? $schedule->dosen->name }}</div>@endif
                                        @if($schedule->ruang)<div class="room-info"><span class="room-icon">▣</span>{{ $schedule->ruang->nama_ruang ?? $schedule->ruang->name }} @if($schedule->ruang->kode_ruang)({{ $schedule->ruang->kode_ruang }})@endif</div>@endif
                                        @if($schedule->kelas && $schedule->kelas->isNotEmpty())
                                            @php
                                                $classNames = $schedule->kelas->map(function ($kelas) {
                                                    return $kelas->nama_kelas ?? $kelas->name ?? null;
                                                })->filter()->unique()->implode(', ');
                                            @endphp
                                            @if($classNames)
                                                <div class="mt-2"><span class="badge bg-azure-lt">{{ $classNames }}</span></div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
@endsection

@section('custom-js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const currentDay = days[new Date().getDay()];
    document.querySelectorAll('.day-header-title').forEach(function (header) {
        if (header.textContent.trim() === currentDay) {
            header.closest('.day-header').classList.add('bg-primary-lt');
            header.textContent += ' (Hari Ini)';
        }
    });
});
</script>
@endsection
