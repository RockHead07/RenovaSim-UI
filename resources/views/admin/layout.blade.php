<!DOCTYPE html>
  <html lang="en" id="html-root" class="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/small_logo.svg') }}" />
    <title>@yield('title', 'RenovaSim Admin')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      (function() {
        const htmlRoot = document.getElementById('html-root');
        const stored = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDark = stored ? stored === 'dark' : prefersDark;
        if (isDark) {
          htmlRoot.classList.add('dark');
        } else {
          htmlRoot.classList.remove('dark');
        }
        window.currentTheme = isDark ? 'dark' : 'light';
      })();
    </script>
    <script>
      window.apiFetch = async function(url, options = {}) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const method = (options.method || 'GET').toUpperCase();
        const hasBody = method !== 'GET' && method !== 'HEAD';
        const defaults = {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            ...(hasBody ? { 'Content-Type': 'application/json' } : {}),
          },
          credentials: 'same-origin',
        };
        const merged = {
          ...defaults,
          ...options,
          headers: { ...defaults.headers, ...(options.headers || {}) },
        };
        const res = await fetch(url, merged);
        if (!res.ok) {
          const err = await res.json().catch(() => ({}));
          throw { status: res.status, ...err };
        }
        return res.json();
      };
    </script>
    <style>
      :root{--background:40 5% 96%;--foreground:30 5% 15%;--card:0 0% 100%;--card-foreground:30 5% 15%;--popover:0 0% 100%;--popover-foreground:30 5% 15%;--primary:78 37% 28%;--primary-foreground:0 0% 100%;--primary-accent:78 65% 38%;--secondary:40 5% 92%;--secondary-foreground:30 5% 15%;--muted:40 5% 92%;--muted-foreground:0 0% 40%;--accent:30 5% 15%;--accent-foreground:0 0% 100%;--destructive:0 60% 50%;--destructive-foreground:0 0% 100%;--border:30 5% 15%;--input:30 5% 15%;--ring:78 37% 28%;--radius:.5rem;--paragraph:0 0% 45%;--status-active:78 50% 35%;--status-warning:36 70% 45%}.dark{--background:30 2% 17.3%;--foreground:0 0% 96.1%;--card:30 2% 20%;--card-foreground:0 0% 96.1%;--popover:30 2% 20%;--popover-foreground:0 0% 96.1%;--primary:78 37% 18%;--primary-foreground:0 0% 96.1%;--primary-accent:78 65% 31%;--secondary:30 2% 22%;--secondary-foreground:0 0% 96.1%;--muted:30 2% 22%;--muted-foreground:0 0% 51.4%;--accent:0 0% 96.1%;--accent-foreground:30 2% 17.3%;--destructive:0 60% 59%;--destructive-foreground:0 0% 96.1%;--border:0 0% 96.1%;--input:0 0% 96.1%;--ring:78 37% 18%;--radius:.5rem;--paragraph:0 0% 51.4%;--status-active:78 65% 45%;--status-warning:36 70% 55%}h1,h2,h3,h4,h5,h6{font-family:var(--font-serif,'PP Editorial New',Georgia,serif)}body{font-family:var(--font-sans,'PP Neue Montreal',Inter,sans-serif)}
    </style>
    @stack('head')
  </head>
  <body class="bg-background text-foreground font-sans">
    <div class="flex h-screen overflow-hidden" x-data="{ collapsed: false, mobileOpen: false, darkMode: window.currentTheme === 'dark' }">
      <div x-show="mobileOpen" x-transition.opacity x-on:click="mobileOpen=false" class="fixed inset-0 z-40 bg-black/50 sm:hidden"></div>
      <aside :class="[collapsed ? 'sm:w-14' : 'sm:w-56', mobileOpen ? 'translate-x-0' : '-translate-x-full sm:translate-x-0']" class="fixed sm:static left-0 top-0 z-50 h-screen w-56 bg-black/10 border-r border-border/5 flex flex-col transition-all duration-300 rounded-tr-lg rounded-br-lg overflow-hidden" >
        <div class="flex items-center justify-between px-4 h-14 shrink-0">
          <span x-show="!collapsed" x-transition class="font-serif text-foreground text-lg tracking-tight">RenovaSim</span>
          <button type="button" x-on:click="collapsed=!collapsed" class="p-1 rounded transition-colors duration-200 hover:bg-muted hidden sm:flex text-paragraph"><svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path x-show="!collapsed" d="m15 18-6-6 6-6"/><path x-show="collapsed" d="m9 18 6-6-6-6"/></svg></button>
          <button type="button" x-on:click="mobileOpen=false" class="p-1 rounded transition-colors duration-200 hover:bg-muted sm:hidden ml-auto text-paragraph">×</button>
        </div>
        <nav class="flex-1 overflow-y-auto py-4 px-2">
          <div class="mb-4">
            <p x-show="!collapsed" class="text-[10px] uppercase tracking-widest text-paragraph px-3 mb-2 font-sans">OVERVIEW</p>
            
  <a href="/admin/dashboard" class="{{ request()->is('admin/dashboard*') ? 'bg-primary text-primary-foreground border-l-2 border-primary-accent' : 'text-paragraph hover:text-foreground hover:bg-muted' }} flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 transition-all duration-200 text-sm font-sans">
    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
    <span x-show="!collapsed" x-transition>Dashboard</span>
  </a>
          </div>
          <div class="mb-4">
            <p x-show="!collapsed" class="text-[10px] uppercase tracking-widest text-paragraph px-3 mb-2 font-sans">MANAGE</p>
            
  <a href="/admin/users" class="{{ request()->is('admin/users*') ? 'bg-primary text-primary-foreground border-l-2 border-primary-accent' : 'text-paragraph hover:text-foreground hover:bg-muted' }} flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 transition-all duration-200 text-sm font-sans">
    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    <span x-show="!collapsed" x-transition>Users</span>
  </a>
            
  <a href="/admin/projects" class="{{ request()->is('admin/projects*') ? 'bg-primary text-primary-foreground border-l-2 border-primary-accent' : 'text-paragraph hover:text-foreground hover:bg-muted' }} flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 transition-all duration-200 text-sm font-sans">
    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
    <span x-show="!collapsed" x-transition>Projects</span>
  </a>

  <a href="/admin/rooms" class="{{ request()->is('admin/rooms*') ? 'bg-primary text-primary-foreground border-l-2 border-primary-accent' : 'text-paragraph hover:text-foreground hover:bg-muted' }} flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 transition-all duration-200 text-sm font-sans">
    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
    <span x-show="!collapsed" x-transition>3D Saves</span>
  </a>
            
  <a href="/admin/materials" class="{{ request()->is('admin/materials*') ? 'bg-primary text-primary-foreground border-l-2 border-primary-accent' : 'text-paragraph hover:text-foreground hover:bg-muted' }} flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 transition-all duration-200 text-sm font-sans">
    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 16V8"/><path d="M12 16V5"/><path d="M17 16v-3"/></svg>
    <span x-show="!collapsed" x-transition>Materials</span>
  </a>
            
  <a href="/admin/pricing-plans" class="{{ request()->is('admin/pricing-plans*') ? 'bg-primary text-primary-foreground border-l-2 border-primary-accent' : 'text-paragraph hover:text-foreground hover:bg-muted' }} flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 transition-all duration-200 text-sm font-sans">
    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
    <span x-show="!collapsed" x-transition>Pricing Plans</span>
  </a>
            
  <a href="/admin/partners" class="{{ request()->is('admin/partners*') ? 'bg-primary text-primary-foreground border-l-2 border-primary-accent' : 'text-paragraph hover:text-foreground hover:bg-muted' }} flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 transition-all duration-200 text-sm font-sans">
    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 17H7A5 5 0 0 1 7 7h2"/><path d="M15 7h2a5 5 0 1 1 0 10h-2"/><line x1="8" x2="16" y1="12" y2="12"/></svg>
    <span x-show="!collapsed" x-transition>Partners</span>
  </a>
          </div>
        </nav>
        <div class="px-2 pb-4 border-t border-border/10 shrink-0">
          <a href="/admin/settings" class="flex items-center gap-3 px-3 py-2 w-full rounded-lg text-paragraph hover:bg-muted hover:text-foreground transition-all duration-200 text-sm font-sans mt-2"><svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.51a2 2 0 0 1 1-1.72l.15-.1a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg><span x-show="!collapsed">Settings</span></a>
          <form method="POST" action="/logout">@csrf<button type="submit" class="flex items-center gap-3 px-3 py-2 w-full rounded-lg text-paragraph hover:bg-muted hover:text-foreground transition-all duration-200 text-sm font-sans"><svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg><span x-show="!collapsed">Logout</span></button></form>
        </div>
      </aside>
      <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-card border-b border-border/10 h-14 flex items-center justify-between px-4 sm:px-6 shrink-0">
          <div class="flex items-center gap-3">
            <button type="button" x-on:click="mobileOpen=true" class="sm:hidden p-1.5 rounded-lg text-paragraph hover:text-foreground hover:bg-muted transition-colors">☰</button>
            <h1 class="font-serif text-foreground text-lg">@yield('page-title', 'Dashboard')</h1>
          </div>
          <div class="flex items-center gap-3 sm:gap-4">
            <button type="button" class="text-paragraph hover:text-foreground transition-colors duration-200"><svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.268 21a2 2 0 0 0 3.464 0"/><path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/></svg></button>
            <div class="w-px h-6 bg-border/10"></div>
            <div class="flex items-center gap-2 sm:gap-3"><div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-foreground text-xs font-sans font-medium shrink-0">AD</div><div class="hidden sm:block"><p class="text-sm text-foreground font-sans leading-none">Admin</p><p class="text-xs text-paragraph font-sans">Administrator</p></div></div>
          </div>
        </header>
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 bg-background">
          @if(session('success'))<div class="border-l-4 px-4 py-3 rounded mb-4 text-sm text-foreground" style="background:rgba(139,160,35,0.15);border-color:#8BA023">{{ session('success') }}</div>@endif
          @if(session('error'))<div class="border-l-4 px-4 py-3 rounded mb-4 text-sm text-foreground" style="background:rgba(220,80,80,0.15);border-color:#e07070">{{ session('error') }}</div>@endif
          @yield('content')
        </main>
      </div>
    </div>
    @stack('scripts')
  </body>
  </html>
  
