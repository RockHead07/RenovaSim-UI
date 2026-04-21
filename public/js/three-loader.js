// Simple wrapper to make Three.js available globally
// Load Three using dynamic import then attach to window
(async () => {
    try {
        // Try to load from local module first
        const THREE = await import('./three.module.min.js').then(m => m.default || m);
        window.THREE = THREE;
        
        // Load TransformControls after THREE is ready
        const script = document.createElement('script');
        script.src = '/three-examples/js/controls/TransformControls.js';
        script.onload = () => {
            console.log('✅ Three.js loaded successfully');
            // Initialize editor if it's waiting
            if (window.initEditor) {
                window.initEditor();
            }
        };
        document.head.appendChild(script);
    } catch (error) {
        console.error('Failed to load Three.js:', error);
    }
})();
