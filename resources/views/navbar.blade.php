{{-- ============================================================
     navbar.blade.php
     Tech stack: Laravel Blade · Tailwind CSS · Vanilla JS
     Converted from: src/components/Navbar.tsx + NavLink.tsx
     ============================================================ --}}

@php
$navLinks = [
    ['label' => 'How It Works', 'href' => '#how-it-works', 'desc' => 'See how RenovaSim works'],
    ['label' => 'Features',     'href' => '#features',     'desc' => 'What we offer'          ],
    ['label' => 'Pricing',      'href' => '#pricing',      'desc' => 'Plans & pricing'        ],
    ['label' => 'FAQ',          'href' => '#faq',          'desc' => 'Common questions'       ],
];
@endphp

{{-- ── Top-edge blur mask (fades in on scroll) ─────────────────── --}}
<div
    id="nav-blur-mask"
    class="fixed top-0 left-0 right-0 z-45 pointer-events-none h-14 opacity-0 transition-opacity duration-500 nav-blur-mask"
></div>

{{-- ── Scroll progress bar ─────────────────────────────────────── --}}
<div class="fixed top-0 left-0 right-0 z-60 h-0.5 pointer-events-none">
    <div
        id="scroll-progress"
        class="h-full bg-linear-to-r from-primary via-[hsl(78_50%_35%)] to-primary/40 transition-[width] duration-100 ease-linear"
        style="width: 0%"
    ></div>
</div>

{{-- ── Main nav ────────────────────────────────────────────────── --}}
<nav
    id="main-nav"
    class="fixed z-50 left-0 right-0 flex items-center justify-center top-2 px-5 md:px-16 py-3 transition-all duration-500 nav-ease"
>
    <div
        id="nav-inner"
        class="flex items-center w-full max-w-full bg-transparent justify-between transition-all duration-500 nav-ease"
    >
        {{-- ── Left: Logo / Home ── --}}
        <div class="flex items-center shrink-0" >
            {{-- Full logo — visible at top --}}
            <img
                id="logo-full"
                src="{{ asset('images/logo.svg') }}"
                alt="RenovaSim"
                class="object-contain h-6 w-auto opacity-100 transition-all duration-500"
                style="filter: brightness(0) invert(1) brightness(0.95);"
            />
            {{-- Compact logo — visible in pill --}}
            <a
                id="logo-pill"
                href="#"
                onclick="window.scrollTo({ top: 0, behavior: 'smooth' }); return false;"
                class="shrink-0 w-0 h-0 opacity-0 overflow-hidden pointer-events-none transition-all duration-500"
            >
                <img src="{{ asset('images/logo.svg') }}" alt="RenovaSim" class="h-5 w-auto object-contain" style="filter: brightness(0) invert(1) brightness(0.95);" />
            </a>
        </div>

        {{-- ── Centre: Desktop links ── --}}
        <div id="desktop-links" class="hidden md:flex items-center gap-6">
            @foreach ($navLinks as $i => $link)
                <a
                    href="{{ $link['href'] }}"
                    data-hash="{{ $link['href'] }}"
                    style="animation-delay: {{ $i * 60 }}ms"
                    class="nav-link relative whitespace-nowrap tracking-wide text-sm font-light py-1 text-foreground/60 hover:text-foreground transition-all duration-200 animate-fade-in opacity-0"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        {{-- ── Right: CTA + Hamburger ── --}}
        <div class="flex items-center gap-2 shrink-0">
            <a
                id="cta-btn"
                href="#pricing"
                class="hidden md:inline-flex relative overflow-hidden rounded-full bg-foreground text-background font-medium px-5 py-2.5 text-sm transition-all duration-200 active:scale-[0.96] whitespace-nowrap hover:opacity-90 cta-shimmer"
            >
                <span id="cta-label">Get Started</span>
            </a>

            {{-- Hamburger — mobile only --}}
            <button
                id="hamburger-btn"
                class="md:hidden flex items-center justify-center w-9 h-9 rounded-full text-foreground/70 hover:text-foreground hover:bg-white/10 transition-all duration-200"
                onclick="openMobileMenu()"
                aria-label="Open menu"
            >
                {{-- Menu icon (SVG equivalent of lucide Menu) --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>
</nav>

{{-- ════════════════════════════════════════════════════════════════
     Mobile bottom sheet
════════════════════════════════════════════════════════════════ --}}

{{-- Backdrop --}}
<div
    id="mobile-backdrop"
    onclick="closeMobileMenu()"
    class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm transition-opacity duration-300 md:hidden opacity-0 pointer-events-none"
></div>

{{-- Sheet --}}
<div
    id="mobile-sheet"
    class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-[hsl(60_1.1%_11%)] rounded-t-4xl border-t border-white/8 flex flex-col translate-y-full transition-transform duration-500 nav-ease"
    style="max-height: 88vh"
>
    {{-- Drag handle --}}
    <div class="flex justify-center pt-3 pb-1">
        <div class="w-10 h-1 rounded-full bg-white/20"></div>
    </div>

    {{-- Sheet header --}}
    <div class="flex items-center justify-between px-6 py-4">
        <div>
            <img src="{{ asset('images/logo.svg') }}" alt="RenovaSim" class="h-5 object-contain mb-0.5" style="filter: brightness(0) invert(1) brightness(0.95);" />
            <p class="text-foreground/30 text-[10px] font-light tracking-wide">AI-powered renovation planning</p>
        </div>
        <button
            onclick="closeMobileMenu()"
            aria-label="Close menu"
            class="flex items-center justify-center w-8 h-8 rounded-full bg-white/[0.07] text-foreground/50 hover:text-foreground transition-all duration-200"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Divider --}}
    <div class="mx-6 h-px bg-white/6"></div>

    {{-- Nav links --}}
    <nav class="flex flex-col px-4 py-4 gap-1 overflow-y-auto flex-1">
        @foreach ($navLinks as $i => $link)
            <button
                data-href="{{ $link['href'] }}"
                onclick="handleMobileLink('{{ $link['href'] }}')"
                class="mobile-nav-link w-full text-left px-4 py-4 rounded-2xl transition-all duration-200 flex items-center justify-between group text-foreground/60 hover:text-foreground hover:bg-white/4"
            >
                <div class="flex items-center gap-4">
                    <span class="text-[10px] font-medium w-5 h-5 rounded-full flex items-center justify-center shrink-0 bg-white/[0.07] text-foreground/30 transition-all duration-200">
                        {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <div>
                        <p class="text-sm font-light tracking-wide leading-tight">{{ $link['label'] }}</p>
                        <p class="text-[11px] font-light mt-0.5 text-foreground/25 transition-colors duration-200">{{ $link['desc'] }}</p>
                    </div>
                </div>
                {{-- ChevronRight --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 text-foreground/20 group-hover:text-foreground/40 transition-all duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
                </svg>
            </button>
        @endforeach
    </nav>

    {{-- Divider --}}
    <div class="mx-6 h-px bg-white/6"></div>

    {{-- CTA footer --}}
    <div class="px-5 pt-4 pb-8 space-y-2">
        <a
            href="#pricing"
            onclick="closeMobileMenu()"
            class="relative overflow-hidden block text-center w-full rounded-full bg-foreground text-background text-sm font-medium px-5 py-4 hover:opacity-90 transition-opacity duration-200 active:scale-[0.97] cta-shimmer"
        >
            Get Started
            <span class="text-xs">↗</span>
        </a>
        <p class="text-center text-foreground/25 text-xs font-light">No credit card required</p>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════
     Vanilla JS — scroll tracking, active hash, mobile menu
     (replaces React useState / useEffect logic)
════════════════════════════════════════════════════════════════ --}}
<script>
(function () {
    /* ── State ── */
    var scrolled   = false;
    var activeHash = '';

    /* ── DOM refs ── */
    var blurMask     = document.getElementById('nav-blur-mask');
    var progressBar  = document.getElementById('scroll-progress');
    var mainNav      = document.getElementById('main-nav');
    var navInner     = document.getElementById('nav-inner');
    var logoFull     = document.getElementById('logo-full');
    var logoPill     = document.getElementById('logo-pill');
    var desktopLinks = document.getElementById('desktop-links');
    var ctaBtn       = document.getElementById('cta-btn');
    var ctaLabel     = document.getElementById('cta-label');
    var backdrop     = document.getElementById('mobile-backdrop');
    var sheet        = document.getElementById('mobile-sheet');

    /* ── Scroll handler ── */
    function onScroll() {
        var sy  = window.scrollY;
        var max = document.documentElement.scrollHeight - window.innerHeight;

        /* Progress bar */
        progressBar.style.width = (max > 0 ? (sy / max) * 100 : 0) + '%';

        /* Blur mask opacity */
        blurMask.style.opacity = Math.min(sy / 80, 1);

        /* Pill / expanded toggle */
        var nowScrolled = sy > 80;
        if (nowScrolled === scrolled) return;
        scrolled = nowScrolled;

        if (scrolled) {
            /* Nav wrapper — pill */
            mainNav.classList.replace('top-2',  'top-4');
            mainNav.classList.replace('px-5',   'px-3');
            mainNav.classList.remove('md:px-16', 'py-3');

            /* Inner — compact */
            navInner.className = [
                'flex items-center w-full transition-all duration-500 nav-ease',
                'max-w-[540px] rounded-full px-3 py-2',
                'bg-[hsl(0_0%_6%/0.95)] backdrop-blur-xl',
                'shadow-[0_0_0_1px_rgba(255,255,255,0.07),0_8px_40px_rgba(0,0,0,0.5)]',
                'gap-1 justify-between relative overflow-visible nav-glow',
            ].join(' ');

            /* Logos */
            logoFull.classList.replace('h-6',      'h-0');
            logoFull.classList.replace('w-auto',   'w-0');
            logoFull.classList.replace('opacity-100', 'opacity-0');
            logoPill.classList.remove('w-0', 'h-0', 'opacity-0', 'overflow-hidden', 'pointer-events-none');
            logoPill.classList.add('opacity-100');

            /* Desktop links — compact */
            desktopLinks.classList.replace('gap-6', 'gap-0');
            desktopLinks.classList.add('mx-1');
            document.querySelectorAll('.nav-link').forEach(function (a) {
                a.classList.remove('text-sm', 'font-light', 'py-1');
                a.classList.add('px-3', 'py-1.5', 'text-xs', 'rounded-full');
            });

            /* CTA — compact */
            ctaBtn.classList.remove('hidden', 'md:inline-flex', 'px-5', 'py-2.5', 'text-sm');
            ctaBtn.classList.add('inline-flex', 'px-3.5', 'py-1.5', 'text-xs');
            ctaLabel.textContent = 'Get Started';
        } else {
            /* Nav wrapper — expanded */
            mainNav.classList.replace('top-4', 'top-2');
            mainNav.classList.replace('px-3',  'px-5');
            mainNav.classList.add('md:px-16', 'py-3');

            /* Inner — expanded */
            navInner.className = 'flex items-center w-full max-w-full bg-transparent justify-between transition-all duration-500 nav-ease';

            /* Logos */
            logoFull.classList.replace('h-0',      'h-6');
            logoFull.classList.replace('w-0',      'w-auto');
            logoFull.classList.replace('opacity-0','opacity-100');
            logoPill.classList.add('w-0', 'h-0', 'opacity-0', 'overflow-hidden', 'pointer-events-none');
            logoPill.classList.remove('opacity-100');

            /* Desktop links — expanded */
            desktopLinks.classList.replace('gap-0', 'gap-6');
            desktopLinks.classList.remove('mx-1');
            document.querySelectorAll('.nav-link').forEach(function (a) {
                a.classList.add('text-sm', 'font-light', 'py-1');
                a.classList.remove('px-3', 'py-1.5', 'text-xs', 'rounded-full');
            });

            /* CTA — expanded */
            ctaBtn.classList.remove('inline-flex', 'px-3.5', 'py-1.5', 'text-xs');
            ctaBtn.classList.add('hidden', 'md:inline-flex', 'px-5', 'py-2.5', 'text-sm');
            ctaLabel.textContent = 'Get Started';
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });

    /* ── Active section (IntersectionObserver) ── */
    var hashes    = ['#how-it-works', '#features', '#pricing', '#faq'];
    var observer  = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) setActiveHash('#' + e.target.id);
        });
    }, { rootMargin: '-40% 0px -55% 0px' });

    hashes.forEach(function (href) {
        var el = document.getElementById(href.slice(1));
        if (el) observer.observe(el);
    });

    function setActiveHash(hash) {
        activeHash = hash;
        document.querySelectorAll('.nav-link').forEach(function (a) {
            var isActive = a.dataset.hash === hash;
            if (scrolled) {
                a.classList.toggle('text-foreground',          isActive);
                a.classList.toggle('bg-white/[0.12]',         isActive);
                a.classList.toggle('text-foreground/55',      !isActive);
            } else {
                a.classList.toggle('text-foreground',         isActive);
                a.classList.toggle('text-foreground/60',      !isActive);
            }
        });
    }

    /* ── Mobile menu ── */
    window.openMobileMenu = function () {
        document.body.style.overflow = 'hidden';
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        backdrop.classList.add('opacity-100', 'pointer-events-auto');
        sheet.classList.remove('translate-y-full');
        sheet.classList.add('translate-y-0');
    };

    window.closeMobileMenu = function () {
        document.body.style.overflow = '';
        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
        backdrop.classList.add('opacity-0', 'pointer-events-none');
        sheet.classList.remove('translate-y-0');
        sheet.classList.add('translate-y-full');
    };

    window.handleMobileLink = function (href) {
        closeMobileMenu();
        setTimeout(function () {
            var el = document.querySelector(href);
            if (el) el.scrollIntoView({ behavior: 'smooth' });
        }, 350);
    };
})();
</script>
