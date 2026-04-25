@props(['assumptions'])

@php
    $labels = config('renovasim.assumption_field_id');
@endphp

@if (! empty($assumptions))
<div
    x-data="{
        editing: null,
        draft: '',
        list: @js($assumptions),
        labels: @js($labels),
        labelFor(field) { return this.labels[field] ?? field; },
        startEdit(a) { this.editing = a.field; this.draft = String(a.value); },
        confirm(field) {
            const v = this.draft.trim();
            if (v) {
                this.list = this.list.map(x => x.field === field
                    ? { ...x, value: v, source: 'user', needs_clarification: false }
                    : x);
            }
            this.editing = null;
        }
    }"
    class="bg-card rounded-2xl p-6 shadow-[0_2px_8px_rgba(0,0,0,0.07)]"
>
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-['Playfair_Display'] italic text-lg text-card-foreground">AI Assumptions</h2>
        <span class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground">Tap to edit</span>
    </div>
    <p class="font-['DM_Sans'] text-[12px] text-muted-foreground mb-4">
        These hidden variables shaped your estimate. Tweak them to recalculate.
    </p>

    <div class="divide-y divide-border">
        <template x-for="a in list" :key="a.field">
            <div class="flex items-center justify-between py-2.5 gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.12em] text-muted-foreground" x-text="labelFor(a.field)"></p>
                        <template x-if="a.needs_clarification">
                            <span class="font-['DM_Sans'] text-[9px] uppercase tracking-[0.1em] bg-[hsl(40,100%,92%)] text-[hsl(36,90%,35%)] rounded px-1.5 py-0.5">
                                Perlu klarifikasi
                            </span>
                        </template>
                    </div>
                    <template x-if="editing === a.field">
                        <input
                            x-model="draft"
                            @keydown.enter="confirm(a.field)"
                            @keydown.escape="editing = null"
                            x-init="$nextTick(() => $el.focus())"
                            class="w-full mt-0.5 font-['DM_Sans'] text-sm text-card-foreground bg-transparent border-b border-primary focus:outline-none"
                        />
                    </template>
                    <template x-if="editing !== a.field">
                        <p class="font-['DM_Sans'] text-sm font-medium text-card-foreground truncate" x-text="String(a.value)"></p>
                    </template>
                    <template x-if="a.reason && editing !== a.field">
                        <p class="font-['DM_Sans'] text-[11px] text-muted-foreground italic mt-0.5" x-text="a.reason"></p>
                    </template>
                </div>

                <template x-if="a.editable && editing === a.field">
                    <div class="flex gap-1 shrink-0">
                        <button @click="confirm(a.field)" class="p-1.5 rounded-md bg-primary text-primary-foreground hover:opacity-90 transition-opacity">
                            <x-lucide-check class="w-[13px] h-[13px]" />
                        </button>
                        <button @click="editing = null" class="p-1.5 rounded-md hover:bg-muted text-muted-foreground transition-colors">
                            <x-lucide-x class="w-[13px] h-[13px]" />
                        </button>
                    </div>
                </template>
                <template x-if="a.editable && editing !== a.field">
                    <button @click="startEdit(a)" class="p-1.5 rounded-md hover:bg-muted text-muted-foreground hover:text-card-foreground transition-colors shrink-0">
                        <x-lucide-pencil class="w-[13px] h-[13px]" />
                    </button>
                </template>
            </div>
        </template>
    </div>
</div>
@endif
