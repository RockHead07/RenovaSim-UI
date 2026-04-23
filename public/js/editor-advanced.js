/**
 * Advanced 3D Room Editor v3 - with Hoodie Character & Dual Camera Modes
 * - First-Person POV (Explore Mode) - like House Flipper
 * - Third-Person Camera (Build Mode) - like The Sims/Unity
 * - 3D Character model (Hoodie Character.glb)
 * - Furniture manipulation, wall painting, and more
 */

class AdvancedRoom3DEditor {
    constructor() {
        if (typeof THREE === 'undefined') {
            throw new Error('THREE.js is not loaded. Please ensure THREE.js is loaded before initializing the editor.');
        }

        this.canvas = document.getElementById('canvas');
        if (!this.canvas) {
            throw new Error('Canvas element with id="canvas" not found in the DOM');
        }

        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2();
        
        // Character
        this.character = null;
        this.characterModel = null;
        this.characterSize = 0.3;
        this.characterHeight = 1.7;
        
        // Mode and selection
        this.mode = 'explore'; // 'explore' or 'build'
        this.selectedObject = null;
        this.selectedWall = null;
        this.currentFurnitureType = null;
        this.currentWallColor = 0xeeeeee;
        this.objects = [];
        
        // Input
        this.keys = {};
        this.mouseMovement = { x: 0, y: 0 };
        this.isPointerLocked = false;
        
        // Camera
        this.cameraSpeed = 10;
        this.cameraRotationSpeed = 0.002;
        this.cameraPitch = 0;
        this.cameraYaw = 0;
        this.cameraDistance = 5; // For build mode
        
        // Room references
        this.walls = {};
        this.floor = null;
        this.ceiling = null;
        
        // GLB Loader
        this.gltfLoader = null;
        
        console.log('✅ AdvancedRoom3DEditor constructor initialized');
    }

    init() {
        console.log('🚀 Initializing Advanced 3D Editor v3...');
        this.setupScene();
        this.setupRenderer();
        this.setupRoom();
        this.setupCharacter(); // Initialize character BEFORE camera for position reference
        this.setupCamera(); // Camera setup needs character position
        this.setupLights();
        this.setupLoaders();
        this.loadCharacterModel();
        this.setupControls();
        this.setupEventListeners();
        this.setupUI();
        this.loadRoomData();
        this.startRenderLoop();
        console.log('✅ Editor initialized successfully!');
    }

    setupScene() {
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x87ceeb);
        this.scene.fog = new THREE.Fog(0x87ceeb, 100, 200);
    }

    setupCamera() {
        const width = this.canvas.clientWidth;
        const height = this.canvas.clientHeight;
        this.camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
        this.updateCameraForMode();
    }

    updateCameraForMode() {
        if (this.mode === 'explore') {
            // First-Person POV
            this.camera.position.copy(this.character.position);
            this.camera.position.y += this.characterHeight - 0.2; // Eye level
            this.camera.lookAt(
                this.character.position.x,
                this.character.position.y + this.characterHeight - 0.2,
                this.character.position.z - 5
            );
        } else {
            // Third-Person Build Mode (like The Sims)
            const centerPos = this.character.position.clone();
            centerPos.y = 1.5;
            
            const angle = this.cameraYaw;
            const distance = this.cameraDistance;
            
            this.camera.position.set(
                centerPos.x + Math.sin(angle) * distance,
                centerPos.y + 3,
                centerPos.z + Math.cos(angle) * distance
            );
            this.camera.lookAt(centerPos);
        }
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
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
        this.scene.add(ambientLight);
        
        // Directional light (sun)
        const sunLight = new THREE.DirectionalLight(0xffffff, 0.9);
        sunLight.position.set(15, 20, 15);
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
        const pointLight = new THREE.PointLight(0xffffff, 0.4);
        pointLight.position.set(0, 5, 0);
        this.scene.add(pointLight);
    }

    setupRoom() {
        if (!window.roomData || !window.roomData.room) {
            console.error('❌ Room data not available. Check if window.roomData is set in the Blade template.');
            throw new Error('Room data (window.roomData) not initialized');
        }

        const roomData = window.roomData.room;
        const width = roomData.width || 4;
        const length = roomData.length || 5;
        const height = roomData.height || 3;

        console.log(`📐 Creating room: ${width}m × ${length}m × ${height}m`);

        // Floor
        const floorGeom = new THREE.PlaneGeometry(width, length);
        const floorMat = new THREE.MeshStandardMaterial({ 
            color: 0xcccccc,
            roughness: 0.8,
            metalness: 0
        });
        this.floor = new THREE.Mesh(floorGeom, floorMat);
        this.floor.rotation.x = -Math.PI / 2;
        this.floor.receiveShadow = true;
        this.floor.name = 'floor';
        this.scene.add(this.floor);

        // Ceiling
        const ceilingGeom = new THREE.PlaneGeometry(width, length);
        const ceilingMat = new THREE.MeshStandardMaterial({ color: 0xffffff });
        this.ceiling = new THREE.Mesh(ceilingGeom, ceilingMat);
        this.ceiling.position.y = height;
        this.ceiling.rotation.x = Math.PI / 2;
        this.ceiling.receiveShadow = true;
        this.scene.add(this.ceiling);

        // Walls
        const wallMat = new THREE.MeshStandardMaterial({ color: 0xeeeeee });
        
        // Front wall
        this.walls.front = new THREE.Mesh(new THREE.PlaneGeometry(width, height), wallMat.clone());
        this.walls.front.position.z = -length / 2;
        this.walls.front.position.y = height / 2;
        this.walls.front.receiveShadow = true;
        this.walls.front.name = 'wall-front';
        this.walls.front.userData.wallType = 'front';
        this.scene.add(this.walls.front);

        // Back wall
        this.walls.back = new THREE.Mesh(new THREE.PlaneGeometry(width, height), wallMat.clone());
        this.walls.back.position.z = length / 2;
        this.walls.back.position.y = height / 2;
        this.walls.back.rotation.y = Math.PI;
        this.walls.back.receiveShadow = true;
        this.walls.back.name = 'wall-back';
        this.walls.back.userData.wallType = 'back';
        this.scene.add(this.walls.back);

        // Left wall
        this.walls.left = new THREE.Mesh(new THREE.PlaneGeometry(length, height), wallMat.clone());
        this.walls.left.position.x = -width / 2;
        this.walls.left.position.y = height / 2;
        this.walls.left.rotation.y = Math.PI / 2;
        this.walls.left.receiveShadow = true;
        this.walls.left.name = 'wall-left';
        this.walls.left.userData.wallType = 'left';
        this.scene.add(this.walls.left);

        // Right wall
        this.walls.right = new THREE.Mesh(new THREE.PlaneGeometry(length, height), wallMat.clone());
        this.walls.right.position.x = width / 2;
        this.walls.right.position.y = height / 2;
        this.walls.right.rotation.y = -Math.PI / 2;
        this.walls.right.receiveShadow = true;
        this.walls.right.name = 'wall-right';
        this.walls.right.userData.wallType = 'right';
        this.scene.add(this.walls.right);

        // Grid helper
        const gridHelper = new THREE.GridHelper(Math.max(width, length), 20, 0xcccccc, 0xeeeeee);
        gridHelper.position.y = 0.01;
        this.scene.add(gridHelper);
    }

    setupCharacter() {
        // Character group - will hold either simple character or loaded GLB model
        this.character = new THREE.Group();
        this.character.position.set(0, 0, 3);
        
        // Simple fallback character (until GLB loads)
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
        
        this.character.add(characterGroup);
        this.scene.add(this.character);
    }

    setupLoaders() {
        if (typeof GLTFLoader !== 'undefined') {
            this.gltfLoader = new GLTFLoader();
        }
    }

    loadCharacterModel() {
        if (!this.gltfLoader) {
            console.warn('GLTFLoader not available, using fallback character');
            return;
        }

        const characterPath = '/images/Hoodie Character.glb';
        
        this.gltfLoader.load(
            characterPath,
            (gltf) => {
                console.log('✅ Character model loaded:', gltf);
                
                // Clear old fallback character
                this.character.children.forEach(child => this.character.remove(child));
                
                const model = gltf.scene;
                
                // Scale and position the model
                model.scale.set(1.5, 1.5, 1.5);
                model.position.y = -0.5;
                
                // Enable shadows
                model.traverse((node) => {
                    if (node.isMesh) {
                        node.castShadow = true;
                        node.receiveShadow = true;
                    }
                });
                
                this.character.add(model);
                this.characterModel = model;
                console.log('✅ Hoodie character added to scene');
            },
            undefined,
            (error) => {
                console.error('❌ Failed to load character model:', error);
            }
        );
    }

    setupControls() {
        // Controls handled by keyboard events
    }

    setupEventListeners() {
        // Keyboard
        document.addEventListener('keydown', (e) => this.onKeyDown(e));
        document.addEventListener('keyup', (e) => this.onKeyUp(e));
        
        // Mouse - Pointer lock for explore mode
        document.addEventListener('click', () => this.requestPointerLock());
        document.addEventListener('pointerlockchange', () => this.onPointerLockChange());
        document.addEventListener('mousemove', (e) => this.onMouseMove(e));
        
        // Canvas click for object selection
        this.canvas.addEventListener('click', (e) => this.onCanvasClick(e));
        
        // UI buttons
        document.addEventListener('click', (e) => this.handleUIClick(e));
    }

    requestPointerLock() {
        if (this.mode === 'explore' && !this.isPointerLocked) {
            this.canvas.requestPointerLock();
        }
    }

    onPointerLockChange() {
        this.isPointerLocked = document.pointerLockElement === this.canvas;
    }

    onMouseMove(e) {
        if (this.mode === 'explore' && !this.isPointerLocked) return;
        
        if (this.mode === 'explore') {
            // First-person mouse look
            this.cameraYaw += e.movementX * this.cameraRotationSpeed;
            this.cameraPitch -= e.movementY * this.cameraRotationSpeed;
            this.cameraPitch = Math.max(-Math.PI / 2.5, Math.min(Math.PI / 2.5, this.cameraPitch));
        } else if (this.mode === 'build') {
            // Third-person camera rotation
            if (e.buttons === 1) { // Left mouse button
                this.cameraYaw += e.movementX * this.cameraRotationSpeed * 0.5;
                this.cameraDistance += e.movementY * 0.05;
                this.cameraDistance = Math.max(2, Math.min(15, this.cameraDistance));
            }
        }
    }

    onKeyDown(e) {
        const key = e.key.toLowerCase();
        this.keys[key] = true;
        
        if (key === 'e') {
            this.toggleMode();
        }
        
        if (key === 'delete') {
            this.deleteSelected();
        }
        
        if (key === 'c') {
            // Activate wall painting mode
            if (this.mode === 'build') {
                this.toggleWallPaintMode();
            }
        }
        
        // Furniture shortcuts
        if (this.mode === 'build') {
            const shortcuts = {
                '1': 'bed',
                '2': 'chair',
                '3': 'table',
                '4': 'sofa',
                '5': 'desk',
                '6': 'shelf',
                '7': 'lamp',
                '8': 'plant'
            };
            if (shortcuts[key]) {
                this.setFurnitureType(shortcuts[key]);
            }
        }
    }

    onKeyUp(e) {
        this.keys[e.key.toLowerCase()] = false;
    }

    onCanvasClick(e) {
        if (this.mode === 'build') {
            const rect = this.canvas.getBoundingClientRect();
            this.mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
            this.mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
            
            this.raycaster.setFromCamera(this.mouse, this.camera);
            
            // Check wall intersection for painting
            const wallObjects = Object.values(this.walls);
            const wallIntersects = this.raycaster.intersectObjects(wallObjects);
            
            if (wallIntersects.length > 0) {
                this.selectedWall = wallIntersects[0].object;
                this.paintWall(this.currentWallColor);
                return;
            }
            
            // Check furniture intersection
            const furnitureIntersects = this.raycaster.intersectObjects(this.objects, true);
            
            if (furnitureIntersects.length > 0) {
                const obj = furnitureIntersects[0].object;
                let furniture = obj;
                while (furniture.parent && !this.objects.includes(furniture)) {
                    furniture = furniture.parent;
                }
                this.selectObject(furniture);
            } else if (this.currentFurnitureType) {
                // Place furniture
                const floorIntersects = this.raycaster.intersectObjects([this.floor]);
                if (floorIntersects.length > 0) {
                    this.addFurniture(this.currentFurnitureType, floorIntersects[0].point);
                }
            }
        }
    }

    handleUIClick(e) {
        const target = e.target;
        
        if (target.id === 'toggle-mode-btn') {
            this.toggleMode();
        } else if (target.id === 'save-room-btn') {
            this.saveRoom();
        } else if (target.id === 'clear-scene-btn') {
            if (confirm('Clear all objects?')) {
                this.clearScene();
            }
        } else if (target.classList.contains('furniture-item')) {
            const type = target.dataset.type;
            this.setFurnitureType(type);
        } else if (target.classList.contains('color-picker')) {
            const color = target.dataset.color;
            this.currentWallColor = parseInt(color, 16);
        }
    }

    setFurnitureType(type) {
        this.currentFurnitureType = type;
        document.querySelectorAll('.furniture-item').forEach(item => {
            item.classList.toggle('active', item.dataset.type === type);
        });
    }

    toggleWallPaintMode() {
        // Toggle wall paint mode UI
        const mode = document.getElementById('wall-paint-mode');
        if (mode) {
            mode.style.display = mode.style.display === 'none' ? 'block' : 'none';
        }
    }

    paintWall(color) {
        if (this.selectedWall) {
            this.selectedWall.material.color.setHex(color);
        }
    }

    addFurniture(type, position) {
        const furniture = this.createFurniture(type);
        furniture.position.copy(position);
        furniture.position.y = this.getFurnitureHeight(type) / 2;
        furniture.userData = {
            type: type,
            width: this.getFurnitureDimensions(type).width,
            height: this.getFurnitureHeight(type),
            depth: this.getFurnitureDimensions(type).depth
        };
        
        this.scene.add(furniture);
        this.objects.push(furniture);
        this.updateUI();
    }

    createFurniture(type) {
        const group = new THREE.Group();
        
        switch(type) {
            case 'bed': this.createBed(group); break;
            case 'chair': this.createChair(group); break;
            case 'table': this.createTable(group); break;
            case 'sofa': this.createSofa(group); break;
            case 'desk': this.createDesk(group); break;
            case 'shelf': this.createShelf(group); break;
            case 'lamp': this.createLamp(group); break;
            case 'plant': this.createPlant(group); break;
        }
        
        group.castShadow = true;
        group.receiveShadow = true;
        group.userData.furnitureType = type;
        return group;
    }

    createBed(group) {
        const woodMat = new THREE.MeshStandardMaterial({ color: 0x8b4513, roughness: 0.7 });
        const fabricMat = new THREE.MeshStandardMaterial({ color: 0xff6b9d, roughness: 0.8 });
        
        const mattress = new THREE.Mesh(new THREE.BoxGeometry(1.4, 0.3, 0.6), fabricMat);
        mattress.position.y = 0.15;
        mattress.castShadow = true;
        group.add(mattress);
        
        const frame = new THREE.Mesh(new THREE.BoxGeometry(1.5, 0.1, 0.7), woodMat);
        frame.castShadow = true;
        group.add(frame);
        
        const pillow = new THREE.Mesh(new THREE.BoxGeometry(0.4, 0.15, 0.4), new THREE.MeshStandardMaterial({ color: 0xffffff }));
        pillow.position.set(0.3, 0.35, 0);
        pillow.castShadow = true;
        group.add(pillow);
    }

    createChair(group) {
        const woodMat = new THREE.MeshStandardMaterial({ color: 0x8b6914, roughness: 0.6 });
        const fabricMat = new THREE.MeshStandardMaterial({ color: 0xccaa55, roughness: 0.7 });
        
        const seat = new THREE.Mesh(new THREE.BoxGeometry(0.5, 0.08, 0.5), fabricMat);
        seat.position.y = 0.4;
        seat.castShadow = true;
        group.add(seat);
        
        const back = new THREE.Mesh(new THREE.BoxGeometry(0.5, 0.4, 0.08), fabricMat);
        back.position.set(0, 0.6, -0.25);
        back.castShadow = true;
        group.add(back);
        
        const legGeom = new THREE.BoxGeometry(0.06, 0.4, 0.06);
        for (let i of [-0.15, 0.15]) {
            for (let j of [-0.15, 0.15]) {
                const leg = new THREE.Mesh(legGeom, woodMat);
                leg.position.set(i, 0.2, j);
                leg.castShadow = true;
                group.add(leg);
            }
        }
    }

    createTable(group) {
        const woodMat = new THREE.MeshStandardMaterial({ color: 0xa0522d, roughness: 0.6 });
        
        const top = new THREE.Mesh(new THREE.BoxGeometry(1, 0.08, 0.8), woodMat);
        top.position.y = 0.75;
        top.castShadow = true;
        group.add(top);
        
        for (let i of [-0.4, 0.4]) {
            for (let j of [-0.3, 0.3]) {
                const leg = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.75, 0.08), woodMat);
                leg.position.set(i, 0.375, j);
                leg.castShadow = true;
                group.add(leg);
            }
        }
    }

    createSofa(group) {
        const fabricMat = new THREE.MeshStandardMaterial({ color: 0x666666, roughness: 0.8 });
        
        const seat = new THREE.Mesh(new THREE.BoxGeometry(2, 0.1, 0.8), fabricMat);
        seat.position.y = 0.4;
        seat.castShadow = true;
        group.add(seat);
        
        const back = new THREE.Mesh(new THREE.BoxGeometry(2, 0.6, 0.1), fabricMat);
        back.position.set(0, 0.65, -0.45);
        back.castShadow = true;
        group.add(back);
        
        for (let x of [-0.95, 0.95]) {
            const arm = new THREE.Mesh(new THREE.BoxGeometry(0.1, 0.5, 0.8), fabricMat);
            arm.position.set(x, 0.55, 0);
            arm.castShadow = true;
            group.add(arm);
        }
    }

    createDesk(group) {
        const woodMat = new THREE.MeshStandardMaterial({ color: 0xa0522d });
        
        const top = new THREE.Mesh(new THREE.BoxGeometry(1.2, 0.08, 0.75), woodMat);
        top.position.y = 0.75;
        top.castShadow = true;
        group.add(top);
        
        for (let i of [-0.5, 0.5]) {
            for (let j of [-0.3, 0.3]) {
                const leg = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.75, 0.08), woodMat);
                leg.position.set(i, 0.375, j);
                leg.castShadow = true;
                group.add(leg);
            }
        }
    }

    createShelf(group) {
        const woodMat = new THREE.MeshStandardMaterial({ color: 0x8b6914 });
        
        for (let i = 0; i < 3; i++) {
            const shelf = new THREE.Mesh(new THREE.BoxGeometry(0.8, 0.04, 0.4), woodMat);
            shelf.position.y = 0.2 + i * 0.4;
            shelf.castShadow = true;
            group.add(shelf);
        }
        
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
        
        const base = new THREE.Mesh(new THREE.CylinderGeometry(0.15, 0.15, 0.05), metalMat);
        base.position.y = 0.025;
        base.castShadow = true;
        group.add(base);
        
        const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, 0.4), metalMat);
        pole.position.y = 0.25;
        pole.castShadow = true;
        group.add(pole);
        
        const bulb = new THREE.Mesh(new THREE.SphereGeometry(0.06, 16, 16), lightMat);
        bulb.position.y = 0.5;
        bulb.castShadow = true;
        group.add(bulb);
        
        const light = new THREE.PointLight(0xffff99, 0.7, 10);
        light.position.y = 0.5;
        group.add(light);
    }

    createPlant(group) {
        const potMat = new THREE.MeshStandardMaterial({ color: 0x8b4513 });
        const leafMat = new THREE.MeshStandardMaterial({ color: 0x228b22, roughness: 0.6 });
        
        const pot = new THREE.Mesh(new THREE.ConeGeometry(0.2, 0.3, 8), potMat);
        pot.position.y = 0.15;
        pot.castShadow = true;
        group.add(pot);
        
        const soil = new THREE.Mesh(new THREE.CylinderGeometry(0.18, 0.18, 0.02), new THREE.MeshStandardMaterial({ color: 0x654321 }));
        soil.position.y = 0.3;
        soil.castShadow = true;
        group.add(soil);
        
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
            bed: 0.3, chair: 0.8, table: 0.8, sofa: 0.9,
            desk: 0.75, shelf: 1.2, lamp: 0.5, plant: 0.4
        };
        return heights[type] || 0.5;
    }

    getFurnitureDimensions(type) {
        const dims = {
            bed: { width: 1.4, depth: 0.6 },
            chair: { width: 0.6, depth: 0.6 },
            table: { width: 1.0, depth: 0.8 },
            sofa: { width: 2.0, depth: 0.8 },
            desk: { width: 1.2, depth: 0.75 },
            shelf: { width: 0.8, depth: 0.4 },
            lamp: { width: 0.2, depth: 0.2 },
            plant: { width: 0.4, depth: 0.4 }
        };
        return dims[type] || { width: 0.5, depth: 0.5 };
    }

    selectObject(obj) {
        if (this.selectedObject) {
            this.selectedObject.traverse(child => {
                if (child.material) {
                    child.material.emissive.setHex(0x000000);
                }
            });
        }
        
        this.selectedObject = obj;
        obj.traverse(child => {
            if (child.material) {
                child.material.emissive.setHex(0x444444);
            }
        });
        
        this.updateUI();
    }

    deleteSelected() {
        if (!this.selectedObject) return;
        
        this.scene.remove(this.selectedObject);
        this.objects = this.objects.filter(o => o !== this.selectedObject);
        this.selectedObject = null;
        this.updateUI();
    }

    toggleMode() {
        this.mode = this.mode === 'explore' ? 'build' : 'explore';
        this.selectedObject = null;
        
        if (this.mode === 'explore') {
            document.exitPointerLock?.();
        }
        
        this.updateUI();
    }

    setupUI() {
        this.updateUI();
    }

    updateUI() {
        const htmlContent = `
            <style>
                #editor-ui { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 10; font-family: system-ui; }
                .ui-panel { position: absolute; background: rgba(0, 0, 0, 0.8); border: 1px solid #444; border-radius: 8px; padding: 15px; color: white; pointer-events: all; font-size: 12px; }
                .top-left { top: 20px; left: 20px; width: 280px; }
                .top-right { top: 20px; right: 20px; width: 250px; }
                .bottom-left { bottom: 20px; left: 20px; max-width: 400px; max-height: 250px; overflow-y: auto; }
                .bottom-right { bottom: 20px; right: 20px; width: 250px; }
                h3 { margin: 0 0 10px 0; font-size: 14px; }
                .mode-badge { display: inline-block; padding: 4px 10px; background: ${this.mode === 'explore' ? '#8b5cf6' : '#06b6d4'}; border-radius: 4px; margin: 5px 0; font-size: 11px; font-weight: bold; }
                button { background: #3b82f6; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; margin: 4px 4px 4px 0; font-size: 11px; }
                button:hover { background: #2563eb; }
                button.danger { background: #dc2626; }
                button.danger:hover { background: #b91c1c; }
                .info-text { font-size: 11px; color: #cbd5e1; margin: 4px 0; }
                .furniture-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 8px 0; }
                .furniture-item { display: flex; flex-direction: column; align-items: center; padding: 8px; background: rgba(71, 85, 105, 0.3); border: 2px solid #555; border-radius: 4px; cursor: pointer; font-size: 20px; }
                .furniture-item:hover { background: rgba(71, 85, 105, 0.6); border-color: #3b82f6; }
                .furniture-item.active { background: rgba(59, 130, 246, 0.5); border-color: #3b82f6; }
                .color-picker { display: inline-block; width: 30px; height: 30px; border-radius: 4px; border: 2px solid white; cursor: pointer; margin: 4px; }
                .help-text { font-size: 10px; color: #94a3b8; margin: 8px 0; line-height: 1.4; background: rgba(2, 6, 23, 0.5); padding: 8px; border-left: 2px solid #3b82f6; }
            </style>
            
            <div class="ui-panel top-left">
                <h3>🏠 3D Room Editor</h3>
                <div class="mode-badge">${this.mode.toUpperCase()} MODE</div>
                <button id="toggle-mode-btn" style="width: 100%;">Switch Mode [E]</button>
                <button id="save-room-btn" style="width: 100%; margin-top: 8px;">💾 Save</button>
                <button class="danger" id="clear-scene-btn" style="width: 100%; margin-top: 8px;">Clear Scene</button>
                ${this.mode === 'explore' ? `
                    <div class="help-text"><strong>Explore:</strong><br/>WASD Move | Mouse Look | Click Paint</div>
                ` : `
                    <div class="help-text"><strong>Build:</strong><br/>Click Furniture | Drag Rotate | C Paint</div>
                `}
            </div>
            
            <div class="ui-panel top-right">
                <h3>Status</h3>
                <div class="info-text">📍 Objects: ${this.objects.length}</div>
                <div class="info-text">👁️ Mode: ${this.mode === 'explore' ? 'First-Person' : 'Third-Person'}</div>
                <div class="info-text">🎨 Tool: ${this.currentFurnitureType ? this.currentFurnitureType.toUpperCase() : 'NONE'}</div>
                ${this.selectedObject ? `<div class="info-text">✓ Selected: ${this.selectedObject.userData.furnitureType}</div>` : ''}
            </div>
            
            ${this.mode === 'build' ? `
                <div class="ui-panel bottom-left">
                    <h3>Furniture (1-8)</h3>
                    <div class="furniture-grid">
                        ${[{t:'bed',e:'🛏️'},{t:'chair',e:'🪑'},{t:'table',e:'📦'},{t:'sofa',e:'🛋️'},{t:'desk',e:'🖥️'},{t:'shelf',e:'📚'},{t:'lamp',e:'🔦'},{t:'plant',e:'🪴'}].map((f,i) => `
                            <div class="furniture-item${this.currentFurnitureType === f.t ? ' active' : ''}" data-type="${f.t}">${f.e}</div>
                        `).join('')}
                    </div>
                    <h3>Wall Paint (C)</h3>
                    <div id="wall-paint-mode">
                        ${['#eeeeee','#ff6b6b','#4ecdc4','#ffe66d','#95e1d3'].map(c => `
                            <div class="color-picker" data-color="${c.replace('#','')}" style="background-color: ${c};"></div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}
            
            <div class="ui-panel bottom-right">
                <h3>Info</h3>
                <div class="info-text">Character: Hoodie Model</div>
                <div class="info-text">Render: ThreeJS</div>
                <div class="info-text">FPS: <span id="fps-counter">60</span></div>
            </div>
        `;
        
        let uiPanel = document.getElementById('editor-ui');
        if (!uiPanel) {
            uiPanel = document.createElement('div');
            uiPanel.id = 'editor-ui';
            document.body.appendChild(uiPanel);
        }
        uiPanel.innerHTML = htmlContent;
    }

    updateCameraPosition() {
        if (this.mode === 'explore') {
            // First-person movement
            const moveVector = new THREE.Vector3();
            const forward = new THREE.Vector3(0, 0, -1).applyAxisAngle(new THREE.Vector3(0, 1, 0), this.cameraYaw);
            const right = new THREE.Vector3(1, 0, 0).applyAxisAngle(new THREE.Vector3(0, 1, 0), this.cameraYaw);
            
            if (this.keys['w']) moveVector.add(forward);
            if (this.keys['s']) moveVector.sub(forward);
            if (this.keys['a']) moveVector.sub(right);
            if (this.keys['d']) moveVector.add(right);
            
            moveVector.normalize();
            moveVector.multiplyScalar(this.cameraSpeed * 0.016);
            
            this.character.position.add(moveVector);
            
            // Update camera
            this.camera.position.copy(this.character.position);
            this.camera.position.y += this.characterHeight - 0.2;
            
            const direction = new THREE.Vector3(0, 0, -1)
                .applyAxisAngle(new THREE.Vector3(1, 0, 0), this.cameraPitch)
                .applyAxisAngle(new THREE.Vector3(0, 1, 0), this.cameraYaw);
            this.camera.lookAt(this.camera.position.clone().add(direction));
            
            // Rotate character to face camera direction
            this.character.rotation.y = this.cameraYaw;
        } else {
            // Third-person camera
            this.updateCameraForMode();
        }
    }

    loadRoomData() {
        const objects = window.roomData.objects || [];
        for (let objData of objects) {
            this.addFurniture(objData.type, new THREE.Vector3(...objData.position));
        }
    }

    clearScene() {
        this.objects.forEach(obj => this.scene.remove(obj));
        this.objects = [];
        this.selectedObject = null;
        this.updateUI();
    }

    saveRoom() {
        const roomData = this.objects.map(obj => ({
            type: obj.userData.furnitureType,
            position: [obj.position.x, obj.position.y, obj.position.z],
            rotation: [obj.rotation.x, obj.rotation.y, obj.rotation.z],
            scale: [obj.scale.x, obj.scale.y, obj.scale.z],
            confidence: null
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
                console.log('Room data:', data);
            } else {
                alert('❌ Failed to save room');
            }
        })
        .catch(err => {
            console.error('Save error:', err);
            alert('❌ Error saving room');
        });
    }

    startRenderLoop() {
        const animate = () => {
            requestAnimationFrame(animate);
            this.updateCameraPosition();
            this.renderer.render(this.scene, this.camera);
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
