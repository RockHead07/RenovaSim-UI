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
            <div class="max-w-md w-full bg-card rounded-2xl p-8 text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-muted flex items-center justify-center mb-4">
                    <x-lucide-info class="w-[22px] h-[22px] text-muted-foreground" />
                </div>
                <h2 class="font-['Playfair_Display'] italic text-xl text-card-foreground mb-2">Belum bisa menghitung</h2>
                <p class="font-['DM_Sans'] text-sm text-muted-foreground mb-6">Mohon lengkapi detail proyek.</p>
                <a href="/project-details" class="block w-full bg-primary text-primary-foreground rounded-lg py-3">
                    Kembali
                </a>
            </div>
        </div>
    @else
        <div class="flex-1 py-8 px-4">
            <div class="max-w-[860px] mx-auto">
                <div class="flex items-center gap-3 mb-6">
                    <button onclick="history.back()" class="text-card-foreground">
                        <x-lucide-arrow-left class="w-5 h-5" />
                    </button>
                    <div>
                        <p class="font-['DM_Sans'] font-semibold text-base text-card-foreground">Estimation Result</p>
                    </div>
                </div>

                <div class="relative w-full h-[180px] rounded-2xl overflow-hidden bg-gradient-to-br from-secondary to-card-foreground mb-6">
                    <div class="absolute bottom-4 left-5">
                        <span class="inline-flex items-center gap-1.5 bg-black/40 text-white text-sm rounded-full px-4 py-2">
                            ✦ {{ $result['project_name'] }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-user::components.estimation.cost-range-card :range="$result['total_range']" :confidence="$result['confidence']" />
                    <x-user::components.estimation.breakdown-card :breakdown="$result['breakdown']" :totalRange="$result['total_range']" />
                </div>
            </div>
        </div>
    @endif
</x-user::layouts.app>
