<?php

use App\Http\Controllers\Admin\AhpPriorityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportVerificationController;
use App\Http\Controllers\Admin\WastePointController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Pelaporan & Prioritas Sampah Liar (Metode AHP) Brebes
|--------------------------------------------------------------------------
*/

// Portal Publik
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/lapor', [PublicReportController::class, 'create'])->name('report.create');
Route::post('/lapor', [PublicReportController::class, 'store'])->name('report.store');
Route::get('/lapor/sukses/{ticket}', [PublicReportController::class, 'success'])->name('report.success');
Route::get('/lacak', [PublicReportController::class, 'checkStatus'])->name('report.status');

// Autentikasi Admin
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Area Terproteksi Administrator DLH
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modul Verifikasi Laporan Masuk
    Route::get('/reports', [ReportVerificationController::class, 'index'])->name('reports.index');
    Route::get('/reports/{id}', [ReportVerificationController::class, 'show'])->name('reports.show');
    Route::post('/reports/{id}/verify', [ReportVerificationController::class, 'verify'])->name('reports.verify');

    // Modul Titik Lokasi Sampah & Penanganan
    Route::get('/waste-points', [WastePointController::class, 'index'])->name('waste_points.index');
    Route::get('/waste-points/{id}', [WastePointController::class, 'show'])->name('waste_points.show');
    Route::get('/waste-points/{id}/assessment', [WastePointController::class, 'editAssessment'])->name('waste_points.edit_assessment');
    Route::post('/waste-points/{id}/assessment', [WastePointController::class, 'updateAssessment'])->name('waste_points.update_assessment');
    Route::post('/waste-points/{id}/status', [WastePointController::class, 'updateStatus'])->name('waste_points.update_status');

    // Modul Metode AHP & Pemeringkatan Prioritas WSM
    Route::get('/ahp/matrix', [AhpPriorityController::class, 'matrix'])->name('ahp.matrix');
    Route::post('/ahp/matrix', [AhpPriorityController::class, 'updateMatrix'])->name('ahp.update_matrix');
    Route::get('/ahp/rankings', [AhpPriorityController::class, 'rankings'])->name('ahp.rankings');
    Route::post('/ahp/recalculate', [AhpPriorityController::class, 'recalculate'])->name('ahp.recalculate');
});

// Area Terproteksi Petugas Lapangan DLH
Route::middleware(['auth', 'officer'])->prefix('officer')->name('officer.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Officer\TaskController::class, 'dashboard'])->name('dashboard');
    Route::get('/tasks/{id}', [\App\Http\Controllers\Officer\TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{id}', [\App\Http\Controllers\Officer\TaskController::class, 'updateProgress'])->name('tasks.update');
});

