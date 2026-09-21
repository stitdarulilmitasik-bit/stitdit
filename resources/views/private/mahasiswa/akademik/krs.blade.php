@extends('core-themes.core-backpage')

@section('content')
<div class="container-fluid">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div> @endif

    <div class="d-flex align-items-center mb-3">
        <div>
            <h2 class="page-title mb-1">Kartu Rencana Studi (KRS)</h2>
            <div class="text-muted">{{ $currentSemester->name ?? '-' }} · {{ $currentSemester->type ?? '-' }}</div>
        </div>
        <div class="ms-auto">
            <a href="{{ route('mahasiswa.akademik.krs-cetak') }}" class="btn btn-primary" target="_blank">🖨 Cetak KRS</a>
            @if(($krsHeader->is_editable ?? false))
                <a href="#tambahKrsModal" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#tambahKrsModal">✎ Edit KRS</a>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahKrsModal">＋ Tambah Mata Kuliah</button>
                @if($krs->count() > 0)
                    <form method="POST" action="{{ route('mahasiswa.akademik.krs.submit') }}" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan KRS ini? Setelah diajukan, KRS tidak dapat diedit sampai diproses oleh akademik.')">
                        @csrf
                        <button type="submit" class="btn btn-primary">✓ Submit KRS</button>
                    </form>
                @endif
            @else
                @if(in_array($krsHeader->status ?? '', ['submitted','approved','published','locked']))
                    <span class="badge bg-success align-self-center">KRS {{ ucfirst($krsHeader->status) }}</span>
                @endif
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Mata Kuliah yang Diambil</h3>
                @if($krsHeader)
                    <small class="text-muted">Status KRS: <strong>{{ ucfirst($krsHeader->status) }}</strong></small>
                @endif
            </div>
            <div class="ms-auto text-muted">Total SKS: <strong>{{ $krs->sum('sks') }}</strong> / {{ $krsHeader->batas_sks ?? 24 }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>No.</th><th>Kode</th><th>Mata Kuliah</th><th>Kelas</th><th>SKS</th><th>Dosen</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($krs as $i => $item)
                    @php $status = $item->attributes['status'] ?? 'Aktif'; @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $item->mataKuliah->code ?? '-' }}</td>
                        <td>{{ $item->mataKuliah->name ?? '-' }}</td>
                        <td>{{ $item->kelas->name ?? '-' }}</td>
                        <td>{{ $item->sks }}</td>
                        <td>{{ $item->dosen->name ?? '-' }}</td>
                        <td><span class="badge {{ $status === 'Aktif' ? 'bg-success' : ($status === 'Mengulang' ? 'bg-warning' : 'bg-secondary') }}">{{ $status }}</span></td>
                        <td>
                            @if(($krsHeader->is_editable ?? false))
                            <form method="POST" action="{{ route('mahasiswa.akademik.krs.destroy', $item->id) }}" onsubmit="return confirm('Batalkan mata kuliah ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Batalkan</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5">Belum ada mata kuliah pada KRS semester ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahKrsModal" tabindex="-1">
<div class="modal-dialog modal-lg"><div class="modal-content">
<form method="POST" action="{{ route('mahasiswa.akademik.krs.store') }}">
@csrf
<div class="modal-header"><h5 class="modal-title">Tambah Mata Kuliah</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<label class="form-label">Mata Kuliah</label>
<select name="mata_kuliah_id" class="form-select" required>
<option value="">-- Pilih Mata Kuliah --</option>
@foreach($availableCourses as $course)
<option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }} ({{ $course->sks }} SKS)</option>
@endforeach
</select>
<div class="form-hint mt-2">Mode edit aktif selama KRS masih Draft atau ditolak. Setelah Submit KRS, perubahan dikunci sampai KRS diproses oleh akademik.</div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Tambah ke KRS</button></div>
</form>
</div></div>
</div>
@endsection
