{{-- ============================================================
     layouts.dashboard — port of src/components/dashboard/DashboardLayout.tsx
     Sidebar shell. Uses Alpine for collapse + mobile drawer state.
     `isTablet` mirrors useIsTablet() (768-1023px) and forces
     `effectiveCollapsed = true` on tablets, matching the React behavior.

     `ready` flag: starts false, set true after first tick so the sidebar
     and main never animate on initial page load (prevents jitter/pulse).
============================================================ --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'RenovaSim' }}</title>
    {{-- Prevent sidebar FOUC: read localStorage synchronously before render --}}
    {{-- Prevent sidebar FOUC: read localStorage synchronously before render --}}
    <script>
    (function() {
        var c = localStorage.getItem('sidebar-collapsed') === 'true';
        var t = window.matchMedia('(min-width: 768px) and (max-width: 1023px)').matches;
        document.documentElement.setAttribute('data-sb', (t || c) ? '1' : '0');
    })();
    </script>
    <style>
        @media (min-width: 768px) {
            /* Width + padding */
            html[data-sb="1"] aside { width: 80px; padding: 12px; }
            html[data-sb="0"] aside { width: 240px; padding: 16px; }
            html[data-sb="1"] main  { padding-left: 112px; }
            html[data-sb="0"] main  { padding-left: 264px; }

            /* Hide text elements when collapsed */
            html[data-sb="1"] aside .sb-hide { display: none; }

            /* Center logo when collapsed */
            html[data-sb="1"] aside .sb-logo {
                justify-content: center;
                width: 100%;
                padding-left: 0;
                padding-right: 0;
            }

            /* Icon-only nav items when collapsed */
            html[data-sb="1"] aside nav {
                align-items: center;
                gap: 12px;
                width: 100%;
            }
            html[data-sb="1"] aside nav a {
                width: 44px;
                height: 44px;
                padding: 0;
                gap: 0;
                justify-content: center;
            }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/user/theme/css/user.css', 'resources/user/theme/js/user.js'])
</head>
<body
    class="theme-user min-h-screen bg-background"
    x-data="{
        collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
        mobileOpen: false,
        isTablet: window.matchMedia('(min-width: 768px) and (max-width: 1023px)').matches,
        isMdUp: window.matchMedia('(min-width: 768px)').matches,
        ready: false,
        init() {
            const mqTablet = window.matchMedia('(min-width: 768px) and (max-width: 1023px)');
            const mqMd     = window.matchMedia('(min-width: 768px)');
            mqTablet.addEventListener('change', (e) => { this.isTablet = e.matches; });
            mqMd.addEventListener('change',     (e) => { this.isMdUp   = e.matches; });
            this.$watch('collapsed', val => {
                localStorage.setItem('sidebar-collapsed', val);
                var t = window.matchMedia('(min-width: 768px) and (max-width: 1023px)').matches;
                document.documentElement.setAttribute('data-sb', (t || val) ? '1' : '0');
            });
            this.$nextTick(() => { this.ready = true; });
        },
        get effectiveCollapsed() { return this.isTablet ? true : this.collapsed; },
        get canToggle() { return !this.isTablet; }
    }"
>
    {{-- Mobile top bar (only below tablet) --}}
    <div class="md:hidden sticky top-0 z-30 flex items-center justify-between bg-card/95 backdrop-blur px-4 py-3 border-b border-border">
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
        class="md:hidden fixed inset-0 bg-black/40 z-30"
        style="display:none"
    ></div>

    <x-user::components.layout.sidebar />

    <main
        :class="ready ? 'transition-[padding] duration-300 ease-in-out' : ''"
        :style="isMdUp ? { paddingLeft: effectiveCollapsed ? '112px' : '264px' } : {}"
        class="px-4 sm:px-6 lg:px-8 py-6 lg:py-8"
    >
        <div class="mx-auto max-w-[1400px]">
            {!! $slot !!}
        </div>
    </main>
</body>
</html>
