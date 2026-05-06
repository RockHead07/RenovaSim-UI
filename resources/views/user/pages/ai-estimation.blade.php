{{-- pages.ai-estimation — port of AIEstimation.tsx
     NOTE: this page renders its own AppNav inside DashboardLayout in React,
     producing the existing "double header" look. We preserve that exactly
     by extending layouts.app (which provides AppNav + AppFooter). --}}
@php
    $renovationTypes = config('renovasim.renovation_types');
    // Per the React source these are the AI-estimation specific values
    $aiRenoTypes = [
        'Pengecatan', 'Renovasi Dapur', 'Renovasi Kamar Mandi',
        'Renovasi Total', 'Penambahan Ruangan', 'Lain-lain',
    ];
    $qualityLevels = ['Ekonomis', 'Standar', 'Premium'];

    // Read step1 inputs from query string (formerly React Router location.state)
    $step1 = [
        'projectName'    => request()->query('projectName', 'Renovasi Rumah Pak Budi'),
        'city'           => request()->query('city', 'Jakarta'),
        'renovationType' => request()->query('renovationType', 'Pengecatan'),
        'quality'        => request()->query('quality', 'Standar'),
    ];
@endphp

<x-user::layouts.app title="RenovaSim — AI Estimation" :hideFooter="false">
    <div
        x-data="{
            step1: @js($step1),
            mode: null,           // 'quick' | 'detailed' | 'ai' | null
            area: '',
            unit: 'm²',
            renovationType: @js($step1['renovationType']),
            quality: @js($step1['quality']),
            notes: '',
            aiPrompt: '',
            loading: false,
            progress: 0,
            aiWriting: false,
            get canSubmit() {
                if (this.mode === 'quick')    return this.area.trim().length > 0;
                if (this.mode === 'detailed') return this.area.trim().length > 0;
                if (this.mode === 'ai')       return this.aiPrompt.trim().length >= 10;
                return false;
            },
            goResults() {
                this.loading = true;
                this.progress = 0;
                const interval = setInterval(() => {
                    this.progress = this.progress >= 100 ? 100 : this.progress + 5;
                }, 90);
                setTimeout(() => {
                    clearInterval(interval);
                    this.progress = 100;
                    this.loading = false;
                    const params = new URLSearchParams({
                        projectName: this.step1.projectName,
                        city: this.step1.city,
                        renovationType: this.renovationType,
                        quality: this.quality,
                        area: this.area,
                        unit: this.unit,
                        description: this.mode === 'ai' ? this.aiPrompt : this.notes,
                        mode: this.mode || '',
                    });
                    window.location.href = '/user/estimation-result?' + params.toString();
                }, 2000);
            },
            generateAiPrompt() {
                this.aiWriting = true;
                setTimeout(() => {
                    this.aiPrompt = 'Saya ingin merenovasi dapur berukuran sekitar 12 m². Ganti seluruh keramik lantai, perbarui meja kerja dengan granit, cat ulang dinding, dan pasang exhaust fan baru. Kualitas standar, fokus pada kerapian dan daya tahan.';
                    this.aiWriting = false;
                }, 1100);
            },
        }"
        class="flex-1 flex flex-col"
    >
        {{-- Step Indicator --}}
        <div class="flex flex-col items-center mt-7 px-4">
            <div class="relative flex gap-[60px]">
                <div class="absolute top-5 left-5 right-5 h-[1.5px] bg-primary"></div>
                <div class="flex flex-col items-center relative z-10">
                    <div class="w-10 h-10 rounded-full border-[1.5px] border-primary bg-background flex items-center justify-center">
                        <x-lucide-check class="w-[18px] h-[18px] text-primary" />
                    </div>
                    <span class="text-[10px] uppercase tracking-widest text-muted-foreground mt-1.5">Details</span>
                </div>
                <div class="flex flex-col items-center relative z-10">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-primary-foreground font-semibold text-sm">2</div>
                    <span class="text-[10px] uppercase tracking-widest text-card-foreground mt-1.5">AI Estimation</span>
                </div>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="flex-1 flex justify-center px-4 mt-6">
            <div class="w-full max-w-[640px] bg-card rounded-2xl p-5 sm:p-9 shadow-[0_2px_8px_rgba(0,0,0,0.07)]">
                {{-- Summary Bar --}}
                <div class="bg-[#F5F3EE] rounded-lg px-4 py-2.5 flex flex-wrap items-center gap-x-4 gap-y-2 mb-7">
                    <span class="text-xs text-secondary flex items-center gap-1.5">
                        <x-lucide-home class="w-[13px] h-[13px] text-primary" /> <span x-text="step1.projectName"></span>
                    </span>
                    <span class="text-xs text-secondary flex items-center gap-1.5">
                        <x-lucide-map-pin class="w-[13px] h-[13px] text-primary" /> <span x-text="step1.city"></span>
                    </span>
                    <span class="text-xs text-secondary flex items-center gap-1.5">
                        <x-lucide-hammer class="w-[13px] h-[13px] text-primary" /> <span x-text="renovationType"></span>
                    </span>
                    <span class="text-xs text-secondary flex items-center gap-1.5">
                        <x-lucide-gem class="w-[13px] h-[13px] text-primary" /> <span x-text="quality"></span>
                    </span>
                    <a href="/user/project-details" class="ml-auto" aria-label="Edit project details">
                        <x-lucide-pencil class="w-[14px] h-[14px] text-muted-foreground hover:text-card-foreground transition-colors" />
                    </a>
                </div>

                {{-- Card Header --}}
                <div class="flex flex-col items-center mb-7">
                    <div class="w-11 h-11 rounded-[10px] bg-[#E8E6E0] flex items-center justify-center">
                        <x-lucide-sparkles class="w-5 h-5 text-card-foreground" />
                    </div>
                    <h1 class="font-['Playfair_Display'] italic text-[22px] sm:text-[24px] text-card-foreground mt-3 text-center">
                        <span x-text="mode === null ? 'Pilih Mode Estimasi' : 'RAI Estimation'"></span>
                    </h1>
                    <p class="text-[13px] text-muted-foreground mt-1 text-center">
                        <span x-text="mode === null
                            ? 'Pilih cara tercepat atau paling akurat sesuai kebutuhanmu'
                            : 'Lengkapi detail di bawah ini lalu jalankan RAI.'"></span>
                    </p>
                </div>

                {{-- MODE SELECTION VIEW --}}
                <div x-show="mode === null" class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 animate-fade-in">
                    @foreach ([
                        ['id' => 'quick',    'icon' => 'zap',             'title' => 'Quick Estimate',    'desc' => 'Fast & simple',   'recommended' => false],
                        ['id' => 'detailed', 'icon' => 'clipboard-list',  'title' => 'Detailed Estimate', 'desc' => 'More accurate',   'recommended' => true],
                        ['id' => 'ai',       'icon' => 'messages-square', 'title' => 'AI Assistant',      'desc' => 'Describe freely', 'recommended' => false],
                    ] as $card)
                        <button
                            @click="mode = '{{ $card['id'] }}'"
                            @class([
                                'group relative text-left rounded-xl border-[1.5px] px-3.5 py-3 transition-all duration-200 flex items-center gap-3 sm:flex-col sm:items-start sm:gap-2',
                                'border-primary bg-primary/5 hover:bg-primary/10 hover:shadow-[0_4px_12px_rgba(122,157,52,0.15)]' => $card['recommended'],
                                'border-[#E0DFDA] bg-card hover:border-primary hover:bg-primary/5'                                 => ! $card['recommended'],
                            ])
                        >
                            @if ($card['recommended'])
                                <span class="absolute -top-2 right-3 bg-primary text-primary-foreground text-[9px] uppercase tracking-wider font-semibold rounded-full px-2 py-0.5 shadow-sm">
                                    Recommended
                                </span>
                            @endif
                            <div @class([
                                'w-9 h-9 rounded-lg flex items-center justify-center shrink-0 transition-colors',
                                'bg-primary text-primary-foreground'                                              => $card['recommended'],
                                'bg-primary/10 text-primary group-hover:bg-primary group-hover:text-primary-foreground' => ! $card['recommended'],
                            ])>
                                <x-dynamic-component :component="'lucide-' . $card['icon']" class="w-4 h-4" />
                            </div>
                            <div class="flex flex-col leading-tight min-w-0">
                                <span class="font-semibold text-[13px] text-card-foreground">{{ $card['title'] }}</span>
                                <span class="text-[11px] text-muted-foreground mt-0.5">{{ $card['desc'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>

                {{-- FORM VIEW --}}
                <div x-show="mode !== null" x-cloak class="animate-fade-in">
                    {{-- Mode pill + back --}}
                    <div class="flex items-center justify-between mb-5">
                        <button
                            @click="mode = null; loading = false"
                            class="inline-flex items-center gap-1.5 text-[12px] text-muted-foreground hover:text-card-foreground transition-colors"
                        >
                            <x-lucide-arrow-left class="w-[13px] h-[13px]" /> Pilih mode lain
                        </button>
                        <span class="text-[11px] uppercase tracking-wider bg-primary/10 text-primary rounded-full px-2.5 py-1 font-medium">
                            <span x-show="mode === 'quick'">Quick Estimate</span>
                            <span x-show="mode === 'detailed'">Detailed Estimate</span>
                            <span x-show="mode === 'ai'">AI Assistant</span>
                        </span>
                    </div>

                    {{-- Shared AreaInput component (used by quick & detailed) --}}
                    <template x-if="mode === 'quick' || mode === 'detailed'">
                        <div class="flex flex-col gap-5">
                            {{-- Area input --}}
                            <div>
                                <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Luas Area</label>
                                <div class="flex gap-2">
                                    <input
                                        type="number"
                                        x-model="area"
                                        placeholder="e.g., 45"
                                        class="flex-1 min-w-0 rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3 text-sm text-card-foreground placeholder:text-[#C0BFBA] focus:outline-none focus:border-primary bg-transparent"
                                    />
                                    <div class="flex rounded-lg overflow-hidden border-[1.5px] border-[#E0DFDA] shrink-0">
                                        <template x-for="u in ['m²', 'sqft']" :key="u">
                                            <button
                                                @click="unit = u"
                                                :class="unit === u ? 'bg-primary text-primary-foreground' : 'bg-[#EDECEA] text-muted-foreground'"
                                                class="px-3.5 py-3 text-sm font-medium transition-colors"
                                                x-text="u"
                                            ></button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Renovation type select --}}
                            <div>
                                <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Tipe Renovasi</label>
                                <select
                                    x-model="renovationType"
                                    class="w-full rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3 text-sm text-card-foreground focus:outline-none focus:border-primary bg-transparent appearance-none pr-9"
                                    style="background-image:url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23999%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22/></svg>'); background-repeat:no-repeat; background-position:right 14px center;"
                                >
                                    @foreach ($aiRenoTypes as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Detailed-only fields --}}
                            <template x-if="mode === 'detailed'">
                                <div class="flex flex-col gap-5">
                                    <div>
                                        <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Kualitas Material</label>
                                        <div class="grid grid-cols-3 gap-2">
                                            @foreach ($qualityLevels as $q)
                                                <button
                                                    @click="quality = '{{ $q }}'"
                                                    :class="quality === '{{ $q }}' ? 'border-primary bg-primary/10 text-primary' : 'border-[#E0DFDA] text-muted-foreground hover:border-primary/40'"
                                                    class="rounded-lg border-[1.5px] px-3 py-2.5 text-sm font-medium transition-colors"
                                                >{{ $q }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Catatan Tambahan</label>
                                        <textarea
                                            x-model="notes"
                                            placeholder="Misal: ganti kusen pintu, perbaiki plafon yang bocor, dst."
                                            class="w-full min-h-[100px] rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3 text-sm text-card-foreground placeholder:text-[#C0BFBA] focus:outline-none focus:border-primary bg-transparent resize-none"
                                        ></textarea>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- AI ASSISTANT MODE --}}
                    <template x-if="mode === 'ai'">
                        <div>
                            <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Ceritakan Rencana Renovasimu</label>
                            <div class="relative">
                                <textarea
                                    x-model="aiPrompt"
                                    placeholder="Saya ingin merenovasi dapur kecil, ganti keramik dan cat ulang dindingnya..."
                                    class="w-full min-h-[180px] rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3.5 pr-12 text-sm text-card-foreground placeholder:text-[#C0BFBA] placeholder:italic focus:outline-none focus:border-primary bg-transparent resize-none"
                                ></textarea>
                                <button
                                    type="button"
                                    :disabled="aiWriting"
                                    @click="generateAiPrompt()"
                                    title="Bantu tulis dengan AI"
                                    aria-label="Bantu tulis dengan AI"
                                    class="absolute top-2.5 right-2.5 w-8 h-8 rounded-md bg-primary/10 hover:bg-primary/20 text-primary flex items-center justify-center transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
                                >
                                    <template x-if="aiWriting"><x-lucide-loader-2 class="w-4 h-4 animate-spin" /></template>
                                    <template x-if="!aiWriting"><x-lucide-sparkles class="w-4 h-4" /></template>
                                </button>
                            </div>
                            <p class="text-[11px] text-muted-foreground mt-1.5">
                                ℹ Tulis minimal 10 karakter. Semakin detail, semakin akurat estimasinya.
                            </p>
                        </div>
                    </template>

                    {{-- CTA --}}
                    <button
                        @click="goResults()"
                        :disabled="loading || !canSubmit"
                        class="w-full bg-primary text-primary-foreground rounded-lg py-3.5 font-['Playfair_Display'] italic text-base hover:bg-primary/90 transition-colors flex items-center justify-center gap-2 mt-7 disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <x-lucide-sparkles class="w-4 h-4" />
                        <span x-show="mode === 'quick'">Generate Estimate</span>
                        <span x-show="mode === 'detailed'">Generate Detailed Estimate</span>
                        <span x-show="mode === 'ai'">Let AI Analyze</span>
                    </button>
                    <p class="text-[11px] text-muted-foreground text-center mt-2">Estimasi dihasilkan dalam beberapa detik</p>

                    {{-- Loading State --}}
                    <div x-show="loading" x-transition class="mt-6 flex flex-col items-center gap-3">
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-muted">
                            <div :style="`width: ${progress}%`" class="h-full bg-primary transition-all"></div>
                        </div>
                        <p class="text-sm italic text-muted-foreground">
                            <span x-text="mode === 'ai' ? 'AI sedang menganalisa deskripsimu...' : 'AI sedang menghitung estimasimu...'"></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-user::layouts.app>
