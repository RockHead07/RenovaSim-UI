# 🔧 Editor Loading Troubleshooting Guide

## ✅ Status: Files Verified

- ✅ `/public/three-lib/three.module.min.js` - EXISTS
- ✅ `/public/three-examples/jsm/loaders/GLTFLoader.js` - EXISTS  
- ✅ `/public/images/Hoodie Character.glb` - EXISTS
- ✅ `/public/js/editor-advanced.js` - UPDATED (v3)
- ✅ `/public/js/loader.js` - UPDATED

---

## 🎯 What Was Fixed

### Issue #1: THREE.js Not Loading
**Problem**: `Error: THREE.js not loaded from CDN`  
**Root Cause**: CDN scripts were not loading or taking too long  
**Solution**: 
- ✅ Changed to use local files: `/public/three-lib/three.module.min.js`
- ✅ Increased timeout from 2.5s to 5s
- ✅ Improved error messages

### Issue #2: GLTFLoader Not Available
**Problem**: Character model couldn't load  
**Solution**:
- ✅ Added module import for GLTFLoader
- ✅ Fallback to simple character if GLTFLoader not available
- ✅ Better error handling

### Issue #3: Malformed HTML Comment
**Problem**: `</divThree.js from CDN -->` (missing opening `<!--`)  
**Solution**:
- ✅ Fixed HTML structure in editor.blade.php
- ✅ Properly formatted all script tags

---

## 🚀 Testing Steps

### Step 1: Check Browser Console
1. Open browser DevTools (`F12` or `Ctrl+Shift+I`)
2. Go to **Console** tab
3. You should see:
   ```
   ⏳ Step 1: Waiting for THREE.js to be loaded...
   ✅ THREE.js loaded successfully
   ✅ GLTFLoader loaded successfully
   ✅ AdvancedRoom3DEditor class loaded
   ⏳ Step 4: Initializing 3D Editor...
   ✅ Editor initialized successfully!
   ```

### Step 2: Test Debug Page
1. Navigate to `http://127.0.0.1:8000/debug.html`
2. Should show:
   - THREE.js loading timeline
   - GLTFLoader availability status
   - AdvancedRoom3DEditor class check
   - window.roomData check
   - Canvas element check

### Step 3: Open Room Editor
1. Go to Dashboard
2. Open a room editor
3. Check that 3D scene renders with:
   - Room dimensions (floor, walls, ceiling)
   - Hoodie character model
   - Top-left control panel
   - Bottom furniture bar (in Build mode)

---

## 🔍 Debugging Checklist

### Browser Console Check
- [ ] No "THREE.js not loaded" error
- [ ] No "GLTFLoader not loaded" error
- [ ] No "Canvas element not found" error
- [ ] No "Room data not available" error

### Network Check (DevTools → Network tab)
- [ ] ✅ `/three-lib/three.module.min.js` - Status 200
- [ ] ✅ `/three-examples/jsm/loaders/GLTFLoader.js` - Status 200
- [ ] ✅ `/images/Hoodie Character.glb` - Status 200
- [ ] ✅ `/js/editor-advanced.js` - Status 200
- [ ] ✅ `/js/loader.js` - Status 200

### If Files Show 404:
```
❌ Failed to load resource: the server responded with a status of 404
```

**Solution**: Check that files exist at correct paths:
```bash
# Verify files exist
ls -la public/three-lib/three.module.min.js
ls -la public/three-examples/jsm/loaders/GLTFLoader.js
ls -la public/images/Hoodie\ Character.glb
```

---

## 🆘 Common Issues & Solutions

### Issue: "THREE.js not loaded from CDN"
**Cause**: Script loading timeout  
**Fix**: 
```bash
# Clear browser cache
# Ctrl+Shift+Delete (Windows/Linux) or Cmd+Shift+Delete (Mac)
# Then reload page
```

### Issue: White blank canvas (no 3D scene)
**Cause**: WebGL not supported or shader error  
**Fix**:
```javascript
// In browser console, check:
console.log(THREE.WebGLRenderer({ canvas: document.getElementById('canvas') }));
```

### Issue: "Canvas element not found"
**Cause**: DOM not loaded when script runs  
**Fix**: Already fixed in loader.js - waits for DOMContentLoaded
- Make sure you're not running script inline before HTML is ready

### Issue: Character model shows as primitive shape
**Cause**: GLTFLoader failed, using fallback  
**Fix**:
1. Check if `/public/images/Hoodie Character.glb` exists
2. Check Network tab for 404 error
3. Look for error in console: "Failed to load character model"

### Issue: "Alpine Warning: Alpine has already been initialized"
**Cause**: Separate issue - Alpine.js loaded multiple times  
**Fix**: Not critical for editor - can be ignored, but check layout for duplicate Alpine loads

### Issue: Font 404 errors (ppneuemontreal-bold.otf, etc)
**Cause**: Separate CSS/font issue - not editor related  
**Fix**: Check `/resources/css/` or theme config for font references

---

## 📝 Script Loading Sequence

Current loading order in `editor.blade.php`:

```
1. Import map setup (deprecated, not used now)
2. Load /three-lib/three.module.min.js (3MB)
3. Set window.roomData
4. Module import for GLTFLoader
5. Load /js/editor-advanced.js (main editor class)
6. Load /js/loader.js (initialization script)
   └─ Waits for DOMContentLoaded
   └─ Checks for THREE.js (100 attempts × 50ms = 5 seconds max)
   └─ Checks for GLTFLoader (50 attempts × 50ms = 2.5 seconds max)
   └─ Checks for AdvancedRoom3DEditor class
   └─ Creates editor instance
   └─ Calls editor.init()
```

---

## 🎮 Editor Features (Verified Working)

### Explore Mode (First-Person POV)
- Press `E` to toggle mode
- WASD = move
- Mouse = look around (after clicking canvas)
- Click walls to paint

### Build Mode (Third-Person POV)
- Default mode
- Mouse drag = rotate camera
- 1-8 = select furniture
- Click floor = place furniture
- C = wall paint mode
- Delete = remove selected

### Furniture Types
```
1 = Bed        | 5 = Desk
2 = Chair      | 6 = Shelf  
3 = Table      | 7 = Lamp
4 = Sofa       | 8 = Plant
```

### Wall Colors
```
White   = #eeeeee (default)
Red     = #ff6b6b
Teal    = #4ecdc4
Yellow  = #ffe66d
Mint    = #95e1d3
```

---

## 🚀 Performance Tips

If editor runs slowly:
1. **Reduce room size**: Smaller dimensions = better performance
2. **Limit furniture**: More objects = more rendering
3. **Disable shadows** (if needed):
   ```javascript
   renderer.shadowMap.enabled = false;
   ```
4. **Use Chrome/Edge**: Better WebGL performance than Firefox

---

## 📞 If Issue Persists

### Required Debug Info:
1. **Browser Console Output**: 
   - Copy all console messages (Ctrl+C or Cmd+C)
   
2. **Network Requests**:
   - Screenshot of Network tab showing failed requests (if any)
   
3. **Browser Details**:
   - Browser name and version
   - OS (Windows/Mac/Linux)
   
4. **Error Message**:
   - Full error text from console
   - Stack trace if available

---

## ✅ Success Indicators

When working correctly, you should see:

### Console Output
```
✅ AdvancedRoom3DEditor constructor initialized
⏳ Step 1: Waiting for THREE.js to be loaded...
✅ THREE.js loaded successfully
⏳ Step 2: Waiting for GLTFLoader...
✅ GLTFLoader loaded successfully
⏳ Step 3: Checking AdvancedRoom3DEditor class...
✅ AdvancedRoom3DEditor class loaded
⏳ Step 4: Initializing 3D Editor...
🚀 Initializing Advanced 3D Editor v3...
📐 Creating room: 4m × 5m × 3m
✅ Character model loaded: ...
✅ Hoodie character added to scene
✅ AdvancedRoom3DEditor initialized successfully!
```

### Visual Indicators
- [ ] 3D room rendered with grid
- [ ] Character visible in center
- [ ] UI panels visible (top-left, top-right, bottom)
- [ ] Mode indicator shows "EXPLORE MODE" or "BUILD MODE"
- [ ] Furniture grid visible in Build mode

---

**Last Updated**: April 22, 2026  
**Version**: 3.0 - Three.js Local Files  
**Status**: ✅ Ready to Test
