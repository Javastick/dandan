@extends('layouts.app')

@section('title', 'Lapor Sampah Liar - Kabupaten Brebes')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-success text-white p-3 rounded-3">
                            <i class="bi bi-megaphone-fill fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">Formulir Laporan Sampah Liar</h4>
                            <p class="text-muted small mb-0">
                                Sampaikan aduan tumpukan sampah liar di wilayah Kabupaten Brebes untuk diverifikasi dan diprioritaskan penanganannya.
                            </p>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>
                            <strong>Mode Demonstrasi Prototipe:</strong> Anda dapat mengunggah foto tumpukan sampah nyata atau gambar sampel untuk menguji alur verifikasi sistem.
                        </div>
                    </div>

                    <form action="{{ route('report.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Identitas Pelapor -->
                        <div class="card bg-light border-0 p-3 mb-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person me-2"></i>Identitas Pelapor</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="reporter_name" class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="reporter_name" 
                                           id="reporter_name" 
                                           class="form-control @error('reporter_name') is-invalid @enderror" 
                                           value="{{ old('reporter_name') }}" 
                                           placeholder="Contoh: Budi Santoso" 
                                           required>
                                    @error('reporter_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="reporter_phone" class="form-label small fw-semibold">No. HP / WhatsApp <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="reporter_phone" 
                                           id="reporter_phone" 
                                           class="form-control @error('reporter_phone') is-invalid @enderror" 
                                           value="{{ old('reporter_phone') }}" 
                                           placeholder="Contoh: 081234567890" 
                                           required>
                                    @error('reporter_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Lokasi Sampah Liar -->
                        <div class="card bg-light border-0 p-3 mb-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-geo-alt me-2"></i>Lokasi Tumpukan Sampah</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="subdistrict" class="form-label small fw-semibold">Kecamatan <span class="text-danger">*</span></label>
                                    <select name="subdistrict" id="subdistrict" class="form-select @error('subdistrict') is-invalid @enderror" required>
                                        <option value="">-- Pilih Kecamatan --</option>
                                        @foreach($subdistricts as $kec)
                                            <option value="{{ $kec }}" {{ old('subdistrict') === $kec ? 'selected' : '' }}>
                                                {{ $kec }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subdistrict')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="village" class="form-label small fw-semibold">Desa / Kelurahan <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="village" 
                                           id="village" 
                                           class="form-control @error('village') is-invalid @enderror" 
                                           value="{{ old('village') }}" 
                                           placeholder="Contoh: Klampok" 
                                           required>
                                    @error('village')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-0">
                                <label for="address_detail" class="form-label small fw-semibold">Detail Patokan Lokasi <span class="text-danger">*</span></label>
                                <textarea name="address_detail" 
                                          id="address_detail" 
                                          rows="2" 
                                          class="form-control @error('address_detail') is-invalid @enderror" 
                                          placeholder="Contoh: Bahu jalan sebelah timur jembatan rel KA, 50 meter di belakang Pasar Seng" 
                                          required>{{ old('address_detail') }}</textarea>
                                @error('address_detail')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Bukti Foto dan Deskripsi -->
                        <div class="card bg-light border-0 p-3 mb-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-camera me-2"></i>Foto Bukti & Kondisi Sampah</h6>
                            
                            <div class="mb-3">
                                <label for="photo" class="form-label small fw-semibold">Unggah Foto Sampah Liar <span class="text-danger">*</span></label>
                                <input type="file" 
                                       name="photo" 
                                       id="photo" 
                                       class="form-control @error('photo') is-invalid @enderror" 
                                       accept="image/*" 
                                       required>
                                <div class="form-text small">Format gambar JPG, PNG, atau WEBP. Ukuran maksimal 5 MB.</div>
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="description" class="form-label small fw-semibold">Keterangan Kondisi Sampah <span class="text-danger">*</span></label>
                                <textarea name="description" 
                                          id="description" 
                                          rows="3" 
                                          class="form-control @error('description') is-invalid @enderror" 
                                          placeholder="Jelaskan kondisi tumpukan (bau, jenis sampah plastik/limbah, dampak bagi warga sekitar)" 
                                          required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success px-4 py-2 fw-semibold">
                                <i class="bi bi-send-fill me-1"></i> Kirim Laporan Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
