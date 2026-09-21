<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Kehadiran {{ $mataKuliah->name ?? 'Mata Kuliah' }}</title>
<style>
    @page { size: A4 landscape; margin: 12mm 12mm 16mm; }
    * { box-sizing:border-box; }
    body { font-family:"DejaVu Sans",Arial,sans-serif; font-size:7.5pt; color:#111; margin:0; }
    .kop { border-bottom:3px solid #111; padding-bottom:6px; margin-bottom:10px; }
    .kop-table { width:100%; border-collapse:collapse; }
    .kop-logo { width:82px; text-align:center; vertical-align:middle; }
    .kop-logo img { width:88px; height:88px; object-fit:contain; }
    .kop-text { text-align:center; vertical-align:middle; line-height:1.2; }
    .kop-text .a { font-size:11pt; font-weight:bold; }
    .kop-text .b { font-size:14pt; font-weight:bold; }
    .kop-text .c { font-size:7.5pt; font-weight:bold; }
    .kop-text .d { font-size:7pt; }
    .title { text-align:center; margin:4px 0 8px; }
    .title-main { font-size:11pt; font-weight:bold; text-decoration:underline; }
    .title-sub { font-size:7.5pt; margin-top:2px; }
    .info { width:100%; border-collapse:collapse; margin-bottom:8px; }
    .info td { padding:2px 3px; vertical-align:top; }
    .info .label { width:90px; font-weight:bold; }
    table.data { width:100%; border-collapse:collapse; table-layout:fixed; }
    .data th,.data td { border:1px solid #333; padding:3px 2px; vertical-align:middle; overflow:hidden; }
    .data th { background:#eee; text-align:center; font-weight:bold; font-size:7pt; }
    .data td { font-size:7pt; }
    .center { text-align:center; }
    .student { text-align:left; }
    .student strong { font-size:7.2pt; }
    .student small { color:#555; font-size:6.2pt; }
    .footer { position:fixed; left:0; right:0; bottom:-9mm; border-top:1px solid #777; padding-top:3px; text-align:center; font-size:6.5pt; color:#555; }
</style>
</head>
<body>
@php
    $logo = null;
    $logoCandidates = [
        storage_path('app/public/images/logo/logo-vert.png'),
        public_path('storage/images/logo/logo-vert.png'),
    ];
    foreach ($logoCandidates as $candidate) {
        if (is_file($candidate)) {
            $mime = mime_content_type($candidate) ?: 'image/png';
            $logo = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($candidate));
            break;
        }
    }
    $tahunAkademik = $nilai->first()->tahunAkademik ?? null;
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
    <div class="title-sub">Semester {{ $semester }}{{ $tahunAkademik ? ' | Tahun Akademik ' . ($tahunAkademik->name ?? $tahunAkademik->code ?? '') : '' }}</div>
</div>

<table class="info">
    <tr>
        <td class="label">Mata Kuliah</td><td><strong>: {{ $mataKuliah->name ?? '-' }}</strong></td>
        <td class="label">Kode</td><td><strong>: {{ $mataKuliah->code ?? '-' }}</strong></td>
    </tr>
    <tr>
        <td class="label">Jumlah Mahasiswa</td><td><strong>: {{ $nilai->count() }}</strong></td>
        <td class="label">Dicetak</td><td>: {{ now()->format('d-m-Y H:i') }}</td>
    </tr>
</table>

<table class="data">
    <colgroup>
        <col style="width:3%">
        <col style="width:10%">
        <col style="width:20%">
        @for($i=1;$i<=16;$i++)<col style="width:2.5%">@endfor
        <col style="width:5.6%"><col style="width:5.6%"><col style="width:5.6%"><col style="width:5.6%"><col style="width:5.6%">
    </colgroup>
    <thead>
        <tr>
            <th>No.</th><th>NIM</th><th>Nama Mahasiswa</th>
            @for($i=1;$i<=16;$i++)<th>P{{ $i }}</th>@endfor
            <th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpa</th><th>% Hadir</th>
        </tr>
    </thead>
    <tbody>
    @foreach($nilai as $index => $n)
        @php
            $att=$n->kehadiranMahasiswa->keyBy('pertemuan');
            $hadir=$att->where('status','Hadir')->count();
            $izin=$att->where('status','Izin')->count();
            $sakit=$att->where('status','Sakit')->count();
            $alpa=$att->where('status','Alpa')->count();
            $total=$att->count();
            $persentase=$total>0 ? round(($hadir/$total)*100,2) : 0;
        @endphp
        <tr>
            <td class="center">{{ $index+1 }}</td>
            <td class="center"><strong>{{ $n->mahasiswa->numb_nim ?? $n->mahasiswa->nim ?? $n->mahasiswa->code ?? '-' }}</strong></td>
            <td class="student"><strong>{{ $n->mahasiswa->name ?? '-' }}</strong></td>
            @for($i=1;$i<=16;$i++)
                @php $a=$att[$i]??null; @endphp
                <td class="center">
                    @if(($a->status??'')==='Hadir')✓
                    @elseif(($a->status??'')==='Izin')I
                    @elseif(($a->status??'')==='Sakit')S
                    @elseif(($a->status??'')==='Alpa')A
                    @else—@endif
                </td>
            @endfor
            <td class="center">{{ $hadir }}</td><td class="center">{{ $izin }}</td><td class="center">{{ $sakit }}</td><td class="center">{{ $alpa }}</td>
            <td class="center"><strong>{{ number_format($persentase,2) }}%</strong></td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="footer">Printed via SIAKAD STIT-Darul Ilmi Tasikmalaya</div>
</body>
</html>