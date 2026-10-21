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
use Illuminate\View\View;

class WastePointController extends Controller
{
    /**
     * Tampilkan daftar titik lokasi sampah liar.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'semua');

        $query = WastePoint::with(['reports', 'assessments.criterion'])
            ->withCount('reports');

        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        // Urutkan berdasarkan rank jika ada, atau skor tertinggi
        $wastePoints = $query->orderByRaw('CASE WHEN `rank` IS NULL THEN 1 ELSE 0 END, `rank` ASC, `total_score` DESC')
            ->paginate(15)
            ->withQueryString();

        // Ambil semua titik sampah yang memiliki koordinat untuk ditampilkan pada peta interaktif
        $mapPointsQuery = WastePoint::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['assignedOfficer'])
            ->withCount('reports');

        if ($status !== 'semua') {
            $mapPointsQuery->where('status', $status);
        }

        $mapPoints = $mapPointsQuery->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'point_code' => $p->point_code,
                'name' => $p->name,
                'subdistrict' => $p->subdistrict,
                'village' => $p->village,
                'address_detail' => $p->address_detail,
                'latitude' => (float) $p->latitude,
                'longitude' => (float) $p->longitude,
                'status' => $p->status,
                'total_score' => (float) $p->total_score,
                'rank' => $p->rank,
                'reports_count' => $p->reports_count,
                'officer_name' => $p->assignedOfficer->name ?? null,
                'url' => route('admin.waste_points.show', $p->id),
            ];
        });

        $counts = [
            'semua' => WastePoint::count(),
            'terverifikasi' => WastePoint::where('status', 'terverifikasi')->count(),
            'diproses' => WastePoint::where('status', 'diproses')->count(),
            'selesai' => WastePoint::where('status', 'selesai')->count(),
        ];

        // Muat layer spasial sungai dan pemukiman Brebes
        $riversGeoJson = [];
        $settlementsGeoJson = [];
        $riversPath = storage_path('gis/brebes_rivers.geojson');
        $settlementsPath = storage_path('gis/brebes_settlements.geojson');
        if (file_exists($riversPath)) {
            $riversGeoJson = json_decode(file_get_contents($riversPath), true);
        }
        if (file_exists($settlementsPath)) {
            $settlementsGeoJson = json_decode(file_get_contents($settlementsPath), true);
        }

        return view('admin.waste_points.index', compact('wastePoints', 'status', 'counts', 'mapPoints', 'riversGeoJson', 'settlementsGeoJson'));
    }

    /**
     * Tampilkan detail titik sampah, laporan warga yang tertaut, dan skor kriteria.
     */
    public function show(int $id, \App\Services\SpatialDistanceService $spatialService): View
    {
        $wastePoint = WastePoint::with(['reports', 'assessments.criterion'])
            ->withCount('reports')
            ->findOrFail($id);

        $criteria = Criterion::where('is_active', true)->orderBy('code')->get();
        $officers = \App\Models\User::where('role', 'petugas')->orderBy('name')->get();

        $spatialAnalysis = null;
        if ($wastePoint->latitude && $wastePoint->longitude) {
            $spatialAnalysis = $spatialService->analyzeLocation((float) $wastePoint->latitude, (float) $wastePoint->longitude);
        }

        return view('admin.waste_points.show', compact('wastePoint', 'criteria', 'officers', 'spatialAnalysis'));
    }

    /**
     * Tampilkan formulir penilaian / penyesuaian kriteria titik sampah (1-5).
     */
    public function editAssessment(int $id, \App\Services\SpatialDistanceService $spatialService): View
    {
        $wastePoint = WastePoint::with(['assessments.criterion'])->findOrFail($id);
        $criteria = Criterion::where('is_active', true)->orderBy('code')->get();
        $assessmentsMap = $wastePoint->assessments->keyBy('criterion_id');

        // Analisis spasial rekomendasi jarak SIG
        $spatialAnalysis = null;
        if ($wastePoint->latitude && $wastePoint->longitude) {
            $spatialAnalysis = $spatialService->analyzeLocation((float) $wastePoint->latitude, (float) $wastePoint->longitude);
        }

        // Panduan operasional skala rating 1-5 untuk demonstrasi
        $rubrics = [
            'C1' => [
                'name' => 'Volume Sampah',
                1 => 'Sangat Kecil (< 1 m³, tumpukan beberapa kantong plastik)',
                2 => 'Kecil (1 - 3 m³, mulai memakan sebagian bahu jalan)',
                3 => 'Sedang (3 - 5 m³, tumpukan memanjang di bahu jalan/saluran)',
                4 => 'Besar (5 - 10 m³, setara 1 muatan armada truk sedang)',
                5 => 'Sangat Besar (> 10 m³, timbunan tinggi membutuhkan alat berat)',
            ],
            'C2' => [
                'name' => 'Jarak dari Pemukiman',
                1 => 'Sangat Jauh (> 500 meter dari pemukiman terdekat)',
                2 => 'Jauh (250 s.d. 500 meter dari kawasan permukiman)',
                3 => 'Sedang (100 s.d. 250 meter dari kawasan permukiman/jalan)',
                4 => 'Dekat (50 s.d. 100 meter dari rumah warga)',
                5 => 'Sangat Dekat (< 50 meter / persis berdampingan rumah warga)',
            ],
            'C3' => [
                'name' => 'Jarak ke Sungai',
                1 => 'Sangat Jauh (> 500 meter dari sempadan sungai/badan air)',
                2 => 'Jauh (250 s.d. 500 meter dari sempadan saluran air)',
                3 => 'Sedang (100 s.d. 250 meter dari saluran irigasi/sungai)',
                4 => 'Dekat (50 s.d. 100 meter dari tepi sempadan air)',
                5 => 'Sangat Dekat (< 50 meter / berada tepat di bantaran atau saluran air)',
            ],
            'C4' => [
                'name' => 'Frekuensi Laporan Masyarakat',
                1 => '1 aduan laporan warga',
                2 => '2 aduan laporan warga',
                3 => '3 aduan laporan warga',
                4 => '4 aduan laporan warga',
                5 => '5 atau lebih aduan laporan warga yang mengonfirmasi lokasi sama',
            ],
        ];

        return view('admin.waste_points.edit-assessment', compact('wastePoint', 'criteria', 'assessmentsMap', 'rubrics', 'spatialAnalysis'));
    }

    /**
     * Simpan pembaruan nilai skala kriteria dan hitung ulang skor WSM.
     */
    public function updateAssessment(Request $request, int $id, AhpService $ahpService): RedirectResponse
    {
        $wastePoint = WastePoint::findOrFail($id);
        $criteria = Criterion::where('is_active', true)->get();

        $rules = [];
        foreach ($criteria as $criterion) {
            $rules['rating_' . $criterion->code] = ['required', 'integer', 'between:1,5'];
        }

        $validated = $request->validate($rules);

        foreach ($criteria as $criterion) {
            $val = $validated['rating_' . $criterion->code];
            PointAssessment::updateOrCreate(
                [
                    'waste_point_id' => $wastePoint->id,
                    'criterion_id' => $criterion->id,
                ],
                [
                    'raw_value' => $val,
                    'normalized_score' => round($val / 5.0, 4),
                    'assessed_by' => auth()->id(),
                ]
            );
        }

        // Hitung ulang ranking prioritas seluruh titik sampah aktif
        $ahpService->calculateAndRankWastePoints();

        return redirect()->route('admin.waste_points.show', $wastePoint->id)
            ->with('success', 'Nilai kriteria berhasil diperbarui dan peringkat prioritas telah dihitung ulang!');
    }

    /**
     * Perbarui status penanganan titik sampah (terverifikasi -> diproses -> selesai).
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $wastePoint = WastePoint::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:terverifikasi,diproses,selesai'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'handling_notes' => ['nullable', 'string', 'max:1000'],
            'resolution_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $updateData = [
            'status' => $validated['status'],
        ];

        if ($request->has('assigned_to')) {
            $updateData['assigned_to'] = $validated['assigned_to'];
        }

        if (!empty($validated['handling_notes'])) {
            $updateData['handling_notes'] = $validated['handling_notes'];
        }

        if ($request->hasFile('resolution_photo')) {
            $photoPath = $request->file('resolution_photo')->store('resolutions', 'public');
            $updateData['resolution_photo'] = $photoPath;
        }

        if ($validated['status'] === 'selesai' && !$wastePoint->handled_at) {
            $updateData['handled_at'] = now();
        }

        $wastePoint->update($updateData);

        // Jika selesai, perbarui seluruh laporan warga yang tertaut agar selesai
        if ($validated['status'] === 'selesai') {
            Report::where('waste_point_id', $wastePoint->id)->update([
                'resolution_photo' => $wastePoint->resolution_photo,
            ]);
        }

        return redirect()->route('admin.waste_points.show', $wastePoint->id)
            ->with('success', "Status penanganan titik sampah berhasil diubah menjadi: {$validated['status']}.");
    }
}
