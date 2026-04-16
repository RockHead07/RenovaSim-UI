@props([
  'name',
  'label',
  'required' => false,
])

@php
  $inputClass = 'w-full bg-background border border-border text-foreground rounded-lg px-4 py-2.5 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary transition-colors';
@endphp

<div class="space-y-1.5">
  <label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">{{ $label }}</label>
  <select name="{{ $name }}" @if($required) required @endif {{ $attributes->merge(['class' => $inputClass]) }}>
    {{ $slot }}
  </select>
</div>

