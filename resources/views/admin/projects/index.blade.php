@extends('admin.layout')

@section('title', 'Projects')
@section('page-title', 'Projects')

@section('content')
<div class="space-y-4" x-data="projectsPage()">

    {{-- Toolbar --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
        {{-- Search --}}
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-paragraph" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" x-model="search" @input="filterProjects()" placeholder="Search by name, user, or room…"
                   class="w-full bg-background rounded-lg pl-9 pr-4 py-2 text-sm font-sans text-foreground placeholder:text-paragraph focus:outline-none focus:border-paragraph border border-border"/>
        </div>
        <div class="flex items-center gap-1.5 flex-wrap">
            <template x-for="f in statuses" :key="f">
                <button @click="statusFilter = f"
                        :class="statusFilter === f ? 'bg-primary text-primary-foreground' : 'text-paragraph hover:text-foreground hover:bg-muted'"
                        class="px-3 py-1.5 rounded-lg text-xs font-sans font-medium transition-colors duration-200"
                        x-text="f"></button>
            </template>
            <a href="/admin/projects/create" class="ml-1 flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Project
            </a>
        </div>
    </div>

    {{-- Mobile: card list --}}
    <div class="flex flex-col gap-3 sm:hidden">
        <template x-if="filtered().length === 0">
            <p class="text-center text-paragraph text-sm py-8">No projects found.</p>
        </template>
        <template x-for="p in filtered()" :key="p.id">
            <div class="bg-card rounded-[12px] border border-border/10 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-[11px] font-semibold text-foreground shrink-0"
                             :style="{ background: avatarColor(p.status) }" x-text="initials(p.name)"></div>
                        <div>
                            <p class="text-sm font-medium text-foreground leading-tight" x-text="p.name"></p>
                            <p class="text-[11px] text-paragraph" x-text="p.user"></p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium" :class="statusBadgeClass(p.status)" x-text="p.status"></span>
                </div>
                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-border/10 text-center">
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph mb-0.5">Room</p>
                        <p class="text-xs font-medium text-foreground" x-text="p.room"></p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph mb-0.5">Area</p>
                        <p class="text-xs font-medium text-foreground" x-text="p.area"></p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph mb-0.5">Cost</p>
                        <p class="text-xs font-medium text-foreground" x-text="p.cost"></p>
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <a :href="'/admin/projects/' + p.id + '/edit'" class="flex-1 py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background text-center">Edit</a>
                    <button @click="deleteProject(p.id)" class="flex-1 py-1.5 rounded-lg text-xs font-sans font-medium" style="background:rgba(220,50,50,0.15);color:hsl(var(--destructive))">Delete</button>
                </div>
            </div>
        </template>
    </div>

    {{-- Desktop: full table --}}
    <div class="hidden sm:block">
        <div class="bg-card rounded-[10px] overflow-hidden border border-border/10">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border/10">
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">ID</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Project Name</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">User</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Room</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Area</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Cost</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Status</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="filtered().length === 0">
                            <tr><td colspan="8" class="text-center text-paragraph text-sm py-8">No projects found.</td></tr>
                        </template>
                        <template x-for="p in filtered()" :key="p.id">
                            <tr class="hover:bg-muted/50 transition-colors duration-200 border-b border-border/5">
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="'#' + p.id"></td>
                                <td class="px-5 py-3 text-sm font-sans text-foreground" x-text="p.name"></td>
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="p.user"></td>
                                <td class="px-5 py-3 text-sm font-sans text-foreground" x-text="p.room"></td>
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="p.area"></td>
                                <td class="px-5 py-3 text-sm font-sans text-foreground" x-text="p.cost"></td>
                                <td class="px-5 py-3">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium" :class="statusBadgeClass(p.status)" x-text="p.status"></span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <a :href="'/admin/projects/' + p.id + '/edit'" class="px-3 py-1 rounded text-xs font-sans font-medium bg-foreground text-background">Edit</a>
                                        <button @click="deleteProject(p.id)" class="px-3 py-1 rounded text-xs font-sans font-medium" style="background:rgba(220,50,50,0.15);color:hsl(var(--destructive))">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@php
    $mappedProjects = $projects->map(function($p) {
        return [
            'id'     => $p->id,
            'name'   => $p->name,
            'user'   => $p->user->username ?? 'N/A',
            'room'   => $p->room_type ?? '—',
            'area'   => $p->area_size ? $p->area_size . ' m²' : '—',
            'cost'   => $p->total_cost ? 'Rp ' . number_format((int)$p->total_cost, 0, ',', '.') : '—',
            'status' => $p->status ? ucfirst($p->status) : 'Draft',
            'estimations_count' => $p->estimations_count ?? 0,
        ];
    });
@endphp
@push('scripts')
<script>
function projectsPage() {
    return {
        search: '',
        statusFilter: 'All',
        statuses: ['All', 'Draft', 'Active', 'Completed'],
        avatarColors: { Completed:'#8BA023', Active:'#d4941a', Draft:'#838383' },
        projects: @json($mappedProjects),

        initials(name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
        },
        avatarColor(status) {
            return this.avatarColors[status] ?? '#838383';
        },
        statusBadgeClass(status) {
            return {
                Completed: 'bg-status-active/15 text-status-active',
                Active:    'bg-status-warning/15 text-status-warning',
                Draft:     'bg-muted text-muted-foreground',
            }[status] ?? 'bg-muted text-muted-foreground';
        },
        filtered() {
            const q = this.search.toLowerCase();
            return this.projects.filter(p => {
                const ms = !q ||
                    p.name.toLowerCase().includes(q) ||
                    p.user.toLowerCase().includes(q) ||
                    (p.room && p.room.toLowerCase().includes(q));
                const mf = this.statusFilter === 'All' || p.status === this.statusFilter;
                return ms && mf;
            });
        },
        async deleteProject(id) {
            if (!confirm('Are you sure you want to delete this project?')) return;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/admin/projects/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                });
                if (res.ok || res.redirected) {
                    this.projects = this.projects.filter(p => p.id !== id);
                } else {
                    alert('Error deleting project');
                }
            } catch (e) {
                alert('Error deleting project');
            }
        },
    }
}
</script>
@endpush
  
