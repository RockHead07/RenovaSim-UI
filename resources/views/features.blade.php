{{-- resources/views/components/features.blade.php --}}

@php
    $features = [
        [
            'number' => '01',
            'title' => 'Cost Estimation Engine',
            'description' => 'Accurately estimate renovation costs based on area size, type of work, and material requirements.',
            'images' => ['feature-1a.jpg', 'feature-1b.jpg'],
        ],
        [
            'number' => '02',
            'title' => 'Detailed Cost Breakdown',
            'description' => 'Understand exactly where your budget goes with clear and structured cost components.',
            'images' => ['feature-2a.jpg', 'feature-2b.jpg'],
        ],
        [
            'number' => '03',
            'title' => 'Design Inspiration',
            'description' => 'Explore visual references to help you decide the style and direction of your renovation.',
            'images' => ['feature-3a.jpg', 'feature-3b.jpg'],
        ],
    ];
@endphp

<section class="py-24 px-8 md:px-16" style="background-color: #2C2C2B;">
    <div class="max-w-6xl mx-auto">
        {{-- Section Title --}}
        <h2 class="font-serif text-3xl md:text-4xl lg:text-5xl leading-tight mb-16" style="color: #F5F5F5;">
            Architecture & Interior<br>Services
        </h2>

        {{-- Feature Rows --}}
        @foreach ($features as $feature)
            <div class="border-t" style="border-color: rgba(245, 245, 245, 0.1);">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-6 py-12 md:py-16">
                    {{-- Left: Number + Title --}}
                    <div class="md:col-span-3">
                        <p class="font-serif text-2xl md:text-3xl" style="color: #F5F5F5;">
                            {{ $feature['number'] }}. {{ $feature['title'] }}
                        </p>
                    </div>

                    {{-- Middle: Description + CTA --}}
                    <div class="md:col-span-4 flex flex-col justify-center">
                        <p class="text-sm font-light leading-relaxed mb-6" style="color: #838383;">
                            {{ $feature['description'] }}
                        </p>
                        <a href="#"
                            class="inline-flex items-center gap-2 text-xs font-medium tracking-widest uppercase transition-opacity duration-200 hover:opacity-70"
                            style="color: #F5F5F5;">
                            Learn More
                            <span class="text-xs">↓</span>
                        </a>
                    </div>

                    {{-- Right: Two Images --}}
                    <div class="md:col-span-5 flex gap-3">
                        <img src="{{ asset('images/' . $feature['images'][0]) }}" alt="{{ $feature['title'] }} preview 1"
                            class="w-1/2 h-36 md:h-44 object-cover" />
                        <img src="{{ asset('images/' . $feature['images'][1]) }}" alt="{{ $feature['title'] }} preview 2"
                            class="w-1/2 h-36 md:h-44 object-cover" />
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>