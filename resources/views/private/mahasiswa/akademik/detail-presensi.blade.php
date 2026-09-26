@extends('core-themes.core-backpage')

@section('title', 'Detail Presensi - ' . $mataKuliah->nama)

@push('styles')
<style>
    .attendance-card {
        margin-bottom: 1.5rem;
        transition: all 0.2s ease-in-out;
    }
    .attendance-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .attendance-date {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    .attendance-time {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }
    .attendance-status {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.8125rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .status-hadir { background-color: #dcfce7; color: #166534; }
    .status-izin { background-color: #dbeafe; color: #1e40af; }
    .status-sakit { background-color: #fef3c7; color: #92400e; }
    .status-alpha { background-color: #fee2e2; color: #991b1b; }
    .status-pending { 
        background-color: #f3f4f6; 
        color: #4b5563;
    }
    .status-late { 
        background-color: #fef3c7; 
        color: #92400e;
        position: relative;
        padding-left: 1.5rem;
    }
    .status-late::before {
        content: "";
        position: absolute;
        left: 0.5rem;
        top: 50%;
        transform: translateY(-50%);
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 9999px;
        background-color: #92400e;
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .lecturer-info {
        font-size: 0.875rem;
        color: #4b5563;
        margin-top: 0.5rem;
    }
    .lecturer-name {
        font-weight: 500;
        color: #1f2937;
    }
    .no-presence {
        text-align: center;
        padding: 3rem 1.5rem;
        background-color: #f9fafb;
        border-radius: 8px;
        color: #6b7280;
    }
    .stats-card {
        text-align: center;
        padding: 1.25rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .stats-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
        margin: 0.5rem 0;
    }
    .stats-label {
        font-size: 0.875rem;
        color: #6b7280;
    }
    .progress-thin {
        height: 6px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page title -->
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Detail Presensi
                </h2>
                <div class="text-muted mt-1">
                    {{ $mataKuliah->name ?? $mataKuliah->nama ?? '-' }} ({{ $mataKuliah->code ?? $mataKuliah->kode_mk ?? '-' }})
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('mahasiswa.akademik.presensi') }}" class="btn btn-outline-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M9 11l-4 4l4 4m-4 -4h11a4 4 0 0 0 0 -8h-1" />
                    </svg>
                    Kembali
                </a>
                <a href="#" class="btn btn-primary d-none d-sm-inline-block ms-2" onclick="window.print()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                        <rect x="7" y="13" width="10" height="8" rx="2" />
                    </svg>
                    Cetak
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Stats Cards -->
            @php
                $total = $presensi->count();
                $hadir = $presensi->where('status', 'H')->count();
                $izin = $presensi->where('status', 'I')->count();
                $sakit = $presensi->where('status', 'S')->count();
                $alpha = $presensi->where('status', 'A')->count();
                $persentase = $total > 0 ? round(($hadir / $total) * 100) : 0;
                $isLate = $presensi->where('keterlambatan', '>', 0)->count() > 0;
            @endphp
            
            <div class="row row-cards mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card stats-card" style="border-top: 3px solid #10b981;">
                        <div class="card-body p-3 text-center">
                            <div class="stats-value text-success">{{ $hadir }}</div>
                            <div class="stats-label">Hadir</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card stats-card" style="border-top: 3px #3b82f6 solid;">
                        <div class="card-body p-3 text-center">
                            <div class="stats-value text-primary">{{ $izin }}</div>
                            <div class="stats-label">Izin</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card stats-card" style="border-top: 3px solid #f59e0b;">
                        <div class="card-body p-3 text-center">
                            <div class="stats-value text-warning">{{ $sakit }}</div>
                            <div class="stats-label">Sakit</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card stats-card" style="border-top: 3px solid #ef4444;">
                        <div class="card-body p-3 text-center">
                            <div class="stats-value text-danger">{{ $alpha }}</div>
                            <div class="stats-label">Alpha</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <div>Kehadiran</div>
                        <div>{{ $persentase }}% ({{ $hadir }}/{{ $total }})</div>
                    </div>
                    <div class="progress progress-thin">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $persentase }}%" 
                             aria-valuenow="{{ $persentase }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                    @if($isLate)
                        <div class="mt-2 text-warning">
                            <small>
                                <i class="fas fa-exclamation-triangle me-1"></i> Terdapat keterlambatan pada beberapa pertemuan
                            </small>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Attendance List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Presensi</h3>
                </div>
                <div class="card-body">
                    @if($presensi->isEmpty())
                        <div class="no-presence">
                            <p class="h4">Belum ada data presensi</p>
                            <p class="text-muted mb-0">Data akan muncul setelah dosen mengisi daftar hadir.</p>
                        </div>
                    @else
                        @foreach($presensi as $attendance)
                            @php
                                $status = $attendance->status;
                                $statusClass = 'status-' . strtolower($status);
                            @endphp
                            <div class="card attendance-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="attendance-date">Pertemuan {{ $attendance->pertemuan }}</div>
                                            <div class="attendance-time">
                                                {{ $attendance->created_at ? $attendance->created_at->locale('id')->translatedFormat('d F Y, H:i') : '-' }}
                                            </div>
                                        </div>
                                        <span class="attendance-status {{ $statusClass }}">{{ $status }}</span>
                                    </div>
                                    @if($attendance->catatan)
                                        <div class="mt-2 alert alert-info p-2 mb-0">{{ $attendance->catatan }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[onclick="window.print()"]').forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            window.print();
        });
    });
});
</script>
@endpush
