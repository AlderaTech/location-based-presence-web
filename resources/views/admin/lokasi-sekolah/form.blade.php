<x-admin.layout title="Form Lokasi">
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    @endpush

    @php
        $latitude = old('latitude', $lokasi->latitude);
        $longitude = old('longitude', $lokasi->longitude);
        $punyaTitik = filled($latitude) && filled($longitude);
        $latitudeAwal = $punyaTitik ? $latitude : -6.2;
        $longitudeAwal = $punyaTitik ? $longitude : 106.816666;
    @endphp

    <h1 class="mb-5 text-2xl font-semibold">{{ $lokasi->exists ? 'Edit Lokasi' : 'Tambah Lokasi' }}</h1>
    <form method="post" action="{{ $lokasi->exists ? route('admin.lokasi-sekolah.update', $lokasi) : route('admin.lokasi-sekolah.store') }}" class="grid gap-4 rounded-md border border-slate-200 bg-white p-4 md:grid-cols-2" data-location-form>
        @csrf @if ($lokasi->exists) @method('put') @endif
        <label class="text-sm">Nama<input name="nama" value="{{ old('nama', $lokasi->nama) }}" required placeholder="Contoh: Sekolah Utama" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Radius Meter<input name="radius_meter" type="number" value="{{ old('radius_meter', $lokasi->radius_meter) }}" required placeholder="Contoh: 150" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>

        <input id="latitude" type="hidden" name="latitude" value="{{ $latitude }}">
        <input id="longitude" type="hidden" name="longitude" value="{{ $longitude }}">

        <div class="space-y-3 md:col-span-2" data-location-map data-initial-latitude="{{ $latitudeAwal }}" data-initial-longitude="{{ $longitudeAwal }}" data-has-point="{{ $punyaTitik ? '1' : '0' }}">
            <div class="flex flex-col gap-2 sm:flex-row">
                <label class="min-w-0 flex-1 text-sm">
                    Cari Titik Presensi
                    <input data-location-search value="{{ old('nama', $lokasi->nama) }}" placeholder="Cari nama sekolah, alamat, atau area" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                </label>
                <button type="button" data-location-search-button class="mt-6 inline-flex items-center justify-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">
                    Cari
                </button>
            </div>

            <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_18rem]">
                <div class="h-[22rem] overflow-hidden rounded-md border border-slate-200 bg-slate-100" data-location-map-canvas></div>
                <div class="space-y-3 rounded-md border border-slate-200 bg-slate-50 p-3 text-sm">
                    <div>
                        <p class="font-semibold text-slate-900">Titik Terpilih</p>
                        <output data-location-coordinate class="mt-1 block rounded-md border border-slate-200 bg-white px-3 py-2 font-mono text-xs text-slate-700">{{ $punyaTitik ? $latitude.', '.$longitude : 'Belum dipilih' }}</output>
                    </div>
                    <div data-location-status class="text-xs text-slate-500">Klik peta, geser pin, atau cari alamat untuk menentukan titik pusat presensi.</div>
                    <div data-location-results class="space-y-2"></div>
                </div>
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm md:col-span-2"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $lokasi->aktif ?? true))> Aktif</label>
        <div class="flex gap-2 md:col-span-2"><button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Simpan</button><a href="{{ route('admin.lokasi-sekolah.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm">Batal</a></div>
    </form>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            (() => {
                const root = document.querySelector('[data-location-map]');
                const form = document.querySelector('[data-location-form]');
                const L = window.L;

                if (!root || !form || !L) {
                    return;
                }

                const latitudeInput = document.getElementById('latitude');
                const longitudeInput = document.getElementById('longitude');
                const radiusInput = form.querySelector('[name="radius_meter"]');
                const mapCanvas = root.querySelector('[data-location-map-canvas]');
                const searchInput = root.querySelector('[data-location-search]');
                const searchButton = root.querySelector('[data-location-search-button]');
                const status = root.querySelector('[data-location-status]');
                const coordinateOutput = root.querySelector('[data-location-coordinate]');
                const results = root.querySelector('[data-location-results]');
                const hasPoint = root.dataset.hasPoint === '1';
                const initialLatitude = Number(root.dataset.initialLatitude || -6.2);
                const initialLongitude = Number(root.dataset.initialLongitude || 106.816666);
                const selectedIcon = L.icon({
                    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41],
                });

                let marker = null;
                let circle = null;

                const map = L.map(mapCanvas).setView([initialLatitude, initialLongitude], hasPoint ? 17 : 12);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(map);

                const setStatus = (message) => {
                    status.textContent = message;
                };

                const radius = () => Math.max(10, Number(radiusInput.value || 100));

                const setPoint = (latitude, longitude, label = null, move = true) => {
                    const point = [Number(latitude), Number(longitude)];
                    latitudeInput.value = point[0].toFixed(7);
                    longitudeInput.value = point[1].toFixed(7);
                    coordinateOutput.textContent = `${latitudeInput.value}, ${longitudeInput.value}`;

                    if (!marker) {
                        marker = L.marker(point, { draggable: true, icon: selectedIcon }).addTo(map);
                        marker.on('dragend', () => {
                            const position = marker.getLatLng();
                            setPoint(position.lat, position.lng, 'Titik diperbarui dari pin.', false);
                        });
                    } else {
                        marker.setLatLng(point);
                    }

                    if (!circle) {
                        circle = L.circle(point, {
                            radius: radius(),
                            color: '#0f766e',
                            fillColor: '#14b8a6',
                            fillOpacity: 0.14,
                            weight: 2,
                        }).addTo(map);
                    } else {
                        circle.setLatLng(point);
                        circle.setRadius(radius());
                    }

                    if (move) {
                        map.setView(point, Math.max(map.getZoom(), 17));
                    }

                    setStatus(label || 'Titik pusat presensi sudah dipilih.');
                };

                const updateRadius = () => {
                    if (circle) {
                        circle.setRadius(radius());
                    }
                };

                const renderResults = (items) => {
                    results.innerHTML = '';

                    if (items.length === 0) {
                        results.innerHTML = '<p class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">Lokasi tidak ditemukan.</p>';
                        return;
                    }

                    items.forEach((item) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-left text-xs text-slate-700 hover:border-teal-300 hover:bg-teal-50';
                        button.textContent = item.display_name;
                        button.addEventListener('click', () => {
                            setPoint(item.lat, item.lon, item.display_name);
                            results.innerHTML = '';
                        });
                        results.appendChild(button);
                    });
                };

                const searchLocation = async () => {
                    const query = searchInput.value.trim();

                    if (query.length < 3) {
                        setStatus('Masukkan minimal 3 karakter untuk mencari lokasi.');
                        return;
                    }

                    searchButton.disabled = true;
                    searchButton.textContent = 'Mencari...';
                    setStatus('Mencari lokasi dari OpenStreetMap.');

                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=5&q=${encodeURIComponent(query)}`, {
                            headers: { Accept: 'application/json' },
                        });
                        const data = await response.json();
                        renderResults(Array.isArray(data) ? data : []);
                        setStatus('Pilih salah satu hasil pencarian atau klik langsung pada peta.');
                    } catch (error) {
                        setStatus('Pencarian lokasi gagal. Klik langsung pada peta untuk memilih titik.');
                    } finally {
                        searchButton.disabled = false;
                        searchButton.textContent = 'Cari';
                    }
                };

                map.on('click', (event) => {
                    setPoint(event.latlng.lat, event.latlng.lng, 'Titik diperbarui dari peta.');
                });

                radiusInput.addEventListener('input', updateRadius);
                searchButton.addEventListener('click', searchLocation);
                searchInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        searchLocation();
                    }
                });

                form.addEventListener('submit', (event) => {
                    if (latitudeInput.value && longitudeInput.value) {
                        return;
                    }

                    event.preventDefault();
                    setStatus('Pilih titik pusat presensi terlebih dahulu.');
                    window.presensiToast?.('Pilih titik pusat presensi terlebih dahulu.', 'error');
                });

                if (hasPoint) {
                    setPoint(initialLatitude, initialLongitude, 'Titik pusat presensi saat ini.', false);
                }
            })();
        </script>
    @endpush
</x-admin.layout>
