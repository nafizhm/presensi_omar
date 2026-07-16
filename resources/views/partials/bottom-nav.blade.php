@php
    $tabs = [
        ['route' => 'presensi.beranda', 'label' => 'Beranda', 'icon' => 'home'],
        ['route' => 'presensi.checkin', 'label' => 'Presensi', 'icon' => 'map-pin'],
        ['route' => 'presensi.riwayat', 'label' => 'Riwayat', 'icon' => 'clock'],
        ['route' => 'presensi.profil', 'label' => 'Profil', 'icon' => 'user'],
    ];

    $icons = [
        'home' => '<path d="M3 9.5 12 3l9 6.5"/><path d="M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10"/>',
        'map-pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
        'user' => '<path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="8" r="4"/>',
    ];
@endphp

<div class="fixed bottom-0 left-0 right-0 mx-auto max-w-[430px] flex items-center justify-around pt-2 pb-6 bg-white border-t border-line z-30">
    @foreach ($tabs as $tab)
        @php $active = request()->routeIs($tab['route']); @endphp
        <a href="{{ route($tab['route']) }}" class="flex flex-col items-center gap-1 px-3 py-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="{{ $active ? '#0B7A70' : '#4A5D5A' }}" stroke-width="{{ $active ? '2.4' : '2' }}"
                 stroke-linecap="round" stroke-linejoin="round">
                {!! $icons[$tab['icon']] !!}
            </svg>
            <span class="text-[10px] font-medium {{ $active ? 'text-teal-dark' : 'text-ink-soft' }}">
                {{ $tab['label'] }}
            </span>
        </a>
    @endforeach
</div>
