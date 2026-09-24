<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Rencana Studi - {{ $krs->mahasiswa->name }}</title>
    <style>
        @page { size: A4 portrait; margin: 10mm 15mm 15mm; }
        body { font-family: Arial, sans-serif; color:#111; font-size:12px; margin:0; }
        .page-width { width: 100%; }

        .kop { width:100%; border-bottom:3px solid #111; padding-bottom:7px; margin-top:0; margin-bottom:12px; }
        .kop-table { width:100%; border-collapse:collapse; }
        .kop-table td { border:0; padding:0; }
        .kop-logo { width:105px; text-align:center; vertical-align:middle; }
        .kop-logo img { width:82px; height:82px; object-fit:contain; }
        .kop-text { text-align:center; line-height:1.3; }
        .kop-text .line1 { font-size:15px; font-weight:bold; }
        .kop-text .line2 { font-size:18px; font-weight:bold; }
        .kop-text .line3 { font-size:10px; font-weight:bold; }
        .kop-text .address { font-size:9px; }
        .header { text-align:center; margin-bottom:14px; }
        .faculty-name { font-size:12px; font-weight:bold; margin-bottom:3px; }
        .document-title { display:inline-block; width:auto; max-width:80%; font-size:15px; font-weight:bold; text-decoration:underline; margin:2px auto 0; padding:0; }
        .semester-line { font-size:12px; font-weight:normal; margin-top:2px; }
        .student-info { margin: 9px 0; }
        .student-info table { width:100%; border-collapse:collapse; table-layout:fixed; }
        .student-info td { border:0; padding:3px 2px; vertical-align:middle; }
        .student-info .label { width:18%; font-weight:bold; white-space:nowrap; }
        .student-info .colon { width:2%; text-align:center; }
        .student-info .value { width:30%; white-space:nowrap; overflow:hidden; }

        .courses-table { width: 100%; table-layout: fixed; border-collapse: collapse; margin: 8px 0; border: 1px solid #000; }
        .courses-table th, .courses-table td { border: 1px solid #000; padding: 5px 4px; text-align: center; font-size: 9.5pt; line-height: 1.15; vertical-align: middle; overflow-wrap: anywhere; word-break: normal; }
        .courses-table th { background-color: #f0f0f0; font-weight: bold; font-size: 10pt; }
        .courses-table .subject-name { text-align: left; padding-left: 6px; font-size: 9.5pt; }
        .courses-table .room { text-align: center; font-size: 9pt; white-space: normal; }
        .courses-table .lecturer { text-align: left; padding-left: 6px; font-size: 9pt; }
        .summary-section { margin: 10px 0; border: 1px solid #000; padding: 7px; }
        .summary-title { font-weight: bold; text-align: center; margin-bottom: 7px; text-decoration: underline; }
        .signature-section { margin-top: 14px; width: 100%; page-break-inside: avoid; }
        .signature-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .signature-cell { width: 33.33%; text-align: center; vertical-align: top; padding: 7px 12px; overflow: hidden; }
        .signature-title { font-weight: bold; margin: 0 auto 42px; min-height: 30px; line-height: 1.25; }
        .signature-name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .signature-name { font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px; }
        .signature-nip { font-size: 8.5pt; margin-top: 4px; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 100pt; color: rgba(0,0,0,0.05); z-index: -1; font-weight: bold; }
        .status-badge { display: inline-block; padding: 2px 7px; border: 1px solid #000; font-weight: bold; font-size: 8.5pt; }
        .print-info { margin-top: 7px; font-size: 8pt; color: #666; text-align: center; }
    </style>
</head>
<body>
@php
    $nim = $krs->mahasiswa->numb_nim ?? $krs->mahasiswa->nim ?? $krs->mahasiswa->code ?? '-';
    $tahunMasukRaw = $krs->mahasiswa->taka_regist ?? null;
    $tahunMasuk = '-';
    if ($tahunMasukRaw !== null && $tahunMasukRaw !== '') {
        $tahunMasukRaw = trim((string) $tahunMasukRaw);
        $tahunMasuk = preg_match('/^\d{2}$/', $tahunMasukRaw)
            ? (string) (2000 + (int) $tahunMasukRaw)
            : $tahunMasukRaw;
    }
    $dosenWali = $krs->dosenPA ?? null; if (!$dosenWali) { $fallbackJabatan = \App\Models\Jabatan::with('dosen')->where('is_active', true)->whereIn('name', ['Dosen Pembimbing Akademik', 'Dosen Pembimbing'])->where(function ($q) use ($krs) { $q->whereNull('prodi_id')->orWhere('prodi_id', $krs->mahasiswa->prodi_id); })->whereNotNull('dosen_id')->orderBy('sort_order')->first(); $dosenWali = $fallbackJabatan?->dosen; }
    $dosenNidn = $dosenWali?->nidn ?? $dosenWali?->nidn_number ?? $dosenWali?->numb_nidn ?? $dosenWali?->number_nidn ?? '-';

    $logoPath = null;
    // Gunakan file logo langsung di public/. Controller mengatur chroot
    // Dompdf ke public_path() agar file lokal dapat dibaca saat render PDF.
    $logoCandidates = [
        public_path('images/branding/logo-vert.png'),
        public_path('logo.png'),
        public_path('images/logo/logo-vert.png'),
        public_path('images/logo/logo-hori.png'),
    ];

    foreach ($logoCandidates as $candidate) {
        if (is_file($candidate) && is_readable($candidate)) {
            $logoPath = $candidate;
            break;
        }
    }
@endphp

<div class="watermark">{{ strtoupper($krs->status) }}</div>

<div class="kop">
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if($logo)
                    <img src="{{ $logoPath }}" alt="Logo STIT Darul Ilmi">
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
    @php
        $tahunAkademikRaw = (string) ($krs->tahunAkademik->name ?? '');
        if (preg_match('/(\d{4}\s*\/\s*\d{4})/', $tahunAkademikRaw, $matches)) {
            $tahunAkademik = preg_replace('/\s+/', '', $matches[1]);
        } else {
            $tahunAkademik = trim(preg_replace('/\s*[-|]\s*(Ganjil|Genap)\s*$/i', '', $tahunAkademikRaw));
        }
    @endphp
    <div class="semester-line">Semester {{ $krs->semester }} | Tahun Akademik {{ $tahunAkademik ?: '-' }}</div>
</div>

<div class="student-info">
    <table>
        <tr>
            <td class="label">Nama Mahasiswa</td><td class="colon">:</td><td class="value">{{ $krs->mahasiswa->name }}</td>
            <td class="label">Program Studi</td><td class="colon">:</td><td class="value">{{ $krs->mahasiswa->programStudi->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td><td class="colon">:</td><td class="value">{{ $nim }}</td>
            <td class="label">Tahun Masuk</td><td class="colon">:</td><td class="value">{{ $tahunMasuk }}</td>
        </tr>
        <tr>
            <td class="label">Semester</td><td class="colon">:</td><td class="value">{{ $krs->semester }}</td>
            <td class="label">Dosen Wali</td><td class="colon">:</td><td class="value">{{ $dosenWali->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Status KRS</td><td class="colon">:</td><td class="value"><span class="status-badge">{{ $krs->status }}</span></td>
            <td class="label">Total SKS</td><td class="colon">:</td><td class="value"><strong>{{ $krs->total_sks }} SKS</strong></td>
        </tr>
    </table>
</div>

<table class="courses-table">
    <thead>
        <tr>
            <th style="width:5%;">No</th>
            <th style="width:12%;">Kode MK</th>
            <th style="width:33%;">Mata Kuliah</th>
            <th style="width:7%;">SKS</th>
            <th style="width:9%;">Kelas</th>
            <th style="width:8%;">Ruang</th>
            <th style="width:26%;">Dosen</th>
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
                <td>{{ $loop->iteration }}</td>
                <td>{{ $detail->mataKuliah->code ?? '-' }}</td>
                <td class="subject-name">{{ $detail->mataKuliah->name ?? '-' }}</td>
                <td>{{ $detail->mataKuliah->sks ?? $detail->sks ?? 0 }}</td>
                <td>{{ $detail->kelas->name ?? '-' }}</td>
                <td class="room">{{ $jadwal?->ruang?->name ?? $jadwal?->ruang ?? '-' }}</td>
                <td class="lecturer">
                    @if ($dosen1)
                        {{ $dosen1->name }}
                        @if ($dosen2)
                            <br>{{ $dosen2->name }}
                        @endif
                    @else
                        -
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;font-style:italic;">Belum ada mata kuliah yang dipilih</td></tr>
        @endforelse
    </tbody>
    @if ($krs->details->count() > 0)
        <tfoot>
            <tr style="background-color:#f0f0f0;">
                <td colspan="3" style="text-align:right;font-weight:bold;">TOTAL SKS</td>
                <td style="font-weight:bold;">{{ $krs->total_sks }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    @endif
</table>

<div style="margin-top:8px;padding:7px;border:1px dashed #000;font-size:9pt;">
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
                    <div style="font-size:8pt;margin-top:4px;">Disetujui: {{ $krs->approved_at->format('d/m/Y H:i') }}</div>
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
    Status KRS: {{ $krs->status }} |
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
