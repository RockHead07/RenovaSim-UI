{{-- pages.estimation-result — port of EstimationResult.tsx --}}
@php
    // Use the mock estimate from config; in production this would come from
    // POST /api/v2/estimate. The structure mirrors EstimateResponse exactly.
    $result = config('renovasim.mock_estimate');
    $projectNameInput = request()->query('projectName');
    if ($projectNameInput) {
        $result['project_name'] = $projectNameInput;
    }
    $avg = (int) round(($result['total_range']['min'] + $result['total_range']['max']) / 2);
@endphp

<x-layouts.app title="RenovaSim — Estimation Result">
    @if ($result['mode'] === 'incomplete')
        {{-- INCOMPLETE MODE --}}
        <div class="flex-1 flex items-center justify-center px-4">
            <div class="max-w-md w-full bg-card rounded-2xl p-8 text-center shadow-[0_2px_8px_rgba(0,0,0,0.07)]">
                <div class="w-12 h-12 mx-auto rounded-full bg-muted flex items-center justify-center mb-4">
                    <x-lucide-info class="w-[22px] h-[22px] text-muted-foreground" />
                </div>
                <h2 class="font-['Playfair_Display'] italic text-xl text-card-foreground mb-2">Belum bisa menghitung estimasi</h2>
                <p class="font-['DM_Sans'] text-sm text-muted-foreground mb-6">Kami belum bisa menghitung estimasi. Mohon lengkapi detail proyek.</p>
                <a href="/project-details" class="block w-full bg-primary text-primary-foreground rounded-lg py-3 font-['DM_Sans'] font-semibold text-sm hover:opacity-90 transition-opacity">
                    Kembali ke Wizard
                </a>
            </div>
        </div>
    @else
        {{-- STANDARD / BEST_EFFORT MODE --}}
        <div class="flex-1 py-8 px-4">
            <div class="max-w-[860px] mx-auto">
                {{-- Back + Title --}}
                <div class="flex items-center gap-3 mb-6">
                    <button onclick="history.back()" class="text-card-foreground hover:opacity-70 transition-opacity">
                        <x-lucide-arrow-left class="w-5 h-5" />
                    </button>
                    <div>
                        <p class="font-['DM_Sans'] font-semibold text-base text-card-foreground">Estimation Result</p>
                        <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground">AI-Powered Renovation Cost Analysis</p>
                    </div>
                </div>

                @if ($result['mode'] === 'best_effort')
                    <div class="mb-4 flex items-start gap-2.5 bg-[hsl(210,90%,96%)] border-[1.5px] border-[hsl(210,90%,55%)] rounded-xl px-4 py-3 animate-fade-in">
                        <x-lucide-info class="w-4 h-4 text-[hsl(210,90%,45%)] shrink-0 mt-0.5" />
                        <p class="font-['DM_Sans'] text-[13px] text-card-foreground leading-relaxed">
                            Estimasi ini berdasarkan banyak asumsi. Lengkapi detail untuk hasil lebih akurat.
                        </p>
                    </div>
                @endif

                {{-- Pre-framing --}}
                <div class="mb-5 flex items-start gap-2.5 bg-muted/60 border border-border rounded-xl px-4 py-3">
                    <x-lucide-info class="w-4 h-4 text-muted-foreground shrink-0 mt-0.5" />
                    <p class="font-['DM_Sans'] text-[12.5px] text-muted-foreground leading-relaxed">{{ $result['pre_framing'] }}</p>
                </div>

                @if (! empty($result['warnings']))
                    <x-estimation.warning-banner :warning="$result['warnings'][0]" />
                @endif

                {{-- Clarification banner --}}
                @if (! empty($result['clarification_needed']))
                    <div class="mb-5 flex items-start gap-2.5 bg-[hsl(210,90%,96%)] border-[1.5px] border-[hsl(210,90%,55%)] rounded-xl px-4 py-3 animate-fade-in">
                        <x-lucide-help-circle class="w-[18px] h-[18px] text-[hsl(210,90%,45%)] shrink-0 mt-0.5" />
                        <div class="flex-1 min-w-0">
                            <p class="font-['DM_Sans'] text-[13px] text-card-foreground leading-relaxed">{{ $result['clarification_needed'] }}</p>
                        </div>
                        <button
                            onclick="document.getElementById('assumptions-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                            class="shrink-0 inline-flex items-center gap-1 bg-[hsl(210,90%,45%)] text-white rounded-md px-3 py-1.5 font-['DM_Sans'] text-[12px] font-medium hover:opacity-90 transition-opacity"
                        >
                            Lengkapi Detail <x-lucide-arrow-right class="w-3 h-3" />
                        </button>
                    </div>
                @endif

                {{-- Hero band --}}
                <div class="relative w-full h-[180px] rounded-2xl overflow-hidden bg-gradient-to-br from-secondary to-card-foreground mb-6">
                    <svg class="absolute inset-0 w-full h-full opacity-[0.08]" viewBox="0 0 860 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="60"  y="30" width="140" height="90" stroke="white" stroke-width="0.8" fill="none" />
                        <rect x="220" y="50" width="80"  height="70" stroke="white" stroke-width="0.8" fill="none" />
                        <rect x="400" y="40" width="120" height="80" stroke="white" stroke-width="0.8" fill="none" />
                        <circle cx="450" cy="80" r="15"             stroke="white" stroke-width="0.6" fill="none" />
                        <rect x="700" y="50" width="100" height="60" stroke="white" stroke-width="0.8" fill="none" />
                    </svg>
                    <div class="absolute bottom-4 left-5">
                        <span class="inline-flex items-center gap-1.5 bg-black/40 text-white font-['DM_Sans'] text-sm rounded-full px-4 py-2">
                            ✦ {{ $result['project_name'] }}
                        </span>
                    </div>
                </div>

                {{-- Two-column layout --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- LEFT --}}
                    <div class="flex flex-col gap-6">
                        <x-estimation.cost-range-card :range="$result['total_range']" :confidence="$result['confidence']" />

                        <div id="assumptions-section">
                            <x-estimation.assumptions-card :assumptions="$result['assumptions']" />
                        </div>

                        @if (! empty($result['explanation']))
                            <div class="bg-[hsl(40,100%,96%)] border-[1.5px] border-[hsl(40,96%,53%)] rounded-2xl p-5">
                                <div class="w-9 h-9 rounded-full bg-[hsl(40,96%,53%)] flex items-center justify-center">
                                    <x-lucide-lightbulb class="w-[18px] h-[18px] text-white" />
                                </div>
                                <h3 class="font-['Playfair_Display'] italic text-lg text-card-foreground mt-2.5">Dasar Perhitungan</h3>
                                <p class="font-['DM_Sans'] text-[12px] text-muted-foreground mt-1">Faktor-faktor yang mempengaruhi estimasi ini</p>
                                <ul class="mt-3 space-y-2">
                                    @foreach ($result['explanation'] as $line)
                                        <li class="font-['DM_Sans'] text-[13px] text-card-foreground leading-relaxed flex gap-2">
                                            <span class="text-[hsl(36,90%,45%)] mt-1 shrink-0">•</span>
                                            <span>{{ $line }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- RIGHT --}}
                    <div class="flex flex-col gap-6">
                        <x-estimation.breakdown-card :breakdown="$result['breakdown']" :totalRange="$result['total_range']" />

                        {{-- Secondary Actions --}}
                        <div class="flex gap-2.5">
                            <a href="/project-details" class="flex-1 border-[1.5px] border-card-foreground text-card-foreground bg-card rounded-lg py-2.5 font-['DM_Sans'] font-medium text-sm flex items-center justify-center gap-1.5 hover:bg-muted transition-colors">
                                <x-lucide-pencil class="w-[14px] h-[14px]" /> Edit Inputs
                            </a>
                            <button onclick="location.reload()" class="flex-1 border-[1.5px] border-primary text-primary bg-card rounded-lg py-2.5 font-['DM_Sans'] font-medium text-sm flex items-center justify-center gap-1.5 hover:bg-primary/5 transition-colors">
                                <x-lucide-rotate-ccw class="w-[14px] h-[14px]" /> Recalculate
                            </button>
                        </div>

                        {{-- Primary CTA --}}
                        <a
                            href="{{ url('/project-overview?' . http_build_query([
                                'projectName' => $result['project_name'],
                                'totalCost'   => $avg,
                                'rangeLow'    => $result['total_range']['min'],
                                'rangeHigh'   => $result['total_range']['max'],
                            ])) }}"
                            class="w-full bg-primary text-primary-foreground font-['Playfair_Display'] italic text-base rounded-lg py-4 flex items-center justify-center gap-2 hover:opacity-90 transition-colors"
                        >
                            <x-lucide-save class="w-4 h-4" /> Save & Open Overview
                        </a>
                    </div>
                </div>

                @if (! empty($result['disclaimer']))
                    <p class="text-[#838383] text-xs text-center italic mt-10 max-w-[640px] mx-auto leading-relaxed">
                        {{ $result['disclaimer'] }}
                    </p>
                @endif
            </div>
        </div>
    @endif
</x-layouts.app>
