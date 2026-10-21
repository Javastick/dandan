<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OfficerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('warning', 'Silakan login terlebih dahulu untuk mengakses panel petugas.');
        }

        if (auth()->user()->role !== 'petugas') {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Petugas Lapangan.');
        }

        return $next($request);
    }
}
