@props(['breakdown', 'totalRange'])

@php
    $jobLabels = config('renovasim.job_type_id');
@endphp

<div class="bg-card rounded-2xl p-6 shadow-[0_2px_8px_rgba(0,0,0,0.07)]">
    <h2 class="font-['Playfair_Display'] italic text-lg text-card-foreground mb-1">Cost Breakdown</h2>
    <p class="font-['DM_Sans'] text-[12px] text-muted-foreground mb-4">Rentang biaya per jenis pekerjaan</p>

    <div class="divide-y divide-border">
        @foreach ($breakdown as $row)
            <div class="py-3 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-['DM_Sans'] font-medium text-[13px] text-card-foreground truncate">
                        {{ $jobLabels[$row['job_type']] ?? $row['job_type'] }}
                    </p>
                    <p class="font-['DM_Sans'] text-[11.5px] text-muted-foreground mt-0.5">{{ $row['area'] }} m²</p>
                </div>
                <span class="font-['DM_Sans'] text-[13px] font-semibold text-card-foreground shrink-0 text-right">
                    {{ format_rp($row['min']) }} – {{ format_rp($row['max']) }}
                </span>
            </div>
        @endforeach
    </div>

    <div class="border-t-[1.5px] border-border mt-2 pt-3 flex items-center justify-between">
        <span class="font-['DM_Sans'] font-semibold text-sm text-card-foreground">Total</span>
        <span class="font-['Playfair_Display'] italic text-lg font-bold text-card-foreground">{{ $totalRange['display'] }}</span>
    </div>
</div>
