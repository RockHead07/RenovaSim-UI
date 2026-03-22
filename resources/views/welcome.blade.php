<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renvasim — A visual space designed for real life</title>
    <meta name="description"
        content="Where material honesty meets spatial intelligence — interiors crafted with intention, clarity, and quiet confidence.">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        :root {
            --paragraph: #444444;
            --secondary-accent: #F5F5F5;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        h1,
        h2,
        h3 {
            font-family: 'DM Serif Display', serif;
        }

        .text-paragraph {
            color: var(--paragraph);
        }

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

<body class="min-h-screen bg-[hsl(0,0%,96%)] text-[hsl(0,0%,15%)] antialiased">

    {{-- Hero Section --}}
    <section class="relative min-h-screen flex flex-col overflow-hidden">

        {{-- Background image --}}
        <div class="absolute inset-0">
            <img src="{{ asset('images/hero-bg.png') }}" alt="" class="w-full h-full hero-bg-img" />
           \
        </div>

        {{-- Navigation --}}
        <nav class="relative z-10 flex items-center justify-between px-8 md:px-16 py-6 animate-fade-in"
            style="animation-delay: 0.1s">
            <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-8" style="filter: brightness(0) invert(1) brightness(0.95);" />

            <div class="hidden md:flex items-center gap-8">
                @foreach (['About Us', 'Features', 'Resources', 'Timers'] as $link)
                    <a href="#"
                        class="text-[hsl(0,0%,96%)]/80 text-sm font-light tracking-wide hover:text-[hsl(0,0%,96%)] transition-colors duration-200">
                        {{ $link }}
                    </a>
                @endforeach
            </div>

            <a href="#"
                class="flex items-center gap-2 rounded-full bg-[hsl(0,0%,96%)]/90 backdrop-blur px-5 py-2.5 text-sm font-medium text-[hsl(0,0%,18%)] hover:bg-[hsl(0,0%,96%)] transition-all duration-200 active:scale-[0.97] shadow-lg shadow-black/10">
                Get Started
                <span
                    class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-[hsl(0,0%,18%)] text-[hsl(0,0%,96%)] text-xs">↗</span>
            </a>
        </nav>

        {{-- Hero Content --}}
        <div class="relative z-10 flex-1 flex flex-col items-center justify-center text-center px-6 pb-32 pt-12">
            <h1 class="text-balance text-5xl sm:text-6xl md:text-7xl lg:text-8xl text-[hsl(0,0%,96%)] leading-[0.95] tracking-tight animate-fade-up opacity-0"
                style="animation-delay: 0.3s">
                A visual space<br>
                designed for <em class="italic">real life</em>
            </h1>

            <p class="mt-6 max-w-xl text-[hsl(0,0%,96%)]/70 text-base md:text-lg font-light leading-relaxed animate-fade-up opacity-0"
                style="animation-delay: 0.5s">
                Where material honesty meets spatial intelligence — interiors
                crafted with intention, clarity, and quiet confidence.
            </p>

            <a href="#"
                class="mt-10 inline-flex items-center gap-2 rounded-full bg-[hsl(0,0%,96%)]/90 backdrop-blur px-7 py-3.5 text-sm font-medium text-[hsl(0,0%,18%)] hover:bg-[hsl(0,0%,96%)] transition-all duration-200 active:scale-[0.97] shadow-xl shadow-black/15 animate-fade-up opacity-0"
                style="animation-delay: 0.7s">
                Start exploring
                <span
                    class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-[hsl(0,0%,18%)] text-[hsl(0,0%,96%)] text-xs">↗</span>
            </a>
        </div>

    </section>

    {{-- Features Section --}}
    @include('components.features')

</body>

</html>