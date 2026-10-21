<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\WastePoint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Tampilkan dasbor dan daftar tugas penanganan sampah untuk petugas yang login.
     */
    public function dashboard(Request $request): View
    {
        $officer = Auth::user();
        $status = $request->query('status', 'semua');

        $query = WastePoint::with(['reports'])
            ->withCount('reports')
            ->where('assigned_to', $officer->id);

        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        // Urutkan berdasarkan prioritas AHP tertinggi (rank 1, 2, dst)
        $tasks = $query->orderByRaw('CASE WHEN `rank` IS NULL THEN 1 ELSE 0 END, `rank` ASC, `total_score` DESC')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => WastePoint::where('assigned_to', $officer->id)->count(),
            'terverifikasi' => WastePoint::where('assigned_to', $officer->id)->where('status', 'terverifikasi')->count(),
            'diproses' => WastePoint::where('assigned_to', $officer->id)->where('status', 'diproses')->count(),
            'selesai' => WastePoint::where('assigned_to', $officer->id)->where('status', 'selesai')->count(),
        ];

        return view('officer.dashboard', compact('tasks', 'stats', 'status', 'officer'));
    }

    /**
     * Tampilkan detail tugas penanganan lapangan.
     */
    public function show(int $id): View
    {
        $officer = Auth::user();
        $task = WastePoint::with(['reports', 'assignedOfficer'])
            ->where('assigned_to', $officer->id)
            ->findOrFail($id);

        return view('officer.show', compact('task'));
    }

    /**
     * Perbarui progres penanganan oleh petugas (mulai penanganan / selesaikan tugas beserta foto bukti).
     */
    public function updateProgress(Request $request, int $id): RedirectResponse
    {
        $officer = Auth::user();
        $task = WastePoint::where('assigned_to', $officer->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:diproses,selesai'],
            'handling_notes' => ['nullable', 'string', 'max:1000'],
            'resolution_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // Jika status selesai, foto bukti penanganan wajib diisi apabila belum ada
        if ($validated['status'] === 'selesai' && !$task->resolution_photo && !$request->hasFile('resolution_photo')) {
            return back()->withErrors(['resolution_photo' => 'Foto bukti penyelesaian wajib diunggah saat menandai tugas selesai.']);
        }

        $updateData = [
            'status' => $validated['status'],
        ];

        if (!empty($validated['handling_notes'])) {
            $updateData['handling_notes'] = $validated['handling_notes'];
        }

        if ($request->hasFile('resolution_photo')) {
            $path = $request->file('resolution_photo')->store('resolutions', 'public');
            $updateData['resolution_photo'] = $path;
        }

        if ($validated['status'] === 'selesai') {
            $updateData['handled_at'] = now();
        }

        $task->update($updateData);

        // Jika selesai, perbarui status seluruh laporan warga yang tertaut ke titik ini
        if ($validated['status'] === 'selesai') {
            Report::where('waste_point_id', $task->id)->update([
                'resolution_photo' => $task->resolution_photo,
            ]);
        }

        return redirect()->route('officer.tasks.show', $task->id)
            ->with('success', 'Status penanganan tugas lapangan berhasil diperbarui!');
    }
}
