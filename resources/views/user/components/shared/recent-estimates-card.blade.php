@php
    $projects = config('renovasim.projects');
    $statusStyles = [
        'In Progress' => 'bg-amber-100 text-amber-700',
        'Completed'   => 'bg-[hsl(73,55%,90%)] text-secondary',
        'Draft'       => 'bg-muted text-muted-foreground',
    ];
    $fmt = fn ($v) => 'Rp ' . number_format($v / 1_000_000, 1, ',', '.') . 'M';
@endphp

<div class="bg-card rounded-[20px] sm:rounded-[24px] shadow-sm p-5 sm:p-7">
    <div class="flex items-start justify-between gap-3 mb-5">
        <div class="min-w-0">
            <h3 class="font-['Playfair_Display'] italic text-lg sm:text-xl text-secondary">Your Recent Estimates</h3>
            <p class="text-xs text-muted-foreground mt-0.5">A snapshot of your latest renovation budgets</p>
        </div>
        <button class="text-xs font-medium text-primary flex items-center gap-1 hover:underline shrink-0">
            View all <x-lucide-arrow-up-right class="w-[13px] h-[13px]" />
        </button>
    </div>

    <div class="flex flex-col gap-2">
        @foreach ($projects as $est)
            <div class="flex flex-col sm:grid sm:grid-cols-12 sm:items-center gap-3 px-4 py-3.5 rounded-2xl bg-muted/40 hover:bg-muted transition-colors">
                <div class="sm:col-span-4 flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-card flex items-center justify-center shadow-sm shrink-0">
                        <span class="text-primary font-semibold text-sm">{{ substr($est['name'], 0, 1) }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-medium text-card-foreground truncate">{{ $est['name'] }}</div>
                        <div class="text-[11px] text-muted-foreground truncate">{{ $est['modified'] }}</div>
                    </div>
                    <span class="sm:hidden text-[10px] uppercase font-medium rounded-full px-2 py-1 shrink-0 {{ $statusStyles[$est['status']] }}">
                        {{ $est['status'] }}
                    </span>
                </div>

                <div class="hidden sm:block sm:col-span-2">
                    <span class="text-[10px] uppercase font-medium rounded-full px-2.5 py-1 {{ $statusStyles[$est['status']] }}">
                        {{ $est['status'] }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:contents">
                    <div class="sm:col-span-2 text-sm">
                        <div class="text-[10px] uppercase text-muted-foreground tracking-wider">Material</div>
                        <div class="text-card-foreground">{{ $fmt($est['materialCost']) }}</div>
                    </div>
                    <div class="sm:col-span-2 text-sm">
                        <div class="text-[10px] uppercase text-muted-foreground tracking-wider">Labor</div>
                        <div class="text-card-foreground">{{ $fmt($est['laborCost']) }}</div>
                    </div>
                </div>

                <div class="sm:col-span-2 flex sm:justify-end">
                    <a href="/project-overview" class="w-full sm:w-auto justify-center flex items-center gap-1.5 text-xs font-medium border border-primary/40 text-primary rounded-full px-3.5 py-1.5 hover:bg-primary hover:text-primary-foreground transition-colors">
                        <x-lucide-eye class="w-[13px] h-[13px]" /> View
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
