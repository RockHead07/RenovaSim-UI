@props([
  'primaryLabel' => 'Save',
  'cancelHref' => null,
])

<div class="flex gap-3 pt-2 justify-start">
  <button type="submit" class="bg-foreground text-background rounded-lg px-6 py-2 text-sm font-sans font-medium hover:opacity-90">
    {{ $primaryLabel }}
  </button>
  <a href="{{ $cancelHref ?? url()->previous() }}" class="border border-border text-paragraph rounded-lg px-6 py-2 text-sm font-sans hover:text-foreground">
    Cancel
  </a>
</div>

