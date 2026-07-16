@extends('layouts.app')
@section('title', 'Beranda')

@section('content')
<div class="px-5 pt-6 pb-2 flex items-center justify-between">
    <div>
        <div class="text-[13px] text-ink-soft">{{ $sapaan ?? 'Selamat pagi' }}</div>
        <div class="font-display text-[21px] font-semibold text-ink">{{ $user->name ?? 'Dinda Pratiwi' }}</div>
    </div>
    @if ($user->photo)
        <img src="{{ asset('storage/'.$user->photo) }}" alt="Foto {{ $user->name }}" class="w-11 h-11 rounded-full object-cover border border-line">
    @else
        <div class="w-11 h-11 rounded-full bg-teal-dark text-white font-display flex items-center justify-center text-[15px] font-semibold">{{ $inisial ?? 'DP' }}</div>
    @endif
</div>
<div class="px-5 text-[13px] text-ink-soft mb-4">{{ $localNow->translatedFormat('l, d F Y') }}</div>

{{-- Status card --}}
<div class="px-5">
    <div class="rounded-3xl p-5 relative overflow-hidden bg-ink">
        <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full blur-2xl bg-teal opacity-25"></div>

        <div class="flex items-center justify-between relative">
            <span class="text-[12px] text-white/60 font-medium">Status hari ini</span>
            @if($presensiHariIni->jam_masuk ?? false)
                <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-teal-tint text-teal-dark">Sudah masuk</span>
            @else
                <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-amber-tint text-[#92620A]">Belum presensi</span>
            @endif
        </div>

        <div class="flex justify-between mt-5 relative">
            <div>
                <div class="text-[11px] text-white/55 mb-1">Jam masuk</div>
                <div class="font-mono text-[24px] font-semibold text-white">{{ $presensiHariIni->jam_masuk ?? '--:--' }}</div>
            </div>
            <div class="w-px bg-white/15"></div>
            <div>
                <div class="text-[11px] text-white/55 mb-1">Jam pulang</div>
                <div class="font-mono text-[24px] font-semibold text-white">{{ $presensiHariIni->jam_pulang ?? '--:--' }}</div>
            </div>
        </div>

        <a href="{{ route('presensi.checkin') }}"
           class="w-full mt-5 py-3 rounded-2xl text-white text-[14px] font-display font-semibold flex items-center justify-center gap-2 bg-teal active:opacity-90">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            {{ ($presensiHariIni->jam_masuk ?? false) ? 'Presensi Pulang' : 'Presensi Masuk' }}
        </a>
    </div>
</div>

{{-- Weekly stats --}}
<div class="px-5 mt-5">
    <div class="font-display text-[14px] font-semibold text-ink mb-3">Ringkasan {{ $localNow->translatedFormat('F Y') }}</div>
    <div class="grid grid-cols-3 gap-3">
        <div class="rounded-2xl py-3 text-center bg-white border border-line">
            <div class="font-mono text-[20px] font-semibold text-teal">{{ $stats['hadir'] ?? 0 }}</div>
            <div class="text-[11px] text-ink-soft mt-0.5">Hadir</div>
        </div>
        <div class="rounded-2xl py-3 text-center bg-white border border-line">
            <div class="font-mono text-[20px] font-semibold text-amber">{{ $stats['telat'] ?? 0 }}</div>
            <div class="text-[11px] text-ink-soft mt-0.5">Telat</div>
        </div>
        <div class="rounded-2xl py-3 text-center bg-white border border-line">
            <div class="font-mono text-[20px] font-semibold text-ink-soft">{{ $stats['izin'] ?? 0 }}</div>
            <div class="text-[11px] text-ink-soft mt-0.5">Izin</div>
        </div>
    </div>
</div>

{{-- Recent activity --}}
<div class="px-5 mt-6">
    <div class="font-display text-[14px] font-semibold text-ink mb-3">Aktivitas terakhir</div>

    @forelse($riwayatTerakhir ?? [] as $r)
        @php
            $tone = match($r->status) {
                'tepat_waktu' => ['bg' => 'bg-teal-tint', 'fg' => 'text-teal-dark', 'label' => 'Tepat waktu'],
                'telat' => ['bg' => 'bg-amber-tint', 'fg' => 'text-[#92620A]', 'label' => 'Telat'],
                'izin' => ['bg' => 'bg-paper-alt', 'fg' => 'text-ink-soft', 'label' => 'Izin'],
                default => ['bg' => 'bg-coral-tint', 'fg' => 'text-coral', 'label' => 'Absen'],
            };
        @endphp
        <div class="rounded-2xl p-4 mb-3 flex items-center justify-between bg-white border border-line">
            <div>
                <div class="text-[13px] font-medium text-ink">{{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('l, d M') }}</div>
                <div class="font-mono text-[12px] text-ink-soft mt-0.5">{{ $r->jam_masuk ?? '—' }} — {{ $r->jam_pulang ?? '—' }}</div>
            </div>
            <span class="text-[11px] font-semibold px-2 py-1 rounded-full {{ $tone['bg'] }} {{ $tone['fg'] }}">{{ $tone['label'] }}</span>
        </div>
    @empty
        <div class="rounded-2xl p-6 text-center bg-white border border-line text-[13px] text-ink-soft">
            Belum ada riwayat presensi.
        </div>
    @endforelse
</div>
@endsection
