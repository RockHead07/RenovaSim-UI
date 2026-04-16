@extends('admin.layout')

@section('title', 'Pricing Plans')
@section('page-title', 'Pricing Plans')

@section('content')
<div class="space-y-4" x-data="pricingPlansPage()">

    {{-- Toolbar --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-paragraph" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" x-model="search" placeholder="Search by plan name…"
                   class="w-full bg-background rounded-lg pl-9 pr-4 py-2 text-sm font-sans text-foreground placeholder:text-paragraph focus:outline-none focus:border-primary border border-border/10"/>
        </div>
        <a href="/admin/pricing-plans/create" class="ml-1 flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Plan
        </a>
    </div>

    {{-- Mobile: card list --}}
    <div class="flex flex-col gap-3 sm:hidden">
        <template x-if="filtered().length === 0">
            <p class="text-center text-paragraph text-sm py-8">No plans found.</p>
        </template>
        <template x-for="p in filtered()" :key="p.id">
            <div class="bg-card rounded-[12px] border border-border/10 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-foreground leading-tight" x-text="p.name"></p>
                        <p class="text-[11px] text-paragraph" x-text="`$${p.price}/mo • ${p.featuresCount} features`"></p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium" :class="p.popular ? 'bg-primary-accent/15 text-primary-accent' : 'bg-primary-accent/15 text-primary-accent'" x-text="p.popular ? 'Popular' : 'Standard'"></span>
                </div>
                
                <div class="flex gap-2 pt-1">
                    <a :href="'/admin/pricing-plans/' + p.id + '/edit'" class="flex-1 py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background text-center">Edit</a>
                    <button @click="deletePlan(p.id)" class="flex-1 py-1.5 rounded-lg text-xs font-sans font-medium" style="background:rgba(220,50,50,0.15);color:hsl(var(--destructive))">Delete</button>
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
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Price</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Features</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Popular</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Status</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="filtered().length === 0">
                            <tr><td colspan="7" class="text-center text-paragraph text-sm py-8">No plans found.</td></tr>
                        </template>
                        <template x-for="p in filtered()" :key="p.id">
                            <tr class="hover:bg-muted/50 transition-colors duration-200 border-b border-border/5">
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="'#' + p.id"></td>
                                <td class="px-5 py-3 text-sm font-sans text-foreground" x-text="p.name"></td>
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="'$' + p.price + '/mo'"></td>
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="p.featuresCount"></td>
                                <td class="px-5 py-3 text-sm font-sans text-foreground">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium" :class="p.popular ? 'bg-status-warning/15 text-status-warning' : 'bg-red-500/15 text-red-500'" x-text="p.popular ? 'Yes' : 'No'"></span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium" :class="p.active ? 'bg-primary-accent/15 text-primary-accent' : 'bg-destructive/15 text-destructive'" x-text="p.active ? 'Active' : 'Inactive'"></span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <a :href="'/admin/pricing-plans/' + p.id + '/edit'" class="px-3 py-1 rounded text-xs font-sans font-medium bg-foreground text-background">Edit</a>
                                        <button @click="deletePlan(p.id)" class="px-3 py-1 rounded text-xs font-sans font-medium" style="background:rgba(220,50,50,0.15);color:hsl(var(--destructive))">Delete</button>
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
function pricingPlansPage() {
    return {
        search: '',
        plans: [
            @foreach($plans as $p)
            {
                id: {{ $p->id }},
                name: '{{ addslashes($p->name) }}',
                price: '{{ number_format($p->price, 2) }}',
                popular: {{ $p->is_popular ? 'true' : 'false' }},
                active: {{ $p->is_active ? 'true' : 'false' }},
                featuresCount: {{ $p->features->count() }},
            },
            @endforeach
        ],
        filtered() {
            const q = this.search.toLowerCase();
            return this.plans.filter(p => p.name.toLowerCase().includes(q));
        },
        deletePlan(id) {
            if (confirm('Are you sure?')) {
                fetch(`/admin/pricing-plans/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
                .then(() => location.reload());
            }
        }
    }
}
</script>
@endpush
  
