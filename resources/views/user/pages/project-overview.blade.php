@php
    $totalCostMin = $project->estimations->sum('cost_min');
    $totalCostMax = $project->estimations->sum('cost_max');
@endphp

<x-user::layouts.dashboard title="RenovaSim – Project Overview">
    <div class="flex-1 py-6 px-4">
        <div class="max-w-[920px] mx-auto">

            {{-- Flash success --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Page Title --}}
            <div class="flex items-center gap-3 mb-6">
                <button onclick="history.back()" class="text-card-foreground hover:opacity-70 transition-opacity">
                    <x-lucide-arrow-left class="w-5 h-5" />
                </button>
                <div>
                    <p class="font-['Playfair_Display'] italic text-xl text-card-foreground">
                        Project Overview: {{ $project->name }}
                    </p>
                    <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground">
                        Manage your target total for this renovation
                    </p>
                </div>
            </div>

            {{-- Main content --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- LEFT --}}
                <div class="md:col-span-2 flex flex-col gap-6">

                    {{-- Project Info --}}
                    <div class="bg-card rounded-2xl shadow-[0_1px_4px_rgba(0,0,0,0.06)]"
                         x-data="{ open: false }">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-4">
                            <div class="flex items-center gap-2">
                                <x-lucide-clipboard-list class="w-4 h-4 text-muted-foreground" />
                                <span class="font-['DM_Sans'] font-medium text-sm text-card-foreground">Project Description</span>
                            </div>
                            <x-lucide-chevron-down class="w-4 h-4 text-muted-foreground transition-transform" ::class="open ? 'rotate-180' : ''" />
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-4">
                            <p class="font-['DM_Sans'] text-sm text-muted-foreground leading-relaxed">
                                {{ $project->description ?? 'Tidak ada deskripsi.' }}
                            </p>
                        </div>
                    </div>

                    {{-- Estimations List --}}
                    <div class="bg-card rounded-2xl p-6 shadow-[0_2px_8px_rgba(0,0,0,0.07)]">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="font-['Playfair_Display'] italic text-xl text-card-foreground">Daftar Estimasi</h2>
                                <p class="font-['DM_Sans'] text-sm text-muted-foreground mt-0.5">
                                    {{ $project->estimations_count }} estimasi tersimpan
                                </p>
                            </div>
                        </div>

                        @if($project->estimations->isEmpty())
                            <div class="text-center py-8 text-muted-foreground">
                                <x-lucide-inbox class="w-10 h-10 mx-auto mb-2 opacity-40" />
                                <p class="font-['DM_Sans'] text-sm">Belum ada estimasi. Mulai dengan klik "+ Tambah Estimasi".</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($project->estimations as $estimation)
                                    <div class="flex items-center justify-between p-4 bg-muted/40 rounded-xl border border-border">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center">
                                                <x-lucide-hammer class="w-4 h-4 text-primary" />
                                            </div>
                                            <div>
                                                <p class="font-['DM_Sans'] font-semibold text-sm text-card-foreground capitalize">
                                                    {{ str_replace('_', ' ', $estimation->label) }}
                                                </p>
                                                <p class="font-['DM_Sans'] text-xs text-muted-foreground">
                                                    {{ $estimation->area ? $estimation->area . ' m²' : '' }}
                                                    {{ $estimation->area && $estimation->mode ? ' · ' : '' }}
                                                    {{ $estimation->mode ? ucfirst($estimation->mode) : '' }}
                                                    @if($estimation->confidence_label)
                                                        · Kepercayaan: {{ $estimation->confidence_label }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-['DM_Sans'] font-semibold text-sm text-card-foreground">
                                                {{ $estimation->cost_display }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Total RAB --}}
                            <div class="mt-4 pt-4 border-t border-border flex items-center justify-between">
                                <p class="font-['DM_Sans'] text-sm font-semibold text-card-foreground">Total RAB (estimasi minimum)</p>
                                <p class="font-['Playfair_Display'] italic text-lg font-bold text-primary">
                                    {{ format_rp($totalCostMin) }}
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2.5 mt-4">
                                <a href="{{ route('user.project.rab', $project->id) }}"
                                   class="bg-primary text-primary-foreground font-['DM_Sans'] font-medium text-sm rounded-lg px-5 py-2.5 hover:opacity-90 transition-opacity inline-flex items-center gap-2">
                                    <x-lucide-file-spreadsheet class="w-4 h-4" />
                                    Lihat RAB Lengkap
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- RIGHT --}}
                <div class="flex flex-col gap-4">
                    {{-- Project Summary --}}
                    <div class="bg-card rounded-2xl p-5 shadow-[0_1px_4px_rgba(0,0,0,0.06)]">
                        <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground mb-3">Project Summary</p>
                        <div class="space-y-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-muted flex items-center justify-center">
                                    <x-lucide-map-pin class="w-[13px] h-[13px] text-muted-foreground" />
                                </div>
                                <div>
                                    <p class="font-['DM_Sans'] text-[11px] text-muted-foreground">Location</p>
                                    <p class="font-['DM_Sans'] text-sm font-medium text-card-foreground capitalize">
                                        {{ $project->location ?? '—' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-muted flex items-center justify-center">
                                    <x-lucide-hammer class="w-[13px] h-[13px] text-muted-foreground" />
                                </div>
                                <div>
                                    <p class="font-['DM_Sans'] text-[11px] text-muted-foreground">Tipe Bangunan</p>
                                    <p class="font-['DM_Sans'] text-sm font-medium text-card-foreground capitalize">
                                        {{ $project->building_type ?? '—' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-muted flex items-center justify-center">
                                    <x-lucide-layers class="w-[13px] h-[13px] text-muted-foreground" />
                                </div>
                                <div>
                                    <p class="font-['DM_Sans'] text-[11px] text-muted-foreground">Total Estimasi</p>
                                    <p class="font-['DM_Sans'] text-sm font-medium text-card-foreground">
                                        {{ $project->estimations_count }} item
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-muted flex items-center justify-center">
                                    <x-lucide-banknote class="w-[13px] h-[13px] text-muted-foreground" />
                                </div>
                                <div>
                                    <p class="font-['DM_Sans'] text-[11px] text-muted-foreground">Total Biaya Min</p>
                                    <p class="font-['DM_Sans'] text-sm font-medium text-primary">
                                        {{ format_rp($totalCostMin) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Add Estimation CTA --}}
                    <a href="{{ route('user.project.add-estimation', $project->id) }}"
                       class="w-full bg-primary text-primary-foreground font-['DM_Sans'] font-semibold text-sm rounded-xl py-3.5 flex items-center justify-center gap-2 hover:opacity-90 transition-opacity">
                        <x-lucide-plus class="w-4 h-4" />
                        Tambah Estimasi Baru
                    </a>

                    <a href="{{ route('user.project.rab', $project->id) }}"
                       class="w-full border-2 border-primary text-primary font-['DM_Sans'] font-semibold text-sm rounded-xl py-3.5 flex items-center justify-center gap-2 hover:bg-primary hover:text-primary-foreground transition-all">
                        <x-lucide-file-spreadsheet class="w-4 h-4" />
                        Lihat RAB Lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-user::layouts.dashboard>
