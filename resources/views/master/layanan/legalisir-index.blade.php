@extends('core-themes.core-backpage')

@section('content')
<div class="container-fluid">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Pengajuan Legalisir Dokumen</h3></div>
        <div class="card-body">
            <form class="row g-2" method="GET">
                <div class="col-md-5"><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari nama mahasiswa atau NIM"></div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach(['Diajukan','Diverifikasi','Disetujui','Ditolak','Diproses','Selesai'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto"><button class="btn btn-primary">Filter</button></div>
                <div class="col-md-auto"><a href="{{ route('web-admin.layanan.legalisir') }}" class="btn btn-outline-secondary">Reset</a></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>No.</th><th>Pengajuan</th><th>Mahasiswa</th><th>Dokumen</th><th>Jumlah</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($pengajuan as $item)
                    @php $badge=['Diajukan'=>'bg-blue-lt','Diverifikasi'=>'bg-cyan-lt','Disetujui'=>'bg-green-lt','Ditolak'=>'bg-red-lt','Diproses'=>'bg-yellow-lt','Selesai'=>'bg-green-lt'][$item->status] ?? 'bg-secondary-lt'; @endphp
                    <tr>
                        <td>{{ $pengajuan->firstItem() + $loop->index }}</td>
                        <td><strong>{{ $item->nomor_pengajuan }}</strong><div class="text-secondary small">{{ optional($item->tanggal_pengajuan)->format('d/m/Y') }}</div></td>
                        <td>{{ $item->mahasiswa?->name ?? '-' }}<div class="text-secondary small">{{ $item->mahasiswa?->numb_nim ?? '-' }}</div></td>
                        <td>{{ $item->jenis_dokumen }}</td>
                        <td>{{ $item->jumlah }}</td>
                        <td><span class="badge {{ $badge }}">{{ $item->status }}</span></td>
                        <td><a class="btn btn-sm btn-primary" href="{{ route('web-admin.layanan.legalisir.detail', $item->id) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5">Belum ada pengajuan legalisir.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($pengajuan->hasPages()) <div class="card-footer">{{ $pengajuan->links() }}</div> @endif
    </div>
</div>
@endsection
