@extends('admin.layout')

@section('title', 'Edit Project')
@section('page-title', 'Edit Project')

@section('content')
<x-admin.form.card title="Edit Project" action="/admin/projects/{{ $project->id }}" method="PUT">
  <x-admin.form.errors />

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-admin.form.input name="name" label="Project Name" :value="$project->name" placeholder="Enter project name" required />
    <x-admin.form.input name="room_type" label="Room Type" :value="$project->room_type" placeholder="Enter room type" required />
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-admin.form.input name="area_size" label="Area Size (m²)" type="number" step="0.01" :value="$project->area_size" placeholder="0.00" required />
  </div>

  <x-admin.form.actions primaryLabel="Update" cancelHref="/admin/projects" />
</x-admin.form.card>
@endsection