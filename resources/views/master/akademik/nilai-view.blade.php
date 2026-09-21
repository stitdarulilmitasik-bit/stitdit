@extends('core-themes.core-backpage')
@section('content')
<div class="container-xl">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Detail Nilai</h3>
            <a class="btn btn-secondary" href="{{ route($spref.'akademik.nilai-render') }}">Kembali</a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <p><strong>Mahasiswa:</strong> {{ $nilai->mahasiswa->name ?? '-' }}</p>
                    <p><strong>NIM:</strong> {{ $nilai->mahasiswa->numb_nim ?? $nilai->mahasiswa->nim ?? '-' }}</p>
                    <p><strong>Mata Kuliah:</strong> {{ $nilai->mataKuliah->name ?? '-' }}</p>
                    <p><strong>Kode Mata Kuliah:</strong> {{ $nilai->mataKuliah->code ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Tahun Akademik:</strong> {{ $nilai->tahunAkademik->name ?? '-' }} - {{ $nilai->tahunAkademik->semester ?? '-' }}</p>
                    <p><strong>Semester:</strong> {{ $nilai->semester ?? '-' }}</p>
                    <p><strong>Status:</strong> {{ $nilai->status ?? '-' }}</p>
                </div>
            </div>

            <hr>

            <h5 class="mb-3">Hasil Nilai</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="border rounded p-3 text-center">
                        <small class="text-muted d-block">Nilai Angka</small>
                        <strong class="fs-3">{{ $nilai->nilai_angka !== null ? number_format((float)$nilai->nilai_angka, 2) : '-' }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 text-center">
                        <small class="text-muted d-block">Nilai Huruf / Grade</small>
                        <strong class="fs-3">{{ $nilai->nilai_huruf ?? '-' }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 text-center">
                        <small class="text-muted d-block">Grade Point / Mutu</small>
                        <strong class="fs-3">{{ $nilai->nilai_mutu !== null ? number_format((float)$nilai->nilai_mutu, 2) : '-' }}</strong>
                    </div>
                </div>
            </div>

            <hr>

            <h5 class="mb-3">Komponen Nilai</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th class="text-center">Nilai</th>
                            <th class="text-center">Bobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Tugas</td><td class="text-center">{{ $nilai->rata_tugas !== null ? number_format((float)$nilai->rata_tugas, 2) : '-' }}</td><td class="text-center">{{ $nilai->bobot_tugas ?? 0 }}%</td></tr>
                        <tr><td>Quiz</td><td class="text-center">{{ $nilai->rata_quiz !== null ? number_format((float)$nilai->rata_quiz, 2) : '-' }}</td><td class="text-center">{{ $nilai->bobot_quiz ?? 0 }}%</td></tr>
                        <tr><td>UTS</td><td class="text-center">{{ $nilai->uts !== null ? number_format((float)$nilai->uts, 2) : '-' }}</td><td class="text-center">{{ $nilai->bobot_uts ?? 0 }}%</td></tr>
                        <tr><td>UAS</td><td class="text-center">{{ $nilai->uas !== null ? number_format((float)$nilai->uas, 2) : '-' }}</td><td class="text-center">{{ $nilai->bobot_uas ?? 0 }}%</td></tr>
                        <tr><td>Praktikum</td><td class="text-center">{{ $nilai->praktikum !== null ? number_format((float)$nilai->praktikum, 2) : '-' }}</td><td class="text-center">{{ $nilai->bobot_praktikum ?? 0 }}%</td></tr>
                        <tr><td>Kehadiran</td><td class="text-center">{{ $nilai->kehadiran !== null ? number_format((float)$nilai->kehadiran, 2) : '-' }}</td><td class="text-center">{{ $nilai->bobot_kehadiran ?? 20 }}%</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
