@extends('admin.layout')

@section('title', 'Add Project')
@section('page-title', 'Add Project')

@section('content')
<x-admin.form.card title="Add Project" action="/admin/projects" method="POST">
  <x-admin.form.errors />

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-admin.form.input name="name" label="Project Name" placeholder="Enter project name" required />
    <x-admin.form.input name="room_type" label="Room Type" placeholder="Enter room type" required />
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-admin.form.input name="area_size" label="Area Size (m²)" type="number" step="0.01" placeholder="0.00" required />
  </div>

  <x-admin.form.actions primaryLabel="Save" cancelHref="/admin/projects" />
</x-admin.form.card>
@endsection