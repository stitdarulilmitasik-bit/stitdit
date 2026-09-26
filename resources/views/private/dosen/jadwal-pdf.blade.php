<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Mengajar Dosen</title>
    <style>
        @page { margin: 25px 30px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        .kop { width: 100%; border-bottom: 3px solid #111; padding-bottom: 9px; margin-bottom: 15px; }
        .kop-table { width: 100%; border-collapse: collapse; }
        .logo { width: 125px; text-align: center; vertical-align: middle; }
        .logo img { width: 105px; height: 105px; object-fit: contain; }
        .kop-text { text-align: center; line-height: 1.35; }
        .kop-text .line1 { font-size: 15px; font-weight: bold; }
        .kop-text .line2 { font-size: 18px; font-weight: bold; }
        .kop-text .line3 { font-size: 10px; font-weight: bold; }
        .kop-text .address { font-size: 9px; }
        h2 { text-align: center; font-size: 14px; margin: 12px 0 4px; }
        .subtitle { text-align: center; font-size: 10px; margin-bottom: 14px; }
        .info { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .info td { padding: 3px 4px; vertical-align: top; }
        .info .label { width: 110px; font-weight: bold; }
        table.schedule { width: 100%; border-collapse: collapse; }
        .schedule th, .schedule td { border: 1px solid #333; padding: 6px 5px; }
        .schedule th { background: #e9ecef; text-align: center; font-weight: bold; }
        .schedule td { vertical-align: middle; }
        .center { text-align: center; }
        .footer { margin-top: 18px; font-size: 8px; color: #555; text-align: right; }
    </style>
</head>
<body>
    @include('shared.pdf.kop-surat')
<h2>JADWAL MENGAJAR DOSEN</h2>
    <div class="subtitle">STIT Darul Ilmi Tasikmalaya</div>

    <table class="info">
        <tr><td class="label">Nama Dosen</td><td>: {{ $user->name }}</td></tr>
        @if(!empty($user->numb_nidn))
            <tr><td class="label">NIDN</td><td>: {{ $user->numb_nidn }}</td></tr>
        @endif
        <tr><td class="label">Dicetak</td><td>: {{ now()->locale('id')->translatedFormat('l, d F Y H:i') }}</td></tr>
    </table>

    <table class="schedule">
        <thead>
            <tr>
                <th width="4%">No.</th>
                <th width="22%">Mata Kuliah</th>
                <th width="18%">Hari, Tanggal</th>
                <th width="15%">Jam Kuliah</th>
                <th width="12%">Kelas</th>
                <th width="10%">Ruang</th>
                <th width="15%">Metode</th>
            </tr>
        </thead>
        <tbody>
        @forelse($jadwalSaya as $no => $item)
            @php
                $waktu = $item->waktuKuliah;
                $mulai = $waktu?->time_start;
                $selesai = $waktu?->time_ended;
                $kelas = $item->kelas->pluck('name')->filter()->join(', ');
                $tanggal = $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->locale('id') : null;
                $hari = $item->hari ?: ($tanggal ? $tanggal->translatedFormat('l') : '-');
                $tanggalText = $tanggal ? $tanggal->translatedFormat('d F Y') : '-';
            @endphp
            <tr>
                <td class="center">{{ $no + 1 }}</td>
                <td><strong>{{ $item->mataKuliah->name ?? '-' }}</strong>@if(!empty($item->mataKuliah->code))<br><small>{{ $item->mataKuliah->code }}</small>@endif</td>
                <td class="center"><strong>{{ $hari }}</strong><br>{{ $tanggalText }}</td>
                <td class="center">{{ $mulai ? \Carbon\Carbon::parse($mulai)->format('H:i') : '-' }} - {{ $selesai ? \Carbon\Carbon::parse($selesai)->format('H:i') : '-' }}</td>
                <td class="center">{{ $kelas ?: '-' }}</td>
                <td class="center">{{ $item->ruang->name ?? '-' }}</td>
                <td class="center">{{ $item->metode ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="center">Belum ada jadwal mengajar.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="footer">Dokumen dihasilkan oleh Sistem Informasi Akademik STIT Darul Ilmi Tasikmalaya.</div>
</body>
</html>
