@extends('core-themes.core-backpage')

@section('content')
<div class="container-xl py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="mb-1">Input Kehadiran Mahasiswa</h2>
            <p class="text-muted mb-0">Catat kehadiran per mata kuliah dan pertemuan. Persentase hadir dihitung otomatis dan menjadi komponen 15% nilai akhir.</p>
        </div>
        <a href="{{ route($spref . 'akademik.nilai-render') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Nilai Mahasiswa
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Semester</label>
                    <select id="filter-semester" class="form-select">
                        @for($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" {{ (int)$semester === $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Pertemuan</label>
                    <select id="filter-pertemuan" class="form-select">
                        @for($i = 1; $i <= 16; $i++)
                            <option value="{{ $i }}" {{ (int)$pertemuan === $i ? 'selected' : '' }}>Pertemuan {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info mb-0 py-2">
                        <strong>Bobot Kehadiran: 15%</strong><br>
                        Hadir dihitung sebagai hadir. Izin, Sakit, dan Alpa tidak menambah persentase hadir.
                        Persentase akan langsung tersimpan ke komponen <strong>Kehadiran</strong> pada Nilai Mahasiswa.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Rekap Kehadiran Mahasiswa</h3>
                <div class="text-muted small">Setiap kolom menunjukkan pertemuan 1–16. Tanda <strong>✓</strong> berarti Hadir. Untuk mengubah status, pilih Pertemuan Aktif lalu gunakan kolom Status dan Simpan.</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table align-middle text-nowrap">
                <thead>
                    <tr>
                        <th style="width:55px">No.</th>
                        <th style="width:125px">NIM</th>
                        <th style="min-width:210px">Nama Mahasiswa</th>
                        <th colspan="16" class="text-center bg-light">Pertemuan</th>
                        <th style="width:100px">Rekap</th>
                        <th style="min-width:190px">Status Pertemuan {{ $pertemuan }}</th>
                        <th style="width:95px">Aksi</th>
                    </tr>
                    <tr>
                        <th colspan="3"></th>
                        @for($i = 1; $i <= 16; $i++)
                            <th class="text-center" style="min-width:52px">P{{ $i }}</th>
                        @endfor
                        <th colspan="3"></th>
                    </tr>
                </thead>
                <tbody>
                @php
                    // Kelompokkan berdasarkan ID mata kuliah dari relasi MataKuliah.
                    // Ini mencegah parameter route menjadi null apabila ada record Nilai
                    // lama yang mata_kuliah_id-nya tidak terisi tetapi relasinya masih tersedia.
                    $groupedMataKuliah = $nilai->getCollection()
                        ->filter(fn($item) => $item->mataKuliah && $item->mataKuliah->id)
                        ->sortBy([
                            [fn($item) => mb_strtolower($item->mataKuliah->name ?? ''), 'asc'],
                            [fn($item) => mb_strtolower($item->mahasiswa->name ?? ''), 'asc'],
                        ])
                        ->groupBy(fn($item) => $item->mataKuliah->id);
                    $nomor = $nilai->firstItem();
                @endphp

                @forelse($groupedMataKuliah as $mataKuliahId => $rows)
                    @php
                        $mk = $rows->first()->mataKuliah;
                    @endphp
                    <tr class="table-light">
                        <td colspan="22" class="fw-bold">
                            <i class="fas fa-book me-1"></i>
                            {{ $mk->name ?? '-' }}
                            @if($mk->code) <span class="text-muted fw-normal">({{ $mk->code }})</span> @endif
                            <span class="text-muted fw-normal ms-2">{{ $rows->count() }} mahasiswa</span>
                            <a href="{{ route('dosen.akademik.kehadiran.mata-kuliah.pdf', ['mataKuliahId' => $mataKuliahId, 'semester' => $semester]) }}"
                               class="btn btn-sm btn-outline-danger float-end"
                               target="_blank"
                               title="Export seluruh mahasiswa {{ $mk->name ?? '' }}">
                                <i class="fas fa-file-pdf me-1"></i> Export PDF Rekap
                            </a>
                        </td>
                    </tr>

                    @foreach($rows as $n)
                        @php
                            $existing = $n->kehadiranMahasiswa->keyBy('pertemuan');
                            $totalPertemuan = $existing->count();
                            $jumlahHadir = $existing->where('status', 'Hadir')->count();
                            $persentase = $totalPertemuan > 0 ? round(($jumlahHadir / $totalPertemuan) * 100, 2) : 0;
                            $currentAttendance = $existing[(int)$pertemuan] ?? null;
                        @endphp
                        <tr>
                            <td class="text-center fw-semibold text-muted">{{ $nomor++ }}</td>
                            <td class="fw-semibold">{{ $n->mahasiswa->numb_nim ?? $n->mahasiswa->nim ?? $n->mahasiswa->code ?? '-' }}</td>
                            <td class="fw-semibold">{{ $n->mahasiswa->name ?? '-' }}</td>

                            @for($i = 1; $i <= 16; $i++)
                                @php $attendance = $existing[$i] ?? null; @endphp
                                <td class="text-center attendance-cell">
                                    @if(($attendance->status ?? '') === 'Hadir')
                                        <span class="text-success fw-bold fs-4" title="Hadir">✓</span>
                                    @elseif(($attendance->status ?? '') === 'Izin')
                                        <span class="text-warning fw-semibold" title="Izin">I</span>
                                    @elseif(($attendance->status ?? '') === 'Sakit')
                                        <span class="text-info fw-semibold" title="Sakit">S</span>
                                    @elseif(($attendance->status ?? '') === 'Alpa')
                                        <span class="text-danger fw-semibold" title="Alpa">A</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endfor

                            <td>
                                <div class="fw-semibold">{{ number_format($persentase, 2) }}%</div>
                                <small class="text-muted">{{ $jumlahHadir }}/{{ $totalPertemuan }} hadir</small>
                            </td>

                            <td>
                                <select name="status" form="attendance-form-{{ $n->id }}" class="form-select form-select-sm" required>
                                    <option value="" {{ $currentAttendance ? '' : 'selected' }} disabled>Pilih status</option>
                                    @foreach(['Hadir','Izin','Sakit','Alpa'] as $status)
                                        <option value="{{ $status }}" {{ (($currentAttendance->status ?? '') === $status) ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="pertemuan" value="{{ $pertemuan }}" form="attendance-form-{{ $n->id }}">
                            </td>

                            <td>
                                <form id="attendance-form-{{ $n->id }}" method="POST" action="{{ route($spref . 'akademik.kehadiran.store') }}">
                                    @csrf
                                    <input type="hidden" name="nilai_id" value="{{ $n->id }}">
                                    <input type="hidden" name="semester" value="{{ $n->semester }}">
                                    <input type="hidden" name="redirect_semester" value="{{ $semester }}">
                                    <input type="hidden" name="redirect_pertemuan" value="{{ $pertemuan }}">
                                    <button class="btn btn-sm btn-primary" title="Simpan status pertemuan {{ $pertemuan }}">
                                        <i class="fas fa-save me-1"></i>Simpan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="22" class="text-center py-4 text-muted">Belum ada data mahasiswa pada semester ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $nilai->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const semester = document.getElementById('filter-semester');
    const pertemuan = document.getElementById('filter-pertemuan');

    function reloadWithFilters() {
        const url = new URL(window.location.href);
        url.searchParams.set('semester', semester.value);
        url.searchParams.set('pertemuan', pertemuan.value);
        window.location.href = url.toString();
    }

    semester?.addEventListener('change', reloadWithFilters);
    pertemuan?.addEventListener('change', reloadWithFilters);
});
</script>
@endsection
