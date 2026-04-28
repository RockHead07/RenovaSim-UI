{{-- pages.project-stage — port of ProjectStage.tsx --}}
<x-user.layouts.app title="RenovaSim — Project Stage">
    <div
        x-data="{ selected: 'started' }"
        class="flex-1 flex items-center justify-center px-4 py-10"
    >
        <div class="w-full max-w-[500px] flex flex-col items-center">
            <div class="w-12 h-12 rounded-xl bg-muted flex items-center justify-center">
                <x-lucide-clipboard-list class="w-[22px] h-[22px] text-muted-foreground" />
            </div>

            <h1 class="font-['Playfair_Display'] italic text-[22px] text-card-foreground text-center mt-4">
                What stage is your renovation in?
            </h1>

            <p class="text-sm text-muted-foreground text-center max-w-[320px] leading-relaxed mt-2">
                This helps us tailor your project setup to your situation.
            </p>

            <div class="w-full flex flex-col gap-3 mt-7">
                <button
                    @click="selected = 'started'"
                    :class="selected === 'started' ? 'border-[1.5px] border-primary shadow-sm' : 'border-[1.5px] border-transparent shadow-sm'"
                    class="w-full flex items-center gap-4 bg-card rounded-xl p-[18px_20px] text-left transition-all hover:shadow-md"
                >
                    <div class="w-9 h-9 rounded-lg bg-[hsl(110,70%,94%)] flex items-center justify-center shrink-0">
                        <x-lucide-check class="w-[18px] h-[18px] text-primary" />
                    </div>
                    <div>
                        <p class="font-medium text-[15px] text-card-foreground">Work has already started</p>
                        <p class="text-[13px] text-muted-foreground mt-0.5">Track payments, documents, and project progress.</p>
                    </div>
                </button>

                <a
                    href="/project-details"
                    @click="selected = 'planning'"
                    :class="selected === 'planning' ? 'border-[1.5px] border-primary shadow-sm' : 'border-[1.5px] border-transparent shadow-sm'"
                    class="w-full flex items-center gap-4 bg-card rounded-xl p-[18px_20px] text-left transition-all hover:shadow-md"
                >
                    <div class="w-9 h-9 rounded-lg bg-muted flex items-center justify-center shrink-0">
                        <x-lucide-clipboard-list class="w-[18px] h-[18px] text-muted-foreground" />
                    </div>
                    <div>
                        <p class="font-medium text-[15px] text-card-foreground">Planning my renovation</p>
                        <p class="text-[13px] text-muted-foreground mt-0.5">Get a cost/budget estimate and compare contractor quotes.</p>
                    </div>
                </a>
            </div>

            <div class="flex gap-1.5 mt-8">
                <div class="w-2 h-2 rounded-full bg-secondary"></div>
                <div class="w-2 h-2 rounded-full bg-border"></div>
                <div class="w-2 h-2 rounded-full bg-border"></div>
            </div>
        </div>
    </div>
</x-user.layouts.app>
