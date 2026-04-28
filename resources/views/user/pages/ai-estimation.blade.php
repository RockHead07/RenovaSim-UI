{{-- pages.estimation-result — port of EstimationResult.tsx --}}
@php
    $result = config('renovasim.mock_estimate');
    $projectNameInput = request()->query('projectName');
    if ($projectNameInput) {
        $result['project_name'] = $projectNameInput;
    }
    $avg = (int) round(($result['total_range']['min'] + $result['total_range']['max']) / 2);
@endphp

<x-user::layouts.app title="RenovaSim — Estimation Result">
    @if ($result['mode'] === 'incomplete')
        <div class="flex-1 flex items-center justify-center px-4">
            <div class="max-w-md w-full bg-card rounded-2xl p-8 text-center shadow-[0_2px_8px_rgba(0,0,0,0.07)]">
                <div class="w-12 h-12 mx-auto rounded-full bg-muted flex items-center justify-center mb-4">
                    <x-lucide-info class="w-[22px] h-[22px] text-muted-foreground" />
                </div>
                <h2 class="font-['Playfair_Display'] italic text-xl text-card-foreground mb-2">Belum bisa menghitung estimasi</h2>
                <p class="font-['DM_Sans'] text-sm text-muted-foreground mb-6">Kami belum bisa menghitung estimasi.</p>
                <a href="/project-details" class="block w-full bg-primary text-primary-foreground rounded-lg py-3">
                    Kembali ke Wizard
                </a>
            </div>
        </div>
    @else
        <div class="flex-1 py-8 px-4">
            <div class="max-w-[860px] mx-auto">
                <div class="flex items-center gap-3 mb-6">
                    <button onclick="history.back()" class="text-card-foreground hover:opacity-70">
                        <x-lucide-arrow-left class="w-5 h-5" />
                    </button>
                    <div>
                        <p class="font-['DM_Sans'] font-semibold text-base text-card-foreground">Estimation Result</p>
                        <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground">AI-Powered Renovation Cost Analysis</p>
                    </div>
                </div>

                <div class="relative w-full h-[180px] rounded-2xl overflow-hidden bg-gradient-to-br from-secondary to-card-foreground mb-6">
                    <svg class="absolute inset-0 w-full h-full opacity-[0.08]" viewBox="0 0 860 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="60"  y="30" width="140" height="90" stroke="white" stroke-width="0.8" fill="none" />
                        <rect x="220" y="50" width="80"  height="70" stroke="white" stroke-width="0.8" fill="none" />
                        <rect x="400" y="40" width="120" height="80" stroke="white" stroke-width="0.8" fill="none" />
                    </svg>
                    <div class="absolute bottom-4 left-5">
                        <span class="inline-flex items-center gap-1.5 bg-black/40 text-white font-['DM_Sans'] text-sm rounded-full px-4 py-2">
                            ✦ {{ $result['project_name'] }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-6">
                        <x-user::components.estimation.cost-range-card :range="$result['total_range']" :confidence="$result['confidence']" />

                        <div id="assumptions-section">
                            <x-user::components.estimation.assumptions-card :assumptions="$result['assumptions']" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-6">
                        <x-user::components.estimation.breakdown-card :breakdown="$result['breakdown']" :totalRange="$result['total_range']" />

                        <div class="flex gap-2.5">
                            <a href="/project-details" class="flex-1 border-[1.5px] border-card-foreground text-card-foreground bg-card rounded-lg py-2.5 text-sm flex items-center justify-center gap-1.5 hover:bg-muted">
                                <x-lucide-pencil class="w-[14px] h-[14px]" /> Edit
                            </a>
                            <button onclick="location.reload()" class="flex-1 border-[1.5px] border-primary text-primary bg-card rounded-lg py-2.5 text-sm flex items-center justify-center gap-1.5 hover:bg-primary/5">
                                <x-lucide-rotate-ccw class="w-[14px] h-[14px]" /> Recalculate
                            </button>
                        </div>

                        <a href="/project-overview" class="w-full bg-primary text-primary-foreground font-['Playfair_Display'] italic text-base rounded-lg py-4 flex items-center justify-center gap-2 hover:opacity-90">
                            <x-lucide-save class="w-4 h-4" /> Save & Open Overview
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-user::layouts.app>
{{-- pages.ai-estimation — port of AIEstimation.tsx --}}
@php
    $renovationTypes = config('renovasim.renovation_types');
    $aiRenoTypes = [
        'Pengecatan', 'Renovasi Dapur', 'Renovasi Kamar Mandi',
        'Renovasi Total', 'Penambahan Ruangan', 'Lain-lain',
    ];
    $qualityLevels = ['Ekonomis', 'Standar', 'Premium'];
    $step1 = [
        'projectName'    => request()->query('projectName', 'Renovasi Rumah Pak Budi'),
        'city'           => request()->query('city', 'Jakarta'),
        'renovationType' => request()->query('renovationType', 'Pengecatan'),
        'quality'        => request()->query('quality', 'Standar'),
    ];
@endphp

<x-user::layouts.app title="RenovaSim — AI Estimation">
    <div
        x-data="{
            step1: @js($step1),
            mode: null,
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
                    window.location.href = '/estimation-result?' + params.toString();
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
                </div>

                {{-- Card Header --}}
                <div class="flex flex-col items-center mb-7">
                    <div class="w-11 h-11 rounded-[10px] bg-[#E8E6E0] flex items-center justify-center">
                        <x-lucide-sparkles class="w-5 h-5 text-card-foreground" />
                    </div>
                    <h1 class="font-['Playfair_Display'] italic text-[22px] sm:text-[24px] text-card-foreground mt-3 text-center">
                        <span x-text="mode === null ? 'Pilih Mode Estimasi' : 'RAI Estimation'"></span>
                    </h1>
                </div>

                {{-- MODE SELECTION VIEW --}}
                <div x-show="mode === null" class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                    <button @click="mode = 'quick'" class="group rounded-xl border-[1.5px] border-[#E0DFDA] px-3.5 py-3 transition-all flex items-center gap-3 sm:flex-col sm:items-start">
                        <div class="w-9 h-9 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                            <x-lucide-zap class="w-4 h-4" />
                        </div>
                        <div class="flex-col leading-tight min-w-0">
                            <span class="font-semibold text-[13px] text-card-foreground">Quick Estimate</span>
                            <span class="text-[11px] text-muted-foreground mt-0.5">Fast & simple</span>
                        </div>
                    </button>

                    <button @click="mode = 'detailed'" class="group rounded-xl border-[1.5px] border-primary bg-primary/5 px-3.5 py-3 transition-all flex items-center gap-3 sm:flex-col sm:items-start">
                        <span class="bg-primary text-primary-foreground text-[9px] uppercase tracking-wider font-semibold rounded-full px-2 py-0.5">Recommended</span>
                        <div class="w-9 h-9 rounded-lg bg-primary text-primary-foreground flex items-center justify-center">
                            <x-lucide-clipboard-list class="w-4 h-4" />
                        </div>
                        <div class="flex-col leading-tight min-w-0">
                            <span class="font-semibold text-[13px] text-card-foreground">Detailed Estimate</span>
                            <span class="text-[11px] text-muted-foreground mt-0.5">More accurate</span>
                        </div>
                    </button>

                    <button @click="mode = 'ai'" class="group rounded-xl border-[1.5px] border-[#E0DFDA] px-3.5 py-3 transition-all flex items-center gap-3 sm:flex-col sm:items-start">
                        <div class="w-9 h-9 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                            <x-lucide-messages-square class="w-4 h-4" />
                        </div>
                        <div class="flex-col leading-tight min-w-0">
                            <span class="font-semibold text-[13px] text-card-foreground">AI Assistant</span>
                            <span class="text-[11px] text-muted-foreground mt-0.5">Describe freely</span>
                        </div>
                    </button>
                </div>

                {{-- FORM VIEW --}}
                <div x-show="mode !== null" x-cloak>
                    <div class="flex items-center justify-between mb-5">
                        <button @click="mode = null; loading = false" class="inline-flex items-center gap-1.5 text-[12px] text-muted-foreground">
                            <x-lucide-arrow-left class="w-[13px] h-[13px]" /> Pilih mode lain
                        </button>
                    </div>

                    <template x-if="mode === 'quick' || mode === 'detailed'">
                        <div class="flex flex-col gap-5">
                            <div>
                                <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Luas Area</label>
                                <input type="number" x-model="area" placeholder="e.g., 45" class="w-full rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3 text-sm text-card-foreground focus:outline-none focus:border-primary bg-transparent" />
                            </div>

                            <template x-if="mode === 'detailed'">
                                <div class="flex flex-col gap-5">
                                    <div>
                                        <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2">Catatan</label>
                                        <textarea x-model="notes" placeholder="Misal: ganti kusen, perbaiki plafon..." class="w-full min-h-[100px] rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3 text-sm text-card-foreground focus:outline-none focus:border-primary bg-transparent resize-none"></textarea>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="mode === 'ai'">
                        <div>
                            <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2">Ceritakan Rencana Renovasimu</label>
                            <textarea x-model="aiPrompt" placeholder="Saya ingin merenovasi dapur..." class="w-full min-h-[180px] rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3 text-sm text-card-foreground focus:outline-none focus:border-primary bg-transparent resize-none"></textarea>
                            <p class="text-[11px] text-muted-foreground mt-1.5">ℹ Tulis minimal 10 karakter.</p>
                        </div>
                    </template>

                    <button @click="goResults()" :disabled="loading || !canSubmit" class="w-full bg-primary text-primary-foreground rounded-lg py-3.5 font-['Playfair_Display'] italic text-base flex items-center justify-center gap-2 mt-7 disabled:opacity-60">
                        <x-lucide-sparkles class="w-4 h-4" />
                        <span>Generate Estimate</span>
                    </button>

                    <div x-show="loading" x-transition class="mt-6 flex flex-col items-center gap-3">
                        <div class="h-1.5 w-full rounded-full bg-muted">
                            <div :style="`width: ${progress}%`" class="h-full bg-primary transition-all"></div>
                        </div>
                        <p class="text-sm italic text-muted-foreground">AI sedang menghitung estimasimu...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-user.layouts.app>
