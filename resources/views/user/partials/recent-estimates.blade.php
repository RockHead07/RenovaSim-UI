<div class="bg-card rounded-[14px] shadow-sm p-7">
    <div class="flex items-center gap-2 mb-6">
        {{-- Eye icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-secondary">
            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>
        <h2 class="font-playfair text-lg text-secondary">
            Your Recent Estimates
        </h2>
    </div>

    {{-- Desktop table --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-[11px] uppercase text-muted-foreground tracking-wider">
                    <th class="text-left pb-4 font-medium">Project Name</th>
                    <th class="text-left pb-4 font-medium">Status</th>
                    <th class="text-left pb-4 font-medium">Material</th>
                    <th class="text-left pb-4 font-medium">Labor</th>
                    <th class="text-left pb-4 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($estimates ?? [
                    ['name' => 'Rumah Pak Budi',    'modified' => 'Modified 2 hours ago', 'status' => 'In Progress', 'material' => 'Rp 12.400.000', 'labor' => 'Rp 8.200.000'],
                    ['name' => 'Kitchen Expansion', 'modified' => 'Modified 1 day ago',   'status' => 'Completed',   'material' => 'Rp 45.000.000', 'labor' => 'Rp 18.500.000'],
                    ['name' => 'Garden Decking',    'modified' => 'Modified 3 days ago',  'status' => 'Draft',       'material' => 'Rp 5.200.000',  'labor' => 'Rp 3.000.000'],
                ] as $est)
                @php
                    $statusClass = match($est['status']) {
                        'In Progress' => 'bg-[hsl(45,96%,89%)] text-[hsl(30,72%,30%)]',
                        'Completed'   => 'bg-[hsl(113,82%,94%)] text-secondary',
                        default       => 'bg-muted text-muted-foreground',
                    };
                @endphp
                <tr class="border-t border-[hsl(0,0%,94%)]">
                    <td class="py-5">
                        <div class="font-playfair text-[15px] text-card-foreground">{{ $est['name'] }}</div>
                        <div class="text-xs text-muted-foreground mt-0.5">{{ $est['modified'] }}</div>
                    </td>
                    <td class="py-5">
                        <span class="text-[11px] uppercase font-medium rounded-full px-2.5 py-1 {{ $statusClass }}">
                            {{ $est['status'] }}
                        </span>
                    </td>
                    <td class="py-5 text-sm text-card-foreground">{{ $est['material'] }}</td>
                    <td class="py-5 text-sm text-card-foreground">{{ $est['labor'] }}</td>
                    <td class="py-5">
                        <a href="#" class="border border-primary text-primary rounded-lg px-3.5 py-1.5 text-sm hover:bg-primary hover:text-primary-foreground transition-colors">
                            View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="sm:hidden flex flex-col gap-4">
        @foreach($estimates ?? [
            ['name' => 'Rumah Pak Budi',    'modified' => 'Modified 2 hours ago', 'status' => 'In Progress', 'material' => 'Rp 12.400.000', 'labor' => 'Rp 8.200.000'],
            ['name' => 'Kitchen Expansion', 'modified' => 'Modified 1 day ago',   'status' => 'Completed',   'material' => 'Rp 45.000.000', 'labor' => 'Rp 18.500.000'],
            ['name' => 'Garden Decking',    'modified' => 'Modified 3 days ago',  'status' => 'Draft',       'material' => 'Rp 5.200.000',  'labor' => 'Rp 3.000.000'],
        ] as $est)
        @php
            $statusClass = match($est['status']) {
                'In Progress' => 'bg-[hsl(45,96%,89%)] text-[hsl(30,72%,30%)]',
                'Completed'   => 'bg-[hsl(113,82%,94%)] text-secondary',
                default       => 'bg-muted text-muted-foreground',
            };
        @endphp
        <div class="border border-border rounded-lg p-4">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <div class="font-playfair text-[15px] text-card-foreground">{{ $est['name'] }}</div>
                    <div class="text-xs text-muted-foreground mt-0.5">{{ $est['modified'] }}</div>
                </div>
                <span class="text-[11px] uppercase font-medium rounded-full px-2.5 py-1 {{ $statusClass }}">
                    {{ $est['status'] }}
                </span>
            </div>
            <div class="flex justify-between text-sm mt-3">
                <div><span class="text-muted-foreground text-xs">Material:</span> {{ $est['material'] }}</div>
                <div><span class="text-muted-foreground text-xs">Labor:</span> {{ $est['labor'] }}</div>
            </div>
            <a href="#" class="mt-3 w-full border border-primary text-primary rounded-lg py-1.5 text-sm hover:bg-primary hover:text-primary-foreground transition-colors text-center block">
                View
            </a>
        </div>
        @endforeach
    </div>

    <div class="text-center mt-6 border-t border-border pt-5">
        <a href="/projects" class="text-primary font-medium text-sm hover:underline">
            See all projects →
        </a>
    </div>
</div>
