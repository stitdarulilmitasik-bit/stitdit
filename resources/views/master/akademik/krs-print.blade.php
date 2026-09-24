@php
$mahasiswa = $krs->mahasiswa;
$nim = $mahasiswa?->numb_nim
    ?? $mahasiswa?->nim
    ?? $mahasiswa?->code
    ?? '-';

$tahunMasuk = $mahasiswa?->taka_regist;
if ($tahunMasuk !== null && $tahunMasuk !== '') {
    $tahunMasuk = (int) $tahunMasuk < 100
        ? 2000 + (int) $tahunMasuk
        : $tahunMasuk;
} else {
    $tahunMasuk = '-';
}

$dosenWali = $krs->dosenPA;
$dosenNidn = $dosenWali?->nidn
    ?? $dosenWali?->nidn_number
    ?? $dosenWali?->numb_nidn
    ?? $dosenWali?->number_nidn
    ?? '-';

$tahunAkademik = $krs->tahunAkademik?->name ?? '-';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KRS - {{ $krs->mahasiswa->name }} ({{ $krs->mahasiswa->numb_nim ?? $krs->mahasiswa->nim ?? '-' }})</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Times New Roman',Times,serif;font-size:12pt;line-height:1.4;color:#000;background:#fff;padding:20px}
.container{width:100%;max-width:800px;margin:0 auto}
.kop-surat{display:table;width:100%;border-bottom:3px double #000;padding-bottom:10px;margin-bottom:15px}
.kop-logo{display:table-cell;width:100px;vertical-align:middle;text-align:center}
.kop-logo img{width:85px;height:auto;display:block;margin:0 auto}
.kop-text{display:table-cell;vertical-align:middle;text-align:center}
.kop-text h3{font-size:14pt;font-weight:bold;text-transform:uppercase}
.kop-text h2{font-size:16pt;font-weight:bold;text-transform:uppercase;margin:2px 0}
.kop-text p{font-size:9pt;margin-top:2px}
.judul-doc{text-align:center;margin-bottom:15px}
.judul-doc h4{font-size:13pt;text-transform:uppercase;text-decoration:underline}
.judul-doc p{font-size:11pt;font-weight:bold}
.table-bio{width:100%;margin-bottom:15px;border-collapse:collapse}
.table-bio td{padding:3px 0;vertical-align:top;font-size:10.5pt}
.table-krs{width:100%;border-collapse:collapse;margin-bottom:15px}
.table-krs th,.table-krs td{border:1px solid #000;padding:5px 6px;font-size:10pt}
.table-krs th{background:#f2f2f2;text-align:center;font-weight:bold}
.text-center{text-align:center}.text-bold{font-weight:bold}
.catatan{font-size:9pt;margin-bottom:25px}.catatan ol{padding-left:18px}
.table-ttd{width:100%;border-collapse:collapse;text-align:center;margin-top:10px}
.table-ttd td{width:33.3%;vertical-align:top;padding-bottom:10px;font-size:10.5pt}
.space-ttd{height:60px}
.footer-print{margin-top:20px;border-top:1px solid #ccc;padding-top:5px;font-size:8pt;color:#555}
@media print{body{padding:0}.container{max-width:100%}.no-print{display:none}@page{size:A4;margin:1.5cm}}
</style>
</head>
<body>
<div class="no-print" style="margin-bottom:20px;text-align:right">
<button onclick="window.print()" style="padding:8px 16px;background:#007bff;color:#fff;border:0;border-radius:4px">Cetak KRS</button>
</div>
<div class="container"><div class="kop-surat">
<div class="kop-logo">
@php
$logoPath = public_path('images/logo/logo-vert1.png');
$logoBase64 = '';
if (is_file($logoPath) && is_readable($logoPath)) {
    try {
        $logoBytes = file_get_contents($logoPath);
        if (is_string($logoBytes) && $logoBytes !== '') {
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoBytes);
        }
    } catch (\Throwable $e) {
        $logoBase64 = '';
    }
}
@endphp
@if($logoBase64)<img src="{{ $logoBase64 }}" alt="Logo STIT Darul Ilmi">@endif
</div>
<div class="kop-text">
<h3>SEKOLAH TINGGI ILMU TARBIYAH</h3>
<h2>STIT DARUL ILMI TASIKMALAYA</h2>
<p>SK Menteri Agama RI No. 536 Tahun 2026</p>
<p>Jl. Cirahayu Sindangraja Jamanis Kabupaten Tasikmalaya Jawa Barat 46175</p>
</div>
</div>
<div class="judul-doc">
<p style="font-size:11pt;text-transform:uppercase">Fakultas Tarbiyah</p>
<h4>KARTU RENCANA STUDI (KRS)</h4>
<p>Semester {{ $krs->semester }} Tahun Akademik {{ $tahunAkademik ?? '-' }}</p>
</div>
<table class="table-bio">
<tr><td width="18%">Nama Mahasiswa</td><td width="2%">:</td><td width="30%" class="text-bold">{{ $krs->mahasiswa->name }}</td><td width="18%">Program Studi</td><td width="2%">:</td><td width="30%">{{ $krs->mahasiswa->programStudi->name ?? '-' }}</td></tr>
<tr><td>NIM</td><td>:</td><td>{{ $nim }}</td><td>Tahun Masuk</td><td>:</td><td>{{ $tahunMasuk }}</td></tr>
<tr><td>Semester</td><td>:</td><td>{{ $krs->semester }}</td><td>Dosen Wali</td><td>:</td><td>{{ $dosenWali->name ?? '-' }}</td></tr>
<tr><td>Status KRS</td><td>:</td><td class="text-bold">{{ $krs->status }}</td><td>Total SKS</td><td>:</td><td>{{ $krs->total_sks }} SKS</td></tr>
</table>
<table class="table-krs">
<thead><tr><th width="5%">No</th><th width="12%">Kode MK</th><th width="33%">Mata Kuliah</th><th width="7%">SKS</th><th width="8%">Kelas</th><th width="10%">Ruang</th><th width="25%">Dosen</th></tr></thead>
<tbody>
@forelse($krs->details as $detail)
@php
$jadwal=$detail->kelas?->jadwalKuliah?->first(function($item)use($detail){return (int)($item->matkul_id??0)===(int)($detail->matkul_id??0);});
$jadwal=$jadwal?:$detail->kelas?->jadwalKuliah?->first();
$dosen1=$detail->mataKuliah?->dosen1;
$dosen2=$detail->mataKuliah?->dosen2;
@endphp
<tr><td class="text-center">{{ $loop->iteration }}</td><td class="text-center">{{ $detail->mataKuliah->code ?? '-' }}</td><td>{{ $detail->mataKuliah->name ?? '-' }}</td><td class="text-center">{{ $detail->mataKuliah->sks ?? $detail->sks ?? 0 }}</td><td class="text-center">{{ $detail->kelas->name ?? '-' }}</td><td class="text-center">{{ $jadwal?->ruang?->name ?? $jadwal?->ruang ?? '-' }}</td><td>{{ $dosen1?->name ?? '-' }}@if($dosen2)<br>{{ $dosen2->name }}@endif</td></tr>
@empty
<tr><td colspan="7" class="text-center">Belum ada mata kuliah yang diambil.</td></tr>
@endforelse
@if($krs->details->count())<tr><td colspan="3" class="text-bold text-center">TOTAL SKS</td><td class="text-center text-bold">{{ $krs->total_sks }}</td><td colspan="3"></td></tr>@endif
</tbody></table>
<div class="catatan"><strong>CATATAN PENTING:</strong><ol><li>KRS ini harus mendapat persetujuan dari Dosen Pembimbing Akademik.</li><li>Perubahan KRS hanya dapat dilakukan pada periode yang telah ditentukan.</li><li>Mahasiswa wajib mengikuti semua mata kuliah yang tercantum dalam KRS.</li><li>KRS yang telah disetujui dan dikunci tidak dapat diubah.</li></ol></div>
<table class="table-ttd"><tr>
<td>Mahasiswa<div class="space-ttd"></div><strong><u>{{ $krs->mahasiswa->name }}</u></strong><br>NIM. {{ $nim }}</td>
<td>Dosen Pembimbing Akademik<div class="space-ttd"></div><strong><u>{{ $dosenWali->name ?? '-' }}</u></strong><br>NIDN. {{ $dosenNidn }}</td>
<td>Ketua Program Studi<div class="space-ttd"></div><strong><u>{{ $kaprodi->name ?? '-' }}</u></strong><br>NIDN. {{ $kaprodi->nidn ?? $kaprodi->nidn_number ?? $kaprodi->numb_nidn ?? $kaprodi->number_nidn ?? '-' }}</td>
</tr></table>
<div class="footer-print">Dicetak pada: {{ now()->locale('id')->translatedFormat('d F Y H:i:s') }} | Status KRS: {{ $krs->status }} | @if($krs->approved_at)Disetujui: {{ $krs->approved_at->format('d F Y H:i') }}@else Belum Disetujui @endif</div>
</div>
</body></html>