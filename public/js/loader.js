// Sequential loader for THREE.js and editor initialization
async function loadEditor() {
    try {
        // Step 1: Wait for THREE.js to be globally available
        console.log('⏳ Step 1: Waiting for THREE.js to be loaded...');
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
        window.THREE = THREE; // Ensure it's accessible

        // Step 2: Wait for GLTFLoader to be available
        console.log('⏳ Step 2: Waiting for GLTFLoader...');
        attempts = 0;
        while (typeof GLTFLoader === 'undefined' && attempts < 50) {
            await new Promise(r => setTimeout(r, 50));
            attempts++;
        }

        if (typeof GLTFLoader === 'undefined') {
            console.warn('⚠️ GLTFLoader not available, character model may not load');
        } else {
            console.log('✅ GLTFLoader loaded successfully');
        }

        // Step 3: Check if AdvancedRoom3DEditor class is defined
        console.log('⏳ Step 3: Checking AdvancedRoom3DEditor class...');
        attempts = 0;
        while (typeof AdvancedRoom3DEditor === 'undefined' && attempts < 20) {
            await new Promise(r => setTimeout(r, 50));
            attempts++;
        }

        if (typeof AdvancedRoom3DEditor === 'undefined') {
            throw new Error('AdvancedRoom3DEditor class not found');
        }
        console.log('✅ AdvancedRoom3DEditor class loaded');

        // Step 4: Initialize editor
        console.log('⏳ Step 4: Initializing 3D Editor...');
        window.editor = new AdvancedRoom3DEditor();
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
