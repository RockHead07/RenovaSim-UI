import * as THREE from 'https://cdn.jsdelivr.net/npm/three@r128/build/three.module.js';

export class Scene3DExtended {
    constructor(canvas, initialMode = 'exploration') {
        this.canvas = canvas;
        this.mode = initialMode;

        // Core THREE objects
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x87ceeb); // Sky blue
        this.scene.fog = new THREE.Fog(0x87ceeb, 100, 200);

        this.camera = new THREE.PerspectiveCamera(
            75,
            window.innerWidth / (window.innerHeight - 60),
            0.1,
            1000
        );
        this.camera.position.set(0, 2, 5);

        this.renderer = new THREE.WebGLRenderer({
            canvas: this.canvas,
            antialias: true,
            alpha: true,
        });
        this.renderer.setSize(window.innerWidth, window.innerHeight - 60);
        this.renderer.setPixelRatio(window.devicePixelRatio);
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFShadowShadowMap;
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.0;

        // Scene layers
        this.layers = {
            exploration: [],
            interior: [],
            build: [],
        };

        // Setup
        this.setupLights();
        this.setupEventListeners();

        // Initialize based on mode
        if (initialMode === 'exploration') {
            this.setupExplorationScene();
        }
    }

    /**
     * Setup lights for the scene
     */
    setupLights() {
        // Ambient light
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);

        // Directional light with shadows
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(50, 100, 50);
        directionalLight.castShadow = true;
        directionalLight.shadow.mapSize.width = 2048;
        directionalLight.shadow.mapSize.height = 2048;
        directionalLight.shadow.camera.left = -100;
        directionalLight.shadow.camera.right = 100;
        directionalLight.shadow.camera.top = 100;
        directionalLight.shadow.camera.bottom = -100;
        directionalLight.shadow.camera.near = 0.5;
        directionalLight.shadow.camera.far = 500;
        this.scene.add(directionalLight);

        // Sky light
        const hemisphereLight = new THREE.HemisphereLight(0x87ceeb, 0x654321, 0.5);
        this.scene.add(hemisphereLight);
    }

    /**
     * Setup exploration scene with open world
     */
    setupExplorationScene() {
        // Clear previous layers
        this.clearLayerObjects('exploration');

        // Create infinite-like ground
        const groundGeometry = new THREE.PlaneGeometry(500, 500);
        const groundMaterial = new THREE.MeshStandardMaterial({
            color: 0x7cb342,
            metalness: 0.0,
            roughness: 0.9,
        });
        const ground = new THREE.Mesh(groundGeometry, groundMaterial);
        ground.rotation.x = -Math.PI / 2;
        ground.receiveShadow = true;
        ground.userData.isFloor = true;
        this.scene.add(ground);
        this.layers.exploration.push(ground);

        // Add grid
        const gridHelper = new THREE.GridHelper(200, 40, 0x444444, 0x888888);
        gridHelper.position.y = 0.01;
        this.scene.add(gridHelper);
        this.layers.exploration.push(gridHelper);

        // Fog for distance
        this.scene.fog.far = 200;
    }

    /**
     * Setup interior room scene
     */
    setupInteriorScene(roomWidth = 4, roomLength = 5, roomHeight = 3) {
        this.clearLayerObjects('interior');

        const materials = {
            floor: new THREE.MeshStandardMaterial({
                color: 0xd4d4d8,
                metalness: 0.1,
                roughness: 0.8,
            }),
            walls: new THREE.MeshStandardMaterial({
                color: 0xf1f5f9,
                metalness: 0.0,
                roughness: 0.9,
            }),
        };

        // Floor
        const floorGeometry = new THREE.PlaneGeometry(roomWidth, roomLength);
        const floor = new THREE.Mesh(floorGeometry, materials.floor);
        floor.rotation.x = -Math.PI / 2;
        floor.receiveShadow = true;
        floor.userData.isFloor = true;
        this.scene.add(floor);
        this.layers.interior.push(floor);

        // Ceiling
        const ceilingGeometry = new THREE.PlaneGeometry(roomWidth, roomLength);
        const ceiling = new THREE.Mesh(ceilingGeometry, materials.walls);
        ceiling.rotation.x = Math.PI / 2;
        ceiling.position.y = roomHeight;
        ceiling.receiveShadow = true;
        this.scene.add(ceiling);
        this.layers.interior.push(ceiling);

        // Walls
        const wallGeometries = [
            { pos: [0, roomHeight / 2, roomLength / 2], rot: [0, 0, 0], size: [roomWidth, roomHeight] }, // front
            { pos: [0, roomHeight / 2, -roomLength / 2], rot: [0, Math.PI, 0], size: [roomWidth, roomHeight] }, // back
            { pos: [-roomWidth / 2, roomHeight / 2, 0], rot: [0, Math.PI / 2, 0], size: [roomLength, roomHeight] }, // left
            { pos: [roomWidth / 2, roomHeight / 2, 0], rot: [0, -Math.PI / 2, 0], size: [roomLength, roomHeight] }, // right
        ];

        wallGeometries.forEach((config) => {
            const wallGeometry = new THREE.PlaneGeometry(config.size[0], config.size[1]);
            const wall = new THREE.Mesh(wallGeometry, materials.walls);
            wall.position.set(...config.pos);
            wall.rotation.set(...config.rot);
            wall.receiveShadow = true;
            this.scene.add(wall);
            this.layers.interior.push(wall);
        });

        // Reduce fog for interior
        this.scene.fog.far = 50;
    }

    /**
     * Clear objects in a specific layer
     */
    clearLayerObjects(layer) {
        this.layers[layer].forEach((obj) => {
            this.scene.remove(obj);
        });
        this.layers[layer] = [];
    }

    /**
     * Switch scene mode
     */
    switchMode(newMode) {
        if (!['exploration', 'interior', 'build'].includes(newMode)) {
            console.error(`Unknown mode: ${newMode}`);
            return;
        }

        this.mode = newMode;
        console.log(`[Scene3D] Switched to mode: ${newMode}`);
    }

    /**
     * Add object to current mode layer
     */
    addObjectToLayer(object, layer = null) {
        const targetLayer = layer || this.mode;
        this.scene.add(object);

        if (this.layers[targetLayer]) {
            this.layers[targetLayer].push(object);
        }

        return object;
    }

    /**
     * Remove object from scene
     */
    removeObject(object) {
        this.scene.remove(object);

        // Remove from all layers
        Object.keys(this.layers).forEach((layer) => {
            this.layers[layer] = this.layers[layer].filter((obj) => obj !== object);
        });
    }

    /**
     * Get camera for mode
     */
    getCamera() {
        return this.camera;
    }

    /**
     * Get renderer
     */
    getRenderer() {
        return this.renderer;
    }

    /**
     * Get scene
     */
    getScene() {
        return this.scene;
    }

    /**
     * Handle window resize
     */
    setupEventListeners() {
        window.addEventListener('resize', () => {
            const width = window.innerWidth;
            const height = window.innerHeight - 60;

            this.camera.aspect = width / height;
            this.camera.updateProjectionMatrix();

            this.renderer.setSize(width, height);
        });
    }

    /**
     * Render frame
     */
    render() {
        this.renderer.render(this.scene, this.camera);
    }

    /**
     * Dispose resources
     */
    dispose() {
        this.renderer.dispose();
        Object.values(this.layers).forEach((layer) => {
            layer.forEach((obj) => {
                if (obj.geometry) obj.geometry.dispose();
                if (obj.material) obj.material.dispose();
            });
        });
    }
}

export default Scene3DExtended;
