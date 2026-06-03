{{-- pages.projects — daftar semua project estimasi user --}}
@php
    $jobTypeMap = config('renovasim.job_type_id') ?? [];
@endphp

<x-user::layouts.dashboard title="RenovaSim — Projects">
    <div class="flex-1 py-6 px-4">
        <div class="max-w-[920px] mx-auto"
             x-data="{
                 deleteModal: false,
                 pendingName: '',
                 pendingAction: '',
                 openDelete(name, action) {
                     this.pendingName = name;
                     this.pendingAction = action;
                     this.deleteModal = true;
                 },
                 submitDelete() {
                     this.$refs.deleteForm.action = this.pendingAction;
                     this.$refs.deleteForm.submit();
                 }
             }">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="font-['Playfair_Display'] italic text-xl text-card-foreground">
                        Projects
                    </h1>
                    <p class="font-['DM_Sans'] text-[11px] uppercase tracking-[0.15em] text-muted-foreground mt-0.5">
                        Daftar estimasi renovasi yang sudah kamu buat
                    </p>
                </div>
                <a href="{{ route('user.project.setup') }}"
                   class="inline-flex items-center gap-2 bg-primary text-primary-foreground font-['DM_Sans'] font-medium text-sm rounded-xl px-4 py-2.5 hover:opacity-90 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Project
                </a>
            </div>

            {{-- Empty state --}}
            @if($projects->isEmpty())
                <div class="bg-card rounded-2xl shadow-[0_1px_4px_rgba(0,0,0,0.06)] flex flex-col items-center justify-center py-16 px-6 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-muted flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                        </svg>
                    </div>
                    <p class="font-['Playfair_Display'] italic text-lg text-card-foreground">Belum ada project</p>
                    <p class="font-['DM_Sans'] text-sm text-muted-foreground mt-1 max-w-xs leading-relaxed">
                        Mulai estimasi renovasimu dan simpan sebagai project untuk melanjutkannya kapan saja.
                    </p>
                    <a href="{{ route('user.project.setup') }}"
                       class="mt-5 inline-flex items-center gap-2 bg-primary text-primary-foreground font-['DM_Sans'] font-medium text-sm rounded-xl px-5 py-2.5 hover:opacity-90 transition-opacity">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Buat Project
                    </a>
                </div>

            {{-- Project grid --}}
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($projects as $project)
                        @php
                            $projectId = $project->id;
                            $projectName = $project->name ?? 'Untitled';
                            $projectLocation = $project->location ?? null;
                            $projectStatus = $project->status ?? 'draft';
                            $projectCreatedAt = $project->created_at ?? null;

                            $estimations = $project->estimations;

                            $costMin = (int) $estimations->sum('cost_min');
                            $costMax = (int) $estimations->sum('cost_max');
                            $costAvg = $costMin > 0 ? (int)(($costMin + $costMax) / 2) : 0;

                            $uniqueJobKeys = $estimations->pluck('job_type')->unique()->filter()->values();
                            $jobLabel = null;
                            if ($uniqueJobKeys->isNotEmpty()) {
                                $firstJobKey = $uniqueJobKeys->first();
                                $firstJobLabel = $jobTypeMap[$firstJobKey] ?? $firstJobKey;
                                if ($uniqueJobKeys->count() === 1) {
                                    $jobLabel = $firstJobLabel;
                                } else {
                                    $jobLabel = $firstJobLabel . ' & ' . ($uniqueJobKeys->count() - 1) . ' lainnya';
                                }
                            }

                            $uniqueQualities = $estimations->pluck('quality')->unique()->filter()->values();
                            $qualityLabel = null;
                            if ($uniqueQualities->isNotEmpty()) {
                                $qualityLabel = $uniqueQualities->map(fn($q) => ucfirst($q))->implode(' / ');
                            }

                            $confScore = $estimations->isNotEmpty() ? (float) $estimations->avg('confidence_score') : 0;
                            $confPct = $confScore <= 1 ? (int)round($confScore * 100) : (int)$confScore;

                            $confLabel = null;
                            if ($estimations->isNotEmpty()) {
                                if ($confScore >= 0.8) {
                                    $confLabel = 'Tinggi';
                                } elseif ($confScore >= 0.5) {
                                    $confLabel = 'Sedang';
                                } else {
                                    $confLabel = 'Rendah';
                                }
                            }

                            $confColor = match($confLabel) {
                                'Tinggi' => '#8BA023',
                                'Sedang' => '#d4941a',
                                default  => '#e05555',
                            };
                            $statusColor = match($projectStatus) {
                                'estimated' => 'bg-primary/10 text-primary',
                                'completed' => 'bg-[hsl(210,80%,94%)] text-[hsl(210,80%,40%)]',
                                default     => 'bg-muted text-muted-foreground',
                            };
                            $statusLabel = match($projectStatus) {
                                'estimated' => 'Estimasi',
                                'completed' => 'Selesai',
                                default     => 'Draft',
                            };
                            $createdFormatted = $projectCreatedAt 
                                ? date('d M Y', strtotime($projectCreatedAt))
                                : 'N/A';
                        @endphp
                        
                        @if($projectId)
                            <div class="bg-card rounded-2xl shadow-[0_1px_4px_rgba(0,0,0,0.06)] flex flex-col overflow-hidden hover:shadow-[0_4px_12px_rgba(0,0,0,0.09)] transition-shadow">

                                {{-- Card top accent --}}
                                <div class="h-1 w-full bg-primary/40"></div>

                                <div class="p-5 flex flex-col gap-4 flex-1">

                                    {{-- Name + status --}}
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="font-['Playfair_Display'] italic text-[16px] text-card-foreground leading-snug">
                                            {{ $projectName }}
                                        </p>
                                        <span class="shrink-0 text-[10px] uppercase tracking-wider font-medium px-2 py-0.5 rounded-full {{ $statusColor }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>

                                    {{-- Meta row --}}
                                    <div class="flex flex-col gap-1.5">
                                        @if($projectLocation)
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3 h-3 text-muted-foreground shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                <span class="font-['DM_Sans'] text-[12px] text-muted-foreground capitalize">
                                                    {{ ucfirst($projectLocation) }}
                                                </span>
                                            </div>
                                        @endif
                                        @if($jobLabel)
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3 h-3 text-muted-foreground shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                                                </svg>
                                                <span class="font-['DM_Sans'] text-[12px] text-muted-foreground">{{ $jobLabel }}</span>
                                            </div>
                                        @endif
                                        @if($qualityLabel)
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3 h-3 text-muted-foreground shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                                </svg>
                                                <span class="font-['DM_Sans'] text-[12px] text-muted-foreground capitalize">
                                                    {{ $qualityLabel }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Cost --}}
                                    @if($costAvg > 0)
                                        <div class="bg-muted/50 rounded-xl px-3.5 py-3">
                                            <p class="font-['DM_Sans'] text-[10px] uppercase tracking-wider text-muted-foreground mb-0.5">
                                                Estimasi Biaya
                                            </p>
                                            <p class="font-['Playfair_Display'] italic text-[20px] text-card-foreground leading-tight">
                                                Rp {{ number_format($costAvg, 0, ',', '.') }}
                                            </p>
                                            @if($costMin !== $costMax)
                                                <p class="font-['DM_Sans'] text-[11px] text-muted-foreground mt-0.5">
                                                    Rp {{ number_format($costMin, 0, ',', '.') }} – Rp {{ number_format($costMax, 0, ',', '.') }}
                                                </p>
                                            @endif

                                            {{-- Confidence bar --}}
                                            @if($confLabel)
                                                <div class="mt-2.5">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="font-['DM_Sans'] text-[10px] text-muted-foreground uppercase tracking-wider">
                                                            Confidence
                                                        </span>
                                                        <span class="font-['DM_Sans'] text-[11px] font-semibold"
                                                              style="color: {{ $confColor }}">
                                                            {{ $confLabel }}
                                                        </span>
                                                    </div>
                                                    <div class="h-1.5 w-full bg-border rounded-full overflow-hidden">
                                                        <div class="h-full rounded-full"
                                                             style="width: {{ $confPct }}%; background-color: {{ $confColor }};"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="bg-muted/40 rounded-xl px-3.5 py-3">
                                            <p class="font-['DM_Sans'] text-[12px] text-muted-foreground italic">
                                                Belum ada data estimasi
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Footer --}}
                                    <div class="flex items-center justify-between mt-auto pt-1">
                                        <div class="flex items-center gap-1.5 text-muted-foreground">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="font-['DM_Sans'] text-[11px]">
                                                {{ $createdFormatted }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- CTA --}}
                                <div class="px-5 pb-5 flex gap-2">
                                    <a href="{{ route('user.projects.show', $projectId) }}"
                                       class="flex-1 inline-flex items-center justify-center gap-2 bg-primary/10 text-primary font-['DM_Sans'] font-medium text-sm rounded-xl py-2.5 hover:bg-primary hover:text-primary-foreground transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Lihat & Lanjutkan
                                    </a>

                                    <button type="button"
                                            @click="openDelete('{{ addslashes($projectName) }}', '{{ route('user.projects.destroy', $projectId) }}')"
                                            class="w-10 h-10 flex items-center justify-center rounded-xl border border-border text-muted-foreground hover:border-red-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                            title="Hapus project">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>

                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Hidden form for delete submission --}}
            <form x-ref="deleteForm" method="POST" style="display:none">
                @csrf
                @method('DELETE')
            </form>

            {{-- Delete confirmation modal --}}
            <div x-show="deleteModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="deleteModal = false"
                 class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/40 backdrop-blur-sm"
                 style="display:none">
                <div x-show="deleteModal"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="bg-card rounded-2xl shadow-xl border border-border w-full max-w-sm p-6">

                    {{-- Icon --}}
                    <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>

                    {{-- Text --}}
                    <p class="font-['DM_Sans'] font-semibold text-[15px] text-card-foreground mb-1">Hapus Project?</p>
                    <p class="font-['DM_Sans'] text-[13px] text-muted-foreground leading-relaxed">
                        Project <span class="font-semibold text-card-foreground" x-text="'&quot;' + pendingName + '&quot;'"></span>
                        dan semua estimasi di dalamnya akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
                    </p>

                    {{-- Actions --}}
                    <div class="flex gap-3 mt-5 justify-end">
                        <button type="button" @click="deleteModal = false"
                                class="px-4 py-2 text-sm font-['DM_Sans'] font-medium text-muted-foreground hover:text-card-foreground bg-muted/60 hover:bg-muted rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="button" @click="submitDelete()"
                                class="px-4 py-2 text-sm font-['DM_Sans'] font-medium text-white bg-red-500 hover:bg-red-600 rounded-xl transition-colors">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-user::layouts.dashboard>
