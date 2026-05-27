{{-- ============================================================
     <x-user.components.layout.sidebar /> — port of src/components/dashboard/Sidebar.tsx
     Reads `effectiveCollapsed`, `canToggle`, `mobileOpen` from the
     parent Alpine scope declared on <body> in layouts/dashboard.blade.php.
============================================================ --}}
@php
    $items = [
        ['label' => 'Dashboard',           'subtitle' => null,                    'icon' => 'layout-dashboard', 'path' => '/user/dashboard'],
        ['label' => 'RAI',                 'subtitle' => 'Renovasim Estimate AI', 'icon' => 'sparkles',         'path' => '/user/ai-estimation'],
        ['label' => 'Projects',            'subtitle' => null,                    'icon' => 'folder-kanban',    'path' => '/user/projects'],
        ['label' => '3D Design Modeling',  'subtitle' => 'House',                 'icon' => 'box',              'path' => '/user/3d'],
    ];
    $current   = '/' . trim(request()->path(), '/');
    $isActive  = fn(string $path) => $current === $path || str_starts_with($current, rtrim($path, '/') . '/');
@endphp

<aside
    :class="[
        ready ? 'transition-[width,transform] duration-300 ease-in-out' : '',
        mobileOpen ? 'translate-x-0' : '-translate-x-full',
        effectiveCollapsed ? 'md:w-20 md:items-center md:px-3 md:py-4' : 'md:w-60 md:p-4'
    ]"
    class="bg-card shadow-sm flex-col z-40 overflow-hidden
        fixed top-0 left-0 bottom-0 w-65 flex p-4
        md:translate-x-0 md:top-4 md:left-4 md:bottom-4 md:rounded-[20px]"
>
    {{-- Mobile close --}}
    <button
        @click="mobileOpen = false"
        class="md:hidden absolute top-3 right-3 w-9 h-9 rounded-xl flex items-center justify-center text-muted-foreground hover:bg-muted transition-colors"
        aria-label="Close menu"
    >
        <x-lucide-x class="w-[18px] h-[18px]" />
    </button>

    {{-- Logo --}}
    <div :class="effectiveCollapsed ? 'md:justify-center md:w-full md:px-0' : 'px-2'" class="pt-2 pb-6 flex items-center gap-2.5">
        <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center shrink-0">
            <span class="text-primary-foreground font-bold text-sm">R</span>
        </div>
        <div :class="effectiveCollapsed && 'md:hidden'" class="flex flex-col">
            <div class="font-['Playfair_Display'] italic text-[17px] text-secondary leading-tight whitespace-nowrap">
                RenovaSim
            </div>
            <div class="text-[10px] uppercase tracking-wider text-muted-foreground whitespace-nowrap">
                Owner workspace
            </div>
        </div>
    </div>

    {{-- Menu label --}}
    <div :class="effectiveCollapsed && 'md:hidden'" class="text-[10px] uppercase tracking-wider text-muted-foreground px-3 pb-2">
        Menu
    </div>

    <nav :class="effectiveCollapsed ? 'md:items-center md:gap-3 md:w-full' : ''" class="flex flex-col gap-1">
        @foreach ($items as $it)
            @php $active = $isActive($it['path']); @endphp
            <a
                href="{{ $it['path'] }}"
                @click="mobileOpen = false"
                :title="effectiveCollapsed ? '{{ $it['label'] }}' : null"
                @class([
                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-colors',
                    'bg-primary/10 text-secondary ring-1 ring-primary/25 shadow-[0_10px_24px_rgba(139,160,35,0.28)]' => $active,
                    'text-card-foreground hover:bg-muted'    => ! $active,
                ])
                :class="effectiveCollapsed ? 'md:w-11 md:h-11 md:px-0 md:py-0 md:gap-0 md:justify-center' : ''"
            >
                <span
                    @class([
                        'shrink-0',
                        'text-secondary' => $active,
                        'text-muted-foreground'   => ! $active,
                    ])
                >
                    <x-dynamic-component
                        :component="'lucide-' . $it['icon']"
                        ::class="effectiveCollapsed ? 'w-5 h-5' : 'w-[18px] h-[18px]'"
                    />
                </span>
                <div :class="effectiveCollapsed && 'md:hidden'" class="flex flex-col">
                    <span class="text-sm font-medium leading-tight whitespace-nowrap">{{ $it['label'] }}</span>
                    @if ($it['subtitle'])
                        <span @class([
                            'text-[10px] whitespace-nowrap',
                            'text-primary-foreground/70' => $active,
                            'text-muted-foreground'      => ! $active,
                        ])>
                            {{ $it['subtitle'] }}
                        </span>
                    @endif
                </div>
            </a>
        @endforeach
    </nav>

    {{-- Tip of the day --}}
    <div :class="effectiveCollapsed && 'md:hidden'" class="mt-auto bg-muted rounded-2xl p-4">
        <div class="text-xs font-medium text-card-foreground mb-1 whitespace-nowrap">Tip of the day</div>
        <p class="text-[11px] text-muted-foreground leading-relaxed">
            Use RAI to generate a detailed estimate from a simple project description.
        </p>
    </div>

    {{-- Collapse toggle (desktop only — hidden on tablet where sidebar is forced collapsed) --}}
    <div
        x-show="canToggle"
        :class="collapsed && 'mt-auto lg:w-full lg:flex lg:justify-center'"
        class="hidden lg:block pt-3"
    >
        <template x-if="collapsed">
            <button
                @click="collapsed = false"
                title="Expand sidebar"
                class="w-11 h-11 rounded-xl flex items-center justify-center text-muted-foreground hover:bg-muted hover:text-card-foreground transition-colors"
            >
                <x-lucide-chevron-right class="w-5 h-5" />
            </button>
        </template>
        <template x-if="!collapsed">
            <button
                @click="collapsed = true"
                title="Collapse sidebar"
                class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-xs text-muted-foreground hover:text-card-foreground hover:bg-muted transition-colors"
            >
                <x-lucide-chevron-left class="w-[18px] h-[18px]" />
                <span>Collapse</span>
            </button>
        </template>
    </div>
</aside>
