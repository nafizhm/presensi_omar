@extends('layouts.app')
@section('title', 'Presensi')

@section('content')
<div x-data="checkinCard()" x-init="init()" class="pb-4">

    <div class="px-5 pt-6 pb-4">
        <div class="font-display text-[20px] font-semibold text-ink">Presensi</div>
        <div class="text-[13px] text-ink-soft mt-0.5" x-text="dateStr"></div>
    </div>

    {{-- SUCCESS STATE --}}
    <template x-if="phase === 'success'">
        <div class="flex flex-col items-center justify-center px-8" style="height: 60vh;">
            <div class="w-20 h-20 rounded-full flex items-center justify-center mb-5 bg-teal success-pop">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <div class="font-display text-[18px] font-semibold text-ink text-center"
                 x-text="mode === 'pulang' ? 'Presensi pulang tercatat' : 'Presensi masuk tercatat'"></div>
            <div class="text-[13px] text-ink-soft mt-1 text-center">
                Pukul <span x-text="timeStr.slice(0,5)"></span> · {{ $namaKantor ?? 'Kantor Pusat' }}
            </div>
        </div>
    </template>

    {{-- IDLE / FORM STATE --}}
    <template x-if="phase !== 'success'">
        <div class="px-5">
            <div class="font-mono text-[40px] font-semibold text-ink text-center tracking-tight" x-text="timeStr"></div>

            <div class="rounded-3xl mt-5 p-5 bg-white border border-line">
                <div class="radar-wrap">
                    <template x-for="i in [0,1,2]" :key="i">
                        <span class="radar-ring" :style="`border-color:${inRange ? '#0D9488' : '#E4572E'}; animation-delay:${i*0.6}s`"></span>
                    </template>
                    <div class="w-4 h-4 rounded-full relative z-10" :style="`background:${inRange ? '#0D9488' : '#E4572E'}`"></div>
                </div>

                <div class="text-center mt-2">
                    <template x-if="status === 'loading'">
                        <div class="text-[13px] text-ink-soft">Mencari lokasi kamu…</div>
                    </template>
                    <template x-if="status === 'denied'">
                        <div class="text-[13px] text-coral">Izin lokasi ditolak. Aktifkan GPS untuk presensi.</div>
                    </template>
                    <template x-if="status === 'ok' && inRange">
                        <div>
                            <div class="text-[14px] font-semibold text-teal-dark flex items-center justify-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 11 18-8-8 18-2-8-8-2Z"/></svg>
                                Dalam radius kantor
                            </div>
                            <div class="text-[12px] text-ink-soft mt-1">
                                <span x-text="Math.round(distance)"></span> m dari {{ $namaKantor ?? 'Kantor Pusat' }} · akurasi GPS <span x-text="Math.round(accuracy)"></span> m
                            </div>
                        </div>
                    </template>
                    <template x-if="status === 'ok' && !inRange">
                        <div>
                            <div class="text-[14px] font-semibold text-coral flex items-center justify-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                Di luar radius kantor
                            </div>
                            <div class="text-[12px] text-ink-soft mt-1">
                                <span x-text="Math.round(distance)"></span> m dari {{ $namaKantor ?? 'Kantor Pusat' }} · mendekatlah untuk presensi
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="rounded-2xl mt-4 p-4 flex items-center gap-3 bg-white border border-line">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-teal-tint">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0B7A70" stroke-width="2"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3Z"/><circle cx="12" cy="13" r="3"/></svg>
                </div>
                <div class="text-[13px] text-ink">
                    <span x-show="!photo">Swafoto akan diambil otomatis saat kamu presensi</span>
                    <span x-show="photo" class="text-teal-dark font-medium">Foto berhasil diambil ✓</span>
                </div>
            </div>

            {{-- hidden camera input, uses device camera on mobile web --}}
            <input type="file" accept="image/*" capture="user" class="hidden" x-ref="cameraInput" @change="onPhoto($event)">

            <form :action="mode === 'pulang' ? '{{ route('presensi.checkout') }}' : '{{ route('presensi.store') }}'"
                  method="POST" @submit.prevent="submitForm($el)">
                @csrf
                <input type="hidden" name="latitude" :value="lat">
                <input type="hidden" name="longitude" :value="lng">
                <input type="hidden" name="accuracy" :value="accuracy">

                <button type="button" @click="!photo ? $refs.cameraInput.click() : submitForm($el.closest('form'))"
                        :disabled="!inRange || status !== 'ok'"
                        :class="(!inRange || status !== 'ok') ? 'bg-paper-alt text-ink-soft' : 'bg-teal-dark text-white active:opacity-90'"
                        class="w-full mt-5 py-4 rounded-2xl text-[15px] font-display font-semibold transition">
                    <span x-text="mode === 'pulang' ? 'Check-out Sekarang' : (photo ? 'Kirim Presensi' : 'Check-in Sekarang')"></span>
                </button>
            </form>

            <div class="mt-3 text-center">
                <button @click="refreshLocation()" class="text-[12px] text-ink-soft underline">Perbarui lokasi</button>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function checkinCard() {
    return {
        // koordinat & radius kantor - sesuaikan dengan data kantor sebenarnya (bisa dikirim dari controller via @json)
        officeLat: {{ $officeLat ?? -6.200000 }},
        officeLng: {{ $officeLng ?? 106.816666 }},
        radiusMeter: {{ $radiusMeter ?? 100 }},
        mode: '{{ $mode ?? "masuk" }}', // "masuk" atau "pulang", dikirim dari controller sesuai status hari ini

        now: new Date(),
        timeStr: '',
        dateStr: '',
        lat: null,
        lng: null,
        accuracy: null,
        distance: null,
        inRange: false,
        status: 'loading', // loading | ok | denied
        phase: 'idle', // idle | success
        photo: null,

        init() {
            this.tick();
            setInterval(() => this.tick(), 1000);
            this.refreshLocation();
        },

        tick() {
            this.now = new Date();
            this.timeStr = this.now.toLocaleTimeString('id-ID');
            this.dateStr = this.now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
        },

        refreshLocation() {
            this.status = 'loading';
            if (!navigator.geolocation) {
                this.status = 'denied';
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.lat = pos.coords.latitude;
                    this.lng = pos.coords.longitude;
                    this.accuracy = pos.coords.accuracy;
                    this.distance = this.haversine(this.lat, this.lng, this.officeLat, this.officeLng);
                    this.inRange = this.distance <= this.radiusMeter;
                    this.status = 'ok';
                },
                () => { this.status = 'denied'; },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },

        haversine(lat1, lon1, lat2, lon2) {
            const R = 6371000; // meter
            const toRad = (d) => d * Math.PI / 180;
            const dLat = toRad(lat2 - lat1);
            const dLon = toRad(lon2 - lon1);
            const a = Math.sin(dLat / 2) ** 2 +
                      Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                      Math.sin(dLon / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        },

        onPhoto(e) {
            const file = e.target.files[0];
            if (file) {
                this.photo = file;
            }
        },

        submitForm(form) {
            if (!this.inRange || this.status !== 'ok') return;

            const data = new FormData(form);
            if (this.photo) data.append('foto', this.photo);

            fetch(form.action, {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then((res) => res.json())
            .then((res) => {
                if (res.success) {
                    this.phase = 'success';
                    setTimeout(() => { window.location.href = '{{ route('presensi.beranda') }}'; }, 1400);
                } else {
                    alert(res.message ?? 'Presensi gagal, coba lagi.');
                }
            })
            .catch(() => alert('Terjadi kesalahan jaringan.'));
        },
    };
}
</script>
@endpush
@endsection
