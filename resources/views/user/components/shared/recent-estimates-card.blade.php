@php
    $user   = \Illuminate\Support\Facades\Auth::user();
    $userId = $user?->getAuthIdentifier() ?? 0;
    $supabase = app(\App\Services\SupabaseService::class);

    $rawProjects = $supabase->select('projects', 'id,name,status,total_cost,room_type,area_size', ['user_id' => $userId]);

    // Sort by id descending (latest first) and take 5
    usort($rawProjects, fn($a, $b) => ($b['id'] ?? 0) - ($a['id'] ?? 0));
    $rawProjects = array_slice($rawProjects, 0, 5);

    // Wrap as objects with safe defaults for columns that may not exist yet
    $projects = collect(array_map(function ($p) {
        return (object) [
            'id'                => $p['id'],
            'name'              => $p['name'] ?? 'Untitled',
            'status'            => $p['status'] ?? 'draft',
            'total_cost'        => $p['total_cost'] ?? 0,
            'location'          => $p['location'] ?? null,
            'building_type'     => $p['building_type'] ?? null,
            'estimations_count' => $p['estimations_count'] ?? 0,
        ];
    }, $rawProjects));

    $statusStyles = [
        'active'    => 'bg-amber-100 text-amber-700',
        'completed' => 'bg-[hsl(73,55%,90%)] text-secondary',
        'draft'     => 'bg-muted text-muted-foreground',
        'estimated' => 'bg-blue-100 text-blue-700',
    ];
    $statusLabels = [
        'active'    => 'In Progress',
        'completed' => 'Completed',
        'draft'     => 'Draft',
        'estimated' => 'Estimated',
    ];
@endphp

<div class="bg-card rounded-[20px] sm:rounded-[24px] shadow-sm p-5 sm:p-7">
    <div class="flex items-start justify-between gap-3 mb-5">
        <div class="min-w-0">
            <h3 class="font-['Playfair_Display'] italic text-lg sm:text-xl text-secondary">Your Recent Projects</h3>
            <p class="text-xs text-muted-foreground mt-0.5">Project renovasi yang tersimpan</p>
        </div>
        <a href="{{ route('user.project.setup') }}"
           class="text-xs font-medium text-primary flex items-center gap-1 hover:underline shrink-0">
            + Baru <x-lucide-arrow-up-right class="w-[13px] h-[13px]" />
        </a>
    </div>

    @if($projects->isEmpty())
        <div class="text-center py-10 text-muted-foreground">
            <x-lucide-folder-open class="w-10 h-10 mx-auto mb-2 opacity-30" />
            <p class="font-['DM_Sans'] text-sm">Belum ada project. Mulai dengan membuat estimasi baru.</p>
            <a href="{{ route('user.project.setup') }}"
               class="inline-block mt-3 text-xs font-medium text-primary hover:underline">
                Buat Project Pertama →
            </a>
        </div>
    @else
        <div class="flex flex-col gap-2">
            @foreach ($projects as $project)
                @php
                    $status      = $project->status ?? 'draft';
                    $statusStyle = $statusStyles[$status] ?? 'bg-muted text-muted-foreground';
                    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                @endphp
                <div class="flex flex-col sm:grid sm:grid-cols-12 sm:items-center gap-3 px-4 py-3.5 rounded-2xl bg-muted/40 hover:bg-muted transition-colors">
                    {{-- Name --}}
                    <div class="sm:col-span-4 flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-card flex items-center justify-center shadow-sm shrink-0">
                            <span class="text-primary font-semibold text-sm">{{ strtoupper(substr($project->name, 0, 1)) }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-card-foreground truncate">{{ $project->name }}</div>
                            <div class="text-[11px] text-muted-foreground truncate capitalize">
                                {{ $project->location ?? '—' }} · {{ $project->building_type ?? '—' }}
                            </div>
                        </div>
                        <span class="sm:hidden text-[10px] uppercase font-medium rounded-full px-2 py-1 shrink-0 {{ $statusStyle }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    {{-- Status --}}
                    <div class="hidden sm:block sm:col-span-2">
                        <span class="text-[10px] uppercase font-medium rounded-full px-2.5 py-1 {{ $statusStyle }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    {{-- Metrics --}}
                    <div class="grid grid-cols-2 gap-3 sm:contents">
                        <div class="sm:col-span-2 text-sm">
                            <div class="text-[10px] uppercase text-muted-foreground tracking-wider">Estimasi</div>
                            <div class="text-card-foreground">{{ $project->estimations_count }} item</div>
                        </div>
                        <div class="sm:col-span-2 text-sm">
                            <div class="text-[10px] uppercase text-muted-foreground tracking-wider">Total Biaya</div>
                            <div class="text-card-foreground">{{ format_rp_short((int) $project->total_cost) }}</div>
                        </div>
                    </div>

                    {{-- View button --}}
                    <div class="sm:col-span-2 flex sm:justify-end">
                        <a href="{{ route('user.project.view', $project->id) }}"
                           class="w-full sm:w-auto justify-center flex items-center gap-1.5 text-xs font-medium border border-primary/40 text-primary rounded-full px-3.5 py-1.5 hover:bg-primary hover:text-primary-foreground transition-colors">
                            <x-lucide-eye class="w-[13px] h-[13px]" /> View
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
