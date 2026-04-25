@extends('layouts.app')

@section('title', 'Project Stage — RenovaSim')

@section('content')
<div class="min-h-screen bg-background flex flex-col">

    @include('partials.app-nav')

    <div class="flex-1 flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-[500px] flex flex-col items-center">

            {{-- Icon --}}
            <div class="w-12 h-12 rounded-xl bg-muted flex items-center justify-center">
                {{-- ClipboardList icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground">
                    <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                    <path d="M12 11h4"/><path d="M12 16h4"/>
                    <path d="M8 11h.01"/><path d="M8 16h.01"/>
                </svg>
            </div>

            <h1 class="font-playfair text-[22px] text-card-foreground text-center mt-4">
                What stage is your renovation in?
            </h1>
            <p class="text-sm text-muted-foreground text-center max-w-[320px] leading-relaxed mt-2">
                This helps us tailor your project setup to your situation.
            </p>

            {{-- Option Cards --}}
            <div class="w-full flex flex-col gap-3 mt-7">

                {{-- Option 1: Work already started --}}
                <a href="#" class="w-full flex items-center gap-4 bg-card rounded-xl p-[18px_20px] text-left border-[1.5px] border-primary shadow-sm hover:shadow-md transition-all">
                    <div class="w-9 h-9 rounded-lg bg-[hsl(110,70%,94%)] flex items-center justify-center shrink-0">
                        {{-- Check icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                            <path d="M20 6 9 17 4 12"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-[15px] text-card-foreground">Work has already started</p>
                        <p class="text-[13px] text-muted-foreground mt-0.5">Track payments, documents, and project progress.</p>
                    </div>
                </a>

                {{-- Option 2: Planning renovation --}}
                <a href="/project-details" class="w-full flex items-center gap-4 bg-card rounded-xl p-[18px_20px] text-left border-[1.5px] border-transparent shadow-sm hover:shadow-md transition-all">
                    <div class="w-9 h-9 rounded-lg bg-muted flex items-center justify-center shrink-0">
                        {{-- ClipboardList icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground">
                            <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <path d="M12 11h4"/><path d="M12 16h4"/>
                            <path d="M8 11h.01"/><path d="M8 16h.01"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-[15px] text-card-foreground">Planning my renovation</p>
                        <p class="text-[13px] text-muted-foreground mt-0.5">Get a cost/budget estimate and compare contractor quotes.</p>
                    </div>
                </a>

            </div>

            {{-- Dot Indicator --}}
            <div class="flex gap-1.5 mt-8">
                <div class="w-2 h-2 rounded-full bg-secondary"></div>
                <div class="w-2 h-2 rounded-full bg-border"></div>
                <div class="w-2 h-2 rounded-full bg-border"></div>
            </div>

        </div>
    </div>

    @include('partials.app-footer')

</div>
@endsection
