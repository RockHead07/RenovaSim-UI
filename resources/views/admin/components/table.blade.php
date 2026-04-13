@php
    $title = $title ?? null;
    $action = $action ?? null;
    $headers = $headers ?? [];
  @endphp
  <div class="bg-card rounded-[10px] overflow-hidden border border-border">
    @if($title || $action)
      <div class="flex items-center justify-between px-5 py-4">
        @if($title)
          <h3 class="font-serif text-foreground text-base">{{ $title }}</h3>
        @endif
        {!! $action !!}
      </div>
    @endif
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b border-border">
            @foreach($headers as $header)
              <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3 group">{{ $header }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          {{ $slot }}
        </tbody>
      </table>
    </div>
  </div>
  