<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel - Penanganan Sampah Liar Brebes')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <!-- Navbar Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-shield-check text-success fs-5"></i>
                <span class="fw-bold">Panel Admin DLH Brebes</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.reports.index') }}">
                            <i class="bi bi-inbox me-1"></i> Verifikasi Laporan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.waste_points.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.waste_points.index') }}">
                            <i class="bi bi-geo-alt me-1"></i> Titik Lokasi Sampah
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.ahp.matrix') ? 'active fw-semibold' : '' }}" href="{{ route('admin.ahp.matrix') }}">
                            <i class="bi bi-diagram-3 me-1"></i> Matriks AHP
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.ahp.rankings') ? 'active fw-semibold' : '' }}" href="{{ route('admin.ahp.rankings') }}">
                            <i class="bi bi-sort-numeric-down me-1"></i> Ranking Prioritas
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
                        <i class="bi bi-person-circle me-1"></i> {{ auth()->user()->name }}
                        <span class="badge bg-success ms-1">{{ strtoupper(auth()->user()->role) }}</span>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
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

    <!-- Konten Dashboard Admin -->
    <main class="flex-grow-1 container-fluid px-lg-4 py-3">
        @yield('content')
    </main>

    <!-- Footer Admin -->
    <footer class="bg-white border-top py-3 mt-auto">
        <div class="container-fluid px-lg-4 text-center text-muted small">
            &copy; {{ date('Y') }} Sistem Penentuan Prioritas Sampah Liar (AHP-WSM) &bull; Diskusi & Bimbingan Skripsi
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
