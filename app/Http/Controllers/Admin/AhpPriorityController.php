<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criterion;
use App\Models\WastePoint;
use App\Services\AhpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AhpPriorityController extends Controller
{
    /**
     * Tampilkan halaman matriks perbandingan kriteria AHP, bobot, dan konsistensi CR.
     */
    public function matrix(AhpService $ahpService): View
    {
        $criteria = Criterion::where('is_active', true)->orderBy('code')->get();
        $matrix = $ahpService->getMatrixFromDatabase();
        $calculation = $ahpService->calculateWeightsFromMatrix($matrix);

        return view('admin.ahp.matrix', compact('criteria', 'matrix', 'calculation'));
    }

    /**
     * Simpan perbaruan matriks perbandingan berpasangan kriteria.
     */
    public function updateMatrix(Request $request, AhpService $ahpService): RedirectResponse
    {
        $criteria = Criterion::where('is_active', true)->orderBy('code')->get();
        $codes = $criteria->pluck('code')->toArray();

        $inputMatrix = [];
        foreach ($codes as $row) {
            $inputMatrix[$row] = [];
            foreach ($codes as $col) {
                if ($row === $col) {
                    $inputMatrix[$row][$col] = 1.0;
                } else {
                    $val = (float) $request->input("matrix.{$row}.{$col}", 1.0);
                    $inputMatrix[$row][$col] = $val > 0 ? $val : 1.0;
                }
            }
        }

        $result = $ahpService->saveMatrixAndWeights($inputMatrix);

        if ($result['is_consistent']) {
            // Hitung ulang ranking prioritas dengan bobot baru
            $ahpService->calculateAndRankWastePoints();

            return redirect()->route('admin.ahp.matrix')
                ->with('success', "Matriks perbandingan KONSISTEN (CR = {$result['cr']} <= 0.10). Bobot baru berhasil disimpan dan prioritas telah diperbarui!");
        }

        return redirect()->route('admin.ahp.matrix')
            ->with('error', "Matriks TIDAK KONSISTEN (CR = {$result['cr']} > 0.10). Bobot tidak disimpan. Silakan perbaiki perbandingan preferensi.");
    }

    /**
     * Tampilkan tabel urutan ranking prioritas penanganan sampah (AHP-WSM).
     */
    public function rankings(AhpService $ahpService): View
    {
        $criteria = Criterion::where('is_active', true)->orderBy('code')->get();

        $wastePoints = WastePoint::whereIn('status', ['terverifikasi', 'diproses'])
            ->with(['assessments.criterion', 'reports'])
            ->orderByRaw('CASE WHEN `rank` IS NULL THEN 1 ELSE 0 END, `rank` ASC, `total_score` DESC')
            ->get();

        return view('admin.ahp.rankings', compact('criteria', 'wastePoints'));
    }

    /**
     * Pemicu manual untuk menghitung ulang seluruh skor dan pemeringkatan.
     */
    public function recalculate(AhpService $ahpService): RedirectResponse
    {
        $count = $ahpService->calculateAndRankWastePoints();

        return redirect()->route('admin.ahp.rankings')
            ->with('success', "Perhitungan prioritas berhasil dijalankan ulang untuk {$count} titik lokasi sampah!");
    }
}
