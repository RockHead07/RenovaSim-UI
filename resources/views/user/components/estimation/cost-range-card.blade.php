@php
    $confidence = $confidence ?? ['label' => 'Tinggi', 'score' => 85, 'message' => 'Estimasi sangat akurat berdasarkan data yang lengkap.'];
    $scoreColor = match(true) {
        $confidence['score'] >= 75 => 'bg-[hsl(146,80%,48%)]',
        $confidence['score'] >= 50 => 'bg-[hsl(35,100%,52%)]',
        default => 'bg-[hsl(0,100%,50%)]'
    };
    $labelColor = match($confidence['label']) {
        'Tinggi' => 'text-[hsl(146,80%,48%)]',
        'Sedang' => 'text-[hsl(35,100%,52%)]',
        default => 'text-[hsl(0,100%,50%)]'
    };
@endphp

<div class="bg-card rounded-2xl shadow-sm p-6 sm:p-7 flex flex-col gap-5">
    <div>
        <div class="text-[10px] uppercase tracking-wider text-muted-foreground mb-2 font-medium">Total Estimated Cost</div>
        <div class="font-['Playfair_Display'] italic text-[28px] sm:text-[36px] text-secondary leading-tight">
            {{ format_rp($range['display'] ?? (($range['min'] + $range['max']) / 2)) }}
        </div>
        <div class="text-sm text-muted-foreground mt-1">
            {{ format_rp($range['min']) }} – {{ format_rp($range['max']) }}
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] uppercase tracking-wider text-muted-foreground font-medium">Confidence Level</span>
            <span class="text-[11px] font-semibold {{ $labelColor }}">{{ $confidence['label'] }}</span>
        </div>
        <div class="relative h-1.5 bg-muted rounded-full overflow-hidden">
            <div class="absolute top-0 left-0 h-full {{ $scoreColor }} rounded-full transition-all" :style="`width: ${Math.min({{ $confidence['score'] }}, 100)}%`"></div>
        </div>
        <div class="bg-[hsl(210,95%,97%)] border border-[hsl(210,90%,75%)] rounded-lg p-3 mt-3 flex items-start gap-2">
            <x-lucide-shield-check class="w-4 h-4 text-[hsl(210,90%,45%)] shrink-0 mt-0.5" />
            <p class="text-xs text-card-foreground leading-relaxed">{{ $confidence['message'] }}</p>
        </div>
    </div>
</div>
