{{-- pages.estimation-result — port-style results page --}}
@php
    $inputs = [
        'projectName'    => request()->query('projectName', 'Renovasi Rumah Pak Budi'),
        'city'           => request()->query('city', 'Jakarta'),
        'renovationType' => request()->query('renovationType', 'Pengecatan'),
        'quality'        => request()->query('quality', 'Standar'),
        'area'           => request()->query('area', '9'),
        'unit'           => request()->query('unit', 'm²'),
        'description'    => request()->query('description', ''),
        'mode'           => request()->query('mode', 'detailed'),
        'budget'         => (int) request()->query('budget', 0),
    ];

    // Demo-ish ranges (replace later with real calc/service)
    $min = 8_000_000;
    $max = 12_000_000;
    $display = 10_000_000;
    $range = ['min' => $min, 'max' => $max, 'display' => $display];

    $confidence = ['label' => 'Tinggi', 'score' => 73, 'message' => 'Estimasi perlu beberapa asumsi tambahan.'];

    $assumptions = [
        ['field' => 'Luas Area', 'value' => $inputs['area'] . ' ' . $inputs['unit'], 'reason' => 'Luas didasarkan dari input Anda', 'needs_clarification' => false],
        ['field' => 'Lokasi', 'value' => $inputs['city'], 'reason' => 'Dari data input Anda', 'needs_clarification' => false],
        ['field' => 'Kualitas Material', 'value' => $inputs['quality'], 'reason' => 'Material kualitas standar pasaran', 'needs_clarification' => false],
    ];

    $breakdown = [
        ['job_type' => 'planning', 'min' => 3_000_000, 'max' => 4_500_000, 'area' => $inputs['area']],
        ['job_type' => 'tiling',   'min' => 5_000_000, 'max' => 7_500_000, 'area' => $inputs['area']],
    ];

    $budgetWarning = null;
    if ($inputs['budget'] > 0 && $inputs['budget'] < $min) {
        $budgetWarning = ['severity' => 'warning', 'message' => 'Budget Anda kemungkinan tidak cukup untuk scope ini.'];
    }

    $infoTip = ['severity' => 'info', 'message' => 'Banyak yang mengira biaya cat hanya untuk catnya saja, padahal persiapan permukaan dan upah tukang sering jadi porsi terbesar.'];

    $aiUrl = '/user/ai-estimation?' . http_build_query([
        'projectName'    => $inputs['projectName'],
        'city'           => $inputs['city'],
        'renovationType' => $inputs['renovationType'],
        'quality'        => $inputs['quality'],
        'budget'         => $inputs['budget'] ?: null,
    ]);
@endphp

<x-user::layouts.app title="RenovaSim — Estimation Result">
    <div class="px-4 sm:px-6 lg:px-8 py-7">
        <div class="mx-auto max-w-[1040px]">
            {{-- Header --}}
            <div class="flex items-center gap-3 mb-5">
                <a href="{{ $aiUrl }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl hover:bg-muted transition-colors" aria-label="Back">
                    <x-lucide-arrow-left class="w-4 h-4 text-muted-foreground" />
                </a>
                <div class="flex-1">
                    <div class="text-[11px] uppercase tracking-[0.18em] text-muted-foreground">Estimation Result</div>
                    <div class="text-[11px] uppercase tracking-[0.18em] text-muted-foreground/80">AI-powered renovation cost analysis</div>
                </div>
            </div>

            <div class="flex flex-col gap-3 mb-5">
                <x-user::components.estimation.warning-banner :warning="$infoTip" />
                @if ($budgetWarning)
                    <x-user::components.estimation.warning-banner :warning="$budgetWarning" />
                @endif
            </div>

            {{-- Top question bar --}}
            <div class="bg-[hsl(210,90%,96%)] border-[1.5px] border-[hsl(210,90%,55%)] rounded-xl px-4 py-3 flex items-center justify-between gap-3 mb-5">
                <div class="flex items-center gap-2.5">
                    <x-lucide-info class="w-[18px] h-[18px] text-[hsl(210,90%,45%)] shrink-0" />
                    <p class="text-[13px] text-card-foreground leading-relaxed">
                        Berapa luas area yang akan direnovasi? (dalam m²)
                    </p>
                </div>
                <a href="{{ $aiUrl }}" class="shrink-0 inline-flex items-center gap-2 bg-[hsl(210,90%,55%)] text-white rounded-lg px-3.5 py-2 text-xs font-semibold hover:opacity-90 transition-opacity">
                    Lengkapi Detail
                    <x-lucide-arrow-right class="w-4 h-4" />
                </a>
            </div>

            {{-- Layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- LEFT --}}
                <div class="flex flex-col gap-6">
                    {{-- Banner / video placeholder --}}
                    <div class="rounded-2xl bg-linear-to-b from-[hsl(75,30%,18%)] to-[hsl(75,30%,12%)] h-[170px] sm:h-[190px] shadow-sm overflow-hidden relative">
                        <div class="absolute inset-0 opacity-25" style="background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.20), transparent 55%);"></div>
                        <div class="absolute bottom-3 left-3 inline-flex items-center gap-2 bg-black/35 text-white rounded-full px-3 py-1 text-[11px]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[hsl(110,70%,60%)]"></span>
                            yes
                        </div>
                    </div>

                    <x-user::components.estimation.cost-range-card :range="$range" :confidence="$confidence" />

                    <x-user::components.estimation.assumptions-card :assumptions="$assumptions" />

                    <div class="bg-[hsl(43,100%,95%)] border border-[hsl(40,100%,80%)] rounded-2xl p-6 sm:p-7 shadow-sm">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-xl bg-white/70 flex items-center justify-center">
                                <x-lucide-lightbulb class="w-4 h-4 text-[hsl(35,100%,45%)]" />
                            </div>
                            <h3 class="font-['Playfair_Display'] italic text-lg text-secondary">Dasar Perhitungan</h3>
                        </div>
                        <ul class="text-[12px] text-muted-foreground leading-relaxed list-disc pl-4 space-y-2">
                            <li>Upah tukang di Jakarta ~30% lebih tinggi dari rata-rata nasional</li>
                            <li>Pemasangan keramik butuh ketelitian lebih → biaya & waktu lebih tinggi</li>
                            <li>Ditambahkan 5% untuk material cadangan dan waste selama pengerjaan</li>
                        </ul>
                    </div>
                </div>

                {{-- RIGHT --}}
                <div class="flex flex-col gap-6">
                    <x-user::components.estimation.breakdown-card :breakdown="$breakdown" :totalRange="$range" />

                    <div class="bg-card rounded-2xl shadow-sm p-5 sm:p-6 flex flex-col gap-3">
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ $aiUrl }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-border bg-card px-4 py-3 text-sm font-medium text-card-foreground hover:bg-muted transition-colors">
                                <x-lucide-pencil class="w-4 h-4 text-muted-foreground" />
                                Edit Inputs
                            </a>
                            <a href="{{ url()->current() . '?' . http_build_query(request()->query()) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-border bg-[hsl(73,55%,92%)] px-4 py-3 text-sm font-medium text-secondary hover:opacity-90 transition-opacity">
                                <x-lucide-refresh-cw class="w-4 h-4" />
                                Recalculate
                            </a>
                        </div>
                        <a href="/user/project-overview" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary text-primary-foreground px-4 py-3.5 text-sm font-semibold hover:opacity-90 transition-opacity">
                            <x-lucide-save class="w-4 h-4" />
                            Save & Open Overview
                        </a>
                    </div>
                </div>
            </div>

            <p class="text-[10px] text-muted-foreground text-center mt-6">
                Estimasi ini berdasarkan harga pasar rata-rata dan dapat bervariasi tergantung kondisi lapangan, ketersediaan material, serta negosiasi dengan kontraktor.
            </p>
        </div>
    </div>
</x-user::layouts.app>
