{{-- ========================================================
     pages.dashboard — port of src/pages/Index.tsx
======================================================== --}}
<x-user.layouts.dashboard title="RenovaSim — Dashboard">
    <x-user.components.layout.topbar name="Oliver" />

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5 auto-rows-min">
        <div class="md:col-span-3 lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
            <x-user.components.shared.portfolio-metrics />
        </div>

        <div class="md:col-span-1 md:row-span-2 lg:col-span-1 lg:row-span-2 order-3 md:order-none">
            <x-user.components.shared.mini-calendar />
        </div>

        <div class="md:col-span-2 lg:col-span-3">
            <x-user.components.shared.hero-widget />
        </div>

        <div class="md:col-span-3 lg:col-span-4">
            <x-user.components.shared.recent-estimates-card />
        </div>
    </div>
</x-user.layouts.dashboard>
