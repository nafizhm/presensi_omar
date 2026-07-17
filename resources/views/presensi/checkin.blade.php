@extends('layouts.app')
@section('title', 'Presensi')

@push('styles')
<style>
    #googleMapFrame { width: 100%; height: 270px; border: 0; border-radius: 1.1rem; overflow: hidden; background: #e7ece9; }
</style>
@endpush

@section('content')
<div class="pb-4">
    <div class="px-5 pt-6 pb-4">
        <div class="font-display text-[20px] font-semibold text-ink">Presensi</div>
        <div id="currentDate" class="text-[13px] text-ink-soft mt-0.5"></div>
    </div>

    <div id="successSection" class="hidden flex-col items-center justify-center px-8" style="height:60vh">
        <div class="w-20 h-20 rounded-full flex items-center justify-center mb-5 bg-teal success-pop">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <div class="font-display text-[18px] font-semibold text-ink text-center">
            {{ $mode === 'pulang' ? 'Presensi pulang tercatat' : 'Presensi masuk tercatat' }}
        </div>
        <div class="text-[13px] text-ink-soft mt-1 text-center">
            Pukul <span id="successTime"></span> · {{ $namaKantor ?? 'Kantor Pusat' }}
        </div>
    </div>

    <div id="attendanceSection" class="px-5">
        <div id="currentTime" class="font-mono text-[40px] font-semibold text-ink text-center tracking-tight"></div>
        <div class="text-[12px] text-ink-soft text-center mt-1">Zona waktu {{ $timezoneLabel ?? 'WIB' }}</div>
        <div class="text-[12px] text-ink-soft text-center mt-1">
            {{ $schedule['shift_name'] }} ·
            {{ $schedule['start']->format('H:i') }} / {{ $schedule['middle']->format('H:i') }} / {{ $schedule['end']->format('H:i') }}
        </div>

        <div class="rounded-3xl mt-5 p-3 bg-white border border-line">
            <iframe id="googleMapFrame" title="Google Maps lokasi karyawan" loading="eager" allowfullscreen
                    referrerpolicy="strict-origin-when-cross-origin"
                    src="https://maps.google.com/maps?q={{ $officeLat ?? -6.2 }},{{ $officeLng ?? 106.816666 }}&z=16&output=embed"></iframe>

            <div class="text-center mt-3 px-2 pb-2">
                <div id="statusLoading" class="text-[13px] text-ink-soft">Mencari lokasi kamu…</div>
                <div id="statusDenied" class="hidden text-[13px] text-coral">
                    Lokasi tidak dapat dibaca. Aktifkan GPS dan berikan izin lokasi.
                </div>
                <div id="statusInside" class="hidden">
                    <div class="text-[14px] font-semibold text-teal-dark flex items-center justify-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 11 18-8-8 18-2-8-8-2Z"/></svg>
                        Kamu berada di dalam radius presensi
                    </div>
                    <div class="text-[12px] text-ink-soft mt-1">
                        Akurasi GPS <span id="accuracyText">0</span> m · radius maksimal {{ $radiusMeter }} m
                    </div>
                </div>
                <div id="statusOutside" class="hidden">
                    <div class="text-[14px] font-semibold text-coral">Kamu berada di luar radius presensi</div>
                    <div class="text-[12px] text-ink-soft mt-1">
                        Jarak <span id="outsideDistanceText">0</span> m · maksimal {{ $radiusMeter }} m
                    </div>
                </div>
            </div>
        </div>

        <form id="attendanceForm"
              action="{{ $mode === 'pulang' ? route('presensi.checkout') : route('presensi.store') }}"
              method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="latitude" class="block text-[12px] font-medium text-ink-soft mb-1">Latitude</label>
                    <input id="latitude" name="latitude" type="text" readonly
                           class="w-full rounded-xl border border-line bg-paper-alt px-3 py-2.5 text-[12px] font-mono text-ink">
                </div>
                <div>
                    <label for="longitude" class="block text-[12px] font-medium text-ink-soft mb-1">Longitude</label>
                    <input id="longitude" name="longitude" type="text" readonly
                           class="w-full rounded-xl border border-line bg-paper-alt px-3 py-2.5 text-[12px] font-mono text-ink">
                </div>
            </div>
            <input id="accuracy" type="hidden" name="accuracy">

            <input id="cameraInput" type="file" name="foto" accept="image/*" capture="user" class="hidden">

            <div class="rounded-2xl mt-4 p-4 bg-white border border-line">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 shrink-0 rounded-xl flex items-center justify-center bg-teal-tint">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0B7A70" stroke-width="2"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3Z"/><circle cx="12" cy="13" r="3"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div id="photoName" class="text-[13px] font-medium text-ink">Belum ada foto</div>
                        <div class="text-[11px] text-ink-soft mt-0.5">Kamera terbuka di HP; file picker terbuka di desktop.</div>
                    </div>
                </div>

                <img id="photoPreview" src="" alt="Pratinjau swafoto"
                     class="hidden w-full h-40 object-cover rounded-xl mt-3 border border-line">

                <button id="takePhotoButton" type="button"
                        class="w-full mt-3 py-3 rounded-xl border border-teal text-teal-dark text-[13px] font-semibold bg-white active:bg-teal-tint">
                    Ambil Foto
                </button>
            </div>

            <button id="submitAttendanceButton" type="submit"
                    class="w-full mt-5 py-4 rounded-2xl text-[15px] font-display font-semibold transition bg-paper-alt text-ink-soft disabled:cursor-not-allowed">
                {{ $mode === 'pulang' ? 'Simpan Absen Pulang' : 'Simpan Absen Masuk' }}
            </button>
            <div class="text-[11px] text-ink-soft text-center mt-2">
                {{ $mode === 'pulang'
                    ? 'Waktu sudah melewati batas tengah, sehingga dicatat sebagai absen pulang.'
                    : 'Waktu belum melewati batas tengah, sehingga dicatat sebagai absen masuk.' }}
            </div>
        </form>

        <div class="mt-3 text-center">
            <button id="refreshLocationButton" type="button" class="text-[12px] text-ink-soft underline">Perbarui lokasi</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const settings = {
        officeLat: @js($officeLat ?? -6.200000),
        officeLng: @js($officeLng ?? 106.816666),
        radiusMeter: @js($radiusMeter ?? 100),
        googleMapsApiKey: @js($googleMapsApiKey ?? ''),
        timezone: @js($timezone ?? 'Asia/Jakarta'),
        redirectUrl: @js(route('presensi.beranda')),
    };

    const elements = {
        date: document.getElementById('currentDate'),
        time: document.getElementById('currentTime'),
        successTime: document.getElementById('successTime'),
        successSection: document.getElementById('successSection'),
        attendanceSection: document.getElementById('attendanceSection'),
        form: document.getElementById('attendanceForm'),
        latitude: document.getElementById('latitude'),
        longitude: document.getElementById('longitude'),
        accuracy: document.getElementById('accuracy'),
        cameraInput: document.getElementById('cameraInput'),
        takePhoto: document.getElementById('takePhotoButton'),
        photoName: document.getElementById('photoName'),
        photoPreview: document.getElementById('photoPreview'),
        submit: document.getElementById('submitAttendanceButton'),
        refresh: document.getElementById('refreshLocationButton'),
        accuracyText: document.getElementById('accuracyText'),
        outsideDistanceText: document.getElementById('outsideDistanceText'),
        mapFrame: document.getElementById('googleMapFrame'),
    };
    const statusElements = ['statusLoading', 'statusDenied', 'statusInside', 'statusOutside'];
    let submitting = false;
    let isInsideRadius = false;
    let previewUrl = '';

    function updateClock() {
        const now = new Date();
        elements.time.textContent = now.toLocaleTimeString('id-ID', {
            timeZone: settings.timezone, hour12: false,
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        });
        elements.date.textContent = now.toLocaleDateString('id-ID', {
            timeZone: settings.timezone, weekday: 'long', day: 'numeric', month: 'long'
        });
    }

    function showStatus(id) {
        statusElements.forEach(function (statusId) {
            document.getElementById(statusId).classList.toggle('hidden', statusId !== id);
        });
    }

    function updateSubmitButton() {
        elements.submit.disabled = submitting || !isInsideRadius;
        elements.submit.classList.toggle('bg-paper-alt', submitting || !isInsideRadius);
        elements.submit.classList.toggle('text-ink-soft', submitting || !isInsideRadius);
        elements.submit.classList.toggle('bg-teal-dark', !submitting && isInsideRadius);
        elements.submit.classList.toggle('text-white', !submitting && isInsideRadius);
    }

    function distanceInMeters(lat1, lng1, lat2, lng2) {
        const toRadians = value => value * Math.PI / 180;
        const earthRadius = 6371000;
        const dLat = toRadians(lat2 - lat1);
        const dLng = toRadians(lng2 - lng1);
        const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) * Math.sin(dLng / 2) ** 2;
        return earthRadius * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function updateGoogleMap(latitude, longitude) {
        const coordinates = latitude + ',' + longitude;
        if (settings.googleMapsApiKey) {
            elements.mapFrame.src = 'https://www.google.com/maps/embed/v1/place?key=' +
                encodeURIComponent(settings.googleMapsApiKey) + '&q=' + encodeURIComponent(coordinates) + '&zoom=18';
            return;
        }
        elements.mapFrame.src = 'https://maps.google.com/maps?q=' + encodeURIComponent(coordinates) + '&z=18&output=embed';
    }

    function refreshLocation() {
        showStatus('statusLoading');
        isInsideRadius = false;
        updateSubmitButton();
        if (!navigator.geolocation) {
            showStatus('statusDenied');
            return;
        }
        navigator.geolocation.getCurrentPosition(function (position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            const accuracy = position.coords.accuracy;

            elements.latitude.value = Number(latitude).toFixed(7);
            elements.longitude.value = Number(longitude).toFixed(7);
            elements.accuracy.value = accuracy;
            elements.accuracyText.textContent = Math.round(accuracy || 0);
            updateGoogleMap(latitude, longitude);
            const distance = distanceInMeters(latitude, longitude, settings.officeLat, settings.officeLng);
            isInsideRadius = distance <= settings.radiusMeter;
            elements.outsideDistanceText.textContent = Math.round(distance);
            showStatus(isInsideRadius ? 'statusInside' : 'statusOutside');
            updateSubmitButton();
        }, function () {
            isInsideRadius = false;
            showStatus('statusDenied');
            updateSubmitButton();
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 });
    }

    elements.takePhoto.addEventListener('click', function () {
        elements.cameraInput.click();
    });

    elements.cameraInput.addEventListener('change', function () {
        const file = elements.cameraInput.files && elements.cameraInput.files[0];
        if (!file) return;
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        elements.photoName.textContent = file.name;
        elements.photoPreview.src = previewUrl;
        elements.photoPreview.classList.remove('hidden');
        elements.takePhoto.textContent = 'Ambil Ulang Foto';
    });

    elements.refresh.addEventListener('click', refreshLocation);

    elements.form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (submitting) return;
        submitting = true;
        elements.submit.textContent = 'Menyimpan absen…';
        updateSubmitButton();

        try {
            const response = await fetch(elements.form.action, {
                method: 'POST',
                body: new FormData(elements.form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Absen gagal disimpan.');
            elements.successTime.textContent = elements.time.textContent.slice(0, 5);
            elements.attendanceSection.classList.add('hidden');
            elements.successSection.classList.remove('hidden');
            elements.successSection.classList.add('flex');
            setTimeout(function () { window.location.href = settings.redirectUrl; }, 1400);
        } catch (error) {
            alert(error.message || 'Terjadi kesalahan jaringan.');
            submitting = false;
            elements.submit.textContent = @js($mode === 'pulang' ? 'Simpan Absen Pulang' : 'Simpan Absen Masuk');
            updateSubmitButton();
        }
    });

    updateClock();
    updateSubmitButton();
    setInterval(updateClock, 1000);
    refreshLocation();
});
</script>
@endpush
@endsection
