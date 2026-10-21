@extends('layouts.app')

@section('title', 'Lapor Sampah Liar - Kabupaten Brebes')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #map {
        height: 380px;
        width: 100%;
        border-radius: 0.5rem;
        z-index: 1;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 8px;
        font-family: inherit;
    }
    .location-btn-pulse {
        animation: pulse-glow 2s infinite;
    }
    @keyframes pulse-glow {
        0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(25, 135, 84, 0); }
        100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
    }
</style>
@endpush

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
                            <strong>Petunjuk Lokasi:</strong> Cukup tekan tombol <em>"Lokasi Saat Ini"</em> atau klik pada peta. Nama desa dan kecamatan akan terisi otomatis.
                        </div>
                    </div>

                    <form action="{{ route('report.store') }}" method="POST" enctype="multipart/form-data" id="reportForm">
                        @csrf

                        <!-- Hidden Inputs untuk Koordinat & Wilayah Otomatis -->
                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                        <input type="hidden" name="subdistrict" id="subdistrict" value="{{ old('subdistrict') }}">
                        <input type="hidden" name="village" id="village" value="{{ old('village') }}">

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
                                           placeholder="Masukkan nama" 
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
                                           placeholder="Masukkan nomor hp" 
                                           required>
                                    @error('reporter_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Titik Lokasi Peta & Wilayah Otomatis -->
                        <div class="card bg-light border-0 p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-geo-alt me-2"></i>Titik Lokasi Sampah</h6>
                                <button type="button" 
                                        id="btnCurrentLocation" 
                                        class="btn btn-success btn-sm fw-semibold shadow-sm location-btn-pulse">
                                    <i class="bi bi-crosshair me-1"></i> Lokasi Saat Ini
                                </button>
                            </div>

                            <p class="text-muted small mb-3">
                                Klik tombol <strong>"Lokasi Saat Ini"</strong> agar sistem mendeteksi posisi Anda, atau ketuk/geser pin langsung pada peta.
                            </p>

                            <!-- Alert Status Geolocation -->
                            <div id="geoAlert" class="alert d-none py-2 px-3 small align-items-center gap-2 mb-3"></div>

                            <!-- Kontainer Peta Leaflet -->
                            <div class="position-relative mb-3">
                                <div id="map" class="shadow-sm border"></div>
                            </div>

                            <!-- Info Text Wilayah Terdeteksi Otomatis (Tanpa Tampilan Koordinat Rumit) -->
                            <div class="card border border-success-subtle bg-white shadow-sm p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div id="locationIconBox" class="bg-success-subtle text-success p-2 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                                        <i class="bi bi-pin-map-fill"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="text-muted small fw-semibold text-uppercase tracking-wider">Wilayah Lokasi Terdeteksi</div>
                                        <div id="locationNameDisplay" class="fw-bold text-dark fs-6">
                                            @if(old('village') || old('subdistrict'))
                                                {{ old('village', 'Desa') }}, Kec. {{ old('subdistrict', 'Brebes') }}
                                            @else
                                                Belum ditentukan
                                            @endif
                                        </div>
                                        <small id="locationHintDisplay" class="text-muted">
                                            @if(old('village') || old('subdistrict'))
                                                Wilayah tersimpan dari titik pin peta.
                                            @else
                                                Gunakan tombol "Lokasi Saat Ini" atau tentukan pin pada peta.
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>

                            @error('latitude')
                                <div class="text-danger small mt-2"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                            @error('subdistrict')
                                <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Foto Bukti & Deskripsi (Disatukan dengan Patokan Lokasi) -->
                        <div class="card bg-light border-0 p-3 mb-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-camera me-2"></i>Foto Bukti & Keterangan</h6>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">
                                    Unggah Foto Sampah <span class="text-danger">*</span> 
                                    <span class="badge bg-secondary ms-1">Maks. 3 Foto</span>
                                </label>

                                <!-- Dropzone / Box Upload Simpel -->
                                <div id="dropzoneContainer" class="border border-2 border-dashed rounded-3 p-4 text-center bg-white position-relative" style="cursor: pointer; transition: all 0.2s ease;">
                                    <input type="file" 
                                           name="photos[]" 
                                           id="photosInput" 
                                           class="d-none" 
                                           accept="image/jpeg,image/png,image/webp" 
                                           multiple>
                                    
                                    <div id="dropzonePrompt">
                                        <div class="bg-success-subtle text-success p-3 rounded-circle d-inline-flex mb-2">
                                            <i class="bi bi-cloud-arrow-up fs-3"></i>
                                        </div>
                                        <div class="fw-semibold text-dark">Klik atau seret foto ke area ini</div>
                                        <div class="text-muted small">(Format JPG, PNG, atau WEBP, maks 5 MB per foto)</div>
                                        <button type="button" class="btn btn-outline-success btn-sm mt-2 px-3 fw-semibold" id="btnSelectPhotos">
                                            <i class="bi bi-images me-1"></i> Pilih Foto
                                        </button>
                                    </div>
                                </div>

                                <!-- Galeri Preview Foto Terpilih -->
                                <div id="previewContainer" class="row g-2 mt-2 d-none">
                                    <!-- Dynamic photo cards will be inserted here -->
                                </div>

                                <div id="photoCountNotice" class="small text-muted mt-2 d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-info-circle me-1"></i>Anda dapat memilih 1 hingga 3 foto sekaligus.</span>
                                    <span id="selectedCountBadge" class="badge bg-dark d-none">0/3 Foto Terpilih</span>
                                </div>

                                @error('photos')
                                    <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                                @error('photos.*')
                                    <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="description" class="form-label small fw-semibold">Deskripsi & Patokan Lokasi <span class="text-danger">*</span></label>
                                <textarea name="description" 
                                          id="description" 
                                          rows="3" 
                                          class="form-control @error('description') is-invalid @enderror" 
                                          placeholder="Tulis deskripsi..." 
                                          required>{{ old('description') }}</textarea>
                                <div class="form-text small">Sertakan patokan tempat agar tim verifikasi mudah menemukan lokasi sampah.</div>
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

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 17 Kecamatan resmi di Kabupaten Brebes
    const BREBES_SUBDISTRICTS = [
        'Banjarharjo', 'Bantarkawung', 'Brebes', 'Bulakamba', 'Bumiayu',
        'Jatibarang', 'Kersana', 'Ketanggungan', 'Larangan', 'Losari',
        'Paguyangan', 'Salem', 'Sirampog', 'Songgom', 'Tanjung',
        'Tonjong', 'Wanasari'
    ];

    const DEFAULT_LAT = -6.8703;
    const DEFAULT_LNG = 109.0436;
    const DEFAULT_ZOOM = 12;

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const subdistrictInput = document.getElementById('subdistrict');
    const villageInput = document.getElementById('village');

    const locationNameDisplay = document.getElementById('locationNameDisplay');
    const locationHintDisplay = document.getElementById('locationHintDisplay');
    const geoAlert = document.getElementById('geoAlert');
    const btnCurrentLocation = document.getElementById('btnCurrentLocation');

    const initialLat = parseFloat(latInput.value) || DEFAULT_LAT;
    const initialLng = parseFloat(lngInput.value) || DEFAULT_LNG;
    const hasInitialCoord = Boolean(latInput.value && lngInput.value);

    // Inisialisasi Peta Leaflet
    const map = L.map('map', {
        center: [initialLat, initialLng],
        zoom: hasInitialCoord ? 16 : DEFAULT_ZOOM,
        scrollWheelZoom: true
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    let marker = null;
    let accuracyCircle = null;
    let reverseGeocodeTimer = null;

    // Fungsi pencocokan kecamatan Brebes dari teks nama tempat
    function matchBrebesSubdistrict(addressObj) {
        const candidates = [
            addressObj.municipality,
            addressObj.city_district,
            addressObj.county,
            addressObj.district,
            addressObj.town,
            addressObj.city
        ].filter(Boolean);

        for (const candidate of candidates) {
            for (const sub of BREBES_SUBDISTRICTS) {
                if (candidate.toLowerCase().includes(sub.toLowerCase())) {
                    return sub;
                }
            }
        }
        return 'Brebes'; // default fallback
    }

    // Fungsi reverse geocoding otomatis dari koordinat ke nama Desa & Kecamatan
    function reverseGeocode(lat, lng) {
        locationNameDisplay.innerHTML = '<span class="spinner-border spinner-border-sm text-success me-1"></span> Mengidentifikasi wilayah...';
        locationHintDisplay.textContent = 'Mendeteksi nama desa dan kecamatan dari titik peta...';

        if (reverseGeocodeTimer) {
            clearTimeout(reverseGeocodeTimer);
        }

        reverseGeocodeTimer = setTimeout(function () {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    const addr = data.address || {};
                    const village = addr.village || addr.suburb || addr.neighbourhood || addr.quarter || addr.hamlet || addr.town || 'Desa Terdeteksi';
                    const subdistrict = matchBrebesSubdistrict(addr);

                    subdistrictInput.value = subdistrict;
                    villageInput.value = village;

                    locationNameDisplay.textContent = `${village}, Kec. ${subdistrict}`;
                    locationHintDisplay.textContent = 'Wilayah otomatis terdeteksi berdasarkan titik pin di peta.';

                    if (marker) {
                        marker.bindPopup(`<strong>${village}</strong><br><small class="text-muted">Kecamatan ${subdistrict}</small>`).openPopup();
                    }
                })
                .catch(() => {
                    const fallbackSubdistrict = subdistrictInput.value || 'Brebes';
                    const fallbackVillage = villageInput.value || 'Wilayah ' + fallbackSubdistrict;

                    subdistrictInput.value = fallbackSubdistrict;
                    villageInput.value = fallbackVillage;

                    locationNameDisplay.textContent = `${fallbackVillage}, Kec. ${fallbackSubdistrict}`;
                    locationHintDisplay.textContent = 'Titik lokasi tersimpan di peta.';
                });
        }, 300);
    }

    // Fungsi menetapkan posisi pin
    function setLocation(lat, lng, accuracy = null, zoom = 16) {
        latInput.value = Number(lat).toFixed(7);
        lngInput.value = Number(lng).toFixed(7);

        if (!marker) {
            marker = L.marker([lat, lng], {
                draggable: true,
                title: 'Geser pin untuk memindahkan posisi sampah'
            }).addTo(map);

            marker.on('dragend', function () {
                const pos = marker.getLatLng();
                setLocation(pos.lat, pos.lng, null, null);
            });
        } else {
            marker.setLatLng([lat, lng]);
        }

        if (accuracyCircle) {
            map.removeLayer(accuracyCircle);
            accuracyCircle = null;
        }

        if (accuracy) {
            accuracyCircle = L.circle([lat, lng], {
                radius: accuracy,
                color: '#198754',
                fillColor: '#198754',
                fillOpacity: 0.15,
                weight: 1
            }).addTo(map);
        }

        if (zoom !== null) {
            map.setView([lat, lng], zoom);
        }

        // Jalankan identifikasi desa & kecamatan otomatis
        reverseGeocode(lat, lng);
    }

    // Jika ada koordinat awal (misal saat submit kembali)
    if (hasInitialCoord) {
        setLocation(initialLat, initialLng, null, 16);
    }

    // Event klik pada peta
    map.on('click', function (e) {
        setLocation(e.latlng.lat, e.latlng.lng, null, null);
        showAlert('success', '<i class="bi bi-check-circle-fill me-1"></i> Titik lokasi sampah dipilih pada peta.');
    });

    function showAlert(type, htmlContent) {
        geoAlert.className = `alert alert-${type} py-2 px-3 small d-flex align-items-center gap-2 mb-3`;
        geoAlert.innerHTML = htmlContent;
        geoAlert.classList.remove('d-none');
    }

    // Tombol "Lokasi Saat Ini"
    btnCurrentLocation.addEventListener('click', function () {
        if (!navigator.geolocation) {
            showAlert('warning', '<i class="bi bi-exclamation-triangle-fill me-1"></i> Perangkat tidak mendukung GPS Geolocation. Silakan ketuk langsung pada peta.');
            return;
        }

        const originalBtnHtml = btnCurrentLocation.innerHTML;
        btnCurrentLocation.disabled = true;
        btnCurrentLocation.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mencari GPS...';
        showAlert('info', '<i class="bi bi-arrow-repeat bi-spin me-1"></i> Sedang mendeteksi sinyal GPS lokasi Anda...');

        navigator.geolocation.getCurrentPosition(
            function (position) {
                btnCurrentLocation.disabled = false;
                btnCurrentLocation.innerHTML = originalBtnHtml;

                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const acc = Math.round(position.coords.accuracy);

                setLocation(lat, lng, acc, 17);
                showAlert('success', `<i class="bi bi-check-circle-fill me-1"></i> Lokasi GPS berhasil dideteksi (Akurasi: ±${acc} meter). Wilayah otomatis diperbarui!`);
            },
            function (error) {
                btnCurrentLocation.disabled = false;
                btnCurrentLocation.innerHTML = originalBtnHtml;

                let errMsg = 'Gagal mendeteksi lokasi GPS.';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errMsg = 'Izin akses lokasi ditolak. Silakan aktifkan izin lokasi browser atau ketuk langsung pada peta.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errMsg = 'Informasi lokasi tidak tersedia. Silakan ketuk langsung pada peta.';
                        break;
                    case error.TIMEOUT:
                        errMsg = 'Waktu pencarian GPS habis. Silakan coba lagi atau ketuk langsung pada peta.';
                        break;
                }
                showAlert('warning', `<i class="bi bi-exclamation-triangle-fill me-1"></i> ${errMsg}`);
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    });

    // Validasi form saat submit
    const reportForm = document.getElementById('reportForm');
    reportForm.addEventListener('submit', function (e) {
        if (!latInput.value || !lngInput.value) {
            e.preventDefault();
            showAlert('danger', '<i class="bi bi-x-circle-fill me-1"></i> <strong>Titik lokasi belum ditentukan!</strong> Silakan klik tombol <strong>"Lokasi Saat Ini"</strong> atau ketuk titik sampah pada peta.');
            document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        if (selectedFiles.length === 0) {
            e.preventDefault();
            alert('Silakan pilih minimal 1 foto bukti tumpukan sampah.');
            document.getElementById('dropzoneContainer').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
    });

    // ----------------------------------------------------
    // Logika Pengunggahan & Pratinjau Foto (Maksimal 3 Foto)
    // ----------------------------------------------------
    const dropzoneContainer = document.getElementById('dropzoneContainer');
    const photosInput = document.getElementById('photosInput');
    const btnSelectPhotos = document.getElementById('btnSelectPhotos');
    const previewContainer = document.getElementById('previewContainer');
    const selectedCountBadge = document.getElementById('selectedCountBadge');

    let selectedFiles = []; // Menyimpan file terpilih

    function updatePhotosInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        photosInput.files = dt.files;

        // Render preview
        renderPreview();
    }

    function renderPreview() {
        previewContainer.innerHTML = '';

        if (selectedFiles.length === 0) {
            previewContainer.classList.add('d-none');
            selectedCountBadge.classList.add('d-none');
            dropzoneContainer.classList.remove('d-none');
            return;
        }

        previewContainer.classList.remove('d-none');
        selectedCountBadge.classList.remove('d-none');
        selectedCountBadge.textContent = `${selectedFiles.length}/3 Foto Terpilih`;

        // Sembunyikan dropzone jika sudah mencapai 3 foto
        if (selectedFiles.length >= 3) {
            dropzoneContainer.classList.add('d-none');
        } else {
            dropzoneContainer.classList.remove('d-none');
        }

        selectedFiles.forEach((file, index) => {
            const col = document.createElement('div');
            col.className = 'col-4';

            const card = document.createElement('div');
            card.className = 'card border shadow-sm position-relative h-100 overflow-hidden';

            const img = document.createElement('img');
            img.className = 'card-img-top';
            img.style.height = '110px';
            img.style.objectFit = 'cover';
            img.src = URL.createObjectURL(file);

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center shadow';
            removeBtn.style.width = '24px';
            removeBtn.style.height = '24px';
            removeBtn.innerHTML = '<i class="bi bi-x fs-6"></i>';
            removeBtn.title = 'Hapus foto';
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                selectedFiles.splice(index, 1);
                updatePhotosInput();
            });

            const cardBody = document.createElement('div');
            cardBody.className = 'card-body p-1 text-center bg-white';
            const sizeInMb = (file.size / (1024 * 1024)).toFixed(1);
            cardBody.innerHTML = `<small class="text-muted d-block text-truncate" style="font-size: 11px;">Foto ${index + 1} (${sizeInMb} MB)</small>`;

            card.appendChild(img);
            card.appendChild(removeBtn);
            card.appendChild(cardBody);
            col.appendChild(card);
            previewContainer.appendChild(col);
        });
    }

    function handleIncomingFiles(fileList) {
        const remainingSlots = 3 - selectedFiles.length;
        if (remainingSlots <= 0) {
            alert('Maksimal hanya dapat mengunggah 3 foto.');
            return;
        }

        const validFiles = Array.from(fileList).filter(file => {
            if (!file.type.match(/^image\/(jpeg|png|webp)$/i)) {
                alert(`File ${file.name} bukan format gambar yang didukung (JPG, PNG, WEBP).`);
                return false;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert(`Ukuran foto ${file.name} melebihi batas 5 MB.`);
                return false;
            }
            return true;
        });

        if (validFiles.length > remainingSlots) {
            alert(`Hanya ${remainingSlots} foto pertama yang akan ditambahkan karena batas maksimal 3 foto.`);
        }

        const toAdd = validFiles.slice(0, remainingSlots);
        selectedFiles = selectedFiles.concat(toAdd);
        updatePhotosInput();
    }

    dropzoneContainer.addEventListener('click', () => photosInput.click());
    btnSelectPhotos.addEventListener('click', (e) => {
        e.stopPropagation();
        photosInput.click();
    });

    photosInput.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            handleIncomingFiles(this.files);
        }
    });

    // Drag and Drop
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzoneContainer.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzoneContainer.classList.add('border-success', 'bg-light');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzoneContainer.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzoneContainer.classList.remove('border-success', 'bg-light');
        });
    });

    dropzoneContainer.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        if (dt && dt.files && dt.files.length > 0) {
            handleIncomingFiles(dt.files);
        }
    });

    setTimeout(function () {
        map.invalidateSize();
    }, 400);
});
</script>
@endpush
