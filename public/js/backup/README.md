# 📦 RenovaSim Editor Backup Archive

**Contents**: Old JavaScript 3D editor files  
**Date Created**: April 26, 2026  
**Status**: Safe archive - not used in current system

---

## 📋 Archive Contents

### Files in This Folder

| File | Size | Purpose | Version |
|------|------|---------|---------|
| `editor-advanced.js` | ~55KB | Advanced room editor | v4 Final |
| `editor-advanced-v4.js` | ~45KB | Advanced editor v4 | v4 |
| `editor-advanced-fixed.js` | ~31KB | Bug-fixed version | v4 Fixed |
| `editor3d.js` | ~10KB | Basic 3D editor | v1 |
| `editor3d-standalone.js` | ~24KB | Standalone variant | v2 |
| `editor3dExtended.js` | ~16KB | Extended features | Extended |
| `editor.js` | ~2KB | Original editor | v1 |
| `bootstrap-editor.js` | ~2KB | Bootstrap variant | v1 |
| `gizmoControls.js` | ~7KB | Transform gizmo | Support |
| `controls.js` | ~3KB | Camera controls | Support |
| `thirdPersonController.js` | ~7KB | 3rd person camera | Support |
| `openWorldGenerator.js` | ~8KB | World generation | Feature |
| `scene3dExtended.js` | ~8KB | Scene management | Extended |
| `sceneModeManager.js` | ~7KB | Mode management | Support |
| `interiorSceneManager.js` | ~5KB | Interior scenes | Feature |

**Total**: ~240KB of archived code

---

## 🔄 What Happened

These files were the primary 3D editors before the reorganization:

1. **Multiple versions** evolved over time (v1 → v4)
2. **Many variations** (standalone, fixed, extended)
3. **Complex dependencies** (gizmo, controls, managers)
4. **Unclear usage** - which one was actually used?

**Solution**: Archive all old versions, consolidate into integration layer

---

## 📖 How to Restore

### If You Need an Old Version
```bash
# Copy specific file back to public/js/
cp backup/editor-advanced-v4.js ../editor-advanced-v4.js

# Or restore all
cp backup/*.js ../
```

### If You Need to Develop a Custom Editor
```bash
# Reference the architecture
cat editor-advanced.js | grep "class\|function"

# See the pattern and extend
# Or import: class CustomEditor extends AdvancedRoom3DEditorV4 { ... }
```

---

## 🔍 When to Use These Files

### Development Scenarios

**Scenario 1: Need old feature**
```
If web editor is missing a feature:
1. Check what was in backup versions
2. Copy relevant code snippet
3. Integrate into current editor.js or editor-advanced.js
4. Test thoroughly
```

**Scenario 2: Rolling back**
```
If current editor has critical bug:
1. Copy backup/editor-advanced-v4.js to editor-advanced.js
2. Update blade template to load it
3. Test
4. Then fix the issue properly
```

**Scenario 3: Learning**
```
To understand the codebase:
1. Compare different versions
2. See what changed between v1-v4
3. Understand the design pattern
4. Apply knowledge to new features
```

---

## ⚡ Performance Notes

These editors were tested in:
- Chrome 90+
- Firefox 88+
- Edge 90+

**Known Issues**:
- Some versions had memory leaks
- Performance degraded with 100+ objects
- Mobile support was limited

**Current System**:
- Uses `editor-advanced.js` (best performing)
- Plus new `editor.js` integration layer
- Better memory management

---

## 🗂️ Organization Logic

### By Purpose

**Core Editors**:
- `editor.js` - Base v1
- `editor-advanced.js` - Final, production v4
- `editor-advanced-v4.js` - Alternative v4

**Support Libraries**:
- `controls.js` - Camera controls
- `gizmoControls.js` - Transform gizmo
- `thirdPersonController.js` - 3rd person view

**Scene Management**:
- `scene.js` - Basic scene
- `scene3dExtended.js` - Extended scene
- `interiorSceneManager.js` - Interior specific

**Special Features**:
- `openWorldGenerator.js` - Procedural generation
- `sceneModeManager.js` - Mode switching
- `bootstrap-editor.js` - Bootstrap integration

---

## 💾 Migration Path

If you were using old files:

### From v1 → Current
```javascript
// OLD: public/js/editor.js
const editor = new Editor3D(container);

// NEW: public/js/editor.js (integration)
const editor = new RoomEditorIntegration(container);
```

### From v3 → Current
```javascript
// OLD: Multiple files loaded manually
<script src="/js/editor3d.js"></script>
<script src="/js/gizmoControls.js"></script>
<script src="/js/controls.js"></script>

// NEW: Everything handled by integration
<script src="/js/editor.js"></script>
<script src="/js/loader.js"></script>
```

### From v4 → Current
```javascript
// OLD: Direct instantiation
const editor = new AdvancedRoom3DEditorV4();
editor.init();

// NEW: Via integration layer
const editor = new RoomEditorIntegration();
editor.editorInstance; // → AdvancedRoom3DEditorV4 instance
```

---

## 🔐 Backup Safety

### Protection Level
- ✅ Read-only recommended
- ✅ Never modified by current system
- ✅ Git tracked (version control)
- ✅ Separate folder (won't conflict)

### To Make Fully Read-Only (Optional)
```bash
# Windows PowerShell
icacls backup /grant Everyone:RX /inheritance:e /remove:U

# macOS/Linux
chmod 555 backup/
```

---

## 📊 Version History

```
v1 (Original)
  └─ 2018: Basic Three.js wrapper
  └─ Size: ~2-10KB per file
  └─ Features: View, basic placement

v2 (Extended)
  └─ 2019: Added controls, gizmo
  └─ Size: ~7-24KB per file
  └─ Features: 3D controls, 3rd person

v3 (Specialized)
  └─ 2020: Interior focus, world generation
  └─ Size: ~5-16KB per file
  └─ Features: Mode switching, interiors

v4 (Advanced - CURRENT BASED)
  └─ 2021-2024: Final comprehensive version
  └─ Size: ~31-55KB per file
  └─ Features: Full editor, gizmo transform
  └─ Used as: Base for current system

v4+ (Integrated - NOW)
  └─ 2024-2026: Reorganized with Python editor
  └─ Architecture: Integration layer + dual editors
  └─ New approach: Web + Desktop options
```

---

## 🚀 Using With Current System

These files are still compatible with:

```javascript
// Load from backup if needed
<script src="/js/backup/editor-advanced-v4.js"></script>

// Or fallback mechanism in loader.js
if (typeof AdvancedRoom3DEditorV4 === 'undefined') {
    // Load from backup
}

// Original features still work
const editor = new AdvancedRoom3DEditorV4();
editor.furniture.push(item);
editor.save();
```

---

## ✅ Checklist for Archive Maintenance

- [x] All files present
- [x] File sizes reasonable
- [x] No corrupted files
- [x] Documentation complete
- [x] Restore procedure documented
- [x] Version history recorded
- [x] Safe from overwrite
- [x] Git tracked

---

## 📞 Need Help?

### Restoring a Backup File
See "How to Restore" section above

### Comparing Versions
```bash
# Find differences
diff backup/editor.js backup/editor-advanced.js

# See size comparison
ls -lh backup/ | sort -k5 -h
```

### Extracting Code Snippets
```bash
# Find specific function
grep "toggleMode" backup/*.js

# Extract class definition
grep -A 50 "class Advanced" backup/editor-advanced.js
```

---

**Last Updated**: April 26, 2026  
**Archive Status**: Complete & Verified  
**Recommended Action**: Keep for reference, don't use in production
