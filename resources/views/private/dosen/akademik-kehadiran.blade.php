@extends('core-themes.core-backpage')

@section('content')
<div class="container-xl py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="mb-1">Input Kehadiran Mahasiswa</h2>
            <p class="text-muted mb-0">Catat kehadiran mahasiswa berdasarkan semester dan pertemuan.</p>
        </div>
        <a href="{{ route('dosen.akademik.nilai-render') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Nilai Mahasiswa
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Semester</label>
                    <select id="filter-semester" class="form-select">
                        @for($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" {{ (int)$semester === $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pertemuan</label>
                    <select id="filter-pertemuan" class="form-select">
                        @for($i = 1; $i <= 16; $i++)
                            <option value="{{ $i }}" {{ (int)$pertemuan === $i ? 'selected' : '' }}>Pertemuan {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="alert alert-info mb-0 w-100 py-2">
                        Pilih semester dan pertemuan untuk menampilkan status kehadiran.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Semester</th>
                        <th>Pertemuan</th>
                        <th>Status Kehadiran</th>
                        <th>Simpan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($nilai as $index => $n)
                    @php
                        $nim = $n->mahasiswa->numb_nim ?? $n->mahasiswa->nim ?? $n->mahasiswa->code ?? '-';
                        $existing = $n->kehadiranMahasiswa->keyBy('pertemuan');
                    @endphp
                    <tr class="attendance-row" data-semester="{{ $n->semester }}" data-nilai-id="{{ $n->id }}">
                        <td>{{ $nilai->firstItem() + $index }}</td>
                        <td class="fw-semibold">{{ $nim }}</td>
                        <td>{{ $n->mahasiswa->name ?? '-' }}</td>
                        <td>
                            <select name="semester" form="attendance-form-{{ $n->id }}" class="form-select form-select-sm semester-select" style="min-width:130px">
                                @for($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}" {{ (int)$n->semester === $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                                @endfor
                            </select>
                        </td>
                        <td>
                            <select name="pertemuan" form="attendance-form-{{ $n->id }}" class="form-select form-select-sm pertemuan-select" style="min-width:145px">
                                @for($i = 1; $i <= 16; $i++)
                                    <option value="{{ $i }}" {{ $i === (int)$pertemuan ? 'selected' : '' }}>Pertemuan {{ $i }}</option>
                                @endfor
                            </select>
                        </td>
                        <td>
                            <select name="status" form="attendance-form-{{ $n->id }}" class="form-select form-select-sm status-select" style="min-width:130px">
                                @foreach(['Hadir','Izin','Sakit','Alpa'] as $status)
                                    <option value="{{ $status }}" {{ (($existing[(int)$pertemuan]->status ?? 'Hadir') === $status) ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <form id="attendance-form-{{ $n->id }}" method="POST" action="{{ route('dosen.akademik.kehadiran.store') }}">
                                @csrf
                                <input type="hidden" name="nilai_id" value="{{ $n->id }}">
                                <input type="hidden" name="redirect_semester" value="{{ $semester }}">
                                <input type="hidden" name="redirect_pertemuan" value="{{ $pertemuan }}">
                                <button class="btn btn-sm btn-primary">
                                    <i class="fas fa-save me-1"></i>Simpan
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data mahasiswa dari KRS/nilai yang diampu.</td></tr>
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
