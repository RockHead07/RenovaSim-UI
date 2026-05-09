{{-- pages.project-overview — port of ProjectOverview.tsx --}}
@php
    $projectName    = request()->query('projectName',    'Untitled Project');
    $city           = request()->query('city',           '—');
    $renovationType = request()->query('renovationType', 'Residential Renovation');
    $quality        = request()->query('quality',        'Standard');
    $incomingTotal    = (int) request()->query('totalCost',    0);
    $incomingMaterial = (int) request()->query('materialCost', 0);
    $incomingLabor    = (int) request()->query('laborCost',    0);
    $projectId        = request()->query('id', 'current');

    $baseTotal     = $incomingTotal    > 0 ? $incomingTotal    : 8_750_000;
    $baseLabor     = $incomingLabor    > 0 ? $incomingLabor    : 3_500_000;
    $basePurchases = $incomingMaterial > 0 ? $incomingMaterial : 5_250_000;

    $savingsAmount = (int) round($basePurchases * 0.12);

    $continueCards = [
        [
            'title' => 'DEFINE COST & BUDGET', 'borderColor' => 'border-primary', 'titleColor' => 'text-primary',
            'items' => [
                ['icon' => 'eye',         'title' => 'View budget breakdown',          'desc' => 'See all cost categories and line items for this project.'],
                ['icon' => 'pencil',      'title' => 'Refine your estimate',           'desc' => 'Tailor costs, scope, and assumptions to your project.'],
                ['icon' => 'dollar-sign', 'title' => 'Find savings to fit your budget','desc' => 'Reduce spend while keeping your priorities.'],
                ['icon' => 'users',       'title' => 'Invite Contributors',            'desc' => 'Plan and review the project together.'],
            ],
        ],
        [
            'title' => 'QUOTES & PROFESSIONALS', 'borderColor' => 'border-[hsl(30,80%,50%)]', 'titleColor' => 'text-[hsl(30,80%,50%)]',
            'items' => [
                ['icon' => 'file-text', 'title' => 'Prepare a request for quotes', 'desc' => 'Turn your project into a clear scope to share with contractors.'],
                ['icon' => 'map-pin',   'title' => 'Get local proposals',          'desc' => 'Request quotes from vetted contractors in your area.'],
                ['icon' => 'users',     'title' => 'Add a professional',           'desc' => 'Manually invite your contractors to submit quotes for your project.'],
                ['icon' => 'search',    'title' => 'Compare contractor quotes',    'desc' => 'Spot gaps, overlaps, and pricing differences.'],
            ],
        ],
        [
            'title' => 'TRACK EXECUTION', 'borderColor' => 'border-[hsl(210,70%,50%)]', 'titleColor' => 'text-[hsl(210,70%,50%)]',
            'items' => [
                ['icon' => 'credit-card', 'title' => 'Track costs, payments, and invoices', 'desc' => 'Manage your renovation finances in one place.'],
            ],
        ],
        [
            'title' => 'KEEP RECORDS', 'borderColor' => 'border-[hsl(270,50%,55%)]', 'titleColor' => 'text-[hsl(270,50%,55%)]',
            'items' => [
                ['icon' => 'file-text', 'title' => 'Document all steps of the process', 'desc' => 'Save files, notes, and key decisions as the project moves forward.'],
            ],
        ],
    ];

    $steps = [
        ['number' => 1, 'label' => 'Define Cost & Budget',   'icon' => 'clipboard-list'],
        ['number' => 2, 'label' => 'Quotes & Professionals', 'icon' => 'users'],
        ['number' => 3, 'label' => 'Track Execution',        'icon' => 'zap'],
        ['number' => 4, 'label' => 'Keep Records',           'icon' => 'file-text'],
    ];
    $activeStep = 1;
@endphp

<x-layouts.app title="RenovaSim — Project Overview" :hideNav="true">
    {{-- Top Navbar (custom — has back arrow at title row, plain logo here) --}}
    <nav class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-4">
        <a href="/" class="font-['Playfair_Display'] italic text-xl text-card-foreground hover:opacity-80 transition-opacity">RenovaSim</a>
        <div class="flex items-center gap-3">
            <button class="w-8 h-8 rounded-full border border-border flex items-center justify-center hover:bg-muted transition-colors">
                <x-lucide-help-circle class="w-4 h-4 text-muted-foreground" />
            </button>
            <button class="w-8 h-8 rounded-full border border-border flex items-center justify-center hover:bg-muted transition-colors">
                <x-lucide-user class="w-4 h-4 text-muted-foreground" />
            </button>
        </div>
    </nav>

    <div
        x-data="{
            descriptionOpen: false,
            savingsApplied: false,
            base: { total: {{ $baseTotal }}, labor: {{ $baseLabor }}, purchases: {{ $basePurchases }} },
            savingsAmount: {{ $savingsAmount }},
            get totalBudget() { return this.savingsApplied ? this.base.total - this.savingsAmount : this.base.total; },
            get professionalsAmount() { return this.base.labor; },
            get purchasesAmount() { return this.savingsApplied ? this.base.purchases - this.savingsAmount : this.base.purchases; },
            get professionalsPercent() { return this.totalBudget > 0 ? Math.round((this.professionalsAmount / this.totalBudget) * 100) : 0; },
            get purchasesPercent() { return this.totalBudget > 0 ? Math.round((this.purchasesAmount / this.totalBudget) * 100) : 0; },
            formatRp(n) { return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(n); },
        }"
        class="flex-1 py-6 px-4"
    >
        <div class="max-w-[920px] mx-auto">
            {{-- Page Title --}}
            <div class="flex items-center gap-3 mb-6">
                <button onclick="history.back()" class="text-card-foreground hover:opacity-70 transition-opacity">
                    <x-lucide-arrow-left class="w-5 h-5" />
                </button>
                <div>
                    <p class="font-['Playfair_Display'] italic text-xl text-card-foreground">Project Overview: {{ $projectName }}</p>
                    <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground">Manage your target total for this renovation</p>
                </div>
            </div>

            {{-- Step Progress --}}
            <div class="bg-card rounded-2xl p-5 shadow-[0_1px_4px_rgba(0,0,0,0.06)] mb-6">
                <div class="flex items-center">
                    @foreach ($steps as $idx => $step)
                        @php
                            $isActive    = $step['number'] === $activeStep;
                            $isCompleted = $step['number'] <  $activeStep;
                        @endphp
                        <div class="flex items-center flex-1 last:flex-none">
                            <div @class([
                                'w-10 h-10 shrink-0 rounded-full flex items-center justify-center transition-colors',
                                'bg-primary text-primary-foreground' => $isActive,
                                'bg-primary/20 text-primary'         => $isCompleted,
                                'bg-muted text-card-foreground/70'   => ! $isActive && ! $isCompleted,
                            ])>
                                @if ($isCompleted)
                                    <x-lucide-check class="w-[18px] h-[18px]" />
                                @else
                                    <x-dynamic-component :component="'lucide-' . $step['icon']" class="w-[18px] h-[18px]" />
                                @endif
                            </div>
                            @if ($idx < count($steps) - 1)
                                <div @class([
                                    'flex-1 h-[2px] mx-3 rounded-full',
                                    'bg-primary' => $step['number'] < $activeStep,
                                    'bg-border'  => $step['number'] >= $activeStep,
                                ])></div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-4 mt-2">
                    @foreach ($steps as $step)
                        @php $isActive = $step['number'] === $activeStep; @endphp
                        <div class="flex flex-col items-center text-center px-0.5">
                            <p @class([
                                'font-[\'DM_Sans\'] text-[9px] sm:text-[10px] uppercase tracking-[0.1em] font-semibold',
                                'text-card-foreground'    => $isActive,
                                'text-card-foreground/80' => ! $isActive,
                            ])>Step {{ $step['number'] }}</p>
                            <p @class([
                                'font-[\'DM_Sans\'] text-[10px] sm:text-[11px] mt-0.5 leading-tight font-medium',
                                'text-card-foreground'    => $isActive,
                                'text-card-foreground/70' => ! $isActive,
                            ])>{{ $step['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Main content --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- LEFT --}}
                <div class="md:col-span-2 flex flex-col gap-6">
                    {{-- Description accordion --}}
                    <div class="bg-card rounded-2xl shadow-[0_1px_4px_rgba(0,0,0,0.06)]">
                        <button @click="descriptionOpen = !descriptionOpen" class="w-full flex items-center justify-between px-6 py-4">
                            <div class="flex items-center gap-2">
                                <x-lucide-clipboard-list class="w-4 h-4 text-muted-foreground" />
                                <span class="font-['DM_Sans'] font-medium text-sm text-card-foreground">Project Description</span>
                            </div>
                            <template x-if="descriptionOpen"><x-lucide-chevron-up   class="w-4 h-4 text-muted-foreground" /></template>
                            <template x-if="!descriptionOpen"><x-lucide-chevron-down class="w-4 h-4 text-muted-foreground" /></template>
                        </button>
                        <div x-show="descriptionOpen" x-collapse class="px-6 pb-4">
                            <p class="font-['DM_Sans'] text-sm text-muted-foreground leading-relaxed">
                                {{ $projectName }} — {{ Str::lower($renovationType) }} in {{ $city }} with {{ Str::lower($quality) }} quality finishes.
                                Budget inherited from your accepted AI estimate.
                            </p>
                        </div>
                    </div>

                    {{-- Estimated Budget Card --}}
                    <div class="bg-card rounded-2xl p-6 shadow-[0_2px_8px_rgba(0,0,0,0.07)]">
                        <h2 class="font-['Playfair_Display'] italic text-xl text-card-foreground">Your Estimated Budget</h2>
                        <p class="font-['DM_Sans'] text-sm text-muted-foreground mt-1">
                            Here's your baseline estimate. Next, use AI to refine your project or request proposals from professionals.
                        </p>

                        <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground mt-5">TOTAL ESTIMATED BUDGET</p>
                        <p class="font-['Playfair_Display'] italic text-[36px] font-bold text-card-foreground mt-1 transition-all duration-500" x-text="formatRp(totalBudget)"></p>

                        <div class="mt-5 space-y-3">
                            {{-- Professionals --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-['DM_Sans'] font-medium text-sm text-card-foreground">Professionals</span>
                                        <span class="font-['DM_Sans'] text-[11px] bg-primary/10 text-secondary rounded px-1.5 py-0.5"><span x-text="professionalsPercent"></span>%</span>
                                    </div>
                                    <span class="font-['DM_Sans'] text-sm text-card-foreground" x-text="formatRp(professionalsAmount)"></span>
                                </div>
                                <div class="w-full h-2 bg-border rounded-full overflow-hidden">
                                    <div :style="`width: ${professionalsPercent}%`" class="h-full bg-primary rounded-full transition-all duration-700"></div>
                                </div>
                            </div>

                            {{-- Purchases --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-['DM_Sans'] font-medium text-sm text-card-foreground">Purchases</span>
                                        <span class="font-['DM_Sans'] text-[11px] bg-primary/10 text-secondary rounded px-1.5 py-0.5"><span x-text="purchasesPercent"></span>%</span>
                                    </div>
                                    <span class="font-['DM_Sans'] text-sm text-card-foreground" x-text="formatRp(purchasesAmount)"></span>
                                </div>
                                <div class="w-full h-2 bg-border rounded-full overflow-hidden">
                                    <div :style="`width: ${purchasesPercent}%`" class="h-full bg-secondary rounded-full transition-all duration-700"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap items-center gap-2.5">
                            <a href="/estimation-result" class="bg-primary text-primary-foreground font-['DM_Sans'] font-medium text-sm rounded-lg px-5 py-3 hover:opacity-90 transition-opacity">
                                View Detailed Breakdown
                            </a>
                            <a
                                href="{{ url('/project/' . $projectId . '/rab?' . http_build_query([
                                    'projectName' => $projectName,
                                    'city' => $city,
                                    'renovationType' => $renovationType,
                                    'quality' => $quality,
                                    'totalCost' => $baseTotal,
                                    'materialCost' => $basePurchases,
                                    'laborCost' => $baseLabor,
                                ])) }}"
                                class="border-[1.5px] border-primary/60 text-primary bg-primary/5 font-['DM_Sans'] font-medium text-sm rounded-lg px-5 py-3 hover:bg-primary/10 transition-colors inline-flex items-center gap-1.5"
                            >
                                <x-lucide-file-text class="w-[14px] h-[14px]" /> Lihat Detail RAB
                            </a>
                        </div>
                    </div>

                    {{-- AI Insight --}}
                    <div class="bg-[hsl(40,100%,96%)] border-[1.5px] border-[hsl(40,96%,53%)] rounded-2xl p-5">
                        <div class="w-9 h-9 rounded-full bg-[hsl(40,96%,53%)] flex items-center justify-center">
                            <x-lucide-lightbulb class="w-[18px] h-[18px] text-white" />
                        </div>
                        <h3 class="font-['Playfair_Display'] italic text-lg text-card-foreground mt-2.5">AI Savings Insight</h3>
                        <p class="font-['DM_Sans'] text-[13px] text-card-foreground leading-relaxed mt-2">
                            AI analysis of <span class="font-semibold">{{ $projectName }}</span> indicates switching the specified
                            'Purchases' items to vetted alternative brands could reduce cost by
                            <span class="font-semibold text-[hsl(36,90%,45%)]">~12%</span>. Our recommended alternative would save you approximately
                            <span class="font-semibold text-[hsl(36,90%,45%)]">{{ format_rp($savingsAmount) }}</span>.
                        </p>
                        <button
                            @click="savingsApplied = true"
                            :disabled="savingsApplied"
                            class="w-full bg-card-foreground text-card font-['DM_Sans'] font-semibold text-sm rounded-lg py-3.5 mt-4 hover:opacity-90 transition-opacity disabled:opacity-50"
                        >
                            <span x-text="savingsApplied ? 'Savings Applied ✓' : 'Apply Suggested Savings'"></span>
                        </button>
                        <p x-show="!savingsApplied" class="font-['DM_Sans'] text-[11px] text-muted-foreground text-center mt-2">
                            Reduce total budget by ~{{ format_rp($savingsAmount) }}
                        </p>
                    </div>
                </div>

                {{-- RIGHT --}}
                <div class="flex flex-col gap-6">
                    <div class="bg-card rounded-2xl p-5 shadow-[0_1px_4px_rgba(0,0,0,0.06)]">
                        <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground mb-3">Project Summary</p>
                        <div class="space-y-3">
                            @foreach ([
                                ['icon' => 'map-pin',  'label' => 'Location', 'value' => $city],
                                ['icon' => 'hammer',   'label' => 'Type',     'value' => $renovationType],
                                ['icon' => 'sparkles', 'label' => 'Quality',  'value' => $quality],
                            ] as $row)
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-muted flex items-center justify-center">
                                        <x-dynamic-component :component="'lucide-' . $row['icon']" class="w-[13px] h-[13px] text-muted-foreground" />
                                    </div>
                                    <div>
                                        <p class="font-['DM_Sans'] text-[11px] text-muted-foreground">{{ $row['label'] }}</p>
                                        <p class="font-['DM_Sans'] text-sm font-medium text-card-foreground">{{ $row['value'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button class="w-full bg-primary text-primary-foreground font-['Playfair_Display'] italic text-base rounded-lg py-4 flex items-center justify-center gap-2 hover:opacity-90 transition-opacity">
                        <x-lucide-save class="w-4 h-4" /> Save Project
                    </button>
                    <a href="/ai-estimation" class="font-['DM_Sans'] text-[13px] text-muted-foreground text-center flex items-center justify-center gap-1.5 -mt-3 hover:text-card-foreground transition-colors">
                        <x-lucide-rotate-ccw class="w-[13px] h-[13px]" /> Recalculate
                    </a>
                </div>
            </div>

            {{-- Continue Building --}}
            <div class="mt-10">
                <h2 class="font-['Playfair_Display'] italic text-xl text-card-foreground">Continue building your renovation with RenovaSim</h2>
                <p class="font-['DM_Sans'] text-sm text-muted-foreground mt-1 mb-5">Use your estimate as a starting point. Refine your plan, get quotes, and track your project.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($continueCards as $card)
                        <div class="bg-card rounded-2xl border-t-[3px] {{ $card['borderColor'] }} p-5 shadow-[0_1px_4px_rgba(0,0,0,0.06)] flex flex-col gap-4">
                            <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.12em] font-semibold {{ $card['titleColor'] }}">{{ $card['title'] }}</p>
                            <div class="flex flex-col gap-3">
                                @foreach ($card['items'] as $item)
                                    <button class="flex items-start gap-2.5 text-left hover:bg-muted/50 rounded-lg p-2 -mx-2 transition-colors">
                                        <div class="w-6 h-6 rounded-md bg-muted shrink-0 flex items-center justify-center mt-0.5">
                                            <x-dynamic-component :component="'lucide-' . $item['icon']" class="w-[13px] h-[13px] text-muted-foreground" />
                                        </div>
                                        <div>
                                            <p class="font-['DM_Sans'] font-medium text-[13px] text-card-foreground">{{ $item['title'] }}</p>
                                            <p class="font-['DM_Sans'] text-[11px] text-muted-foreground leading-snug mt-0.5">{{ $item['desc'] }}</p>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
