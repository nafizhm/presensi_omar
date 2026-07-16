@extends('layouts.app')
@section('title', 'Riwayat')

@section('content')
<div class="px-5 pt-6 pb-4">
    <div class="font-display text-[20px] font-semibold text-ink">Riwayat</div>
    <div class="text-[13px] text-ink-soft mt-0.5">{{ $localNow->translatedFormat('F Y') }}</div>
</div>

<div class="px-5 flex items-center gap-2 mb-3 overflow-x-auto">
    @foreach (['semua' => 'Semua', 'tepat_waktu' => 'Tepat waktu', 'telat' => 'Telat', 'izin' => 'Izin', 'absen' => 'Absen'] as $value => $filter)
        <button type="button" data-history-filter="{{ $value }}" class="history-filter text-[12px] font-medium px-3 py-1.5 rounded-full whitespace-nowrap border
            {{ $loop->first ? 'bg-ink text-white border-ink' : 'bg-white text-ink-soft border-line' }}">
            {{ $filter }}
        </button>
    @endforeach
</div>

<div class="px-5">
    @forelse($riwayat ?? [] as $record)
        @php
            $tone = match($record->status) {
                'tepat_waktu' => ['bg' => 'bg-teal-tint', 'fg' => 'text-teal-dark', 'label' => 'Tepat waktu'],
                'telat' => ['bg' => 'bg-amber-tint', 'fg' => 'text-[#92620A]', 'label' => 'Telat'],
                'izin' => ['bg' => 'bg-paper-alt', 'fg' => 'text-ink-soft', 'label' => 'Izin'],
                default => ['bg' => 'bg-coral-tint', 'fg' => 'text-coral', 'label' => 'Absen'],
            };
            $date = \Carbon\Carbon::parse($record->tanggal);
        @endphp

        <details data-history-item data-status="{{ $record->status }}" class="rounded-2xl mb-3 p-4 bg-white border border-line cursor-pointer">
            <summary class="list-none" style="list-style:none">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-paper-alt flex flex-col items-center justify-center">
                            <span class="font-display text-[13px] font-semibold text-ink leading-none">{{ $date->format('d') }}</span>
                            <span class="text-[9px] text-ink-soft mt-0.5">{{ $date->translatedFormat('M') }}</span>
                        </div>
                        <div>
                            <div class="text-[13px] font-medium text-ink">{{ $date->translatedFormat('l') }}</div>
                            <div class="font-mono text-[12px] text-ink-soft mt-0.5">
                                {{ $record->jam_masuk ?? '—' }} — {{ $record->jam_pulang ?? '—' }}
                            </div>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold px-2 py-1 rounded-full {{ $tone['bg'] }} {{ $tone['fg'] }}">{{ $tone['label'] }}</span>
                </div>
            </summary>
            <div class="mt-3 pt-3 border-t border-line flex items-center gap-2">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#4A5D5A" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                <span class="text-[12px] text-ink-soft">{{ $record->keterangan_lokasi ?? '—' }}</span>
            </div>
        </details>
    @empty
        <div class="rounded-2xl p-6 text-center bg-white border border-line text-[13px] text-ink-soft">
            Belum ada riwayat pada periode ini.
        </div>
    @endforelse
    <div id="filteredHistoryEmpty" class="hidden rounded-2xl p-6 text-center bg-white border border-line text-[13px] text-ink-soft">Tidak ada riwayat dengan status ini.</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filters = document.querySelectorAll('[data-history-filter]');
    const items = document.querySelectorAll('[data-history-item]');
    const empty = document.getElementById('filteredHistoryEmpty');

    filters.forEach(button => button.addEventListener('click', function () {
        const selected = this.dataset.historyFilter;
        let visible = 0;
        filters.forEach(filter => {
            const active = filter === this;
            filter.classList.toggle('bg-ink', active); filter.classList.toggle('text-white', active); filter.classList.toggle('border-ink', active);
            filter.classList.toggle('bg-white', !active); filter.classList.toggle('text-ink-soft', !active); filter.classList.toggle('border-line', !active);
        });
        items.forEach(item => { const show = selected === 'semua' || item.dataset.status === selected; item.classList.toggle('hidden', !show); if (show) visible++; });
        if (empty) empty.classList.toggle('hidden', visible > 0);
    }));
});
</script>
@endpush
