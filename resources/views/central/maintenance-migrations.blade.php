@extends('core-themes.core-backpage')

@section('title', 'Database Migration')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Database Migration</h2>
                <div class="text-muted mt-1">
                    Pilih migration yang ingin dijalankan. Migration yang sudah selesai akan ditandai sebagai <strong>Sudah dijalankan</strong>.
                </div>
            </div>
        </div>
    </div>

    @if(session('maintenance_success'))
        <div class="alert alert-success">{{ session('maintenance_success') }}</div>
    @endif
    @if(session('maintenance_error'))
        <div class="alert alert-danger">{{ session('maintenance_error') }}</div>
    @endif
    @if(session('maintenance_output'))
        <div class="card mb-3">
            <div class="card-header"><strong>Output Migration</strong></div>
            <div class="card-body">
                <pre class="mb-0" style="white-space: pre-wrap;">{{ session('maintenance_output') }}</pre>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('web-admin.maintenance.migrate.run') }}" id="migration-form">
        @csrf

        <div class="card">
            <div class="card-header">
                <div class="row align-items-center w-100">
                    <div class="col">
                        <h3 class="card-title mb-0">Daftar Migration</h3>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="select-pending">
                            Pilih Semua Belum Dijalankan
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th style="width: 55px;">Pilih</th>
                            <th>Migration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($files as $migration)
                        <tr>
                            <td>
                                @if(!$migration['ran'])
                                    <input class="form-check-input migration-check" type="checkbox"
                                           name="migrations[]"
                                           value="{{ $migration['name'] }}">
                                @else
                                    <input class="form-check-input" type="checkbox" disabled>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $migration['name'] }}</div>
                                <div class="text-muted small">{{ $migration['file'] }}</div>
                            </td>
                            <td>
                                @if($migration['ran'])
                                    <span class="badge bg-success-lt text-success">Sudah dijalankan</span>
                                @else
                                    <span class="badge bg-warning-lt text-warning">Belum dijalankan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Tidak ada file migration.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <a href="{{ url('/web-admin/home') }}" class="btn btn-outline-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary" id="run-selected">
                    <i class="fas fa-database me-1"></i> Jalankan Migration Terpilih
                </button>
            </div>
        </div>
    </form>

    <div class="alert alert-warning mt-3">
        <strong>Perhatian:</strong> pilih hanya migration yang memang ingin diterapkan.
        Jangan memilih migration lama yang sudah dijalankan. Migration terpilih akan dijalankan sesuai urutan timestamp nama file.
        Jika sebuah migration membutuhkan tabel/kolom dari migration sebelumnya, jalankan migration sebelumnya terlebih dahulu.
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectPending = document.getElementById('select-pending');
    const checks = () => Array.from(document.querySelectorAll('.migration-check'));
    const form = document.getElementById('migration-form');

    selectPending?.addEventListener('click', function () {
        const items = checks();
        const allChecked = items.length > 0 && items.every(item => item.checked);
        items.forEach(item => item.checked = !allChecked);
        this.textContent = allChecked ? 'Pilih Semua Belum Dijalankan' : 'Batalkan Semua Pilihan';
    });

    form?.addEventListener('submit', function (event) {
        const selected = checks().filter(item => item.checked);
        if (!selected.length) {
            event.preventDefault();
            alert('Pilih minimal satu migration yang belum dijalankan.');
            return;
        }

        const names = selected.map(item => item.value).join("\\n");
        if (!confirm('Jalankan migration berikut?\\n\\n' + names)) {
            event.preventDefault();
        }
    });
});
</script>
@endsection
