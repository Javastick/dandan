<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Penanganan Sampah Liar - Kab. Brebes')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <!-- Navbar Navigasi Utama -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('home') }}">
                <i class="bi bi-trash2-fill"></i>
                <span>Dandan Sampah Brebes</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active fw-semibold' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('report.create') ? 'active fw-semibold' : '' }}" href="{{ route('report.create') }}">
                            <i class="bi bi-megaphone me-1"></i> Buat Laporan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('report.status') ? 'active fw-semibold' : '' }}" href="{{ route('report.status') }}">
                            <i class="bi bi-search me-1"></i> Lacak Tiket
                        </a>
                    </li>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item ms-lg-2">
                                <a class="btn btn-warning btn-sm text-dark fw-bold px-3 shadow-sm" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Panel Admin
                                </a>
                            </li>
                        @endif
                        <li class="nav-item ms-lg-2">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-light btn-sm text-success fw-bold px-3 shadow-sm" href="{{ route('login') }}">
                                <i class="bi bi-shield-lock me-1"></i> Login Admin
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Notifikasi Flash Message -->
    <div class="container mt-3">
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

    <!-- Konten Utama Halaman -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-3 mt-auto">
        <div class="container text-center text-muted small">
            &copy; {{ date('Y') }} Sistem Pelaporan dan Penentuan Prioritas Penanganan Sampah Liar (Metode AHP) &bull; Kabupaten Brebes
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
