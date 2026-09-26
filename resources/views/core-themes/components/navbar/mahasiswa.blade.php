<!-- Akademik Menu -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#navbar-akademik" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M22 9l-10 -4l-10 4l10 4l10 -4v6" />
                <path d="M6 10.6v5.4a6 3 0 0 0 12 0v-5.4" />
            </svg>
        </span>
        <span class="nav-link-title">Akademik</span>
    </a>
    <div class="dropdown-menu">
        <div class="dropdown-menu-columns">
            <div class="dropdown-menu-column">
                <a class="dropdown-item" href="{{ route('mahasiswa.akademik.krs-render') }}">
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                            <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                            <path d="M9 12l2 2l4 -4" />
                        </svg>
                    </span>
                    KRS (Kartu Rencana Studi)
                </a>
                <a class="dropdown-item" href="{{ route('mahasiswa.dashboard-render') }}">
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0 0 0" fill="none"/>
                            <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                            <path d="M16 3v4" />
                            <path d="M8 3v4" />
                            <path d="M4 11h16" />
                        </svg>
                    </span>
                    Jadwal Kuliah
                </a>
                <a class="dropdown-item" href="{{ route('mahasiswa.akademik.presensi') }}">
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="none" />
                            <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                            <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                        </svg>
                    </span>
                    Presensi
                </a>
                <a class="dropdown-item" href="{{ route('mahasiswa.akademik.nilai') }}">
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="none" />
                            <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                            <path d="M12 1l3 6l6 3l-6 3l-3 6l-3 -6l-6 -3z" />
                        </svg>
                    </span>
                    Nilai & IPK
                </a>
            </div>
        </div>
    </div>
</li>

<!-- Keuangan Menu -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#navbar-keuangan" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" />
                <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" />
            </svg>
        </span>
        <span class="nav-link-title">Keuangan</span>
    </a>
    <div class="dropdown-menu">
        <div class="dropdown-menu-columns">
            <div class="dropdown-menu-column">
                <a class="dropdown-item" href="{{ route('mahasiswa.keuangan.tagihan') }}">
                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12h-2"/></svg></span>
                    Tagihan Kuliah
                </a>
                <a class="dropdown-item" href="{{ route('mahasiswa.keuangan.riwayat') }}">
                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12"/></svg></span>
                    Riwayat Pembayaran
                </a>
                <a class="dropdown-item" href="{{ route('mahasiswa.keuangan.virtual-account') }}">
                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"><path d="M3 21h18M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16"/></svg></span>
                    Virtual Account
                </a>
                <a class="dropdown-item" href="{{ route('mahasiswa.keuangan.bukti-pembayaran') }}">
                    <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"><path d="M14 3v4a1 1 0 0 0 1 1h4M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/></svg></span>
                    Bukti Pembayaran
                </a>
            </div>
        </div>
    </div>
</li>

<!-- Layanan Menu -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#navbar-layanan" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"/></svg>
        </span>
        <span class="nav-link-title">Layanan</span>
    </a>
    <div class="dropdown-menu">
        <div class="dropdown-menu-columns">
            <div class="dropdown-menu-column">
                <a class="dropdown-item" href="{{ route('mahasiswa.layanan.transkrip') }}"><span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"><path d="M14 3v4a1 1 0 0 0 1 1h4M17 21h-10a2 2 0 0 0 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 0 -2 2z"/></svg></span>Transkrip Nilai</a>
                <a class="dropdown-item" href="{{ route('mahasiswa.layanan.surat-keterangan') }}"><span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"><path d="M14 3v4a1 1 0 0 0 1 1h4"/></svg></span>Surat Keterangan</a>
                <a class="dropdown-item" href="{{ route('mahasiswa.layanan.legalisir') }}"><span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11"/></svg></span>Legalisir Dokumen</a>
                <a class="dropdown-item" href="{{ route('mahasiswa.layanan.cuti') }}"><span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 0 0 -8 0z"/></svg></span>Cuti Akademik</a>
            </div>
        </div>
    </div>
</li>

<!-- Informasi Menu -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#navbar-informasi" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"><path d="M12 8v4M12 16h.01"/></svg>
        </span>
        <span class="nav-link-title">Informasi</span>
    </a>
    <div class="dropdown-menu">
        <div class="dropdown-menu-columns">
            <div class="dropdown-menu-column">
                <a class="dropdown-item" href="{{ route('mahasiswa.informasi.pengumuman') }}">Pengumuman</a>
                <a class="dropdown-item" href="{{ route('mahasiswa.informasi.kalender-akademik') }}">Kalender Akademik</a>
                <a class="dropdown-item" href="{{ route('mahasiswa.informasi.beasiswa') }}">Beasiswa</a>
                <a class="dropdown-item" href="{{ route('mahasiswa.informasi.kontak-kampus') }}">Kontak Kampus</a>
            </div>
        </div>
    </div>
</li>

<!-- Bantuan -->
<li class="nav-item">
    <a class="nav-link" href="{{ route('mahasiswa.bantuan') }}">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"><path d="M12 16v.01M12 13a2 2 0 0 0 .914 -3.782"/></svg>
        </span>
        <span class="nav-link-title">Bantuan</span>
    </a>
</li>