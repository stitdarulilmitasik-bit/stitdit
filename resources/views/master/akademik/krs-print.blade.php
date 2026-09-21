<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Rencana Studi - {{ $krs->mahasiswa->name }}</title>
    <style>
        @page { size: A4 portrait; margin: 1.0cm 0.9cm; }
        body { font-family: 'Times New Roman', serif; font-size: 9.5pt; line-height: 1.15; color: #000; margin: 0; padding: 0; }
        .kop { width: 100%; border-bottom: 2px solid #111; padding-bottom: 6px; margin-bottom: 10px; }
        .kop-table { width: 100%; border-collapse: collapse; }
        .kop-logo { width: 85px; text-align: center; vertical-align: middle; }
        .kop-logo img { width: 68px; height: 68px; object-fit: contain; }
        .kop-text { text-align: center; vertical-align: middle; line-height: 1.25; }
        .kop-text .line1 { font-size: 12pt; font-weight: bold; }
        .kop-text .line2 { font-size: 15pt; font-weight: bold; }
        .kop-text .line3 { font-size: 8pt; font-weight: bold; }
        .kop-text .address { font-size: 8pt; }
        .header { text-align: center; margin-bottom: 8px; }
        .faculty-name { font-size: 10.5pt; font-weight: bold; margin-bottom: 3px; }
        .document-title { display: inline-block; width: auto; max-width: 80%; font-size: 12pt; font-weight: bold; text-decoration: underline; margin: 4px auto 0; padding: 0 18px; }
        .semester-line { font-size: 9pt; margin-top: 4px; }
        .student-info { margin: 8px 0; }
        .student-info table { width: 100%; border-collapse: collapse; }
        .student-info td { padding: 2px 2px; vertical-align: top; }
        .student-info .label { width: 92px; font-weight: bold; }
        .student-info .colon { width: 8px; text-align: center; }
        .courses-table { width: 100%; table-layout: fixed; border-collapse: collapse; margin: 8px 0; border: 1px solid #000; }
        .courses-table th, .courses-table td { border: 1px solid #000; padding: 3px 2px; text-align: center; font-size: 7.5pt; line-height: 1.05; vertical-align: middle; }
        .courses-table th { background-color: #f0f0f0; font-weight: bold; }
        .courses-table .subject-name { text-align: left; padding-left: 4px; overflow-wrap: anywhere; font-size: 7.5pt; }
        .courses-table .room { text-align: center; font-size: 7pt; white-space: nowrap; }\n        .courses-table .lecturer { text-align: left; padding-left: 4px; font-size: 7pt; overflow-wrap: anywhere; }
        .summary-section { margin: 10px 0; border: 1px solid #000; padding: 7px; }
        .summary-title { font-weight: bold; text-align: center; margin-bottom: 7px; text-decoration: underline; }
        .signature-section { margin-top: 14px; width: 100%; page-break-inside: avoid; }
        .signature-table { width: 100%; border-collapse: collapse; }
        .signature-cell { width: 33.33%; text-align: center; vertical-align: top; padding: 8px 10px; }
        .signature-title { font-weight: bold; margin: 0 auto 42px; min-height: 28px; line-height: 1.25; }
        .signature-name { font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px; }
        .signature-nip { font-size: 8pt; margin-top: 4px; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 100pt; color: rgba(0,0,0,0.05); z-index: -1; font-weight: bold; }
        .status-badge { display: inline-block; padding: 2px 7px; border: 1px solid #000; font-weight: bold; font-size: 8pt; }
        .print-info { margin-top: 8px; font-size: 7.5pt; color: #666; text-align: center; }
    </style>
</head>
<body>
@php
    // Data identitas mahasiswa untuk report KRS.
    $nim = $krs->mahasiswa->numb_nim ?? $krs->mahasiswa->nim ?? $krs->mahasiswa->code ?? '-';
    $tahunMasukRaw = $krs->mahasiswa->taka_regist ?? null;
    $tahunMasuk = '-';
    if ($tahunMasukRaw !== null && $tahunMasukRaw !== '') {
        $tahunMasukRaw = trim((string) $tahunMasukRaw);
        $tahunMasuk = preg_match('/^\\d{2}$/', $tahunMasukRaw)
            ? (string) (2000 + (int) $tahunMasukRaw)
            : $tahunMasukRaw;
    }
    $dosenWali = $krs->dosenPA ?? null; if (!$dosenWali) { $fallbackJabatan = \App\Models\Jabatan::with('dosen')->where('is_active', true)->whereIn('name', ['Dosen Pembimbing Akademik', 'Dosen Pembimbing'])->where(function ($q) use ($krs) { $q->whereNull('prodi_id')->orWhere('prodi_id', $krs->mahasiswa->prodi_id); })->whereNotNull('dosen_id')->orderBy('sort_order')->first(); $dosenWali = $fallbackJabatan?->dosen; }
    $dosenNidn = $dosenWali?->nidn ?? $dosenWali?->nidn_number ?? $dosenWali?->numb_nidn ?? $dosenWali?->number_nidn ?? '-';

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
@endphp

@if ($krs->status === 'approved')
    <div class="watermark">APPROVED</div>
@elseif ($krs->status === 'locked')
    <div class="watermark">LOCKED</div>
@else
    <div class="watermark">DRAFT</div>
@endif

<div class="kop">
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if($logo)
                    <img src="{{ $logo }}" alt="Logo STIT Darul Ilmi">
                @endif
            </td>
            <td class="kop-text">
                <div class="line1">SEKOLAH TINGGI ILMU TARBIYAH</div>
                <div class="line2">STIT DARUL ILMI TASIKMALAYA</div>
                <div class="line3">SK Menteri Agama RI No. 536 Tahun 2026</div>
                <div class="address">Alamat : Jl. Cirahayu Sindangraja Jamanis Kabupaten Tasikmalaya Jawa Barat 46175</div>
            </td>
            <td style="width:110px"></td>
        </tr>
    </table>
</div>

<div class="header">
    <div class="faculty-name">{{ $krs->mahasiswa->programStudi->fakultas->name ?? 'FAKULTAS' }}</div>
    <div class="document-title">KARTU RENCANA STUDI (KRS)</div>
    <div class="semester-line">Semester {{ $krs->semester }} | {{ $krs->tahunAkademik->name ?? '-' }} - {{ $krs->tahunAkademik->type ?? '-' }}</div>
</div>

<div class="student-info">
    <table>
        <tr>
            <td class="label">Nama Mahasiswa</td><td class="colon">:</td><td style="width:27%;">{{ $krs->mahasiswa->name }}</td>
            <td class="label" style="padding-left:15px;">Program Studi</td><td class="colon">:</td><td>{{ $krs->mahasiswa->programStudi->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td><td class="colon">:</td><td style="width:27%;">{{ $nim }}</td>
            <td class="label" style="padding-left:15px;">Tahun Masuk</td><td class="colon">:</td><td>{{ $tahunMasuk }}</td>
        </tr>
        <tr>
            <td class="label">Semester</td><td class="colon">:</td><td style="width:27%;">{{ $krs->semester }}</td>
            <td class="label" style="padding-left:15px;">Dosen Wali</td><td class="colon">:</td><td>{{ $dosenWali->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Status KRS</td><td class="colon">:</td><td style="width:27%;"><span class="status-badge">{{ strtoupper($krs->status) }}</span></td>
            <td class="label" style="padding-left:15px;">Total SKS</td><td class="colon">:</td><td><strong>{{ $krs->total_sks }} SKS</strong></td>
        </tr>
    </table>
</div>

<table class="courses-table">
    <thead>
        <tr>
            <th rowspan="2" style="width:9%;">Kode MK</th>
            <th rowspan="2" style="width:30%;">Mata Kuliah</th>
            <th rowspan="2" style="width:7%;">SKS</th>
            <th rowspan="2" style="width:11%;">Kelas</th>
            <th rowspan="2" style="width:10%;">Ruang</th>
            <th rowspan="2" style="width:33%;">Dosen</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($krs->details as $detail)
            @php
                $jadwal = $detail->kelas?->jadwalKuliah
                    ?->first(function ($item) use ($detail) {
                        return (int) ($item->matkul_id ?? 0) === (int) ($detail->matkul_id ?? 0);
                    });
                $jadwal = $jadwal ?: $detail->kelas?->jadwalKuliah?->first();
                $dosen1 = $detail->mataKuliah?->dosen1 ?? null;
                $dosen2 = $detail->mataKuliah?->dosen2 ?? null;
            @endphp
            <tr>
                <td>{{ $detail->mataKuliah->code ?? '-' }}</td>
                <td class="subject-name">{{ $detail->mataKuliah->name ?? '-' }}</td>
                <td>{{ $detail->mataKuliah->sks ?? $detail->sks ?? 0 }}</td>
                <td>{{ $detail->kelas->name ?? '-' }}</td>
                <td class="room">{{ $jadwal?->ruang?->name ?? $jadwal?->ruang ?? '-' }}</td>
                <td class="lecturer">
                    @if ($dosenDetail)
                        {{ $dosenDetail->name }}
                    @elseif ($detail->mataKuliah->dosen1)
                        {{ $detail->mataKuliah->dosen1->name }}
                        @if ($detail->mataKuliah->dosen2)
                            <br>{{ $detail->mataKuliah->dosen2->name }}
                        @endif
                    @else
                        -
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;font-style:italic;">Belum ada mata kuliah yang dipilih</td></tr>
        @endforelse
    </tbody>
    @if ($krs->details->count() > 0)
        <tfoot>
            <tr style="background-color:#f0f0f0;">
                <td colspan="2" style="text-align:right;font-weight:bold;">TOTAL SKS</td>
                <td style="font-weight:bold;">{{ $krs->total_sks }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    @endif
</table>

<div style="margin-top:8px;padding:7px;border:1px dashed #000;font-size:8.5pt;">
    <strong>CATATAN PENTING:</strong>
    <ol style="margin:4px 0;padding-left:18px;">
        <li>KRS ini harus mendapat persetujuan dari Dosen Pembimbing Akademik.</li>
        <li>Perubahan KRS hanya dapat dilakukan pada periode yang telah ditentukan.</li>
        <li>Mahasiswa wajib mengikuti semua mata kuliah yang tercantum dalam KRS.</li>
        <li>KRS yang telah disetujui dan dikunci tidak dapat diubah.</li>
    </ol>
</div>

<div class="signature-section">
    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-title">Mahasiswa</div>
                <div class="signature-name">{{ $krs->mahasiswa->name }}</div>
                <div class="signature-nip">NIM. {{ $nim }}</div>
            </td>
            <td class="signature-cell">
                <div class="signature-title">Dosen Pembimbing Akademik</div>
                <div class="signature-name">{{ $dosenWali->name ?? '[Nama Dosen PA]' }}</div>
                <div class="signature-nip">NIDN. {{ $dosenNidn !== '-' ? $dosenNidn : '[NIDN Dosen PA]' }}</div>
                @if ($krs->approved_at)
                    <div style="font-size:7.5pt;margin-top:4px;">Disetujui: {{ $krs->approved_at->format('d/m/Y H:i') }}</div>
                @endif
            </td>
            <td class="signature-cell">
                <div class="signature-title">Ketua Program Studi</div>
                <div class="signature-name">{{ $kaprodi->name ?? '[Nama Ketua Prodi]' }}</div>
                <div class="signature-nip">NIDN. {{ $kaprodi->nidn ?? $kaprodi->nidn_number ?? $kaprodi->numb_nidn ?? $kaprodi->number_nidn ?? '[NIDN Ketua Prodi]' }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="print-info">
    Dicetak pada: {{ now()->locale('id')->translatedFormat('d F Y H:i:s') }} |
    Status: {{ strtoupper($krs->status) }} |
    @if ($krs->approved_at)
        Disetujui: {{ $krs->approved_at->format('d F Y H:i:s') }}
    @else
        Belum Disetujui
    @endif
    @if ($krs->published_at)
        | Dipublish: {{ $krs->published_at->format('d F Y H:i:s') }}
    @endif
</div>
</body>
</html>
