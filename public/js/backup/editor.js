import * as THREE from 'https://cdn.jsdelivr.net/npm/three@r128/build/three.module.js';
import { TransformControls } from 'https://cdn.jsdelivr.net/npm/three@r128/examples/jsm/controls/TransformControls.js';

export class EditControls {
    constructor(camera, renderer, scene) {
        this.camera = camera;
        this.renderer = renderer;
        this.scene = scene;

        this.transformControls = new TransformControls(camera, renderer.domElement);
        this.transformControls.addEventListener('change', () => this.onTransformChange?.());

        this.mode = 'translate'; // translate, rotate, scale
        this.selectedObject = null;
        this.currentTool = null;

        this.setupEventListeners();
    }

    setupEventListeners() {
        document.addEventListener('keydown', (e) => {
            const key = e.key.toLowerCase();

            // Transform modes
            if (key === 'g') this.setTransformMode('translate');
            if (key === 'r') this.setTransformMode('rotate');
            if (key === 's') this.setTransformMode('scale');

            // Delete
            if (key === 'delete') this.deleteSelected();
        });
    }

    setTransformMode(mode) {
        this.mode = mode;
        this.transformControls.setMode(mode);
    }

    selectObject(mesh) {
        if (this.selectedObject) {
            this.transformControls.detach(this.selectedObject);
        }

        this.selectedObject = mesh;

        if (mesh) {
            this.transformControls.attach(mesh);
        }

        return mesh;
    }

    deselectObject() {
        if (this.selectedObject) {
            this.transformControls.detach(this.selectedObject);
        }
        this.selectedObject = null;
    }

    deleteSelected() {
        if (this.selectedObject) {
            this.scene.remove(this.selectedObject);
            this.selectedObject = null;
            this.onObjectDeleted?.();
        }
    }

    update() {
        // Update transform controls if needed
    }

    dispose() {
        this.transformControls.dispose();
    }

    getSelectedObject() {
        return this.selectedObject;
    }

    getTransformControls() {
        return this.transformControls;
    }
}

export default EditControls;
