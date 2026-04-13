@extends('admin.layout')
  @section('title', 'Partners')
  @section('page-title', 'Partners')
  @section('content')
  <div class="space-y-4">
    
    
    <div class="hidden sm:block bg-card rounded-[10px] overflow-hidden border border-border/10">
      <div class="flex items-center justify-between px-5 py-4"><h3 class="font-serif text-foreground text-base">Partners</h3><a href="/admin/partners/create" class="px-3 py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background">+ Add Partner</a></div>
      <div class="overflow-x-auto"><table class="w-full"><thead><tr class="border-b border-border/10"><th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Name</th><th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Logo</th><th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Order</th><th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Status</th><th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Actions</th></tr></thead><tbody>@forelse($partners as $partner)
<tr class="hover:bg-muted/50 transition-colors duration-200 border-b border-border/5"><td class="px-5 py-3 text-sm font-sans text-foreground">{{ $partner->name }}</td><td class="px-5 py-3 text-sm font-sans text-foreground"><div class="w-8 h-8 rounded bg-primary flex items-center justify-center text-foreground text-xs font-sans font-medium">{{ strtoupper(substr($partner->name, 0, 2)) }}</div></td><td class="px-5 py-3 text-sm font-sans text-foreground"><span class="text-paragraph">{{ $partner->order }}</span></td><td class="px-5 py-3 text-sm font-sans text-foreground"><span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium bg-status-active/15 text-status-active">Active</span></td><td class="px-5 py-3 text-sm font-sans text-foreground"><div class="flex gap-2"><a href="/admin/partners/{{ $partner->id }}/edit" class="px-3 py-1 rounded text-xs font-sans font-medium bg-foreground text-background">Edit</a><form method="POST" action="/admin/partners/{{ $partner->id }}" style="display:inline" onsubmit="return confirm('Are you sure?')">@csrf @method('DELETE')<button type="submit" class="px-3 py-1 rounded text-xs font-sans font-medium bg-destructive/15 text-destructive">Delete</button></form></div></td></tr>
@empty
<tr><td colspan="5" class="px-5 py-3 text-center text-paragraph">No partners found</td></tr>
@endforelse</tbody></table></div>
    </div>
  </div>
  @endsection
  
