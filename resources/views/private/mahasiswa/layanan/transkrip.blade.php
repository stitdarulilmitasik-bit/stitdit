@extends('core-themes.core-backpage')

@section('custom-css')
<style>
    .transkrip-summary{border-radius:12px}
    .transkrip-summary .value{font-size:1.75rem;font-weight:700}
    .transkrip-table th{white-space:nowrap}
    .transkrip-table td{vertical-align:middle}
    @media print{.d-print-none{display:none!important}.card{box-shadow:none!important;border:1px solid #ddd}}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Transkrip Nilai</h2>
                <div class="text-muted">Rekapitulasi hasil studi kumulatif mahasiswa</div>
            </div>
            <div class="col-auto">
                <a href="{{ route('mahasiswa.layanan.transkrip.cetak') }}" class="btn btn-primary">
                    Cetak Transkrip PDF
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">Nama Mahasiswa</div>
                    <div class="fw-bold">{{ $user->name ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">NIM</div>
                    <div class="fw-bold">{{ $user->numb_nim ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Program Studi</div>
                    <div class="fw-bold">{{ $user->programStudi->name ?? $user->prodi->name ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Tahun Masuk</div>
                    <div class="fw-bold">{{ $user->taka_regist ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-4">
            <div class="card transkrip-summary h-100">
                <div class="card-body">
                    <div class="text-muted">Total SKS</div>
                    <div class="value">{{ number_format((float)$totalSks, 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card transkrip-summary h-100">
                <div class="card-body">
                    <div class="text-muted">Total Mutu</div>
                    <div class="value">{{ number_format((float)$totalMutu, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card transkrip-summary h-100">
                <div class="card-body">
                    <div class="text-muted">IPK</div>
                    <div class="value">{{ number_format((float)$ipk, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hasil Studi</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table transkrip-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Semester</th>
                        <th>Kode MK</th>
                        <th>Mata Kuliah</th>
                        <th class="text-center">SKS</th>
                        <th class="text-center">Nilai</th>
                        <th class="text-center">Bobot</th>
                        <th class="text-center">Mutu</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($nilai as $i => $n)
                    @php
                        $huruf = strtoupper(trim((string)($n->nilai_huruf ?? '')));
                        $map = \App\Models\Akademik\Nilai::NILAI_HURUF_MAP;
                        $bobot = isset($map[$huruf]) ? (float)$map[$huruf]['mutu'] : (float)($n->nilai_mutu ?? 0);
                        $sks = (float)($n->sks ?? $n->mataKuliah?->sks ?? $n->mataKuliah?->bsks ?? 0);
                        $mutu = $bobot * $sks;
                        $statusLulus = $bobot >= 2.00;
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $n->semester ?? '-' }}</td>
                        <td>{{ $n->mataKuliah->code ?? $n->mataKuliah->kode_mk ?? '-' }}</td>
                        <td>{{ $n->mataKuliah->name ?? $n->mataKuliah->nama ?? '-' }}</td>
                        <td class="text-center">{{ number_format($sks, 0) }}</td>
                        <td class="text-center">{{ $n->nilai_angka !== null ? number_format((float)$n->nilai_angka, 2) : '-' }}</td>
                        <td class="text-center">{{ $huruf ?: '-' }}</td>
                        <td class="text-center">{{ number_format($mutu, 2) }}</td>
                        <td class="text-center">
                            <span class="badge {{ $statusLulus ? 'bg-success' : 'bg-danger' }}">
                                {{ $statusLulus ? 'Lulus' : 'Belum Lulus' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty py-5">
                                <p class="empty-title">Belum ada hasil studi</p>
                                <p class="empty-subtitle text-muted">
                                    Transkrip akan terisi otomatis setelah nilai mahasiswa berstatus Published atau Locked.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
                @if($nilai->count())
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="4" class="text-end">TOTAL</td>
                        <td class="text-center">{{ number_format((float)$totalSks, 0) }}</td>
                        <td colspan="2"></td>
                        <td class="text-center">{{ number_format((float)$totalMutu, 2) }}</td>
                        <td class="text-center">IPK {{ number_format((float)$ipk, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="text-muted small mt-3">
        Catatan: jika mata kuliah ditempuh lebih dari satu kali, transkrip menampilkan hasil terbaik yang sudah Published/Locked.
    </div>
</div>
@endsection
