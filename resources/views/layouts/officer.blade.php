<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Petugas Kebersihan - DLH Brebes')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <!-- Navbar Petugas -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container-fluid px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('officer.dashboard') }}">
                <i class="bi bi-truck fs-5"></i>
                <span class="fw-bold">Panel Petugas Lapangan</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#officerNavbar" aria-controls="officerNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="officerNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('officer.dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('officer.dashboard') }}">
                            <i class="bi bi-list-task me-1"></i> Daftar Tugas Saya
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm text-nowrap" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Portal Publik
                        </a>
                    </li>
                    <li class="nav-item text-light small d-none d-md-block px-2">
                        <i class="bi bi-person-badge me-1"></i> {{ auth()->user()->name }}
                        <span class="badge bg-warning text-dark ms-1">PETUGAS</span>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Notifikasi Flash Message -->
    <div class="container-fluid px-lg-4 mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Konten Dashboard Petugas -->
    <main class="flex-grow-1 container-fluid px-lg-4 py-3">
        @yield('content')
    </main>

    <!-- Footer Petugas -->
    <footer class="bg-white border-top py-3 mt-auto">
        <div class="container-fluid px-lg-4 text-center text-muted small">
            &copy; {{ date('Y') }} Panel Petugas Lapangan &bull; DLH Kabupaten Brebes
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
