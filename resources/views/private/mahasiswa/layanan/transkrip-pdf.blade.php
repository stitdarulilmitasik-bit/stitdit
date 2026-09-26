<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
@page{size:A4 portrait;margin:12mm}
body{font-family:DejaVu Sans,Arial,sans-serif;font-size:9px;color:#111}
.title{text-align:center;margin:0 0 12px}
.meta{width:100%;border-collapse:collapse;margin-bottom:12px}.meta td{padding:3px}
.table{width:100%;border-collapse:collapse}.table th,.table td{border:1px solid #222;padding:4px}.table th{background:#eee;text-align:center}
.center{text-align:center}.right{text-align:right}.total{font-weight:bold;background:#f5f5f5}
.note{margin-top:8px;font-size:8px;color:#555}
.footer{margin-top:18px;text-align:center;font-size:8px;color:#555}
</style>
</head>
<body>
@include('shared.pdf.kop-surat')

<div class="title">
    <h3 style="margin:0;font-size:12.5pt;text-decoration:underline;">TRANSKRIP NILAI</h3>
</div>

<table class="meta">
<tr><td width="18%">Nama</td><td width="32%">: <strong>{{ $mahasiswa->name ?? '-' }}</strong></td><td width="18%">NIM</td><td>: <strong>{{ $mahasiswa->numb_nim ?? $mahasiswa->nim ?? '-' }}</strong></td></tr>
<tr><td>Program Studi</td><td>: {{ $mahasiswa->programStudi->name ?? $mahasiswa->prodi->name ?? '-' }}</td><td>Tahun Masuk</td><td>: {{ $mahasiswa->taka_regist ?? '-' }}</td></tr>
<tr><td>Total SKS</td><td>: {{ number_format((float)$totalSks,0) }}</td><td>IPK</td><td>: <strong>{{ number_format((float)$ipk,2) }}</strong></td></tr>
</table>

<table class="table">
<thead><tr><th>No</th><th>Semester</th><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Angka</th><th>Huruf</th><th>Bobot</th><th>Mutu</th></tr></thead>
<tbody>
@forelse($nilai as $i=>$n)
@php
$huruf=strtoupper(trim((string)($n->nilai_huruf ?? '')));
$map=\App\Models\Akademik\Nilai::NILAI_HURUF_MAP;
$bobot=isset($map[$huruf])?(float)$map[$huruf]['mutu']:(float)($n->nilai_mutu??0);
$sks=(float)($n->sks??$n->mataKuliah?->sks??$n->mataKuliah?->bsks??0);
$mutu=$bobot*$sks;
@endphp
<tr>
<td class="center">{{ $i+1 }}</td><td class="center">{{ $n->semester ?? '-' }}</td>
<td>{{ $n->mataKuliah->code ?? $n->mataKuliah->kode_mk ?? '-' }}</td>
<td>{{ $n->mataKuliah->name ?? $n->mataKuliah->nama ?? '-' }}</td>
<td class="center">{{ number_format($sks,0) }}</td>
<td class="center">{{ $n->nilai_angka !== null ? number_format((float)$n->nilai_angka,2) : '-' }}</td>
<td class="center">{{ $huruf ?: '-' }}</td><td class="center">{{ number_format($bobot,2) }}</td>
<td class="right">{{ number_format($mutu,2) }}</td>
</tr>
@empty
<tr><td colspan="9" class="center">Belum ada nilai yang telah dipublikasikan.</td></tr>
@endforelse
@if($nilai->count())
<tr class="total"><td colspan="4" class="right">TOTAL</td><td class="center">{{ number_format((float)$totalSks,0) }}</td><td colspan="3"></td><td class="right">{{ number_format((float)$totalMutu,2) }}</td></tr>
@endif
</tbody>
</table>

<div class="note">Transkrip mengambil nilai berstatus Published atau Locked. Jika mata kuliah ditempuh lebih dari satu kali, nilai terbaik yang telah dipublikasikan digunakan dalam perhitungan kumulatif.</div>
<div class="footer">Printed via SIAKAD STIT Darul Ilmi Tasikmalaya | Halaman <span class="page-number"></span></div>
</body>
</html>