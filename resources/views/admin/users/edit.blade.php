@extends('admin.layout')
  @section('title', 'Edit User')
  @section('page-title', 'Edit User')
  @section('content')
  <div class="bg-card rounded-xl w-full max-w-2xl border border-border/10">
    <div class="p-6 pb-0"><h2 class="font-serif text-foreground text-lg">Edit User</h2></div>
    <form method="POST" action="/admin/users/{{ $user->id }}" class="p-6 space-y-5">
      @csrf
      @method('PUT')
      <div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Username</label><input name="username" type="text" placeholder="Enter username" value="{{ $user->username }}" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary" required></div>
<div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Email</label><input name="email" type="email" placeholder="Enter email address" value="{{ $user->email }}" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary" required></div>
      <div class="flex gap-3 pt-2"><button type="submit" class="bg-foreground text-background rounded-lg px-6 py-2 text-sm font-sans font-medium hover:opacity-90">Update</button><a href="/admin/users" class="border border-border text-paragraph rounded-lg px-6 py-2 text-sm font-sans hover:text-foreground">Cancel</a></div>
    </form>
  </div>
  @endsection
