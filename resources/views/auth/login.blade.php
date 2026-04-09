<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - RenovaSim</title>
    <meta name="description" content="Sign in to RenovaSim - AI-powered renovation planning for homeowners.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground font-sans">
    <div class="flex min-h-screen w-full">

        {{-- LEFT COLUMN --}}
        <div class="hidden lg:flex lg:w-3/5 bg-[#030303] text-foreground flex-col justify-between p-10 relative overflow-hidden">
            {{-- Full background image --}}
            <img
                src="{{ asset('images/phone-mockup.png') }}"
                alt=""
                class="absolute bottom-0 left-0 max-w-full max-h-full object-contain"
            />

            {{-- Top: Wordmark --}}
            <a href="{{ url('/') }}" class="relative z-10 inline-block hover:opacity-80 transition-opacity">
                <img
                    src="{{ asset('images/logo.svg') }}"
                    alt="RenovaSim"
                    class="object-contain h-6 w-auto cursor-pointer"
                    style="filter: brightness(0) invert(1) brightness(0.95);"
                />
            </a>

            {{-- Center: Headline + Subtext --}}
            <div class="flex-1 flex flex-col justify-start pt-10 max-w-lg relative z-10">
                <h1 class="font-serif text-4xl md:text-5xl leading-tight text-foreground mb-6">
                    Plan Your Renovation with RenovaSim
                </h1>
            </div>

            {{-- Bottom: Footer --}}
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-8 h-0.5 bg-signin-accent"></div>
                <span class="text-paragraph text-xs font-sans uppercase tracking-widest">
                    The Modern Authority in Home Design
                </span>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="w-full lg:w-1/1 bg-signin flex flex-col justify-between p-8 md:p-12">
            {{-- Top: New here? --}}
            <div class="flex justify-end items-center gap-2">
                <span class="text-signin-muted text-sm font-sans">New here?</span>
                <a
                    href="{{ route('register') }}"
                    class="text-signin-accent font-sans font-medium text-sm hover:underline"
                >
                    Sign Up
                </a>
            </div>

            {{-- Center: Form --}}
            <div class="flex-1 flex items-center justify-center">
                <div class="w-full max-w-md">
                    <h2 class="font-serif text-3xl text-signin-foreground mb-1">
                        Sign In
                    </h2>
                    <p class="font-sans text-signin-muted text-xs mb-6">
                        Welcome back to your renovation journey.
                    </p>

                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block font-sans text-xs uppercase tracking-widest text-signin-muted mb-1.5">
                                Email or Username
                            </label>
                            <input
                                id="email"
                                type="text"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="hello@renovasim.com"
                                class="w-full rounded-full border border-signin-border bg-transparent px-4 py-2 text-xs font-sans text-signin-foreground placeholder:text-signin-muted/60 focus:outline-none focus:ring-2 focus:ring-ring"
                                required
                                autofocus
                            />
                            @error('email')
                                <p class="text-destructive text-xs mt-1 px-5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label for="password" class="font-sans text-xs uppercase tracking-widest text-signin-muted">
                                    Password
                                </label>
                                @if (Route::has('password.request'))
                                    <a
                                        href="{{ route('password.request') }}"
                                        class="font-sans text-xs text-signin-accent hover:underline"
                                    >
                                        Forgot password?
                                    </a>
                                @endif
                            </div>
                            <div class="relative" x-data="{ show: false }">
                                <input
                                    id="password"
                                    :type="show ? 'text' : 'password'"
                                    name="password"
                                    placeholder="12345678"
                                    class="w-full rounded-full border border-signin-border bg-transparent px-4 py-2 text-xs font-sans text-signin-foreground placeholder:text-signin-muted/60 focus:outline-none focus:ring-2 focus:ring-ring"
                                    required
                                />
                                <button
                                    type="button"
                                    @click="show = !show"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-signin-muted hover:text-signin-foreground"
                                >
                                    {{-- Eye icon --}}
                                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                                    {{-- EyeOff icon --}}
                                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-destructive text-xs mt-1 px-5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Sign In Button --}}
                        <button
                            type="submit"
                            class="w-full rounded-full bg-primary text-primary-foreground py-2 font-sans font-medium text-xs uppercase tracking-widest hover:opacity-90 transition-opacity"
                        >
                            Sign In
                        </button>
                    </form>

                    {{-- Divider --}}
                    <div class="flex items-center gap-4 my-4">
                        <div class="flex-1 h-px bg-signin-border"></div>
                        <span class="font-sans text-xs uppercase tracking-widest text-paragraph">
                            Or continue with
                        </span>
                        <div class="flex-1 h-px bg-signin-border"></div>
                    </div>

                    {{-- Social Buttons --}}
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ url('/auth/google') }}" class="flex items-center justify-center gap-2 rounded-full border border-signin-border bg-transparent py-2 font-sans text-xs text-signin-foreground hover:bg-signin-border/20 transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4" />
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05" />
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
                            </svg>
                            Google
                        </a>
                        <a href="{{ url('/auth/apple') }}" class="flex items-center justify-center gap-2 rounded-full border border-signin-border bg-transparent py-2 font-sans text-xs text-signin-foreground hover:bg-signin-border/20 transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.05 20.28c-.98.95-2.05.88-3.08.4-1.09-.5-2.08-.48-3.24 0-1.44.62-2.2.44-3.06-.4C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z" />
                            </svg>
                            Apple
                        </a>
                    </div>
                </div>
            </div>

            {{-- Bottom Footer --}}
            <div class="flex flex-wrap text-paragraph justify-center gap-x-4 gap-y-1 pt-12">
                <span class="font-sans text-xs uppercase tracking-widest text-signin-muted">
                    &copy; 2024 RenovaSim AI
                </span>
                <span class="font-sans text-xs text-signin-muted">&middot;</span>
                <a href="#" class="font-sans text-xs uppercase tracking-widest text-signin-muted hover:text-signin-foreground">
                    Privacy Policy
                </a>
                <span class="font-sans text-xs text-signin-muted">&middot;</span>
                <a href="#" class="font-sans text-xs uppercase tracking-widest text-signin-muted hover:text-signin-foreground">
                    Terms of Service
                </a>
                <span class="font-sans text-xs text-signin-muted">&middot;</span>
                <a href="#" class="font-sans text-xs uppercase tracking-widest text-signin-muted hover:text-signin-foreground">
                    Contact Us
                </a>
            </div>
        </div>
    </div>

    <div class="cursor-dot"></div>
    <div class="cursor-dot-outline"></div>
</body>
</html>