@extends('layouts.app')

@section('title', 'Project Details — RenovaSim')

@section('content')
<div class="min-h-screen bg-background flex flex-col">

    @include('partials.app-nav')

    <div class="flex-1 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-[460px]">

            {{-- Card --}}
            <div class="bg-card rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.09)] overflow-hidden">

                {{-- Thin olive progress bar — top edge of card --}}
                @php $step = request('step', 1); $pct = round($step / 4 * 100); @endphp
                <div class="h-[3px] bg-[#E8E6E0] w-full">
                    <div class="h-full bg-primary transition-all duration-500" style="width: {{ $pct }}%"></div>
                </div>

                <div class="px-9 pt-10 pb-9">

                    {{-- ── STEP 1: Project Name ────────────────────────── --}}
                    @if($step == 1)
                    <h1 class="font-playfair text-[26px] leading-tight text-secondary text-center">
                        What is the name<br>of your project?
                    </h1>
                    <p class="text-[13px] text-muted-foreground text-center mt-3 leading-relaxed">
                        Give your renovation a distinct name<br>to easily identify it later.
                    </p>
                    <form action="/project-details?step=2" method="POST" class="mt-7">
                        @csrf
                        <input
                            type="text"
                            name="project_name"
                            value="{{ old('project_name', session('project_name')) }}"
                            placeholder="e.g., Renovasi Rumah Pak Budi"
                            autofocus
                            class="w-full bg-[#F4F3EF] border border-[#E0DFDA] rounded-xl px-4 py-3.5 text-sm text-card-foreground placeholder:text-[#BEBAB3] focus:outline-none focus:border-primary focus:bg-white transition-colors"
                        >
                        <button type="submit" class="mt-8 w-full bg-primary text-primary-foreground rounded-xl py-3.5 font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_14px_rgba(139,160,35,0.32)] hover:opacity-90 transition-opacity">
                            Next <span>→</span>
                        </button>
                    </form>
                    <a href="/project-stage" class="block w-full mt-3 text-[13px] text-muted-foreground hover:text-card-foreground text-center py-1">
                        Cancel
                    </a>

                    {{-- ── STEP 2: Location ────────────────────────────── --}}
                    @elseif($step == 2)
                    <h1 class="font-playfair text-[26px] leading-tight text-secondary text-center">
                        Where is your project<br>located?
                    </h1>
                    <p class="text-[13px] text-muted-foreground text-center mt-3 leading-relaxed">
                        Select your city to get accurate local<br>material and labor rates.
                    </p>
                    <form action="/project-details?step=3" method="POST" class="mt-7">
                        @csrf
                        <input type="hidden" name="project_name" value="{{ session('project_name') }}">
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                            </svg>
                            <select name="city" class="w-full appearance-none bg-[#F4F3EF] border border-[#E0DFDA] rounded-xl pl-9 pr-10 py-3.5 text-sm text-card-foreground focus:outline-none focus:border-primary focus:bg-white transition-colors">
                                <option value="" disabled selected>Pilih kota / kabupaten…</option>
                                @foreach(['Aceh','Ambon','Balikpapan','Bandung','Banjarmasin','Batam','Bekasi','Bogor','Denpasar','Depok','Gorontalo','Jakarta','Jambi','Jayapura','Kendari','Kupang','Makassar','Malang','Manado','Mataram','Medan','Padang','Palembang','Palu','Pekanbaru','Pontianak','Samarinda','Semarang','Surabaya','Surakarta','Tangerang','Yogyakarta'] as $c)
                                <option value="{{ $c }}" {{ old('city', session('city')) === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                            <svg class="absolute right-3.5 top-1/2 -translate-y-1/2 text-primary pointer-events-none w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </div>

                        {{-- Indonesia map graphic --}}
                        <div class="mt-5 rounded-xl overflow-hidden bg-[#1a2535] relative w-full" style="height:130px">
                            <div class="absolute top-2.5 right-3 text-[9px] uppercase tracking-[0.18em] text-[#7fa8c0] font-medium">Region: Southeast Asia</div>
                            <svg viewBox="0 0 420 120" class="w-full h-full" fill="none">
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
                            </svg>
                            <div class="absolute bottom-2.5 left-1/2 -translate-x-1/2 flex gap-1.5">
                                <div class="w-1.5 h-1.5 rounded-full bg-primary"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-[#3a5068]"></div>
                            </div>
                        </div>

                        <button type="submit" class="mt-6 w-full bg-primary text-primary-foreground rounded-xl py-3.5 font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_14px_rgba(139,160,35,0.32)] hover:opacity-90 transition-opacity">
                            Next <span>→</span>
                        </button>
                    </form>
                    <a href="/project-details?step=1" class="block w-full mt-3 text-[13px] text-muted-foreground hover:text-card-foreground text-center py-1">
                        ← Back
                    </a>

                    {{-- ── STEP 3: Renovation Type ─────────────────────── --}}
                    @elseif($step == 3)
                    <h1 class="font-playfair text-[26px] leading-tight text-secondary text-center">
                        What type of renovation<br>are you planning?
                    </h1>
                    <p class="text-[13px] text-muted-foreground text-center mt-3 leading-relaxed">
                        Select the primary focus<br>of your project.
                    </p>
                    <form action="/project-details?step=4" method="POST" class="mt-7">
                        @csrf
                        <input type="hidden" name="project_name" value="{{ session('project_name') }}">
                        <input type="hidden" name="city" value="{{ session('city') }}">
                        <div class="relative">
                            <select name="renovation_type" class="w-full appearance-none bg-[#F4F3EF] border border-[#E0DFDA] rounded-xl px-4 pr-10 py-3.5 text-sm text-card-foreground focus:outline-none focus:border-primary focus:bg-white transition-colors">
                                <option value="" disabled selected>Pilih tipe renovasi…</option>
                                @foreach(['Pengecatan','Lantai Keramik','Kamar Mandi','Dapur','Atap','Plafon','Listrik','Sanitasi','Pintu & Jendela','Taman & Eksterior','Renovasi Total'] as $t)
                                <option value="{{ $t }}" {{ old('renovation_type', session('renovation_type')) === $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                            <svg class="absolute right-3.5 top-1/2 -translate-y-1/2 text-primary pointer-events-none w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </div>
                        <button type="submit" class="mt-8 w-full bg-primary text-primary-foreground rounded-xl py-3.5 font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_14px_rgba(139,160,35,0.32)] hover:opacity-90 transition-opacity">
                            Next <span>→</span>
                        </button>
                    </form>
                    <a href="/project-details?step=2" class="block w-full mt-3 text-[13px] text-muted-foreground hover:text-card-foreground text-center py-1">
                        ← Back
                    </a>

                    {{-- ── STEP 4: Material Quality ────────────────────── --}}
                    @elseif($step == 4)
                    <h1 class="font-playfair text-[26px] leading-tight text-secondary text-center">
                        What material quality<br>do you envision?
                    </h1>
                    <p class="text-[13px] text-muted-foreground text-center mt-3 leading-relaxed">
                        This helps us generate a more accurate<br>baseline estimate for your project.
                    </p>
                    <form action="/ai-estimation" method="POST" class="mt-8">
                        @csrf
                        <input type="hidden" name="project_name" value="{{ session('project_name') }}">
                        <input type="hidden" name="city" value="{{ session('city') }}">
                        <input type="hidden" name="renovation_type" value="{{ session('renovation_type') }}">
                        <input type="hidden" name="quality" id="quality-input" value="{{ old('quality', 'Standar') }}">

                        <div class="flex flex-col gap-3">
                            @foreach([['Ekonomi','Material standar lokal, hemat biaya'],['Standar','Keseimbangan kualitas dan harga'],['Premium','Material impor, kualitas tinggi']] as [$q, $desc])
                            <label class="w-full rounded-xl px-5 py-4 border-[1.5px] cursor-pointer transition-all
                                {{ old('quality', 'Standar') === $q ? 'bg-primary border-primary text-primary-foreground shadow-[0_2px_12px_rgba(139,160,35,0.28)]' : 'bg-[#F4F3EF] border-[#E0DFDA] text-card-foreground hover:border-primary/40' }}">
                                <input type="radio" name="quality" value="{{ $q }}" class="sr-only" {{ old('quality', 'Standar') === $q ? 'checked' : '' }}>
                                <p class="font-semibold text-sm">{{ $q }}</p>
                                <p class="text-[12px] mt-0.5 {{ old('quality', 'Standar') === $q ? 'text-primary-foreground/80' : 'text-muted-foreground' }}">{{ $desc }}</p>
                            </label>
                            @endforeach
                        </div>

                        <button type="submit" class="mt-8 w-full bg-primary text-primary-foreground rounded-xl py-3.5 font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_14px_rgba(139,160,35,0.32)] hover:opacity-90 transition-opacity">
                            Lanjutkan <span>→</span>
                        </button>
                    </form>
                    <a href="/project-details?step=3" class="block w-full mt-3 text-[13px] text-muted-foreground hover:text-card-foreground text-center py-1">
                        ← Back
                    </a>
                    @endif

                    {{-- Step counter --}}
                    <p class="text-center text-[10px] uppercase tracking-widest text-muted-foreground mt-5">
                        Step {{ $step }} of 4
                    </p>

                </div>
            </div>

        </div>
    </div>

    <p class="text-center text-[10px] uppercase tracking-widest text-muted-foreground pb-5">
        RenovaSim · Editorial Workflow © 2025
    </p>

</div>
@endsection
