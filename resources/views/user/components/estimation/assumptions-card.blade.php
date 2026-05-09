@php
    $assumptions = $assumptions ?? [];
@endphp

<div class="bg-card rounded-2xl shadow-sm p-6 sm:p-7 flex flex-col gap-4">
    <h3 class="font-['Playfair_Display'] italic text-lg text-secondary">Assumptions & Details</h3>

    <div class="space-y-3">
        @foreach ($assumptions as $assum)
            <div
                x-data="{ editing: false, tempValue: '{{ $assum['value'] }}' }"
                class="pb-3 border-b border-border/50 last:border-b-0 flex flex-col gap-1.5"
            >
                <label class="text-[10px] uppercase tracking-wider text-muted-foreground font-medium">
                    {{ $assum['field'] }}
                </label>

                <div x-show="!editing" class="flex items-center justify-between gap-2 group">
                    <div class="text-sm text-card-foreground wrap-break-word flex-1">
                        {{ $assum['value'] }}
                    </div>
                    <button
                        @click="editing = true"
                        class="p-1 rounded hover:bg-muted opacity-0 group-hover:opacity-100 transition-opacity shrink-0"
                        title="Edit"
                    >
                        <x-lucide-pencil class="w-[13px] h-[13px] text-muted-foreground" />
                    </button>
                </div>

                <div x-show="editing" x-cloak class="flex gap-2">
                    <input
                        x-model="tempValue"
                        @keydown.escape="editing = false"
                        @keydown.enter="editing = false"
                        class="flex-1 text-sm px-0 py-1 border-b-[1.5px] border-primary bg-transparent text-card-foreground focus:outline-none"
                        type="text"
                    />
                </div>

                @if (!empty($assum['reason']))
                    <p class="text-xs text-muted-foreground italic">{{ $assum['reason'] }}</p>
                @endif

                @if (!empty($assum['needs_clarification']))
                    <div class="text-[10px] text-[hsl(35,100%,52%)] font-medium">⚠ Needs clarification</div>
                @endif
            </div>
        @endforeach
    </div>
</div>
