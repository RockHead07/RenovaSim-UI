import * as THREE from 'https://cdn.jsdelivr.net/npm/three@r128/build/three.module.js';

export class SceneModeManager {
    constructor(scene, camera, renderer) {
        this.scene = scene;
        this.camera = camera;
        this.renderer = renderer;

        this.currentMode = 'exploration'; // exploration, interior, build
        this.previousMode = null;

        this.modeData = {
            exploration: {
                name: 'Exploration',
                description: 'Explore the open world with your character',
                active: true,
            },
            interior: {
                name: 'Interior',
                description: 'Enter a building or room',
                active: false,
            },
            build: {
                name: 'Build',
                description: 'Edit and place furniture',
                active: false,
            },
        };

        this.sceneBackup = null;
        this.cameraBackup = {
            position: new THREE.Vector3(),
            rotation: new THREE.Euler(),
            fov: 75,
        };
    }

    /**
     * Switch to a different mode
     */
    switchMode(targetMode) {
        if (!this.modeData[targetMode]) {
            console.error(`Unknown mode: ${targetMode}`);
            return false;
        }

        if (this.currentMode === targetMode) {
            return false; // Already in this mode
        }

        console.log(`[SceneModeManager] Switching from ${this.currentMode} to ${targetMode}`);

        // Exit current mode
        this.exitMode(this.currentMode);

        // Update mode tracking
        this.previousMode = this.currentMode;
        this.currentMode = targetMode;

        // Enter new mode
        this.enterMode(targetMode);

        return true;
    }

    /**
     * Enter a specific mode
     */
    enterMode(mode) {
        this.modeData[mode].active = true;

        switch (mode) {
            case 'exploration':
                this.setupExplorationMode();
                break;
            case 'interior':
                this.setupInteriorMode();
                break;
            case 'build':
                this.setupBuildMode();
                break;
        }

        this.onModeChanged?.(mode);
    }

    /**
     * Exit current mode
     */
    exitMode(mode) {
        this.modeData[mode].active = false;

        switch (mode) {
            case 'exploration':
                this.cleanupExplorationMode();
                break;
            case 'interior':
                this.cleanupInteriorMode();
                break;
            case 'build':
                this.cleanupBuildMode();
                break;
        }
    }

    /**
     * Setup exploration mode (open world with character)
     */
    setupExplorationMode() {
        // Save camera state
        this.saveCameraState();

        // Show exploration UI
        this.updateUI('EXPLORATION MODE', 'Use WASD to move, mouse to look around, SPACE to jump');

        // Ensure scene is visible
        this.scene.visible = true;

        // Enable shadows
        this.renderer.shadowMap.enabled = true;

        this.onExplorationModeSetup?.();
    }

    /**
     * Cleanup exploration mode
     */
    cleanupExplorationMode() {
        this.onExplorationModeCleanup?.();
    }

    /**
     * Setup interior mode (inside a building/room with TPP)
     */
    setupInteriorMode() {
        this.saveCameraState();
        this.updateUI('INTERIOR MODE', 'Use WASD to move, mouse to look around. Press B to enter Build mode');

        this.scene.visible = true;
        this.renderer.shadowMap.enabled = true;

        this.onInteriorModeSetup?.();
    }

    /**
     * Cleanup interior mode
     */
    cleanupInteriorMode() {
        this.onInteriorModeCleanup?.();
    }

    /**
     * Setup build mode (like Unity editor with gizmo)
     */
    setupBuildMode() {
        this.saveCameraState();
        this.updateUI('BUILD MODE', 'Select objects to edit. Press G/R/S for move/rotate/scale. E to exit build mode');

        this.scene.visible = true;
        this.renderer.shadowMap.enabled = true;

        // Show gizmo UI
        this.showGizmoUI();

        this.onBuildModeSetup?.();
    }

    /**
     * Cleanup build mode
     */
    cleanupBuildMode() {
        this.hideGizmoUI();
        this.onBuildModeCleanup?.();
    }

    /**
     * Save current camera state
     */
    saveCameraState() {
        this.cameraBackup.position.copy(this.camera.position);
        this.cameraBackup.rotation.copy(this.camera.rotation);
        if (this.camera instanceof THREE.PerspectiveCamera) {
            this.cameraBackup.fov = this.camera.fov;
        }
    }

    /**
     * Restore camera state
     */
    restoreCameraState() {
        this.camera.position.copy(this.cameraBackup.position);
        this.camera.rotation.copy(this.cameraBackup.rotation);
        if (this.camera instanceof THREE.PerspectiveCamera) {
            this.camera.fov = this.cameraBackup.fov;
            this.camera.updateProjectionMatrix();
        }
    }

    /**
     * Update UI display
     */
    updateUI(title, description) {
        const modeDisplay = document.getElementById('mode-display');
        const modeDescription = document.getElementById('mode-description');

        if (modeDisplay) {
            modeDisplay.textContent = title;
            modeDisplay.className = `mode-badge ${this.currentMode}`;
        }

        if (modeDescription) {
            modeDescription.textContent = description;
        }

        this.onUIUpdate?.(title, description);
    }

    /**
     * Show gizmo/transform UI
     */
    showGizmoUI() {
        const gizmoPanel = document.getElementById('gizmo-panel');
        if (gizmoPanel) {
            gizmoPanel.style.display = 'block';
        }

        // Show transform mode buttons
        const transformModes = document.getElementById('transform-modes');
        if (transformModes) {
            transformModes.style.display = 'flex';
        }
    }

    /**
     * Hide gizmo/transform UI
     */
    hideGizmoUI() {
        const gizmoPanel = document.getElementById('gizmo-panel');
        if (gizmoPanel) {
            gizmoPanel.style.display = 'none';
        }

        const transformModes = document.getElementById('transform-modes');
        if (transformModes) {
            transformModes.style.display = 'none';
        }
    }

    /**
     * Get current mode
     */
    getCurrentMode() {
        return this.currentMode;
    }

    /**
     * Check if in specific mode
     */
    isMode(mode) {
        return this.currentMode === mode;
    }

    /**
     * Get mode data
     */
    getModeData(mode) {
        return this.modeData[mode];
    }

    /**
     * Get all modes
     */
    getAllModes() {
        return Object.keys(this.modeData);
    }
}

export default SceneModeManager;
