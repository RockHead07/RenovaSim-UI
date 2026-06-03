{{-- resources/views/components/partners-carousel.blade.php --}}

{{-- Add this CSS to your stylesheet or <style> block --}}

<style>
    @keyframes scroll-left {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .animate-scroll-left {
        animation: scroll-left 20s linear infinite;
    }
    .modal-backdrop {
        backdrop-filter: blur(4px);
        background-color: rgba(0, 0, 0, 0.5);
    }
</style>

@php
    $partners = \App\Models\Partner::where('is_active', true)
        ->orderBy('order')
        ->get(['id', 'name', 'logo', 'logo_image']);

    if ($partners->isEmpty()) {
        $partners = collect([
            (object)['name' => 'IKEA',        'logo' => 'images/partners/ikea.png',       'logo_image' => null],
            (object)['name' => 'INFORMA',     'logo' => 'images/partners/informa.png',    'logo_image' => null],
            (object)['name' => 'Mitra10',     'logo' => 'images/partners/mitra10.png',    'logo_image' => null],
            (object)['name' => 'BJ Home',     'logo' => 'images/partners/bjhome.png',     'logo_image' => null],
            (object)['name' => 'Qhomemart',   'logo' => 'images/partners/qhomemart.png',  'logo_image' => null],
            (object)['name' => 'Kanggo',      'logo' => 'images/partners/kanggo.png',     'logo_image' => null],
            (object)['name' => 'Tukang.com',  'logo' => 'images/partners/tukangcom.png',  'logo_image' => null],
        ]);
    }
@endphp

<section class="py-16 bg-background" style="border-bottom: 1px solid rgba(245, 245, 245, 0.1);" x-data="partnerCarousel()">
    <h3 class="text-center font-sans text-xl md:text-2xl tracking-widest uppercase mb-10 text-paragraph">
        Our Partners
    </h3>
    <div class="relative overflow-hidden">
        {{-- Fade edges --}}
        <div class="absolute left-0 top-0 bottom-0 w-16 z-10"
            style="background: linear-gradient(to right, hsl(30 2% 17.3%), transparent);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-16 z-10"
            style="background: linear-gradient(to left, hsl(30 2% 17.3%), transparent);"></div>

        {{-- Scrolling track (duplicated for seamless loop) --}}
        <div class="flex animate-scroll-left w-max">
            @for ($i = 0; $i < 2; $i++)
                @foreach ($partners as $partner)
                    <div class="flex items-center justify-center px-10 md:px-14">
                        @php
                            // Determine logo path - prioritize logo_image
                            if (isset($partner->logo_image) && $partner->logo_image) {
                                // Use the actual logo image from storage
                                $logoPath = asset('storage/' . $partner->logo_image);
                                $partnerName = $partner->name;
                            } elseif (isset($partner->logo) && $partner->logo) {
                                // Fallback to logo field
                                $logoPath = str_contains($partner->logo, '/') 
                                    ? asset('storage/' . $partner->logo)
                                    : asset('images/partners/' . $partner->logo);
                                $partnerName = $partner->name;
                            } elseif (isset($partner['logo'])) {
                                // Static partner array fallback
                                $logoPath = asset($partner['logo']);
                                $partnerName = $partner['name'];
                            } else {
                                $logoPath = asset('images/logo.svg');
                                $partnerName = $partner->name ?? $partner['name'] ?? 'Partner';
                            }
                        @endphp
                        <button @click="openModal('{{ $logoPath }}', '{{ $partnerName }}')" type="button"
                            class="cursor-pointer bg-none border-none p-0 hover:scale-110 transition-transform duration-200">
                            <img src="{{ $logoPath }}" alt="{{ $partnerName }}"
                                class="h-8 md:h-10 w-auto object-contain opacity-50 grayscale brightness-150 hover:opacity-100 hover:grayscale-0 hover:brightness-100 transition-all duration-300" 
                                onerror="this.src='{{ asset('images/logo.svg') }}'"/>
                        </button>
                    </div>
                @endforeach
            @endfor
        </div>
    </div>

    {{-- Image Modal --}}
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center modal-backdrop" 
        @click="showModal = false" @keydown.escape="showModal = false" style="display: none;">
        <div @click.stop class="relative bg-card rounded-xl border border-border/20 p-6 max-w-3xl max-h-[85vh] overflow-auto">
            {{-- Close Button --}}
            <button @click="showModal = false" type="button" 
                class="absolute top-4 right-4 p-2 rounded-lg hover:bg-muted transition-colors z-10">
                <svg class="w-6 h-6 text-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Modal Content --}}
            <div class="text-center">
                <p class="text-sm text-paragraph mb-4" x-text="partnerName"></p>
                <img :src="modalImage" :alt="partnerName" 
                    class="w-full h-auto rounded-lg mb-4"/>
                <p class="text-xs text-paragraph/70">Click outside, press ESC, or click X to close</p>
            </div>
        </div>
    </div>
</section>

<script>
function partnerCarousel() {
    return {
        showModal: false,
        modalImage: '',
        partnerName: '',
        
        openModal(imagePath, name) {
            this.modalImage = imagePath;
            this.partnerName = name;
            this.showModal = true;
        }
    }
}
</script>