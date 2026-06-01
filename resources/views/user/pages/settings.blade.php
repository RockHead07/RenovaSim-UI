@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" defer></script>
@endpush

<x-user::layouts.dashboard title="RenovaSim — Settings">
    <div class="flex-1 py-6 px-4">
        <div class="max-w-[680px] mx-auto">

            {{-- Header --}}
            <div class="mb-6">
                <p class="font-['Playfair_Display'] italic text-xl text-card-foreground">Account Settings</p>
                <p class="font-['DM_Sans'] text-sm text-muted-foreground mt-0.5">Kelola profil dan preferensi akun kamu</p>
            </div>

            {{-- Success alerts --}}
            @if(session('success_profile'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-xl text-sm">
                    ✓ {{ session('success_profile') }}
                </div>
            @endif
            @if(session('success_password'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-xl text-sm">
                    ✓ {{ session('success_password') }}
                </div>
            @endif

            {{-- SECTION 1: Profil --}}
            <div class="bg-card rounded-2xl shadow-sm p-6 mb-4">
                <h2 class="font-['DM_Sans'] font-semibold text-base text-card-foreground mb-4">Profil</h2>

                <form method="POST" action="{{ route('user.settings.profile') }}" enctype="multipart/form-data">
                    @csrf

                    {{-- Avatar --}}
                    <div class="flex items-center gap-4 mb-5" x-data="avatarCrop()">

                        {{-- Hidden base64 input --}}
                        <input type="hidden" name="avatar_base64" x-model="croppedBase64">

                        {{-- Preview --}}
                        <div class="w-16 h-16 rounded-full bg-primary flex items-center justify-center shrink-0 overflow-hidden">
                            <template x-if="previewUrl">
                                <img :src="previewUrl" class="w-full h-full object-cover" alt="Avatar">
                            </template>
                            <template x-if="!previewUrl">
                                @if($user->avatar_path)
                                    <img src="{{ Storage::url($user->avatar_path) }}" class="w-full h-full object-cover" alt="Avatar">
                                @else
                                    <span class="text-primary-foreground font-bold text-xl">
                                        {{ strtoupper(substr($user->username ?? $user->email, 0, 1)) }}
                                    </span>
                                @endif
                            </template>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="cursor-pointer inline-flex items-center gap-2 text-sm font-medium text-primary hover:opacity-80 transition-opacity">
                                <input type="file" accept="image/*" class="hidden" @change="onFileSelect">
                                Ganti foto
                            </label>
                            @if($user->avatar_path)
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="remove_avatar" value="1" class="rounded">
                                    <span class="text-xs text-muted-foreground">Hapus foto</span>
                                </label>
                            @endif
                            <p class="text-xs text-muted-foreground">JPG, PNG maks. 2MB</p>
                        </div>

                        {{-- Crop Modal --}}
                        <div x-show="showModal" x-cloak
                             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
                             style="display: none">
                            <div class="bg-card rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                                {{-- Modal header --}}
                                <div class="flex items-center justify-between px-5 py-4 border-b border-border">
                                    <p class="font-['DM_Sans'] font-semibold text-sm text-card-foreground">Crop foto profil</p>
                                    <button type="button" @click="closeModal()" class="text-muted-foreground hover:text-card-foreground">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>

                                {{-- Crop area --}}
                                <div class="p-4 bg-black/90" style="max-height: 400px; overflow: hidden">
                                    <img id="cropperImage" style="max-width: 100%; display: block">
                                </div>

                                {{-- Actions --}}
                                <div class="px-5 py-4 flex gap-3 justify-end border-t border-border">
                                    <button type="button" @click="closeModal()"
                                        class="px-4 py-2 text-sm font-['DM_Sans'] font-medium text-muted-foreground hover:text-card-foreground transition-colors">
                                        Batal
                                    </button>
                                    <button type="button" @click="applyCrop()"
                                        class="px-5 py-2 bg-primary text-primary-foreground text-sm font-['DM_Sans'] font-medium rounded-xl hover:opacity-90 transition-opacity">
                                        Set foto profil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-['DM_Sans'] text-xs font-medium text-muted-foreground mb-1.5 uppercase tracking-wider">Username *</label>
                            <input type="text" name="username" value="{{ old('username', $user->username) }}"
                                class="w-full border border-border rounded-xl px-4 py-2.5 text-sm font-['DM_Sans'] text-card-foreground bg-background focus:outline-none focus:border-primary transition-colors @error('username') border-red-400 @enderror">
                            @error('username')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block font-['DM_Sans'] text-xs font-medium text-muted-foreground mb-1.5 uppercase tracking-wider">Email</label>
                            <input type="email" value="{{ $user->email }}" disabled
                                class="w-full border border-border rounded-xl px-4 py-2.5 text-sm font-['DM_Sans'] text-muted-foreground bg-muted/40 cursor-not-allowed">
                            <p class="text-xs text-muted-foreground mt-1">Email tidak dapat diubah</p>
                        </div>

                        <div>
                            <label class="block font-['DM_Sans'] text-xs font-medium text-muted-foreground mb-1.5 uppercase tracking-wider">Nama Depan</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}"
                                class="w-full border border-border rounded-xl px-4 py-2.5 text-sm font-['DM_Sans'] text-card-foreground bg-background focus:outline-none focus:border-primary transition-colors">
                        </div>

                        <div>
                            <label class="block font-['DM_Sans'] text-xs font-medium text-muted-foreground mb-1.5 uppercase tracking-wider">Nama Belakang</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}"
                                class="w-full border border-border rounded-xl px-4 py-2.5 text-sm font-['DM_Sans'] text-card-foreground bg-background focus:outline-none focus:border-primary transition-colors">
                        </div>

                        <div>
                            <label class="block font-['DM_Sans'] text-xs font-medium text-muted-foreground mb-1.5 uppercase tracking-wider">Nomor Telepon</label>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                                placeholder="08xx-xxxx-xxxx"
                                class="w-full border border-border rounded-xl px-4 py-2.5 text-sm font-['DM_Sans'] text-card-foreground bg-background focus:outline-none focus:border-primary transition-colors">
                        </div>

                        <div>
                            <label class="block font-['DM_Sans'] text-xs font-medium text-muted-foreground mb-1.5 uppercase tracking-wider">Kota Default Estimasi</label>
                            <select name="default_location"
                                class="w-full border border-border rounded-xl px-4 py-2.5 text-sm font-['DM_Sans'] text-card-foreground bg-background focus:outline-none focus:border-primary transition-colors">
                                <option value="">Tidak ada (pilih setiap estimasi)</option>
                                @foreach($cities as $city)
                                    <option value="{{ strtolower($city) }}"
                                        {{ old('default_location', $user->default_location) === strtolower($city) ? 'selected' : '' }}>
                                        {{ $city }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-muted-foreground mt-1">Auto-fill lokasi saat estimasi baru</p>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="submit"
                            class="bg-primary text-primary-foreground font-['DM_Sans'] font-medium text-sm rounded-xl px-6 py-2.5 hover:opacity-90 transition-opacity">
                            Simpan Profil
                        </button>
                    </div>
                </form>
            </div>

            {{-- SECTION 2: Ganti Password --}}
            <div class="bg-card rounded-2xl shadow-sm p-6">
                <h2 class="font-['DM_Sans'] font-semibold text-base text-card-foreground mb-4">Ganti Password</h2>

                <form method="POST" action="{{ route('user.settings.password') }}">
                    @csrf

                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="block font-['DM_Sans'] text-xs font-medium text-muted-foreground mb-1.5 uppercase tracking-wider">Password Saat Ini</label>
                            <input type="password" name="current_password"
                                class="w-full border border-border rounded-xl px-4 py-2.5 text-sm font-['DM_Sans'] bg-background focus:outline-none focus:border-primary transition-colors @error('current_password') border-red-400 @enderror">
                            @error('current_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block font-['DM_Sans'] text-xs font-medium text-muted-foreground mb-1.5 uppercase tracking-wider">Password Baru</label>
                            <input type="password" name="password"
                                class="w-full border border-border rounded-xl px-4 py-2.5 text-sm font-['DM_Sans'] bg-background focus:outline-none focus:border-primary transition-colors @error('password') border-red-400 @enderror">
                            @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block font-['DM_Sans'] text-xs font-medium text-muted-foreground mb-1.5 uppercase tracking-wider">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation"
                                class="w-full border border-border rounded-xl px-4 py-2.5 text-sm font-['DM_Sans'] bg-background focus:outline-none focus:border-primary transition-colors">
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="submit"
                            class="bg-primary text-primary-foreground font-['DM_Sans'] font-medium text-sm rounded-xl px-6 py-2.5 hover:opacity-90 transition-opacity">
                            Ganti Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
    function avatarCrop() {
        return {
            showModal: false,
            cropper: null,
            croppedBase64: '',
            previewUrl: '',

            onFileSelect(e) {
                const file = e.target.files[0];
                if (!file) return;
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2MB');
                    return;
                }
                const reader = new FileReader();
                reader.onload = (ev) => {
                    const img = document.getElementById('cropperImage');
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
</x-user::layouts.dashboard>
