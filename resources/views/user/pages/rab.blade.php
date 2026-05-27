@php
    $grandAvg = ($rab['grand_min'] + $rab['grand_max']) / 2;
@endphp

<x-user::layouts.dashboard title="RAB — {{ $project->name }}">
    <div class="flex-1 py-6 px-4">
        <div class="max-w-[860px] mx-auto">

            {{-- Header --}}
            <div class="flex items-center gap-3 mb-6">
                <a href="{{ route('user.projects') }}" class="text-card-foreground hover:opacity-70 transition-opacity">
                    <x-lucide-arrow-left class="w-5 h-5" />
                </a>
                <div class="flex-1 min-w-0">
                    <p class="font-['Playfair_Display'] italic text-xl text-card-foreground truncate">
                        RAB — {{ $project->name }}
                    </p>
                    <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground">
                        Rencana Anggaran Biaya
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('user.project.rab.export', $project->id) }}"
                       class="inline-flex items-center gap-1.5 bg-primary/10 text-primary font-['DM_Sans'] font-medium text-sm rounded-xl px-4 py-2.5 hover:bg-primary hover:text-primary-foreground transition-colors">
                        <x-lucide-download class="w-4 h-4" />
                        Export XLSX
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-5 flex items-start gap-2.5 bg-[hsl(120,60%,96%)] border border-[hsl(120,60%,70%)] rounded-xl px-4 py-3">
                    <x-lucide-check class="w-4 h-4 text-[hsl(120,60%,35%)] shrink-0 mt-0.5" />
                    <p class="font-['DM_Sans'] text-[13px] text-card-foreground">{{ session('success') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- LEFT: RAB Table --}}
                <div class="lg:col-span-2 flex flex-col gap-4">

                    @if(empty($rab['items']))
                        <div class="bg-card rounded-2xl p-10 shadow-[0_1px_4px_rgba(0,0,0,0.06)] flex flex-col items-center text-center">
                            <x-lucide-inbox class="w-10 h-10 text-muted-foreground mb-3 opacity-40" />
                            <p class="font-['Playfair_Display'] italic text-card-foreground">Belum ada data RAB</p>
                            <p class="font-['DM_Sans'] text-sm text-muted-foreground mt-1">
                                Tambahkan estimasi ke project ini terlebih dahulu.
                            </p>
                            <a href="{{ route('user.project.add-estimation', $project->id) }}"
                               class="mt-4 inline-flex items-center gap-2 bg-primary text-primary-foreground font-['DM_Sans'] font-medium text-sm rounded-xl px-5 py-2.5 hover:opacity-90 transition-opacity">
                                <x-lucide-plus class="w-4 h-4" />
                                Tambah Estimasi
                            </a>
                        </div>
                    @else
                        {{-- RAB Table --}}
                        <div class="bg-card rounded-2xl shadow-[0_1px_4px_rgba(0,0,0,0.06)] overflow-hidden">
                            <div class="px-5 py-4 border-b border-border flex items-center gap-2">
                                <x-lucide-table-2 class="w-4 h-4 text-muted-foreground" />
                                <span class="font-['DM_Sans'] font-medium text-sm text-card-foreground">Rincian Item Pekerjaan</span>
                            </div>

                            <table class="w-full text-sm font-['DM_Sans']">
                                <thead>
                                    <tr class="bg-primary text-primary-foreground text-[11px] uppercase tracking-wider">
                                        <th class="px-3 py-3 text-center font-semibold w-8">No</th>
                                        <th class="px-3 py-3 text-left font-semibold">Item Pekerjaan</th>
                                        <th class="px-3 py-3 text-right font-semibold w-16">Vol</th>
                                        <th class="px-3 py-3 text-left font-semibold w-10">Sat.</th>
                                        <th class="px-3 py-3 text-right font-semibold">Harga/m²</th>
                                        <th class="px-3 py-3 text-right font-semibold">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rab['items'] as $i => $item)
                                        <tr class="border-t border-border {{ $i % 2 === 0 ? 'bg-card' : 'bg-muted/30' }} hover:bg-primary/5 transition-colors">
                                            <td class="px-3 py-3 text-muted-foreground text-center text-xs">{{ $i + 1 }}</td>
                                            <td class="px-3 py-3 font-medium text-card-foreground capitalize">{{ $item['label'] }}</td>
                                            <td class="px-3 py-3 text-right text-muted-foreground text-xs">{{ number_format($item['area'], 2) }}</td>
                                            <td class="px-3 py-3 text-muted-foreground text-xs">m²</td>
                                            <td class="px-3 py-3 text-right text-card-foreground text-xs">
                                                <span class="block">Rp {{ number_format($item['unit_price_min'], 0, ',', '.') }}</span>
                                                <span class="block text-muted-foreground">– Rp {{ number_format($item['unit_price_max'], 0, ',', '.') }}</span>
                                            </td>
                                            <td class="px-3 py-3 text-right font-semibold text-card-foreground text-xs">
                                                <span class="block text-primary">Rp {{ number_format($item['total_min'], 0, ',', '.') }}</span>
                                                <span class="block text-muted-foreground">– Rp {{ number_format($item['total_max'], 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-primary/10 border-t-2 border-primary/30 font-semibold">
                                        <td colspan="4" class="px-3 py-3 text-right text-xs uppercase tracking-wide text-card-foreground">Total RAB</td>
                                        <td class="px-3 py-3"></td>
                                        <td class="px-3 py-3 text-right text-xs">
                                            <span class="block text-primary font-bold">Rp {{ number_format($rab['grand_min'], 0, ',', '.') }}</span>
                                            <span class="block text-muted-foreground">– Rp {{ number_format($rab['grand_max'], 0, ',', '.') }}</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="px-5 py-3 border-t border-border">
                                <p class="font-['DM_Sans'] text-[11px] text-muted-foreground italic">
                                    * Estimasi berdasarkan harga pasar rata-rata. Harga aktual dapat berbeda tergantung kondisi lapangan.
                                </p>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- RIGHT: Summary + Share --}}
                <div class="flex flex-col gap-4">

                    {{-- Cost Summary --}}
                    <div class="bg-card rounded-2xl p-5 shadow-[0_1px_4px_rgba(0,0,0,0.06)]">
                        <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground mb-3">
                            Ringkasan Biaya
                        </p>
                        <div class="space-y-3">
                            <div>
                                <p class="font-['DM_Sans'] text-[11px] text-muted-foreground">Estimasi Minimum</p>
                                <p class="font-['Playfair_Display'] italic text-lg font-bold text-card-foreground">
                                    Rp {{ number_format($rab['grand_min'], 0, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <p class="font-['DM_Sans'] text-[11px] text-muted-foreground">Estimasi Maximum</p>
                                <p class="font-['Playfair_Display'] italic text-lg font-bold text-card-foreground">
                                    Rp {{ number_format($rab['grand_max'], 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="pt-2 border-t border-border">
                                <p class="font-['DM_Sans'] text-[11px] text-muted-foreground">Rata-rata</p>
                                <p class="font-['Playfair_Display'] italic text-xl font-bold text-primary">
                                    Rp {{ number_format($grandAvg, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Project Info --}}
                    <div class="bg-card rounded-2xl p-5 shadow-[0_1px_4px_rgba(0,0,0,0.06)]">
                        <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground mb-3">
                            Info Project
                        </p>
                        <div class="space-y-2.5">
                            <div class="flex items-center gap-2">
                                <x-lucide-map-pin class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
                                <span class="font-['DM_Sans'] text-sm text-card-foreground capitalize">
                                    {{ $project->location ?? '—' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-lucide-layers class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
                                <span class="font-['DM_Sans'] text-sm text-card-foreground">
                                    {{ count($rab['items']) }} item pekerjaan
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-lucide-calendar class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
                                <span class="font-['DM_Sans'] text-sm text-muted-foreground">
                                    {{ now()->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Share Card --}}
                    <div class="bg-card rounded-2xl p-5 shadow-[0_1px_4px_rgba(0,0,0,0.06)]"
                         x-data="rabShare({{ $project->id }})">
                        <div class="flex items-center gap-2 mb-3">
                            <x-lucide-share-2 class="w-4 h-4 text-muted-foreground" />
                            <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground">
                                Bagikan RAB
                            </p>
                        </div>

                        @if($share)
                            <div class="mb-3 p-3 bg-muted/50 rounded-xl border border-border">
                                <p class="font-['DM_Sans'] text-[11px] text-muted-foreground mb-1">Link aktif hingga {{ $share->expires_at->format('d M Y') }}</p>
                                <div class="flex items-center gap-2">
                                    <input type="text" readonly
                                           value="{{ route('rab.public', $share->token) }}"
                                           class="flex-1 bg-transparent font-['DM_Sans'] text-[12px] text-card-foreground min-w-0 truncate outline-none"
                                           onclick="this.select()" />
                                    <button onclick="navigator.clipboard.writeText('{{ route('rab.public', $share->token) }}')"
                                            class="shrink-0 text-primary hover:opacity-70 transition-opacity"
                                            title="Salin link">
                                        <x-lucide-copy class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-col gap-2">
                            <label class="font-['DM_Sans'] text-[11px] text-muted-foreground">Visibilitas</label>
                            <div class="flex gap-2">
                                <label class="flex-1 flex items-center gap-2 p-2.5 rounded-lg border border-border cursor-pointer hover:border-primary/50 transition-colors"
                                       :class="visibility === 'public' ? 'border-primary bg-primary/5' : ''">
                                    <input type="radio" name="visibility" value="public" x-model="visibility" class="hidden">
                                    <x-lucide-globe class="w-3.5 h-3.5 text-muted-foreground" />
                                    <span class="font-['DM_Sans'] text-[12px]">Publik</span>
                                </label>
                                <label class="flex-1 flex items-center gap-2 p-2.5 rounded-lg border border-border cursor-pointer hover:border-primary/50 transition-colors"
                                       :class="visibility === 'private' ? 'border-primary bg-primary/5' : ''">
                                    <input type="radio" name="visibility" value="private" x-model="visibility" class="hidden">
                                    <x-lucide-lock class="w-3.5 h-3.5 text-muted-foreground" />
                                    <span class="font-['DM_Sans'] text-[12px]">Privat</span>
                                </label>
                            </div>
                            <button @click="generate()"
                                    :disabled="loading"
                                    class="w-full bg-primary text-primary-foreground font-['DM_Sans'] font-medium text-sm rounded-xl py-2.5 hover:opacity-90 transition-opacity disabled:opacity-60 flex items-center justify-center gap-2">
                                <x-lucide-link class="w-4 h-4" />
                                <span x-text="loading ? 'Generating...' : 'Generate Link'"></span>
                            </button>
                        </div>

                        {{-- Result --}}
                        <div x-show="shareUrl" x-cloak class="mt-3 p-3 bg-muted/50 rounded-xl border border-primary/30">
                            <p class="font-['DM_Sans'] text-[11px] text-muted-foreground mb-1">
                                Link berlaku hingga <span x-text="expiresAt" class="font-semibold text-card-foreground"></span>
                            </p>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly :value="shareUrl"
                                       class="flex-1 bg-transparent font-['DM_Sans'] text-[12px] text-card-foreground min-w-0 truncate outline-none"
                                       @click="$el.select()" />
                                <button @click="copy()"
                                        class="shrink-0 text-primary hover:opacity-70 transition-opacity">
                                    <x-lucide-copy class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <a href="{{ route('user.project.add-estimation', $project->id) }}"
                       class="w-full bg-muted text-card-foreground font-['DM_Sans'] font-medium text-sm rounded-xl py-3 flex items-center justify-center gap-2 hover:bg-muted/80 transition-colors">
                        <x-lucide-plus class="w-4 h-4" />
                        Tambah Estimasi
                    </a>

                </div>
            </div>

        </div>
    </div>

</x-user::layouts.dashboard>

<script>
function rabShare(projectId) {
    return {
        projectId,
        visibility: 'public',
        loading: false,
        shareUrl: null,
        expiresAt: null,

        async generate() {
            this.loading = true;
            try {
                const res = await fetch(`/user/project/${projectId}/rab/share`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ visibility: this.visibility }),
                });
                const data = await res.json();
                this.shareUrl  = data.url;
                this.expiresAt = data.expires_at;
            } catch (e) {
                alert('Gagal generate link. Coba lagi.');
            } finally {
                this.loading = false;
            }
        },

        copy() {
            if (this.shareUrl) navigator.clipboard.writeText(this.shareUrl);
        },
    };
}
</script>
