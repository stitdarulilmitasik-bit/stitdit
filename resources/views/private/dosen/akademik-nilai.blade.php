@extends('core-themes.core-backpage')
@section('content')
<div class="container-xl py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h2>Input / Update Nilai</h2>
            <p class="text-muted mb-0">Hanya nilai dari mata kuliah yang diampu yang ditampilkan.</p>
        </div>
        <a href="{{ route('dosen.akademik.kehadiran') }}" class="btn btn-success">
            <i class="fas fa-user-check me-1"></i> Input Kehadiran Mahasiswa
        </a>
    </div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card"><div class="table-responsive"><table class="table card-table table-vcenter"><thead><tr><th>Mahasiswa</th><th>Mata Kuliah</th><th>Semester</th><th>Status</th><th>Nilai Akhir</th><th>Update</th></tr></thead><tbody>
@forelse($nilai as $n)<tr><td>{{ $n->mahasiswa->name ?? '-' }}</td><td>{{ $n->mataKuliah->name ?? '-' }}</td><td>{{ $n->semester }}</td><td><span class="badge">{{ $n->status }}</span></td><td>{{ $n->nilai_angka ?? '-' }} / {{ $n->nilai_huruf ?? '-' }}</td><td><form method="POST" action="{{ route('dosen.akademik.nilai.update',$n->code) }}">@csrf @method('PATCH')<div class="d-flex gap-1 flex-wrap"><input class="form-control form-control-sm" style="width:90px" name="tugas_1" type="number" min="0" max="100" step="0.01" value="{{ $n->tugas_1 }}" placeholder="Tugas"><input class="form-control form-control-sm" style="width:90px" name="uts" type="number" min="0" max="100" step="0.01" value="{{ $n->uts }}" placeholder="UTS"><input class="form-control form-control-sm" style="width:90px" name="uas" type="number" min="0" max="100" step="0.01" value="{{ $n->uas }}" placeholder="UAS"><button class="btn btn-sm btn-primary" {{ $n->status !== 'Draft' ? 'disabled' : '' }}>Simpan</button></div></form></td></tr>@empty<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data nilai untuk mata kuliah yang Anda ampu.</td></tr>@endforelse
</tbody></table></div><div class="card-footer">{{ $nilai->links() }}</div></div></div>
@endsection
