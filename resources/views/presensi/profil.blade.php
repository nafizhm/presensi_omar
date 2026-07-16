@extends('layouts.app')
@section('title', 'Profil')

@section('content')
<div class="px-5 pt-6 pb-4">
    <div class="font-display text-[20px] font-semibold text-ink">Profil</div>
</div>

<div class="px-5">
    <div class="rounded-3xl p-5 flex items-center gap-4 bg-white border border-line">
        <div class="w-14 h-14 rounded-full bg-teal-dark text-white font-display flex items-center justify-center text-[18px] font-semibold">
            {{ $inisial ?? 'DP' }}
        </div>
        <div>
            <div class="font-display text-[16px] font-semibold text-ink">{{ $user->name ?? 'Dinda Pratiwi' }}</div>
            <div class="text-[12px] text-ink-soft mt-0.5">{{ $user->jabatan ?? 'UI/UX Designer' }} · ID {{ $user->employee_id ?? '20291' }}</div>
        </div>
    </div>

    <div class="rounded-2xl mt-4 p-4 bg-white border border-line">
        <div class="text-[11px] font-medium text-ink-soft mb-2">JADWAL KERJA</div>
        <div class="flex items-center justify-between">
            <div class="text-[13px] font-medium text-ink">Senin — Jumat</div>
            <div class="font-mono text-[13px] font-semibold text-ink">08:00 – 17:00</div>
        </div>
    </div>

    <div class="mt-4 rounded-2xl overflow-hidden border border-line">
        @php
            $rows = [
                ['label' => 'Radius kantor', 'value' => ($radiusMeter ?? 100).' m', 'icon' => 'map-pin'],
                ['label' => 'Notifikasi', 'value' => 'Aktif', 'icon' => 'bell'],
                ['label' => 'Keamanan & biometrik', 'value' => '', 'icon' => 'shield'],
                ['label' => 'Bahasa', 'value' => 'Indonesia', 'icon' => 'globe'],
            ];
            $icons = [
                'map-pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
                'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
                'shield' => '<path d="M12 2 4 6v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V6l-8-4Z"/>',
                'globe' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>',
            ];
        @endphp
        @foreach ($rows as $i => $r)
            <div class="px-4 py-3.5 flex items-center justify-between bg-white {{ !$loop->last ? 'border-b border-line' : '' }}">
                <div class="flex items-center gap-3">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5D5A" stroke-width="2">{!! $icons[$r['icon']] !!}</svg>
                    <span class="text-[13px] text-ink">{{ $r['label'] }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    @if($r['value'])
                        <span class="text-[12px] text-ink-soft">{{ $r['value'] }}</span>
                    @endif
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#4A5D5A" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                </div>
            </div>
        @endforeach
    </div>

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="w-full mt-5 py-3.5 rounded-2xl text-[14px] font-display font-semibold flex items-center justify-center gap-2 bg-coral-tint text-coral">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
            Keluar
        </button>
    </form>
</div>
@endsection
