<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Report Kehadiran Dosen</title>
<style>
@page{size:A4 landscape;margin:9mm}
*{box-sizing:border-box}body{font-family:"DejaVu Sans",Arial,sans-serif;font-size:7pt;color:#111;margin:0}
.kop{border-bottom:2px solid #111;padding-bottom:5px;margin-bottom:7px}.kop table{width:100%;border-collapse:collapse}.logo{width:65px;text-align:center}.logo img{width:58px;height:58px;object-fit:contain}.koptext{text-align:center;line-height:1.2}.a{font-size:9pt;font-weight:bold}.b{font-size:12pt;font-weight:bold}.c{font-size:7pt;font-weight:bold}.d{font-size:6.5pt}
.title{text-align:center;margin:3px 0 7px}.title-main{font-size:10pt;font-weight:bold;text-decoration:underline}.title-sub{font-size:7pt;margin-top:2px}
.info{width:100%;border-collapse:collapse;margin-bottom:7px}.info td{padding:1px 3px}.label{font-weight:bold;width:90px}
.data{width:100%;border-collapse:collapse;table-layout:fixed}.data th,.data td{border:1px solid #333;padding:3px 2px;vertical-align:middle;line-height:1.1}.data th{background:#eee;text-align:center}.center{text-align:center}.student{word-wrap:break-word;overflow-wrap:break-word}.att{text-align:center;font-size:6pt}.pct{text-align:center;font-size:6pt}
.footer{position:fixed;left:0;right:0;bottom:-5mm;border-top:1px solid #777;padding-top:2px;text-align:center;font-size:6pt;color:#555}
</style>
</head>
<body>
@php
/*
 * Dompdf pada hosting dapat gagal membaca URL /storage/... walaupun
 * gambar tampil normal di browser. Gunakan data URI dan cek beberapa
 * lokasi storage Laravel yang umum dipakai di ByetHost.
 */
$logo = null;
$logoCandidates = array_unique([
    storage_path('app/public/images/logo/logo-vert1.png'),
    base_path('storage/images/logo/logo-vert1.png'),
    public_path('storage/images/logo/logo-vert1.png'),
    public_path('images/logo/logo-vert1.png'),
]);

foreach ($logoCandidates as $candidate) {
    if (is_file($candidate) && is_readable($candidate)) {
        $contents = file_get_contents($candidate);
        if ($contents !== false && $contents !== '') {
            $logo = 'data:image/png;base64,' . base64_encode($contents);
            break;
        }
    }
}$tahunAkademik=$nilai->first()->tahunAkademik??null;
$groupCounts=$nilai->groupBy('matkul_id')->map->count();
$groupSeen=[];
@endphp
@include('shared.pdf.kop-surat')
<div class="title"><div class="title-main">REPORT KEHADIRAN MAHASISWA</div><div class="title-sub">Semester {{ $semester }}{{ $tahunAkademik ? ' | Tahun Akademik '.($tahunAkademik->name ?? $tahunAkademik->code ?? '') : '' }}</div></div>
<table class="info"><tr><td class="label">Dosen</td><td>: {{ Auth::guard('dosen')->user()->name ?? '-' }}</td><td class="label">Jumlah Data</td><td>: {{ $nilai->count() }}</td></tr><tr><td class="label">Filter</td><td>: {{ $mataKuliahId ? 'Mata kuliah terpilih' : ($mahasiswaId ? 'Mahasiswa terpilih' : 'Semua mata kuliah yang diampu') }}</td><td class="label">Dicetak</td><td>: {{ now()->format('d-m-Y H:i') }}</td></tr></table>
<table class="data"><thead><tr>
<th style="width:3%">No.</th><th style="width:15%">Mata Kuliah</th><th style="width:7%">Kode</th><th style="width:10%">NIM</th><th style="width:17%">Nama Mahasiswa</th>
@for($i=1;$i<=16;$i++)<th style="width:3.2%">P{{ $i }}</th>@endfor
<th style="width:4.5%">Hadir</th><th style="width:4.5%">Izin</th><th style="width:4.5%">Sakit</th><th style="width:4.5%">Alpa</th><th style="width:5%">% Hadir</th>
</tr></thead><tbody>
@foreach($nilai as $index=>$n)
@php
$att=$n->kehadiranMahasiswa->keyBy('pertemuan');$hadir=$att->where('status','Hadir')->count();$izin=$att->where('status','Izin')->count();$sakit=$att->where('status','Sakit')->count();$alpa=$att->where('status','Alpa')->count();$total=$att->count();$persentase=$total>0?round(($hadir/$total)*100,2):0;
$key=(string)$n->matkul_id;$show=!isset($groupSeen[$key]);if($show)$groupSeen[$key]=true;
@endphp
<tr><td class="center">{{ $index+1 }}</td>
@if($show)<td rowspan="{{ $groupCounts[$n->matkul_id] ?? 1 }}">{{ $n->mataKuliah->name ?? '-' }}</td><td rowspan="{{ $groupCounts[$n->matkul_id] ?? 1 }}" class="center">{{ $n->mataKuliah->code ?? '-' }}</td>@endif
<td class="center">{{ $n->mahasiswa->numb_nim ?? $n->mahasiswa->nim ?? $n->mahasiswa->code ?? '-' }}</td><td class="student">{{ $n->mahasiswa->name ?? '-' }}</td>
@for($i=1;$i<=16;$i++)@php $a=$att[$i]??null; @endphp<td class="att">@if(($a->status??'')==='Hadir')✓@elseif(($a->status??'')==='Izin')I@elseif(($a->status??'')==='Sakit')S@elseif(($a->status??'')==='Alpa')A@else—@endif</td>@endfor
<td class="center">{{ $hadir }}</td><td class="center">{{ $izin }}</td><td class="center">{{ $sakit }}</td><td class="center">{{ $alpa }}</td><td class="pct"><strong>{{ number_format($persentase,2) }}%</strong></td></tr>
@endforeach</tbody></table>
<div class="footer">Printed via SIAKAD STIT Darul Ilmi Tasikmalaya</div>
</body></html>