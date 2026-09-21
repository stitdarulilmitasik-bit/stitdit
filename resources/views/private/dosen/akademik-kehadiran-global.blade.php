@extends('core-themes.core-backpage')

@section('content')
<style>
    .global-attendance-wrap { width:100%; overflow-x:auto; }
    .global-attendance { min-width:1450px; table-layout:fixed; }
    .global-attendance th,.global-attendance td { padding:5px 6px !important; vertical-align:middle; line-height:1.2; }
    .global-attendance .c-no{width:45px}.global-attendance .c-course{width:220px}.global-attendance .c-code{width:90px}
    .global-attendance .c-nim{width:120px}.global-attendance .c-name{width:210px}
    .global-attendance .c-meeting{width:40px}.global-attendance .c-summary{width:100px}
    .global-attendance .student-name{white-space:normal;overflow-wrap:anywhere}
</style>
<div class="container-xl py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="mb-1">Report Global Kehadiran</h2>
            <p class="text-muted mb-0">Rekap kehadiran seluruh mata kuliah pada semester yang dipilih.</p>
        </div>
        <div class="d-flex gap-2">
            @if($mahasiswaId)
                <a href="{{ route('web-admin.akademik.kehadiran.pdf', ['mahasiswaId' => $mahasiswaId, 'semester' => $semester]) }}"
                   class="btn btn-danger" target="_blank">
                    <i class="ti ti-file-type-pdf me-1"></i> Export PDF Mahasiswa
                </a>
            @endif
            @if($mataKuliahId)
                <a href="{{ route('web-admin.akademik.kehadiran.mata-kuliah.pdf', ['mataKuliahId' => $mataKuliahId, 'semester' => $semester]) }}"
                   class="btn btn-danger" target="_blank">
                    <i class="ti ti-file-type-pdf me-1"></i> Export PDF Mata Kuliah
                </a>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Semester</label>
                    <select id="filter-semester" class="form-select">
                        @for($i=1;$i<=8;$i++)
                            <option value="{{ $i }}" {{ (int)$semester === $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mahasiswa</label>
                    <select id="filter-mahasiswa" class="form-select">
                        <option value="">Semua Mahasiswa</option>
                        @foreach($mahasiswaOptions as $m)
                            <option value="{{ $m->id }}" {{ (string)$mahasiswaId === (string)$m->id ? 'selected' : '' }}>
                                {{ $m->numb_nim ?? '-' }} - {{ $m->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mata Kuliah</label>
                    <select id="filter-mata-kuliah" class="form-select">
                        <option value="">Semua Mata Kuliah</option>
                        @foreach($mataKuliahOptions as $mk)
                            <option value="{{ $mk->id }}" {{ (string)$mataKuliahId === (string)$mk->id ? 'selected' : '' }}>
                                {{ $mk->code ?? '-' }} - {{ $mk->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-info mb-0 py-2">
                        Report global seluruh mata kuliah.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">{{ $mataKuliahId ? 'Report Kehadiran Mata Kuliah' : 'Report Global Kehadiran' }}</h3>
        </div>
        <div class="global-attendance-wrap">
            <table class="table table-bordered table-vcenter global-attendance mb-0">
                <thead>
                    <tr>
                        <th class="c-no text-center">No.</th>
                        <th class="c-course">Mata Kuliah</th>
                        <th class="c-code text-center">Kode</th>
                        <th class="c-nim">NIM</th>
                        <th class="c-name">Nama Mahasiswa</th>
                        @for($i=1;$i<=16;$i++)<th class="c-meeting text-center">P{{ $i }}</th>@endfor
                        <th class="c-summary text-center">Hadir</th>
                        <th class="c-summary text-center">Izin</th>
                        <th class="c-summary text-center">Sakit</th>
                        <th class="c-summary text-center">Alpa</th>
                        <th class="c-summary text-center">% Hadir</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($nilai as $index => $n)
                    @php
                        $att=$n->kehadiranMahasiswa->keyBy('pertemuan');
                        $hadir=$att->where('status','Hadir')->count();
                        $izin=$att->where('status','Izin')->count();
                        $sakit=$att->where('status','Sakit')->count();
                        $alpa=$att->where('status','Alpa')->count();
                        $total=$att->count();
                        $persentase=$total>0 ? round(($hadir/$total)*100,2) : 0;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $nilai->firstItem()+$index }}</td>
                        <td><strong>{{ $n->mataKuliah->name ?? '-' }}</strong></td>
                        <td class="text-center">{{ $n->mataKuliah->code ?? '-' }}</td>
                        <td><strong>{{ $n->mahasiswa->numb_nim ?? $n->mahasiswa->nim ?? $n->mahasiswa->code ?? '-' }}</strong></td>
                        <td class="student-name"><strong>{{ $n->mahasiswa->name ?? '-' }}</strong></td>
                        @for($i=1;$i<=16;$i++)
                            @php $a=$att[$i]??null; @endphp
                            <td class="text-center">
                                @if(($a->status??'')==='Hadir')<span class="text-success fw-bold">✓</span>
                                @elseif(($a->status??'')==='Izin')<span class="text-warning fw-semibold">I</span>
                                @elseif(($a->status??'')==='Sakit')<span class="text-info fw-semibold">S</span>
                                @elseif(($a->status??'')==='Alpa')<span class="text-danger fw-semibold">A</span>
                                @else<span class="text-muted">—</span>@endif
                            </td>
                        @endfor
                        <td class="text-center">{{ $hadir }}</td><td class="text-center">{{ $izin }}</td><td class="text-center">{{ $sakit }}</td><td class="text-center">{{ $alpa }}</td>
                        <td class="text-center"><strong>{{ number_format($persentase,2) }}%</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="26" class="text-center py-4 text-muted">Belum ada data kehadiran.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $nilai->links() }}</div>
    </div>
</div>
<script>
function applyFilters() {
    const url = new URL(window.location.href);
    url.searchParams.set('semester', document.getElementById('filter-semester').value);
    const mahasiswa = document.getElementById('filter-mahasiswa').value;
    const mataKuliah = document.getElementById('filter-mata-kuliah').value;
    mahasiswa ? url.searchParams.set('mahasiswa_id', mahasiswa) : url.searchParams.delete('mahasiswa_id');
    mataKuliah ? url.searchParams.set('mata_kuliah_id', mataKuliah) : url.searchParams.delete('mata_kuliah_id');
    window.location.href = url.toString();
}
document.getElementById('filter-semester')?.addEventListener('change', applyFilters);
document.getElementById('filter-mahasiswa')?.addEventListener('change', applyFilters);
document.getElementById('filter-mata-kuliah')?.addEventListener('change', applyFilters);
</script>
@endsection
