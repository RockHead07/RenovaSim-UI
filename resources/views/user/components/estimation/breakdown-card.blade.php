@php
    $breakdown = $breakdown ?? [];
    $totalRange = $totalRange ?? ['min' => 0, 'max' => 0, 'display' => 0];
    $jobTypeIdMap = config('renovasim.job_type_id') ?? [];
    $total = 0;
@endphp

<div class="bg-card rounded-2xl shadow-sm p-6 sm:p-7">
    <h3 class="font-['Playfair_Display'] italic text-lg text-secondary mb-4">Breakdown by Type</h3>

    <div class="space-y-3">
        @foreach ($breakdown as $item)
            @php
                $label = $jobTypeIdMap[$item['job_type']] ?? $item['job_type'];
                $minCost = $item['min'] ?? 0;
                $maxCost = $item['max'] ?? 0;
                $midCost = ($minCost + $maxCost) / 2;
                $total += $midCost;
            @endphp
            <div class="pb-3 border-b border-border/50 last:border-b-0">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="text-sm font-medium text-card-foreground">{{ $label }}</div>
                    <div class="text-right">
                        <div class="text-[11px] uppercase tracking-wider text-muted-foreground">Area</div>
                        <div class="text-sm text-secondary">{{ $item['area'] ?? 0 }} m²</div>
                    </div>
                </div>
                <div class="flex justify-between text-xs text-muted-foreground">
                    <span>Rp {{ number_format($minCost, 0, ',', '.') }} – Rp {{ number_format($maxCost, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-5 pt-5 border-t-2 border-primary">
        <div class="flex justify-between items-baseline">
            <span class="text-sm font-medium text-card-foreground">Total Estimated Cost</span>
            <div class="font-['Playfair_Display'] italic text-xl text-secondary">
                {{ $totalRange['display'] }}
            </div>
        </div>
    </div>
</div>
