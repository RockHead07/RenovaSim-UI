import Scene3DExtended from './scene3dExtended.js';
import ThirdPersonController from './thirdPersonController.js';
import OpenWorldGenerator from './openWorldGenerator.js';
import InteriorSceneManager from './interiorSceneManager.js';
import SceneModeManager from './sceneModeManager.js';
import GizmoControls from './gizmoControls.js';
import { Furniture, Raycaster3D } from './furniture.js';
import * as THREE from 'https://cdn.jsdelivr.net/npm/three@r128/build/three.module.js';

class Editor3DExtended {
    constructor() {
        this.canvas = document.getElementById('canvas');
        this.scene3d = null;
        this.modeManager = null;
        this.thirdPersonController = null;
        this.worldGenerator = null;
        this.interiorManager = null;
        this.gizmoControls = null;
        this.raycaster = null;

        // State
        this.sceneObjects = [];
        this.lastTime = Date.now();
        this.currentTool = null;

        // Data
        this.roomData = window.roomData || { room: { width: 4, length: 5, height: 3 }, objects: [] };
        this.csrfToken = window.csrfToken || '';
        this.saveUrl = window.saveUrl || '/api/save-room';
    }

    async init() {
        console.log('[Editor3DExtended] Initializing...');

        // Initialize 3D scene
        this.scene3d = new Scene3DExtended(this.canvas, 'exploration');
        this.scene3d.setupExplorationScene();

        // Initialize mode manager
        this.modeManager = new SceneModeManager(this.scene3d.getScene(), this.scene3d.getCamera(), this.scene3d.getRenderer());

        // Initialize world generator for exploration
        this.worldGenerator = new OpenWorldGenerator(this.scene3d.getScene());
        this.generateWorld();

        // Initialize third person controller
        this.thirdPersonController = new ThirdPersonController(this.scene3d.getCamera(), this.canvas);
        this.thirdPersonController.setBounds(-100, 100, -100, 100, 0);
        this.scene3d.getScene().add(this.thirdPersonController.getCharacterMesh());

        // Initialize interior manager
        this.interiorManager = new InteriorSceneManager(this.scene3d.getScene());
        this.setupInteriorRooms();

        // Initialize gizmo controls (for build mode)
        this.gizmoControls = new GizmoControls(
            this.scene3d.getCamera(),
            this.scene3d.getRenderer(),
            this.scene3d.getScene()
        );

        // Initialize raycaster
        this.raycaster = new Raycaster3D(this.scene3d.getCamera(), this.scene3d.getScene());

        // Setup UI
        this.setupUI();

        // Load existing objects
        this.loadObjects();

        // Setup event listeners
        this.setupEventListeners();

        // Start render loop
        this.startRenderLoop();

        console.log('[Editor3DExtended] Initialization complete!');
    }

    /**
     * Generate exploration world
     */
    generateWorld() {
        // Generate infinite-like ground
        this.worldGenerator.generateInfiniteGround();

        // Generate procedural city
        this.worldGenerator.generateProceduralCity(0, 0, 50);

        // Generate trees
        this.worldGenerator.generateTrees(0, 0, 100);

        // Generate roads
        this.worldGenerator.generateRoads(0, 0, 50);
    }

    /**
     * Setup interior rooms
     */
    setupInteriorRooms() {
        // Create main house/building room
        const room1 = this.interiorManager.createRoom('main', 'Main Room', 8, 10, 3.5);

        // Create bedroom
        const room2 = this.interiorManager.createRoom('bedroom', 'Bedroom', 5, 6, 3);

        // Create kitchen
        const room3 = this.interiorManager.createRoom('kitchen', 'Kitchen', 4, 5, 3);

        console.log('[Editor3DExtended] Interior rooms created');
    }

    /**
     * Setup UI
     */
    setupUI() {
        // Mode display
        this.modeManager.updateUI('EXPLORATION MODE', 'Use WASD to move, mouse to look around');

        // Toggle mode button (E key)
        document.addEventListener('keydown', (e) => {
            if (e.key.toLowerCase() === 'e') {
                this.toggleMode();
            }

            // B key to enter build mode from interior
            if (e.key.toLowerCase() === 'b' && this.modeManager.isMode('interior')) {
                this.modeManager.switchMode('build');
            }
        });

        // Mode buttons
        const modeButtons = {
            'mode-explore': () => this.modeManager.switchMode('exploration'),
            'mode-interior': () => this.modeManager.switchMode('interior'),
            'mode-build': () => this.modeManager.switchMode('build'),
        };

        for (const [id, callback] of Object.entries(modeButtons)) {
            const btn = document.getElementById(id);
            if (btn) btn.addEventListener('click', callback);
        }

        // Furniture/object selection (for build mode)
        const furnitureGrid = document.getElementById('furniture-grid');
        if (furnitureGrid) {
            Furniture.getAvailableTypes().forEach((type) => {
                const item = document.createElement('div');
                item.className = 'furniture-item';
                item.innerHTML = `
                    <div>${Furniture.MODELS[type].emoji}</div>
                    <div class="furniture-label">${type}</div>
                `;
                item.addEventListener('click', () => this.selectTool(type, item));
                furnitureGrid.appendChild(item);
            });
        }

        // Clear scene button
        const clearBtn = document.getElementById('clear-scene-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to clear all objects?')) {
                    this.clearScene();
                }
            });
        }

        // Save button
        const saveBtn = document.getElementById('save-room-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveRoom());
        }

        // Delete selected button
        const deleteBtn = document.getElementById('delete-selected-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => this.deleteSelected());
        }

        // Canvas click for object placement (build mode)
        this.canvas.addEventListener('click', (e) => {
            if (this.modeManager.isMode('build')) {
                this.onCanvasClick(e);
            }
        });

        // Setup mode manager callbacks
        this.modeManager.onModeChanged = (mode) => {
            this.handleModeChange(mode);
        };

        // Update UI info display
        setInterval(() => {
            const objectCount = document.getElementById('object-count');
            if (objectCount) objectCount.textContent = this.sceneObjects.length;

            const toolDisplay = document.getElementById('current-tool');
            if (toolDisplay) toolDisplay.textContent = this.currentTool || 'None';

            const selectedInfo = document.getElementById('selected-info');
            if (selectedInfo) {
                const selected = this.gizmoControls.getSelectedObject();
                selectedInfo.textContent = selected ? `${selected.userData.type} (${selected.userData.emoji})` : 'None';
            }
        }, 100);
    }

    /**
     * Handle mode changes
     */
    handleModeChange(newMode) {
        console.log(`[Editor3DExtended] Mode changed to: ${newMode}`);

        if (newMode === 'exploration') {
            // Show world, hide interior
            this.interiorManager.exitRoom();
            this.thirdPersonController.exitPointerLock();
        } else if (newMode === 'interior') {
            // Enter interior room
            const mainRoom = this.interiorManager.getRoom('main');
            if (mainRoom) {
                this.interiorManager.enterRoom('main');
                // Position character at room entrance
                this.thirdPersonController.setCharacterPosition(0, 1, 3);
            }
        } else if (newMode === 'build') {
            // Enter build mode (interior must be active)
            if (!this.interiorManager.getCurrentRoom()) {
                this.interiorManager.enterRoom('main');
            }
            // Enable gizmo, disable character
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Canvas mouse move for raycasting
        this.canvas.addEventListener('mousemove', (e) => {
            if (this.modeManager.isMode('build')) {
                this.raycaster.setMousePosition(e);
            }
        });

        // Mode manager callbacks
        this.modeManager.onExplorationModeSetup = () => {
            this.gizmoControls.setEnabled(false);
            console.log('[UI] Exploration mode active');
        };

        this.modeManager.onInteriorModeSetup = () => {
            this.gizmoControls.setEnabled(false);
            console.log('[UI] Interior mode active');
        };

        this.modeManager.onBuildModeSetup = () => {
            this.gizmoControls.setEnabled(true);
            console.log('[UI] Build mode active');
        };

        // Gizmo callbacks
        this.gizmoControls.onObjectSelected = (obj) => {
            console.log(`[GizmoControls] Selected: ${obj.userData.type}`);
        };

        this.gizmoControls.onObjectDeleted = (obj) => {
            this.sceneObjects = this.sceneObjects.filter((o) => o !== obj);
        };
    }

    /**
     * Toggle between modes
     */
    toggleMode() {
        const modes = ['exploration', 'interior', 'build'];
        const currentIndex = modes.indexOf(this.modeManager.getCurrentMode());
        const nextMode = modes[(currentIndex + 1) % modes.length];
        this.modeManager.switchMode(nextMode);
    }

    /**
     * Select furniture tool
     */
    selectTool(toolName, element) {
        document.querySelectorAll('.furniture-item').forEach((el) => {
            el.classList.remove('selected');
        });
        if (element) {
            element.classList.add('selected');
        }

        this.currentTool = toolName;
        document.getElementById('current-tool').textContent = toolName;
    }

    /**
     * Handle canvas click (place object in build mode)
     */
    onCanvasClick(e) {
        this.raycaster.setMousePosition(e);

        // Check if clicking on existing object
        const intersections = this.raycaster.getIntersections(false);
        if (intersections.length > 0) {
            const clicked = intersections[0].object;
            if (clicked.userData.type) {
                this.gizmoControls.selectObject(clicked);
            }
            return;
        }

        // If tool selected, place new object
        if (this.currentTool) {
            const floorIntersections = this.raycaster.getFloorIntersection();
            if (floorIntersections.length > 0) {
                const point = floorIntersections[0].point;
                this.addObject(this.currentTool, [point.x, 0.1, point.z]);
            }
        }
    }

    /**
     * Add object to scene
     */
    addObject(type, position = [0, 0.1, 0], rotation = [0, 0, 0], scale = [1, 1, 1]) {
        const mesh = Furniture.createModel(type);
        if (!mesh) return;

        mesh.position.set(...position);
        mesh.rotation.set(...rotation);
        mesh.scale.set(...scale);

        // Add to appropriate scene
        if (this.modeManager.isMode('interior') && this.interiorManager.getCurrentRoom()) {
            this.interiorManager.addObjectToRoom(mesh);
        } else {
            this.scene3d.getScene().add(mesh);
        }

        this.sceneObjects.push(mesh);
        console.log(`[Editor3DExtended] Added ${type}`);

        return mesh;
    }

    /**
     * Delete selected object
     */
    deleteSelected() {
        const obj = this.gizmoControls.getSelectedObject();
        if (obj) {
            this.gizmoControls.deselectObject();
            this.scene3d.getScene().remove(obj);
            this.sceneObjects = this.sceneObjects.filter((o) => o !== obj);
            console.log('[Editor3DExtended] Object deleted');
        }
    }

    /**
     * Clear all objects
     */
    clearScene() {
        this.sceneObjects.forEach((obj) => {
            this.scene3d.getScene().remove(obj);
        });
        this.sceneObjects = [];
        this.gizmoControls.deselectObject();
        console.log('[Editor3DExtended] Scene cleared');
    }

    /**
     * Load objects from data
     */
    loadObjects() {
        if (this.roomData.objects && this.roomData.objects.length > 0) {
            this.roomData.objects.forEach((objData) => {
                this.addObject(objData.type, objData.position, objData.rotation, objData.scale);
            });
            console.log(`[Editor3DExtended] Loaded ${this.sceneObjects.length} objects`);
        }
    }

    /**
     * Save room to server
     */
    async saveRoom() {
        const saveBtn = document.getElementById('save-room-btn');
        const originalText = saveBtn?.textContent || 'Save Room';

        if (saveBtn) {
            saveBtn.innerHTML = '<span class="loading-spinner"></span> Saving...';
            saveBtn.disabled = true;
        }

        try {
            const objects = this.sceneObjects.map((obj) => ({
                type: obj.userData.type,
                position: [obj.position.x, obj.position.y, obj.position.z],
                rotation: [obj.rotation.x, obj.rotation.y, obj.rotation.z],
                scale: [obj.scale.x, obj.scale.y, obj.scale.z],
            }));

            const response = await fetch(this.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({ objects }),
            });

            if (response.ok) {
                const statusMsg = document.getElementById('status-message');
                if (statusMsg) {
                    statusMsg.textContent = '✅ Room saved successfully!';
                    setTimeout(() => {
                        statusMsg.textContent = 'Ready';
                    }, 3000);
                }
                console.log('[Editor3DExtended] Room saved');
            } else {
                throw new Error('Save failed');
            }
        } catch (error) {
            console.error('[Editor3DExtended] Save error:', error);
            const statusMsg = document.getElementById('status-message');
            if (statusMsg) statusMsg.textContent = '❌ Save failed: ' + error.message;
        } finally {
            if (saveBtn) {
                saveBtn.textContent = originalText;
                saveBtn.disabled = false;
            }
        }
    }

    /**
     * Start render loop
     */
    startRenderLoop() {
        const animate = () => {
            requestAnimationFrame(animate);

            const now = Date.now();
            const deltaTime = Math.min((now - this.lastTime) / 1000, 0.016); // Cap at 60 FPS
            this.lastTime = now;

            // Update based on mode
            if (this.modeManager.isMode('exploration')) {
                this.thirdPersonController.update(deltaTime);
            } else if (this.modeManager.isMode('interior')) {
                this.thirdPersonController.update(deltaTime);
            }
            // Build mode: gizmo handles updates

            // Render
            this.scene3d.render();
        };

        animate();
    }
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.editor3d = new Editor3DExtended();
        window.editor3d.init();
    });
} else {
    window.editor3d = new Editor3DExtended();
    window.editor3d.init();
}

export default Editor3DExtended;
