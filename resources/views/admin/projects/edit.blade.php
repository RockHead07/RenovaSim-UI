@extends('admin.layout')

@section('title', 'Edit Project')
@section('page-title', 'Edit Project')

@section('content')
<div class="bg-card rounded-[14px] border border-border/10 p-6 max-w-md">
    <h2 class="font-serif text-foreground text-lg mb-4">Edit Project</h2>
    <form action="/admin/projects/{{ $project->id }}" method="POST">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div class="space-y-1.5">
                <label class="text-xs font-sans text-paragraph uppercase tracking-widest">Project Name</label>
                <input type="text" name="name" value="{{ $project->name }}" required class="w-full bg-background rounded-lg px-4 py-2 text-sm font-sans text-foreground focus:outline-none border border-border/10"/>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-sans text-paragraph uppercase tracking-widest">Room Type</label>
                <input type="text" name="room_type" value="{{ $project->room_type }}" required class="w-full bg-background rounded-lg px-4 py-2 text-sm font-sans text-foreground focus:outline-none border border-border/10"/>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-sans text-paragraph uppercase tracking-widest">Area Size (m²)</label>
                <input type="number" step="0.01" name="area_size" value="{{ $project->area_size }}" required class="w-full bg-background rounded-lg px-4 py-2 text-sm font-sans text-foreground focus:outline-none border border-border/10"/>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <a href="/admin/projects" class="px-4 py-2 rounded-lg text-xs font-sans font-medium text-paragraph hover:text-foreground transition-colors">Cancel</a>
            <button type="submit" class="px-4 py-2 rounded-lg text-xs font-sans font-medium bg-primary text-white">Update</button>
        </div>
    </form>
</div>
@endsection