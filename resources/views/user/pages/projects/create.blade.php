{{-- pages.project-details — 5-step wizard, port of ProjectDetails.tsx --}}
@php
    $cities = config('renovasim.cities');
    $renovationTypes = config('renovasim.renovation_types');
    $TOTAL_STEPS = 5;
@endphp

<x-layouts.app title="RenovaSim — Project Details" :hideFooter="true">
    <div
        x-data="{
            step: 1,
            total: {{ $TOTAL_STEPS }},
            animating: false,
            direction: 'forward',
            projectName: '',
            city: '',
            renovationType: '',
            quality: '',
            budgetDisplay: '',
            get budgetValue() {
                const d = (this.budgetDisplay || '').replace(/\D/g, '');
                return d ? parseInt(d, 10) : 0;
            },
            get progressPct() { return Math.round((this.step / this.total) * 100); },
            formatIDR(raw) {
                const digits = (raw || '').replace(/\D/g, '');
                if (!digits) return '';
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            },
            canAdvance() {
                if (this.step === 1) return this.projectName.trim().length > 0;
                if (this.step === 2) return this.city.length > 0;
                if (this.step === 3) return this.renovationType.length > 0;
                if (this.step === 4) return this.quality.length > 0;
                if (this.step === 5) return this.budgetValue > 0;
                return false;
            },
            buildEstimationUrl(includeBudget) {
                const params = new URLSearchParams({
                    projectName: this.projectName,
                    city: this.city,
                    renovationType: this.renovationType,
                    quality: this.quality,
                });
                if (includeBudget && this.budgetValue > 0) params.set('budget', this.budgetValue);
                return '/ai-estimation?' + params.toString();
            },
            transition(cb) {
                this.animating = true;
                setTimeout(() => { cb(); this.animating = false; }, 220);
            },
            next() {
                if (!this.canAdvance()) return;
                if (this.step === this.total) {
                    window.location.href = this.buildEstimationUrl(true);
                    return;
                }
                this.direction = 'forward';
                this.transition(() => this.step++);
            },
            skip() {
                if (this.step === 5) window.location.href = this.buildEstimationUrl(false);
            },
            back() {
                if (this.step === 1) { window.location.href = '/project-stage'; return; }
                this.direction = 'back';
                this.transition(() => this.step--);
            },
        }"
        class="flex-1 flex items-center justify-center px-4 py-8"
    >
        <div class="w-full max-w-[460px]">
            {{-- Step labels --}}
            <div class="flex items-center justify-between px-1 mb-2">
                <span class="text-[10px] uppercase tracking-[0.18em] text-muted-foreground font-medium">
                    Step <span x-text="step"></span> of <span x-text="total"></span>
                </span>
                <span class="text-[10px] uppercase tracking-[0.18em] text-primary font-semibold">
                    <span x-text="progressPct"></span>% complete
                </span>
            </div>

            {{-- Progress bar --}}
            <div class="relative h-2 bg-[#E8E6E0] w-full rounded-full overflow-hidden shadow-inner mb-5">
                <div
                    :style="`width: ${progressPct}%`"
                    class="absolute inset-y-0 left-0 rounded-full bg-gradient-to-r from-primary via-primary to-[#A8C547] transition-all duration-700 ease-out"
                >
                    <div class="absolute inset-0 opacity-40 animate-shimmer bg-gradient-to-r from-transparent via-white to-transparent bg-[length:200%_100%]"></div>
                </div>
                <div class="absolute inset-0 flex">
                    @for ($i = 0; $i < $TOTAL_STEPS; $i++)
                        <div class="flex-1 relative">
                            @if ($i < $TOTAL_STEPS - 1)
                                <div class="absolute right-0 top-0 h-full w-px bg-background/80"></div>
                            @endif
                            <template x-if="step === {{ $i + 1 }}">
                                <div class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-1/2 w-2.5 h-2.5 rounded-full bg-primary ring-2 ring-background animate-pulse"></div>
                            </template>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Card --}}
            <div class="bg-card rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.09)] overflow-hidden">
                <div
                    :class="animating
                        ? (direction === 'forward' ? 'opacity-0 translate-x-4' : 'opacity-0 -translate-x-4')
                        : 'opacity-100 translate-x-0'"
                    class="px-5 sm:px-9 pt-7 sm:pt-10 pb-7 sm:pb-9 transition-all duration-200 ease-in-out"
                >
                    {{-- Step 1: Name --}}
                    <div x-show="step === 1">
                        <h1 class="font-['Playfair_Display'] italic text-[26px] leading-tight text-secondary text-center">
                            What is the name<br>of your project?
                        </h1>
                        <p class="text-[13px] text-muted-foreground text-center mt-3 leading-relaxed">
                            Give your renovation a distinct name<br>to easily identify it later.
                        </p>
                        <div class="mt-7 relative">
                            <input
                                type="text"
                                x-model="projectName"
                                @keydown.enter="next()"
                                placeholder="e.g., Renovasi Rumah Pak Budi"
                                class="w-full bg-[#F4F3EF] border border-[#E0DFDA] rounded-xl px-4 py-3.5 text-sm text-card-foreground placeholder:text-[#BEBAB3] focus:outline-none focus:border-primary focus:bg-white transition-colors"
                            />
                        </div>
                    </div>

                    {{-- Step 2: Location --}}
                    <div x-show="step === 2">
                        <h1 class="font-['Playfair_Display'] italic text-[26px] leading-tight text-secondary text-center">
                            Where is your project<br>located?
                        </h1>
                        <p class="text-[13px] text-muted-foreground text-center mt-3 leading-relaxed">
                            Select your city to get accurate local<br>material and labor rates.
                        </p>
                        <div class="mt-7 relative">
                            <x-lucide-map-pin class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                            <select
                                x-model="city"
                                class="w-full appearance-none bg-[#F4F3EF] border border-[#E0DFDA] rounded-xl pl-9 pr-10 py-3.5 text-sm text-card-foreground focus:outline-none focus:border-primary focus:bg-white transition-colors"
                            >
                                <option value="" disabled>Pilih kota / kabupaten…</option>
                                @foreach ($cities as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                            <x-lucide-chevron-down class="w-4 h-4 absolute right-3.5 top-1/2 -translate-y-1/2 text-primary pointer-events-none" />
                        </div>

                        {{-- Indonesia Map (preserved exactly) --}}
                        <div class="mt-5 rounded-xl overflow-hidden bg-[#1a2535] relative w-full" style="height: 130px">
                            <div class="absolute top-2.5 right-3 text-[9px] uppercase tracking-[0.18em] text-[#7fa8c0] font-medium">
                                Region: Southeast Asia
                            </div>
                            <svg viewBox="0 0 420 120" class="w-full h-full" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M30 68 L38 52 L55 44 L74 40 L88 36 L98 40 L104 50 L100 60 L90 65 L78 70 L62 74 L44 76 Z" fill="#2e4a5e" stroke="#4a7a96" stroke-width="0.8"/>
                                <path d="M108 72 L126 66 L148 62 L170 60 L192 61 L212 64 L228 68 L234 74 L218 78 L196 80 L174 79 L152 76 L130 78 Z" fill="#2e4a5e" stroke="#4a7a96" stroke-width="0.8"/>
                                <path d="M160 28 L178 20 L200 16 L224 18 L244 24 L256 34 L258 46 L254 58 L240 64 L220 68 L200 66 L180 60 L166 50 L158 40 Z" fill="#2e4a5e" stroke="#4a7a96" stroke-width="0.8"/>
                                <path d="M268 28 L276 22 L284 24 L290 32 L286 44 L278 52 L274 62 L278 70 L284 76 L278 80 L270 74 L264 64 L260 52 L262 40 Z" fill="#2e4a5e" stroke="#4a7a96" stroke-width="0.8"/>
                                <path d="M284 40 L296 34 L308 30 L318 34 L316 42 L304 46 L292 46 Z" fill="#2e4a5e" stroke="#4a7a96" stroke-width="0.8"/>
                                <path d="M330 30 L348 22 L370 20 L392 24 L406 34 L410 48 L404 60 L388 68 L366 72 L346 68 L330 58 L324 46 Z" fill="#2e4a5e" stroke="#4a7a96" stroke-width="0.8"/>
                                <ellipse cx="242" cy="72" rx="7" ry="4" fill="#2e4a5e" stroke="#4a7a96" stroke-width="0.7"/>
                                <ellipse cx="256" cy="74" rx="5" ry="3" fill="#2e4a5e" stroke="#4a7a96" stroke-width="0.7"/>
                                <ellipse cx="268" cy="76" rx="4" ry="3" fill="#2e4a5e" stroke="#4a7a96" stroke-width="0.7"/>
                                <ellipse cx="308" cy="54" rx="5" ry="6" fill="#2e4a5e" stroke="#4a7a96" stroke-width="0.7"/>
                                <ellipse cx="318" cy="48" rx="4" ry="4" fill="#2e4a5e" stroke="#4a7a96" stroke-width="0.7"/>
                            </svg>
                            <div class="absolute bottom-2.5 left-1/2 -translate-x-1/2 flex gap-1.5">
                                <div class="w-1.5 h-1.5 rounded-full bg-primary"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-[#3a5068]"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3: Renovation Type --}}
                    <div x-show="step === 3">
                        <h1 class="font-['Playfair_Display'] italic text-[26px] leading-tight text-secondary text-center">
                            What type of renovation<br>are you planning?
                        </h1>
                        <p class="text-[13px] text-muted-foreground text-center mt-3 leading-relaxed">
                            Select the primary focus<br>of your project.
                        </p>
                        <div class="mt-7 relative">
                            <x-lucide-chevron-down class="w-4 h-4 absolute right-3.5 top-1/2 -translate-y-1/2 text-primary pointer-events-none" />
                            <select
                                x-model="renovationType"
                                class="w-full appearance-none bg-[#F4F3EF] border border-[#E0DFDA] rounded-xl px-4 pr-10 py-3.5 text-sm text-card-foreground focus:outline-none focus:border-primary focus:bg-white transition-colors"
                            >
                                <option value="" disabled>Pilih tipe renovasi…</option>
                                @foreach ($renovationTypes as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>

                        <template x-if="renovationType">
                            <div class="mt-5 flex flex-wrap gap-2">
                                @foreach (['Material', 'Tenaga Kerja', 'Peralatan'] as $tag)
                                    <span class="text-[11px] uppercase tracking-wide bg-[#EAF0D4] text-secondary rounded-full px-3 py-1 font-medium">
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        </template>
                    </div>

                    {{-- Step 4: Quality --}}
                    <div x-show="step === 4">
                        <h1 class="font-['Playfair_Display'] italic text-[26px] leading-tight text-secondary text-center">
                            What material quality<br>do you envision?
                        </h1>
                        <p class="text-[13px] text-muted-foreground text-center mt-3 leading-relaxed">
                            This helps us generate a more accurate<br>baseline estimate for your project.
                        </p>

                        <div class="mt-8 flex flex-col gap-3">
                            @foreach ([
                                ['q' => 'Ekonomi', 'desc' => 'Material standar lokal, hemat biaya'],
                                ['q' => 'Standar', 'desc' => 'Keseimbangan kualitas dan harga'],
                                ['q' => 'Premium', 'desc' => 'Material impor, kualitas tinggi'],
                            ] as $opt)
                                <button
                                    @click="quality = '{{ $opt['q'] }}'"
                                    :class="quality === '{{ $opt['q'] }}'
                                        ? 'bg-primary border-primary text-primary-foreground shadow-[0_2px_12px_rgba(139,160,35,0.28)]'
                                        : 'bg-[#F4F3EF] border-[#E0DFDA] text-card-foreground hover:border-primary/40'"
                                    class="w-full rounded-xl px-5 py-4 text-left border-[1.5px] transition-all"
                                >
                                    <p class="font-semibold text-sm">{{ $opt['q'] }}</p>
                                    <p :class="quality === '{{ $opt['q'] }}' ? 'text-primary-foreground/80' : 'text-muted-foreground'" class="text-[12px] mt-0.5">
                                        {{ $opt['desc'] }}
                                    </p>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Step 5: Budget --}}
                    <div x-show="step === 5">
                        <h1 class="font-['Playfair_Display'] italic text-[26px] leading-tight text-secondary text-center">
                            What is your<br>renovation budget?
                        </h1>
                        <p class="text-[13px] text-muted-foreground text-center mt-3 leading-relaxed">
                            This helps us flag if the estimate exceeds<br>your budget. You can skip this.
                        </p>

                        <div class="mt-7">
                            <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">
                                Anggaran Renovasi (Rp)
                            </label>
                            <div class="relative">
                                <x-lucide-wallet class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                                <span class="absolute left-9 top-1/2 -translate-y-1/2 text-sm text-muted-foreground pointer-events-none font-medium">Rp</span>
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    :value="budgetDisplay"
                                    @input="budgetDisplay = formatIDR($event.target.value)"
                                    @keydown.enter="next()"
                                    placeholder="e.g., 15.000.000"
                                    class="w-full bg-[#F4F3EF] border border-[#E0DFDA] rounded-xl pl-[68px] pr-4 py-3.5 text-sm text-card-foreground placeholder:text-[#BEBAB3] focus:outline-none focus:border-primary focus:bg-white transition-colors"
                                />
                            </div>
                            <template x-if="budgetValue > 0">
                                <p class="text-[11px] text-muted-foreground mt-2">
                                    ≈ Rp <span x-text="budgetDisplay"></span> — kami akan beri peringatan bila estimasi melebihi angka ini.
                                </p>
                            </template>
                        </div>
                    </div>

                    {{-- Action buttons --}}
                    <div class="mt-8">
                        <button
                            @click="next()"
                            :disabled="!canAdvance()"
                            :class="canAdvance()
                                ? 'bg-primary text-primary-foreground hover:opacity-90 hover:shadow-[0_6px_18px_rgba(139,160,35,0.4)]'
                                : 'bg-[#D4D2CC] text-[#9A9890] cursor-not-allowed shadow-none'"
                            class="w-full rounded-xl py-3.5 font-semibold text-sm flex items-center justify-center gap-2 transition-all shadow-[0_4px_14px_rgba(139,160,35,0.32)]"
                        >
                            <span x-text="step === total ? 'Lanjutkan' : 'Next'"></span>
                            <span class="text-base">→</span>
                        </button>

                        <button
                            x-show="step === 5"
                            @click="skip()"
                            class="w-full mt-3 rounded-xl py-3 text-sm font-medium text-[#838383] hover:text-card-foreground hover:bg-[#F4F3EF] transition-colors"
                        >
                            Skip for now
                        </button>

                        <button
                            @click="back()"
                            class="w-full mt-3 text-[13px] text-muted-foreground hover:text-card-foreground transition-colors text-center py-1"
                        >
                            <span x-text="step === 1 ? 'Cancel' : '← Back'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Minimal footer --}}
    <p class="text-center text-[10px] uppercase tracking-widest text-muted-foreground pb-5">
        RenovaSim · Editorial Workflow © 2025
    </p>
</x-layouts.app>
