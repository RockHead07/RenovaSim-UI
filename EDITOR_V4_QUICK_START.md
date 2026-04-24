# 🎮 3D Room Editor V4 - Quick Start Guide

## 🚀 Mulai di Explore Mode

Saat aplikasi pertama kali dibuka, Anda berada di **Explore Mode** - seperti bermain game!

### Apa yang Bisa Dilakukan:
- ✅ Berjalan dengan karakter 3D
- ✅ Lihat ruangan dari perspective ketiga (TPP - Third Person)
- ✅ Tambahkan pintu
- ✅ Jelajahi layout ruangan

### Kontrol Explore Mode
```
🕹️ WASD       → Bergerak maju/mundur/kiri/kanan
🖱️  Mouse      → Lihat ke arah berbeda
G Key         → Tambah pintu di ruangan
E Key         → Tukar ke Build Mode
```

**Catatan**: Saat di Explore Mode, mouse Anda akan ter-lock pada layar untuk kontrol yang smooth!

---

## 🛠️ Build Mode - Seperti Unity Editor!

Tekan **E** untuk masuk ke Build Mode. Di sini Anda dapat:
- ✅ Menempatkan furniture
- ✅ Memindahkan/Merotasi/Menskala objek
- ✅ Mengecat dinding
- ✅ Mengubah ukuran ruangan
- ✅ Menyimpan desain

### Mode Transformasi (Build Mode)
```
Transform Mode Selector:
Q Key    → Translate (Pindahkan)
W Key    → Rotate (Putar) 
R Key    → Scale (Ubah Ukuran)
```

Setelah memilih mode, **klik pada objek** untuk memilihnya, lalu **drag** untuk menggunakannya!

### Furniture Placement
```
Cara Menempatkan Furniture:

1. Pilih salah satu dari 8 furniture type:
   1 = Bed 🛏️
   2 = Chair 🪑
   3 = Table 📦
   4 = Sofa 🛋️
   5 = Desk 🖥️
   6 = Shelf 📚
   7 = Lamp 💡
   8 = Plant 🪴

2. Klik di lantai untuk menempatkan

3. Furniture akan muncul di lokasi yang Anda klik!

4. Sekarang Anda bisa memanipulasinya dengan Q/W/R
```

### Wall Painting
```
Mengecat Dinding:

1. Tekan C untuk membuka color palette
2. Pilih warna:
   ⚪ Abu-abu
   🔴 Merah
   🔵 Teal
   🟡 Kuning
   💚 Mint Green
   🟣 Purple

3. Klik di dinding untuk mengecat

4. Warna akan berubah langsung!
```

### Room Expansion
```
Mengubah Ukuran Ruangan:

Di bagian bawah kiri, Anda akan melihat:

Width (Lebar):
  + Button  → Tambah 1 meter
  - Button  → Kurangi 1 meter

Length (Panjang):
  + Button  → Tambah 1 meter
  - Button  → Kurangi 1 meter

Height (Tinggi):
  + Button  → Tambah 1 meter
  - Button  → Kurangi 1 meter

⚠️ Minimum size: 2m x 2m x 2m
```

---

## 📏 Contoh Workflow

### Scenario 1: Desain Ruangan Baru

1. **Buka editor** → Anda di Explore Mode
2. **Tekan E** → Masuk ke Build Mode
3. **Adjust room size**:
   - Ruangan default: 4m x 5m x 3m
   - Klik buttons untuk sesuaikan dengan ukuran ruangan asli Anda
4. **Tambah furniture**:
   - Tekan 1 untuk bed
   - Klik di lantai untuk tempat tidur
   - Tekan 2 untuk chair
   - Klik lagi di lokasi berbeda
5. **Arrange furniture**:
   - Tekan Q (translate) untuk pindahkan
   - Tekan W (rotate) untuk putar
   - Tekan R (scale) untuk ubah ukuran
6. **Paint walls**:
   - Tekan C untuk buka color palette
   - Pilih warna
   - Klik di dinding
7. **Save**:
   - Klik "💾 Save Room"
   - Design tersimpan!

### Scenario 2: Explore Hasil Design

1. **Tekan E** → Masuk ke Explore Mode
2. **WASD** → Jalan-jalan lihat desainnya
3. **Mouse** → Lihat ke arah berbeda
4. **G** → Tambah pintu di ruangan
5. **Tekan E lagi** → Kembali ke Build Mode jika perlu edit

---

## 💡 Tips & Tricks

### Kamera di Explore Mode
- Camera berada di belakang karakter
- Gerak mouse UP = lihat ke atas
- Gerak mouse DOWN = lihat ke bawah
- Gerak mouse LEFT/RIGHT = putar kamera mengelilingi karakter

### Kamera di Build Mode
- **Middle Mouse Drag** = Putar kamera mengelilingi center point
- **Right Mouse Drag (naik/turun)** = Zoom in/out
- Ini memudahkan Anda melihat design dari berbagai sudut!

### Object Selection
- Klik pada furniture untuk memilih
- Object yang terpilih akan bersinar (highlight)
- Tekan Delete untuk hapus object terpilih

### Undo Furniture
- Tidak ada undo yang built-in
- Tapi Anda bisa delete (tekan Delete) dan place lagi
- Future version akan punya undo/redo

---

## 🚪 Door System

### Menambahkan Pintu
```
1. Masuk ke Explore Mode (tekan E)
2. Berdiri di lokasi pintu
3. Tekan G
4. Pintu akan muncul di dinding!
```

### Karakteristik Pintu
- Frame: Kayu cokelat 🟤
- Panel: Beige/cream 
- Knob: Emas (bersinar) ✨
- Ukuran: 1m lebar x 2m tinggi

**Future**: Pintu akan bisa dimasuki untuk portal ke ruangan lain!

---

## 📊 Room Data Format

Saat Anda save, data disimpan dalam format:

```json
{
    "room": {
        "width": 5.0,
        "length": 6.0,
        "height": 3.5
    },
    "objects": [
        {
            "type": "bed",
            "position": [1.5, 0.15, -1.2],
            "rotation": [0, 0.785, 0],
            "scale": [1, 1, 1]
        },
        {
            "type": "chair",
            "position": [-1.0, 0.4, 0.5],
            "rotation": [0, 0, 0],
            "scale": [1, 1, 1]
        }
    ]
}
```

---

## 🎯 Fitur Unggulan V4

| Fitur | Sebelumnya | Sekarang |
|-------|-----------|---------|
| **Kamera Explore** | First-Person (FPS) | Third-Person (TPP - seperti game!) ✨ |
| **Build Edit** | Click & drag basic | Unity-like Transform Controls 🎮 |
| **Mouse Control** | Bisa inverted | Always natural ✅ |
| **Room Size** | Fixed | Dynamic - ubah besar-kecil! 📐 |
| **Doors** | None | Add dengan G key 🚪 |
| **Physics** | None | Gravity & ground collision ⚙️ |
| **Character** | Static | Animation support 🏃 |

---

## ⚠️ Common Issues & Solutions

### Issue: Karakter tidak bergerak
**Solusi**: 
- Pastikan pointer lock aktif (klik canvas)
- Coba tekan WASD lagi

### Issue: Transform Controls tidak berfungsi
**Solusi**:
- Pastikan Anda di Build Mode (badge harus biru)
- Pastikan sudah klik object untuk select
- Coba tekan Q/W/R untuk switch mode

### Issue: Furniture tidak hilang saat delete
**Solusi**:
- Pastikan furniture ter-highlight (bersinar)
- Tekan Delete key (bukan backspace)

### Issue: Dinding tidak berubah warna
**Solusi**:
- Tekan C dulu untuk buka color palette
- Pilih warna dari grid
- Klik di dinding (bukan furniture)

### Issue: Room size tidak berubah
**Solusi**:
- Pastikan Anda di Build Mode
- Scroll ke bagian bawah untuk lihat room controls
- Klik + atau - button

---

## 🔄 Workflow Rekomendasi

**Untuk hasil terbaik:**

```
1. Start di Explore Mode
   ↓
2. Switch ke Build Mode (E)
   ↓
3. Atur ukuran ruangan dulu
   ↓
4. Tambah furniture dari bawah ke atas
   ↓
5. Arrange & rotate sesuai kebutuhan
   ↓
6. Paint dinding dengan warna favorit
   ↓
7. Switch ke Explore Mode (E) untuk preview
   ↓
8. Edit lebih lanjut jika perlu
   ↓
9. Save (💾 button) saat puas!
```

---

## 🎨 Color Palette

```
Available Colors:
⚪ #eeeeee (Light Gray - Default)
🔴 #ff6b6b (Red - Warm)
🔵 #4ecdc4 (Teal - Cool)
🟡 #ffe66d (Yellow - Bright)
💚 #95e1d3 (Mint - Fresh)
🟣 #a29bfe (Purple - Creative)
```

---

## 📱 Supported Input Devices

- ✅ Mouse + Keyboard (Primary)
- ✅ Trackpad (Build Mode)
- ⚠️ Touch (Limited - no pointer lock on mobile)
- ⚠️ Gamepad (Not yet supported)

---

## 🎓 Learning Path

**Beginner (5 min)**:
- Explore Mode walk-around
- Basic furniture placement

**Intermediate (15 min)**:
- Room size adjustment
- Transform controls (Q/W/R)
- Wall painting

**Advanced (30+ min)**:
- Door placement strategy
- Multi-room design
- Optimization for performance

---

## 🆘 Need Help?

### In-Game Help
- Check the **Help text** panels in UI
- Status panel shows current room dimensions
- Mode badge indicates current mode

### UI Navigation
- **Top-Left**: Controls & mode switcher
- **Top-Right**: Real-time status
- **Bottom-Left**: Furniture & room controls (Build Mode only)
- **Bottom-Right**: Info & FPS counter

---

## 🚀 Future Features Coming Soon

- [ ] Undo/Redo system
- [ ] Door navigation between rooms
- [ ] More furniture types
- [ ] Texture/material editor
- [ ] Lighting controls
- [ ] 3D model export
- [ ] Collaboration tools
- [ ] Mobile app support

---

**Selamat berdesain! Enjoy your new 3D Room Editor! 🎉**

*RenovaSim - Professional Room Planning Tool*
