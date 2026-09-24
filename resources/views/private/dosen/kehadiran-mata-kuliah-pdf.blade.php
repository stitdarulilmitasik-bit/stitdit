<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Kehadiran {{ $mataKuliah->name ?? 'Mata Kuliah' }}</title>
<style>
    @page { 
        size: A4 landscape; 
        margin: 10mm; 
    }
    * { 
        box-sizing: border-box; 
    }
    body { 
        font-family: "DejaVu Sans", Arial, sans-serif; 
        font-size: 7pt; 
        color: #111; 
        margin: 0; 
    }

    /* KOP SURAT */
    .kop { 
        border-bottom: 2px solid #111; 
        padding-bottom: 5px; 
        margin-bottom: 8px; 
    }
    .kop-table { 
        width: 100%; 
        border-collapse: collapse; 
    }
    .kop-logo { 
        width: 70px; 
        text-align: center; 
        vertical-align: middle; 
    }
    .kop-logo img { 
        width: 65px; 
        height: 65px; 
        object-fit: contain; 
    }
    .kop-text { 
        text-align: center; 
        vertical-align: middle; 
        line-height: 1.2; 
    }
    .kop-text .a { font-size: 10pt; font-weight: bold; }
    .kop-text .b { font-size: 12pt; font-weight: bold; }
    .kop-text .c { font-size: 7.5pt; font-weight: bold; }
    .kop-text .d { font-size: 7pt; }

    /* JUDUL & INFO */
    .title { text-align: center; margin: 4px 0 8px; }
    .title-main { font-size: 10pt; font-weight: bold; text-decoration: underline; }
    .title-sub { font-size: 7.5pt; margin-top: 2px; }
    
    .info { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    .info td { padding: 1.5px 3px; vertical-align: top; }
    .info .label { width: 100px; font-weight: bold; }

    /* TABEL DATA */
    table.data { 
        width: 100%; 
        border-collapse: collapse; 
        table-layout: fixed; /* Mengunci lebar kolom berdasarkan <th> */
    }
    .data th, .data td { 
        border: 1px solid #333; 
        padding: 3px 1px; 
        vertical-align: middle; 
        line-height: 1.1;
        font-size: 7pt;
    }
    .data th { 
        background: #eee; 
        text-align: center; 
        font-weight: bold; 
    }
    
    .center { text-align: center; }
    
    /* GAYA SEL SPESIFIK */
    .student { 
        text-align: left; 
        padding-left: 3px !important; 
        padding-right: 3px !important;
        white-space: normal !important; 
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
    }
    .student strong {
        font-weight: bold;
    }
    .nim { 
        text-align: center;
        font-size: 6pt !important; 
        white-space: nowrap;
    }
    .attendance { 
        font-size: 6pt !important; 
        text-align: center; 
    }
    .pct { 
        font-size: 6pt !important; 
        text-align: center;
    }

    .footer { 
        position: fixed; 
        left: 0; 
        right: 0; 
        bottom: -6mm; 
        border-top: 1px solid #777; 
        padding-top: 3px; 
        text-align: center; 
        font-size: 6pt; 
        color: #555; 
    }
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
            <td style="width:70px"></td>
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
    <thead>
        <tr>
            <th style="width: 3%;">No.</th>
            <th style="width: 12%;">NIM</th>
            <th style="width: 25%;">Nama Mahasiswa</th>
            @for($i=1;$i<=16;$i++)
                <th style="width: 4%;">P{{ $i }}</th>
            @endfor
            <th style="width: 5.6%;">% Hadir</th>
        </tr>
    </thead>
    <tbody>
    @foreach($nilai as $index => $n)
        @php
            $att = $n->kehadiranMahasiswa->keyBy('pertemuan');
            $hadir = $att->where('status','Hadir')->count();
            $total = $att->count();
            $persentase = $total > 0 ? round(($hadir/$total)*100, 2) : 0;
        @endphp
        <tr>
            <td class="center">{{ $index+1 }}</td>
            <td class="nim"><strong>{{ $n->mahasiswa->numb_nim ?? $n->mahasiswa->nim ?? $n->mahasiswa->code ?? '-' }}</strong></td>
            <td class="student"><strong>{{ $n->mahasiswa->name ?? '-' }}</strong></td>
            @for($i=1;$i<=16;$i++)
                @php $a = $att[$i] ?? null; @endphp
                <td class="attendance">
                    @if(($a->status??'')==='Hadir')✓
                    @elseif(($a->status??'')==='Izin')I
                    @elseif(($a->status??'')==='Sakit')S
                    @elseif(($a->status??'')==='Alpa')A
                    @else—@endif
                </td>
            @endfor
            <td class="pct"><strong>{{ number_format($persentase, 2) }}%</strong></td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">Printed via SIAKAD STIT-Darul Ilmi Tasikmalaya</div>
</body>
</html>
