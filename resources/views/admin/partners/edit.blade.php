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
        <img src="{{ \Illuminate\Support\Facades\Storage::url($partner->logo_image) }}" alt="{{ $partner->name }}" class="max-w-xs max-h-40 rounded">
      </div>
    @endif
    <div
      id="dropZone"
      class="relative w-full bg-background border-2 border-dashed border-border rounded-lg px-4 py-8 text-center cursor-pointer transition-colors hover:border-primary hover:bg-background/50 focus-within:ring-1 focus-within:ring-primary"
      role="button"
      tabindex="0"
      aria-label="Upload logo image (drag and drop or browse)"
    >
      <input type="file" id="logoInput" name="logo_image" accept="image/png,image/jpeg,image/gif" class="hidden">
      <div id="uploadContent">
        <svg class="mx-auto mb-2 w-12 h-12 text-paragraph/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        <p class="text-sm text-foreground mb-1">Drag and drop your logo here or <span class="text-primary font-medium">browse</span></p>
        <p class="text-xs text-paragraph">PNG, JPG, GIF (Max 5MB)</p>
      </div>
      <div id="previewContent" class="hidden">
        <img id="preview" src="" alt="Logo preview" class="max-w-xs max-h-40 mx-auto rounded mb-3">
        <p id="fileReadyText" class="text-xs text-paragraph mb-2">File ready to upload</p>
        <p id="fileName" class="text-xs text-paragraph mb-3"></p>
        <button type="button" id="clearUploadBtn" class="text-xs text-primary hover:underline">Choose another file</button>
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

    const fileNameEl = document.getElementById('fileName');
    const clearUploadBtn = document.getElementById('clearUploadBtn');

    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    const ALLOWED_MIME_TYPES = ['image/png', 'image/jpeg', 'image/gif'];
    const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif'];

    function getFileExtension(file) {
      const name = (file && file.name) ? file.name : '';
      const parts = name.split('.');
      return parts.length > 1 ? parts.pop().toLowerCase() : '';
    }

    function isAllowedFile(file) {
      if (!file) return false;
      const type = (file.type || '').toLowerCase();
      if (ALLOWED_MIME_TYPES.includes(type)) return true;
      // Fallback: some browsers may not provide correct MIME type for dragged files.
      return ALLOWED_EXTENSIONS.includes(getFileExtension(file));
    }

    function setHoverState(isHover) {
      if (isHover) {
        dropZone.classList.add('border-primary', 'bg-background/50');
        dropZone.classList.remove('border-border');
        dropZone.classList.add('ring-1', 'ring-primary/30');
      } else {
        dropZone.classList.remove('border-primary', 'bg-background/50');
        dropZone.classList.remove('ring-1', 'ring-primary/30');
        dropZone.classList.add('border-border');
      }
    }

    let dragDepth = 0;

    dropZone.addEventListener('click', () => logoInput.click());
    dropZone.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        logoInput.click();
      }
    });

    logoInput.addEventListener('change', (e) => {
      handleFile(e.target.files && e.target.files.length ? e.target.files[0] : null);
    });

    dropZone.addEventListener('dragenter', (e) => {
      e.preventDefault();
      e.stopPropagation();
      dragDepth += 1;
      setHoverState(true);
    });

    dropZone.addEventListener('dragover', (e) => {
      e.preventDefault();
      e.stopPropagation();
      e.dataTransfer.dropEffect = 'copy';
      setHoverState(true);
    });

    dropZone.addEventListener('dragleave', (e) => {
      e.preventDefault();
      e.stopPropagation();
      dragDepth -= 1;
      if (dragDepth <= 0) {
        dragDepth = 0;
        setHoverState(false);
      }
    });

    dropZone.addEventListener('drop', (e) => {
      e.preventDefault();
      e.stopPropagation();
      dragDepth = 0;
      setHoverState(false);

      const files = e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files : null;
      if (files && files.length > 0) {
        handleFile(files[0], e);
      }
    });

    function handleFile(file) {
      errorMsg.classList.add('hidden');
      
      if (!file) {
        clearUpload();
        return;
      }

      if (!isAllowedFile(file)) {
        errorMsg.textContent = 'Invalid file type. Please upload PNG, JPG, or GIF.';
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

      // Show filename immediately for better perceived responsiveness.
      fileNameEl.textContent = file.name || 'Selected file';

      const reader = new FileReader();
      reader.onload = (e) => {
        preview.src = e.target.result;
        uploadContent.classList.add('hidden');
        previewContent.classList.remove('hidden');
      };
      reader.readAsDataURL(file);

      // Critical: ensure the dropped file is actually submitted with the form.
      try {
        const dt = new DataTransfer();
        dt.items.add(file);
        logoInput.files = dt.files;
      } catch (err) {
        // If DataTransfer isn't supported, the preview will still work but the submit might not include the file.
        // (Most modern browsers support this.)
      }
    }

    function clearUpload() {
      logoInput.value = '';
      preview.src = '';
      fileNameEl.textContent = '';
      uploadContent.classList.remove('hidden');
      previewContent.classList.add('hidden');
      errorMsg.classList.add('hidden');
    }

    clearUploadBtn.addEventListener('click', () => clearUpload());
  </script>
  @endsection
  
