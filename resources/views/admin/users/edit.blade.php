@extends('admin.layout')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" defer></script>
@endpush

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

    <details open class="rounded-xl border border-border/10 bg-background/40">
        <summary class="cursor-pointer select-none px-4 py-3 text-sm font-sans font-medium text-foreground flex items-center justify-between">
            <span>Pricing Plan</span>
            <span class="text-paragraph text-xs">Assign plan manual</span>
        </summary>
        <div class="px-4 pb-4 pt-2 space-y-3">
            <p class="text-xs text-muted-foreground">Assign plan jika user sudah melakukan pembayaran di luar sistem.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach($pricingPlans as $plan)
                <label class="relative cursor-pointer">
                    <input type="radio" name="pricing_plan_id" value="{{ $plan->id }}"
                           class="sr-only peer"
                           {{ old('pricing_plan_id', $user->pricing_plan_id) == $plan->id ? 'checked' : '' }}>
                    <div class="border border-border/20 rounded-xl p-4 peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-foreground">{{ $plan->name }}</span>
                            @if($plan->is_popular)
                                <span class="text-[10px] bg-primary/15 text-primary px-2 py-0.5 rounded-full">Popular</span>
                            @endif
                        </div>
                        <p class="text-xs text-muted-foreground font-mono">{{ $plan->slug }}</p>
                        <p class="text-sm font-medium text-foreground mt-1">
                            {{ $plan->price > 0 ? 'Rp ' . number_format($plan->price, 0, ',', '.') : 'Gratis' }}
                        </p>
                    </div>
                </label>
                @endforeach
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
          <div class="space-y-1.5" x-data="adminAvatarCrop()">
            <label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Profile Picture / Avatar</label>

            {{-- Hidden base64 input --}}
            <input type="hidden" name="avatar_base64" x-model="croppedBase64">

            <div class="flex items-center gap-3">
              {{-- Avatar preview --}}
              <div class="w-12 h-12 rounded-full border border-border/10 bg-muted shrink-0 overflow-hidden flex items-center justify-center">
                <template x-if="previewUrl">
                  <img :src="previewUrl" class="w-full h-full object-cover" alt="Avatar">
                </template>
                <template x-if="!previewUrl">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-paragraph" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </template>
              </div>

              <div class="flex flex-col gap-1.5">
                <button type="button" @click="$refs.fileInput.click()"
                  class="inline-flex items-center gap-1.5 text-xs font-sans text-primary hover:opacity-80 transition-opacity">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                  Upload Photo
                </button>
                <input x-ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileSelect">
                @if (!empty($user->avatar_path))
                  <label class="inline-flex items-center gap-1.5 text-xs font-sans text-paragraph cursor-pointer">
                    <input type="checkbox" name="remove_avatar" value="1" class="accent-primary">
                    Remove current avatar
                  </label>
                @endif
                <p class="text-[11px] text-paragraph">PNG/JPG/WebP. Max 5MB.</p>
              </div>
            </div>

            {{-- Crop Modal --}}
            <div x-show="showModal" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
                 style="display: none">
              <div class="bg-card rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-border/10">
                <div class="flex items-center justify-between px-5 py-4 border-b border-border/10">
                  <p class="text-sm font-sans font-medium text-foreground">Crop Profile Photo</p>
                  <button type="button" @click="closeModal()" class="text-paragraph hover:text-foreground">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  </button>
                </div>
                <div class="p-4 bg-black/90" style="max-height: 380px; overflow: hidden">
                  <img id="adminCropperImage" style="max-width: 100%; display: block">
                </div>
                <div class="px-5 py-4 flex gap-3 justify-end border-t border-border/10">
                  <button type="button" @click="closeModal()"
                    class="px-4 py-2 text-sm font-sans text-paragraph hover:text-foreground transition-colors">
                    Cancel
                  </button>
                  <button type="button" @click="applyCrop()"
                    class="px-5 py-2 bg-primary text-primary-foreground text-sm font-sans rounded-lg hover:opacity-90 transition-opacity">
                    Apply Crop
                  </button>
                </div>
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

@push('scripts')
<script>
function adminAvatarCrop() {
    return {
        showModal: false,
        cropper: null,
        croppedBase64: '',
        previewUrl: '{{ $user->avatar_url ?? '' }}',

        onFileSelect(e) {
            const file = e.target.files[0];
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be under 5MB.');
                return;
            }
            const reader = new FileReader();
            reader.onload = (ev) => {
                const img = document.getElementById('adminCropperImage');
                img.src = ev.target.result;
                this.showModal = true;
                this.$nextTick(() => {
                    if (this.cropper) this.cropper.destroy();
                    this.cropper = new Cropper(img, {
                        aspectRatio: 1,
                        viewMode: 2,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        cropBoxResizable: true,
                        cropBoxMovable: true,
                        background: false,
                    });
                });
            };
            reader.readAsDataURL(file);
        },

        applyCrop() {
            if (!this.cropper) return;
            const canvas = this.cropper.getCroppedCanvas({ width: 256, height: 256 });
            this.croppedBase64 = canvas.toDataURL('image/jpeg', 0.85);
            this.previewUrl = this.croppedBase64;
            this.closeModal();
        },

        closeModal() {
            this.showModal = false;
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
        }
    }
}
</script>
@endpush
