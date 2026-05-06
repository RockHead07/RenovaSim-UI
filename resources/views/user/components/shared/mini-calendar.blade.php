{{-- ============================================================
     <x-mini-calendar /> — port of MiniCalendar.tsx
     Fully Alpine-driven month grid; today is highlighted in olive.
============================================================ --}}
<div
    x-data="{
        monthNames: ['January','February','March','April','May','June','July','August','September','October','November','December'],
        dayLabels: ['M','T','W','T','F','S','S'],
        today: new Date(),
        view: { year: new Date().getFullYear(), month: new Date().getMonth() },
        get cells() {
            const first = new Date(this.view.year, this.view.month, 1);
            const offset = (first.getDay() + 6) % 7;
            const days = new Date(this.view.year, this.view.month + 1, 0).getDate();
            const arr = [];
            for (let i = 0; i < offset; i++) arr.push(null);
            for (let d = 1; d <= days; d++) arr.push(d);
            return arr;
        },
        isToday(d) {
            return d === this.today.getDate()
                && this.view.month === this.today.getMonth()
                && this.view.year === this.today.getFullYear();
        },
        move(delta) {
            const next = new Date(this.view.year, this.view.month + delta, 1);
            this.view = { year: next.getFullYear(), month: next.getMonth() };
        }
    }"
    class="bg-card rounded-[24px] shadow-sm p-6 h-full flex flex-col"
>
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="font-['Playfair_Display'] italic text-lg text-secondary leading-tight" x-text="monthNames[view.month]"></div>
            <div class="text-xs text-muted-foreground" x-text="view.year"></div>
        </div>
        <div class="flex items-center gap-1">
            <button @click="move(-1)" class="w-7 h-7 rounded-full hover:bg-muted flex items-center justify-center text-muted-foreground">
                <x-lucide-chevron-left class="w-[15px] h-[15px]" />
            </button>
            <button @click="move(1)" class="w-7 h-7 rounded-full hover:bg-muted flex items-center justify-center text-muted-foreground">
                <x-lucide-chevron-right class="w-[15px] h-[15px]" />
            </button>
        </div>
    </div>

    <div class="grid grid-cols-7 gap-1 text-center">
        <template x-for="(d, i) in dayLabels" :key="i">
            <div class="text-[10px] uppercase tracking-wider text-muted-foreground py-1" x-text="d"></div>
        </template>
        <template x-for="(c, i) in cells" :key="i">
            <div class="aspect-square flex items-center justify-center">
                <template x-if="c === null"><span></span></template>
                <template x-if="c !== null">
                    <span
                        :class="isToday(c)
                            ? 'bg-primary text-primary-foreground font-semibold shadow-md shadow-primary/30'
                            : 'text-card-foreground hover:bg-muted'"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-xs"
                        x-text="c"
                    ></span>
                </template>
            </div>
        </template>
    </div>

    <div class="mt-auto pt-5 border-t border-border/60">
        <div class="text-[10px] uppercase tracking-wider text-muted-foreground mb-2">Upcoming</div>
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-9 rounded-full bg-primary"></div>
            <div>
                <div class="text-sm font-medium text-card-foreground">Site visit · Pak Budi</div>
                <div class="text-[11px] text-muted-foreground">Tomorrow · 10:00 AM</div>
            </div>
        </div>
    </div>
</div>
