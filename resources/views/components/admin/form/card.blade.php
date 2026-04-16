@props([
  'title',
  'action',
  'method' => 'POST',
  'enctype' => null,
  'maxWidth' => 'max-w-4xl',
])

<div class="bg-card rounded-xl w-full {{ $maxWidth }} border border-border/10 mx-auto">
  <div class="p-6 pb-0">
    <h2 class="font-serif text-foreground text-lg">{{ $title }}</h2>
  </div>

  <form method="POST" action="{{ $action }}" @if($enctype) enctype="{{ $enctype }}" @endif class="p-6 space-y-5">
    @csrf
    @if(strtoupper($method) !== 'POST')
      @method($method)
    @endif

    {{ $slot }}
  </form>
</div>

