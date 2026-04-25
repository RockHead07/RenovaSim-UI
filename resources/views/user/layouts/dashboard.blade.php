{{-- ============================================================
     layouts.dashboard — port of src/components/dashboard/DashboardLayout.tsx
     Sidebar shell. Uses Alpine for collapse + mobile drawer state.
============================================================ --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'RenovaSim' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="{ collapsed: false, mobileOpen: false }"
    class="min-h-screen bg-background"
>
    {{-- Mobile top bar --}}
    <div class="lg:hidden sticky top-0 z-30 flex items-center justify-between bg-card/95 backdrop-blur px-4 py-3 border-b border-border">
        <button
            @click="mobileOpen = true"
            aria-label="Open menu"
            class="w-10 h-10 rounded-xl flex items-center justify-center text-card-foreground hover:bg-muted transition-colors"
        >
            <x-lucide-menu class="w-5 h-5" />
        </button>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center">
                <span class="text-primary-foreground font-bold text-xs">R</span>
            </div>
            <span class="font-['Playfair_Display'] italic text-base text-secondary">RenovaSim</span>
        </div>
        <div class="w-10"></div>
    </div>

    {{-- Mobile drawer backdrop --}}
    <div
        x-show="mobileOpen"
        x-transition.opacity
        @click="mobileOpen = false"
        class="lg:hidden fixed inset-0 bg-black/40 z-30"
        style="display:none"
    ></div>

    <x-sidebar />

    <main
        :class="collapsed ? 'lg:pl-[112px]' : 'lg:pl-[264px]'"
        class="transition-[padding] duration-300 ease-in-out px-4 sm:px-6 lg:px-8 py-6 lg:py-8"
    >
        <div class="mx-auto max-w-[1400px]">
            {!! $slot !!}
        </div>
    </main>
</body>
</html>
