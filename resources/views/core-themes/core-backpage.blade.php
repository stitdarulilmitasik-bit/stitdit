<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    @PwaHead

    @php
        $layoutMenus = $menus ?? 'Akademik';
        $layoutPages = $pages ?? 'Halaman';
        // Sebagian halaman backend tidak mengirim $academy. Gunakan nama aplikasi
        // sebagai fallback agar layout tetap aman tanpa mengubah data halaman.
        $layoutAcademy = $academy ?? config('app.name', 'STIT Darul Ilmi Tasikmalaya');
    @endphp

    <title>{{ ($layoutMenus ? $layoutMenus . ' - ' : '') . $layoutPages . ' - ' . $layoutAcademy }}</title>

    <link href="{{ asset('dashboard') }}/libs/jsvectormap/dist/jsvectormap.css" rel="stylesheet" />
    <link href="{{ asset('dashboard') }}/dist/css/tabler.css" rel="stylesheet" />
    <link href="{{ asset('dashboard') }}/dist/css/tabler-flags.css" rel="stylesheet" />
    <link href="{{ asset('dashboard') }}/dist/css/tabler-socials.css" rel="stylesheet" />
    <link href="{{ asset('dashboard') }}/dist/css/tabler-payments.css" rel="stylesheet" />
    <link href="{{ asset('dashboard') }}/dist/css/tabler-vendors.css" rel="stylesheet" />
    <link href="{{ asset('dashboard') }}/dist/css/tabler-marketing.css" rel="stylesheet" />
    <link href="{{ asset('dashboard') }}/dist/css/tabler-themes.css" rel="stylesheet" />

    @yield('custom-css')
    <style>
        .form-label,
        .form-control {
            margin-top: 0 !important;
        }
    </style>
    <style>
        /* Compact dashboard tables: keep data dense without making forms/cards cramped. */
        .page-body .card {
            border-radius: 10px;
        }

        .page-body .card-header {
            padding: .65rem .85rem;
        }

        .page-body .card-body {
            padding: .85rem;
        }

        .page-body .table-responsive {
            margin-bottom: 0;
        }

        .page-body .table {
            font-size: .8125rem;
            margin-bottom: 0;
        }

        .page-body .table > :not(caption) > * > * {
            padding: .42rem .55rem;
            vertical-align: middle;
        }

        .page-body .table thead th {
            font-size: .75rem;
            font-weight: 600;
            white-space: nowrap;
            line-height: 1.25;
        }

        .page-body .table tbody td {
            line-height: 1.3;
        }

        .page-body .table .btn {
            padding: .22rem .48rem;
            font-size: .72rem;
            line-height: 1.35;
        }

        .page-body .table .btn.btn-icon {
            padding: .3rem;
        }

        .page-body .table .badge {
            font-size: .68rem;
        }

        /* Preserve readability for long names, courses, addresses and notes. */
        .page-body .table td.text-wrap,
        .page-body .table td .text-wrap {
            white-space: normal;
        }

        /* Tables that explicitly need horizontal scrolling remain scrollable. */
        .page-body .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 767.98px) {
            .page-body .card-header,
            .page-body .card-body {
                padding: .7rem;
            }

            .page-body .table {
                font-size: .77rem;
            }

            .page-body .table > :not(caption) > * > * {
                padding: .35rem .45rem;
            }
        }

        @media print {
            .page-body .card-header,
            .page-body .card-body {
                padding: .35rem;
            }
        }
    </style>
    <style>
        @import url("https://rsms.me/inter/inter.css");
    </style>
</head>

@php
    // Beberapa halaman (termasuk Maintenance Composer) tidak mengirim $spref.
    // Untuk halaman backend tanpa prefix, gunakan guard web-admin sebagai default.
    $spref = $spref ?? 'web-admin.';

    // Maintenance Composer dan beberapa halaman backend tidak selalu menerima $webs.
    // Ambil pengaturan web sebagai fallback agar logo/layout tetap dapat dirender.
    if (! isset($webs) || ! $webs) {
        try {
            $webs = \App\Models\Pengaturan\WebSetting::query()->first();
        } catch (\Throwable $e) {
            $webs = null;
        }
    }

    /*
     * Gunakan identitas dari guard yang memang memiliki prefix halaman.
     * Ini mencegah variabel $user dari child view/loop menimpa identitas
     * akun yang sedang login (misalnya Administrator berubah menjadi Staff/Operator).
     */
    $layoutUser = match (true) {
        in_array($spref ?? '', ['web-admin.', 'akademik.', 'finance.', 'kemahasiswaan.', 'it.', 'library.', 'umum.', 'admisi.'], true)
            => \Illuminate\Support\Facades\Auth::guard('web')->user(),
        $spref === 'dosen.'
            => \Illuminate\Support\Facades\Auth::guard('dosen')->user(),
        $spref === 'mahasiswa.'
            => \Illuminate\Support\Facades\Auth::guard('mahasiswa')->user(),
        default => $user ?? null,
    };
@endphp

<body class="layout-fluid" data-bs-theme="light">
<div class="page">
    <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="navbar-brand navbar-brand-autodark">
                <a href="{{ route('root.home-index') }}" aria-label="STIT Darul Ilmi Tasikmalaya">
                    <img src="/storage/images/logo/logo-hori.png?v={{ @filemtime(storage_path('images/logo/logo-hori.png')) }}" style="height: 64px; width:200px; object-fit:contain;" alt="Logo STIT Darul Ilmi" loading="eager" onerror="this.onerror=null;this.src='/storage/images/logo/logo-vert.png?v={{ @filemtime(storage_path('images/logo/logo-vert.png')) }}';">
                </a>
            </div>

            <div class="collapse navbar-collapse" id="sidebar-menu">
                @include('core-themes.components.navbar-menu', ['layoutUser' => $layoutUser])
            </div>
        </div>
    </aside>

    <header class="navbar navbar-expand-md d-none d-lg-flex d-print-none">
        <div class="container-xl">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="navbar-nav flex-row order-md-last align-items-center gap-2">
                <a href="{{ route('root.home-index') }}" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1" aria-label="Buka Homepage">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12l-1 0a1 1 0 0 1 -.7 -1.7l7.3 -7.3l7.3 7.3a1 1 0 0 1 -.7 1.7h-1.2v6a2 2 0 0 1 -2 2h-2v-5h-4v5h-2a2 2 0 0 1 -2 -2v-6z"/>
                    </svg>
                    <span class="d-none d-xl-inline">Homepage</span>
                </a>

                @if($layoutUser)
                    <a href="{{ route($spref . 'handle-logout') }}" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" aria-label="Keluar dari Web Admin">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M14 8v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v16a2 2 0 0 0 2 2h6a2 2 0 0 0 2 -2v-4"/>
                            <path d="M8 12h13l-3 -3"/>
                            <path d="M18 15l3 -3"/>
                        </svg>
                        <span class="d-none d-xl-inline">Logout</span>
                    </a>
                @endif

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown" aria-label="Buka menu pengguna">
                        <span class="avatar avatar-sm p-0 overflow-hidden profile-navbar-avatar"><img src="{{ $layoutUser ? $layoutUser->photo : stit_profile_image_url(null) }}" alt="{{ $layoutUser?->name ?? 'Pengguna' }}" class="w-100 h-100" style="object-fit:cover;display:block;" loading="eager" onerror="this.onerror=null;this.src='{{ stit_profile_image_url(null) }}';"></span>
                        <div class="d-none d-xl-block ps-2">
                            <div>{{ $layoutUser == null ? 'Pengguna' : $layoutUser->name }}</div>
                            <div class="mt-1 small text-secondary">{{ $layoutUser == null ? '' : $layoutUser->type }}</div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        @if($layoutUser)
                            <a href="{{ route($spref . 'profile-render') }}" class="dropdown-item">Profil</a>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route($spref . 'handle-logout') }}" class="dropdown-item">Keluar</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <div class="page-header d-print-none" aria-label="Page header">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">{{ $layoutMenus }}</div>
                        <h2 class="page-title">{{ $layoutPages }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl">
                @include('core-themes.components.alerts')
                @yield('content')
                @include('sweetalert::alert')
            </div>
        </div>

        <footer class="footer footer-transparent d-print-none">
            <div class="container-xl">
                <div class="row text-center align-items-center">
                    <div class="col-12">
                        <div class="text-secondary">
                            Copyright &copy; {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                            Siakad STIT Darul Ilmi. Seluruh hak cipta dilindungi.
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

@RegisterServiceWorkerScript
<script src="{{ asset('dashboard') }}/libs/apexcharts/dist/apexcharts.min.js" defer></script>
<script src="{{ asset('dashboard') }}/libs/jsvectormap/dist/jsvectormap.min.js" defer></script>
<script src="{{ asset('dashboard') }}/libs/jsvectormap/dist/maps/world.js" defer></script>
<script src="{{ asset('dashboard') }}/libs/jsvectormap/dist/maps/world-merc.js" defer></script>
<script src="{{ asset('dashboard') }}/dist/js/tabler.min.js" defer></script>
@yield('custom-js')
</body>
</html>
