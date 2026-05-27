<x-user::layouts.app title="RenovaSim — Buat Project Baru">
    <div class="flex-1 flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-[520px]">

            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h1 class="font-['Playfair_Display'] italic text-[26px] text-card-foreground">
                    Buat Project Baru
                </h1>
                <p class="text-sm text-muted-foreground mt-2">
                    Isi detail project renovasimu, lalu pilih cara estimasi.
                </p>
            </div>

            {{-- Error --}}
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('user.project.setup.store') }}"
                  class="bg-card rounded-2xl shadow-sm p-6 sm:p-8 space-y-5">
                @csrf

                {{-- Nama Project --}}
                <div>
                    <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">
                        Nama Project <span class="text-red-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="project_name"
                        value="{{ old('project_name') }}"
                        placeholder="misal: Renovasi Rumah Pak Budi"
                        required
                        class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm text-card-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                    />
                    @error('project_name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipe Bangunan --}}
                <div>
                    <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">
                        Tipe Bangunan <span class="text-muted-foreground/50">(opsional)</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2"
                         x-data="{ selected: '{{ old('building_type', '') }}' }">
                        @foreach([
                            ['value' => 'rumah',      'label' => 'Rumah',      'icon' => 'home'],
                            ['value' => 'apartemen',  'label' => 'Apartemen',  'icon' => 'building-2'],
                            ['value' => 'ruko',       'label' => 'Ruko',       'icon' => 'store'],
                            ['value' => 'kantor',     'label' => 'Kantor',     'icon' => 'briefcase'],
                            ['value' => 'villa',      'label' => 'Villa',      'icon' => 'tree-pine'],
                            ['value' => 'lainnya',    'label' => 'Lainnya',    'icon' => 'more-horizontal'],
                        ] as $type)
                            <button
                                type="button"
                                @click="selected = '{{ $type['value'] }}'"
                                :class="selected === '{{ $type['value'] }}'
                                    ? 'border-primary bg-primary/10 text-primary'
                                    : 'border-border text-muted-foreground hover:border-primary/40'"
                                class="flex flex-col items-center gap-1.5 rounded-xl border px-3 py-3 text-xs font-medium transition-colors"
                            >
                                <x-dynamic-component
                                    :component="'lucide-' . $type['icon']"
                                    class="w-5 h-5"
                                />
                                {{ $type['label'] }}
                            </button>
                        @endforeach
                        <input type="hidden" name="building_type" :value="selected">
                    </div>
                </div>

                {{-- Lokasi --}}
                <div>
                    <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">
                        Lokasi Project <span class="text-muted-foreground/50">(opsional)</span>
                    </label>
                    <select
                        name="location"
                        class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm text-card-foreground focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors appearance-none"
                    >
                        <option value="">Pilih kota...</option>
                        @foreach(config('renovasim.cities') as $city)
                            <option value="{{ strtolower($city) }}"
                                    {{ old('location') === strtolower($city) ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-[11px] uppercase tracking-widest text-muted-foreground mb-2 font-medium">
                        Deskripsi Singkat <span class="text-muted-foreground/50">(opsional)</span>
                    </label>
                    <textarea
                        name="description"
                        rows="3"
                        placeholder="misal: Renovasi total rumah 2 lantai, fokus dapur dan kamar mandi..."
                        class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm text-card-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors resize-none"
                    >{{ old('description') }}</textarea>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full bg-primary text-primary-foreground rounded-xl py-3.5 text-sm font-medium hover:opacity-90 transition-opacity flex items-center justify-center gap-2 mt-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    Lanjut ke Estimasi
                </button>

                <p class="text-center text-[11px] text-muted-foreground">
                    Data project disimpan sementara hingga estimasi selesai
                </p>
            </form>

            {{-- Back to dashboard --}}
            <div class="text-center mt-4">
                <a href="{{ route('dashboard') }}"
                   class="text-sm text-muted-foreground hover:text-card-foreground transition-colors">
                    ← Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</x-user::layouts.app>
