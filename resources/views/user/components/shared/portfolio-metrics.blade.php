@php
    $projects = config('renovasim.projects');
    $totalProjects = count($projects);
    $totalCost = array_sum(array_column($projects, 'totalCost'));
    $totalPaid = array_sum(array_column($projects, 'paid'));
    $paidPct = $totalCost > 0 ? (int) round(($totalPaid / $totalCost) * 100) : 0;

    $metrics = [
        [
            'icon' => 'users', 'iconBg' => 'bg-blue-50', 'iconColor' => 'text-blue-500',
            'label' => 'Total Projects', 'value' => (string) $totalProjects,
            'trend' => '+1 this month',
        ],
        [
            'icon' => 'dollar-sign', 'iconBg' => 'bg-[hsl(73,55%,94%)]', 'iconColor' => 'text-primary',
            'label' => 'Total Project Cost', 'value' => format_rp_short($totalCost),
            'trend' => 'Across all projects',
        ],
        [
            'icon' => 'wallet', 'iconBg' => 'bg-blue-50', 'iconColor' => 'text-blue-600',
            'label' => 'Total Paid', 'value' => format_rp_short($totalPaid),
            'trend' => $paidPct . '% of total',
        ],
    ];
@endphp

@foreach ($metrics as $m)
    <div class="bg-card rounded-[20px] shadow-sm p-5 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <div class="w-10 h-10 rounded-xl {{ $m['iconBg'] }} flex items-center justify-center">
                <x-dynamic-component :component="'lucide-' . $m['icon']" :class="'w-[18px] h-[18px] ' . $m['iconColor']" />
            </div>
            <span class="text-[10px] uppercase tracking-wider text-muted-foreground">{{ $m['label'] }}</span>
        </div>
        <div>
            <div class="text-[26px] font-semibold text-card-foreground leading-tight">{{ $m['value'] }}</div>
            <div class="text-xs text-muted-foreground mt-0.5">{{ $m['trend'] }}</div>
        </div>
    </div>
@endforeach
