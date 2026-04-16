@extends('admin.layout')
@section('title', 'Edit Material')
@section('page-title', 'Edit Material')
@section('content')
<x-admin.form.card title="Edit Material" action="/admin/materials/{{ $material->id }}" method="PUT">
  <x-admin.form.errors />

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-admin.form.input name="name" label="Name" :value="$material->name" placeholder="Enter material name" required />
    <x-admin.form.input name="category" label="Category" :value="$material->category" placeholder="Enter category" required />
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-admin.form.input name="price_per_unit" label="Price Per Unit" type="number" step="0.01" :value="$material->price_per_unit" placeholder="0.00" required />
    <x-admin.form.input name="unit" label="Unit" :value="$material->unit" placeholder="e.g., m², piece, liter" required />
  </div>

  <x-admin.form.actions primaryLabel="Update" cancelHref="/admin/materials" />
</x-admin.form.card>
@endsection
