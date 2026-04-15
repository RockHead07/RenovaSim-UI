<section id="pricing" class="py-24 px-8 md:px-16 bg-background/98">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="font-serif text-3xl md:text-4xl lg:text-5xl text-foreground mb-4">
                Simple, Transparent Pricing
            </h2>
            <p class="text-paragraph text-sm md:text-base font-light max-w-xl mx-auto leading-relaxed">
                Choose the plan that fits your project scale - from basic estimation to full AI-powered design.
            </p>
        </div>

        @if(isset($pricingPlans) && $pricingPlans->count())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                @foreach($pricingPlans as $plan)
                    <div class="relative flex flex-col rounded-2xl p-8 transition-all duration-300 {{ $plan->is_popular ? 'border-2 scale-[1.03] shadow-[0_0_40px_-12px_hsl(var(--primary)/0.3)]' : 'border border-border bg-card hover:border-primary/30' }}"
                         @if($plan->is_popular) style="border-color: #8BA023; background-color: rgba(139, 160, 35, 0.08);" @endif>
                        @if($plan->is_popular)
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                <span class="bg-primary text-primary-foreground text-xs font-medium tracking-wider uppercase px-4 py-1.5 rounded-full">
                                    Most Popular
                                </span>
                            </div>
                        @endif

                        <h3 class="font-serif text-2xl text-foreground">{{ $plan->name }}</h3>
                        <p class="text-paragraph text-sm font-light mt-1 mb-6">{{ $plan->description ?: 'Flexible plan for your renovation workflow.' }}</p>

                        @php
                            $currentPrice = (float) $plan->price;
                            $originalPrice = (float) ($plan->original_price ?? 0);
                            $hasDiscount = $originalPrice > $currentPrice && $currentPrice >= 0;
                            $discountPercent = $hasDiscount
                                ? (int) round((($originalPrice - $currentPrice) / $originalPrice) * 100)
                                : 0;
                        @endphp

                        @if($hasDiscount)
                            <div class="mb-1 flex items-center gap-2">
                                <span class="text-paragraph text-sm line-through">${{ number_format($originalPrice, floor($originalPrice) == $originalPrice ? 0 : 2) }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-status-active/15 text-status-active">
                                    Save {{ $discountPercent }}%
                                </span>
                            </div>
                        @endif
                        <div class="flex items-baseline gap-1 mb-1">
                            <span class="font-serif text-5xl text-foreground">${{ number_format($currentPrice, floor($currentPrice) == $currentPrice ? 0 : 2) }}</span>
                        </div>
                        <p class="text-paragraph text-xs font-light mb-8">Billed each month / Per user</p>

                        <ul class="space-y-3.5 mb-8 flex-1">
                            @foreach($plan->features as $feature)
                                <li class="flex items-start gap-3">
                                    <span class="mt-0.5 shrink-0 w-5 h-5 rounded-full flex items-center justify-center {{ $feature->is_available ? 'bg-primary' : 'bg-muted' }}">
                                        @if($feature->is_available)
                                            <svg class="w-3 h-3 text-primary-foreground" viewBox="0 0 12 12" fill="none"><polyline points="2,6 5,9 10,3" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @else
                                            <svg class="w-3 h-3 text-paragraph" viewBox="0 0 12 12" fill="none"><line x1="2" y1="2" x2="10" y2="10" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><line x1="10" y1="2" x2="2" y2="10" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                                        @endif
                                    </span>
                                    <span class="text-sm font-light {{ $feature->is_available ? 'text-foreground' : 'text-paragraph/50' }}">{{ $feature->feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a href="#" class="block text-center py-3.5 rounded-xl text-sm font-medium tracking-wide transition-all duration-200 active:scale-[0.97] {{ $plan->is_popular ? 'bg-primary text-primary-foreground hover:bg-primary/90' : 'bg-accent text-accent-foreground hover:bg-accent/80' }}">
                            Get {{ $plan->name }} Access
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-center text-paragraph text-sm">Pricing plans will be available soon.</p>
        @endif
    </div>
</section>
