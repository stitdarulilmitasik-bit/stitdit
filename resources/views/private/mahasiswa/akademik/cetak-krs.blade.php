<!doctype html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cetak KRS - {{ $mahasiswa->name ?? $mahasiswa->numb_nim }}</title>
<style>
@page { size: A4 portrait; margin: 10mm 15mm 15mm; }
body { font-family: Arial, sans-serif; color:#111; font-size:12px; margin:0; }
.no-print { display:none; }
.kop { width:100%; border-bottom:3px solid #111; padding-bottom:7px; margin-top:0; margin-bottom:12px; }
.kop-table { width:100%; border-collapse:collapse; }
.kop-table td { border:0; padding:0; }
.logo { width:105px; text-align:center; vertical-align:middle; }
.logo img { width:82px; height:82px; object-fit:contain; }
.kop-text { text-align:center; line-height:1.3; }
.kop-text .line1 { font-size:15px; font-weight:bold; }
.kop-text .line2 { font-size:18px; font-weight:bold; }
.kop-text .line3 { font-size:10px; font-weight:bold; }
.kop-text .address { font-size:9px; }
table { width:100%; border-collapse:collapse; }
th,td { border:1px solid #222; padding:6px; }
.krs-table { width:100%; table-layout:fixed; }
.krs-table .c-no { width:4%; }
.krs-table .c-kode { width:12%; }
.krs-table .c-mk { width:39%; }
.krs-table .c-sks { width:6%; }
.krs-table .c-kelas { width:3%; text-align:center; white-space:nowrap; overflow:hidden; padding:4px 1px; }
.krs-table .c-dosen { width:36%; }
th { background:#eee; }
.meta td { border:0; padding:3px 0; }
.text-center{text-align:center}.text-right{text-align:right}
.signature { margin-top:35px; width:100%; display:flex; justify-content:flex-end; }
.signature-box { width:220px; text-align:center; }
</style>
</head>
<body>
<div class="kop">
    <table class="kop-table">
        <tr>
            <td class="logo">
                @if(!empty($logoDataUri))
                    <img src="{{ $logoDataUri }}" alt="Logo STIT Darul Ilmi">
                @endif
            </td>
            <td class="kop-text">
                <div class="line1">SEKOLAH TINGGI ILMU TARBIYAH</div>
                <div class="line2">STIT DARUL ILMI TASIKMALAYA</div>
                <div class="line3">SK Menteri Agama RI No. 536 Tahun 2026</div>
                <div class="address">Alamat : Jl. Cirahayu Sindangraja Jamanis Kabupaten Tasikmalaya Jawa Barat 46175</div>
            </td>
            <td style="width:105px"></td>
        </tr>
    </table>
</div>
<div style="text-align:center; margin-bottom:14px;">
    <h3 style="margin:2px 0;">KARTU RENCANA STUDI (KRS)</h3>
    <p style="margin:2px 0;">{{ $currentSemester->name ?? '' }}</p>
</div>
<table class="meta">
<tr><td width="18%">Nama</td><td>: {{ $mahasiswa->name ?? '-' }}</td><td width="18%">NIM</td><td>: {{ $mahasiswa->numb_nim ?? '-' }}</td></tr>
<tr><td>Program Studi</td><td>: {{ $mahasiswa->programStudi->name ?? '-' }}</td><td>Semester</td><td>: {{ $mahasiswa->semester ?? '-' }}</td></tr>
<tr><td>Status KRS</td><td>: {{ $krsHeader->status ?? 'Belum dibuat' }}</td><td>Kode KRS</td><td>: {{ $krsHeader->code ?? '-' }}</td></tr>
</table>
<br>
<table class="krs-table">
<colgroup>
<col style="width:8mm"><col style="width:22mm"><col style="width:62mm"><col style="width:10mm"><col style="width:12mm"><col style="width:66mm">
</colgroup>
<thead><tr><th class="c-no">No</th><th class="c-kode">Kode</th><th class="c-mk">Mata Kuliah</th><th class="c-sks">SKS</th><th class="c-kelas">Kelas</th><th class="c-dosen">Dosen</th></tr></thead>
<tbody>
@forelse($krs as $i => $item)
<tr>
<td class="text-center">{{ $i+1 }}</td>
<td>{{ $item->mataKuliah->kode_mk ?? $item->mataKuliah->code ?? '-' }}</td>
<td>{{ $item->mataKuliah->nama ?? $item->mataKuliah->name ?? '-' }}</td>
<td class="text-center">{{ $item->sks ?? $item->mataKuliah->sks ?? 0 }}</td>
<td class="c-kelas">{{ $item->kelas->nama_kelas ?? $item->kelas->name ?? '-' }}</td>
<td>{{ $item->dosen->nama_lengkap ?? $item->dosen->name ?? '-' }}</td>
</tr>
@empty
<tr><td colspan="6" class="text-center">Belum ada mata kuliah dalam KRS.</td></tr>
@endforelse
</tbody>
<tfoot><tr><th colspan="3" class="text-right">Total SKS</th><th class="text-center">{{ $krs->sum('sks') }}</th><th colspan="2"></th></tr></tfoot>
</table>
<div class="signature"><div class="signature-box"><p>Mengetahui,<br>Dosen Pembimbing Akademik</p><br><br><br><strong>{{ $krsHeader->dosenPA?->name ?? '________________________' }}</strong></div></div>
</body>
</html>
