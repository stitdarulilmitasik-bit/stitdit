@extends('core-themes.core-backpage')

@section('custom-css')
<style>
    .legalisir-hero { background: linear-gradient(135deg, #f8fafc 0%, #eef8f2 100%); border: 1px solid #e5e7eb; }
    .status-badge { min-width: 90px; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali formulir.</strong>
            <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card legalisir-hero mb-3">
        <div class="card-body py-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-green-lt mb-2">LAYANAN MAHASISWA</span>
                    <h2 class="mb-2">Legalisir Dokumen</h2>
                    <p class="text-secondary mb-0">Ajukan legalisir dokumen akademik melalui SIAKAD. Pengajuan akan diverifikasi dan diproses oleh bagian akademik.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-lg-end">
                    <div class="text-secondary small">Mahasiswa</div>
                    <div class="fw-bold">{{ $user->name }}</div>
                    <div class="text-secondary">NIM: {{ $user->numb_nim ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Ajukan Legalisir</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('mahasiswa.layanan.ajukan-legalisir') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label required">Jenis Dokumen</label>
                            <select name="jenis_dokumen" class="form-select" required>
                                <option value="">Pilih dokumen</option>
                                @foreach($jenisDokumen as $jenis)
                                    <option value="{{ $jenis }}" @selected(old('jenis_dokumen') === $jenis)>{{ $jenis }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Jumlah Salinan</label>
                            <input type="number" name="jumlah" class="form-control" min="1" max="20" value="{{ old('jumlah', 1) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Keperluan</label>
                            <textarea name="keperluan" class="form-control" rows="3" maxlength="1000" required>{{ old('keperluan') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan_mahasiswa" class="form-control" rows="2" maxlength="2000">{{ old('catatan_mahasiswa') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dokumen Pendukung</label>
                            <input type="file" name="file_pendukung" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-hint">PDF/JPG/PNG, maksimal 2 MB.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title">Riwayat Pengajuan</h3></div>
                <div class="card-body">
                    @forelse($pengajuan as $item)
                        @php
                            $badge = [
                                'Diajukan' => 'bg-blue-lt',
                                'Diverifikasi' => 'bg-cyan-lt',
                                'Disetujui' => 'bg-green-lt',
                                'Ditolak' => 'bg-red-lt',
                                'Diproses' => 'bg-yellow-lt',
                                'Selesai' => 'bg-green-lt',
                            ][$item->status] ?? 'bg-secondary-lt';
                        @endphp
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <div class="fw-bold">{{ $item->jenis_dokumen }}</div>
                                    <div class="text-secondary small">{{ $item->nomor_pengajuan }} · {{ optional($item->tanggal_pengajuan)->format('d/m/Y') }}</div>
                                </div>
                                <span class="badge {{ $badge }} status-badge">{{ $item->status }}</span>
                            </div>
                            <div class="mt-2 small"><strong>Jumlah:</strong> {{ $item->jumlah }} salinan</div>
                            <div class="small"><strong>Keperluan:</strong> {{ $item->keperluan }}</div>
                            @if($item->catatan_admin)
                                <div class="alert alert-info mt-2 mb-0 py-2"><strong>Catatan:</strong> {{ $item->catatan_admin }}</div>
                            @endif
                            @if($item->status === 'Selesai' && $item->file_hasil)
                                <div class="mt-2"><a class="btn btn-sm btn-success" href="{{ asset('storage/' . $item->file_hasil) }}" target="_blank">Lihat Hasil</a></div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <h3>Belum Ada Pengajuan</h3>
                            <p class="text-secondary mb-0">Riwayat legalisir akan tampil setelah pengajuan dikirim.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
