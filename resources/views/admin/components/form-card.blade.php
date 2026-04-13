@php
    $action = $action ?? url()->current();
    $method = $method ?? 'POST';
  @endphp
  <div class="bg-card rounded-xl w-full max-w-2xl border border-border">
    <div class="p-6 pb-0">
      <h2 class="font-serif text-foreground text-lg">{{ $title }}</h2>
    </div>
    <form method="POST" action="{{ $action }}" class="p-6 space-y-5">
      @csrf
      @if(strtoupper($method) !== 'POST')
        @method($method)
      @endif
      {{ $slot }}
      <div class="flex gap-3 pt-2">
        <button type="submit" class="bg-foreground text-background rounded-lg px-6 py-2 text-sm font-sans font-medium hover:opacity-90">Save</button>
        <a href="{{ url()->previous() }}" class="border border-border text-paragraph rounded-lg px-6 py-2 text-sm font-sans hover:text-foreground">Cancel</a>
      </div>
    </form>
  </div>
  