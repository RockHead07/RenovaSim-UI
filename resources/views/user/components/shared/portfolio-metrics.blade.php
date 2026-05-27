@php
    $user   = \Illuminate\Support\Facades\Auth::user();
    $userId = $user?->id ?? 0;
    $totalProjects    = \App\Models\Project::where('user_id', $userId)->count();
    $totalCostMin     = \App\Models\Project::where('user_id', $userId)->sum('total_cost');
    $totalEstimations = \App\Models\Estimation::where('user_id', $userId)->count();
    $thisMonth        = \App\Models\Project::where('user_id', $userId)
                            ->whereMonth('created_at', now()->month)
                            ->count();

    $metrics = [
        [
            'icon' => 'folder-open', 'iconBg' => 'bg-blue-50', 'iconColor' => 'text-blue-500',
            'label' => 'Total Projects',
            'value' => (string) $totalProjects,
            'trend' => '+' . $thisMonth . ' bulan ini',
        ],
        [
            'icon' => 'dollar-sign', 'iconBg' => 'bg-[hsl(73,55%,94%)]', 'iconColor' => 'text-primary',
            'label' => 'Total Biaya Estimasi',
            'value' => format_rp_short((int) $totalCostMin),
            'trend' => 'Dari semua project',
        ],
        [
            'icon' => 'calculator', 'iconBg' => 'bg-blue-50', 'iconColor' => 'text-blue-600',
            'label' => 'Total Estimasi',
            'value' => (string) $totalEstimations,
            'trend' => 'Item estimasi tersimpan',
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
