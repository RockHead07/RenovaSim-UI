# Python Room Editor 3D - Dokumentasi Integrasi

## 📋 Overview

Sistem editor 3D telah diperbarui untuk menggunakan **Python Backend** dengan JavaScript frontend. Ini menggantikan editor JavaScript murni dengan arsitektur yang lebih modular.

### Komponen Utama:
- **Backend**: Flask API server (`app_server.py`)
- **Frontend**: JavaScript client (`python-editor-client.js`)
- **View**: Blade template (`resources/views/room/editor.blade.php`)

---

## 🚀 Memulai (Getting Started)

### Langkah 1: Install Dependencies

```bash
cd python-editor/
pip install -r requirements.txt
```

Dependencies yang dibutuhkan:
- `flask` - Web framework
- `flask-cors` - CORS support
- `flask-restful` - REST API builder
- `ursina` - 3D engine (untuk implementasi lanjutan)

### Langkah 2: Jalankan Python Server

**Windows:**
```bash
python-editor/start_server.bat
```

**Linux/Mac:**
```bash
bash python-editor/start_server.sh
```

Atau jalankan langsung:
```bash
python python-editor/app_server.py
```

Server akan start di `http://localhost:5000`

### Langkah 3: Akses Editor Melalui Browser

Navigasi ke: `http://localhost/room/{room_id}/editor`

---

## 📁 Struktur File Baru

```
python-editor/
├── app_server.py              # Flask API server (BARU)
├── room_editor_3d.py          # Ursina 3D editor (untuk implementasi lanjutan)
├── requirements.txt           # Python dependencies (UPDATED)
├── start_server.bat           # Windows startup script (BARU)
├── start_server.sh            # Linux/Mac startup script (BARU)
├── data/                      # Room data storage
│   └── room_*.json           # Saved room files
└── README.md                  # Original documentation

public/js/
├── python-editor-client.js    # JavaScript API client (BARU)
├── backup/                    # Backup folder
│   ├── editor.js.backup
│   ├── editor-advanced.js.backup
│   └── loader.js.backup
├── editor.js                  # (DEPRECATED - backed up)
├── editor-advanced.js         # (DEPRECATED - backed up)
└── loader.js                  # (DEPRECATED - backed up)
```

---

## 🔌 API Endpoints

Base URL: `http://localhost:5000/api`

### Health Check
```
GET /api/status
Response: { status, version, timestamp }
```

### Room Management
```
GET    /api/rooms/<room_id>              # Get room data
POST   /api/rooms/<room_id>              # Create/Update room
POST   /api/rooms/<room_id>/save         # Save room (alternative)
```

### Objects Management
```
GET    /api/rooms/<room_id>/objects      # Get all objects
POST   /api/rooms/<room_id>/objects      # Update objects
```

### Furniture Catalog
```
GET    /api/furniture                    # Get all furniture items
```

---

## 💻 Penggunaan JavaScript Client

### Inisialisasi

```javascript
// Di view Laravel (editor.blade.php):
const editor = initPythonEditor(roomId, {
    apiUrl: 'http://localhost:5000/api'
});
```

### API Methods

```javascript
// Room Operations
await editor.loadRoom();
await editor.saveRoom();
await editor.createNewRoom();
editor.getRoomDimensions();

// Object Operations
editor.addObject(type, position, rotation, scale);
editor.updateObject(objectId, updates);
editor.deleteObject(objectId);
editor.selectObject(objectId);
editor.clear();

// Mode Management
editor.setMode('explore');  // atau 'build'
editor.toggleMode();

// Queries
editor.getObjectCount();
editor.getFurnitureInfo(type);
editor.getAllFurniture();
editor.isOnline();
```

### Event Listeners

```javascript
editor.on('initialized', (data) => {
    console.log('Editor ready!');
});

editor.on('roomLoaded', (roomData) => {
    console.log('Room loaded:', roomData);
});

editor.on('objectAdded', (object) => {
    console.log('Object added:', object);
});

editor.on('objectDeleted', (object) => {
    console.log('Object deleted:', object);
});

editor.on('roomSaved', (result) => {
    console.log('Room saved!');
});

editor.on('error', (error) => {
    console.error('Error:', error.message);
});
```

---

## 📊 Data Format

### Room Object
```json
{
  "id": 1,
  "name": "My Room",
  "width": 8,
  "length": 10,
  "height": 3.2,
  "objects": [],
  "created_at": "2026-04-26T12:00:00",
  "updated_at": "2026-04-26T12:00:00"
}
```

### Object in Room
```json
{
  "id": 1682345600000,
  "type": "Bed",
  "position": [0, 1, 0],
  "rotation": [0, 0, 0],
  "scale": [1.4, 0.6, 2.0],
  "color": "8b7355",
  "emoji": "🛏",
  "created_at": "2026-04-26T12:00:00"
}
```

---

## ⌨️ Keyboard Shortcuts

| Key | Fungsi |
|-----|--------|
| E | Toggle Explore/Edit mode |
| Delete | Delete selected object |
| Escape | Deselect object |
| Ctrl+S | Save room |

---

## 🔧 Troubleshooting

### ❌ "Connection refused" error

**Solusi:**
1. Pastikan Python server sudah running di port 5000
2. Cek apakah port 5000 tidak digunakan aplikasi lain
3. Buka `http://localhost:5000/api/status` di browser untuk test

### ❌ CORS Error

**Solusi:**
- CORS sudah enabled di `app_server.py`
- Pastikan frontend dan backend berjalan (bisa di domain berbeda)
- Cek browser console untuk detail error

### ❌ Python modules not found

**Solusi:**
```bash
pip install flask flask-cors flask-restful
# atau
pip3 install flask flask-cors flask-restful
```

### ❌ Room data tidak tersimpan

**Solusi:**
1. Cek folder `python-editor/data/` exists
2. Pastikan aplikasi punya write permission
3. Cek server logs untuk error messages

---

## 🚀 Implementasi Lanjutan

### Menambah Furniture Baru

Edit di `app_server.py`:

```python
FURNITURE_CATALOG = {
    "Your Item": {
        "color": "hex_color",
        "scale": [width, height, depth],
        "emoji": "🎯"
    },
    ...
}
```

### Integrasi dengan Database

Untuk menyimpan ke database MySQL (bukan file JSON):

```python
# Ganti fungsi load_room() dan save_room()
def load_room(room_id):
    room = Room.query.get(room_id)
    return room.to_dict() if room else None

def save_room(room_id, data):
    room = Room.query.get(room_id)
    room.update(data)
    db.session.commit()
```

---

## 📝 Migration dari Old Editor

### File yang Di-backup:
- `public/js/editor.js` → `public/js/backup/editor.js.backup`
- `public/js/editor-advanced.js` → `public/js/backup/editor-advanced.js.backup`
- `public/js/loader.js` → `public/js/backup/loader.js.backup`

### Untuk Kembali ke Editor Lama:

1. Restore file dari backup
2. Update `resources/views/room/editor.blade.php`:
```blade
<!-- Restore old scripts -->
<script src="/js/editor.js?v={{ time() }}"></script>
<script src="/js/editor-advanced.js?v={{ time() }}"></script>
<script src="/js/loader.js?v={{ time() }}"></script>
```

---

## 📞 Support & Debugging

### Enable Debug Mode

```javascript
// Di browser console:
console.log(window.pyEditor);
window.pyEditor.isOnline();
window.pyEditor.getObjectCount();
```

### View Server Logs

```bash
# Terminal dimana server running akan menunjukkan:
# - API requests
# - Data saves
# - Errors
```

---

## ✅ Checklist Setup

- [ ] Python 3.x installed
- [ ] Dependencies installed: `pip install -r requirements.txt`
- [ ] Python server running: `python python-editor/app_server.py`
- [ ] Laravel running: `php artisan serve`
- [ ] Access editor: `http://localhost/room/1/editor`
- [ ] Check console: No CORS or connection errors
- [ ] Test save: Click "Save Room" button

---

## 📄 Changelog

### v1.0.0 (2026-04-26)
- ✅ Migrasi dari JavaScript editor ke Python API
- ✅ Implementasi Flask REST API
- ✅ JavaScript client untuk komunikasi
- ✅ CORS support untuk browser requests
- ✅ File-based data storage (JSON)
- ✅ Furniture catalog system
- ✅ Room management API
- ✅ Object CRUD operations

---

**Happy Editing! 🎨🛋️✨**
