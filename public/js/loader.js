// Simple sequential loader
async function loadEditor() {
    try {
        // Step 1: Load THREE.js module
        console.log('Step 1: Loading THREE.js...');
        const threeModule = await import('three');
        window.THREE = threeModule;
        console.log('✅ THREE.js loaded');

        // Step 2: Load TransformControls
        console.log('Step 2: Loading TransformControls...');
        const tcModule = await import('three/addons/controls/TransformControls.js');
        window.TransformControls = tcModule.TransformControls;
        console.log('✅ TransformControls loaded');

        // Step 3: Check if AdvancedRoom3DEditor class is defined
        console.log('Step 3: Checking AdvancedRoom3DEditor class...');
        let attempts = 0;
        while (typeof AdvancedRoom3DEditor === 'undefined' && attempts < 20) {
            await new Promise(r => setTimeout(r, 50));
            attempts++;
        }

        if (typeof AdvancedRoom3DEditor === 'undefined') {
            throw new Error('AdvancedRoom3DEditor class not found after waiting');
        }
        console.log('✅ AdvancedRoom3DEditor class found');

        // Step 4: Initialize editor
        console.log('Step 4: Initializing editor...');
        window.editor = new AdvancedRoom3DEditor();
        window.editor.init();
        console.log('✅ Editor initialized successfully!');

    } catch (error) {
        console.error('❌ Failed to load editor:', error);
        console.error(error.stack);
    }
}

// Start loading when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadEditor);
} else {
    loadEditor();
}
