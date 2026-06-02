@php
    $user   = \Illuminate\Support\Facades\Auth::user();
    $userId = $user?->getAuthIdentifier() ?? 0;
    $supabase = app(\App\Services\SupabaseService::class);

    $allProjects = $supabase->select('projects', 'id,total_cost,created_at,status', ['user_id' => $userId]);
    $totalProjects = count($allProjects);
    $totalCostMin  = (int) array_sum(array_column($allProjects, 'total_cost'));

    $thisMonthNum = now()->month;
    $thisYear     = now()->year;
    $thisMonth = count(array_filter($allProjects, function ($p) use ($thisMonthNum, $thisYear) {
        if (empty($p['created_at'])) return false;
        $d = \Carbon\Carbon::parse($p['created_at']);
        return $d->month === $thisMonthNum && $d->year === $thisYear;
    }));

    // estimations table may not exist yet — treat as 0
    try {
        $allEstimations   = $supabase->select('estimations', 'id', ['user_id' => $userId]);
        $totalEstimations = count($allEstimations);
    } catch (\Throwable) {
        $totalEstimations = 0;
    }

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
            'value' => format_rp_short($totalCostMin),
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
