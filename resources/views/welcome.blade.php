<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RenovaSim — A visual space designed for real life</title>
    <meta name="description"
        content="Where material honesty meets spatial intelligence — interiors crafted with intention, clarity, and quiet confidence.">

    {{-- Fonts --}}
    {{-- Add PP Neue Montreal and PP Editorial New fonts from your font provider --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>

        @keyframes fade-up {
            from {
                opacity: 0;
                transform: translateY(16px);
                filter: blur(4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
                filter: blur(0);
            }
        }

        @keyframes fade-in {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .animate-fade-up {
            animation: fade-up 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-fade-in {
            animation: fade-in 0.6s ease-out forwards;
        }

        .hero-bg-img {
            transform-origin: center;
            object-fit: cover;   
        }

        @media (max-width: 768px) {
            .hero-bg-img {
                transform: scale(1);
            }
        }
    </style>
</head>

<body class="min-h-screen bg-background text-foreground antialiased font-sans\">

    {{-- Hero Section --}}
    <section class="relative min-h-screen flex flex-col overflow-hidden">

        {{-- Background image --}}
        <div class="absolute inset-0">
            <img src="{{ asset('images/hero-bg.png') }}" alt="" class="w-full h-full hero-bg-img">
        </div>

        {{-- Navigation --}}
        <nav class="relative z-10 flex items-center justify-between px-8 md:px-16 py-6 animate-fade-in"
            style="animation-delay: 0.1s">
            <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-8" style="filter: brightness(0) invert(1) brightness(0.95);" />

            <div class="hidden md:flex items-center gap-8">
                @foreach (['How It Works', 'Features', 'Pricing', 'FAQ'] as $link)
                    <a href="#"
                        class="text-muted-foreground/80 text-sm font-light tracking-wide hover:text-foreground transition-colors duration-200 font-sans">
                        {{ $link }}
                    </a>
                @endforeach
            </div>

            <a href="#"
                class="flex items-center gap-2 rounded-full bg-accent/90 backdrop-blur px-5 py-2.5 text-sm font-bold text-accent-foreground hover:bg-accent transition-all duration-200 active:scale-[0.97] shadow-lg shadow-black/10 font-sans">
                Get Started
                <svg class="w-4 h-4 ml-1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="7" y1="17" x2="17" y2="7"></line>
                    <polyline points="7 7 17 7 17 17"></polyline>
                </svg>
            </a>
        </nav>

        {{-- Hero Content --}}
        <div class="relative z-10 flex-1 flex flex-col items-center justify-center text-center px-6 pb-32 pt-12">
            <h1 class="text-balance text-5xl sm:text-6xl md:text-7xl lg:text-8xl text-foreground leading-[0.75] tracking-tight animate-fade-up opacity-0 font-sans font-normal"
                style="animation-delay: 0.3s">
                A visual space<br>
                designed for <em class="leading-[0.75] italic font-serif font-normal">real life</em>
            </h1>

            <p class="mt-6 max-w-xl text-muted-foreground/70 text-base md:text-lg font-light leading-relaxed animate-fade-up opacity-0 font-sans"
                style="animation-delay: 0.5s">
                Where material honesty meets spatial intelligence — interiors
                crafted with intention, clarity, and quiet confidence.
            </p>

            <a href="#"
                class="mt-10 inline-flex items-center gap-2 rounded-full bg-accent/90 backdrop-blur px-7 py-3.5 text-sm font-bold text-accent-foreground hover:bg-accent transition-all duration-200 active:scale-[0.97] shadow-xl shadow-black/15 animate-fade-up opacity-0 font-sans"
                style="animation-delay: 0.7s">
                Start exploring
                <svg class="w-4 h-4 ml-1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="7" y1="17" x2="17" y2="7"></line>
                    <polyline points="7 7 17 7 17 17"></polyline>
                </svg>
            </a>
        </div>

    </section>

    {{-- Partners Carousel (Bridge between Page 1 & 2) --}}
    @include('components.partners-carousel')

    {{-- Page 2 Section --}}
    @include('components.about')

    {{-- Features Section --}}
    @include('components.features')

    {{-- Pricing Section --}}
    @include('components.pricing')

    {{-- FAQ Section --}}
    @include('faq')


    <div class="cursor-dot"></div>
    <div class="cursor-dot-outline"></div>
</body>

</html>