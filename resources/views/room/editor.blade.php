@extends('room.layout')

@section('title', $room->name . ' - Editor')
@section('heading', $room->name . ' - 3D Editor')

<!-- Import map for ES modules -->
<script type="importmap">
{
  "imports": {
    "three": "/three-lib/three.module.min.js",
    "three/addons/": "/three-examples/jsm/"
  }
}
</script>

@section('styles')
<style>
    * {
        box-sizing: border-box;
    }

    html, body {
        margin: 0;
        padding: 0;
        overflow: hidden;
        width: 100%;
        height: 100%;
    }

    body {
        margin: 0;
        overflow: hidden;
    }

    #editor-container {
        position: fixed;
        top: 60px;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: calc(100vh - 60px);
        overflow: hidden;
        z-index: 1;
    }

    #canvas-container {
        width: 100%;
        height: 100%;
        position: relative;
        background: #1e293b;
        display: block;
    }

    #canvas {
        display: block;
        width: 100%;
        height: 100%;
    }

    /* Editor UI */
    #editor-ui {
        position: fixed;
        top: 60px;
        left: 0;
        width: 100%;
        height: calc(100vh - 60px);
        pointer-events: none;
        z-index: 100;
        display: block !important;
    }

    .ui-panel {
        pointer-events: all;
        background: rgba(15, 23, 42, 0.98);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(71, 85, 105, 0.8);
        border-radius: 8px;
        padding: 16px;
        color: white;
        font-family: system-ui, -apple-system, sans-serif;
        font-size: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        max-height: 90vh;
        overflow-y: auto;
    }

    /* Top-left: Controls */
    #controls-panel {
        position: fixed;
        top: 80px;
        left: 20px;
        width: 320px;
        z-index: 101;
        display: block !important;
    }

    /* Top-right: Mode Info */
    #mode-panel {
        position: fixed;
        top: 80px;
        right: 20px;
        width: 320px;
        z-index: 101;
        display: block !important;
    }

    /* Bottom-left: Furniture */
    #furniture-panel {
        position: fixed;
        bottom: 20px;
        left: 20px;
        max-width: 450px;
        max-height: 350px;
        z-index: 101;
        display: block !important;
        overflow-y: auto;
    }

    /* Bottom-right: Status */
    #status-panel {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 320px;
        z-index: 101;
        display: block !important;
    }

    .button {
        display: inline-block;
        padding: 10px 16px;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: background 0.2s, transform 0.1s;
        margin: 4px;
        user-select: none;
    }

    .button:hover {
        background: #2563eb;
        transform: translateY(-2px);
    }

    .button:active {
        transform: translateY(0);
    }

    .button.active {
        background: #059669;
    }

    .button.danger {
        background: #dc2626;
    }

    .button.danger:hover {
        background: #b91c1c;
    }

    .section-title {
        font-size: 13px;
        font-weight: 700;
        margin: 12px 0 8px 0;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #e2e8f0;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    }

    .info-text {
        font-size: 12px;
        color: #cbd5e1;
        margin: 6px 0;
        line-height: 1.6;
        word-wrap: break-word;
    }

    .furniture-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
        gap: 8px;
        margin: 12px 0;
    }

    .furniture-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 12px 8px;
        background: rgba(71, 85, 105, 0.4);
        border: 2px solid rgba(71, 85, 105, 0.6);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 24px;
        min-height: 80px;
        user-select: none;
    }

    .furniture-item:hover {
        background: rgba(71, 85, 105, 0.8);
        border-color: #3b82f6;
        transform: scale(1.05);
    }

    .furniture-item.selected {
        background: rgba(59, 130, 246, 0.3);
        border-color: #3b82f6;
        box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
    }

    .furniture-label {
        font-size: 9px;
        color: #94a3b8;
        margin-top: 4px;
        text-align: center;
        font-weight: 500;
    }

    .mode-badge {
        display: inline-block;
        padding: 8px 14px;
        background: #3b82f6;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        margin: 4px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        width: 100%;
        text-align: center;
    }

    .mode-badge.explore {
        background: #8b5cf6;
    }

    .mode-badge.edit {
        background: #06b6d4;
    }

    .help-text {
        font-size: 11px;
        color: #cbd5e1;
        margin: 12px 0;
        line-height: 1.7;
        background: rgba(15, 23, 42, 0.8);
        padding: 10px;
        border-left: 3px solid #3b82f6;
        border-radius: 4px;
    }

    .help-text strong {
        color: #f1f5f9;
        display: block;
        margin-bottom: 4px;
    }

    .visibility-toggle {
        margin: 8px 0;
    }

    .visibility-toggle label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 12px;
        user-select: none;
    }

    .visibility-toggle input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #3b82f6;
    }

    /* Scrollbar styling */
    .ui-panel::-webkit-scrollbar {
        width: 6px;
    }

    .ui-panel::-webkit-scrollbar-track {
        background: rgba(71, 85, 105, 0.2);
        border-radius: 3px;
    }

    .ui-panel::-webkit-scrollbar-thumb {
        background: rgba(71, 85, 105, 0.6);
        border-radius: 3px;
    }

    .ui-panel::-webkit-scrollbar-thumb:hover {
        background: rgba(71, 85, 105, 0.8);
    }

    /* Loading overlay */
    .loading-spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Debug styles */
    .debug-box {
        position: fixed;
        top: 70px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(59, 130, 246, 0.9);
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }
</style>
@endsection

@section('content')
<div id="editor-container">
    <div id="canvas-container">
        <canvas id="canvas"></canvas>
    </div>

    <div id="editor-ui">
        <!-- Top-Left: Controls Panel -->
        <div id="controls-panel" class="ui-panel">
            <div class="section-title">🎮 3D Editor Controls</div>

            <div class="mode-badge explore" id="mode-display">EXPLORE MODE</div>

            <button class="button" id="toggle-mode-btn" style="width: calc(100% - 8px); margin-top: 8px;">
                ↔️ Switch to Edit Mode [E]
            </button>

            <button class="button danger" id="clear-scene-btn" style="width: calc(100% - 8px); margin-top: 8px;">
                🗑️ Clear Scene
            </button>

            <button class="button" id="save-room-btn" style="width: calc(100% - 8px); margin-top: 8px; background: #059669;">
                💾 Save Room [Ctrl+S]
            </button>

            <div class="help-text">
                <strong>Explore Mode:</strong>
                WASD = Move | Mouse = Look | E = Edit
            </div>

            <div class="help-text">
                <strong>Edit Mode:</strong>
                Click = Select | G = Move | R = Rotate | S = Scale | Del = Delete
            </div>

            <div class="section-title">📐 Scene Info</div>
            <div class="info-text">
                Dimensions: {{ $room->width }}m × {{ $room->length }}m × {{ $room->height }}m
            </div>
            <div class="info-text">
                Objects: <span id="object-count" style="font-weight: bold; color: #10b981;">0</span>
            </div>
        </div>

        <!-- Top-Right: Mode Info Panel -->
        <div id="mode-panel" class="ui-panel">
            <div class="section-title">🔧 Current Tool</div>
            <div class="info-text" id="current-tool">None</div>

            <div class="section-title">👆 Selected Object</div>
            <div class="info-text" id="selected-info" style="min-height: 20px;">None</div>

            <button class="button danger" id="delete-selected-btn" style="width: calc(100% - 8px); margin-top: 8px; display: none;">
                🗑️ Delete Selected [Del]
            </button>
        </div>

        <!-- Bottom-Left: Furniture Selection Panel -->
        <div id="furniture-panel" class="ui-panel">
            <div class="section-title">🪑 Furniture Library</div>
            <div class="furniture-grid" id="furniture-grid">
                <!-- Populated by JavaScript -->
                <div style="grid-column: 1/-1; text-align: center; color: #64748b; padding: 20px;">
                    Loading furniture...
                </div>
            </div>
        </div>

        <!-- Bottom-Right: Status Panel -->
        <div id="status-panel" class="ui-panel">
            <div class="section-title">📊 Status</div>
            <div class="info-text" id="status-message" style="min-height: 20px; color: #94a3b8;">
                Initializing...
            </div>
            <div class="info-text" id="fps-counter" style="margin-top: 8px; color: #64748b;">
                FPS: 0
            </div>
            <div class="info-text" style="margin-top: 12px; font-size: 10px; color: #475569; border-top: 1px solid rgba(71, 85, 105, 0.3); padding-top: 8px;">
                Python Editor v1.0 | <a href="http://localhost:5000/api/status" target="_blank" style="color: #3b82f6; text-decoration: none;">API Status</a>
            </div>
        </div>
    </div>
</div>

<!-- Initialize room data from Blade -->
<script>
    window.roomData = {
        room: {
            id: {{ $room->id }},
            width: {{ $room->width }},
            length: {{ $room->length }},
            height: {{ $room->height }},
        },
        objects: @json($room->objects->toArray())
    };

    window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    window.saveUrl = '{{ route("room.save", $room) }}';
    
    console.log('📋 Room data initialized:', window.roomData);
</script>

<!-- ===== PYTHON EDITOR INTEGRATION ===== -->

<!-- Load Python Editor API Client -->
<script src="/js/python-editor-client.js?v={{ time() }}"></script>

<!-- Initialize Python Editor -->
<script>
    // Helper: Wait for DOM ready or execute immediately if already ready
    function onReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    onReady(async function() {
        console.log('🚀 Initializing Python Room Editor...');
        
        // Verify UI elements exist
        const requiredElements = [
            'status-message', 'object-count', 'toggle-mode-btn', 
            'clear-scene-btn', 'save-room-btn', 'mode-display',
            'furniture-grid', 'fps-counter', 'current-tool', 'selected-info'
        ];
        
        let missingElements = [];
        for (const id of requiredElements) {
            if (!document.getElementById(id)) {
                missingElements.push(id);
                console.warn(`⚠️ Missing element: #${id}`);
            }
        }
        
        if (missingElements.length > 0) {
            console.error('❌ Missing UI elements:', missingElements);
            document.body.innerHTML += `<div style="color: red; padding: 20px; background: black; position: fixed; top: 0; left: 0; right: 0; z-index: 9999;">
                <strong>ERROR: Missing UI elements!</strong><br>
                ${missingElements.join(', ')}
            </div>`;
            return;
        }
        
        // Create editor instance
        const editor = initPythonEditor({{ $room->id }}, {
            apiUrl: 'http://localhost:5000/api'
        });

        // Update UI when editor initializes
        editor.on('initialized', function() {
            console.log('✅ Python Editor ready!');
            const statusMsg = document.getElementById('status-message');
            if (statusMsg) {
                statusMsg.textContent = '✅ Connected to Python Editor';
                statusMsg.style.color = '#10b981';
            }
            // Force show all panels
            document.getElementById('editor-ui').style.display = 'block';
            document.getElementById('controls-panel').style.display = 'block';
            document.getElementById('mode-panel').style.display = 'block';
            document.getElementById('furniture-panel').style.display = 'block';
            document.getElementById('status-panel').style.display = 'block';
        });

        // Update UI when room loads
        editor.on('roomLoaded', function(roomData) {
            console.log('📍 Room loaded:', roomData);
            const objCount = document.getElementById('object-count');
            if (objCount) {
                objCount.textContent = roomData.objects.length;
            }
            populateFurniturePanel();
        });

        // Update object count when objects change
        editor.on('objectAdded', function() {
            const objCount = document.getElementById('object-count');
            if (objCount) {
                objCount.textContent = editor.getObjectCount();
            }
        });

        editor.on('objectDeleted', function() {
            const objCount = document.getElementById('object-count');
            if (objCount) {
                objCount.textContent = editor.getObjectCount();
            }
        });

        // Handle save
        const saveBtn = document.getElementById('save-room-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', async function() {
                try {
                    const statusMsg = document.getElementById('status-message');
                    if (statusMsg) statusMsg.textContent = '💾 Saving...';
                    await editor.saveRoom();
                    if (statusMsg) {
                        statusMsg.textContent = '✅ Room saved!';
                        setTimeout(() => {
                            statusMsg.textContent = 'Ready';
                        }, 2000);
                    }
                } catch (error) {
                    const statusMsg = document.getElementById('status-message');
                    if (statusMsg) statusMsg.textContent = '❌ Save failed!';
                    console.error('Save error:', error);
                }
            });
        }

        // Handle clear scene
        const clearBtn = document.getElementById('clear-scene-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (confirm('⚠️ Clear all objects? This cannot be undone!')) {
                    editor.clear();
                    const objCount = document.getElementById('object-count');
                    if (objCount) objCount.textContent = '0';
                    const statusMsg = document.getElementById('status-message');
                    if (statusMsg) statusMsg.textContent = '✅ Scene cleared';
                }
            });
        }

        // Handle mode toggle
        const toggleBtn = document.getElementById('toggle-mode-btn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                editor.toggleMode();
                const modeDisplay = document.getElementById('mode-display');
                const btnText = document.getElementById('toggle-mode-btn');
                
                if (editor.mode === 'build') {
                    if (modeDisplay) {
                        modeDisplay.textContent = 'BUILD MODE';
                        modeDisplay.classList.remove('explore');
                        modeDisplay.classList.add('edit');
                    }
                    if (btnText) btnText.textContent = 'Switch to Explore Mode [E]';
                } else {
                    if (modeDisplay) {
                        modeDisplay.textContent = 'EXPLORE MODE';
                        modeDisplay.classList.remove('edit');
                        modeDisplay.classList.add('explore');
                    }
                    if (btnText) btnText.textContent = 'Switch to Edit Mode [E]';
                }
            });
        }

        // Populate furniture panel
        function populateFurniturePanel() {
            const grid = document.getElementById('furniture-grid');
            if (!grid) {
                console.warn('⚠️ furniture-grid not found');
                return;
            }
            
            grid.innerHTML = '';
            
            const furniture = editor.getAllFurniture();
            for (const [name, info] of Object.entries(furniture)) {
                const item = document.createElement('div');
                item.className = 'furniture-item';
                item.innerHTML = `
                    <div style="font-size: 24px;">${info.emoji}</div>
                    <div class="furniture-label">${name}</div>
                `;
                
                item.addEventListener('click', function() {
                    console.log('🪑 Adding:', name);
                    editor.addObject(name, [0, 1, 0]);
                });
                
                grid.appendChild(item);
            }
            console.log('✅ Furniture panel populated with', Object.keys(furniture).length, 'items');
        }

        // Handle errors
        editor.on('error', function(error) {
            console.error('❌ Editor error:', error);
            const statusMsg = document.getElementById('status-message');
            if (statusMsg) {
                statusMsg.textContent = '❌ ' + error.message;
                statusMsg.style.color = '#ef4444';
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            switch(e.key.toLowerCase()) {
                case 'e':
                    if (!document.activeElement.matches('input, textarea')) {
                        editor.toggleMode();
                    }
                    break;
                case 's':
                    if (e.ctrlKey) {
                        e.preventDefault();
                        editor.saveRoom();
                    }
                    break;
                case 'delete':
                    if (editor.selectedObject) {
                        editor.deleteObject(editor.selectedObject);
                    }
                    break;
                case 'escape':
                    editor.deselectObject();
                    break;
            }
        });

        // Update FPS counter
        let frameCount = 0;
        let lastTime = Date.now();
        function updateFPS() {
            frameCount++;
            const currentTime = Date.now();
            if (currentTime >= lastTime + 1000) {
                const fpsCounter = document.getElementById('fps-counter');
                if (fpsCounter) {
                    fpsCounter.textContent = `FPS: ${frameCount}`;
                }
                frameCount = 0;
                lastTime = currentTime;
            }
            requestAnimationFrame(updateFPS);
        }
        updateFPS();

        // Make editor global for debugging
        window.pyEditor = editor;
        console.log('✅ Editor available as window.pyEditor');
    });
</script>

@endsection
