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
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/small_logo.svg') }}" />
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/user/theme/css/user.css', 'resources/user/theme/js/user.js'])
</head>
<body class="theme-user min-h-screen bg-background flex flex-col">
    @if (! ($hideNav ?? false))
        <x-user::components.layout.app-nav />
    @endif

    {{ $slot }}

    @if (! ($hideFooter ?? false))
        <x-user::components.layout.app-footer :class="$footerClass ?? ''" />
    @endif
</body>
</html>
