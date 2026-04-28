{{-- pages.three-d-design — port of ThreeDDesign.tsx --}}
<x-user.layouts.dashboard title="RenovaSim — 3D Design">
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="font-['Playfair_Display'] italic text-[28px] text-secondary">3D Design Modeling House</h1>
            <p class="text-sm text-muted-foreground mt-1">
                Visualize your renovation in three dimensions before construction begins.
            </p>
        </div>

        <div class="bg-card rounded-[24px] shadow-sm p-10 min-h-[420px] flex flex-col items-center justify-center text-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center">
                <x-lucide-box class="w-7 h-7 text-primary" />
            </div>
            <h2 class="font-['Playfair_Display'] text-2xl text-secondary">Coming soon</h2>
            <p class="text-sm text-muted-foreground max-w-md">
                A built-in 3D modeling workspace is on the way. You'll be able to sketch rooms, swap finishes and preview your renovation in real time.
            </p>
            <button class="mt-2 inline-flex items-center gap-2 bg-primary text-primary-foreground rounded-full px-5 py-2.5 text-sm font-medium hover:opacity-90 transition-opacity">
                <x-lucide-sparkles class="w-[15px] h-[15px]" /> Notify me when ready
            </button>
        </div>
    </div>
</x-user.layouts.dashboard>
