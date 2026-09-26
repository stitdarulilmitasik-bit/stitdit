@extends('core-themes.core-backpage')

@section('content')
<div class="container-fluid">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Detail Pengajuan Legalisir</h3><div class="card-actions"><a href="{{ route('web-admin.layanan.legalisir') }}" class="btn btn-outline-secondary">Kembali</a></div></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-5">Nomor Pengajuan</dt><dd class="col-sm-7">{{ $item->nomor_pengajuan }}</dd>
                        <dt class="col-sm-5">Mahasiswa</dt><dd class="col-sm-7">{{ $item->mahasiswa?->name ?? '-' }}</dd>
                        <dt class="col-sm-5">NIM</dt><dd class="col-sm-7">{{ $item->mahasiswa?->numb_nim ?? '-' }}</dd>
                        <dt class="col-sm-5">Dokumen</dt><dd class="col-sm-7">{{ $item->jenis_dokumen }}</dd>
                        <dt class="col-sm-5">Jumlah</dt><dd class="col-sm-7">{{ $item->jumlah }} salinan</dd>
                        <dt class="col-sm-5">Tanggal</dt><dd class="col-sm-7">{{ optional($item->tanggal_pengajuan)->format('d/m/Y') }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <strong>Keperluan</strong>
                    <div class="border rounded p-3 mt-2 mb-3">{{ $item->keperluan }}</div>
                    @if($item->catatan_mahasiswa)
                        <strong>Catatan Mahasiswa</strong>
                        <div class="border rounded p-3 mt-2">{{ $item->catatan_mahasiswa }}</div>
                    @endif
                    @if($item->file_pendukung)
                        <a class="btn btn-sm btn-outline-primary mt-3" target="_blank" href="{{ asset('storage/'.$item->file_pendukung) }}">Lihat Dokumen Pendukung</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Proses Pengajuan</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('web-admin.layanan.legalisir.update', $item->id) }}" enctype="multipart/form-data">
                @csrf @method('PATCH')
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">Status</label>
                        <select name="status" class="form-select" required>
                            @foreach(['Diajukan','Diverifikasi','Disetujui','Ditolak','Diproses','Selesai'] as $status)
                                <option value="{{ $status }}" @selected($item->status === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3"><label class="form-label">Nomor Legalisir</label><input name="nomor_legalisir" class="form-control" value="{{ old('nomor_legalisir',$item->nomor_legalisir) }}"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Tanggal Legalisir</label><input type="date" name="tanggal_legalisir" class="form-control" value="{{ old('tanggal_legalisir', optional($item->tanggal_legalisir)->format('Y-m-d')) }}"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Nama Pejabat</label><input name="pejabat_nama" class="form-control" value="{{ old('pejabat_nama',$item->pejabat_nama) }}"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Jabatan Pejabat</label><input name="pejabat_jabatan" class="form-control" value="{{ old('pejabat_jabatan',$item->pejabat_jabatan) }}"></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Catatan Admin</label><textarea name="catatan_admin" class="form-control" rows="3">{{ old('catatan_admin',$item->catatan_admin) }}</textarea></div>
                    <div class="col-md-12 mb-3"><label class="form-label">File Hasil Legalisir</label><input type="file" name="file_hasil" class="form-control" accept=".pdf,.jpg,.jpeg,.png"><div class="form-hint">PDF/JPG/PNG, maksimal 5 MB.</div></div>
                </div>
                <button class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
@endsection
