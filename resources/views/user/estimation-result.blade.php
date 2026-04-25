@extends('layouts.app')

@section('title', 'Estimation Result — RenovaSim')

@section('content')
<div class="min-h-screen bg-background flex flex-col">

    @include('partials.app-nav')

    <div class="flex-1 py-8 px-4">
        <div class="max-w-[860px] mx-auto">

            {{-- Back + Title --}}
            <div class="flex items-center gap-3 mb-6">
                <a href="/ai-estimation" class="text-card-foreground hover:opacity-70 transition-opacity">
                    {{-- ArrowLeft icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 19-7-7 7-7"/>
                        <path d="M19 12H5"/>
                    </svg>
                </a>
                <div>
                    <p class="font-sans font-semibold text-[16px] text-card-foreground">Estimation Result</p>
                    <p class="font-sans text-[10px] uppercase tracking-[0.15em] text-muted-foreground">AI-Powered Renovation Cost Analysis</p>
                </div>
            </div>

            {{-- Hero Image Card --}}
            <div class="relative w-full h-[200px] rounded-2xl overflow-hidden bg-gradient-to-br from-[#3B411E] to-[#2C2C2B] mb-6">
                <svg class="absolute inset-0 w-full h-full opacity-[0.08]" viewBox="0 0 860 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="60" y="30" width="140" height="90" stroke="white" stroke-width="0.8" fill="none"/>
                    <rect x="220" y="50" width="80" height="70" stroke="white" stroke-width="0.8" fill="none"/>
                    <line x1="60" y1="120" x2="300" y2="120" stroke="white" stroke-width="0.6"/>
                    <line x1="200" y1="30" x2="200" y2="120" stroke="white" stroke-width="0.6"/>
                    <rect x="400" y="40" width="120" height="80" stroke="white" stroke-width="0.8" fill="none"/>
                    <rect x="560" y="60" width="70" height="50" stroke="white" stroke-width="0.8" fill="none"/>
                    <line x1="400" y1="120" x2="630" y2="120" stroke="white" stroke-width="0.6"/>
                    <circle cx="450" cy="80" r="15" stroke="white" stroke-width="0.6" fill="none"/>
                    <rect x="700" y="50" width="100" height="60" stroke="white" stroke-width="0.8" fill="none"/>
                </svg>
                <div class="absolute bottom-4 left-5">
                    <span class="inline-flex items-center gap-1.5 bg-black/40 text-white font-sans text-sm rounded-full px-4 py-2">
                        ✦ {{ $project_name ?? 'Living Room Project' }}
                    </span>
                </div>
            </div>

            {{-- Two-column layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Left Column --}}
                <div class="flex flex-col gap-6">

                    {{-- AI Estimate Card --}}
                    <div class="bg-white rounded-2xl p-6 shadow-[0_2px_8px_rgba(0,0,0,0.07)]">
                        <p class="font-sans text-[10px] uppercase tracking-[0.15em] text-[#8BA023] font-medium flex items-center gap-1">
                            {{-- Zap icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                            </svg>
                            AI-Driven Estimate
                        </p>
                        <p class="font-playfair text-[36px] font-bold text-card-foreground mt-2">
                            {{ $total_estimate ?? 'Rp 8.750.000' }}
                        </p>
                        <p class="font-sans text-xs text-muted-foreground mt-1">Generated using regional data and predictive modeling.</p>
                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-sans text-[10px] uppercase tracking-[0.15em] text-muted-foreground">AI Confidence</span>
                                <span class="font-sans text-sm font-semibold text-card-foreground">{{ $confidence ?? '87' }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-[#E0DFDA] rounded-full overflow-hidden">
                                <div class="h-full bg-[#8BA023] rounded-full" style="width: {{ $confidence ?? 87 }}%"></div>
                            </div>
                            <p class="font-sans text-[11px] text-muted-foreground mt-1.5">Based on 120+ local renovation data points</p>
                        </div>
                    </div>

                    {{-- Cost Comparison --}}
                    <div>
                        <h2 class="font-playfair text-lg text-card-foreground mb-3">Cost Comparison</h2>
                        <div class="bg-white rounded-xl p-4 shadow-[0_1px_4px_rgba(0,0,0,0.06)]">
                            <div class="flex items-center justify-between">
                                <span class="font-sans text-[10px] uppercase tracking-[0.15em] text-muted-foreground">Current Estimate</span>
                                {{-- Eye icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </div>
                            <p class="font-playfair text-2xl text-card-foreground mt-1">{{ $total_estimate ?? 'Rp 8.750.000' }}</p>
                            <p class="font-sans text-xs text-muted-foreground mt-0.5">Selected materials and standard labor rates</p>
                        </div>
                        <div class="bg-[#F0F5E8] border-[1.5px] border-[#8BA023] rounded-xl p-4 mt-3 relative">
                            <span class="absolute top-3 right-3 bg-[#8BA023] text-white font-sans text-[10px] uppercase rounded-full px-2.5 py-1">
                                AI Optimized ✓
                            </span>
                            <span class="font-sans text-[10px] uppercase tracking-[0.15em] text-muted-foreground">Optimized Estimate</span>
                            <p class="font-playfair text-2xl text-[#3B411E] mt-1">{{ $optimized_estimate ?? 'Rp 7.200.000' }}</p>
                            <p class="font-sans text-xs text-muted-foreground mt-0.5">With recommended material adjustments</p>
                        </div>
                    </div>

                    {{-- AI Insight Card --}}
                    <div class="bg-[#FFF8EC] border-[1.5px] border-[#F59E0B] rounded-2xl p-5">
                        <div class="w-9 h-9 rounded-full bg-[#F59E0B] flex items-center justify-center">
                            {{-- Lightbulb icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/>
                                <path d="M9 18h6"/><path d="M10 22h4"/>
                            </svg>
                        </div>
                        <h3 class="font-playfair text-lg text-card-foreground mt-2.5">AI Insight</h3>
                        <p class="font-sans text-[13px] text-card-foreground leading-relaxed mt-2">
                            Premium tiles contribute <span class="font-semibold text-[#D97706]">34% of total cost</span>.
                            Switching to ceramic tiles could reduce cost by <span class="font-semibold text-[#D97706]">Rp 1.200.000</span>.
                        </p>
                        <a href="/apply-cost-reduction" class="block w-full bg-[#2C2C2B] text-white font-sans font-semibold text-sm rounded-lg py-3.5 mt-4 hover:bg-[#3B411E] transition-colors text-center">
                            Apply Cost Reduction
                        </a>
                        <p class="font-sans text-[11px] text-muted-foreground text-center mt-2">Reduce cost by 18% (= Rp 1.500.000)</p>
                    </div>

                </div>

                {{-- Right Column --}}
                <div class="flex flex-col gap-6">

                    {{-- Cost Breakdown --}}
                    <div class="bg-white rounded-2xl p-6 shadow-[0_2px_8px_rgba(0,0,0,0.07)]">
                        <h2 class="font-playfair text-lg text-card-foreground mb-4">Cost Breakdown</h2>

                        <div>
                            <div class="flex items-center justify-between">
                                <span class="font-sans font-medium text-sm text-card-foreground">Material Cost</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-sans text-sm text-card-foreground">{{ $material_cost ?? 'Rp 5.250.000' }}</span>
                                    <span class="font-sans text-[11px] bg-[#F0F5E8] text-[#3B411E] rounded px-1.5 py-0.5">{{ $material_pct ?? '60' }}%</span>
                                </div>
                            </div>
                            <div class="w-full h-1.5 bg-[#E0DFDA] rounded-full overflow-hidden mt-2">
                                <div class="h-full bg-[#8BA023] rounded-full" style="width: {{ $material_pct ?? 60 }}%"></div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="flex items-center justify-between">
                                <span class="font-sans font-medium text-sm text-card-foreground">Labor Cost</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-sans text-sm text-card-foreground">{{ $labor_cost ?? 'Rp 3.500.000' }}</span>
                                    <span class="font-sans text-[11px] bg-[#F0F5E8] text-[#3B411E] rounded px-1.5 py-0.5">{{ $labor_pct ?? '40' }}%</span>
                                </div>
                            </div>
                            <div class="w-full h-1.5 bg-[#E0DFDA] rounded-full overflow-hidden mt-2">
                                <div class="h-full bg-[#3B411E] rounded-full" style="width: {{ $labor_pct ?? 40 }}%"></div>
                            </div>
                        </div>

                        <div class="border-t border-[#E0DFDA] my-4"></div>
                        <p class="font-sans text-[10px] uppercase tracking-[0.15em] text-muted-foreground mb-3">Detailed Items</p>

                        @foreach($items ?? [
                            ['emoji' => '🎨', 'name' => 'Paint',  'badge' => 'Recommended Change', 'badge_class' => 'bg-[#FEF3C7] text-[#92400E]', 'price' => 'Rp 1.500.000'],
                            ['emoji' => '⬛', 'name' => 'Tiles',  'badge' => 'HIGHEST COST',        'badge_class' => 'bg-[#FEE2E2] text-[#DC2626]', 'price' => 'Rp 3.000.000', 'bg' => 'bg-[#FEE2E2]'],
                            ['emoji' => '👷', 'name' => 'Labor',  'badge' => null,                   'badge_class' => '',                            'price' => 'Rp 2.000.000'],
                        ] as $item)
                        <div class="flex items-center justify-between py-2">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full {{ $item['bg'] ?? 'bg-[#F0F5E8]' }} flex items-center justify-center text-xs">{{ $item['emoji'] }}</div>
                                <div>
                                    <p class="font-sans font-medium text-[13px] text-card-foreground">{{ $item['name'] }}</p>
                                    @if($item['badge'])
                                    <span class="font-sans text-[10px] {{ $item['badge_class'] }} rounded px-1.5 py-0.5">{{ $item['badge'] }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="font-sans text-[13px] text-card-foreground">{{ $item['price'] }}</span>
                        </div>
                        @endforeach
                    </div>

                    {{-- Secondary Actions --}}
                    <div class="flex gap-2.5">
                        <a href="/ai-estimation" class="flex-1 border-[1.5px] border-card-foreground text-card-foreground bg-white rounded-lg py-2.5 font-sans font-medium text-sm flex items-center justify-center gap-1.5 hover:bg-background transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                <path d="m15 5 4 4"/>
                            </svg>
                            Edit Inputs
                        </a>
                        <a href="#" class="flex-1 border-[1.5px] border-[#8BA023] text-[#8BA023] bg-white rounded-lg py-2.5 font-sans font-medium text-sm flex items-center justify-center gap-1.5 hover:bg-[#F0F5E8] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            Adjust Material Quality
                        </a>
                    </div>

                    {{-- Primary CTA --}}
                    <a href="/save-project" class="w-full bg-[#8BA023] text-white font-playfair text-base rounded-lg py-4 flex items-center justify-center gap-2 hover:bg-[#7A8E1F] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
                            <path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/>
                            <path d="M7 3v4a1 1 0 0 0 1 1h7"/>
                        </svg>
                        Save Project
                    </a>

                    <a href="/ai-estimation" class="font-sans text-[13px] text-muted-foreground text-center flex items-center justify-center gap-1.5 -mt-2 hover:text-card-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                        </svg>
                        Recalculate
                    </a>

                </div>
            </div>

        </div>
    </div>

    @include('partials.app-footer')

</div>
@endsection
