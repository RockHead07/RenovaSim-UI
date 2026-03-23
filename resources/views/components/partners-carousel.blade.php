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
</style>

@php
    $partners = [
        ['name' => 'IKEA', 'logo' => 'partners/ikea.png'],
        ['name' => 'INFORMA', 'logo' => 'partners/informa.png'],
        ['name' => 'Mitra10', 'logo' => 'partners/mitra10.png'],
        ['name' => 'BJ Home', 'logo' => 'partners/bjhome.png'],
        ['name' => 'Qhomemart', 'logo' => 'partners/qhomemart.png'],
        ['name' => 'Kanggo', 'logo' => 'partners/kanggo.png'],
        ['name' => 'Tukang.com', 'logo' => 'partners/tukangcom.png'],
    ];
@endphp

<section class="py-16" style="background-color: #2C2C2B; border-bottom: 1px solid rgba(245, 245, 245, 0.1);">
    <h2 class="text-center font-serif text-xl md:text-2xl tracking-widest uppercase mb-10" style="color: #F5F5F5;">
        Our Partners
    </h2>
    <div class="relative overflow-hidden">
        {{-- Fade edges --}}
        <div class="absolute left-0 top-0 bottom-0 w-16 z-10"
            style="background: linear-gradient(to right, #2C2C2B, transparent);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-16 z-10"
            style="background: linear-gradient(to left, #2C2C2B, transparent);"></div>

        {{-- Scrolling track (duplicated for seamless loop) --}}
        <div class="flex animate-scroll-left w-max">
            @for ($i = 0; $i < 2; $i++)
                @foreach ($partners as $partner)
                    <div class="flex items-center justify-center px-10 md:px-14">
                        <img src="{{ asset('images/' . $partner['logo']) }}" alt="{{ $partner['name'] }}"
                            class="h-8 md:h-10 w-auto object-contain opacity-50 grayscale brightness-150 hover:opacity-100 hover:grayscale-0 hover:brightness-100 transition-all duration-300" />
                    </div>
                @endforeach
            @endfor
        </div>
    </div>
</section>