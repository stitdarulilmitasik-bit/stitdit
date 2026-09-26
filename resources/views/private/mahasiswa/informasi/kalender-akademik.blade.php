@extends('core-themes.core-mainpage')

@section('title', 'Kalender Akademik')

@section('custom-css')
<style>
    .academic-calendar-wrap { overflow-x: auto; }
    .academic-calendar {
        min-width: 980px;
        border: 1px solid #e6e9ee;
        border-radius: .75rem;
        overflow: hidden;
        background: #fff;
    }
    .calendar-weekdays,
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
    }
    .calendar-weekday {
        padding: .75rem .65rem;
        text-align: center;
        font-size: .78rem;
        font-weight: 700;
        color: #667085;
        background: #f8fafc;
        border-right: 1px solid #e6e9ee;
        border-bottom: 1px solid #e6e9ee;
    }
    .calendar-weekday:last-child { border-right: 0; }
    .calendar-day {
        min-height: 145px;
        padding: .55rem;
        border-right: 1px solid #e6e9ee;
        border-bottom: 1px solid #e6e9ee;
        background: #fff;
    }
    .calendar-grid .calendar-day:nth-child(7n) { border-right: 0; }
    .calendar-day.muted { background: #fafafa; color: #98a2b3; }
    .calendar-day.today { box-shadow: inset 0 0 0 2px rgba(13, 110, 253, .25); }
    .day-number {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 700;
        margin-bottom: .35rem;
    }
    .calendar-day.today .day-number {
        background: var(--tblr-primary, #206bc4);
        color: #fff;
    }
    .calendar-event {
        display: block;
        margin-top: .3rem;
        padding: .38rem .45rem;
        border-radius: .45rem;
        background: #eef6ff;
        border-left: 3px solid #206bc4;
        font-size: .72rem;
        line-height: 1.25;
    }
    .calendar-event.academic {
        background: #f1f8f3;
        border-left-color: #2fb344;
    }
    .calendar-event .event-title {
        font-weight: 700;
        color: #344054;
    }
    .calendar-event .event-meta {
        color: #667085;
        margin-top: .15rem;
    }
    .calendar-empty {
        color: #98a2b3;
        font-size: .72rem;
        padding-top: .2rem;
    }
    @media (max-width: 767.98px) {
        .academic-calendar { min-width: 900px; }
        .calendar-day { min-height: 125px; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Kalender Akademik</h2>
                <div class="text-muted mt-1">
                    Kalender kegiatan akademik dan jadwal kuliah Anda
                    @if($currentSemester)
                        · Semester {{ $currentSemester->type ?? $currentSemester->nama ?? $currentSemester->name ?? '-' }}
                        @if($currentSemester->name && $currentSemester->type && $currentSemester->name !== $currentSemester->type)
                            · {{ $currentSemester->name }}
                        @endif
                    @endif
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a class="btn btn-outline-primary" href="{{ route('mahasiswa.informasi.kalender-akademik', ['year' => $monthStart->copy()->subMonth()->year, 'month' => $monthStart->copy()->subMonth()->month]) }}">‹</a>
                    <a class="btn btn-primary" href="{{ route('mahasiswa.informasi.kalender-akademik', ['year' => $today->year, 'month' => $today->month]) }}">Bulan Ini</a>
                    <a class="btn btn-outline-primary" href="{{ route('mahasiswa.informasi.kalender-akademik', ['year' => $monthStart->copy()->addMonth()->year, 'month' => $monthStart->copy()->addMonth()->month]) }}">›</a>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">Cetak</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <div class="w-100 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h3 class="card-title mb-0">{{ $monthStart->locale('id')->translatedFormat('F Y') }}</h3>
                <div class="d-flex gap-2 small">
                    <span><span class="badge bg-green-lt">Kegiatan Akademik</span></span>
                    <span><span class="badge bg-blue-lt">Jadwal Kuliah</span></span>
                </div>
            </div>
        </div>
        <div class="card-body p-2">
            <div class="academic-calendar-wrap">
                <div class="academic-calendar">
                    <div class="calendar-weekdays">
                        @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $weekday)
                            <div class="calendar-weekday">{{ $weekday }}</div>
                        @endforeach
                    </div>

                    <div class="calendar-grid">
                        @foreach($days as $day)
                            @php
                                $dateKey = $day->toDateString();
                                $isCurrentMonth = $day->month === $monthStart->month;
                                $isToday = $day->isSameDay($today);
                                $daySchedules = $scheduleByDate->get($dateKey, collect());
                                $dayEvents = $items->filter(function ($item) use ($day) {
                                    $start = CarbonCarbon::parse($item->start_date)->startOfDay();
                                    $end = $item->ended_date
                                        ? CarbonCarbon::parse($item->ended_date)->endOfDay()
                                        : $start->copy()->endOfDay();
                                    return $day->betweenIncluded($start, $end);
                                });
                            @endphp

                            <div class="calendar-day {{ $isCurrentMonth ? '' : 'muted' }} {{ $isToday ? 'today' : '' }}">
                                <div class="day-number">{{ $day->day }}</div>

                                @foreach($dayEvents as $event)
                                    <div class="calendar-event academic">
                                        <div class="event-title">{{ $event->title ?? $event->name ?? 'Kegiatan Akademik' }}</div>
                                        @if($event->ended_date && $event->start_date != $event->ended_date)
                                            <div class="event-meta">
                                                {{ CarbonCarbon::parse($event->start_date)->locale('id')->translatedFormat('d M') }}
                                                – {{ CarbonCarbon::parse($event->ended_date)->locale('id')->translatedFormat('d M') }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                                @foreach($daySchedules as $schedule)
                                    @php
                                        $courseName = $schedule->mataKuliah->name ?? $schedule->mataKuliah->nama ?? 'Mata Kuliah';
                                        $courseCode = $schedule->mataKuliah->code ?? $schedule->mataKuliah->kode_mk ?? '-';
                                        $timeStart = $schedule->waktuKuliah?->time_start ? CarbonCarbon::parse($schedule->waktuKuliah->time_start)->format('H:i') : '-';
                                        $timeEnd = $schedule->waktuKuliah?->time_ended ? CarbonCarbon::parse($schedule->waktuKuliah->time_ended)->format('H:i') : '-';
                                        $lecturer = $schedule->dosen->nama_lengkap ?? $schedule->dosen->name ?? null;
                                        $room = $schedule->ruang->nama_ruang ?? $schedule->ruang->name ?? null;
                                    @endphp
                                    <div class="calendar-event" title="{{ $courseName }}">
                                        <div class="event-title">{{ $timeStart }}–{{ $timeEnd }} · {{ $courseName }}</div>
                                        <div class="event-meta">{{ $courseCode }} @if($lecturer) · {{ $lecturer }} @endif</div>
                                        @if($room)
                                            <div class="event-meta">Ruang: {{ $room }}</div>
                                        @endif
                                        @if($schedule->metode)
                                            <div class="event-meta">{{ $schedule->metode }}</div>
                                        @endif
                                    </div>
                                @endforeach

                                @if($dayEvents->isEmpty() && $daySchedules->isEmpty())
                                    <div class="calendar-empty">Tidak ada agenda</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info">
        <div class="fw-semibold">Jadwal kuliah mengikuti data jadwal yang dibuat oleh dosen/admin/operator.</div>
        <div class="small mt-1">Yang ditampilkan adalah jadwal yang terhubung dengan KRS Anda pada semester aktif dan memiliki tanggal kuliah pada bulan yang sedang dibuka.</div>
    </div>
</div>
@endsection
