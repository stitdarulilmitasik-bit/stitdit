<li class="nav-item">
    <a class="nav-link {{ Route::is('dosen.dashboard-render', request()->path()) ? 'active' : '' }}" href="{{ route('dosen.dashboard-render') }}">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l-2 -0l9 -9l9 9l-2 0"/><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path d="M9 21v-6a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6"/></svg>
        </span>
        <span class="nav-link-title">Dashboard</span>
    </a>
</li>

<li class="nav-item">
    <span class="nav-link"><span class="nav-link-title">Data Akademik</span></span>
</li>

<li class="nav-item dropdown">
    <a class="nav-link {{ Route::is('dosen.akademik.*') ? 'active' : '' }} dropdown-toggle" href="#navbar-dosen-akademik" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-school"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M22 9l-10 -4l-10 4l10 4l10 -4v6"/><path d="M6 10.6v5.4a6 6 0 0 0 12 0v-5.4"/></svg>
        </span>
        <span class="nav-link-title">Master Akademik</span>
    </a>
    <div class="dropdown-menu">
        <a class="dropdown-item {{ Route::is('dosen.akademik.daftar-mahasiswa') ? 'active' : '' }}" href="{{ route('dosen.akademik.daftar-mahasiswa') }}">Daftar Mahasiswa</a>
        <a class="dropdown-item {{ Route::is('dosen.akademik.taka-*') ? 'active' : '' }}" href="{{ route('dosen.akademik.taka-render') }}">Tahun Akademik</a>
        <a class="dropdown-item {{ Route::is('dosen.akademik.fakultas-*') ? 'active' : '' }}" href="{{ route('dosen.akademik.fakultas-render') }}">Fakultas</a>
        <a class="dropdown-item {{ Route::is('dosen.akademik.prodi-*') ? 'active' : '' }}" href="{{ route('dosen.akademik.prodi-render') }}">Program Studi</a>
        <a class="dropdown-item {{ Route::is('dosen.akademik.kurikulum-*') ? 'active' : '' }}" href="{{ route('dosen.akademik.kurikulum-render') }}">Kurikulum</a>
        <a class="dropdown-item {{ Route::is('dosen.akademik.mata-kuliah-*') ? 'active' : '' }}" href="{{ route('dosen.akademik.mata-kuliah-render') }}">Mata Kuliah</a>
        <a class="dropdown-item {{ Route::is('dosen.akademik.jenis-kelas-*') ? 'active' : '' }}" href="{{ route('dosen.akademik.jenis-kelas-render') }}">Jenis Kelas</a>
        <a class="dropdown-item {{ Route::is('dosen.akademik.kelas-*') ? 'active' : '' }}" href="{{ route('dosen.akademik.kelas-render') }}">Kelas</a>
        <a class="dropdown-item {{ Route::is('dosen.akademik.waktu-kuliah-*') ? 'active' : '' }}" href="{{ route('dosen.akademik.waktu-kuliah-render') }}">Waktu Kuliah</a>
        <a class="dropdown-item {{ Route::is('dosen.akademik.jadwal-kuliah-*') ? 'active' : '' }}" href="{{ route('dosen.akademik.jadwal-kuliah-render') }}">Jadwal Kuliah</a>
        <a class="dropdown-item {{ Route::is('dosen.akademik.krs-*') ? 'active' : '' }}" href="{{ route('dosen.akademik.krs-render') }}">KRS (Kartu Rencana Studi)</a>
        <a class="dropdown-item {{ Route::is('dosen.akademik.nilai-*') ? 'active' : '' }}" href="{{ route('dosen.akademik.nilai-render') }}">Nilai Mahasiswa</a>
        <a class="dropdown-item {{ Route::is('dosen.akademik.kehadiran') ? 'active' : '' }}" href="{{ route('dosen.akademik.kehadiran') }}">Input Kehadiran Mahasiswa</a>
        <a class="dropdown-item {{ Route::is('dosen.akademik.khs-*') ? 'active' : '' }}" href="{{ route('dosen.akademik.khs-render') }}">KHS (Kartu Hasil Studi)</a>
    </div>
</li>
