@extends('admin.layout')
  @section('title', 'Add Plan')
  @section('page-title', 'Add Plan')
  @section('content')
  <div class="bg-card rounded-xl w-full max-w-2xl border border-border/10">
    <div class="p-6 pb-0"><h2 class="font-serif text-foreground text-lg">Add Plan</h2></div>
    <form method="POST" action="/admin/pricing-plans" class="p-6 space-y-5">
      @csrf
      
      <div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Name</label><input name="name" type="text" placeholder="Enter plan name" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary"></div>
<div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Price ($/mo)</label><input name="price" type="number" placeholder="Enter monthly price" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary"></div>
<div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Popular</label><select name="popular" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary"><option value="Yes">Yes</option><option value="No">No</option></select></div>
<div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Active</label><select name="active" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary"><option value="Yes">Yes</option><option value="No">No</option></select></div>
      <div class="flex gap-3 pt-2"><button type="submit" class="bg-foreground text-background rounded-lg px-6 py-2 text-sm font-sans font-medium hover:opacity-90">Save</button><a href="javascript:history.back()" class="border border-border text-paragraph rounded-lg px-6 py-2 text-sm font-sans hover:text-foreground">Cancel</a></div>
    </form>
  </div>
  @endsection
  
