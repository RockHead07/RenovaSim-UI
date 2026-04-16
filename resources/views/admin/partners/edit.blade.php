@extends('admin.layout')
@section('title', 'Edit Partner')
@section('page-title', 'Edit Partner')
@section('content')
<x-admin.form.card title="Edit Partner" action="/admin/partners/{{ $partner->id ?? 1 }}" method="PUT" enctype="multipart/form-data">
  <x-admin.form.errors />

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-admin.form.input name="name" label="Partner Name" :value="$partner->name" placeholder="Enter partner name" />
    <x-admin.form.input name="logo" label="Logo Initials" :value="$partner->logo" placeholder="e.g. BM" />
  </div>

  <div class="space-y-1.5">
    <label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Logo Image</label>
    @if($partner->logo_image)
      <div class="mb-3 p-3 bg-muted rounded-lg border border-border/10">
        <p class="text-xs text-paragraph mb-2">Current image:</p>
        <img src="{{ asset('storage/' . $partner->logo_image) }}" alt="{{ $partner->name }}" class="max-w-xs max-h-40 rounded">
      </div>
    @endif
    <div id="dropZone" class="relative w-full bg-background border-2 border-dashed border-border rounded-lg px-4 py-8 text-center cursor-pointer transition-colors hover:border-primary hover:bg-background/50 focus-within:ring-1 focus-within:ring-primary"
         ondragover="this.classList.add('border-primary', 'bg-background/50')" ondragleave="this.classList.remove('border-primary', 'bg-background/50')" ondrop="handleDrop(event)">
      <input type="file" id="logoInput" name="logo_image" accept="image/*" class="hidden" onchange="handleFileSelect(event)">
      <div id="uploadContent">
        <svg class="mx-auto mb-2 w-12 h-12 text-paragraph/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        <p class="text-sm text-foreground mb-1">Drag and drop your logo here or <span class="text-primary font-medium">browse</span></p>
        <p class="text-xs text-paragraph">PNG, JPG, GIF (Max 5MB)</p>
      </div>
      <div id="previewContent" class="hidden">
        <img id="preview" src="" alt="Logo preview" class="max-w-xs max-h-40 mx-auto rounded mb-3">
        <p class="text-xs text-paragraph mb-2">File ready to upload</p>
        <button type="button" onclick="clearUpload()" class="text-xs text-primary hover:underline">Choose another file</button>
      </div>
    </div>
    <p id="errorMsg" class="text-xs text-red-500 mt-1 hidden"></p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-admin.form.input name="order" label="Display Order" type="number" :value="$partner->order" placeholder="Enter order number" />
    <x-admin.form.select name="status" label="Status">
      @php($status = old('status', $partner->is_active ? 'Active' : 'Inactive'))
      <option value="Active" {{ $status === 'Active' ? 'selected' : '' }}>Active</option>
      <option value="Inactive" {{ $status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
    </x-admin.form.select>
  </div>

  <x-admin.form.actions primaryLabel="Update" cancelHref="/admin/partners" />
</x-admin.form.card>
  
  <script>
    const dropZone = document.getElementById('dropZone');
    const logoInput = document.getElementById('logoInput');
    const uploadContent = document.getElementById('uploadContent');
    const previewContent = document.getElementById('previewContent');
    const preview = document.getElementById('preview');
    const errorMsg = document.getElementById('errorMsg');
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    const ALLOWED_TYPES = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];

    dropZone.addEventListener('click', () => logoInput.click());

    dropZone.addEventListener('dragover', (e) => {
      e.preventDefault();
      dropZone.classList.add('border-primary', 'bg-background/50');
    });

    dropZone.addEventListener('dragleave', () => {
      dropZone.classList.remove('border-primary', 'bg-background/50');
    });

    dropZone.addEventListener('drop', (e) => {
      e.preventDefault();
      dropZone.classList.remove('border-primary', 'bg-background/50');
      handleDrop(e);
    });

    function handleDrop(e) {
      const files = e.dataTransfer?.files || e.target.files;
      if (files && files.length > 0) {
        handleFile(files[0]);
      }
    }

    function handleFileSelect(e) {
      handleFile(e.target.files[0]);
    }

    function handleFile(file) {
      errorMsg.classList.add('hidden');
      
      if (!ALLOWED_TYPES.includes(file.type)) {
        errorMsg.textContent = 'Invalid file type. Please upload PNG, JPG, GIF, or WebP.';
        errorMsg.classList.remove('hidden');
        clearUpload();
        return;
      }

      if (file.size > MAX_FILE_SIZE) {
        errorMsg.textContent = 'File size exceeds 5MB limit.';
        errorMsg.classList.remove('hidden');
        clearUpload();
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        preview.src = e.target.result;
        uploadContent.classList.add('hidden');
        previewContent.classList.remove('hidden');
      };
      reader.readAsDataURL(file);
    }

    function clearUpload() {
      logoInput.value = '';
      preview.src = '';
      uploadContent.classList.remove('hidden');
      previewContent.classList.add('hidden');
      errorMsg.classList.add('hidden');
    }
  </script>
  @endsection
  
