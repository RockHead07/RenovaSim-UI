@php
    $phpMonthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    $phpDayLabels  = ['M','T','W','T','F','S','S'];
    $phpToday      = now();
    $phpFirstDay   = $phpToday->copy()->startOfMonth();
    $phpOffset     = $phpFirstDay->dayOfWeek === 0 ? 6 : ($phpFirstDay->dayOfWeek - 1);
    $phpCells      = array_merge(array_fill(0, $phpOffset, null), range(1, $phpToday->daysInMonth));
    $phpTodayDay   = $phpToday->day;
@endphp

<div
    x-data="{
        monthNames: ['January','February','March','April','May','June','July','August','September','October','November','December'],
        dayLabels: ['M','T','W','T','F','S','S'],
        today: new Date(),
        view: { year: new Date().getFullYear(), month: new Date().getMonth() },
        ready: false,
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
        },
        init() {
            this.$nextTick(() => { this.ready = true; });
        }
    }"
    class="bg-card rounded-[24px] shadow-sm p-6 h-full flex flex-col"
>
    {{-- Header: month name + year + nav buttons --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            {{-- PHP fallback shown before Alpine; Alpine replaces text after init --}}
            <div class="font-['Playfair_Display'] italic text-lg text-secondary leading-tight"
                 x-text="monthNames[view.month]">{{ $phpMonthNames[$phpToday->month - 1] }}</div>
            <div class="text-xs text-muted-foreground"
                 x-text="view.year">{{ $phpToday->year }}</div>
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

    {{-- Day label row --}}
    <div class="grid grid-cols-7 gap-1 text-center mb-1">
        @foreach($phpDayLabels as $dl)
            <div class="text-[10px] uppercase tracking-wider text-muted-foreground py-1">{{ $dl }}</div>
        @endforeach
    </div>

    {{-- PHP-rendered grid: visible before Alpine, hidden once Alpine is ready --}}
    <div x-show="!ready" class="grid grid-cols-7 gap-1 text-center">
        @foreach($phpCells as $cell)
            <div class="aspect-square flex items-center justify-center">
                @if($cell === null)
                    <span></span>
                @else
                    <span class="w-8 h-8 flex items-center justify-center rounded-full text-xs
                        {{ $cell === $phpTodayDay ? 'bg-primary text-primary-foreground font-semibold shadow-md shadow-primary/30' : 'text-card-foreground hover:bg-muted' }}">
                        {{ $cell }}
                    </span>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Alpine-rendered grid: hidden until Alpine is ready --}}
    <div x-show="ready" style="display: none" class="grid grid-cols-7 gap-1 text-center">
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

    {{-- Upcoming events (static) --}}
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
