import * as THREE from '/three-lib/three.module.min.js';
import { TransformControls } from '/three-examples/jsm/controls/TransformControls.js';

// Explicitly assign to window so they're accessible globally
window.THREE = THREE;
window.TransformControls = TransformControls;

console.log('✅ Bootstrap: THREE and TransformControls loaded and assigned to window');
console.log('THREE type:', typeof window.THREE);
console.log('TransformControls type:', typeof window.TransformControls);

// Now initialize editor once DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeEditor);
} else {
    // DOM already loaded
    setTimeout(initializeEditor, 100);
}

function initializeEditor() {
    console.log('Initializing 3D Editor...');
    if (typeof Room3DEditor === 'undefined') {
        console.error('Room3DEditor class not found');
        return;
    }
    try {
        const editor = new Room3DEditor();
        editor.init();
        console.log('✅ 3D Editor initialized successfully');
    } catch (error) {
        console.error('Failed to initialize editor:', error);
    }
}

