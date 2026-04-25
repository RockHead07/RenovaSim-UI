{{-- pages.project-rab — port of ProjectRAB.tsx --}}
@php
    /** @var string $projectId */
    $projects = config('renovasim.projects');
    $project  = collect($projects)->firstWhere('id', $projectId);

    $projectName    = request()->query('projectName',    $project['name'] ?? 'Untitled Project');
    $city           = request()->query('city',           '—');
    $renovationType = request()->query('renovationType', 'Residential Renovation');
    $quality        = request()->query('quality',        'Standard');
    $area           = (int) request()->query('area', 20);

    $totalCost    = (int) (request()->query('totalCost')    ?: ($project['totalCost']    ?? 8_750_000));
    $materialCost = (int) (request()->query('materialCost') ?: ($project['materialCost'] ?? 5_250_000));
    $laborCost    = (int) (request()->query('laborCost')    ?: ($project['laborCost']    ?? 3_500_000));

    $m = $materialCost; $l = $laborCost;
    $maxArea  = max(1, $area);
    $maxArea2 = max(1, $area * 1.2);
    $maxArea3 = max(1, $area * 2.4);

    $rawGroups = [
        ['category' => 'Persiapan', 'rows' => [
            ['uraian' => 'Pembersihan & pengukuran lokasi', 'satuan' => 'ls',  'volume' => 1,                                 'hargaSatuan' => (int) round(($m + $l) * 0.03)],
            ['uraian' => 'Bongkaran ringan & pembuangan puing', 'satuan' => 'm³', 'volume' => max(1, (int) round($area * 0.05)), 'hargaSatuan' => 250_000],
        ]],
        ['category' => 'Dinding', 'rows' => [
            ['uraian' => 'Plesteran & acian dinding', 'satuan' => 'm²', 'volume' => (int) round($area * 1.2), 'hargaSatuan' => (int) round(($m * 0.18 + $l * 0.20) / $maxArea2)],
            ['uraian' => 'Pemasangan list profil dinding', 'satuan' => 'm', 'volume' => (int) round($area * 0.8), 'hargaSatuan' => 45_000],
        ]],
        ['category' => 'Lantai', 'rows' => [
            ['uraian' => 'Pemasangan keramik / tiles', 'satuan' => 'm²', 'volume' => $area, 'hargaSatuan' => (int) round(($m * 0.45 + $l * 0.25) / $maxArea)],
            ['uraian' => 'Nat & finishing lantai',     'satuan' => 'm²', 'volume' => $area, 'hargaSatuan' => (int) round(($m * 0.04 + $l * 0.06) / $maxArea)],
        ]],
        ['category' => 'Pengecatan', 'rows' => [
            ['uraian' => 'Cat dasar (primer) dinding & plafon', 'satuan' => 'm²', 'volume' => (int) round($area * 2.4), 'hargaSatuan' => (int) round(($m * 0.08) / $maxArea3) + 18_000],
            ['uraian' => 'Cat finishing 2 lapis',                'satuan' => 'm²', 'volume' => (int) round($area * 2.4), 'hargaSatuan' => (int) round(($m * 0.10 + $l * 0.18) / $maxArea3) + 22_000],
        ]],
        ['category' => 'Finishing & Lain-lain', 'rows' => [
            ['uraian' => 'Pekerjaan instalasi listrik tambahan', 'satuan' => 'ttk', 'volume' => max(4, (int) round($area * 0.3)), 'hargaSatuan' => 175_000],
            ['uraian' => 'Pembersihan akhir & serah terima',     'satuan' => 'ls',  'volume' => 1, 'hargaSatuan' => (int) round(($m + $l) * 0.02)],
        ]],
    ];

    // Floor each hargaSatuan at 15_000, then number sequentially
    $counter = 0;
    $groups = [];
    $grandTotal = 0;
    foreach ($rawGroups as $g) {
        $rows = [];
        $subtotal = 0;
        foreach ($g['rows'] as $r) {
            $r['hargaSatuan'] = max(15_000, $r['hargaSatuan']);
            $r['no']          = ++$counter;
            $r['total']       = $r['volume'] * $r['hargaSatuan'];
            $subtotal        += $r['total'];
            $rows[]           = $r;
        }
        $groups[] = ['category' => $g['category'], 'rows' => $rows, 'subtotal' => $subtotal];
        $grandTotal += $subtotal;
    }
@endphp

<x-layouts.app title="RenovaSim — RAB" :hideNav="true" :hideFooter="false" footerClass="print:hidden">
    {{-- Top Navbar (custom — print:hidden) --}}
    <nav class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-4 print:hidden">
        <a href="/" class="font-['Playfair_Display'] italic text-xl text-card-foreground hover:opacity-80 transition-opacity">RenovaSim</a>
        <div class="flex items-center gap-3">
            <button class="w-8 h-8 rounded-full border border-border flex items-center justify-center hover:bg-muted transition-colors">
                <x-lucide-help-circle class="w-4 h-4 text-muted-foreground" />
            </button>
            <button class="w-8 h-8 rounded-full border border-border flex items-center justify-center hover:bg-muted transition-colors">
                <x-lucide-user class="w-4 h-4 text-muted-foreground" />
            </button>
        </div>
    </nav>

    <div class="flex-1 py-6 px-4">
        <div class="max-w-[980px] mx-auto">
            {{-- Back to Overview --}}
            <a
                href="{{ url('/project-overview?' . http_build_query([
                    'projectName' => $projectName, 'city' => $city, 'renovationType' => $renovationType,
                    'quality' => $quality, 'totalCost' => $totalCost,
                    'materialCost' => $materialCost, 'laborCost' => $laborCost,
                ])) }}"
                class="inline-flex items-center gap-2 text-card-foreground hover:opacity-70 transition-opacity mb-5 print:hidden"
            >
                <x-lucide-arrow-left class="w-4 h-4" />
                <span class="font-['DM_Sans'] text-sm font-medium">Back to Overview</span>
            </a>

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
                <div>
                    <p class="font-['DM_Sans'] text-[10px] uppercase tracking-[0.15em] text-muted-foreground">Rencana Anggaran Biaya</p>
                    <h1 class="font-['Playfair_Display'] italic text-2xl text-card-foreground mt-1">Detail RAB — {{ $projectName }}</h1>
                    <p class="font-['DM_Sans'] text-sm text-muted-foreground mt-1">{{ $city }} · {{ $renovationType }} · Quality: {{ $quality }}</p>
                </div>

                <div class="flex items-center gap-2 print:hidden">
                    <button
                        onclick="exportRabAsCsv()"
                        class="inline-flex items-center gap-1.5 border-[1.5px] border-primary text-primary bg-card rounded-lg px-3 py-2 font-['DM_Sans'] text-[13px] font-medium hover:bg-primary/5 transition-colors"
                    >
                        <x-lucide-file-spreadsheet class="w-[14px] h-[14px]" /> Export Excel
                    </button>
                    <button
                        onclick="window.print(); alert('Use your browser print dialog to save as PDF');"
                        class="inline-flex items-center gap-1.5 bg-primary text-primary-foreground rounded-lg px-3 py-2 font-['DM_Sans'] text-[13px] font-medium hover:opacity-90 transition-opacity"
                    >
                        <x-lucide-file-down class="w-[14px] h-[14px]" /> Download PDF
                    </button>
                </div>
            </div>

            {{-- Mobile cards --}}
            <div class="md:hidden flex flex-col gap-4">
                @foreach ($groups as $g)
                    <div class="bg-card rounded-2xl shadow-[0_2px_8px_rgba(0,0,0,0.07)] overflow-hidden">
                        <div class="bg-primary/10 px-4 py-2.5">
                            <p class="font-['DM_Sans'] text-[11px] uppercase tracking-[0.12em] font-semibold text-primary">{{ $g['category'] }}</p>
                        </div>
                        <div class="divide-y divide-border">
                            @foreach ($g['rows'] as $r)
                                <div class="p-4">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <p class="font-['DM_Sans'] text-[13px] font-medium text-card-foreground flex-1">
                                            <span class="text-muted-foreground mr-2">{{ $r['no'] }}.</span>{{ $r['uraian'] }}
                                        </p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-[12px]">
                                        <div>
                                            <p class="text-muted-foreground">Volume</p>
                                            <p class="text-card-foreground tabular-nums">{{ $r['volume'] }} {{ $r['satuan'] }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-muted-foreground">Harga satuan</p>
                                            <p class="text-card-foreground tabular-nums">{{ format_rp($r['hargaSatuan']) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-border">
                                        <span class="text-[11px] uppercase tracking-wider text-muted-foreground">Total</span>
                                        <span class="font-semibold text-sm text-card-foreground tabular-nums">{{ format_rp($r['total']) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="bg-primary/5 px-4 py-2.5 flex items-center justify-between">
                            <span class="font-['DM_Sans'] text-[12px] italic text-muted-foreground">Subtotal {{ $g['category'] }}</span>
                            <span class="font-['DM_Sans'] text-[12px] font-semibold text-card-foreground tabular-nums">{{ format_rp($g['subtotal']) }}</span>
                        </div>
                    </div>
                @endforeach
                <div class="bg-primary text-primary-foreground rounded-2xl px-4 py-4 flex items-center justify-between">
                    <span class="font-['Playfair_Display'] italic text-base font-bold">GRAND TOTAL</span>
                    <span class="font-['Playfair_Display'] italic text-base font-bold tabular-nums">{{ format_rp($grandTotal) }}</span>
                </div>
            </div>

            {{-- Desktop table --}}
            <div class="hidden md:block bg-card rounded-2xl shadow-[0_2px_8px_rgba(0,0,0,0.07)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-primary text-primary-foreground">
                                <th class="font-['DM_Sans'] text-[11px] uppercase tracking-[0.1em] font-semibold px-3 py-3 w-[50px]">No</th>
                                <th class="font-['DM_Sans'] text-[11px] uppercase tracking-[0.1em] font-semibold px-3 py-3">Uraian Pekerjaan</th>
                                <th class="font-['DM_Sans'] text-[11px] uppercase tracking-[0.1em] font-semibold px-3 py-3 w-[70px]">Satuan</th>
                                <th class="font-['DM_Sans'] text-[11px] uppercase tracking-[0.1em] font-semibold px-3 py-3 w-[80px] text-right">Volume</th>
                                <th class="font-['DM_Sans'] text-[11px] uppercase tracking-[0.1em] font-semibold px-3 py-3 w-[140px] text-right">Harga Satuan</th>
                                <th class="font-['DM_Sans'] text-[11px] uppercase tracking-[0.1em] font-semibold px-3 py-3 w-[160px] text-right">Total Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groups as $g)
                                <tr class="bg-primary/10">
                                    <td colspan="6" class="px-3 py-2 font-['DM_Sans'] text-[11px] uppercase tracking-[0.12em] font-semibold text-primary">{{ $g['category'] }}</td>
                                </tr>
                                @foreach ($g['rows'] as $i => $r)
                                    <tr class="border-b border-border {{ $i % 2 === 0 ? 'bg-card' : 'bg-muted/40' }}">
                                        <td class="px-3 py-2.5 font-['DM_Sans'] text-[13px] text-muted-foreground">{{ $r['no'] }}</td>
                                        <td class="px-3 py-2.5 font-['DM_Sans'] text-[13px] text-card-foreground">{{ $r['uraian'] }}</td>
                                        <td class="px-3 py-2.5 font-['DM_Sans'] text-[13px] text-muted-foreground">{{ $r['satuan'] }}</td>
                                        <td class="px-3 py-2.5 font-['DM_Sans'] text-[13px] text-card-foreground text-right tabular-nums">{{ $r['volume'] }}</td>
                                        <td class="px-3 py-2.5 font-['DM_Sans'] text-[13px] text-card-foreground text-right tabular-nums">{{ format_rp($r['hargaSatuan']) }}</td>
                                        <td class="px-3 py-2.5 font-['DM_Sans'] text-[13px] font-semibold text-card-foreground text-right tabular-nums">{{ format_rp($r['total']) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-primary/5">
                                    <td colspan="5" class="px-3 py-2 font-['DM_Sans'] text-[12px] text-right text-muted-foreground italic">Subtotal {{ $g['category'] }}</td>
                                    <td class="px-3 py-2 font-['DM_Sans'] text-[12px] text-right text-card-foreground font-semibold tabular-nums">{{ format_rp($g['subtotal']) }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-primary text-primary-foreground">
                                <td colspan="5" class="px-3 py-3.5 font-['Playfair_Display'] italic text-base font-bold text-right">GRAND TOTAL</td>
                                <td class="px-3 py-3.5 font-['Playfair_Display'] italic text-base font-bold text-right tabular-nums">{{ format_rp($grandTotal) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footnote --}}
            <p class="font-['DM_Sans'] text-[11px] text-muted-foreground mt-4 leading-relaxed">
                * Volume dihitung berdasarkan luas area proyek ({{ $area }} m²) dan rasio material/labor dari estimasi AI.
                Harga satuan termasuk material dan upah pemasangan. Estimasi total: {{ format_rp($totalCost) }}.
            </p>
        </div>
    </div>

    {{-- CSV export script --}}
    <script>
        const __rabRows = @json(collect($groups)->flatMap(fn($g) => collect($g['rows'])->map(fn($r) => [
            'no' => $r['no'], 'category' => $g['category'], 'uraian' => $r['uraian'],
            'satuan' => $r['satuan'], 'volume' => $r['volume'],
            'hargaSatuan' => $r['hargaSatuan'], 'total' => $r['total'],
        ]))->values());
        const __grandTotal = {{ $grandTotal }};
        const __projectName = @json($projectName);

        function exportRabAsCsv() {
            const lines = ['No,Kategori,Uraian Pekerjaan,Satuan,Volume,Harga Satuan,Total Harga'];
            __rabRows.forEach(r => {
                lines.push(`${r.no},"${r.category}","${r.uraian}",${r.satuan},${r.volume},${r.hargaSatuan},${r.total}`);
            });
            lines.push(`,,,,,GRAND TOTAL,${__grandTotal}`);
            const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href = url;
            a.download = `RAB-${__projectName.replace(/\s+/g, '-')}.csv`;
            a.click();
            URL.revokeObjectURL(url);
        }
    </script>
</x-layouts.app>
