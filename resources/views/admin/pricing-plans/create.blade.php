@extends('admin.layout')
@section('title', 'Add Plan')
@section('page-title', 'Add Plan')

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
<x-admin.form.card title="Add Plan" action="/admin/pricing-plans" method="POST" maxWidth="max-w-4xl">
    <x-admin.form.errors />

    <x-admin.form.input name="name" label="Name" placeholder="Enter plan name" required />

    <x-admin.form.textarea name="description" label="Description" rows="3" placeholder="Brief description for the landing page" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <x-admin.form.input name="price" label="Current Price ($/mo)" type="number" step="0.01" min="0" :value="old('price', '0')" placeholder="Enter current monthly price" required x-model.number="finalPrice" />
      <x-admin.form.input name="original_price" label="Original Price (Optional)" type="number" step="0.01" min="0" :value="old('original_price')" placeholder="Example: 50" x-model.number="originalPrice" />
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
              <input id="is_popular_toggle_create" class="tgl tgl-flip" type="checkbox" name="is_popular" value="1" {{ old('is_popular') ? 'checked' : '' }}>
              <label class="tgl-btn" data-tg-off="No" data-tg-on="Yes" for="is_popular_toggle_create"></label>
            </div>
          </div>
          <div class="inline-flex items-center justify-between sm:justify-start gap-2 sm:gap-3">
            <span class="text-sm text-foreground">Active</span>
            <div class="checkbox-wrapper-10">
              <input id="is_active_toggle_create" class="tgl tgl-flip" type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
              <label class="tgl-btn" data-tg-off="No" data-tg-on="Yes" for="is_active_toggle_create"></label>
            </div>
          </div>
        </div>
        <p class="text-[11px] text-paragraph/80 pt-1">Only one pricing plan can be "Most Popular". Saving this plan as popular will unset others.</p>
      </div>
    </div>

    <div class="space-y-3">
      <div class="flex items-center justify-between">
        <label class="block text-xs font-sans uppercase tracking-widest text-paragraph">Features</label>
        <button type="button" @click="addFeature()" class="px-3 py-1.5 rounded text-xs font-sans font-medium bg-foreground text-background transition hover:opacity-85">Add Feature</button>
      </div>

      <template x-for="(feature, index) in features" :key="index">
        <div class="tidy-panel rounded-lg p-3 grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-3 items-center">
          <input :name="`features[${index}][feature]`" type="text" x-model="feature.feature" placeholder="Feature name" class="sm:col-span-8 bg-background border border-border text-foreground rounded-lg px-4 py-2.5 text-sm font-sans transition-colors hover:border-primary/40 focus:outline-none focus:ring-1 focus:ring-primary">
          <div class="sm:col-span-3 inline-flex items-center justify-between sm:justify-center gap-2">
            <span class="text-xs text-foreground">Included</span>
            <div class="checkbox-wrapper-10">
              <input :id="`feature_available_create_${index}`" :name="`features[${index}][is_available]`" type="checkbox" value="1" x-model="feature.is_available" class="tgl tgl-flip">
              <label class="tgl-btn" data-tg-off="No" data-tg-on="Yes" :for="`feature_available_create_${index}`"></label>
            </div>
          </div>
          <button type="button" @click="removeFeature(index)" class="sm:col-span-1 justify-self-end text-destructive text-base px-2 py-1 rounded transition hover:bg-destructive/10">✕</button>
        </div>
      </template>
    </div>

    <div class="pt-2">
      <x-admin.form.actions primaryLabel="Save" cancelHref="/admin/pricing-plans" />
    </div>
</x-admin.form.card>
</div>
@endsection

@push('scripts')
<script>
function planForm() {
  return {
    originalPrice: {{ old('original_price') ? (float) old('original_price') : 'null' }},
    finalPrice: {{ (float) old('price', '0') }},
    features: [{ feature: '', is_available: true }],
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
    addFeature() {
      this.features.push({ feature: '', is_available: true });
    },
    removeFeature(index) {
      this.features.splice(index, 1);
      if (!this.features.length) this.addFeature();
    },
  };
}
</script>
@endpush

