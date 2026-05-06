# 🏠 Room Editor 3D
### House Flipper × Unity Editor — Python Edition

---

## ⚡ Install & Run

```bash
pip install ursina
python room_editor_3d.py
```

> Requires Python 3.8+. Ursina auto-installs its dependencies (Panda3D, Pillow, etc.)

---

## 🎮 EXPLORE MODE  *(House Flipper style)*

First-person walk-around inside the room.

| Input | Action |
|-------|--------|
| `WASD` | Move |
| `Mouse` | Look around |
| `F` | Inspect nearby furniture |
| `ESC` | Toggle mouse lock |
| `TAB` | Switch to **Build Mode** |

---

## 🔨 BUILD MODE  *(Unity Editor style)*

Free-orbit camera with gizmo controls.

| Input | Action |
|-------|--------|
| `Right Mouse Drag` | Orbit camera |
| `Middle Mouse Drag` | Pan camera |
| `Scroll Wheel` | Zoom in/out |
| `Left Click` | Select object |
| `Left Drag (selected)` | Move object on floor |
| `W` | **Move** gizmo |
| `E` | **Rotate** gizmo |
| `R` | **Scale** gizmo |
| `Q / Z` | Rotate selected ±45° |
| `F` | Focus camera on selected |
| `G` | Toggle grid snap (0.5m) |
| `Delete` | Delete selected |
| `Ctrl+Z` | Undo last action |
| `Ctrl+S` | Save scene |
| `TAB` | Switch to **Explore Mode** |

---

## 🛋️ Furniture Catalog

| Item | Item | Item |
|------|------|------|
| 🛏 Bed | 🛋 Sofa | 🪑 Chair |
| 🍽 Table | 🖥 Desk | 📚 Bookshelf |
| 🚪 Wardrobe | 📺 TV Stand | 💡 Lamp |
| 🪴 Plant | 🛁 Bathtub | 🚽 Toilet |
| 🚰 Sink | 🧊 Fridge | ♨ Oven |
| 🖼 Painting | 🪞 Mirror | 🟥 Rug |

---

## 💾 Save / Load

- Scene auto-loads from `scene_save.json` on startup
- Press `Ctrl+S` or click **SAVE** button to save
- Edit the JSON directly to script scenes

---

## 🗂️ File Structure

```
room_editor_3d.py       ← Main application
scene_save.json         ← Auto-generated save file
README.md
```

---

## 🔧 Customization

Edit constants at the top of `room_editor_3d.py`:

```python
ROOM_W, ROOM_L, ROOM_H = 8, 10, 3.2   # Room dimensions (meters)
GRID_SNAP = 0.5                         # Snap grid size
SAVE_FILE  = "scene_save.json"          # Save file path
```

Add furniture to the `FURNITURE_CATALOG` dict:

```python
FURNITURE_CATALOG = {
    "MyItem": {
        "color": color.rgb(255, 128, 0),
        "scale": (1.0, 1.0, 1.0),
        "emoji": "📦"
    },
    ...
}
```
