@extends('admin.layout')
@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('content')
<x-admin.form.card title="Edit User" action="/admin/users/{{ $user->id }}" method="PUT" enctype="multipart/form-data" maxWidth="max-w-4xl">
  <x-admin.form.errors />

    <details open class="rounded-xl border border-border/10 bg-background/40">
      <summary class="cursor-pointer select-none px-4 py-3 text-sm font-sans font-medium text-foreground flex items-center justify-between">
        <span>Account Settings</span>
        <span class="text-paragraph text-xs">Username, role, status</span>
      </summary>
      <div class="px-4 pb-4 pt-2 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <x-admin.form.input name="username" label="Username" :value="$user->username" placeholder="Enter username" required />
          <x-admin.form.select name="role" label="Role" required>
            @php($role = old('role', $user->role ?? 'user'))
            <option value="user" {{ $role === 'user' ? 'selected' : '' }}>User</option>
            <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="super_admin" {{ $role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
            <option value="owner" {{ $role === 'owner' ? 'selected' : '' }}>Owner</option>
          </x-admin.form.select>
        </div>

        <x-admin.form.input name="email" label="Email" type="email" :value="$user->email" placeholder="Enter email address" required />

        <div class="space-y-1.5">
          <label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Account Status</label>
          <div class="inline-flex rounded-lg border border-border/10 overflow-hidden">
            @php($status = old('account_status', $user->account_status ?? 'active'))
            <label class="px-3 py-2 text-sm font-sans cursor-pointer {{ $status === 'active' ? 'bg-foreground text-background' : 'text-paragraph hover:text-foreground hover:bg-muted' }}">
              <input type="radio" name="account_status" value="active" class="sr-only" {{ $status === 'active' ? 'checked' : '' }}>
              Active
            </label>
            <label class="px-3 py-2 text-sm font-sans cursor-pointer {{ $status === 'suspended' ? 'bg-foreground text-background' : 'text-paragraph hover:text-foreground hover:bg-muted' }}">
              <input type="radio" name="account_status" value="suspended" class="sr-only" {{ $status === 'suspended' ? 'checked' : '' }}>
              Suspended
            </label>
            <label class="px-3 py-2 text-sm font-sans cursor-pointer {{ $status === 'inactive' ? 'bg-foreground text-background' : 'text-paragraph hover:text-foreground hover:bg-muted' }}">
              <input type="radio" name="account_status" value="inactive" class="sr-only" {{ $status === 'inactive' ? 'checked' : '' }}>
              Inactive
            </label>
          </div>
        </div>
      </div>
    </details>

    <details class="rounded-xl border border-border/10 bg-background/40">
      <summary class="cursor-pointer select-none px-4 py-3 text-sm font-sans font-medium text-foreground flex items-center justify-between">
        <span>Personal Information</span>
        <span class="text-paragraph text-xs">Name, phone, avatar</span>
      </summary>
      <div class="px-4 pb-4 pt-2 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <x-admin.form.input name="first_name" label="First Name" :value="$user->first_name" placeholder="Enter first name" />
          <x-admin.form.input name="last_name" label="Last Name" :value="$user->last_name" placeholder="Enter last name" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <x-admin.form.input name="phone" label="Phone Number" type="tel" :value="$user->phone" placeholder="Enter phone number" />
          <div class="space-y-1.5">
            <label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Profile Picture / Avatar</label>
            <div class="flex items-start gap-3">
              @if (!empty($user->avatar_path))
                <img src="{{ asset('storage/' . $user->avatar_path) }}" alt="Avatar"
                     class="w-12 h-12 rounded-full border border-border/10 object-cover bg-muted shrink-0">
              @else
                <div class="w-12 h-12 rounded-full border border-border/10 bg-muted shrink-0"></div>
              @endif
              <div class="flex-1 space-y-2">
                <input name="avatar" type="file" accept="image/*"
                       class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2.5 text-sm font-sans placeholder:text-paragraph/70 focus:outline-none focus:ring-1 focus:ring-primary transition-colors file:mr-3 file:rounded-md file:border-0 file:bg-muted file:px-3 file:py-1.5 file:text-xs file:font-sans file:text-foreground hover:file:bg-muted/70">
                @if (!empty($user->avatar_path))
                  <label class="inline-flex items-center gap-2 text-sm font-sans text-paragraph">
                    <input type="checkbox" name="remove_avatar" value="1" class="accent-primary">
                    Remove current avatar
                  </label>
                @endif
                <p class="text-[11px] text-paragraph">PNG/JPG/WebP. Max 5MB.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </details>

    <details class="rounded-xl border border-border/10 bg-background/40">
      <summary class="cursor-pointer select-none px-4 py-3 text-sm font-sans font-medium text-foreground flex items-center justify-between">
        <span>Preferences</span>
        <span class="text-paragraph text-xs">Timezone, language</span>
      </summary>
      <div class="px-4 pb-4 pt-2">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <x-admin.form.select name="timezone" label="Timezone">
              @php($timezone = old('timezone', $user->timezone))
              <option value="">System default</option>
              @foreach ($timezones as $tz)
                <option value="{{ $tz }}" {{ $timezone === $tz ? 'selected' : '' }}>{{ $tz }}</option>
              @endforeach
          </x-admin.form.select>
          <x-admin.form.select name="language" label="Language">
              @php($language = old('language', $user->language))
              <option value="">System default</option>
              @foreach ($languages as $code => $label)
                <option value="{{ $code }}" {{ $language === $code ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
          </x-admin.form.select>
        </div>
      </div>
    </details>

    <details class="rounded-xl border border-border/10 bg-background/40">
      <summary class="cursor-pointer select-none px-4 py-3 text-sm font-sans font-medium text-foreground flex items-center justify-between">
        <span>Organizational Details</span>
        <span class="text-paragraph text-xs">Title, assigned projects</span>
      </summary>
      <div class="px-4 pb-4 pt-2 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <x-admin.form.input name="job_title" label="Job Title / Position" :value="$user->job_title" placeholder="e.g. Project Manager" />
          <div class="space-y-1.5">
            <label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Assigned Projects</label>
            @php($selected = collect(old('assigned_projects', $selectedProjectIds ?? []))->map(fn($v) => (int) $v)->all())
            <select name="assigned_projects[]" multiple
                    class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2.5 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary transition-colors h-40">
              @foreach ($projects as $project)
                <option value="{{ $project->id }}" {{ in_array($project->id, $selected, true) ? 'selected' : '' }}>
                  {{ $project->name }}
                </option>
              @endforeach
            </select>
            <p class="text-[11px] text-paragraph">Hold Ctrl (Windows) / Cmd (Mac) to select multiple.</p>
          </div>
        </div>
      </div>
    </details>

    <x-admin.form.actions primaryLabel="Update" cancelHref="/admin/users" />
</x-admin.form.card>
@endsection
