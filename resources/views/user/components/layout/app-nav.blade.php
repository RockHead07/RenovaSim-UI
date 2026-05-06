{{-- Top nav for non-dashboard pages — port of AppNav.tsx --}}
<nav class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-4">
    <a
        href="/"
        class="font-['Playfair_Display'] italic text-xl text-card-foreground hover:opacity-80 transition-opacity"
    >RenovaSim</a>
    <div class="flex items-center gap-3">
        <button class="w-8 h-8 rounded-full border border-border flex items-center justify-center hover:bg-muted transition-colors">
            <x-lucide-help-circle class="w-4 h-4 text-muted-foreground" />
        </button>
        <button class="w-8 h-8 rounded-full border border-border flex items-center justify-center hover:bg-muted transition-colors">
            <x-lucide-user class="w-4 h-4 text-muted-foreground" />
        </button>
    </div>
</nav>
