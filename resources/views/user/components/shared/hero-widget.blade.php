{{-- Glassmorphism CTA card — port of HeroWidget.tsx --}}
<div class="relative overflow-hidden rounded-[20px] sm:rounded-[24px] shadow-sm p-6 sm:p-10 bg-card min-h-[240px] sm:min-h-[280px] flex flex-col justify-between">
    <div class="absolute inset-0 z-0">
        <div class="absolute -top-20 -right-16 w-72 h-72 rounded-full bg-primary/30 blur-3xl"></div>
        <div class="absolute -bottom-24 -left-10 w-80 h-80 rounded-full bg-secondary/15 blur-3xl"></div>
        <div class="absolute inset-0 backdrop-blur-2xl bg-white/40"></div>
    </div>

    <div class="relative z-10 flex flex-col gap-3 max-w-md">
        <div class="inline-flex items-center gap-2 bg-white/60 backdrop-blur border border-white/80 rounded-full px-3 py-1 w-fit">
            <x-lucide-sparkles class="w-[13px] h-[13px] text-primary" />
            <span class="text-[11px] uppercase tracking-wider text-secondary font-medium">Powered by RAI</span>
        </div>
        <h2 class="font-['Playfair_Display'] text-[26px] sm:text-[34px] lg:text-[40px] leading-[1.05] text-secondary">
            Plan & Estimate <span class="italic">with RAI</span>
        </h2>
        <p class="text-sm sm:text-[15px] text-card-foreground/80 max-w-[420px] leading-relaxed">
            Get a personalized cost estimate, plan your project and track payments in one place.
        </p>
    </div>

    <div class="relative z-10 flex flex-wrap items-center gap-3 mt-6">
        <a
            href="{{ route('user.project.setup') }}"
            class="bg-primary text-primary-foreground rounded-full px-5 sm:px-6 py-2.5 sm:py-3 text-xs sm:text-sm font-medium flex items-center gap-2 hover:opacity-90 transition-opacity shadow-md shadow-primary/25"
        >
            <x-lucide-plus class="w-4 h-4" /> Create your first project
        </a>
        <span class="text-[11px] sm:text-xs text-muted-foreground">Takes less than 30 seconds</span>
    </div>
</div>
