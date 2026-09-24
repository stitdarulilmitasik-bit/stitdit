@extends('core-themes.core-backpage')

@section('custom-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
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
        }

        .table td {
            vertical-align: middle;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid rgba(0,0,0,0.1);
            padding: 0.5rem 1rem;
        }

        .info-item {
            margin-bottom: 1rem;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1.1rem;
            color: #495057;
        }

                /* Responsive table: keep dense admin tables horizontally scrollable on mobile */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .table-responsive > .table {
            width: max-content;
            min-width: 100%;
            margin-bottom: 0;
        }

        .table-responsive .table th,
        .table-responsive .table td {
            white-space: nowrap;
        }

        .table-responsive .table .btn-group {
            display: inline-flex;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .table-responsive .table .btn {
            flex: 0 0 auto;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }

            .table-responsive {
                margin-left: -0.25rem;
                margin-right: -0.25rem;
                width: calc(100% + 0.5rem);
            }

            .table-responsive > .table {
                min-width: 760px;
            }

            .table-responsive .table th,
            .table-responsive .table td {
                padding: 0.55rem 0.65rem;
                font-size: 0.875rem;
                vertical-align: middle;
            }

            .table-responsive .table th {
                white-space: nowrap;
            }

            .table-responsive .table td[data-label]::before {
                content: none !important;
            }

            .table-responsive .table .btn-group .btn {
                padding: 0.3rem 0.5rem;
            }
        }
    </style>
@endsection

@section('content')
    @php
        // KRS lama dapat menyimpan mahasiswa/tahun akademik yang sudah tidak tersedia.
        // Jangan biarkan relasi null menyebabkan 500 pada halaman detail.
        $mahasiswa = $krs->mahasiswa;
        $tahunAkademik = $krs->tahunAkademik;
    @endphp

    @if (!$mahasiswa || !$tahunAkademik)
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning d-flex align-items-start gap-3">
                    <i class="fas fa-triangle-exclamation mt-1"></i>
                    <div>
                        <strong>Data KRS tidak lengkap.</strong>
                        <div class="mt-1">
                            {{ !$mahasiswa ? 'Data mahasiswa yang terkait dengan KRS ini tidak ditemukan.' : '' }}
                            {{ (!$mahasiswa && !$tahunAkademik) ? ' ' : '' }}
                            {{ !$tahunAkademik ? 'Data tahun akademik yang terkait dengan KRS ini tidak ditemukan.' : '' }}
                        </div>
                        <small class="text-muted">Kode KRS: {{ $krs->code }}</small>
                    </div>
                </div>
                <a href="{{ route($spref . 'akademik.krs-render') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke daftar KRS
                </a>
            </div>
        </div>
    @else
    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Detail KRS - {{ $mahasiswa->name ?? $mahasiswa->numb_nim ?? '-' }}</h4>
                    <p class="text-muted mb-0">{{ $mahasiswa->nim ?? $mahasiswa->numb_nim ?? '-' }} | {{ $tahunAkademik->name ?? '-' }} - {{ $tahunAkademik->semester ?? '-' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route($spref . 'akademik.krs-render') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    @if (in_array($krs->status, ['approved', 'published', 'locked']))
                        <a href="{{ route($spref . 'akademik.krs-print', $krs->code) }}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-print me-2"></i>Cetak KRS
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-file-pdf me-2 text-danger"></i>Preview PDF KRS</h6>
                    <a href="{{ route($spref . 'akademik.krs-print', $krs->code) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="fas fa-external-link-alt me-1"></i>Buka PDF
                    </a>
                </div>
                <div class="card-body p-2">
                    <iframe
                        src="{{ route($spref . 'akademik.krs-preview', $krs->code) }}"
                        title="Preview PDF KRS {{ $krs->code }}"
                        style="width:100%;height:820px;border:1px solid #dee2e6;border-radius:8px;background:#f8f9fa;"
                        loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- KRS Information -->
        <div class="col-lg-4 col-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Informasi KRS</h6>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Mahasiswa</div>
                        <div class="info-value">{{ $mahasiswa->name ?? $mahasiswa->numb_nim ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">NIM</div>
                        <div class="info-value">{{ $mahasiswa->nim ?? $mahasiswa->numb_nim ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Program Studi</div>
                        <div class="info-value">{{ $mahasiswa->programStudi?->name ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tahun Akademik</div>
                        <div class="info-value">{{ $tahunAkademik->name ?? '-' }} - {{ $tahunAkademik->semester ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Semester</div>
                        <div class="info-value">{{ $krs->semester }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Dosen Wali</div>
                        <div class="info-value">{{ $krs->dosenWali->name ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total SKS</div>
                        <div class="info-value">
                            <span class="badge bg-info fs-6">{{ $krs->total_sks }} SKS</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'submitted' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'published' => 'primary',
                                    'locked' => 'dark'
                                ];
                                $statusLabels = [
                                    'draft' => 'Dibuat',
                                    'submitted' => 'Diajukan',
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    'published' => 'Dipublish',
                                    'locked' => 'Dikunci'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$krs->status] ?? 'secondary' }} fs-6">
                                {{ $statusLabels[$krs->status] ?? ucfirst($krs->status) }}
                            </span>
                        </div>
                    </div>
                    @if ($krs->catatan)
                        <div class="info-item">
                            <div class="info-label">Catatan</div>
                            <div class="info-value">{{ $krs->catatan }}</div>
                        </div>
                    @endif
                    @if ($krs->rejection_reason)
                        <div class="info-item">
                            <div class="info-label">Alasan Penolakan</div>
                            <div class="info-value text-danger">{{ $krs->rejection_reason }}</div>
                        </div>
                    @endif
                    <div class="info-item">
                        <div class="info-label">Dibuat</div>
                        <div class="info-value">{{ $krs->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @if ($krs->approved_at)
                        <div class="info-item">
                            <div class="info-label">Disetujui</div>
                            <div class="info-value">{{ $krs->approved_at->format('d/m/Y H:i') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Card -->
            @if (in_array($krs->status, ['submitted', 'approved']))
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Aksi</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if ($krs->status == 'submitted')
                                <button class="btn btn-success" onclick="approveKRS()">
                                    <i class="fas fa-check me-2"></i>Setujui KRS
                                </button>
                                <button class="btn btn-danger" onclick="rejectKRS()">
                                    <i class="fas fa-times me-2"></i>Tolak KRS
                                </button>
                            @elseif ($krs->status == 'approved')
                                <button class="btn btn-dark" onclick="lockKRS()">
                                    <i class="fas fa-lock me-2"></i>Kunci KRS
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- KRS Details -->
        <div class="col-lg-8 col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Detail Mata Kuliah</h6>
                    @if (in_array($krs->status, ['draft', 'submitted']))
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMataKuliahModal">
                            <i class="fas fa-plus me-2"></i>Tambah Mata Kuliah
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kode MK</th>
                                    <th>Mata Kuliah</th>
                                    <th class="text-center">SKS</th>
                                    <th>Kelas</th>
                                    <th>Ruang</th>
                                    <th>Dosen</th>
                                    @if (in_array($krs->status, ['draft', 'submitted']))
                                        <th class="text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($krs->details as $detail)
                                    <tr>
                                        <td data-label="Kode MK">{{ $detail->mataKuliah->code }}</td>
                                        <td data-label="Mata Kuliah">
                                            <div class="d-flex flex-column">
                                                <strong>{{ $detail->mataKuliah->name }}</strong>
                                                <small class="text-muted">Semester {{ $detail->mataKuliah->semester }}</small>
                                            </div>
                                        </td>
                                        <td class="text-center" data-label="SKS">
                                            <span class="badge bg-info">{{ $detail->mataKuliah->sks }}</span>
                                        </td>
                                        <td data-label="Kelas">{{ $detail->kelas->name ?? '-' }}</td>
                                        <td data-label="Ruang">
                                            @php
                                                $jadwal = $detail->kelas?->jadwalKuliah?->first(function ($item) use ($detail) {
                                                    return (int) ($item->matkul_id ?? 0) === (int) ($detail->matkul_id ?? 0);
                                                }) ?: $detail->kelas?->jadwalKuliah?->first();
                                            @endphp
                                            {{ $jadwal?->ruang?->name ?? $jadwal?->ruang ?? '-' }}
                                        </td>
                                        <td data-label="Dosen">
                                            <div class="d-flex flex-column">
                                                @if ($detail->mataKuliah->dosen1)
                                                    <span>{{ $detail->mataKuliah->dosen1->name }}</span>
                                                @endif
                                                @if ($detail->mataKuliah->dosen2)
                                                    <small class="text-muted">{{ $detail->mataKuliah->dosen2->name }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        @if (in_array($krs->status, ['draft', 'submitted']))
                                            <td class="text-center" data-label="Aksi">
                                                <button type="button" class="btn btn-sm btn-warning me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editMataKuliahModal{{ $detail->id }}"
                                                    title="Edit Mata Kuliah">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="removeDetail('{{ $detail->id }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ in_array($krs->status, ['draft', 'submitted']) ? '7' : '6' }}" class="text-center text-muted">
                                            Belum ada mata kuliah yang dipilih
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($krs->details->count() > 0)
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="2"><strong>Total SKS</strong></td>
                                        <td class="text-center"><strong>{{ $krs->total_sks }}</strong></td>
                                        <td colspan="{{ in_array($krs->status, ['draft', 'submitted']) ? '4' : '3' }}"></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Edit Mata Kuliah Modals -->
    @if (in_array($krs->status, ['draft', 'submitted']))
        @foreach ($krs->details as $detail)
            <div class="modal fade" id="editMataKuliahModal{{ $detail->id }}" tabindex="-1" aria-labelledby="editMataKuliahLabel{{ $detail->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editMataKuliahLabel{{ $detail->id }}">
                                <i class="fas fa-pen me-2"></i>Edit Mata Kuliah dalam KRS
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <form action="{{ route($spref . 'akademik.krs-update-matakuliah', [$krs->code, $detail->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="modal-body">
                                <div class="alert alert-info py-2">
                                    Perubahan hanya dapat dilakukan selama KRS berstatus Draft atau Diajukan.
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Mata Kuliah</label>
                                    <select class="form-select" name="mata_kuliah_id" required>
                                        @foreach ($available_matakuliah as $mk)
                                            <option value="{{ $mk->id }}" @selected((int) $detail->matkul_id === (int) $mk->id)>
                                                {{ $mk->code }} - {{ $mk->name }} ({{ $mk->sks }} SKS)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kelas</label>
                                        <select class="form-select" name="kelas_id">
                                            <option value="">-- Pilih Kelas --</option>
                                            @foreach ($kelas as $kls)
                                                <option value="{{ $kls->id }}" @selected((int) $detail->kelas_id === (int) $kls->id)>
                                                    {{ $kls->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Dosen</label>
                                        <select class="form-select" name="dosen_id">
                                            <option value="">-- Pilih Dosen --</option>
                                            @foreach ($dosens as $dosen)
                                                <option value="{{ $dosen->id }}" @selected((int) $detail->dosen_id === (int) $dosen->id)>
                                                    {{ $dosen->name }}{{ $dosen->nidn ? ' - NIDN '.$dosen->nidn : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Catatan</label>
                                    <textarea class="form-control" name="notes" rows="3">{{ $detail->notes ?? '' }}</textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <!-- Add Mata Kuliah Modal -->
    @if (in_array($krs->status, ['draft', 'submitted']))
        <div class="modal fade" id="addMataKuliahModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Mata Kuliah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route($spref . 'akademik.krs-add-matakuliah', $krs->code) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info py-2">
                                Pilih Mata Kuliah, kemudian Kelas. Jadwal dan Dosen akan menyesuaikan otomatis.
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="mata_kuliah_id" class="form-label">Mata Kuliah</label>
                                    <select class="form-select" name="mata_kuliah_id" id="mata_kuliah_id" required onchange="loadKelas()">
                                        <option value="">Pilih Mata Kuliah</option>
                                        @foreach ($available_matakuliah as $mk)
                                            <option value="{{ $mk->id }}" data-sks="{{ $mk->sks }}">
                                                {{ $mk->code }} - {{ $mk->name }} ({{ $mk->sks }} SKS)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kelas_id" class="form-label">Kelas</label>
                                    <select class="form-select" name="kelas_id" id="kelas_id" required disabled onchange="loadJadwal()">
                                        <option value="">Pilih Mata Kuliah terlebih dahulu</option>
                                    </select>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="jadwal_kuliah_id" class="form-label">Jadwal Kuliah</label>
                                    <select class="form-select" name="jadwal_kuliah_id" id="jadwal_kuliah_id" required disabled onchange="loadDosen()">
                                        <option value="">Pilih Kelas terlebih dahulu</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Dosen</label>
                                    <input type="text" class="form-control" id="dosen_display" readonly placeholder="Otomatis dari jadwal">
                                    <input type="hidden" name="dosen_id" id="dosen_id">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="btnTambahMatakuliah" disabled>
                                <i class="fas fa-plus me-1"></i>Tambah Mata Kuliah
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    @endif
@endsection

@section('custom-js')
    <script>

        function approveKRS() {
            if (confirm('Apakah Anda yakin ingin menyetujui KRS ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route($spref . "akademik.krs-approve", $krs->code) }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function rejectKRS() {
            const reason = prompt('Masukkan alasan penolakan:');
            if (reason) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route($spref . "akademik.krs-reject", $krs->code) }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'reason';
                reasonInput.value = reason;
                form.appendChild(reasonInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function publishKRS() {
            if (confirm('Apakah Anda yakin ingin mempublish KRS ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route($spref . "akademik.krs-publish", $krs->code) }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function lockKRS() {
            if (confirm('Apakah Anda yakin ingin mengunci KRS ini? KRS yang dikunci tidak dapat diubah lagi.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route($spref . "akademik.krs-lock", $krs->code) }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function removeDetail(detailId) {
            if (confirm('Apakah Anda yakin ingin menghapus mata kuliah ini dari KRS?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route($spref . "akademik.krs-handle") }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'remove_detail';
                form.appendChild(actionInput);

                const detailIdInput = document.createElement('input');
                detailIdInput.type = 'hidden';
                detailIdInput.name = 'detail_id';
                detailIdInput.value = detailId;
                form.appendChild(detailIdInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        @php
            $jadwalKrsData = $jadwal_kuliah->map(function ($jadwal) {
                return [
                    'id' => $jadwal->id,
                    'matkul_id' => $jadwal->matkul_id,
                    'dosen_id' => $jadwal->dosen_id,
                    'dosen_name' => $jadwal->dosen?->name ?? '-',
                    'hari' => $jadwal->hari,
                    'waktu' => $jadwal->waktuKuliah?->name ?? ($jadwal->waktuKuliah?->start_time ?? ''),
                    'ruang' => $jadwal->ruang?->name ?? '-',
                    'kelas_ids' => $jadwal->kelas->pluck('id')->values(),
                    'kelas_names' => $jadwal->kelas->pluck('name')->values(),
                ];
            })->values()->all();
        @endphp
        const jadwalKrs = @json($jadwalKrsData);

        function loadKelas() {
            const mataKuliahId = document.getElementById('mata_kuliah_id').value;
            const kelasSelect = document.getElementById('kelas_id');
            const jadwalSelect = document.getElementById('jadwal_kuliah_id');
            const dosenDisplay = document.getElementById('dosen_display');
            const dosenInput = document.getElementById('dosen_id');
            const button = document.getElementById('btnTambahMatakuliah');

            kelasSelect.innerHTML = '<option value="">Pilih Kelas</option>';
            jadwalSelect.innerHTML = '<option value="">Pilih Kelas terlebih dahulu</option>';
            dosenDisplay.value = '';
            dosenInput.value = '';
            button.disabled = true;

            if (!mataKuliahId) {
                kelasSelect.disabled = true;
                jadwalSelect.disabled = true;
                return;
            }

            const schedules = jadwalKrs.filter(j => String(j.matkul_id) === String(mataKuliahId));
            const classes = new Map();

            schedules.forEach(j => {
                (j.kelas_ids || []).forEach((id, index) => {
                    if (!classes.has(String(id))) {
                        classes.set(String(id), j.kelas_names[index] || ('Kelas #' + id));
                    }
                });
            });

            if (!classes.size) {
                kelasSelect.innerHTML = '<option value="">Belum ada kelas/jadwal untuk mata kuliah ini</option>';
                kelasSelect.disabled = true;
                return;
            }

            classes.forEach((name, id) => {
                kelasSelect.insertAdjacentHTML('beforeend', '<option value="' + id + '">' + escapeHtml(name) + '</option>');
            });
            kelasSelect.disabled = false;
        }

        function loadJadwal() {
            const mataKuliahId = document.getElementById('mata_kuliah_id').value;
            const kelasId = document.getElementById('kelas_id').value;
            const jadwalSelect = document.getElementById('jadwal_kuliah_id');
            const dosenDisplay = document.getElementById('dosen_display');
            const dosenInput = document.getElementById('dosen_id');
            const button = document.getElementById('btnTambahMatakuliah');

            jadwalSelect.innerHTML = '<option value="">Pilih Jadwal</option>';
            dosenDisplay.value = '';
            dosenInput.value = '';
            button.disabled = true;

            if (!mataKuliahId || !kelasId) {
                jadwalSelect.disabled = true;
                return;
            }

            const schedules = jadwalKrs.filter(j =>
                String(j.matkul_id) === String(mataKuliahId) &&
                (j.kelas_ids || []).map(String).includes(String(kelasId))
            );

            schedules.forEach(j => {
                const label = [j.hari, j.waktu, 'Ruang: ' + j.ruang].filter(Boolean).join(' • ');
                jadwalSelect.insertAdjacentHTML('beforeend',
                    '<option value="' + j.id + '">' + escapeHtml(label || ('Jadwal #' + j.id)) + '</option>'
                );
            });

            jadwalSelect.disabled = schedules.length === 0;
            if (!schedules.length) {
                jadwalSelect.innerHTML = '<option value="">Belum ada jadwal untuk kelas ini</option>';
            }
        }

        function loadDosen() {
            const scheduleId = document.getElementById('jadwal_kuliah_id').value;
            const jadwal = jadwalKrs.find(j => String(j.id) === String(scheduleId));
            const dosenDisplay = document.getElementById('dosen_display');
            const dosenInput = document.getElementById('dosen_id');
            const button = document.getElementById('btnTambahMatakuliah');

            dosenDisplay.value = jadwal?.dosen_name || '-';
            dosenInput.value = jadwal?.dosen_id || '';
            button.disabled = !jadwal || !jadwal.dosen_id;
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (char) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char];
            });
        }    </script>
@endsection
