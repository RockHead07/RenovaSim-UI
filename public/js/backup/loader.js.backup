/**
 * RenovaSim 3D Editor Loader v2
 * 
 * Initializes the 3D room editor with proper dependencies
 * Supports web editor (advanced.js) with integration layer (editor.js)
 * 
 * Architecture:
 * - editor.js         : Integration layer (new)
 * - editor-advanced.js: Web-based 3D editor (in backup/ if old versions)
 * - loader.js         : This initialization script
 */

async function loadEditor() {
    try {
        // ═══════════════════════════════════════════
        // STEP 1: Verify THREE.js
        // ═══════════════════════════════════════════
        console.log('🚀 RenovaSim Editor Loader v2');
        console.log('⏳ Step 1: Verifying THREE.js...');
        let attempts = 0;
        const maxAttempts = 100; // 5 seconds at 50ms intervals
        
        while (typeof THREE === 'undefined' && attempts < maxAttempts) {
            await new Promise(r => setTimeout(r, 50));
            attempts++;
        }

        if (typeof THREE === 'undefined') {
            throw new Error('THREE.js failed to load from /three-lib/three.module.min.js');
        }
        console.log('✅ THREE.js loaded successfully');
        window.THREE = THREE;

        // ═══════════════════════════════════════════
        // STEP 2: Verify GLTFLoader
        // ═══════════════════════════════════════════
        console.log('⏳ Step 2: Waiting for GLTFLoader...');
        attempts = 0;
        while (typeof GLTFLoader === 'undefined' && attempts < 50) {
            await new Promise(r => setTimeout(r, 50));
            attempts++;
        }

        if (typeof GLTFLoader === 'undefined') {
            console.warn('⚠️ GLTFLoader not available, some features may be limited');
        } else {
            console.log('✅ GLTFLoader loaded successfully');
        }

        // ═══════════════════════════════════════════
        // STEP 3: Verify AdvancedRoom3DEditorV4 class
        // ═══════════════════════════════════════════
        console.log('⏳ Step 3: Checking AdvancedRoom3DEditorV4 class...');
        attempts = 0;
        while (typeof AdvancedRoom3DEditorV4 === 'undefined' && attempts < 20) {
            await new Promise(r => setTimeout(r, 50));
            attempts++;
        }

        if (typeof AdvancedRoom3DEditorV4 === 'undefined') {
            throw new Error('AdvancedRoom3DEditorV4 class not found');
        }
        console.log('✅ AdvancedRoom3DEditorV4 class loaded');

        // ═══════════════════════════════════════════
        // STEP 4: Initialize editor via integration
        // ═══════════════════════════════════════════
        console.log('⏳ Step 4: Initializing 3D Editor...');
        window.editor = new AdvancedRoom3DEditorV4();
        window.editor.init();
        console.log('✅ Editor initialized successfully!');

    } catch (error) {
        console.error('❌ Failed to load editor:', error.message);
        console.error('Stack trace:', error.stack);
        
        // Show user-friendly error
        const errorDiv = document.createElement('div');
        errorDiv.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fee2e2;border:2px solid #dc2626;padding:20px;border-radius:8px;z-index:9999;max-width:400px;';
        errorDiv.innerHTML = `
            <h2 style="margin:0 0 10px 0;color:#dc2626;">❌ Editor Failed to Load</h2>
            <p style="margin:0;color:#991b1b;font-size:14px;">${error.message}</p>
            <p style="margin:10px 0 0 0;color:#7f1d1d;font-size:12px;">Check browser console for details.</p>
        `;
        document.body.appendChild(errorDiv);
    }
}

// Start loading when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadEditor);
} else {
    loadEditor();
}
