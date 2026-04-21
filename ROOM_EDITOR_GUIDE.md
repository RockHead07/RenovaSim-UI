# 🏠 RenovaSim 3D Interior Editor

## ✨ Fitur Utama

### 1. **User Panel & Management**
- Setelah sign up, user langsung masuk ke panel dashboard
- Dapat membuat multiple rooms dengan dimensi custom
- View list semua rooms milik user
- Edit dan manage setiap room

### 2. **3D Editor dengan Two Modes**

#### **EXPLORE MODE** 🎮
- **Control:**
  - `WASD` = Bergerak maju/mundur/kiri/kanan
  - `Mouse Move` = Lihat sekeliling
  - `Click Canvas` = Lock pointer untuk immersive view
  - `E` = Masuk Edit Mode
- **Fitur:**
  - FPS-like camera control
  - Natural human eye height (~1.6m)
  - Explore ruangan dengan detail

#### **EDIT MODE** 🛠️
- **Control:**
  - `Click Object` = Select furniture
  - `Click Floor` = Place furniture (jika tool dipilih)
  - `G` = Move mode
  - `R` = Rotate mode
  - `S` = Scale mode
  - `Delete` = Hapus object yang dipilih
  - `E` = Kembali ke Explore Mode
- **Fitur:**
  - Transform Controls (move, rotate, scale)
  - Visual object selection
  - Real-time editing

### 3. **Furniture System**
Tersedia 8 tipe furniture:
- 🛏️ **Bed** (1.4m × 0.6m × 2.0m)
- 🪑 **Chair** (0.6m × 0.8m × 0.6m)
- 📦 **Table** (1.0m × 0.8m × 1.0m)
- 🛋️ **Sofa** (2.0m × 0.8m × 0.9m)
- 🖥️ **Desk** (1.2m × 0.75m × 0.6m)
- 📚 **Shelf** (0.8m × 1.5m × 0.4m)
- 🔦 **Lamp** (0.2m × 0.5m × 0.2m)
- 🪴 **Plant** (0.4m × 0.5m × 0.4m)

### 4. **3D Scene**
- **Rendering:**
  - Three.js WebGL dengan High Quality
  - ACESFilmic tone mapping
  - PBR (Physically Based Rendering)
  - Shadow mapping active

- **Lighting:**
  - Ambient light untuk base lighting
  - Directional light dengan dynamic shadows
  - Point light untuk accent

- **Room Setup:**
  - Floor dengan grid helper
  - 4 dinding dengan material realistic
  - Ceiling
  - Auto-sized sesuai room dimensions

### 5. **Data Persistence**
- **Save Room:** Click tombol "💾 Save Room"
- **Auto-Load:** Room data otomatis dimuat saat membuka editor
- **Format:** JSON structure dengan posisi, rotasi, dan scale setiap object

### 6. **Database Integration**
- **Models:**
  - `Room` - Menyimpan data ruangan
  - `RoomObject` - Menyimpan furniture data

- **API Endpoints:**
  - `GET /api/room/{id}` - Fetch room data
  - `POST /api/room/{id}/save` - Save room changes

---

## 🚀 Cara Menggunakan

### 1. **Sign Up**
```
1. Buka halaman register
2. Isi username, email, password
3. Submit
4. ✅ Langsung masuk ke User Panel
```

### 2. **Buat Room Baru**
```
1. Click "➕ Create New Room"
2. Isi room details:
   - Room Name
   - Description (optional)
   - Width, Length, Height (meter)
3. Click "Create Room"
4. ✅ Room siap untuk diedit
```

### 3. **Explore 3D Room**
```
1. Klik canvas untuk lock pointer
2. WASD = walk around
3. Mouse = look around
4. Press E = masuk Edit Mode
```

### 4. **Edit & Place Furniture**
```
1. Press E (atau click "Switch to Edit Mode")
2. Pilih furniture dari grid di bawah
3. Click di floor untuk menempatkan
4. Click furniture untuk select
5. Gunakan G/R/S untuk transform
6. Delete key untuk hapus
```

### 5. **Save Changes**
```
1. Click "💾 Save Room" di top-left
2. Tunggu konfirmasi
3. ✅ Data tersimpan di server
```

---

## 📁 File Structure

```
public/js/
├── editor3d.js       # Main orchestrator
├── scene.js          # Three.js scene setup
├── controls.js       # Explore mode controls
├── editor.js         # Edit mode with TransformControls
└── furniture.js      # Furniture models & raycasting

app/Models/
├── Room.php
└── RoomObject.php

app/Http/Controllers/
└── RoomController.php

resources/views/room/
├── layout.blade.php
├── index.blade.php
├── create.blade.php
└── editor.blade.php

database/migrations/
├── 2026_04_18_000000_create_rooms_table.php
└── 2026_04_18_000001_create_room_objects_table.php
```

---

## 🔧 Technical Details

### Scene 3D
- **Camera:** PerspectiveCamera dengan FOV 75°
- **Renderer:** WebGLRenderer dengan pixel ratio optimization
- **Lighting:** Directional + Ambient + Point lights

### Physics & Interaction
- **Raycasting** untuk object selection & placement
- **TransformControls** untuk move/rotate/scale
- **Pointer Lock API** untuk immersive explore mode

### Data Flow
```
User Signup
    ↓
Create Auto Room
    ↓
Load Room Data
    ↓
Render 3D Scene
    ↓
Edit & Place Furniture
    ↓
Save to Server
    ↓
Data Persisted
```

---

## 🎨 Styling
- Dark theme (slate-800/900)
- Blue accent color (#3b82f6)
- Overlay UI panels dengan backdrop blur
- Responsive layout

---

## ⚡ Performance Tips
- FPS counter di bottom-right
- Efficient raycasting hanya saat diperlukan
- Optimized shadow rendering
- WebGL hardware acceleration

---

## 🔒 Security
- CSRF token protection
- User-specific room access control
- Server-side validation untuk all inputs

---

## 📝 Future Enhancements (Optional)
- [ ] AI draft layout dari foto
- [ ] Model loading dari GLB files
- [ ] Color/material customization
- [ ] Room templates library
- [ ] Export scene as image/video
- [ ] Multiplayer collaboration
- [ ] Mobile touch controls

---

**Happy Designing! 🎨✨**
