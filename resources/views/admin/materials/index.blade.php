@extends('admin.layout')

@section('title', 'Materials')
@section('page-title', 'Materials')

@section('content')
<div class="space-y-4" x-data="materialsPage()">

    {{-- Toolbar --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
        {{-- Search --}}
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-paragraph" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" x-model="search" @input="filterMaterials()" placeholder="Search by name or category…"
                   class="w-full bg-background rounded-lg pl-9 pr-4 py-2 text-sm font-sans text-foreground placeholder:text-paragraph focus:outline-none focus:border-paragraph border border-border"/>
        </div>
        <a href="/admin/materials/create" class="ml-1 flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Material
        </a>
    </div>

    {{-- Mobile: card list --}}
    <div class="flex flex-col gap-3 sm:hidden">
        <template x-if="filtered().length === 0">
            <p class="text-center text-paragraph text-sm py-8">No materials found.</p>
        </template>
        <template x-for="m in filtered()" :key="m.id">
            <div class="bg-card rounded-[12px] border border-border/10 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-[11px] font-semibold text-foreground shrink-0"
                             style="background: #8BA023" x-text="initials(m.name)"></div>
                        <div>
                            <p class="text-sm font-medium text-foreground leading-tight" x-text="m.name"></p>
                            <p class="text-[11px] text-paragraph" x-text="m.category"></p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-border/10 text-center">
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph mb-0.5">Price</p>
                        <p class="text-xs font-medium text-foreground" x-text="'$' + m.price"></p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph mb-0.5">Unit</p>
                        <p class="text-xs font-medium text-foreground" x-text="m.unit"></p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph mb-0.5">ID</p>
                        <p class="text-xs font-medium text-foreground" x-text="'#' + m.id"></p>
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <a :href="'/admin/materials/' + m.id + '/edit'" class="flex-1 py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background text-center">Edit</a>
                    <button @click="deleteMaterial(m.id)" class="flex-1 py-1.5 rounded-lg text-xs font-sans font-medium" style="background:rgba(220,50,50,0.15);color:hsl(var(--destructive))">Delete</button>
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
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Name</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Category</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Price/Unit</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Unit</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="filtered().length === 0">
                            <tr><td colspan="6" class="text-center text-paragraph text-sm py-8">No materials found.</td></tr>
                        </template>
                        <template x-for="m in filtered()" :key="m.id">
                            <tr class="hover:bg-muted/50 transition-colors duration-200 border-b border-border/5">
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="'#' + m.id"></td>
                                <td class="px-5 py-3 text-sm font-sans text-foreground" x-text="m.name"></td>
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="m.category"></td>
                                <td class="px-5 py-3 text-sm font-sans text-foreground" x-text="'$' + m.price"></td>
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="m.unit"></td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <a :href="'/admin/materials/' + m.id + '/edit'" class="px-3 py-1 rounded text-xs font-sans font-medium bg-foreground text-background">Edit</a>
                                        <button @click="deleteMaterial(m.id)" class="px-3 py-1 rounded text-xs font-sans font-medium" style="background:rgba(220,50,50,0.15);color:hsl(var(--destructive))">Delete</button>
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

@push('scripts')
<script>
function materialsPage() {
    return {
        search: '',
        materials: [],
        async init() {
            try {
                const res = await apiFetch('/api/materials');
                const raw = res.data ?? [];
                this.materials = raw.map(m => ({
                    id: m.id,
                    name: m.name,
                    category: m.category,
                    price: Number(m.price_per_unit).toFixed(2),
                    unit: m.unit,
                }));
            } catch (e) {
                console.error('Failed to fetch materials:', e);
            }
        },
        initials(name) { return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0,2); },
        filtered() {
            const q = this.search.toLowerCase();
            return this.materials.filter(m => (
                m.name.toLowerCase().includes(q) || m.category.toLowerCase().includes(q)
            ));
        },
        async deleteMaterial(id) {
            if (!confirm('Are you sure?')) return;
            try {
                await apiFetch(`/api/materials/${id}`, { method: 'DELETE' });
                this.materials = this.materials.filter(m => m.id !== id);
            } catch (e) {
                alert('Error deleting material');
            }
        }
    }
}
</script>
@endpush
