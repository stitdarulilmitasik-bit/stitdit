@extends('core-themes.core-backpage')

@section('custom-css')
<style>
.card{border:none;box-shadow:0 0 10px rgba(0,0,0,.05);border-radius:10px}
.table-responsive{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}
.table-responsive>.table{min-width:1350px}
.table th,.table td{white-space:nowrap;vertical-align:middle}
.avatar{width:40px;height:40px;object-fit:cover;border-radius:50%;border:2px solid #e9ecef}
</style>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Daftar Mahasiswa</h2>
        <div class="text-secondary">Data mahasiswa sebagai referensi akademik Dosen.</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('dosen.akademik.daftar-mahasiswa-export-excel', request()->query()) }}" class="btn btn-success">
            <i class="ti ti-file-spreadsheet me-1"></i> Export to Excel
        </a>
        <span class="badge bg-light-primary text-primary"><i class="ti ti-users me-1"></i>{{ $mahasiswa->total() }} Mahasiswa</span>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('dosen.akademik.daftar-mahasiswa') }}">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Semester</label>
                    <select name="semester" class="form-select">
                        <option value="">Semua Semester</option>
                        @for($i=1;$i<=8;$i++)
                            <option value="{{ $i }}" {{ (string)$semesterFilter === (string)$i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Program Studi</label>
                    <select name="prodi_id" class="form-select">
                        <option value="">Semua Program Studi</option>
                        @foreach($prodiOptions as $p)
                            <option value="{{ $p->id }}" {{ (string)$prodiFilter === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasOptions as $k)
                            <option value="{{ $k->id }}" {{ (string)$kelasFilter === (string)$k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cari</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" value="{{ $searchFilter }}" placeholder="Nama atau NIM">
                        <button class="btn btn-primary"><i class="ti ti-search"></i></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="ti ti-users me-2 text-primary"></i>Daftar Mahasiswa</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-vcenter mb-0">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>Mahasiswa</th>
                        <th>NIM</th>
                        <th>Nomor Telepon</th>
                        <th>Tanggal Lahir</th>
                        <th>Alamat</th>
                        <th>Program Studi</th>
                        <th>Kelas</th>
                        <th>Semester</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($mahasiswa as $index => $item)
                    <tr>
                        <td class="text-center">{{ $mahasiswa->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ stit_profile_image_url($item->photo) }}" alt="{{ $item->name }}" class="avatar me-2">
                                <div>
                                    <strong>{{ $item->name }}</strong>
                                    <div class="text-secondary small">{{ $item->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $item->numb_nim ?? '-' }}</td>
                        <td>{{ $item->phone ?? '-' }}</td>
                        <td>
                            @php
                                $tanggalLahir = $item->getRawOriginal('bio_datebirth');
                            @endphp
                            {{ $tanggalLahir ? date('d-M-Y', strtotime($tanggalLahir)) : '-' }}
                        </td>
                        <td style="min-width:240px;max-width:360px;white-space:normal;">{{ $item->getRawOriginal('ktp_addres') ?: '-' }}</td>
                        <td>{{ optional($item->programStudi)->name ?? '-' }}</td>
                        <td>{{ optional($item->kelas)->name ?? '-' }}</td>
                        <td>Semester {{ $item->semester ?? '-' }}</td>
                        <td>{{ $item->type ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-secondary py-5">Tidak ada data mahasiswa.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($mahasiswa->hasPages())
        <div class="card-footer">{{ $mahasiswa->links() }}</div>
    @endif
</div>
@endsection
