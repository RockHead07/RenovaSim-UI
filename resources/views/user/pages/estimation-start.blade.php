@php
    $hasProjects = $projects->isNotEmpty();
@endphp

<x-user::layouts.dashboard title="RenovaSim — Mulai Estimasi">
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-lg">

            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                    <x-lucide-sparkles class="w-7 h-7 text-primary" />
                </div>
                <h1 class="font-['Playfair_Display'] italic text-2xl text-card-foreground">Mulai Estimasi</h1>
                <p class="font-['DM_Sans'] text-sm text-muted-foreground mt-1">
                    Pilih bagaimana estimasi ini akan disimpan
                </p>
            </div>

            <div class="space-y-3">

                {{-- Opsi 1: Tambah ke project yang ada --}}
                @if($hasProjects)
                    <div x-data="{ open: false }" class="bg-card rounded-2xl border border-border shadow-sm overflow-hidden">
                        <button @click="open = !open"
                            class="w-full flex items-center justify-between px-5 py-4 hover:bg-muted/40 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                                    <x-lucide-folder-open class="w-5 h-5 text-primary" />
                                </div>
                                <div class="text-left">
                                    <p class="font-['DM_Sans'] font-semibold text-sm text-card-foreground">
                                        Tambah ke project yang ada
                                    </p>
                                    <p class="font-['DM_Sans'] text-xs text-muted-foreground">
                                        Pilih salah satu project milikmu
                                    </p>
                                </div>
                            </div>
                            <x-lucide-chevron-down class="w-4 h-4 text-muted-foreground transition-transform"
                                ::class="open ? 'rotate-180' : ''" />
                        </button>

                        <div x-show="open" x-collapse class="border-t border-border" style="display: none">
                            <div class="p-3 space-y-2">
                                @foreach($projects as $project)
                                    <a href="{{ route('user.project.add-estimation', $project->id) }}"
                                       class="flex items-center justify-between px-4 py-3 rounded-xl bg-muted/40 hover:bg-primary/10 hover:border-primary/30 border border-transparent transition-all group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                                <span class="text-primary font-semibold text-xs">
                                                    {{ strtoupper(substr($project->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="font-['DM_Sans'] font-medium text-sm text-card-foreground">
                                                    {{ $project->name }}
                                                </p>
                                                <p class="font-['DM_Sans'] text-xs text-muted-foreground capitalize">
                                                    {{ $project->location ?? '—' }} · {{ $project->estimations_count }} estimasi
                                                </p>
                                            </div>
                                        </div>
                                        <x-lucide-arrow-right class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Opsi 2: Buat project baru --}}
                <a href="{{ route('user.project.setup') }}"
                   class="flex items-center gap-3 px-5 py-4 bg-card rounded-2xl border border-border shadow-sm hover:border-primary/40 hover:bg-primary/5 transition-all group">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                        <x-lucide-plus-circle class="w-5 h-5 text-green-600" />
                    </div>
                    <div class="flex-1">
                        <p class="font-['DM_Sans'] font-semibold text-sm text-card-foreground">Buat project baru</p>
                        <p class="font-['DM_Sans'] text-xs text-muted-foreground">
                            Mulai project renovasi baru dari awal
                        </p>
                    </div>
                    <x-lucide-arrow-right class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                </a>

                {{-- Opsi 3: Estimasi cepat --}}
                <a href="{{ route('user.estimation.quick') }}"
                   class="flex items-center gap-3 px-5 py-4 bg-card rounded-2xl border border-border shadow-sm hover:border-muted-foreground/30 hover:bg-muted/40 transition-all group">
                    <div class="w-10 h-10 rounded-xl bg-muted flex items-center justify-center shrink-0">
                        <x-lucide-zap class="w-5 h-5 text-muted-foreground" />
                    </div>
                    <div class="flex-1">
                        <p class="font-['DM_Sans'] font-semibold text-sm text-card-foreground">Estimasi cepat</p>
                        <p class="font-['DM_Sans'] text-xs text-muted-foreground">
                            Hitung estimasi tanpa menyimpan ke project
                        </p>
                    </div>
                    <x-lucide-arrow-right class="w-4 h-4 text-muted-foreground group-hover:text-card-foreground transition-colors" />
                </a>

            </div>

            {{-- Back --}}
            <div class="text-center mt-6">
                <a href="{{ route('dashboard') }}"
                   class="font-['DM_Sans'] text-xs text-muted-foreground hover:text-card-foreground transition-colors">
                    ← Kembali ke Dashboard
                </a>
            </div>

        </div>
    </div>
</x-user::layouts.dashboard>
