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

        /* Table styling */
        .table {
            margin-bottom: 0;
        }

        .table thead th {
            border-top: none;
            border-bottom: 2px solid rgba(0,0,0,0.05);
            font-weight: 600;
            color: #6c757d;
            padding-top: 1rem;    /* Added padding top */
            padding-bottom: 0.75rem;
            text-align: left;     /* Default left align for headers */
        }

        .table td {
            vertical-align: middle;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            text-align: left; /* Default left align for cells */
        }

        /* Specific text alignment for certain columns */
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
        }

                /* Responsive table: keep dense admin tables horizontally scrollable on mobile */
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

        .table-responsive .table .btn-group {
            display: inline-flex;
            flex-wrap: nowrap;
            white-space: nowrap;
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
    <div class="kelas-page"><div class="row g-4 align-items-start">
        <!-- Main Content -->
        <div class="col-lg-8 col-12 mb-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center gap-3">
                    <div><div class="page-title"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>{{ $pages }}</div><div class="page-subtitle">Kelola data kelas dan kapasitas mahasiswa</div></div>
                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseForm" aria-expanded="false" aria-controls="collapseForm">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Kelas
                    </button>
                </div>
                <div class="card-body">
                    <!-- Quick Stats -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light-primary rounded">
                                <h6 class="mb-2">Total Kelas</h6>
                                <h3 class="mb-0">{{ count($kelas) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="p-3 bg-light-success rounded">
                                <h6 class="mb-2">Kelas Aktif</h6>
                                <h3 class="mb-0">{{ $kelas->where('status', 'Aktif')->count() }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="p-3 bg-light-warning rounded">
                                <h6 class="mb-2">Kelas Tidak Aktif</h6>
                                <h3 class="mb-0">{{ $kelas->where('status', 'Tidak Aktif')->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Collapsible Form -->
                    <div class="collapse" id="collapseForm">
                        <div class="card card-body border">
                            <h5 class="card-title mb-3">Tambah Kelas Baru</h5>
                            <form action="{{ route($spref . 'akademik.kelas-handle') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="taka_id" class="form-label">Tahun Akademik</label>
                                        <select class="form-select" name="taka_id" id="taka_id" required>
                                            <option value="">Pilih Tahun Akademik</option>
                                            @foreach ($tahun_akademik as $ta)
                                                <option value="{{ $ta->id }}">{{ $ta->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('taka_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="prodi_id" class="form-label">Program Studi</label>
                                        <select class="form-select" name="prodi_id" id="prodi_id" required>
                                            <option value="">Pilih Program Studi</option>
                                            @foreach ($program_studi as $ps)
                                                <option value="{{ $ps->id }}">{{ $ps->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('prodi_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="jenis_kelas_id" class="form-label">Jenis Kelas</label>
                                        <select class="form-select" name="jenis_kelas_id" id="jenis_kelas_id" required>
                                            <option value="">Pilih Jenis Kelas</option>
                                            @foreach ($jenis_kelas as $jk)
                                                <option value="{{ $jk->id }}">{{ $jk->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('jenis_kelas_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="ketua_id" class="form-label">Ketua Kelas (Opsional)</label>
                                        <select class="form-select" name="ketua_id" id="ketua_id">
                                            <option value="">Pilih Ketua Kelas</option>
                                            @foreach ($mahasiswa as $mhs)
                                                <option value="{{ $mhs->id }}">{{ $mhs->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('ketua_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="capacity" class="form-label">Kapasitas</label>
                                        <input type="number" class="form-control" name="capacity" id="capacity" min="1" required>
                                        @error('capacity')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Nama Kelas</label>
                                        <input type="text" class="form-control" name="name" id="name" required>
                                        @error('name')
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
                    
                   <!-- Table --> <div class="table-wrap mt-4"> <table class="table align-middle"> <thead> <tr> <th class="text-center">No</th> <th>Nama Kelas</th> <th>Kode</th> <th>Program Studi</th> <th>Tahun Akademik</th> <th>Jenis Kelas</th> <th>Kapasitas</th> <th class="text-center">Aksi</th> </tr> </thead> <tbody> @foreach ($kelas as $key => $item) <tr> {{-- 1. No --}} <td data-label="No" class="text-center"> {{ $key + 1 }} </td> {{-- 2. Nama Kelas --}} <td data-label="Nama Kelas"> <span class="class-name"> {{ $item->name }} </span> </td> {{-- 3. Kode --}} <td data-label="Kode"><span class="code-badge">{{ $item->code }}</span></td> {{-- 4. Program Studi --}} <td data-label="Program Studi"> {{ $item->programStudi?->name ?? 'Program Studi belum ditentukan' }} </td> {{-- 5. Tahun Akademik --}} <td data-label="Tahun Akademik"> {{ $item->tahunAkademik?->name ?? '-' }} </td> {{-- 6. Jenis Kelas --}} <td data-label="Jenis Kelas"> {{ $item->jenisKelas?->name ?? '-' }} </td> {{-- 7. Kapasitas --}} <td data-label="Kapasitas"> {{ $item->anggota?->count() ?? 0 }} / {{ $item->capacity ?? 0 }} </td> {{-- 8. Aksi --}} <td data-label="Aksi" class="text-center"> <div class="btn-group action-group" role="group"> <a href="#" data-bs-toggle="modal" data-bs-target="#editData{{ $item->code }}" class="btn btn-sm btn-primary" title="Edit Kelas"> <i class="fas fa-edit"></i> </a> <form action="{{ route($spref . 'akademik.kelas-delete', $item->code) }}" method="POST" class="d-inline" id="delete-form-{{ $item->code }}"> @csrf @method('DELETE') <button type="button" class="btn btn-sm btn-danger" title="Hapus Kelas" onclick="confirmDelete('{{ $item->code }}')"> <i class="fas fa-trash"></i> </button> </form> </div> </td> </tr> @endforeach </tbody> </table> </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar Content -->
        <div class="col-lg-4 col-12 mb-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Informasi Kelas</h5>
                </div>
                <div class="card-body">
                    <p>Kelas adalah kelompok belajar yang terdiri dari mahasiswa dalam program studi tertentu pada tahun akademik tertentu.</p>
                    
                    <div class="alert alert-light-success">
                        <h6 class="">Petunjuk Penggunaan:</h6>
                        <ul class="mb-0">
                            <li>Klik tombol "Tambah Kelas" untuk menambahkan kelas baru</li>
                            <li>Klik ikon <i class="fas fa-edit"></i> untuk mengedit data kelas</li>
                            <li>Klik ikon <i class="fas fa-trash"></i> untuk menghapus kelas</li>
                        </ul>
                    </div>
                    
                    @if(count($kelas) > 0)
                        <div class="mt-4">
                            <h6>Kelas Terbaru</h6>
                            <div class="list-group">
                                @foreach($kelas->take(5) as $k)
                                    <div class="list-group-item list-group-item-action recent-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1 recent-name">{{ $k->name }}</h6>
                                            <small class="recent-meta">{{ $k->jenisKelas?->name ?? '-' }}</small>
                                        </div>
                                        <p class="mb-1 recent-meta">{{ $k->programStudi?->name ?? '-' }}</p>
                                        <small>Kapasitas: {{ $k->capacity }} mahasiswa</small>
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
    @foreach ($kelas as $item)
        <div class="modal fade" id="editData{{ $item->code }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $item->code }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <form action="{{ route($spref . 'akademik.kelas-update', $item->code) }}" method="POST">
                        @method('patch')
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel{{ $item->code }}">Edit Kelas - {{ $item->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_taka_id{{ $item->code }}" class="form-label">Tahun Akademik</label>
                                    <select class="form-select" name="taka_id" id="edit_taka_id{{ $item->code }}" required>
                                        <option value="">Pilih Tahun Akademik</option>
                                        @foreach ($tahun_akademik as $ta)
                                            <option value="{{ $ta->id }}" {{ $item->taka_id == $ta->id ? 'selected' : '' }}>{{ $ta->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('taka_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_prodi_id{{ $item->code }}" class="form-label">Program Studi</label>
                                    <select class="form-select" name="prodi_id" id="edit_prodi_id{{ $item->code }}" required>
                                        <option value="">Pilih Program Studi</option>
                                        @foreach ($program_studi as $ps)
                                            <option value="{{ $ps->id }}" {{ $item->prodi_id == $ps->id ? 'selected' : '' }}>{{ $ps->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('prodi_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_jenis_kelas_id{{ $item->code }}" class="form-label">Jenis Kelas</label>
                                    <select class="form-select" name="jenis_kelas_id" id="edit_jenis_kelas_id{{ $item->code }}" required>
                                        <option value="">Pilih Jenis Kelas</option>
                                        @foreach ($jenis_kelas as $jk)
                                            <option value="{{ $jk->id }}" {{ $item->jenis_kelas_id == $jk->id ? 'selected' : '' }}>{{ $jk->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('jenis_kelas_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_ketua_id{{ $item->code }}" class="form-label">Ketua Kelas (Opsional)</label>
                                    <select class="form-select" name="ketua_id" id="edit_ketua_id{{ $item->code }}">
                                        <option value="">Pilih Ketua Kelas</option>
                                        @foreach ($mahasiswa as $mhs)
                                            <option value="{{ $mhs->id }}" {{ $item->ketua_id == $mhs->id ? 'selected' : '' }}>{{ $mhs->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('ketua_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_capacity{{ $item->code }}" class="form-label">Kapasitas</label>
                                    <input type="number" class="form-control" name="capacity" id="edit_capacity{{ $item->code }}" min="1" value="{{ $item->capacity }}" required>
                                    @error('capacity')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_name{{ $item->code }}" class="form-label">Nama Kelas</label>
                                    <input type="text" class="form-control" name="name" id="edit_name{{ $item->code }}" value="{{ $item->name }}" required>
                                    @error('name')
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
                text: "Data kelas yang dihapus tidak dapat dikembalikan!",
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