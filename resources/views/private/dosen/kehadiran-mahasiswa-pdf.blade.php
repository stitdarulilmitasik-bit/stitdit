<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Kehadiran {{ $mahasiswa->name ?? 'Mahasiswa' }}</title>
<style>
    @page { size: A4 landscape; margin: 12mm 12mm 16mm 12mm; }
    * { box-sizing: border-box; }
    body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 7.5pt; color: #111; margin: 0; }
    .kop { border-bottom: 3px solid #111; padding-bottom: 6px; margin-bottom: 10px; }
    .kop-table { width: 100%; border-collapse: collapse; }
    .kop-logo { width: 82px; text-align: center; vertical-align: middle; }
    .kop-logo img { width: 88px; height: 88px; object-fit: contain; }
    .kop-text { text-align: center; vertical-align: middle; line-height: 1.2; }
    .kop-text .a { font-size: 11pt; font-weight: bold; }
    .kop-text .b { font-size: 14pt; font-weight: bold; }
    .kop-text .c { font-size: 7.5pt; font-weight: bold; }
    .kop-text .d { font-size: 7pt; }
    .title { text-align: center; margin: 4px 0 8px; }
    .title-main { font-size: 11pt; font-weight: bold; text-decoration: underline; }
    .title-sub { font-size: 7.5pt; margin-top: 2px; }
    .info { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    .info td { padding: 2px 3px; vertical-align: top; }
    .info .label { width: 88px; font-weight: bold; }
    table.data { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .data th, .data td { overflow: hidden; }
    .data .meeting { width: 2.5%; }
    .data .summary { width: 5.6%; }
    .data th, .data td { border: 1px solid #333; padding: 3px 2px; vertical-align: middle; }
    .data th { background: #eeeeee; text-align: center; font-weight: bold; font-size: 7pt; }
    .data td { font-size: 7pt; }
    .center { text-align: center; }
    .matkul { text-align: left; }
    .matkul strong { font-size: 7.2pt; }
    .matkul small { color: #555; font-size: 6.2pt; }
    .hadir { font-weight: bold; }
    .footer { position: fixed; left: 0; right: 0; bottom: -9mm; border-top: 1px solid #777; padding-top: 3px; text-align: center; font-size: 6.5pt; color: #555; }
</style>
</head>
<body>
@php
    $logo = null;
    $logoCandidates = [
        storage_path('app/public/images/logo/logo-vert1.png'),
    ];
    foreach ($logoCandidates as $candidate) {
        if (is_file($candidate)) {
            $mime = mime_content_type($candidate) ?: 'image/png';
            $logo = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($candidate));
            break;
        }
    }
@endphp

<div class="kop">
    <table class="kop-table">
        <tr>
            <td class="kop-logo">@if($logo)<img src="{{ $logo }}" alt="Logo STIT Darul Ilmi">@endif</td>
            <td class="kop-text">
                <div class="a">SEKOLAH TINGGI ILMU TARBIYAH</div>
                <div class="b">STIT DARUL ILMI TASIKMALAYA</div>
                <div class="c">SK Menteri Agama RI No. 536 Tahun 2026</div>
                <div class="d">Alamat : Jl. Cirahayu Sindangraja Jamanis Kabupaten Tasikmalaya Jawa Barat 46175</div>
            </td>
            <td style="width:82px"></td>
        </tr>
    </table>
</div>

<div class="title">
    <div class="title-main">REKAP KEHADIRAN MAHASISWA</div>
    <div class="title-sub">Semester {{ $semester }}{{ $nilai->first()->tahunAkademik ? ' | Tahun Akademik ' . ($nilai->first()->tahunAkademik->name ?? $nilai->first()->tahunAkademik->code ?? '') : '' }}</div>
</div>

<table class="info">
    <tr>
        <td class="label">Nama Mahasiswa</td><td><strong>: {{ $mahasiswa->name ?? '-' }}</strong></td>
        <td class="label">NIM</td><td><strong>: {{ $mahasiswa->numb_nim ?? $mahasiswa->nim ?? '-' }}</strong></td>
    </tr>
    <tr>
        <td class="label">Program Studi</td><td>: {{ $mahasiswa->programStudi->name ?? '-' }}</td>
        <td class="label">Dicetak</td><td>: {{ now()->format('d-m-Y H:i') }}</td>
    </tr>
</table>

<table class="data">
    <colgroup>
        <col style="width:3%">
        <col style="width:23%">
        @for($i = 1; $i <= 16; $i++)<col class="meeting" style="width:2.5%">@endfor
        <col class="summary" style="width:5.6%">
        <col class="summary" style="width:5.6%">
        <col class="summary" style="width:5.6%">
        <col class="summary" style="width:5.6%">
        <col class="summary" style="width:5.6%">
    </colgroup>
    <thead>
        <tr>
            <th style="width:3%">No.</th>
            <th style="width:23%">Mata Kuliah</th>
            @for($i = 1; $i <= 16; $i++)<th class="meeting">P{{ $i }}</th>@endfor
            <th class="summary">Hadir</th>
            <th class="summary">Izin</th>
            <th class="summary">Sakit</th>
            <th class="summary">Alpa</th>
            <th class="summary">% Hadir</th>
        </tr>
    </thead>
    <tbody>
    @forelse($nilai as $index => $n)
        @php
            $att = $n->kehadiranMahasiswa->keyBy('pertemuan');
            $hadir = $att->where('status', 'Hadir')->count();
            $izin = $att->where('status', 'Izin')->count();
            $sakit = $att->where('status', 'Sakit')->count();
            $alpa = $att->where('status', 'Alpa')->count();
            $total = $att->count();
            $persentase = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;
        @endphp
        <tr>
            <td class="center">{{ $index + 1 }}</td>
            <td class="matkul">
                <strong>{{ $n->mataKuliah->name ?? '-' }}</strong>
                @if($n->mataKuliah->code)<br><small>{{ $n->mataKuliah->code }}</small>@endif
            </td>
            @for($i = 1; $i <= 16; $i++)
                @php $a = $att[$i] ?? null; @endphp
                <td class="center">
                    @if(($a->status ?? '') === 'Hadir')
                        <span class="hadir">✓</span>
                    @elseif(($a->status ?? '') === 'Izin') I
                    @elseif(($a->status ?? '') === 'Sakit') S
                    @elseif(($a->status ?? '') === 'Alpa') A
                    @else —
                    @endif
                </td>
            @endfor
            <td class="center">{{ $hadir }}</td>
            <td class="center">{{ $izin }}</td>
            <td class="center">{{ $sakit }}</td>
            <td class="center">{{ $alpa }}</td>
            <td class="center"><strong>{{ number_format($persentase, 2) }}%</strong></td>
        </tr>
    @empty
        <tr><td colspan="23" class="center">Tidak ada data kehadiran.</td></tr>
    @endforelse
    </tbody>
</table>

<div class="footer">Printed via SIAKAD STIT-Darul Ilmi Tasikmalaya &nbsp;|&nbsp; Halaman <span class="page"></span></div>
</body>
</html>