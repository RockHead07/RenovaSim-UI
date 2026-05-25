<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RenovaSim 3D Editor - Extended</title>
    <link rel="stylesheet" href="/css/editor3d-extended.css">
</head>
<body>
    <!-- Canvas -->
    <canvas id="canvas"></canvas>

    <!-- Top UI Bar -->
    <div id="ui-container">
        <!-- Left Section: Mode Info -->
        <div class="ui-section" style="flex: 1;">
            <div>
                <div id="mode-display" class="mode-badge exploration">EXPLORATION MODE</div>
                <div id="mode-description">Use WASD to move, mouse to look around</div>
            </div>
        </div>

        <!-- Center Section: Mode Controls -->
        <div id="mode-controls" class="ui-section">
            <button id="mode-explore" class="mode-btn active" title="Press E to toggle">
                🌍 Explore
            </button>
            <button id="mode-interior" class="mode-btn" title="Enter buildings">
                🏠 Interior
            </button>
            <button id="mode-build" class="mode-btn" title="Edit furniture">
                🔨 Build
            </button>
        </div>

        <!-- Right Section: Actions -->
        <div class="ui-section">
            <div id="status-message">Ready</div>
            <button id="save-room-btn" class="button success" title="Save room (Ctrl+S)">
                💾 Save
            </button>
            <button id="clear-scene-btn" class="button danger" title="Clear all objects">
                🗑️ Clear
            </button>
            <button id="delete-selected-btn" class="button danger" style="display: none;" title="Delete selected object (Del)">
                ✕ Delete
            </button>
        </div>
    </div>

    <!-- Gizmo Control Panel -->
    <div id="gizmo-panel" class="visible">
        <div id="transform-modes" style="display: none;">
            <button id="gizmo-translate" class="gizmo-btn active" title="Move (Press G)">📍 Move</button>
            <button id="gizmo-rotate" class="gizmo-btn" title="Rotate (Press R)">🔄 Rotate</button>
            <button id="gizmo-scale" class="gizmo-btn" title="Scale (Press S)">📏 Scale</button>
            <button id="gizmo-world" class="gizmo-btn" title="Toggle World/Local (Press X)">🌐 WORLD</button>
        </div>
        <div id="gizmo-mode">Mode: Move | Space: World</div>
    </div>

    <!-- Right Sidebar -->
    <div id="right-sidebar">
        <!-- Object Info -->
        <div class="sidebar-section" style="display: none;">
            <h3>📊 Object Info</h3>
            <div id="object-info">
                <div class="info-row">
                    <span class="info-label">Selected:</span>
                    <span class="info-value" id="selected-info">None</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total:</span>
                    <span class="info-value" id="object-count">0</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Current Tool:</span>
                    <span class="info-value" id="current-tool">None</span>
                </div>
            </div>
        </div>

        <!-- Furniture Selection (Build Mode) -->
        <div class="sidebar-section" style="display: none;">
            <h3>🛋️ Furniture</h3>
            <div id="furniture-grid"></div>
        </div>

        <!-- Keyboard Shortcuts -->
        <div class="sidebar-section">
            <h3>⌨️ Shortcuts</h3>
            <div style="font-size: 12px; color: #d1d5db; line-height: 1.6;">
                <p><strong>Exploration:</strong></p>
                <p>WASD - Move</p>
                <p>Mouse - Look</p>
                <p>Space - Jump</p>
                <p>Shift - Sprint</p>

                <p style="margin-top: 12px;"><strong>Build Mode:</strong></p>
                <p>G - Move (Translate)</p>
                <p>R - Rotate</p>
                <p>S - Scale</p>
                <p>X - Toggle World/Local</p>
                <p>Del - Delete Selected</p>

                <p style="margin-top: 12px;"><strong>General:</strong></p>
                <p>E - Toggle Mode</p>
                <p>B - Enter Build Mode</p>
                <p>N - Toggle Snap</p>
            </div>
        </div>
    </div>

    <!-- Data Scripts -->
    <script>
        // Pass data from server
        window.roomData = {
            room: {
                width: {{ $room->width ?? 4 }},
                length: {{ $room->length ?? 5 }},
                height: {{ $room->height ?? 3 }}
            },
            objects: {!! json_encode($objects ?? []) !!}
        };
        window.csrfToken = "{{ csrf_token() }}";
        window.saveUrl = "{{ route('api.save-room', $room->id ?? 0) }}";
    </script>

    <!-- Three.js and Editor Scripts -->
    <script type="module">
        import Editor3DExtended from '/js/editor3dExtended.js';
        
        // Auto-initialize (already done in editor3dExtended.js)
        console.log('[Init] Editor3D Extended loaded');
    </script>
</body>
</html>
