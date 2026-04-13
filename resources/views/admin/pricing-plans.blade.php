@extends('layouts.app')

@section('title', 'Pricing Plans')
@section('page-title', 'Pricing Plans')

@section('content')
<div x-data="pricingPlansPage()">

    {{-- Mobile: card list --}}
    <div class="flex flex-col gap-3 sm:hidden mb-4">
        <div class="flex items-center justify-between mb-1">
            <h2 class="font-serif text-lg text-foreground">Pricing Plans</h2>
            <button class="px-3 py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background">+ Add Plan</button>
        </div>
        <template x-for="p in plans" :key="p.name">
            <div class="bg-card rounded-[12px] border border-border/10 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-[11px] font-semibold text-foreground shrink-0"
                             :style="{ background: planColors[p.name] ?? '#838383' }"
                             x-text="p.name.slice(0,2).toUpperCase()"></div>
                        <p class="text-sm font-medium text-foreground" x-text="p.name"></p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium bg-status-active/15 text-status-active"
                          x-text="p.active === 'Yes' ? 'Active' : 'Inactive'"></span>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-border/10 text-center">
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph mb-0.5">Price</p>
                        <p class="text-base font-serif text-foreground" x-text="p.price"></p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph mb-0.5">Popular</p>
                        <p class="text-xs font-medium text-foreground" x-text="p.popular"></p>
                    </div>
                </div>
                <button @click="openEdit(p)" class="w-full py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background">
                    Edit Plan
                </button>
            </div>
        </template>
    </div>

    {{-- Desktop: full table --}}
    <div class="hidden sm:block">
        <div class="bg-card rounded-[10px] overflow-hidden border border-border/10">
            <div class="flex items-center justify-between px-5 py-4">
                <h3 class="font-serif text-foreground text-base">Pricing Plans</h3>
                <button class="px-3 py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background">+ Add Plan</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border/10">
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Name</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Price</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Popular</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Active</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(p, i) in plans" :key="p.name">
                            <tr class="hover:bg-muted/50 transition-colors duration-200 border-b border-border/5">
                                <td class="px-5 py-3 text-sm font-sans text-foreground" x-text="p.name"></td>
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="p.price"></td>
                                <td class="px-5 py-3 text-sm font-sans" :class="p.popular === 'Yes' ? 'text-foreground' : 'text-paragraph'" x-text="p.popular"></td>
                                <td class="px-5 py-3">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium bg-status-active/15 text-status-active"
                                          x-text="p.active === 'Yes' ? 'Active' : 'Inactive'"></span>
                                </td>
                                <td class="px-5 py-3">
                                    <button @click="openEdit(p)" class="px-3 py-1 rounded text-xs font-sans font-medium bg-foreground text-background">Edit</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="editOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/60" @click="editOpen = false"></div>
        <div class="relative bg-card border border-border/10 rounded-[14px] p-6 w-full max-w-md shadow-2xl">
            <h2 class="font-serif text-foreground text-lg mb-4">Edit Plan</h2>
            <div class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-sans text-paragraph uppercase tracking-widest">Name</label>
                    <input x-model="form.name" class="w-full bg-background rounded-lg px-4 py-2 text-sm font-sans text-foreground focus:outline-none border border-border/10"/>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-sans text-paragraph uppercase tracking-widest">Price ($/mo)</label>
                    <input type="number" x-model="form.price" class="w-full bg-background rounded-lg px-4 py-2 text-sm font-sans text-foreground focus:outline-none border border-border/10"/>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-sans text-paragraph uppercase tracking-widest">Popular</label>
                    <select x-model="form.popular" class="w-full bg-background rounded-lg px-4 py-2 text-sm font-sans text-foreground focus:outline-none border border-border/10">
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-sans text-paragraph uppercase tracking-widest">Active</label>
                    <select x-model="form.active" class="w-full bg-background rounded-lg px-4 py-2 text-sm font-sans text-foreground focus:outline-none border border-border/10">
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button @click="editOpen = false" class="px-4 py-2 rounded-lg text-xs font-sans font-medium text-paragraph hover:text-foreground transition-colors">Cancel</button>
                <button @click="editOpen = false" class="px-4 py-2 rounded-lg text-xs font-sans font-medium bg-primary text-white">Save Changes</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const plansData = {!! json_encode($plans->map(p => [
    'name' => p.name,
    'price' => '$' . p.price . '/mo',
    'popular' => p.is_popular ? 'Yes' : 'No',
    'active' => p.is_active ? 'Yes' : 'No'
])->toArray() ?? []) !!};

function pricingPlansPage() {
    return {
        editOpen: false,
        form: { name:'', price:'', popular:'No', active:'Yes' },
        planColors: { Free:'#838383', Smart:'#8BA023', Pro:'#d4941a' },
        plans: plansData,
        openEdit(row) {
            this.form = { name:row.name, price:row.price.replace('/mo','').replace('$',''), popular:row.popular, active:row.active };
            this.editOpen = true;
        },
    }
}
</script>
@endpush
