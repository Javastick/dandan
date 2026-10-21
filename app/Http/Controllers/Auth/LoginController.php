<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Tampilkan formulir login.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            if (Auth::user()->isOfficer()) {
                return redirect()->route('officer.dashboard');
            }
            return redirect('/');
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi login pengguna.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            if (Auth::user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Selamat datang kembali, Admin ' . Auth::user()->name . '!');
            }

            if (Auth::user()->isOfficer()) {
                return redirect()->intended(route('officer.dashboard'))
                    ->with('success', 'Selamat datang, Petugas ' . Auth::user()->name . '!');
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun Anda tidak memiliki hak akses sistem.',
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan tidak sesuai.',
        ])->onlyInput('email');
    }

    /**
     * Proses logout dan invalidasi session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
