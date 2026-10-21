<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criterion;
use App\Models\PointAssessment;
use App\Models\Report;
use App\Models\WastePoint;
use App\Services\AhpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportVerificationController extends Controller
{
    /**
     * Tampilkan daftar seluruh laporan warga untuk diverifikasi admin.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'menunggu');

        $query = Report::with(['wastePoint', 'verifiedByUser'])->latest();

        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        $reports = $query->paginate(15)->withQueryString();

        $counts = [
            'menunggu' => Report::where('status', 'menunggu')->count(),
            'diterima' => Report::where('status', 'diterima')->count(),
            'ditolak' => Report::where('status', 'ditolak')->count(),
            'semua' => Report::count(),
        ];

        return view('admin.reports.index', compact('reports', 'status', 'counts'));
    }

    /**
     * Tampilkan detail laporan dan opsi verifikasi.
     */
    public function show(int $id, \App\Services\SpatialDistanceService $spatialService): View
    {
        $report = Report::with(['wastePoint', 'verifiedByUser'])->findOrFail($id);

        // Ambil titik lokasi sampah yang sudah ada di kecamatan yang sama untuk opsi penautan
        $existingWastePoints = WastePoint::where('subdistrict', $report->subdistrict)
            ->whereIn('status', ['terverifikasi', 'diproses'])
            ->latest()
            ->get();

        $criteria = Criterion::where('is_active', true)->orderBy('code')->get();

        // Analisis spasial otomatis untuk C2 (Pemukiman) dan C3 (Sungai)
        $spatialAnalysis = null;
        if ($report->latitude && $report->longitude) {
            $spatialAnalysis = $spatialService->analyzeLocation((float) $report->latitude, (float) $report->longitude);
        }

        return view('admin.reports.show', compact('report', 'existingWastePoints', 'criteria', 'spatialAnalysis'));
    }

    /**
     * Proses keputusan verifikasi laporan (terima sebagai titik baru, tautkan ke titik ada, atau tolak).
     */
    public function verify(Request $request, int $id, AhpService $ahpService): RedirectResponse
    {
        $report = Report::findOrFail($id);
        $action = $request->input('action');

        if ($action === 'accept_new') {
            $validated = $request->validate([
                'point_name' => ['required', 'string', 'max:150'],
                'c1_volume' => ['required', 'integer', 'between:1,5'],
                'c2_settlement' => ['required', 'integer', 'between:1,5'],
                'c3_river' => ['required', 'integer', 'between:1,5'],
                'c4_frequency' => ['required', 'integer', 'between:1,5'],
            ]);

            // Buat entitas fisik titik sampah baru
            $pointCode = 'LOC-' . date('ymd') . '-' . strtoupper(Str::random(4));
            $wastePoint = WastePoint::create([
                'point_code' => $pointCode,
                'name' => $validated['point_name'],
                'subdistrict' => $report->subdistrict,
                'village' => $report->village,
                'address_detail' => $report->address_detail,
                'latitude' => $report->latitude,
                'longitude' => $report->longitude,
                'status' => 'terverifikasi',
                'total_score' => 0.0,
            ]);

            // Tautkan laporan ke titik sampah baru
            $report->update([
                'waste_point_id' => $wastePoint->id,
                'status' => 'diterima',
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            // Simpan penilaian 4 kriteria awal
            $criteria = Criterion::where('is_active', true)->get()->keyBy('code');

            $ratings = [
                'C1' => $validated['c1_volume'],
                'C2' => $validated['c2_settlement'],
                'C3' => $validated['c3_river'],
                'C4' => $validated['c4_frequency'],
            ];

            foreach ($ratings as $code => $val) {
                if (isset($criteria[$code])) {
                    PointAssessment::updateOrCreate(
                        [
                            'waste_point_id' => $wastePoint->id,
                            'criterion_id' => $criteria[$code]->id,
                        ],
                        [
                            'raw_value' => $val,
                            'normalized_score' => round($val / 5.0, 4),
                            'assessed_by' => auth()->id(),
                        ]
                    );
                }
            }

            // Hitung ulang ranking prioritas secara otomatis
            $ahpService->calculateAndRankWastePoints();

            return redirect()->route('admin.reports.show', $report->id)
                ->with('success', "Laporan berhasil diverifikasi dan dijadikan Titik Sampah Baru [{$pointCode}]. Skor prioritas telah diperbarui!");
        }

        if ($action === 'accept_existing') {
            $validated = $request->validate([
                'waste_point_id' => ['required', 'exists:waste_points,id'],
            ]);

            $wastePoint = WastePoint::findOrFail($validated['waste_point_id']);

            $report->update([
                'waste_point_id' => $wastePoint->id,
                'status' => 'diterima',
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            // Perbarui kriteria C4 (Frekuensi Laporan Masyarakat) otomatis jika ada
            $c4 = Criterion::where('code', 'C4')->first();
            if ($c4) {
                $countReports = Report::where('waste_point_id', $wastePoint->id)->where('status', 'diterima')->count();
                $c4Value = min(5, max(1, $countReports));
                PointAssessment::updateOrCreate(
                    [
                        'waste_point_id' => $wastePoint->id,
                        'criterion_id' => $c4->id,
                    ],
                    [
                        'raw_value' => $c4Value,
                        'normalized_score' => round($c4Value / 5.0, 4),
                        'assessed_by' => auth()->id(),
                    ]
                );
            }

            // Hitung ulang ranking
            $ahpService->calculateAndRankWastePoints();

            return redirect()->route('admin.reports.show', $report->id)
                ->with('success', "Laporan berhasil ditautkan ke Titik Sampah [{$wastePoint->point_code}]. Frekuensi laporan terakumulasi!");
        }

        if ($action === 'reject') {
            $validated = $request->validate([
                'rejection_reason' => ['required', 'string', 'max:500'],
            ]);

            $report->update([
                'status' => 'ditolak',
                'rejection_reason' => $validated['rejection_reason'],
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            return redirect()->route('admin.reports.show', $report->id)
                ->with('warning', 'Laporan berhasil ditolak dengan alasan yang tercatat.');
        }

        return back()->with('error', 'Aksi verifikasi tidak valid.');
    }
}
