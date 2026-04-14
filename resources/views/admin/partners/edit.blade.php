@extends('admin.layout')
  @section('title', 'Edit Partner')
  @section('page-title', 'Edit Partner')
  @section('content')
  <div class="bg-card rounded-xl w-full max-w-2xl border border-border/10">
    
    @if ($errors->any())
    <div class="p-4 mb-4 bg-red-500/10 border border-red-500/20 rounded-lg">
      <p class="text-red-500 text-sm font-medium">Please fix the following errors:</p>
      <ul class="text-red-400 text-xs mt-2 space-y-1">
        @foreach ($errors->all() as $error)
          <li>• {{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <form method="POST" action="/admin/partners/{{ $partner->id ?? 1 }}" enctype="multipart/form-data" class="p-6 space-y-5">
      @csrf
      @method('PUT')
      <div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Partner Name</label><input name="name" type="text" placeholder="Enter partner name" value="{{ $partner->name }}" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary"></div>
<div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Logo Initials</label><input name="logo" type="text" placeholder="e.g. BM" value="{{ $partner->logo }}" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary"></div><div class="space-y-1.5">
  <label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Logo Image</label>
  @if($partner->logo_image)
  <div class="mb-3 p-3 bg-muted rounded-lg">
    <p class="text-xs text-paragraph mb-2">Current image:</p>
    <img src="{{ asset('storage/' . $partner->logo_image) }}" alt="{{ $partner->name }}" class="max-w-xs max-h-40 rounded">
  </div>
  @endif
  <div id="dropZone" class="relative w-full bg-background border-2 border-dashed border-border rounded-lg px-4 py-8 text-center cursor-pointer transition-colors hover:border-primary hover:bg-background/50" ondragover="this.classList.add('border-primary', 'bg-background/50')" ondragleave="this.classList.remove('border-primary', 'bg-background/50')" ondrop="handleDrop(event)">
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
</div><div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Display Order</label><input name="order" type="number" placeholder="Enter order number" value="{{ $partner->order }}" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary"></div>
<div class="space-y-1.5"><label class="block text-xs font-sans uppercase tracking-widest text-paragraph mb-1.5">Status</label><select name="status" class="w-full bg-background border border-border text-foreground rounded-lg px-4 py-2 text-sm font-sans focus:outline-none focus:ring-1 focus:ring-primary"><option value="Active" {{ $partner->is_active ? 'selected' : '' }}>Active</option><option value="Inactive" {{ !$partner->is_active ? 'selected' : '' }}>Inactive</option></select></div>
      <div class="flex gap-3 pt-2"><button type="submit" class="bg-foreground text-background rounded-lg px-6 py-2 text-sm font-sans font-medium hover:opacity-90">Save</button><a href="javascript:history.back()" class="border border-border text-paragraph rounded-lg px-6 py-2 text-sm font-sans hover:text-foreground">Cancel</a></div>
    </form>
  </div>
  
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
  
