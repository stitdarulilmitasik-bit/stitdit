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

            <hr>

            @if(($nilai->status ?? 'Draft') === 'Draft')
            <h5 class="mb-3">Input Nilai & Bobot</h5>
            <form method="POST" action="{{ route($spref.'akademik.nilai-update', $nilai->code) }}" id="form-nilai">
                @csrf
                @method('PATCH')

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-3">
                        <thead>
                            <tr>
                                <th style="width: 35%">Komponen</th>
                                <th class="text-center" style="width: 30%">Nilai (0-100)</th>
                                <th class="text-center" style="width: 25%">Bobot (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Tugas <small class="text-muted">(rata-rata Tugas 1-3)</small></td>
                                <td><input type="number" class="form-control" name="tugas_1" value="{{ old('tugas_1', $nilai->tugas_1) }}" min="0" max="100" step="0.01" placeholder="Nilai tugas"></td>
                                <td><input type="number" class="form-control bobot-input" name="bobot_tugas" value="{{ old('bobot_tugas', $nilai->bobot_tugas ?? 20) }}" min="0" max="100" step="0.01"></td>
                            </tr>
                            <tr>
                                <td>Quiz <small class="text-muted">(rata-rata Quiz 1-2)</small></td>
                                <td><input type="number" class="form-control" name="quiz_1" value="{{ old('quiz_1', $nilai->quiz_1) }}" min="0" max="100" step="0.01" placeholder="Nilai quiz"></td>
                                <td><input type="number" class="form-control bobot-input" name="bobot_quiz" value="{{ old('bobot_quiz', $nilai->bobot_quiz ?? 10) }}" min="0" max="100" step="0.01"></td>
                            </tr>
                            <tr>
                                <td>UTS</td>
                                <td><input type="number" class="form-control" name="uts" value="{{ old('uts', $nilai->uts) }}" min="0" max="100" step="0.01"></td>
                                <td><input type="number" class="form-control bobot-input" name="bobot_uts" value="{{ old('bobot_uts', $nilai->bobot_uts ?? 25) }}" min="0" max="100" step="0.01"></td>
                            </tr>
                            <tr>
                                <td>UAS</td>
                                <td><input type="number" class="form-control" name="uas" value="{{ old('uas', $nilai->uas) }}" min="0" max="100" step="0.01"></td>
                                <td><input type="number" class="form-control bobot-input" name="bobot_uas" value="{{ old('bobot_uas', $nilai->bobot_uas ?? 25) }}" min="0" max="100" step="0.01"></td>
                            </tr>
                            <tr>
                                <td>Praktikum</td>
                                <td><input type="number" class="form-control" name="praktikum" value="{{ old('praktikum', $nilai->praktikum) }}" min="0" max="100" step="0.01"></td>
                                <td><input type="number" class="form-control bobot-input" name="bobot_praktikum" value="{{ old('bobot_praktikum', $nilai->bobot_praktikum ?? 5) }}" min="0" max="100" step="0.01"></td>
                            </tr>
                            <tr>
                                <td>Kehadiran <small class="text-muted">(otomatis dari absensi)</small></td>
                                <td><input type="number" class="form-control" name="kehadiran" value="{{ old('kehadiran', $nilai->kehadiran) }}" min="0" max="100" step="0.01"></td>
                                <td><input type="number" class="form-control" name="bobot_kehadiran" value="15" readonly></td>
                            </tr>
                            <tr class="table-light">
                                <th colspan="2" class="text-end">Total Bobot</th>
                                <th class="text-center"><span id="total-bobot">0</span>%</th>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="bobot-warning" class="alert alert-warning d-none">
                    Total bobot harus tepat 100%. Kehadiran ditetapkan 15%, sehingga lima komponen akademik harus berjumlah 85%.
                </div>

                <button type="submit" class="btn btn-primary" id="btn-simpan-nilai">
                    <i class="bi bi-save me-1"></i> Simpan Nilai & Bobot
                </button>
            </form>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const inputs = document.querySelectorAll('.bobot-input');
                const total = document.getElementById('total-bobot');
                const warning = document.getElementById('bobot-warning');
                const form = document.getElementById('form-nilai');
                const button = document.getElementById('btn-simpan-nilai');

                function hitungBobot() {
                    let jumlah = 15;
                    inputs.forEach(input => jumlah += parseFloat(input.value || 0));
                    total.textContent = jumlah.toFixed(2).replace(/\\.00$/, '');

                    const valid = Math.abs(jumlah - 100) < 0.01;
                    warning.classList.toggle('d-none', valid);
                    total.classList.toggle('text-danger', !valid);
                    total.classList.toggle('text-success', valid);
                    button.disabled = !valid;
                }

                inputs.forEach(input => input.addEventListener('input', hitungBobot));
                hitungBobot();

                form.addEventListener('submit', function () {
                    button.disabled = true;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
                });
            });
            </script>
            @endif

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
                        <tr><td>Kehadiran</td><td class="text-center">{{ $nilai->kehadiran !== null ? number_format((float)$nilai->kehadiran, 2) : '-' }}</td><td class="text-center">{{ $nilai->bobot_kehadiran ?? 15 }}%</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
