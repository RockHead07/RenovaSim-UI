@if ($errors->any())
  <div class="rounded-lg border border-border/10 bg-muted/40 p-4">
    <p class="text-sm font-sans text-foreground font-medium mb-2">Please fix the following:</p>
    <ul class="list-disc pl-5 text-sm text-paragraph space-y-1">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

