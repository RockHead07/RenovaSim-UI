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
    // Fetch active partners from database, ordered by 'order' column
    $partners = \App\Models\Partner::where('is_active', true)
                                    ->orderBy('order')
                                    ->get();
    
    // Fallback to static partners if no database partners exist
    if ($partners->isEmpty()) {
        $partners = collect([
            ['name' => 'IKEA', 'logo' => 'images/partners/ikea.png'],
            ['name' => 'INFORMA', 'logo' => 'images/partners/informa.png'],
            ['name' => 'Mitra10', 'logo' => 'images/partners/mitra10.png'],
            ['name' => 'BJ Home', 'logo' => 'images/partners/bjhome.png'],
            ['name' => 'Qhomemart', 'logo' => 'images/partners/qhomemart.png'],
            ['name' => 'Kanggo', 'logo' => 'images/partners/kanggo.png'],
            ['name' => 'Tukang.com', 'logo' => 'images/partners/tukangcom.png'],
        ]);
    }
@endphp

<section class="py-16 bg-background" style="border-bottom: 1px solid rgba(245, 245, 245, 0.1);">
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
                            // Determine logo path
                            if (isset($partner->logo)) {
                                // Database partner - check if logo is stored or public
                                if ($partner->logo) {
                                    // Check if logo is a full path (from storage) or just a filename (from public)
                                    $logoPath = str_contains($partner->logo, '/') 
                                        ? asset('storage/' . $partner->logo)
                                        : asset('images/partners/' . $partner->logo);
                                } else {
                                    $logoPath = asset('images/logo.svg');
                                }
                                $partnerName = $partner->name;
                            } else {
                                // Static partner array fallback
                                $logoPath = asset($partner['logo']);
                                $partnerName = $partner['name'];
                            }
                        @endphp
                        <img src="{{ $logoPath }}" alt="{{ $partnerName }}"
                            class="h-8 md:h-10 w-auto object-contain opacity-50 grayscale brightness-150 hover:opacity-100 hover:grayscale-0 hover:brightness-100 transition-all duration-300" 
                            onerror="this.src='{{ asset('images/logo.svg') }}'"/>
                    </div>
                @endforeach
            @endfor
        </div>
    </div>
</section>