@if(session('success'))
  <div class="border-l-4 px-4 py-3 rounded mb-4 text-sm text-foreground" style="background:hsl(var(--color-primary-accent) / 0.15); border-color:hsl(var(--color-primary-accent));">
    {{ session('success') }}
  </div>
  @endif

  @if(session('error'))
  <div class="border-l-4 px-4 py-3 rounded mb-4 text-sm text-foreground" style="background:hsl(var(--color-destructive) / 0.15); border-color:hsl(var(--color-destructive));">
    {{ session('error') }}
  </div>
  @endif
  