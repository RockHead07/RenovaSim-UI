@props(['warning'])

@php
    $tones = [
        'critical' => ['container' => 'bg-[hsl(0,80%,95%)] border-[hsl(0,75%,55%)]',  'icon' => 'text-[hsl(0,75%,45%)]',  'text' => 'text-card-foreground'],
        'warning'  => ['container' => 'bg-[hsl(40,100%,96%)] border-[hsl(36,90%,55%)]', 'icon' => 'text-[hsl(36,90%,45%)]', 'text' => 'text-card-foreground'],
        'info'     => ['container' => 'bg-[hsl(210,90%,96%)] border-[hsl(210,90%,55%)]', 'icon' => 'text-[hsl(210,90%,45%)]', 'text' => 'text-card-foreground'],
    ];
    $tone = $tones[$warning['severity']] ?? $tones['warning'];
    $iconName = $warning['severity'] === 'info' ? 'info' : 'alert-triangle';
@endphp

<div class="mb-5 flex items-start gap-2.5 border-[1.5px] rounded-xl px-4 py-3 animate-fade-in {{ $tone['container'] }}">
    <x-dynamic-component :component="'lucide-' . $iconName" :class="'w-[18px] h-[18px] shrink-0 mt-0.5 ' . $tone['icon']" />
    <p class="font-['DM_Sans'] text-[13px] leading-relaxed {{ $tone['text'] }}">{{ $warning['message'] }}</p>
</div>
