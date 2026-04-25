@extends('layouts.app')

@section('title', 'AI Estimation — RenovaSim')

@section('content')
<div class="min-h-screen bg-background flex flex-col">

    @include('partials.app-nav')

    {{-- Step Indicator --}}
    <div class="flex flex-col items-center mt-7">
        <div class="relative flex gap-[60px]">
            <div class="absolute top-5 left-5 right-5 h-[1.5px] bg-primary"></div>

            <div class="flex flex-col items-center relative z-10">
                <div class="w-10 h-10 rounded-full border-[1.5px] border-primary bg-background flex items-center justify-center">
                    {{-- Check icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                        <path d="M20 6 9 17 4 12"/>
                    </svg>
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
        <div class="w-full max-w-[560px] bg-card rounded-2xl p-9 shadow-[0_2px_8px_rgba(0,0,0,0.07)]">

            {{-- Summary Bar --}}
            <div class="bg-[#F5F3EE] rounded-lg px-4 py-2.5 flex flex-wrap items-center gap-3 mb-7">
                <span class="text-xs text-secondary">🏠 {{ $project_name ?? 'Renovasi Rumah Pak Budi' }}</span>
                <span class="text-xs text-secondary">📍 {{ $city ?? 'Jakarta' }}</span>
                <span class="text-xs text-secondary">🔨 {{ $renovation_type ?? 'Pengecatan' }}</span>
                <span class="text-xs text-secondary">✨ {{ $quality ?? 'Standar' }}</span>
                <a href="/project-details" class="ml-auto">
                    {{-- Pencil icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground">
                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                        <path d="m15 5 4 4"/>
                    </svg>
                </a>
            </div>

            {{-- Card Header --}}
            <div class="flex flex-col items-center mb-8">
                <div class="w-11 h-11 rounded-[10px] bg-[#E8E6E0] flex items-center justify-center">
                    {{-- Sparkles icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-card-foreground">
                        <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
                        <path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/>
                    </svg>
                </div>
                <h1 class="font-playfair text-[22px] text-card-foreground mt-3">AI Estimation</h1>
                <p class="text-[13px] text-muted-foreground mt-1">Tell us more and let AI build your cost blueprint.</p>
            </div>

            <form action="/ai-estimation" method="POST">
                @csrf
                <input type="hidden" name="project_name"     value="{{ $project_name ?? '' }}">
                <input type="hidden" name="city"             value="{{ $city ?? '' }}">
                <input type="hidden" name="renovation_type"  value="{{ $renovation_type ?? '' }}">
                <input type="hidden" name="quality"          value="{{ $quality ?? '' }}">

                {{-- Luas Area --}}
                <div class="mb-5">
                    <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Luas Area</label>
                    <div class="flex gap-2">
                        <input
                            type="number"
                            name="area"
                            value="{{ old('area') }}"
                            placeholder="e.g., 45"
                            class="flex-1 rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3 text-sm text-card-foreground placeholder:text-[#C0BFBA] focus:outline-none focus:border-primary bg-transparent"
                        >
                        <div class="flex rounded-lg overflow-hidden border-[1.5px] border-[#E0DFDA]">
                            <button type="button" id="btn-m2" onclick="selectUnit('m²')"
                                class="px-3.5 py-3 text-sm font-medium transition-colors bg-primary text-primary-foreground">m²</button>
                            <button type="button" id="btn-sqft" onclick="selectUnit('sqft')"
                                class="px-3.5 py-3 text-sm font-medium transition-colors bg-[#EDECEA] text-muted-foreground">sqft</button>
                        </div>
                        <input type="hidden" name="unit" id="unit-input" value="m²">
                    </div>
                </div>

                {{-- Deskripsi Renovasi --}}
                <div class="mb-5">
                    <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">Deskripsi Renovasi</label>
                    <textarea
                        name="description"
                        placeholder="Ceritakan detail kebutuhanmu... contoh: cat ulang 2 ruangan termasuk plamir, dinding kamar 3×4m, pakai cat merk lokal."
                        class="w-full min-h-[120px] rounded-lg border-[1.5px] border-[#E0DFDA] px-3.5 py-3.5 text-sm text-card-foreground placeholder:text-[#C0BFBA] placeholder:italic focus:outline-none focus:border-primary bg-transparent resize-none"
                    >{{ old('description') }}</textarea>
                    <p class="text-[11px] text-muted-foreground mt-1.5">ℹ Semakin detail deskripsimu, semakin akurat estimasinya.</p>
                </div>

                <button type="submit" class="w-full bg-primary text-primary-foreground rounded-lg py-3.5 font-playfair text-base hover:bg-primary/90 transition-colors flex items-center justify-center gap-2 mt-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
                        <path d="M20 3v4"/><path d="M22 5h-4"/>
                    </svg>
                    Generate Estimasi
                </button>
                <p class="text-[11px] text-muted-foreground text-center mt-2">Estimasi dihasilkan dalam beberapa detik</p>

            </form>
        </div>
    </div>

    @include('partials.app-footer')

</div>
@endsection

@push('scripts')
<script>
    function selectUnit(unit) {
        document.getElementById('unit-input').value = unit;
        const m2   = document.getElementById('btn-m2');
        const sqft = document.getElementById('btn-sqft');
        if (unit === 'm²') {
            m2.className   = m2.className.replace('bg-[#EDECEA] text-muted-foreground', 'bg-primary text-primary-foreground');
            sqft.className = sqft.className.replace('bg-primary text-primary-foreground', 'bg-[#EDECEA] text-muted-foreground');
        } else {
            sqft.className = sqft.className.replace('bg-[#EDECEA] text-muted-foreground', 'bg-primary text-primary-foreground');
            m2.className   = m2.className.replace('bg-primary text-primary-foreground', 'bg-[#EDECEA] text-muted-foreground');
        }
    }
</script>
@endpush
