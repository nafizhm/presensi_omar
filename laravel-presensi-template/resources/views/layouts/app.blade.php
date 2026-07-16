<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>@yield('title', 'Presensi')</title>
    @vite(['resources/css/presensi.css', 'resources/js/app.js'])
</head>
<body class="font-body text-ink">
    <div class="phone-frame relative">
        <div class="pb-24 min-h-[100dvh]">
            @yield('content')
        </div>

        @include('partials.bottom-nav')
    </div>

    @stack('scripts')
</body>
</html>
