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
                    <img src="{{ $webs->school_logo_hori }}" style="height: 64px; width:200px; object-fit:contain;" alt="Logo STIT Darul Ilmi">
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

            <div class="navbar-nav flex-row order-md-last">
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown" aria-label="Buka menu pengguna">
                        <span class="avatar avatar-sm" style="background-image: url({{ $layoutUser == null ? '' : $layoutUser->photo }})"></span>
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
                            <span class="ms-1">{{ $layoutAcademy }}</span>. Seluruh hak cipta dilindungi.
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
