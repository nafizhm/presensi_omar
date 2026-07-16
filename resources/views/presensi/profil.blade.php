@extends('layouts.app')
@section('title', 'Profil')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<style>
    .profile-modal { position: fixed; inset: 0; z-index: 100; display: none; align-items: flex-end; justify-content: center; background: rgba(15, 23, 42, .52); padding: 0; }
    .profile-modal.is-open { display: flex; }
    .profile-sheet { width: 100%; max-width: 430px; max-height: 92vh; overflow-y: auto; background: #fff; border-radius: 24px 24px 0 0; box-shadow: 0 -12px 40px rgba(15, 23, 42, .18); }
    .profile-input { width: 100%; border: 1px solid #dce5e3; border-radius: 12px; padding: 11px 13px; font-size: 14px; outline: none; background: #fff; }
    .profile-input:focus { border-color: #0f766e; box-shadow: 0 0 0 3px rgba(15, 118, 110, .1); }
    #employeeLocationMap { height: 280px; border-radius: 16px; border: 1px solid #dce5e3; z-index: 1; }
    .menu-row { width: 100%; text-align: left; }
    .profile-photo-button { position: relative; width: 58px; height: 58px; flex: 0 0 58px; border-radius: 999px; overflow: hidden; }
    .profile-photo-button img { width: 100%; height: 100%; object-fit: cover; }
    .profile-photo-camera { position: absolute; right: 0; bottom: 0; width: 22px; height: 22px; border-radius: 999px; display: flex; align-items: center; justify-content: center; color: white; background: #0f766e; border: 2px solid white; }
</style>
@endpush

@section('content')
<div class="px-5 pt-6 pb-4"><div class="font-display text-[20px] font-semibold text-ink">Profil</div></div>

<div class="px-5">
    @if (session('success'))
        <div class="mb-4 rounded-2xl px-4 py-3 bg-teal-tint text-teal-dark text-[13px]">{{ session('success') }}</div>
    @endif

    <div class="rounded-3xl p-5 flex items-center gap-4 bg-white border border-line">
        <form id="profilePhotoForm" action="{{ route('presensi.profil.photo.update') }}" method="POST" enctype="multipart/form-data">@csrf
            <label class="profile-photo-button block cursor-pointer" title="Ambil foto profil baru">
                @if ($user->photo)<img src="{{ asset('storage/'.$user->photo) }}" alt="Foto {{ $user->name }}">
                @else<div class="w-full h-full bg-teal-dark text-white font-display flex items-center justify-center text-[18px] font-semibold">{{ $inisial }}</div>@endif
                <span class="profile-photo-camera"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3Z"/><circle cx="12" cy="13" r="3"/></svg></span>
                <input id="profilePhotoInput" class="hidden" type="file" name="photo" accept="image/*" capture="user">
            </label>
        </form>
        <div class="min-w-0">
            <div class="font-display text-[16px] font-semibold text-ink truncate">{{ $user->name }}</div>
            <div class="text-[12px] text-ink-soft mt-0.5">Kode Karyawan: {{ $user->employee_code ?? '-' }}</div>
            <div class="text-[10px] text-teal-dark mt-1">Ketuk foto untuk memperbarui lewat kamera</div>
        </div>
    </div>

    <div class="rounded-2xl mt-4 p-4 bg-white border border-line">
        <div class="text-[11px] font-medium text-ink-soft mb-2">JADWAL KERJA</div>
        <div class="flex items-center justify-between gap-3"><div class="text-[13px] font-medium text-ink">{{ $user->shift?->name ?? 'Jadwal default' }}</div><div class="text-[12px] text-ink-soft">Presensi aktif</div></div>
    </div>

    <div class="mt-4 rounded-2xl overflow-hidden border border-line bg-white">
        <button type="button" class="menu-row px-4 py-3.5 flex items-center justify-between border-b border-line" data-open-modal="profileDataModal">
            <div class="flex items-center gap-3">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#4A5D5A" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                <div><div class="text-[13px] text-ink">Data Profil</div><div class="text-[11px] text-ink-soft mt-0.5">Telepon, email, dan password</div></div>
            </div>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#4A5D5A" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        </button>

        @if ($user->can_manage_location_points)
            <button type="button" class="menu-row px-4 py-3.5 flex items-center justify-between border-b border-line" data-open-modal="locationModal">
                <div class="flex items-center gap-3">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#4A5D5A" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    <div><div class="text-[13px] text-ink">Pengaturan Titik Lokasi</div><div class="text-[11px] text-ink-soft mt-0.5">Tambahkan lokasi presensi baru</div></div>
                </div>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#4A5D5A" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        @endif

        <div class="px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#4A5D5A" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/></svg><span class="text-[13px] text-ink">Bahasa</span></div>
            <span class="text-[12px] text-ink-soft">Indonesia</span>
        </div>
    </div>

    <form action="{{ route('logout') }}" method="POST">@csrf
        <button type="submit" class="w-full mt-5 py-3.5 rounded-2xl text-[14px] font-display font-semibold flex items-center justify-center gap-2 bg-coral-tint text-coral">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>Keluar
        </button>
    </form>
</div>

<div id="profileDataModal" class="profile-modal" aria-hidden="true"><div class="profile-sheet">
    <div class="p-5 flex items-center justify-between border-b border-line"><div><div class="font-display text-[17px] font-semibold">Data Profil</div><div class="text-[11px] text-ink-soft mt-1">Perbarui informasi akun Anda</div></div><button type="button" data-close-modal class="text-[26px] text-ink-soft">&times;</button></div>
    <form action="{{ route('presensi.profil.update') }}" method="POST" class="p-5">@csrf @method('PATCH')
        <input type="hidden" name="_form_mode" value="profile">
        <div class="mb-4"><label class="block text-[12px] font-medium mb-1.5">Nomor Telepon</label><input class="profile-input" type="text" name="phone" value="{{ old('_form_mode') === 'profile' ? old('phone') : $user->phone }}" required maxlength="30"></div>
        <div class="mb-4"><label class="block text-[12px] font-medium mb-1.5">Email</label><input class="profile-input" type="email" name="email" value="{{ old('_form_mode') === 'profile' ? old('email') : $user->email }}" required></div>
        <div class="mb-4"><label class="block text-[12px] font-medium mb-1.5">Password Baru</label><input class="profile-input" type="password" name="password" minlength="8"><div class="text-[11px] text-ink-soft mt-1">Kosongkan jika tidak ingin mengganti password.</div></div>
        <div class="mb-4"><label class="block text-[12px] font-medium mb-1.5">Konfirmasi Password</label><input class="profile-input" type="password" name="password_confirmation" minlength="8"></div>
        @if ($errors->any() && old('_form_mode') === 'profile')<div class="mb-4 rounded-xl bg-coral-tint text-coral p-3 text-[12px]"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <button class="w-full py-3 rounded-xl bg-teal-dark text-white text-[14px] font-semibold" type="submit">Simpan Perubahan</button>
    </form>
</div></div>

@if ($user->can_manage_location_points)
<div id="locationModal" class="profile-modal" aria-hidden="true"><div class="profile-sheet">
    <div class="p-5 flex items-center justify-between border-b border-line"><div><div class="font-display text-[17px] font-semibold">Titik Lokasi Baru</div><div class="text-[11px] text-ink-soft mt-1">Tentukan lokasi dan radius presensi</div></div><button type="button" data-close-modal class="text-[26px] text-ink-soft">&times;</button></div>
    <form action="{{ route('presensi.profil.locations.store') }}" method="POST" class="p-5">@csrf <input type="hidden" name="_form_mode" value="location">
        <div class="mb-4"><label class="block text-[12px] font-medium mb-1.5">Nama Lokasi</label><input class="profile-input" type="text" name="name" value="{{ old('_form_mode') === 'location' ? old('name') : '' }}" required></div>
        <div class="flex items-center justify-between mb-2"><label class="text-[12px] font-medium">Pilih pada Peta</label><button id="useDeviceLocation" type="button" class="text-[12px] font-semibold text-teal-dark">Gunakan GPS</button></div>
        <div id="employeeLocationMap"></div><div id="locationStatus" class="text-[11px] text-ink-soft mt-2">Klik peta atau geser penanda untuk memilih titik.</div>
        <div class="grid grid-cols-2 gap-3 mt-4"><div><label class="block text-[12px] font-medium mb-1.5">Latitude</label><input id="employeeLatitude" class="profile-input" name="latitude" value="{{ old('latitude', '-6.2000000') }}" readonly required></div><div><label class="block text-[12px] font-medium mb-1.5">Longitude</label><input id="employeeLongitude" class="profile-input" name="longitude" value="{{ old('longitude', '106.8166660') }}" readonly required></div></div>
        <div class="grid grid-cols-2 gap-3 mt-4"><div><label class="block text-[12px] font-medium mb-1.5">Radius (meter)</label><input id="employeeRadius" class="profile-input" type="number" name="radius_meters" value="{{ old('radius_meters', 100) }}" min="1" max="100000" required></div><div><label class="block text-[12px] font-medium mb-1.5">Zona Waktu</label><select class="profile-input" name="timezone" required><option value="Asia/Jakarta">WIB</option><option value="Asia/Makassar">WITA</option><option value="Asia/Jayapura">WIT</option></select></div></div>
        @if ($errors->any() && old('_form_mode') === 'location')<div class="my-4 rounded-xl bg-coral-tint text-coral p-3 text-[12px]"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <button class="w-full mt-5 py-3 rounded-xl bg-teal-dark text-white text-[14px] font-semibold" type="submit">Simpan Titik Lokasi</button>
    </form>
</div></div>
@endif
@endsection

@push('scripts')
@if ($user->can_manage_location_points)<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    let locationMap = null;
    const photoInput = document.getElementById('profilePhotoInput');
    if (photoInput) photoInput.addEventListener('change', function () { if (this.files && this.files[0]) document.getElementById('profilePhotoForm').submit(); });
    document.querySelectorAll('[data-open-modal]').forEach(button => button.addEventListener('click', function () {
        const modal = document.getElementById(this.dataset.openModal); modal.classList.add('is-open'); modal.setAttribute('aria-hidden', 'false');
        if (modal.id === 'locationModal') setTimeout(initLocationMap, 50);
    }));
    document.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', () => closeModal(button.closest('.profile-modal'))));
    document.querySelectorAll('.profile-modal').forEach(modal => modal.addEventListener('click', event => { if (event.target === modal) closeModal(modal); }));
    function closeModal(modal) { modal.classList.remove('is-open'); modal.setAttribute('aria-hidden', 'true'); }

    function initLocationMap() {
        if (!document.getElementById('employeeLocationMap') || typeof L === 'undefined') return;
        if (locationMap) { locationMap.invalidateSize(); return; }
        const latInput = document.getElementById('employeeLatitude'); const lngInput = document.getElementById('employeeLongitude'); const radiusInput = document.getElementById('employeeRadius');
        const initial = [Number(latInput.value) || -6.2, Number(lngInput.value) || 106.816666];
        locationMap = L.map('employeeLocationMap').setView(initial, 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(locationMap);
        const marker = L.marker(initial, { draggable: true }).addTo(locationMap); const circle = L.circle(initial, { radius: Number(radiusInput.value) || 100, color: '#0f766e', fillOpacity: .12 }).addTo(locationMap);
        function setPoint(point) { latInput.value = Number(point.lat).toFixed(7); lngInput.value = Number(point.lng).toFixed(7); marker.setLatLng(point); circle.setLatLng(point); }
        locationMap.on('click', event => setPoint(event.latlng)); marker.on('dragend', event => setPoint(event.target.getLatLng())); radiusInput.addEventListener('input', () => circle.setRadius(Math.max(1, Number(radiusInput.value) || 1)));
        document.getElementById('useDeviceLocation').addEventListener('click', function () { document.getElementById('locationStatus').textContent = 'Mencari lokasi perangkat...'; navigator.geolocation.getCurrentPosition(position => { const point = { lat: position.coords.latitude, lng: position.coords.longitude }; setPoint(point); locationMap.setView(point, 18); document.getElementById('locationStatus').textContent = 'Lokasi perangkat ditemukan. Geser penanda bila diperlukan.'; }, () => document.getElementById('locationStatus').textContent = 'Lokasi tidak dapat dibaca. Pastikan izin GPS aktif dan halaman menggunakan HTTPS.'); });
    }
    @if ($errors->any() && old('_form_mode') === 'profile') document.querySelector('[data-open-modal="profileDataModal"]').click();
    @elseif ($errors->any() && old('_form_mode') === 'location' && $user->can_manage_location_points) document.querySelector('[data-open-modal="locationModal"]').click(); @endif
});
</script>
@endpush
