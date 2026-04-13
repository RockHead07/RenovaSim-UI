@extends('admin.layout')
  @section('title', 'Add Material')
  @section('page-title', 'Add Material')
  @section('content')
  <div class="bg-card rounded-xl w-full max-w-2xl border border-border/10">
    <div class="p-6 pb-0"><h2 class="font-serif text-foreground text-lg">Add Material</h2></div>
    <form method="POST" action="/admin/materials" class="p-6 space-y-5">
      @csrf
      <div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Name</label><input name="name" type="text" placeholder="Enter material name" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary" required></div>
<div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Category</label><input name="category" type="text" placeholder="Enter category" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary" required></div>
<div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Price Per Unit</label><input name="price_per_unit" type="number" step="0.01" placeholder="Enter price per unit" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary" required></div>
<div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Unit</label><input name="unit" type="text" placeholder="e.g., m², piece, liter" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary" required></div>
      <div class="flex gap-3 pt-2"><button type="submit" class="bg-foreground text-background rounded-lg px-6 py-2 text-sm font-sans font-medium hover:opacity-90">Save</button><a href="/admin/materials" class="border border-border text-paragraph rounded-lg px-6 py-2 text-sm font-sans hover:text-foreground">Cancel</a></div>
    </form>
  </div>
  @endsection
