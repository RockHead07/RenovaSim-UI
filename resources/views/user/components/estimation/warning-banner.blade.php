@php
    $warning = $warning ?? ['severity' => 'info', 'message' => 'Information'];
    $severityConfig = [
        'critical' => ['bg' => 'bg-[hsl(0,95%,96%)]', 'border' => 'border-[hsl(0,95%,40%)]', 'icon' => 'alert-triangle', 'color' => 'text-[hsl(0,95%,40%)]'],
        'warning'  => ['bg' => 'bg-[hsl(35,100%,96%)]', 'border' => 'border-[hsl(35,100%,52%)]', 'icon' => 'alert-circle', 'color' => 'text-[hsl(35,100%,52%)]'],
        'info'     => ['bg' => 'bg-[hsl(210,90%,96%)]', 'border' => 'border-[hsl(210,90%,55%)]', 'icon' => 'info', 'color' => 'text-[hsl(210,90%,45%)]'],
    ];
    $style = $severityConfig[$warning['severity']] ?? $severityConfig['info'];
@endphp

<div class="flex items-start gap-2.5 {{ $style['bg'] }} border-[1.5px] {{ $style['border'] }} rounded-xl px-4 py-3 animate-fade-in">
    <x-dynamic-component :component="'lucide-' . $style['icon']" :class="'w-[18px] h-[18px] ' . $style['color'] . ' shrink-0 mt-0.5'" />
    <p class="font-['DM_Sans'] text-[13px] text-card-foreground leading-relaxed">
        {{ $warning['message'] }}
    </p>
</div>
