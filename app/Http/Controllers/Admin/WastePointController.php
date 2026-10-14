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

        $counts = [
            'semua' => WastePoint::count(),
            'terverifikasi' => WastePoint::where('status', 'terverifikasi')->count(),
            'diproses' => WastePoint::where('status', 'diproses')->count(),
            'selesai' => WastePoint::where('status', 'selesai')->count(),
        ];

        return view('admin.waste_points.index', compact('wastePoints', 'status', 'counts'));
    }

    /**
     * Tampilkan detail titik sampah, laporan warga yang tertaut, dan skor kriteria.
     */
    public function show(int $id): View
    {
        $wastePoint = WastePoint::with(['reports', 'assessments.criterion'])
            ->withCount('reports')
            ->findOrFail($id);

        $criteria = Criterion::where('is_active', true)->orderBy('code')->get();

        return view('admin.waste_points.show', compact('wastePoint', 'criteria'));
    }

    /**
     * Tampilkan formulir penilaian / penyesuaian kriteria titik sampah (1-5).
     */
    public function editAssessment(int $id): View
    {
        $wastePoint = WastePoint::with(['assessments.criterion'])->findOrFail($id);
        $criteria = Criterion::where('is_active', true)->orderBy('code')->get();
        $assessmentsMap = $wastePoint->assessments->keyBy('criterion_id');

        // Panduan operasional skala rating 1-5 untuk demonstrasi
        $rubrics = [
            'C1' => [
                'name' => 'Volume Sampah',
                1 => 'Volume sangat kecil (< 1 m³, tumpukan beberapa kantong plastik)',
                2 => 'Volume kecil (1 - 3 m³, mulai memakan sebagian bahu jalan)',
                3 => 'Volume sedang (3 - 5 m³, tumpukan memanjang di bahu jalan/saluran)',
                4 => 'Volume besar (5 - 10 m³, setara 1 muatan armada truk sedang)',
                5 => 'Volume sangat besar (> 10 m³, timbunan tinggi membutuhkan alat berat)',
            ],
            'C2' => [
                'name' => 'Dampak Lingkungan',
                1 => 'Bau tidak menyengat, berada di lahan kosong jauh dari permukiman',
                2 => 'Bau ringan, dekat persawahan namun tidak menghambat saluran air',
                3 => 'Bau menyengat tercium warga, dekat warung makan atau bahu jalan ramai',
                4 => 'Bau sangat menyengat, menyumbat aliran drainase/saluran irigasi aktif',
                5 => 'Sumber lalat/vektor penyakit, sangat dekat fasilitas umum/faskes/sekolah',
            ],
            'C3' => [
                'name' => 'Frekuensi Laporan',
                1 => '1 aduan laporan warga',
                2 => '2 aduan laporan warga',
                3 => '3 aduan laporan warga',
                4 => '4 aduan laporan warga',
                5 => '5 atau lebih aduan laporan warga yang mengonfirmasi lokasi sama',
            ],
            'C4' => [
                'name' => 'Lama Belum Ditangani',
                1 => 'Kurang dari 2 hari sejak diverifikasi',
                2 => '2 sampai 4 hari belum tertangani',
                3 => '5 sampai 7 hari belum tertangani',
                4 => '8 sampai 14 hari belum tertangani',
                5 => 'Lebih dari 14 hari menumpuk belum ditangani',
            ],
        ];

        return view('admin.waste_points.edit-assessment', compact('wastePoint', 'criteria', 'assessmentsMap', 'rubrics'));
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
            'resolution_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $photoPath = null;
        if ($request->hasFile('resolution_photo')) {
            $photoPath = $request->file('resolution_photo')->store('resolutions', 'public');
        }

        $wastePoint->update([
            'status' => $validated['status'],
        ]);

        // Jika selesai, perbarui seluruh laporan warga yang tertaut agar selesai
        if ($validated['status'] === 'selesai') {
            Report::where('waste_point_id', $wastePoint->id)->update([
                'resolution_photo' => $photoPath,
            ]);
        }

        return redirect()->route('admin.waste_points.show', $wastePoint->id)
            ->with('success', "Status penanganan titik sampah berhasil diubah menjadi: {$validated['status']}.");
    }
}
