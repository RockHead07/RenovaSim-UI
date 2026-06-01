@extends('admin.layout')
@section('title', 'Edit Plan')
@section('page-title', 'Edit Plan')

@push('head')
<style>
  .checkbox-wrapper-10 .tgl {
    display: none;
  }
  .checkbox-wrapper-10 .tgl,
  .checkbox-wrapper-10 .tgl:after,
  .checkbox-wrapper-10 .tgl:before,
  .checkbox-wrapper-10 .tgl *,
  .checkbox-wrapper-10 .tgl *:after,
  .checkbox-wrapper-10 .tgl *:before,
  .checkbox-wrapper-10 .tgl + .tgl-btn {
    box-sizing: border-box;
  }
  .checkbox-wrapper-10 .tgl + .tgl-btn {
    outline: 0;
    display: block;
    width: 4.6em;
    height: 1.9em;
    position: relative;
    cursor: pointer;
    user-select: none;
    transform: translateZ(0);
  }
  @media (min-width: 640px) {
    .checkbox-wrapper-10 .tgl + .tgl-btn {
      width: 5.2em;
      height: 2em;
    }
  }
  .checkbox-wrapper-10 .tgl + .tgl-btn:after,
  .checkbox-wrapper-10 .tgl + .tgl-btn:before {
    position: absolute;
    top: 0;
    left: 0;
    display: inline-block;
    width: 100%;
    line-height: 1.9em;
    text-align: center;
    font-size: 10px;
    font-weight: 700;
    border-radius: 8px;
    transition: all .35s ease;
    backface-visibility: hidden;
    letter-spacing: .02em;
  }
  .checkbox-wrapper-10 .tgl-flip + .tgl-btn {
    perspective: 100px;
  }
  .checkbox-wrapper-10 .tgl-flip + .tgl-btn:hover:before,
  .checkbox-wrapper-10 .tgl-flip + .tgl-btn:hover:after {
    filter: brightness(1.08);
  }
  .checkbox-wrapper-10 .tgl-flip + .tgl-btn:before {
    content: attr(data-tg-off);
    background: #3a3a3a;
    color: #b8b8b8;
    border: 1px solid rgba(245, 245, 245, 0.1);
  }
  .checkbox-wrapper-10 .tgl-flip + .tgl-btn:after {
    content: attr(data-tg-on);
    transform: rotateY(-180deg);
    background: #8ba023;
    color: #111;
    border: 1px solid rgba(139, 160, 35, 0.4);
  }
  .checkbox-wrapper-10 .tgl-flip:checked + .tgl-btn:before {
    transform: rotateY(180deg);
  }
  .checkbox-wrapper-10 .tgl-flip:checked + .tgl-btn:after {
    transform: rotateY(0);
  }
  .tidy-panel {
    background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.015));
    border: 1px solid rgba(245,245,245,0.08);
  }
</style>
@endpush

@section('content')
<div x-data="planForm()">
<x-admin.form.card title="Edit Plan" action="/admin/pricing-plans/{{ $plan->id }}" method="PUT" maxWidth="max-w-4xl">
    <x-admin.form.errors />

    <x-admin.form.input name="name" label="Name" :value="$plan->name" placeholder="Enter plan name" required />

    <x-admin.form.textarea name="description" label="Description" :value="$plan->description" rows="3" placeholder="Brief description for the landing page" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <x-admin.form.input name="price" label="Current Price ($/mo)" type="number" step="0.01" min="0" :value="$plan->price" placeholder="Enter current monthly price" required x-model.number="finalPrice" />
      <x-admin.form.input name="original_price" label="Original Price (Optional)" type="number" step="0.01" min="0" :value="$plan->original_price" placeholder="Example: 50" x-model.number="originalPrice" />
    </div>

    <div class="tidy-panel rounded-lg p-3 sm:p-4 grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4">
      <p class="text-xs text-paragraph">
        Marketing preview:
        <span class="text-foreground font-semibold" x-text="discountLabel()"></span>
      </p>
      <p class="text-xs text-paragraph">
        Discount:
        <span class="text-status-active font-semibold" x-text="discountPercent() + '%'"></span>
      </p>
      <p class="text-xs text-paragraph">
        You save:
        <span class="text-status-warning font-semibold" x-text="'$' + savingsAmount()"></span>
      </p>
    </div>
    <p x-show="hasInvalidDiscount()" class="text-xs text-destructive -mt-2" style="display:none">
      Original price must be greater than current price to apply a discount.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="space-y-1.5">
        <label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Visibility</label>
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-2">
          <div class="inline-flex items-center justify-between sm:justify-start gap-2 sm:gap-3">
            <span class="text-sm text-foreground">Popular</span>
            <div class="checkbox-wrapper-10">
              <input id="is_popular_toggle_edit" class="tgl tgl-flip" type="checkbox" name="is_popular" value="1" {{ old('is_popular', $plan->is_popular) ? 'checked' : '' }}>
              <label class="tgl-btn" data-tg-off="No" data-tg-on="Yes" for="is_popular_toggle_edit"></label>
            </div>
          </div>
          <div class="inline-flex items-center justify-between sm:justify-start gap-2 sm:gap-3">
            <span class="text-sm text-foreground">Active</span>
            <div class="checkbox-wrapper-10">
              <input id="is_active_toggle_edit" class="tgl tgl-flip" type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
              <label class="tgl-btn" data-tg-off="No" data-tg-on="Yes" for="is_active_toggle_edit"></label>
            </div>
          </div>
        </div>
        <p class="text-[11px] text-paragraph/80 pt-1">Only one pricing plan can be "Most Popular". Saving this plan as popular will unset others.</p>
      </div>
    </div>

    <div class="space-y-3">
      <label class="block text-xs font-sans uppercase tracking-widest text-paragraph">Features</label>
      <div class="tidy-panel rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-white/10">
              <th class="text-left px-4 py-2.5 text-[10px] uppercase tracking-widest text-paragraph font-normal w-48">Key</th>
              <th class="text-left px-4 py-2.5 text-[10px] uppercase tracking-widest text-paragraph font-normal">Label</th>
              <th class="text-left px-4 py-2.5 text-[10px] uppercase tracking-widest text-paragraph font-normal w-36">Value</th>
              <th class="text-left px-4 py-2.5 text-[10px] uppercase tracking-widest text-paragraph font-normal w-28">Available</th>
            </tr>
          </thead>
          <tbody>
            @foreach($plan->features as $i => $feature)
            <tr class="border-b border-white/5 last:border-0">
              <td class="px-4 py-3">
                <input type="hidden" name="features[{{ $i }}][feature_key]" value="{{ $feature->feature_key }}">
                <span class="text-xs font-mono text-paragraph bg-white/5 px-2 py-1 rounded whitespace-nowrap">{{ $feature->feature_key }}</span>
              </td>
              <td class="px-4 py-3">
                <input type="text" name="features[{{ $i }}][feature_label]"
                       value="{{ old("features.$i.feature_label", $feature->feature_label) }}"
                       class="w-full bg-background border border-border text-foreground rounded-lg px-3 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary hover:border-primary/40 transition-colors">
              </td>
              <td class="px-4 py-3">
                <input type="text" name="features[{{ $i }}][feature_value]"
                       value="{{ old("features.$i.feature_value", $feature->feature_value) }}"
                       placeholder="2 / unlimited / true"
                       class="w-full bg-background border border-border text-foreground rounded-lg px-3 py-2 text-sm font-sans font-mono focus:outline-none focus:ring-1 focus:ring-primary hover:border-primary/40 transition-colors">
              </td>
              <td class="px-4 py-3">
                <div class="checkbox-wrapper-10">
                  <input id="feat_avail_{{ $i }}" name="features[{{ $i }}][is_available]"
                         type="checkbox" value="1" class="tgl tgl-flip"
                         {{ old("features.$i.is_available", $feature->is_available) ? 'checked' : '' }}>
                  <label class="tgl-btn" data-tg-off="No" data-tg-on="Yes" for="feat_avail_{{ $i }}"></label>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="pt-2">
      <x-admin.form.actions primaryLabel="Update" cancelHref="/admin/pricing-plans" />
    </div>
</x-admin.form.card>
</div>
@endsection

@push('scripts')
<script>
function planForm() {
  return {
    originalPrice: {{ old('original_price', $plan->original_price) ? (float) old('original_price', $plan->original_price) : 'null' }},
    finalPrice: {{ (float) old('price', $plan->price) }},
    discountPercent() {
      if (!this.originalPrice || !this.finalPrice || this.originalPrice <= this.finalPrice) return 0;
      return Math.round(((this.originalPrice - this.finalPrice) / this.originalPrice) * 100);
    },
    savingsAmount() {
      if (!this.originalPrice || !this.finalPrice || this.originalPrice <= this.finalPrice) return '0.00';
      return (this.originalPrice - this.finalPrice).toFixed(2);
    },
    discountLabel() {
      if (!this.originalPrice || this.originalPrice <= this.finalPrice) {
        return `$${(this.finalPrice || 0).toFixed(2)}/mo`;
      }
      return `$${this.originalPrice.toFixed(2)} -> $${this.finalPrice.toFixed(2)}/mo`;
    },
    hasInvalidDiscount() {
      return !!this.originalPrice && !!this.finalPrice && this.originalPrice <= this.finalPrice;
    },
  };
}
</script>
@endpush

