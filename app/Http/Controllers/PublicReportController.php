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
        $validated = $request->validate([
            'reporter_name' => ['required', 'string', 'max:100'],
            'reporter_phone' => ['required', 'string', 'max:30'],
            'subdistrict' => ['required', 'string', 'in:' . implode(',', $this->subdistricts)],
            'village' => ['required', 'string', 'max:100'],
            'address_detail' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:1000'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // maks 5MB
        ], [
            'reporter_name.required' => 'Nama pelapor wajib diisi.',
            'reporter_phone.required' => 'Nomor kontak / WhatsApp wajib diisi.',
            'subdistrict.required' => 'Kecamatan wajib dipilih.',
            'village.required' => 'Desa atau Kelurahan wajib diisi.',
            'address_detail.required' => 'Patokan alamat lokasi wajib dijelaskan.',
            'description.required' => 'Keterangan kondisi sampah wajib diisi.',
            'photo.required' => 'Foto bukti tumpukan sampah wajib diunggah.',
            'photo.image' => 'File yang diunggah harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        // Simpan file foto ke storage public/reports
        $path = $request->file('photo')->store('reports', 'public');

        // Generate kode tiket unik: LPR-YYMMDD-XXXX
        $ticketCode = 'LPR-' . date('ymd') . '-' . strtoupper(Str::random(5));

        $report = Report::create([
            'ticket_code' => $ticketCode,
            'reporter_name' => $validated['reporter_name'],
            'reporter_phone' => $validated['reporter_phone'],
            'subdistrict' => $validated['subdistrict'],
            'village' => $validated['village'],
            'address_detail' => $validated['address_detail'],
            'description' => $validated['description'],
            'photo_path' => $path,
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
