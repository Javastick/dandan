@extends('layouts.app')

@section('title', 'Login Administrator - Dandan Sampah Brebes')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-success-subtle text-success d-inline-flex p-3 rounded-circle mb-2">
                            <i class="bi bi-person-lock fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Login Admin</h4>
                        <p class="text-muted small">Akses Panel Pengelolaan & Perhitungan AHP</p>
                    </div>

                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" 
                                       placeholder="nama@instansi.go.id" 
                                       required 
                                       autofocus>
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label small fw-semibold">Kata Sandi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="••••••••" 
                                       required>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="remember">
                                Ingat sesi login saya
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-semibold py-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
                        </button>
                    </form>

                    <div class="card bg-light border-0 mt-4 p-3 small text-muted">
                        <div class="fw-semibold text-dark mb-2"><i class="bi bi-info-circle me-1"></i> Akun Pengujian:</div>
                        <div class="mb-2 pb-2 border-bottom">
                            <span class="badge bg-dark me-1">ADMINISTRATOR</span>
                            <div>Email: <code>admin@dandan.test</code></div>
                            <div>Password: <code>admin123</code></div>
                        </div>
                        <div>
                            <span class="badge bg-success me-1">PETUGAS LAPANGAN</span>
                            <div>Email: <code>petugas@dandan.test</code></div>
                            <div>Password: <code>petugas123</code></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
