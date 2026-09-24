<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Rencana Studi - {{ $krs->mahasiswa->name }}</title>
    <style>
        body {
            font-family: helvetica, sans-serif;
            font-size: 9pt;
            color: #111111;
            margin: 0;
            padding: 0;
        }

        .kop {
            width: 100%;
            border-bottom: 2px solid #111111;
            padding-bottom: 7px;
            margin-bottom: 10px;
        }

        .kop-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kop-table td {
            border: 0;
            padding: 0;
            vertical-align: middle;
        }

        .kop-logo {
            width: 18%;
            text-align: center;
        }

        .kop-logo img {
            width: 68px;
            height: 68px;
        }

        .kop-text {
            width: 82%;
            text-align: center;
        }

        .kop-text .line1 {
            font-size: 11pt;
            font-weight: bold;
        }

        .kop-text .line2 {
            font-size: 15pt;
            font-weight: bold;
        }

        .kop-text .line3 {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 2px;
        }

        .kop-text .address {
            font-size: 7.5pt;
            margin-top: 2px;
        }

        .document-header {
            width: 100%;
            text-align: center;
            margin-bottom: 8px;
        }

        .document-header .faculty {
            font-size: 9pt;
            font-weight: bold;
        }

        .document-header .title {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 2px;
        }

        .document-header .semester {
            font-size: 9pt;
            margin-top: 2px;
        }

        .student-info {
            width: 100%;
            margin-bottom: 8px;
        }

        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-info td {
            border: 0;
            padding: 2.5px 2px;
            vertical-align: middle;
        }

        .student-info .label {
            width: 16%;
            font-weight: bold;
        }

        .student-info .colon {
            width: 2%;
            text-align: center;
        }

        .student-info .value {
            width: 32%;
        }

        .student-info .label-right {
            width: 16%;
            font-weight: bold;
            padding-left: 8px;
        }

        .student-info .value-right {
            width: 32%;
        }

        .status {
            font-weight: bold;
        }

        .courses {
            width: 100%;
            border-collapse: collapse;
            border: 0.6px solid #222222;
            margin-bottom: 8px;
        }

        .courses th {
            background-color: #e9ecef;
            border: 0.6px solid #222222;
            padding: 4px 3px;
            font-size: 8pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .courses td {
            border: 0.6px solid #222222;
            padding: 4px 3px;
            font-size: 8pt;
            line-height: 1.15;
            vertical-align: middle;
        }

        .center {
            text-align: center;
        }

        .left {
            text-align: left;
        }

        .total-row td {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .notes {
            width: 100%;
            border: 0.6px solid #777777;
            padding: 5px 7px;
            margin-bottom: 9px;
            font-size: 7.8pt;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .notes ol {
            margin: 0;
            padding-left: 15px;
        }

        .notes li {
            margin-bottom: 1px;
        }

        .signatures {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
            margin-top: 4px;
        }

        .signatures td {
            width: 33.33%;
            border: 0;
            text-align: center;
            vertical-align: top;
            padding: 4px 8px;
        }

        .signature-title {
            font-weight: bold;
            min-height: 30px;
        }

        .signature-space {
            height: 38px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-number {
            font-size: 7.5pt;
            margin-top: 2px;
        }

        .print-info {
            font-size: 6.8pt;
            color: #666666;
            text-align: center;
            margin-top: 7px;
        }
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

    $dosenWali = $krs->dosenPA ?? null;
    if (!$dosenWali) {
        $fallbackJabatan = \App\Models\Jabatan::with('dosen')
            ->where('is_active', true)
            ->whereIn('name', ['Dosen Pembimbing Akademik', 'Dosen Pembimbing'])
            ->where(function ($q) use ($krs) {
                $q->whereNull('prodi_id')->orWhere('prodi_id', $krs->mahasiswa->prodi_id);
            })
            ->whereNotNull('dosen_id')
            ->orderBy('sort_order')
            ->first();

        $dosenWali = $fallbackJabatan?->dosen;
    }

    $dosenNidn = $dosenWali?->nidn
        ?? $dosenWali?->nidn_number
        ?? $dosenWali?->numb_nidn
        ?? $dosenWali?->number_nidn
        ?? '-';

    $logo = $logoDataUri ?? null;

    $tahunAkademikRaw = (string) ($krs->tahunAkademik->name ?? '');
    if (preg_match('/(\d{4}\s*\/\s*\d{4})/', $tahunAkademikRaw, $matches)) {
        $tahunAkademik = preg_replace('/\s+/', '', $matches[1]);
    } else {
        $tahunAkademik = trim(preg_replace('/\s*[-|]\s*(Ganjil|Genap)\s*$/i', '', $tahunAkademikRaw));
    }
@endphp

<div class="kop">
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo STIT Darul Ilmi">
                @endif
            </td>
            <td class="kop-text">
                <div class="line1">SEKOLAH TINGGI ILMU TARBIYAH</div>
                <div class="line2">STIT DARUL ILMI TASIKMALAYA</div>
                <div class="line3">SK Menteri Agama RI No. 536 Tahun 2026</div>
                <div class="address">Jl. Cirahayu Sindangraja Jamanis Kabupaten Tasikmalaya Jawa Barat 46175</div>
            </td>
        </tr>
    </table>
</div>

<div class="document-header">
    <div class="faculty">{{ $krs->mahasiswa->programStudi->fakultas->name ?? 'FAKULTAS' }}</div>
    <div class="title">KARTU RENCANA STUDI (KRS)</div>
    <div class="semester">Semester {{ $krs->semester }} &nbsp;|&nbsp; Tahun Akademik {{ $tahunAkademik ?: '-' }}</div>
</div>

<div class="student-info">
    <table>
        <tr>
            <td class="label">Nama Mahasiswa</td>
            <td class="colon">:</td>
            <td class="value">{{ $krs->mahasiswa->name }}</td>
            <td class="label-right">Program Studi</td>
            <td class="colon">:</td>
            <td class="value-right">{{ $krs->mahasiswa->programStudi->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td>
            <td class="colon">:</td>
            <td class="value">{{ $nim }}</td>
            <td class="label-right">Tahun Masuk</td>
            <td class="colon">:</td>
            <td class="value-right">{{ $tahunMasuk }}</td>
        </tr>
        <tr>
            <td class="label">Semester</td>
            <td class="colon">:</td>
            <td class="value">{{ $krs->semester }}</td>
            <td class="label-right">Dosen Wali</td>
            <td class="colon">:</td>
            <td class="value-right">{{ $dosenWali->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Status KRS</td>
            <td class="colon">:</td>
            <td class="value status">{{ $krs->status }}</td>
            <td class="label-right">Total SKS</td>
            <td class="colon">:</td>
            <td class="value-right"><strong>{{ $krs->total_sks }} SKS</strong></td>
        </tr>
    </table>
</div>

<table class="courses">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="12%">Kode MK</th>
            <th width="31%">Mata Kuliah</th>
            <th width="7%">SKS</th>
            <th width="9%">Kelas</th>
            <th width="10%">Ruang</th>
            <th width="26%">Dosen</th>
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
                <td class="center">{{ $loop->iteration }}</td>
                <td class="center">{{ $detail->mataKuliah->code ?? '-' }}</td>
                <td class="left">{{ $detail->mataKuliah->name ?? '-' }}</td>
                <td class="center">{{ $detail->mataKuliah->sks ?? $detail->sks ?? 0 }}</td>
                <td class="center">{{ $detail->kelas->name ?? '-' }}</td>
                <td class="center">{{ $jadwal?->ruang?->name ?? $jadwal?->ruang ?? '-' }}</td>
                <td class="left">
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
            <tr>
                <td colspan="7" class="center"><i>Belum ada mata kuliah yang dipilih.</i></td>
            </tr>
        @endforelse

        @if ($krs->details->count() > 0)
            <tr class="total-row">
                <td colspan="3" class="left">TOTAL SKS</td>
                <td class="center">{{ $krs->total_sks }}</td>
                <td colspan="3"></td>
            </tr>
        @endif
    </tbody>
</table>

<table class="notes">
    <tr>
        <td style="border:0; padding:0;">
            <div class="notes-title">CATATAN PENTING</div>
            <ol>
                <li>KRS ini harus mendapat persetujuan dari Dosen Pembimbing Akademik.</li>
                <li>Perubahan KRS hanya dapat dilakukan pada periode yang telah ditentukan.</li>
                <li>Mahasiswa wajib mengikuti semua mata kuliah yang tercantum dalam KRS.</li>
                <li>KRS yang telah disetujui dan dikunci tidak dapat diubah.</li>
            </ol>
        </td>
    </tr>
</table>

<table class="signatures">
    <tr>
        <td>
            <div class="signature-title">Mahasiswa</div>
            <div class="signature-space"></div>
            <div class="signature-name">{{ $krs->mahasiswa->name }}</div>
            <div class="signature-number">NIM. {{ $nim }}</div>
        </td>
        <td>
            <div class="signature-title">Dosen Pembimbing Akademik</div>
            <div class="signature-space"></div>
            <div class="signature-name">{{ $dosenWali->name ?? '[Nama Dosen PA]' }}</div>
            <div class="signature-number">NIDN. {{ $dosenNidn !== '-' ? $dosenNidn : '[NIDN Dosen PA]' }}</div>
            @if ($krs->approved_at)
                <div class="signature-number">Disetujui: {{ $krs->approved_at->format('d/m/Y H:i') }}</div>
            @endif
        </td>
        <td>
            <div class="signature-title">Ketua Program Studi</div>
            <div class="signature-space"></div>
            <div class="signature-name">{{ $kaprodi->name ?? '[Nama Ketua Prodi]' }}</div>
            <div class="signature-number">
                NIDN. {{ $kaprodi->nidn ?? $kaprodi->nidn_number ?? $kaprodi->numb_nidn ?? $kaprodi->number_nidn ?? '[NIDN Ketua Prodi]' }}
            </div>
        </td>
    </tr>
</table>

<div class="print-info">
    Dicetak pada: {{ now()->locale('id')->translatedFormat('d F Y H:i:s') }}
    &nbsp;|&nbsp; Status KRS: {{ $krs->status }}
    &nbsp;|&nbsp;
    @if ($krs->approved_at)
        Disetujui: {{ $krs->approved_at->format('d F Y H:i') }}
    @else
        Belum Disetujui
    @endif
    @if ($krs->published_at)
        &nbsp;|&nbsp; Dipublish: {{ $krs->published_at->format('d F Y H:i') }}
    @endif
</div>
</body>
</html>
