// bootstrap-editor.js - Fixed version (works when loaded as module)
import * as THREE from '/three-lib/three.module.min.js';
import { TransformControls } from '/three-examples/jsm/controls/TransformControls.js';

// Make globals available for non-module scripts (like editor-advanced.js)
window.THREE = THREE;
window.TransformControls = TransformControls;

console.log('✅ Bootstrap: THREE and TransformControls loaded');

// Dynamically load the editor class script if not already defined
async function loadEditorClass() {
    if (typeof Room3DEditor !== 'undefined') return;
    
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        // Adjust path to your editor class file (e.g., editor3d-standalone.js)
        script.src = '/js/editor3d-standalone.js';
        script.onload = () => {
            console.log('✅ Editor class script loaded');
            resolve();
        };
        script.onerror = () => reject(new Error('Failed to load editor class script'));
        document.head.appendChild(script);
    });
}

async function initializeEditor() {
    console.log('Initializing 3D Editor...');
    try {
        await loadEditorClass();
        if (typeof Room3DEditor === 'undefined') {
            throw new Error('Room3DEditor class still not defined');
        }
        const editor = new Room3DEditor();
        editor.init();
        console.log('✅ 3D Editor initialized successfully');
    } catch (error) {
        console.error('Failed to initialize editor:', error);
        // Show error on page
        const errorDiv = document.createElement('div');
        errorDiv.style.cssText = 'position:fixed;top:20px;left:20px;background:#fcc;border:1px solid red;padding:10px;z-index:10000';
        errorDiv.innerText = `Editor error: ${error.message}`;
        document.body.appendChild(errorDiv);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeEditor);
} else {
    initializeEditor();
}