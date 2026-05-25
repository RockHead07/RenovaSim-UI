<footer class="bg-background pt-16 md:pt-24 px-6 md:px-16">

    {{-- CTA Card --}}
    <div class="max-w-6xl mx-auto mb-16 md:mb-24">
        <div class="bg-card rounded-2xl md:rounded-3xl overflow-hidden border border-border">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-0">

                {{-- Left --}}
                <div class="p-8 md:p-12 flex flex-col justify-center">
                    <p class="text-muted-foreground text-xs font-medium tracking-[0.2em] uppercase mb-4">
                        Start Estimating
                    </p>
                    <h2 class="font-serif text-2xl md:text-3xl lg:text-4xl text-foreground leading-tight mb-4">
                        Ready to Know Your Real Renovation Cost?
                    </h2>
                    <p class="text-paragraph text-sm font-light leading-relaxed mb-8 max-w-md">
                        Get an AI-powered cost estimate and material breakdown before you spend a single rupiah — no contractor needed.
                    </p>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 self-start rounded-full bg-foreground text-background px-6 py-3 text-sm font-medium hover:opacity-80 transition-opacity duration-200">
                        Get Started
                        <span class="text-xs">↗</span>
                    </a>
                </div>

                {{-- Right: illustration --}}
                <div class="p-6 md:p-10 flex items-center justify-center">
                    <img
                        src="{{ asset('images/cta-illustration.png') }}"
                        alt="Home design and estimation illustration"
                        class="w-full max-w-sm rounded-xl"
                    />
                </div>

            </div>
        </div>
    </div>

    {{-- Footer Columns --}}
    <div class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 md:gap-8 pb-12">

        {{-- Column 1: Logo & description --}}
        <div>
            <img src="{{ asset('images/logo.svg') }}" alt="RenovaSim" class="h-6 mb-5"/>
            <p class="text-paragraph text-sm font-light leading-relaxed">
                AI-powered renovation planning. Estimate costs, simulate designs, and make smarter decisions — before you build.
            </p>
        </div>

        {{-- Column 2: Product --}}
        <div>
            <h4 class="text-foreground text-sm font-medium mb-5">Product</h4>
            <ul class="space-y-3">
                <li><a href="#how-it-works" class="text-paragraph text-sm font-light hover:text-foreground transition-colors duration-200">How It Works</a></li>
                <li><a href="#features"     class="text-paragraph text-sm font-light hover:text-foreground transition-colors duration-200">Features</a></li>
                <li><a href="#pricing"      class="text-paragraph text-sm font-light hover:text-foreground transition-colors duration-200">Pricing</a></li>
                <li><a href="#faq"          class="text-paragraph text-sm font-light hover:text-foreground transition-colors duration-200">FAQ</a></li>
            </ul>
        </div>

        {{-- Column 3: Socials --}}
        <div>
            <h4 class="text-foreground text-sm font-medium mb-5">Socials</h4>
            <ul class="space-y-3">
                <li>
                    <a href="#" target="_blank" rel="noopener noreferrer"
                       class="text-paragraph text-sm font-light hover:text-foreground transition-colors duration-200 inline-flex items-center gap-1">
                        Instagram <span class="text-xs">↗</span>
                    </a>
                </li>
                <li>
                    <a href="#" target="_blank" rel="noopener noreferrer"
                       class="text-paragraph text-sm font-light hover:text-foreground transition-colors duration-200 inline-flex items-center gap-1">
                        LinkedIn <span class="text-xs">↗</span>
                    </a>
                </li>
                <li>
                    <a href="#" target="_blank" rel="noopener noreferrer"
                       class="text-paragraph text-sm font-light hover:text-foreground transition-colors duration-200 inline-flex items-center gap-1">
                        X (Twitter) <span class="text-xs">↗</span>
                    </a>
                </li>
                <li>
                    <a href="#" target="_blank" rel="noopener noreferrer"
                       class="text-paragraph text-sm font-light hover:text-foreground transition-colors duration-200 inline-flex items-center gap-1">
                        GitHub <span class="text-xs">↗</span>
                    </a>
                </li>
                <li>
                    <a href="#" target="_blank" rel="noopener noreferrer"
                       class="text-paragraph text-sm font-light hover:text-foreground transition-colors duration-200 inline-flex items-center gap-1">
                        Dribbble <span class="text-xs">↗</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Column 4: Newsletter --}}
        <div>
            <h4 class="text-foreground text-sm font-medium mb-5">Newsletter</h4>
            <p class="text-paragraph text-sm font-light leading-relaxed mb-5">
                Stay updated on new features and AI improvements as we build RenovaSim.
            </p>
            <form action="{{ route('newsletter.subscribe') }}" method="POST">
                @csrf
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/>
                            <polyline points="2,4 12,13 22,4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email..."
                        required
                        class="w-full rounded-full bg-card border border-border pl-10 pr-14 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring"
                    />
                    <button
                        type="submit"
                        aria-label="Subscribe"
                        class="absolute right-1.5 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-foreground text-background flex items-center justify-center hover:opacity-80 transition-opacity duration-200"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <polyline points="13,6 19,12 13,18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- Bottom bar --}}
    <div class="max-w-6xl mx-auto border-t border-border py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-paragraph text-xs font-light">
            &copy; 2025 RenovaSim &middot; AI-Powered Renovation Planning
        </p>
        <div class="flex items-center gap-6">
            <a href="#" class="text-paragraph text-xs font-light hover:text-foreground transition-colors duration-200">Privacy Policy</a>
            <a href="#" class="text-paragraph text-xs font-light hover:text-foreground transition-colors duration-200">Terms of Service</a>
        </div>
    </div>

</footer>
