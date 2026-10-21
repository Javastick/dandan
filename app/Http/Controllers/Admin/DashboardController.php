<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criterion;
use App\Models\Report;
use App\Models\WastePoint;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dasbor utama admin dengan statistik dan antrian prioritas.
     */
    public function index(): View
    {
        $stats = [
            'criteria_count' => Criterion::count(),
            'waste_points_count' => WastePoint::count(),
            'pending_reports_count' => Report::where('status', 'menunggu')->count(),
            'verified_points_count' => WastePoint::where('status', 'terverifikasi')->count(),
            'in_progress_points_count' => WastePoint::where('status', 'diproses')->count(),
            'resolved_points_count' => WastePoint::where('status', 'selesai')->count(),
            'total_reports_count' => Report::count(),
        ];

        // 5 Titik Sampah Prioritas Tertinggi (AHP)
        $topPriorities = WastePoint::whereIn('status', ['terverifikasi', 'diproses'])
            ->withCount('reports')
            ->orderByRaw('CASE WHEN `rank` IS NULL THEN 1 ELSE 0 END, `rank` ASC, `total_score` DESC')
            ->take(5)
            ->get();

        // 5 Laporan Masuk Terbaru yang Perlu Diverifikasi
        $latestReports = Report::where('status', 'menunggu')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'topPriorities', 'latestReports'));
    }
}
