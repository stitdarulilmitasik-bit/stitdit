<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Mahasiswa - STIT Darul Ilmi Tasikmalaya</title>
<style>
    @page { size: A4 landscape; margin: 12mm 12mm 18mm 12mm; }
    * { box-sizing: border-box; }
    body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 8.5pt; color: #111; margin: 0; }
    .kop { border-bottom: 3px solid #111; padding-bottom: 6px; margin-bottom: 12px; }
    .kop-table { width: 100%; border-collapse: collapse; }
    .kop-logo { width: 95px; text-align: center; vertical-align: middle; }
    .kop-logo img { width: 102px; height: 102px; object-fit: contain; }
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

.pdf-system-footer{position:fixed;left:0;right:0;bottom:-7mm;border-top:1px solid #777;padding-top:2px;text-align:center;font-size:6.5pt;color:#555}.pdf-system-footer .page:after{content:"Halaman " counter(page) " dari " counter(pages);}
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

@include('shared.pdf.kop-surat')
<div class="title">
    <div class="title-main">DAFTAR MAHASISWA</div>
    <div class="title-sub">Sistem Informasi Akademik STIT Darul Ilmi Tasikmalaya</div>
</div>

<table class="data">
    <thead><tr>
        <th style="width:4%">No</th><th style="width:23%">Nama</th><th style="width:12%">NIM</th>
        <th style="width:19%">Program Studi</th><th style="width:20%">Alamat</th>
        <th style="width:11%">Status</th><th style="width:11%">Angkatan</th>
    </tr></thead>
    <tbody>
        @forelse($mahasiswa as $i => $item)
            @php
                $takaRegist = $item->taka_regist;
                $angkatan = $takaRegist !== null && $takaRegist !== ''
                    ? (strlen((string) $takaRegist) === 2 ? '20' . $takaRegist : $takaRegist)
                    : null;
            @endphp
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td><strong>{{ $item->name ?? '-' }}</strong>@if($item->email)<br><small>{{ $item->email }}</small>@endif</td>
                <td>{{ $item->numb_nim ?? '-' }}</td>
                <td>{{ $item->programStudi->name ?? '-' }}</td>
                <td>{{ $item->ktp_village ?? '-' }}</td>
                <td class="center status">{{ $item->type ?? '-' }}</td>
                <td class="center">{{ $angkatan ? 'Angkatan ' . $angkatan : '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;padding:12px;">Tidak ada data mahasiswa.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Printed via SIAKAD STIT Darul Ilmi Tasikmalaya
    &nbsp;|&nbsp; <span class="page"></span>
</div>
<div class="pdf-system-footer">Printed via SIAKAD STIT Darul Ilmi Tasikmalaya &nbsp;|&nbsp; <span class="page"></span></div>
</body>
</html>
