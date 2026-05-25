@php
    $confidence = $confidence ?? ['label' => 'Tinggi', 'score' => 85, 'message' => 'Estimasi sangat akurat berdasarkan data yang lengkap.'];
    // Normalize: API may return 0–1 float, component expects 0–100
    $scoreNormalized = ($confidence['score'] ?? 0) <= 1
        ? (int) round(($confidence['score'] ?? 0) * 100)
        : (int) ($confidence['score'] ?? 0);
    $barColor = match($confidence['label'] ?? '') {
        'Tinggi' => '#8BA023',
        'Sedang' => '#d4941a',
        default  => '#e05555',
    };
    $labelColor = match($confidence['label'] ?? '') {
        'Tinggi' => 'text-[hsl(146,80%,48%)]',
        'Sedang' => 'text-[hsl(35,100%,52%)]',
        default  => 'text-[hsl(0,100%,50%)]',
    };
@endphp

<div class="bg-card rounded-2xl shadow-sm p-6 sm:p-7 flex flex-col gap-5">
    <div>
        <div class="text-[10px] uppercase tracking-wider text-muted-foreground mb-2 font-medium">Total Estimated Cost</div>
        <div class="font-['Playfair_Display'] italic text-[28px] sm:text-[36px] text-secondary leading-tight">
            {{ $range['display'] }}
        </div>
        <div class="text-sm text-muted-foreground mt-1">
            Rp {{ number_format($range['min'], 0, ',', '.') }} – Rp {{ number_format($range['max'], 0, ',', '.') }}
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] uppercase tracking-wider text-muted-foreground font-medium">Confidence Level</span>
            <span class="text-[11px] font-semibold {{ $labelColor }}">{{ $confidence['label'] }}</span>
        </div>
        <div class="relative h-2 bg-muted rounded-full overflow-hidden">
            <div class="absolute top-0 left-0 h-full rounded-full"
                 style="width: {{ $scoreNormalized }}%; background-color: {{ $barColor }}; transition: width 1s ease;"></div>
        </div>
        <div class="bg-[hsl(210,95%,97%)] border border-[hsl(210,90%,75%)] rounded-lg p-3 mt-3 flex items-start gap-2">
            <x-lucide-shield-check class="w-4 h-4 text-[hsl(210,90%,45%)] shrink-0 mt-0.5" />
            <p class="text-xs text-card-foreground leading-relaxed">{{ $confidence['message'] }}</p>
        </div>
    </div>
</div>
