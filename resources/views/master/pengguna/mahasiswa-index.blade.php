@extends('core-themes.core-backpage')

@section('custom-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        /* Stats cards */
        .bg-light-primary {
            background-color: rgba(67, 94, 190, 0.1);
        }
        
        .bg-light-success {
            background-color: rgba(40, 167, 69, 0.1);
        }
        
        .bg-light-warning {
            background-color: rgba(255, 193, 7, 0.1);
        }
        
        .bg-light-info {
            background-color: rgba(23, 162, 184, 0.1);
        }

        /* Card styling */
        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            border-radius: 10px;
        }

        .card-header {
            background: none;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Mahasiswa action buttons */
        .mahasiswa-header {
            display: block !important;
        }

        .mahasiswa-action-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            width: 100%;
            margin-top: 1rem;
        }

        .mahasiswa-action-btn {
            min-width: 140px;
            height: 38px;
            padding: 0.5rem 0.75rem !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            flex: 0 0 auto;
        }

        /* Table styling */
        .table {
            margin-bottom: 0;
        }

        .table thead th {
            border-top: none;
            border-bottom: 2px solid rgba(0,0,0,0.05);
            font-weight: 600;
            color: #6c757d;
            padding-top: 1rem;
            padding-bottom: 0.75rem;
            text-align: left;
        }

        .table td {
            vertical-align: middle;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            text-align: left;
        }

        .table th.text-center, .table td.text-center {
            text-align: center !important;
        }

        /* Button styling */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
        }

        /* Form styling */
        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid rgba(0,0,0,0.1);
            padding: 0.5rem 1rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #435ebe;
            box-shadow: 0 0 0 0.2rem rgba(67, 94, 190, 0.25);
        }

        /* Badge styling */
        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
        }

        /* Collapsible form */
        .collapse {
            transition: all 0.3s ease;
        }

        .collapse.show {
            margin-top: 1rem;
        }

        /* Responsive styling */
        @media screen and (max-width: 768px) {
            .table td[data-label] .d-flex.align-items-center,
            .table td[data-label] .d-flex.flex-column.align-items-center {
                align-items: flex-end !important;
                text-align: right;
            }

            .mahasiswa-action-btn {
                min-width: 135px;
            }
        }

        @media (max-width: 768px) {
            .table-responsive table,
            .table-responsive thead,
            .table-responsive tbody,
            .table-responsive th,
            .table-responsive td,
            .table-responsive tr {
                display: block;
                width: 100%;
            }
            .table-responsive thead {
                display: none;
            }
            .table-responsive tr {
                margin-bottom: 1rem;
                border-bottom: 2px solid #eee;
            }
            .table-responsive td {
                position: relative;
                padding-left: 50%;
                text-align: left !important;
                border: none;
                border-bottom: 1px solid #eee;
            }
            .table-responsive td:before {
                position: absolute;
                top: 0;
                left: 0;
                width: 48%;
                padding-left: 1rem;
                white-space: nowrap;
                font-weight: bold;
                color: #888;
                content: attr(data-label);
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 col-12 mb-2">
            <div class="card">
                <div class="card-header mahasiswa-header">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0">{{ $pages }}</h5>
                    </div>

                    <div class="mahasiswa-action-row">
                        <a href="{{ route($spref . 'pengguna.mahasiswa-export-pdf') }}" class="btn btn-danger mahasiswa-action-btn" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                        </a>
                        <a href="{{ route($spref . 'pengguna.mahasiswa-export-excel') }}" class="btn btn-success mahasiswa-action-btn">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </a>
                        <a href="{{ route($spref . 'pengguna.mahasiswa-export-full-excel') }}" class="btn btn-dark mahasiswa-action-btn" title="Export seluruh isi tabel mahasiswas">
                            <i class="fas fa-database me-1"></i> Export Full Excel
                        </a>
                        <button class="btn btn-warning mahasiswa-action-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseImportMahasiswa" aria-expanded="false" aria-controls="collapseImportMahasiswa">
                            <i class="fas fa-file-import me-1"></i> Import Excel
                        </button>
                        <button class="btn btn-primary mahasiswa-action-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseForm" aria-expanded="false" aria-controls="collapseForm">
                            <i class="fas fa-plus-circle me-1"></i>Tambah Mahasiswa
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="collapse mb-4" id="collapseImportMahasiswa">
                        <div class="card border border-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-1"><i class="fas fa-file-import me-2"></i>Import Data Mahasiswa</h6>
                                        <small class="text-muted">Gunakan template Excel agar nama kolom sesuai dengan sistem.</small>
                                    </div>
                                    <a href="{{ route($spref . 'pengguna.mahasiswa-import-template') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-download me-1"></i> Download Template
                                    </a>
                                </div>
                                <form action="{{ route($spref . 'pengguna.mahasiswa-import-excel') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row align-items-end">
                                        <div class="col-md-9 mb-2">
                                            <label for="import_mahasiswa_file" class="form-label">File Excel</label>
                                            <input type="file" class="form-control" name="file" id="import_mahasiswa_file" accept=".xlsx,.xls,.csv" required>
                                            <small class="text-muted">Maksimal 10 MB. Format: XLSX, XLS, atau CSV.</small>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <button type="submit" class="btn btn-warning w-100">
                                                <i class="fas fa-upload me-1"></i> Mulai Import
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <div class="alert alert-info mt-3 mb-0">
                                    <small>
                                        <strong>Catatan:</strong> Data dicocokkan berdasarkan NIM. Jika NIM sudah ada, data akan diperbarui.
                                        Jika NIM belum ada, mahasiswa baru dibuat. Program Studi harus sama dengan nama Program Studi di sistem.
                                        Untuk mahasiswa baru, Email dan Nomor Telepon wajib diisi. Jika Password dikosongkan, Password awal menggunakan NIM.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-2">
                            <div class="p-3 bg-light-primary rounded">
                                <h6 class="mb-2">Total Mahasiswa</h6>
                                <h3 class="mb-0">{{ $mahasiswa ? count($mahasiswa) : 0 }}</h3>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="p-3 bg-light-success rounded">
                                <h6 class="mb-2">Mahasiswa Aktif</h6>
                                <h3 class="mb-0">{{ $mahasiswa ? $mahasiswa->where('type', 1)->count() : 0 }}</h3>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Collapsible Form -->
                    <div class="collapse" id="collapseForm">
                        <div class="card card-body border">
                            <h5 class="card-title mb-3">Tambah Mahasiswa Baru</h5>
                            <form action="{{ route($spref . 'pengguna.mahasiswa-handle') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" name="name" id="name" required>
                                        @error('name')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" id="email" required>
                                        @error('email')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Nomor Telepon</label>
                                        <input type="text" class="form-control" name="phone" id="phone" required>
                                        @error('phone')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password" id="password" required>
                                        @error('password')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="numb_nim" class="form-label">NIM</label>
                                        <input type="text" class="form-control" name="numb_nim" id="numb_nim" required>
                                        @error('numb_nim')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="prodi_id" class="form-label">Program Studi</label>
                                        <select class="form-select" name="prodi_id" id="prodi_id" required>
                                            <option value="">Pilih Program Studi</option>
                                            @foreach ($prodi as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('prodi_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="kelas_id" class="form-label">Kelas</label>
                                        <select class="form-select" name="kelas_id" id="kelas_id">
                                            <option value="">Belum ditentukan</option>
                                            @foreach ($kelas as $k)
                                                <option value="{{ $k->id }}">
                                                    {{ $k->name }}{{ $k->programStudi ? ' - ' . $k->programStudi->name : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kelas_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="type" class="form-label">Status</label>
                                        <select class="form-select" name="type" id="type" required>
                                            <option value="">Pilih Status</option>
                                            <option value="0">Calon Mahasiswa Baru</option>
                                            <option value="1">Mahasiswa Aktif</option>
                                            <option value="2">Mahasiswa Non-Aktif</option>
                                            <option value="3">Mahasiswa Lulus</option>
                                            <option value="4">Mahasiswa Cuti</option>
                                            <option value="5">Mahasiswa Pindah</option>
                                        </select>
                                        @error('type')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="semester" class="form-label">Semester</label>
                                        <input type="number" class="form-control" name="semester" id="semester" min="0" max="14" value="0">
                                        @error('semester')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Simpan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Table -->
                    <div class="table-responsive mt-3">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Nama</th>
                                    <th>NIM</th>
                                    <th>Program Studi</th>
                                    <th>Kelas</th>
                                    <th>Nomor Telepon</th>
                                    <th>Alamat</th>
                                    <th>Status</th>
                                    <th>Angkatan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mahasiswa as $key => $item)
                                    <tr>
                                        <td data-label="No">{{ ++$key }}</td>
                                        <td data-label="Nama">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $item->photo }}" alt="{{ $item->name }}" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #e9ecef;">
                                                <div>
                                                    <span class="fw-bold">{{ $item->name }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $item->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="NIM">{{ $item->numb_nim ?? '-' }}</td>
                                        <td data-label="Program Studi">{{ $item->programStudi->name ?? '-' }}</td>
                                        <td data-label="Kelas">
                                            @if($item->kelas)
                                                <span class="badge bg-light-primary text-primary">{{ $item->kelas->name }}</span>
                                            @else
                                                <span class="badge bg-light-warning text-warning">Belum ada kelas</span>
                                            @endif
                                        </td>
                                        <td data-label="Nomor Telepon">{{ $item->phone ?? '-' }}</td>
                                        <td data-label="Alamat">{{ $item->ktp_addres ?? '-' }}</td>
                                        <td data-label="Status">
                                            <span class="badge {{ $item->raw_type == 1 ? 'bg-light-success text-success' : ($item->raw_type == 0 ? 'bg-light-info text-info' : ($item->raw_type == 4 ? 'bg-light-warning text-warning' : ($item->raw_type == 2 ? 'bg-light-warning text-warning' : 'bg-light-secondary text-secondary'))) }}">
                                                {{ $item->type }}
                                            </span>
                                        </td>
                                        <td data-label="Angkatan">
                                            @php
                                                $takaRegist = $item->taka_regist;
                                                $angkatan = $takaRegist !== null && $takaRegist !== ''
                                                    ? (strlen((string) $takaRegist) === 2 ? '20' . $takaRegist : $takaRegist)
                                                    : null;
                                            @endphp
                                            {{ $angkatan ?? '-' }}
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route($spref.'pengguna.mahasiswa-views', $item->code) }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="Lihat Mahasiswa">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#editData{{ $item->code }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit Mahasiswa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route($spref . 'pengguna.mahasiswa-delete', $item->code) }}" method="POST" class="d-inline" id="delete-form-{{ $item->code }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger" data-confirm-delete="true" data-bs-toggle="tooltip" title="Hapus Mahasiswa" onclick="confirmDelete('{{ $item->code }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar Content -->
        <div class="col-lg-4 col-12 mb-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Informasi Mahasiswa</h5>
                </div>
                <div class="card-body">
                    <p>Bagian ini menampilkan informasi umum dan petunjuk terkait pengelolaan data Mahasiswa.</p>
                    
                    <div class="alert alert-light-success">
                        <h6 class="">Petunjuk Penggunaan:</h6>
                        <ul class="mb-0">
                            <li>Klik tombol "Tambah Mahasiswa" untuk menambahkan mahasiswa baru</li>
                            <li>Klik ikon <i class="fas fa-eye"></i> untuk melihat detail mahasiswa</li>
                            <li>Klik ikon <i class="fas fa-edit"></i> untuk mengedit data mahasiswa</li>
                            <li>Klik ikon <i class="fas fa-trash"></i> untuk menghapus mahasiswa</li>
                        </ul>
                    </div>

                    @if(count($mahasiswa) > 0)
                        <div class="mt-4">
                            <h6>Mahasiswa Terbaru</h6>
                            <div class="list-group">
                                @foreach($mahasiswa->sortByDesc('created_at')->take(3) as $mhs)
                                    <div class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">{{ $mhs->name }}</h6>
                                            <small class="text-muted">{{ $mhs->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1">{{ $mhs->numb_nim }}</p>
                                        <small class="text-muted">{{ $mhs->programStudi->name ?? '-' }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modals -->
    @foreach ($mahasiswa as $item)
        <div class="modal fade" id="editData{{ $item->code }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $item->code }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <form action="{{ route($spref . 'pengguna.mahasiswa-update', $item->code) }}" method="POST">
                        @method('patch')
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel{{ $item->code }}">Edit Mahasiswa - {{ $item->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_name{{ $item->code }}" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" name="name" id="edit_name{{ $item->code }}" value="{{ $item->name }}" required>
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_email{{ $item->code }}" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="edit_email{{ $item->code }}" value="{{ $item->email }}" required>
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_phone{{ $item->code }}" class="form-label">Nomor Telepon</label>
                                    <input type="text" class="form-control" name="phone" id="edit_phone{{ $item->code }}" value="{{ $item->phone }}" required>
                                    @error('phone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_password{{ $item->code }}" class="form-label">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                                    <input type="password" class="form-control" name="password" id="edit_password{{ $item->code }}">
                                    @error('password')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_numb_nim{{ $item->code }}" class="form-label">NIM</label>
                                    <input type="text" class="form-control" name="numb_nim" id="edit_numb_nim{{ $item->code }}" value="{{ $item->numb_nim }}" required>
                                    @error('numb_nim')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_prodi_id{{ $item->code }}" class="form-label">Program Studi</label>
                                    <select class="form-select" name="prodi_id" id="edit_prodi_id{{ $item->code }}" required>
                                        <option value="">Pilih Program Studi</option>
                                        @foreach ($prodi as $p)
                                            <option value="{{ $p->id }}" {{ $item->prodi_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('prodi_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_kelas_id{{ $item->code }}" class="form-label">Kelas</label>
                                    <select class="form-select" name="kelas_id" id="edit_kelas_id{{ $item->code }}">
                                        <option value="">Belum ditentukan</option>
                                        @foreach ($kelas as $k)
                                            <option value="{{ $k->id }}" {{ (int) $item->kelas_id === (int) $k->id ? 'selected' : '' }}>
                                                {{ $k->name }}{{ $k->programStudi ? ' - ' . $k->programStudi->name : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kelas_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_type{{ $item->code }}" class="form-label">Status</label>
                                    <select class="form-select" name="type" id="edit_type{{ $item->code }}" required>
                                        <option value="">Pilih Status</option>
                                        <option value="0" {{ $item->raw_type == 0 ? 'selected' : '' }}>Calon Mahasiswa Baru</option>
                                        <option value="1" {{ $item->raw_type == 1 ? 'selected' : '' }}>Mahasiswa Aktif</option>
                                        <option value="2" {{ $item->raw_type == 2 ? 'selected' : '' }}>Mahasiswa Non-Aktif</option>
                                        <option value="3" {{ $item->raw_type == 3 ? 'selected' : '' }}>Mahasiswa Lulus</option>
                                        <option value="4" {{ $item->raw_type == 4 ? 'selected' : '' }}>Mahasiswa Cuti</option>
                                        <option value="5" {{ $item->raw_type == 5 ? 'selected' : '' }}>Mahasiswa Pindah</option>
                                    </select>
                                    @error('type')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_semester{{ $item->code }}" class="form-label">Semester</label>
                                    <input type="number" class="form-control" name="semester" id="edit_semester{{ $item->code }}" min="0" max="14" value="{{ $item->semester }}">
                                    @error('semester')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('custom-js')
    <script src="{{ asset('dist') }}/assets/extensions/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('.table').DataTable({
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: "Data tidak ditemukan",
                    info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                    infoEmpty: "Tidak ada data yang tersedia",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                },
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });
        });

        // Konfirmasi delete dengan SweetAlert
        function confirmDelete(code) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data mahasiswa yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + code).submit();
                }
            });
        }
    </script>
@endsection
