<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicReportController extends Controller
{
    /**
     * Daftar 17 Kecamatan di Kabupaten Brebes.
     */
    protected array $subdistricts = [
        'Banjarharjo', 'Bantarkawung', 'Brebes', 'Bulakamba', 'Bumiayu',
        'Jatibarang', 'Kersana', 'Ketanggungan', 'Larangan', 'Losari',
        'Paguyangan', 'Salem', 'Sirampog', 'Songgom', 'Tanjung',
        'Tonjong', 'Wanasari'
    ];

    /**
     * Tampilkan formulir pengiriman laporan warga.
     */
    public function create(): View
    {
        return view('public.report-create', [
            'subdistricts' => $this->subdistricts,
        ]);
    }

    /**
     * Simpan laporan baru dari warga.
     */
    public function store(Request $request): RedirectResponse
    {
        // Normalisasi kecamatan otomatis dari reverse geocoding map
        $rawSubdistrict = $request->input('subdistrict');
        $matchedSubdistrict = null;
        if ($rawSubdistrict) {
            foreach ($this->subdistricts as $sub) {
                if (stripos($rawSubdistrict, $sub) !== false) {
                    $matchedSubdistrict = $sub;
                    break;
                }
            }
        }
        $subdistrict = $matchedSubdistrict ?? ($rawSubdistrict ?: 'Brebes');

        // Normalisasi desa/kelurahan otomatis
        $village = trim((string) $request->input('village'));
        if (empty($village)) {
            $village = 'Wilayah ' . $subdistrict;
        }

        // Satukan kolom detail patokan dengan deskripsi
        $description = trim((string) $request->input('description'));
        $addressDetail = trim((string) $request->input('address_detail'));
        if (empty($addressDetail)) {
            $addressDetail = $description;
        }

        $request->merge([
            'subdistrict' => $subdistrict,
            'village' => $village,
            'address_detail' => $addressDetail,
        ]);

        $validated = $request->validate([
            'reporter_name' => ['required', 'string', 'max:100'],
            'reporter_phone' => ['required', 'string', 'max:30'],
            'subdistrict' => ['required', 'string', 'max:100'],
            'village' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'description' => ['required', 'string', 'max:1500'],
            'photos' => ['required', 'array', 'min:1', 'max:3'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // maks 5MB per gambar
        ], [
            'reporter_name.required' => 'Nama pelapor wajib diisi.',
            'reporter_phone.required' => 'Nomor HP wajib diisi.',
            'latitude.required' => 'Titik lokasi sampah wajib ditentukan pada peta (klik tombol "Lokasi Saat Ini" atau tentukan pin pada peta).',
            'latitude.numeric' => 'Format titik koordinat latitude tidak valid.',
            'longitude.required' => 'Titik lokasi sampah wajib ditentukan pada peta (klik tombol "Lokasi Saat Ini" atau tentukan pin pada peta).',
            'longitude.numeric' => 'Format titik koordinat longitude tidak valid.',
            'description.required' => 'Deskripsi dan patokan lokasi wajib diisi.',
            'photos.required' => 'Minimal 1 foto bukti tumpukan sampah wajib diunggah.',
            'photos.min' => 'Minimal 1 foto bukti tumpukan sampah wajib diunggah.',
            'photos.max' => 'Maksimal 3 foto yang dapat diunggah.',
            'photos.*.image' => 'File yang diunggah harus berupa gambar.',
            'photos.*.mimes' => 'Format gambar harus berupa JPG, JPEG, PNG, atau WEBP.',
            'photos.*.max' => 'Ukuran masing-masing foto maksimal 5 MB.',
        ]);

        // Simpan file foto-foto ke storage public/reports
        $storedPhotos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photoFile) {
                $storedPhotos[] = $photoFile->store('reports', 'public');
            }
        }

        // Generate kode tiket unik: LPR-YYMMDD-XXXX
        $ticketCode = 'LPR-' . date('ymd') . '-' . strtoupper(Str::random(5));

        $report = Report::create([
            'ticket_code' => $ticketCode,
            'reporter_name' => $validated['reporter_name'],
            'reporter_phone' => $validated['reporter_phone'],
            'subdistrict' => $validated['subdistrict'],
            'village' => $validated['village'],
            'address_detail' => $validated['description'], // Disatukan dengan deskripsi & patokan lokasi
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'description' => $validated['description'],
            'photo_path' => $storedPhotos[0] ?? '',
            'photos' => $storedPhotos,
            'status' => 'menunggu',
        ]);

        return redirect()->route('report.success', ['ticket' => $ticketCode]);
    }

    /**
     * Halaman sukses submit laporan.
     */
    public function success(string $ticket): View
    {
        $report = Report::where('ticket_code', $ticket)->firstOrFail();

        return view('public.report-success', compact('report'));
    }

    /**
     * Halaman pencarian dan pelacakan status tiket.
     */
    public function checkStatus(Request $request): View
    {
        $ticket = trim($request->query('ticket', ''));
        $report = null;

        if (!empty($ticket)) {
            $report = Report::where('ticket_code', $ticket)
                ->with(['wastePoint', 'verifiedByUser'])
                ->first();
        }

        return view('public.check-status', compact('ticket', 'report'));
    }
}
