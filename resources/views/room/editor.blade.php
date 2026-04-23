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
    body {
        margin: 0;
        overflow: hidden;
    }

    #canvas-container {
        width: 100%;
        height: calc(100vh - 60px);
        position: relative;
        background: #1e293b;
    }

    #canvas {
        display: block;
        width: 100%;
        height: 100%;
    }

    /* Editor UI */
    #editor-ui {
        position: absolute;
        top: 70px;
        left: 0;
        width: 100%;
        height: calc(100vh - 70px);
        pointer-events: none;
        z-index: 10;
    }

    .ui-panel {
        pointer-events: all;
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(71, 85, 105, 0.5);
        border-radius: 8px;
        padding: 16px;
        color: white;
        font-family: system-ui, -apple-system, sans-serif;
    }

    /* Top-left: Controls */
    #controls-panel {
        position: absolute;
        top: 20px;
        left: 20px;
        width: 280px;
    }

    /* Top-right: Mode Info */
    #mode-panel {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 300px;
    }

    /* Bottom-left: Furniture */
    #furniture-panel {
        position: absolute;
        bottom: 20px;
        left: 20px;
        max-width: 400px;
        max-height: 300px;
        overflow-y: auto;
    }

    /* Bottom-right: Status */
    #status-panel {
        position: absolute;
        bottom: 20px;
        right: 20px;
        width: 300px;
    }

    .button {
        display: inline-block;
        padding: 8px 16px;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: background 0.2s;
        margin: 4px;
    }

    .button:hover {
        background: #2563eb;
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
        font-size: 14px;
        font-weight: 600;
        margin: 12px 0 8px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #cbd5e1;
    }

    .info-text {
        font-size: 12px;
        color: #94a3b8;
        margin: 4px 0;
        line-height: 1.5;
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
        padding: 8px;
        background: rgba(71, 85, 105, 0.3);
        border: 2px solid rgba(71, 85, 105, 0.5);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 24px;
    }

    .furniture-item:hover {
        background: rgba(71, 85, 105, 0.6);
        border-color: #3b82f6;
    }

    .furniture-item.selected {
        background: rgba(59, 130, 246, 0.3);
        border-color: #3b82f6;
    }

    .furniture-label {
        font-size: 10px;
        color: #94a3b8;
        margin-top: 4px;
        text-align: center;
    }

    .mode-badge {
        display: inline-block;
        padding: 6px 12px;
        background: #3b82f6;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin: 4px 0;
    }

    .mode-badge.explore {
        background: #8b5cf6;
    }

    .mode-badge.edit {
        background: #06b6d4;
    }

    .help-text {
        font-size: 11px;
        color: #64748b;
        margin: 8px 0;
        line-height: 1.6;
        background: rgba(2, 6, 23, 0.5);
        padding: 8px;
        border-left: 2px solid #3b82f6;
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
    }

    .visibility-toggle input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    /* Scrollbar styling */
    #furniture-panel::-webkit-scrollbar {
        width: 6px;
    }

    #furniture-panel::-webkit-scrollbar-track {
        background: rgba(71, 85, 105, 0.2);
        border-radius: 3px;
    }

    #furniture-panel::-webkit-scrollbar-thumb {
        background: rgba(71, 85, 105, 0.6);
        border-radius: 3px;
    }

    #furniture-panel::-webkit-scrollbar-thumb:hover {
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
</style>
@endsection

@section('content')
<div id="editor-container" style="position: relative; width: 100vw; height: calc(100vh - 60px); overflow: hidden;">
    <div id="canvas-container">
        <canvas id="canvas"></canvas>
    </div>

    <div id="editor-ui">
        <!-- Top-Left: Controls -->
        <div id="controls-panel" class="ui-panel">
            <div class="section-title">3D Editor Controls</div>

            <div class="mode-badge explore" id="mode-display">EXPLORE MODE</div>

            <button class="button" id="toggle-mode-btn" style="width: 100%; margin-top: 8px;">
                Switch to Edit Mode [E]
            </button>

            <button class="button danger" id="clear-scene-btn" style="width: 100%; margin-top: 8px;">
                Clear Scene
            </button>

            <button class="button" id="save-room-btn" style="width: 100%; margin-top: 8px;">
                💾 Save Room
            </button>

            <div class="help-text">
                <strong>Explore Mode:</strong><br/>
                WASD = Move<br/>
                Mouse = Look Around<br/>
                E = Edit Mode
            </div>

            <div class="help-text">
                <strong>Edit Mode:</strong><br/>
                Click = Select/Place<br/>
                G = Move<br/>
                R = Rotate<br/>
                S = Scale<br/>
                Del = Delete
            </div>

            <div class="section-title">Scene Info</div>
            <div class="info-text">
                📐 {{ $room->width }}m × {{ $room->length }}m × {{ $room->height }}m
            </div>
            <div class="info-text">
                Objects: <span id="object-count">0</span>
            </div>
        </div>

        <!-- Top-Right: Mode Info -->
        <div id="mode-panel" class="ui-panel">
            <div class="section-title">Current Tool</div>
            <div class="info-text" id="current-tool">None</div>

            <div class="section-title">Selected Object</div>
            <div class="info-text" id="selected-info">None</div>

            <button class="button danger" id="delete-selected-btn" style="width: 100%; margin-top: 8px; display: none;">
                🗑️ Delete Selected
            </button>
        </div>

        <!-- Bottom-Left: Furniture Selection -->
        <div id="furniture-panel" class="ui-panel">
            <div class="section-title">Furniture Library</div>
            <div class="furniture-grid" id="furniture-grid">
                <!-- Populated by JavaScript -->
            </div>
        </div>

        <!-- Bottom-Right: Status -->
        <div id="status-panel" class="ui-panel">
            <div class="section-title">Status</div>
            <div class="info-text" id="status-message">Ready</div>
            <div class="info-text" id="fps-counter">FPS: 60</div>
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
</script>

<!-- Load THREE.js and GLTFLoader as ES modules -->
<script type="module">
    import * as THREE from '/three-lib/three.module.min.js';
    import { GLTFLoader } from '/three-examples/jsm/loaders/GLTFLoader.js';
    
    // Make them globally available
    window.THREE = THREE;
    window.GLTFLoader = GLTFLoader;
    
    console.log('✅ THREE.js and GLTFLoader loaded as modules');
</script>

<!-- 1. Load AdvancedRoom3DEditor class definition -->
<script src="/js/editor-advanced.js?v={{ time() }}"></script>

<!-- 2. Initialize editor -->
<script src="/js/loader.js?v={{ time() }}"></script>
@endsection
