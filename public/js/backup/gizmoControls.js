import * as THREE from 'https://cdn.jsdelivr.net/npm/three@r128/build/three.module.js';
import { TransformControls } from 'https://cdn.jsdelivr.net/npm/three@r128/examples/jsm/controls/TransformControls.js';

export class GizmoControls {
    constructor(camera, renderer, scene) {
        this.camera = camera;
        this.renderer = renderer;
        this.scene = scene;

        this.transformControls = new TransformControls(camera, renderer.domElement);
        this.transformControls.setSize(1);

        // Mode states
        this.mode = 'translate'; // translate, rotate, scale
        this.space = 'world'; // world, local
        this.selectedObject = null;

        this.setupEventListeners();
        this.setupTransformControlEvents();
    }

    /**
     * Setup keyboard shortcuts
     */
    setupEventListeners() {
        document.addEventListener('keydown', (e) => {
            const key = e.key.toLowerCase();

            // Transform modes
            if (key === 'g') {
                this.setMode('translate');
                this.updateModeUI('translate');
            }
            if (key === 'r') {
                this.setMode('rotate');
                this.updateModeUI('rotate');
            }
            if (key === 's') {
                this.setMode('scale');
                this.updateModeUI('scale');
            }

            // Space toggle
            if (key === 'x') {
                this.space = this.space === 'world' ? 'local' : 'world';
                this.transformControls.setSpace(this.space);
                this.updateSpaceUI(this.space);
            }

            // Snap toggle
            if (key === 'n') {
                const snap = this.transformControls.translationSnap;
                this.transformControls.setTranslationSnap(snap ? null : 0.5);
                this.transformControls.setRotationSnap(snap ? null : Math.PI / 16);
                this.transformControls.setScaleSnap(snap ? null : 0.5);
            }

            // Delete
            if (key === 'delete' || key === 'backspace') {
                this.deleteSelected();
            }
        });

        // UI Button clicks
        const buttons = {
            'gizmo-translate': () => this.setMode('translate'),
            'gizmo-rotate': () => this.setMode('rotate'),
            'gizmo-scale': () => this.setMode('scale'),
            'gizmo-world': () => this.toggleSpace(),
        };

        for (const [id, callback] of Object.entries(buttons)) {
            const btn = document.getElementById(id);
            if (btn) btn.addEventListener('click', callback);
        }
    }

    /**
     * Setup TransformControls events
     */
    setupTransformControlEvents() {
        this.transformControls.addEventListener('change', () => {
            this.renderer.render(this.scene, this.camera);
        });

        this.transformControls.addEventListener('dragging-changed', (event) => {
            if (this.selectedObject) {
                this.onObjectTransformed?.(this.selectedObject);
            }
        });
    }

    /**
     * Set transform mode
     */
    setMode(mode) {
        if (!['translate', 'rotate', 'scale'].includes(mode)) {
            console.warn(`Invalid mode: ${mode}`);
            return;
        }

        this.mode = mode;
        this.transformControls.setMode(mode);
        console.log(`[GizmoControls] Mode changed to: ${mode}`);

        this.onModeChanged?.(mode);
    }

    /**
     * Toggle space (world/local)
     */
    toggleSpace() {
        this.space = this.space === 'world' ? 'local' : 'world';
        this.transformControls.setSpace(this.space);
        this.updateSpaceUI(this.space);
    }

    /**
     * Select object
     */
    selectObject(mesh) {
        // Deselect previous
        if (this.selectedObject) {
            this.deselectObject();
        }

        this.selectedObject = mesh;

        if (mesh) {
            // Attach gizmo
            this.transformControls.attach(mesh);

            // Add highlight
            this.addHighlight(mesh);

            console.log(`[GizmoControls] Selected: ${mesh.userData.type || 'object'}`);
            this.onObjectSelected?.(mesh);
        }

        return mesh;
    }

    /**
     * Deselect current object
     */
    deselectObject() {
        if (this.selectedObject) {
            this.transformControls.detach(this.selectedObject);
            this.removeHighlight(this.selectedObject);

            this.selectedObject = null;
            this.onObjectDeselected?.();
        }
    }

    /**
     * Add highlight to object
     */
    addHighlight(mesh) {
        if (mesh.userData.highlight) return; // Already highlighted

        // Create outline with EdgesGeometry
        const edges = new THREE.EdgesGeometry(mesh.geometry);
        const wireframe = new THREE.LineSegments(
            edges,
            new THREE.LineBasicMaterial({ color: 0x00ff00, linewidth: 3 })
        );
        wireframe.name = 'highlight';
        mesh.add(wireframe);
        mesh.userData.highlight = wireframe;
    }

    /**
     * Remove highlight from object
     */
    removeHighlight(mesh) {
        if (mesh.userData.highlight) {
            mesh.remove(mesh.userData.highlight);
            mesh.userData.highlight = null;
        }
    }

    /**
     * Delete selected object
     */
    deleteSelected() {
        if (this.selectedObject) {
            const obj = this.selectedObject;
            this.deselectObject();
            this.scene.remove(obj);
            this.onObjectDeleted?.(obj);
            console.log(`[GizmoControls] Deleted object`);
        }
    }

    /**
     * Update mode UI
     */
    updateModeUI(mode) {
        const buttons = {
            translate: 'gizmo-translate',
            rotate: 'gizmo-rotate',
            scale: 'gizmo-scale',
        };

        document.querySelectorAll('[id^="gizmo-"]').forEach((btn) => {
            btn.classList.remove('active');
        });

        const activeBtn = document.getElementById(buttons[mode]);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }

        const modeDisplay = document.getElementById('gizmo-mode');
        if (modeDisplay) {
            modeDisplay.textContent = mode.toUpperCase();
        }
    }

    /**
     * Update space UI
     */
    updateSpaceUI(space) {
        const spaceBtn = document.getElementById('gizmo-world');
        if (spaceBtn) {
            spaceBtn.textContent = space.toUpperCase();
            spaceBtn.classList.toggle('active', space === 'local');
        }
    }

    /**
     * Get selected object
     */
    getSelectedObject() {
        return this.selectedObject;
    }

    /**
     * Get current mode
     */
    getMode() {
        return this.mode;
    }

    /**
     * Get transform controls
     */
    getTransformControls() {
        return this.transformControls;
    }

    /**
     * Enable/disable gizmo
     */
    setEnabled(enabled) {
        this.transformControls.enabled = enabled;
    }

    /**
     * Dispose resources
     */
    dispose() {
        this.transformControls.dispose();
    }
}

export default GizmoControls;
