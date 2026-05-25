/**
 * 🎨 RenovaSim 3D Room Editor
 * 
 * Integrated interface for web-based and desktop 3D editors
 * 
 * - Web Version: editor-advanced.js (in backup/)
 * - Desktop Version: room_editor_3d.py (Ursina - recommended for best experience)
 * 
 * This file provides initialization and mode selection
 */

class RoomEditorIntegration {
    constructor(containerId = 'canvas-container') {
        this.container = document.getElementById(containerId);
        this.editorMode = 'web'; // 'web' or 'desktop'
        this.editorInstance = null;
        
        console.log('🎨 RenovaSim 3D Editor Integration loaded');
        this.initializeEditor();
    }

    initializeEditor() {
        const editorType = this.detectEditorType();
        
        if (editorType === 'desktop') {
            this.initializeDesktopEditor();
        } else {
            this.initializeWebEditor();
        }
    }

    detectEditorType() {
        // Check if running as desktop app or web
        // For now, default to web since Python editor runs standalone
        return 'web';
    }

    initializeWebEditor() {
        console.log('📱 Initializing Web-based 3D Editor...');
        this.editorMode = 'web';
        
        // Check if AdvancedRoom3DEditorV4 is available
        if (typeof AdvancedRoom3DEditorV4 !== 'undefined') {
            try {
                this.editorInstance = new AdvancedRoom3DEditorV4();
                console.log('✅ Web Editor initialized successfully');
            } catch (error) {
                console.error('❌ Error initializing web editor:', error);
                this.showEditorError(error);
            }
        } else {
            console.warn('⚠️ AdvancedRoom3DEditorV4 not found - loading from backup');
            this.loadBackupEditor();
        }
    }

    initializeDesktopEditor() {
        console.log('🖥️ Desktop Editor Mode');
        this.editorMode = 'desktop';
        this.showDesktopEditorInfo();
    }

    loadBackupEditor() {
        // Load from backup if needed
        const script = document.createElement('script');
        script.src = '/js/backup/editor-advanced.js?v=' + Math.random();
        script.onload = () => {
            console.log('✅ Backup editor loaded');
            try {
                this.editorInstance = new AdvancedRoom3DEditorV4();
            } catch (e) {
                console.error('Backup editor initialization failed:', e);
            }
        };
        script.onerror = () => {
            console.error('Failed to load backup editor');
            this.showEditorError(new Error('Could not load editor files'));
        };
        document.head.appendChild(script);
    }

    showEditorError(error) {
        if (this.container) {
            this.container.innerHTML = `
                <div style="
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    height: 100%;
                    background: #1a1a1a;
                    color: #fff;
                    font-family: monospace;
                    padding: 20px;
                ">
                    <h2>⚠️ Editor Initialization Error</h2>
                    <p>${error.message}</p>
                    <details style="margin-top: 20px; color: #888;">
                        <summary>Details</summary>
                        <pre>${error.stack}</pre>
                    </details>
                </div>
            `;
        }
    }

    showDesktopEditorInfo() {
        if (this.container) {
            this.container.innerHTML = `
                <div style="
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    height: 100%;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    font-family: system-ui, -apple-system, sans-serif;
                    padding: 40px;
                    text-align: center;
                ">
                    <h1>🖥️ Desktop 3D Editor</h1>
                    <p style="font-size: 18px; margin: 20px 0;">
                        The advanced Python-based editor is now available
                    </p>
                    <div style="
                        background: rgba(0,0,0,0.3);
                        padding: 30px;
                        border-radius: 10px;
                        max-width: 500px;
                        margin: 20px 0;
                    ">
                        <h3>📋 To Use the Desktop Editor:</h3>
                        <ol style="text-align: left;">
                            <li>Open terminal in: <code style="background: #000; padding: 5px;">python-editor/</code></li>
                            <li>Run: <code style="background: #000; padding: 5px;">python room_editor_3d.py</code></li>
                            <li>Full Explore & Build modes with advanced controls</li>
                        </ol>
                    </div>
                    <a href="/panel" style="
                        display: inline-block;
                        margin-top: 20px;
                        padding: 12px 30px;
                        background: rgba(255,255,255,0.2);
                        border: 2px solid white;
                        color: white;
                        text-decoration: none;
                        border-radius: 5px;
                        cursor: pointer;
                    ">← Back to Panel</a>
                </div>
            `;
        }
    }

    // Export scene to Python editor format
    exportToPythonEditor() {
        if (this.editorInstance && this.editorInstance.furniture) {
            const sceneData = this.editorInstance.furniture.map(obj => ({
                type: obj.userData?.type || 'Unknown',
                pos: [obj.position.x, obj.position.y, obj.position.z],
                rot: [obj.rotation.x, obj.rotation.y, obj.rotation.z],
                scl: [obj.scale.x, obj.scale.y, obj.scale.z]
            }));
            return JSON.stringify(sceneData, null, 2);
        }
        return null;
    }

    // Import scene from Python editor format
    importFromPythonEditor(sceneJson) {
        try {
            const sceneData = JSON.parse(sceneJson);
            if (this.editorInstance && this.editorInstance.loadFromData) {
                this.editorInstance.loadFromData(sceneData);
                return true;
            }
        } catch (error) {
            console.error('Error importing scene:', error);
        }
        return false;
    }
}

// Auto-initialize when document is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.roomEditor = new RoomEditorIntegration();
    });
} else {
    window.roomEditor = new RoomEditorIntegration();
}
