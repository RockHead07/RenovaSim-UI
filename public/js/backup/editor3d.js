import Scene3D from './scene.js';
import ExploreControls from './controls.js';
import EditControls from './editor.js';
import { Furniture, Raycaster3D } from './furniture.js';
import * as THREE from 'https://cdn.jsdelivr.net/npm/three@r128/build/three.module.js';

class Editor3D {
    constructor() {
        this.canvas = document.getElementById('canvas');
        this.scene3d = null;
        this.exploreControls = null;
        this.editControls = null;
        this.raycaster = null;

        this.isExploreMode = true;
        this.currentTool = null;
        this.sceneObjects = [];
        this.lastTime = Date.now();

        this.roomData = window.roomData;
        this.csrfToken = window.csrfToken;
        this.saveUrl = window.saveUrl;
    }

    async init() {
        // Initialize 3D scene
        this.scene3d = new Scene3D(
            this.canvas,
            this.roomData.room.width,
            this.roomData.room.length,
            this.roomData.room.height
        );

        // Set bounds for explore mode
        this.exploreControls = new ExploreControls(
            this.scene3d.camera,
            this.canvas
        );
        this.exploreControls.setBounds(
            0.5,
            this.roomData.room.width - 0.5,
            0.5,
            this.roomData.room.height - 0.5,
            0.5,
            this.roomData.room.length - 0.5
        );

        // Initialize edit controls
        this.editControls = new EditControls(
            this.scene3d.camera,
            this.scene3d.renderer,
            this.scene3d.scene
        );

        // Initialize raycaster
        this.raycaster = new Raycaster3D(
            this.scene3d.camera,
            this.scene3d.scene
        );

        // Setup UI
        this.setupUI();

        // Load existing objects
        this.loadObjects();

        // Setup event listeners
        this.setupEventListeners();

        // Start render loop
        this.startRenderLoop();
    }

    setupUI() {
        // Mode display
        const modeDisplay = document.getElementById('mode-display');

        // Toggle mode button
        document.getElementById('toggle-mode-btn').addEventListener('click', () => {
            this.toggleMode();
        });

        // Furniture grid
        const furnitureGrid = document.getElementById('furniture-grid');
        Furniture.getAvailableTypes().forEach(type => {
            const item = document.createElement('div');
            item.className = 'furniture-item';
            item.innerHTML = `
                <div>${Furniture.MODELS[type].emoji}</div>
                <div class="furniture-label">${type}</div>
            `;
            item.addEventListener('click', () => {
                this.selectTool(type, item);
            });
            furnitureGrid.appendChild(item);
        });

        // Clear scene button
        document.getElementById('clear-scene-btn').addEventListener('click', () => {
            if (confirm('Are you sure you want to clear all objects?')) {
                this.clearScene();
            }
        });

        // Save button
        document.getElementById('save-room-btn').addEventListener('click', () => {
            this.saveRoom();
        });

        // Delete selected button
        const deleteBtn = document.getElementById('delete-selected-btn');
        deleteBtn.addEventListener('click', () => {
            this.deleteSelected();
        });

        // Canvas click for placing objects
        this.canvas.addEventListener('click', (e) => {
            if (!this.isExploreMode) {
                this.onCanvasClick(e);
            }
        });

        // Update UI display
        setInterval(() => {
            modeDisplay.textContent = this.isExploreMode ? 'EXPLORE MODE' : 'EDIT MODE';
            modeDisplay.className = this.isExploreMode ? 'mode-badge explore' : 'mode-badge edit';

            deleteBtn.style.display = this.editControls.getSelectedObject() ? 'block' : 'none';

            document.getElementById('object-count').textContent = this.sceneObjects.length;
            document.getElementById('current-tool').textContent = this.currentTool || 'None';

            const selectedObj = this.editControls.getSelectedObject();
            if (selectedObj) {
                document.getElementById('selected-info').textContent = `${selectedObj.userData.type} (${selectedObj.userData.emoji})`;
            } else {
                document.getElementById('selected-info').textContent = 'None';
            }
        }, 100);
    }

    setupEventListeners() {
        // Toggle mode with E key
        document.addEventListener('keydown', (e) => {
            if (e.key.toLowerCase() === 'e') {
                this.toggleMode();
            }
        });

        // Canvas events for edit mode
        this.canvas.addEventListener('mousemove', (e) => {
            if (!this.isExploreMode) {
                this.raycaster.setMousePosition(e);
            }
        });
    }

    toggleMode() {
        this.isExploreMode = !this.isExploreMode;

        if (this.isExploreMode) {
            // Exit edit mode
            this.editControls.deselectObject();
            document.exitPointerLock?.();
            console.log('Switched to EXPLORE MODE');
        } else {
            // Enter edit mode
            this.exploreControls.exit();
            console.log('Switched to EDIT MODE');
        }

        document.getElementById('status-message').textContent =
            this.isExploreMode ? 'Explore mode active' : 'Edit mode active';
    }

    selectTool(toolName, element) {
        // Update UI
        document.querySelectorAll('.furniture-item').forEach(el => {
            el.classList.remove('selected');
        });
        if (element) {
            element.classList.add('selected');
        }

        this.currentTool = toolName;
        document.getElementById('current-tool').textContent = toolName;
    }

    onCanvasClick(e) {
        this.raycaster.setMousePosition(e);

        // Check if clicking on existing object
        const intersections = this.raycaster.getIntersections(false);
        if (intersections.length > 0) {
            const clicked = intersections[0].object;
            this.editControls.selectObject(clicked);
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

    addObject(type, position = [0, 0.1, 0], rotation = [0, 0, 0], scale = [1, 1, 1]) {
        const mesh = Furniture.createModel(type);
        if (!mesh) return;

        mesh.position.set(...position);
        mesh.rotation.set(...rotation);
        mesh.scale.set(...scale);

        this.scene3d.scene.add(mesh);
        this.sceneObjects.push(mesh);

        document.getElementById('status-message').textContent = `Added ${type}`;

        return mesh;
    }

    deleteSelected() {
        const obj = this.editControls.getSelectedObject();
        if (obj) {
            this.scene3d.scene.remove(obj);
            this.sceneObjects = this.sceneObjects.filter(o => o !== obj);
            this.editControls.deselectObject();
            document.getElementById('status-message').textContent = 'Object deleted';
        }
    }

    clearScene() {
        this.sceneObjects.forEach(obj => {
            this.scene3d.scene.remove(obj);
        });
        this.sceneObjects = [];
        this.editControls.deselectObject();
        document.getElementById('status-message').textContent = 'Scene cleared';
    }

    loadObjects() {
        if (this.roomData.objects && this.roomData.objects.length > 0) {
            this.roomData.objects.forEach(objData => {
                this.addObject(
                    objData.type,
                    objData.position,
                    objData.rotation,
                    objData.scale
                );
            });
            document.getElementById('status-message').textContent = `Loaded ${this.sceneObjects.length} objects`;
        }
    }

    async saveRoom() {
        const saveBtn = document.getElementById('save-room-btn');
        const originalText = saveBtn.textContent;
        saveBtn.innerHTML = '<span class="loading-spinner"></span> Saving...';
        saveBtn.disabled = true;

        try {
            const objects = this.sceneObjects.map(obj => ({
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
                document.getElementById('status-message').textContent = '✅ Room saved successfully!';
                setTimeout(() => {
                    document.getElementById('status-message').textContent = 'Ready';
                }, 3000);
            } else {
                throw new Error('Save failed');
            }
        } catch (error) {
            console.error('Save error:', error);
            document.getElementById('status-message').textContent = '❌ Save failed: ' + error.message;
        } finally {
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        }
    }

    startRenderLoop() {
        const animate = () => {
            requestAnimationFrame(animate);

            const now = Date.now();
            const deltaTime = (now - this.lastTime) / 1000;
            this.lastTime = now;

            // Update controls
            if (this.isExploreMode) {
                this.exploreControls.update(deltaTime);
            }

            // Render scene
            this.scene3d.render();

            // Update FPS
            const fps = Math.round(1 / deltaTime);
            document.getElementById('fps-counter').textContent = `FPS: ${fps}`;
        };

        animate();
    }
}

// Export
window.Editor3D = Editor3D;
export default Editor3D;
