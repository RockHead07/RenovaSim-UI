# 🖥️ Python 3D Room Editor - Quick Start

## ⚡ Get Running in 2 Minutes

### Step 1: Install Python 3.8+
```bash
# Windows
# Download from python.org or use:
choco install python

# macOS
brew install python3

# Linux
sudo apt-get install python3
```

### Step 2: Install Ursina Engine
```bash
pip install ursina
```

That's it! Ursina automatically installs all dependencies (Panda3D, NumPy, etc.)

### Step 3: Run the Editor
```bash
cd python-editor
python room_editor_3d.py
```

**The 3D window will open in about 5 seconds.**

---

## 🎮 Basic Controls (First 5 Minutes)

### EXPLORE MODE (Default)
```
W, A, S, D        Walk around
Mouse             Look around (auto-locked)
ESC               Release mouse
TAB               Go to Build Mode
```

**Try this**: Walk around the empty room. Notice the walls, floor, ceiling, and light.

### BUILD MODE (Press TAB)
```
Left Click        Click on furniture button → click floor to place
Right Mouse Drag  Orbit camera around
Scroll            Zoom in/out
Delete            Remove selected object
TAB               Back to Explore
Ctrl+S            Save scene
```

**Try this**:
1. Press TAB to enter Build Mode
2. Click "🛏 Bed" button on right sidebar
3. Click on floor to place
4. Right-click drag to rotate camera
5. Left-click the bed to select it
6. Press R to scale it
7. Press Delete to remove
8. Press Ctrl+S to save

---

## 📦 Furniture Catalog

Click any button to select, then click floor to place:

```
🛏 Bed          Sleepbed (1.4m × 0.6m × 2.0m)
🛋 Sofa         Couch (2.0m × 0.8m × 0.9m)
🪑 Chair        Dining chair (0.6m × 0.8m)
🍽 Table        Dining table (1.0m × 0.8m)
🖥 Desk         Work desk (1.2m × 0.75m)
📚 Bookshelf    Storage (0.8m × 1.5m × 0.4m)
🚪 Wardrobe     Closet (1.0m × 1.8m × 0.5m)
📺 TV Stand     Entertainment (1.4m × 0.5m)
💡 Lamp         Light fixture (0.2m × 1.2m)
🪴 Plant        Green plant (0.4m × 0.6m)
🛁 Bathtub      Bath (1.8m × 0.6m × 0.9m)
🚽 Toilet       WC (0.5m × 0.8m)
🚰 Sink         Washbasin (0.6m × 0.8m)
🧊 Fridge       Refrigerator (0.7m × 1.7m)
♨ Oven         Cooking (0.7m × 0.85m)
🖼 Painting     Wall art (0.8m × 0.6m)
🪞 Mirror       Reflective (0.6m × 1.0m)
🟥 Rug         Floor covering (2.0m × 3.0m)
```

---

## 🎯 Common Tasks

### Place Multiple Items
```
1. Click [🛏 Bed]
2. Click floor 3 times to place 3 beds
3. Click a different furniture
4. Continue placing
```

### Move an Object
```
1. Left-click on object to select
2. Press [W] to enter Move mode
3. Left-drag it around the floor
```

### Rotate an Object
```
1. Click object to select
2. Press [E] for Rotate mode
3. Right-drag with mouse to rotate camera so you see it
4. Press Q or Z to rotate selected ±45°
5. Press [G] to snap to grid (45° increments)
```

### Scale (Resize) Objects
```
1. Click object to select
2. Press [R] to enter Scale mode
3. Drag horizontally to scale up/down
4. Or use keyboard to manually adjust
```

### Delete Objects
```
1. Click object to select
2. Press [Delete] key
OR
2. Right-click → Delete menu (if enabled)
```

### Save Your Work
```
Ctrl+S              Saves to scene_save.json
```

### Undo Last Action
```
Ctrl+Z              Undoes last add/delete
```

---

## 🔧 Advanced Features

### Grid Snap Toggle
```
Press [G]           Toggle grid snap (0.5m increments)
                    Makes alignment easier
```

### Focus Camera on Object
```
1. Click object
2. Press [F]        Camera zooms to that object
```

### Transform Information
Bottom of screen shows:
```
Bed | P(0.50, 0.30, -1.20) R(0.0°) S(1.40, 0.60, 2.00)
Type Position          Rotation  Scale
```

---

## ✅ Room Specifications

**Default Room**:
- Width: 8m
- Length: 10m  
- Height: 3.2m

To change room size, edit `room_editor_3d.py` line 42:
```python
ROOM_W, ROOM_L, ROOM_H = 8, 10, 3.2  # Edit these
```

---

## 💾 Save File Format

**File**: `scene_save.json` in `python-editor/` folder

**Format**:
```json
[
  {
    "type": "Bed",
    "pos": [0.5, 0.3, -1.2],
    "rot": [0, 45, 0],
    "scl": [1.4, 0.6, 2.0]
  },
  {
    "type": "Sofa",
    "pos": [-2.0, 0.4, 0.0],
    "rot": [0, 0, 0],
    "scl": [2.0, 0.8, 0.9]
  }
]
```

You can:
- ✅ Edit this file manually to adjust positions
- ✅ Copy to backup
- ✅ Share with others
- ✅ Convert to web editor format

---

## 🐛 Quick Fixes

**Problem**: "ModuleNotFoundError: No module named 'ursina'"
```bash
pip install ursina --upgrade
```

**Problem**: Black window, no content
```
Wait 10 seconds for engine to initialize
Or: Check GPU drivers are up to date
```

**Problem**: Can't place furniture in Explore mode
```
Must be in Build Mode first!
Press [TAB] to switch modes
```

**Problem**: Furniture not showing in sidebar
```
Check bottom of window for scrollable panel
Resize window if needed
```

**Problem**: Slow performance
```
Close other applications
Reduce window size
Try disabling grid display (if available)
```

---

## 📖 Learn More

See `EDITOR_INTEGRATION_GUIDE.md` for:
- Web editor features
- Data import/export
- System requirements
- Full API reference
- Troubleshooting guide

---

## 🎬 Video Demo Sequence

Follow this to learn all features:

1. **Explore Mode** (2 min)
   - Walk around with WASD
   - Look with mouse
   - Press ESC to release mouse
   - Reset with E key

2. **Build Mode Entry** (1 min)
   - Press TAB
   - Notice UI changes
   - See furniture sidebar appear

3. **Furniture Placement** (3 min)
   - Click bed button
   - Click floor 3 times
   - Try other furniture
   - Build a simple bedroom

4. **Object Manipulation** (3 min)
   - Click to select
   - Move (W), Rotate (E), Scale (R)
   - Delete with Delete key
   - Use grid snap (G)

5. **Camera Control** (2 min)
   - Right-drag to orbit
   - Scroll to zoom
   - F to focus
   - Middle-drag to pan

6. **Save & Exit** (1 min)
   - Ctrl+S to save
   - Check scene_save.json
   - Close with ESC or X

**Total**: ~15 minutes to learn everything!

---

**Happy Room Designing! 🎨**

For issues, check the console output or the main integration guide.
