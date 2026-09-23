@extends('core-themes.core-backpage')

@section('custom-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
	#dosenTable th:nth-child(2),
	#dosenTable td:nth-child(2) {
  	  min-width: 260px;
  	  width: 260px;
  	  white-space: nowrap;
	}
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

        /* Dosen action buttons */
        .dosen-header {
            display: block !important;
        }

        .dosen-action-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            width: 100%;
            margin-top: 1rem;
        }

        .dosen-action-btn {
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

        /* Responsive table: keep columns intact and scroll horizontally on small screens */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .table-responsive > .table {
            width: max-content;
            min-width: 100%;
            margin-bottom: 0;
        }

        .table-responsive .table th,
        .table-responsive .table td {
            white-space: nowrap;
        }

        .table-responsive .table td:first-child,
        .table-responsive .table th:first-child {
            width: 1%;
        }

        .table-responsive .table .btn-group {
            display: inline-flex;
            flex-wrap: nowrap;
            white-space: nowrap;
            gap: 0;
        }

        .table-responsive .table .btn {
            flex: 0 0 auto;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }

            .table-responsive {
                margin-left: -0.25rem;
                margin-right: -0.25rem;
                width: calc(100% + 0.5rem);
            }

            .table-responsive > .table {
                min-width: 760px;
            }

            .table-responsive .table th,
            .table-responsive .table td {
                padding: 0.55rem 0.65rem;
                font-size: 0.875rem;
                vertical-align: middle;
            }

            .table-responsive .table th {
                white-space: nowrap;
            }

            .table-responsive .table td[data-label]::before {
                content: none !important;
            }

            .table-responsive .table .btn-group .btn {
                padding: 0.3rem 0.5rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 col-12 mb-2">
            <div class="card">
                <div class="card-header dosen-header">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0">{{ $pages }}</h5>
                    </div>

                    <div class="dosen-action-row">
                        <a href="{{ route($spref . 'pengguna.dosen-export-pdf') }}" class="btn btn-danger dosen-action-btn" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                        </a>
                        <a href="{{ route($spref . 'pengguna.dosen-export-excel') }}" class="btn btn-success dosen-action-btn">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </a>
                        <a href="{{ route($spref . 'pengguna.dosen-export-full-excel') }}" class="btn btn-dark dosen-action-btn" title="Export seluruh isi tabel dosens">
                            <i class="fas fa-database me-1"></i> Export Full Excel
                        </a>
                        <button class="btn btn-warning dosen-action-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseImportDosen" aria-expanded="false" aria-controls="collapseImportDosen">
                            <i class="fas fa-file-import me-1"></i> Import Excel
                        </button>
                        <button class="btn btn-primary dosen-action-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseForm" aria-expanded="false" aria-controls="collapseForm">
                            <i class="fas fa-plus-circle me-1"></i>Tambah Dosen
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="collapse mb-4" id="collapseImportDosen">
                        <div class="card border border-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-1"><i class="fas fa-file-import me-2"></i>Import Data Dosen</h6>
                                        <small class="text-muted">Gunakan template Excel agar nama kolom sesuai dengan database dosen.</small>
                                    </div>
                                    <a href="{{ route($spref . 'pengguna.dosen-import-template') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-download me-1"></i> Download Template
                                    </a>
                                </div>
                                <form action="{{ route($spref . 'pengguna.dosen-import-excel') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row align-items-end">
                                        <div class="col-md-9 mb-2">
                                            <label for="import_dosen_file" class="form-label">File Excel</label>
                                            <input type="file" class="form-control" name="file" id="import_dosen_file" accept=".xlsx,.xls,.csv" required>
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
                                    <small><strong>Catatan:</strong> Data dicocokkan berdasarkan Code atau Email. Jika sudah ada, data akan diperbarui; jika belum ada, dosen baru dibuat.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-2">
                            <div class="p-3 bg-light-primary rounded">
                                <h6 class="mb-2">Total Pengguna</h6>
                                <h3 class="mb-0">{{ $dosen ? count($dosen) : 0 }}</h3>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="p-3 bg-light-success rounded">
                                <h6 class="mb-2">Pengguna Aktif</h6>
                                <h3 class="mb-0">{{ $dosen ? $dosen->where('status', 'Aktif')->count() : 0 }}</h3>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Collapsible Form -->
                    <div class="collapse" id="collapseForm">
                        <div class="card card-body border">
                            <h5 class="card-title mb-3">Tambah Pengguna Baru</h5>
                            <form action="{{ route($spref . 'pengguna.dosen-handle') }}" method="post">
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
                                        <label for="type" class="form-label">Status Kerja</label>
                                        <select class="form-select" name="type" id="type" required>
                                            <option value="">Pilih Status Kerja</option>
                                            <option value="0">Tidak Aktif</option>
                                            <option value="1">Aktif</option>
                                        </select>
                                        @error('type')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">                                        <label for="status_dosen" class="form-label">Status Dosen</label>                                        <select class="form-select" name="status_dosen" id="status_dosen" required>                                            <option value="">Pilih Status Dosen</option>                                            <option value="Dosen Tetap">Dosen Tetap</option>                                            <option value="Dosen Tidak Tetap">Dosen Tidak Tetap</option>                                        </select>                                        @error('status_dosen')                                            <small class="text-danger">{{ $message }}</small>                                        @enderror                                    </div>                                    <div class="col-12 d-flex justify-content-end">
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
                        <table class="table" id="dosenTable">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Status Kerja</th>                                    <th>Status Dosen</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dosen as $key => $item)
                                    <tr>
                                        <td data-label="No">{{ ++$key }}</td>
                                        <td data-label="Nama">{{ $item->name }}</td>
                                        <td data-label="Email">{{ $item->email }}</td>
                                        <td data-label="Telepon">{{ $item->phone }}</td>
                                        <td data-label="Status Kerja">{{ $item->type }}</td>                                        <td data-label="Status Dosen">{{ $item->status_dosen }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route($spref.'pengguna.dosen-views', $item->code) }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="Lihat Pengguna">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#editData{{ $item->code }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit Pengguna">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route($spref . 'pengguna.dosen-delete', $item->code) }}" method="POST" class="d-inline" id="delete-form-{{ $item->code }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger" data-confirm-delete="true" data-bs-toggle="tooltip" title="Hapus Pengguna" onclick="confirmDelete('{{ $item->code }}')">
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
                    <h5 class="card-title">Informasi Pengguna</h5>
                </div>
                <div class="card-body">
                    <p>Bagian ini menampilkan informasi umum dan petunjuk terkait pengelolaan Pengguna.</p>
                    
                    <div class="alert alert-light-success">
                        <h6 class="">Petunjuk Penggunaan:</h6>
                        <ul class="mb-0">
                            <li>Klik tombol "Tambah Pengguna" untuk menambahkan pengguna baru</li>
                            <li>Klik ikon <i class="fas fa-edit"></i> untuk mengedit data pengguna</li>
                            <li>Klik ikon <i class="fas fa-trash"></i> untuk menghapus pengguna</li>
                        </ul>
                    </div>

                    @if(count($dosen) > 0)
                        <div class="mt-4">
                            <h6>Pengguna Terbaru</h6>
                            <div class="list-group">
                                @foreach($dosen->sortByDesc('created_at')->take(3) as $dosenItem)
                                    <div class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">{{ $dosenItem->name }}</h6>
                                            <small class="text-muted">{{ $dosenItem->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1">{{ $dosenItem->email }}</p>
                                        <small class="text-muted">{{ $dosenItem->phone }}</small>
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
    @foreach ($dosen as $item)
        <div class="modal fade" id="editData{{ $item->code }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $item->code }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <form action="{{ route($spref . 'pengguna.dosen-update', $item->code) }}" method="POST">
                        @method('patch')
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel{{ $item->code }}">Edit Pengguna - {{ $item->name }}</h5>
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
                                    <label for="edit_type{{ $item->code }}" class="form-label">Status Kerja</label>
                                    <select class="form-select" name="type" id="edit_type{{ $item->code }}" required>
                                        <option value="">Pilih Status Kerja</option>
                                        <option value="0" {{ $item->raw_type == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                                        <option value="1" {{ $item->raw_type == 1 ? 'selected' : '' }}>Aktif</option>
                                    </select>
                                    @error('type')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
<!-- TAMBAHKAN DI SINI -->
<div class="col-md-6 mb-3">
    <label for="edit_status_dosen{{ $item->code }}" class="form-label">Status Dosen</label>
    <select class="form-select" name="status_dosen" id="edit_status_dosen{{ $item->code }}" required>
        <option value="">Pilih Status Dosen</option>
        <option value="Dosen Tetap" {{ $item->status_dosen == 'Dosen Tetap' ? 'selected' : '' }}>Dosen Tetap</option>
        <option value="Dosen Tidak Tetap" {{ $item->status_dosen == 'Dosen Tidak Tetap' ? 'selected' : '' }}>Dosen Tidak Tetap</option>
    </select>
    @error('status_dosen')
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
                responsive: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]]
            });
        });

        // Konfirmasi delete dengan SweetAlert
        function confirmDelete(code) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data pengguna yang dihapus tidak dapat dikembalikan!",
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
