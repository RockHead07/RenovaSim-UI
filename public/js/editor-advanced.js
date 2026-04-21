/**
 * Advanced 3D Room Editor v2
 * - Realistic furniture geometries
 * - Character controller for explore mode
 * - The Sims-style UI with furniture bar
 * - Proper mouse controls (non-inverted)
 * - Transform gizmos
 */

class AdvancedRoom3DEditor {
    constructor() {
        if (typeof THREE === 'undefined') {
            throw new Error('THREE.js is not loaded');
        }

        this.canvas = document.getElementById('canvas');
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2();
        
        // Character
        this.character = null;
        this.characterSize = 0.3;
        this.characterHeight = 1.7;
        
        // Mode and selection
        this.mode = 'explore'; // 'explore' or 'edit'
        this.selectedObject = null;
        this.currentFurnitureType = null;
        this.objects = [];
        
        // Input
        this.keys = {};
        this.mouseMovement = { x: 0, y: 0 };
        this.isPointerLocked = false;
        
        // Camera
        this.cameraSpeed = 5;
        this.cameraRotationSpeed = 0.002;
        this.cameraPitch = 0;
        this.cameraYaw = 0;
        
        // Transform tools
        this.transformMode = null; // 'move', 'rotate', 'scale'
        this.transformGizmo = null;
    }

    init() {
        console.log('🚀 Initializing Advanced 3D Editor...');
        this.setupScene();
        this.setupCamera();
        this.setupRenderer();
        this.setupLights();
        this.setupRoom();
        this.setupCharacter();
        this.setupControls();
        this.setupEventListeners();
        this.setupUI();
        this.loadRoomData();
        this.startRenderLoop();
        console.log('✅ Editor initialized successfully!');
    }

    setupScene() {
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x87ceeb); // Sky blue
        this.scene.fog = new THREE.Fog(0x87ceeb, 50, 100);
    }

    setupCamera() {
        const width = this.canvas.clientWidth;
        const height = this.canvas.clientHeight;
        this.camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
        
        // Start at center height
        this.camera.position.set(0, this.characterHeight, 0);
        this.camera.lookAt(0, this.characterHeight - 0.3, -5);
    }

    setupRenderer() {
        this.renderer = new THREE.WebGLRenderer({ canvas: this.canvas, antialias: true });
        this.renderer.setSize(this.canvas.clientWidth, this.canvas.clientHeight);
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFShadowShadowMap;
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.exposure = 1;
        
        window.addEventListener('resize', () => this.onWindowResize());
    }

    setupLights() {
        // Ambient light
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);
        
        // Directional light (sun)
        const sunLight = new THREE.DirectionalLight(0xffffff, 0.8);
        sunLight.position.set(10, 15, 10);
        sunLight.castShadow = true;
        sunLight.shadow.mapSize.width = 2048;
        sunLight.shadow.mapSize.height = 2048;
        sunLight.shadow.camera.left = -50;
        sunLight.shadow.camera.right = 50;
        sunLight.shadow.camera.top = 50;
        sunLight.shadow.camera.bottom = -50;
        sunLight.shadow.camera.near = 0.5;
        sunLight.shadow.camera.far = 500;
        this.scene.add(sunLight);
        
        // Point light
        const pointLight = new THREE.PointLight(0xffffff, 0.3);
        pointLight.position.set(0, 5, 0);
        this.scene.add(pointLight);
    }

    setupRoom() {
        const roomData = window.roomData.room;
        const width = roomData.width;
        const length = roomData.length;
        const height = roomData.height;

        // Floor
        const floorGeom = new THREE.PlaneGeometry(width, length);
        const floorMat = new THREE.MeshStandardMaterial({ 
            color: 0xcccccc,
            roughness: 0.8,
            metalness: 0
        });
        const floor = new THREE.Mesh(floorGeom, floorMat);
        floor.rotation.x = -Math.PI / 2;
        floor.receiveShadow = true;
        floor.name = 'floor';
        this.scene.add(floor);

        // Ceiling
        const ceilingGeom = new THREE.PlaneGeometry(width, length);
        const ceilingMat = new THREE.MeshStandardMaterial({ color: 0xffffff });
        const ceiling = new THREE.Mesh(ceilingGeom, ceilingMat);
        ceiling.position.y = height;
        ceiling.rotation.x = Math.PI / 2;
        ceiling.receiveShadow = true;
        this.scene.add(ceiling);

        // Walls
        const wallMat = new THREE.MeshStandardMaterial({ color: 0xeeeeee });
        
        // Front wall
        const frontWall = new THREE.Mesh(
            new THREE.PlaneGeometry(width, height),
            wallMat
        );
        frontWall.position.z = -length / 2;
        frontWall.position.y = height / 2;
        frontWall.receiveShadow = true;
        this.scene.add(frontWall);

        // Back wall
        const backWall = new THREE.Mesh(
            new THREE.PlaneGeometry(width, height),
            wallMat
        );
        backWall.position.z = length / 2;
        backWall.position.y = height / 2;
        backWall.rotation.y = Math.PI;
        backWall.receiveShadow = true;
        this.scene.add(backWall);

        // Left wall
        const leftWall = new THREE.Mesh(
            new THREE.PlaneGeometry(length, height),
            wallMat
        );
        leftWall.position.x = -width / 2;
        leftWall.position.y = height / 2;
        leftWall.rotation.y = Math.PI / 2;
        leftWall.receiveShadow = true;
        this.scene.add(leftWall);

        // Right wall
        const rightWall = new THREE.Mesh(
            new THREE.PlaneGeometry(length, height),
            wallMat
        );
        rightWall.position.x = width / 2;
        rightWall.position.y = height / 2;
        rightWall.rotation.y = -Math.PI / 2;
        rightWall.receiveShadow = true;
        this.scene.add(rightWall);

        // Grid helper
        const gridHelper = new THREE.GridHelper(Math.max(width, length), 20, 0xcccccc, 0xeeeeee);
        gridHelper.position.y = 0.01;
        this.scene.add(gridHelper);
    }

    setupCharacter() {
        // Simple character (capsule: head + body)
        const characterGroup = new THREE.Group();
        
        // Body
        const bodyGeom = new THREE.CylinderGeometry(0.2, 0.25, 0.8, 8);
        const bodyMat = new THREE.MeshStandardMaterial({ color: 0x4488ff, roughness: 0.6 });
        const body = new THREE.Mesh(bodyGeom, bodyMat);
        body.position.y = 0.4;
        body.castShadow = true;
        characterGroup.add(body);
        
        // Head
        const headGeom = new THREE.SphereGeometry(0.15, 16, 16);
        const headMat = new THREE.MeshStandardMaterial({ color: 0xffdbac });
        const head = new THREE.Mesh(headGeom, headMat);
        head.position.y = 1.2;
        head.castShadow = true;
        characterGroup.add(head);
        
        // Eyes for direction indicator
        const eyeGeom = new THREE.SphereGeometry(0.04, 8, 8);
        const eyeMat = new THREE.MeshStandardMaterial({ color: 0x000000 });
        const leftEye = new THREE.Mesh(eyeGeom, eyeMat);
        leftEye.position.set(-0.08, 1.25, 0.13);
        const rightEye = new THREE.Mesh(eyeGeom, eyeMat);
        rightEye.position.set(0.08, 1.25, 0.13);
        characterGroup.add(leftEye);
        characterGroup.add(rightEye);
        
        characterGroup.position.set(0, 0, 0);
        this.character = characterGroup;
        this.scene.add(this.character);
    }

    setupControls() {
        // We'll use keyboard for now, mouse will be for camera
    }

    setupEventListeners() {
        // Keyboard
        document.addEventListener('keydown', (e) => this.onKeyDown(e));
        document.addEventListener('keyup', (e) => this.onKeyUp(e));
        
        // Mouse - Pointer lock
        document.addEventListener('click', () => this.requestPointerLock());
        document.addEventListener('pointerlockchange', () => this.onPointerLockChange());
        document.addEventListener('mousemove', (e) => this.onMouseMove(e));
        
        // Canvas click for object selection
        this.canvas.addEventListener('click', (e) => this.onCanvasClick(e));
    }

    requestPointerLock() {
        if (this.mode === 'explore') {
            this.canvas.requestPointerLock();
        }
    }

    onPointerLockChange() {
        this.isPointerLocked = document.pointerLockElement === this.canvas;
    }

    onMouseMove(e) {
        if (!this.isPointerLocked) return;
        
        // Natural mouse movement (non-inverted)
        this.mouseMovement.x += e.movementX * this.cameraRotationSpeed;
        this.mouseMovement.y += e.movementY * this.cameraRotationSpeed;
        
        // Clamp vertical rotation to prevent flipping
        this.mouseMovement.y = Math.max(-Math.PI / 2, Math.min(Math.PI / 2, this.mouseMovement.y));
    }

    onKeyDown(e) {
        this.keys[e.key.toLowerCase()] = true;
        
        if (e.key.toLowerCase() === 'e') {
            this.toggleMode();
        }
        
        if (e.key.toLowerCase() === 'delete') {
            this.deleteSelected();
        }
        
        if (this.mode === 'edit') {
            if (e.key.toLowerCase() === 'g') this.setTransformMode('move');
            if (e.key.toLowerCase() === 'r') this.setTransformMode('rotate');
            if (e.key.toLowerCase() === 's') this.setTransformMode('scale');
        }
    }

    onKeyUp(e) {
        this.keys[e.key.toLowerCase()] = false;
    }

    onCanvasClick(e) {
        if (this.mode === 'explore') return;
        
        // Raycasting for edit mode
        const rect = this.canvas.getBoundingClientRect();
        this.mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
        this.mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
        
        this.raycaster.setFromCamera(this.mouse, this.camera);
        
        // Check intersection with furniture
        const intersects = this.raycaster.intersectObjects(this.objects, true);
        
        if (intersects.length > 0) {
            // Select object
            const selected = intersects[0].object;
            while (selected.parent && !this.objects.includes(selected)) {
                selected = selected.parent;
            }
            this.selectObject(selected);
        } else {
            // Place furniture if one is selected
            if (this.currentFurnitureType) {
                const intersectsFloor = this.raycaster.intersectObjects(this.scene.children);
                for (let intersection of intersectsFloor) {
                    if (intersection.object.name === 'floor') {
                        this.addFurniture(this.currentFurnitureType, intersection.point);
                        break;
                    }
                }
            }
        }
    }

    addFurniture(type, position) {
        const furniture = this.createFurniture(type);
        furniture.position.copy(position);
        furniture.position.y = this.getFurnitureHeight(type) / 2;
        furniture.userData = {
            type: type,
            width: this.getFurnitureDimensions(type).width,
            height: this.getFurnitureDimensions(type).height,
            depth: this.getFurnitureDimensions(type).depth
        };
        
        this.scene.add(furniture);
        this.objects.push(furniture);
        
        this.updateObjectCount();
    }

    createFurniture(type) {
        const group = new THREE.Group();
        
        switch(type) {
            case 'bed':
                this.createBed(group);
                break;
            case 'chair':
                this.createChair(group);
                break;
            case 'table':
                this.createTable(group);
                break;
            case 'sofa':
                this.createSofa(group);
                break;
            case 'desk':
                this.createDesk(group);
                break;
            case 'shelf':
                this.createShelf(group);
                break;
            case 'lamp':
                this.createLamp(group);
                break;
            case 'plant':
                this.createPlant(group);
                break;
        }
        
        group.castShadow = true;
        group.receiveShadow = true;
        group.userData.furnitureType = type;
        return group;
    }

    createBed(group) {
        const woodMat = new THREE.MeshStandardMaterial({ color: 0x8b4513, roughness: 0.7, metalness: 0 });
        const fabricMat = new THREE.MeshStandardMaterial({ color: 0xff6b9d, roughness: 0.8, metalness: 0 });
        
        // Mattress
        const mattress = new THREE.Mesh(new THREE.BoxGeometry(1.4, 0.3, 0.6), fabricMat);
        mattress.position.y = 0.15;
        mattress.castShadow = true;
        group.add(mattress);
        
        // Frame
        const frame = new THREE.Mesh(new THREE.BoxGeometry(1.5, 0.1, 0.7), woodMat);
        frame.position.y = 0;
        frame.castShadow = true;
        group.add(frame);
        
        // Pillow
        const pillow = new THREE.Mesh(new THREE.BoxGeometry(0.4, 0.15, 0.4), new THREE.MeshStandardMaterial({ color: 0xffffff }));
        pillow.position.set(0.3, 0.35, 0);
        pillow.castShadow = true;
        group.add(pillow);
    }

    createChair(group) {
        const woodMat = new THREE.MeshStandardMaterial({ color: 0x8b6914, roughness: 0.6 });
        const fabricMat = new THREE.MeshStandardMaterial({ color: 0xccaa55, roughness: 0.7 });
        
        // Seat
        const seat = new THREE.Mesh(new THREE.BoxGeometry(0.5, 0.08, 0.5), fabricMat);
        seat.position.y = 0.4;
        seat.castShadow = true;
        group.add(seat);
        
        // Back
        const back = new THREE.Mesh(new THREE.BoxGeometry(0.5, 0.4, 0.08), fabricMat);
        back.position.set(0, 0.6, -0.25);
        back.castShadow = true;
        group.add(back);
        
        // Legs (4)
        const legGeom = new THREE.BoxGeometry(0.06, 0.4, 0.06);
        for (let i = -1; i <= 1; i += 2) {
            for (let j = -1; j <= 1; j += 2) {
                const leg = new THREE.Mesh(legGeom, woodMat);
                leg.position.set(i * 0.15, 0.2, j * 0.15);
                leg.castShadow = true;
                group.add(leg);
            }
        }
    }

    createTable(group) {
        const woodMat = new THREE.MeshStandardMaterial({ color: 0xa0522d, roughness: 0.6 });
        
        // Top
        const top = new THREE.Mesh(new THREE.BoxGeometry(1, 0.08, 0.8), woodMat);
        top.position.y = 0.75;
        top.castShadow = true;
        group.add(top);
        
        // Legs (4)
        const legGeom = new THREE.BoxGeometry(0.08, 0.75, 0.08);
        for (let i of [-0.4, 0.4]) {
            for (let j of [-0.3, 0.3]) {
                const leg = new THREE.Mesh(legGeom, woodMat);
                leg.position.set(i, 0.375, j);
                leg.castShadow = true;
                group.add(leg);
            }
        }
    }

    createSofa(group) {
        const fabricMat = new THREE.MeshStandardMaterial({ color: 0x666666, roughness: 0.8 });
        const woodMat = new THREE.MeshStandardMaterial({ color: 0x8b4513 });
        
        // Seat
        const seat = new THREE.Mesh(new THREE.BoxGeometry(2, 0.1, 0.8), fabricMat);
        seat.position.y = 0.4;
        seat.castShadow = true;
        group.add(seat);
        
        // Back
        const back = new THREE.Mesh(new THREE.BoxGeometry(2, 0.6, 0.1), fabricMat);
        back.position.set(0, 0.65, -0.45);
        back.castShadow = true;
        group.add(back);
        
        // Armrests (2)
        for (let x of [-0.95, 0.95]) {
            const arm = new THREE.Mesh(new THREE.BoxGeometry(0.1, 0.5, 0.8), fabricMat);
            arm.position.set(x, 0.55, 0);
            arm.castShadow = true;
            group.add(arm);
        }
    }

    createDesk(group) {
        const woodMat = new THREE.MeshStandardMaterial({ color: 0xa0522d });
        
        // Top
        const top = new THREE.Mesh(new THREE.BoxGeometry(1.2, 0.08, 0.75), woodMat);
        top.position.y = 0.75;
        top.castShadow = true;
        group.add(top);
        
        // Legs (4)
        const legGeom = new THREE.BoxGeometry(0.08, 0.75, 0.08);
        for (let i of [-0.5, 0.5]) {
            for (let j of [-0.3, 0.3]) {
                const leg = new THREE.Mesh(legGeom, woodMat);
                leg.position.set(i, 0.375, j);
                leg.castShadow = true;
                group.add(leg);
            }
        }
    }

    createShelf(group) {
        const woodMat = new THREE.MeshStandardMaterial({ color: 0x8b6914 });
        
        // Shelves (3 levels)
        for (let i = 0; i < 3; i++) {
            const shelf = new THREE.Mesh(new THREE.BoxGeometry(0.8, 0.04, 0.4), woodMat);
            shelf.position.y = 0.2 + i * 0.4;
            shelf.castShadow = true;
            group.add(shelf);
        }
        
        // Frame sides
        for (let x of [-0.4, 0.4]) {
            const side = new THREE.Mesh(new THREE.BoxGeometry(0.04, 1.2, 0.04), woodMat);
            side.position.set(x, 0.6, 0);
            side.castShadow = true;
            group.add(side);
        }
    }

    createLamp(group) {
        const metalMat = new THREE.MeshStandardMaterial({ color: 0x222222, metalness: 0.8, roughness: 0.2 });
        const lightMat = new THREE.MeshStandardMaterial({ color: 0xffffdd, emissive: 0xffff00, emissiveIntensity: 0.5 });
        
        // Base
        const base = new THREE.Mesh(new THREE.CylinderGeometry(0.15, 0.15, 0.05), metalMat);
        base.position.y = 0.025;
        base.castShadow = true;
        group.add(base);
        
        // Pole
        const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, 0.4), metalMat);
        pole.position.y = 0.25;
        pole.castShadow = true;
        group.add(pole);
        
        // Bulb
        const bulb = new THREE.Mesh(new THREE.SphereGeometry(0.06, 16, 16), lightMat);
        bulb.position.y = 0.5;
        bulb.castShadow = true;
        group.add(bulb);
        
        // Light source
        const light = new THREE.PointLight(0xffff99, 0.5, 10);
        light.position.y = 0.5;
        group.add(light);
    }

    createPlant(group) {
        const potMat = new THREE.MeshStandardMaterial({ color: 0x8b4513 });
        const leafMat = new THREE.MeshStandardMaterial({ color: 0x228b22, roughness: 0.6 });
        
        // Pot
        const potGeom = new THREE.ConeGeometry(0.2, 0.3, 8);
        const pot = new THREE.Mesh(potGeom, potMat);
        pot.position.y = 0.15;
        pot.castShadow = true;
        group.add(pot);
        
        // Soil
        const soil = new THREE.Mesh(new THREE.CylinderGeometry(0.18, 0.18, 0.02), new THREE.MeshStandardMaterial({ color: 0x654321 }));
        soil.position.y = 0.3;
        soil.castShadow = true;
        group.add(soil);
        
        // Leaves (simple spheres)
        for (let i = 0; i < 4; i++) {
            const angle = (i / 4) * Math.PI * 2;
            const leaf = new THREE.Mesh(new THREE.SphereGeometry(0.1, 8, 8), leafMat);
            leaf.position.set(Math.cos(angle) * 0.15, 0.5 + i * 0.05, Math.sin(angle) * 0.15);
            leaf.scale.set(1, 1.5, 1);
            leaf.castShadow = true;
            group.add(leaf);
        }
    }

    getFurnitureHeight(type) {
        const heights = {
            bed: 0.3,
            chair: 0.8,
            table: 0.8,
            sofa: 0.9,
            desk: 0.75,
            shelf: 1.2,
            lamp: 0.5,
            plant: 0.4
        };
        return heights[type] || 0.5;
    }

    getFurnitureDimensions(type) {
        const dims = {
            bed: { width: 1.4, height: 0.3, depth: 0.6 },
            chair: { width: 0.6, height: 0.8, depth: 0.6 },
            table: { width: 1.0, height: 0.75, depth: 0.8 },
            sofa: { width: 2.0, height: 0.9, depth: 0.8 },
            desk: { width: 1.2, height: 0.75, depth: 0.75 },
            shelf: { width: 0.8, height: 1.2, depth: 0.4 },
            lamp: { width: 0.2, height: 0.5, depth: 0.2 },
            plant: { width: 0.4, height: 0.4, depth: 0.4 }
        };
        return dims[type] || { width: 0.5, height: 0.5, depth: 0.5 };
    }

    selectObject(obj) {
        // Deselect previous
        if (this.selectedObject) {
            this.selectedObject.traverse(child => {
                if (child.material) {
                    child.material.emissive.setHex(0x000000);
                }
            });
        }
        
        // Select new
        this.selectedObject = obj;
        obj.traverse(child => {
            if (child.material) {
                child.material.emissive.setHex(0x444444);
            }
        });
        
        this.updateUIStatus();
    }

    deleteSelected() {
        if (!this.selectedObject) return;
        
        this.scene.remove(this.selectedObject);
        this.objects = this.objects.filter(o => o !== this.selectedObject);
        this.selectedObject = null;
        
        this.updateObjectCount();
    }

    setTransformMode(mode) {
        this.transformMode = mode;
        this.updateUIStatus();
    }

    toggleMode() {
        this.mode = this.mode === 'explore' ? 'edit' : 'explore';
        this.selectedObject = null;
        this.transformMode = null;
        
        if (this.mode === 'explore') {
            document.exitPointerLock?.();
        }
        
        this.updateUIStatus();
    }

    setupUI() {
        const uiPanel = document.getElementById('editor-ui');
        if (!uiPanel) {
            const panel = document.createElement('div');
            panel.id = 'editor-ui';
            document.querySelector('main').appendChild(panel);
        }
        this.updateUI();
    }

    updateUI() {
        const uiPanel = document.getElementById('editor-ui');
        uiPanel.innerHTML = `
            <style>
                #editor-ui {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    pointer-events: none;
                    z-index: 10;
                    font-family: system-ui;
                }
                
                .top-panel {
                    position: absolute;
                    top: 20px;
                    left: 20px;
                    right: 20px;
                    background: rgba(0, 0, 0, 0.7);
                    border-radius: 8px;
                    padding: 15px;
                    color: white;
                    pointer-events: all;
                }
                
                .top-panel h3 {
                    margin: 0 0 10px 0;
                    font-size: 16px;
                }
                
                .mode-indicator {
                    display: inline-block;
                    padding: 5px 12px;
                    background: ${this.mode === 'explore' ? '#4488ff' : '#ff6b6b'};
                    border-radius: 4px;
                    margin-right: 10px;
                }
                
                .bottom-furniture-bar {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    background: linear-gradient(180deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.9) 100%);
                    border-top: 2px solid #444;
                    padding: 10px 20px;
                    display: flex;
                    gap: 15px;
                    align-items: center;
                    overflow-x: auto;
                    pointer-events: all;
                    max-height: 120px;
                }
                
                .furniture-item {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 8px;
                    padding: 10px;
                    background: rgba(255, 255, 255, 0.1);
                    border: 2px solid transparent;
                    border-radius: 6px;
                    cursor: pointer;
                    transition: all 0.2s;
                    color: white;
                    font-size: 12px;
                    white-space: nowrap;
                    pointer-events: all;
                }
                
                .furniture-item:hover {
                    background: rgba(255, 255, 255, 0.2);
                    transform: scale(1.05);
                }
                
                .furniture-item.active {
                    border-color: #4488ff;
                    background: rgba(68, 136, 255, 0.3);
                }
                
                .furniture-emoji {
                    font-size: 24px;
                }
                
                .right-panel {
                    position: absolute;
                    top: 20px;
                    right: 20px;
                    background: rgba(0, 0, 0, 0.7);
                    border-radius: 8px;
                    padding: 15px;
                    color: white;
                    pointer-events: all;
                    min-width: 200px;
                }
                
                .status-item {
                    margin: 8px 0;
                    font-size: 12px;
                    display: flex;
                    justify-content: space-between;
                }
                
                button {
                    background: #4488ff;
                    color: white;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 4px;
                    cursor: pointer;
                    margin: 5px 5px 5px 0;
                    font-size: 12px;
                    pointer-events: all;
                }
                
                button:hover {
                    background: #3366dd;
                }
            </style>
            
            <div class="top-panel">
                <h3>🏠 3D Interior Editor</h3>
                <div>
                    <span class="mode-indicator">${this.mode.toUpperCase()} MODE</span>
                    <button onclick="window.editor.toggleMode()" style="pointer-events: all;">Switch [E]</button>
                    <button onclick="window.editor.saveRoom()" style="pointer-events: all;">💾 Save</button>
                    <button onclick="window.editor.clearScene()" style="pointer-events: all;">Clear</button>
                </div>
            </div>
            
            <div class="right-panel">
                <div class="status-item">
                    <span>Objects:</span>
                    <strong>${this.objects.length}</strong>
                </div>
                <div class="status-item">
                    <span>Mode:</span>
                    <strong>${this.mode === 'explore' ? 'FPS Explorer' : 'Build Mode'}</strong>
                </div>
                <div class="status-item">
                    <span>FPS:</span>
                    <strong id="fps-counter">60</strong>
                </div>
                ${this.selectedObject ? `
                    <div class="status-item">
                        <span>Selected:</span>
                        <strong>${this.selectedObject.userData.furnitureType}</strong>
                    </div>
                ` : ''}
            </div>
            
            <div class="bottom-furniture-bar">
                <span style="color: white; font-size: 12px; margin-right: auto;">Select Furniture:</span>
                ${this.createFurnitureBarItems()}
            </div>
        `;

        // Re-attach event listeners
        document.querySelectorAll('.furniture-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const type = e.currentTarget.dataset.type;
                document.querySelectorAll('.furniture-item').forEach(i => i.classList.remove('active'));
                e.currentTarget.classList.add('active');
                this.currentFurnitureType = type;
            });
        });
    }

    createFurnitureBarItems() {
        const furniture = [
            { type: 'bed', emoji: '🛏️', label: 'Bed' },
            { type: 'chair', emoji: '🪑', label: 'Chair' },
            { type: 'table', emoji: '📦', label: 'Table' },
            { type: 'sofa', emoji: '🛋️', label: 'Sofa' },
            { type: 'desk', emoji: '🖥️', label: 'Desk' },
            { type: 'shelf', emoji: '📚', label: 'Shelf' },
            { type: 'lamp', emoji: '🔦', label: 'Lamp' },
            { type: 'plant', emoji: '🪴', label: 'Plant' }
        ];

        return furniture.map(f => `
            <div class="furniture-item" data-type="${f.type}" style="pointer-events: all;">
                <span class="furniture-emoji">${f.emoji}</span>
                <span>${f.label}</span>
            </div>
        `).join('');
    }

    updateUIStatus() {
        this.updateUI();
    }

    updateObjectCount() {
        const count = document.querySelector('[id="object-count"]');
        if (count) count.textContent = this.objects.length;
    }

    loadRoomData() {
        // Load existing room objects from window.roomData
        const objects = window.roomData.objects || [];
        for (let objData of objects) {
            this.addFurniture(objData.type, new THREE.Vector3(...objData.position));
        }
    }

    clearScene() {
        this.objects.forEach(obj => this.scene.remove(obj));
        this.objects = [];
        this.selectedObject = null;
        this.updateObjectCount();
    }

    saveRoom() {
        const roomData = this.objects.map(obj => ({
            type: obj.userData.furnitureType,
            position: [obj.position.x, obj.position.y, obj.position.z],
            rotation: [obj.rotation.x, obj.rotation.y, obj.rotation.z],
            scale: [obj.scale.x, obj.scale.y, obj.scale.z]
        }));

        fetch(window.saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({ objects: roomData })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('✅ Room saved successfully!');
            }
        })
        .catch(err => console.error('Save error:', err));
    }

    updateCameraPosition() {
        if (this.mode !== 'explore') return;

        const forward = new THREE.Vector3(0, 0, -1).applyAxisAngle(new THREE.Vector3(0, 1, 0), this.mouseMovement.x);
        const right = new THREE.Vector3(1, 0, 0).applyAxisAngle(new THREE.Vector3(0, 1, 0), this.mouseMovement.x);

        // WASD movement
        if (this.keys['w']) this.camera.position.addScaledVector(forward, this.cameraSpeed * 0.016);
        if (this.keys['s']) this.camera.position.addScaledVector(forward, -this.cameraSpeed * 0.016);
        if (this.keys['a']) this.camera.position.addScaledVector(right, -this.cameraSpeed * 0.016);
        if (this.keys['d']) this.camera.position.addScaledVector(right, this.cameraSpeed * 0.016);
        if (this.keys[' ']) this.camera.position.y += this.cameraSpeed * 0.008;
        if (this.keys['shift']) this.camera.position.y -= this.cameraSpeed * 0.008;

        // Character position follows camera
        if (this.character) {
            this.character.position.copy(this.camera.position);
            this.character.position.y = 0;
        }

        // Look direction
        const target = this.camera.position.clone();
        target.addScaledVector(forward, 5);
        target.y += this.characterHeight * 0.7 - this.mouseMovement.y;
        this.camera.lookAt(target);
    }

    startRenderLoop() {
        const clock = new THREE.Clock();
        
        const animate = () => {
            requestAnimationFrame(animate);
            
            const deltaTime = clock.getDelta();
            
            // Update camera
            this.updateCameraPosition();
            
            // Render
            this.renderer.render(this.scene, this.camera);
            
            // Update FPS
            const fps = Math.round(1 / deltaTime);
            const fpsCounter = document.getElementById('fps-counter');
            if (fpsCounter) fpsCounter.textContent = fps;
        };
        
        animate();
    }

    onWindowResize() {
        const width = this.canvas.clientWidth;
        const height = this.canvas.clientHeight;
        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
    }
}

// Make editor globally accessible
console.log('✅ AdvancedRoom3DEditor class loaded');
