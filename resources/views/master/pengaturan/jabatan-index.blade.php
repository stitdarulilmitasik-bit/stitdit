@extends('core-themes.core-backpage')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title mb-1">Tambah Jabatan</h3>
                    <div class="text-secondary">Masukkan jabatan dan pejabat yang memegangnya.</div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route($spref . 'pengaturan.jabatan-handle') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Ketua STIT" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category" class="form-select">
                            <option value="">Pilih kategori</option>
                            <option value="Pimpinan">Pimpinan</option>
                            <option value="Akademik">Akademik</option>
                            <option value="Program Studi">Program Studi</option>
                            <option value="Kemahasiswaan">Kemahasiswaan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pejabat / Dosen</label>
                        <select name="dosen_id" class="form-select">
                            <option value="">Belum ditentukan</option>
                            @foreach($dosen as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}{{ $item->nidn ? ' - NIDN ' . $item->nidn : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Program Studi (opsional)</label>
                        <select name="prodi_id" class="form-select">
                            <option value="">Semua / tingkat institusi</option>
                            @foreach($program_studi as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-secondary">Gunakan untuk jabatan seperti Ketua Prodi atau Dosen Pembimbing Akademik.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Keterangan tambahan"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="sort_order" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                <span class="form-check-label">Aktif</span>
                            </label>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100 mt-4" type="submit">Simpan Jabatan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title mb-1">Data Jabatan</h3>
                    <div class="text-secondary">Data ini menjadi sumber pejabat untuk KRS, KHS, transkrip, surat, dan laporan lainnya.</div>
                </div>
                <span class="badge bg-green-lt">{{ $jabatan->where('is_active', true)->count() }} aktif</span>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jabatan</th>
                            <th>Pejabat</th>
                            <th>Program Studi</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($jabatan as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="fw-bold">{{ $item->name }}</div>
                                @if($item->category)<div class="text-secondary small">{{ $item->category }}</div>@endif
                            </td>
                            <td>{{ $item->dosen?->name ?? 'Belum ditentukan' }}</td>
                            <td>{{ $item->programStudi?->name ?? 'Institusi' }}</td>
                            <td>
                                <span class="badge {{ $item->is_active ? 'bg-green-lt' : 'bg-secondary-lt' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editJabatan{{ $item->code }}">Edit</button>
                                <form action="{{ route($spref . 'pengaturan.jabatan-delete', $item->code) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jabatan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-5">Belum ada data jabatan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($jabatan as $item)
<div class="modal fade" id="editJabatan{{ $item->code }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route($spref . 'pengaturan.jabatan-update', $item->code) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Jabatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select">
                                @foreach(['Pimpinan','Akademik','Program Studi','Kemahasiswaan','Lainnya'] as $category)
                                    <option value="{{ $category }}" {{ $item->category === $category ? 'selected' : '' }}>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pejabat / Dosen</label>
                            <select name="dosen_id" class="form-select">
                                <option value="">Belum ditentukan</option>
                                @foreach($dosen as $d)
                                    <option value="{{ $d->id }}" {{ (int)$item->dosen_id === (int)$d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Program Studi</label>
                            <select name="prodi_id" class="form-select">
                                <option value="">Institusi</option>
                                @foreach($program_studi as $p)
                                    <option value="{{ $p->id }}" {{ (int)$item->prodi_id === (int)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="description" class="form-control" rows="3">{{ $item->description }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="sort_order" class="form-control" min="0" value="{{ $item->sort_order }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}>
                                <span class="form-check-label">Aktif</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
