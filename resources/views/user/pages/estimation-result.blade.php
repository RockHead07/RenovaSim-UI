{{-- pages.estimation-result — displays real API results from session --}}
@php
    $result = session('estimation_result');
    $jobTypeIdMap = config('renovasim.job_type_id') ?? [];
    $fieldIdMap = config('renovasim.assumption_field_id') ?? [];
    $min = $result['total_range']['min'] ?? 8_000_000;
    $max = $result['total_range']['max'] ?? 12_000_000;
    $display = ($min + $max) / 2;
    $range = [
        'min' => $min,
        'max' => $max,
        'display' => $result['total_range']['display'] ?? ('Rp ' . number_format($display, 0, ',', '.'))
    ];

    $confidence = [
        'label' => $result['confidence']['label'] ?? 'Tinggi',
        'score' => $result['confidence']['score'] ?? 73,
        'message' => $result['confidence']['message'] ?? ''
    ];

    $assumptions = array_map(function($item) {
        return [
            'field' => $item['field'],
            'value' => $item['value'],
            'reason' => $item['reason'] ?? '',
            'needs_clarification' => $item['needs_clarification'] ?? false
        ];
    }, $result['assumptions'] ?? []);

    $breakdown = array_map(function($item) {
        return [
            'job_type' => $item['job_type'],
            'min' => $item['min'],
            'max' => $item['max'],
            'area' => $item['area']
        ];
    }, $result['breakdown'] ?? []);

    $budgetWarning = null;
    if (!empty($result['warnings'])) {
        foreach ($result['warnings'] as $warn) {
            if ($warn['severity'] === 'danger' || $warn['severity'] === 'warning') {
                $budgetWarning = ['severity' => $warn['severity'], 'message' => $warn['message']];
                break;
            }
        }
    }

    $infoTip = [
        'severity' => 'info',
        'message' => $result['pre_framing'] ?? 'Banyak yang mengira biaya cat hanya untuk catnya saja, padahal persiapan permukaan dan upah tukang sering jadi porsi terbesar.'
    ];

    // Extract summary data from assumptions
    $summaryData = [];
    foreach (($result['assumptions'] ?? []) as $assumption) {
        $summaryData[$assumption['field']] = $assumption['value'];
    }

    $jobTypes = array_unique(array_map(
        fn($item) => config('renovasim.job_type_id')[$item['job_type']] ?? $item['job_type'],
        $result['breakdown'] ?? []
    ));

    $summaryLocation = $summaryData['location'] ?? null;
    $summaryQuality  = $summaryData['quality'] ?? null;
    $summaryScope    = $summaryData['scope'] ?? null;
@endphp

@if(!$result)
    {{-- No result in session — redirect back to wizard --}}
    <meta http-equiv="refresh" content="0;url={{ route('user.estimation.wizard') }}">
    <p style="text-align:center;padding:2rem;">Redirecting…</p>
@else

<x-user::layouts.app title="RenovaSim — Estimation Result">
    @push('styles')
    <style>[x-cloak] { display: none !important; }</style>
    @endpush
    <div id="estimation-data" 
         data-result="{{ json_encode($result) }}" 
         style="display:none"></div>
    <div class="px-4 sm:px-6 lg:px-8 py-7">
        <div class="mx-auto max-w-[1040px]">
            {{-- Header --}}
            <div class="flex items-center gap-3 mb-5">
                <a href="{{ route('user.estimation.wizard') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl hover:bg-muted transition-colors" aria-label="Kembali">
                    <x-lucide-arrow-left class="w-4 h-4 text-muted-foreground" />
                </a>
                <div class="flex-1">
                    <div class="text-[11px] uppercase tracking-[0.18em] text-muted-foreground">Hasil Estimasi</div>
                    <div class="text-[11px] uppercase tracking-[0.18em] text-muted-foreground/80">
                        {{ $result['project_name'] ?? 'Renovasi' }} — mode {{ $result['mode'] ?? 'standard' }}
                    </div>
                </div>
            </div>

            {{-- Pre-framing --}}
            @if(!empty($result['pre_framing']))
                <div class="bg-[hsl(43,100%,95%)] border border-[hsl(40,100%,80%)] rounded-xl px-4 py-3 mb-5">
                    <p class="font-['DM_Sans'] text-[13px] text-card-foreground leading-relaxed italic">
                        💡 {{ $result['pre_framing'] }}
                    </p>
                </div>
            @endif

            {{-- Warnings --}}
            @if(!empty($result['warnings']))
                <div class="flex flex-col gap-3 mb-5">
                    @foreach($result['warnings'] as $warning)
                        <x-user::components.estimation.warning-banner :warning="$warning" />
                    @endforeach
                </div>
            @endif

            {{-- Clarification needed --}}
            @if(!empty($result['clarification_needed']))
                <div class="bg-[hsl(210,90%,96%)] border-[1.5px] border-[hsl(210,90%,55%)] rounded-xl px-4 py-3 flex items-center justify-between gap-3 mb-5">
                    <div class="flex items-center gap-2.5">
                        <x-lucide-info class="w-[18px] h-[18px] text-[hsl(210,90%,45%)] shrink-0" />
                        <p class="text-[13px] text-card-foreground leading-relaxed">
                            {{ $result['clarification_needed'] }}
                        </p>
                    </div>
                    <a href="{{ route('user.estimation.wizard') }}" class="shrink-0 inline-flex items-center gap-2 bg-[hsl(210,90%,55%)] text-white rounded-lg px-3.5 py-2 text-xs font-semibold hover:opacity-90 transition-opacity">
                        Lengkapi Detail
                        <x-lucide-arrow-right class="w-4 h-4" />
                    </a>
                </div>
            @endif

            {{-- Layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- LEFT --}}
                <div class="flex flex-col gap-6">
                    {{-- Cost Range Card --}}
                    @php
                        $totalRange = $result['total_range'] ?? ['min' => 0, 'max' => 0, 'display' => 'Rp 0'];
                        $confidence = $result['confidence'] ?? ['score' => 0, 'label' => '-', 'message' => ''];
                        // Normalize confidence score: API returns 0–1, component expects 0–100
                        if (isset($confidence['score']) && $confidence['score'] <= 1) {
                            $confidence['score'] = (int) round($confidence['score'] * 100);
                        }
                    @endphp
                    <x-user::components.estimation.cost-range-card :range="$totalRange" :confidence="$confidence" />

                    {{-- Assumptions Card --}}
                    @if(!empty($result['assumptions']))
                        <div class="bg-card rounded-2xl shadow-sm p-6 sm:p-7"
                             x-data="{
                                 changes: {},
                                 editingField: null,
                                 get hasChanges() { return Object.keys(this.changes).length > 0; },
                                 startEdit(field) { this.editingField = field; },
                                 stopEdit() { this.editingField = null; },
                                 updateChange(field, value) {
                                     if (value !== null && value !== '' && value !== undefined) {
                                         this.changes[field] = value;
                                     } else {
                                         delete this.changes[field];
                                     }
                                 },
                                 isEditing(field) { return this.editingField === field; }
                             }">

                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-['Playfair_Display'] italic text-lg text-secondary">
                                    Assumptions & Details
                                </h3>
                                <span class="text-[11px] text-muted-foreground">Klik nilai untuk mengedit</span>
                            </div>

                            @if(session('error'))
                                <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-start gap-2">
                                    <span class="text-red-500 text-sm">⚠</span>
                                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                                </div>
                            @endif

                            <div class="space-y-4">
                                @foreach($result['assumptions'] as $assumption)
                                <div class="pb-4 border-b border-border/50 last:border-b-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <label class="text-[10px] uppercase tracking-wider text-muted-foreground font-medium">
                                            {{ config('renovasim.assumption_field_id')[$assumption['field']] ?? $assumption['field'] }}
                                        </label>
                                        @if(($assumption['source'] ?? '') === 'assumed' || ($assumption['needs_clarification'] ?? false))
                                            <span class="text-[9px] bg-amber-100 text-amber-700 rounded-full px-2 py-0.5">
                                                ⚠ Diasumsikan
                                            </span>
                                        @endif
                                    </div>

                                    @if($assumption['editable'] ?? false)
                                        {{-- Show text when not editing this field --}}
                                        <div x-show="!isEditing('{{ $assumption['field'] }}')"
                                             @click="startEdit('{{ $assumption['field'] }}')"
                                             class="flex items-center gap-2 cursor-pointer group">
                                            <span class="text-sm text-card-foreground font-medium group-hover:text-primary transition-colors"
                                                  x-text="changes['{{ $assumption['field'] }}'] ?? '{{ addslashes($assumption['value']) }}'">
                                            </span>
                                            <svg class="w-3 h-3 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </div>

                                        {{-- Show input when editing this field --}}
                                        <div x-show="isEditing('{{ $assumption['field'] }}')"
                                             x-cloak
                                             class="flex items-center gap-2">

                                            @if($assumption['field'] === 'quality')
                                                <select @change="updateChange('{{ $assumption['field'] }}', $event.target.value)"
                                                        class="text-sm border border-primary rounded-lg px-2 py-1.5 bg-white text-card-foreground focus:outline-none focus:ring-1 focus:ring-primary">
                                                    @foreach(['ekonomi' => 'Ekonomi', 'standar' => 'Standar', 'premium' => 'Premium'] as $val => $label)
                                                        <option value="{{ $val }}" {{ $assumption['value'] === $val ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                            @elseif($assumption['field'] === 'scope')
                                                <select @change="updateChange('{{ $assumption['field'] }}', $event.target.value)"
                                                        class="text-sm border border-primary rounded-lg px-2 py-1.5 bg-white text-card-foreground focus:outline-none focus:ring-1 focus:ring-primary">
                                                    @foreach(['light' => 'Light', 'medium' => 'Medium', 'full' => 'Full'] as $val => $label)
                                                        <option value="{{ $val }}" {{ $assumption['value'] === $val ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                            @elseif($assumption['field'] === 'job_type')
                                                <select @change="updateChange('{{ $assumption['field'] }}', $event.target.value)"
                                                        class="text-sm border border-primary rounded-lg px-2 py-1.5 bg-white text-card-foreground focus:outline-none focus:ring-1 focus:ring-primary">
                                                    @foreach(config('renovasim.job_type_id') as $key => $label)
                                                        <option value="{{ $key }}" {{ $assumption['value'] === $key ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                            @elseif($assumption['field'] === 'location')
                                                <select @change="updateChange('{{ $assumption['field'] }}', $event.target.value)"
                                                        class="text-sm border border-primary rounded-lg px-2 py-1.5 bg-white text-card-foreground focus:outline-none focus:ring-1 focus:ring-primary">
                                                    <option value="">-- Pilih Kota --</option>
                                                    @foreach(config('renovasim.cities') as $city)
                                                        <option value="{{ strtolower($city) }}"
                                                                {{ strtolower($assumption['value']) === strtolower($city) ? 'selected' : '' }}>
                                                            {{ $city }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                            @else
                                                {{-- area or other numeric fields --}}
                                                <input type="{{ $assumption['field'] === 'area' ? 'number' : 'text' }}"
                                                       value="{{ $assumption['value'] }}"
                                                       @input="updateChange('{{ $assumption['field'] }}', $event.target.value)"
                                                       @keydown.enter="stopEdit()"
                                                       @keydown.escape="stopEdit()"
                                                       min="{{ $assumption['field'] === 'area' ? '1' : '' }}"
                                                       step="{{ $assumption['field'] === 'area' ? '0.5' : '' }}"
                                                       class="text-sm border border-primary rounded-lg px-2 py-1.5 w-32 bg-white text-card-foreground focus:outline-none focus:ring-1 focus:ring-primary"/>
                                            @endif

                                            <button @click="stopEdit()"
                                                    class="text-xs text-primary hover:text-primary/80 font-medium px-2 py-1 rounded hover:bg-primary/10 transition-colors">
                                                ✓ Selesai
                                            </button>
                                        </div>

                                    @else
                                        <p class="text-sm text-card-foreground font-medium">{{ $assumption['value'] }}</p>
                                    @endif

                                    @if(!empty($assumption['reason']))
                                        <p class="text-xs text-muted-foreground italic mt-1">{{ $assumption['reason'] }}</p>
                                    @endif
                                </div>
                                @endforeach
                            </div>

                            {{-- Refine form — only visible when there are changes --}}
                            <form method="POST"
                                  action="{{ route('user.estimation.refine') }}"
                                  x-show="hasChanges"
                                  x-cloak
                                  class="mt-6 pt-5 border-t border-border/50">
                                @csrf

                                {{-- Dynamically add hidden inputs for each change --}}
                                <template x-for="[field, value] in Object.entries(changes)" :key="field">
                                    <input type="hidden" :name="field" :value="value">
                                </template>

                                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-4">
                                    <p class="text-xs text-amber-700">
                                        <span x-text="Object.keys(changes).length"></span> field diubah.
                                        Klik "Hitung Ulang" untuk mendapatkan estimasi yang lebih akurat.
                                    </p>
                                </div>

                                <button type="submit"
                                        class="w-full bg-primary text-primary-foreground rounded-xl py-3 text-sm font-medium hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Hitung Ulang dengan Koreksi
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Explanation (Dasar Perhitungan) --}}
                    @if(!empty($result['explanation']))
                        <div class="bg-[hsl(43,100%,95%)] border border-[hsl(40,100%,80%)] rounded-2xl p-6 sm:p-7 shadow-sm">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-8 h-8 rounded-xl bg-white/70 flex items-center justify-center">
                                    <x-lucide-lightbulb class="w-4 h-4 text-[hsl(35,100%,45%)]" />
                                </div>
                                <h3 class="font-['Playfair_Display'] italic text-lg text-secondary">Dasar Perhitungan</h3>
                            </div>
                            <ul class="text-[12px] text-muted-foreground leading-relaxed list-disc pl-4 space-y-2">
                                @foreach($result['explanation'] as $line)
                                    <li>{{ $line }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                {{-- RIGHT --}}
                <div class="flex flex-col gap-6">
                    {{-- Breakdown Card --}}
                    @if(!empty($result['breakdown']))
                        <x-user::components.estimation.breakdown-card
                            :breakdown="$result['breakdown']"
                            :totalRange="$totalRange"
                        />
                    @endif

                    {{-- Project Summary --}}
                    <div class="bg-card rounded-2xl shadow-sm p-5 sm:p-6">
                        <p class="text-[10px] uppercase tracking-[0.15em] text-muted-foreground mb-4 font-medium">
                            Project Summary
                        </p>
                        <div class="space-y-3">

                            {{-- Location --}}
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-full bg-muted flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-[13px] h-[13px] text-muted-foreground" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-[11px] text-muted-foreground">Lokasi</p>
                                    <p class="text-sm font-medium text-card-foreground capitalize">
                                        {{ $summaryLocation && $summaryLocation !== 'default'
                                            ? ucfirst($summaryLocation)
                                            : '—' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Job Types --}}
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-full bg-muted flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-[13px] h-[13px] text-muted-foreground" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-[11px] text-muted-foreground">Jenis Renovasi</p>
                                    <p class="text-sm font-medium text-card-foreground">
                                        {{ count($jobTypes) > 0 ? implode(', ', $jobTypes) : '—' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Quality --}}
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-full bg-muted flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-[13px] h-[13px] text-muted-foreground" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-[11px] text-muted-foreground">Kualitas Material</p>
                                    <p class="text-sm font-medium text-card-foreground capitalize">
                                        {{ $summaryQuality ? ucfirst($summaryQuality) : '—' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Mode badge --}}
                            <div class="pt-2 border-t border-border/50">
                                <span class="text-[10px] uppercase tracking-wider font-medium px-2.5 py-1 rounded-full
                                    {{ ($result['mode'] ?? '') === 'standard'
                                        ? 'bg-primary/10 text-primary'
                                        : 'bg-amber-100 text-amber-700' }}">
                                    {{ ($result['mode'] ?? '') === 'standard' ? 'Mode Wizard' : 'Mode AI' }}
                                </span>
                            </div>

                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="bg-card rounded-2xl shadow-sm p-5 sm:p-6 flex flex-col gap-3">
                        <a href="{{ route('user.estimation.wizard') }}" 
                           class="inline-flex items-center justify-center gap-2 rounded-xl border border-border bg-card px-4 py-3 text-sm font-medium text-card-foreground hover:bg-muted transition-colors">
                            <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                            </svg>
                            Estimasi Baru
                        </a>
                        <form method="POST" action="{{ route('user.project.save') }}">
                            @csrf
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors duration-200 flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Simpan ke Project
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Disclaimer --}}
            @if(!empty($result['disclaimer']))
                <p class="text-[10px] text-muted-foreground text-center mt-6">
                    {{ $result['disclaimer'] }}
                </p>
            @else
                <p class="text-[10px] text-muted-foreground text-center mt-6">
                    Estimasi ini berdasarkan harga pasar rata-rata dan dapat bervariasi tergantung kondisi lapangan, ketersediaan material, serta negosiasi dengan kontraktor.
                </p>
            @endif
        </div>
    </div>


</x-user::layouts.app>

@endif
