<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>DAFTAR DOSEN - STIT Darul Ilmi Tasikmalaya</title>
<style>
    @page { size: A4 landscape; margin: 12mm 12mm 18mm 12mm; }
    * { box-sizing: border-box; }
    body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 8.5pt; color: #111; margin: 0; }
    .kop { border-bottom: 3px solid #111; padding-bottom: 6px; margin-bottom: 12px; }
    .kop-table { width: 100%; border-collapse: collapse; }
    .kop-logo { width: 95px; text-align: center; vertical-align: middle; }
    .kop-logo img { width: 78px; height: 78px; object-fit: contain; }
    .kop-text { text-align: center; vertical-align: middle; line-height: 1.22; }
    .kop-text .a { font-size: 13pt; font-weight: bold; }
    .kop-text .b { font-size: 16pt; font-weight: bold; }
    .kop-text .c { font-size: 8.5pt; font-weight: bold; }
    .kop-text .d { font-size: 7.8pt; }
    .title { text-align: center; margin: 5px 0 12px; }
    .title-main { font-size: 13pt; font-weight: bold; text-decoration: underline; }
    .title-sub { font-size: 8.5pt; margin-top: 3px; }
    table.data { width: 100%; border-collapse: collapse; }
    .data th, .data td { border: 1px solid #222; padding: 5px 4px; vertical-align: middle; }
    .data th { background: #f0f0f0; text-align: center; font-weight: bold; }
    .data td.center { text-align: center; }
    .status { font-weight: bold; }
    .footer {
        position: fixed; left: 0; right: 0; bottom: -11mm;
        border-top: 1px solid #777; padding-top: 4px;
        text-align: center; font-size: 7.5pt; color: #555;
    }
    .footer .page:after { content: "Halaman " counter(page) " dari " counter(pages); }
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
            <td class="kop-logo">
                @if($logo)<img src="{{ $logo }}" alt="Logo STIT Darul Ilmi">@endif
            </td>
            <td class="kop-text">
                <div class="a">SEKOLAH TINGGI ILMU TARBIYAH</div>
                <div class="b">STIT DARUL ILMI TASIKMALAYA</div>
                <div class="c">SK Menteri Agama RI No. 536 Tahun 2026</div>
                <div class="d">Alamat : Jl. Cirahayu Sindangraja Jamanis Kabupaten Tasikmalaya Jawa Barat 46175</div>
            </td>
            <td style="width:95px"></td>
        </tr>
    </table>
</div>

<div class="title">
    <div class="title-main">DAFTAR DOSEN</div>
    <div class="title-sub">Sistem Informasi Akademik STIT Darul Ilmi Tasikmalaya</div>
</div>

<table class="data">
    <thead>
        <tr>
            <th style="width:5%">No</th>
            <th style="width:27%">Nama</th>
            <th style="width:25%">Email</th>
            <th style="width:16%">Telepon</th>
            <th style="width:14%">Status Kerja</th>
            <th style="width:13%">Status Dosen</th>
        </tr>
    </thead>
    <tbody>
        @forelse($dosen as $i => $item)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $item->name ?? '-' }}</td>
                <td>{{ $item->email ?? '-' }}</td>
                <td>{{ $item->phone ?? '-' }}</td>
                <td class="center status">{{ $item->type ?? '-' }}</td>
                <td class="center">{{ $item->status_dosen ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;padding:12px;">Tidak ada data dosen.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Printed via SIAKAD STIT-Darul Ilmi Tasikmalaya
    &nbsp;|&nbsp; <span class="page"></span>
</div>
</body>
</html>