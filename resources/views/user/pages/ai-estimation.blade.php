{{-- pages.ai-estimation — Mode selection → Wizard / AI form
     Connected to FastAPI backend via EstimationController --}}
@php
    $renovationTypes = $renovationTypes ?? config('renovasim.renovation_types');
    $qualities       = $qualities ?? config('renovasim.qualities');
    $cities          = $cities ?? config('renovasim.cities');
    $jobTypeMap      = $jobTypeMap ?? config('renovasim.job_type_id');
    $renovationTypes = config('renovasim.renovation_types');
    // Per the React source these are the AI-estimation specific values
    $aiRenoTypes = [
        'Pengecatan', 'Renovasi Dapur', 'Renovasi Kamar Mandi',
        'Renovasi Total', 'Penambahan Ruangan', 'Lain-lain',
    ];
    $qualityLevels = ['Ekonomis', 'Standar', 'Premium'];


@endphp

<x-user::layouts.app title="RenovaSim — AI Estimation" :hideFooter="false">
    <div
        x-data="{
            mode: null,
            submitting: false,

            // Wizard fields
            jobType: '',
            area: '',
            location: '',
            quality: 'standar',
            budgetDisplay: '',
            description: '',
            get budgetValue() {
                const d = (this.budgetDisplay || '').replace(/\D/g, '');
                return d ? parseInt(d, 10) : 0;
            },
            formatIDR(raw) {
                const digits = (raw || '').replace(/\D/g, '');
                if (!digits) return '';
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            },

            // AI fields
            aiLocation: '',
            aiBudgetDisplay: '',
            get aiBudgetValue() {
                const d = (this.aiBudgetDisplay || '').replace(/\D/g, '');
                return d ? parseInt(d, 10) : 0;
            },

            // Validation
            get wizardValid() { return this.jobType.length > 0 && parseFloat(this.area) >= 1; },
            get aiValid() { return this.description.trim().length >= 10; },
        }"
        class="flex-1 flex flex-col"
    >
        {{-- Step Indicator (shown when a mode is selected) --}}
        <div x-show="mode !== null" x-transition class="flex flex-col items-center mt-7 px-4">
            <div class="relative flex gap-[60px]">
                <div class="absolute top-5 left-5 right-5 h-[1.5px] bg-primary"></div>
                <div class="flex flex-col items-center relative z-10">
                    <div class="w-10 h-10 rounded-full border-[1.5px] border-primary bg-background flex items-center justify-center">
                        <x-lucide-check class="w-[18px] h-[18px] text-primary" />
                    </div>
                    <span class="text-[10px] uppercase tracking-widest text-muted-foreground mt-1.5">Pilih Mode</span>
                </div>
                <div class="flex flex-col items-center relative z-10">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-primary-foreground font-semibold text-sm">2</div>
                    <span class="text-[10px] uppercase tracking-widest text-card-foreground mt-1.5">Estimasi</span>
                </div>
            </div>
        </div>

        {{-- Session Error --}}
        @if(session('error'))
            <div class="max-w-[640px] mx-auto w-full px-4 mt-4">
                <div class="flex items-start gap-2.5 bg-[hsl(0,95%,96%)] border-[1.5px] border-[hsl(0,95%,40%)] rounded-xl px-4 py-3 animate-fade-in">
                    <x-lucide-alert-triangle class="w-[18px] h-[18px] text-[hsl(0,95%,40%)] shrink-0 mt-0.5" />
                    <p class="font-['DM_Sans'] text-[13px] text-card-foreground leading-relaxed">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="max-w-[640px] mx-auto w-full px-4 mt-4">
                <div class="flex items-start gap-2.5 bg-[hsl(0,95%,96%)] border-[1.5px] border-[hsl(0,95%,40%)] rounded-xl px-4 py-3 animate-fade-in">
                    <x-lucide-alert-triangle class="w-[18px] h-[18px] text-[hsl(0,95%,40%)] shrink-0 mt-0.5" />
                    <div>
                        @foreach($errors->all() as $error)
                            <p class="font-['DM_Sans'] text-[13px] text-card-foreground leading-relaxed">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Main Card --}}
        <div class="flex-1 flex justify-center px-4 mt-6">
            <div class="w-full max-w-[640px] bg-card rounded-2xl p-5 sm:p-9 shadow-[0_2px_8px_rgba(0,0,0,0.07)]">

                {{-- ============================================ --}}
                {{-- SCREEN A: MODE SELECTION                     --}}
                {{-- ============================================ --}}
                <div x-show="mode === null" class="animate-fade-in">
                    <div class="flex flex-col items-center mb-7">
                        <div class="w-11 h-11 rounded-[10px] bg-[#E8E6E0] flex items-center justify-center">
                            <x-lucide-sparkles class="w-5 h-5 text-card-foreground" />
                        </div>
                        <h1 class="font-['Playfair_Display'] italic text-[22px] sm:text-[24px] text-card-foreground mt-3 text-center">
                            Pilih Mode Estimasi
                        </h1>
                        <p class="text-[13px] text-muted-foreground mt-1 text-center">
                            Pilih cara tercepat atau paling akurat sesuai kebutuhanmu
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Card: Pilih Sendiri --}}
                        <button
                            @click="mode = 'wizard'"
                            class="group relative text-left rounded-xl border-[1.5px] border-[#E0DFDA] bg-card hover:border-primary hover:bg-primary/5 px-4 py-5 transition-all duration-200 flex flex-col items-start gap-2.5"
                        >
                            <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary group-hover:bg-primary group-hover:text-primary-foreground flex items-center justify-center transition-colors">
                                <x-lucide-clipboard-list class="w-5 h-5" />
                            </div>
                            <div class="flex flex-col leading-tight">
                                <span class="font-semibold text-[14px] text-card-foreground">Pilih Sendiri</span>
                                <span class="text-[12px] text-muted-foreground mt-1">Isi form langkah demi langkah — pilih jenis renovasi, luas area, dan budget</span>
                            </div>
                        </button>

                        {{-- Card: Ceritakan ke AI --}}
                        <button
                            @click="mode = 'ai'"
                            class="group relative text-left rounded-xl border-[1.5px] border-primary bg-primary/5 hover:bg-primary/10 hover:shadow-[0_4px_12px_rgba(122,157,52,0.15)] px-4 py-5 transition-all duration-200 flex flex-col items-start gap-2.5"
                        >
                            <span class="absolute -top-2 right-3 bg-primary text-primary-foreground text-[9px] uppercase tracking-wider font-semibold rounded-full px-2 py-0.5 shadow-sm">
                                Disarankan
                            </span>
                            <div class="w-10 h-10 rounded-lg bg-primary text-primary-foreground flex items-center justify-center transition-colors">
                                <x-lucide-messages-square class="w-5 h-5" />
                            </div>
                            <div class="flex flex-col leading-tight">
                                <span class="font-semibold text-[14px] text-card-foreground">Ceritakan ke AI</span>
                                <span class="text-[12px] text-muted-foreground mt-1">Ketik bebas rencana renovasimu — AI yang proses dan hitung estimasinya</span>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- SCREEN B: WIZARD MODE                        --}}
                {{-- ============================================ --}}
                <div x-show="mode === 'wizard'" x-cloak class="animate-fade-in">
                    {{-- Mode pill + back --}}
                    <div class="flex items-center justify-between mb-5">
                        <button
                            @click="mode = null; submitting = false"
                            class="inline-flex items-center gap-1.5 text-[12px] text-muted-foreground hover:text-card-foreground transition-colors"
                        >
                            <x-lucide-arrow-left class="w-[13px] h-[13px]" /> Pilih mode lain
                        </button>
                        <span class="text-[11px] uppercase tracking-wider bg-primary/10 text-primary rounded-full px-2.5 py-1 font-medium">
                            Pilih Sendiri
                        </span>
                    </div>

                    {{-- Card Header --}}
                    <div class="flex flex-col items-center mb-7">
                        <div class="w-11 h-11 rounded-[10px] bg-[#E8E6E0] flex items-center justify-center">
                            <x-lucide-clipboard-list class="w-5 h-5 text-card-foreground" />
                        </div>
                        <h1 class="font-['Playfair_Display'] italic text-[22px] sm:text-[24px] text-card-foreground mt-3 text-center">
                            Detail Renovasi
                        </h1>
                        <p class="text-[13px] text-muted-foreground mt-1 text-center">
                            Lengkapi form di bawah, lalu klik estimasi.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('user.estimation.submitWizard') }}" @submit="submitting = true">
                        @csrf
                        <div class="flex flex-col gap-5">
                            {{-- Jenis Renovasi (job_type) — REQUIRED --}}
                            <div>
                                <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Jenis Renovasi <span class="text-[hsl(0,95%,40%)]">*</span></label>
                                <select
                                    name="job_type"
                                    x-model="jobType"
                                    required
                                    class="w-full rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3 text-sm text-card-foreground focus:outline-none focus:border-primary bg-transparent appearance-none pr-9"
                                    style="background-image:url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23999%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22/></svg>'); background-repeat:no-repeat; background-position:right 14px center;"
                                >
                                    <option value="" disabled>Pilih jenis renovasi…</option>
                                    @foreach ($jobTypeMap as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Luas Area — REQUIRED --}}
                            <div>
                                <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Luas Area (m²) <span class="text-[hsl(0,95%,40%)]">*</span></label>
                                <input
                                    type="number"
                                    name="area"
                                    x-model="area"
                                    min="1"
                                    step="0.1"
                                    required
                                    placeholder="misal: 20"
                                    class="w-full rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3 text-sm text-card-foreground placeholder:text-[#C0BFBA] focus:outline-none focus:border-primary bg-transparent"
                                />
                            </div>

                            {{-- Lokasi (optional) --}}
                            <div>
                                <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Lokasi <span class="text-[10px] text-muted-foreground/60">(opsional)</span></label>
                                <select
                                    name="location"
                                    x-model="location"
                                    class="w-full rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3 text-sm text-card-foreground focus:outline-none focus:border-primary bg-transparent appearance-none pr-9"
                                    style="background-image:url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23999%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22/></svg>'); background-repeat:no-repeat; background-position:right 14px center;"
                                >
                                    <option value="">Pilih kota…</option>
                                    @foreach ($cities as $c)
                                        <option value="{{ strtolower($c) }}">{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Kualitas Material --}}
                            <div>
                                <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Kualitas Material</label>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach (['ekonomi' => 'Ekonomi', 'standar' => 'Standar', 'premium' => 'Premium'] as $val => $label)
                                        <button
                                            type="button"
                                            @click="quality = '{{ $val }}'"
                                            :class="quality === '{{ $val }}' ? 'border-primary bg-primary/10 text-primary' : 'border-[#E0DFDA] text-muted-foreground hover:border-primary/40'"
                                            class="rounded-lg border-[1.5px] px-3 py-2.5 text-sm font-medium transition-colors"
                                        >{{ $label }}</button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="quality" :value="quality" />
                            </div>

                            {{-- Budget Rp (optional) --}}
                            <div>
                                <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Budget Rp <span class="text-[10px] text-muted-foreground/60">(opsional)</span></label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-muted-foreground pointer-events-none font-medium">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        :value="budgetDisplay"
                                        @input="budgetDisplay = formatIDR($event.target.value)"
                                        placeholder="misal: 15.000.000"
                                        class="w-full rounded-lg border-[1.5px] border-[#E0DFDA] pl-10 pr-3.5 py-3 text-sm text-card-foreground placeholder:text-[#C0BFBA] focus:outline-none focus:border-primary bg-transparent"
                                    />
                                    <input type="hidden" name="budget" :value="budgetValue" />
                                </div>
                            </div>

                            {{-- Catatan (optional) --}}
                            <div>
                                <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Catatan Tambahan <span class="text-[10px] text-muted-foreground/60">(opsional)</span></label>
                                <textarea
                                    name="description"
                                    x-model="description"
                                    placeholder="Misal: ganti kusen pintu, perbaiki plafon yang bocor, dst."
                                    class="w-full min-h-[80px] rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3 text-sm text-card-foreground placeholder:text-[#C0BFBA] focus:outline-none focus:border-primary bg-transparent resize-none"
                                ></textarea>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <button
                            type="submit"
                            :disabled="submitting || !wizardValid"
                            class="w-full bg-primary text-primary-foreground rounded-lg py-3.5 font-['Playfair_Display'] italic text-base hover:bg-primary/90 transition-colors flex items-center justify-center gap-2 mt-7 disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <template x-if="submitting">
                                <x-lucide-loader-2 class="w-4 h-4 animate-spin" />
                            </template>
                            <template x-if="!submitting">
                                <x-lucide-sparkles class="w-4 h-4" />
                            </template>
                            <span x-text="submitting ? 'Menghitung estimasi...' : 'Hitung Estimasi'"></span>
                        </button>
                        <p class="text-[11px] text-muted-foreground text-center mt-2">Estimasi dihasilkan oleh AI dalam beberapa detik</p>
                    </form>
                </div>

                {{-- ============================================ --}}
                {{-- SCREEN C: AI MODE                            --}}
                {{-- ============================================ --}}
                <div x-show="mode === 'ai'" x-cloak class="animate-fade-in">
                    {{-- Mode pill + back --}}
                    <div class="flex items-center justify-between mb-5">
                        <button
                            @click="mode = null; submitting = false"
                            class="inline-flex items-center gap-1.5 text-[12px] text-muted-foreground hover:text-card-foreground transition-colors"
                        >
                            <x-lucide-arrow-left class="w-[13px] h-[13px]" /> Pilih mode lain
                        </button>
                        <span class="text-[11px] uppercase tracking-wider bg-primary/10 text-primary rounded-full px-2.5 py-1 font-medium">
                            AI Assistant
                        </span>
                    </div>

                    {{-- Card Header --}}
                    <div class="flex flex-col items-center mb-7">
                        <div class="w-11 h-11 rounded-[10px] bg-[#E8E6E0] flex items-center justify-center">
                            <x-lucide-messages-square class="w-5 h-5 text-card-foreground" />
                        </div>
                        <h1 class="font-['Playfair_Display'] italic text-[22px] sm:text-[24px] text-card-foreground mt-3 text-center">
                            Ceritakan Rencana Renovasimu
                        </h1>
                        <p class="text-[13px] text-muted-foreground mt-1 text-center">
                            Tulis bebas — AI akan menganalisa dan menghitung estimasi biaya.
                        </p>
                    </div>

                    <form id="ai-submit-form" method="POST" action="{{ route('user.estimation.submitAI') }}" @submit="submitting = true">
                        @csrf
                        <div class="flex flex-col gap-5">
                            {{-- Textarea --}}
                            <div>
                                <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Deskripsikan Rencana Renovasi <span class="text-[hsl(0,95%,40%)]">*</span></label>
                                <textarea
                                    name="description"
                                    x-model="description"
                                    placeholder="Contoh: mau cat ruang tamu 4x5 meter pakai cat bagus di jakarta, budget sekitar 5 juta..."
                                    class="w-full min-h-[180px] rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3.5 text-sm text-card-foreground placeholder:text-[#C0BFBA] placeholder:italic focus:outline-none focus:border-primary bg-transparent resize-none"
                                ></textarea>
                                <p class="text-[11px] mt-1.5" :class="description.trim().length >= 10 ? 'text-primary' : 'text-muted-foreground'">
                                    <span x-text="description.trim().length"></span>/10 karakter minimum
                                    <template x-if="description.trim().length >= 10">
                                        <span>✓</span>
                                    </template>
                                </p>
                            </div>

                            {{-- Lokasi (optional) --}}
                            <div>
                                <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Lokasi <span class="text-[10px] text-muted-foreground/60">(opsional)</span></label>
                                <select
                                    name="location"
                                    x-model="aiLocation"
                                    class="w-full rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3 text-sm text-card-foreground focus:outline-none focus:border-primary bg-transparent appearance-none pr-9"
                                    style="background-image:url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23999%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22/></svg>'); background-repeat:no-repeat; background-position:right 14px center;"
                                >
                                    <option value="">Pilih kota…</option>
                                    @foreach ($cities as $c)
                                        <option value="{{ strtolower($c) }}">{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Budget (optional) --}}
                            <div>
                                <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Budget Rp <span class="text-[10px] text-muted-foreground/60">(opsional)</span></label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-muted-foreground pointer-events-none font-medium">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        :value="aiBudgetDisplay"
                                        @input="aiBudgetDisplay = formatIDR($event.target.value)"
                                        placeholder="misal: 5.000.000"
                                        class="w-full rounded-lg border-[1.5px] border-[#E0DFDA] pl-10 pr-3.5 py-3 text-sm text-card-foreground placeholder:text-[#C0BFBA] focus:outline-none focus:border-primary bg-transparent"
                                    />
                                    <input type="hidden" name="budget" :value="aiBudgetValue" />
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <button
                            type="submit"
                            :disabled="submitting || !aiValid"
                            class="w-full bg-primary text-primary-foreground rounded-lg py-3.5 font-['Playfair_Display'] italic text-base hover:bg-primary/90 transition-colors flex items-center justify-center gap-2 mt-7 disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <template x-if="submitting">
                                <x-lucide-loader-2 class="w-4 h-4 animate-spin" />
                            </template>
                            <template x-if="!submitting">
                                <x-lucide-sparkles class="w-4 h-4" />
                            </template>
                            <span x-text="submitting ? 'AI sedang menganalisa...' : 'Analisa dengan AI'"></span>
                        </button>
                        <p class="text-[11px] text-muted-foreground text-center mt-2">Semakin detail deskripsimu, semakin akurat estimasinya</p>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- Thinking overlay — shown while AI form is processing --}}
    <div id="thinking-overlay"
         class="hidden fixed inset-0 z-50 flex flex-col items-center justify-center"
         style="background: rgba(0,0,0,0.75); backdrop-filter: blur(6px);">

        <style>
            @keyframes thinking-pulse {
                0%, 100% { transform: scale(1); opacity: 0.9; }
                50% { transform: scale(1.05); opacity: 1; }
            }
            @keyframes dot-appear {
                0%, 100% { opacity: 0.2; transform: scale(0.8); }
                50% { opacity: 1; transform: scale(1.2); }
            }
            @keyframes dot-blink {
                0%, 100% { opacity: 0; }
                50% { opacity: 1; }
            }
        </style>

        <svg width="120" height="120" viewBox="0 0 120 120" fill="none"
             xmlns="http://www.w3.org/2000/svg"
             style="animation: thinking-pulse 2s ease-in-out infinite;">
            <circle cx="60" cy="60" r="55" stroke="white" stroke-width="1.5"
                    fill="none" opacity="0.6"/>
            <circle cx="60" cy="38" r="14" stroke="white" stroke-width="1.5" fill="none"/>
            <path d="M35 85 Q38 68 52 65 Q60 63 68 65 Q82 68 85 85"
                  stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <path d="M52 75 Q50 70 54 66"
                  stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <path d="M54 66 Q57 62 62 64"
                  stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <circle cx="78" cy="28" r="2" fill="white" opacity="0.4"
                    style="animation: dot-appear 1.5s ease-in-out infinite 0s;"/>
            <circle cx="86" cy="22" r="2.5" fill="white" opacity="0.6"
                    style="animation: dot-appear 1.5s ease-in-out infinite 0.3s;"/>
            <circle cx="95" cy="15" r="3" fill="white" opacity="0.8"
                    style="animation: dot-appear 1.5s ease-in-out infinite 0.6s;"/>
        </svg>

        <p style="color: white; font-family: 'Playfair Display', serif; font-style: italic;
                  font-size: 18px; margin-top: 28px; letter-spacing: 0.05em;">
            Sedang menganalisa<span style="animation: dot-blink 1s infinite 0s;">.</span><span style="animation: dot-blink 1s infinite 0.3s;">.</span><span style="animation: dot-blink 1s infinite 0.6s;">.</span>
        </p>
        <p style="color: rgba(255,255,255,0.5); font-size: 12px; margin-top: 10px;
                  letter-spacing: 0.08em; text-transform: uppercase;">
            AI sedang memproses deskripsimu
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('ai-submit-form');
            var overlay = document.getElementById('thinking-overlay');
            if (form && overlay) {
                form.addEventListener('submit', function () {
                    overlay.classList.remove('hidden');
                });
            }
        });
    </script>

</x-user::layouts.app>
