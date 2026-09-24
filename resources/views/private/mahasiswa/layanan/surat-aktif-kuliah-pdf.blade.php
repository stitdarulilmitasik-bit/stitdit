@php
    $logo = null;
    $candidates = [storage_path('app/public/images/logo/logo-vert1.png')];
    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            $mime = mime_content_type($candidate) ?: 'image/png';
            $logo = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($candidate));
            break;
        }
    }
    $tgl = \Carbon\Carbon::parse($tanggal_surat)->locale('id')->translatedFormat('d F Y');
@endphp
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
@page{size:A4 portrait;margin:12mm 15mm 10mm 18mm}
*{box-sizing:border-box}body{font-family:DejaVu Sans,Arial,sans-serif;font-size:10.2pt;line-height:1.32;color:#111;margin:0}.kop{border-bottom:3px solid #111;padding-bottom:6px;margin-bottom:10px}.kop-table{width:100%;border-collapse:collapse}.kop-logo{width:125px;text-align:center;vertical-align:middle}.kop-logo img{width:110px;height:110px;object-fit:contain}.kop-text{text-align:center;vertical-align:middle}.kop-text .a{font-size:13.5pt;font-weight:bold}.kop-text .b{font-size:17pt;font-weight:bold}.kop-text .c{font-size:9pt;font-weight:bold}.kop-text .d{font-size:8.4pt}.title{text-align:center;font-weight:bold;font-size:12.5pt;text-decoration:underline;margin-top:3px}.nomor{text-align:center;margin:2px 0 10px}.row{display:table;width:100%;margin:2px 0}.label{display:table-cell;width:178px;vertical-align:top}.value{display:table-cell;vertical-align:top}.section{margin:7px 0 3px}.date-right{width:43%;margin-left:57%;text-align:center;margin-top:18px}.ttd{width:43%;margin-left:57%;margin-top:3px;border-collapse:collapse}.ttd td{vertical-align:top}.right{text-align:center;width:100%}.signature-space{height:52px}.tembusan{margin-top:18px;font-size:8.8pt}.small{font-size:8.8pt}
</style>
</head>
<body>
<div class="kop"><table class="kop-table"><tr><td class="kop-logo">@if($logo)<img src="{{ $logo }}">@endif</td><td class="kop-text"><div class="a">SEKOLAH TINGGI ILMU TARBIYAH</div><div class="b">STIT DARUL ILMI TASIKMALAYA</div><div class="c">SK Menteri Agama RI No. 536 Tahun 2026</div><div class="d">Alamat : Jl. Cirahayu Sindangraja Jamanis Kabupaten Tasikmalaya Jawa Barat 46175</div></td></tr></table></div>
<div class="title">SURAT KETERANGAN AKTIF KULIAH</div>
<div class="nomor">Nomor : {{ $nomor_surat }}</div>
<p>Yang bertandatangan di bawah ini:</p>
<div class="row"><div class="label">Nama</div><div class="value">: {{ $pejabat_nama }}</div></div>
<div class="row"><div class="label">NIP/NIK</div><div class="value">: {{ $pejabat_nip }}</div></div>
<div class="row"><div class="label">Jabatan</div><div class="value">: {{ $pejabat_jabatan }}</div></div>
<div class="row"><div class="label">Perguruan Tinggi</div><div class="value">: STIT Darul Ilmi Tasikmalaya</div></div>
<p class="section">Menerangkan bahwa:</p>
<div class="row"><div class="label">Nama</div><div class="value">: {{ $nama }}</div></div>
<div class="row"><div class="label">NIK</div><div class="value">: {{ $nik }}</div></div>
<div class="row"><div class="label">NIM</div><div class="value">: {{ $nim }}</div></div>
<div class="row"><div class="label">Tempat, Tanggal Lahir</div><div class="value">: {{ $ttl }}</div></div>
<div class="row"><div class="label">Alamat</div><div class="value">: {{ $alamat }}</div></div>
<p class="section">Merupakan mahasiswa aktif:</p>
<div class="row"><div class="label">Jenjang</div><div class="value">: {{ $jenjang }}</div></div>
<div class="row"><div class="label">Program Studi</div><div class="value">: {{ $program_studi }}</div></div>
<div class="row"><div class="label">Memulai studi pada</div><div class="value">: {{ $periode_mulai }} tahun akademik {{ $tahun_akademik }}</div></div>
<p class="section">Demikian surat ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
<div class="date-right">Tasikmalaya, {{ $tgl }}</div>
<table class="ttd"><tr><td class="right">{{ $pejabat_jabatan }}<div class="signature-space"></div><strong><u>{{ $pejabat_nama }}</u></strong><br>NIP/NIK. {{ $pejabat_nip }}</td></tr></table>
<div class="tembusan"><strong>Tembusan:</strong><br>1. Ketua Prodi MPI STIT Darul Ilmi Tasikmalaya<br>2. Arsip</div>
</body>
</html>
