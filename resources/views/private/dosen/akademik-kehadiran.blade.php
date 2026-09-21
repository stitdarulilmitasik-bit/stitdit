@extends('core-themes.core-backpage')

@section('content')
<div class="container-xl py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="mb-1">Input Kehadiran Mahasiswa</h2>
            <p class="text-muted mb-0">Catat kehadiran per mata kuliah dan pertemuan. Persentase hadir dihitung otomatis dan menjadi komponen 15% nilai akhir.</p>
        </div>
        <a href="{{ route('dosen.akademik.nilai-render') }}" class="btn btn-outline-primary">
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
                <h3 class="card-title mb-1">Daftar Kehadiran</h3>
                <div class="text-muted small">Pilih status pada pertemuan yang aktif, kemudian tekan Simpan.</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Mata Kuliah</th>
                        <th>Semester</th>
                        <th>Pertemuan</th>
                        <th>Status Kehadiran</th>
                        <th>Rekap</th>
                        <th>Simpan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($nilai as $index => $n)
                    @php
                        $nim = $n->mahasiswa->numb_nim ?? $n->mahasiswa->nim ?? $n->mahasiswa->code ?? '-';
                        $existing = $n->kehadiranMahasiswa->keyBy('pertemuan');
                        $currentAttendance = $existing[(int)$pertemuan] ?? null;
                        $totalPertemuan = $existing->count();
                        $jumlahHadir = $existing->where('status', 'Hadir')->count();
                        $persentase = $totalPertemuan > 0 ? round(($jumlahHadir / $totalPertemuan) * 100, 2) : 0;
                    @endphp
                    <tr>
                        <td>{{ $nilai->firstItem() + $index }}</td>
                        <td class="fw-semibold text-nowrap">{{ $nim }}</td>
                        <td>{{ $n->mahasiswa->name ?? '-' }}</td>
                        <td>
                            <div class="fw-semibold">{{ $n->mataKuliah->name ?? '-' }}</div>
                            @if($n->mataKuliah->code ?? null)
                                <small class="text-muted">{{ $n->mataKuliah->code }}</small>
                            @endif
                        </td>
                        <td class="text-nowrap">{{ $n->semester }}</td>
                        <td>
                            <select name="pertemuan" form="attendance-form-{{ $n->id }}" class="form-select form-select-sm" style="min-width:135px">
                                @for($i = 1; $i <= 16; $i++)
                                    <option value="{{ $i }}" {{ $i === (int)$pertemuan ? 'selected' : '' }}>Pertemuan {{ $i }}</option>
                                @endfor
                            </select>
                        </td>
                        <td>
                            <select name="status" form="attendance-form-{{ $n->id }}" class="form-select form-select-sm" style="min-width:115px" required>
                                <option value="" {{ $currentAttendance ? '' : 'selected' }} disabled>Pilih status</option>
                                @foreach(['Hadir','Izin','Sakit','Alpa'] as $status)
                                    <option value="{{ $status }}" {{ (($currentAttendance->status ?? '') === $status) ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-nowrap">
                            <div class="fw-semibold">{{ number_format($persentase, 2) }}%</div>
                            <small class="text-muted">{{ $jumlahHadir }}/{{ $totalPertemuan }} hadir</small>
                        </td>
                        <td class="text-nowrap">
                            <form id="attendance-form-{{ $n->id }}" method="POST" action="{{ route('dosen.akademik.kehadiran.store') }}">
                                @csrf
                                <input type="hidden" name="nilai_id" value="{{ $n->id }}">
                                <input type="hidden" name="semester" value="{{ $n->semester }}">
                                <input type="hidden" name="redirect_semester" value="{{ $semester }}">
                                <input type="hidden" name="redirect_pertemuan" value="{{ $pertemuan }}">
                                <button class="btn btn-sm btn-primary">
                                    <i class="fas fa-save me-1"></i>Simpan
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">Belum ada data mahasiswa pada mata kuliah yang Anda ampu untuk semester ini.</td></tr>
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
