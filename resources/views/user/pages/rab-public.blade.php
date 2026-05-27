@php
    $grandAvg = ($rab['grand_min'] + $rab['grand_max']) / 2;
@endphp

<x-user::layouts.app title="RAB — {{ $project->name }} | RenovaSim">
    <div class="flex-1 py-10 px-4">
        <div class="max-w-[860px] mx-auto">

            {{-- Watermark header --}}
            <div class="flex items-center justify-between mb-8">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-['Playfair_Display'] italic text-lg font-bold text-primary">RenovaSim</span>
                        <span class="text-[10px] uppercase tracking-widest font-['DM_Sans'] text-muted-foreground">RAB Publik</span>
                    </div>
                    <h1 class="font-['Playfair_Display'] italic text-2xl text-card-foreground">
                        {{ $project->name }}
                    </h1>
                    @if($project->location)
                        <p class="font-['DM_Sans'] text-sm text-muted-foreground mt-0.5 capitalize">
                            {{ ucfirst($project->location) }}
                        </p>
                    @endif
                </div>
                <div class="text-right hidden sm:block">
                    <p class="font-['DM_Sans'] text-[11px] text-muted-foreground">Tanggal</p>
                    <p class="font-['DM_Sans'] text-sm font-medium text-card-foreground">{{ now()->format('d M Y') }}</p>
                    @if($share->visibility === 'public' && $owner)
                        <p class="font-['DM_Sans'] text-[11px] text-muted-foreground mt-1">oleh {{ $owner->name }}</p>
                    @endif
                </div>
            </div>

            {{-- Expired notice (shouldn't normally show due to controller check, but safety) --}}
            @if($share->isExpired())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 font-['DM_Sans'] text-sm">
                    Link ini sudah kadaluarsa.
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- LEFT: Table --}}
                <div class="lg:col-span-2">
                    @if(empty($rab['items']))
                        <div class="bg-card rounded-2xl p-10 shadow-[0_1px_4px_rgba(0,0,0,0.06)] flex flex-col items-center text-center">
                            <x-lucide-inbox class="w-10 h-10 text-muted-foreground mb-3 opacity-40" />
                            <p class="font-['DM_Sans'] text-sm text-muted-foreground">Tidak ada data RAB tersedia.</p>
                        </div>
                    @else
                        <div class="bg-card rounded-2xl shadow-[0_1px_4px_rgba(0,0,0,0.06)] overflow-hidden">
                            <div class="px-5 py-4 border-b border-border">
                                <p class="font-['DM_Sans'] font-semibold text-sm text-card-foreground">Rincian Item Pekerjaan</p>
                                <p class="font-['DM_Sans'] text-[11px] text-muted-foreground mt-0.5">RENCANA ANGGARAN BIAYA (RAB)</p>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm font-['DM_Sans']">
                                    <thead>
                                        <tr class="bg-primary text-primary-foreground text-xs uppercase tracking-wider">
                                            <th class="px-4 py-3 text-left font-semibold">No</th>
                                            <th class="px-4 py-3 text-left font-semibold">Item Pekerjaan</th>
                                            <th class="px-4 py-3 text-right font-semibold">Volume</th>
                                            <th class="px-4 py-3 text-left font-semibold">Sat.</th>
                                            <th class="px-4 py-3 text-right font-semibold">Harga Sat. Min</th>
                                            <th class="px-4 py-3 text-right font-semibold">Harga Sat. Max</th>
                                            <th class="px-4 py-3 text-right font-semibold">Total Min</th>
                                            <th class="px-4 py-3 text-right font-semibold">Total Max</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rab['items'] as $i => $item)
                                            <tr class="border-t border-border {{ $i % 2 === 0 ? 'bg-card' : 'bg-muted/30' }}">
                                                <td class="px-4 py-3 text-center text-muted-foreground">{{ $i + 1 }}</td>
                                                <td class="px-4 py-3 font-medium text-card-foreground capitalize">{{ $item['label'] }}</td>
                                                <td class="px-4 py-3 text-right text-muted-foreground">{{ number_format($item['area'], 2) }}</td>
                                                <td class="px-4 py-3 text-muted-foreground">m²</td>
                                                <td class="px-4 py-3 text-right">Rp {{ number_format($item['unit_price_min'], 0, ',', '.') }}</td>
                                                <td class="px-4 py-3 text-right">Rp {{ number_format($item['unit_price_max'], 0, ',', '.') }}</td>
                                                <td class="px-4 py-3 text-right font-medium text-card-foreground">
                                                    Rp {{ number_format($item['total_min'], 0, ',', '.') }}
                                                </td>
                                                <td class="px-4 py-3 text-right font-medium text-card-foreground">
                                                    Rp {{ number_format($item['total_max'], 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-primary/10 border-t-2 border-primary/30 font-semibold text-card-foreground">
                                            <td colspan="6" class="px-4 py-3 text-right uppercase tracking-wide text-xs">Total RAB</td>
                                            <td class="px-4 py-3 text-right text-primary font-bold">
                                                Rp {{ number_format($rab['grand_min'], 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-primary font-bold">
                                                Rp {{ number_format($rab['grand_max'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="px-5 py-3 border-t border-border space-y-1">
                                <p class="font-['DM_Sans'] text-[11px] text-muted-foreground italic">
                                    * Estimasi berdasarkan harga pasar rata-rata. Harga aktual dapat berbeda tergantung kondisi lapangan.
                                </p>
                                <p class="font-['DM_Sans'] text-[11px] text-muted-foreground">
                                    Generated by RenovaSim — {{ now()->format('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- RIGHT: Summary --}}
                <div class="flex flex-col gap-4">
                    <div class="bg-card rounded-2xl p-5 shadow-[0_1px_4px_rgba(0,0,0,0.06)]">
                        <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground mb-4">
                            Ringkasan Biaya
                        </p>
                        <div class="space-y-4">
                            <div class="p-4 bg-muted/40 rounded-xl">
                                <p class="font-['DM_Sans'] text-[11px] text-muted-foreground">Rata-rata Estimasi</p>
                                <p class="font-['Playfair_Display'] italic text-2xl font-bold text-primary mt-0.5">
                                    Rp {{ number_format($grandAvg, 0, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <p class="font-['DM_Sans'] text-[11px] text-muted-foreground">Minimum</p>
                                <p class="font-['DM_Sans'] text-base font-semibold text-card-foreground">
                                    Rp {{ number_format($rab['grand_min'], 0, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <p class="font-['DM_Sans'] text-[11px] text-muted-foreground">Maximum</p>
                                <p class="font-['DM_Sans'] text-base font-semibold text-card-foreground">
                                    Rp {{ number_format($rab['grand_max'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-card rounded-2xl p-5 shadow-[0_1px_4px_rgba(0,0,0,0.06)]">
                        <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground mb-3">
                            Info Project
                        </p>
                        <div class="space-y-2.5">
                            @if($project->location)
                                <div class="flex items-center gap-2">
                                    <x-lucide-map-pin class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
                                    <span class="font-['DM_Sans'] text-sm text-card-foreground capitalize">{{ ucfirst($project->location) }}</span>
                                </div>
                            @endif
                            <div class="flex items-center gap-2">
                                <x-lucide-layers class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
                                <span class="font-['DM_Sans'] text-sm text-card-foreground">{{ count($rab['items']) }} item pekerjaan</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-lucide-clock class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
                                <span class="font-['DM_Sans'] text-sm text-muted-foreground">
                                    Berlaku hingga {{ $share->expires_at->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ url('/') }}"
                       class="w-full bg-primary/10 text-primary font-['DM_Sans'] font-medium text-sm rounded-xl py-3 flex items-center justify-center gap-2 hover:bg-primary hover:text-primary-foreground transition-colors">
                        <x-lucide-home class="w-4 h-4" />
                        Buka RenovaSim
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-user::layouts.app>
