/**
 * Advanced 3D Room Editor v4 - Complete Enhancement
 * - Third-Person Perspective (Explore Mode) - like a game with character animation
 * - Build Mode with Unity-like editor (TransformControls for drag/rotate/scale)
 * - Door/Portal system for room transitions
 * - Room expansion & dynamic sizing
 * - Improved physics and ground collision
 * - Non-inverted mouse controls
 */

class AdvancedRoom3DEditorV4 {
    constructor() {
        if (typeof THREE === 'undefined') {
            throw new Error('THREE.js is not loaded');
        }

        this.canvas = document.getElementById('canvas');
        if (!this.canvas) {
            throw new Error('Canvas element with id="canvas" not found');
        }

        // Scene setup
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2();

        // Character system
        this.character = null;
        this.characterModel = null;
        this.characterSize = 0.3;
        this.characterHeight = 1.7;
        this.animations = {};
        this.animationActions = {};
        this.currentAnimation = 'idle';

        // Mode and selection
        this.mode = 'explore'; // 'explore' or 'build'
        this.selectedObject = null;
        this.selectedWall = null;
        this.currentFurnitureType = null;
        this.currentWallColor = 0xeeeeee;
        this.objects = [];
        this.doors = [];

        // Input system
        this.keys = {};
        this.mouseMovement = { x: 0, y: 0 };
        this.isPointerLocked = false;
        this.mouseDown = { left: false, right: false, middle: false };

        // Camera system (TPP)
        this.cameraSpeed = 10;
        this.cameraRotationSpeed = 0.002;
        this.cameraPitch = 0;
        this.cameraYaw = 0;
        this.cameraDistance = 3; // TPP distance
        this.cameraHeight = 1.2; // Eye level offset

        // Transform controls (Unity-like)
        this.transformControls = null;
        this.transformMode = 'translate'; // translate, rotate, scale

        // Room system
        this.walls = {};
        this.floor = null;
        this.ceiling = null;
        this.roomWidth = 4;
        this.roomLength = 5;
        this.roomHeight = 3;

        // Physics
        this.velocity = new THREE.Vector3();
        this.gravity = -9.8;
        this.isGrounded = false;
        this.characterCollider = null;

        // Loaders
        this.gltfLoader = null;
        this.mixer = null;
        this.clock = new THREE.Clock();

        // Room expansion UI
        this.isExpandingRoom = false;
        this.expandDirection = null;

        console.log('✅ AdvancedRoom3DEditorV4 constructor initialized');
    }

    init() {
        console.log('🚀 Initializing Advanced 3D Editor V4...');
        this.setupScene();
        this.setupRenderer();
        this.setupRoom();
        this.setupCharacter();
        this.setupCamera();
        this.setupLights();
        this.setupLoaders();
        this.loadCharacterModel();
        this.setupTransformControls();
        this.setupEventListeners();
        this.setupUI();
        this.loadRoomData();
        this.startRenderLoop();
        console.log('✅ Editor V4 initialized successfully!');
    }

    setupScene() {
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x87ceeb);
        this.scene.fog = new THREE.Fog(0x87ceeb, 100, 200);
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
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);

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

        const pointLight = new THREE.PointLight(0xffffff, 0.4);
        pointLight.position.set(0, 5, 0);
        this.scene.add(pointLight);
    }

    setupRoom() {
        const roomData = window.roomData?.room || {};
        this.roomWidth = roomData.width || 4;
        this.roomLength = roomData.length || 5;
        this.roomHeight = roomData.height || 3;

        this.createRoomGeometry();
    }

    createRoomGeometry() {
        // Remove existing room if any
        if (this.floor) this.scene.remove(this.floor);
        if (this.ceiling) this.scene.remove(this.ceiling);
        Object.values(this.walls).forEach(wall => this.scene.remove(wall));

        const w = this.roomWidth;
        const l = this.roomLength;
        const h = this.roomHeight;

        // Floor
        const floorGeom = new THREE.PlaneGeometry(w, l);
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
        const ceilingGeom = new THREE.PlaneGeometry(w, l);
        const ceilingMat = new THREE.MeshStandardMaterial({ color: 0xffffff });
        this.ceiling = new THREE.Mesh(ceilingGeom, ceilingMat);
        this.ceiling.position.y = h;
        this.ceiling.rotation.x = Math.PI / 2;
        this.ceiling.receiveShadow = true;
        this.scene.add(this.ceiling);

        // Walls
        const wallMat = new THREE.MeshStandardMaterial({ color: 0xeeeeee });

        // Front
        this.walls.front = new THREE.Mesh(new THREE.PlaneGeometry(w, h), wallMat.clone());
        this.walls.front.position.z = -l / 2;
        this.walls.front.position.y = h / 2;
        this.walls.front.receiveShadow = true;
        this.walls.front.name = 'wall-front';
        this.walls.front.userData.wallType = 'front';
        this.scene.add(this.walls.front);

        // Back
        this.walls.back = new THREE.Mesh(new THREE.PlaneGeometry(w, h), wallMat.clone());
        this.walls.back.position.z = l / 2;
        this.walls.back.position.y = h / 2;
        this.walls.back.rotation.y = Math.PI;
        this.walls.back.receiveShadow = true;
        this.walls.back.name = 'wall-back';
        this.walls.back.userData.wallType = 'back';
        this.scene.add(this.walls.back);

        // Left
        this.walls.left = new THREE.Mesh(new THREE.PlaneGeometry(l, h), wallMat.clone());
        this.walls.left.position.x = -w / 2;
        this.walls.left.position.y = h / 2;
        this.walls.left.rotation.y = Math.PI / 2;
        this.walls.left.receiveShadow = true;
        this.walls.left.name = 'wall-left';
        this.walls.left.userData.wallType = 'left';
        this.scene.add(this.walls.left);

        // Right
        this.walls.right = new THREE.Mesh(new THREE.PlaneGeometry(l, h), wallMat.clone());
        this.walls.right.position.x = w / 2;
        this.walls.right.position.y = h / 2;
        this.walls.right.rotation.y = -Math.PI / 2;
        this.walls.right.receiveShadow = true;
        this.walls.right.name = 'wall-right';
        this.walls.right.userData.wallType = 'right';
        this.scene.add(this.walls.right);

        // Grid
        const gridHelper = new THREE.GridHelper(Math.max(w, l), 20, 0xcccccc, 0xeeeeee);
        gridHelper.position.y = 0.01;
        this.scene.add(gridHelper);

        // Ground for physics
        const ground = new THREE.Mesh(
            new THREE.PlaneGeometry(200, 200),
            new THREE.MeshStandardMaterial({ color: 0x2b2b2b })
        );
        ground.rotation.x = -Math.PI / 2;
        ground.position.y = -0.01;
        ground.receiveShadow = true;
        ground.name = 'ground';
        this.scene.add(ground);
    }

    setupCharacter() {
        this.character = new THREE.Group();
        this.character.position.set(0, 0, 2);

        // Fallback character (until GLB loads)
        const characterGroup = new THREE.Group();

        const bodyGeom = new THREE.CylinderGeometry(0.2, 0.25, 0.8, 8);
        const bodyMat = new THREE.MeshStandardMaterial({ color: 0x4488ff, roughness: 0.6 });
        const body = new THREE.Mesh(bodyGeom, bodyMat);
        body.position.y = 0.4;
        body.castShadow = true;
        characterGroup.add(body);

        const headGeom = new THREE.SphereGeometry(0.15, 16, 16);
        const headMat = new THREE.MeshStandardMaterial({ color: 0xffdbac });
        const head = new THREE.Mesh(headGeom, headMat);
        head.position.y = 1.2;
        head.castShadow = true;
        characterGroup.add(head);

        this.character.add(characterGroup);
        this.scene.add(this.character);

        // Character collider
        this.characterCollider = new THREE.Sphere(new THREE.Vector3(0, 0.85, 0), 0.3);
    }

    setupCamera() {
        const width = this.canvas.clientWidth;
        const height = this.canvas.clientHeight;
        this.camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
        this.updateCameraPosition();
    }

    setupTransformControls() {
        if (typeof TransformControls === 'undefined') {
            console.warn('TransformControls not available');
            return;
        }

        this.transformControls = new TransformControls(this.camera, this.renderer.domElement);
        this.transformControls.setMode('translate');
        this.transformControls.addEventListener('change', () => this.renderer.render(this.scene, this.camera));
        this.transformControls.addEventListener('dragging-changed', (event) => {
            // Disable orbit controls while transforming
        });
        this.scene.add(this.transformControls);
    }

    setupLoaders() {
        if (typeof GLTFLoader !== 'undefined') {
            this.gltfLoader = new GLTFLoader();
        }
    }

    loadCharacterModel() {
        if (!this.gltfLoader) {
            console.warn('GLTFLoader not available');
            return;
        }

        const characterPath = '/images/Hoodie Character.glb';
        this.gltfLoader.load(
            characterPath,
            (gltf) => {
                this.character.children.forEach(child => this.character.remove(child));

                const model = gltf.scene;
                model.scale.set(1.5, 1.5, 1.5);
                model.position.y = -0.5;

                model.traverse((node) => {
                    if (node.isMesh) {
                        node.castShadow = true;
                        node.receiveShadow = true;
                    }
                });

                this.character.add(model);
                this.characterModel = model;

                // Setup animation mixer
                if (gltf.animations && gltf.animations.length > 0) {
                    this.mixer = new THREE.AnimationMixer(model);
                    gltf.animations.forEach((clip) => {
                        this.mixer.clipAction(clip).play();
                    });
                }

                console.log('✅ Character model loaded');
            },
            undefined,
            (error) => console.error('❌ Failed to load character:', error)
        );
    }

    setupEventListeners() {
        // Keyboard
        document.addEventListener('keydown', (e) => this.onKeyDown(e));
        document.addEventListener('keyup', (e) => this.onKeyUp(e));

        // Mouse
        document.addEventListener('click', () => this.requestPointerLock());
        document.addEventListener('pointerlockchange', () => this.onPointerLockChange());
        document.addEventListener('mousemove', (e) => this.onMouseMove(e));
        document.addEventListener('mousedown', (e) => this.onMouseDown(e));
        document.addEventListener('mouseup', (e) => this.onMouseUp(e));

        this.canvas.addEventListener('click', (e) => this.onCanvasClick(e));
        document.addEventListener('click', (e) => this.handleUIClick(e));
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

        if (key === 'c' && this.mode === 'build') {
            this.toggleWallPaintMode();
        }

        if (key === 'g' && this.mode === 'explore') {
            this.addDoor();
        }

        // Furniture shortcuts
        if (this.mode === 'build') {
            const shortcuts = {
                '1': 'bed', '2': 'chair', '3': 'table', '4': 'sofa',
                '5': 'desk', '6': 'shelf', '7': 'lamp', '8': 'plant'
            };
            if (shortcuts[key]) {
                this.setFurnitureType(shortcuts[key]);
            }
        }

        // Transform mode (build)
        if (this.mode === 'build' && this.transformControls) {
            if (key === 'q') this.transformControls.setMode('translate');
            if (key === 'w') this.transformControls.setMode('rotate');
            if (key === 'r') this.transformControls.setMode('scale');
        }
    }

    onKeyUp(e) {
        this.keys[e.key.toLowerCase()] = false;
    }

    onMouseDown(e) {
        if (e.button === 0) this.mouseDown.left = true;
        if (e.button === 2) this.mouseDown.right = true;
        if (e.button === 1) this.mouseDown.middle = true;
    }

    onMouseUp(e) {
        if (e.button === 0) this.mouseDown.left = false;
        if (e.button === 2) this.mouseDown.right = false;
        if (e.button === 1) this.mouseDown.middle = false;
    }

    onMouseMove(e) {
        if (this.mode === 'explore') {
            if (!this.isPointerLocked) return;

            // Non-inverted mouse (standard controls)
            this.cameraYaw -= e.movementX * this.cameraRotationSpeed;
            this.cameraPitch -= e.movementY * this.cameraRotationSpeed; // Removed inversion

            this.cameraPitch = Math.max(-Math.PI / 2.5, Math.min(Math.PI / 2.5, this.cameraPitch));
        } else if (this.mode === 'build' && this.mouseDown.middle) {
            // Middle mouse to rotate camera
            this.cameraYaw -= e.movementX * this.cameraRotationSpeed;
            this.cameraPitch -= e.movementY * this.cameraRotationSpeed;
            this.cameraPitch = Math.max(-Math.PI / 2.5, Math.min(Math.PI / 2.5, this.cameraPitch));
        } else if (this.mode === 'build' && this.mouseDown.right) {
            // Right mouse to zoom
            this.cameraDistance += e.movementY * 0.05;
            this.cameraDistance = Math.max(1, Math.min(20, this.cameraDistance));
        }
    }

    onCanvasClick(e) {
        if (this.mode === 'build') {
            const rect = this.canvas.getBoundingClientRect();
            this.mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
            this.mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;

            this.raycaster.setFromCamera(this.mouse, this.camera);

            // Check wall intersection
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
            this.setFurnitureType(target.dataset.type);
        } else if (target.classList.contains('color-picker')) {
            this.currentWallColor = parseInt(target.dataset.color, 16);
        } else if (target.id?.includes('expand-')) {
            this.expandRoom(target.id.replace('expand-', ''));
        } else if (target.id?.includes('shrink-')) {
            this.shrinkRoom(target.id.replace('shrink-', ''));
        }
    }

    expandRoom(direction) {
        const expansion = 1; // Expand by 1m
        switch(direction) {
            case 'width':
                this.roomWidth += expansion;
                break;
            case 'length':
                this.roomLength += expansion;
                break;
            case 'height':
                this.roomHeight += expansion;
                break;
        }
        this.createRoomGeometry();
        this.updateUI();
    }

    shrinkRoom(direction) {
        const shrinkage = 1;
        switch(direction) {
            case 'width':
                this.roomWidth = Math.max(2, this.roomWidth - shrinkage);
                break;
            case 'length':
                this.roomLength = Math.max(2, this.roomLength - shrinkage);
                break;
            case 'height':
                this.roomHeight = Math.max(2, this.roomHeight - shrinkage);
                break;
        }
        this.createRoomGeometry();
        this.updateUI();
    }

    addDoor() {
        // Add door at character position
        const door = this.createDoor();
        door.position.copy(this.character.position);
        door.position.x = this.roomWidth / 2 - 0.5;
        this.scene.add(door);
        this.doors.push(door);
        console.log('🚪 Door added');
    }

    createDoor() {
        const doorGroup = new THREE.Group();

        // Door frame
        const frameGeom = new THREE.BoxGeometry(1, 2, 0.1);
        const frameMat = new THREE.MeshStandardMaterial({ color: 0x8b6914 });
        const frame = new THREE.Mesh(frameGeom, frameMat);
        doorGroup.add(frame);

        // Door panel
        const panelGeom = new THREE.BoxGeometry(0.8, 1.8, 0.05);
        const panelMat = new THREE.MeshStandardMaterial({ color: 0xd4a574 });
        const panel = new THREE.Mesh(panelGeom, panelMat);
        panel.position.z = 0.05;
        doorGroup.add(panel);

        // Door knob
        const knobGeom = new THREE.SphereGeometry(0.05, 16, 16);
        const knobMat = new THREE.MeshStandardMaterial({ color: 0xffd700, metalness: 0.8 });
        const knob = new THREE.Mesh(knobGeom, knobMat);
        knob.position.set(0.3, 0.9, 0.1);
        doorGroup.add(knob);

        doorGroup.userData.isDoor = true;
        doorGroup.castShadow = true;
        doorGroup.receiveShadow = true;
        return doorGroup;
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

        // Attach to transform controls
        if (this.transformControls && this.mode === 'build') {
            this.transformControls.attach(obj);
        }

        this.updateUI();
    }

    deleteSelected() {
        if (!this.selectedObject) return;

        if (this.transformControls) {
            this.transformControls.detach(this.selectedObject);
        }

        this.scene.remove(this.selectedObject);
        this.objects = this.objects.filter(o => o !== this.selectedObject);
        this.selectedObject = null;
        this.updateUI();
    }

    setFurnitureType(type) {
        this.currentFurnitureType = type;
        document.querySelectorAll('.furniture-item').forEach(item => {
            item.classList.toggle('active', item.dataset.type === type);
        });
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
        const heights = { bed: 0.3, chair: 0.8, table: 0.8, sofa: 0.9, desk: 0.75, shelf: 1.2, lamp: 0.5, plant: 0.4 };
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

    toggleWallPaintMode() {
        const mode = document.getElementById('wall-paint-mode');
        if (mode) {
            mode.style.display = mode.style.display === 'none' ? 'block' : 'none';
        }
    }

    toggleMode() {
        this.mode = this.mode === 'explore' ? 'build' : 'explore';
        this.selectedObject = null;

        if (this.transformControls) {
            this.transformControls.detach();
        }

        if (this.mode === 'explore') {
            document.exitPointerLock?.();
        }

        this.updateUI();
    }

    updateCameraPosition() {
        if (this.mode === 'explore') {
            // Third-Person Perspective
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

            // Clamp character to room
            this.character.position.x = Math.max(-this.roomWidth / 2 + 0.5, Math.min(this.roomWidth / 2 - 0.5, this.character.position.x));
            this.character.position.z = Math.max(-this.roomLength / 2 + 0.5, Math.min(this.roomLength / 2 - 0.5, this.character.position.z));

            // Keep character on ground
            this.character.position.y = 0;

            // TPP Camera position
            const cameraOffsetDistance = this.cameraDistance;
            const cameraOffsetHeight = this.cameraHeight;

            const cameraX = this.character.position.x - Math.sin(this.cameraYaw) * cameraOffsetDistance;
            const cameraY = this.character.position.y + cameraOffsetHeight;
            const cameraZ = this.character.position.z - Math.cos(this.cameraYaw) * cameraOffsetDistance;

            this.camera.position.set(cameraX, cameraY, cameraZ);

            const lookX = this.character.position.x + Math.sin(this.cameraPitch) * 0.3;
            const lookY = this.character.position.y + this.characterHeight * 0.8 - Math.sin(this.cameraPitch) * 0.5;
            const lookZ = this.character.position.z;

            this.camera.lookAt(lookX, lookY, lookZ);

            // Rotate character
            this.character.rotation.y = this.cameraYaw;
        } else {
            // Build Mode - Orbit camera around character
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

    updateCharacterAnimation(delta) {
        if (this.mixer) {
            this.mixer.update(delta);
        }
    }

    updatePhysics(delta) {
        // Character gravity
        this.velocity.y += this.gravity * delta;

        // Keep character on ground
        if (this.character.position.y < 0) {
            this.character.position.y = 0;
            this.velocity.y = 0;
            this.isGrounded = true;
        } else {
            this.isGrounded = false;
        }
    }

    clearScene() {
        this.objects.forEach(obj => this.scene.remove(obj));
        this.objects = [];
        this.selectedObject = null;
        this.updateUI();
    }

    saveRoom() {
        const roomData = {
            room: {
                width: this.roomWidth,
                length: this.roomLength,
                height: this.roomHeight
            },
            objects: this.objects.map(obj => ({
                type: obj.userData.furnitureType,
                position: [obj.position.x, obj.position.y, obj.position.z],
                rotation: [obj.rotation.x, obj.rotation.y, obj.rotation.z],
                scale: [obj.scale.x, obj.scale.y, obj.scale.z]
            }))
        };

        fetch(window.saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify(roomData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('✅ Room saved successfully!');
            } else {
                alert('❌ Failed to save room');
            }
        })
        .catch(err => {
            console.error('Save error:', err);
            alert('❌ Error saving room');
        });
    }

    loadRoomData() {
        const objects = window.roomData?.objects || [];
        for (let objData of objects) {
            this.addFurniture(objData.type, new THREE.Vector3(...objData.position));
        }
    }

    setupUI() {
        this.updateUI();
    }

    updateUI() {
        const htmlContent = `
            <style>
                #editor-ui { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 10; font-family: system-ui; }
                .ui-panel { position: absolute; background: rgba(0, 0, 0, 0.85); border: 2px solid #0f766e; border-radius: 8px; padding: 15px; color: #00d9ff; pointer-events: all; font-size: 12px; }
                .top-left { top: 20px; left: 20px; width: 300px; }
                .top-right { top: 20px; right: 20px; width: 280px; }
                .bottom-left { bottom: 20px; left: 20px; max-width: 400px; max-height: 300px; overflow-y: auto; }
                .bottom-right { bottom: 20px; right: 20px; width: 280px; }
                h3 { margin: 0 0 10px 0; font-size: 14px; color: #00d9ff; border-bottom: 1px solid #0f766e; padding-bottom: 8px; }
                .mode-badge { display: inline-block; padding: 6px 12px; background: ${this.mode === 'explore' ? '#8b5cf6' : '#06b6d4'}; border-radius: 4px; margin: 5px 0; font-size: 11px; font-weight: bold; }
                button { background: #0f766e; color: #00d9ff; border: 1px solid #00d9ff; padding: 8px 12px; border-radius: 4px; cursor: pointer; margin: 4px 4px 4px 0; font-size: 11px; transition: all 0.2s; }
                button:hover { background: #00d9ff; color: #0f766e; }
                button.danger { background: #dc2626; color: white; border-color: #dc2626; }
                button.danger:hover { background: #b91c1c; }
                .info-text { font-size: 11px; color: #7dd3fc; margin: 4px 0; }
                .furniture-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 8px 0; }
                .furniture-item { display: flex; flex-direction: column; align-items: center; padding: 8px; background: rgba(15, 118, 110, 0.3); border: 2px solid #0f766e; border-radius: 4px; cursor: pointer; font-size: 20px; transition: all 0.2s; }
                .furniture-item:hover { background: rgba(15, 118, 110, 0.6); border-color: #00d9ff; }
                .furniture-item.active { background: rgba(0, 217, 255, 0.3); border-color: #00d9ff; }
                .color-picker { display: inline-block; width: 30px; height: 30px; border-radius: 4px; border: 2px solid #0f766e; cursor: pointer; margin: 4px; transition: all 0.2s; }
                .color-picker:hover { border-color: #00d9ff; transform: scale(1.1); }
                .help-text { font-size: 10px; color: #7dd3fc; margin: 8px 0; line-height: 1.4; background: rgba(15, 118, 110, 0.3); padding: 8px; border-left: 2px solid #00d9ff; }
                .room-controls { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin: 8px 0; }
                .room-control-label { font-size: 10px; color: #7dd3fc; margin-bottom: 4px; }
            </style>

            <div class="ui-panel top-left">
                <h3>🏠 3D Room Editor V4</h3>
                <div class="mode-badge">${this.mode.toUpperCase()} MODE</div>
                <button id="toggle-mode-btn" style="width: 100%;">⎇ Switch Mode [E]</button>
                <button id="save-room-btn" style="width: 100%; margin-top: 8px;">💾 Save Room</button>
                <button class="danger" id="clear-scene-btn" style="width: 100%; margin-top: 8px;">🗑️ Clear Scene</button>
                
                ${this.mode === 'explore' ? `
                    <div class="help-text">
                        <strong>🎮 Explore Mode</strong><br/>
                        WASD: Move | Mouse: Look | G: Add Door | E: Build
                    </div>
                ` : `
                    <div class="help-text">
                        <strong>🛠️ Build Mode</strong><br/>
                        Q: Move | W: Rotate | R: Scale<br/>
                        MMB: Rotate | RMB: Zoom | E: Play
                    </div>
                `}
            </div>

            <div class="ui-panel top-right">
                <h3>Status</h3>
                <div class="info-text">📍 Objects: ${this.objects.length}</div>
                <div class="info-text">👁️ View: ${this.mode === 'explore' ? '3rd Person' : 'Orbit'}</div>
                <div class="info-text">🎨 Tool: ${this.currentFurnitureType ? this.currentFurnitureType.toUpperCase() : 'NONE'}</div>
                <div class="info-text">📏 Room: ${this.roomWidth.toFixed(1)}x${this.roomLength.toFixed(1)}x${this.roomHeight.toFixed(1)}</div>
                ${this.selectedObject ? `<div class="info-text">✓ Selected: ${this.selectedObject.userData.furnitureType}</div>` : ''}
            </div>

            ${this.mode === 'build' ? `
                <div class="ui-panel bottom-left">
                    <h3>🪑 Furniture (1-8)</h3>
                    <div class="furniture-grid">
                        ${[{t:'bed',e:'🛏️'},{t:'chair',e:'🪑'},{t:'table',e:'📦'},{t:'sofa',e:'🛋️'},{t:'desk',e:'🖥️'},{t:'shelf',e:'📚'},{t:'lamp',e:'💡'},{t:'plant',e:'🪴'}].map(f => `
                            <div class="furniture-item${this.currentFurnitureType === f.t ? ' active' : ''}" data-type="${f.t}">${f.e}</div>
                        `).join('')}
                    </div>
                    
                    <h3>🎨 Wall Paint (C)</h3>
                    <div id="wall-paint-mode" style="display: none;">
                        ${['#eeeeee','#ff6b6b','#4ecdc4','#ffe66d','#95e1d3','#a29bfe'].map(c => `
                            <div class="color-picker" data-color="${c.replace('#','')}" style="background-color: ${c};"></div>
                        `).join('')}
                    </div>
                    
                    <h3>📐 Room Size</h3>
                    <div class="room-controls">
                        <div>
                            <div class="room-control-label">Width: ${this.roomWidth.toFixed(1)}m</div>
                            <button id="expand-width" style="width: 48%; margin-right: 4px;">+</button>
                            <button id="shrink-width" style="width: 48%;">-</button>
                        </div>
                        <div>
                            <div class="room-control-label">Length: ${this.roomLength.toFixed(1)}m</div>
                            <button id="expand-length" style="width: 48%; margin-right: 4px;">+</button>
                            <button id="shrink-length" style="width: 48%;">-</button>
                        </div>
                        <div>
                            <div class="room-control-label">Height: ${this.roomHeight.toFixed(1)}m</div>
                            <button id="expand-height" style="width: 48%; margin-right: 4px;">+</button>
                            <button id="shrink-height" style="width: 48%;">-</button>
                        </div>
                    </div>
                </div>
            ` : ''}

            <div class="ui-panel bottom-right">
                <h3>ℹ️ Info</h3>
                <div class="info-text">Character: Hoodie Model</div>
                <div class="info-text">Render: ThreeJS v${THREE.REVISION}</div>
                <div class="info-text">Camera: ${this.mode === 'explore' ? 'TPP' : 'Orbit'}</div>
                <div class="info-text">Physics: Enabled</div>
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

    requestPointerLock() {
        if (this.mode === 'explore' && !this.isPointerLocked) {
            this.canvas.requestPointerLock?.();
        }
    }

    onPointerLockChange() {
        this.isPointerLocked = document.pointerLockElement === this.canvas;
    }

    startRenderLoop() {
        let lastTime = Date.now();
        let frameCount = 0;
        let fpsTime = 0;

        const animate = () => {
            requestAnimationFrame(animate);

            const currentTime = Date.now();
            const delta = (currentTime - lastTime) / 1000;
            lastTime = currentTime;

            // Update physics
            this.updatePhysics(delta);

            // Update character animation
            this.updateCharacterAnimation(delta);

            // Update camera
            this.updateCameraPosition();

            // Render
            this.renderer.render(this.scene, this.camera);

            // FPS counter
            frameCount++;
            fpsTime += delta;
            if (fpsTime >= 1) {
                document.getElementById('fps-counter').textContent = frameCount;
                frameCount = 0;
                fpsTime = 0;
            }
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

// Initialize on load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.editor = new AdvancedRoom3DEditorV4();
        window.editor.init();
    });
} else {
    window.editor = new AdvancedRoom3DEditorV4();
    window.editor.init();
}
