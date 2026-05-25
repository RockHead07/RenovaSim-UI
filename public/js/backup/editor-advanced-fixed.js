/**
 * Advanced 3D Room Editor - FIXED VERSION
 * Proper character movement + Three.js editor-like build mode
 * 
 * EXPLORE MODE: WASD to move (W=forward, A=left, S=backward, D=right), Mouse to look
 * BUILD MODE: Gizmo controls like Three.js editor
 */

class AdvancedRoom3DEditor {
    constructor() {
        if (typeof THREE === 'undefined') {
            throw new Error('THREE.js not loaded');
        }

        this.canvas = document.getElementById('canvas');
        if (!this.canvas) throw new Error('Canvas not found');

        // Scene setup
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        
        // Character
        this.character = null;
        this.characterModel = null;
        this.characterHeight = 1.7;
        this.characterSpeed = 0.15;
        this.characterSprintSpeed = 0.25;
        
        // Mode
        this.mode = 'explore'; // 'explore' or 'build'
        this.selectedObject = null;
        
        // Input
        this.keys = {};
        this.isPointerLocked = false;
        this.mouseLook = { yaw: 0, pitch: 0 };
        this.mouseDown = false;
        this.lastMouseMove = { x: 0, y: 0 };
        
        // Build mode
        this.transformControls = null;
        this.editorCamera = null; // Separate camera for editor mode
        this.editorCameraDistance = 8;
        this.editorCameraTarget = new THREE.Vector3(0, 1, 0);
        this.editorCameraYaw = 0;
        this.editorCameraPitch = Math.PI / 4;
        
        // Room
        this.roomWidth = 4;
        this.roomLength = 5;
        this.roomHeight = 3;
        this.objects = [];
        this.walls = {};
        this.floor = null;
        this.ceiling = null;
        
        // Loaders
        this.gltfLoader = null;
        
        // Clock
        this.clock = new THREE.Clock();
        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2();
        
        console.log('✅ Editor constructor ready');
    }

    init() {
        console.log('🚀 Initializing Editor...');
        this.setupScene();
        this.setupRenderer();
        this.setupRoom();
        this.setupCharacter();
        this.setupCamera();
        this.setupLights();
        this.setupLoaders();
        this.loadCharacterModel();
        this.setupEventListeners();
        this.setupUI();
        this.loadRoomData();
        this.startRenderLoop();
        console.log('✅ Editor initialized!');
    }

    setupScene() {
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x87ceeb);
        this.scene.fog = new THREE.Fog(0x87ceeb, 150, 300);
    }

    setupRenderer() {
        this.renderer = new THREE.WebGLRenderer({ 
            canvas: this.canvas, 
            antialias: true,
            alpha: true 
        });
        this.renderer.setSize(this.canvas.clientWidth, this.canvas.clientHeight);
        this.renderer.setPixelRatio(window.devicePixelRatio);
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFShadowShadowMap;
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.exposure = 1;

        window.addEventListener('resize', () => this.onWindowResize());
    }

    setupRoom() {
        if (window.roomData?.room) {
            this.roomWidth = window.roomData.room.width || 4;
            this.roomLength = window.roomData.room.length || 5;
            this.roomHeight = window.roomData.room.height || 3;
        }

        console.log(`📐 Room: ${this.roomWidth}m × ${this.roomLength}m × ${this.roomHeight}m`);

        // Floor
        const floorGeom = new THREE.PlaneGeometry(this.roomWidth, this.roomLength);
        const floorMat = new THREE.MeshStandardMaterial({ 
            color: 0xdddddd,
            roughness: 0.8 
        });
        this.floor = new THREE.Mesh(floorGeom, floorMat);
        this.floor.rotation.x = -Math.PI / 2;
        this.floor.receiveShadow = true;
        this.floor.name = 'floor';
        this.scene.add(this.floor);

        // Ceiling
        const ceilingGeom = new THREE.PlaneGeometry(this.roomWidth, this.roomLength);
        const ceilingMat = new THREE.MeshStandardMaterial({ color: 0xffffff });
        this.ceiling = new THREE.Mesh(ceilingGeom, ceilingMat);
        this.ceiling.position.y = this.roomHeight;
        this.ceiling.rotation.x = Math.PI / 2;
        this.ceiling.receiveShadow = true;
        this.scene.add(this.ceiling);

        // Walls
        const wallMat = new THREE.MeshStandardMaterial({ color: 0xeeeeee });
        
        // Front wall
        this.walls.front = new THREE.Mesh(
            new THREE.PlaneGeometry(this.roomWidth, this.roomHeight), 
            wallMat.clone()
        );
        this.walls.front.position.z = -this.roomLength / 2;
        this.walls.front.position.y = this.roomHeight / 2;
        this.walls.front.receiveShadow = true;
        this.walls.front.name = 'wall-front';
        this.scene.add(this.walls.front);

        // Back wall
        this.walls.back = new THREE.Mesh(
            new THREE.PlaneGeometry(this.roomWidth, this.roomHeight), 
            wallMat.clone()
        );
        this.walls.back.position.z = this.roomLength / 2;
        this.walls.back.position.y = this.roomHeight / 2;
        this.walls.back.rotation.y = Math.PI;
        this.walls.back.receiveShadow = true;
        this.walls.back.name = 'wall-back';
        this.scene.add(this.walls.back);

        // Left wall
        this.walls.left = new THREE.Mesh(
            new THREE.PlaneGeometry(this.roomLength, this.roomHeight), 
            wallMat.clone()
        );
        this.walls.left.position.x = -this.roomWidth / 2;
        this.walls.left.position.y = this.roomHeight / 2;
        this.walls.left.rotation.y = Math.PI / 2;
        this.walls.left.receiveShadow = true;
        this.walls.left.name = 'wall-left';
        this.scene.add(this.walls.left);

        // Right wall
        this.walls.right = new THREE.Mesh(
            new THREE.PlaneGeometry(this.roomLength, this.roomHeight), 
            wallMat.clone()
        );
        this.walls.right.position.x = this.roomWidth / 2;
        this.walls.right.position.y = this.roomHeight / 2;
        this.walls.right.rotation.y = -Math.PI / 2;
        this.walls.right.receiveShadow = true;
        this.walls.right.name = 'wall-right';
        this.scene.add(this.walls.right);

        // Grid helper
        const gridSize = Math.max(this.roomWidth, this.roomLength);
        const gridHelper = new THREE.GridHelper(gridSize * 1.5, 20, 0xcccccc, 0xeeeeee);
        gridHelper.position.y = 0.01;
        this.scene.add(gridHelper);
    }

    setupCharacter() {
        this.character = new THREE.Group();
        this.character.position.set(0, 0, 0);
        
        // Simple character model (fallback)
        const bodyGeom = new THREE.CylinderGeometry(0.2, 0.25, 0.8, 8);
        const bodyMat = new THREE.MeshStandardMaterial({ color: 0x3b82f6 });
        const body = new THREE.Mesh(bodyGeom, bodyMat);
        body.position.y = 0.4;
        body.castShadow = true;
        this.character.add(body);
        
        const headGeom = new THREE.SphereGeometry(0.15, 16, 16);
        const headMat = new THREE.MeshStandardMaterial({ color: 0xffdbac });
        const head = new THREE.Mesh(headGeom, headMat);
        head.position.y = 1.2;
        head.castShadow = true;
        this.character.add(head);
        
        this.scene.add(this.character);
    }

    setupCamera() {
        const width = this.canvas.clientWidth;
        const height = this.canvas.clientHeight;
        
        // Main camera (for all modes)
        this.camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
        this.updateCameraMode();
    }

    setupLights() {
        // Ambient light
        const ambient = new THREE.AmbientLight(0xffffff, 0.7);
        this.scene.add(ambient);
        
        // Directional light
        const sun = new THREE.DirectionalLight(0xffffff, 0.9);
        sun.position.set(15, 25, 15);
        sun.castShadow = true;
        sun.shadow.mapSize.width = 2048;
        sun.shadow.mapSize.height = 2048;
        sun.shadow.camera.left = -50;
        sun.shadow.camera.right = 50;
        sun.shadow.camera.top = 50;
        sun.shadow.camera.bottom = -50;
        sun.shadow.camera.near = 0.1;
        sun.shadow.camera.far = 500;
        this.scene.add(sun);
        
        // Point light
        const point = new THREE.PointLight(0xffffff, 0.4);
        point.position.set(0, 5, 0);
        this.scene.add(point);
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

        const path = '/images/Hoodie Character.glb';
        this.gltfLoader.load(
            path,
            (gltf) => {
                console.log('✅ Character model loaded');
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
        document.addEventListener('mousedown', (e) => this.onMouseDown(e));
        document.addEventListener('mouseup', (e) => this.onMouseUp(e));
        document.addEventListener('mousemove', (e) => this.onMouseMove(e));
        document.addEventListener('wheel', (e) => this.onMouseWheel(e));
        
        // Pointer lock
        document.addEventListener('click', () => {
            if (this.mode === 'explore' && !this.isPointerLocked) {
                this.canvas.requestPointerLock();
            }
        });
        document.addEventListener('pointerlockchange', () => {
            this.isPointerLocked = document.pointerLockElement === this.canvas;
        });

        // Raycasting for object selection in build mode
        this.canvas.addEventListener('click', (e) => this.onCanvasClick(e));
    }

    onKeyDown(e) {
        const key = e.key.toLowerCase();
        this.keys[key] = true;

        // Mode toggle
        if (key === 'e') {
            this.toggleMode();
        }

        // Delete object in build mode
        if (key === 'delete' && this.mode === 'build') {
            this.deleteSelected();
        }
    }

    onKeyUp(e) {
        this.keys[e.key.toLowerCase()] = false;
    }

    onMouseDown(e) {
        this.mouseDown = true;
        this.lastMouseMove = { x: e.clientX, y: e.clientY };
    }

    onMouseUp(e) {
        this.mouseDown = false;
    }

    onMouseMove(e) {
        if (this.mode === 'explore' && this.isPointerLocked) {
            // First-person look
            const sensitivity = 0.002;
            this.mouseLook.yaw += e.movementX * sensitivity;
            this.mouseLook.pitch += e.movementY * sensitivity;
            this.mouseLook.pitch = Math.max(-Math.PI / 2.5, Math.min(Math.PI / 2.5, this.mouseLook.pitch));
        } else if (this.mode === 'build' && this.mouseDown) {
            // Editor camera control
            const deltaX = e.clientX - this.lastMouseMove.x;
            const deltaY = e.clientY - this.lastMouseMove.y;
            this.lastMouseMove = { x: e.clientX, y: e.clientY };

            if (e.button === 2) { // Right mouse button
                // Rotate view
                this.editorCameraYaw += deltaX * 0.01;
                this.editorCameraPitch -= deltaY * 0.01;
                this.editorCameraPitch = Math.max(0.1, Math.min(Math.PI - 0.1, this.editorCameraPitch));
            } else if (e.button === 0 && e.shiftKey) { // Left + Shift
                // Pan view
                const panSpeed = 0.05;
                const right = new THREE.Vector3(1, 0, 0);
                const up = new THREE.Vector3(0, 1, 0);
                const forward = this.editorCameraTarget.clone()
                    .sub(this.camera.position)
                    .normalize();
                
                right.cross(forward).normalize();
                up.cross(right).normalize();
                
                this.editorCameraTarget.add(right.multiplyScalar(-deltaX * panSpeed));
                this.editorCameraTarget.add(up.multiplyScalar(deltaY * panSpeed));
            }
        }
    }

    onMouseWheel(e) {
        e.preventDefault();
        if (this.mode === 'build') {
            this.editorCameraDistance += e.deltaY * 0.005;
            this.editorCameraDistance = Math.max(2, Math.min(50, this.editorCameraDistance));
        }
    }

    onCanvasClick(e) {
        if (this.mode !== 'build') return;

        const rect = this.canvas.getBoundingClientRect();
        this.mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
        this.mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;

        this.raycaster.setFromCamera(this.mouse, this.camera);

        // Try to select furniture
        const intersects = this.raycaster.intersectObjects(this.objects, true);
        if (intersects.length > 0) {
            let obj = intersects[0].object;
            while (obj.parent && !this.objects.includes(obj)) {
                obj = obj.parent;
            }
            this.selectObject(obj);
        } else {
            this.deselectObject();
        }
    }

    selectObject(obj) {
        this.deselectObject();
        this.selectedObject = obj;
        
        // Highlight selected object
        obj.traverse(child => {
            if (child.material && !child.material.isLineBasicMaterial) {
                child.userData.originalColor = child.material.color.getHex();
                child.material.emissive.setHex(0x444444);
            }
        });
        
        this.updateUI();
    }

    deselectObject() {
        if (this.selectedObject) {
            this.selectedObject.traverse(child => {
                if (child.material && !child.material.isLineBasicMaterial) {
                    child.material.emissive.setHex(0x000000);
                }
            });
            this.selectedObject = null;
        }
    }

    deleteSelected() {
        if (!this.selectedObject) return;
        this.scene.remove(this.selectedObject);
        this.objects = this.objects.filter(o => o !== this.selectedObject);
        this.deselectObject();
        this.updateUI();
    }

    toggleMode() {
        this.mode = this.mode === 'explore' ? 'build' : 'explore';
        this.deselectObject();
        
        if (this.mode === 'explore') {
            document.exitPointerLock?.();
        }
        
        this.updateCameraMode();
        this.updateUI();
    }

    updateCameraMode() {
        if (this.mode === 'explore') {
            // First-person view
            this.camera.position.copy(this.character.position);
            this.camera.position.y += this.characterHeight - 0.15;
        } else {
            // Build mode - isometric-like view
            const x = Math.sin(this.editorCameraYaw) * this.editorCameraDistance * Math.cos(this.editorCameraPitch);
            const y = Math.sin(this.editorCameraPitch) * this.editorCameraDistance;
            const z = Math.cos(this.editorCameraYaw) * this.editorCameraDistance * Math.cos(this.editorCameraPitch);
            
            this.camera.position.set(
                this.editorCameraTarget.x + x,
                this.editorCameraTarget.y + y + 1,
                this.editorCameraTarget.z + z
            );
            this.camera.lookAt(this.editorCameraTarget.x, this.editorCameraTarget.y + 1, this.editorCameraTarget.z);
        }
    }

    updateCharacterMovement() {
        if (this.mode !== 'explore') return;

        const speed = this.keys['shift'] ? this.characterSprintSpeed : this.characterSpeed;
        const moveVector = new THREE.Vector3();

        // Get the camera direction
        const cameraDirection = new THREE.Vector3(0, 0, -1)
            .applyAxisAngle(new THREE.Vector3(0, 1, 0), this.mouseLook.yaw);
        
        const right = new THREE.Vector3(1, 0, 0)
            .applyAxisAngle(new THREE.Vector3(0, 1, 0), this.mouseLook.yaw);

        // FIXED WASD CONTROLS:
        // W = move forward
        // S = move backward
        // A = move left
        // D = move right

        if (this.keys['w']) moveVector.add(cameraDirection);      // W = forward
        if (this.keys['s']) moveVector.sub(cameraDirection);      // S = backward
        if (this.keys['a']) moveVector.sub(right);                // A = left
        if (this.keys['d']) moveVector.add(right);                // D = right

        if (moveVector.length() > 0) {
            moveVector.normalize();
            moveVector.multiplyScalar(speed);
            this.character.position.add(moveVector);
        }

        // Update character rotation to face movement direction
        if (moveVector.length() > 0) {
            const angle = Math.atan2(moveVector.x, moveVector.z);
            this.character.rotation.y = angle;
        } else {
            // Face camera direction when not moving
            this.character.rotation.y = this.mouseLook.yaw;
        }

        // Clamp character position to room bounds
        const hw = this.roomWidth / 2 - 0.3;
        const hl = this.roomLength / 2 - 0.3;
        this.character.position.x = Math.max(-hw, Math.min(hw, this.character.position.x));
        this.character.position.z = Math.max(-hl, Math.min(hl, this.character.position.z));
        this.character.position.y = 0; // Keep on ground
    }

    loadRoomData() {
        const objects = window.roomData?.objects || [];
        for (let data of objects) {
            const pos = new THREE.Vector3(...data.position);
            const rot = data.rotation ? data.rotation.map(r => r || 0) : [0, 0, 0];
            const scale = data.scale ? data.scale.map(s => s || 1) : [1, 1, 1];
            this.addFurniture(data.type, pos, rot, scale);
        }
    }

    addFurniture(type, pos, rot = [0, 0, 0], scale = [1, 1, 1]) {
        const furniture = this.createFurniture(type);
        furniture.position.set(pos.x, pos.y, pos.z);
        furniture.rotation.set(rot[0], rot[1], rot[2]);
        furniture.scale.set(scale[0], scale[1], scale[2]);
        furniture.userData.type = type;
        
        this.scene.add(furniture);
        this.objects.push(furniture);
        this.updateUI();
    }

    createFurniture(type) {
        const group = new THREE.Group();
        group.castShadow = true;
        group.receiveShadow = true;

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

        return group;
    }

    createBed(group) {
        const mattress = new THREE.Mesh(
            new THREE.BoxGeometry(1.4, 0.3, 0.6),
            new THREE.MeshStandardMaterial({ color: 0xff6b9d })
        );
        mattress.position.y = 0.15;
        mattress.castShadow = true;
        group.add(mattress);

        const pillow = new THREE.Mesh(
            new THREE.BoxGeometry(0.4, 0.15, 0.4),
            new THREE.MeshStandardMaterial({ color: 0xffffff })
        );
        pillow.position.set(0.3, 0.35, 0);
        pillow.castShadow = true;
        group.add(pillow);
    }

    createChair(group) {
        const seat = new THREE.Mesh(
            new THREE.BoxGeometry(0.5, 0.08, 0.5),
            new THREE.MeshStandardMaterial({ color: 0xccaa55 })
        );
        seat.position.y = 0.4;
        seat.castShadow = true;
        group.add(seat);

        const back = new THREE.Mesh(
            new THREE.BoxGeometry(0.5, 0.4, 0.08),
            new THREE.MeshStandardMaterial({ color: 0xccaa55 })
        );
        back.position.set(0, 0.6, -0.25);
        back.castShadow = true;
        group.add(back);
    }

    createTable(group) {
        const top = new THREE.Mesh(
            new THREE.BoxGeometry(1, 0.08, 0.8),
            new THREE.MeshStandardMaterial({ color: 0xa0522d })
        );
        top.position.y = 0.75;
        top.castShadow = true;
        group.add(top);
    }

    createSofa(group) {
        const seat = new THREE.Mesh(
            new THREE.BoxGeometry(2, 0.1, 0.8),
            new THREE.MeshStandardMaterial({ color: 0x666666 })
        );
        seat.position.y = 0.4;
        seat.castShadow = true;
        group.add(seat);
    }

    createDesk(group) {
        const top = new THREE.Mesh(
            new THREE.BoxGeometry(1.2, 0.08, 0.75),
            new THREE.MeshStandardMaterial({ color: 0xa0522d })
        );
        top.position.y = 0.75;
        top.castShadow = true;
        group.add(top);
    }

    createShelf(group) {
        for (let i = 0; i < 3; i++) {
            const shelf = new THREE.Mesh(
                new THREE.BoxGeometry(0.8, 0.04, 0.4),
                new THREE.MeshStandardMaterial({ color: 0x8b6914 })
            );
            shelf.position.y = 0.2 + i * 0.4;
            shelf.castShadow = true;
            group.add(shelf);
        }
    }

    createLamp(group) {
        const base = new THREE.Mesh(
            new THREE.CylinderGeometry(0.15, 0.15, 0.05),
            new THREE.MeshStandardMaterial({ color: 0x222222 })
        );
        base.position.y = 0.025;
        base.castShadow = true;
        group.add(base);

        const bulb = new THREE.Mesh(
            new THREE.SphereGeometry(0.06, 16, 16),
            new THREE.MeshStandardMaterial({ color: 0xffffdd, emissive: 0xffff00, emissiveIntensity: 0.5 })
        );
        bulb.position.y = 0.5;
        bulb.castShadow = true;
        group.add(bulb);
    }

    createPlant(group) {
        const pot = new THREE.Mesh(
            new THREE.ConeGeometry(0.2, 0.3, 8),
            new THREE.MeshStandardMaterial({ color: 0x8b4513 })
        );
        pot.position.y = 0.15;
        pot.castShadow = true;
        group.add(pot);
    }

    setupUI() {
        this.updateUI();
    }

    updateUI() {
        let panel = document.getElementById('editor-ui');
        if (!panel) {
            panel = document.createElement('div');
            panel.id = 'editor-ui';
            document.body.appendChild(panel);
        }

        const modeColor = this.mode === 'explore' ? '#8b5cf6' : '#06b6d4';
        const modeName = this.mode === 'explore' ? 'EXPLORE' : 'BUILD';
        const selectedInfo = this.selectedObject ? `Selected: ${this.selectedObject.userData.type}` : 'None';

        panel.innerHTML = `
            <style>
                #editor-ui { position: fixed; top: 0; left: 0; width: 100%; height: 100vh; pointer-events: none; z-index: 10; font-family: system-ui; }
                .ui-panel { position: fixed; background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(10px); border: 1px solid #555; border-radius: 8px; padding: 15px; color: white; pointer-events: all; font-size: 12px; }
                .top-left { top: 80px; left: 20px; width: 280px; }
                .top-right { top: 80px; right: 20px; width: 280px; }
                .bottom-left { bottom: 20px; left: 20px; max-width: 400px; max-height: 300px; overflow-y: auto; }
                .bottom-right { bottom: 20px; right: 20px; width: 280px; }
                h3 { margin: 0 0 10px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
                .badge { display: inline-block; padding: 6px 12px; background: ${modeColor}; border-radius: 4px; font-size: 11px; font-weight: bold; }
                button { background: #3b82f6; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 11px; margin: 4px 4px 4px 0; transition: 0.2s; }
                button:hover { background: #2563eb; }
                button.danger { background: #dc2626; }
                button.danger:hover { background: #b91c1c; }
                .info { font-size: 11px; color: #cbd5e1; margin: 6px 0; }
                .help { font-size: 10px; color: #94a3b8; background: rgba(2, 6, 23, 0.5); padding: 8px; border-left: 2px solid #3b82f6; margin: 8px 0; line-height: 1.5; }
                .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 8px 0; }
                .item { display: flex; flex-direction: column; align-items: center; padding: 8px; background: rgba(71, 85, 105, 0.3); border: 2px solid #555; border-radius: 4px; cursor: pointer; font-size: 18px; transition: 0.2s; }
                .item:hover { background: rgba(71, 85, 105, 0.6); border-color: #3b82f6; }
                .item.active { background: rgba(59, 130, 246, 0.5); border-color: #3b82f6; }
            </style>
            
            <div class="ui-panel top-left">
                <h3>🏠 Room Editor</h3>
                <div class="badge">${modeName} MODE</div>
                <button style="width: 100%; margin-top: 8px;" onclick="window.editor.toggleMode()">Switch Mode [E]</button>
                <button style="width: 100%; margin-top: 8px;" onclick="window.editor.saveRoom()">💾 Save</button>
                <button class="danger" style="width: 100%; margin-top: 8px;" onclick="if(confirm('Clear all?')) window.editor.clearScene()">Clear All</button>
                
                <div class="help">
                    ${this.mode === 'explore' ? `
                        <strong>WASD</strong> Move | <strong>Mouse</strong> Look | <strong>Shift</strong> Sprint | <strong>E</strong> Build
                    ` : `
                        <strong>Click</strong> Select | <strong>Right+Drag</strong> Rotate | <strong>Scroll</strong> Zoom | <strong>E</strong> Explore
                    `}
                </div>
            </div>
            
            <div class="ui-panel top-right">
                <h3>Status</h3>
                <div class="info">📍 Objects: ${this.objects.length}</div>
                <div class="info">👁️ Mode: ${this.mode === 'explore' ? '1st Person' : 'Editor'}</div>
                <div class="info">✓ ${selectedInfo}</div>
                ${this.selectedObject ? `<button class="danger" style="width: 100%; margin-top: 8px;" onclick="window.editor.deleteSelected()">Delete Selected</button>` : ''}
            </div>
            
            ${this.mode === 'build' ? `
                <div class="ui-panel bottom-left">
                    <h3>Furniture</h3>
                    <div class="grid">
                        <div class="item" onclick="window.editor.selectFurnitureType('bed')">🛏️</div>
                        <div class="item" onclick="window.editor.selectFurnitureType('chair')">🪑</div>
                        <div class="item" onclick="window.editor.selectFurnitureType('table')">📦</div>
                        <div class="item" onclick="window.editor.selectFurnitureType('sofa')">🛋️</div>
                        <div class="item" onclick="window.editor.selectFurnitureType('desk')">🖥️</div>
                        <div class="item" onclick="window.editor.selectFurnitureType('shelf')">📚</div>
                        <div class="item" onclick="window.editor.selectFurnitureType('lamp')">🔦</div>
                        <div class="item" onclick="window.editor.selectFurnitureType('plant')">🪴</div>
                    </div>
                </div>
            ` : ''}
            
            <div class="ui-panel bottom-right">
                <h3>Info</h3>
                <div class="info">📐 Room: ${this.roomWidth}×${this.roomLength}×${this.roomHeight}m</div>
                <div class="info">👤 Character: Hoodie Model</div>
                <div class="info">🎮 Engine: Three.js</div>
                <div class="info">FPS: <span id="fps">60</span></div>
            </div>
        `;
    }

    selectFurnitureType(type) {
        const pos = new THREE.Vector3(0, 1, 2);
        this.addFurniture(type, pos);
    }

    clearScene() {
        this.objects.forEach(obj => this.scene.remove(obj));
        this.objects = [];
        this.deselectObject();
        this.updateUI();
    }

    saveRoom() {
        const data = this.objects.map(obj => ({
            type: obj.userData.type,
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
            body: JSON.stringify({ objects: data })
        })
        .then(r => r.json())
        .then(d => {
            alert(d.success ? '✅ Saved!' : '❌ Failed');
            console.log(d);
        })
        .catch(e => {
            console.error(e);
            alert('❌ Error');
        });
    }

    startRenderLoop() {
        const animate = () => {
            requestAnimationFrame(animate);
            const delta = this.clock.getDelta();

            // Update character movement
            this.updateCharacterMovement();

            // Update camera based on mode
            this.updateCameraMode();

            // Render
            this.renderer.render(this.scene, this.camera);

            // Update FPS
            document.getElementById('fps').textContent = Math.round(1 / delta);
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

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    if (typeof THREE !== 'undefined') {
        window.editor = new AdvancedRoom3DEditor();
        window.editor.init();
    }
});
