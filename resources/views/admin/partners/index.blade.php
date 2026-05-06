@extends('admin.layout')

@section('title', 'Partners')
@section('page-title', 'Partners')

@section('content')
<div class="space-y-4" x-data="partnersPage()" class="relative">

    {{-- Toolbar --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
        {{-- Search --}}
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-paragraph" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" x-model="search" @input="filterPartners()" placeholder="Search by partner name…"
                   class="w-full bg-background rounded-lg pl-9 pr-4 py-2 text-sm font-sans text-foreground placeholder:text-paragraph focus:outline-none focus:border-paragraph border border-border"/>
        </div>
        <a href="/admin/partners/create" class="ml-1 flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Partner
        </a>
    </div>

    {{-- Mobile: card list --}}
    <div class="flex flex-col gap-3 sm:hidden">
        <template x-if="filtered().length === 0">
            <p class="text-center text-paragraph text-sm py-8">No partners found.</p>
        </template>
        <template x-for="p in filtered()" :key="p.id">
            <div class="bg-card rounded-[12px] border border-border/10 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                            <button @click="openModal(p.logo_image, p.name)" type="button" class="w-9 h-9 rounded-full flex items-center justify-center text-[11px] font-semibold text-foreground shrink-0 bg-primary overflow-hidden cursor-pointer hover:scale-110 transition-transform duration-200 border-none p-0">
                                <template x-if="p.logo_image">
                                    <img :src="p.logo_image" :alt="p.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!p.logo_image">
                                    <span x-text="initials(p.name)"></span>
                                </template>
                            </button>
                        <div>
                            <p class="text-sm font-medium text-foreground leading-tight" x-text="p.name"></p>
                            <p class="text-[11px] text-paragraph" x-text="'Order: ' + p.order"></p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium bg-status-active/15 text-status-active">Active</span>
                </div>
                <div class="grid grid-cols-1 gap-2 pt-2 border-t border-border/10 text-center">
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph mb-0.5">Status</p>
                        <p class="text-xs font-medium text-status-active">Active</p>
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <a :href="'/admin/partners/' + p.id + '/edit'" class="flex-1 py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background text-center">Edit</a>
                    <button @click="deletePartner(p.id)" class="flex-1 py-1.5 rounded-lg text-xs font-sans font-medium" style="background:rgba(220,50,50,0.15);color:hsl(var(--destructive))">Delete</button>
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
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3 cursor-pointer hover:text-foreground transition-colors">
                                <button @click="toggleSort('name')" class="inline-flex items-center gap-1.5">
                                    <span class="text-[10px] uppercase tracking-widest">Name</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 transition-all" :class="sortBy === 'name' ? 'text-foreground' : 'text-paragraph opacity-60'" :style="{ transform: sortBy === 'name' && sortOrder === 'desc' ? 'scaleY(-1)' : 'scaleY(1)' }">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </button>
                            </th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Logo</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3 cursor-pointer hover:text-foreground transition-colors">
                                <button @click="toggleSort('order')" class="inline-flex items-center gap-1.5">
                                    <span class="text-[10px] uppercase tracking-widest">Order</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 transition-all" :class="sortBy === 'order' ? 'text-foreground' : 'text-paragraph opacity-60'" :style="{ transform: sortBy === 'order' && sortOrder === 'desc' ? 'scaleY(-1)' : 'scaleY(1)' }">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </button>
                            </th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Status</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="filtered().length === 0">
                            <tr><td colspan="5" class="text-center text-paragraph text-sm py-8">No partners found.</td></tr>
                        </template>
                        <template x-for="p in filtered()" :key="p.id">
                            <tr class="hover:bg-muted/50 transition-colors duration-200 border-b border-border/5">
                                <td class="px-5 py-3 text-sm font-sans text-foreground" x-text="p.name"></td>
                                <td class="px-5 py-3 text-sm font-sans text-foreground">
                                    <button @click="openModal(p.logo_image, p.name)" type="button" class="w-8 h-8 rounded bg-primary flex items-center justify-center text-foreground text-xs font-sans font-medium overflow-hidden cursor-pointer hover:scale-110 transition-transform duration-200 border-none p-0">
                                        <template x-if="p.logo_image">
                                            <img :src="p.logo_image" :alt="p.name" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!p.logo_image">
                                            <span x-text="initials(p.name)"></span>
                                        </template>
                                    </button>
                                </td>
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="p.order"></td>
                                <td class="px-5 py-3">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium bg-primary text-primary-accent">Active</span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <a :href="'/admin/partners/' + p.id + '/edit'" class="px-3 py-1 rounded text-xs font-sans font-medium bg-foreground text-background">Edit</a>
                                        <button @click="deletePartner(p.id)" class="px-3 py-1 rounded text-xs font-sans font-medium" style="background:rgba(220,50,50,0.15);color:hsl(var(--destructive))">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Image Preview Modal --}}
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center" 
        style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
        @click="showModal = false" @keydown.escape="showModal = false">
        <div @click.stop class="relative bg-card rounded-lg shadow-2xl max-w-2xl w-11/12 max-h-[80vh] flex flex-col">
            <div class="absolute top-4 right-4 z-10">
                <button @click="showModal = false" type="button" class="text-paragraph hover:text-foreground transition-colors">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="flex-1 flex items-end justify-center p-8 overflow-auto">
                <img :src="modalImage" :alt="partnerName" class="max-w-full max-h-full object-contain">
            </div>
            <div class="border-t border-border/10 px-8 py-4 bg-muted/50">
                <p class="text-center text-foreground font-medium" x-text="partnerName"></p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function partnersPage() {
    return {
        search: '',
        sortBy: 'order',
        sortOrder: 'asc',
        showModal: false,
        modalImage: '',
        partnerName: '',
        partners: [],
        async init() {
            try {
                const res = await apiFetch('/api/partners');
                const raw = res.data ?? [];
                this.partners = raw.map(p => ({
                    id: p.id,
                    name: p.name,
                    order: p.order,
                    logo_image: p.logo_image ?? '',
                }));
            } catch (e) {
                console.error('Failed to fetch partners:', e);
            }
        },
        initials(name) { return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0,2); },
        toggleSort(field) {
            if (this.sortBy === field) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = field;
                this.sortOrder = 'asc';
            }
        },
        filtered() {
            const q = this.search.toLowerCase();
            let result = this.partners.filter(p => p.name.toLowerCase().includes(q));
            
            result.sort((a, b) => {
                let aVal = a[this.sortBy];
                let bVal = b[this.sortBy];
                
                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                
                if (this.sortOrder === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
            
            return result;
        },
        openModal(imagePath, name) {
            if (imagePath) {
                this.modalImage = imagePath;
                this.partnerName = name;
                this.showModal = true;
            }
        },
        async deletePartner(id) {
            if (!confirm('Are you sure?')) return;
            try {
                await apiFetch(`/api/partners/${id}`, { method: 'DELETE' });
                this.partners = this.partners.filter(p => p.id !== id);
            } catch (e) {
                alert('Error deleting partner');
            }
        }
    }
}
</script>
@endpush
  
