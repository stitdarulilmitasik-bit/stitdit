@extends('core-themes.core-mainpage')

@section('custom-css')
<style>
    .pmb-hero {
        background: linear-gradient(135deg, rgba(var(--tblr-primary-rgb), .12), rgba(var(--tblr-info-rgb), .08));
        border: 1px solid var(--tblr-border-color-light);
        border-radius: 1.5rem;
        padding: 2rem;
        margin-bottom: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-xl py-4">
    <div class="pmb-hero">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <span class="badge bg-primary-lt mb-2">PMB Tahun Ajaran {{ now()->format('Y') }}/{{ now()->addYear()->format('Y') }}</span>
                <h1 class="mb-2">Pendaftaran Calon Mahasiswa Baru</h1>
                <p class="text-secondary mb-0">
                    Lengkapi data berikut untuk mengajukan pendaftaran sebagai calon mahasiswa
                    {{ $webs->school_name ?? 'STIT Darul Ilmi' }}.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('root.prodi-index') }}" class="btn btn-outline-primary">
                    Lihat Program Studi
                </a>
            </div>
        </div>
    </div>
