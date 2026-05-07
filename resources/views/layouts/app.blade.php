<!DOCTYPE html>
<html lang="en" x-data="appLayout()" :class="{ 'dark': dark }" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/small_logo.svg') }}" />
    <title>RenovaSim Admin – @yield('title', 'Dashboard')</title>

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['Georgia', '"Times New Roman"', 'serif'],
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        border:      'hsl(var(--border))',
                        input:       'hsl(var(--input))',
                        ring:        'hsl(var(--ring))',
                        background:  'hsl(var(--background))',
                        foreground:  'hsl(var(--foreground))',
                        paragraph:   'hsl(var(--paragraph))',
                        primary: {
                            DEFAULT:    'hsl(var(--primary))',
                            foreground: 'hsl(var(--primary-foreground))',
                            accent:     'hsl(var(--primary-accent))',
                        },
                        secondary: {
                            DEFAULT:    'hsl(var(--secondary))',
                            foreground: 'hsl(var(--secondary-foreground))',
                        },
                        destructive: {
                            DEFAULT:    'hsl(var(--destructive))',
                            foreground: 'hsl(var(--destructive-foreground))',
                        },
                        muted: {
                            DEFAULT:    'hsl(var(--muted))',
                            foreground: 'hsl(var(--muted-foreground))',
                        },
                        accent: {
                            DEFAULT:    'hsl(var(--accent))',
                            foreground: 'hsl(var(--accent-foreground))',
                        },
                        card: {
                            DEFAULT:    'hsl(var(--card))',
                            foreground: 'hsl(var(--card-foreground))',
                        },
                        'status-active':  'hsl(var(--status-active))',
                        'status-warning': 'hsl(var(--status-warning))',
                    },
                    borderRadius: {
                        lg: 'var(--radius)',
                        md: 'calc(var(--radius) - 2px)',
                        sm: 'calc(var(--radius) - 4px)',
                    },
                },
            },
        };
    </script>

    {{-- Theme CSS variables --}}
    <style>
        :root {
            --background: 40 5% 96%;
            --foreground: 30 5% 15%;
            --card: 0 0% 100%;
            --card-foreground: 30 5% 15%;
            --popover: 0 0% 100%;
            --popover-foreground: 30 5% 15%;
            --primary: 78 37% 28%;
            --primary-foreground: 0 0% 100%;
            --primary-accent: 78 65% 38%;
            --secondary: 40 5% 92%;
            --secondary-foreground: 30 5% 15%;
            --muted: 40 5% 92%;
            --muted-foreground: 0 0% 40%;
            --accent: 30 5% 15%;
            --accent-foreground: 0 0% 100%;
            --destructive: 0 60% 50%;
            --destructive-foreground: 0 0% 100%;
            --border: 30 5% 15%;
            --input: 30 5% 15%;
            --ring: 78 37% 28%;
            --radius: 0.5rem;
            --paragraph: 0 0% 45%;
            --status-active: 78 50% 35%;
            --status-warning: 36 70% 45%;
        }
        .dark {
            --background: 30 2% 17.3%;
            --foreground: 0 0% 96.1%;
            --card: 30 2% 20%;
            --card-foreground: 0 0% 96.1%;
            --popover: 30 2% 20%;
            --popover-foreground: 0 0% 96.1%;
            --primary: 78 37% 18%;
            --primary-foreground: 0 0% 96.1%;
            --primary-accent: 78 65% 31%;
            --secondary: 30 2% 22%;
            --secondary-foreground: 0 0% 96.1%;
            --muted: 30 2% 22%;
            --muted-foreground: 0 0% 51.4%;
            --accent: 0 0% 96.1%;
            --accent-foreground: 30 2% 17.3%;
            --destructive: 0 60% 59%;
            --destructive-foreground: 0 0% 96.1%;
            --border: 0 0% 96.1%;
            --input: 0 0% 96.1%;
            --ring: 78 37% 18%;
            --radius: 0.5rem;
            --paragraph: 0 0% 51.4%;
            --status-active: 78 65% 45%;
            --status-warning: 36 70% 55%;
        }
        * { border-color: hsl(var(--border) / 0.1); }
        body { background-color: hsl(var(--background)); color: hsl(var(--foreground)); font-family: Inter, system-ui, sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: Georgia, 'Times New Roman', serif; }

        /* Sidebar transition */
        #sidebar { transition: width 0.3s ease; }
        #main-content { transition: margin-left 0.3s ease; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: hsl(var(--border) / 0.3); border-radius: 9999px; }

        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');
    </style>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('head')
</head>
<body class="min-h-screen bg-background text-foreground font-sans" :class="{ 'overflow-hidden': mobileOpen }">

{{-- Mobile backdrop --}}
<div
    x-show="mobileOpen"
    x-transition:enter="transition-opacity duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="mobileOpen = false"
    class="fixed inset-0 z-40 bg-black/50 sm:hidden"
></div>

{{-- ══════════════════════════════════════════════ SIDEBAR ══════════════════════════════════════════════ --}}
<aside
    id="sidebar"
    :style="{ width: collapsed ? '56px' : '220px' }"
    :class="mobileOpen ? 'translate-x-0' : '-translate-x-full sm:translate-x-0'"
    class="fixed left-0 top-0 h-screen flex flex-col bg-background z-50 border-r border-border transition-all duration-300 ease-in-out"
>
    {{-- Logo + Toggle --}}
    <div class="flex items-center justify-between px-4 h-14 shrink-0">
        <span x-show="!collapsed" class="font-serif text-foreground text-lg tracking-tight">RenovaSim</span>
        {{-- Desktop collapse --}}
        <button @click="collapsed = !collapsed" class="p-1 rounded transition-colors duration-200 hover:bg-muted hidden sm:flex ml-auto">
            <template x-if="collapsed">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-paragraph"><polyline points="9 18 15 12 9 6"/></svg>
            </template>
            <template x-if="!collapsed">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-paragraph"><polyline points="15 18 9 12 15 6"/></svg>
            </template>
        </button>
        {{-- Mobile close --}}
        <button @click="mobileOpen = false" class="p-1 rounded transition-colors hover:bg-muted sm:hidden ml-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-paragraph"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-4 px-2">
        {{-- OVERVIEW --}}
        <div class="mb-4">
            <p x-show="!collapsed" class="text-[10px] uppercase tracking-widest text-paragraph px-3 mb-2 font-sans">Overview</p>
            <a href="{{ url('/dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 transition-all duration-200 text-sm font-sans
                      {{ Request::is('/') || Request::is('dashboard') ? 'bg-primary text-white border-l-2 border-primary-accent' : 'text-paragraph hover:bg-muted hover:text-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span x-show="!collapsed">Dashboard</span>
            </a>
        </div>
        {{-- MANAGE --}}
        <div class="mb-4">
            <p x-show="!collapsed" class="text-[10px] uppercase tracking-widest text-paragraph px-3 mb-2 font-sans">Manage</p>
            <a href="{{ url('/users') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 transition-all duration-200 text-sm font-sans
                      {{ Request::is('users*') ? 'bg-primary text-white border-l-2 border-primary-accent' : 'text-paragraph hover:bg-muted hover:text-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span x-show="!collapsed">Users</span>
            </a>
            <a href="{{ url('/projects') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 transition-all duration-200 text-sm font-sans
                      {{ Request::is('projects*') ? 'bg-primary text-white border-l-2 border-primary-accent' : 'text-paragraph hover:bg-muted hover:text-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span x-show="!collapsed">Projects</span>
            </a>
            <a href="{{ url('/materials') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 transition-all duration-200 text-sm font-sans
                      {{ Request::is('materials*') ? 'bg-primary text-white border-l-2 border-primary-accent' : 'text-paragraph hover:bg-muted hover:text-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <span x-show="!collapsed">Materials</span>
            </a>
            <a href="{{ url('/pricing-plans') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 transition-all duration-200 text-sm font-sans
                      {{ Request::is('pricing-plans*') ? 'bg-primary text-white border-l-2 border-primary-accent' : 'text-paragraph hover:bg-muted hover:text-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span x-show="!collapsed">Pricing Plans</span>
            </a>
            <a href="{{ url('/partners') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 transition-all duration-200 text-sm font-sans
                      {{ Request::is('partners*') ? 'bg-primary text-white border-l-2 border-primary-accent' : 'text-paragraph hover:bg-muted hover:text-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                <span x-show="!collapsed">Partners</span>
            </a>
        </div>
    </nav>

    {{-- Bottom actions --}}
    <div class="px-2 pb-4 border-t border-border/10 shrink-0">
        <button class="flex items-center gap-3 px-3 py-2 w-full rounded-lg text-paragraph hover:bg-muted hover:text-foreground transition-all duration-200 text-sm font-sans mt-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
            <span x-show="!collapsed">Settings</span>
        </button>
        <button class="flex items-center gap-3 px-3 py-2 w-full rounded-lg text-paragraph hover:bg-muted hover:text-foreground transition-all duration-200 text-sm font-sans">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span x-show="!collapsed">Logout</span>
        </button>
    </div>
</aside>

{{-- ══════════════════════════════════════════════ MAIN AREA ══════════════════════════════════════════════ --}}
<div
    id="main-content"
    :style="{ marginLeft: window.innerWidth >= 640 ? (collapsed ? '56px' : '220px') : '0px' }"
    class="flex flex-col min-h-screen transition-all duration-300 ease-in-out"
>
    {{-- Top Navbar --}}
    <header class="h-14 flex items-center justify-between px-4 sm:px-6 bg-card shrink-0" style="border-bottom: 1px solid hsl(var(--border) / 0.1);">
        <div class="flex items-center gap-3">
            {{-- Mobile hamburger --}}
            <button @click="mobileOpen = true" class="sm:hidden p-1.5 rounded-lg text-paragraph hover:text-foreground hover:bg-muted transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <h1 class="font-serif text-foreground text-lg">@yield('page-title', 'Dashboard')</h1>
        </div>
        <div class="flex items-center gap-3 sm:gap-4">
            {{-- Theme toggle --}}
            <button @click="dark = !dark" class="text-paragraph hover:text-foreground transition-colors duration-200" title="Toggle theme">
                <template x-if="dark">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </template>
                <template x-if="!dark">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </template>
            </button>
            {{-- Bell --}}
            <button class="text-paragraph hover:text-foreground transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </button>
            <div class="w-px h-6 bg-border/10"></div>
            {{-- Avatar --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-foreground text-xs font-sans font-medium shrink-0">AD</div>
                <div class="hidden sm:block">
                    <p class="text-sm text-foreground font-sans leading-none">Admin</p>
                    <p class="text-xs text-paragraph font-sans">Administrator</p>
                </div>
            </div>
        </div>
    </header>

    {{-- Page content --}}
    <main class="flex-1 p-4 sm:p-6">
        @yield('content')
    </main>
</div>

@stack('scripts')

<script>
    function appLayout() {
        return {
            collapsed: false,
            mobileOpen: false,
            dark: localStorage.getItem('theme') !== 'light',
            init() {
                this.$watch('dark', val => {
                    localStorage.setItem('theme', val ? 'dark' : 'light');
                    document.documentElement.classList.toggle('dark', val);
                });
                document.documentElement.classList.toggle('dark', this.dark);
                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 640) this.mobileOpen = false;
                });
            }
        }
    }
</script>
</body>
</html>
