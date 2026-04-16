@props([
  'name',
  'label',
  'value' => null,
  'placeholder' => null,
  'required' => false,
  'rows' => 3,
])

@php
  $inputClass = 'w-full bg-background border border-border text-foreground rounded-lg px-4 py-2.5 text-sm font-sans placeholder:text-paragraph/70 focus:outline-none focus:ring-1 focus:ring-primary transition-colors';
@endphp

<div class="space-y-1.5">
  <label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">{{ $label }}</label>
  <textarea
    name="{{ $name }}"
    rows="{{ $rows }}"
    @if(!is_null($placeholder)) placeholder="{{ $placeholder }}" @endif
    @if($required) required @endif
    {{ $attributes->merge(['class' => $inputClass]) }}
  >{{ old($name, $value) }}</textarea>
</div>

