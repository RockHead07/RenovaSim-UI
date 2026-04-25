{{-- ============================================================
     layouts.app — wizard / result / RAB layout
     AppNav at top, AppFooter at bottom (unless suppressed by page).
============================================================ --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'RenovaSim' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background flex flex-col">
    @if (! ($hideNav ?? false))
        <x-app-nav />
    @endif

    {!! $slot !!}

    @if (! ($hideFooter ?? false))
        <x-app-footer :class="$footerClass ?? ''" />
    @endif
</body>
</html>
