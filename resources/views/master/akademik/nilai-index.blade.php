@extends('core-themes.core-backpage')

@section('custom-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        /* Stats cards */
        .bg-light-primary {
            background-color: rgba(67, 94, 190, 0.1);
        }

        .bg-light-success {
            background-color: rgba(40, 167, 69, 0.1);
        }

        .bg-light-warning {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .bg-light-info {
            background-color: rgba(23, 162, 184, 0.1);
        }

        .bg-light-danger {
            background-color: rgba(220, 53, 69, 0.1);
        }

        /* Card styling */
        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            border-radius: 10px;
        }

        .card-header {
            background: none;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Table styling */
        .table {
            margin-bottom: 0;
        }

        .table thead th {
            border-top: none;
            border-bottom: 2px solid rgba(0,0,0,0.05);
            font-weight: 600;
            color: #6c757d;
            padding-top: 1rem;
            padding-bottom: 0.75rem;
            text-align: left;
        }

        .table td {
            vertical-align: middle;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            text-align: left;
        }

        .table th.text-center, .table td.text-center {
            text-align: center !important;
        }

        /* Button styling */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
        }

        /* Form styling */
        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid rgba(0,0,0,0.1);
            padding: 0.5rem 1rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #435ebe;
            box-shadow: 0 0 0 0.2rem rgba(67, 94, 190, 0.25);
        }

        /* Badge styling */
        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
        }

        /* Grade input styling */
        .grade-input {
            width: 80px;
            text-align: center;
        }

        /* Collapsible form */
        .collapse {
            transition: all 0.3s ease;
        }

        .collapse.show {
            margin-top: 1rem;
        }

        /* Responsive styling */
        @media (max-width: 768px) {
            .table-responsive table,
            .table-responsive thead,
            .table-responsive tbody,
            .table-responsive th,
            .table-responsive td,
            .table-responsive tr {
                display: block;
                width: 100%;
            }
            .table-responsive thead {
                display: none;
            }
            .table-responsive tr {
                margin-bottom: 1rem;
                border-bottom: 2px solid #eee;
            }
            .table-responsive td {
                position: relative;
                padding-left: 50%;
                text-align: left !important;
                border: none;
                border-bottom: 1px solid #eee;
            }
            .table-responsive td:before {
                position: absolute;
                top: 0;
                left: 0;
                width: 48%;
                padding-left: 1rem;
                white-space: nowrap;
                font-weight: bold;
                color: #888;
                content: attr(data-label);
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 col-12 mb-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $pages }}</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route($spref . 'akademik.nilai-import') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-upload me-2"></i>Import Nilai
                        </a>
                        <a href="{{ route($spref . 'akademik.nilai-export') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-download me-2"></i>Export Nilai
                        </a>
                        <button class="btn btn-warning btn-sm" onclick="bulkUpdate()">
                            <i class="fas fa-edit me-2"></i>Update Terpilih
                        </button>
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseForm" aria-expanded="true" aria-controls="collapseForm">
                            <i class="fas fa-plus-circle me-2"></i>Tambah Nilai
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-2">
                            <div class="p-3 bg-light-primary rounded">
                                <h6 class="mb-2">Total Nilai</h6>
                                <h3 class="mb-0">{{ count($nilai_list) }}</h3>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2">
                            <div class="p-3 bg-light-success rounded">
                                <h6 class="mb-2">Lulus (≥ C)</h6>
                                <h3 class="mb-0">{{ $nilai_list->where('grade_point', '>=', 2.0)->count() }}</h3>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2">
                            <div class="p-3 bg-light-danger rounded">
                                <h6 class="mb-2">Tidak Lulus (< C)</h6>
                                <h3 class="mb-0">{{ $nilai_list->where('grade_point', '<', 2.0)->count() }}</h3>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2">
                            <div class="p-3 bg-light-warning rounded">
                                <h6 class="mb-2">Belum Publish</h6>
                                <h3 class="mb-0">{{ $nilai_list->where('is_published', false)->count() }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Collapsible Form -->
                    <div class="collapse show" id="collapseForm">
                        <div class="card card-body border">
                            <h5 class="card-title mb-3">Tambah Nilai Baru</h5>
                            <form action="{{ route($spref . 'akademik.nilai-handle') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="mahasiswa_id" class="form-label">Mahasiswa</label>
                                        <select class="form-select" name="mahasiswa_id" id="mahasiswa_id" required>
                                            <option value="">Pilih Mahasiswa</option>
                                            @foreach ($mahasiswa as $m)
                                                <option value="{{ $m->id }}">{{ $m->nim }} - {{ $m->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('mahasiswa_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="mata_kuliah_id" class="form-label">Mata Kuliah</label>
                                        <select class="form-select" name="mata_kuliah_id" id="mata_kuliah_id" required>
                                            <option value="">Pilih Mata Kuliah</option>
                                            @foreach ($mata_kuliah as $mk)
                                                <option value="{{ $mk->id }}"
                                                    data-dosen1="{{ $mk->dosen1_id }}"
                                                    data-dosen2="{{ $mk->dosen2_id }}"
                                                    data-dosen3="{{ $mk->dosen3_id }}">
                                                    {{ $mk->code }} - {{ $mk->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('mata_kuliah_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tahun_akademik_id" class="form-label">Tahun Akademik</label>
                                        <select class="form-select" name="tahun_akademik_id" id="tahun_akademik_id" required>
                                            <option value="">Pilih Tahun Akademik</option>
                                            @foreach ($tahun_akademik as $ta)
                                                <option value="{{ $ta->id }}" {{ $ta->status == 'Aktif' ? 'selected' : '' }}>
                                                    {{ $ta->name }} - {{ $ta->semester }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('tahun_akademik_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="semester" class="form-label">Semester</label>
                                        <select class="form-select" name="semester" id="semester" required>
                                            <option value="">Pilih Semester</option>
                                            @for ($s = 1; $s <= 8; $s++)
                                                <option value="{{ $s }}" {{ old('semester') == $s ? 'selected' : '' }}>
                                                    Semester {{ $s }}
                                                </option>
                                            @endfor
                                        </select>
                                        @error('semester')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="dosen_id" class="form-label">Dosen</label>
                                        <select class="form-select" name="dosen_id" id="dosen_id">
                                            <option value="">Pilih Mata Kuliah terlebih dahulu</option>
                                        </select>
                                        <small class="text-muted">Daftar dosen otomatis mengikuti dosen pengampu pada tabel mata kuliahs (Dosen 1, Dosen 2, Dosen 3).</small>
                                        @error('dosen_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="nilai_angka" class="form-label">Nilai Angka</label>
                                        <input type="number" class="form-control" name="nilai_angka" id="nilai_angka"
                                               min="0" max="100" step="0.1" placeholder="0-100" onchange="calculateGrade()">
                                        @error('nilai_angka')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="nilai_huruf" class="form-label">Nilai Huruf</label>
                                        <select class="form-select" name="nilai_huruf" id="nilai_huruf" onchange="calculatePoint()">
                                            <option value="">Pilih Grade</option>
                                            <option value="A">A</option>
                                            <option value="A-">A-</option>
                                            <option value="B+">B+</option>
                                            <option value="B">B</option>
                                            <option value="B-">B-</option>
                                            <option value="C+">C+</option>
                                            <option value="C">C</option>
                                            <option value="D">D</option>
                                            <option value="E">E</option>
                                        </select>
                                        @error('nilai_huruf')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="grade_point" class="form-label">Grade Point</label>
                                        <input type="number" class="form-control" name="grade_point" id="grade_point"
                                               min="0" max="4" step="0.1" readonly>
                                        @error('grade_point')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="status_lulus" class="form-label">Status</label>
                                        <select class="form-select" name="status_lulus" id="status_lulus">
                                            <option value="lulus">Lulus</option>
                                            <option value="tidak_lulus">Tidak Lulus</option>
                                            <option value="mengulang">Mengulang</option>
                                        </select>
                                        @error('status_lulus')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="card border">
                                            <div class="card-body">
                                                <h6 class="mb-3 fw-bold text-primary"><i class="fas fa-clipboard-check me-2"></i>Komponen Nilai</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Tugas (rata-rata Tugas 1-3)</label>
                                                        <div class="row g-2">
                                                            <div class="col-4"><input type="number" class="form-control komponen-nilai" name="tugas_1" id="tugas_1" min="0" max="100" step="0.01" placeholder="Tugas 1"></div>
                                                            <div class="col-4"><input type="number" class="form-control komponen-nilai" name="tugas_2" id="tugas_2" min="0" max="100" step="0.01" placeholder="Tugas 2"></div>
                                                            <div class="col-4"><input type="number" class="form-control komponen-nilai" name="tugas_3" id="tugas_3" min="0" max="100" step="0.01" placeholder="Tugas 3"></div>
                                                        </div>
                                                        <small class="text-muted">Rata-rata: <strong id="rata_tugas_preview">0.00</strong></small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Quiz (rata-rata Quiz 1-2)</label>
                                                        <div class="row g-2">
                                                            <div class="col-6"><input type="number" class="form-control komponen-nilai" name="quiz_1" id="quiz_1" min="0" max="100" step="0.01" placeholder="Quiz 1"></div>
                                                            <div class="col-6"><input type="number" class="form-control komponen-nilai" name="quiz_2" id="quiz_2" min="0" max="100" step="0.01" placeholder="Quiz 2"></div>
                                                        </div>
                                                        <small class="text-muted">Rata-rata: <strong id="rata_quiz_preview">0.00</strong></small>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">UTS</label>
                                                        <input type="number" class="form-control komponen-nilai" name="uts" id="uts" min="0" max="100" step="0.01" placeholder="0-100">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">UAS</label>
                                                        <input type="number" class="form-control komponen-nilai" name="uas" id="uas" min="0" max="100" step="0.01" placeholder="0-100">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">Praktikum</label>
                                                        <input type="number" class="form-control komponen-nilai" name="praktikum" id="praktikum" min="0" max="100" step="0.01" placeholder="0-100">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Kehadiran (otomatis dari absensi)</label>
                                                        <input type="number" class="form-control" name="kehadiran" id="kehadiran" value="0" min="0" max="100" step="0.01" readonly>
                                                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Nilai kehadiran akan mengikuti data absensi mahasiswa.</small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Nilai Akhir</label>
                                                        <input type="number" class="form-control fw-bold" id="nilai_akhir_preview" value="0.00" readonly>
                                                        <small class="text-muted">Dihitung otomatis berdasarkan bobot di bawah.</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <div class="card border bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-2 fw-bold text-primary"><i class="fas fa-sliders-h me-2"></i>Bobot Komponen Nilai (WAJIB 100%)</h6>
                                                <p class="text-muted small mb-3">
                                                    Masukkan bobot untuk setiap komponen penilaian. <strong>Total seluruh bobot wajib tepat 100%.</strong>
                                                    Bobot ini akan disimpan bersama data nilai mahasiswa dan digunakan dalam perhitungan nilai akhir.
                                                </p>
                                                <div class="row g-3">
                                                    <div class="col-lg-2 col-md-4 col-6">
                                                        <label class="form-label fw-semibold small">Tugas (%)</label>
                                                        <input type="number" class="form-control bobot-tambah text-center fw-semibold" name="bobot_tugas" value="20" min="0" max="100" step="0.01" required>
                                                    </div>
                                                    <div class="col-md-2 col-6">
                                                        <label class="form-label small">Quiz (%)</label>
                                                        <input type="number" class="form-control bobot-tambah" name="bobot_quiz" value="10" min="0" max="100" step="0.01" required>
                                                    </div>
                                                    <div class="col-md-2 col-6">
                                                        <label class="form-label small">UTS (%)</label>
                                                        <input type="number" class="form-control bobot-tambah" name="bobot_uts" value="25" min="0" max="100" step="0.01" required>
                                                    </div>
                                                    <div class="col-md-2 col-6">
                                                        <label class="form-label small">UAS (%)</label>
                                                        <input type="number" class="form-control bobot-tambah" name="bobot_uas" value="30" min="0" max="100" step="0.01" required>
                                                    </div>
                                                    <div class="col-md-2 col-6">
                                                        <label class="form-label small">Praktikum (%)</label>
                                                        <input type="number" class="form-control bobot-tambah" name="bobot_praktikum" value="0" min="0" max="100" step="0.01" required>
                                                    </div>
                                                    <div class="col-md-2 col-6">
                                                        <label class="form-label small">Kehadiran (%)</label>
                                                        <input type="number" class="form-control bobot-tambah" name="bobot_kehadiran" value="15" min="0" max="100" step="0.01" required>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                                    <span class="fw-semibold">Total Bobot</span>
                                                    <span id="total-bobot-tambah" class="badge bg-success fs-6 px-3 py-2">100%</span>
                                                </div>
                                                <div id="warning-bobot-tambah" class="alert alert-success mt-3 mb-0 py-2">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Total bobot sudah tepat <strong>100%</strong>.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label for="catatan" class="form-label">Catatan</label>
                                        <textarea class="form-control" name="catatan" id="catatan" rows="3" placeholder="Catatan untuk nilai..."></textarea>
                                        @error('catatan')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseForm">
                                        <i class="fas fa-times me-2"></i>Batal
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Simpan Nilai
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="filterTahunAkademik" onchange="filterTable()">
                                <option value="">Semua Tahun Akademik</option>
                                @foreach ($tahun_akademik as $ta)
                                    <option value="{{ $ta->name }}">{{ $ta->name }} - {{ $ta->semester }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterMataKuliah" onchange="filterTable()">
                                <option value="">Semua Mata Kuliah</option>
                                @foreach ($mata_kuliah as $mk)
                                    <option value="{{ $mk->name }}">{{ $mk->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterGrade" onchange="filterTable()">
                                <option value="">Semua Grade</option>
                                <option value="A">A</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B">B</option>
                                <option value="B-">B-</option>
                                <option value="C+">C+</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="searchInput" placeholder="Cari mahasiswa..." onkeyup="filterTable()">
                        </div>
                    </div>

                    <!-- Nilai Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="nilaiTable">
                            <thead>
                                <tr>
                                    <th class="text-center">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                    <th>Mahasiswa</th>
                                    <th>Mata Kuliah</th>
                                    <th>Tahun Akademik</th>
                                    <th class="text-center">Nilai</th>
                                    <th class="text-center">Grade</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Published</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $groupedNilai = $nilai_list
                                        ->sortBy([
                                            [fn($item) => mb_strtolower($item->mahasiswa->name ?? ''), 'asc'],
                                            [fn($item) => mb_strtolower($item->mataKuliah->name ?? ''), 'asc'],
                                        ])
                                        ->groupBy(fn($item) => $item->mahasiswa_id);
                                @endphp

                                @forelse ($groupedNilai as $mahasiswaId => $rows)
                                    @php
                                        $firstNilai = $rows->first();
                                        $namaMahasiswa = $firstNilai->mahasiswa->name ?? '-';
                                        $nimMahasiswa = $firstNilai->mahasiswa->nim ?? $firstNilai->mahasiswa->numb_nim ?? '-';
                                    @endphp

                                    @foreach ($rows as $nilai)
                                        <tr
                                            data-mahasiswa="{{ strtolower($namaMahasiswa . ' ' . $nimMahasiswa) }}"
                                            data-mata-kuliah="{{ strtolower($nilai->mataKuliah->name ?? '') }}"
                                            data-tahun-akademik="{{ strtolower(($nilai->tahunAkademik->name ?? '') . ' ' . ($nilai->tahunAkademik->semester ?? '')) }}"
                                            data-grade="{{ strtolower($nilai->nilai_huruf ?? '') }}"
                                        >
                                            <td class="text-center" data-label="Pilih">
                                                <input type="checkbox" class="nilai-checkbox" value="{{ $nilai->code }}">
                                            </td>

                                            @if ($loop->first)
                                                <td data-label="Mahasiswa" rowspan="{{ $rows->count() }}" class="align-middle">
                                                    <div class="d-flex flex-column">
                                                        <strong>{{ $namaMahasiswa }}</strong>
                                                        <small class="text-muted">{{ $nimMahasiswa }}</small>
                                                        <small class="text-muted mt-1">{{ $rows->count() }} mata kuliah</small>
                                                    </div>
                                                </td>
                                            @endif

                                            <td data-label="Mata Kuliah">
                                                <div class="d-flex flex-column">
                                                    <strong>{{ $nilai->mataKuliah->name ?? '-' }}</strong>
                                                    <small class="text-muted">
                                                        {{ $nilai->mataKuliah->code ?? '-' }} ({{ $nilai->mataKuliah->sks ?? 0 }} SKS)
                                                    </small>
                                                </div>
                                            </td>

                                            <td data-label="Tahun Akademik">
                                                {{ $nilai->tahunAkademik->name ?? '-' }} - {{ $nilai->tahunAkademik->semester ?? '-' }}
                                            </td>

                                            <td class="text-center" data-label="Nilai">
                                                @if ($nilai->is_locked)
                                                    <span class="badge bg-secondary">{{ $nilai->nilai_angka ?? 'N/A' }}</span>
                                                @else
                                                    <input type="number" class="form-control grade-input"
                                                           value="{{ $nilai->nilai_angka }}"
                                                           onchange="updateNilai('{{ $nilai->code }}', 'nilai_angka', this.value)"
                                                           min="0" max="100" step="0.1">
                                                @endif
                                            </td>

                                            <td class="text-center" data-label="Grade">
                                                @php
                                                    $gradeColors = [
                                                        'A' => 'success', 'A-' => 'success',
                                                        'B+' => 'info', 'B' => 'info', 'B-' => 'info',
                                                        'C+' => 'warning', 'C' => 'warning',
                                                        'D' => 'danger', 'E' => 'danger'
                                                    ];
                                                @endphp
                                                @if ($nilai->is_locked)
                                                    <span class="badge bg-{{ $gradeColors[$nilai->nilai_huruf] ?? 'secondary' }}">
                                                        {{ $nilai->nilai_huruf }} ({{ $nilai->grade_point }})
                                                    </span>
                                                @else
                                                    <select class="form-select form-select-sm"
                                                            onchange="updateNilai('{{ $nilai->code }}', 'nilai_huruf', this.value)"
                                                            style="width: 100px;">
                                                        <option value="">-</option>
                                                        <option value="A" {{ $nilai->nilai_huruf == 'A' ? 'selected' : '' }}>A</option>
                                                        <option value="A-" {{ $nilai->nilai_huruf == 'A-' ? 'selected' : '' }}>A-</option>
                                                        <option value="B+" {{ $nilai->nilai_huruf == 'B+' ? 'selected' : '' }}>B+</option>
                                                        <option value="B" {{ $nilai->nilai_huruf == 'B' ? 'selected' : '' }}>B</option>
                                                        <option value="B-" {{ $nilai->nilai_huruf == 'B-' ? 'selected' : '' }}>B-</option>
                                                        <option value="C+" {{ $nilai->nilai_huruf == 'C+' ? 'selected' : '' }}>C+</option>
                                                        <option value="C" {{ $nilai->nilai_huruf == 'C' ? 'selected' : '' }}>C</option>
                                                        <option value="D" {{ $nilai->nilai_huruf == 'D' ? 'selected' : '' }}>D</option>
                                                        <option value="E" {{ $nilai->nilai_huruf == 'E' ? 'selected' : '' }}>E</option>
                                                    </select>
                                                @endif
                                            </td>

                                            <td class="text-center" data-label="Status">
                                                @php
                                                    $statusColors = [
                                                        'lulus' => 'success',
                                                        'tidak_lulus' => 'danger',
                                                        'mengulang' => 'warning'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$nilai->status_lulus] ?? 'secondary' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $nilai->status_lulus ?? 'belum ditentukan')) }}
                                                </span>
                                            </td>

                                            <td class="text-center" data-label="Published">
                                                @if ($nilai->is_published)
                                                    <span class="badge bg-success">Published</span>
                                                @elseif (($nilai->status ?? '') === 'Approved')
                                                    <span class="badge bg-info">Approved</span>
                                                @elseif (($nilai->status ?? '') === 'Submitted')
                                                    <span class="badge bg-warning">Submitted</span>
                                                @elseif (($nilai->status ?? '') === 'Locked')
                                                    <span class="badge bg-dark">Locked</span>
                                                @else
                                                    <span class="badge bg-secondary">Draft</span>
                                                @endif
                                            </td>

                                            <td class="text-center" data-label="Aksi">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editNilai('{{ $nilai->code }}')" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @if (!$nilai->is_published)
                                                        <button class="btn btn-sm btn-success" onclick="approveNilai('{{ $nilai->code }}')" title="Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                    @if ($nilai->status === 'Approved' && !$nilai->is_published)
                                                        <button class="btn btn-sm btn-primary" onclick="publishNilai('{{ $nilai->code }}')" title="Publish">
                                                            <i class="fas fa-share"></i>
                                                        </button>
                                                    @endif
                                                    @if ($nilai->is_published && !$nilai->is_locked)
                                                        <button class="btn btn-sm btn-dark" onclick="lockNilai('{{ $nilai->code }}')" title="Kunci">
                                                            <i class="fas fa-lock"></i>
                                                        </button>
                                                    @endif
                                                    @if (!$nilai->is_published)
                                                        <button class="btn btn-sm btn-danger" onclick="deleteNilai('{{ $nilai->code }}')" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">Belum ada data nilai mahasiswa.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Statistik Nilai</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <h6 class="text-muted">Distribusi Grade</h6>
                            @php
                                $gradeDistribution = [
                                    'A' => $nilai_list->where('nilai_huruf', 'A')->count(),
                                    'A-' => $nilai_list->where('nilai_huruf', 'A-')->count(),
                                    'B+' => $nilai_list->where('nilai_huruf', 'B+')->count(),
                                    'B' => $nilai_list->where('nilai_huruf', 'B')->count(),
                                    'B-' => $nilai_list->where('nilai_huruf', 'B-')->count(),
                                    'C+' => $nilai_list->where('nilai_huruf', 'C+')->count(),
                                    'C' => $nilai_list->where('nilai_huruf', 'C')->count(),
                                    'D' => $nilai_list->where('nilai_huruf', 'D')->count(),
                                    'E' => $nilai_list->where('nilai_huruf', 'E')->count(),
                                ];
                            @endphp
                            @foreach ($gradeDistribution as $grade => $count)
                                @if ($count > 0)
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Grade {{ $grade }}</span>
                                        <span class="badge bg-primary">{{ $count }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="col-12 mb-3">
                            <h6 class="text-muted">Status Workflow</h6>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>Draft</span>
                                <span class="badge bg-warning">{{ $nilai_list->where('is_published', false)->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>Published</span>
                                <span class="badge bg-success">{{ $nilai_list->where('is_published', true)->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>Locked</span>
                                <span class="badge bg-dark">{{ $nilai_list->where('is_locked', true)->count() }}</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <h6 class="text-muted">Aksi Cepat</h6>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-warning btn-sm" onclick="bulkUpdate()">
                                    <i class="fas fa-edit me-2"></i>Update Terpilih
                                </button>
                                <a href="{{ route($spref . 'akademik.nilai-import') }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-upload me-2"></i>Import Nilai
                                </a>
                                <a href="{{ route($spref . 'akademik.nilai-export') }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-download me-2"></i>Export Nilai
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-js')
    <script>
        // Validasi bobot pada form Tambah Nilai
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('#collapseForm form');
            const inputs = document.querySelectorAll('.bobot-tambah');
            const totalEl = document.getElementById('total-bobot-tambah');
            const warningEl = document.getElementById('warning-bobot-tambah');

            function updateTotalBobot() {
                let total = 0;
                inputs.forEach(input => total += parseFloat(input.value || 0));
                totalEl.textContent = total.toFixed(2).replace(/\\.00$/, '') + '%';

                const valid = Math.abs(total - 100) < 0.01;
                totalEl.classList.toggle('bg-success', valid);
                totalEl.classList.toggle('bg-danger', !valid);
                warningEl.classList.toggle('d-none', valid);

                if (valid) {
                    warningEl.innerHTML = '<i class="fas fa-check-circle me-1"></i> Total bobot sudah tepat <strong>100%</strong>.';
                    warningEl.classList.remove('alert-warning');
                    warningEl.classList.add('alert-success');
                } else {
                    warningEl.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Bobot saat ini <strong>' + total.toFixed(2).replace(/\\.00$/, '') + '%</strong>. Sesuaikan bobot agar total tepat 100% sebelum menyimpan.';
                    warningEl.classList.remove('alert-success');
                    warningEl.classList.add('alert-warning');
                }
            }

            inputs.forEach(input => input.addEventListener('input', updateTotalBobot));
            if (form) {
                form.addEventListener('submit', function (event) {
                    let total = 0;
                    inputs.forEach(input => total += parseFloat(input.value || 0));
                    if (Math.abs(total - 100) > 0.01) {
                        event.preventDefault();
                        updateTotalBobot();
                        alert('Total bobot harus tepat 100%. Saat ini: ' + total.toFixed(2) + '%.');
                    }
                });
            }

            updateTotalBobot();
        });

        // Dosen pengampu mengikuti dosen1_id/dosen2_id/dosen3_id pada mata kuliah.
        document.addEventListener('DOMContentLoaded', function () {
            const mkSelect = document.getElementById('mata_kuliah_id');
            const dosenSelect = document.getElementById('dosen_id');
            if (!mkSelect || !dosenSelect) return;

            @php
                $dosensForJs = $dosens->keyBy('id')->map(function ($d) {
                    return [
                        'id' => $d->id,
                        'nidn' => $d->nidn,
                        'name' => $d->name,
                    ];
                });
            @endphp
            const dosens = @json($dosensForJs);

            function refreshDosenPengampu() {
                const selected = mkSelect.options[mkSelect.selectedIndex];
                const ids = selected ? [
                    selected.dataset.dosen1,
                    selected.dataset.dosen2,
                    selected.dataset.dosen3
                ].filter(id => id && id !== '0') : [];

                dosenSelect.innerHTML = '';
                if (!ids.length) {
                    dosenSelect.innerHTML = '<option value="">Belum ada dosen pengampu</option>';
                    dosenSelect.disabled = true;
                    return;
                }

                dosenSelect.disabled = false;
                dosenSelect.innerHTML = '';

                [...new Set(ids)].forEach((id, index) => {
                    const d = dosens[id];
                    if (!d) return;
                    const option = document.createElement('option');
                    option.value = d.id;
                    option.textContent = (d.nidn ? d.nidn + ' - ' : '') + d.name;
                    if (index === 0) {
                        option.selected = true;
                    }
                    dosenSelect.appendChild(option);
                });
            }

            mkSelect.addEventListener('change', refreshDosenPengampu);
            refreshDosenPengampu();
        });

        // Grade mapping
        const gradeMapping = {
            'A': { point: 4.0, min: 85 },
            'A-': { point: 3.7, min: 80 },
            'B+': { point: 3.3, min: 75 },
            'B': { point: 3.0, min: 70 },
            'B-': { point: 2.7, min: 65 },
            'C+': { point: 2.3, min: 60 },
            'C': { point: 2.0, min: 55 },
            'D': { point: 1.0, min: 40 },
            'E': { point: 0.0, min: 0 }
        };

        function calculateGrade() {
            const nilaiAngka = parseFloat(document.getElementById('nilai_angka').value);
            const nilaiHurufSelect = document.getElementById('nilai_huruf');
            const gradePointInput = document.getElementById('grade_point');
            const statusSelect = document.getElementById('status_lulus');

            if (nilaiAngka >= 0) {
                let grade = 'E';
                for (const [g, data] of Object.entries(gradeMapping)) {
                    if (nilaiAngka >= data.min) {
                        grade = g;
                        break;
                    }
                }

                nilaiHurufSelect.value = grade;
                gradePointInput.value = gradeMapping[grade].point;
                statusSelect.value = gradeMapping[grade].point >= 2.0 ? 'lulus' : 'tidak_lulus';
            }
        }

        function calculatePoint() {
            const nilaiHuruf = document.getElementById('nilai_huruf').value;
            const gradePointInput = document.getElementById('grade_point');
            const statusSelect = document.getElementById('status_lulus');

            if (nilaiHuruf && gradeMapping[nilaiHuruf]) {
                gradePointInput.value = gradeMapping[nilaiHuruf].point;
                statusSelect.value = gradeMapping[nilaiHuruf].point >= 2.0 ? 'lulus' : 'tidak_lulus';
            }
        }

        function filterTable() {
            const tahunAkademik = document.getElementById('filterTahunAkademik').value.toLowerCase();
            const mataKuliah = document.getElementById('filterMataKuliah').value.toLowerCase();
            const grade = document.getElementById('filterGrade').value.toLowerCase();
            const search = document.getElementById('searchInput').value.toLowerCase();
            const table = document.getElementById('nilaiTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const mahasiswaText = row.cells[1].textContent.toLowerCase();
                const mataKuliahText = row.cells[2].textContent.toLowerCase();
                const tahunAkademikText = row.cells[3].textContent.toLowerCase();
                const gradeText = row.cells[5].textContent.toLowerCase();

                let showRow = true;

                if (tahunAkademik && !tahunAkademikText.includes(tahunAkademik)) {
                    showRow = false;
                }
                if (mataKuliah && !mataKuliahText.includes(mataKuliah)) {
                    showRow = false;
                }
                if (grade && !gradeText.includes(grade)) {
                    showRow = false;
                }
                if (search && !mahasiswaText.includes(search)) {
                    showRow = false;
                }

                row.style.display = showRow ? '' : 'none';
            }
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.nilai-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }

        function updateNilai(code, field, value) {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PATCH');
            formData.append(field, value);

            fetch(`{{ route($spref . 'akademik.nilai-update', ':code') }}`.replace(':code', code), {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal mengupdate nilai: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengupdate nilai');
            });
        }

        function bulkUpdate() {
            const checkboxes = document.querySelectorAll('.nilai-checkbox:checked');
            const codes = Array.from(checkboxes).map(cb => cb.value);

            if (codes.length === 0) {
                alert('Pilih minimal satu nilai untuk diupdate!');
                return;
            }

            if (confirm(`Apakah Anda yakin ingin mengupdate ${codes.length} nilai yang dipilih?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route($spref . "akademik.nilai-bulk-update") }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                codes.forEach(code => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'codes[]';
                    input.value = code;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            }
        }

        function approveNilai(code) {
            if (confirm('Apakah Anda yakin ingin menyetujui nilai ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route($spref . 'akademik.nilai-approve', ':code') }}`.replace(':code', code);

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function publishNilai(code) {
            if (confirm('Apakah Anda yakin ingin mempublish nilai ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route($spref . 'akademik.nilai-publish', ':code') }}`.replace(':code', code);

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function lockNilai(code) {
            if (confirm('Apakah Anda yakin ingin mengunci nilai ini? Nilai yang dikunci tidak dapat diubah lagi.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route($spref . 'akademik.nilai-lock', ':code') }}`.replace(':code', code);

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteNilai(code) {
            if (confirm('Apakah Anda yakin ingin menghapus nilai ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route($spref . 'akademik.nilai-delete', ':code') }}`.replace(':code', code);

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function editNilai(code) {
            // Buka halaman detail/edit nilai. Jangan hanya menulis ke console,
            // karena tombol Edit sebelumnya tidak melakukan navigasi apa pun.
            const url = `{{ route($spref . 'akademik.nilai-view', ':code') }}`.replace(':code', encodeURIComponent(code));
            window.location.href = url;
        }
    </script>
@endsection