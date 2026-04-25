<nav class="flex items-center justify-between px-8 py-4">
    <a href="/" class="font-playfair text-xl text-card-foreground hover:opacity-80 transition-opacity">
        RenovaSim
    </a>
    <div class="flex items-center gap-3">
        <button class="w-8 h-8 rounded-full border border-border flex items-center justify-center hover:bg-muted transition-colors">
            {{-- HelpCircle icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground">
                <circle cx="12" cy="12" r="10"/>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                <path d="M12 17h.01"/>
            </svg>
        </button>
        <button class="w-8 h-8 rounded-full border border-border flex items-center justify-center hover:bg-muted transition-colors">
            {{-- User icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
        </button>
    </div>
</nav>
