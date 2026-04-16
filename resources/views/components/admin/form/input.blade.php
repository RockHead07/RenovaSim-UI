@props([
  'name',
  'label',
  'type' => 'text',
  'value' => null,
  'placeholder' => null,
  'required' => false,
  'step' => null,
  'min' => null,
  'max' => null,
])

@php
  $inputClass = 'w-full bg-background border border-border text-foreground rounded-lg px-4 py-2.5 text-sm font-sans placeholder:text-paragraph/70 focus:outline-none focus:ring-1 focus:ring-primary transition-colors';
@endphp

<div class="space-y-1.5">
  <label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">{{ $label }}</label>
  <input
    name="{{ $name }}"
    type="{{ $type }}"
    value="{{ old($name, $value) }}"
    @if(!is_null($placeholder)) placeholder="{{ $placeholder }}" @endif
    @if($required) required @endif
    @if(!is_null($step)) step="{{ $step }}" @endif
    @if(!is_null($min)) min="{{ $min }}" @endif
    @if(!is_null($max)) max="{{ $max }}" @endif
    {{ $attributes->merge(['class' => $inputClass]) }}
  >
</div>

