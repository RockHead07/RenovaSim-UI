@props(['name' => 'user@email.com'])

<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
    <div class="flex flex-col gap-1.5 min-w-0">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="font-['Playfair_Display'] italic text-[20px] sm:text-[26px] text-secondary leading-tight">
                Welcome back, <span class="not-italic font-semibold">{{ $name }}</span>
            </span>
            <span class="bg-secondary text-secondary-foreground text-[10px] uppercase font-medium rounded-full px-2.5 py-1 tracking-wider">
                Project Owner
            </span>
        </div>
        <p class="text-xs sm:text-sm text-muted-foreground">
            Here's what's happening across your renovation projects today.
        </p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <button
            type="button"
            class="flex items-center gap-2 bg-card text-card-foreground rounded-full px-3 sm:px-4 py-2 text-xs sm:text-sm shadow-sm hover:bg-muted transition-colors"
        >
            <x-lucide-settings class="w-[15px] h-[15px]" /> <span>Account</span>
        </button>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="flex items-center gap-2 bg-card text-card-foreground rounded-full px-3 sm:px-4 py-2 text-xs sm:text-sm shadow-sm hover:bg-muted transition-colors"
            >
                <x-lucide-log-out class="w-[15px] h-[15px]" /> <span>Sign Out</span>
            </button>
        </form>
    </div>
</div>
