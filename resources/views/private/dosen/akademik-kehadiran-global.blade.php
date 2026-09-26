@extends('core-themes.core-backpage')

@section('content')
<style>
    .global-attendance-wrap { width:100%; overflow-x:auto; }
    .global-attendance { min-width:1450px; table-layout:fixed; }
    .global-attendance th,.global-attendance td { padding:5px 6px !important; vertical-align:middle; line-height:1.2; }
    .global-attendance .c-no{width:45px}.global-attendance .c-course{width:220px}.global-attendance .c-code{width:90px}
    .global-attendance .c-nim{width:120px}.global-attendance .c-name{width:210px}
    .global-attendance .c-meeting{width:48px}.global-attendance .c-summary{width:82px}
    .global-attendance .c-selected{width:105px;background:rgba(32,107,196,.06)}
    .global-attendance .student-name{white-space:normal;overflow-wrap:anywhere}
</style>

<div class="container-xl py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="mb-1">Input Kehadiran Mahasiswa</h2>
            <p class="text-muted mb-0">Input dan rekap kehadiran mahasiswa per mata kuliah dan pertemuan.</p>
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

    <form method="POST" action="{{ route('web-admin.akademik.kehadiran.store') }}" class="card mb-3">
        @csrf
        <input type="hidden" name="semester" value="{{ $semester }}">
        <input type="hidden" name="pertemuan" value="{{ $pertemuan }}">
        <div class="card-header"><h3 class="card-title mb-0">Input Kehadiran Mahasiswa</h3></div>
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Semester</label>
                    <select id="filter-semester" class="form-select">
                        @for($i=1;$i<=8;$i++)
                            <option value="{{ $i }}" {{ (int)$semester === $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Pertemuan</label>
                    <select id="filter-pertemuan" class="form-select">
                        @for($i=1;$i<=16;$i++)
                            <option value="{{ $i }}" {{ (int)$pertemuan === $i ? 'selected' : '' }}>Pertemuan {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mahasiswa</label>
                    <select name="mahasiswa_id" id="filter-mahasiswa" class="form-select" required>
                        <option value="">Pilih Mahasiswa</option>
                        @foreach($mahasiswaOptions as $m)
                            <option value="{{ $m->id }}" {{ (string)$mahasiswaId === (string)$m->id ? 'selected' : '' }}>
                                {{ $m->numb_nim ?? '-' }} - {{ $m->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mata Kuliah</label>
                    <select name="mata_kuliah_id" id="filter-mata-kuliah" class="form-select" required>
                        <option value="">Pilih Mata Kuliah</option>
                        @foreach($mataKuliahOptions as $mk)
                            <option value="{{ $mk->id }}" {{ (string)$mataKuliahId === (string)$mk->id ? 'selected' : '' }}>
                                {{ $mk->code ?? '-' }} - {{ $mk->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Kehadiran</label>
                    <select name="status" class="form-select" required>
                        <option value="">Pilih</option>
                        @foreach(['Hadir','Izin','Sakit','Alpa'] as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Report Kehadiran</h3>
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
                        @for($i=1;$i<=16;$i++)
                            <th class="c-meeting text-center {{ (int)$pertemuan === $i ? 'c-selected' : '' }}">P{{ $i }}</th>
                        @endfor
                        <th class="c-summary text-center">Hadir</th>
                        <th class="c-summary text-center">Izin</th>
                        <th class="c-summary text-center">Sakit</th>
                        <th class="c-summary text-center">Alpa</th>
                        <th class="c-summary text-center">% Hadir</th>
                    </tr>
                </thead>
                <tbody>
                @php
                    $groupCounts = $nilai->getCollection()->groupBy('matkul_id')->map->count();
                    $groupSeen = [];
                @endphp
                @forelse($nilai as $index => $n)
                    @php
                        $att = $n->kehadiranMahasiswa->keyBy('pertemuan');
                        $matkulKey = (string) $n->matkul_id;
                        $showMataKuliah = !isset($groupSeen[$matkulKey]);
                        if ($showMataKuliah) {
                            $groupSeen[$matkulKey] = true;
                        }

                        $hadir = $att->where('status','Hadir')->count();
                        $izin = $att->where('status','Izin')->count();
                        $sakit = $att->where('status','Sakit')->count();
                        $alpa = $att->where('status','Alpa')->count();
                        $total = $att->count();
                        $persentase = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $nilai->firstItem() + $index }}</td>
                        @if($showMataKuliah)
                            <td rowspan="{{ $groupCounts[$n->matkul_id] ?? 1 }}" class="align-middle">
                                <strong>{{ $n->mataKuliah->name ?? '-' }}</strong>
                            </td>
                            <td rowspan="{{ $groupCounts[$n->matkul_id] ?? 1 }}" class="align-middle text-center">
                                <strong>{{ $n->mataKuliah->code ?? '-' }}</strong>
                            </td>
                        @endif
                        <td><strong>{{ $n->mahasiswa->numb_nim ?? $n->mahasiswa->nim ?? $n->mahasiswa->code ?? '-' }}</strong></td>
                        <td class="student-name"><strong>{{ $n->mahasiswa->name ?? '-' }}</strong></td>
                        @for($i=1;$i<=16;$i++)
                            @php $a = $att[$i] ?? null; @endphp
                            <td class="text-center {{ (int)$pertemuan === $i ? 'c-selected' : '' }}">
                                @if(($a->status ?? '') === 'Hadir')
                                    <span class="text-success fw-bold">✓</span>
                                @elseif(($a->status ?? '') === 'Izin')
                                    <span class="text-warning fw-semibold">I</span>
                                @elseif(($a->status ?? '') === 'Sakit')
                                    <span class="text-info fw-semibold">S</span>
                                @elseif(($a->status ?? '') === 'Alpa')
                                    <span class="text-danger fw-semibold">A</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        @endfor
                        <td class="text-center">{{ $hadir }}</td>
                        <td class="text-center">{{ $izin }}</td>
                        <td class="text-center">{{ $sakit }}</td>
                        <td class="text-center">{{ $alpa }}</td>
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

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="card-title mb-0">Rekap Kehadiran Global</h3>
                <div class="text-muted small">
                    Semester {{ $semester }}
                    @if($mahasiswaId) · Mahasiswa terpilih @endif
                    @if($mataKuliahId) · Mata kuliah terpilih @endif
                </div>
            </div>
            <form method="GET" action="{{ route('web-admin.akademik.kehadiran') }}" class="d-flex align-items-center gap-2">
                <input type="hidden" name="semester" value="{{ $semester }}">
                <input type="hidden" name="pertemuan" value="{{ $pertemuan }}">
                @if($mahasiswaId)<input type="hidden" name="mahasiswa_id" value="{{ $mahasiswaId }}">@endif
                @if($mataKuliahId)<input type="hidden" name="mata_kuliah_id" value="{{ $mataKuliahId }}">@endif
                <label class="form-label mb-0 text-nowrap">Group By</label>
                <select name="group_by" class="form-select" onchange="this.form.submit()" style="min-width:190px">
                    <option value="mata_kuliah" {{ $groupBy === 'mata_kuliah' ? 'selected' : '' }}>Mata Kuliah</option>
                    <option value="mahasiswa" {{ $groupBy === 'mahasiswa' ? 'selected' : '' }}>Nama Mahasiswa</option>
                </select>
            </form>
        </div>
        <div class="global-attendance-wrap">
            <table class="table table-bordered table-vcenter mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width:55px">No.</th>
                        @if($groupBy === 'mata_kuliah')
                            <th>Kode</th>
                            <th>Mata Kuliah</th>
                            <th class="text-center">Mahasiswa</th>
                        @else
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th class="text-center">Mata Kuliah</th>
                        @endif
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Izin</th>
                        <th class="text-center">Sakit</th>
                        <th class="text-center">Alpa</th>
                        <th class="text-center">Total Pertemuan</th>
                        <th class="text-center">% Hadir</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rekapKehadiran as $index => $r)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        @if($groupBy === 'mata_kuliah')
                            <td><strong>{{ $r['kode'] }}</strong></td>
                            <td>{{ $r['mata_kuliah'] }}</td>
                            <td class="text-center">{{ $r['jumlah_mahasiswa'] }}</td>
                        @else
                            <td><strong>{{ $r['nim'] }}</strong></td>
                            <td>{{ $r['mahasiswa'] }}</td>
                            <td class="text-center">{{ $r['jumlah_mata_kuliah'] }}</td>
                        @endif
                        <td class="text-center">{{ $r['hadir'] }}</td>
                        <td class="text-center">{{ $r['izin'] }}</td>
                        <td class="text-center">{{ $r['sakit'] }}</td>
                        <td class="text-center">{{ $r['alpa'] }}</td>
                        <td class="text-center">{{ $r['total'] }}</td>
                        <td class="text-center"><strong>{{ number_format($r['persentase'], 2) }}%</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">Belum ada data rekap kehadiran untuk filter yang dipilih.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const url = new URL(window.location.href);
    url.searchParams.set('semester', document.getElementById('filter-semester').value);
    url.searchParams.set('pertemuan', document.getElementById('filter-pertemuan').value);

    const mahasiswa = document.getElementById('filter-mahasiswa').value;
    const mataKuliah = document.getElementById('filter-mata-kuliah').value;

    mahasiswa ? url.searchParams.set('mahasiswa_id', mahasiswa) : url.searchParams.delete('mahasiswa_id');
    mataKuliah ? url.searchParams.set('mata_kuliah_id', mataKuliah) : url.searchParams.delete('mata_kuliah_id');

    window.location.href = url.toString();
}

document.getElementById('filter-semester')?.addEventListener('change', applyFilters);
document.getElementById('filter-pertemuan')?.addEventListener('change', applyFilters);
document.getElementById('filter-mahasiswa')?.addEventListener('change', applyFilters);
document.getElementById('filter-mata-kuliah')?.addEventListener('change', applyFilters);
</script>
@endsection
