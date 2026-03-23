{{-- resources/views/components/about.blade.php --}}
<section class="py-24 px-8 md:px-16" style="background-color: #2C2C2B;">
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-16 md:gap-24">
        {{-- Left - Headline --}}
        <div>
            <h2 class="font-serif text-3xl md:text-4xl lg:text-5xl leading-tight" style="color: #F5F5F5;">
                Plan Your Renovation with Clear Cost Estimates
            </h2>
        </div>
        {{-- Right - Description + CTA --}}
        <div class="flex flex-col justify-center">
            <p class="text-sm md:text-base font-light leading-relaxed mb-4" style="color: #838383;">
                RenovaSim helps you estimate renovation costs based on area size, type of work, and material requirements — quickly and transparently.
            </p>
            <p class="text-sm md:text-base font-light leading-relaxed mb-4" style="color: #838383;">
                Get a clear picture of your budget early and avoid unrealistic planning before starting your renovation.
            </p>
            <p class="text-sm md:text-base font-light leading-relaxed mb-8" style="color: #838383;">
                You can also explore design references to find ideas that match your vision.
            </p>
            <a href="#" class="inline-flex items-center gap-2 text-sm font-medium tracking-widest uppercase hover:opacity-70 transition-opacity duration-200" style="color: #F5F5F5;">
                Start Estimating
                <span class="text-xs">→</span>
            </a>
        </div>
    </div>

    {{-- Metrics --}}
    @php
        $metrics = [
            ['label' => 'Projects', 'value' => '67+', 'desc' => 'Renovation estimations have been generated across various types of work, helping users understand potential costs before starting their projects.'],
            ['label' => 'Users', 'value' => '120+', 'desc' => 'Individuals have used RenovaSim to explore renovation scenarios and get a clearer picture of their budget planning.'],
            ['label' => 'Experience', 'value' => '3 Years', 'desc' => 'Built as a concept-driven platform, RenovaSim reflects ongoing development and learning in simplifying renovation cost estimation.'],
        ];
    @endphp

    <div class="max-w-6xl mx-auto mt-20 pt-16 grid grid-cols-1 sm:grid-cols-3 gap-12" style="border-top: 1px solid rgba(245, 245, 245, 0.1);">
        @foreach ($metrics as $metric)
            <div>
                <p class="text-xs font-medium tracking-widest uppercase mb-3" style="color: rgba(245, 245, 245, 0.5);">{{ $metric['label'] }}</p>
                <p class="font-serif text-4xl md:text-5xl mb-3" style="color: #F5F5F5;">{{ $metric['value'] }}</p>
                <p class="text-sm font-light leading-relaxed" style="color: #838383;">{{ $metric['desc'] }}</p>
            </div>
        @endforeach
    </div>
</section>
