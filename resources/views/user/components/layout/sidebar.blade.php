{{-- ============================================================
     <x-sidebar /> — port of src/components/dashboard/Sidebar.tsx
     Reads `collapsed` & `mobileOpen` from the parent Alpine scope
     declared on <body> in layouts/dashboard.blade.php.
============================================================ --}}
@php
    $items = [
        ['label' => 'Dashboard',           'subtitle' => null,                    'icon' => 'layout-dashboard', 'path' => '/'],
        ['label' => 'RAI',                 'subtitle' => 'Renovasim Estimate AI', 'icon' => 'sparkles',         'path' => '/ai-estimation'],
        ['label' => 'Projects',            'subtitle' => null,                    'icon' => 'folder-kanban',    'path' => '/project-overview'],
        ['label' => '3D Design Modeling',  'subtitle' => 'House',                 'icon' => 'box',              'path' => '/3d'],
    ];
    $current = '/' . trim(request()->path(), '/');
    if ($current === '/') {} elseif (request()->path() === '/') { $current = '/'; }
@endphp

<aside
    :class="[
        mobileOpen ? 'translate-x-0' : '-translate-x-full',
        collapsed ? 'lg:w-[80px] lg:items-center lg:px-3 lg:py-4' : 'lg:w-[240px] lg:p-4'
    ]"
    class="bg-card shadow-sm flex-col z-40 transition-[width,transform] duration-300 ease-in-out overflow-hidden
        fixed top-0 left-0 bottom-0 w-[260px] flex p-4
        lg:translate-x-0 lg:top-4 lg:left-4 lg:bottom-4 lg:rounded-[20px]"
>
    {{-- Mobile close --}}
    <button
        @click="mobileOpen = false"
        class="lg:hidden absolute top-3 right-3 w-9 h-9 rounded-xl flex items-center justify-center text-muted-foreground hover:bg-muted transition-colors"
        aria-label="Close menu"
    >
        <x-lucide-x class="w-[18px] h-[18px]" />
    </button>

    {{-- Logo --}}
    <div :class="collapsed ? 'lg:justify-center lg:w-full lg:px-0' : 'px-2'" class="pt-2 pb-6 flex items-center gap-2.5">
        <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center shrink-0">
            <span class="text-primary-foreground font-bold text-sm">R</span>
        </div>
        <div :class="collapsed && 'lg:hidden'" class="flex flex-col">
            <div class="font-['Playfair_Display'] italic text-[17px] text-secondary leading-tight whitespace-nowrap">
                RenovaSim
            </div>
            <div class="text-[10px] uppercase tracking-wider text-muted-foreground whitespace-nowrap">
                Owner workspace
            </div>
        </div>
    </div>

    {{-- Menu label --}}
    <div :class="collapsed && 'lg:hidden'" class="text-[10px] uppercase tracking-wider text-muted-foreground px-3 pb-2">
        Menu
    </div>

    <nav :class="collapsed ? 'lg:items-center lg:gap-3 lg:w-full' : ''" class="flex flex-col gap-1">
        @foreach ($items as $it)
            @php $active = $current === $it['path']; @endphp
            <a
                href="{{ $it['path'] }}"
                @click="mobileOpen = false"
                :title="collapsed ? '{{ $it['label'] }}' : null"
                @class([
                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-colors',
                    'bg-secondary text-secondary-foreground' => $active,
                    'text-card-foreground hover:bg-muted'    => ! $active,
                ])
                :class="collapsed ? 'lg:w-11 lg:h-11 lg:px-0 lg:py-0 lg:gap-0 lg:justify-center' : ''"
            >
                <span
                    @class([
                        'shrink-0',
                        'text-primary-foreground' => $active,
                        'text-muted-foreground'   => ! $active,
                    ])
                >
                    <x-dynamic-component
                        :component="'lucide-' . $it['icon']"
                        ::class="collapsed ? 'w-5 h-5' : 'w-[18px] h-[18px]'"
                    />
                </span>
                <div :class="collapsed && 'lg:hidden'" class="flex flex-col">
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
    <div :class="collapsed && 'lg:hidden'" class="mt-auto bg-muted rounded-2xl p-4">
        <div class="text-xs font-medium text-card-foreground mb-1 whitespace-nowrap">Tip of the day</div>
        <p class="text-[11px] text-muted-foreground leading-relaxed">
            Use RAI to generate a detailed estimate from a simple project description.
        </p>
    </div>

    {{-- Collapse toggle (desktop only) --}}
    <div :class="collapsed && 'mt-auto lg:w-full lg:flex lg:justify-center'" class="hidden lg:block pt-3">
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
