{{-- ========================================================
     pages.dashboard — port of src/pages/Index.tsx
======================================================== --}}
<x-layouts.dashboard title="RenovaSim — Dashboard">
    <x-topbar name="Oliver" />

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-5 auto-rows-min">
        <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
            <x-portfolio-metrics />
        </div>

        <div class="lg:col-span-1 lg:row-span-2 order-3 lg:order-none">
            <x-mini-calendar />
        </div>

        <div class="lg:col-span-3">
            <x-hero-widget />
        </div>

        <div class="lg:col-span-4">
            <x-recent-estimates-card />
        </div>
    </div>
</x-layouts.dashboard>
