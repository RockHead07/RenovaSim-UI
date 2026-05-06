@props(['class' => ''])

{{-- Footer with line-art skyline — port of AppFooter.tsx --}}
<div {{ $attributes->class('mt-12 ' . $class) }}>
    <div class="w-full h-[120px] bg-[#DDDBD3] relative overflow-hidden">
        <svg class="absolute inset-0 w-full h-full opacity-30" viewBox="0 0 800 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <line x1="50" y1="20" x2="50" y2="100" stroke="#C8C6BE" stroke-width="1" />
            <line x1="50" y1="20" x2="200" y2="20" stroke="#C8C6BE" stroke-width="1" />
            <line x1="200" y1="20" x2="200" y2="60" stroke="#C8C6BE" stroke-width="1" />
            <line x1="200" y1="60" x2="350" y2="60" stroke="#C8C6BE" stroke-width="1" />
            <line x1="350" y1="20" x2="350" y2="100" stroke="#C8C6BE" stroke-width="1" />
            <line x1="350" y1="20" x2="500" y2="20" stroke="#C8C6BE" stroke-width="1" />
            <line x1="500" y1="20" x2="500" y2="100" stroke="#C8C6BE" stroke-width="1" />
            <line x1="500" y1="100" x2="700" y2="100" stroke="#C8C6BE" stroke-width="1" />
            <line x1="700" y1="40" x2="700" y2="100" stroke="#C8C6BE" stroke-width="1" />
            <line x1="600" y1="40" x2="700" y2="40" stroke="#C8C6BE" stroke-width="1" />
            <rect x="100" y="50" width="60" height="40" stroke="#C8C6BE" stroke-width="0.8" fill="none" />
            <rect x="400" y="40" width="50" height="30" stroke="#C8C6BE" stroke-width="0.8" fill="none" />
            <rect x="550" y="60" width="40" height="30" stroke="#C8C6BE" stroke-width="0.8" fill="none" />
        </svg>
    </div>
    <p class="text-center text-[10px] uppercase tracking-widest text-muted-foreground mt-3">Budget</p>

    <footer class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 sm:px-6 lg:px-8 py-4 mt-4">
        <span class="text-[11px] uppercase text-muted-foreground">© 2025 RENOVASIM. ALL RIGHTS RESERVED.</span>
        <div class="flex gap-6 mt-2 sm:mt-0">
            <a href="#" class="text-[11px] uppercase text-muted-foreground hover:text-card-foreground">Privacy Policy</a>
            <a href="#" class="text-[11px] uppercase text-muted-foreground hover:text-card-foreground">Terms of Service</a>
        </div>
    </footer>
</div>
