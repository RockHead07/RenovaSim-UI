# 🔧 Python Editor - Troubleshooting & Fixes

## ✅ Perbaikan Yang Sudah Dilakukan

### 1. **Script Initialization Fix**
- ❌ **Masalah**: `DOMContentLoaded` mungkin tidak fire jika script di-load setelah DOM siap
- ✅ **Solusi**: Gunakan `onReady()` function yang check apakah DOM sudah ready

### 2. **Element Validation**
- ❌ **Masalah**: Element tidak ditemukan, script error tidak ditampilkan
- ✅ **Solusi**: Validate semua required elements sebelum di-gunakan, tampilkan error jika ada yang missing

### 3. **CSS Layout Fix**
- ❌ **Masalah**: UI panels menggunakan `position: absolute`, bisa keluar dari view
- ✅ **Solusi**: Ganti ke `position: fixed` dengan proper z-index layering

### 4. **Force Display Panels**
- ❌ **Masalah**: Panel mungkin hidden karena CSS atau default styling
- ✅ **Solusi**: Force `display: block !important` dan set explicit z-index

### 5. **Null Check Safety**
- ❌ **Masalah**: Direct access ke element tanpa check bisa cause error
- ✅ **Solusi**: Wrap semua DOM operations dengan null checks

### 6. **Improved Error Logging**
- ❌ **Masalah**: Errors tidak terlihat dengan jelas
- ✅ **Solusi**: Add detailed console logging untuk debugging

---

## 🧪 Testing UI Panels

### Method 1: UI Test Page (Recommended)
```
http://localhost/ui-test.html
```

Halaman ini menampilkan UI panels tanpa perlu backend:
- ✅ Test semua UI panels
- ✅ Test buttons dan interactions
- ✅ Check positioning dan styling
- ✅ No API dependency

### Method 2: Real Editor
```
http://localhost/room/1/editor
```

Requirements:
- PHP server running
- Python API server at `http://localhost:5000`

---

## 📋 Checklist Debug

Jika UI tetap tidak muncul, check:

### 1. Browser Console (F12)
```
Cek untuk:
- ✅ No red errors
- ✅ Client initialized message
- ✅ Room loaded message
- ✅ Furniture loaded message
```

### 2. Network Tab (F12)
```
Cek untuk:
- ✅ python-editor-client.js di-load (status 200)
- ✅ API requests ke localhost:5000 berhasil (200 OK)
```

### 3. Visual Check
```
Verifikasi:
- ✅ Canvas container terlihat (dark background)
- ✅ Control panel di top-left
- ✅ Mode panel di top-right
- ✅ Furniture panel di bottom-left
- ✅ Status panel di bottom-right
```

### 4. Elements Inspector (F12)
```
Check:
- ✅ <div id="editor-ui"> exists dan visible
- ✅ <div id="controls-panel"> exists dan computed display: block
- ✅ <div id="furniture-panel"> exists dan computed display: block
- ✅ z-index values terlihat benar
```

---

## 🔍 Common Issues & Solutions

### Issue 1: "All UI panels invisible"

**Diagnosis:**
```javascript
// Open console dan run:
console.log(document.getElementById('editor-ui'));
console.log(getComputedStyle(document.getElementById('editor-ui')).display);
```

**Solutions:**
1. Check apakah browser JS enabled
2. Check F12 Console untuk errors
3. Try `http://localhost/ui-test.html` untuk test UI tanpa dependency

---

### Issue 2: "Panels visible tapi di tempat yang salah"

**Check CSS z-index:**
```javascript
// Console
document.querySelectorAll('.ui-panel').forEach(el => {
    const style = getComputedStyle(el);
    console.log(el.id, {
        zIndex: style.zIndex,
        position: style.position,
        display: style.display,
        visibility: style.visibility
    });
});
```

**Solutions:**
- Z-index harus: 101 (panels) > 100 (editor-ui) > 1 (container)
- Position harus: `fixed` (panels) atau `absolute` (editor-ui)

---

### Issue 3: "API connection error"

**Check API status:**
```
curl http://localhost:5000/api/status
```

Should return:
```json
{
  "status": "online",
  "version": "1.0.0",
  "editor": "Python Room Editor API",
  "timestamp": "..."
}
```

**Solutions:**
1. Start Python server: `python python-editor/app_server.py`
2. Check port 5000 not used: `netstat -ano | findstr :5000`
3. Check firewall not blocking localhost:5000

---

### Issue 4: "Room data not loading"

**Check logs:**
```
python-editor/data/room_{id}.json
```

**Solutions:**
1. Create manually if missing
2. Check write permissions on data/ folder
3. Check Python server logs for errors

---

## 🎯 Files Modified

```
✅ resources/views/room/editor.blade.php
   - Fixed HTML structure
   - Improved CSS with fixed positioning
   - Better script initialization
   - Element validation
   - Proper error handling

✅ public/js/python-editor-client.js
   - Better logging
   - Status detail
   - Error handling

✅ public/ui-test.html (NEW)
   - Standalone UI test page
   - No backend required
   - Test panels, buttons, interactions
```

---

## 🧪 Testing Workflow

### Step 1: Test UI Without Backend
```bash
# Open in browser
http://localhost/ui-test.html

# Should see:
- ✅ All 4 panels visible and positioned correctly
- ✅ Buttons clickable
- ✅ Furniture grid populated
- ✅ Mode toggle working
```

### Step 2: Test with API Server
```bash
# Terminal 1: Start Python API
python python-editor/app_server.py

# Terminal 2: Start Laravel
php artisan serve

# Browser: Access editor
http://localhost:8000/room/1/editor
```

### Step 3: Debug Console
```javascript
// In browser console:
window.pyEditor                    // Check editor instance
window.pyEditor.isOnline()         // Check connection
window.pyEditor.getObjectCount()   // Check objects
window.pyEditor.getAllFurniture()  // Check furniture
```

---

## 📊 Expected Output

When everything works:

### Browser Console:
```
✅ Python Editor Client loaded
🚀 Initializing Python Editor Client...
📍 API URL: http://localhost:5000/api
🟢 Server Status: {status: 'online', ...}
🪑 Furniture loaded: 18 items
✅ Python Editor Client initialized successfully
✅ Python Editor ready!
📍 Room loaded: {...}
✅ Furniture panel populated with 18 items
```

### Visual:
- Dark gray canvas area (#1e293b)
- Blue panel on top-left with controls
- Blue panel on top-right with mode info
- Blue panel on bottom-left with furniture (18 items in grid)
- Blue panel on bottom-right with status
- All panels have proper border and shadow

---

## 🚀 Quick Fixes To Try

### Fix 1: Clear Browser Cache
```
Ctrl+Shift+Delete → Clear all data → Reload
```

### Fix 2: Hard Refresh
```
Ctrl+Shift+R (or Cmd+Shift+R on Mac)
```

### Fix 3: Check Console for Specific Errors
```
F12 → Console tab → Look for red error messages
```

### Fix 4: Test API Directly
```javascript
// In console:
fetch('http://localhost:5000/api/status')
    .then(r => r.json())
    .then(d => console.log('✅ API:', d))
    .catch(e => console.error('❌ API Error:', e))
```

### Fix 5: Check All Elements Exist
```javascript
// In console:
['editor-ui', 'controls-panel', 'mode-panel', 'furniture-panel', 'status-panel']
    .forEach(id => {
        const el = document.getElementById(id);
        console.log(id, el ? '✅' : '❌', el);
    });
```

---

## 📞 Debug Information To Provide

If still having issues, collect:

1. **Browser Console Output** (F12 → Console)
2. **Network Requests** (F12 → Network → look for failed requests)
3. **Element Inspector** (F12 → Inspector → check #editor-ui styling)
4. **Python Server Logs** (terminal output)
5. **Browser Name & Version** (Help → About)
6. **URL Accessed** (exact URL from address bar)

---

## ✅ Success Indicators

If everything working:

```
Console:
✅ Client initialized successfully
✅ Server online
✅ Furniture loaded
✅ Room loaded
✅ UI interactions working

Visual:
✅ 4 panels visible
✅ Buttons responsive
✅ Furniture grid displayed
✅ Status updates in real-time
✅ Mode badge can toggle
```

---

## 📝 Next Steps

1. **Test UI**: Open `http://localhost/ui-test.html`
2. **Start API**: Run `python python-editor/app_server.py`
3. **Access Editor**: Go to `http://localhost/room/1/editor`
4. **Monitor Console**: Keep F12 Console open and watch for messages
5. **Test Interactions**: Click buttons, add furniture, toggle mode

---

**Good luck with debugging! 🎉**

For more details, see:
- `PYTHON_EDITOR_INTEGRATION.md` - API documentation
- `SETUP_SUMMARY.md` - Installation guide
