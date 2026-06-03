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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <title>{{ $title ?? 'RenovaSim' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/small_logo.svg') }}" />
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
    @stack('head')
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

    {{-- Global Flash Messages --}}
    @php
    $toasts = array_values(array_filter([
        session('success')          ? ['type'=>'success', 'msg'=>session('success')]          : null,
        session('error')            ? ['type'=>'error',   'msg'=>session('error')]            : null,
        session('warning')          ? ['type'=>'warning', 'msg'=>session('warning')]          : null,
        session('success_profile')  ? ['type'=>'success', 'msg'=>session('success_profile')]  : null,
        session('success_password') ? ['type'=>'success', 'msg'=>session('success_password')] : null,
        session('error_profile')    ? ['type'=>'error',   'msg'=>session('error_profile')]    : null,
        session('error_password')   ? ['type'=>'error',   'msg'=>session('error_password')]   : null,
    ]));
    @endphp
    @if(count($toasts) > 0)
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2"
         x-data="toastManager({{ Js::from($toasts) }})"
         @keydown.escape.window="dismissAll()">
        <template x-for="(toast, i) in toasts" :key="i">
            <div x-show="toast.visible"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 class="flex items-center w-72 sm:w-80 rounded-xl bg-card border border-border shadow-lg px-3 py-3 gap-3">
                <div class="shrink-0 p-1.5 rounded-lg" :class="iconBg(toast.type)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="2" stroke="currentColor" class="w-5 h-5" :class="iconColor(toast.type)">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath(toast.type)"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[12px] font-semibold font-['DM_Sans'] text-card-foreground" x-text="toastTitle(toast.type)"></p>
                    <p class="text-[11px] font-['DM_Sans'] text-muted-foreground leading-snug mt-0.5 break-words" x-text="toast.msg"></p>
                </div>
                <button @click="dismiss(i)"
                        class="shrink-0 text-muted-foreground hover:bg-muted p-1 rounded-md transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>
    @endif

    <main
        :class="ready ? 'transition-[padding] duration-300 ease-in-out' : ''"
        :style="isMdUp ? { paddingLeft: effectiveCollapsed ? '112px' : '264px' } : {}"
        class="px-4 sm:px-6 lg:px-8 py-6 lg:py-8"
    >
        <div class="mx-auto max-w-[1400px]">
            {!! $slot !!}
        </div>
    </main>
    @stack('scripts')
    <script>
    function toastManager(initial) {
        return {
            toasts: initial.map(t => ({ ...t, visible: true })),
            init() {
                this.toasts.forEach((_, i) => {
                    setTimeout(() => this.dismiss(i), 5000 + i * 300);
                });
            },
            dismiss(i) { this.toasts[i].visible = false; },
            dismissAll() { this.toasts.forEach(t => t.visible = false); },
            toastTitle(type) {
                return { success: 'Berhasil', error: 'Terjadi Kesalahan', warning: 'Perhatian' }[type] ?? 'Notifikasi';
            },
            iconBg(type) {
                return { success: 'bg-primary/10', error: 'bg-destructive/10', warning: 'bg-amber-500/10' }[type] ?? 'bg-muted';
            },
            iconColor(type) {
                return { success: 'text-primary', error: 'text-destructive', warning: 'text-amber-500' }[type] ?? 'text-muted-foreground';
            },
            iconPath(type) {
                const paths = {
                    success: 'm4.5 12.75 6 6 9-13.5',
                    error:   'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
                    warning: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
                };
                return paths[type] ?? paths.success;
            },
        };
    }
    </script>
</body>
</html>
