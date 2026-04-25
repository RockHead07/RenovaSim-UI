@props(['range', 'confidence'])

@php
    $tones = [
        'Tinggi' => [
            'label' => 'High Confidence',
            'bar'   => 'bg-[hsl(142,70%,45%)]',
            'text'  => 'text-[hsl(142,70%,45%)]',
            'bg'    => 'bg-[hsl(142,70%,45%)]/10',
        ],
        'Sedang' => [
            'label' => 'Medium Confidence',
            'bar'   => 'bg-[hsl(40,96%,53%)]',
            'text'  => 'text-[hsl(36,90%,45%)]',
            'bg'    => 'bg-[hsl(40,100%,96%)]',
        ],
        'Rendah' => [
            'label' => 'Low Confidence',
            'bar'   => 'bg-[hsl(0,75%,55%)]',
            'text'  => 'text-[hsl(0,75%,45%)]',
            'bg'    => 'bg-[hsl(0,80%,95%)]',
        ],
    ];
    $tone = $tones[$confidence['label']] ?? $tones['Sedang'];
    $avg  = (int) round(($range['min'] + $range['max']) / 2);
    $pct  = max(8, min(100, (int) round(($confidence['score'] ?? 0.5) * 100)));
@endphp

<div class="bg-card rounded-2xl p-6 shadow-[0_2px_8px_rgba(0,0,0,0.07)]">
    <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-primary font-medium flex items-center gap-1">
        <x-lucide-zap class="w-3 h-3" /> AI-Driven Estimate
    </p>

    <p class="font-['Playfair_Display'] italic text-[28px] font-bold text-card-foreground mt-2 leading-tight">
        {{ $range['display'] }}
    </p>
    <div class="flex items-baseline gap-2 mt-1">
        <span class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground">Target Average</span>
        <span class="font-['DM_Sans'] text-sm font-semibold text-card-foreground">{{ format_rp($avg) }}</span>
    </div>

    <div class="mt-5">
        <div class="flex items-center justify-between mb-1.5">
            <span class="font-['DM_Sans'] text-[11px] font-semibold {{ $tone['text'] }}">{{ $tone['label'] }}</span>
            <span class="font-['DM_Sans'] text-[10px] text-muted-foreground uppercase tracking-[0.12em]">{{ $pct }}%</span>
        </div>
        <div class="relative h-2 bg-muted rounded-full overflow-hidden">
            <div class="absolute inset-y-0 left-0 {{ $tone['bar'] }} rounded-full transition-all" style="width: {{ $pct }}%"></div>
        </div>
    </div>

    <div class="mt-4 flex items-center gap-2 rounded-lg px-3 py-2.5 {{ $tone['bg'] }}">
        <x-lucide-shield-check class="w-4 h-4 shrink-0 {{ $tone['text'] }}" />
        <p class="font-['DM_Sans'] text-[12.5px] text-card-foreground">{{ $confidence['message'] }}</p>
    </div>
</div>
