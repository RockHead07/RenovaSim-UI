#!/usr/bin/env bash
# Editor Setup Verification Script
# Run this to check if all editor dependencies are installed

echo "======================================"
echo "🔧 RenovaSim UI - Editor Verification"
echo "======================================"
echo ""

# Check Three.js
echo "1. Checking THREE.js files..."
if [ -f "public/three-lib/three.module.min.js" ]; then
    size=$(wc -c < "public/three-lib/three.module.min.js")
    echo "   ✅ THREE.js ($(($size / 1024))KB)"
else
    echo "   ❌ THREE.js NOT FOUND"
fi

# Check GLTFLoader
echo "2. Checking GLTFLoader..."
if [ -f "public/three-examples/jsm/loaders/GLTFLoader.js" ]; then
    size=$(wc -c < "public/three-examples/jsm/loaders/GLTFLoader.js")
    echo "   ✅ GLTFLoader ($(($size / 1024))KB)"
else
    echo "   ❌ GLTFLoader NOT FOUND"
fi

# Check Character Model
echo "3. Checking Character Model..."
if [ -f "public/images/Hoodie Character.glb" ]; then
    size=$(wc -c < "public/images/Hoodie Character.glb")
    echo "   ✅ Hoodie Character ($(($size / 1024))KB)"
else
    echo "   ❌ Character Model NOT FOUND"
fi

# Check Editor Scripts
echo "4. Checking Editor Scripts..."
if [ -f "public/js/editor-advanced.js" ]; then
    lines=$(wc -l < "public/js/editor-advanced.js")
    echo "   ✅ editor-advanced.js ($lines lines)"
else
    echo "   ❌ editor-advanced.js NOT FOUND"
fi

if [ -f "public/js/loader.js" ]; then
    lines=$(wc -l < "public/js/loader.js")
    echo "   ✅ loader.js ($lines lines)"
else
    echo "   ❌ loader.js NOT FOUND"
fi

# Check Blade Template
echo "5. Checking Blade Template..."
if grep -q "three.module.min.js" "resources/views/room/editor.blade.php"; then
    echo "   ✅ editor.blade.php updated"
else
    echo "   ⚠️  editor.blade.php might not be updated"
fi

echo ""
echo "======================================"
echo "✅ Verification Complete!"
echo "======================================"
echo ""
echo "Next Steps:"
echo "1. Run: php artisan serve"
echo "2. Login and navigate to a room editor"
echo "3. Check browser console for THREE.js loading"
echo "4. Or visit: http://127.0.0.1:8000/debug.html"
