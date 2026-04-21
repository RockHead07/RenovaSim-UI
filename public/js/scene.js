import * as THREE from 'https://cdn.jsdelivr.net/npm/three@r128/build/three.module.js';

export class Scene3D {
    constructor(canvas, width = 4, length = 5, height = 3) {
        this.canvas = canvas;
        this.width = width;
        this.length = length;
        this.height = height;

        // Scene
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x1e293b);
        this.scene.fog = new THREE.Fog(0x1e293b, 50, 100);

        // Camera
        this.camera = new THREE.PerspectiveCamera(
            75,
            window.innerWidth / (window.innerHeight - 60),
            0.1,
            1000
        );
        this.camera.position.set(width / 2, height / 2, length / 2 + 2);
        this.camera.lookAt(width / 2, height / 1.5, length / 2);

        // Renderer
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

        // Lights
        this.setupLights();

        // Room geometry
        this.setupRoom();

        // Handle window resize
        window.addEventListener('resize', () => this.onWindowResize());
    }

    setupLights() {
        // Ambient light
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);

        // Directional light with shadows
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(this.width / 2, this.height, this.length / 2);
        directionalLight.castShadow = true;
        directionalLight.shadow.mapSize.width = 2048;
        directionalLight.shadow.mapSize.height = 2048;
        directionalLight.shadow.camera.left = -this.width;
        directionalLight.shadow.camera.right = this.width;
        directionalLight.shadow.camera.top = this.height;
        directionalLight.shadow.camera.bottom = 0;
        directionalLight.shadow.camera.near = 0.5;
        directionalLight.shadow.camera.far = this.height * 2;
        this.scene.add(directionalLight);

        // Point light for accent
        const pointLight = new THREE.PointLight(0xffffff, 0.3);
        pointLight.position.set(this.width / 4, this.height * 0.8, this.length / 4);
        this.scene.add(pointLight);
    }

    setupRoom() {
        const material = {
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
            ceiling: new THREE.MeshStandardMaterial({
                color: 0xf1f5f9,
                metalness: 0.0,
                roughness: 0.9,
            }),
        };

        // Floor
        const floorGeometry = new THREE.PlaneGeometry(this.width, this.length);
        const floor = new THREE.Mesh(floorGeometry, material.floor);
        floor.rotation.x = -Math.PI / 2;
        floor.receiveShadow = true;
        this.scene.add(floor);

        // Ceiling
        const ceilingGeometry = new THREE.PlaneGeometry(this.width, this.length);
        const ceiling = new THREE.Mesh(ceilingGeometry, material.ceiling);
        ceiling.position.y = this.height;
        ceiling.rotation.x = Math.PI / 2;
        this.scene.add(ceiling);

        // Walls
        const walls = [
            // Back wall (Z+)
            { w: this.width, h: this.height, x: 0, y: this.height / 2, z: this.length / 2, rx: 0, ry: 0 },
            // Front wall (Z-)
            { w: this.width, h: this.height, x: 0, y: this.height / 2, z: -this.length / 2, rx: 0, ry: 0 },
            // Right wall (X+)
            { w: this.length, h: this.height, x: this.width / 2, y: this.height / 2, z: 0, rx: 0, ry: Math.PI / 2 },
            // Left wall (X-)
            { w: this.length, h: this.height, x: -this.width / 2, y: this.height / 2, z: 0, rx: 0, ry: Math.PI / 2 },
        ];

        walls.forEach(w => {
            const wallGeometry = new THREE.PlaneGeometry(w.w, w.h);
            const wall = new THREE.Mesh(wallGeometry, material.walls);
            wall.position.set(w.x, w.y, w.z);
            wall.rotation.x = w.rx;
            wall.rotation.y = w.ry;
            wall.receiveShadow = true;
            this.scene.add(wall);
        });

        // Grid helper (optional)
        const gridHelper = new THREE.GridHelper(Math.max(this.width, this.length) * 1.5, 20, 0x444444, 0x888888);
        gridHelper.position.y = 0.01;
        this.scene.add(gridHelper);
    }

    addObject(geometry, material, position = [0, 0, 0], rotation = [0, 0, 0], scale = [1, 1, 1]) {
        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.set(...position);
        mesh.rotation.set(...rotation);
        mesh.scale.set(...scale);
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        this.scene.add(mesh);
        return mesh;
    }

    onWindowResize() {
        const width = window.innerWidth;
        const height = window.innerHeight - 60;

        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();

        this.renderer.setSize(width, height);
    }

    render() {
        this.renderer.render(this.scene, this.camera);
    }

    dispose() {
        this.renderer.dispose();
    }
}

export default Scene3D;
